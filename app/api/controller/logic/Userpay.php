<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2023/4/26
 * Time: 11:42
 */

namespace app\api\controller\logic;

use think\facade\Db;

require_once '/www/wwwroot/yuzhou.haidiao888.com/'.'extend/aop/AopClient.php';
require_once '/www/wwwroot/yuzhou.haidiao888.com/'.'extend/aop/AopCertClient.php';
require_once '/www/wwwroot/yuzhou.haidiao888.com/'.'extend/aop/AopCertification.php';
require_once '/www/wwwroot/yuzhou.haidiao888.com/'.'extend/aop/AlipayConfig.php';
require_once '/www/wwwroot/yuzhou.haidiao888.com/'.'extend/aop/request/AlipayFundTransUniTransferRequest.php';
class Userpay
{
    public static function transferAccountsUser($open_id)
    {
        $aop = new \aop\AopClient ();
        $aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
        $aop->appId = '2021003183680107';
        $aop->rsaPrivateKey = Db::name('config')->where('type','=','ali_mnp')->where('name','=','ali_rsa_privatekey')->value('value');
        $aop->alipayrsaPublicKey = Db::name('config')->where('type','=','ali_mnp')->where('name','=','ali_alipayrsa_publickey')->value('value');
        $aop->apiVersion = '1.0';
        $aop->signType = 'RSA2';
        $aop->postCharset='GBK';
        $aop->format='json';
        $alipayClient = new \AopCertClient($aop);
        $alipayClient->isCheckAlipayPublicCert = true;
        $request = new \AlipayFundTransUniTransferRequest();
        $request->setBizContent("{" .
            "\"out_biz_no\":\"201806300001\"," .
            "\"remark\":\"201905代发\"," .
            "\"business_params\":\"{\\\"payer_show_name_use_alias\\\":\\\"true\\\"}\"," .
            "\"biz_scene\":\"DIRECT_TRANSFER\"," .
            "\"payee_info\":{" .
            "\"identity\":\"$open_id\"," .
            "\"identity_type\":\"ALIPAY_USER_ID\"," .
            "\"name\":\"牛梦涵\"" .
            "}," .
            "\"trans_amount\":\"0.01\"," .
            "\"product_code\":\"TRANS_ACCOUNT_NO_PWD\"," .
            "\"order_title\":\"201905代发\"" .
            "}");
        $responseResult = $alipayClient->execute($request);
        $responseApiName = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $response = $responseResult->$responseApiName;
        if (!empty($response->code) && $response->code == 10000) {
            echo("调用成功");
        } else {
            echo("调用失败");
        }
    }
}