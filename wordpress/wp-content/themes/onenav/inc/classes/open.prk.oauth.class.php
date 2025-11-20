<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2022-06-19 13:26:09
 * @LastEditors: iowen
 * @LastEditTime: 2023-01-27 17:29:37
 * @FilePath: \onenav\inc\classes\open.prk.oauth.class.php
 * @Description: 
 */
class ioLoginPrk{
	private $apiurl;
	private $appid;
	private $appkey;
	private $callback;
	private $state;

	function __construct($config){
		$this->apiurl 	= $config['apiurl'].'connect.php';
		$this->appid 	= $config['appid'];
		$this->appkey 	= $config['appkey'];
		$this->state 	= $config['state'];
		$this->callback = $config['callback'];
	}

	//获取登录跳转url
	public function login($type){
		//-------构造请求参数列表
		$keysArr = array(
			"act" => "login",
			"appid" => $this->appid,
			"appkey" => $this->appkey,
			"type" => $type,
			"redirect_uri" => $this->callback,
			"state" => $this->state
		);
		$login_url = $this->apiurl.'?'.http_build_query($keysArr);
		$response = $this->get_curl($login_url);
		$arr = json_decode($response,true);
		return $arr;
	}

	//登录成功返回网站
	public function callback(){
		//-------请求参数列表
		$keysArr = array(
			"act" => "callback",
			"appid" => $this->appid,
			"appkey" => $this->appkey,
			"code" => $_GET['code']
		);

		//------构造请求access_token的url
		$token_url = $this->apiurl.'?'.http_build_query($keysArr);
		$response = $this->get_curl($token_url);

		$arr = json_decode($response,true);
		return $arr;
	}

	//查询用户信息
	public function query($type, $social_uid){
		//-------请求参数列表
		$keysArr = array(
			"act" => "query",
			"appid" => $this->appid,
			"appkey" => $this->appkey,
			"type" => $type,
			"social_uid" => $social_uid
		);

		//------构造请求access_token的url
		$token_url = $this->apiurl.'?'.http_build_query($keysArr);
		$response = $this->get_curl($token_url);

		$arr = json_decode($response,true);
		return $arr;
	}

    public function use_db($userInfo,$back_url){ 
		if(isset($userInfo['code']) && $userInfo['code']==0){
        
            $oauth_data = array(
                'type'   		=> 'io_'.$userInfo['type'],
                'openid' 		=> $userInfo['social_uid'],
                'name' 			=> !empty($userInfo['nickname']) ? $userInfo['nickname'] : '',
                'avatar' 		=> !empty($userInfo['faceimg']) ? $userInfo['faceimg'] : '',
                'description' 	=> '',
                'getUserInfo' 	=> $userInfo,
                'rurl'          => $back_url, 
            );
        
            $oauth_result = io_oauth_update_user($oauth_data);
        
            io_oauth_login_after_execute($oauth_result);
        } else {
            wp_die(
                '<h1>' . __('处理错误') . '</h1>' .
                    '<p>' . json_encode($userInfo) . '</p>' .
                    '<p>msg:' . $userInfo['msg'] . '</p>',
                403
            );
            exit;
        }
        
        wp_safe_redirect(home_url());
        exit;
    } 

	private function get_curl($url){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.132 Safari/537.36");
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$ret = curl_exec($ch);
		curl_close($ch);
		return $ret;
	}
}
