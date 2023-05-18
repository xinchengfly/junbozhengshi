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


namespace app\api\login;

//use app\api\cache\TokenCache;
//use app\api\server\UserServer;
use app\common\login\LogicBase;
//use app\common\model\Client_;
//use app\common\server\ConfigServer;
use think\Db;
use think\Exception;
use think\facade\Env;

//require Env::get('root_path').'extend/aop/AopClient.php';
//require Env::get('root_path').'extend/aop/request/AlipaySystemOauthTokenRequest.php';
//require Env::get('root_path').'extend/aop/request/AlipayUserInfoShareRequest.php';

/**
 * Notes: 支付宝小程序登录
 */
class AlipayLoginLogic
{
    public function ce(){
        halt(1);
        halt(Env::get('root_path'));
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
            $aop = new \aopClient();
            $aop->gatewayUrl = ConfigServer::get('ali_mnp', 'ali_gateway_url');
            $aop->appId = ConfigServer::get('ali_mnp', 'ali_app_id' );
            $aop->rsaPrivateKey = ConfigServer::get('ali_mnp', 'ali_rsa_privatekey');
            $aop->alipayrsaPublicKey = ConfigServer::get('ali_mnp', 'ali_alipayrsa_publickey');
            $aop->apiVersion = '1.0';
            $aop->signType = 'RSA2';
            $aop->postCharset='utf-8';
            $aop->format='json';

            $request = new \AlipaySystemOauthTokenRequest();
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
        $user_id = Db::name('user_auth au')
            ->join('user u', 'au.user_id=u.id')
            ->where(['u.del' => 0])
            ->where(['au.openid' => $userid])
            ->value('user_id');

        if (empty($user_id)) {
            //系统中没有用户-调用mnpAlipayauthLogin接口生成新用户
            return self::dataSuccess('', []);
        } else {
            $user_info = UserServer::updateUser($response, Client_::ali_mnp, $user_id);
        }

        //验证用户信息
        if ($user_info['disable']) {
            return self::dataError('该用户被禁用');
        }
        if (empty($user_info)) {
            return self::dataError('登录失败:user');
        }

        //创建会话
        $user_info['token'] = self::createSession($user_info['id'], Client_::ali_mnp, $response);

        unset($user_info['id']);
        unset($user_info['disable']);
        return self::dataSuccess('登录成功', $user_info);
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
            $aop = new \aopClient();
            $aop->gatewayUrl = ConfigServer::get('ali_mnp', 'ali_gateway_url');
            $aop->appId = ConfigServer::get('ali_mnp', 'ali_app_id' );
            $aop->rsaPrivateKey = ConfigServer::get('ali_mnp', 'ali_rsa_privatekey');
            $aop->alipayrsaPublicKey = ConfigServer::get('ali_mnp', 'ali_alipayrsa_publickey');
            $aop->apiVersion = '1.0';
            $aop->signType = 'RSA2';
            $aop->postCharset='utf-8';
            $aop->format='json';

            $request = new \AlipaySystemOauthTokenRequest();
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
        $user_id = Db::name('user_auth au')
            ->join('user u', 'au.user_id=u.id')
            ->where(['u.del' => 0])
            ->where(['au.openid' => $userid])
            ->value('user_id');

        if (empty($user_id)) {
            $user_info = UserServer::createUser($response, Client_::ali_mnp);
        } else {
            $user_info = UserServer::updateUser($response, Client_::ali_mnp, $user_id);
        }

        //验证用户信息
        if ($user_info['disable']) {
            return self::dataError('该用户被禁用');
        }
        if (empty($user_info)) {
            return self::dataError('登录失败:user');
        }

        //创建会话
        $user_info['token'] = self::createSession($user_info['id'], Client_::ali_mnp, $response);

        unset($user_info['id']);
        unset($user_info['disable']);
        return self::dataSuccess('登录成功', $user_info);
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
        $login_ip = $ip = request()->ip();
        Db::name('user')
            ->where(['id' => $user_id])
            ->update(['login_time' => $time, 'login_ip' => $login_ip]);

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
            $aop = new \aopClient();
            $aop->gatewayUrl = ConfigServer::get('ali_h5', 'ali_gateway_url');
            $aop->appId = ConfigServer::get('ali_h5', 'ali_app_id' );
            $aop->rsaPrivateKey = ConfigServer::get('ali_h5', 'ali_rsa_privatekey');
            $aop->alipayrsaPublicKey = ConfigServer::get('ali_h5', 'ali_alipayrsa_publickey');
            $aop->apiVersion = '1.0';
            $aop->signType = 'RSA2';
            $aop->postCharset='utf-8';
            $aop->format='json';

            //换取 access_token 和 user_id
            $request = new \AlipaySystemOauthTokenRequest();
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
            $user_info = UserServer::createUser($response, Client_::h5);
        } else {
            $user_info = UserServer::updateUser($response, Client_::h5, $user_id);
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

}