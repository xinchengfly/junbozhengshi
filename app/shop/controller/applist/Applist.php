<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2023/4/19
 * Time: 16:59
 */

namespace app\shop\controller\applist;

use app\shop\controller\Controller;
use app\shop\model\applist\Applist as ApplistModel;

class Applist extends Controller
{
    /**
     * 厂商列表
     */
    public function list()
    {
        $list = ApplistModel::getList($this->postData());
        return $this->renderSuccess('', compact('list'));
    }

    public function add()
    {
        $model = new ApplistModel();
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
        $model = ApplistModel::detail($id);
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
        $model = new ApplistModel;
        // 新增记录
        if ($model->edit($this->request->param())) {
            return $this->renderSuccess('修改成功');
        }
        return $this->renderError($model->getError() ?: '修改失败');
    }
}