<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2023/4/27
 * Time: 17:34
 */

namespace app\api\controller\order;

use app\api\controller\Controller;
use think\facade\Env;
use think\facade\Db;
use app\common\model\order\Order as OrderModel;
use app\common\model\applist\Applist;

require_once '/www/wwwroot/'.Env::get('url.url', 'yuzhou.haidiao888.com').'/extend/aop/AopClient.php';
require_once '/www/wwwroot/'.Env::get('url.url', 'yuzhou.haidiao888.com').'/extend/aop/AopCertClient.php';
require_once '/www/wwwroot/'.Env::get('url.url', 'yuzhou.haidiao888.com').'/extend/aop/AopCertification.php';
require_once '/www/wwwroot/'.Env::get('url.url', 'yuzhou.haidiao888.com').'/extend/aop/AlipayConfig.php';
require_once '/www/wwwroot/'.Env::get('url.url', 'yuzhou.haidiao888.com').'/extend/aop/request/AlipayMerchantItemFileUploadRequest.php';
require_once '/www/wwwroot/'.Env::get('url.url', 'yuzhou.haidiao888.com').'/extend/aop/request/AntMerchantExpandItemOpenCreateRequest.php';
require_once '/www/wwwroot/'.Env::get('url.url', 'yuzhou.haidiao888.com').'/extend/aop/request/AlipayMerchantOrderSyncRequest.php';
require_once '/www/wwwroot/'.Env::get('url.url', 'yuzhou.haidiao888.com').'/extend/aop/request/AlipayOfflineMaterialImageUploadRequest.php';
require_once '/www/wwwroot/'.Env::get('url.url', 'yuzhou.haidiao888.com').'/extend/aop/request/AlipayMarketingCardTemplateCreateRequest.php';
require_once '/www/wwwroot/'.Env::get('url.url', 'yuzhou.haidiao888.com').'/extend/aop/request/AlipayMarketingCardOpenRequest.php';
require_once '/www/wwwroot/'.Env::get('url.url', 'yuzhou.haidiao888.com').'/extend/aop/request/AlipaySystemOauthTokenRequest.php';
require_once '/www/wwwroot/'.Env::get('url.url', 'yuzhou.haidiao888.com').'/extend/aop/request/AlipayMarketingCardUpdateRequest.php';
require_once '/www/wwwroot/'.Env::get('url.url', 'yuzhou.haidiao888.com').'/extend/aop/request/AlipayMarketingCardQueryRequest.php';
require_once '/www/wwwroot/'.Env::get('url.url', 'yuzhou.haidiao888.com').'/extend/aop/request/AlipayMarketingCardFormtemplateSetRequest.php';
require_once '/www/wwwroot/'.Env::get('url.url', 'yuzhou.haidiao888.com').'/extend/aop/request/AlipayMarketingCardTemplateQueryRequest.php';
require_once '/www/wwwroot/'.Env::get('url.url', 'yuzhou.haidiao888.com').'/extend/aop/request/AlipayMarketingCardActivateurlApplyRequest.php';
require_once '/www/wwwroot/'.Env::get('url.url', 'yuzhou.haidiao888.com').'/extend/aop/request/AlipayMarketingCardActivateformQueryRequest.php';
require_once '/www/wwwroot/'.Env::get('url.url', 'yuzhou.haidiao888.com').'/extend/aop/request/AlipayMarketingCardTemplateModifyRequest.php';



class OrderCenter extends Controller
{

    public $applet_id = 1;
    public $accessToken = 'composeB79788f6e38da48d0b6d4148f0e79dX72';
    //配置
    public function conf($applet_id)
    {
        $alipayClient = new \aop\AopClient();
        $alipayClient->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
        $alipayClient->appId = Db::name('config')->where('type','=','ali_mnp')->where('name','=','ali_app_id')->where('appid_id', '=', $applet_id)->value('value');
        $alipayClient->rsaPrivateKey = Db::name('config')->where('type','=','ali_mnp')->where('name','=','ali_rsa_privatekey')->where('appid_id', '=', $applet_id)->value('value');
        $alipayClient->alipayrsaPublicKey = Db::name('config')->where('type','=','ali_mnp')->where('name','=','ali_alipayrsa_publickey')->where('appid_id', '=', $applet_id)->value('value');
        $alipayClient->apiVersion = '1.0';
        $alipayClient->signType = 'RSA2';
        $alipayClient->postCharset='GBK';
        $alipayClient->format='json';
        return $alipayClient;
    }

    //同步订单方法
    public function index2($order_id, $states = 1)
    {
        $orderModel = new OrderModel();
        $biz_content = $orderModel->getBizContent($order_id, $states);
        $alipayClient = $this->conf($biz_content['applet_id']);
        unset($biz_content['applet_id']);
        $request = new \AlipayMerchantOrderSyncRequest();
        $biz_content = json_encode($biz_content, true);
        $request->setBizContent($biz_content);
        $responseResult = $alipayClient->execute($request);
        $responseApiName = str_replace(".","_",$request->getApiMethodName())."_response";
        $response = $responseResult->$responseApiName;
        if(!empty($response->code)&&$response->code==10000){
            return $this->renderSuccess('成功', compact('responseResult'));
        }
        else{
            return $this->renderError('失败', compact('responseResult'));
        }
    }

    //同步订单上传图片
    public function index()
    {
        $applet_id = $this->applet_id;
        $alipayClient = $this->conf($applet_id);
        $request = new \AlipayMerchantItemFileUploadRequest();
        $request->setScene("SYNC_ORDER");
//请填写需要上传的文件路径 如：@/home/admin/test.jpg
        $request->setFileContent("@".'/www/wwwroot/'.Env::get('url.url', 'yuzhou.haidiao888.com').'/'.'public/image/agent/junbo_logo_600x600.png');
        $responseResult = $alipayClient->execute($request);
        dump($responseResult);
        $responseApiName = str_replace(".","_",$request->getApiMethodName())."_response";
        $response = $responseResult->$responseApiName;
        if(!empty($response->code)&&$response->code==10000){
            echo("调用成功");
        }
        else{
            echo("调用失败");
        }
        return 123;
    }

    //TODO(会员卡模块)
    //会员卡上传图片
    public function xxx()
    {
        $applet_id = $this->applet_id;
        $alipayClient = $this->conf($applet_id);
        $request = new \AlipayOfflineMaterialImageUploadRequest();
        $request->setImageType("png");
        $request->setImageName("海底捞1");
        $request->setImageContent("@".'/www/wwwroot/'.Env::get('url.url', 'yuzhou.haidiao888.com').'/'.'public/image/agent/junbo_logo_600x600.png');
        $request->setImagePid("2088021822217233");
        $result = $alipayClient->execute( $request);
        dump($result);
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;
        if(!empty($resultCode)&&$resultCode == 10000){
            echo "成功";
        } else {
            echo "失败";
        }
    }

    //会员卡模板创建
    public function create()
    {
        $applet_id = $this->applet_id;
        $alipayClient = $this->conf($applet_id);
        $app_data = Applist::detail($applet_id);
        $appid = $app_data['appid'];
        $appname = $app_data['name'].'卡';
        $biz_content = [
            'request_id' => '202305080000144638',
            'card_type' => 'OUT_MEMBER_CARD',
            'biz_no_prefix' => 'wyzj',
            'biz_no_suffix_len' => '20',
            'write_off_type' => 'qrcode',
            'template_style_info' => [
                'card_show_name' => $appname,
                'logo_id' => 'A*85W8RZFbcy0AAAAAAAAAAAAABKd2AQ',
                'background_id' => 'A*4JskQY2NVuoAAAAAAAAAAAAABKd2AQ',
                'bg_color' => 'rgb(55,112,179)',
            ],
            'column_info_list' => [
                [
                    'code' => 'BALANCE',
                    'title' => '余额',
                    'value' => '',
                ],
                [
                    'code' => 'Point',
                    'title' => '积分',
                    'value' => '',
                ],
            ],
            'field_rule_list' => [
                [
                    'field_name' => 'Balance',
                    'rule_name' => 'ASSIGN_FROM_REQUEST',
                    'rule_value' => 'Balance',
                ],
                [
                    'field_name' => 'Point',
                    'rule_name' => 'ASSIGN_FROM_REQUEST',
                    'rule_value' => 'Point',
                ],
            ],
            'card_action_list' => [
                [
                    'code' => 'card_action_list1',
                    'text' => '签到领积分',
                    'url_type' => 'miniAppUrl',
                    'mini_app_url' => [
                        'mini_app_id' => $appid
                    ],
                ],
                [
                    'code' => 'card_action_list2',
                    'text' => $app_data['name'],
                    'url_type' => 'miniAppUrl',
                    'mini_app_url' => [
                        'mini_app_id' => $appid
                    ],
                ]
            ],
            'spi_app_id' => $appid
        ];
        dump(json_encode($biz_content, true));
        $request = new \AlipayMarketingCardTemplateCreateRequest();
        $request->setBizContent(json_encode($biz_content, true));
        $result = $alipayClient->execute( $request);
        dump($result);
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;
        if(!empty($resultCode)&&$resultCode == 10000){
            echo "成功";
        } else {
            echo "失败";
        }
    }

    //会员卡模板修改
    public function template_modify()
    {
        $applet_id = $this->applet_id;
        $alipayClient = $this->conf($applet_id);
        $app_data = Applist::detail($applet_id);
        $appid = $app_data['appid'];
        $appname = $app_data['name'].'卡';
        $biz_content = [
            'request_id' => 'wyzj'.time(),
            'template_id' => '20230516000000004676436000300917',
            'biz_no_prefix' => 'wyzj',
            'biz_no_suffix_len' => '20',
            'write_off_type' => 'qrcode',
            'template_style_info' => [
                'card_show_name' => $appname,
                'logo_id' => 'A*o4eoTLlqCQQAAAAAAAAAAAAABKd2AQ',
                'background_id' => 'A*wPzTQJQJUpAAAAAAAAAAAAAABKd2AQ',
                'bg_color' => 'rgb(55,112,179)',
            ],
            'column_info_list' => [
                [
                    'code' => 'BALANCE',
                    'title' => '余额',
                    'value' => '',
                ],
                [
                    'code' => 'Point',
                    'title' => '积分',
                    'value' => '',
                ],
            ],
            'field_rule_list' => [
                [
                    'field_name' => 'Balance',
                    'rule_name' => 'ASSIGN_FROM_REQUEST',
                    'rule_value' => 'Balance',
                ],
                [
                    'field_name' => 'Point',
                    'rule_name' => 'ASSIGN_FROM_REQUEST',
                    'rule_value' => 'Point',
                ],
            ],
            'card_action_list' => [
              [
                  'code' => 'card_action_list1',
                  'text' => '签到领积分',
                  'url_type' => 'miniAppUrl',
                  'mini_app_url' => [
                      'mini_app_id' => $appid
                  ],
              ],
                [
                    'code' => 'card_action_list2',
                    'text' => $app_data['name'],
                    'url_type' => 'miniAppUrl',
                    'mini_app_url' => [
                        'mini_app_id' => $appid
                    ],
                ]
            ],
            'spi_app_id' => $appid
        ];
        dump('request_id'.'='.$biz_content['request_id']);
        $request = new \AlipayMarketingCardTemplateModifyRequest ();
        $request->setBizContent(json_encode($biz_content, true));
        $result = $alipayClient->execute ( $request);
        dump($result);
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;
        if(!empty($resultCode)&&$resultCode == 10000){
            echo "成功";
        } else {
            echo "失败";
        }
    }

    //会员卡模板查询
    public function query()
    {
        $applet_id = $this->applet_id;
        $alipayClient = $this->conf($applet_id);
        $request = new \AlipayMarketingCardTemplateQueryRequest ();
        $request->setBizContent("{" .
            "  \"template_id\":\"20230516000000004676436000300917\"" .
            "}");
        $result = $alipayClient->execute ( $request);
        dump($result);
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;
        if(!empty($resultCode)&&$resultCode == 10000){
            echo "成功";
        } else {
            echo "失败";
        }
    }

    //会员卡开卡表单模板配置
    public function formtemplate_set()
    {
        $applet_id = $this->applet_id;
        $alipayClient = $this->conf($applet_id);
        $request = new \AlipayMarketingCardFormtemplateSetRequest ();
        $biz_content = [
          'template_id' => '20230516000000004676436000300917',
            'fields' => [
                'required' => [
                    'common_fields' => ['OPEN_FORM_FIELD_MOBILE']
                ]
            ]
        ];
        $request->setBizContent(json_encode($biz_content, true));
        $result = $alipayClient->execute ( $request);
        dump($result);
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;
        if(!empty($resultCode)&&$resultCode == 10000){
            echo "成功";
        } else {
            echo "失败";
        }
    }

    //获取会员卡领卡投放链接
    public function activateurl_apply()
    {
        $applet_id = $this->applet_id;
        $alipayClient = $this->conf($applet_id);
        $request = new \AlipayMarketingCardActivateurlApplyRequest();
        $biz_content = [
            'template_id' => '20230508000000004639102000300105',
            'callback' => base_url() . 'index.php/job/cark/callback'
        ];
        $request->setBizContent(json_encode($biz_content, true));
        $result = $alipayClient->execute ( $request);
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;
        if(!empty($resultCode)&&$resultCode == 10000){
            return $this->renderSuccess('', $result);
        } else {
            return $this->renderError('请求失败', $result);
        }
    }

    //会员卡开通，获取会员卡信息
    public function opencard_get()
    {
        log_write('支付宝开卡回调------------------------------------开始-----------------------------------------------');
        $params = $_POST;
        $get = $_GET;
        log_write($get);
        log_write($params);
        log_write('支付宝开卡回调------------------------------------结束-----------------------------------------------');
        exit();
    }

    //查询用户提交的会员卡表单信息
    public function activateform_query()
    {
        $applet_id = $this->applet_id;
        $alipayClient = $this->conf($applet_id);
        $accessToken = 'composeB79788f6e38da48d0b6d4148f0e79dX72';
        $request = new \AlipayMarketingCardActivateformQueryRequest ();
        $biz_content = [
            'biz_type' => 'MEMBER_CARD',
            'template_id' => '20230508000000004639102000300105',
            'request_id' => '20230508013098675817616864720'
        ];
        $request->setBizContent(json_encode($biz_content, true));
        $result = $alipayClient->execute ($request, $accessToken);
        dump($result);
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;
        if(!empty($resultCode)&&$resultCode == 10000){
            echo "成功";
        } else {
            echo "失败";
        }
    }

    //换取授权访问令牌
    public function get_token()
    {
        $applet_id = $this->applet_id;
        $alipayClient = $this->conf($applet_id);
        $request = new \aop\request\AlipaySystemOauthTokenRequest();
        $request->setGrantType("authorization_code");
        $request->setCode("2404262797884bb188d9c3c4324aLA72");
//        $request->setRefreshToken("201208134b203fe6c11548bcabd8da5bb087a83b");
        $result = $alipayClient->execute ( $request);
        dump($result);
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;
        if(!empty($resultCode)&&$resultCode == 10000){
            echo "成功";
        } else {
            echo "失败";
        }
    }

    //会员卡开卡
    public function add()
    {
        $applet_id = $this->applet_id;
        $accessToken = $this->accessToken;
        $alipayClient = $this->conf($applet_id);
        $request = new \AlipayMarketingCardOpenRequest ();
        $biz_content = [
            'out_serial_no' => '201606270000001',
            'card_template_id' => '20230508000000004639102000300105',
            'card_user_info' => [
                'user_uni_id' => '2088532541583075',
                'user_uni_id_type' => 'UID'
            ],
            'card_ext_info' => [
                'external_card_no' => 'EXT0003',
                'open_date' => date('Y-m-d H:i:s',time()),
                'valid_date' => date('Y-m-d H:i:s',time()+31536000)
            ]
        ];
        $request->setBizContent(json_encode($biz_content, true));
        $result = $alipayClient->execute ( $request , $accessToken );
        dump($result);
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;
        if(!empty($resultCode)&&$resultCode == 10000){
            echo "成功";
        } else {
            echo "失败";
        }
    }

    //会员卡更新
    public function update()
    {
        $applet_id = $this->applet_id;
        $alipayClient = $this->conf($applet_id);
        $request = new \AlipayMarketingCardUpdateRequest ();
        $request->setBizContent("{" .
            "  \"target_card_no\":\"000001\"," .
            "  \"target_card_no_type\":\"BIZ_CARD\"," .
            "  \"occur_time\":\"2020-12-27 12:12:12\"," .
            "  \"card_info\":{" .
            "    \"biz_card_no\":\"000001\"," .
            "    \"external_card_no\":\"EXT0001\"," .
            "    \"open_date\":\"2014-02-20 21:20:46\"," .
            "    \"valid_date\":\"2020-02-20 21:20:46\"," .
            "    \"level\":\"VIP1\"," .
            "    \"point\":\"88\"," .
            "    \"balance\":\"124.89\"," .
            "    \"template_id\":\"20170308000000000058101000300045\"," .
            "    \"custom_assets\":\"100元\"," .
            "    \"mdcode_info\":{" .
            "      \"code_status\":\"SUCCESS\"," .
            "      \"code_value\":\"1KFCDY0002\"," .
            "      \"expire_time\":\"2017-06-09 16:25:53\"," .
            "      \"time_stamp\":1496996459" .
            "    }," .
            "    \"front_text_list\":[" .
            "      {" .
            "        \"label\":\"专业\"," .
            "        \"value\":\"金融贸易\"" .
            "      }" .
            "    ]," .
            "    \"front_image_id\":\"9fxnkgt0QFmqKAl5V2BqxQAAACMAAQED\"" .
            "  }," .
            "  \"ext_info\":\"\\\"\\\"\"," .
            "  \"notify_messages\":[" .
            "    {" .
            "      \"message_type\":\"OPEN_CARD\"," .
            "      \"change_reason\":\"由于你的消费\"," .
            "      \"ext_info\":\"{}\"" .
            "    }" .
            "  ]," .
            "  \"mcard_style_info\":{" .
            "    \"bg_color\":\"rgb(55,112,179)\"," .
            "    \"logo_id\":\"1T8Pp00AT7eo9NoAJkMR3AAAACMAAQEC\"," .
            "    \"background_id\":\"1T8Pp00AT7eo9NoAJkMR3AAAACMAAQEC\"" .
            "  }," .
            "  \"merchant_card_msg_info\":{" .
            "    \"changed_point\":\"100.00\"" .
            "  }," .
            "  \"paid_outer_card_info\":{" .
            "    \"action\":\"OPEN\"," .
            "    \"purchase_info\":{" .
            "      \"source\":\"ALIPAY_TINY_APP\"," .
            "      \"price\":\"88.88\"," .
            "      \"action_date\":\"2021-08-12 12:12:12\"," .
            "      \"out_trade_no\":\"20150320010101001\"," .
            "      \"alipay_trade_no\":\"2015042321001004720200028594\"" .
            "    }," .
            "    \"cycle_info\":{" .
            "      \"open_status\":\"OPEN\"," .
            "      \"close_reason\":\"MANUAL_CLOSE\"," .
            "      \"cycle_type\":\"YEAR\"," .
            "      \"alipay_deduct_scene\":\"PAID_OUTER_CARD\"," .
            "      \"alipay_deduct_product_code\":\"PAID_OUTER_CARD\"," .
            "      \"alipay_deduct_agreement\":\"20151127000928469118\"" .
            "    }" .
            "  }" .
            "}");
        $result = $alipayClient->execute ( $request);

        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;
        if(!empty($resultCode)&&$resultCode == 10000){
            echo "成功";
        } else {
            echo "失败";
        }
    }

    //会员卡查询
    public function card_query()
    {
        $applet_id = $this->applet_id;
        $alipayClient = $this->conf($applet_id);
        $request = new \AlipayMarketingCardQueryRequest ();
        $request->setBizContent("{" .
            "  \"target_card_no_type\":\"BIZ_CARD\"," .
            "  \"target_card_no\":\"0000001\"," .
            "  \"card_user_info\":{" .
            "    \"user_uni_id\":\"2088302463082075\"," .
            "    \"user_uni_id_type\":\"UID\"" .
            "  }," .
            "  \"ext_info\":\"\\\"\\\"\"" .
            "}");
        $result = $alipayClient->execute ( $request);

        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;
        if(!empty($resultCode)&&$resultCode == 10000){
            echo "成功";
        } else {
            echo "失败";
        }
    }
}