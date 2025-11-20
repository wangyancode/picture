<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2021-03-01 10:19:02
 * @LastEditors: iowen
 * @LastEditTime: 2023-02-17 06:45:46
 * @FilePath: \onenav\inc\auth\gzh.php
 * @Description: 
 */
include_once('../../../../../wp-config.php');
//启用 session
if(!session_id()) session_start(); 

$config           = io_get_option('open_weixin_gzh_key');
$callback         = get_oauth_callback_url('gzh');
$_SESSION['rurl'] = isset($_REQUEST["loginurl"]) ? $_REQUEST["loginurl"] : ''; // 储存返回页面

try {
    if (io_is_wechat_app()) {
        $oauth             = new \Yurun\OAuthLogin\Weixin\OAuth2($config['appid'], $config['appkey']);
        $url               = $oauth->getWeixinAuthUrl($callback);
        $_SESSION['state'] = $oauth->state;

        header('location:' . $url);
        exit();
    } else {
        $oauth        = new ioLoginWechatGZH($config['appid'], $config['appkey'],'gzh');
        $qrcode_array = $oauth->getQrcode();
        $qrcode       = io_get_qrcode_base64($qrcode_array['url']);
        $_SESSION['state'] = $oauth->state;

        $text = '微信扫码' . (!empty($_REQUEST["bind"]) ? '绑定' : '登录');
        $html = '<div class="text-center mb-2"><i class="text-success iconfont icon-qr-sweep mr-2"></i>' . $text . '</div>';
        $html .= '<div class="text-center"><img class="signin-qrcode-img" src="' . $qrcode . '" alt="' . $text . '" style="width:198px"></div>';
        $but  = '<div class="text-muted"><small>使用其他方式 <a href="'. esc_url(home_url()) .'/login/" class="signup">'.__('登录','i_theme').'</a> / <a href="'. esc_url(home_url()) .'/login/?action=register" class="signup">'.__('注册','i_theme').'</a></small></div>';
        echo (json_encode(array('html' => $html, 'but'=>$but, 'url' => $callback, 'state' => $oauth->state)));
    }
    exit();
} catch (\Exception $e) {
    echo (json_encode(array('error' => 1, 'msg' => $e->getMessage())));
    exit();
}
