<?php  
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2021-03-01 10:19:02
 * @LastEditors: iowen
 * @LastEditTime: 2023-01-28 22:51:51
 * @FilePath: \onenav\inc\auth\gitee.php
 * @Description: 
 */
include_once('../../../../../wp-config.php');
if(!session_id()) session_start();

$config            = io_get_option('open_gitee_key');
$callback          = get_oauth_callback_url('gitee');
$oauth             = new \Yurun\OAuthLogin\Gitee\OAuth2($config['appid'], $config['appkey'], $callback);

$url               = $oauth->getAuthUrl();
$_SESSION['state'] = $oauth->state;
$_SESSION['rurl']  = isset($_REQUEST["loginurl"]) ? $_REQUEST["loginurl"] : '';

header('location:' . $url);
?>