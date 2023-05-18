<?php

namespace app\api\controller\plus\points;

use app\api\controller\Controller;
use app\api\model\plus\points\Product as ProductModel;
use app\api\model\settings\Setting as SettingModel;
use app\common\service\product\BaseProductService;

/**
 * 积分商城控制器
 */
class Product extends Controller
{
    /**
     *积分商品列表
     */
    public function index()
    {
        $model = new ProductModel();
        $list = $model->getList($this->request->param());
        $user = $this->getUser();
        return $this->renderSuccess('', compact('list','user'));
    }

    /**
     *积分商品列表
     */
    public function detail($point_product_id)
    {
        $detail = (new ProductModel())->getPointDetail($point_product_id);
        //规格
        $specData = BaseProductService::getSpecData($detail['product']);
        //是否显示店铺信息
        $store_open = SettingModel::getStoreOpen();
        return $this->renderSuccess('',compact('detail', 'specData', 'store_open'));
    }
}