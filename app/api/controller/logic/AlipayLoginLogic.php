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
use app\shop\model\applist\Applist as ApplistModel;

//require $_SERVER ['DOCUMENT_ROOT'].'extend/aop/AopClient.php';
//require $_SERVER ['DOCUMENT_ROOT'].'extend/aop/request/AlipaySystemOauthTokenRequest.php';
//require $_SERVER ['DOCUMENT_ROOT'].'extend/aop/request/AlipayUserInfoShareRequest.php';
require '/www/wwwroot/'.Env::get('url.url', 'yuzhou.haidiao888.com').'/extend/aop/AopClient.php';
require '/www/wwwroot/'.Env::get('url.url', 'yuzhou.haidiao888.com').'/extend/aop/request/AlipaySystemOauthTokenRequest.php';
require '/www/wwwroot/'.Env::get('url.url', 'yuzhou.haidiao888.com').'/extend/aop/request/AlipayUserInfoShareRequest.php';

/**
 * Notes: 支付宝小程序登录
 */
class AlipayLoginLogic extends LogicBase
{
    public function ce(){
        halt($_SERVER ['DOCUMENT_ROOT']);
    }

    /**
     * Notes: 旧用户登录
     * @param $post
     * @author ljj(2021/6/19 16:57)
     * @return array
     */
    public static function mnpAlipaySilentLogin($post)
    {
        try {
            $aop = new \aop\AopClient();
            $aop->gatewayUrl = Db::name('config')->where('type','=','ali_mnp')->where('name','=','ali_gateway_url')->value('value');
            $aop->appId = Db::name('config')->where('type','=','ali_mnp')->where('name','=','ali_app_id')->value('value');
            $aop->rsaPrivateKey = Db::name('config')->where('type','=','ali_mnp')->where('name','=','ali_rsa_privatekey')->value('value');
            $aop->alipayrsaPublicKey = Db::name('config')->where('type','=','ali_mnp')->where('name','=','ali_alipayrsa_publickey')->value('value');
            $aop->apiVersion = '1.0';
            $aop->signType = 'RSA2';
            $aop->postCharset='utf-8';
            $aop->format='json';

            $request = new \aop\request\AlipaySystemOauthTokenRequest();
            $request->setGrantType("authorization_code");
            $request->setCode($post['code']);
            $result = $aop->execute($request);

            $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
            $userid = $result->$responseNode->user_id ?? '';
            $resultCode = $result->$responseNode->code ?? '';
            if (!$userid) {
                throw new Exception($result->error_response->sub_msg);
            }
            if(!empty($resultCode) && $resultCode == 10000){
                throw new Exception($result->$responseNode->sub_msg);
            }

        } catch (Exception $e) {
            return self::dataError('登录失败:' . $e->getMessage());
        }

        $response = json_decode(json_encode($result->$responseNode),true);
        $response['openid'] = $response['user_id'];
        //添加或更新用户
        $user_id = Db::name('user')
            ->where(['del' => 0])
            ->where(['openid' => $userid])
            ->value('user_id');

        if (empty($user_id)) {
            //系统中没有用户-调用mnpAlipayauthLogin接口生成新用户
            return self::dataSuccess('', []);
        } else {
            $user_info = User::updateUser($response, Client_::ali_mnp, $user_id);
        }

        //验证用户信息
        if ($user_info['disable']) {
            return self::dataError('该用户被禁用');
        }
        if (empty($user_info)) {
            return self::dataError('登录失败:user');
        }

        //创建会话
//        $user_info['token'] = self::createSession($user_info['id'], Client_::ali_mnp, $response);
        $model = new UserOpenModel;
        $user_id = $model->login((array)$user_info, $referee_id=0);
        unset($user_info['id']);
        unset($user_info['disable']);
        return self::dataSuccess('登录成功', $user_info);
    }

    public static function pay(){
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
        $object->out_trade_no = '20210823010101009796654';
        $object->total_amount = 0.01;
        $object->subject = '测试商品';
        $object->buyer_id ='2088622120794003';
        $object->timeout_express = '10m';
////商品信息明细，按需传入
// $goodsDetail = [
//     [
//         'goods_id'=>'goodsNo1',
//         'goods_name'=>'子商品1',
//         'quantity'=>1,
//         'price'=>0.01,
//     ],
// ];
// $object->goodsDetail = $goodsDetail;
// //扩展信息，按需传入
// $extendParams = [
//     'sys_service_provider_id'=>'2088511833207846',
// ];
//  $object->extend_params = $extendParams;
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
// $businessParams = [
//     'busi_params_key'=>'busiParamsValue',
// ];
// $object->business_params= $businessParams;
// // 营销信息，按需传入
// $promoParams = [
//     'promo_params_key'=>'promoParamsValue'
// ];
// $object->promoParams = $promoParams;
        $json = json_encode($object);
        $request = new \aop\request\AlipayTradeCreateRequest();
        $request->setNotifyUrl('');
        $request->setBizContent($json);
        $result = $aop->execute ($request);
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;
        if(!empty($resultCode)&&$resultCode == 10000){
             return  $result;
//            echo "成功";
        } else {
            echo "失败";
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
        $object->out_trade_no = '20210823010101001b212';
//$object->trade_no = '2014112611001004680073956707';
        $json = json_encode($object);
        $request = new \aop\request\AlipayTradeQueryRequest();
        $request->setBizContent($json);
        $result = $aop->execute ( $request);
        print_r($result);die;
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;
        if(!empty($resultCode)&&$resultCode == 10000){
            echo "成功";
        } else {
            echo "失败";
        }
    }
    /**
     * Notes: 新用户登录
     * @param $post
     * @author ljj(2021/6/19 16:57)
     * @return array
     */
    public static function mnpAlipayAuthLogin($post)
    {


        try {
            if (!empty($post['appid'])){
                $appid = $post['appid'];
                $data = ApplistModel::detail(['appid'=>$appid]);
                if (!$data){
                    return self::dataError('登录失败:appid错误');
                }
                $appid_id = $data['id'];
            }else{
                $appid_id = 1;
                $appid = Db::name('config')->where('type','=','ali_mnp')->where('name','=','ali_app_id')->where('appid_id', '=', $appid_id)->value('value');
            }
            $aop = new \aop\AopClient();
            $aop->gatewayUrl = Db::name('config')->where('type','=','ali_mnp')->where('name','=','ali_gateway_url')->where('appid_id', '=', $appid_id)->value('value');
            $aop->appId = $appid;
            $aop->rsaPrivateKey = Db::name('config')->where('type','=','ali_mnp')->where('name','=','ali_rsa_privatekey')->where('appid_id', '=', $appid_id)->value('value');
            $aop->alipayrsaPublicKey = Db::name('config')->where('type','=','ali_mnp')->where('name','=','ali_alipayrsa_publickey')->where('appid_id', '=', $appid_id)->value('value');
            $aop->signType = 'RSA2';
            $aop->postCharset='utf-8';
            $aop->format='json';

            $request = new \aop\request\AlipaySystemOauthTokenRequest();
            $request->setGrantType("authorization_code");
            $request->setCode($post['code']);
            $result = $aop->execute($request);
            $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
            $userid = $result->$responseNode->user_id ?? '';
            $resultCode = $result->$responseNode->code ?? '';
            if (!$userid) {
                throw new Exception($result->error_response->sub_msg);
            }
            if(!empty($resultCode) && $resultCode == 10000){
                throw new Exception($result->$responseNode->sub_msg);
            }

        } catch (Exception $e) {
            return self::dataError('登录失败:' . $e->getMessage());
        }

        $response = json_decode(json_encode($result->$responseNode),true);
        $response['openid'] = $response['user_id'];
        $response['headimgurl'] = $post['avatar'] ?? '';
        $response['nickname'] = $post['nickname'] ?? '';
        //添加或更新用户

        $user_id = Db::name('user')
            ->where(['open_id' => $response['openid']])
            ->where(['applet_id' => $appid_id])
            ->value('user_id');

        if (empty($user_id)) {
            $user_info = [];
            try {
                $openid = $response['openid'] ?? '';
                $unionid = $response['unionid'] ?? '';
                $avatar_url = $response['headimgurl'] ?? '';
                $nickname = $response['nickname'] ?? '';
                Db::startTrans();
                // 获取存储引擎
//                $config = [
//                    'default' => ConfigServer::get('storage', 'default', 'local'),
//                    'engine'  => ConfigServer::get('storage_engine')
//                ];
                $time   = time(); //创建时间
                $avatar = '';     //头像路径

                if (empty($avatar_url)) {
                    $avatar = '';
                } else {
                    $file_name = md5($openid . $time) . '.jpeg';
                    $avatar = download_file($avatar_url, 'uploads/user/avatar/', $file_name);
                }
                $data = [
                    'open_id'           => $openid,
                    'reg_source'        => Client_::ali_mnp,
                    'avatarUrl'         => $avatar,
                    'nickName'          => $nickname,
                    'create_time'       => $time,
                    'app_id'            => 10001,
                    'applet_id'         => $appid_id,
                ];
                if (empty($nickname)) {
                    $data['nickName'] = '用户'.mt_rand(10000,99999);
                }
                $user_id = Db::name('user')
                    ->insertGetId($data);
                Db::commit();
                $user_info = Db::name('user')
                    ->where(['user_id' => $user_id])
                    ->where(['applet_id' => $appid_id])
                    ->find();
//                if (empty($user_info['avatar'])) {
//                    $user_info['avatar'] = UrlServer::getFileUrl(ConfigServer::get('website', 'user_image', ''));
//                } else {
//                    $user_info['avatar'] = UrlServer::getFileUrl($user_info['avatar']);
//                }
            } catch (Exception $e) {
                Db::rollback();
                throw new Exception($e->getMessage());
            }

        } else {
            $time = time();
            try {
                $openid = $response['openid'] ?? '';
                $unionid = $response['unionid'] ?? '';
                $avatar_url = $response['headimgurl'] ?? '';
                $nickname = $response['nickname'] ?? '';
                Db::startTrans();
//                //ios,android
//                if (in_array($client, [Client_::ios, Client_::android])) {
//                    Db::name('user_auth')
//                        ->where(['openid' => $openid])
//                        ->update(['client' => $client]);
//                }
                //用户已存在，但是无该端的授权信息，保存数据
//                $user_auth_id = Db::name('user_auth')
//                    ->where(['user_id' => $user_id, 'openid' => $openid])
//                    ->value('id');
//
//                if (empty($user_auth_id)) {
//                    $data = [
//                        'create_time' => $time,
//                        'openid' => $openid,
//                        'unionid' => $unionid,
//                        'user_id' => $user_id,
//                        'client' => $client,
//                    ];
//                    Db::name('user_auth')
//                        ->insert($data);
//                }
                $user_info = Db::name('user')
                    ->where(['open_id' => $openid])
                    ->where(['applet_id' => $appid_id])
                    ->find();
                //之前无unionid需要更新
                if (empty($user_info['union_id']) && isset($unionid)) {
                    $data = [];
                    $data['union_id'] = $unionid;
                    $data['update_time'] = $time;
                    Db::name('user')
                        ->where(['user_id' => $user_info['user_id']])
                        ->update($data);
                }
                //无头像需要更新头像
                if (empty($user_info['avatar'])) {
//                    // 获取存储引擎
//                    $config = [
//                        'default' => ConfigServer::get('storage', 'default', 'local'),
//                        'engine'  => ConfigServer::get('storage_engine')
//                    ];
                    $avatar = '';     //头像路径
//                    if ($config['default'] == 'local') {
                        $file_name = md5($openid . $time) . '.jpeg';
                        $avatar = download_file($avatar_url, 'uploads/user/avatar/', $file_name);
//                    } else {
//                        $avatar = 'uploads/user/avatar/' . md5($openid . $time) . '.jpeg';
//                        $StorageDriver = new StorageDriver($config);
//                        if (!$StorageDriver->fetch($avatar_url, $avatar)) {
//                            throw new Exception( '头像保存失败:'. $StorageDriver->getError());
//                        }
//                    }
                    $data['avatarUrl'] = $avatar;
                    $data['update_time'] = $time;
                    $data['nickName'] = $nickname;
                    Db::name('user')
                        ->where(['user_id' => $user_info['user_id']])
                        ->update($data);
                }
                $user_info = Db::name('user')
                    ->where(['user_id' => $user_info['user_id']])
                    ->find();
//                if (empty($user_info['avatar'])) {
//                    $user_info['avatar'] = UrlServer::getFileUrl(ConfigServer::get('website', 'user_image', ''));
//                } else {
//                    $user_info['avatar'] = UrlServer::getFileUrl($user_info['avatar']);
//                }
                Db::commit();
            } catch (Exception $e) {
                Db::rollback();
                throw new Exception($e->getMessage());
            }
        }

        //验证用户信息
//        if ($user_info['disable']) {
//            return self::dataError('该用户被禁用');
//        }
        if (empty($user_info)) {
            return self::dataError('登录失败:user');
        }

        //创建会话
//        $user_info['token'] = self::createSession($user_info['user_id'], Client_::ali_mnp, $response);
        $model = new UserOpenModel;
        $user_id = $model->login((array)$user_info, $referee_id ='');


        $code= 1;
        $msg = '';
        $data = [
            'user_id' => $user_id,
            'token' => $model->getToken()
        ];
        return compact('code', 'msg', 'data');
    }

    /**
     * 创建会话
     * @param $user_id
     * @param $client
     * @param $response
     * @return string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public static function createSession($user_id, $client, $response)
    {

        //清除之前缓存
        $token = Db::name('session')
            ->where(['user_id' => $user_id, 'client' => $client])
            ->value('token');

        if($token) {
            $token_cache = new TokenCache($token);

            $token_cache->del();
        }
        $result = Db::name('session')
            ->where(['user_id' => $user_id, 'client' => $client])
            ->find();

        $time = time();
        $expire_time = $time + $response['expires_in'];
        $token = md5($response['access_token']);
        $data = [
            'user_id' => $user_id,
            'token' => $token,
            'client' => $client,
            'update_time' => $time,
            'expire_time' => $expire_time,
        ];

        if (empty($result)) {
            Db::name('session')->insert($data);
        } else {
            Db::name('session')
                ->where(['user_id' => $user_id, 'client' => $client])
                ->update($data);
        }
        //更新登录信息
//        $login_ip = $ip = request()->ip();
//        Db::name('user')
//            ->where(['id' => $user_id])
//            ->update(['login_time' => $time, 'login_ip' => $login_ip]);
        //创建新的缓存
        (new TokenCache($token, ['token' => $token]))->set(300);
        return $token;
    }


    /**
     * @notes 支付宝生活号登陆
     * @param $get
     * @return array
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     * @author ljj
     * @date 2021/7/26 4:52 下午
     */
    public static function h5AlipayAuthLogin($post)
    {
        try {
            $aop = new \aop\AopClient();
            $aop->gatewayUrl = Db::name('config')->where('type','=','ali_mnp')->where('name','=','ali_gateway_url')->value('value');
            $aop->appId = Db::name('config')->where('type','=','ali_mnp')->where('name','=','ali_app_id')->value('value');
            $aop->rsaPrivateKey = Db::name('config')->where('type','=','ali_mnp')->where('name','=','ali_rsa_privatekey')->value('value');
            $aop->alipayrsaPublicKey = Db::name('config')->where('type','=','ali_mnp')->where('name','=','ali_alipayrsa_publickey')->value('value');
            $aop->apiVersion = '1.0';
            $aop->signType = 'RSA2';
            $aop->postCharset='utf-8';
            $aop->format='json';

            //换取 access_token 和 user_id
            $request = new \aop\request\AlipaySystemOauthTokenRequest();

            $request->setGrantType("authorization_code");
            $request->setCode($post['code']);
            $result = $aop->execute($request);
            $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
            $userid = $result->$responseNode->user_id ?? '';
            $resultCode = $result->$responseNode->code ?? '';
            $access_token = $result->$responseNode->access_token ?? '';
            $expires_in = $result->$responseNode->expires_in ?? '';
            if (!$userid) {
                throw new Exception($result->error_response->sub_msg);
            }
            if(!empty($resultCode) && $resultCode == 10000){
                throw new Exception($result->$responseNode->sub_msg);
            }

            //获取用户信息
            $request_a = new \AlipayUserInfoShareRequest();
            $result_a = $aop->execute ($request_a,$access_token); //这里传入获取的access_token
            $responseNode_a = str_replace(".", "_", $request_a->getApiMethodName()) . "_response";
            $user_id = $result_a->$responseNode_a->user_id;   //用户唯一id
            $headimgurl = $result_a->$responseNode_a->avatar;   //用户头像
            $nick_name = $result_a->$responseNode_a->nick_name;    //用户昵称

        } catch (Exception $e) {
            return self::dataError('登录失败:' . $e->getMessage());
        }

        $response['openid'] = $user_id;
        $response['headimgurl'] = $headimgurl ?? '';
        $response['nickname'] = $nick_name ?? '';
        $response['expires_in'] = $expires_in;
        $response['access_token'] = $access_token;
        //添加或更新用户
        $user_id = Db::name('user_auth au')
            ->join('user u', 'au.user_id=u.id')
            ->where(['u.del' => 0])
            ->where(['au.openid' => $user_id])
            ->value('user_id');

        if (empty($user_id)) {
            $user_info = User::createUser($response, Client_::h5);
        } else {
            $user_info = User::updateUser($response, Client_::h5, $user_id);
        }

        //验证用户信息
        if ($user_info['disable']) {
            return self::dataError('该用户被禁用');
        }
        if (empty($user_info)) {
            return self::dataError('登录失败:user');
        }

        //创建会话
        $user_info['token'] = self::createSession($user_info['id'], Client_::h5, $response);

        unset($user_info['id']);
        unset($user_info['disable']);
        return self::dataSuccess('登录成功', $user_info);
    }

    /**
     * 人脸认证
     */
    public static function faceAuthentication($outer_order_no,$cert_name,$cert_no,$url){
        $aop = new \aop\AopClient ();
        $aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
        $aop->appId = Db::name('config')->where('type','=','ali_mnp')->where('name','=','ali_app_id')->value('value');
        $aop->rsaPrivateKey = Db::name('config')->where('type','=','ali_mnp')->where('name','=','ali_rsa_privatekey')->value('value');
        $aop->alipayrsaPublicKey = Db::name('config')->where('type','=','ali_mnp')->where('name','=','ali_alipayrsa_publickey')->value('value');
        $aop->apiVersion = '1.0';
        $aop->signType = 'RSA2';
        $aop->postCharset='GBK';
        $aop->format='json';
        $request = new \aop\request\AlipayUserCertifyOpenInitializeRequest ();
        $request->setBizContent("{" .
            "  \"outer_order_no\":\"$outer_order_no\"," .
            "  \"biz_code\":\"FACE\"," .
            "  \"identity_param\":{\"identity_type\": \"CERT_INFO\", \"cert_type\":\"IDENTITY_CARD\", \"cert_name\":\"$cert_name\",\"cert_no\":\"$cert_no\",\"phone_no\":\"\"}," .
            "  \"merchant_config\":{\"return_url\":\"$url\"}" .
            "}");
        $result = $aop->execute ( $request);
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;
        if(!empty($resultCode)&&$resultCode == 10000){
            $certify_id = $result->$responseNode->certify_id;
            $data['code'] = 1;
            $data['url'] = self::faceAuthenticationStart($certify_id);
            $data['certify_id'] = $certify_id;
            return  $data;
        } else {
            return false;
        }
    }

    public static function faceAuthenticationStart($certify_id){
        $aop = new \aop\AopClient ();
        $aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
        $aop->appId = Db::name('config')->where('type','=','ali_mnp')->where('name','=','ali_app_id')->value('value');
        $aop->rsaPrivateKey = Db::name('config')->where('type','=','ali_mnp')->where('name','=','ali_rsa_privatekey')->value('value');
        $aop->alipayrsaPublicKey = Db::name('config')->where('type','=','ali_mnp')->where('name','=','ali_alipayrsa_publickey')->value('value');
        $aop->apiVersion = '1.0';
        $aop->signType = 'RSA2';
        $aop->postCharset='UTF-8';
        $aop->format='json';
        $request = new \aop\request\AlipayUserCertifyOpenCertifyRequest ();
        $request->setBizContent("{" .
            "  \"certify_id\":\"$certify_id\"" .
            "}");
        $result = $aop->pageExecute ($request,'GET');
        if ($result){
            return $result;
        }
        return false;

//        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
//        halt($result);
//        $resultCode = $result->$responseNode->code;

//        if(!empty($resultCode)&&$resultCode == 10000){
//            echo "成功";
//        } else {
//            echo "失败";
//        }
    }
}