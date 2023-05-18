<?php

namespace app\api\service\order\paysuccess\source;

use app\common\enum\settings\DeliveryTypeEnum;

/**
 * 砍价订单支付成功后的回调
 */
class BargainPaySuccessService
{
    /**
     * 回调方法
     */
    public function onPaySuccess($order)
    {
        // 如果是虚拟商品，则标记为已完成，无需发货
        if($order['delivery_type']['value'] == DeliveryTypeEnum::NO_EXPRESS){
            $order->save([
                'delivery_status' => 20,
                'delivery_time' => time(),
                'receipt_status' => 20,
                'receipt_time' => time(),
                'order_status' => 30
            ]);
        }
        return true;
    }
}