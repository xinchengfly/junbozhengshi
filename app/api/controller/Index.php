<?php

namespace app\api\controller;

use app\api\controller\logic\AlipayLoginLogic;
use app\api\model\page\Page as AppPage;
use app\api\model\settings\Setting as SettingModel;
use app\common\enum\settings\SettingEnum;
use app\common\model\app\AppUpdate as AppUpdateModel;
use app\common\model\supplier\Service as ServiceModel;
use app\api\model\plus\chat\Chat as ChatModel;
use esign\emun\HttpEmun;

/**
 * 页面控制器
 */
class Index extends Controller
{

    public function ce(){
        $header=[

        ];
        $data=[];
        $res = send_post('https://smlopenapi.esign.cn/v3/files/file-upload-url',$data,$header);
        halt($res);
    }
    /**
     * 首页
     */
    public function index($page_id = null, $url = '', $appid = '')
    {
        // 页面元素
        $data = AppPage::getPageData($this->getUser(false), $page_id, $appid);
        //消息条数
        $Chat = new ChatModel;
        $data['msgNum'] = $Chat->mCount($this->getUser(false));
        $data['setting'] = array(
            'collection' => SettingModel::getItem('collection'),
            'officia' => SettingModel::getItem('officia'),
            'homepush' => SettingModel::getItem('homepush'),
        );
        // 扫一扫参数
        $data['signPackage'] = $this->getScanParams($url)['signPackage'];
        return $this->renderSuccess('', $data);
    }
    /**
     * 首页
     */
    public function diy($page_id = null, $url = '')
    {
        // 页面元素
        $data = AppPage::getPageData($this->getUser(false), $page_id);
        // 微信公众号分享参数
        $data['share'] = $this->getShareParams($url, $data['page']['params']['share_title'], $data['page']['params']['share_title'], '/pages/diy-page/diy-page');
        return $this->renderSuccess('', $data);
    }
    // 公众号客服
    public function mpService($shop_supplier_id)
    {
        $mp_service = ServiceModel::detail($shop_supplier_id);
        return $this->renderSuccess('', compact('mp_service'));
    }

    //底部导航
    public function nav()
    {
        $data['vars'] = SettingModel::getItem(SettingEnum::NAV);
        $data['theme'] = SettingModel::getItem(SettingEnum::THEME);
        return $this->renderSuccess('', $data);
    }

    // app更新
    public function update($name, $version, $platform)
    {
        $result = [
            'update' => false,
            'wgtUrl' => '',
            'pkgUrl' => '',
        ];
        try {
            $model = AppUpdateModel::getLast();
            if($platform == 'android'){
                $compare_version = $model['version_android'];
            }else{
                $compare_version = $model['version_ios'];
            }
            // 这里简单判定下，不相等就是有更新。
            if($model && $version != $compare_version){
                $currentVersions = explode('.', $version);
                $resultVersions = explode('.', $compare_version);

                if ($currentVersions[0] < $resultVersions[0]) {
                    // 说明有大版本更新
                    $result['update'] = true;
                    $result['pkgUrl'] = $platform == 'android' ? $model['pkg_url_android'] : $model['pkg_url_ios'];
                    log_write('大版本');
                } else {
                    // 其它情况均认为是小版本更新
                    $result['update'] = true;
                    $result['wgtUrl'] = $model['wgt_url'];
                    log_write('小版本' . $result['wgtUrl']);
                }
            }
        } catch (\Exception $e) {

        }
        return $this->renderSuccess('', compact('result'));
    }

}
