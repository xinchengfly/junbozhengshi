<?php

namespace app\api\controller;

use app\api\model\settings\Setting as SettingModel;
use app\common\model\app\AppOpen as AppOpenModel;
use app\common\model\settings\Region as RegionModel;

/**
 * 页面控制器
 */
class Settings extends Controller
{

    // app分享
    public function appShare()
    {
        // 分享设置
        $appshare = SettingModel::getItem('appshare');
        // logo
        $logo = AppOpenModel::detail()['logo'];
        return $this->renderSuccess('', compact('appshare', 'logo'));
    }

    /**
     * 获取省市区
     */
    public function getRegion(){
        $regionData = RegionModel::getRegionForApi();
        return $this->renderSuccess('', compact('regionData'));
    }

    public function store($delivery_reminder = '')
    {
        $store = SettingModel::getItem('store');
        if (!empty($delivery_reminder)){
            $store = $store[$delivery_reminder];
        }
        return $this->renderSuccess('', compact('store'));
    }
}
