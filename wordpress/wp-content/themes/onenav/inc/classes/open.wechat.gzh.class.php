<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2021-03-01 10:19:02
 * @LastEditors: iowen
 * @LastEditTime: 2024-05-04 22:53:04
 * @FilePath: /onenav/inc/classes/open.wechat.gzh.class.php
 * @Description: 微信公众号
 */
class GZHException extends \Exception
{
}

class ioLoginWechatGZH
{
    protected $appid;
    protected $secret;
    protected $accessToken;
    public $state;
    public $ticket;
    public $callback;
    public $type;

    /**
     * Summary of __construct
     * @param mixed $appid
     * @param mixed $appSecret
     * @param mixed $type 公众号'gzh'  订阅号'dyh'
     */
    function __construct($appid = null, $appSecret = null, $type = 'gzh')
    {
        $this->appid       = $appid;
        $this->secret      = $appSecret;
        $this->type        = $type;

        $accessToken_option = get_option('wechat_'.$type.'_access_token');
        $new_time = strtotime('+300 Second'); //获取现在时间加5分钟

        if (!empty($accessToken_option['access_token']) && $accessToken_option['expiration_time'] > $new_time) {
            $this->accessToken = $accessToken_option['access_token'];
        } else {
            $this->accessToken = $this->getAccessToken();
        }
    }

    /**
     * 获取access_token
     * token的有效时间为2小时，这里可以做下处理，提高效率不用每次都去获取，
     * 将token存储到缓存中，每2小时更新一下，然后从缓存取即可
     * @return
     */
    private function getAccessToken()
    {
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . $this->appid . "&secret=" . $this->secret;
        $res = json_decode($this->httpRequest($url), true);

        if (!empty($res['access_token'])) {
            //储存access_token到本地
            $res['expiration_time'] = strtotime('+' . $res['expires_in'] . ' Second');
            update_option('wechat_'.$this->type.'_access_token', $res);
            $this->accessToken = $res['access_token'];
            return $res['access_token'];
        } 
        //wp_die( __('AccessToken获取失败：', 'i_theme'). json_encode($res), __('获取失败', 'i_theme'), array('response'=>403)); 
        throw new GZHException( __('AccessToken获取失败：', 'i_theme') . json_encode($res));
    }

    /**
     * 回调函数
     */
    public function callback()
    {
        $callbackXml = file_get_contents('php://input'); //获取返回的xml
        //下面是返回的xml
        //<xml><ToUserName><![CDATA[gh_xxxxxxxxxxx]]></ToUserName> //微信公众号的微信号
        //<FromUserName><![CDATA[oxxxxxxxxxxxxxxxxxxxxxxxxxxxxx]]></FromUserName> //openid用于获取用户信息，做登录使用
        //<CreateTime>1374981407</CreateTime> //回调时间
        //<MsgType><![CDATA[event]]></MsgType>
        //<Event><![CDATA[SCAN]]></Event>
        //<EventKey><![CDATA[lrfun1531453236]]></EventKey> //上面自定义的参数（scene_str）
        //<Ticket><![CDATA[gqxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx]]></Ticket> //换取二维码的ticket
        //</xml>

        //<xml><ToUserName><![CDATA[gh_xxxxxxxxxxx]]></ToUserName>
        //<FromUserName><![CDATA[oxxxxxxxxxxxxxxxxxxxxxxxxxxxxx]]></FromUserName>
        //<CreateTime>1374981407</CreateTime>
        //<MsgType><![CDATA[text]]></MsgType>
        //<Content><![CDATA[测试]]></Content>
        //<MsgId>2300000000000000</MsgId>
        //</xml>
        $data = json_decode(json_encode(simplexml_load_string($callbackXml, 'SimpleXMLElement', LIBXML_NOCDATA)), true); //将返回的xml转为数组

        if ('gzh'===$this->type && !empty($data['FromUserName']) && !empty($data['EventKey']) && !empty($data['Event']) && in_array($data['Event'], array('subscribe', 'SCAN'))) {
            $this->callback = $data;
            return $data;
        }
        if (!empty($data['FromUserName'])) {
            $this->callback = $data;
            return $data;
        }
        return false;
    }

    /**
     * 保存临时数据
     * @param mixed $code
     * @param mixed $callback
     * @return void
     */
    public function saveFromUserName($code, $callback)
    {
        //储存临时数据
        $new_time_YmdHis = (int) current_time('timestamp');
        $wechat_dyh_event_data = get_option('wechat_dyh_event_data');
        //清理过期数据
        if ($wechat_dyh_event_data && is_array($wechat_dyh_event_data)) {
            foreach ($wechat_dyh_event_data as $k => $v) {
                if ($new_time_YmdHis > ($v['update_time'] + (5 * MINUTE_IN_SECONDS)) || $v['FromUserName'] === $callback['FromUserName']) {
                    unset($wechat_dyh_event_data[$k]);
                }
            }
        } else {
            $wechat_dyh_event_data = array();
        }

        $wechat_dyh_event_data[$code] = array(
            'FromUserName' => $callback['FromUserName'],
            'update_time'  => $new_time_YmdHis,
        );
        update_option('wechat_dyh_event_data', $wechat_dyh_event_data, false);
    }

    /**
     * 自动回复消息
     * @param mixed $msgs
     * @return void
     */
    public function responseMsg($msgs = array()) { 
        $callback = $this->callback; 
        if(!empty($callback['MsgType'])){
            switch ($callback['MsgType']) {
                case 'text':
                    if('dyh'===$this->type){
                        if($callback['Content'] == '登录' || $callback['Content'] == '登陆' || $callback['Content'] == '绑定'){
                            $code = rand(100000,999999); 
                            $this->saveFromUserName($code, $callback);
                            $content = "验证码：".$code."，5分钟内有效，过期后请重新发送“登录”二字获取";
                        }
                        echo $this->sendMessage($content);
                    }
                    $callback_content = trim($callback['Content']);
                    if (!empty($msgs['text'][0])) {
                        foreach ($msgs['text'] as $v) {
                            $in = trim($v['in']);
                            if ('include' === $v['mode']) {
                                if ($in && stristr($callback_content, $in)) {
                                    echo $this->sendMessage($v['out']);
                                }
                            } else {
                                if ($in && $in == $callback_content) {
                                    echo $this->sendMessage($v['out']);
                                }
                            }
                        }
                    }
                    break;
                case 'image':
                    if (!empty($msgs['image'])) {
                        echo $this->sendMessage($msgs['image']);
                    }
                    break;
                case 'voice':
                    if (!empty($msgs['voice'])) {
                        echo $this->sendMessage($msgs['voice']);
                    }
                    break;
            }
            if ('dyh' === $this->type && (
                    (!empty($callback['Event']) && in_array($callback['Event'], array('subscribe', 'SCAN'))) ||
                    (isset($callback['Event']) && $callback['Event'] == 'click' && $callback['EventKey'] == 'io_ws_login')
            )) {
                $code = rand(100000, 999999);
                $this->saveFromUserName($code, $callback);
                $content = "验证码：" . $code . "，5分钟内有效，过期后请重新发送“登录”二字获取";
                echo $this->sendMessage($content);
            }
            if (!empty($msgs['default'])) {
                echo $this->sendMessage($msgs['default']);
            }
        }  
    }
    /**
     * 创建自定义菜单
     * @param mixed $data
     * @return mixed
     */
    public function createMenu($data = '')
    {

        $url    = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token=' . $this->accessToken;
        $result = $this->httpRequest($url, json_encode($data, JSON_UNESCAPED_UNICODE));
        return json_decode($result, true);
    }
    /**
     * POST或GET请求
     * @param mixed $url 请求url
     * @param mixed $data POST数据
     * @return mixed
     */
    private function httpRequest($url, $data = "")
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        if (!empty($data)) {  //判断是否为POST请求
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
    }

    /**
     * 获取openID和unionId
     * @param mixed $code 微信授权登录返回的code
     * @return
     */
    public function getOpenIdOrUnionId($code)
    {
        $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=" . $this->appid . "&secret=" . $this->secret . "&code=" . $code . "&grant_type=authorization_code";
        $data = $this->httpRequest($url);
        return $data;
    }

    /**
     * 发送模板短信
     * @param mixed $data 请求数据
     * @return
     */
    public function sendTemplateMessage($data = "")
    {
        $url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=" . $this->accessToken;
        $result = $this->httpRequest($url, $data);
        return $result;
    }


    /**
     * 回复消息
     * @param mixed $msg 消息内容
     * @return
     */
    public function sendMessage($msg = "")
    {
        $callback = $this->callback;

        if (empty($callback['FromUserName']) || empty($callback['ToUserName']) || !$msg) return;
        $time = time();    //时间戳
        $msgtype = 'text'; //消息类型：文本
        $textTpl = "<xml>
            <ToUserName><![CDATA[%s]]></ToUserName>
            <FromUserName><![CDATA[%s]]></FromUserName>
            <CreateTime>%s</CreateTime>
            <MsgType><![CDATA[%s]]></MsgType>
            <Content><![CDATA[%s]]></Content>
            </xml>";

        $fromUsername = $callback['FromUserName']; //请求消息的用户
        $toUsername = $callback['ToUserName'];    //"我"的公众号id
        $resultStrq = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgtype, $msg);
        return $resultStrq;
    }

    /**
     * 生成带参数的二维码|此方式暂未使用
     * 使用scene_id的方式，QR_SCENE为临时的整型参数值
     * @param mixed $scene_id 自定义参数（整型）
     * @return
     */
    public function getQrcodeById($repeat = true)
    {
        $state = time() . mt_rand(11, 99);
        $this->state = (int)$state;

        $url = "https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=" . $this->accessToken;
        $data = array(
            "expire_seconds" => 3600, //二维码的有效时间（1小时）
            "action_name" => "QR_SCENE",
            "action_info" => array("scene" => array("scene_id" => $this->state))
        );
        $result = $this->httpRequest($url, json_encode($data));
        $result = json_decode($result, true);

        if (!empty($result['ticket'])) {
            $this->ticket = $result['ticket'];
            return $result;
        }

        //如果access_token错误则在执行一次
        if (!empty($result['errmsg']) && stristr($result['errmsg'], 'access_token') && $repeat) {
            $this->getAccessToken();
            return $this->getQrcodeById(false);
        }

        //wp_die( __('二维码获取失败：', 'i_theme'). json_encode($result), __('获取失败', 'i_theme'), array('response'=>403));
        throw new GZHException(__('二维码获取失败：', 'i_theme') . json_encode($result));
    }

    /**
     * 生成带参数的二维码
     * 使用 scene_str 方式，QR_STR_SCENE为临时的字符串参数值
     * @param mixed $scene_str 自定义参数（字符串）
     * @return
     */
    public function getQrcode($repeat = true)
    {
        $state = time() . mt_rand(11, 99);
        $this->state = $state;
        $url = "https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=" . $this->accessToken;
        $data = array(
            "expire_seconds" => 3600 * 24, //二维码的有效时间（1天）
            "action_name" => "QR_STR_SCENE",
            "action_info" => array("scene" => array("scene_str" => $this->state))
        );
        $result = $this->httpRequest($url, json_encode($data));
        $result = json_decode($result, true);
        if (!empty($result['ticket'])) {
            $this->ticket = $result['ticket'];
            return $result;
        }

        //如果access_token错误则在执行一次
        if (!empty($result['errmsg']) && stristr($result['errmsg'], 'access_token') && $repeat) {
            $this->getAccessToken();
            return $this->getQrcode(false);
        }

        //wp_die( __('二维码获取失败：', 'i_theme'). json_encode($result), __('获取失败', 'i_theme'), array('response'=>403));
        throw new GZHException(__('二维码获取失败：', 'i_theme') . json_encode($result));
    }

    /**
     * 换取二维码
     * @return
     */
    public function generateQrcode()
    {

        $this->getQrcode();

        return "https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=" . $this->ticket;
    }

    /**
     * 通过openId获取用户信息
     * @param mixed $openId
     * @return
     */
    public function getUserInfo($openId)
    {

        $url  = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=" . $this->accessToken . "&openid=" . $openId . "&lang=zh_CN";
        $data = json_decode($this->httpRequest($url),true);

        if ('gzh'===$this->type && !empty($data['openid'])) {
            return $data;
        }elseif('dyh'===$this->type){
            return $data;
        }

        throw new GZHException(__('用户信息获取失败：', 'i_theme') . json_encode($data));
    }

}
