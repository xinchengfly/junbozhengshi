<?php
// +----------------------------------------------------------------------
// | LikeShop100%开源免费商用电商系统
// +----------------------------------------------------------------------
// | 欢迎阅读学习系统程序代码，建议反馈是我们前进的动力
// | 开源版本可自由商用，可去除界面版权logo
// | 商业版本务必购买商业授权，以免引起法律纠纷
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | Gitee下载：https://gitee.com/likemarket/likeshopv2
// | 访问官网：https://www.likemarket.net
// | 访问社区：https://home.likemarket.net
// | 访问手册：http://doc.likemarket.net
// | 微信公众号：好象科技
// | 好象科技开发团队 版权所有 拥有最终解释权
// +----------------------------------------------------------------------

// | Author: LikeShopTeam
// +----------------------------------------------------------------------


namespace  app\api\controller\logic;

//use app\api\cache\TokenCache;
use aop\request\AlipaySystemOauthTokenRequest;
use app\api\cache\TokenCache;
use app\api\logic\DistributionLogic;
use app\api\model\user\User;
use app\common\library\agora\token\TokenService;
use app\common\logic\LogicBase;
//use app\common\server\ConfigServer;
use app\common\model\Client_;
use app\common\model\NoticeSetting;
use app\common\server\ConfigServer;
use app\common\server\storage\Driver as StorageDriver;
use app\common\server\UrlServer;
use think\facade\Cache;
use think\facade\Db;
use think\Exception;
use think\facade\Env;
use think\facade\Hook;
use app\api\model\user\UserOpen as UserOpenModel;
use app\common\model\order\Order as OrderModel;
use app\common\model\applist\Applist as ApplistModel;

//require $_SERVER ['DOCUMENT_ROOT'].'extend/aop/AopClient.php';
//require $_SERVER ['DOCUMENT_ROOT'].'extend/aop/request/AlipaySystemOauthTokenRequest.php';
//require $_SERVER ['DOCUMENT_ROOT'].'extend/aop/request/AlipayUserInfoShareRequest.php';
//require '/www/wwwroot/shumazulin.rchz.top/'.'extend/aop/AopClient.php';
require '/www/wwwroot/'.Env::get('url.url', 'yuzhou.haidiao888.com').'/extend/aop/request/AlipaySystemOauthTokenRequest.php';
require '/www/wwwroot/'.Env::get('url.url', 'yuzhou.haidiao888.com').'/extend/aop/request/AlipayUserInfoShareRequest.php';
require '/www/wwwroot/'.Env::get('url.url', 'yuzhou.haidiao888.com').'/extend/aop/request/AlipayTradePrecreateRequest.php';
require '/www/wwwroot/'.Env::get('url.url', 'yuzhou.haidiao888.com').'/extend/aop/request/AlipayTradeWapPayRequest.php';
require '/www/wwwroot/'.Env::get('url.url', 'yuzhou.haidiao888.com').'/extend/aop/request/AlipayFundAuthOrderAppFreezeRequest.php';

/**
 * Notes: 支付宝小程序登录
 */
class Pay extends LogicBase
{
    public static function pay($out_trade_no,$total_amount,$subject,$buyer_id,$notifyUrl){
        $orderInfo = OrderModel::detailByNo($out_trade_no);
        if ($orderInfo['collect_preferential'] != 0){
            $total_amount = $total_amount - $orderInfo['collect_preferential'];
            Db::name('user')->where('user_id', '=', $orderInfo['user_id'])->update(['collect' => 2]);
        }
        $appid = ApplistModel::detail($orderInfo['applet_id']);
        $aop = new \aop\AopClient ();
        $aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
        $aop->appId = $appid['appid'];
        $aop->rsaPrivateKey = Db::name('config')->where('type','=','ali_mnp')->where('name','=','ali_rsa_privatekey')->where('appid_id', '=', $orderInfo['applet_id'])->value('value');
        $aop->alipayrsaPublicKey = Db::name('config')->where('type','=','ali_mnp')->where('name','=','ali_alipayrsa_publickey')->where('appid_id', '=', $orderInfo['applet_id'])->value('value');
        $aop->apiVersion = '1.0';
        $aop->signType = 'RSA2';
        $aop->postCharset='GBK';
        $aop->format='json';
        $object = new \stdClass();
        $object->out_trade_no = $out_trade_no;
        $object->total_amount = $total_amount;
        $object->subject = '浙江宇洲信息科技有限公司';
        $object->buyer_id =$buyer_id;
        $object->timeout_express = '15d';

        //商品信息明细，按需传入
         $goodsDetail = [
             [
                 'goods_id'=>$orderInfo['product'][0]['product_id'],
                 'goods_name'=>$orderInfo['product'][0]['product_name'],
                 'quantity'=>1,
                 'price'=>$total_amount,
             ],
         ];
         $object->goods_detail = $goodsDetail;
// //扩展信息，按需传入
//     $extendParams = [
//         'hb_fq_num'=>'3',
//         'hb_fq_seller_percent'=>0,
//     ];
//      $object->extend_params = $extendParams;
// //结算信息，按需传入
// $settleInfo = [
//     'settle_detail_infos'=>[
//         [
//             'trans_in_type'=>'defaultSettle',
//             'amount'=>0.01,
//         ]
//     ]
// ];
// $object->settle_info = $settleInfo;
// //二级商户信息，按需传入
// $subMerchant = [
//     'merchant_id'=>'2088600522519475',
// ];
// $object->sub_merchant = $subMerchant;
// // 业务参数信息，按需传入
 $businessParams = [
     'delivery_promo_tags'=>$orderInfo['product'][0]['product_id'],
 ];
 $object->business_params= $businessParams;
// // 营销信息，按需传入
// $promoParams = [
//     'promo_params_key'=>'promoParamsValue'
// ];
// $object->promoParams = $promoParams;
        $json = json_encode($object);
//        dump($json);
//        exit();
        $request = new \aop\request\AlipayTradeCreateRequest();
        $request->setNotifyUrl($notifyUrl);
        $request->setBizContent($json);
        $result = $aop->execute ($request);

        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;
        if(!empty($resultCode)&&$resultCode == 10000){
            return  $result;
        } else {
            return false;
        }
    }

    public static function cea(){
        $aop = new \aop\AopClient ();
        $aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
        $aop->appId = Db::name('config')->where('type','=','ali_mnp')->where('name','=','ali_app_id')->value('value');
        $aop->rsaPrivateKey = Db::name('config')->where('type','=','ali_mnp')->where('name','=','ali_rsa_privatekey')->value('value');
        $aop->alipayrsaPublicKey = Db::name('config')->where('type','=','ali_mnp')->where('name','=','ali_alipayrsa_publickey')->value('value');
        $aop->apiVersion = '1.0';
        $aop->signType = 'RSA2';
        $aop->postCharset='GBK';
        $aop->format='json';
        $object = new \stdClass();
        $object->out_trade_no = '20210823010101001b213';
//$object->trade_no = '2014112611001004680073956707';
        $json = json_encode($object);
        $request = new \aop\request\AlipayTradeQueryRequest();
        $request->setBizContent($json);
        $result = $aop->execute ( $request);
//        print_r($result);die;
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;
        if(!empty($resultCode)&&$resultCode == 10000){
            echo "成功";
        } else {
            echo "失败";
        }
    }

    public static function huabei($out_trade_no,$total_amount,$subject,$buyer_id,$notifyUrl){
        $aop = new \aop\AopClient();
        $aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
        $aop->appId = Db::name('config')->where('type','=','ali_mnp')->where('name','=','ali_app_id')->value('value');
        $aop->rsaPrivateKey = Db::name('config')->where('type','=','ali_mnp')->where('name','=','ali_rsa_privatekey')->value('value');
        $aop->alipayrsaPublicKey = Db::name('config')->where('type','=','ali_mnp')->where('name','=','ali_alipayrsa_publickey')->value('value');
//        $aop->apiVersion = '1.0';
//        $aop->signType = 'RSA2';
//        $aop->postCharset='GBK';
//        $aop->format='json';
//        $object = new \stdClass();
//        $object->out_trade_no = $out_trade_no;
//        $object->total_amount = 0.01;
//        $object->subject = $subject;
//        $json = json_encode($object);
//        $request = new \AlipayTradePrecreateRequest();
//        $request->setNotifyUrl($notifyUrl);
//        $request->setBizContent($json);
//        $result = $aop->execute ( $request);
//
//        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
//        $resultCode = $result->$responseNode->code;
//        if(!empty($resultCode)&&$resultCode == 10000){
//            return  $result;
//        } else {
//            return false;
//        }


    }

    public static function freeze($out_trade_no,$total_amount,$subject,$buyer_id,$notifyUrl){
        $aop = new \aop\AopClient();
        $aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
        $aop->appId = Db::name('config')->where('type','=','ali_mnp')->where('name','=','ali_app_id')->value('value');
        $aop->rsaPrivateKey = Db::name('config')->where('type','=','ali_mnp')->where('name','=','ali_rsa_privatekey')->value('value');
        $aop->alipayrsaPublicKey = Db::name('config')->where('type','=','ali_mnp')->where('name','=','ali_alipayrsa_publickey')->value('value');
        $aop->apiVersion = '1.0';
        $aop->signType = 'RSA2';
        $aop->postCharset='GBK';
        $aop->format='json';

        $object = new \stdClass();
        $object->out_order_no = $out_trade_no;
        $object->out_request_no = $out_trade_no;
        $object->order_title = '预授权冻结';
        $object->amount = $total_amount;
        $object->product_code ='PRE_AUTH_ONLINE';
//        $object->enable_pay_channels ='[{"payChannelType":"MONEY_FUND"},{"payChannelType":"PCREDIT_PAY"},{"payChannelType":"CREDITZHIMA"}]';
//        $object->payee_user_id = $buyer_id;

        $json = json_encode($object);
        $request = new \AlipayFundAuthOrderAppFreezeRequest();
        $request->setNotifyUrl($notifyUrl);
        $request->setBizContent($json);

        $result = $aop->sdkExecute($request);
        return  $result;
    }

}