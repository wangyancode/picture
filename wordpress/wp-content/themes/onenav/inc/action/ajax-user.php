<?php
/*!
 * Theme Name:One Nav
 * Theme URI:https://www.iotheme.cn/
 * Author:iowen
 * Author URI:https://www.iowen.cn/
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
/**
 * 登录
 * @return 
 */
function user_login_callback(){ 
    $username = esc_sql($_POST['username']);
    $password = $_POST['password'];
    $remember = esc_sql($_POST['rememberme']);
    if($remember){
        $remember = true;
    } else {
        $remember = false;
    }
    if($username=='' || $password==''){ 
        io_error('{"status":2,"msg":"'.__('请认真填写表单！','i_theme').'"}');
    }
    io_ajax_agreement_judgment();
    //执行人机验证
    io_ajax_is_robots();

    if(is_email($username)){
        $user = get_user_by( 'email', $username );
        if ( isset( $user, $user->user_login, $user->user_status ) && 0 == (int) $user->user_status )
            $username = $user->user_login;
    }elseif (io_get_option('user_login_phone',false) && IOSMS::is_phone_number($username)) {
        $user_data = io_get_user_by($username, 'phone');
        if (empty($user_data)) {
            io_error('{"status":3,"msg":"'.__('未找到此手机号注册账户!','i_theme').'"}'); 
        }
        $username = $user_data->user_login;
    }
    $login_data = array(
        'user_login' =>$username,
        'user_password' =>$password,
        'remember' =>$remember,
    ); 

    $user_verify = wp_signon($login_data);
    //wp_signon 是wordpress自带的函数，通过用户信息来授权用户(登录)，可记住用户名
    if(is_wp_error($user_verify)){
        if( $user_verify->get_error_code() == 'too_many_retries' )
            io_error(array('status'=>3,'msg'=>$user_verify->get_error_message()));
        io_error('{"status":3,"msg":"'.__('用户名或密码错误，请重试!','i_theme').'"}'); 
    } else {  
        //绑定尝试社交登录的账号
        if (io_user_center_enable() && isset($_POST['old_bind'])) {
            io_update_oauth_data($user_verify->ID);
        }

        if ( (isset( $_REQUEST['redirect'] ) && !empty($_REQUEST['redirect'])) || (isset( $_REQUEST['redirect_to'] ) && !empty($_REQUEST['redirect_to'])) ){
            $redirect_to = isset($_REQUEST['redirect']) ?  $_REQUEST['redirect'] : $_REQUEST['redirect_to'];  
        } elseif (user_can($user_verify->ID,'manage_options')) {
            $redirect_to = admin_url();  
        } else {
            $redirect_to = home_url();  
        }
        if(!( !defined( 'WP_CACHE' ) || !WP_CACHE ) && defined('WP_ROCKET_VERSION')){
            $redirect_to = $redirect_to.'?t='.time();
        }
        io_error('{"status":1,"msg":"'.__('登录成功，跳转中！','i_theme').'","goto":"'.urldecode($redirect_to).'"}'); 
        exit();
    }
}
add_action('wp_ajax_nopriv_user_login', 'user_login_callback'); 

/**
 * 注册
 * @return 
 */
function user_register_callback(){ 
    $username   = sanitize_user( $_POST['user_login'] );
    $user_email = isset($_POST['email_phone']) ? esc_sql($_POST['email_phone']) : '';

    $is_reg = io_get_option('reg_verification', false);
      // 检查名称
    if ( $username == '' ) {
        io_error('{"status":2,"msg":"'.__('请输入用户名！','i_theme').'"}');
    } elseif ( strlen($username) < 5 ) {
        io_error('{"status":2,"msg":"'.__('用户名长度至少5位!','i_theme').'"}');
    } elseif ( ! validate_username( $username ) || is_disable_username($username) ) {
        io_error('{"status":2,"msg":"'.__('此用户名包含无效字符，只能使用字母数字下划线！','i_theme').'"}');
    } elseif ( username_exists( $username ) ) {
        io_error('{"status":2,"msg":"'.__('该用户名已被注册，请再选择一个！','i_theme').'"}');
    }

    if($is_reg){
        $reg = reg_form_judgment($user_email);
        if(!empty($reg['error'])){
            io_error('{"status":2,"msg":"'.$reg['error']['msg'].'"}');
        }
        $reg_type   = $reg['type'];
        $user_email = $reg['to'];
        
        // 检查邮件
        if ('email' == $reg_type && email_exists($user_email)) {
            io_error('{"status":3,"msg":"' . __('该电子邮件地址已经被注册，请换一个！', 'i_theme') . '"}');
        }
        if ('phone' == $reg_type && io_get_user_by( $user_email,'phone')) {
            io_error('{"status":3,"msg":"' . __('该手机号已有绑定帐号！', 'i_theme') . '"}');
        }
        if(!session_id()) session_start();
        // 验证邮箱验证码
        if (!isset($_SESSION['new_mail'])) {
            io_error('{"status":3,"msg":"' . __('数据错误！', 'i_theme') . '"}');
        }
        if ($_SESSION['new_mail'] != $user_email) {
            io_error('{"status":3,"msg":"' . __('邮箱怎么变了！', 'i_theme') . '"}');
        }
        if(!$_POST['verification_code']){
            io_error('{"status":3,"msg":"' . __('请输入验证码！', 'i_theme') . '"}');
        }elseif(!isset($_SESSION['reg_mail_token']) || $_POST['verification_code'] != $_SESSION['reg_mail_token'] ){
            io_error('{"status":3,"msg":"' . __('验证码不正确！', 'i_theme') . '"}');
        }
        session_write_close();
    }
    // 检查密码
    if(strlen($_POST['user_pass']) < 6)
        io_error('{"status":2,"msg":"'.__('密码长度至少6位!','i_theme').'"}');
    elseif($_POST['user_pass'] != $_POST['user_pass2'])
        io_error('{"status":2,"msg":"'.__('密码不一致!','i_theme').'"}');

    io_ajax_agreement_judgment();

    io_ajax_is_robots();
    $user_id = wp_create_user( $username, $_POST['user_pass'], $user_email );
    if (is_wp_error($user_id)) {
        io_error('{"status":4,"msg":"'.$user_id->get_error_message().'"}');
    } elseif (!$user_id) {
        io_error('{"status":4,"msg":"'.sprintf( '无法完成您的注册请求... 请联系<a href="mailto:%s">管理员</a>！', get_option( 'admin_email' ) ).'"}');
    }
    $user = get_user_by('id', $user_id);
    $user_id = $user->ID;

    update_user_meta($user_id, 'avatar_type', 'letter');

    //保存用户手机号
    if ($is_reg && 'phone' == $reg_type) {
        update_user_meta($user_id, 'phone_number', $user_email);
    }

    // 发送激活成功与注册欢迎信
    // io_async_mail('', get_option('admin_email'), sprintf(__('您的站点「%s」有新用户注册 :', 'i_theme'), get_bloginfo('name')), array('loginName' => $username, 'email' => $user_email, 'ip' => $_SERVER['REMOTE_ADDR']), 'register-admin');

    // 自动登录 
    wp_set_current_user($user_id, $user->user_login);
    wp_set_auth_cookie($user_id, true);
    do_action('wp_login', $user->user_login, $user);

    if ( (isset( $_REQUEST['redirect'] ) && !empty($_REQUEST['redirect'])) || (isset( $_REQUEST['redirect_to'] ) && !empty($_REQUEST['redirect_to'])) ){
        $redirect_to = isset($_REQUEST['redirect']) ?  $_REQUEST['redirect'] : $_REQUEST['redirect_to'];  
    } elseif (user_can($user_id,'manage_options')) {
        $redirect_to = admin_url();  
    } else {
        $redirect_to = home_url();  
    }
    if(!( !defined( 'WP_CACHE' ) || !WP_CACHE ) && defined('WP_ROCKET_VERSION')){
        $redirect_to = $redirect_to.'?t='.time();
    }
    io_error('{"status":1,"msg":"'.__('注册成功，跳转中！','i_theme').'","goto":"'.urldecode($redirect_to).'"}'); 
}
add_action('wp_ajax_nopriv_user_register', 'user_register_callback'); 


/**
 * 修改用户信息 
 * @return void
 */
function io_change_user_info_callback()
{
    if (!wp_verify_nonce($_POST['_wpnonce'], 'change_user_info')) {
        __echo(2, __('安全检查失败，请刷新或稍后再试！', 'i_theme'));
    }
    $user = wp_get_current_user();
    if (!$user->ID) {
        __echo(2, __('请先登录！', 'i_theme'));
    }
    
    $avatar_type = __post('avatar', 'custom');
    $nickname    = sanitize_user(__post('name'));
    // 判断提示不能有特殊字符
    if (find_character($nickname, array('<', '>', '&', '"', '\'', '#', '^', '*', '_', '+', '$', '?', '!'))) {
        __echo(3, __('昵称不能有特殊字符！', 'i_theme'));
    }
    $legal       = is_username_legal($nickname);
    if ($legal['error']) {
        __echo(3, $legal['msg']);
    }

    $extra_info = array(
        'qq'     => __post('qq'),
        'wechat' => __post('wechat'),
        'weibo'  => __post('weibo'),
        'github' => __post('github'),
    );
    $userdata = array(
        'ID'           => $user->ID,
        'nickname'     => $nickname,
        'display_name' => $nickname,
        'user_url'     => esc_sql($_POST['url']),
        'description'  => esc_sql($_POST['desc']), 
    );

    update_user_meta($userdata['ID'], 'avatar_type', $avatar_type);
    update_user_meta($userdata['ID'], 'extra_info', $extra_info);

    wp_update_user($userdata);

    __refresh(__('资料修改成功！', 'i_theme'));
}
add_action('wp_ajax_change_user_info', 'io_change_user_info_callback');

//注册&绑定邮箱或者手机
add_action('wp_ajax_register_after_bind_email', 'register_after_bind_email_callback');
add_action('wp_ajax_nopriv_register_after_bind_email', 'register_after_bind_email_callback');  
function register_after_bind_email_callback(){
    $new_mail  = esc_sql($_POST['email_phone']);
    $mm_token  = ($_POST['verification_code']);
    $bind_type = esc_sql($_POST['bind_type']);
    $task      = esc_sql($_POST['task']);
    
    if(empty($new_mail) || empty($mm_token) || empty($bind_type)){
        io_error('{"status":2,"msg":"'.__('请认真填写表单！','i_theme').'"}'); 
    }

    if(!session_id()) session_start();
    if(isset($_SESSION['new_mail']) && $_SESSION['new_mail'] != $new_mail)
        io_error('{"status":3,"msg":"'.__('数据错误！', 'i_theme').'"}');
    if(!isset($_SESSION['reg_mail_token']) || $mm_token != $_SESSION['reg_mail_token'] )
        io_error('{"status":4,"msg":"'.__('验证码不正确！','i_theme').'"}');

    //执行人机验证
    io_ajax_is_robots();

    if ($task === 'new') {//新增用户
        $args =  maybe_unserialize($_SESSION['temp_oauth']);

        if ($bind_type == 'email') {
            $prename = explode('@', $new_mail)[0];
            $extname = rand(100, 988);
            $login_name = $prename;
            if (username_exists($login_name)) {
                $login_name = $prename . $extname;
                while (username_exists($login_name)) {
                    $extname++;
                }
            }
        }else{
            $login_name = $new_mail;
        }
        $user_pass = wp_generate_password();
        $user_mail = $bind_type == 'email' ? $new_mail : '';

        $user_id = wp_create_user($login_name, $user_pass, $user_mail);
        if (is_wp_error($user_id)) {
            //新建用户出错
            io_error('{"status":3,"msg":"'.$user_id->get_error_message().'"}');  
        } else {
            //新建用户成功
            update_user_meta($user_id, 'oauth_new', $args['type']);
            /**标记为系统新建用户 */
            //更新用户mate
            $args['user_id'] = $user_id;
            $args['login_name'] = $login_name;
            io_oauth_update_user_meta($args, true);

            if ($bind_type == 'phone')
                update_user_meta($user_id, 'phone_number', $new_mail);
            //登录
            $user = get_user_by('id', $user_id);
            wp_set_current_user($user_id, $user->user_login);
            wp_set_auth_cookie($user_id, true);
            do_action('wp_login', $user->user_login, $user);
    
            // 准备返回数据
            unset($_SESSION['new_mail']);
            unset($_SESSION['reg_mail_token']);
            unset($_SESSION['temp_oauth']);
    
            io_error('{"status":1,"msg":"'.__('绑定成功！','i_theme').'","goto":"'.$args['rurl'].'"}'); 
        }
    } else {
        $user = wp_get_current_user();
        if (!$user->ID) {
            io_error('{"status":2,"msg":"' . __('非法请求!', 'i_theme') . '"}');
        }

        if ($bind_type == 'email') {
            $userdata = array(
                'ID'         => $user->ID,
                'user_email' => $new_mail
            );
            $return = wp_update_user($userdata);
            if (is_wp_error($return)) {
                io_error('{"status":3,"msg":"' . $return->get_error_message() . '"}');
            }
        } else {
            update_user_meta($user->ID, 'phone_number', $new_mail);
        }
        unset($_SESSION['new_mail']);
        unset($_SESSION['reg_mail_token']);

        io_error('{"status":1,"msg":"' . __('绑定成功！', 'i_theme') . '"}');
    }
}

//注册时邮箱&手机号验证
add_action('wp_ajax_nopriv_reg_email_or_phone_token', 'reg_email_token_callback');  
add_action('wp_ajax_reg_email_or_phone_token', 'reg_email_token_callback');
function reg_email_token_callback(){
    $to   = addslashes(trim($_POST['email_phone']));
    $type = isset($_POST['bind_type']) ? esc_sql($_POST['bind_type']) : '';
    $reg  = reg_form_judgment($to,$type);
    if(!empty($reg['error'])){
        io_error(json_encode($reg['error']));
    }
    $reg_type = $reg['type'];
    $to       = $reg['to'];
    $user = wp_get_current_user();
    if ('email' == $reg_type && (!$user->ID && email_exists($to)) || ($user->ID && email_exists($to) && $user->user_email != $to)) {
        io_error('{"status":3,"msg":"' . __('该电子邮件地址已经被注册，请换一个！', 'i_theme') . '"}');
    }
    if ('phone' == $reg_type && io_get_user_by( $to,'phone')) {
        io_error('{"status":3,"msg":"' . __('该手机号已有绑定帐号！', 'i_theme') . '"}');
    }
    //执行人机验证
    io_ajax_is_robots();
    io_error(io_send_captcha($to,$reg_type));    
}

//找回密码时邮箱&手机号验证
function lost_email_token_callback(){
    $to = addslashes(trim($_POST['email_phone']));
    $reg = reg_form_judgment($to,'', 'lost_verify');
    if(!empty($reg['error'])){
        io_error(json_encode($reg['error']));
    }
    $reg_type = $reg['type'];
    $to       = $reg['to'];
    if ('email' == $reg_type && !email_exists($to)) {
        io_error('{"status":3,"msg":"' . __('该电子邮件尚未注册！', 'i_theme') . '"}');
    }
    if ('phone' == $reg_type && !io_get_user_by( $to,'phone')) {
        io_error('{"status":3,"msg":"' . __('该手机号尚未注册！', 'i_theme') . '"}');
    }
    //执行人机验证
    io_ajax_is_robots('reset_password');
    io_error(io_send_captcha($to,$reg_type));    
}
add_action('wp_ajax_nopriv_lost_email_or_phone_token', 'lost_email_token_callback');  
add_action('wp_ajax_lost_email_or_phone_token', 'lost_email_token_callback');

/**
 * 重设密码
 * @return void
 */
function reset_password_callback(){
    if (empty($_POST['user_pass']) || empty($_POST['user_pass2'])) {
        io_error('{"status":2,"msg":"' . __('密码不能为空', 'i_theme') . '"}');
    }
    if (strlen($_POST['user_pass']) < 6) {
        io_error('{"status":2,"msg":"' . __('密码长度至少6位!', 'i_theme') . '"}');
    }
    if ($_POST['user_pass'] !== $_POST['user_pass2']) {
        io_error('{"status":2,"msg":"' . __('密码不一致!', 'i_theme') . '"}');
    }

    $to       = addslashes(trim($_POST['email_phone']));
    $reg      = reg_form_judgment($to,'', 'lost_verify');
    if(!empty($reg['error'])){
        io_error(json_encode($reg['error']));
    }
    $reg_type = $reg['type'];
    $to       = $reg['to'];


    if ('email' == $reg_type) {
        $user = get_user_by('email', $to);
    }
    if ('phone' == $reg_type) {
        $user = io_get_user_by('phone', $to);
    }

    $current_user_id = get_current_user_id();
    if ($current_user_id && (!$user || $current_user_id != $user->ID)) {
        $captcha_type_name = array(
            'email' => '邮箱帐号',
            'phone' => '手机号',
        );
        io_error('{"status":2,"msg":"' . '您的' . $captcha_type_name[$reg_type] . '输入错误' . '"}');
    }
    if (!$user) {
        io_error('{"status":3,"msg":"' . __('未查询到您的帐号信息', 'i_theme') . '"}');
    }

    io_ajax_is_captcha($reg_type, $to);
    //人机验证
    io_ajax_is_robots();

    $status = wp_update_user(array(
            'ID'        => $user->ID,
            'user_pass' => $_POST['user_pass2'],
    ));

    if (is_wp_error($status)) {
        io_error('{"status":3,"msg":"' . $status->get_error_message() . '"}');
    }

    if (!$current_user_id) {
        wp_set_current_user($user->ID, $user->user_login);
        wp_set_auth_cookie($user->ID, true);
        do_action('wp_login', $user->user_login, $user);
    }
    io_remove_captcha();
    $redirect_to = isset($_SERVER['redirect_to']) ? esc_url($_SERVER['redirect_to']) : esc_url(home_url());
    io_error('{"status":1,"msg":"'.__('密码修改成功！','i_theme').'","goto":"'.$redirect_to.'"}'); 
}
add_action('wp_ajax_nopriv_reset_password', 'reset_password_callback');  
add_action('wp_ajax_reset_password', 'reset_password_callback');


//修改用户密码
function io_ajax_user_change_password(){
    $user = wp_get_current_user();
    
    if(!$user->ID) {
        io_error('{"status":2,"msg":"'.__('请先登录!','i_theme').'"}'); 
    }  
    if (empty($_POST['user_pass']) || empty($_POST['user_pass2'])) {
        io_error('{"status":2,"msg":"' . __('密码不能为空', 'i_theme') . '"}');
    }
    if (strlen($_POST['user_pass']) < 6) {
        io_error('{"status":2,"msg":"' . __('密码长度至少6位!', 'i_theme') . '"}');
    }
    if ($_POST['user_pass'] !== $_POST['user_pass2']) {
        io_error('{"status":2,"msg":"' . __('密码不一致!', 'i_theme') . '"}');
    }

    $oauth_new = get_user_meta($user->ID, 'oauth_new', true);
    if (!$oauth_new) {
        global $wp_hasher;
        if ( empty( $wp_hasher ) ) {
            require_once ABSPATH . WPINC . '/class-phpass.php';
            $wp_hasher = new PasswordHash( 8, true );
        }
        if (empty($_POST['user_pass_old'])) {
            io_error('{"status":2,"msg":"' . __('请输入原密码!', 'i_theme') . '"}');
        }

        if ($_POST['user_pass'] == $_POST['user_pass_old']) {
            io_error('{"status":2,"msg":"' . __('新密码和原密码不能相同!', 'i_theme') . '"}');
        }

        if (!$wp_hasher->CheckPassword($_POST['user_pass_old'], $user->user_pass)) {
            io_error('{"status":3,"msg":"' . __('原密码错误!', 'i_theme') . '"}');
        }
    }

    //人机验证
    io_ajax_is_robots();

    $status = wp_update_user(
        array(
            'ID'        => $user->ID,
            'user_pass' => $_POST['user_pass'],
        )
    );

    if (is_wp_error($status)) {
        io_error('{"status":3,"msg":"' . $status->get_error_message() . '"}');
    }
    delete_user_meta($user->ID, 'oauth_new');
    $msg = $oauth_new ? '密码设置成功' : '修改成功，下次请使用新密码登录';
    io_error('{"status":1,"msg":"' . $msg . '","reload":1}');
    exit();
}
add_action('wp_ajax_user_change_password', 'io_ajax_user_change_password');

//存储书签页设置 
add_action('wp_ajax_save_bookmark_set', 'save_bookmark_set_callback');
function save_bookmark_set_callback(){ 
    if (!wp_verify_nonce($_POST['_wpnonce'],'bookmark_set')){
        io_error('{"status":0,"msg":"'.__('安全检查失败，请刷新或稍后再试！','i_theme').'"}');
    } 
    $key =  absint(base64_io_decode($_POST['key']));
    $userid = wp_get_current_user()->ID;
    if($key != $userid){
        io_error('{"status":2,"msg":"'.__('无权修改！','i_theme').'"}');
    }
    $share_bookmark = isset($_POST['share-bookmark'])?1:0;
    $hide_title     = isset($_POST['hide-title'])?1:0;
    $is_go          = isset($_POST['is-go'])?1:0;
    $sites_title    = esc_sql($_POST['sites-title']);
    $quick_nav      = esc_sql($_POST['quick-nav']);
    $custom_img     = esc_sql($_POST['custom-img']);
    $bg             = esc_sql($_POST['bg']);

    $userdata = array(
        'share_bookmark'    => $share_bookmark,
        'hide_title'        => $hide_title,
        'is_go'             => $is_go,
        'sites_title'       => $sites_title,
        'quick_nav'         => $quick_nav,
        'bg'                => $bg,
        'custom_img'        => $custom_img,
    ); 
    update_user_meta($userid, 'bookmark_set', maybe_serialize($userdata));
    io_error('{"status":1,"msg":"'.__('保存成功！','i_theme').'"}');
}

//用户账户功能设置模态框
function io_ajax_get_user_set_modal(){
    $user = wp_get_current_user();
    if(!$user->ID) {
        io_error('{"status":2,"msg":"'.__('请先登录!','i_theme').'"}'); 
    }  
    $type = $_GET['type'];
    echo io_get_user_bind_info_html($type, $user);
    exit();
}
add_action('wp_ajax_get_user_security_info_set_modal', 'io_ajax_get_user_set_modal');


//发送验证旧权限验证码
function io_ajax_verify_user_email_or_phone_token(){
    $user = wp_get_current_user();
    if(!$user->ID) {
        io_error('{"status":2,"msg":"'.__('请先登录!','i_theme').'"}'); 
    }  
    if (empty($_REQUEST['type'])) {
        io_error('{"status":3,"msg":"' . __('参数错误！', 'i_theme') . '"}');
    }

    $type = $_REQUEST['type'];
    if ('email' == $type) {
        $to = $user->user_email;
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            io_error('{"status":3,"msg":"' . __('用户邮箱错误，请联系管理员。', 'i_theme') . '"}');
        }
    } else {
        $to = get_user_meta($user->ID, 'phone_number', true);
        if (!$to) {
            io_error('{"status":3,"msg":"' . __('未找到绑定的手机号。', 'i_theme') . '"}');
        }
    }

    //执行人机验证
    io_ajax_is_robots('verify_user_competence');

    io_error(io_send_captcha($to, $type));  
}
add_action('wp_ajax_verify_user_email_or_phone_token', 'io_ajax_verify_user_email_or_phone_token');

//验证旧权限
function io_ajax_verify_user_competence(){
    $user = wp_get_current_user();
    if(!$user->ID) {
        io_error('{"status":2,"msg":"'.__('请先登录!','i_theme').'"}'); 
    }  

    if (empty($_REQUEST['type'])) {
        io_error('{"status":3,"msg":"' . __('参数错误！', 'i_theme') . '"}');
    }

    $type = $_REQUEST['type'];
    if ('email' == $type) {
        $to = $user->user_email;
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            io_error('{"status":3,"msg":"' . __('用户邮箱错误，请联系管理员。', 'i_theme') . '"}');
        }
    } else {
        $to = get_user_meta($user->ID, 'phone_number', true);
        if (!$to) {
            io_error('{"status":3,"msg":"' . __('未找到绑定的手机号。', 'i_theme') . '"}');
        }
    }

    io_ajax_is_captcha($type, $to);

    //执行人机验证
    io_ajax_is_robots();

    io_set_user_verify_state($type, $user->ID);

    echo io_error(array('status'=>1,'msg'=>'','html'=>io_get_user_bind_info_html($type, $user, 2)));
    exit();
}
add_action('wp_ajax_verify_user_competence', 'io_ajax_verify_user_competence');

//发送新手机或者邮箱验证码
function io_ajax_bind_new_email_or_phone_token(){
    $user = wp_get_current_user();
    if(!$user->ID) {
        io_error('{"status":2,"msg":"'.__('请先登录!','i_theme').'"}'); 
    }  
    if (empty($_REQUEST['type'])) {
        io_error('{"status":3,"msg":"' . __('参数错误！', 'i_theme') . '"}');
    }

    $to   = $_POST['email_phone'];
    $type = $_REQUEST['type'];

    $reg  = reg_form_judgment($to,$type);
    if(!empty($reg['error'])){
        io_error(json_encode($reg['error']));
    }
    if ('email' == $type) {
        $old_to = $user->user_email;
        if ($old_to == $to) {
            io_error('{"status":3,"msg":"' . __('不能与现在的邮箱相同！', 'i_theme') . '"}');
        }
        if (email_exists($to)) {
            io_error('{"status":3,"msg":"' . __('该邮箱已有绑定帐号！', 'i_theme') . '"}');
        }
    } else {
        $old_to = get_user_meta($user->ID, 'phone_number', true);
        if ($old_to == $to) {
            io_error('{"status":3,"msg":"' . __('不能与现在的手机号相同！', 'i_theme') . '"}');
        }
        if ( io_get_user_by( $to,'phone')) {
            io_error('{"status":3,"msg":"' . __('该手机号已有绑定帐号！', 'i_theme') . '"}');
        }
    }

    //执行人机验证
    io_ajax_is_robots();

    io_error(io_send_captcha($to, $type));  
}
add_action('wp_ajax_bind_new_email_or_phone_token', 'io_ajax_bind_new_email_or_phone_token');

//绑定新手机或者邮箱
function io_ajax_user_bind_new_email_or_phone(){
    $user = wp_get_current_user();
    if(!$user->ID) {
        io_error('{"status":2,"msg":"'.__('请先登录!','i_theme').'"}'); 
    }  

    $to   = $_POST['email_phone'];
    $type = $_POST['type'];

    io_ajax_agreement_judgment();

    $reg  = reg_form_judgment($to,$type);
    if(!empty($reg['error'])){
        io_error(json_encode($reg['error']));
    }

    $to = $reg['to'];
    if('email'===$reg['type']){
        $old_to = $user->user_email;
        if ($old_to) {
            if ($old_to == $to) {
                io_error('{"status":3,"msg":"' . __('不能与现在的邮箱相同！', 'i_theme') . '"}');
            }
        }
        if (email_exists($to)) {
            io_error('{"status":3,"msg":"' . __('该邮箱已有绑定帐号！', 'i_theme') . '"}');
        }
    }else{
        $old_to = get_user_meta($user->ID, 'phone_number', true);
        if ($old_to) {
            if ($old_to == $to) {
                io_error('{"status":3,"msg":"' . __('不能与现在的手机号相同！', 'i_theme') . '"}');
            }
        }
        if ( io_get_user_by( $to,'phone')) {
            io_error('{"status":3,"msg":"' . __('该手机号已有绑定帐号！', 'i_theme') . '"}');
        }
    }

    io_ajax_is_captcha($type,$to);

    if ($old_to) {
        $is_verify = io_is_user_verify($reg['type'], $user->ID);
        if ($is_verify['error']) {
            io_error('{"status":3,"msg":"' . $is_verify['msg'] . '"}');
        }
    }
    //执行人机验证
    io_ajax_is_robots();

    if ('email' === $reg['type']) {
        $status = wp_update_user(
            array(
                'ID'         => $user->ID,
                'user_email' => $to,
            )
        );
        $msg = $old_to ? '邮箱修改成功' : '邮箱绑定成功';
    }else{
        update_user_meta($user->ID, 'phone_number', $to);
        $msg = $old_to ? '手机号修改成功' : '手机号绑定成功';
    }

    do_action('io_user_bind_new_email_or_phone', $user->ID, $type, $to, $old_to);

    io_remove_captcha();
    
    io_error('{"status":1,"msg":"' . $msg . '","reload":1}');
}
add_action('wp_ajax_user_bind_new_email_or_phone', 'io_ajax_user_bind_new_email_or_phone');

/**
 * 上传用户头像和封面
 * @return void
 */
function io_ajax_upload_user_avatar()
{
    // 确保文件未缓存
    header("HTTP/1.1 200 OK");
    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
    header("Cache-Control: no-store, no-cache, must-revalidate");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");

    $user_id = get_current_user_id();
    if (!$user_id) {
        __echo(2, __('请先登录!', 'i_theme'));
    }
    $type = $_POST['img_type'];
    $file = $_FILES['file'];
    if (empty($file)) {
        __echo(3, __('没有文件上传!', 'i_theme'));
    }
    if (!in_array($type, ['avatar', 'cover'])) {
        __echo(3, __('参数错误!', 'i_theme'));
    }

    // 判断文件类型
    $file_type = $file['type'];
    if (!in_array($file_type, ['image/jpeg', 'image/png'])) {
        __echo(3, __('文件类型错误!', 'i_theme'));
    }
    // 判断文件大小
    $file_size  = $file['size'];
    $limit_size = ('avatar' === $type ? 1024 * 512 : 1024 * 1024);
    if ($file_size > $limit_size) {
        __echo(3, sprintf(__('文件大小不能超过 %sKB!', 'i_theme'), $limit_size / 1024));
    }

    // 保存文件
    $upload_dir  = wp_upload_dir();
    $upload_url  = $upload_dir['baseurl'] . '/avatars';
    $upload_path = $upload_dir['basedir'] . '/avatars';

    if (!file_exists($upload_path)) {
        mkdir($upload_path, 0777, true);
    }
    $origin_path = $upload_path . '/' . time() . '_' . $file['name'];
    if (!move_uploaded_file($file['tmp_name'], $origin_path)) {
        __echo(3, __('文件保存失败!', 'i_theme'));
    }

    $img_url = '';
    if ('avatar' === $type) {
        $avatar_path = $upload_path . '/' . $user_id . '.jpg';
        io_resize_img($origin_path, $avatar_path, 100, 100, true);
        io_update_user_avatar_by_upload($user_id);
        $img_url = $upload_url . '/' . $user_id . '.jpg?_=' . time();
        update_user_meta($user_id, 'custom_avatar', $img_url);
    } else {
        $cover_path      = $upload_path . '/' . $user_id . '_cover_full.jpg';
        $cover_mini_path = $upload_path . '/' . $user_id . '_cover_mini.jpg';
        io_resize_img($origin_path, $cover_path, 1400, 400, false);
        io_resize_img($origin_path, $cover_mini_path, 380, 190, true);
        io_update_user_cover_by_upload($user_id, $upload_url . '/' . $user_id . '_cover_');
        $img_url = $upload_url . '/' . $user_id . '_cover_full.jpg?_=' . time();
    }

    __echo(1, __('图片上传成功!', 'i_theme'), array('img' => $img_url), '', true);
}
add_action('wp_ajax_upload_user_avatar', 'io_ajax_upload_user_avatar');
