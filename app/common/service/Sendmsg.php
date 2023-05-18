<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2023/3/29
 * Time: 17:05
 */

namespace app\common\Service;

use think\facade\Db;
use think\facade\Env;

require '/www/wwwroot/'.Env::get('url.url', 'yuzhou.haidiao888.com').'/extend/aop/AopClient.php';
require '/www/wwwroot/'.Env::get('url.url', 'yuzhou.haidiao888.com').'/extend/aop/request/AlipayFundAuthOrderUnfreezeRequest.php';
require '/www/wwwroot/'.Env::get('url.url', 'yuzhou.haidiao888.com').'/extend/aop/request/AlipayTradePayRequest.php';
require '/www/wwwroot/'.Env::get('url.url', 'yuzhou.haidiao888.com').'/extend/aop/request/AlipayTradeRefundRequest.php';
require '/www/wwwroot/'.Env::get('url.url', 'yuzhou.haidiao888.com').'/extend/aop/request/AlipayOpenAppMiniTemplatemessageSendRequest.php';

class Sendmsg
{
    //发送订阅消息
    public function sendmsg($userid,$tmpid,$keyword)
    {
        $aop = new \aop\AopClient();
        $aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
        $aop->appId = Db::name('config')->where('type','=','ali_mnp')->where('name','=','ali_app_id')->value('value');
        $aop->rsaPrivateKey = Db::name('config')->where('type','=','ali_mnp')->where('name','=','ali_rsa_privatekey')->value('value');
        $aop->alipayrsaPublicKey = Db::name('config')->where('type','=','ali_mnp')->where('name','=','ali_alipayrsa_publickey')->value('value');
        $aop->signType = 'RSA2';
        $aop->postCharset='UTF8';
        $aop->format='json';
        $object = new \stdClass();
        $object->data = $keyword;;
        $object->page = 'pages/order/myorder';
        $object->user_template_id = $tmpid;
        $object->to_user_id = $userid;
        $json = json_encode($object);
        $request = new \AlipayOpenAppMiniTemplatemessageSendRequest();
        $request->setBizContent($json);
        $result = $aop->execute ( $request);
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;
        if(!empty($resultCode)&&$resultCode == 10000){
        } else {
        }
    }
}