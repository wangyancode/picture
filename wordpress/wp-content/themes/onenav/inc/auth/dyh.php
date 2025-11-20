<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2021-03-01 10:19:02
 * @LastEditors: iowen
 * @LastEditTime: 2024-11-05 01:38:48
 * @FilePath: /onenav/inc/auth/dyh.php
 * @Description: 
 */
include_once('../../../../../wp-config.php');
//启用 session
if(!session_id()) session_start(); 

$config           = io_get_option('open_weixin_gzh_key');
$callback         = get_oauth_callback_url('dyh');
$_SESSION['rurl'] = isset($_REQUEST["loginurl"]) ? $_REQUEST["loginurl"] : ''; // 储存返回页面

$qrcode           = $config['qr_code'];

$action = !empty($_REQUEST["bind"]) ? '绑定' : '登录';
$text = '微信扫码' . $action;
$html = '<div class="text-center mb-2"><i class="text-success iconfont icon-qr-sweep mr-2"></i>' . $text . '</div>';
$html .= '<div class="text-center"><img class="signin-qrcode-img" src="' . $qrcode . '" alt="' . $text . '" style="width:198px"></div>';
$html .= '<div class="text-center text-xs text-muted mt-1 mb-3"> 如已关注，请回复“'.$action.'”二字获取验证码 </div>';
$html .= '<div class="io-wx-box"><input type="text" id="io_ws_code" class="io-wx-input form-control" placeholder="验证码"/><button type="button" class="btn vc-blue btn-sm io-wx-btn ml-1">验证'.$action.'</button></div>'; 
//$but  = '<div class="text-center mt-2"><a href="'. esc_url(home_url()) .'/login/" class="btn btn-outline-danger px-4 px-lg-5 ml-auto">返回</a></div>';
$but  = '<div class="text-muted mt-3"><small>使用其他方式 <a href="'. esc_url(home_url()) .'/login/" class="signup">'.__('登录','i_theme').'</a> / <a href="'. esc_url(home_url()) .'/login/?action=register" class="signup">'.__('注册','i_theme').'</a></small></div>';
echo (json_encode(array('html' => $html, 'but'=>$but, 'url' => $callback)));
exit();
