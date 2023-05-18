<?php

namespace app\common\model\supplier;

use app\common\model\BaseModel;

/**
 * 商家供应商模型
 */
class Supplier extends BaseModel
{
    protected $name = 'supplier';
    protected $pk = 'shop_supplier_id';

    /**
     * 关联应用表
     */
    public function app()
    {
        return $this->belongsTo('app\\common\\model\\app\\App', 'app_id', 'app_id');
    }

    /**
     * 关联logo
     */
    public function logo()
    {
        return $this->hasOne('app\\common\\model\\file\\UploadFile', 'file_id', 'logo_id');
    }
    /**
     * 关联品牌类型
     */
    public function category()
    {
        return $this->hasOne('app\\common\\model\\supplier\\Category', 'category_id', 'category_id');
    }
    /**
     * 关联business
     */
    public function business()
    {
        return $this->hasOne('app\\common\\model\\file\\UploadFile', 'file_id', 'business_id');
    }
    /**
     * 关联超管
     */
    public function superUser()
    {
        return $this->hasOne('app\\common\\model\\supplier\\User', 'shop_supplier_id', 'shop_supplier_id')
            ->where('is_super','=', 1);
    }
    /**
     * 详情
     */
    public static function detail($shop_supplier_id, $with = [])
    {
        return (new static())->with($with)->find($shop_supplier_id);
    }

    /**
     * 累积供应商结算金额 (批量)
     */
    public function onBatchIncSupplierMoney($data)
    {
        foreach ($data as $supplierId => $supplierMoney) {
            $this->where(['shop_supplier_id' => $supplierId])
                ->inc('total_money', $supplierMoney)
                ->inc('money', $supplierMoney)
                ->update();
        }
        return true;
    }


}