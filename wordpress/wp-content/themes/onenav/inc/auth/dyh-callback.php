<?php   
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2021-03-01 10:19:02
 * @LastEditors: iowen
 * @LastEditTime: 2025-03-26 19:06:50
 * @FilePath: /onenav/inc/auth/dyh-callback.php
 * @Description: 
 */
include_once('../../../../../wp-config.php'); 
if(!session_id()) session_start();

$config = io_get_option('open_weixin_gzh_key');

if (!empty($_REQUEST['echostr']) && !empty($_REQUEST['signature'])) {
    header("Content-type:text/html;charset=utf-8");
    //微信接口校验
    $signature = $_GET["signature"];
    $timestamp = $_GET["timestamp"];
    $nonce     = $_GET["nonce"];
    $token     = $config['token'];
    $tmpArr    = array($token, $timestamp, $nonce);

    sort($tmpArr, SORT_STRING);
    $tmpStr = implode($tmpArr);
    $tmpStr = sha1($tmpStr);

    if ($tmpStr == $signature) {
        echo $_REQUEST['echostr'];
    }
    exit();
}

$oauth  = new ioLoginWechatGZH($config['appid'], $config['appkey'], 'dyh');
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'callback';

switch ($action) {
    case 'callback':
        $callback = $oauth->callback();
        if ($callback) {
            $oauth->responseMsg($config['auto_reply']);
        }
        exit;
    case 'check_callback':
        $code       = $_REQUEST['code'];
        $back_url   = $_REQUEST['loginurl'];
        $status     = 0; 

        $wechat_dyh_event_data = get_option('wechat_dyh_event_data'); //读取临时数据
        if (!isset($wechat_dyh_event_data[$code])) {
            $title = get_current_user_id() ? __('绑定失败','i_theme') : __('登录失败','i_theme');
            io_tips_error($title, false);
            exit;
        }
        //删除已使用过的数据
        $openId = $wechat_dyh_event_data[$code]['FromUserName'];
        unset($wechat_dyh_event_data[$code]);
        update_option('wechat_dyh_event_data', $wechat_dyh_event_data, false);

        if($openId){
            try {
                $userInfo = $oauth->getUserInfo($openId); //第三方用户信息
            } catch (Exception $err) {
                io_tips_error($err->getMessage(), false);
                exit;
            }
    
            if (!empty($userInfo)) {
                $userInfo['name']   = isset($userInfo['nickname']) ? $userInfo['nickname'] : '';
                $userInfo['avatar'] = isset($userInfo['headimgurl']) ? $userInfo['headimgurl'] : '';
    
                $oauth_data = array(
                    'type'          => 'wechat_dyh',
                    'openid'        => $openId,
                    'name'          => $userInfo['name'],
                    'avatar'        => $userInfo['avatar'],
                    'description'   => '',
                    'getUserInfo'   => $userInfo,
                    'rurl'          => $_SESSION['rurl'], 
                );
    
                $oauth_result = io_oauth_update_user($oauth_data,true);
                $execute      = io_oauth_login_after_execute($oauth_result,false);
                if($execute['status']){
                    $status     = 1;
                    $back_url   = $execute['rurl'];
                }
            }
        }
        $result = array(
            'status' => $status,
            'goto'   => $back_url
        );
        io_error($result);
        exit;
}
wp_safe_redirect(home_url());
exit;