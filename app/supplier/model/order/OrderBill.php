<?php


namespace app\supplier\model\order;

use app\common\model\order\OrderBill as OrderBillModel;

class OrderBill extends OrderBillModel
{
    public static function getOrderBill($order_id){
        return self::where('order_id','=',$order_id)->select();
    }
}