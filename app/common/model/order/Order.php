<?php

namespace app\common\model\order;

use app\common\enum\order\OrderSourceEnum;
use app\common\model\applist\Applist;
use app\common\model\BaseModel;
use app\common\enum\settings\DeliveryTypeEnum;
use app\common\enum\order\OrderPayStatusEnum;
use app\common\enum\order\OrderTypeEnum;
use app\common\enum\order\OrderPayTypeEnum;
use app\common\library\helper;
use app\common\service\order\OrderService;
use app\common\service\order\OrderCompleteService;
use app\common\model\store\Order as StoreOrderModel;
use app\common\service\order\OrderRefundService;
use app\common\service\product\factory\ProductFactory;
use app\common\model\plus\coupon\UserCoupon as UserCouponModel;
use app\common\model\user\User as UserModel;
use app\common\service\message\MessageService;
/**
 * 订单模型模型
 */
class Order extends BaseModel
{
    protected $pk = 'order_id';
    protected $name = 'order';

    /**
     * 追加字段
     * @var string[]
     */
    protected $append = [
        'state_text',
        'order_source_text',
    ];

    /**
     * 订单商品列表
     */
    public function product()
    {
        return $this->hasMany('app\\common\\model\\order\\OrderProduct', 'order_id', 'order_id')->hidden(['content']);
    }

    /**
     * 订单商品列表
     */
    public function applist()
    {
        return $this->belongsTo('app\\common\\model\\applist\\Applist', 'applet_id', 'id');
    }


    /**
     * 关联订单收货地址表
     */
    public function address()
    {
        return $this->hasOne('app\\common\\model\\order\\OrderAddress');
    }

    /**
     * 关联自提订单联系方式
     */
    public function extract()
    {
        return $this->hasOne('app\\common\\model\\order\\OrderExtract');
    }

    /**
     * 关联物流公司表
     */
    public function express()
    {
        return $this->belongsTo('app\\common\\model\\settings\\Express', 'express_id', 'express_id');
    }

    /**
     * 关联自提门店表
     */
    public function extractStore()
    {
        return $this->belongsTo('app\\common\\model\\store\\Store', 'extract_store_id', 'store_id');
    }

    /**
     * 关联门店店员表
     */
    public function extractClerk()
    {
        return $this->belongsTo('app\\common\\model\\store\\Clerk', 'extract_clerk_id');
    }

    /**
     * 关联用户表
     */
    public function user()
    {
        return $this->belongsTo('app\\common\\model\\user\\User', 'user_id', 'user_id');
    }

    /**
     * 关联用户表
     */
    public function room()
    {
        return $this->belongsTo('app\\common\\model\\plus\\live\\Room', 'room_id', 'room_id');
    }

    /**
     * 关联供应商表
     */
    public function supplier()
    {
        return $this->belongsTo('app\\common\\model\\supplier\\Supplier', 'shop_supplier_id', 'shop_supplier_id')->field(['shop_supplier_id', 'name', 'user_id']);
    }

    /**
     * 订单状态文字描述
     * @param $value
     * @param $data
     * @return string
     */
    public function getStateTextAttr($value, $data)
    {
        // 订单状态
        if (in_array($data['order_status'], [11, 12, 20, 30,1,31,32])) {
            $orderStatus = [11=>'待归还',12=>'归还中', 20 => '已取消', 30 => '已完成',1=>'未付款',31=>'已完成，已买断',32=>'已完成'];
            return $orderStatus[$data['order_status']];
        }
        // 付款状态
        if ($data['pay_status'] == 10 && $data['deposit_pay_status'] == 0) {
            return '待支付押金';
        }else if ($data['pay_status'] == 10){
            return '待支付';
        }
        // 拼团状态
        if ($data['order_source'] == OrderSourceEnum::ASSEMBLE) {
            $assemble_status = $this->getAssembleStatus($data);
            if ($assemble_status != '') {
                return $assemble_status;
            }
        }
        // 发货状态
        if ($data['delivery_status'] == 10) {
            return '已付款，待发货';
        }
        if ($data['receipt_status'] == 10) {
            return '已发货，待收货';
        }
        if ($data['delivery_status'] == 20 && $data['order_status'] == 10) {
            return '已收货,进行中';
        }
        return $value;
    }

    /**
     *  拼团订单状态
     */
    private function getAssembleStatus($data)
    {
        // 发货状态
        if ($data['assemble_status'] == 10) {
            return '已付款，未成团';
        }
        if ($data['assemble_status'] == 20 && $data['delivery_status'] == 10) {
            return '拼团成功，待发货';
        }
        if ($data['assemble_status'] == 30) {
            return '拼团失败';
        }
        return '';
    }

    /**
     * 付款状态
     * @param $value
     * @return array
     */
    public function getPayTypeAttr($value)
    {
        return ['text' => OrderPayTypeEnum::data()[$value]['name'], 'value' => $value];
    }

    /**
     * 订单来源
     * @param $value
     * @return array
     */
    public function getOrderSourceTextAttr($value, $data)
    {
        return OrderSourceEnum::data()[$data['order_source']]['name'];
    }

    /**
     * 付款状态
     * @param $value
     * @return array
     */
    public function getPayStatusAttr($value)
    {
        return ['text' => OrderPayStatusEnum::data()[$value]['name'], 'value' => $value];
    }

    /**
     * 改价金额（差价）
     * @param $value
     * @return array
     */
    public function getUpdatePriceAttr($value)
    {
        return [
            'symbol' => $value < 0 ? '-' : '+',
            'value' => sprintf('%.2f', abs($value))
        ];
    }

    /**
     * 发货状态
     * @param $value
     * @return array
     */
    public function getDeliveryStatusAttr($value)
    {
        $status = [10 => '待发货', 20 => '已发货'];
        return ['text' => $status[$value], 'value' => $value];
    }

    /**
     * 收货状态
     * @param $value
     * @return array
     */
    public function getReceiptStatusAttr($value)
    {
        $status = [10 => '待收货', 20 => '已收货'];
        return ['text' => $status[$value], 'value' => $value];
    }

    /**
     * 收货状态
     * @param $value
     * @return array
     */
    public function getOrderStatusAttr($value)
    {
        $status = [10 => '进行中',11=>'待归还',12=>'已归还', 20 => '已取消', 21 => '待取消', 30 => '已完成',1=>'待审核',2=>'审核通过代付款',31=>'订单已经买断',32=>'提前结束订单'];
        return ['text' => $status[$value], 'value' => $value];
    }

    /**
     * 配送方式
     * @param $value
     * @return array
     */
    public function getDeliveryTypeAttr($value)
    {
        return ['text' => DeliveryTypeEnum::data()[$value]['name'], 'value' => $value];
    }

    /**
     * 订单详情
     * @param $where
     * @param string[] $with
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function detail($where, $with = ['user', 'address', 'product' => ['image', 'refund'], 'extract', 'express', 'extractStore.logo', 'extractClerk', 'supplier'])
    {
        is_array($where) ? $filter = $where : $filter['order_id'] = (int)$where;
        return (new static())->with($with)->where($filter)->find();
    }

    /**
     * 订单详情
     */
    public static function detailByNo($order_no, $with = ['user', 'address', 'product' => ['image', 'refund'], 'extract', 'express', 'extractStore.logo', 'extractClerk', 'supplier'])
    {
        return (new static())->with($with)->where('order_no', '=', $order_no)->find();
    }

    /**
     * 批量获取订单列表
     */
    public function getListByIds($orderIds, $with = [])
    {
        $data = $this->getListByInArray('order_id', $orderIds, $with);
        return helper::arrayColumn2Key($data, 'order_id');
    }

    /**
     * 批量更新订单
     */
    public function onBatchUpdate($orderIds, $data)
    {
        return $this->where('order_id', 'in', $orderIds)->save($data);
    }

    /**
     * 批量获取订单列表
     */
    private function getListByInArray($field, $data, $with = [])
    {
        return $this->with($with)
            ->where($field, 'in', $data)
            ->where('is_delete', '=', 0)
            ->select();
    }

    /**
     * 生成订单号
     */
    public function orderNo()
    {
        return OrderService::createOrderNo();
    }

    /**
     * 确认核销（自提订单）
     */
    public function verificationOrder($extractClerkId)
    {
        if (
            $this['pay_status']['value'] != 20
            || $this['delivery_type']['value'] != DeliveryTypeEnum::EXTRACT
            || $this['delivery_status']['value'] == 20
            || in_array($this['order_status']['value'], [20, 21])
        ) {
            $this->error = '该订单不满足核销条件';
            return false;
        }
        return $this->transaction(function () use ($extractClerkId) {
            // 更新订单状态：已发货、已收货
            $status = $this->save([
                'extract_clerk_id' => $extractClerkId,  // 核销员
                'delivery_status' => 20,
                'delivery_time' => time(),
                'receipt_status' => 20,
                'receipt_time' => time(),
                'order_status' => 30
            ]);
            // 新增订单核销记录
            StoreOrderModel::add(
                $this['order_id'],
                $this['extract_store_id'],
                $this['extract_clerk_id'],
                $this['shop_supplier_id'],
                OrderTypeEnum::MASTER
            );
            // 执行订单完成后的操作
            $OrderCompleteService = new OrderCompleteService(OrderTypeEnum::MASTER);
            $OrderCompleteService->complete([$this], static::$app_id);
            return $status;
        });
    }


    /**
     * 获取已付款订单总数 (可指定某天)
     */
    public function getOrderData($startDate = null, $endDate = null, $type, $shop_supplier_id = 0)
    {
        $model = $this;

        !is_null($startDate) && $model = $model->where('pay_time', '>=', strtotime($startDate));

        if (is_null($endDate)) {
            !is_null($startDate) && $model = $model->where('pay_time', '<', strtotime($startDate) + 86400);
        } else {
            $model = $model->where('pay_time', '<', strtotime($endDate) + 86400);
        }

        if ($shop_supplier_id > 0) {
            $model = $model->where('shop_supplier_id', '=', $shop_supplier_id);
        }

        $model = $model->where('is_delete', '=', 0)
            ->where('pay_status', '=', 20)
            ->where('order_status', '<>', 20);


        if ($type == 'order_total') {
            // 订单数量
            return $model->count();
        } else if ($type == 'order_total_price') {
            // 订单总金额
            return $model->sum('pay_price');
        } else if ($type == 'order_user_total') {
            // 支付用户数
            return count($model->distinct(true)->column('user_id'));
        }
        return 0;
    }

    /**
     * 修改订单价格
     */
    public function updatePrice($data)
    {
        if ($this['pay_status']['value'] != 10) {
            $this->error = '该订单不合法';
            return false;
        }
        if ($this['order_source'] != 10) {
            $this->error = '该订单不合法';
            return false;
        }
        // 实际付款金额
        $payPrice = bcadd($data['update_price'], $data['update_express_price'], 2);
        if ($payPrice <= 0) {
            $this->error = '订单实付款价格不能为0.00元';
            return false;
        }
        return $this->save([
                'order_no' => $this->orderNo(), // 修改订单号, 否则微信支付提示重复
                'order_price' => $data['update_price'],
                'pay_price' => $payPrice,
                'update_price' => helper::bcsub($data['update_price'], helper::bcsub($this['total_price'], $this['coupon_money'])),
                'express_price' => $data['update_express_price']
            ]) !== false;
    }

    /**
     * 后台取消订单
     */
    public function orderCancel($data)
    {
        // 判断订单是否可以取消
        if ($this['delivery_status']['value'] == 20 || $this['order_status']['value'] != 10 || $this['pay_status']['value'] != 20) {
            $this->error = "订单不允许取消";
            return false;
        }
        // 订单取消事件
        $status = $this->transaction(function () use ($data) {
            // 执行退款操作
            (new OrderRefundService)->execute($this);
            // 回退商品库存
            ProductFactory::getFactory($this['order_source'])->backProductStock($this['product'], true);
            // 回退用户优惠券
            $this['coupon_id'] > 0 && UserCouponModel::setIsUse($this['coupon_id'], false);
            $this['coupon_id_sys'] > 0 && UserCouponModel::setIsUse($this['coupon_id_sys'], false);
            // 回退用户积分
            $user = UserModel::detail($this['user_id']);
            $describe = "订单取消：{$this['order_no']}";
            $this['points_num'] > 0 && $user->setIncPoints($this['points_num'], $describe);
            // 更新订单状态
            return $this->save(['order_status' => 20, 'cancel_remark' => $data['cancel_remark']]);
        });
        return $status;
    }

    /**
     * 审核：用户取消订单
     */
    public function confirmCancel($data)
    {
        // 判断订单是否有效
        if ($this['pay_status']['value'] != 20) {
            $this->error = '该订单不合法';
            return false;
        }
        // 订单取消事件
        $status = $this->transaction(function () use ($data) {
            if ($data['is_cancel'] == true) {
                // 执行退款操作
                (new OrderRefundService)->execute($this);
                // 回退商品库存
                ProductFactory::getFactory($this['order_source'])->backProductStock($this['product'], true);
                // 回退用户优惠券
                $this['coupon_id'] > 0 && UserCouponModel::setIsUse($this['coupon_id'], false);
                $this['coupon_id_sys'] > 0 && UserCouponModel::setIsUse($this['coupon_id_sys'], false);
                // 回退用户积分
                $user = UserModel::detail($this['user_id']);
                $describe = "订单取消：{$this['order_no']}";
                $this['points_num'] > 0 && $user->setIncPoints($this['points_num'], $describe);
            }
            // 更新订单状态
            return $this->save(['order_status' => $data['is_cancel'] ? 20 : 10]);
        });
        return $status;
    }

    /**
     * 确认发货(单独订单)
     */
    public function delivery($data)
    {
        // 转义为订单列表
        $orderList = [$this];
        // 验证订单是否满足发货条件
        if (!$this->verifyDelivery($orderList)) {
            return false;
        }
        // 整理更新的数据
        $updateList = [[
            'order_id' => $this['order_id'],
            'express_id' => $data['express_id'],
            'express_no' => $data['express_no']
        ]];
        // 更新订单发货状态
        if ($status = $this->updateToDelivery($updateList)) {
            // 获取已发货的订单
            $completed = self::detail($this['order_id'], ['user', 'address', 'product', 'express']);
            // 发送消息通知
            $this->sendDeliveryMessage([$completed]);
        }
        return $status;
    }

    /**
     * 确认发货后发送消息通知
     */
    public function sendDeliveryMessage($orderList)
    {
        // 实例化消息通知服务类
        $Service = new MessageService;
        foreach ($orderList as $item) {
            // 发送消息通知
            $Service->delivery($item, OrderTypeEnum::MASTER);
        }
        return true;
    }

    /**
     * 更新订单发货状态(批量)
     */
    private function updateToDelivery($orderList)
    {
        $data = [];
        foreach ($orderList as $item) {
            $data[] = [
                'data' => [
                    'order_id' => $item['order_id'],
                    'express_no' => $item['express_no'],
                    'express_id' => $item['express_id'],
                    'delivery_status' => 20,
                    'delivery_time' => time(),
                ],
                'where' => [
                    'order_id' => $item['order_id']
                ]
            ];
        }
        return $this->updateAll($data);
    }

    /**
     * 验证订单是否满足发货条件
     */
    private function verifyDelivery($orderList)
    {
        foreach ($orderList as $order) {
            if (
                $order['pay_status']['value'] != 20
                || $order['delivery_type']['value'] != DeliveryTypeEnum::EXPRESS
                || $order['delivery_status']['value'] != 10
            ) {
                $this->error = "订单号[{$order['order_no']}] 不满足发货条件!";
                return false;
            }
        }
        return true;
    }

    public function editLogistics($data)
    {
        return $this->save($data);
    }

    //同步订单参数拼接
    public function getBizContent($order_id, $type = 1)
    {
        $orderData = $this->detail($order_id);
        $appid = Applist::detail($orderData['applet_id'])['appid'];
        //图片id（logo）
        $img_id = '2023051600502200000068596016';
        if ($type == 1){
            //下单成功
            $data = [
                'amount' => $orderData['pay_price'],
                'order_modified_time' => date('Y-m-d H:i:s', time()),
                'order_create_time' => $orderData['create_time'],
                'pay_amount' => $orderData['pay_price'],
                'out_biz_no' => $orderData['order_no'],
                'buyer_id' => $orderData['user']['open_id'],
                'order_type' => 'SERVICE_ORDER',
                'item_order_list' => [
                    [
                        'quantity' => 1,
                        'item_name' => $orderData['product'][0]['product_name'],
                        'unit_price' => $orderData['product'][0]['product_price'],
                        'ext_info' => [
                            'ext_key' => 'image_material_id',
                            'ext_value' => $img_id,
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
                        'ext_value' => 'CREATE',
                    ],
                    [
                        'ext_key' => 'merchant_order_link_page',
                        'ext_value' => '/pages/order/order-detail?order_id='.$order_id,
                    ],
                    [
                        'ext_key' => 'tiny_app_id',
                        'ext_value' => $appid,
                    ],
                ]
            ];
        }elseif ($type ==2){
            //待支付
            $data = [
                'amount' => $orderData['pay_price'],
                'order_modified_time' => date('Y-m-d H:i:s', time()),
                'order_create_time' => $orderData['create_time'],
                'pay_amount' => $orderData['pay_price'],
                'out_biz_no' => $orderData['order_no'],
                'buyer_id' => $orderData['user']['open_id'],
                'order_type' => 'SERVICE_ORDER',
                'item_order_list' => [
                    [
                        'quantity' => 1,
                        'item_name' => $orderData['product'][0]['product_name'],
                        'unit_price' => $orderData['product'][0]['product_price'],
                        'ext_info' => [
                            'ext_key' => 'image_material_id',
                            'ext_value' => $img_id,
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
                        'ext_value' => 'WAIT_PAY',
                    ],
                    [
                        'ext_key' => 'merchant_order_link_page',
                        'ext_value' => '/pages/order/order-detail?order_id='.$order_id,
                    ],
                    [
                        'ext_key' => 'tiny_app_id',
                        'ext_value' => $appid,
                    ],
                ]
            ];
        }elseif ($type ==3){
            //已关闭
            $data = [
                'amount' => $orderData['pay_price'],
                'order_modified_time' => date('Y-m-d H:i:s', time()),
                'order_create_time' => $orderData['create_time'],
                'pay_amount' => $orderData['pay_price'],
                'out_biz_no' => $orderData['order_no'],
                'buyer_id' => $orderData['user']['open_id'],
                'order_type' => 'SERVICE_ORDER',
                'item_order_list' => [
                    [
                        'quantity' => 1,
                        'item_name' => $orderData['product'][0]['product_name'],
                        'unit_price' => $orderData['product'][0]['product_price'],
                        'ext_info' => [
                            'ext_key' => 'image_material_id',
                            'ext_value' => $img_id,
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
                        'ext_value' => 'CLOSED',
                    ],
                    [
                        'ext_key' => 'merchant_order_link_page',
                        'ext_value' => '/pages/order/order-detail?order_id='.$order_id,
                    ],
                    [
                        'ext_key' => 'tiny_app_id',
                        'ext_value' => $appid,
                    ],
                ]
            ];
        }elseif ($type ==4){
            //支付完成
            $data = [
                'amount' => $orderData['pay_price'],
                'order_modified_time' => date('Y-m-d H:i:s', time()),
                'order_create_time' => $orderData['create_time'],
                'pay_amount' => $orderData['pay_price'],
                'out_biz_no' => $orderData['order_no'],
                'buyer_id' => $orderData['user']['open_id'],
                'order_type' => 'SERVICE_ORDER',
                'item_order_list' => [
                    [
                        'quantity' => 1,
                        'item_name' => $orderData['product'][0]['product_name'],
                        'unit_price' => $orderData['product'][0]['product_price'],
                        'ext_info' => [
                            'ext_key' => 'image_material_id',
                            'ext_value' => $img_id,
                        ]
                    ]
                ],
                'ext_info' => [
                    [
                        'ext_key' => 'business_info',
                        'ext_value' => '{"come_again":"alipays://platformapi/startapp?appId='.$appid.'page=pages%2Fproduct%2Fdetail%2Fdetail%3Fproduct_id%3D'.$orderData['product'][0]['product_id'].'"}',
                    ],
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
                        'ext_value' => '/pages/order/order-detail?order_id='.$order_id,
                    ],
                    [
                        'ext_key' => 'tiny_app_id',
                        'ext_value' => $appid,
                    ],
                ]
            ];
        }elseif ($type ==5){
            //已发货
            $data = [
                'amount' => $orderData['pay_price'],
                'order_modified_time' => date('Y-m-d H:i:s', time()),
                'order_create_time' => $orderData['create_time'],
                'pay_amount' => $orderData['pay_price'],
                'out_biz_no' => $orderData['order_no'],
                'buyer_id' => $orderData['user']['open_id'],
                'order_type' => 'SERVICE_ORDER',
                'item_order_list' => [
                    [
                        'quantity' => 1,
                        'item_name' => $orderData['product'][0]['product_name'],
                        'unit_price' => $orderData['product'][0]['product_price'],
                        'ext_info' => [
                            'ext_key' => 'image_material_id',
                            'ext_value' => $img_id,
                        ]
                    ]
                ],
                'ext_info' => [
                    [
                        'ext_key' => 'business_info',
                        'ext_value' => '{"courier_number":"'.$orderData['express_no'].'"}',
                    ],
                    [
                        'ext_key' => 'merchant_biz_type',
                        'ext_value' => 'KX_SHOPPING',
                    ],
                    [
                        'ext_key' => 'merchant_order_status',
                        'ext_value' => 'IN_DELIVERY',
                    ],
                    [
                        'ext_key' => 'merchant_order_link_page',
                        'ext_value' => '/pages/order/order-detail?order_id='.$order_id,
                    ],
                    [
                        'ext_key' => 'tiny_app_id',
                        'ext_value' => $appid,
                    ],
                ]
            ];
        }elseif ($type ==6){
            //退款中
            $data = [
                'amount' => $orderData['pay_price'],
                'order_modified_time' => date('Y-m-d H:i:s', time()),
                'order_create_time' => $orderData['create_time'],
                'pay_amount' => $orderData['pay_price'],
                'out_biz_no' => $orderData['order_no'],
                'buyer_id' => $orderData['user']['open_id'],
                'order_type' => 'SERVICE_ORDER',
                'item_order_list' => [
                    [
                        'quantity' => 1,
                        'item_name' => $orderData['product'][0]['product_name'],
                        'unit_price' => $orderData['product'][0]['product_price'],
                        'ext_info' => [
                            'ext_key' => 'image_material_id',
                            'ext_value' => $img_id,
                        ]
                    ]
                ],
                'ext_info' => [
                    [
                        'ext_key' => 'business_info',
                        'ext_value' => '{"come_again":"alipays://platformapi/startapp?appId='.$appid.'page=pages%2Fproduct%2Fdetail%2Fdetail%3Fproduct_id%3D'.$orderData['product'][0]['product_id'].'"}',
                    ],
                    [
                        'ext_key' => 'merchant_biz_type',
                        'ext_value' => 'KX_SHOPPING',
                    ],
                    [
                        'ext_key' => 'merchant_order_status',
                        'ext_value' => 'REFUNDING',
                    ],
                    [
                        'ext_key' => 'merchant_order_link_page',
                        'ext_value' => '/pages/order/order-detail?order_id='.$order_id,
                    ],
                    [
                        'ext_key' => 'tiny_app_id',
                        'ext_value' => $appid,
                    ],
                ]
            ];
        }elseif ($type ==7){
            //已退款
            $data = [
                'amount' => $orderData['pay_price'],
                'order_modified_time' => date('Y-m-d H:i:s', time()),
                'order_create_time' => $orderData['create_time'],
                'pay_amount' => $orderData['pay_price'],
                'out_biz_no' => $orderData['order_no'],
                'buyer_id' => $orderData['user']['open_id'],
                'order_type' => 'SERVICE_ORDER',
                'item_order_list' => [
                    [
                        'quantity' => 1,
                        'item_name' => $orderData['product'][0]['product_name'],
                        'unit_price' => $orderData['product'][0]['product_price'],
                        'ext_info' => [
                            'ext_key' => 'image_material_id',
                            'ext_value' => $img_id,
                        ]
                    ]
                ],
                'ext_info' => [
                    [
                        'ext_key' => 'business_info',
                        'ext_value' => '{"come_again":"alipays://platformapi/startapp?appId='.$appid.'page=pages%2Fproduct%2Fdetail%2Fdetail%3Fproduct_id%3D'.$orderData['product'][0]['product_id'].'"}',
                    ],
                    [
                        'ext_key' => 'merchant_biz_type',
                        'ext_value' => 'KX_SHOPPING',
                    ],
                    [
                        'ext_key' => 'merchant_order_status',
                        'ext_value' => 'REFUNDED',
                    ],
                    [
                        'ext_key' => 'merchant_order_link_page',
                        'ext_value' => '/pages/order/order-detail?order_id='.$order_id,
                    ],
                    [
                        'ext_key' => 'tiny_app_id',
                        'ext_value' => $appid,
                    ],
                ]
            ];
        }elseif ($type ==8){
            //已完成
            $data = [
                'amount' => $orderData['pay_price'],
                'order_modified_time' => date('Y-m-d H:i:s', time()),
                'order_create_time' => $orderData['create_time'],
                'pay_amount' => $orderData['pay_price'],
                'out_biz_no' => $orderData['order_no'],
                'buyer_id' => $orderData['user']['open_id'],
                'order_type' => 'SERVICE_ORDER',
                'item_order_list' => [
                    [
                        'quantity' => 1,
                        'item_name' => $orderData['product'][0]['product_name'],
                        'unit_price' => $orderData['product'][0]['product_price'],
                        'ext_info' => [
                            'ext_key' => 'image_material_id',
                            'ext_value' => $img_id,
                        ]
                    ]
                ],
                'ext_info' => [
                    [
                        'ext_key' => 'business_info',
                        'ext_value' => '{"come_again":"alipays://platformapi/startapp?appId='.$appid.'page=pages%2Fproduct%2Fdetail%2Fdetail%3Fproduct_id%3D'.$orderData['product'][0]['product_id'].'"}',
                    ],
                    [
                        'ext_key' => 'merchant_biz_type',
                        'ext_value' => 'KX_SHOPPING',
                    ],
                    [
                        'ext_key' => 'merchant_order_status',
                        'ext_value' => 'FINISHED',
                    ],
                    [
                        'ext_key' => 'merchant_order_link_page',
                        'ext_value' => '/pages/order/order-detail?order_id='.$order_id,
                    ],
                    [
                        'ext_key' => 'tiny_app_id',
                        'ext_value' => $appid,
                    ],
                ]
            ];
        }
        $data['applet_id'] = $orderData['applet_id'];
        return $data;
    }
}