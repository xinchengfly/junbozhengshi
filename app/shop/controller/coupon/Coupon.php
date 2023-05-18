<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2023/3/30
 * Time: 14:38
 */

namespace app\shop\controller\coupon;

use think\facade\Db;
use think\facade\Env;

require '/www/wwwroot/'.Env::get('url.url', 'yuzhou.haidiao888.com').'/extend/aop/AopClient.php';
require '/www/wwwroot/'.Env::get('url.url', 'yuzhou.haidiao888.com').'/extend/aop/AopCertClient.php';
require '/www/wwwroot/'.Env::get('url.url', 'yuzhou.haidiao888.com').'/extend/aop/AopCertification.php';
require '/www/wwwroot/'.Env::get('url.url', 'yuzhou.haidiao888.com').'/extend/aop/AlipayConfig.php';
require '/www/wwwroot/'.Env::get('url.url', 'yuzhou.haidiao888.com').'/extend/aop/request/AlipayMarketingActivityDeliveryCreateRequest.php';
require '/www/wwwroot/'.Env::get('url.url', 'yuzhou.haidiao888.com').'/extend/aop/AlipayMarketingMaterialImageUploadRequest.php';
require '/www/wwwroot/'.Env::get('url.url', 'yuzhou.haidiao888.com').'/extend/aop/AlipayMarketingActivityUserBatchqueryvoucherRequest.php';

class Coupon
{

    public function conf()
    {
        $alipayConfig = new \aop\AopClient();
        $alipayConfig->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
        $alipayConfig->appId = Db::name('config')->where('type','=','ali_mnp')->where('name','=','ali_app_id')->value('value');
        $alipayConfig->rsaPrivateKey = Db::name('config')->where('type','=','ali_mnp')->where('name','=','ali_rsa_privatekey')->value('value');
        $alipayConfig->alipayrsaPublicKey = Db::name('config')->where('type','=','ali_mnp')->where('name','=','ali_alipayrsa_publickey')->value('value');
        $alipayConfig->apiVersion = '1.0';
        $alipayConfig->signType = 'RSA2';
        $alipayConfig->postCharset='GBK';
        $alipayConfig->format='json';
        return $alipayConfig;
    }

    public function test()
    {
        $alipayConfig = $this->conf();
        $request = new \AlipayMarketingActivityUserBatchqueryvoucherRequest();
        $request->setBizContent("{" .
            "  \"user_id\":\"2088512417841101\"," .
            "  \"activity_id\":\"2016042700826004508401111111\"," .
            "  \"belong_merchant_id\":\"2088202967380463\"," .
            "  \"sender_merchant_id\":\"2088102161342862\"," .
            "  \"voucher_status\":\"SENDED\"," .
            "  \"page_num\":1," .
            "  \"merchant_access_mode\":\"AGENCY_MODE\"," .
            "  \"page_size\":20" .
            "}");
        $result = $alipayConfig->execute ( $request , $accessToken );

        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;
        if(!empty($resultCode)&&$resultCode == 10000){
            echo "成功";
        } else {
            echo "失败";
        }
    }

    public function index()
    {
        $alipayConfig = $this->conf();
        $request = new \AlipayMarketingActivityDeliveryCreateRequest();
        $request->setBizContent("{".
            "\"delivery_booth_code\":\"PUBLIC_UNION\",".
            "\"out_biz_no\":\"20170101000001654bb46ba\",".
            "\"delivery_play_config\":{".
            "\"delivery_single_send_config\":{".
            "\"delivery_content_config\":{".
            "\"delivery_send_guide\":{".
            "\"delivery_guide_url\":\"alipays://platformapi/startapp?appId=xxxx\"".
            "}".
            "},".
            "\"delivery_content_info\":{".
            "\"delivery_activity_content\":{".
            "\"activity_id\":\"20122131234434557567\"".
            "},".
            "\"delivery_content_type\":\"ACTIVITY\"".
            "}".
            "}".
            "},".
            "\"delivery_base_info\":{".
            "\"delivery_material\":{".
            "\"delivery_single_material\":{".
            "\"delivery_image\":\"A*SbGnSavwrJUAAAAAAAAAAAAAARwnAQ\"".
            "}".
            "},".
            "\"delivery_name\":\"第十期投放计划\",".
            "\"delivery_begin_time\":\"2023-04-01 00:00:01\",".
            "\"delivery_end_time\":\"2023-04-11 00:00:01\"".
            "},".
            "\"delivery_target_rule\":{".
            "\"delivery_city_code_rule\":{".
            "\"all_city\":\"true\",".
            "\"city_codes\":[".
            "\"100100\"".
            "]".
            "}".
            "},".
            "\"merchant_access_mode\":\"SELF_MODE\"".
            "}");
        $responseResult = $alipayConfig->execute($request);
        $responseApiName = str_replace(".","_",$request->getApiMethodName())."_response";
        $response = $responseResult->$responseApiName;
        dump($response);
        if(!empty($response->code)&&$response->code==10000){
            echo("调用成功");
        }
        else{
            echo("调用失败");
        }
    }

    public function updateImg()
    {
        $alipayConfig = $this->conf();
        $imageBase64 = "iVBORw0KGgoAAAANSUhEUgAAAAoAAAAKCAYAAACNMs+9AAAAEUlEQVR42mP4TyRgGFVIX4UAI/uOgGWVNeQAAAAASUVORK5CYII=";
        $request = new \AlipayMarketingMaterialImageUploadRequest();
        $request->setBelongMerchantInfo("{".
            "\"merchant_id_type\":\"SMID\",".
            "\"merchant_id\":\"2088520084673290\",".
            "\"business_type\":\"ISV_FOR_MERCHANT\"".
            "}");
        //请填写需要上传的文件路径 如：@/home/admin/test.jpg
        $request->setFileContent('@'.'/www/wwwroot/yuzhou.haidiao888.com/'.'/public/image/agent/ceshi2.png');
        $request->setFileKey("PUBLIC_UNION_CHANNEL_PIC");
        $request->setMerchantAccessMode("SELF_MODE");

        $responseResult = $alipayConfig->execute($request);
        $responseApiName = str_replace(".","_",$request->getApiMethodName())."_response";
        $response = $responseResult->$responseApiName;
        if(!empty($response->code)&&$response->code==10000){
            echo("调用成功");
        }
        else{
            echo("调用失败");
        }
    }
}