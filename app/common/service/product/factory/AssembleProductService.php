<?php

namespace app\common\service\product\factory;

use app\common\enum\product\DeductStockTypeEnum;
use app\common\model\plus\assemble\AssembleSku as ProductSkuModel;
use app\common\model\plus\assemble\Product as ProductModel;
use app\common\model\plus\assemble\Bill as BillModel;
/**
 * 商品来源-普通商品扩展类
 */
class AssembleProductService extends ProductService
{
    /**
     * 更新商品库存 (针对下单减库存的商品)
     */
    public function updateProductStock($productList)
    {
        foreach ($productList as $product) {
            // 下单减库存
            $sku = ProductSkuModel::detail($product['sku_source_id']);
            //增加参与人数
            (new ProductModel)->where('assemble_product_id', '=', $sku['assemble_product_id'])->inc('join_num')->update();
            if ($product['deduct_stock_type'] == DeductStockTypeEnum::CREATE) {
                try{
                    // 主库存减少
                    (new ProductModel)->where('assemble_product_id', '=', $sku['assemble_product_id'])->dec('stock', $product['total_num'])->update();
                    // 下单减库存
                    (new ProductSkuModel)->where('assemble_product_sku_id', '=', $sku['assemble_product_sku_id'])->dec('assemble_stock', $product['total_num'])->update();
                }catch (\Exception $e){
                    log_write('assemble updateProductStock'. $e->getMessage());
                }
            }
        }
    }

    /**
     * 更新商品库存销量（订单付款后）
     */
    public function updateStockSales($productList)
    {
        foreach ($productList as $product) {
            $sku = ProductSkuModel::detail($product['sku_source_id']);
            // 记录商品的销量
            (new ProductModel)->where('assemble_product_id', '=', $sku['assemble_product_id'])->inc('total_sales', $product['total_num'])->update();
            // 付款减库存
            if ($product['deduct_stock_type'] == DeductStockTypeEnum::PAYMENT) {
                try{
                    // 主库存减少
                    (new ProductModel)->where('assemble_product_id', '=', $sku['assemble_product_id'])->dec('stock', $product['total_num'])->update();
                    // 下单减库存
                    (new ProductSkuModel)->where('assemble_product_sku_id', '=', $sku['assemble_product_sku_id'])->dec('assemble_stock', $product['total_num'])->update();
                }catch (\Exception $e){
                    log_write('assemble updateStockSales'. $e->getMessage());
                }
            }
            //插入或更新拼团信息
            if($product['bill_source_id'] == 0){
                $bill_model = new BillModel();
                //团长
                $bill_model->newOrder($product, $sku);
            }else{
                $bill_model = BillModel::detail($product['bill_source_id']);
                $bill_model->updateOrder($product, $sku);
            }
        }
        return true;
    }

    /**
     * 回退商品库存
     */
    public function backProductStock($productList, $isPayOrder = false)
    {
        foreach ($productList as $product) {
            // 未付款订单并且创建时减库存，回退库存
            if (!$isPayOrder && $product['deduct_stock_type'] == DeductStockTypeEnum::CREATE) {
                $point_sku = ProductSkuModel::detail($product['product_source_id']);
                // 回退主库存
                (new ProductModel)->where('assemble_product_id', '=', $point_sku['assemble_product_id'])->inc('stock')->update();
                // 回退sku库存
                (new ProductSkuModel)->where('assemble_product_sku_id', '=', $point_sku['assemble_product_sku_id'])->inc('assemble_stock')->update();
            }
        }
    }

}