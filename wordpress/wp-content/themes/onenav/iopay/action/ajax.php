<?php 
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2023-02-25 22:49:54
 * @LastEditors: iowen
 * @LastEditTime: 2024-10-24 17:23:15
 * @FilePath: /onenav/iopay/action/ajax.php
 * @Description: 
 */


require get_theme_file_path('/iopay/action/ajax-pay.php');
require get_theme_file_path('/iopay/action/ajax-posts.php');

/**
 * 获取自动广告支付模态框
 * 
 * @return never
 */
function iopay_ajax_pay_auto_ad_modal(){
    $loc   = isset($_REQUEST['loc']) ? $_REQUEST['loc'] : 'home';
    $modal = iopay_pay_auto_ad_modal($loc);
    echo $modal;
    exit;
}
add_action('wp_ajax_pay_auto_ad_modal', 'iopay_ajax_pay_auto_ad_modal');
add_action('wp_ajax_nopriv_pay_auto_ad_modal', 'iopay_ajax_pay_auto_ad_modal');

/**
 * 获取自动广告列表
 * 
 * @return never
 */
function io_ajax_get_auto_ad_url_list_html(){
    $loc = isset($_REQUEST['loc']) ? $_REQUEST['loc'] : 'home';
    echo iopay_get_auto_ad_url_list($loc);
    exit();
}
add_action('wp_ajax_get_auto_ad_url_list', 'io_ajax_get_auto_ad_url_list_html');
add_action('wp_ajax_nopriv_get_auto_ad_url_list', 'io_ajax_get_auto_ad_url_list_html');

/**
 * 微信官方支付获取openid
 * @return never
 */
function io_ajax_get_gzh_open_id(){
    $return_url = !empty($_REQUEST['return_url']) ? $_REQUEST['return_url'] : '';
    $code       = !empty($_REQUEST['code']) ? $_REQUEST['code'] : '';

    $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?';

    $config = iopay_get_option('official_wechat');
    if (!$config['appsecret']) {
        $wxConfig            = io_get_option('open_weixin_gzh_key');
        $config['appid']     = $wxConfig['appid'];
        $config['appsecret'] = $wxConfig['appkey'];
    }

    $url_data = array(
        'appid'      => $config['appid'],
        'secret'     => $config['appsecret'],
        'code'       => $code,
        'grant_type' => 'authorization_code',
    );
    $http     = new Yurun\Util\HttpRequest;
    $response = $http->timeout(10000)->get($url, $url_data);
    $result   = $response->json(true);

    if (!empty($result['openid'])) {
        $return_url = add_query_arg(array('iopay' => 'wechat', 'openid' => $result['openid']), $return_url);
        header('location:' . $return_url);
        exit();
    } else {
        wp_die(
            '<h3>' . __('微信支付错误：','i_theme') . '</h3>' .
            '<p>' . json_encode($result) . '</p>',
            403
        );
        exit;
    }
}
add_action('wp_ajax_get_gzh_open_id', 'io_ajax_get_gzh_open_id');
add_action('wp_ajax_nopriv_get_gzh_open_id', 'io_ajax_get_gzh_open_id');
