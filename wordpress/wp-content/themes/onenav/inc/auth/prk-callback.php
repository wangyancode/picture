<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2022-06-19 16:23:07
 * @LastEditors: iowen
 * @LastEditTime: 2022-06-19 17:30:05
 * @FilePath: \onenav\inc\auth\prk-callback.php
 * @Description: 
 */

include_once('../../../../../wp-config.php');
if(!session_id()) session_start();

$type 		= isset($_GET['type'])?$_GET['type']:'qq';
$pyk 		= io_get_option('open_prk_key');
$pyk_config = array(
	'apiurl' 	=> $pyk['apiurl'],
	'appid' 	=> $pyk['appid'],
	'appkey' 	=> $pyk['appkey'],
	'state' 	=> $_SESSION ['state'],
	'callback' 	=> get_theme_file_uri('/inc/auth/prk-callback.php'),
);
if($_GET['code']){
	if($_GET['state'] != $_SESSION['state']){
		exit("The state does not match. You may be a victim of CSRF.");
	}
	unset($_SESSION['state']);
	$Oauth	= new ioLoginPrk($pyk_config);
	$arr 	= $Oauth->callback();
    $Oauth->use_db($arr,$_SESSION['rurl']);
}