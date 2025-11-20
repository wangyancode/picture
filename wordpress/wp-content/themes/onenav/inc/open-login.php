<?php 
/*
 * @Theme Name:One Nav
 * @Theme URI:https://www.iotheme.cn/
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2021-06-03 08:55:58
 * @LastEditors: iowen
 * @LastEditTime: 2025-03-26 19:36:57
 * @FilePath: /onenav/inc/open-login.php
 * @Description: 
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

require_once get_theme_file_path('/inc/classes/open.wechat.gzh.class.php');
require_once get_theme_file_path('/inc/classes/open.prk.oauth.class.php');

//session_write_close()

//检测IO_ID是否重复
function userioid_exists( $ioid ) {
    global $wpdb;
    $user = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM $wpdb->users WHERE io_id = %s LIMIT 1",
            $ioid
        )
    );
    if ( ! $user ) {
        return false;
    }else{
        return $user->ID;
    }
} 
//添加 io_id
add_action('user_register', 'io_register_extra_fields');
function io_register_extra_fields($user_id) {
	global $wpdb;

    $prename = 'io';
    $extname = rand(100000,999998);
    $ioid = $prename.$extname;
    if(userioid_exists($ioid)){
        while(userioid_exists($ioid)){ $extname++; }
        $ioid = $prename.$extname;
    }
    $wpdb->query("UPDATE $wpdb->users SET io_id = '$ioid' WHERE ID = '$user_id'"); 
} 

/**
 * 社交登录按钮
 * @return string
 */
function get_social_login_btn()
{
    $buttons = '';
    $datas = get_social_type_data();
    
    $weixin_type = io_get_option('open_weixin_gzh_key', 'gzh', 'type');

    foreach ($datas as $data) {
        $type = $data['type'];
        $name = $data['name'];
        $icon = '<i class="iconfont ' . $data['icon'] . '"></i>';
        if ('alipay' == $type) {
            if (wp_is_mobile() && !strpos($_SERVER['HTTP_USER_AGENT'], 'Alipay')) {
                continue;
            }
            //移动端并且不是支付宝APP不显示支付宝
        }

        $href = io_get_oauth_login_url($type);
        if ($href) {
            $_class = $data['class'];
            if ('weixin_gzh' === $type) {
                $_class .= ' qrcode-signin weixin-' . $weixin_type;
            }
            $buttons .= '<a href="' . esc_url($href) . '" title="' . $name . __('登录','i_theme').'" class="open-login ' . $_class . '">' . $icon . '</a>';
        }
    }
    $buttons .= get_prk_btn_html();
    return $buttons;
}
function openloginFormButton(){
    if ( isset($_GET['action']) &&'register' === $_GET['action'])
        return;
    $btn = get_social_login_btn();
    if(empty($btn)) return;
    echo '<div id="openlogin-box" class="openlogin-box text-center">'; 
    echo '<span class="social-separator separator text-muted text-xs mb-3">'.__('社交帐号登录','i_theme').'</span>';
    echo $btn;
    echo '</div>';
    if (io_get_oauth_login_url('weixin_gzh')) {
        $type = io_get_option('open_weixin_gzh_key', 'gzh', 'type');
        if(!(io_is_wechat_app() && 'gzh'===$type))
            get_weixin_qr_js();
    }
}
add_filter('io_login_form', 'openloginFormButton');

/**
 * 判断用户是否已经绑定了开放平台账户.
 *
 * @param string $type
 * @param int    $user_id
 *
 * @return bool|string
 */
function io_has_connect($type = 'qq', $user_id = 0){
    $user_id = $user_id ?: get_current_user_id();
    if($type){
        if ('weixin_gzh' == $type) {
            $type = 'wechat_'.io_get_option('open_weixin_gzh_key', 'gzh', 'type');
        }
        return get_user_meta($user_id, $type.'_openid', true);
    }

    return false;
} 
/**
 * 获取社交登录名称
 * @param mixed $type
 * @return mixed
 */
function get_open_login_name($type){
    $datas = get_social_type_data();
    if('wx'===$type){
        $type = 'wechat';
    }
    if('sina'===$type){
        $type = 'weibo';
    }
    return $datas[$type]['name']; 
}

function get_prk_btn_html(){
    $btn = '';
    if(io_get_option('open_prk',false)){
        $list = io_get_option('open_prk_list','');
        if(is_array($list)){
            foreach($list as $type){
                $ico = $type;
                if('wx'===$type){
                    $ico = 'wechat';
                }
                if('sina'===$type){
                    $ico = 'weibo';
                }
                $btn .= '<a href="'.get_theme_file_uri('/inc/auth/prk.php').'?type='.$type.'&loginurl='.(isset($_REQUEST["redirect_to"])?$_REQUEST["redirect_to"]:io_get_option('open_login_url','')).'" title="'.sprintf(__('%s快速登录','i_theme'),get_open_login_name($type)).'" rel="nofollow" class="open-login prk-login openlogin-'.$ico.'-a"><i class="iconfont icon-'.$ico.'"></i></a>';
            }
        }
    }
    return $btn;
}
/**
 * 获取社交头像列表
 * @param mixed $current_user
 * @return array
 */
function get_open_avatar_list($current_user){ 
    $open_avatar = array();
    if(isset($current_user->qq_avatar)){
        $open_avatar['qq'] = $current_user->qq_avatar;
    }
    if(isset($current_user->wechat_avatar)){
        $open_avatar['wechat'] = $current_user->wechat_avatar;
    }
    if(isset($current_user->wechat_gzh_avatar)){
        $open_avatar['wechat_gzh'] = $current_user->wechat_gzh_avatar;
    }
    if(isset($current_user->wx_avatar)){
        $open_avatar['wx'] = $current_user->wx_avatar;
    }
    if(isset($current_user->alipay_avatar)){
        $open_avatar['alipay'] = $current_user->alipay_avatar;
    }
    if(isset($current_user->sina_avatar)){
        $open_avatar['sina'] = $current_user->sina_avatar;
    }
    if(isset($current_user->baidu_avatar)){
        $open_avatar['baidu'] = $current_user->baidu_avatar;
    }
    if(isset($current_user->huawei_avatar)){
        $open_avatar['huawei'] = $current_user->huawei_avatar;
    }
    if(isset($current_user->google_avatar)){
        $open_avatar['google'] = $current_user->google_avatar;
    }
    if(isset($current_user->microsoft_avatar)){
        $open_avatar['microsoft'] = $current_user->microsoft_avatar;
    }
    if(isset($current_user->facebook_avatar)){
        $open_avatar['facebook'] = $current_user->facebook_avatar;
    }
    if(isset($current_user->twitter_avatar)){
        $open_avatar['twitter'] = $current_user->twitter_avatar;
    }
    if(isset($current_user->dingtalk_avatar)){
        $open_avatar['dingtalk'] = $current_user->dingtalk_avatar;
    }
    if(isset($current_user->github_avatar)){
        $open_avatar['github'] = $current_user->github_avatar;
    }
    if(isset($current_user->gitee_avatar)){
        $open_avatar['gitee'] = $current_user->gitee_avatar;
    }
    return apply_filters('io_open_login_avatar_list_filters', $open_avatar); 
}

/**
 * filter nickname
 * @param mixed $nickname
 * @return string
 */
function io_filter_nickname($nickname){
    $nickname = preg_replace('/[\x{1F600}-\x{1F64F}]/u', '', $nickname);
    $nickname = preg_replace('/[\x{1F300}-\x{1F5FF}]/u', '', $nickname);
    $nickname = preg_replace('/[\x{1F680}-\x{1F6FF}]/u', '', $nickname);
    $nickname = preg_replace('/[\x{2600}-\x{26FF}]/u', '', $nickname);
    $nickname = preg_replace('/[\x{2700}-\x{27BF}]/u', '', $nickname);
    $nickname = str_replace(array('"','\''), '', $nickname);
    $nickname = preg_replace_callback( '/./u', function (array $match) {
        return strlen($match[0]) >= 4 ? '' : $match[0];
    }, $nickname);
    return addslashes(trim($nickname));
}



/**
 * 处理返回数据，更新用户资料
 */
function io_oauth_update_user($args, $is_weixin_dyh = false)
{
    /** 需求数据明细 */
    $defaults = array(
        'type'        => '',
        'openid'      => '',
        'name'        => '',
        'avatar'      => '',
        'description' => '',
        'getUserInfo' => array(),
        'rurl'        => '',
    );
    $_rurl = isset($args['rurl']) && !empty($args['rurl']) ? $args['rurl'] : esc_url(home_url());//get_author_posts_url($current_user_id)//重定向链接到用户中心

    $args = wp_parse_args((array) $args, $defaults);

    // 初始化信息
    $openid_meta_key =  $args['type'] . '_openid';
    $openid = $args['openid'];
    $return_data = array(
        'redirect_url' => '',
        'msg' => '',
        'bind' => false,
        'error' => true,
    );

    global $wpdb, $current_user;

    // 查询该openid是否已存在
    $user_exist = $wpdb->get_var($wpdb->prepare("SELECT user_id FROM $wpdb->usermeta WHERE meta_key=%s AND meta_value=%s", $openid_meta_key, $openid));

    // 查询已登录用户
    $current_user_id = get_current_user_id();

    //如果已经登录，且该openid已经存在
    if ($current_user_id && isset($user_exist) && $current_user_id != $user_exist) {
        $return_data['msg'] = __('绑定失败，可能之前已有其他账号绑定，请先登录并解绑。','i_theme');
        return $return_data;
    }

    if (isset($user_exist) && (int) $user_exist > 0) {
        // 该开放平台账号已连接过WP系统，再次使用它直接登录
        $user_exist = (int) $user_exist;

        //登录
        $user = get_user_by('id', $user_exist);
        wp_set_current_user($user_exist);
        wp_set_auth_cookie($user_exist, true);
        do_action('wp_login', $user->user_login, $user);

        //绑定尝试社交登录的账号
        io_update_oauth_data($user_exist);

        $return_data['redirect_url'] = $_rurl;  
        $return_data['error'] = false;
    } elseif ($current_user_id) {
        // 已经登录，但openid未占用，则绑定，更新用户字段
        // 更新用户mate
        $args['user_id'] = $current_user_id;

        //绑定用户不更新以下数据
        $args['name'] = '';
        $args['description'] = '';

        io_oauth_update_user_meta($args);
        // 准备返回数据
        $return_data['redirect_url'] = $_rurl;
        $return_data['error'] = false;
    } else {
        if(io_is_close_register()){
            $return_data['msg'] = __('禁止注册','i_theme');
            return $return_data;
        }
        // 既未登录且openid未占用，则新建用户并绑定
        if(io_user_center_enable() && io_get_option('bind_email','must')=='must'){
            //添加到临时数据
            if(!session_id()) session_start();
            $_SESSION['temp_oauth'] = maybe_serialize($args);
            if($is_weixin_dyh){
                $return_data['redirect_url'] = $_rurl;
                $return_data['bind'] = true;
                $return_data['error'] = false;
                return $return_data;
            }else{
                wp_safe_redirect(home_url().'/login/?action=bind&redirect_to='.$args['rurl']);
                exit();
            }
        }
        $prename = 'io';
        $extname = rand(1000000,9999998);
        $login_name = $prename.$extname;
        if(username_exists($login_name)){
            while(username_exists($login_name)){ $extname++; }
            $login_name = $prename.$extname;
        }
        $user_pass = wp_generate_password();  

        $user_id = wp_create_user($login_name, $user_pass);
        if (is_wp_error($user_id)) {
            //新建用户出错
            $return_data['msg'] = $user_id->get_error_message();
        } else {
            //新建用户成功
            update_user_meta($user_id, 'oauth_new', $args['type']);
            /**标记为系统新建用户 */
            //更新用户mate
            $args['user_id'] = $user_id;
            $args['login_name'] = $login_name;
            io_oauth_update_user_meta($args, true);

            //登录
            $user = get_user_by('id', $user_id);
            wp_set_current_user($user_id, $user->user_login);
            wp_set_auth_cookie($user_id, true);
            do_action('wp_login', $user->user_login, $user);
            // 准备返回数据
            $return_data['redirect_url'] = $_rurl;
            $return_data['bind'] = true;
            $return_data['error'] = false;
        }
    }
    return $return_data;
}

/**
 * 更新第三方账号数据
 * @param mixed $args
 * @param mixed $is_new
 * @return void
 */
function io_oauth_update_user_meta($args, $is_new = false)
{
    /** 需求数据明细 */
    $defaults = array(
        'user_id'     => '',
        'type'        => '',
        'openid'      => '',
        'name'        => '',
        'login_name'  => '',
        'avatar'      => '',
        'description' => '',
        'getUserInfo' => array(),
    );
    $args = wp_parse_args((array) $args, $defaults);

    update_user_meta($args['user_id'],  $args['type'] . '_openid', $args['openid']);
    update_user_meta($args['user_id'],  $args['type'] . '_getUserInfo', $args['getUserInfo']);
    update_user_meta($args['user_id'], 'name_change', 1); 

    //自定义头像，无则添加
    $custom_avatar = get_user_meta($args['user_id'], 'custom_avatar', true);
    if ($args['avatar'] && !$custom_avatar) {
        update_user_meta($args['user_id'], 'custom_avatar', $args['avatar']);
    }
    if($args['avatar']){
        update_user_meta($args['user_id'], $args['type'] . '_avatar', $args['avatar']);
    }

    //自定义简介，无则添加
    $description = get_user_meta($args['user_id'], 'description', true);
    if ($args['description'] && !$description) {
        update_user_meta($args['user_id'], 'description', $args['description']);
    }

    if ($is_new) {
        if($args['avatar'])
            update_user_meta($args['user_id'], 'avatar_type', $args['type']);
        else
            update_user_meta($args['user_id'], 'avatar_type', 'letter');
        //新建用户，更新display_name
        $nickname = trim($args['name']);
        if (is_username_legal($nickname)['error']) {
            //判断用户名是否合法，将io换成用户作为昵称
            $nickname = $args['login_name'] ? str_replace('io', __('用户','i_theme'), $args['login_name'])  : __('用户','i_theme') .  rand(1000000,9999998);
        }

        $user_datas = array(
            'ID' => $args['user_id'],
            'display_name' => $nickname,
            'nickname'     => $nickname,
        );
        wp_update_user($user_datas);
    }
}
/**
 * 社交登录后执行
 * @description: 
 * @param array $oauth_result 社交登录结果
 * @param bool $is_redirect 是否重定向
 * @return 
 */
function io_oauth_login_after_execute($oauth_result, $is_redirect = true)
{
    if ($oauth_result['error']) {
        wp_die('<meta charset="UTF-8" />' . ($oauth_result['msg'] ? $oauth_result['msg'] : '处理失败'));
        exit;
    } else {
        $rurl = isset($oauth_result['rurl']) && !empty($oauth_result['rurl']) ? $oauth_result['rurl'] : $oauth_result['redirect_url'];
        if (io_user_center_enable() && $oauth_result['bind'] && io_get_option('bind_email', 'bind') === 'must') {
            $rurl = home_url() . '/login/?action=bind&redirect_to=' . $rurl;
        }
        if ($is_redirect) {
            wp_safe_redirect($rurl);
            exit;
        } else {
            return array(
                'status' => true,
                'rurl'   => $rurl,
            );
        }
    }
}
/**
 * 第一次社交登录时绑定到已有账号
 * @param int $user_id 旧账号 ID
 * @return null
 */
function io_update_oauth_data($user_id){

    if(!session_id()) session_start();
    if( isset($_SESSION['temp_oauth']) && !empty($_SESSION['temp_oauth'])){
        $args =  maybe_unserialize($_SESSION['temp_oauth']);
        $args['user_id'] = $user_id;
        //绑定用户不更新以下数据
        $args['name'] = '';
        $args['description'] = '';
        if (!io_has_connect($args['type'], $user_id)) {
            io_oauth_update_user_meta($args);
            unset($_SESSION['temp_oauth']);
        }else{
            unset($_SESSION['temp_oauth']);
            io_error('{"status":3,"msg":"'.sprintf(__('该用户已经绑定了 %s 账号','i_theme'),$args['type']).'","goto":"'.esc_url(home_url()).'/user/security"}'); 
        }
    }
}
