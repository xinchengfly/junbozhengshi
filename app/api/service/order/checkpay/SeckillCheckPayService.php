<?php

namespace app\api\service\order\checkpay;

use app\common\enum\product\DeductStockTypeEnum;
use app\common\model\plus\seckill\SeckillSku as SeckillSkuModel;

/**
 * 秒杀订单支付检查服务类
 */
class SeckillCheckPayService extends CheckPayService
{
    /**
     * 判断订单是否允许付款
     */
    public function checkOrderStatus($order)
    {
        // 判断订单状态
        if (!$this->checkOrderStatusCommon($order)) {
            return false;
        }
        // 判断商品状态、库存
        if (!$this->checkProductStatus($order['product'])) {
            return false;
        }
        return true;
    }

    /**
     * 判断商品状态、库存 (未付款订单)
     */
    protected function checkProductStatus($productList)
    {
        foreach ($productList as $product) {
            // 秒杀商品sku信息
            $seckillProductSku = SeckillSkuModel::detail($product['sku_source_id'], ['product']);
            $seckillProduct = $seckillProductSku['product'];
            // sku是否存在
            if (empty($seckillProductSku)) {
                $this->error = "很抱歉，商品 [{$product['product_name']}] sku已不存在，请重新下单";
                return false;
            }
            // 判断商品是否下架
            if (empty($seckillProduct)) {
                $this->error = "很抱歉，商品 [{$product['product_name']}] 不存在或已删除";
                return false;
            }

            // 付款减库存
            if ($product['deduct_stock_type'] == DeductStockTypeEnum::PAYMENT && $product['total_num'] > $seckillProduct['seckill_stock']) {
                $this->error = "很抱歉，商品 [{$product['product_name']}] 库存不足";
                return false;
            }
        }
        return true;
    }

}