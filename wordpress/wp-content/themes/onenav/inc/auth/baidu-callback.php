<?php  
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2021-03-01 10:19:02
 * @LastEditors: iowen
 * @LastEditTime: 2023-11-01 18:14:18
 * @FilePath: \onenav\inc\auth\baidu-callback.php
 * @Description: 
 */
include_once('../../../../../wp-config.php'); 
if(!session_id()) session_start();

if (empty($_SESSION['state'])) {
    wp_safe_redirect(home_url());
    exit;
}

$config   = io_get_option('open_baidu_key');
$callback = get_oauth_callback_url('baidu');
$oauth    = new \Yurun\OAuthLogin\Baidu\OAuth2($config['appid'], $config['appkey'], $callback);

try {
    $accessToken = $oauth->getAccessToken($_SESSION['state']);
    $userInfo    = $oauth->getUserInfo();
    $openId      = $oauth->openid;
} catch (Exception $err) {
    $title = get_current_user_id() ? __('绑定失败','i_theme') : __('登录失败','i_theme');
    wp_die(
        '<h1>' .$title. '</h1>' .
            '<p>' . $err->getMessage() . '</p>',
        403
    );
    exit;
}

if ($openId && $userInfo) {
    $userInfo['nick_name'] = !empty($userInfo['realname']) ? $userInfo['realname'] : (!empty($userInfo['username']) ? $userInfo['username'] : (!empty($userInfo['uname']) ? $userInfo['uname'] : ''));
    $userInfo['name']      = $userInfo['nick_name'];
    $userInfo['avatar']    = !empty($userInfo['portrait']) ? 'http://tb.himg.baidu.com/sys/portrait/item/' . $userInfo['portrait'] : '';

    $oauth_data = array(
        'type'        => 'baidu',
        'openid'      => $openId,
        'name'        => $userInfo['nick_name'],
        'avatar'      => $userInfo['avatar'],
        'description' => !empty($userInfo['userdetail']) ? $userInfo['userdetail'] : '',
        'getUserInfo' => $userInfo,
        'rurl'        => $_SESSION['rurl'], 
    );
    $oauth_result = io_oauth_update_user($oauth_data);
    io_oauth_login_after_execute($oauth_result);
} else {
    wp_die(
        '<h1>' . __('处理错误') . '</h1>' .
            '<p>' . json_encode($userInfo) . '</p>' .
            '<p>openid:' . $openId . '</p>',
        403
    );
    exit;
}

wp_safe_redirect(home_url());
exit;
?>