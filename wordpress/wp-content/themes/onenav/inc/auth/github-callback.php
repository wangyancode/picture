<?php  
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2021-03-01 10:19:02
 * @LastEditors: iowen
 * @LastEditTime: 2023-01-28 23:01:52
 * @FilePath: \onenav\inc\auth\github-callback.php
 * @Description: 
 */
include_once('../../../../../wp-config.php'); 
if(!session_id()) session_start();

if (empty($_SESSION['state'])) {
    wp_safe_redirect(home_url());
    exit;
}

$config   = io_get_option('open_github_key');
$callback = get_oauth_callback_url('github');
$oauth    = new \Yurun\OAuthLogin\Github\OAuth2($config['appid'], $config['appkey'], $callback);

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
    $oauth_data = array(
        'type'        => 'github',
        'openid'      => $openId,
        'name'        => !empty($userInfo['name']) ? $userInfo['name'] : '',
        'avatar'      => !empty($userInfo['avatar_url']) ? $userInfo['avatar_url'] : '',
        'description' => '',
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