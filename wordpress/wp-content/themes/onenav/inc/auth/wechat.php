<?php  
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2021-03-01 10:19:02
 * @LastEditors: iowen
 * @LastEditTime: 2023-01-26 10:04:53
 * @FilePath: \onenav\inc\auth\wechat.php
 * @Description: 微信开放平台
 */
include_once('../../../../../wp-config.php');
if(!session_id()) session_start();

$config            = io_get_option('open_wechat_key');
$callback          = get_oauth_callback_url('wechat');
$oauth             = new \Yurun\OAuthLogin\Weixin\OAuth2($config['appid'], $config['appkey']);

$url               = $oauth->getAuthUrl($callback);
$_SESSION['state'] = $oauth->state;
$_SESSION['rurl']  = isset($_REQUEST["loginurl"]) ? $_REQUEST["loginurl"] : '';

header('location:' . $url);
?>