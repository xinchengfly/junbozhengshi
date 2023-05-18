<?php


namespace app\common\model\supplier;

use app\common\model\BaseModel;
use app\common\service\order\OrderService;

/**
 * 供应商申请模型
 */
class DepositOrder extends BaseModel
{
    protected $pk = 'order_id';
    protected $name = 'supplier_deposit_order';

    /**
     * 生成订单号
     */
    public function orderNo()
    {
        return OrderService::createOrderNo();
    }

    /**
     * 关联用户表
     */
    public function user()
    {
        return $this->belongsTo('app\\common\\model\\user\\User', 'user_id', 'user_id');
    }

    /**
     * 获取订单详情
     */
    public static function detail($where)
    {
        $model = (new static())->where($where)->find();
        return $model;
    }
}