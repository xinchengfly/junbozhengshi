<?php

namespace app\api\service\order\settled;

use app\common\enum\order\OrderSourceEnum;
use app\common\model\settings\Setting as SettingModel;
use app\api\model\order\Order as OrderModel;

/**
 * 积分商城订单结算服务类
 */
class PointsOrderSettledService extends OrderSettledService
{
    private $config;
    /**
     * 构造函数
     */
    public function __construct($user, $supplierData, $params)
    {
       parent::__construct($user, $supplierData, $params);
        // 订单来源
        $this->orderSource = [
            'source' => OrderSourceEnum::POINTS,
       ];
        $this->config = SettingModel::getItem('pointsmall');
        // 自身构造,差异化规则
        $this->settledRule = array_merge($this->settledRule, [
            'force_points' => true,     //强制使用积分，积分兑换
            'is_coupon' => $this->config['is_coupon'],
            'is_agent' => $this->config['is_agent'],
            'is_user_grade' => false,     // 会员等级折扣
            'is_reduce' => false, //满减
        ]);
    }

    /**
     * 验证订单商品的状态
     */
    public function validateProductList()
    {
        // 判断活动是否开启
        if(!$this->config['is_open']){
            $this->error = "商品积分兑换活动未开启";
            return false;
        }
        foreach ($this->supplierData[0]['productList'] as $product) {
            // 判断商品是否下架
            if ($product['product_status']['value'] != 10) {
                $this->error = "很抱歉，积分兑换商品已下架";
                return false;
            }
            // 判断商品库存
            if ($product['total_num'] > $product['point_sku']['point_stock']) {
                $this->error = "很抱歉，积分兑换商品库存不足";
                return false;
            }
            // 是否超过购买数
            $hasNum = OrderModel::getPlusOrderNum($this->user['user_id'], $product['product_source_id'],OrderSourceEnum::POINTS);
            if($hasNum + $product['total_num'] > $product['point_product']['limit_num']){
                $this->error = "很抱歉，你已兑换或超过最大兑换数量";
                return false;
            }
        }
        return true;
    }
}