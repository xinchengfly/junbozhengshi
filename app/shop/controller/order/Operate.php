<?php

namespace app\shop\controller\order;

use app\shop\controller\Controller;
use app\shop\model\order\Order as OrderModel;
use think\facade\Env;



/**
 * 订单操作
 * @package app\shop\controller\order
 */
class Operate extends Controller
{
    /**
     * 订单导出
     */
    public function export($dataType)
    {
        $model = new OrderModel();
        return $model->exportList($dataType, $this->postData());
    }

    /**
     * 一键导出
     * @param $dataType
     */
    public function exportOne($dataType)
    {
        $model = new OrderModel();
        return $model->exportListOne($dataType, $this->postData());
    }

    public function import()
    {
        if ($_FILES["file"]["error"] > 0) {
            return $this->renderError('错误: ' . $_FILES["file"]["error"]);
        }
        if (file_exists('uploads/Excel/' . $_FILES["file"]["name"])) {
//            return $this->renderError($_FILES["file"]["name"] . ' 文件已经存在. ');
        } else {
            move_uploaded_file($_FILES["file"]["tmp_name"],'uploads/Excel/' . $_FILES["file"]["name"]);
        }
        $filePath = ROOT_PATH() . 'public/' . 'uploads/Excel/' . $_FILES["file"]["name"];
        $model = new OrderModel();
        $data = $model->import2($filePath);
        if ($data == '导入成功'){
            return $this->renderSuccess('导入成功');
        }else{
            return $this->renderError('导入失败');
        }
    }



    /**
     * 审核：用户取消订单
     */
    public function confirmCancel($order_id)
    {
        $model = OrderModel::detail($order_id);
        if ($this->postData()['is_cancel'] == 'false'){
            return $this->renderSuccess('操作失败');
        }
        if ($model->confirmCancel($this->postData())) {
            $data = [
                'app_id' => 10001,
                'order_id' => $order_id,
                'states' => 7,
            ];
            curlPost('https://'.Env::get('url.url', 'yuzhou.haidiao888.com').'/index.php/api/order.order_center/index2', $data);
            return $this->renderSuccess('操作成功');
        }
        return $this->renderError('操作失败');
    }

    /**
     * 门店自提核销
     */
    public function extract()
    {
        $params = $this->postData('extract_form');
        $model = OrderModel::detail($params['order_id']);
        if ($model->verificationOrder($params['order']['extract_clerk_id'])) {
            return $this->renderSuccess('核销成功');
        }
        return $this->renderError($model->getError() ?: '核销失败');
    }

}