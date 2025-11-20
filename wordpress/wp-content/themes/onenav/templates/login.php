<?php
/*
 * @Theme Name:One Nav
 * @Theme URI:https://www.iotheme.cn/
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2021-06-03 08:56:00
 * @LastEditors: iowen
 * @LastEditTime: 2024-09-29 23:45:48
 * @FilePath: /onenav/templates/login.php
 * @Description: 
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

$user_ID = get_current_user_id();

$action = (isset($_GET['action']) ) ? htmlspecialchars($_GET['action']) : 'login'; 

$redirect_to = get_redirect_to(home_url());
$bind_var = array();

switch ($action) {
    case 'register':
        $title = __('注册', 'i_theme');
        $type = 'reg';
        break;
    case 'bind':
        $bind_var = get_bind_var($redirect_to);
        $title = $bind_var['bind_title'];
        $type = 'bind';
        break;
    case 'lostpassword':
        $title = __('找回密码', 'i_theme');
        $type = 'lost';
        break;

    case 'login':
    default:
        $title = __('登录', 'i_theme');
        $type = 'login';
        if ($user_ID) {
            if (is_super_admin())
                wp_safe_redirect(admin_url());
            else
                wp_safe_redirect($redirect_to);
        }
        break;
}

io_login_header($title);

io_login_body($type, $bind_var);

io_login_footer(); 
