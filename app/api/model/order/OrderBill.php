<?php


namespace app\api\model\order;
use app\common\model\order\OrderBill as OrderBillModel;

class OrderBill extends OrderBillModel
{
    protected $hidden = [
        'app_id',
        'update_time'
    ];
}