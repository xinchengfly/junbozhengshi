<?php
/**
 * 合同
 * Create by PhpStorm
 * User: zoey
 * Data: 2022/9/28
 * Time: 13:44
 */

namespace app\api\controller\order;

use app\api\controller\Controller;
use esign\comm\EsignHttpHelper;
use esign\comm\EsignLogHelper;
use esign\comm\EsignUtilHelper;
use esign\emun\HttpEmun;
use think\facade\Db;
use think\helper\Arr;

class Contract extends Controller
{
    protected $config = [
        'eSignAppId' => '7438931923', //app id模拟环境
        'eSignAppSecret' => '1b6e11854eff74fd7204af70a764e908', // app key模拟环境
        'eSignHost' => 'https://smlopenapi.esign.cn', //模拟环境
        'docTemplateId' => 'e16645ac6dd14d5b84fda1538a6b9aa0', //模拟环境
        'orgId' => '5ad602b33ce14ee0b8729aad99e424da', //模拟环境
//        'eSignAppId' => '5111755002', //app id正式环境
//        'eSignAppSecret' => '32a0676ba78cd13a97b5d8d9e975b1c8', // app key正式环境
//        'eSignHost' => 'https://openapi.esign.cn', //正式环境
//        'docTemplateId' => '10e011301c994132b5af9da35be35022', //正式环境
//        'orgId' => '924aa62974c94127a4b06d55282aad49', //正式环境
    ];

    // 填写模板生成文件
    public function createByDocTemplate()
    {
        $orderId = input('order_id');

        // 获取订单信息
        $orderInfo = Db::name('order')->where(['order_id' => $orderId])->find();

        // 获取订单地址
        $orderAddress = Db::name('order_address')->where(['order_id' => $orderId])->find();
        // 省
        $province = Db::name('region')->where(['id' => $orderAddress['province_id']])->value('name');
        // 市
        $city = Db::name('region')->where(['id' => $orderAddress['city_id']])->value('name');
        // 区
        $region = Db::name('region')->where(['id' => $orderAddress['region_id']])->value('name');

        // 商品信息
        $goodsInfo = Db::name('order_product')->where(['order_id' => $orderId])->find();

        // 获取订单期数(最后一期)
        $billInfo = Db::name('order_bill')->where(['order_id' => $orderId])->order('bill_id', 'desc')->find();

        // 获取订单期数(所有)
        $billList = Db::name('order_bill')->where(['order_id' => $orderId])->select();


        $lessorName = '甲方名称甲方名称甲方名称甲方名称';
        $lessorAddress = '甲方地址甲方地址甲方地址甲方地址甲方地址';
        $tenantryName = $orderInfo['username'];//'乙方名称';
        $tenantryCard = $orderInfo['usernum'];//身份证号
        $tenantryAddress = $province . $city . $region . $orderAddress['detail'];//'乙方地址乙方地址乙方地址乙方地址';
        $tenantryMobile = $orderAddress['phone'];//'乙方手机号';
        $tenantryEmail = '';// 乙方邮箱
        $platformMobile = '';// 丙方电话
        $orderNo = $orderInfo['order_no'];// 订单号
        $name = $orderInfo['username'];//'乙方名称';
        $mobile = $orderAddress['phone'];//'乙方手机号';
        $address = $province . $city . $region . $orderAddress['detail'];//'乙方地址乙方地址乙方地址乙方地址';
        $productName = $goodsInfo['product_name'];//'商品名称商品名称商品名称商品名称商品名称商品名称';
        $productModel = $goodsInfo['product_name'];//'商品型号商品型号';
        $productSpecs = $goodsInfo['product_attr'];//'商品规格商品规格商品规格';
        $productPrice = $goodsInfo['product_price'];//商品单价;
        $productNum = $goodsInfo['total_num'];//数量;
        $totalPrice = $goodsInfo['total_pay_price'];//总价;
        $startTime = date('Y年m月d日');//开始时间;
        $endTime = date('Y年m月d日', $billInfo['repayment_date']);// 结束时间;
        $singlePrice = $goodsInfo['total_pay_price'];//总价;
        $allPrice = $goodsInfo['total_pay_price'];//总价;
        $buyout = $goodsInfo['total_pay_price'];//总价;
        $allBuyout = $goodsInfo['total_pay_price'];//总价;
        $psnAccount = $orderAddress['phone'];//'乙方手机号';
        $psnAccountYi = '17633525930';// 商家
        $apiaddr = '/v3/files/create-by-doc-template';
        $data = [
            'docTemplateId' => $this->config['docTemplateId'],
            'fileName' => '合同',
            'components' => [
                [
                    'componentKey' => 'lessorName',
                    'componentValue' => $lessorName,
                    "requiredCheck" => false
                ],
                [
                    'componentKey' => 'lessorAddress',
                    'componentValue' => $lessorAddress,
                    "requiredCheck" => false
                ],
                [
                    'componentKey' => 'tenantryName',
                    'componentValue' => $tenantryName,
                    "requiredCheck" => false
                ],
                [
                    'componentKey' => 'tenantryCard',
                    'componentValue' => $tenantryCard,
                    "requiredCheck" => false
                ],
                [
                    'componentKey' => 'tenantryAddress',
                    'componentValue' => $tenantryAddress,
                    "requiredCheck" => false
                ],
                [
                    'componentKey' => 'tenantryMobile',
                    'componentValue' => $tenantryMobile,
                    "requiredCheck" => false
                ],
                [
                    'componentKey' => 'tenantryEmail',
                    'componentValue' => $tenantryEmail,
                    "requiredCheck" => false
                ],
                [
                    'componentKey' => 'platformMobile',
                    'componentValue' => $platformMobile,
                    "requiredCheck" => false
                ],
                [
                    'componentKey' => 'orderNo',
                    'componentValue' => $orderNo,
                    "requiredCheck" => false
                ],
                [
                    'componentKey' => 'name',
                    'componentValue' => $name,
                    "requiredCheck" => false
                ],
                [
                    'componentKey' => 'mobile',
                    'componentValue' => $mobile,
                    "requiredCheck" => false
                ],
                [
                    'componentKey' => 'address',
                    'componentValue' => $address,
                    "requiredCheck" => false
                ],
                [
                    'componentKey' => 'productName',
                    'componentValue' => $productName,
                    "requiredCheck" => false
                ],
                [
                    'componentKey' => 'productModel',
                    'componentValue' => $productModel,
                    "requiredCheck" => false
                ],
                [
                    'componentKey' => 'productSpecs',
                    'componentValue' => $productSpecs,
                    "requiredCheck" => false
                ],
                [
                    'componentKey' => 'productPrice',
                    'componentValue' => $productPrice,
                    "requiredCheck" => false
                ],
                [
                    'componentKey' => 'productNum',
                    'componentValue' => $productNum,
                    "requiredCheck" => false
                ],
                [
                    'componentKey' => 'totalPrice',
                    'componentValue' => $totalPrice,
                    "requiredCheck" => false
                ],
                [
                    'componentKey' => 'startTime',
                    'componentValue' => $startTime,
                    "requiredCheck" => false
                ],
                [
                    'componentKey' => 'endTime',
                    'componentValue' => $endTime,
                    "requiredCheck" => false
                ],
                [
                    'componentKey' => 'singlePrice',
                    'componentValue' => $singlePrice,
                    "requiredCheck" => false
                ],
                [
                    'componentKey' => 'allPrice',
                    'componentValue' => $allPrice,
                    "requiredCheck" => false
                ],
                [
                    'componentKey' => 'buyout',
                    'componentValue' => $buyout,
                    "requiredCheck" => false
                ],
                [
                    'componentKey' => 'allBuyout',
                    'componentValue' => $allBuyout,
                    "requiredCheck" => false
                ],


                [
                    'componentKey' => 'signTimeA',
                    'componentValue' => date('Y年m月d日'),
                    "requiredCheck" => false
                ],
                [
                    'componentKey' => 'signTimeB',
                    'componentValue' => date('Y年m月d日'),
                    "requiredCheck" => false
                ],
                [
                    'componentKey' => 'signTimeC',
                    'componentValue' => date('Y年m月d日'),
                    "requiredCheck" => false
                ],
            ]
        ];

        foreach ($billList as $v) {
            switch ($v['current_period']) {
                case 2:
                    $componentKey = 'second';
                    break;
                case 3:
                    $componentKey = 'third';
                    break;
                case 4:
                    $componentKey = 'fourth';
                    break;
                case 5:
                    $componentKey = 'fifth';
                    break;
                case 6:
                    $componentKey = 'sixth';
                    break;
                case 7:
                    $componentKey = 'seventh';
                    break;
                case 8:
                    $componentKey = 'eighth';
                    break;
                case 9:
                    $componentKey = 'ninth';
                    break;
                case 10:
                    $componentKey = 'tenth';
                    break;
                case 11:
                    $componentKey = 'eleventh';
                    break;
                case 12:
                    $componentKey = 'twelfth';
                    break;
                default:
                    $componentKey = 'first';
                    break;
            }
            $components[] = [
                'componentKey' => $componentKey,
                'componentValue' => date('Y年m月d日', $v['repayment_date']) . '  ' . $v['price'],
                "requiredCheck" => false
            ];
        }
        $components = array_merge($data['components'], $components);
        $data['components'] = $components;
        $paramStr = json_encode($data);
        //生成签名验签+json体的header

        $signAndBuildSignAndJsonHeader = EsignHttpHelper::signAndBuildSignAndJsonHeader($this->config['eSignAppId'], $this->config['eSignAppSecret'], $paramStr, 'POST', $apiaddr);
        //发起接口请求
//        EsignLogHelper::printMsg($signAndBuildSignAndJsonHeader);
        $response = EsignHttpHelper::doCommHttp($this->config['eSignHost'], $apiaddr, 'POST', $signAndBuildSignAndJsonHeader, $paramStr);
//        EsignLogHelper::printMsg($response->getStatus());
//        EsignLogHelper::printMsg($response->getBody());
halt(json_decode($response->getBody()));
        if (json_decode($response->getBody())->code != 0) {
            return $this->renderError('合同操作失败');
        }
        $fileId = json_decode($response->getBody())->data->fileId;
        $fileDownloadUrl = json_decode($response->getBody())->data->fileDownloadUrl;
        // 保存到本地
        $filePath = $this->savePdf($this->getUser()['user_id'], $fileDownloadUrl);

        $flowId = $this->createByFile($fileId, $filePath, $psnAccount, $psnAccountYi, $orderNo);
        $url = $this->getSignUrl($flowId, $psnAccount);// 用户
        $url2 = $this->getSignUrl($flowId, $psnAccountYi);// 商家
        // 存入数据库
        Db::name('order')->where(['order_id' => $orderId])->update(['folwid' => $flowId, 'user_url' => $url, 'shop_url' => $url2]);
        return $this->renderSuccess('获取合同文件签署链接调用成功', compact('url'));
    }

    //基于文件发起签署
    public function createByFile($fileId, $filePath, $psnAccount, $psnAccountYi, $orderNo)
    {
        //上传文件，获取文件id
        $fileName = "合同.pdf";
        $notifyUrl = 'http://yuhangrenrenzu.rchz.top/index.php/api/order.contract/notify?app_id=10001';
        $redirectUrl = 'http://yuhangrenrenzu.rchz.top';
        $orgId = $this->config['orgId'];

//        EsignLogHelper::printMsg("**********基于文件发起签署调用开始**********");
        $apiaddr = "/v3/sign-flow/create-by-file";
        $requestType = HttpEmun::POST;
        $contentMd5 = $this->contentMd5($filePath);
        $this->fileUploadUrl($filePath, $contentMd5);
        $data = [
            "docs" => [
                [
                    "fileId" => $fileId,
                    "fileName" => $fileName
                ]
            ],
            "signFlowConfig" => [
                "signFlowTitle" => "租赁",
                "autoStart" => true,
                "autoFinish" => true,
                "signConfig" => [
                    "availableSignClientTypes" => "1",
                    "showBatchDropSealButton" => true
                ],
                "noticeConfig" => [
                    "noticeTypes" => "1,2"
                ],
                "notifyUrl" => $notifyUrl,
                "redirectConfig" => [
                    "redirectUrl" => $redirectUrl
                ],
            ],
            "signers" => [
                // 丙方
                [
                    "orgSignerInfo" => [
                        "orgId" => $orgId
                    ],
                    "signConfig" => [
                        "signOrder" => 1,
                    ],
                    "signerType" => 1,
                    "signFields" => [
                        [
//                            "customBizNum" => "自定义编码",
                            "fileId" => $fileId,
                            "normalSignFieldConfig" => [
                                "autoSign" => true,
                                "signFieldPosition" => [
                                    "positionPage" => "13",
                                    "positionX" => 470,
                                    "positionY" => 377.811,
                                ],
                                "signFieldStyle" => 1,
                            ],
                        ],
                    ],
                ],
                // 乙方
                [
                    "psnSignerInfo" => [
                        "psnAccount" => $psnAccount,
                    ],
                    "signConfig" => [
                        "forcedReadingTime" => 10,
                        "signOrder" => 1,
                    ],
                    "signerType" => 0,
                    "signFields" => [
                        [
                            "customBizNum" => "user_" . $orderNo,
                            "fileId" => $fileId,
                            "normalSignFieldConfig" => [
                                "signFieldPosition" => [
                                    "positionPage" => "13",
                                    "positionX" => 298,
                                    "positionY" => 377.811,
                                ],
                                "signFieldStyle" => 1,
                            ]
                        ]
                    ]
                ],
                // 甲方
                [
                    "psnSignerInfo" => [
                        "psnAccount" => $psnAccountYi,
                    ],
                    "signConfig" => [
                        "forcedReadingTime" => 10,
                        "signOrder" => 1,
                    ],
                    "signerType" => 0,
                    "signFields" => [
                        [
                            "customBizNum" => "shop_" . $orderNo,
                            "fileId" => $fileId,
                            "normalSignFieldConfig" => [
                                "signFieldPosition" => [
                                    "positionPage" => "13",
                                    "positionX" => 130,
                                    "positionY" => 377.811,
                                ],
                                "signFieldStyle" => 1,
                            ]
                        ]
                    ]
                ],
            ]
        ];
        $paramStr = json_encode($data);

        $signAndBuildSignAndJsonHeader = EsignHttpHelper::signAndBuildSignAndJsonHeader($this->config['eSignAppId'], $this->config['eSignAppSecret'], $paramStr, $requestType, $apiaddr);

//        EsignLogHelper::printMsg($signAndBuildSignAndJsonHeader);
        $response = EsignHttpHelper::doCommHttp($this->config['eSignHost'], $apiaddr, $requestType, $signAndBuildSignAndJsonHeader, $paramStr);
//        EsignLogHelper::printMsg($response->getStatus());
//        EsignLogHelper::printMsg($response->getBody());

        $flowId = false;
        if ($response->getStatus() == 200) {
            $result = json_decode($response->getBody());
            if ($result->code == 0) {
                $flowId = $result->data->signFlowId;
                return $flowId;
//                return $this->renderSuccess('基于文件发起签署接口调用成功', compact('flowId'));
//                EsignLogHelper::printMsg("基于文件发起签署接口调用成功，flowId: " . $flowId);
            } else {
                return $this->renderError("基于文件发起签署接口调用失败，错误信息: " . $result->message);
//                EsignLogHelper::printMsg("基于文件发起签署接口调用失败，错误信息: " . $result->message);
            }
        } else {
            return $this->renderError("基于文件发起签署接口调用失败，HTTP错误码: " . $response->getStatus());
//            EsignLogHelper::printMsg("基于文件发起签署接口调用失败，HTTP错误码" . $response->getStatus());
        }
//        EsignLogHelper::printMsg("**********基于文件发起签署调用结束**********");
//        return $flowId;
    }

    /**
     * 获取合同文件签署链接
     */
    public function getSignUrl($flowId, $psnAccount)
    {
//        $flowId = "96d9ba701b4e482fbc0ac9b09f74be80";
//        $psnAccount = "17633525930";
//        EsignLogHelper::printMsg("**********获取合同文件签署链接开始**********");

        $apiaddr = "/v3/sign-flow/%s/sign-url";
        $apiaddr = sprintf($apiaddr, $flowId);
        $requestType = HttpEmun::POST;
        $data = [
            "clientType" => "ALL",
            "needLogin" => false,
            "operator" => [
                "psnAccount" => $psnAccount
            ],
            "urlType" => 2
        ];
        $paramStr = json_encode($data);

        $signAndBuildSignAndJsonHeader = EsignHttpHelper::signAndBuildSignAndJsonHeader($this->config['eSignAppId'], $this->config['eSignAppSecret'], $paramStr, $requestType, $apiaddr);

//        EsignLogHelper::printMsg($signAndBuildSignAndJsonHeader);
        $response = EsignHttpHelper::doCommHttp($this->config['eSignHost'], $apiaddr, $requestType, $signAndBuildSignAndJsonHeader, $paramStr);
//        EsignLogHelper::printMsg($response->getStatus());
//        EsignLogHelper::printMsg($response->getBody());
//        $url = null;
        if ($response->getStatus() == 200) {
            $url = json_decode($response->getBody())->data->url;
            return $url;
//            return $this->renderSuccess('获取合同文件签署链接调用成功', compact('url'));
//            EsignLogHelper::printMsg("获取合同文件签署链接调用成功，url: " . $url);
        } else {
            return $this->renderError('获取合同文件签署链接接口调用失败，HTTP错误码：' . $response->getStatus());
//            EsignLogHelper::printMsg("获取合同文件签署链接接口调用失败，HTTP错误码" . $response->getStatus());
        }
//        EsignLogHelper::printMsg("**********获取合同文件签署链接调用结束**********");
//        return $flowId;
    }

    /**
     * 获取合同文件签署链接
     */
    private function getSignUrl2($flowId, $psnAccount)
    {
//        $flowId = "96d9ba701b4e482fbc0ac9b09f74be80";
//        $psnAccount = "15257161153";
//        EsignLogHelper::printMsg("**********获取合同文件签署链接开始**********");

        $apiaddr = "/v3/sign-flow/%s/sign-url";
        $apiaddr = sprintf($apiaddr, $flowId);
        $requestType = HttpEmun::POST;
        $data = [
            "clientType" => "ALL",
            "needLogin" => false,
            "operator" => [
                "psnAccount" => $psnAccount
            ],
            "urlType" => 2
        ];
        $paramStr = json_encode($data);

        $signAndBuildSignAndJsonHeader = EsignHttpHelper::signAndBuildSignAndJsonHeader($this->config['eSignAppId'], $this->config['eSignAppSecret'], $paramStr, $requestType, $apiaddr);

//        EsignLogHelper::printMsg($signAndBuildSignAndJsonHeader);
        $response = EsignHttpHelper::doCommHttp($this->config['eSignHost'], $apiaddr, $requestType, $signAndBuildSignAndJsonHeader, $paramStr);
//        EsignLogHelper::printMsg($response->getStatus());
//        EsignLogHelper::printMsg($response->getBody());
//        $url = null;
        if ($response->getStatus() == 200) {
            $url = json_decode($response->getBody())->data->shortUrl;
            return $url;
//            return $this->renderSuccess('获取合同文件签署链接调用成功', compact('url'));
//            EsignLogHelper::printMsg("获取合同文件签署链接调用成功，url: " . $url);
        } else {
            return $this->renderError('获取合同文件签署链接接口调用失败，HTTP错误码：' . $response->getStatus());
//            EsignLogHelper::printMsg("获取合同文件签署链接接口调用失败，HTTP错误码" . $response->getStatus());
        }
//        EsignLogHelper::printMsg("**********获取合同文件签署链接调用结束**********");
//        return $flowId;
    }

    public function keywordPositions()
    {
        $name = input('name');
        $apiaddr = "/v3/files/e16645ac6dd14d5b84fda1538a6b9aa0/keyword-positions?keywords=" . $name;
        $requestType = HttpEmun::GET;

        $signAndBuildSignAndJsonHeader = EsignHttpHelper::signAndBuildSignAndJsonHeader($this->config['eSignAppId'], $this->config['eSignAppSecret'], '', $requestType, $apiaddr);

//        EsignLogHelper::printMsg($signAndBuildSignAndJsonHeader);
        $response = EsignHttpHelper::doCommHttp($this->config['eSignHost'], $apiaddr, $requestType, $signAndBuildSignAndJsonHeader, '');
        if ($response->getStatus() == 200) {
            $data = json_decode($response->getBody())->data->keywordPositions;
            return $this->renderSuccess('调用成功', compact('data'));
//            EsignLogHelper::printMsg("获取合同文件签署链接调用成功，url: " . $url);
        } else {
            return $this->renderError('调用失败，HTTP错误码：' . $response->getStatus());
//            EsignLogHelper::printMsg("获取合同文件签署链接接口调用失败，HTTP错误码" . $response->getStatus());
        }
    }

    /**
     * 回调
     */
    public function notify()
    {
        $params = file_get_contents('php://input');
        file_put_contents(root_path() . 'public/pdf/' . date('Ymd') . '.txt', json_encode($params) . PHP_EOL, FILE_APPEND);
        $params = json_encode($params);
        $post = json_decode($params, true);
        if ($post['action'] == 'OPERATOR_READ') {
            $customBizNum = explode('_', $post['customBizNum']);
            if ($post['signResult'] == 2) {
                if (strstr($post['customBizNum'], 'shop')) {
                    // 商家签署
                    Db::name('order')->where(['order_no' => $customBizNum[1]])->update(['is_shop' => 2]);
                } else {
                    // 用户签署
                    Db::name('order')->where(['order_no' => $customBizNum[1]])->update(['is_user' => 2]);
                }
            }
        }
    }

    // 保存pdf到本地
    public function savePdf($userId, $url)
    {
        $file = file_get_contents($url);
        $filend = "pdf";
        $fileName = root_path() . 'public/pdf/' . date('Ymd') . '_' . $userId . '_' . rand(0, 999999999) . '.' . $filend;
        file_put_contents($fileName, $file);
        return $fileName;
    }

    private function contentMd5($filePath)
    {
        if (!file_exists($filePath)) {
//            EsignLogHelper::printMsg($filePath . "文件不存在");
//            exit;
            return $this->renderError('文件不存在');
        }
        return EsignUtilHelper::getContentBase64Md5($filePath);
    }

    /**
     * @param $filePath
     * @param $contentMd5
     * @return mixed
     */
    private function fileUploadUrl($filePath, $contentMd5)
    {
        $apiaddr = "/v3/files/file-upload-url";
        $requestType = HttpEmun::POST;
        $data = [
            "contentMd5" => $contentMd5,
            "contentType" => "application/pdf",
            "convertToPDF" => false,
            "fileName" => "房屋租赁协议.pdf",
            "fileSize" => filesize($filePath)
        ];
        $paramStr = json_encode($data);
        //生成签名验签+json体的header

        $signAndBuildSignAndJsonHeader = EsignHttpHelper::signAndBuildSignAndJsonHeader($this->config['eSignAppId'], $this->config['eSignAppSecret'], $paramStr, $requestType, $apiaddr);
        //获取文件上传地址
//    EsignLogHelper::printMsg($signAndBuildSignAndJsonHeader);
        $response = EsignHttpHelper::doCommHttp($this->config['eSignHost'], $apiaddr, $requestType, $signAndBuildSignAndJsonHeader, $paramStr);
//    EsignLogHelper::printMsg($response->getStatus());
//    EsignLogHelper::printMsg($response->getBody());
        $fileUploadUrl = json_decode($response->getBody())->data->fileUploadUrl;
        $fileId = json_decode($response->getBody())->data->fileId;
        //文件流put上传
        $response = EsignHttpHelper::upLoadFileHttp($fileUploadUrl, $filePath, "application/pdf");
        EsignLogHelper::printMsg($response->getStatus());
        EsignLogHelper::printMsg($response->getBody());
        return $fileId;
    }
}