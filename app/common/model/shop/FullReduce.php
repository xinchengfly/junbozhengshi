<?php

namespace app\common\model\shop;

use app\common\model\BaseModel;

/**
 * 满减模型
 */
class FullReduce extends BaseModel
{
    protected $pk = 'fullreduce_id';
    protected $name = 'shop_fullreduce';

    /**
     * 获取详情
     */
    public static function detail($fullreduce_id)
    {
        return (new static())->find($fullreduce_id);
    }

    /**
     * 列表
     */
    public function getAll($shop_supplier_id)
    {
        return $this->where('is_delete', '=', '0')
            ->where('shop_supplier_id', '=', $shop_supplier_id)
            ->order(['create_time' => 'asc'])
            ->select();
    }

    /**
     * 列表,系统满减
     */
    public function getProductAll($product_id)
    {
        return $this->where('is_delete', '=', '0')
            ->where('product_id', '=', $product_id)
            ->order(['create_time' => 'asc'])
            ->select();
    }
}