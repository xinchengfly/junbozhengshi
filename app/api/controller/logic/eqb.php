<?php


namespace app\api\controller\logic;


use app\api\controller\Controller;

class eqb extends Controller
{
    // 这里是我的代码
// e签宝
    public function eqb($m='sign',$data = []){
        $aop = new \aop\AopClient ();
        $url = "https://openapi.esign.cn";
        $path = public_path('/eqb_token.txt');
        // $url = $this->get_eqb($m);
        $appid = 'xxx';
        $secret='xxx';
        $grantType = 'client_credentials';

        $params = [];
        $headers = ['Content-Type'=>'application/json','X-Tsign-Open-Token'=>'','X-Tsign-Open-App-Id'=>$appid];
        switch ($m) {
            case 'sign':
                $url = $url.'/v1/oauth2/access_token';
                $params = ['appId'=>$appid,'secret'=>$secret,'grantType'=>$grantType];
                if(!is_file($path)){

                    $info = curlPost($url,$params);
                    $infos = $info->getContents();
                    $access_token = json_decode($infos,true)['data']['token'];
                    file_put_contents($path,$access_token);
                }else{
                    $times = time()-filemtime($path);
                    if($times>7100){
                        $info = curlPost($url,$params);
                        $infos = $info->getContents();
                        $access_token = json_decode($infos,true)['data']['token'];
                        file_put_contents($path,$access_token);
                    }else{
                        $access_token = file_get_contents($path);
                    }
                }
                return $access_token;
                break;
            // 模版创建文件  cc64d30f82bb4fec8bd600aeb1b22b87   9b8ddb0a85664b0c9776bd304354dee4
            case 'createByTemplate':
                $url = $url.'/v1/files/createByTemplate';
                $access_token = $this->eqb('sign');
                $headers['X-Tsign-Open-Token'] = $access_token;

                $params['name'] = '签约合同';
                $params['simpleFormFields'] = ['bc51106fd81e439191361db77832c79c'=>$data['store_company_name']]; //,'e17ec4b6183249ab912a1c856409e8c5'=>'印章'
                $params['templateId'] = 'ca1158703413449394471a0ed2a8d5ea';
//                $info = $this->http_send($url,$params,'json',$headers);
                $info = curlPost($url,$params);
                $infos = $info->getContents();
                $contents = json_decode($infos,true);
                return $contents;
                break;
            // 模版文件查询
            case 'docTemplates':
                $url = $url.'/v1/docTemplates/ca1158703413449394471a0ed2a8d5ea';
                $access_token = $this->eqb('sign');
                $headers['X-Tsign-Open-Token'] = $access_token;

//                $info = $this->http_send($url,$params,'GET',$headers);
                $info = curl($url,$params);
                $infos = $info->getContents();
                $contents = json_decode($infos,true);
                return $contents;
                break;
            // 模版创建

            case 'createByUploadUrl':  // 9e2decbb266b4f89a74013ac3b45c76a
                $url = $url.'/v1/docTemplates/createByUploadUrl';
                $access_token = $this->eqb('sign');
                $headers['X-Tsign-Open-Token'] = $access_token;

                $params['contentMd5'] = 'Jtls1+T8iPbrj7FM4xfX1Q==';
                $params['contentType'] = 'application/octet-stream';
                $params['fileName'] = '开发合同书.doc';
                $params['convert2Pdf'] = 'true';

//                $info = $this->http_send($url,$params,'json',$headers);
                $info = curlPost($url,$params);
                $infos = $info->getContents();
                $contents = json_decode($infos,true);
                return $contents;
                break;
            // 模版组件创建
            case 'components':  // 9e2decbb266b4f89a74013ac3b45c76a
                $url = $url.'/v1/docTemplates/9e2decbb266b4f89a74013ac3b45c76a/components';
                $access_token = $this->eqb('sign');
                $headers['X-Tsign-Open-Token'] = $access_token;

                $params['structComponent'] = [
                    'type'  =>  1,
                    'context'   =>[
                        'label' =>  'name',
                        'required' =>  true,
                        'style' =>[
                            'width' =>  120.0,
                            'height' =>  50.0,
                        ],
                        'pos'   =>[
                            'page'  =>  1,
                            'x'     =>  199.0,
                            'y'     =>  699.0,
                        ],
                    ],
                ];
//                $info = $this->http_send($url,$params,'json',$headers);
                $info = curlPost($url,$params);
                $infos = $info->getContents();
                $contents = json_decode($infos,true);
                return $contents;
                break;
            // 个人账号创建
            case 'accounts':
                $url = $url.'/v1/accounts/createByThirdPartyUserId';
                $access_token = $this->eqb('sign');
                $headers['X-Tsign-Open-Token'] = $access_token;

                $params['thirdPartyUserId'] = $data['id_card_no'];
                $params['name'] = $data['legal_person'];
                $params['idType'] = 'CRED_PSN_CH_IDCARD';
                $params['idNumber'] = $data['id_card_no'];
                $params['mobile'] = $data['emergency_contact_phone'];
//                $info = $this->http_send($url,$params,'json',$headers);
                $info = curlPost($url,$params);
                $infos = $info->getContents();
                $contents = json_decode($infos,true);
                return $contents;
                break;
            // 企业账号创建
            case 'organizations':
                $url = $url.'/v1/organizations/createByThirdPartyUserId';
                $access_token = $this->eqb('sign');
                $headers['X-Tsign-Open-Token'] = $access_token;

                $params['thirdPartyUserId'] = $data['business_license_no'];
                $params['creator'] = $data['signerAccountId'];
                $params['name'] = $data['store_company_name'];
                $params['idType'] = 'CRED_ORG_USCC';
                $params['idNumber'] = $data['business_license_no'];
//                $info = $this->http_send($url,$params,'json',$headers);
                $info = curlPost($url,$params);
                $infos = $info->getContents();
                $contents = json_decode($infos,true); // orgId:f109d2c52fa14ab59fc87b055188f7df
                return $contents;
                break;

            // 开启签署流程
            case 'start':
                $url = $url.'/v1/signflows/083a234bea6f4c209381cca5217a5439/start';
                $access_token = $this->eqb('sign');
                $headers['X-Tsign-Open-Token'] = $access_token;

//                $info = $this->http_send($url,$params,'PUT',$headers);
                $info = curlPost($url,$params);
                $infos = $info->getContents();
                $contents = json_decode($infos,true);
                return $contents;
                break;

            // 一步发起签署
            case 'signflows':
                $url = $url.'/api/v2/signflows/createFlowOneStep';
                $access_token = $this->eqb('sign');
                $headers['X-Tsign-Open-Token'] = $access_token;

                $params['docs'][] = [
                    'fileId'    =>$data['fileId'],
                ];
                $params['flowInfo'] = [
                    'autoArchive'=>true,
                    'autoInitiate'=>true,
                    'businessScene' =>'入驻合同',

                ];
                $params['signers'] = [
                    [
                        // 'platformSign'=>true,
                        'signOrder'=>1,
                        'signerAccount'=>[
                            'signerAccountId'=>$data['signerAccountId'],
                            'authorizedAccountId'=>$data['authorizedAccountId'],
                        ],
                        'signfields'=>[
                            [
                                // 'autoExecute'=>true,
                                'actorIndentityType'=>2,
                                'fileId'=>$data['fileId'],
                                'posBean'=>[
                                    'posPage'=>1,
                                    'posX'=>440,
                                    'posY'=>440,
                                    ]
                                ]
                        ],

                    ]
                ];
//                $info = $this->http_send($url,$params,'json',$headers);
                $info = curlPost($url,$params);
                $infos = $info->getContents();
                $contents = json_decode($infos,true); // orgId:f109d2c52fa14ab59fc87b055188f7df
                return $contents;
                break;
        }

    }

    public function deqb($data){
        // An highlighted block
        $data2 = $data;
        $data2['signerAccountId'] = $this->eqb('accounts',$data2)['data']['accountId']; // 创建个人账号
        $data2['authorizedAccountId'] = $this->eqb('organizations',$data2)['data']['orgId']; // 创建企业账号
        $data2['fileId'] = $this->eqb('createByTemplate',$data2)['data']['fileId']; // 创建文件
// return $this->error_msg($helper_model->eqb('createByTemplate',$data2)['data']['fileId']);exit;
        $rs = $this->eqb('signflows',$data2); // 发起签署
    }
}