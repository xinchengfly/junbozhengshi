<?php

namespace app\shop\model\supplier;

use app\common\library\sms\Driver as SmsDriver;
use app\common\model\supplier\Apply as ApplyModel;
use app\common\model\user\User as UserModel;
use app\api\model\supplier\Supplier as SupplierModel;
use app\api\model\settings\Setting as SettingModel;
use app\common\model\supplier\Category as SupplierCategoryModel;
/**
 * 供应商模型
 */
class Apply extends ApplyModel
{

    /**
     * 获取列表数据
     */
    public function getList($params)
    {
        $model = $this;
        if ($params['status'] != '') {
            $model = $model->where('status', '=', $params['status']);
        }
        if ($params['store_name']) {
            $model = $model->where('store_name', 'like', '%' . trim($params['store_name']) . '%');
        }
        // 查询列表数据
        $list = $model->with(['user', 'category', 'businessImage'])
            ->order(['create_time' => 'desc'])
            ->paginate($params);
        // 整理列表数据并返回
        return $this->setListData($list, true);
    }

    /**
     * 设置商品展示的数据
     */
    protected function setListData($data, $isMultiple = true, callable $callback = null)
    {
        if (!$isMultiple) $dataSource = [&$data]; else $dataSource = &$data;

        return $data;
    }

    /**
     * 详情
     */
    public static function detail($supplier_apply_id, $with = [])
    {
        return (new static())->with($with)->find($supplier_apply_id);
    }

    /**
     * 审核
     */
    public function audit($data)
    {
        // 开启事务
        $this->startTrans();
        try {
            if ($data['status'] == 2) {
                if (empty($data['content'])) {
                    $this->error = "备注不能为空";
                    return false;
                }
                $template_code = "supplier_reject_code";
                UserModel::updateType($this['user_id'], 1);
                // 申请状态
                $this->save(['status' => 2]);
            } else if ($data['status'] == 1) {
                $template_code = "supplier_pass_code";
                //用户为供应商
                UserModel::updateType($this['user_id'], 2);
                // 申请状态
                $this->save(['status' => 1]);
                //添加供应商账号更新
                $supplier_data = [
                    'user_name' => $this['mobile'],
                    'password' => $this['password'],
                    'name' => $this['store_name'],
                    'user_id' => $this['user_id'],
                    'real_name' => $this['user_name'],
                    'link_phone' => $this['mobile'],
                    'link_name' => $this['user_name'],
                    'business_id' => $this['business_id'],
                    'category_id' => $this['category_id'],
                    'app_id' => self::$app_id
                ];
                // 供应商状态,如果类目保证金为0，则直接通过，否则需要交纳保证金
                $category = SupplierCategoryModel::detail($this['category_id']);
                if($category['deposit_money'] > 0){
                    $supplier_data['status'] = 20;
                } else{
                    $supplier_data['status'] = 0;
                }
                $SupplierModel = new SupplierModel;
                if (!$SupplierModel->addData($supplier_data)) {
                    $this->error = $SupplierModel->getError();
                    return false;
                }
            }

            $smsConfig = SettingModel::getItem('sms', self::$app_id);
            $send_template_code = $smsConfig['engine']['aliyun'][$template_code];
            $SmsDriver = new SmsDriver($smsConfig);
            $SmsDriver->sendSms($this['mobile'], $send_template_code, '');

            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            $this->rollback();
            return false;
        }
    }

    /**
     * 获取供应商申请数
     */
    public static function getApplyCount(){
        return (new static())->where('status', '=', 0)->count();
    }

    /**
     * 获取供应商申请数
     */
    public static function getApplyCountByDay($day){
        $startTime = strtotime($day);
        return (new static())->where('create_time', '>=', $startTime)
            ->where('create_time', '<', $startTime + 86400)
            ->count();
    }

    /**
     * 获取供应商申请统计数量
     */
    public function getApplyData($startDate = null, $endDate = null, $type)
    {
        $model = $this;
        if(!is_null($startDate)){
            $model = $model->where('create_time', '>=', strtotime($startDate));
        }
        if(is_null($endDate)){
            $model = $model->where('create_time', '<', strtotime($startDate) + 86400);
        }else{
            $model = $model->where('create_time', '<', strtotime($endDate) + 86400);
        }
        return $model->count();
    }
}