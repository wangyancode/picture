<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2023-01-14 17:19:39
 * @LastEditors: iowen
 * @LastEditTime: 2023-02-04 01:02:18
 * @FilePath: \onenav\inc\action\ajax-admin.php
 * @Description: 
 */

//test_send_sms
function io_test_send_sms()
{
    if (empty($_POST['phone_number'])) {
        echo (json_encode(array('error' => 1, 'msg' => '请输入手机号码')));
        exit();
    }

    echo json_encode(IOSMS::send($_POST['phone_number'], '8888'));
    exit();
}
add_action('wp_ajax_test_send_sms', 'io_test_send_sms');

/**
 * 设置微信公众号菜单
 * @return void
 */
function io_weixin_gzh_create_menu()
{
    $json = $_REQUEST['json'];
    if (!$json) {
        io_error(array('error'=>1,'msg'=>'输入json配置代码'));
    }
    $data = json_decode(wp_unslash(trim($json)), true);
    if (!$data || !is_array($data)) {
        io_error(array('error'=>1,'msg'=>'json格式错误'));
    }
    $config = io_get_option('open_weixin_gzh_key',array());
    try {
        require_once get_theme_file_path('/inc/classes/open.wechat.gzh.class.php');
        $oauth = new ioLoginWechatGZH($config['appid'], $config['appkey'],io_get_option('open_weixin_gzh_key', 'gzh', 'type'));
        $menu  = $oauth->createMenu($data);
        if (isset($menu['errcode'])) {
            if (0 == $menu['errcode']) {
                update_option('io_gzh_menu_json',base64_encode($json));
                io_error(array('error'=>0,'msg'=>'设置成功，5-10分钟后生效，请耐心等待'));
            } else {
                io_error(array('error'=>1,'msg'=>'设置失败<br>错误码：' . $menu['errcode'] . '<br>错误消息：' . $menu['errmsg']));
            }
        }
    } catch (\Exception $e) {
        io_error(array('error'=>1,'msg'=>$e->getMessage()));
    }

}
add_action('wp_ajax_set_weixin_gzh_menu', 'io_weixin_gzh_create_menu');