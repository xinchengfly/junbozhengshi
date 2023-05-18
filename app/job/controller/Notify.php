<?php

namespace app\job\controller;


use app\api\model\order\Order;
use app\api\model\order\Order as OrderModel;
use app\api\model\order\OrderBill;
use app\api\model\user\User;
use app\common\library\alipay\AliPay;
use app\common\library\easywechat\WxPay;
use app\api\controller\order\OrderCenter;
use think\facade\Env;

/**
 * 微信支付回调
 */
class Notify
{

    /**
     * 支付宝支付回调
     * 2022-09-06 zxy
     */
    public function alipay_notify(){
        log_write('支付宝回调------------------------------------开始-----------------------------------------------');
        $params = $_POST;
        $order_sn= $params['out_trade_no'];
        if (!$order_sn) return false;
        $orderinfo = Order::where('order_no','=',$order_sn)->find();
        if($orderinfo){
            if ($orderinfo['pay_status']['value'] == 10){
                Order::where('order_no','=',$order_sn)->update(['order_status'=>10,'pay_status'=>20,'transaction_id'=>$params['trade_no'],'update_time'=>time(),'pay_time'=>time()]);
                $data = [
                    'app_id' => 10001,
                    'order_id' => $orderinfo['order_id'],
                    'states' => 4,
                ];
                curlPost('https://'.Env::get('url.url', 'yuzhou.haidiao888.com').'/index.php/api/order.order_center/index2', $data);
            }
        }
        log_write('支付宝回调------------------------------------成功-----------------------------------------------');
        echo 'success';
    }

    /**
     * 支付宝支付回调
     * 2022-09-06 zxy
     */
    public function orderBill_notify(){
        log_write('支付宝分期订单支付回调------------------------------------开始-----------------------------------------------');
        $params = $_POST;
        log_write($params);
        $order_sn= $params['out_trade_no'];
        if (!$order_sn) return false;
        $bill_id = explode('_',$order_sn);
        $order_no =$bill_id[0];
        unset($bill_id[0]);
        OrderBill::where('bill_id','in',$bill_id)->save(['is_pay_status'=>1,'transaction_id'=>$params['trade_no']]);
//        $order =  Order::where('order_no','=',$order_no)->find();
//        $billType = OrderBill::where('order_id','=',$order['order_id'])->where('is_pay_status','=',0)->find();
//        if (!$billType){
//            Order::where('order_no','=',$order_no)->update(['order_status'=>30]);
//        }
        log_write('支付宝分期订单支付回调------------------------------------成功-----------------------------------------------');
    }

    /**
     * 买断 2022-09-05 14:29 zxy
     */
    public function buyOut_notify(){
        log_write('支付宝买断订单支付回调------------------------------------开始-----------------------------------------------');
        $params = $_POST;
        $order_sn= $params['out_trade_no'];
        $order = OrderModel::where('order_no','=',$order_sn)->find();
        Order::where('order_no','=',$order_sn)->update(['order_status'=>31,'update_time'=>time()]);
        OrderBill::where('order_id','=',$order['order_id'])->save(['is_pay_status'=>1,'update_time'=>time()]);
        User::where('user_id','=',$order['user_id'])->inc('balance',$order['deposit'])->update();
        log_write('支付宝买断订单支付回调------------------------------------成功-----------------------------------------------');
    }

    /**
     * 提前结束租赁 2022-09-05 14:29 zxy
     */
    public static function earlyEnd_notify(){
        log_write('支付宝买断订单支付回调------------------------------------开始-----------------------------------------------');
        $params = $_POST;
        $order_sn= $params['out_trade_no'];
        log_write($params);
        $order = OrderModel::where('order_no','=',$order_sn)->find();
        Order::where('order_no','=',$order_sn)->update(['order_status'=>11,'update_time'=>time()]);
        OrderBill::where('order_id','=',$order['order_id'])->save(['is_pay_status'=>1,'update_time'=>time()]);
        log_write('支付宝买断订单支付回调------------------------------------成功-----------------------------------------------');

    }

    /**
     * 押金支付
     */
    public function orderPay_notify(){
        log_write('押金支付------------------------------------开始-----------------------------------------------');
        $params = $_POST;
        $order_sn= $params['out_trade_no'];
        $order = OrderModel::where('order_no','=',$order_sn)->find();
        Order::where('order_no','=',$order_sn)->update(['order_status'=>10,'pay_status'=>20,'transaction_id'=>$params['trade_no'],'deposit_pay_status'=>1,'update_time'=>time()]);
        log_write('押金支付------------------------------------成功-----------------------------------------------');
    }
    /**
     * 微信支付回调
     */
    public function wxpay()
    {
        // 微信支付组件：验证异步通知
        $WxPay = new WxPay(false);
        $WxPay->notify();
    }

    /**
     * 支付宝支付回调（同步）
     */
    public function alipay_return()
    {
        $AliPay = new AliPay($_POST['pay_source']);
        $url = $AliPay->return();
        if($url){
            return redirect($url);
        }
    }

    /**
     * 支付宝支付回调（异步） //弃用
     */
    public function balipay_notify()
    {
        $AliPay = new AliPay($_POST['pay_source']);
        $AliPay->notify();
    }

    /**
     * @return false
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * 模拟支付回调
     */
    public function cealipay_notify()
    {
       $order_sn= $_POST['order_sn'];
       if (!$order_sn) return false;
       $bill_id = explode('_',$order_sn);
       $order_no =$bill_id[0];
       unset($bill_id[0]);

       $OrderBill = OrderBill::where('bill_id','in',$bill_id)->select();

       if ($OrderBill){
           foreach ($OrderBill as $k=>$v){
               if ($v['current_period'] == 1){
                   Order::where('order_no','=',$order_no)->update(['order_status'=>10,'pay_status'=>20]);
               }
               if ($v['current_period'] == $v['Total_number_of_periods']){
                   Order::where('order_no','=',$order_no)->update(['order_status'=>30]);
               }
           }
       }
       OrderBill::where('bill_id','in',$bill_id)->save(['is_pay_status'=>1,'transaction_id'=>$order_sn]);
    }



}
