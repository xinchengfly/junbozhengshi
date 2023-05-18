<?php

namespace app\shop\controller\product;

use app\shop\model\product\Product as ProductModel;
use app\shop\model\product\Category as CategoryModel;
use app\shop\service\ProductService;
use app\shop\controller\Controller;
use think\facade\Db;

/**
 * 商品管理控制器
 */
class Product extends Controller
{
    /**
     * 商品列表(全部)
     */
    public function index()
    {
        // 获取全部商品列表
        $model = new ProductModel;
        $list = $model->getList(array_merge(['status' => -1], $this->postData()));
        // 商品分类
        $category = CategoryModel::getCacheTree();
        // 数量
        $product_count = [
            'sell' => $model->getCount('sell'),
            'recovery' => $model->getCount('recovery'),
            'lower' => $model->getCount('lower'),
            'audit' => $model->getCount('audit'),
            'no_audit' => $model->getCount('no_audit')
        ];
        return $this->renderSuccess('', compact('list', 'category', 'product_count'));
    }

    /**
     * 商品列表(在售)
     */
    public function lists()
    {
        // 获取全部商品列表
        $model = new ProductModel;
        $list = $model->getLists($this->postData());
        // 商品分类
        $catgory = CategoryModel::getCacheTree();
        return $this->renderSuccess('', compact('list', 'catgory'));
    }

    /**
     * 添加商品
     */
    public function add($scene = 'add')
    {
        // get请求
        if($this->request->isGet()){
            return $this->getBaseData();
        }
        //post请求
        $data = json_decode($this->postData()['params'], true);
        if($scene == 'copy'){
            unset($data['create_time']);
            unset($data['update_time']);
            unset($data['sku']['product_sku_id']);
            unset($data['sku']['product_id']);
            unset($data['product_sku']['product_sku_id']);
            unset($data['product_sku']['product_id']);
            if($data['spec_type'] == 20){
                foreach ($data['spec_many']['spec_list'] as &$spec){
                    $spec['product_sku_id'] = 0;
                }
            }
            //初始化销量等数据
            $data['sales_initial'] = 0;
        }

        $model = new ProductModel;
        if (isset($data['product_id'])) {
            $data['product_id'] = 0;
        }

        if ($model->add($data)) {
            return $this->renderSuccess('添加成功');
        }
        return $this->renderError($model->getError() ?: '添加失败');
    }

    /**
     * 获取基础数据
     */
    public function getBaseData()
    {
        return $this->renderSuccess('', array_merge(ProductService::getEditData(null, 'add'), []));
    }

    /**
     * 获取编辑数据
     */
    public function getEditData($product_id, $scene = 'edit')
    {
        $model = ProductModel::detail($product_id);
        return $this->renderSuccess('', array_merge(ProductService::getEditData($model, $scene), compact('model')));
    }

    /**
     * 商品编辑
     */
    public function edit($product_id, $scene = 'edit')
    {
        if($this->request->isGet()){
            $model = ProductModel::detail($product_id);
            $product_lease_time = Db::name('product_lease_time')->where('product_id','=',$product_id)->select();
            $model['tableData'] = $product_lease_time;
            return $this->renderSuccess('', array_merge(ProductService::getEditData($model, $scene), compact('model')));
        }
        if ($scene == 'copy') {
            return $this->add($scene);
        }
        // 商品详情
        $model = ProductModel::detail($product_id);
        // 更新记录
        if ($model->edit(json_decode($this->postData()['params'], true))) {
            return $this->renderSuccess('更新成功');
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }
    /**
     * 删除商品分期
     */
    public function delLeaseTime(){
        $id = $this->request->param('id');
        $res = Db::name('product_lease_time')->where('product_stage_id','=',$id)->delete();
        if ($res){
            return $this->renderSuccess('删除成功');
        }
        return $this->renderError('删除失败');
    }

    /**
     * 修改商品状态
     */
    public function state($product_id, $state)
    {
        // 商品详情
        $model = ProductModel::detail($product_id);
        if (!$model->setStatus($state)) {
            return $this->renderError('操作失败');
        }
        return $this->renderSuccess('操作成功');
    }

    /**
     * 强制下架、再上架
     */
    public function audit($product_id, $state)
    {
        // 商品详情
        $model = ProductModel::detail($product_id);
        if (!$model->setAudit($state)) {
            return $this->renderError('操作失败');
        }
        return $this->renderSuccess('操作成功');
    }
    /**
     * 删除商品
     */
    public function delete($product_id)
    {
        // 商品详情
        $model = ProductModel::detail($product_id);
        if (!$model->setDelete()) {
            return $this->renderError($model->getError() ?: '删除失败');
        }
        return $this->renderSuccess('删除成功');
    }

}
