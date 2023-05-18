<?php

namespace app\shop\controller\supplier;

use app\shop\controller\Controller;
use app\shop\model\supplier\DepositOrder as DepositOrderModel;

/**
 * 供应商押金订单控制器
 */
class Order extends Controller
{

    /**
     * 押金订单列表
     */
    public function index()
    {

        $model = new DepositOrderModel;
        $list = $model->getList($this->postData());
        return $this->renderSuccess('', compact('list'));
    }

}
