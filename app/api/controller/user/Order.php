<?php

namespace app\api\controller\user;

use app\api\controller\Controller;
use app\api\controller\logic\AlipayLoginLogic;
use app\api\controller\logic\Pay;
use app\api\model\order\Order as OrderModel;
use app\api\model\order\OrderBill;
use app\api\model\order\OrderProduct;
use app\api\model\product\ProductSku;
use app\api\model\settings\Express;
use app\api\model\settings\Setting as SettingModel;
use app\api\service\pay\PayService;
use app\common\enum\order\OrderPayTypeEnum;
use app\common\service\qrcode\ExtractService;
use app\common\model\supplier\Service as ServiceModel;
use app\common\model\supplier\User as SupplierUserModel;
use app\common\model\app\App as AppModel;
use app\common\enum\order\OrderTypeEnum;
use think\facade\Env;

/**
 * 我的订单
 */
class Order extends Controller
{
    // user
    private $user;

    /**
     * 构造方法
     */
    public function initialize()
    {
        parent::initialize();
        $this->user = $this->getUser();   // 用户信息

    }

    /**
     * 我的订单列表
     */
    public function lists($dataType)
    {
        $data = $this->postData();
        $model = new OrderModel;
        $list = $model->getList($this->user['user_id'], $dataType, $data);
        $show_alipay = PayService::isAlipayOpen($data['pay_source'], $this->user['app_id']);
        return $this->renderSuccess('', compact('list', 'show_alipay'));
    }

    /**
     * 订单详情信息
     */
    public function detail($order_id, $pay_source = '')
    {
        // 订单详情
        $model = OrderModel::getUserOrderDetail($order_id, $this->user['user_id']);
        // 剩余支付时间
        if ($model['pay_status']['value'] == 10 && $model['order_status']['value'] != 20 && $model['pay_end_time'] != 0) {
            $model['pay_end_time'] = $this->formatPayEndTime($model['pay_end_time'] - time());
        } else {
            $model['pay_end_time'] = '';
        }
//        $orderBill = OrderBill::where('order_id','=',$model['order_id'])->select();
//        if ($orderBill){
//            $orderBill = $orderBill->toArray();
//            foreach ($orderBill as $k=>&$v){
//                if ($v['repayment_date']){
//                    $v['repayment_date'] = date('Y-m-d',$v['repayment_date']);
//                }
//            }
//        }
        //计算买断金额
        $isPayPrice = 0;

        $orderProduct = OrderProduct::where('order_id','=',$order_id)->find();
        $buy_out_price = ProductSku::where(['product_id'=>$orderProduct['product_id'],'spec_sku_id'=>$orderProduct['spec_sku_id']])->value('buy_out_price');
//        if (!$buy_out_price || $buy_out_price<0) return $this->renderError('订单异常请联系客服');

//        $orderBill = OrderBill::where(['order_id'=>$order_id])->select();
//        if ($orderBill){
//            foreach ($orderBill as $key=>$value){
//                $isPayPrice += $value['price'];
//            }
//        }

        //需支付金额
        $buyOutPrice = $buy_out_price-$isPayPrice;
        // 该订单是否允许申请售后
        $model['isAllowRefund'] = $model->isAllowRefund();
        $model['supplier']['supplier_user_id'] = (new SupplierUserModel())->where('shop_supplier_id', '=', $model['shop_supplier_id'])->value('supplier_user_id');
        return $this->renderSuccess('', [
            'order' => $model,  // 订单详情
            'orderBill' =>'',
            'buyOutPrice' => $buyOutPrice,
            'setting' => [
                // 积分名称
                'points_name' => SettingModel::getPointsName(),
                //是否开启客服
                'service_open' => SettingModel::getSysConfig()['service_open'],
                //店铺客服信息
                'mp_service' => ServiceModel::detail($model['shop_supplier_id']),
            ],
            'show_alipay' => PayService::isAlipayOpen($pay_source, $model['app_id'])
        ]);
    }

    /**
     * 支付成功详情信息
     */
    public function paySuccess($order_id)
    {
        $order_arr = explode(',', $order_id);
        $order = [
            'pay_price' => 0,
            'points_bonus' => 0
        ];
        foreach ($order_arr as $id) {
            $model = OrderModel::getUserOrderDetail($id, $this->user['user_id']);
            $order['pay_price'] += $model['pay_price'];
            $order['points_bonus'] += $model['points_bonus'];
        }
        $order['pay_price'] = round($order['pay_price'], 2);
        return $this->renderSuccess('', compact('order'));
    }

    /**
     * 获取物流信息
     */
    public function express($order_id)
    {
        // 订单信息
        $order = OrderModel::getUserOrderDetail($order_id, $this->user['user_id']);
        if (!$order['express_no']) {
            return $this->renderError('没有物流信息');
        }
        // 获取物流信息
        $model = $order['express'];
        $express = $model->dynamic($model['express_name'], $model['express_code'], $order['express_no']);
        if ($express === false) {
            return $this->renderError($model->getError());
        }
        return $this->renderSuccess('', compact('express'));
    }

    /**
     * 获取快递列表
     */
    public function getExpress(){
        $data = Express::select();
        return $this->renderSuccess('',$data);
    }

    /**
     * 归还订单
     */
    public function returned($order_id,$express_id,$express_no){
        $Express = Express::where('express_id','=',$express_id)->find();
        $res = OrderModel::where('order_id',$order_id)->update([
            'return_express_id'=> $express_id,
            'return_express_company'=> $Express['express_name'],
            'return_express_no'=> $express_no,
            'order_status' => 12,
            'update_time' => time(),
        ]);
        if($res){
            return $this->renderSuccess('归还成功，请等待商家验货后退还押金');
        }
        return $this->renderError('系统繁忙，请稍后再试');
    }
    /**
     * 取消订单
     */
    public function cancel($order_id)
    {
        $model = OrderModel::getUserOrderDetail($order_id, $this->user['user_id']);

        if ($model->cancel($this->user)) {
            if ($model['pay_status'] == 10){
                $states = 3;
            }elseif ($model['pay_status'] == 20){
                $states = 6;
            }else{
                $states = 3;
            }
            $data = [
                'app_id' => 10001,
                'order_id' => $order_id,
                'states' => $states,
            ];
            curlPost('https://'.Env::get('url.url', 'yuzhou.haidiao888.com').'/index.php/api/order.order_center/index2', $data);
            return $this->renderSuccess('订单取消成功');
        }
        return $this->renderError($model->getError() ?: '订单取消失败');
    }

    /**
     * 确认收货
     */
    public function receipt($order_id)
    {
        $model = OrderModel::getUserOrderDetail($order_id, $this->user['user_id']);
        if ($model->receipt()) {
            $data = [
                'app_id' => 10001,
                'order_id' => $order_id,
                'states' => 8,
            ];
            curlPost('https://'.Env::get('url.url', 'yuzhou.haidiao888.com').'/index.php/api/order.order_center/index2', $data);
            return $this->renderSuccess('收货成功');
        }
        return $this->renderError($model->getError() ?: '收货失败');
    }

    /**
     * 立即支付
     */
    public function zpay($order_id, $payType = OrderPayTypeEnum::WECHAT)
    {
        // 获取订单详情
        $model = OrderModel::getUserOrderDetail($order_id, $this->user['user_id']);
        $params = $this->postData();
        if ($this->request->isGet()) {
            // 开启的支付类型
            $payTypes = AppModel::getPayType($model['app_id'], $params['pay_source']);
            // 支付金额
            $payPrice = $model['pay_price'];
            $balance = $this->user['balance'];
            return $this->renderSuccess('', compact('payTypes', 'payPrice', 'balance'));
        }
        // 订单支付事件
        if (!$model->onPay($payType)) {
            return $this->renderError($model->getError() ?: '订单支付失败');
        }
        // 构建支付请求
        $payInfo = (new OrderModel)->OrderPay($this->getUser(),$params);
        // 支付状态提醒
        return $this->renderSuccess('', [
            'order_id' => $payInfo['order_id'],   // 订单id
            'pay_type' => $payInfo['payType'],  // 支付方式
            'payment' => $payInfo['payment'],   // 微信支付参数
            'order_type' => OrderTypeEnum::MASTER, //订单类型
        ]);
    }

    /**
     * 订单支付
     */
    public function pay()
    {
        $params = $this->postData();
        $user = $this->getUser();
        $payDetail = OrderModel::orderInfo($params['order_id'], $user);
//        halt(json_decode(json_encode($payDetail),true));
//        $orderBill = (new OrderBill())
//            ->where('order_id','=',$params['order_id'])
//            ->where('is_pay','=',1)
//            ->where('repayment_date','=',strtotime(date('Y-m-d',time())))
//            ->select();
        $payPrice = $payDetail['total_price'];
//        foreach ($orderBill as $k=>$v){
//            $payPrice += $v['price'];
//            if ($v['current_period']==1){
//                $payPrice += $payDetail['deposit']+$v['price'];
//            }else{
//                $payPrice += $v['price'];
//            }
//        }
//        $payPrice = floor($payPrice*100)/100;
        $payPrice = $payPrice*100/100;
        if ($this->request->isGet()){
            // 开启的支付类型
            $payTypes = AppModel::getPayType($payDetail['app_id'], $params['pay_source']);
            // 支付金额
            $balance = $user['balance'];
            return $this->renderSuccess('', compact('payTypes', 'payPrice', 'balance'));
        }
        $notifyUrl = base_url() . 'index.php/job/notify/alipay_notify';
        $payInfo = (new OrderModel)->OrderPay($user, $params,$payPrice,'',$notifyUrl);

        if ($payInfo){
            return $this->renderSuccess('', $payInfo);
        }
        return $this->renderError('系统繁忙,请稍后再试');
//        $payInfo = Pay::pay();
//        base_url() . 'index.php/job/notify/alipay_notify?order_type='.$orderType.'&pay_source='.$pay_source.'&ple='.$multiple;
//       print_r($payInfo);die;
//        return $this->renderSuccess('', [
//            'order_id' => $payInfo['order_id'],   // 订单id
//            'pay_type' => $payInfo['payType'],  // 支付方式
//            'payment' => $payInfo['payment'],  // 支付参数
//            'order_type' => OrderTypeEnum::MASTER, //订单类型
//        ]);
    }
    /**
     * 分期订单支付
     */
    public function billPay()
    {
        $params = $this->postData();
        $user = $this->getUser();
        $payPrice = 0;
        $orderBill = (new OrderBill())
            ->where('bill_id','=',$params['bill_id'])
            ->find();
        $payPrice = $orderBill['price'];
        $order = \app\api\model\order\Order::where('order_id','=',$orderBill['order_id'])->find();
//        print_r($order->toArray());die;
        if (empty($order) || $order['order_status']['value']!=10)  return $this->renderError('订单状态异常，不可支付');

        $notifyUrl = base_url() . 'index.php/job/notify/orderBill_notify';
        $payInfo = Pay::pay($order['order_no'].'_'.$params['bill_id'],$payPrice,'ces',$user['open_id'],$notifyUrl);

        if ($payInfo){
            $data = $payInfo;
            $data->order_id[] = $orderBill['order_id'];
            return $this->renderSuccess('', $data);
        }
        return $this->renderError('系统繁忙,请稍后再试');
//        $payInfo = Pay::pay();
//        base_url() . 'index.php/job/notify/alipay_notify?order_type='.$orderType.'&pay_source='.$pay_source.'&ple='.$multiple;
//       print_r($payInfo);die;
//        return $this->renderSuccess('', [
//            'order_id' => $payInfo['order_id'],   // 订单id
//            'pay_type' => $payInfo['payType'],  // 支付方式
//            'payment' => $payInfo['payment'],  // 支付参数
//            'order_type' => OrderTypeEnum::MASTER, //订单类型
//        ]);
    }
    /**
     * 获取订单核销二维码
     */
    public function qrcode($order_id, $source)
    {
        // 订单详情
        $order = OrderModel::getUserOrderDetail($order_id, $this->user['user_id']);
        // 判断是否为待核销订单
        if (!$order->checkExtractOrder($order)) {
            return $this->renderError($order->getError());
        }
        $Qrcode = new ExtractService(
            $this->app_id,
            $this->user,
            $order_id,
            $source,
            $order['order_no']
        );
        return $this->renderSuccess('', [
            'qrcode' => $Qrcode->getImage(),
        ]);
    }

    private function formatPayEndTime($leftTime)
    {
        if ($leftTime <= 0) {
            return '';
        }

        $str = '';
        $day = floor($leftTime / 86400);
        $hour = floor(($leftTime - $day * 86400) / 3600);
        $min = floor((($leftTime - $day * 86400) - $hour * 3600) / 60);

        if ($day > 0) $str .= $day . '天';
        if ($hour > 0) $str .= $hour . '小时';
        if ($min > 0) $str .= $min . '分钟';
        return $str;
    }
}