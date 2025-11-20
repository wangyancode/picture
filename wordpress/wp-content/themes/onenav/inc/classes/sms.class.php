<?php

//阿里sdk
use Aliyun\DySDKLite\SignatureHelper;

//腾讯sdk
use Tencent\ioSms\SendSms;
/**
 * 腾讯SDK3.0
use TencentCloud\Common\Credential;
use TencentCloud\Common\Profile\ClientProfile;
use TencentCloud\Common\Profile\HttpProfile;
use TencentCloud\Common\Exception\TencentCloudSDKException;
use TencentCloud\Sms\V20210111\SmsClient;
use TencentCloud\Sms\V20210111\Models\SendSmsRequest;
*/

class IOSMS {
    public static $time = '30';

    public static function send($to, $code, $sdk = '')
    {
        $sdk = $sdk ? $sdk : io_get_option('sms_sdk','ali');
        if (!$sdk) {
            return array('error' => 1, 'to' => $to, 'msg' => '暂无短信接口，请与客服联系');
        }
        if (!self::is_phone_number($to)) {
            return array('error' => 1, 'to' => $to, 'msg' => '手机号码格式有误');
        }

        switch ($sdk) {
            case 'ali':
                $result = self::ali_send($to, $code);
                break;
            case 'tencent':
                $result = self::tencent_send2($to, $code);
                break;
            case 'smsbao':
                $result = self::smsbao_send($to, $code);
                break;
        }

        if (!empty($result['result']) && empty($result['msg'])) {
            $result['error'] = 0;
            $result['msg'] = __('短信已发送','i_theme');
        }
        return $result;
    }
    public static function is_phone_number($num)
    {
        return preg_match("/^1[3456789]{1}\d{9}$/", $num);
    }


    /**
     * @description: 短信宝
     * @param {*}
     * @return {*}
     */
    public static function smsbao_send($to, $code){
        $cofig = io_get_option('sms_smsbao_option');
        if (empty($cofig['userame']) || (empty($cofig['password']) && empty($cofig['api_key'])) || empty($cofig['template'])) {
            return array('error' => 1, 'msg' => '短信宝：缺少配置参数');
        }

        if (!stristr($cofig['template'], '{code}')) {
            return array('error' => 1, 'msg' => '短信宝：模板内容缺少{code}变量符');
        }

        $statusStr = array(
            "0"  => "短信发送成功",
            "-1" => "参数不全",
            "-2" => "服务器空间不支持,请确认支持curl或者fsocket，联系您的空间商解决或者更换空间！",
            "30" => "短信宝：密码错误",
            "40" => "短信宝：账号不存在",
            "41" => "短信宝：余额不足",
            "42" => "短信宝：帐户已过期",
            "43" => "短信宝：IP地址限制",
            "50" => "短信宝：内容含有敏感词",
            "51" => "手机号码不正确",
        );
        $smsapi = "http://api.smsbao.com/";
        $user   = $cofig['userame']; //短信平台帐号
        $pass   = md5($cofig['password']); //短信平台密码
        if (!empty($cofig['api_key'])) {
            $pass = $cofig['api_key'];
        }
        $content = str_replace('{code}', $code, $cofig['template']);
        $content = str_replace('{time}', self::$time, $content);
        $sendurl = $smsapi . "sms?u=" . $user . "&p=" . $pass . "&m=" . $to . "&c=" . urlencode($content);
        $result  = file_get_contents($sendurl);

        $toArray = array();
        if ($result == 0) {
            $toArray['error']  = 0;
            $toArray['result'] = true;
        } else {
            $toArray['error']  = 1;
            $toArray['msg']    = (isset($statusStr[$result]) ? $statusStr[$result] : '短信发送失败') . ' | 错误码:' . $result;
            $toArray['result'] = false;
        }
        return $toArray;
    }

    //阿里云发送短信
    public static function ali_send($to, $code){
        // Download：https://github.com/jacky-pony/DySDKLite

        $cofig = io_get_option('sms_ali_option');
        if (empty($cofig['keyid']) || empty($cofig['keysecret']) || empty($cofig['sign_name']) || empty($cofig['template_code'])) {
            return array('error' => 1, 'msg' => '阿里云短信：缺少配置参数');
        }
        //准备参数
        $access_keyid  = $cofig['keyid'];
        $access_secret = $cofig['keysecret'];
        $sign_name     = $cofig['sign_name'];
        $template_code = $cofig['template_code'];
        $regionid      = !empty($cofig['regionid']) ? $cofig['regionid'] : 'cn-hangzhou';

        // fixme 必填：是否启用https
        $security = false;

        $params = array();
        // fixme 必填: 短信接收号码
        $params["PhoneNumbers"] = $to;

        // fixme 必填: 短信签名，应严格按"签名名称"填写，请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/sign
        $params["SignName"] = $sign_name;

        // fixme 必填: 短信模板Code，应严格按"模板CODE"填写, 请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/template
        $params["TemplateCode"] = $template_code;

        // fixme 可选: 设置模板参数, 假如模板中存在变量需要替换则为必填项
        $params['TemplateParam'] = json_encode(array(
            "code" => $code,
        ));

        //引入阿里签名文件
        require_once __DIR__ . "/SignatureHelper.php";
        $helper = new SignatureHelper();
        try {
            $result = $helper->request(
                $access_keyid,
                $access_secret,
                "dysmsapi.aliyuncs.com",
                array_merge($params, array(
                    "RegionId" => "cn-hangzhou",
                    "Action"   => "SendSms",
                    "Version"  => "2017-05-25",
                )),
                $security
            );
            $toArray = (array) $result;
            if (!empty($toArray['BizId'])) {
                $toArray['error']  = 0;
                $toArray['result'] = true;
            } else {
                $toArray['error'] = 1;
                $toArray['msg']   = $toArray['Message'] . ' | 错误码: ' . $toArray['Code'];
            }
            return $toArray;
        } catch (\Exception $e) {
            return array('error' => 1, 'msg' => $e->getMessage());
        }
    }

    public static function tencent_send($to, $code){
        //腾讯php sdk3.0发送短信
        /**
            如果使用3.0，需在 composer.json 文件中添加 "tencentcloud/sms": "^3.0"
         */
        $cofig = io_get_option('sms_tencent_option');
        if (empty($cofig['app_id']) || empty($cofig['secret_id']) || empty($cofig['secret_key']) || empty($cofig['sign_name']) || empty($cofig['template_id'])) {
            return array('error' => 1, 'msg' => '腾讯云短信：缺少配置参数');
        }
        $app_id        = $cofig['app_id'];
        $access_keyid  = $cofig['secret_id'];
        $access_secret = $cofig['secret_key'];
        $sign_name     = $cofig['sign_name'];
        $template_id   = $cofig['template_id'];
        $regionid      = !empty($cofig['regionid']) ? $cofig['regionid'] : 'ap-beijing';

        try {
            $cred = new Credential($access_keyid, $access_secret);

            // 实例化一个 http 选项，可选，无特殊需求时可以跳过
            $httpProfile = new HttpProfile();
            // $httpProfile->setReqMethod("GET");
            $httpProfile->setReqTimeout(10); // 请求超时时间，单位为秒（默认60秒）
            $httpProfile->setEndpoint("sms.tencentcloudapi.com"); // 指定接入地域域名（默认就近接入）

            // 实例化一个 client 选项，可选，无特殊需求时可以跳过
            $clientProfile = new ClientProfile();
            $clientProfile->setHttpProfile($httpProfile);

            // 实例化 SMS 的 client 对象，clientProfile 是可选的

            $clientProfile = null;
            $client        = new SmsClient($cred, $regionid, $clientProfile);
            // 实例化一个 sms 发送短信请求对象，每个接口都会对应一个 request 对象。
            $req = new SendSmsRequest();

            $params = array(
                "PhoneNumberSet"   => array($to),
                "TemplateParamSet" => array($code, self::$time),
                "TemplateId"       => $template_id,
                "SignName"         => $sign_name,
                "SmsSdkAppId"      => $app_id,
            );
            $req->fromJsonString(json_encode($params));

            $resp    = $client->SendSms($req);
            $toArray = @json_decode($resp->toJsonString(), true)['SendStatusSet'][0];
            if (($toArray['Code'] == "Ok") && ($toArray['Message'] == "send success")) {
                $toArray['error']  = 0;
                $toArray['result'] = true;
            } else {
                $toArray['error'] = 1;
                $toArray['msg']   = $toArray['Message'] . ' | ' . $toArray['Code'];
            }
            return $toArray;
        } catch (TencentCloudSDKException $e) {
            return array('error' => 1, 'msg' => $e->getMessage());
        }
    }
    public static function tencent_send2($to, $code) {
        //https://github.com/qcloudsms/qcloudsms_php
        //腾讯php sdk2.0发送短信
        $cofig = io_get_option('sms_tencent_option');
        if (empty($cofig['app_id']) || empty($cofig['app_key']) || empty($cofig['sign_name']) || empty($cofig['template_id'])) {
            return array('error' => 1, 'msg' => '腾讯云短信：缺少配置参数');
        }
        $app_id      = $cofig['app_id'];
        $app_key     = $cofig['app_key'];
        $sign_name   = $cofig['sign_name'];
        $template_id = $cofig['template_id'];

        require_once __DIR__ . "/TencentSms.php";
        try {
            $sender  = new SendSms($app_id, $app_key);
            $result  = $sender->sendParam("86", $to, $template_id, array($code, "30"), $sign_name);
            $toArray = json_decode($result, true);

            if (isset($toArray['result']) && $toArray['result'] == 0) {
                $toArray['error']  = 0;
                $toArray['result'] = true;
            } else {
                $toArray['error']  = 1;
                $toArray['msg']    = $toArray['errmsg'] . ' | 错误码:' . $toArray['result'];
                $toArray['result'] = false;
            }
            return $toArray;
        } catch (\Exception $e) {
            return array('error' => 1, 'msg' => $e->getMessage());
        }
    }
}
