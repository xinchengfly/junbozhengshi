<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2023/4/12
 * Time: 15:18
 */

namespace app\shop\controller\manufacturer;

use app\shop\controller\Controller;
use app\shop\model\manufacturer\Manufacturer as ManufacturerModel;

class Manufacturer extends Controller
{

    /**
     * 厂商列表
     */
    public function list()
    {
        $list = ManufacturerModel::getList($this->postData());
        return $this->renderSuccess('', compact('list'));
    }

    public function add()
    {
        $model = new ManufacturerModel();
        $name = $model::detail(['name' => $this->postData()['name']]);
        if ($name){
            return $this->renderError('厂商已存在');
        }
        if ($model->add($this->postData())) {
            return $this->renderSuccess('添加成功');
        };
        return $this->renderError('添加失败');
    }

    /**
     * 删除厂商
     */
    public function delete($id)
    {
        // 协议详情
        $model = ManufacturerModel::detail($id);
        if ($model && $model->setDelete()) {
            return $this->renderSuccess('删除成功');
        }
        return $this->renderError($model->getError() ?: '删除失败');
    }

    /**
     * 编辑厂商
     */
    public function edituser()
    {
        $model = new ManufacturerModel;
        // 新增记录
        if ($model->edit($this->request->param())) {
            return $this->renderSuccess('修改成功');
        }
        return $this->renderError($model->getError() ?: '修改失败');
    }
}