<?php

namespace app\supplier\model\supplier;

use app\common\model\supplier\Supplier as SupplierModel;
/**
 * 后台管理员登录模型
 */
class Supplier extends SupplierModel
{
    /**
     *检查登录
     */
    public function checkLogin($params)
    {
        $where['user_name'] = $params['username'];
        $where['password'] = $params['password'];
        $where['is_delete'] = 0;

        if (!$supplier = $this->where($where)->with(['app'])->find()) {
            return false;
        }
        if (empty($supplier['app'])) {
            $this->error = '登录失败, 未找到应用信息';
            return false;
        }
        if ($supplier['app']['is_recycle']) {
            $this->error = '登录失败, 当前应用已删除';
            return false;
        }
        // 保存登录状态
        $this->loginState($supplier);
        return true;
    }


    /*
    * 修改密码
    */
    public function editPass($data, $user)
    {
        $user_info = User::detail($user['shop_user_id']);
        if ($data['password'] != $data['confirmPass']) {
            $this->error = '密码错误';
            return false;
        }
        if ($user_info['password'] != salt_hash($data['oldpass'])) {
            $this->error = '两次密码不相同';
            return false;
        }
        $date['password'] = salt_hash($data['password']);
        $user_info->save($date);
        return true;
    }

    /**
     * 保存登录状态
     */
    public function loginState($supplier)
    {
        $app = $supplier['app'];
        // 保存登录状态
        $session = array(
            'supplier' => [
                'shop_supplier_id' => $supplier['shop_supplier_id'],
                'user_name' => $supplier['user_name']
            ],
            'app' => $app->toArray(),
            'is_login' => true,
        );
        session('jjjshop_supplier', $session);
    }

    /**
     * 修改
     */
    public function edit($data){
        $isexist = $this->where('name','=',$data['name'])->where('shop_supplier_id','<>',$data['shop_supplier_id'])->find();
        if($isexist){
            $this->error = '店铺名称已存在';
            return false;
        }
        return $this->save([
            'link_name' => $data['link_name'],
            'link_phone' => $data['link_phone'],
            'address' => $data['address'],
            'description' => $data['description'],
            'logo_id' => $data['logo_id'],
            'business_id' => $data['business_id'],
            'app_id' => self::$app_id,
            'name' => $data['name'],
            'is_full' => 1
        ]);
    }

    /**
     * 资金冻结
     */
    public function freezeMoney($money)
    {
        return $this->save([
            'money' => $this['money'] - $money,
            'freeze_money' => $this['freeze_money'] + $money,
        ]);
    }
}