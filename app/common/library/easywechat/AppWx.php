<?php

namespace app\common\library\easywechat;

use EasyWeChat\Factory;
use app\common\exception\BaseException;
use app\common\model\app\AppWx as AppWxModel;
use app\common\model\app\App as AppModel;

/**
 * 微信小程序
 */
class AppWx
{
    public static function getApp($app_id = null)
    {
        // 获取当前小程序信息
        $wxConfig = AppWxModel::getAppWxCache($app_id);
        // 验证appid和appsecret是否填写
        if (empty($wxConfig['wxapp_id']) || empty($wxConfig['wxapp_secret'])) {
            throw new BaseException(['msg' => '请到 [后台-应用-小程序设置] 填写appid 和 appsecret']);
        }
        $config = [
            'app_id' => $wxConfig['wxapp_id'],
            'secret' => $wxConfig['wxapp_secret'],
            'response_type' => 'array',
        ];
        return Factory::miniProgram($config);
    }

    public static function getWxPayApp($app_id)
    {
        // 获取当前小程序信息
        $wxConfig = AppWxModel::getAppWxCache($app_id);
        // 验证appid和appsecret是否填写
        if (empty($wxConfig['wxapp_id']) || empty($wxConfig['wxapp_secret'])) {
            throw new BaseException(['msg' => '请到 [后台-应用-小程序设置] 填写appid 和 appsecret']);
        }

        $app = AppModel::detail($app_id);
        if (empty($app['cert_pem']) || empty($app['key_pem'])) {
            throw new BaseException(['msg' => '请先到后台[应用->支付设置]填写微信支付证书文件']);
        }
        // cert目录
        $filePath = root_path() . 'runtime/cert/app/' . $wxConfig['app_id'] . '/';

        $config = [
            'app_id' => $wxConfig['wxapp_id'],
            'mch_id' => $app['mchid'],
            'key' => $app['apikey'],   // API 密钥
            // 如需使用敏感接口（如退款、发送红包等）需要配置 API 证书路径(登录商户平台下载 API 证书)
            'cert_path' => $filePath . 'cert.pem',
            'key_path' => $filePath . 'key.pem',
            'sandbox' => false, // 设置为 false 或注释则关闭沙箱模式
        ];
        return Factory::payment($config);
    }

    /**
     * 获取session_key
     * @param $code
     * @return array|mixed
     */
    public static function sessionKey($app, $code)
    {
        /**
         * code 换取 session_key
         * ​这是一个 HTTPS 接口，开发者服务器使用登录凭证 code 获取 session_key 和 openid。
         * 其中 session_key 是对用户数据进行加密签名的密钥。为了自身应用安全，session_key 不应该在网络上传输。
         */
        $url = 'https://api.weixin.qq.com/sns/jscode2session';
        $result = json_decode(curl($url, [
            'appid' => $app['config']['app_id'],
            'secret' => $app['config']['secret'],
            'grant_type' => 'authorization_code',
            'js_code' => $code
        ]), true);
        if (isset($result['errcode'])) {
            return false;
        }
        return $result;
    }
}
