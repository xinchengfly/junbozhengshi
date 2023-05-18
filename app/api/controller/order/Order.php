<?php

namespace app\api\controller\order;

use app\api\controller\logic\AlipayLoginLogic;
use app\api\controller\logic\Pay;
use app\api\model\order\Cart as CartModel;
use app\api\model\order\Order as OrderModel;
use app\api\model\order\OrderBill;
use app\api\model\order\OrderProduct;
use app\api\model\product\ProductSku;
use app\api\model\user\User;
use app\api\service\order\settled\MasterOrderSettledService;
use app\api\controller\Controller;
use app\api\model\settings\Message as MessageModel;
use app\api\service\pay\PayService;
use app\common\enum\order\OrderTypeEnum;
use app\common\library\helper;
use app\api\model\settings\Setting as SettingModel;
use app\common\model\app\App as AppModel;
use app\job\controller\Notify;
use think\facade\Db;
use app\common\model\applist\Applist as ApplistModel;

/**
 * 普通订单
 */
class Order extends Controller
{
    /**
     * 订单确认-立即购买
     */
    public function buy()
    {
        // 立即购买：获取订单商品列表
        $params = json_decode($this->postData()['params'], true);
        $post = $this->request->post();
        if (!empty($post['appid'])){
            $appid = $post['appid'];
            $data = ApplistModel::detail(['appid'=>$appid]);
            if (!$data){
                return $this->renderError('appid错误');
            }
            $appid_id = $data['id'];
        }else{
            $appid_id = 1;
        }

        $supplierData = OrderModel::getOrderProductListByNow($params);

//--------------------
        $user = $this->getUsertest();
        // 实例化订单service
        $orderService = new MasterOrderSettledService($user, $supplierData, $params);

        //判断偏远地区
        $remoteAddress = $orderService->remoteAddress($params['address']);
        if ($remoteAddress){
            return $this->renderError('当前地区不在配送范围');
        }

        // 获取订单信息
        if (!isset($params['address'])){
            $params['address'] = [];
        }
        $orderInfo = $orderService->settlement($params['address']);



//-------订单信息查询---------
//        if ($lease_time) {
//            $orderInfo['lease_time'] = $lease_time;
////            halt(json_decode(json_encode($orderInfo['supplierList']),true));
//            $orderInfo['lease_time']['number_of_periods'] = intval($lease_time['lease_time'] / 30);
//            //每期金额
//            $remaining_lease_term = floor(($orderInfo['supplierList']['0']['productList']['0']['product_price'] / $orderInfo['lease_time']['number_of_periods']) * 100) / 100;
//            $orderInfo['lease_time']['remaining_lease_term'] = '¥' . $remaining_lease_term . '*' . ($orderInfo['lease_time']['number_of_periods'] - 1) . '期';
//            $orderInfo['lease_time']['remaining_lease_term_price'] = $remaining_lease_term;
//            //尾期金额
//            $end_period = floor(($orderInfo['supplierList']['0']['productList']['0']['product_price'] - ($remaining_lease_term * ($orderInfo['lease_time']['number_of_periods'] - 1))) * 100) / 100;
//            $orderInfo['lease_time']['end_period'] = '¥' . $end_period . '*1期';
//            $orderInfo['lease_time']['end_period_price'] = $end_period;
//
//            //押金
//            $orderInfo['lease_time']['deposit'] = $orderInfo['supplierList']['0']['productList']['0']['product_sku']['deposit'];
//            //总金额
//            $orderInfo['lease_time']['total_amount'] = ($orderInfo['supplierList']['0']['productList']['0']['deposit'] + $orderInfo['supplierList']['0']['productList']['0']['product_price']);
//            //预计开始日期
//            $orderInfo['lease_time']['estimated_start_date'] = date('Y-m-d', time() + (86400 * 2));
//            //预计结束日期
//            $orderInfo['lease_time']['expected_end_date'] = date('Y-m-d', time() + (86400 * ($lease_time['lease_time'] + 2)));
//        }
//--------------------
        // 订单结算提交
        if ($orderService->hasError()) {
            return $this->renderError($orderService->getError());
        }

        if ($this->request->isGet()) {

            // 如果来源是小程序, 则获取小程序订阅消息id.获取支付成功,发货通知.
            $template_arr = MessageModel::getMessageByNameArr($params['pay_source'], ['order_pay_user', 'order_delivery_user']);
            //是否显示店铺信息
            $store_open = SettingModel::getStoreOpen();
            // 是否开启支付宝支付
            $show_alipay = PayService::isAlipayOpen($params['pay_source'], '10001');
            $balance = isset($user['balance'])?$user['balance']:'';
            return $this->renderSuccess('', compact('orderInfo', 'template_arr', 'store_open', 'show_alipay', 'balance'));
        }
//dump(json_decode(json_encode($orderInfo, true), true));
        $orderInfo['orderData']['coupon'] = 0;
        if (!empty($params['coupon'])){
            $orderInfo['orderData']['coupon'] = $params['coupon'];
        }
        $orderInfo['orderData']['appid_id'] = $appid_id;

        // 创建订单
        $order_id = $orderService->createOrder($orderInfo);

        if (!$order_id) {
            return $this->renderError($orderService->getError() ?: '订单创建失败');
        }

        // 返回订单信息
        return $this->renderSuccess('', [
            'order_id' => $order_id,   // 订单号
        ]);
    }

    /**
     * 订单确认-立即购买
     */
    public function cart()
    {
        // 立即购买：获取订单商品列表
        if ($this->request->isGet()) {
            $params = json_decode($this->postData()['params'], true);
        } else {
            $params = json_decode($this->postData()['params'], true);
        }
        $post = $this->request->post();
        if (!empty($post['appid'])){
            $appid = $post['appid'];
            $data = ApplistModel::detail(['appid'=>$appid]);
            if (!$data){
                return $this->renderError('appid错误');
            }
            $appid_id = $data['id'];
        }else{
            $appid_id = 1;
        }
        $user = $this->getUsertest();
        // 商品结算信息
        $CartModel = new CartModel();
        // 购物车商品列表
        $supplierData = $CartModel->getList($user, $params['cart_ids']);
        // 实例化订单service
        $orderService = new MasterOrderSettledService($user, $supplierData, $params);
        // 获取订单信息
        $orderInfo = $orderService->settlement($params['address']);
        if ($this->request->isGet()) {
            // 如果来源是小程序, 则获取小程序订阅消息id.获取支付成功,发货通知.
            $template_arr = MessageModel::getMessageByNameArr($params['pay_source'], ['order_pay_user', 'order_delivery_user']);
            //是否显示店铺信息
            $store_open = SettingModel::getStoreOpen();
            // 是否开启支付宝支付
            $show_alipay = PayService::isAlipayOpen($params['pay_source'], $user['app_id']);
            $balance = $user['balance'];
            return $this->renderSuccess('', compact('orderInfo', 'template_arr', 'store_open', 'show_alipay', 'balance'));
        }
        // 订单结算提交
        if ($orderService->hasError()) {
            return $this->renderError($orderService->getError());
        }
        $orderInfo['orderData']['appid_id'] = $appid_id;
        // 创建订单
        $order_id = $orderService->createOrder($orderInfo);
        if (!$order_id) {
            return $this->renderError($orderService->getError() ?: '订单创建失败');
        }
        // 移出购物车中已下单的商品
        $CartModel->clearAll($user, $params['cart_ids']);
        // 返回订单信息
        return $this->renderSuccess('', [
            'order_id' => $order_id,   // 订单号
        ]);
    }

    /**
     * 订单支付
     */
    public function zpay()
    {
        $params = $this->postData();
        $user = $this->getUser();
        $payDetail = OrderModel::orderInfo($params['order_id'], $user);
        if ($this->request->isGet()) {
            // 开启的支付类型
            $payTypes = AppModel::getPayType($payDetail['app_id'], $params['pay_source']);
            // 支付金额
            $payPrice = $payDetail['payPrice'];
            $balance = $user['balance'];
            return $this->renderSuccess('', compact('payTypes', 'payPrice', 'balance'));
        }
        $payInfo = (new OrderModel)->OrderPay($user, $params);
        return $this->renderSuccess('', [
            'order_id' => $payInfo['order_id'],   // 订单id
            'pay_type' => $payInfo['payType'],  // 支付方式
            'payment' => $payInfo['payment'],  // 支付参数
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
//        print_r($payDetail);die;
        $payPrice = $payDetail['total_price'];

//        $payPrice = floor($payPrice * 100) / 100;
        if ($this->request->isGet()) {
            // 开启的支付类型
            $payTypes = AppModel::getPayType($payDetail['app_id'], $params['pay_source']);
            // 支付金额
            $balance = $user['balance'];
            return $this->renderSuccess('', compact('payTypes', 'payPrice', 'balance'));
        }
        $notifyUrl = base_url() . 'index.php/job/notify/orderPay_notify';
        $payInfo = Pay::pay($payDetail['order_no'], $payPrice, 'ces', $user['open_id'], $notifyUrl);
        if ($payInfo) {
            $data = $payInfo;
            $data->order_id[] = $payDetail['order_id'];
            return $this->renderSuccess('', $data);
        }
        return $this->renderError('系统繁忙,请稍后再试');
    }

    /**
     * 买断 2022-09-05 14:29 zxy
     */
    public function buyOut($order_id)
    {
        $user = $this->getUser();
        //获取订单信息
        $order = OrderModel::where('order_id', '=', $order_id)->find();
        if ($order['lease_type'] == 2) return $this->renderError('当前订单不可买断');

        //获取商品信息
        $orderProduct = OrderProduct::where('order_id', '=', $order_id)->find();
        $buy_out_price = ProductSku::where(['product_id' => $orderProduct['product_id'], 'spec_sku_id' => $orderProduct['spec_sku_id']])->value('buy_out_price');
        if (!$buy_out_price || $buy_out_price < 0) return $this->renderError('订单异常请联系客服');

        //获取已支付金额
        $isPayPrice = 0;//已支付金额

        $orderBill = OrderBill::where(['order_id' => $order_id, 'is_pay_status' => 1])->select();
        if ($orderBill) {
            foreach ($orderBill as $key => $value) {
                $isPayPrice += $value['price'];
            }
        }

        //需支付金额
        $PayPrice = $buy_out_price - $isPayPrice;
        $notifyUrl = base_url() . 'index.php/job/notify/buyOut_notify';
        $payInfo = Pay::pay($order['order_no'], $PayPrice, 'ces', $user['open_id'], $notifyUrl);
        if ($payInfo) {
            $data = $payInfo;
            $data->order_id[] = $order_id;
            return $this->renderSuccess('', $data);
        }
        return $this->renderError('系统繁忙,请稍后再试');
    }

    /**
     * 提前结束租赁
     */
    public function earlyEnd($order_id)
    {
        $user = $this->getUser();
        //获取订单信息
        $order = OrderModel::where('order_id', '=', $order_id)->find();
        if ($order['order_status']['value'] != 10) return $this->renderError('订单状态异常');
        $orderBillres = OrderBill::where('order_id', '=', $order_id)->where('is_pay_status', '=', 2)->find();
        if ($orderBillres) {
            return $this->renderError('您有逾期账单请先支付逾期账单');
        }
        $orderBill = OrderBill::where('order_id', '=', $order_id)->where('is_pay_status', '=', '1')->select();
        //已支付天数
        $dayNum = count($orderBill) * 30;
        //已租赁天数
        $time = ceil((time() - $order['lease_start_time']) / 864000);
        //每天金额
        $dayPrice = $orderBill['0']['price'] / 30;
        //有剩余时间
        if ($dayNum > $time) {
            $sprice = ($dayNum - $time) * $dayPrice;
        } else {
            $sprice = 0;
        }
        $Price = 0;
        if ($order['new_machine'] == 1) {
            $orderBill = OrderBill::where(['order_id' => $order_id])->select();
            $Price = $orderBill[(count($orderBill) - 1)]['price'];
        } elseif ($order['new_machine'] == 2) {
            $orderBill = OrderBill::where(['order_id' => $order_id, 'is_pay_status' => 0])->select();
            foreach ($orderBill as $k => $v) {
                $Price += $v['price'];
            }
//            $payPrice = floor((($Price / 100) * 30) * 100) / 100;
        }
        if ($sprice > $Price) {
            $payPrice = floor(($sprice - $Price) * 100) / 100;
            $res = User::where('user_id', '=', $user['user_id'])->inc('balance', $payPrice)->update();
            if ($res) {
                $res = OrderModel::where('order_id', '=', $order_id)->update(['order_status' => 32, 'lease_end_time' => time()]);
                if ($res) {
                    return $this->renderSuccess('已提前结束订单，退回金额请在余额查看', '', 124);
                }
            }
            return $this->renderError('系统繁忙,请稍后再试1');
        } else {
            $payPrice = floor(($Price - $sprice) * 100) / 100;
            $notifyUrl = base_url() . 'index.php/job/notify/earlyEnd_notify';

            $payInfo = Pay::pay($order['order_no'] . 'md' . rand(10000, 99999), $payPrice, 'ces', $user['open_id'], $notifyUrl);

            if ($payInfo) {
                return $this->renderSuccess('', $payInfo);
            }
            return $this->renderError('系统繁忙,请稍后再试2');
        }

//        Notify::earlyEnd_notify($order['order_no']);
    }

    /**
     * 人脸认证
     */
    public function faceAuthentication()
    {
        $user = $this->getUser();
        if (!$user['username'] || !$user['usernum']) {
            return $this->renderError('', '', 2000);
        }
        $no = date('Ymd') . substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
        $data = AlipayLoginLogic::faceAuthentication('MXZA' . $no . rand(10000, 99999) . $user['user_id'], $user['username'], $user['usernum'], 'pages/index/index');
        if ($data) {
            return $this->renderSuccess('', $data);
        }
        return $this->renderError('失败');
    }

    /**
     * 实名认证
     */
    public function sfaceAuthentication()
    {
        $username = $this->request->param('username');
        $username = $this->request->param('usernum');
        $user = $this->getUser();
        if (!$username || !$username) {
            return $this->renderError('', '', 2000);
        }
        $no = date('Ymd') . substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
        $data = AlipayLoginLogic::faceAuthentication('SMRZ' . $no . rand(10000, 99999) . $user['user_id'], $username, $username, 'pages/index/index');
        if ($data) {
            return $this->renderSuccess('', $data);
        }
        return $this->renderError('失败');
    }

    function img_base64($path)
    {
        //对path进行判断，如果是本地文件就二进制读取并base64编码，如果是url,则返回
        $img_data = "";
        if (substr($path, 0, strlen("http")) === "http") {
            $img_data = $path;
        } else {
            if ($fp = fopen($path, "rb", 0)) {
                $binary = fread($fp, filesize($path)); // 文件读取
                fclose($fp);
                $img_data = base64_encode($binary); // 转码
            } else {
                printf("%s 图片不存在", $img_path);
            }
        }
        return $img_data;
    }

    public function ce()
    {
        $url = "http://dm-51.data.aliyun.com/rest/160601/ocr/ocr_idcard.json";

        $appcode = "你的appcode";
        $img_path = "图片本地路径或者url";
        $method = "POST";

        $headers = array();
        array_push($headers, "Authorization:APPCODE " . $appcode);
        //根据API的要求，定义相对应的Content-Type
        array_push($headers, "Content-Type" . ":" . "application/json; charset=UTF-8");

        //如果没有configure字段，config设为空
        $config = array(
            "side" => "face"
        );

        $img_data = $this->img_base64($img_path);
        $request = array(
            "image" => "$img_data"
        );
        if (count($config) > 0) {
            $request["configure"] = json_encode($config);
        }
        $body = json_encode($request);

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_FAILONERROR, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, true);
        if (1 == strpos("$" . $url, "https://")) {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }
        curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
        $result = curl_exec($curl);
        $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $rheader = substr($result, 0, $header_size);
        $rbody = substr($result, $header_size);

        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if ($httpCode == 200) {
            $result_str = $rbody;
            printf("result is :\n %s\n", $result_str);
        } else {
            printf("Http error code: %d\n", $httpCode);
            printf("Error msg in body: %s\n", $rbody);
            printf("header: %s\n", $rheader);
        }
    }
}