<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2023/5/15
 * Time: 16:35
 */

namespace app\job\controller;

use app\shop\model\order\Order as OrderModel;
use app\api\model\order\Order as OrderApiModel;
use think\facade\Env;

class ScheduledTasks
{
    //发货七天自动确认收货
    public function confirm_receipt()
    {
        // 订单列表
        $model = new OrderModel();
        $data = [];
        $data['delivery_time'] = time()-604800;
        $list = $model->getList('received', $data);
        $list = json_decode(json_encode($list, true), true);
        foreach ($list['data'] as $key => $value) {
            $this->receive($value['order_id'], $value['user']['user_id']);
//            dump($value['order_id']);
        }
    }

    /**
     * 已发货订单自动确认收货
     */
    private function receive($order_id, $user_id)
    {
        $model = OrderApiModel::getUserOrderDetail($order_id, $user_id);
        if ($model->receipt()) {
            $data = [
                'app_id' => 10001,
                'order_id' => $order_id,
                'states' => 8,
            ];
            curlPost('https://'.Env::get('url.url', 'yuzhou.haidiao888.com').'/index.php/api/order.order_center/index2', $data);
        }
    }
}
