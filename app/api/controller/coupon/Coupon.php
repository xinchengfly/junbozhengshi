<?php

namespace app\api\controller\coupon;

use app\api\controller\Controller;
use app\api\model\plus\coupon\Coupon as CouponModel;
use app\api\model\product\Product as ProductModel;
use think\facade\Db;
use app\common\model\applist\Applist as ApplistModel;
use think\facade\Env;

require_once '/www/wwwroot/'.Env::get('url.url', 'yuzhou.haidiao888.com').'/extend/aop/AopClient.php';
require_once '/www/wwwroot/'.Env::get('url.url', 'yuzhou.haidiao888.com').'/extend/aop/request/AlipayMarketingActivityUserBatchqueryvoucherRequest.php';
require_once '/www/wwwroot/'.Env::get('url.url', 'yuzhou.haidiao888.com').'/extend/aop/request/AlipaySystemOauthTokenRequest.php';
require_once '/www/wwwroot/'.Env::get('url.url', 'yuzhou.haidiao888.com').'/extend/aop/request/AlipayMarketingActivityUserQueryvoucherRequest.php';
require_once '/www/wwwroot/'.Env::get('url.url', 'yuzhou.haidiao888.com').'/extend/aop/request/AlipayMarketingCampaignOrderVoucherConsultRequest.php';



/**
 * 优惠券中心
 */
class Coupon extends Controller
{
    /**
     * 优惠券列表
     */
    public function lists()
    {
        $model = new CouponModel;
        $list = $model->getWaitList([], $this->getUser(false), 0, 1);
        return $this->renderSuccess('', compact('list'));
    }

    public function detail($coupon_id)
    {
        $model = CouponModel::detailWithSupplier($coupon_id);
        if ($model['apply_range'] == 20) {
            $product_ids = explode(',', $model['product_ids']);
            $model['product'] = (new ProductModel())->getListByIdsFromApi($product_ids, $this->getUser(false));
        }
        $product_list = [];
        if ($model['apply_range'] == 30) {
            $category_ids = json_decode($model['category_ids'], true);
            $product_list = (new ProductModel())->getListByCatIdsFromApi($category_ids, $this->getUser(false));
        }
        return $this->renderSuccess('', compact('model', 'product_list'));
    }

    public function conf($appid)
    {
        if (!empty($appid)){
            $data = ApplistModel::detail(['appid'=>$appid]);
            if (!$data){
                return $this->renderError('appid错误');
            }
            $appid_id = $data['id'];
        }else{
            $appid_id = 1;
            $data = ApplistModel::detail($appid_id);
            $appid = $data['appid'];
        }
        $alipayConfig = new \aop\AopClient();
        $alipayConfig->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
        $alipayConfig->appId = $appid;
        $alipayConfig->rsaPrivateKey = Db::name('config')->where('type','=','ali_mnp')->where('name','=','ali_rsa_privatekey')->where('appid_id', '=', $appid_id)->value('value');
        $alipayConfig->alipayrsaPublicKey = Db::name('config')->where('type','=','ali_mnp')->where('name','=','ali_alipayrsa_publickey')->where('appid_id', '=', $appid_id)->value('value');
        $alipayConfig->apiVersion = '1.0';
        $alipayConfig->signType = 'RSA2';
        $alipayConfig->postCharset='GBK';
        $alipayConfig->format='json';
        return $alipayConfig;
    }

    //获取token
    public function getToken($code, $appid = '')
    {
        $aop = $this->conf($appid);
        $request = new \aop\request\AlipaySystemOauthTokenRequest();
        $request->setGrantType("authorization_code");
        $request->setCode($code);
//        $request->setRefreshToken("201208134b203fe6c11548bcabd8da5bb087a83b");
        $result = $aop->execute($request);
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        if (isset($result->error_response)){
            $data = $result->error_response;
            return $this->renderError('失败', compact('data'));
        }
        $data = $result->$responseNode;
        return $this->renderSuccess('', compact('data'));
    }

    //获取优惠券列表
    public function getList($access_token,$page_num = 1, $page_size = 20, $appid = '')
    {
        $aop = $this->conf($appid);
        // 当前用户信息
        $userInfo = json_decode($this->getUser(), true);
        $open_id = $userInfo['open_id'];
        $request = new \AlipayMarketingActivityUserBatchqueryvoucherRequest();
        $request->setBizContent("{" .
            "  \"user_id\":\"$open_id\"," .
//            "  \"activity_id\":\"2016042700826004508401111111\"," .
            "  \"belong_merchant_id\":\"2088531332645101\"," .
//            "  \"sender_merchant_id\":\"2088531332645101\"," .
//            "  \"voucher_status\":\"SENDED\"," .
            "  \"page_num\":$page_num," .
//            "  \"merchant_access_mode\":\"AGENCY_MODE\"," .
            "  \"page_size\":$page_size" .
            "}");
        $result = $aop->execute ($request, $access_token);
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;
        if(!empty($resultCode)&&$resultCode == 10000){
            $data = $result->$responseNode;
            return $this->renderSuccess('', compact('data'));
        } else {
            $data = $result->$responseNode;
            return $this->renderError('失败', compact('data'));
        }
    }

    //获取优惠券详情
    public function getDetail($voucher_id, $appid = '')
    {
        $aop = $this->conf($appid);
        // 当前用户信息
        $userInfo = json_decode($this->getUser(), true);
        $open_id = $userInfo['open_id'];
        $request = new \AlipayMarketingActivityUserQueryvoucherRequest();
        $request->setBizContent("{" .
            "  \"merchant_id\":\"2088531332645101\"," .
            "  \"user_id\":\"$open_id\"," .
//            "  \"activity_id\":\"2016042700826004508401111111\"," .
            "  \"voucher_id\":\"$voucher_id\"," .
//            "  \"voucher_code\":\"ABE44\"," .
            "  \"merchant_access_mode\":\"SELF_MODE\"" .
            "}");
        $result = $aop->execute($request);
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;
        if(!empty($resultCode)&&$resultCode == 10000){
            $data = $result->$responseNode;
            return $this->renderSuccess('', compact('data'));
        } else {
            $data = $result->$responseNode;
            return $this->renderError('失败', compact('data'));
        }
    }

    //订单优惠前置咨询接口
    public function consult($access_token, $order_amount, $item_id = '', $quantity = '', $price = '', $appid = '')
    {
        $aop = $this->conf($appid);
        $request = new \AlipayMarketingCampaignOrderVoucherConsultRequest ();
        $request->setBizContent("{" .
            "  \"scene_code\":[" .
            "    \"DEFAULT\"" .
            "  ]," .
            "  \"specified_app_id\":\"$aop->appId\"," .
            "  \"order_amount\":\"$order_amount\"," .
            "  \"item_consult_list\":[" .
            "    {" .
            "      \"item_id\":\"$item_id\"," .
            "      \"quantity\":\"$quantity\"," .
            "      \"price\":\"$price\"" .
            "    }" .
            "  ]," .
            "  \"business_param\":\"{\\\"useBigAmountSkipOrderThold\\\":\\\"N\\\"}\"" .
            "}");
        $result = $aop->execute ($request, $access_token);

        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;
        if(!empty($resultCode)&&$resultCode == 10000){
            $data = $result->$responseNode;
            return $this->renderSuccess('', compact('data'));
        } else {
            $data = $result->$responseNode;
            return $this->renderError('失败', compact('data'));
        }
    }
}