<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2022-02-09 21:11:15
 * @LastEditors: iowen
 * @LastEditTime: 2024-10-31 23:31:24
 * @FilePath: /onenav/inc/functions/io-login.php
 * @Description: 
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }


/*----------------------------------连续登录验证--------------------------------------*/
// 初始化登录失败次数限制逻辑
if (io_get_option('login_limit', 5) > 0) {
    add_action('wp_login_failed', 'io_login_failed_action', 10, 1);
    add_filter('authenticate', 'io_login_authenticate_filter', 10, 3);
    add_filter('shake_error_codes', 'io_shake_error_codes');
    // 登录成功后清除登录失败记录
    add_action('wp_login', 'io_wp_login_action', 10, 2);
}

/**
 * 获取用户登录失败记录
 * @param mixed $key
 * @return mixed
 */
function io_get_login_failed_data($key) {
    $login_failed = get_option('io_login_failed', array());
    return isset($login_failed[$key]) ? $login_failed[$key] : null;
}

/**
 * 更新用户登录失败记录
 * @param mixed $key
 * @param mixed $data
 * @return void
 */
function io_update_login_failed_data($key, $data) {
    $login_failed = get_option('io_login_failed', array());
    $login_failed[$key] = $data;
    update_option('io_login_failed', $login_failed, false);
}

/**
 * 登录失败时处理逻辑
 * 
 * 只通过IP限制登录失败次数
 * @param mixed $username
 * @return void
 */
function io_login_failed_action($username) {
    $key = md5(IOTOOLS::get_ip());
    $data = io_get_login_failed_data($key) ?: array('limit' => 0, 'time' => current_time('timestamp'));

    $data['limit'] += 1;
    if ($data['limit'] <= io_get_option('login_limit', 5)) {
        io_update_login_failed_data($key, $data);
    }
}

/**
 * 验证登录时的限制逻辑
 * @param mixed $user
 * @param mixed $username
 * @param mixed $password
 * @return mixed
 */
function io_login_authenticate_filter($user, $username, $password) {
    $key = md5(IOTOOLS::get_ip());
    $data = io_get_login_failed_data($key);

    if (!$data) {
        return $user;
    }

    if ($data['limit'] >= io_get_option('login_limit', 5)) {
        $time_elapsed = current_time('timestamp') - $data['time'];
        $limit_time = MINUTE_IN_SECONDS * io_get_option('login_limit_time', 10);

        if ($time_elapsed < $limit_time) {
            remove_filter('authenticate', 'wp_authenticate_username_password', 20);
            remove_filter('authenticate', 'wp_authenticate_email_password', 20);
            return new WP_Error(
                'too_many_retries',
                sprintf(
                    __('已多次登录失败，请%s后重试！', 'i_theme'),
                    io_get_time_diff_title($limit_time - $time_elapsed)
                )
            );
        } else {
            // 超过限制时间，重置登录失败次数
            $data['limit'] = ceil(io_get_option('login_limit', 5) / 2);
            $data['time'] = current_time('timestamp');
            io_update_login_failed_data($key, $data);
        }
    }

    return $user;
}

/**
 * 添加错误代码到登录失败时的动画效果中
 * @param mixed $error_codes
 * @return mixed
 */
function io_shake_error_codes($error_codes) {
    $error_codes[] = 'too_many_retries';
    return $error_codes;
}

/**
 * 登录成功后清除登录失败记录
 * @param mixed $user_login
 * @param mixed $user
 * @return void
 */
function io_wp_login_action($user_login, $user) {
    $key = md5(IOTOOLS::get_ip());
    $login_failed = get_option('io_login_failed', array());

    // 清理过期数据
    foreach ($login_failed as $k => $v) {
        if ($v['time'] < (current_time('timestamp') - (MINUTE_IN_SECONDS * io_get_option('login_limit_time', 10)))) {
            unset($login_failed[$k]);
        }
        if ($key == $k) {
            unset($login_failed[$k]);
        }
    }

    update_option('io_login_failed', $login_failed, false);
}

/**
 * 获取时间差的标题
 * @param mixed $diff
 * @return string
 */
function io_get_time_diff_title($diff) {
    return ($diff >= 60) ? ceil($diff / 60) . '分钟' : "{$diff}秒";
}

/*----------------------------------连续登录验证 END----------------------------------*/






/*---------------------------------- 默认登录页美化 ----------------------------------*/

if (!io_user_center_enable() && io_get_option('modify_default_style',false)) {
    add_action('login_head', 'io_custom_login_style');
    add_action('login_header', 'io_wp_login_header');
    add_action('login_footer', 'io_wp_login_footer');

    //登录页面的LOGO链接为首页链接
    add_filter('login_headerurl', function () {
        return esc_url(home_url());
    });
    //登陆界面logo的title为博客副标题
    add_filter('login_headertext', function () {
        return get_bloginfo('description');
    });
}
/**
 * 默认登录页美化css
 * @return void
 */
function io_custom_login_style(){
    $login_color = io_get_option('login_color',array('color-l'=>'','color-r'=>''));
    echo '<style type="text/css">
    body{background:'.$login_color['color-l'].';background:-o-linear-gradient(45deg,'.$login_color['color-l'].','.$login_color['color-r'].');background:linear-gradient(45deg,'.$login_color['color-l'].','.$login_color['color-r'].');height:100vh}
    .login h1 a{background-image:url('.io_get_option('logo_normal_light',get_template_directory_uri() .'/assets/images/logo_l@2x.png').');width:180px;background-position:center center;background-size:80px}
    .login-container{position:relative;display:flex;align-items:center;justify-content:center;height:100vh}
    .login-body{position:relative;display:flex;margin:0 1.5rem}
    .login-img{display:none}
    .img-bg{color:#fff;padding:2rem;bottom:-2rem;left:0;top:-2rem;right:0;border-radius:10px;background-image:url('.io_get_option('login_ico',get_template_directory_uri() .'/assets/images/login.jpg').');background-repeat:no-repeat;background-position:center center;background-size:cover}
    .img-bg h2{font-size:2rem;margin-bottom:1.25rem}
    #login{position:relative;background:#fff;border-radius:10px;padding:28px;width:280px;box-shadow:0 1rem 3rem rgba(0,0,0,.175)}
    .flex-fill{flex:1 1 auto}
    .position-relative{position:relative}
    .position-absolute{position:absolute}
    .shadow-lg{box-shadow:0 1rem 3rem rgba(0,0,0,.175)!important}
    .footer-copyright{bottom:0;color:rgba(255,255,255,.6);text-align:center;margin:20px;left:0;right:0}
    .footer-copyright a{color:rgba(255,255,255,.6);text-decoration:none}
    #login form{-webkit-box-shadow:none;-moz-box-shadow:none;box-shadow:none;border-width:0;padding:0}
    #login form .forgetmenot{float:none}
    .login #login_error,.login .message,.login .success{border-left-color:#40b9f1;box-shadow:none;background:#d4eeff;border-radius:6px;color:#2e73b7}
    .login #login_error{border-left-color:#f1404b;background:#ffd4d6;color:#b72e37}
    #login form p.submit{padding:20px 0 0}
    #login form p.submit .button-primary{float:none;background-color:#f1404b;font-weight:bold;color:#fff;width:100%;height:40px;border-width:0;text-shadow:none!important;border-color:none;transition:.5s}
    #login form input{box-shadow:none!important;outline:none!important}
    #login form p.submit .button-primary:hover{background-color:#444}
    .login #backtoblog,.login #nav{padding:0}
    .login .privacy-policy-page-link{text-align:left;margin:0}
    @media screen and (min-width:768px){.login-body{width:1200px}
    .login-img{display:block}
    #login{margin-left:-60px;padding:40px}
    }
</style>';
}
/**
 * 默认登录页html BEGIN
 */
function io_wp_login_header(){
    echo '<div class="login-container">
    <div class="login-body">
        <div class="login-img shadow-lg position-relative flex-fill">
            <div class="img-bg position-absolute">
                <div class="login-info">
                    <h2>'. get_bloginfo('name') .'</h1>
                    <p>'. get_bloginfo('description') .'</p>
                </div>
            </div>
        </div>';
}
/**
 * 默认登录页html END
 */
function io_wp_login_footer(){
    echo '</div><!--login-body END-->
    </div><!--login-container END-->
    <div class="footer-copyright position-absolute">
            <span>Copyright © <a href="'. esc_url(home_url()) .'" class="text-white-50" title="'. get_bloginfo('name') .'" rel="home">'. get_bloginfo('name') .'</a></span> 
    </div>';
}

/*---------------------------------- 默认登录页美化 END ----------------------------------*/






/**
 * 获取注册时验证标题
 * 'email' 'phone'
 * @param mixed $page reg or lost_verify
 * @return string
 */
function get_reg_name($page = 'reg'){
    $title = '';
    $types = io_get_option("{$page}_type",array('email'));
    if (count($types) == 1) {
        foreach ($types as $v) {
            switch ($v) {
                case 'email':
                    $title .= __('邮箱', 'i_theme');
                    break;
                case 'phone':
                    $title .= __('手机号', 'i_theme');
                    break;
            }
        }
    }else{
        $title = __('邮箱或手机号', 'i_theme');
    }
    return $title;
}

/**
 * 验证方式判断
 * @param mixed $to
 * @param mixed $type
 * @param mixed $page reg or lost_verify
 * @return array
 */
function reg_form_judgment($to, $type ='', $page = 'reg'){
    $reg_type = $type ?: io_get_option("{$page}_type", array('email'));
    $error = '';
    $type = '';
    if (!$reg_type || !$to) {
        return array('type' => $type, 'to' => $to, 'error'=>(array('status' => 3, 'msg' => __('参数传入错误','i_theme' ))));
    }

    if ( is_array($reg_type) && count($reg_type) == 1) {
        foreach ($reg_type as $v) {
            $data  = io_filter_var_to($to,$v);
            $type  = isset($data['type'])?$data['type']:'';
            $error = isset($data['error'])?$data['error']:'';
        }
    } else {
        if($reg_type){
            $data  = io_filter_var_to($to,$reg_type);
            $type  = isset($data['type'])?$data['type']:'';
            $error = isset($data['error'])?$data['error']:'';
        }
        if (is_numeric($to)) {
            if (IOSMS::is_phone_number($to)) {
                $type = 'phone';
            }
        } elseif (filter_var($to, FILTER_VALIDATE_EMAIL)) {
            $type = 'email';
        } else {
            $error = array(
                "status" => 3,
                "msg" => __('手机号或邮箱格式错误！', 'i_theme')
            );
        }
    }
    return array('type' => $type, 'to' => $to, 'error' => $error);
}

/**
 * 验证提示
 * @param mixed $to
 * @param mixed $type
 * @return array
 */
function io_filter_var_to($to, $type){
    $data = array();
    switch ($type) {
        case 'email':
            $data['type'] = 'email';
            if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
                $data['error'] = array(
                    "status" => 3,
                    "msg" => __('邮箱格式错误', 'i_theme')
                );
            }
            break;
        case 'phone':
            $data['type'] = 'phone';
            if (!IOSMS::is_phone_number($to)) {
                $data['error'] = array(
                    "status" => 3,
                    "msg" => __('手机号格式错误！', 'i_theme')
                );
            }
            break;
    }
    return $data;
}
/**
 * 获取重定向地址
 * @param string $defaults 默认地址
 * @return string
 */
function get_redirect_to($defaults = '')
{
    $redirect_to = $defaults;
    if ((isset($_REQUEST['redirect']) && !empty($_REQUEST['redirect'])) || (isset($_REQUEST['redirect_to']) && !empty($_REQUEST['redirect_to']))) {
        $redirect_to = isset($_REQUEST['redirect']) ? $_REQUEST['redirect'] : $_REQUEST['redirect_to'];
    }
    return esc_url($redirect_to);
}

function io_login_header($title)
{
    $img_type  = io_get_option('login_img_type', 'min-img');
    $blog_name = get_bloginfo('name');
    $login_bg  = io_get_option('login_ico', '');

    $title .= ' - ' . $blog_name;
    $class = $login_bg ? $img_type : 'no-img';

    $login_color = io_get_option('login_color', array());
    $style       = '<style> :root {--bg-color-l: ' . $login_color['color-l'] . ';--bg-color-r: ' . $login_color['color-r'] . ';--this-bg-image:url(' . $login_bg . ')} </style>';
    $body_class  = implode(' ', get_body_class($class));

    echo '<!DOCTYPE html>';
    echo '<html ' . get_language_attributes() . '>';
    echo '<head>';
    echo '<meta charset="UTF-8">';
    echo '<meta http-equiv="X-UA-Compatible" content="IE=edge, chrome=1">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">';
    echo '<title>' . $title . '</title>';
    echo '<link rel="shortcut icon" href="' . io_get_option('favicon', '') . '">';
    echo '<link rel="apple-touch-icon" href="' . io_get_option('apple_icon', '') . '">';
    echo '<meta name="robots" content="noindex,nofollow" /> ';
    do_action('login_head');
    wp_head();
    echo $style;
    echo '</head>';
    echo '<body class="' . esc_attr($body_class) . '">';
    dark_mode_js();
}

function io_login_body($type, $args = array())
{
    $img_type  = io_get_option('login_img_type', 'min-img');
    $redirect_to = get_redirect_to();

    $login_bg   = io_get_option('login_ico', '');
    $login_img = '';
    if ($login_bg) {
        $login_img .= '<div class="login-img flex-fill">';
        $login_img .= '<div class="login-logo position-relative d-none d-md-block p-4">';
        $login_img .= io_get_logo_html(true);
        $login_img .= '</div>';
        $login_img .= '</div>';
    }

    echo 'max-img' === $img_type ? $login_img : '';
    ?>
    <div class="container login-container d-flex flex-fill justify-content-md-end justify-content-center">
        <div class="login-body blur-bg d-flex m-auto">
            <?php echo 'max-img' === $img_type? '' : $login_img ?>
            <div class="login-forms">
                <?php 
                switch ($type) {
                    case 'login':
                        io_get_form_login_html($redirect_to);
                        break;
                    case 'lost':
                        io_get_form_lost_html();
                        break;
                    case 'reg':
                        echo io_get_form_reg_html($redirect_to);
                        break;
                    case 'bind':
                        echo io_get_form_bind_html($redirect_to, $args);
                        break;
                        
                    default:
                        io_get_form_login_html($redirect_to);
                        break;
                }
                ?>
            </div> 
        </div>
    </div>
    <?php
}

function io_login_footer()
{
    echo '<footer class="footer-copyright position-relative">';
    echo '<div class="text-white-50 text-xs text-center">';
    io_copyright('text-white-50', true);
    echo '</div>';
    echo '</footer>';

    do_action('login_footer');

    echo '</body>';
    echo '</html>';
}

/**
 * 获取登录表单
 * @param mixed $redirect_to
 * @return void
 */
function io_get_form_login_html($redirect_to)
{
    ?>
    <div class="sign-header h4 mb-3 mb-md-4"><?php _e('欢迎回来', 'i_theme') ?></div>
    <form method="post" action="" class="form-validate wp-user-form" id="wp_login_form">
        <input type="hidden" name="action" value="user_login" />
        <div class="form-group mb-3">
            <input type="text" name="username" placeholder="<?php io_get_option('user_login_phone', false) ? _e('用户名/手机号/邮箱', 'i_theme') : _e('用户名或邮箱', 'i_theme') ?>" class="form-control">
        </div>
        <div class="form-group position-relative mb-3">
            <input type="password" name="password" placeholder="<?php _e('密码', 'i_theme') ?>" class="form-control">
            <div class="password-show-btn" data-show="0"><i class="iconfont icon-chakan-line"></i></div>
        </div> 
        <div class="form-group mb-3">
            <?php echo get_captcha_input_html('user_login', 'form-control') ?>
        </div> 
        <div class="custom-control custom-checkbox text-xs">
            <input type="checkbox" class="custom-control-input" checked="checked" name="rememberme" id="check1" value="forever">
            <label class="custom-control-label" for="check1"><?php _e('记住我的登录信息', 'i_theme') ?></label>
        </div> 
        <?php echo io_get_agreement_input() ?>
        <div class="login-form mb-3"><?php do_action('login_form'); ?></div>
        <div class="d-flex mb-3">
            <button id="submit" type="submit" class="btn btn-shadow vc-theme btn-hover-dark btn-block"><?php _e('登录', 'i_theme') ?></button>
            <a href="<?php echo esc_url(home_url()) ?>" class="btn vc-theme btn-outline btn-block mt-0 ml-4"><?php _e('首页', 'i_theme') ?></a>
        </div> 
        <div class=" text-muted">
            <small><?php _e('没有账号？', 'i_theme') ?><a href="<?php echo esc_url(io_add_redirect(home_url() . '/login/?action=register')) ?>" class="signup"><?php _e('注册', 'i_theme') ?></a> / <a href="<?php echo esc_url(io_add_redirect(home_url() . '/login/?action=lostpassword')) ?>" class="signup"><?php _e('找回密码', 'i_theme') ?></a></small> 
        </div>
        <div class="login-form mt-4"><?php do_action('io_login_form'); ?></div>
        <input type="hidden" name="redirect_to" value="<?php echo esc_url_raw($redirect_to); ?>" />
    </form> 
    <?php
}
/**
 * 获取重设密码表单
 * @return void
 */
function io_get_form_lost_html()
{
    ?>
    <div class="sign-header h4 mb-3 mb-md-4"><?php _e('重设密码', 'i_theme') ?></div>
    <?php echo io_get_password_reset_from() ?> 
    <div class="mt-3 text-muted">
        <small><a href="<?php echo esc_url(io_add_redirect(home_url() . '/login/?action=register')) ?>" class="signup"><?php _e('注册', 'i_theme') ?></a> | <a href="<?php echo esc_url(io_add_redirect(home_url() . '/login/')) ?>" class="signup"><?php _e('登录', 'i_theme') ?></a></small> 
    </div>
    <?php
}
/**
 * 获取注册表单
 * @param mixed $redirect_to
 * @return void
 */
function io_get_form_reg_html($redirect_to)
{
    if (io_is_close_register()) {
        echo '<div class="sign-header h4 mb-2 mb-md-5">' . __('禁止注册', 'i_theme') . '</div>';
        echo '<p class="reg-error"><i class="iconfont icon-tishi"></i> ' . __('请联系管理员！', 'i_theme') . '</p>';
        echo '<div class=" text-muted">';
        echo '<small>' . __('已有账号?', 'i_theme') . ' <a href="' . esc_url(io_add_redirect(home_url() . '/login/')) . '" class="signup">' . __('登录', 'i_theme') . '</a></small> ';
        echo '</div>';
    } elseif (!is_user_logged_in()) {
        io_get_reg_html($redirect_to);
    } else {
        echo '<div class="sign-header h4 mb-2 mb-md-5">' . __('注册成功', 'i_theme') . '</div>';
        echo '<div class="d-flex mt-3">';
        echo '<a href="' . wp_logout_url(home_url()) . '" class="btn btn-shadow vc-theme btn-hover-dark btn-block">' . __('退出登录', 'i_theme') . '</a>';
        if (current_user_can('manage_options')) {
            echo '&nbsp;&nbsp;<a href="' . admin_url() . '" class="btn vc-theme btn-outline btn-block mt-0 ml-4">' . sprintf(__('管理站点', 'i_theme')) . '</a>';
        } else {
            echo '&nbsp;&nbsp;<a href="'.home_url('/user/').'" class="btn vc-theme btn-outline btn-block mt-0 ml-4">' . sprintf(__('用户中心', 'i_theme')) . '</a>';
        }
        echo '</div>';
        echo '<p class="text-xs text-muted mt-3"><i class="iconfont icon-tishi"></i>' . __('3秒后自动跳转', 'i_theme') . '</p>';
        echo '<script type="text/javascript">setTimeout(location.href="' . $redirect_to . '",3000);</script>';
    }
}
/**
 * 获取注册提交表单
 *
 * @param mixed $redirect_to
 * @return void
 */
function io_get_reg_html($redirect_to)
{
    $is_reg = io_get_option('reg_verification', false);
    ?>
    <div class="sign-header h4 mb-2 mb-md-4"><?php _e('注册', 'i_theme') ?></div>
    <form name="registerform" class="form-validate wp-user-form" id="wp_login_form">
        <div class="form-group mb-3">
            <input type="text" name="user_login" tabindex="1" id="user_login" placeholder="<?php _e('用户名', 'i_theme') ?>" size="30" class="form-control"/> 
        </div> 
        <?php if ($is_reg) { ?>
        <div class="form-group mb-3">
            <input type="text" name="email_phone" tabindex="2" id="user_email" placeholder="<?php echo get_reg_name() ?>" size="30" class="form-control"/> 
        </div> 
        <div class="form-group mb-3 verification" style="display:none">
            <input type="text" name="verification_code" tabindex="3" id="verification_code" placeholder="验证码" size="6" class="form-control"/> 
            <a href="javascript:;" class="btn-token col-form-label text-sm" data-action="reg_email_or_phone_token"><?php _e('发送验证码', 'i_theme') ?></a>
        </div> 
        <?php } ?>
        <div class="form-group position-relative mb-3">
            <input type="password" name="user_pass" tabindex="4" id="user_pwd1" placeholder="<?php _e('密码', 'i_theme') ?>" size="30" class="form-control"/> 
            <div class="password-show-btn" data-show="0"><i class="iconfont icon-chakan-line"></i></div>
        </div> 
        <div class="form-group position-relative mb-3">
            <input type="password" name="user_pass2" tabindex="5" id="user_pwd2" placeholder="<?php _e('确认密码', 'i_theme') ?>" size="30" class="form-control"/> 
            <div class="password-show-btn" data-show="0"><i class="iconfont icon-chakan-line"></i></div>
        </div> 
        <div class="form-group mb-3">
            <?php echo get_captcha_input_html('user_register', 'form-control') ?>
        </div> 
        <div class="login-form mb-3"><?php do_action('register_form'); ?></div>
        <?php echo io_get_agreement_input() ?>
        <div class="d-flex my-3">
            <input type="hidden" name="action" value="user_register" />
            <input type="hidden" name="user_reg" value="ok" />
            <button id="submit" type="submit" name="submit" class="btn btn-shadow vc-theme btn-hover-dark btn-block"><?php _e('注册', 'i_theme'); ?></button>
            <a href="<?php echo esc_url(home_url()) ?>" class="btn vc-theme btn-outline btn-block mt-0 ml-4"><?php _e('首页', 'i_theme'); ?></a>
        </div> 
        <div class=" text-muted">
            <small><?php _e('已有账号?', 'i_theme'); ?> <a href="<?php echo esc_url(io_add_redirect(home_url() . '/login/')) ?>" class="signup"><?php _e('登录', 'i_theme'); ?></a></small> 
        </div>
        <div class="login-form"><?php do_action('io_login_form'); ?></div>
        <input type="hidden" name="redirect_to" value="<?php echo esc_url_raw($redirect_to); ?>" />
    </form> 
    <?php
}

/**
 * 获取绑定账号表单
 * @param mixed $redirect_to
 * @return void
 */
function io_get_form_bind_html($redirect_to, $args)
{
    extract($args)
    ?>
    
    <div class="sign-header h4 mb-3 mb-md-4"><?php echo $bind_title ?></div>
    <?php if($is_bind_type=="must"&&!isset($_GET['type'])): ?>
    <ul class="nav nav-justified mb-4" id="pills-tab" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="pills-new-tab" data-toggle="pill" data-btn="<?php _e('确定','i_theme') ?>" data-action="<?php echo $action_1 ?>" href="#pills-new" role="tab" aria-controls="pills-new" aria-selected="true"><?php echo $bind_title ?></a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="pills-old-tab" data-toggle="pill" data-btn="<?php _e('登录并绑定','i_theme') ?>" data-action="user_login" href="#pills-old" role="tab" aria-controls="pills-old" aria-selected="false"><?php _e('绑定现有账号','i_theme') ?></a>
        </li>
    </ul>
    <?php endif; ?>
    <form method="post" action="" class="form-validate wp-user-form mb-3" id="wp_login_form">
        <div class="tab-content" id="pills-tabContent">
            <div class="tab-pane fade show active" id="pills-new" role="tabpanel" aria-labelledby="pills-new-tab">
                <input type="hidden" name="action" value="<?php echo $action_1 ?>" />
                <input type="hidden" name="old_bind" value="1" />
                <input type="hidden" name="bind_type" value="<?php echo $bind_current_type ?>" />
                <input type="hidden" name="task" value="<?php echo $task ?>" />
                <div class="form-group mb-3">
                    <input type="text" name="email_phone" tabindex="2" id="user_email" placeholder="<?php echo $bind_placeholder ?>" size="30" class="form-control"/> 
                </div> 
                <div class="form-group mb-3 verification" style="display:none">
                    <input type="text" name="verification_code" tabindex="3" id="verification_code" placeholder="验证码"  size="6" class="form-control"/> 
                    <a href="javascript:;" class="btn-token col-form-label text-sm" data-action="reg_email_or_phone_token"><?php _e('发送验证码','i_theme') ?></a>
                </div> 
            </div>
            <div class="tab-pane fade" id="pills-old" role="tabpanel" aria-labelledby="pills-old-tab">
                <div class="form-group mb-3">
                    <input type="text" name="username" placeholder="<?php io_get_option('user_login_phone',false)?_e('用户名/手机号/邮箱','i_theme'):_e('用户名或邮箱','i_theme') ?>" class="form-control">
                </div>
                <div class="form-group position-relative mb-3">
                    <input type="password" name="password" placeholder="<?php _e('密码','i_theme') ?>" class="form-control">
                    <div class="password-show-btn" data-show="0"><i class="iconfont icon-chakan-line"></i></div>
                </div> 
                <input type="hidden" name="redirect_to" value="<?php echo esc_url_raw( $redirect_to ); ?>" />
            </div>
            <div class="mb-3"><?php do_action('io_bind_form'); ?></div>
        </div>
        <div class="form-group mb-3">
            <?php echo get_captcha_input_html($action_1,'form-control') ?>
        </div> 
        <button id="submit" type="submit" class="btn btn-shadow vc-red btn-hover-dark btn-block submit-btn"><?php _e('确定','i_theme') ?></button>
        <?php if (is_user_logged_in() && $is_bind_type=='must') { ?>
        <a href="<?php echo esc_url(wp_logout_url(home_url())) ?>" class="btn vc-red btn-outline btn-block mt-3"><?php _e('退出登录', 'i_theme') ?></a>
        <?php } ?>
        <div class="login-form mt-4 mb-n4 d-none"><?php do_action('io_login_form'); ?></div>
    </form> 
    <?php
}

/**
 * 获取绑定表单参数
 * @param string $redirect_to
 * @return array
 */
function get_bind_var($redirect_to)
{
    $is_bind_type      = io_get_option('bind_email', '');
    $bind_type         = io_get_option('bind_type', '');
    $bind_current_type = 'email';
    $bind_title        = __('绑定邮箱完成注册', 'i_theme');
    $action_1          = "register_after_bind_email";
    $task              = 'bind';
    $bind_placeholder  = __('输入邮箱', 'i_theme');
    $type              = '';
    
    if (empty($bind_type)) {
        wp_safe_redirect($redirect_to);
    }
    if ($is_bind_type != 'must' && !is_user_logged_in()) {
        wp_safe_redirect($redirect_to);
    } elseif (is_user_logged_in()) {
        $user  = wp_get_current_user();
        $email = $user->user_email;
        if (count($bind_type) == 1) {
            switch ($bind_type[0]) {
                case 'email':
                    if ($email) {
                        wp_safe_redirect($redirect_to);
                    } else {
                        $bind_current_type = 'email';
                    }
                    break;
                case 'phone':
                    $phone = io_get_user_phone($user->ID);
                    if ($phone) {
                        wp_safe_redirect($redirect_to);
                    } else {
                        $bind_current_type = 'phone';
                    }
                    break;
            }
        } else {
            if (!$email) {
                $bind_current_type = 'email';
            } else {
                $phone = io_get_user_phone($user->ID);
                if (!$phone) {
                    $bind_current_type = 'phone';
                }
            }
            if ($email && $phone) {
                wp_safe_redirect($redirect_to);
            }
        }
    } else {
        if ($is_bind_type == 'null' && !isset($_GET['type'])) {
            wp_safe_redirect($redirect_to);
        }
        if ($is_bind_type == 'must' && !isset($_GET['type'])) { //执行绑定
            if (count($bind_type) == 1) {
                $bind_current_type = $bind_type[0];
            }
            $task = 'new';
            if (!session_id())
                session_start();
            if (!isset($_SESSION['temp_oauth']) || (isset($_SESSION['temp_oauth']) && empty($_SESSION['temp_oauth'])))
                wp_safe_redirect($redirect_to);
            $type = maybe_unserialize($_SESSION['temp_oauth'])['type'];
            switch ($type) {
                case 'sina':
                    $type = 'weibo';
                    break;
                case 'wechat_gzh':
                    $type = 'wechat-gzh';
                    break;
                case 'wechat_dyh':
                    $type = 'wechat-dyh';
                    break;
                case 'wx':
                    $type = 'wechat';
                    break;
            }
        } elseif ($is_bind_type == 'must' && isset($_GET['type']) && !is_user_logged_in()) {
            wp_safe_redirect($redirect_to);
        }
    }
    if ($bind_current_type == 'email') {
        $bind_title = __('绑定邮箱', 'i_theme');
    } else {
        $bind_title       = __('绑定手机号', 'i_theme');
        $bind_placeholder = __('输入手机号', 'i_theme');
    }
    return compact('is_bind_type', 'bind_type', 'bind_current_type', 'bind_title', 'action_1', 'task', 'bind_placeholder', 'type');
}
