<?php

namespace app\api\controller\supplier;

use app\api\controller\Controller;
use app\api\model\product\Product as ProductModel;

/**
 * 供应商产品
 */
class Product extends Controller
{
    // user
    private $user;
    private $supplierUser;

    /**
     * 构造方法
     */
    public function initialize()
    {
        parent::initialize();
        $this->user = $this->getUser();   // 用户信息
        $this->supplierUser = $this->getSupplierUser($this->user);
    }
    /**
     * 供应商中心主页
     */
    public function index()
    { 
        $data = $this->postData();
        // 获取商品列表数据
        $model = new ProductModel;
        $data['shop_supplier_id'] = $this->supplierUser['shop_supplier_id'];
        $productList = $model->getList($data, $this->user);
        return $this->renderSuccess('', compact('productList'));
    }

    //商品上下架
    public function modify(){
        $data = $this->postData();
        // 获取商品数据
        $model = ProductModel::detail($data['product_id']);
        if($this->supplierUser['shop_supplier_id'] != $model['shop_supplier_id']){
            return $this->renderError('非法请求');
        }
        if(!$model->editStatus($data)){
            return $this->renderError('操作失败');
        }
        return $this->renderSuccess('操作成功');
    }
   
}