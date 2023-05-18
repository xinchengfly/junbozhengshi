<?php


namespace app\common\model\order;


use app\common\model\BaseModel;

class OrderBill extends BaseModel
{
    protected $name = 'order_bill';
    protected $pk = 'bill_id';
    protected $updateTime = false;
}