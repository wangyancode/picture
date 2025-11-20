<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2022-06-19 16:23:07
 * @LastEditors: iowen
 * @LastEditTime: 2022-06-19 17:31:10
 * @FilePath: \onenav\inc\auth\prk.php
 * @Description: 
 */
include_once('../../../../../wp-config.php'); 

if(!session_id()) session_start();

$type 				= isset($_GET['type'])?$_GET['type']:'qq';
$pyk 				= io_get_option('open_prk_key');
$_SESSION['state']  = md5 ( uniqid ( rand (), true ) ); //CSRF protection
$_SESSION['rurl']   = $_REQUEST ["loginurl"];
$pyk_config 		= array(
	'apiurl' 	=> $pyk['apiurl'],
	'appid' 	=> $pyk['appid'],
	'appkey' 	=> $pyk['appkey'],
	'state' 	=> $_SESSION ['state'],
	'callback' 	=> get_theme_file_uri('/inc/auth/prk-callback.php'),
);


$Oauth	= new ioLoginPrk($pyk_config);
$arr 	= $Oauth->login($type);
if(isset($arr['code']) && $arr['code']==0){
	exit("<script language='javascript'>window.location.href='{$arr['url']}';</script>");
}elseif(isset($arr['code'])){
	exit('登录接口返回：'.$arr['msg']);
}else{
	exit('获取登录地址失败');
}
