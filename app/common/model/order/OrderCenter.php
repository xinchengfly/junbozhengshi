<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2023/5/4
 * Time: 14:44
 */

namespace app\common\model\order;

use app\common\model\BaseModel;

class OrderCenter extends BaseModel
{

    public function getBizContent($order_id, $type = 1)
    {
        $orderData = Order::detail($order_id);
        dump($orderData);
        exit();
        if ($type == 1){
            $data = [
                'amount' => 10.55,
                'order_modified_time' => '2021-08-25 13:43:08',
                'order_create_time' => '2021-08-25 13:43:08',
                'pay_amount' => 10.55,
                'out_biz_no' => '2023050497975098',
                'buyer_id' => '2088922383783720',
                'order_type' => 'SERVICE_ORDER',
                'item_order_list' => [
                    [
                        'quantity' => 1,
                        'item_name' => '商品的名称',
                        'unit_price' => '10.55',
                        'ext_info' => [
                            'ext_key' => 'image_material_id',
                            'ext_value' => '2023042800502200000060161209',
                        ]
                    ]
                ],
                'ext_info' => [
                    [
                        'ext_key' => 'merchant_biz_type',
                        'ext_value' => 'KX_SHOPPING',
                    ],
                    [
                        'ext_key' => 'merchant_order_status',
                        'ext_value' => 'PAID',
                    ],
                    [
                        'ext_key' => 'merchant_order_link_page',
                        'ext_value' => '/pages/order/myorder',
                    ],
                    [
                        'ext_key' => 'tiny_app_id',
                        'ext_value' => '2021003183680107',
                    ],
                ]
            ];
        }elseif ($type ==2){

        }elseif ($type ==3){

        }elseif ($type ==4){

        }elseif ($type ==5){

        }elseif ($type ==6){

        }elseif ($type ==7){

        }elseif ($type ==8){

        }elseif ($type ==9){

        }elseif ($type ==10){

        }

        return $data;

    }
}