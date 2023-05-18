<?php

namespace app\api\model\order;

use app\api\controller\logic\Pay;
use app\api\model\product\Product as ProductModel;
use app\api\service\order\paysuccess\type\MasterPaySuccessService;
use app\api\service\order\PaymentService;
use app\api\model\settings\Setting as SettingModel;
use app\common\enum\order\OrderPayTypeEnum;
use app\common\enum\order\OrderSourceEnum;
use app\common\enum\order\OrderTypeEnum;
use app\common\enum\order\OrderPayStatusEnum;
use app\common\enum\order\OrderStatusEnum;
use app\common\exception\BaseException;
use app\common\model\applist\Applist;
use app\common\service\order\OrderCompleteService;
use app\common\enum\settings\DeliveryTypeEnum;
use app\common\library\helper;
use app\common\model\order\Order as OrderModel;
use app\api\service\order\checkpay\CheckPayFactory;
use app\common\service\product\factory\ProductFactory;
use app\common\model\plus\coupon\UserCoupon as UserCouponModel;
use app\common\model\order\OrderTrade as OrderTradeModel;
use think\facade\Db;
use think\facade\Env;

require '/www/wwwroot/'.Env::get('url.url', 'yuzhou.haidiao888.com').'/extend/aop/AopClient.php';
require '/www/wwwroot/'.Env::get('url.url', 'yuzhou.haidiao888.com').'/extend/aop/request/AlipayFundAuthOrderUnfreezeRequest.php';
require '/www/wwwroot/'.Env::get('url.url', 'yuzhou.haidiao888.com').'/extend/aop/request/AlipayTradePayRequest.php';
require '/www/wwwroot/'.Env::get('url.url', 'yuzhou.haidiao888.com').'/extend/aop/request/AlipayTradeRefundRequest.php';

/**
 * 普通订单模型
 */
class Order extends OrderModel
{
    /**
     * 隐藏字段
     * @var array
     */
    protected $hidden = [
        'update_time'
    ];

    /**
     * 订单支付事件
     */
    public function onPay($payType = OrderPayTypeEnum::WECHAT)
    {
        // 判断订单状态
        $checkPay = CheckPayFactory::getFactory($this['order_source']);

        if (!$checkPay->checkOrderStatus($this)) {
            $this->error = $checkPay->getError();
            return false;
        }

        // 余额支付
        if ($payType == OrderPayTypeEnum::BALANCE) {
            return $this->onPaymentByBalance($this['order_no']);
        }
        return true;
    }

    /**
     * 用户中心订单列表
     */
    public function getList($user_id, $type = 'all', $params)
    {
        // 筛选条件
        $filter = [];
        // 订单数据类型
        switch ($type) {
            case 'all'://全部
                break;
            case 'examine';//待审核
                $filter['pay_status'] = OrderPayStatusEnum::PENDING;
                $filter['order_status'] = 1;
                break;
            case 'payment';//代付款
                $filter['pay_status'] = OrderPayStatusEnum::PENDING;
                $filter['order_status'] = 1;
                break;
            case 'delivery';//代发货
                $filter['pay_status'] = OrderPayStatusEnum::SUCCESS;
                $filter['delivery_status'] = 10;
                $filter['order_status'] = 10;
                break;
            case 'received';//待收货
                $filter['pay_status'] = OrderPayStatusEnum::SUCCESS;
                $filter['delivery_status'] = 20;
                $filter['receipt_status'] = 10;
                $filter['order_status'] = 10;
                break;
            case 'haveInHand';//进行中
                $filter['pay_status'] = OrderPayStatusEnum::SUCCESS;
                $filter['delivery_status'] = 20;
                $filter['receipt_status'] = 20;
                $filter['order_status'] = 10;
                break;
            case 'returned';//待归还
                $filter['pay_status'] = OrderPayStatusEnum::SUCCESS;
                $filter['order_status'] = 11;
                break;
            case 'complete';//已完成
                $filter['pay_status'] = OrderPayStatusEnum::SUCCESS;
                $filter['delivery_status'] = 20;
                $filter['receipt_status'] = 20;
                $filter['order_status'] = 30;
                break;
            case 'cancel';//已取消
//                $filter['pay_status'] = OrderPayStatusEnum::SUCCESS;
                $filter['order_status'] = 20;
                break;
            case 'Returning';//归还中
                $filter['pay_status'] = OrderPayStatusEnum::SUCCESS;
                $filter['order_status'] = 12;
                break;
            case 'comment';//待评价
                $filter['is_comment'] = 0;
                $filter['order_status'] = 30;
                break;
        }
        $model = new self;
        if (isset($params['shop_supplier_id']) && $params['shop_supplier_id']) {
            $model = $model->where('shop_supplier_id', '=', $params['shop_supplier_id']);
        } else {
            // 用户查询
            $model = $model->where('user_id', '=', $user_id);
        }
        if (isset($filter['order_status']) && $filter['order_status'] == 30) {
            unset($filter['order_status']);
            return $model->with(['product.image', 'supplier'])
                ->where($filter)
                ->where('order_status', 'in', [30, 32, 31])
                ->where('is_delete', '=', 0)
                ->order(['create_time' => 'desc'])
                ->paginate($params);

        }
            return $model->with(['product.image', 'supplier'])
                ->where($filter)
                ->where('is_delete', '=', 0)
                ->order(['create_time' => 'desc'])
                ->paginate($params);

    }

    /**
     * 确认收货
     */
    public function receipt()
    {
        // 验证订单是否合法
        // 条件1: 订单必须已发货
        // 条件2: 订单必须未收货
        if ($this['delivery_status']['value'] != 20 || $this['receipt_status']['value'] != 10) {
            $this->error = '该订单不合法';
            return false;
        }
        $startTime = strtotime(date('Y-m-d',time()));
        return $this->transaction(function ()use ($startTime) {
            // 更新订单状态
            $status = $this->save([
                'receipt_status' => 20,
                'receipt_time' => time(),
                'order_status' => 30,
                'lease_start_time'=> $startTime,
                'lease_end_time' => $startTime+(86400*$this['lease_time'])

            ]);
            // 执行订单完成后的操作
            $OrderCompleteService = new OrderCompleteService(OrderTypeEnum::MASTER);
            $OrderCompleteService->complete([$this], static::$app_id);
            return $status;
        });
    }

    /**
     * 立即购买：获取订单商品列表
     */
    public static function getOrderProductListByNow($params,$lease_time='',$type='')
    {
        // 商品详情
        $product = ProductModel::detail($params['product_id']);

        // 商品sku信息
        $product['product_sku'] = ProductModel::getProductSku($product, $params['product_sku_id']);
        // 商品列表
        $productList = [$product->hidden(['category', 'content', 'image', 'sku'])];

        foreach ($productList as &$item) {

//             商品单价
//            if ($lease_time && $item['product_sku']['product_attr']){
//                $item['product_sku']['product_attr'] .= "时间:{$lease_time['lease_time']}天;";
//            }
//            if ($type==1){
//                $item['product_price'] =floor(((($item['product_sku']['product_price']/100)*$lease_time['val'])*100))/100;
//            }elseif ($type==2){
//                $item['product_price'] =floor(((($item['product_sku']['product_price']/100)*$lease_time['value'])*100))/100;
//            }else{
//                $item['product_price'] =floor(((($item['product_sku']['product_price']/100)*$lease_time['val'])*100))/100;
//            }

            // 商品购买数量
            $item['total_num'] = $params['product_num'];
            $item['spec_sku_id'] = $item['product_sku']['spec_sku_id'];
            $coupon = Db::name('product')->where('product_id', '=', $params['product_id'])->find()['coupon'];
            // 商品购买总金额
            if ($coupon == 1){
                $item['total_price'] = helper::bcmul($item['product_sku']['courier_fee'], $params['product_num']) + $item['product_sku']['low_price'];
            }else{
                $item['total_price'] = helper::bcmul($item['product_sku']['product_price'], $params['product_num']);
            }
        }
        $supplierData[] = [
            'shop_supplier_id' => $product['shop_supplier_id'],
            'supplier' => $product['supplier'],
            'productList' => $productList
        ];
        unset($product['supplier']);
        return $supplierData;
    }

    /**
     * 获取订单总数
     */
    public function getCount($user, $type = 'all')
    {
        if ($user === false) {
            return false;
        }
        // 筛选条件
        $filter = [];
        // 订单数据类型
        switch ($type) {
            case 'all':
                break;
            case 'payment';
                $filter['pay_status'] = OrderPayStatusEnum::PENDING;
                break;
            case 'delivery';
                $filter['pay_status'] = OrderPayStatusEnum::SUCCESS;
                $filter['delivery_status'] = 10;
                $filter['order_status'] = 10;
                break;
            case 'received';
                $filter['pay_status'] = OrderPayStatusEnum::SUCCESS;
                $filter['delivery_status'] = 20;
                $filter['receipt_status'] = 10;
                break;
            case 'comment';
                $filter['order_status'] = 30;
                $filter['is_comment'] = 0;
                break;
        }
        return $this->where('user_id', '=', $user['user_id'])
            ->where('order_status', '<>', 20)
            ->where($filter)
            ->where('is_delete', '=', 0)
            ->count();
    }

    /**
     * 取消订单
     */
    public function cancel($user)
    {
        if ($this['delivery_status']['value'] == 20) {
            $this->error = '已发货订单不可取消';
            return false;
        }
        //进行中的拼团订单不能取消
        if ($this['order_source'] == OrderSourceEnum::ASSEMBLE) {
            if ($this['assemble_status'] == 10) {
                $this->error = '订单正在拼团，到期后如果订单未拼团成功将自动退款';
                return false;
            }
        }
        // 订单取消事件
        return $this->transaction(function () use ($user) {
            // 订单是否已支付
            $isPay = $this['pay_status']['value'] == OrderPayStatusEnum::SUCCESS;

//            $result = $this->refund($this['order_id']);
            // 未付款的订单
            if ($isPay == false) {
                //主商品退回库存
                ProductFactory::getFactory($this['order_source'])->backProductStock($this['product'], $isPay);
                // 回退用户优惠券
                $this['coupon_id'] > 0 && UserCouponModel::setIsUse($this['coupon_id'], false);
                $this['coupon_id_sys'] > 0 && UserCouponModel::setIsUse($this['coupon_id_sys'], false);
                // 回退用户积分
                $describe = "订单取消：{$this['order_no']}";
                $this['points_num'] > 0 && $user->setIncPoints($this['points_num'], $describe);
            }
            // 更新订单状态
            return $this->save(['order_status' => $isPay ? OrderStatusEnum::APPLY_CANCEL : OrderStatusEnum::CANCELLED]);
        });
    }

    public function refund($order_id){
        $orderModel = new OrderModel();
        $orderinfo = $orderModel->where('order_id','=',$order_id)->find();
        if ($orderinfo['transaction_id']){
            //如果押金转租金了也得退回去
            $aop = new \aop\AopClient();
            $aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
            $aop->appId = Db::name('config')->where('type','=','ali_mnp')->where('name','=','ali_app_id')->value('value');
            $aop->rsaPrivateKey = Db::name('config')->where('type','=','ali_mnp')->where('name','=','ali_rsa_privatekey')->value('value');
            $aop->alipayrsaPublicKey = Db::name('config')->where('type','=','ali_mnp')->where('name','=','ali_alipayrsa_publickey')->value('value');
            $aop->apiVersion = '1.0';
            $aop->signType = 'RSA2';
            $aop->postCharset='GBK';
            $aop->format='json';
            $object = new \stdClass();
            $object->trade_no = $orderinfo['transaction_id'];
            $object->refund_amount = $orderinfo['total_price'];
            $object->out_request_no = $orderinfo['order_no'];
            $json = json_encode($object);
            $request = new \AlipayTradeRefundRequest();
            $request->setBizContent($json);

            $result = $aop->execute ( $request);

            $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
            $resultCode = $result->$responseNode->code;
            if(!empty($resultCode)&&$resultCode == 10000){

            } else {
                return false;
            }
        }
        return true;
    }

    /**
     * 订单详情
     */
    public static function getUserOrderDetail($order_id, $user_id)
    {
        $model = new static();
        $order = $model->where(['order_id' => $order_id, 'user_id' => $user_id])->with(['product' => ['image', 'refund'], 'address', 'express', 'extractStore', 'supplier'])->find();
        if (empty($order)) {
            throw new BaseException(['msg' => '订单不存在']);
        }
        return $order;
    }

    /**
     * 供应商查看订单详情
     */
    public static function getSupplierOrderDetail($order_id, $shop_supplier_id)
    {
        $model = new static();
        $order = $model->where(['order_id' => $order_id, 'shop_supplier_id' => $shop_supplier_id])->with(['product' => ['image', 'refund'], 'address', 'express', 'extractStore', 'supplier'])->find();
        if (empty($order)) {
            throw new BaseException(['msg' => '订单不存在']);
        }
        return $order;
    }

    /**
     * 余额支付标记订单已支付
     */
    public function onPaymentByBalance($orderNo, $data)
    {
        // 获取订单详情
        $PaySuccess = new MasterPaySuccessService($orderNo, $data);
        // 发起余额支付
        $status = $PaySuccess->onPaySuccess(OrderPayTypeEnum::BALANCE);
        if (!$status) {
            $this->error = $PaySuccess->getError();
        }
        return $status;
    }

    /**
     * 构建微信支付请求
     */
    protected static function onPaymentByWechat($user, $order_no, $pay_source, $online_money, $multiple)
    {
        return PaymentService::wechat(
            $user,
            $order_no,
            OrderTypeEnum::MASTER,
            $pay_source,
            $online_money,
            $multiple
        );
    }

    /**
     * 构建支付宝请求
     */
    protected static function onPaymentByAlipay($user, $order_no, $pay_source, $online_money, $multiple)
    {
        return PaymentService::alipay(
            $user,
            $order_no,
            OrderTypeEnum::MASTER,
            $pay_source,
            $online_money,
            $multiple
        );
    }

    /**
     * 待支付订单详情
     */
    public static function getPayDetail($orderNo)
    {
        $model = new static();
        return $model->where(['order_no' => $orderNo, 'pay_status' => 10, 'is_delete' => 0])->with(['product', 'user'])->find();
    }

    /**
     * 构建支付请求的参数
     */
    public static function onOrderPayment($user, $order_no, $payType, $pay_source, $online_money, $multiple)
    {
        //如果来源是h5,首次不处理，payH5再处理
        if ($pay_source == 'h5') {
            return [];
        }
        if ($payType == OrderPayTypeEnum::WECHAT) {
            return self::onPaymentByWechat($user, $order_no, $pay_source, $online_money, $multiple);
        }
        if ($payType == OrderPayTypeEnum::ALIPAY) {
            return self::onPaymentByAlipay($user, $order_no, $pay_source, $online_money, $multiple);
        }
        return [];
    }

    /**
     * 判断当前订单是否允许核销
     */
    public function checkExtractOrder(&$order)
    {
        if (
            $order['pay_status']['value'] == OrderPayStatusEnum::SUCCESS
            && $order['delivery_type']['value'] == DeliveryTypeEnum::EXTRACT
            && $order['delivery_status']['value'] == 10
        ) {
            return true;
        }
        $this->setError('该订单不能被核销');
        return false;
    }

    /**
     * 当前订单是否允许申请售后
     */
    public function isAllowRefund()
    {
        // 必须是已发货的订单
        if ($this['delivery_status']['value'] != 20) {
            return false;
        }
        // 允许申请售后期限(天)
        $refundDays = SettingModel::getItem('trade')['order']['refund_days'];
        // 不允许售后
        if ($refundDays == 0) {
            return false;
        }
        // 当前时间超出允许申请售后期限
        if (
            $this['receipt_status'] == 20
            && time() > ($this['receipt_time'] + ((int)$refundDays * 86400))
        ) {
            return false;
        }
        return true;
    }

    /**
     * 获取活动订单
     * 已付款，未取消
     */
    public static function getPlusOrderNum($user_id, $product_id, $order_source)
    {
        $model = new static();
        return $model->alias('order')->where('order.user_id', '=', $user_id)
            ->join('order_product', 'order_product.order_id = order.order_id', 'left')
            ->where('order_product.product_source_id', '=', $product_id)
            ->where('order.pay_status', '=', 20)
            ->where('order.order_source', '=', $order_source)
            ->where('order.order_status', '<>', 20)
            ->sum('total_num');
    }

    /**
     * 累计成交笔数
     */
    public static function getTotalPayOrder($shop_supplier_id)
    {
        //累积成交笔数
        return (new static())->where('shop_supplier_id', '=', $shop_supplier_id)
            ->where('pay_status', '=', 20)
            ->where('order_status', 'in', [10, 30])
            ->count();
    }

    public static function getTodayPayOrder($shop_supplier_id)
    {
        //开始
        $beginToday = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
        //结束
        $endToday = mktime(0, 0, 0, date('m'), date('d') + 1, date('Y')) - 1;
        //今日成交笔数
        return (new static())->where('shop_supplier_id', '=', $shop_supplier_id)
            ->where('pay_status', '=', 20)
            ->where('order_status', 'in', [10, 30])
            ->whereBetweenTime('create_time', $beginToday, $endToday)
            ->count();
    }

    /**
     * 设置错误信息
     */
    protected function setError($error)
    {
        empty($this->error) && $this->error = $error;
    }

    /**
     * 是否存在错误
     */
    public function hasError()
    {
        return !empty($this->error);
    }

    /**
     * 获取直播订单
     */
    public function getLiveOrder($params)
    {
        $model = $this;
        if (isset($params['room_id']) && $params['room_id']) {
            $model = $model->where('room_id', '=', $params['room_id']);
        }
        if (isset($params['pay_status']) && $params['pay_status']) {
            $model = $model->where('pay_status', '=', $params['pay_status']);
        }
        return $model->with(['product.image'])
            ->where('shop_supplier_id', '=', $params['shop_supplier_id'])
            ->where('room_id', '>', 0)
            ->where('is_delete', '=', 0)
            ->order(['create_time' => 'desc'])
            ->paginate($params);
    }

    /**
     * 主订单购买的数量
     * 未取消的订单
     */
    public static function getHasBuyOrderNum($user_id, $product_id)
    {
        $model = new static();
        return $model->alias('order')->where('order.user_id', '=', $user_id)
            ->join('order_product', 'order_product.order_id = order.order_id', 'left')
            ->where('order_product.product_id', '=', $product_id)
            ->where('order.order_source', '=', OrderSourceEnum::MASTER)
            ->where('order.order_status', '<>', 21)
            ->sum('total_num');
    }

    /**
     * 获取订单信息
     */
    public static function orderInfo($order_id, $user)
    {
        $orderID = explode(',', $order_id);
        $multiple = 0;
        if (count($orderID) > 0) {
//            $payPrice = OrderModel::where('order_id', 'in', $orderID)
//                ->where('user_id', '=', $user['user_id'])
//                ->where('pay_status', '=', 10)
//                ->sum('pay_price');
            $payDetail = OrderModel::where('order_id', 'in', $orderID)
                ->where('user_id', '=', $user['user_id'])
                ->where('pay_status', '=', 10)
                ->select();
            $multiple = 1;
            $deposit =$payDetail[0]['deposit'];
        } else {
            $payDetail = OrderModel::where('order_id', '=', $orderID)
                ->where('pay_status', '=', 10)
                ->find();
//            $payPrice = $payDetail['pay_price'];
            $deposit =$payDetail['deposit'];
        }
        $appid = Applist::detail($payDetail[0]['applet_id']);
        $payInfo['order_id'] = $orderID;
        $payInfo['total_price'] = $payDetail[0]['total_price'];
        $payInfo['multiple'] = $multiple;
        $payInfo['app_id'] = $appid['appid'];
        $payInfo['deposit'] = $deposit;
        $payInfo['order_no'] = $payDetail[0]['order_no'];
        return $payInfo;
    }

//    /**
//     * 创建订单支付信息
//     */
//    public function OrderPay($user, $params,$payPrice='',$orderBill = '')
//    {
//        $payType = $params['payType'];
//        $payment = '';
//        $online_money = 0;
//        $orderID = explode(',', $params['order_id']);
//        $order_no = $this->orderNo();
//        $multiple = 0;
//        $orderInfo = "";
//        if (count($orderID) > 1) {
//            return false;
////            if (!$payPrice){
////                $payPrice = OrderModel::where('order_id', 'in', $orderID)
////                    ->where('user_id', '=', $user['user_id'])
////                    ->where('pay_status', '=', 10)
////                    ->sum('pay_price');
////            }
////            OrderTradeModel::where('order_id', 'in', $orderID)
////                ->where('pay_status', '=', 10)
////                ->update(['balance' => 0, 'online_money' => 0, 'out_trade_no' => $order_no]);
////            $multiple = 1;
//        } else {
//            $orderInfo = OrderModel::where('order_id', 'in', $orderID)
//                ->where('pay_status', '=', 10)
//                ->find();
////            $order_no = $orderInfo['order_no'];
////            if ($orderBill){
////                foreach ($orderBill as $k=>$v){
////                    $order_no .='_'.$v['bill_id'];
////                }
////            }
//            $orderInfo->save(['balance' => 0, 'online_money' => 0, 'order_no' => $order_no]);
//            if (!$payPrice){
//                $payPrice = $orderInfo['pay_price'];
//            }
//
//        }
//        // 余额支付标记余额抵扣
//        if ($params['use_balance'] == 1) {
//            if ($user['balance'] >= $payPrice) {
//                $payType = 10;
//                if ($multiple == 1) {
//                    OrderTradeModel::where('order_id', 'in', $orderID)
//                        ->where('pay_status', '=', 10)
//                        ->update(['balance' => $payPrice]);
//                } else {
//                    $orderInfo->save(['balance' => $payPrice]);
//                }
//                $data['multiple'] = $multiple;
//                $this->onPaymentByBalance($order_no, $data);
//            } else {
//                $online_money = round($payPrice - $user['balance'], 2);
//                if ($multiple == 1) {
//                    OrderTradeModel::where('order_id', 'in', $orderID)
//                        ->where('pay_status', '=', 10)
//                        ->update(['balance' => $user['balance'], 'online_money' => $online_money]);
//                } else {
//                    $orderInfo->save(['balance' => $user['balance'], 'online_money' => $online_money]);
//                }
//            }
//        } else {
//            $online_money = $payPrice;
//            if ($multiple == 1) {
//                OrderTradeModel::where('order_id', 'in', $orderID)
//                    ->where('pay_status', '=', 10)
//                    ->update(['online_money' => $online_money]);
//            } else {
//                $orderInfo->save(['online_money' => $online_money]);
//            }
//        }
//
//        $online_money > 0 && $payment = self::onOrderPayment($user, $order_no, $payType, $params['pay_source'], $online_money, $multiple);
//
//        $result['order_id'] = $orderID;
//        $result['payType'] = $payType;
//        $result['payment'] = $payment;
//        return $result;
//    }

    /**
     * 创建订单支付信息
     */
    public function cOrderPay($user, $params)
    {
        $payType = $params['payType'];
        $payment = '';
        $online_money = 0;
        $orderID = explode(',', $params['order_id']);
        $order_no = $this->orderNo();
        $multiple = 0;
        $orderInfo = "";
        if (count($orderID) > 1) {
            $payPrice = OrderModel::where('order_id', 'in', $orderID)
                ->where('user_id', '=', $user['user_id'])
                ->where('pay_status', '=', 10)
                ->sum('pay_price');
            OrderTradeModel::where('order_id', 'in', $orderID)
                ->where('pay_status', '=', 10)
                ->update(['balance' => 0, 'online_money' => 0, 'out_trade_no' => $order_no]);
            $multiple = 1;
        } else {
            $orderInfo = OrderModel::where('order_id', 'in', $orderID)
                ->where('pay_status', '=', 10)
                ->find();
            $orderInfo->save(['balance' => 0, 'online_money' => 0, 'order_no' => $order_no]);
            $payPrice = $orderInfo['pay_price'];
        }
        // 余额支付标记余额抵扣
        if ($params['use_balance'] == 1) {
            if ($user['balance'] >= $payPrice) {
                $payType = 10;
                if ($multiple == 1) {
                    OrderTradeModel::where('order_id', 'in', $orderID)
                        ->where('pay_status', '=', 10)
                        ->update(['balance' => $payPrice]);
                } else {
                    $orderInfo->save(['balance' => $payPrice]);
                }
                $data['multiple'] = $multiple;
                $this->onPaymentByBalance($order_no, $data);
            } else {
                $online_money = round($payPrice - $user['balance'], 2);
                if ($multiple == 1) {
                    OrderTradeModel::where('order_id', 'in', $orderID)
                        ->where('pay_status', '=', 10)
                        ->update(['balance' => $user['balance'], 'online_money' => $online_money]);
                } else {
                    $orderInfo->save(['balance' => $user['balance'], 'online_money' => $online_money]);
                }
            }
        } else {
            $online_money = $payPrice;
            if ($multiple == 1) {
                OrderTradeModel::where('order_id', 'in', $orderID)
                    ->where('pay_status', '=', 10)
                    ->update(['online_money' => $online_money]);
            } else {
                $orderInfo->save(['online_money' => $online_money]);
            }
        }
        $online_money > 0 && $payment = self::onOrderPayment($user, $order_no, $payType, $params['pay_source'], $online_money, $multiple);

        $result['order_id'] = $orderID;
        $result['payType'] = $payType;
        $result['payment'] = $payment;
        return $result;
    }



    public function OrderPay($user, $params,$payPrice='',$orderBill = '',$notifyUrl='')
    {
        $payType = $params['payType'];
        $payment = '';
        $online_money = 0;
        $orderID = explode(',', $params['order_id']);
//        $order_no = $this->orderNo();
        $multiple = 0;
        $orderInfo = "";
        if (count($orderID) > 1) {
            return false;
//            if (!$payPrice){
//                $payPrice = OrderModel::where('order_id', 'in', $orderID)
//                    ->where('user_id', '=', $user['user_id'])
//                    ->where('pay_status', '=', 10)
//                    ->sum('pay_price');
//            }
//            OrderTradeModel::where('order_id', 'in', $orderID)
//                ->where('pay_status', '=', 10)
//                ->update(['balance' => 0, 'online_money' => 0, 'out_trade_no' => $order_no]);
//            $multiple = 1;
        } else {
            $orderInfo = OrderModel::where('order_id', 'in', $orderID)
                ->where('pay_status', '=', 10)
                ->find();
            $order_no = $orderInfo['order_no'];
            $orderInfo->save(['balance' => 0, 'online_money' => 0, 'order_no' => $order_no]);
           if ($orderBill){
                foreach ($orderBill as $k=>$v){
                    $order_no .='_'.$v['bill_id'];
                }
            }
            if (!$payPrice){
                $payPrice = $orderInfo['pay_price'];
            }

        }
        // 余额支付标记余额抵扣
        if ($params['use_balance'] == 1) {
            if ($user['balance'] >= $payPrice) {
                $payType = 10;
                if ($multiple == 1) {
                    OrderTradeModel::where('order_id', 'in', $orderID)
                        ->where('pay_status', '=', 10)
                        ->update(['balance' => $payPrice]);
                } else {
                    $orderInfo->save(['balance' => $payPrice]);
                }
                $data['multiple'] = $multiple;
                $this->onPaymentByBalance($order_no, $data);
            } else {
                $online_money = round($payPrice - $user['balance'], 2);
                if ($multiple == 1) {
                    OrderTradeModel::where('order_id', 'in', $orderID)
                        ->where('pay_status', '=', 10)
                        ->update(['balance' => $user['balance'], 'online_money' => $online_money]);
                } else {
                    $orderInfo->save(['balance' => $user['balance'], 'online_money' => $online_money]);
                }
            }
        } else {
            $online_money = $payPrice;
            if ($multiple == 1) {
                OrderTradeModel::where('order_id', 'in', $orderID)
                    ->where('pay_status', '=', 10)
                    ->update(['online_money' => $online_money]);
            } else {
                $orderInfo->save(['online_money' => $online_money]);
            }
        }
        $payInfo = Pay::pay($order_no,$online_money,'ces',$user['open_id'],$notifyUrl);
//        $online_money > 0 && $payment = self::onOrderPayment($user, $order_no, $payType, $params['pay_source'], $online_money, $multiple);
        if($payInfo){
            $payInfo->order_id = $orderID;
        }

        return  $payInfo;
//        $result['order_id'] = $orderID;
//        $result['payType'] = $payType;
//        $result['payment'] = $payment;
//        return $result;
    }

}