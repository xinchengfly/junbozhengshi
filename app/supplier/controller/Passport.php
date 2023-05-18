<?php

namespace app\supplier\controller;

use app\supplier\model\supplier\User;
use app\supplier\model\settings\Setting as SettingModel;
/**
 * 商户认证
 */
class Passport extends Controller
{
    /**
     * 商户后台登录
     */
    public function login()
    {
        //登录前清空session
        session('jjjshop_supplier', null);
        $user = $this->postData();
        $user['password'] = salt_hash($user['password']);
        $username = $user['username'];
        $model = new User();
        if ($model->checkLogin($user)) {
            $url = SettingModel::getSysConfig()['url'];
            return $this->renderSuccess('登录成功', compact('username', 'url'));
        }
        return $this->renderError($model->getError()?:'登录失败');
    }

    /**
     * 退出登录
     */
    public function logout()
    {
        session('jjjshop_supplier', null);
        return $this->renderSuccess('退出成功');
    }

    /*
   * 修改密码
   */
    public function editPass()
    {
        $model = new User();
        if ($model->editPass($this->postData(), $this->supplier['user'])) {
            return $this->renderSuccess('修改成功');
        }
        return $this->renderError($model->getError()?:'修改失败');
    }
}
