<?php


namespace app\api\controller;


use app\api\model\order\Order;

class Timing extends Controller
{
    /**
     * 查询租赁到期订单设置归还状态
     */
    public function setReturn(){
        Order::where(['order_status'=>10])->where('lease_end_time','<=',time())->update(['order_status'=>11]);
    }
}