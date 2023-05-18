<?php

namespace app\api\model\plus\points;

use app\common\exception\BaseException;
use app\common\model\plus\point\Product as PointProductModel;
use app\api\model\product\Product as ProductModel;
use app\api\model\supplier\Supplier as SupplierModel;
use app\api\model\supplier\ServiceApply;
use app\common\model\product\ProductImage;
use think\facade\Db;

/**
 * 积分商城模型
 */
class Product extends PointProductModel
{
    /*
     * 获取列表
     */
    public function getList($params)
    {
        // 获取列表数据
        $list = $this->alias("a")
            ->with(['product.image.file','sku'])
            ->join('product product', 'product.product_id=a.product_id')
            ->join('supplier supplier', 'product.shop_supplier_id = supplier.shop_supplier_id', 'left')
            ->where('a.is_delete','=', 0)
            ->where('a.status', '=', 10)
            ->where('product.is_delete', '=', 0)
            ->where('supplier.is_delete', '=', 0)
            ->where('supplier.status', '=', 0)
            ->where('supplier.is_recycle', '=', 0)
            ->where('product.product_status', '=', 10)
            ->where('product.audit_status', '=', 10)
            ->field('a.*')
            ->order(['a.sort' => 'asc'])
            ->paginate($params);
        foreach ($list as $key => $val) {
            $list[$key]['product_image'] = $val['product']['image'][0]['file_path'];
        }
        return $list;
    }

    /**
     * 获取积分任务的商品列表（用于订单结算）
     */
    public static function getPointsProduct($params)
    {
        // 积分商品详情
        $points = self::detail($params['point_product_id'],['sku']);
        if (empty($points) || $points['status'] == 20 || $points['is_delete'] == 1) {
            throw new BaseException(['msg' => '积分兑换商品不存在或已结束']);
        }

        // 积分商品详情
        $product = ProductModel::detail($points['product_id']);
        // 积分商品sku信息
        $point_sku = null;
        if($product['spec_type'] == 10){
            $point_sku = $points['sku'][0];
        }else{
            //多规格
            foreach ($points['sku'] as $sku){
                if($sku['point_product_sku_id'] == $params['point_product_sku_id']){
                    $point_sku = $sku;
                    break;
                }
            }
        }
        if ($point_sku == null) {
            throw new BaseException(['msg' => '积分兑换商品规格不存在']);
        }
        // 商品sku信息
        $product['product_sku'] = ProductModel::getProductSku($product, $params['product_sku_id']);
        $product['point_sku'] = $point_sku;

        // 商品列表
        $productList = [$product->hidden(['category', 'content', 'image', 'sku'])];
        // 只会有一个商品
        foreach ($productList as &$item) {
            // 商品单价
            $item['product_price'] = $point_sku['point_money'];
            // 商品购买数量
            $item['total_num'] = $params['product_num'];
            $item['spec_sku_id'] = $item['product_sku']['spec_sku_id'];
            // 商品购买总金额
            $item['total_price'] =  $point_sku['point_money'] * $item['total_num'];
            $item['points_num'] = $point_sku['point_num'] * $item['total_num'];
            $item['point_product_sku_id'] = $point_sku['point_product_sku_id'];
            $item['product_sku_id'] = $params['product_sku_id'];
            $item['product_source_id'] = $point_sku['point_product_id'];
            $item['sku_source_id'] = $point_sku['point_product_sku_id'];
            // 积分商品最大兑换数
            $item['point_product'] = [
                'limit_num' => $points['limit_num']
            ];
            // 积分抵扣金额
            $item['points_money'] = ($item['product_sku']['product_price'] - $point_sku['point_money']) * $params['product_num'];
        }
        $supplierData[] = [
            'shop_supplier_id' => $product['shop_supplier_id'],
            'supplier' => $product['supplier'],
            'productList' => $productList
        ];
        unset($product['supplier']);
        return $supplierData;
    }

    /**
     * 积分商品详情
     */
    public function getPointDetail($point_product_id)
    {
        $result = self::detail($point_product_id, ['product.image.file','sku.productSku.image']);

        if (!empty($result)) {
            $image = new ProductImage();
            $arr = $image->with('file')->where('product_id', '=', $result['product']['product_id'])->where('image_type', '=', 1)->select()->toArray();
            $result['product']['contentImage'] = $arr;
            $point_arr = array_column($result->toArray()['sku'], 'assemble_price');
            $product_arr = array_column($result->toArray()['sku'], 'product_price');
            sort($point_arr);
            sort($product_arr);
            $result['point_price'] =  current($point_arr);
            $result['line_price'] = current($product_arr);
            if (count($point_arr) > 1) {
                $res['point_high_price'] = end($point_arr);
                $res['line_high_price'] = end($product_arr);
            }
            $SupplierModel =new SupplierModel;
        if($result['shop_supplier_id']){
            $supplier = $SupplierModel::detail($result['shop_supplier_id'],['logo', 'category']);
            $supplier['logos'] = $supplier['logo']['file_path'];unset($supplier['logo']);
            $supplier['category_name'] = $supplier['category']['name'];unset($supplier['category']);
            $supplier->visible(['logos', 'category_name', 'name', 'shop_supplier_id', 'product_sales', 'server_score','store_type']);
            $server = (new ServiceApply())->getList($result['shop_supplier_id']);
        }else{
            $supplier = [];
            $server = [];
        }
            $result['supplier'] = $supplier;
            $result['server'] = $server;
        }
        return $result;
    }
}
