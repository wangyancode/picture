<?php  
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2021-03-01 10:19:02
 * @LastEditors: iowen
 * @LastEditTime: 2023-01-26 10:12:19
 * @FilePath: \onenav\inc\auth\qq.php
 * @Description: 
 */
include_once('../../../../../wp-config.php');
if(!session_id()) session_start();

$config            = io_get_option('open_qq_key');
$callback          = get_oauth_callback_url('qq');
$oauth             = new \Yurun\OAuthLogin\QQ\OAuth2($config['appid'], $config['appkey'], $callback);

$url               = $oauth->getAuthUrl();
$_SESSION['state'] = $oauth->state;
$_SESSION['rurl']  = isset($_REQUEST["loginurl"]) ? $_REQUEST["loginurl"] : '';

header('location:' . $url);
?>