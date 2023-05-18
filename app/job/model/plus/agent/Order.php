<?php

namespace app\job\model\plus\agent;

use app\common\model\plus\agent\Order as OrderModel;
use app\common\service\order\OrderService;

/**
 * 分销商订单模型
 */
class Order extends OrderModel
{
    /**
     * 获取未结算的分销订单
     */
    public function getUnSettledList()
    {
        $list = $this->where('is_invalid', '=', 0)
            ->where('is_settled', '=', 0)
            ->select();
        if ($list->isEmpty()) {
            return $list;
        }
        // 整理订单信息
        $with = ['product' => ['refund']];
        return OrderService::getOrderList($list, 'order_master', $with);
    }

    /**
     * 标记订单已失效(批量)
     */
    public function setInvalid($ids)
    {
        return $this->where('id', 'in', $ids)
            ->save(['is_invalid' => 1]);
    }

}