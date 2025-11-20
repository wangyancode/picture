<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2023-01-17 22:37:17
 * @LastEditors: iowen
 * @LastEditTime: 2025-06-04 15:47:01
 * @FilePath: /onenav/inc/functions/io-user.php
 * @Description: 
 */

/**
 * 用户中心是否开启
 * @return bool
 */
function io_user_center_enable(){
    if(is_login() && isset($_GET['uc_key']) && $_GET['uc_key'] === io_get_option('uc_key')){
        return false;
    }
    return io_get_option('user_center', false) ? true : false;
}
/**
 * 获取隐藏的手机号码
 * @param string $phone
 * @return string
 */
function io_hide_phone($phone){
    if (strlen($phone) > 10) {
        return substr_replace($phone, '****', 3, 4);
    }
    return $phone;
}

/**
 * 隐藏邮箱号码
 * @param string $email
 * @return string
 */
function io_hide_email($email){
    $email_args = explode('@', $email);

    if (isset($email_args[0])) {
        return substr($email_args[0], 0, 1) . '****' . substr($email_args[0], -1) . '@' . $email_args[1];
    }

    return $email;
}

/**
 * 获取隐藏的内容
 * @param mixed $to
 * @param mixed $type
 * @return string
 */
function io_get_hide_info($to, $type){
    if ('email' === $type)
        return io_hide_email($to);
    else
        return io_hide_phone($to);
}
/**
 * 获取用户的手机号码
 * @param int $user_id
 * @param bool $hide
 * @return mixed
 */
function io_get_user_phone($user_id, $hide = true)
{
    $phone = get_user_meta($user_id, 'phone_number', true);

    if (!$phone) {
        return false;
    }

    if ($hide) {
        return io_hide_phone($phone);
    }

    return $phone;
}
/**
 * 获取用户权限描述字符.
 * @param mixed $user_id
 * @return string
 */
function io_get_user_cap_string($user_id)
{
    if(!$user_id) {
        return __('游客', 'i_theme');
    }
    if (user_can($user_id, 'manage_options')) {
        return __('管理员', 'i_theme');
    }
    if (user_can($user_id, 'edit_others_posts')) {
        return __('编辑', 'i_theme');
    }
    if (user_can($user_id, 'publish_posts')) {
        return __('作者', 'i_theme');
    }
    if (user_can($user_id, 'edit_posts')) {
        return __('投稿者', 'i_theme');
    }

    return __('读者', 'i_theme');
}
/**
 * 根据meta获取用户
 * 检查手机号是否存在
 * 
 * @param mixed $value  meta 值，如：手机号
 * @param mixed $field
 * @return mixed
 */
function io_get_user_by( $value, $field = 'phone')
{
    $cache = wp_cache_get($value, 'user_by_' . $field, true);
    if (false !== $cache) {
        return $cache;
    }

    $query = new WP_User_Query(array('meta_key' => 'phone_number', 'meta_value' => $value));

    if (!is_wp_error($query) && !empty($query->get_results())) {
        $user = $query->get_results()[0];
        wp_cache_set($value, $user, 'user_by_' . $field);
        return $user;
    } else {
        return false;
    }
}
/**
 * 绑定手机号，清空对应缓存
 * @param mixed $user_id
 * @param mixed $type
 * @param mixed $new_phone
 * @param mixed $old_phone
 * @return void
 */
function io_bind_phone_del_cache($user_id, $type, $new_phone, $old_phone){
    wp_cache_delete($old_phone, 'user_by_phone');
}
add_action('io_user_bind_new_email_or_phone', 'io_bind_phone_del_cache', 10, 4);

/**
 * 是否禁止注册
 * 
 * @return bool
 */
function io_is_close_register(){
    if (is_super_admin()) {
        return false;
    }
    return !get_option('users_can_register');
}
/**
 * Summary of io_get_user_cover
 * @param mixed $user_id
 * @param mixed $size
 * @param mixed $default
 * @return mixed
 */
function io_get_user_cover($user_id, $size = 'full', $default = ''){
    if (!in_array($size, array('full', 'mini'))) {
        $size = 'full';
    }
    if ($cover = get_user_meta($user_id, 'io_user_cover', true)) {
        return $cover . $size . '.jpg';
    }

    return $default ? $default : get_theme_file_uri('/assets/images/user-default-cover-'.$size.'.jpg');
}
/**
 * 保存用户的验证成功状态
 * @param mixed $type
 * @param mixed $user_id
 * @return void
 */
function io_set_user_verify_state($type, $user_id){
    if(!session_id()) session_start();
    $_SESSION['user_verify_state_' . $type . '_' . $user_id] = current_time('mysql');
    io_refresh_captcha_time();
}

/**
 * 校验用户验证是否通过
 * @param mixed $type
 * @param mixed $user_id
 * @return array
 */
function io_is_user_verify($type, $user_id){
    $name = array(
        'email' => __('旧邮箱', 'i_theme'),
        'phone' => __('旧手机号', 'i_theme'),
    );
    if(!session_id()) session_start();
    if (empty($_SESSION['user_verify_state_' . $type . '_' . $user_id])) {
        return array('error' => 1, 'msg' => sprintf( '%s验证失败！',$name[$type]));
    } else {
        $time_x = strtotime(current_time('mysql')) - strtotime($_SESSION['user_verify_state_' . $type . '_' . $user_id]);
        if ($time_x > (60*5)) { // 5分钟
            return array('error' => 1, 'msg' => sprintf( '%s验证已过期，请重新验证！',$name[$type]));
        }
        return array('error' => 0, 'msg' => sprintf( '%s验证成功！',$name[$type]));
    }
}

/**
 * 刷新验证码限制时间
 * @param mixed $second
 * @return void
 */
function io_refresh_captcha_time($second = 60){
    if(!session_id()) session_start();
    if (!empty($_SESSION['code_time'])) {
        $_SESSION['code_time'] = date('Y-m-d H:i:s', strtotime('-' . $second . ' second', strtotime($_SESSION['code_time'])));
    }
}

/**
 * 强制绑定邮箱或手机
 * @return void
 */
function io_redirect_user_bind_page()
{
    $is_bind   = io_get_option('bind_email','must');
    $bind_type = io_get_option('bind_type',array());

    if ( $is_bind!='must' || empty($bind_type) || is_super_admin() ) {
        return;
    }

    $user        = wp_get_current_user();
    $tab         = !empty($_GET['action']) ? $_GET['action'] : '';
    $redirect_to = !empty($_GET['redirect_to']) ? $_GET['redirect_to'] : home_url();
    if (!empty($user->ID) && !is_admin() && 'bind' != $tab) {
        //已经登录
        $email = $user->user_email;
        if (!$email && in_array('email', $bind_type)) {
            $bind_url = add_query_arg('redirect_to', $redirect_to, home_url().'/login/?action=bind&type=bind');
            wp_safe_redirect($bind_url);
            exit;
        }

        $phone = io_get_user_phone($user->ID);
        if (!$phone && in_array('phone', $bind_type)) {
            $bind_url = add_query_arg('redirect_to', $redirect_to, home_url().'/login/?action=bind&type=bind');
            wp_safe_redirect($bind_url);
            exit;
        }
    }
}
add_action('template_redirect', 'io_redirect_user_bind_page');

function is_user_page($name){
    return get_query_var('user_child_route') == $name;
}
/**
 * 绑定提示
 * @return void
 */
function add_remind_bind(){
    $user = wp_get_current_user();
    $is_bind   = io_get_option('bind_email','');
    if(!$user->ID || $is_bind!='bind' || is_404() || !io_get_option('remind_bind',false) || is_user_page('security') || is_io_login()) {
        return; 
    }
    $bind_type = io_get_option('bind_type',array());
    $title     = '';
    $email     = $user->user_email;
    if (!$email && in_array('email', $bind_type)) {
        $title = __('你没有绑定邮箱！','i_theme');
    } else {
        $phone = io_get_user_phone($user->ID);
        if (!$phone && in_array('phone', $bind_type)) {
            $title = __('你没有绑定手机号！','i_theme');
        }else{
            return; 
        }
    }

    if( !io_get_option('remind_only',false) || (  io_get_option('remind_only',false) && !isset($_COOKIE['io_remind_only'])) || (isset($_COOKIE['io_remind_only'])&&  $_COOKIE['io_remind_only']!="1") ){ 
        ?>
        <div id='io-remind-bind' class="io-bomb">
            <div class="io-bomb-overlay"></div>
            <div class="io-bomb-body text-center" style="max-width:260px">
                <div class="io-bomb-content rounded bg-white p-3"> 
                <i class="iconfont icon-tishi text-128 text-success"></i> 
                            <p class="text-md mt-3"><?php echo $title ?></p> 
                            <a href="<?php echo home_url().'/login/?action=bind&type=bind' ?>" class="btn btn-danger mt-3 popup-bind-close"><?php _e('前往绑定','i_theme') ?></a>
                </div>
                <div class="btn-close-bomb mt-2 text-center">
                    <i class="iconfont popup-bind-close icon-close-circle"></i>
                </div>
            </div>
            <script>  
                $(document).ready(function(){
                    <?php echo io_get_option('remind_only',false)?"if(getCookie('io_remind_only')!=1)":"" ?>
                        $('#io-remind-bind').addClass('io-bomb-open');
                });
                $(document).on('click','.popup-bind-close',function() {
                    $('#io-remind-bind').removeClass('io-bomb-open').addClass('io-bomb-close');
                    <?php echo (io_get_option('remind_only',false)?'setCookie("io_remind_only",1,1);':'') ?>
                    setTimeout(function(){
                        $('#io-remind-bind').remove(); 
                    },600);
                });
            </script>
        </div>
    <?php
    }
}
add_action( 'wp_footer', 'add_remind_bind' );

/**
 * 绑定按钮
 * @param mixed $user_id
 * @return string
 */
function io_bind_oauth_html( $user_id ){
    if (!$user_id) {
        return '';
    }

    $btn  = '';
    $like = io_get_uc_link('safe');

    $args = get_social_type_data();

    $weixin_type = io_get_option('open_weixin_gzh_key', 'gzh', 'type');

    foreach ($args as $arg) {
        $name     = $arg['name'];
        $type     = $arg['type'];
        $class    = $arg['class'];
        $name_key = $arg['n_key'];
        $icon     = '<i class="iconfont ' . $arg['icon'] . '"></i>';
        if ('alipay' == $type) {
            if (wp_is_mobile() && !strpos($_SERVER['HTTP_USER_AGENT'], 'Alipay')) {
                continue;
            }
        }

        $bind_href = io_get_oauth_login_url($type, $like);
        if ($bind_href) {
            $meta_key = $type;
            if ('weixin_gzh' == $type) {
                $meta_key = 'wechat_'.$weixin_type;
            }
            $oauth_info = get_user_meta($user_id, $meta_key . '_getUserInfo', true);
            $oauth_id   = get_user_meta($user_id, $meta_key . '_openid', true);
            $_btn       = '';
            if ( $oauth_id ) {
                $name .= !empty($oauth_info['name']) ? ' ' . esc_attr($oauth_info['name']) : (!empty($oauth_info[$name_key]) ? ' ' . esc_attr($oauth_info[$name_key]) : __('帐号','i_theme'));
                $_btn = '<a data-toggle="tooltip" href="javascript:;" openid="' . esc_attr($oauth_id) . '" title="'.__('解绑','i_theme') . $name . '" data-user_id="' . $user_id . '" data-type="' . $type . '" data-action="unbound_open_id" class="btn btn-block unbound-open-id vc-l-violet '.$class.'">' . $icon . ' ' . __('已绑定','i_theme') . $name . '</a>';
            } else {
                if ('weixin_gzh' === $type) {
                    $class .= ' qrcode-signin is-popup weixin-' . $weixin_type;
                }
                $_btn = '<a data-toggle="tooltip" title="'.__('绑定','i_theme') . $name . '" href="' . esc_url(add_query_arg(array('bind' => $type), $bind_href)) . '" class="btn btn-block btn-outline vc-blue '.$class.'">' . $icon . ' ' . __('绑定','i_theme') . $name . __('帐号','i_theme') . '</a>';
            }
            $btn .= '<div class="col-12 col-md-6 my-2">'.$_btn.'</div>';
        }
    }
    if (io_get_option('open_prk',false)) {
        $list = io_get_option('open_prk_list','');
        $bind_href = io_get_oauth_login_url('prk', io_get_current_url());
        if (is_array($list)) {
            foreach ($list as $type) { //聚合登录增加前置io_
                $oauth_info = get_user_meta($user_id, 'io_' . $type . '_getUserInfo', true);
                $oauth_id = get_user_meta($user_id, 'io_' . $type . '_openid', true);
                $name = get_open_login_name($type);
                $_btn = '';
                $ico  = $type;
                if('wx'===$type){
                    $ico = 'wechat';
                }
                if('sina'===$type){
                    $ico = 'weibo';
                }
                $icon = '<i class="iconfont icon-' . $ico . '"></i>';
                if ($oauth_info && $oauth_id) {
                    $name .= !empty($oauth_info['nickname']) ? ' ' . esc_attr($oauth_info['nickname']) : __('帐号','i_theme');
                    $_btn = '<a data-toggle="tooltip" href="javascript:;" openid="' . esc_attr($oauth_id) . '" title="'.__('解绑','i_theme') . $name . '" data-user_id="' . $user_id . '" data-type="io_' . $type . '" data-action="unbound_open_id" class="btn btn-block unbound-open-id vc-l-violet ">' . $icon . ' ' . __('已绑定','i_theme') . $name . '</a>';
                } else {
                    $_btn = '<a data-toggle="tooltip" title="'.__('绑定','i_theme') . $name . '" href="' . esc_url(add_query_arg(array('type' => $type), $bind_href)) . '" class="btn btn-block btn-outline vc-blue ' . $class . '">' . $icon . ' ' . __('绑定','i_theme') . $name . __('帐号','i_theme') . '</a>';
                }
                $btn .= '<div class="col-12 col-md-6 my-2">'.$_btn.'</div>';
            }
        }
    }

    $html = '<div class="text-lg pb-3 border-bottom border-color border-2w mb-3 mt-5">'.__('账号绑定','i_theme').'</div>';
    $html .= '<div class="row">';
    $html .= $btn;
    $html .= '</div>';
    
    return $html;
}


/**
 * 后台用户表格添加自定义内容
 * @param mixed $columns
 * @return mixed
 */
function io_admin_users_columns($columns){
    $orderby = isset($_REQUEST['orderby']) ? $_REQUEST['orderby'] : '';//箭头按钮
    $order   = isset($_REQUEST['order']) && 'desc' == $_REQUEST['order'] ? 'asc' : 'desc';
    $_role = $columns['role'];

    unset($columns['role']);
    unset($columns['name']);
    unset($columns['posts']);
    unset($columns['email']);

    $columns['user_data'] = '<a href="' . add_query_arg(array('orderby' => 'display_name', 'order' => $order)) . '"><span>昵称</span></a>';
    $columns['user_data'] .= ' · <a href="' . add_query_arg(array('orderby' => 'email', 'order' => $order)) . '"><span>邮箱</span></a>';
    $columns['user_data'] .= ' · <a href="' . add_query_arg(array('orderby' => 'phone_number', 'order' => $order)) . '"><span>手机号</span></a>';

    $columns['oauth'] = '绑定账号';
    
    $columns['all_time'] = '<a href="' . add_query_arg(array('orderby' => 'user_registered', 'order' => $order)) . '"><span>注册</span></a> · <a href="' . add_query_arg(array('orderby' => 'last_login', 'order' => $order)) . '"><span>登录</span></a>';

    $columns['role'] = $_role;

    return $columns;
}
add_filter('manage_users_columns', 'io_admin_users_columns');

/**
 * 后台用户表格自定义内容
 * @param mixed $output
 * @param mixed $column_name
 * @param mixed $user_id
 * @return mixed
 */
function io_admin_output_users_columns($output, $column_name, $user_id){
    $user = get_userdata($user_id);
    switch ($column_name) {
        case "user_data":
            $user_email   = empty($user->display_name) ? '未设置' : $user->display_name;
            $user_email   = empty($user->user_email) ? '未设置' : '<a href="mailto:' . $user->user_email . '">' . $user->user_email . '</a>';
            $phone_number = get_user_meta($user->ID, 'phone_number', true)?:'未绑定';
            return '<div style="font-size: 12px;">昵称：' . $user->display_name . '<br>邮箱：' . $user_email . '<br>手机：' . $phone_number . '</div>';

        case "all_time":
            $last_login = get_user_meta($user->ID, 'last_login', true);
            $last_login = '最近登录：'. ($last_login ? '<span title="' . $last_login . '">' . io_date_time($last_login) . '</span>' : '--');
            $reg_time   = get_date_from_gmt($user->user_registered);
            $reg_time   = '注册时间：'. ($reg_time ? '<span title="' . $reg_time . '">' . io_date_time($reg_time) . '</span>' : '--');
            $last_ip    = get_user_meta($user->ID, 'last_login_ip', true);
            $last_ip    = '登录地点：'. ($reg_time ? '<span title="' . $last_ip . '">' . io_get_ip_location($last_ip) . '</span>' : '--');
            return '<div style="font-size: 12px;">' . $reg_time . '<br>' . $last_login . '<br>' . $last_ip . '</div>';

        case "oauth":
            $args =  get_social_type_data();
            $oauth = array();
            foreach ($args as $arg) {
                $name = $arg['name'];
                $type = $arg['type'];
                if (io_get_oauth_login_url($type)) {
                    if ('weixin_gzh' == $type) {
                        $type = 'wechat_'.io_get_option('open_weixin_gzh_key', 'gzh', 'type');
                    }
                    $oauth_id = get_user_meta($user_id, $type . '_openid', true);
                    if ($oauth_id) {
                        $oauth[] = $name;
                    }
                }
            }
            $html = $oauth ? '已绑定：' . implode('、', $oauth) : '未绑定';
            return '<div style="font-size: 12px;">' . $html . '</div>';
    }
    return $output;
}
add_filter('manage_users_custom_column', 'io_admin_output_users_columns', 10, 3);

function io_admin_users_list_sort_query_args($args){
    $orderby = isset($_REQUEST['orderby']) ? $_REQUEST['orderby'] : '';
    if (in_array($orderby, array('last_login', 'phone_number'))) {
        $args['orderby']  = 'meta_value';
        $args['meta_key'] = $orderby;
    }
    //默认排序方式为注册时间
    if (!isset($_REQUEST['orderby'])) {
        $args['order']   = 'desc';
        $args['orderby'] = 'user_registered';
    }
    return $args;
}
add_filter('users_list_table_query_args', 'io_admin_users_list_sort_query_args');

/**
 * 用户中心绑定、修改信息html
 * 
 * @param mixed $type
 * @param mixed $user
 * @param mixed $step
 * @return string
 */
function io_get_user_bind_info_html($type = 'email', $user = '', $step = 1){
    $user = $user ? : wp_get_current_user();
    switch ($type) {
        case 'email':
            $name  = __('邮箱','i_theme');
            $to    = $user->user_email;
            $title = $to ? __('修改邮箱','i_theme'):__('绑定邮箱','i_theme');
            $icon  = 'icon-email';
            break;
        case 'phone':
            $name  = __('手机号','i_theme');
            $to    = io_get_user_phone($user->ID);
            $title = $to ? __('修改手机号','i_theme'):__('绑定手机号','i_theme');
            $icon  = 'icon-phone-num';
            break;
        case 'password':
            $is_new = get_user_meta($user->ID, 'oauth_new', true);
            $title = $is_new ? __('设置密码','i_theme') : __('修改密码','i_theme');
            $icon  = 'icon-key-circle';
            break;
        case 'reset_password':
            $title = __('重设密码','i_theme');
            $icon  = 'icon-key-circle';
            break;
    }
    $head_fx  = '';
    $bind_tip = '';
    if ('password' === $type) {
        $head_fx  = 'fx-yellow';
        $bind_html = io_get_password_set_from($is_new, $user);
    } elseif ('reset_password' === $type) {
        $head_fx  = 'fx-red';
        $bind_html = io_get_password_reset_from($user);
    } else {
        if ($to) {
            $bind_tip = '<div class="mx-3 mt-3 step-simple text-xs">
            <span class="' . (1 === $step ? 'active' : '') . '">' . sprintf(__('验证旧%s', 'i_theme'), $name) . '</span>
            <span class="' . (2 === $step ? 'active' : '') . '">' . sprintf(__('设置新%s', 'i_theme'), $name) . '</span>
            <span class="' . (3 === $step ? 'active' : '') . '">' . __('修改成功', 'i_theme') . '</span></div>';
            if (1 === $step) {
                $bind_html = io_get_verify_user_from($to, $type);
            } elseif (2 === $step) {
                $bind_html = io_get_user_bind_from($type, false);
            }
        } else {
            $bind_html = io_get_user_bind_from($type);
        }
    }
    $html = '';
    $html .= io_get_modal_header($head_fx, $icon);
    $html .= '<div class="modal-body blur-bg">';
    $html .= '<div class="text-center pt-3">'.$title.'</div>';
    $html .= $bind_tip;
    $html .= $bind_html;
    $html .= '</div>';

    return $html;
}

/**
 * 验证邮箱或者手机权限
 * @param mixed $to
 * @param mixed $type
 * @return string
 */
function io_get_verify_user_from($to, $type = 'email'){
    $des   = array(
        'email' => __('获取验证码以验证旧邮箱：', 'i_theme'),
        'phone' => __('获取验证码以验证旧手机号：', 'i_theme'),
    );
    $form  = '';
    $input = '<div class="mb-4">';
    $input .= '<div class="text-xs text-muted mb-3">' . $des[$type] . '<code>'.io_get_hide_info($to, $type).'</code></div>';
    $input .= '<div class="form-group verification mb-3">
            <input type="text" name="verification_code" tabindex="3" id="verification_code" placeholder="' . __('验证码', 'i_theme') . '" size="6" class="form-control"/> 
            <a href="javascript:;" class="btn-token col-form-label text-sm" data-action="verify_user_email_or_phone_token">' . __('发送验证码', 'i_theme') . '</a>
        </div>';
    $input .= '<div class="form-group mb-3">'.get_captcha_input_html('verify_user_competence').'</div>';
    $input .= '</div>';

    $input .= '<input type="hidden" name="action" value="verify_user_competence">';
    $input .= '<input type="hidden" name="type" value="' . $type . '">';
    $input .= '<button type="submit" class="btn vc-blue btn-shadow btn-submit btn-block"><i class="iconfont icon-competence mr-1"></i>'.__('立即验证','i_theme').'</button>';

    $form = '<form class="user-bind-from p-3">' . $input . '</form>';
    return $form;
}

/**
 * 绑定邮箱或者手机
 * @param mixed $type
 * @param mixed $is_new
 * @return string
 */
function io_get_user_bind_from($type = 'email',$is_new = true){
    $form    = '';
    $placeholder = array(
        'email' => __('请输入邮箱', 'i_theme'),
        'phone' => __('请输入手机号', 'i_theme'),
    );
    $des         = array(
        'email' => __('请输入您需要修改的新邮箱。', 'i_theme'),
        'phone' => __('请输入您需要修改的新手机号。', 'i_theme'),
    );
    $input = '';
    $input .= '<div class="mb-4">';
    $input .= $is_new ? '' : '<div class="text-xs text-muted mb-1">' . $des[$type] . '</div>';
    $input .= '<div class="form-group mb-3">
            <input type="text" name="email_phone" tabindex="2" id="user_email" placeholder="' . $placeholder[$type] . '" size="30" class="form-control"/> 
        </div> 
        <div class="form-group mb-3 verification" style="display:none">
            <input type="text" name="verification_code" tabindex="3" id="verification_code" placeholder="' . __('验证码', 'i_theme') . '" size="6" class="form-control"/> 
            <a href="javascript:;" class="btn-token col-form-label text-sm" data-action="bind_new_email_or_phone_token">' . __('发送验证码', 'i_theme') . '</a>
        </div> ';
    $input .= '<div class="form-group mb-3">'.get_captcha_input_html('user_bind_number_captcha').'</div>';
    $input .= '</div>';

    $input .= io_get_agreement_input();
    $input .= '<input type="hidden" name="action" value="user_bind_new_email_or_phone">';
    $input .= '<input type="hidden" name="type" value="' . $type . '">';
    $input .= '<button type="submit" class="btn vc-blue btn-shadow btn-submit btn-block"><i class="iconfont icon-adopt mr-1"></i>'.__('确认提交','i_theme').'</button>';

    $form = '<form class="user-bind-from p-3">' . $input . '</form>';

    return $form;
}

/**
 * 用户协议html
 * @param mixed $checked
 * @return string
 */
function io_get_agreement_input($checked = ''){
    $option = io_get_option('user_agreement', array('switch'=>false));
    $html  = '';
    if($option['switch']){
        if(''===$checked ){
            $checked = $option['default'];
        }
        $agreement = '';
        if($option['pact_page']){
            $agreement = ' <a target="_blank" href="' . get_permalink($option['pact_page']) . '">'.__('用户协议','i_theme').'</a>';
        }
        if($option['privacy_page']){
            $agreement .= '、<a target="_blank" href="' . get_permalink($option['privacy_page']) . '">'.__('隐私声明','i_theme').'</a>';
        }
        $checked = $checked ? ' checked="checked"' : '';
        $input = '<input type="checkbox" class="custom-control-input" name="user_agreement" id="user_agreement"' . $checked . ' value="agree">';
        $input .= '<label class="custom-control-label" for="user_agreement">' . __('阅读并同意','i_theme') . $agreement . '</label>';
        $html = '<div class="custom-control custom-checkbox text-xs mb-2 text-muted">' . $input . '</div>';
    }
    return $html;
}

/**
 * 用户协议判断
 * @return void
 */
function io_ajax_agreement_judgment(){
    if (io_get_option('user_agreement', false, 'switch') && empty($_REQUEST['user_agreement'])) {
        io_error (array('status' => 3, 'msg' => __('请先阅读并同意用户协议', 'i_theme')));
        exit();
    }
}
/**
 * 获取安全信息设置按钮
 * 
 * @param mixed $user
 * @param mixed $type
 * @return string
 */
function io_get_security_info_bind_btn($user, $type = 'email'){
    if (!$user) {
        return '';
    }
    switch ($type) {
        case 'email':
            $title = __('绑定邮箱','i_theme');
            $to    = $user->user_email;
            $icon  = 'icon-email';
            break;
        case 'phone':
            $title = __('绑定手机','i_theme');
            $to    = io_get_user_phone($user->ID);
            $icon  = 'icon-phone-num';
            break;
        default:
            $title = __('设置密码','i_theme');
            $to    = '';
            $icon  = 'icon-key-circle';
            break;
    }
    $url = esc_url(add_query_arg(array('type' => $type, 'action' => 'get_user_security_info_set_modal'), admin_url( 'admin-ajax.php' )));
    if('password'===$type){
        $tip = __('修改密码','i_theme');
        $class = 'vc-theme btn-shadow';
        $name  = $tip;
    }else{
        if($to){
            $tip = __('换绑账号','i_theme');
            $class = 'vc-l-gray';
            $name  = __('已绑定：','i_theme'). io_get_hide_info($to, $type);
        }else{
            $tip = __('绑定账号','i_theme');
            $class = 'vc-l-red btn-outline';
            $name  = $title;
        }
    }
    $html = '<div class="d-flex my-4 align-items-center">
        <div class="text-sm text-muted">' . $title . '</div>
        <a data-toggle="tooltip" title="' . $tip . '" href="' . $url . '" class="btn user-bind-modal btn-sm ml-auto ' . $class . '"><i class="iconfont ' . $icon . ' mr-2"></i>' . $name . '</a>
    </div>';
    return $html;
}
/**
 * 获取密码设置表单
 * @param mixed $is_new
 * @param mixed $user
 * @return string
 */
function io_get_password_set_from($is_new, $user){
    $user_email = $user->user_email;
    $phone      = io_get_user_phone($user->ID);
    $html       = '';

    $html .= '<form class="user-bind-from p-3">';
    $html .= '<div class="mb-4">';
    if (!$is_new) {
        $html .= '<div class="form-group position-relative mb-3">';
        $html .= '<input type="password" name="user_pass_old" tabindex="1" placeholder="' . __('请输入原密码','i_theme') . '" size="30" class="form-control"/>';
        $html .= '<div class="password-show-btn" data-show="0"><i class="iconfont icon-chakan-line"></i></div>';
        $html .= '</div>';
    } else {
        $html .= '<input type="hidden" name="oauth_new" value="' . $is_new . '">';
    }
    $html .= '<div class="form-group position-relative mb-3">';
    $html .= '<input type="password" name="user_pass" tabindex="2" placeholder="' . __('请输入新密码','i_theme') . '" size="30" class="form-control"/>';
    $html .= '<div class="password-show-btn" data-show="0"><i class="iconfont icon-chakan-line"></i></div>';
    $html .= '</div>';
    $html .= '<div class="form-group position-relative mb-3">';
    $html .= '<input type="password" name="user_pass2" tabindex="3" placeholder="' . __('请再次输入新密码','i_theme') . '" size="30" class="form-control"/>';
    $html .= '<div class="password-show-btn" data-show="0"><i class="iconfont icon-chakan-line"></i></div>';
    $html .= '</div>';
    $html .= '<div class="form-group mb-3">'.get_captcha_input_html('user_change_password').'</div>';
    $html .= '</div>';

    $html .= '<input type="hidden" name="action" value="user_change_password">';
    $html .= '<button type="button" class="btn vc-yellow btn-shadow btn-submit btn-block"><i class="iconfont icon-adopt mr-1"></i>'.__('确认提交','i_theme').'</button>';
    $html .= '</form>';
    if (!$is_new && ($user_email || $phone)) {
        $url = esc_url(add_query_arg(array('type' => 'reset_password', 'action' => 'get_user_security_info_set_modal'), admin_url( 'admin-ajax.php' )));
        $html .= '<div class="text-right px-3 pb-2 mt-n3">';
        $html .= '<a href="' . $url . '" class="text-xs user-reset-password text-muted"><i class="iconfont icon-tishi mr-1"></i>'.__('忘记密码？点击重设密码。','i_theme').'</a>';
        $html .= '</div>';
    }

    return $html;
}
/**
 * 重设密码表单
 * 
 * @param mixed $user
 * @return string
 */
function io_get_password_reset_from($user=''){
    $user = $user ? : wp_get_current_user();
    if(!$user->ID){
        $html = '<form method="post" class="wp-user-form" id="wp_login_form">';
    }else{
        $html = '<form class="p-3" id="wp_login_form">';
    }
    $html .= '<div class="form-group mb-3">
            <input type="text" name="email_phone" tabindex="2" id="user_email" placeholder="' . get_reg_name('lost_verify') . '" size="30" class="form-control input-material"/> 
        </div> 
        <div class="form-group mb-3 verification" style="display:none">
            <input type="text" name="verification_code" tabindex="3" id="verification_code" placeholder="' . __('验证码', 'i_theme') . '" size="6" class="form-control input-material"/> 
            <a href="javascript:;" class="btn-token col-form-label text-sm" data-action="lost_email_or_phone_token">' . __('发送验证码', 'i_theme') . '</a>
        </div> 
        <div class="form-group position-relative mb-3">
            <input type="password" name="user_pass" tabindex="4" id="user_pwd1" placeholder="' . __('密码', 'i_theme') . '" size="30" class="form-control input-material"/> 
            <div class="password-show-btn" data-show="0"><i class="iconfont icon-chakan-line"></i></div>
        </div> 
        <div class="form-group position-relative mb-3">
            <input type="password" name="user_pass2" tabindex="5" id="user_pwd2" placeholder="' . __('确认密码', 'i_theme') . '" size="30" class="form-control input-material"/> 
            <div class="password-show-btn" data-show="0"><i class="iconfont icon-chakan-line"></i></div>
        </div> 
        <div class="form-group mb-3">' . get_captcha_input_html('reset_password', 'form-control input-material') . '</div> 
        <div class="login_fields">
            <input type="hidden" name="action" value="reset_password" />
            <input type="submit" id="submit" name="user-submit" value="' . __('提交', 'i_theme') . '" class="btn vc-theme btn-block btn-shadow" />
            '.(!$user->ID?'':'<input type="hidden" name="redirect_to" value="' . io_get_current_url() . '?reset=true" />').'
        </div>';
    $html .= '</form>';
    return $html;
}

function io_user_restrict_admin() {
    $can = 'manage_options';
    if(io_get_option('edit_to_admin', false)){
        $can = 'edit_posts'; //'edit_others_posts';
    }
    if (is_admin() && !current_user_can($can) && !wp_doing_ajax()) {
        wp_redirect(home_url('user/'));
        exit;
    }
}
add_action( 'admin_init', 'io_user_restrict_admin', 1 );

/**
 * 获取用户不同状态的文章数量
 * @param int $user_id  
 * @param int $status 
 * @return int 
 */
function get_user_post_count($user_id, $status = 'publish', $post_type = 'post') {
    global $wpdb;

    if(!$user_id){
        return 0;
    }

    $cache_key = 'post_count_' . $user_id . '_' . $status;
    $cache_num = wp_cache_get($cache_key, 'post_count_' . $post_type, true);
    if (false !== $cache_num) {
        return $cache_num;
    }

    if ('all' == $status) {
        $count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(`ID`) FROM $wpdb->posts WHERE `post_author` = %d AND `post_type` = %s", $user_id, $post_type));
    } else {
        $count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(`ID`) FROM $wpdb->posts WHERE `post_author` = %d AND `post_type` = %s AND `post_status` = %s", $user_id, $post_type, $status));
    }

    wp_cache_set($cache_key, $count, 'post_count_' . $post_type, DAY_IN_SECONDS);

    return $count;
}

/**
 * 获取作者签名
 */
function get_user_desc($user_id) {
    $des = get_user_meta($user_id, 'description', true);
    if (!$des) {
        $des = __('帅气的我简直无法用语言描述！', 'i_theme');
    }
    return esc_attr($des);
}


/**
 * 获取用户点赞、查看数量
 * @param mixed $user_id
 * @return mixed
 */
function io_get_user_badges($user_id, $rand_color = false)
{
    if (!$user_id) {
        return;
    }

    $post_n   = io_number_format(get_user_post_count($user_id, 'publish'));
    $com_n    = io_number_format(get_user_comment_count($user_id));
    $like_n   = io_number_format(get_user_posts_meta_sum($user_id, '_like_count'));
    $view_n   = io_number_format(get_user_posts_meta_sum($user_id, 'views'));
    $user_url = get_author_posts_url($user_id);

    $badges = array(
        array(
            'title' => sprintf(__('共%s篇文章', 'i_theme'), strip_tags($post_n)),
            'icon'  => 'icon-post',
            'text'  => $post_n,
            'color' => $rand_color ? 'vc-l-blue' : 'vc-l-white',
            'url'   => $user_url
        ),
        array(
            'title' => sprintf(__('共%s条评论', 'i_theme'), strip_tags($com_n)),
            'icon'  => 'icon-comment',
            'text'  => $com_n,
            'color' => $rand_color ? 'vc-l-purple' : 'vc-l-white',
            'url'   => $user_url
        ),
        array(
            'title' => sprintf(__('获得%s个赞', 'i_theme'), strip_tags($like_n)),
            'icon'  => 'icon-like-line',
            'text'  => $like_n,
            'color' => $rand_color ? 'vc-l-yellow' : 'vc-l-white',
            'url'   => $user_url
        ),
        array(
            'title' => __('人气 ', 'i_theme') . strip_tags($view_n),
            'icon'  => 'icon-hot',
            'text'  => $view_n,
            'color' => $rand_color ? 'vc-l-red' : 'vc-l-white',
            'url'   => $user_url
        )
    );
    $badges = apply_filters('user_count_badges', $badges, $user_id);

    $html = '';
    foreach ($badges as $badge) {
        $html .= '<span class="badge ' . $badge['color'] . ' flex-fill" data-toggle="tooltip" title="' . $badge['title'] . '"><i class="iconfont ' . $badge['icon'] . '"></i>' . $badge['text'] . '</span>';
    }

    return $html;
}

/**
 * 获取用户操作面板
 * @return string
 */
function io_get_menu_user_box($class = '') {
    static $html = null;
    if (null !== $html) {
        return '<div class="menu-user-box ' . $class . '">' . $html . '</div>';
    }

    if (io_is_close_register() && !is_user_logged_in()) {
        $html = '';
        return $html;
    }

    $user    = wp_get_current_user();
    $user_id = $user->ID;
    $con     = '';
    if ($user_id) {
        $btn = '<div class="d-flex justify-content-around mt-2">';
        $btn .= io_get_menu_user_btn(home_url('user/'), 'icon-user', __('个人中心', 'i_theme'), 'vc-l-blue', false);

        if (io_get_option('is_contribute', true)) {
            $btn .= io_get_menu_user_btn(io_get_template_page_url('template-contribute.php'), 'icon-creation', __('收录投稿', 'i_theme'), 'vc-l-purple', false);
        }

        $btn .= io_get_menu_user_btn(wp_logout_url(home_url()), 'icon-quit', __('安全退出', 'i_theme'), 'vc-l-red', false);
        $btn .= '</div>';

        if (is_super_admin()) {
            $btn .= '<div class="d-flex justify-content-around mt-2">';
            $btn .= io_get_menu_user_btn(io_get_admin_iocf_url(), 'icon-theme', __('主题设置', 'i_theme'), 'vc-l-yellow');
            $btn .= io_get_menu_user_btn(io_get_admin_iocf_url('', 'home_module'), 'icon-home-config', __('首页配置', 'i_theme'), 'vc-l-cyan');
            $btn .= io_get_menu_user_btn(admin_url(), 'icon-seting', __('后台管理', 'i_theme'), 'vc-l-yellow');
            $btn .= '</div>';
        }

        $con .= io_get_user_box($user);
        $con .= '<div class="mt-2 text-center user-btn">' . $btn . '</div>';
    } else {
        $btn = '<div class="d-flex justify-content-around mt-2">';
        $btn .= io_get_menu_user_btn(wp_login_url(io_get_current_url()), 'icon-user', __('登录', 'i_theme'), 'vc-l-blue');
        $btn .= io_get_menu_user_btn(wp_registration_url(), 'icon-register', __('注册', 'i_theme'), 'vc-l-green');
        $btn .= io_get_menu_user_btn(wp_lostpassword_url(io_get_current_url()), 'icon-password', __('找回密码', 'i_theme'), 'vc-l-yellow');
        $btn .= '</div>';

        $con .= io_get_user_box($user);
        $con .= '<div class="text-center user-btn">' . $btn . '</div>';

        $social_btn = '';//get_social_login_btn(); TODO 微信登陆需要弹窗登录支持。。。。。。。。。。。
        if ($social_btn) {
            $con .= '<p class="social-separator my-3">' . __('快速登录', 'i_theme') . '</p>';
            $con .= '<div class="social-login-btn my-2 d-flex flex-wrap justify-content-center">';
            $con .= $social_btn;
            $con .= '</div>';
        }
    }

    $html = $con;
    return '<div class="menu-user-box ' . $class . '">' . $html . '</div>';
}

/**
 * 获取用户信息框
 * @param mixed $user
 * @return string
 */
function io_get_user_box($user) {
    $user_id = $user->ID;
    if ($user_id) {
        $url       = get_author_posts_url($user_id);
        $avatar    = get_avatar($user_id);
        $user_name = $user->display_name;
        $desc      = get_user_desc($user_id);
        $badge     = io_get_user_badges($user_id);
        $badge     = $badge ? '<div class="author-badge mt-2 d-flex justify-content-center">' . $badge . '</div>' : '';
    } else {
        $url       = wp_login_url(io_get_current_url());
        $avatar    = '<img src="' . get_theme_file_uri('/assets/images/gravatar.jpg') . '" class="avatar avatar-96 photo" height="96" width="96">';
        $user_name = __('未登录', 'i_theme');
        $desc      = __('登录后即可体验更多功能', 'i_theme');
        $badge     = '';
    }
    $user_box = '<div class="nav-user-box br-lg mt-n5 fx-bg fx-shadow px-3 py-2" js-href="' . esc_url($url) . '">';
    $user_box .= '<div class="user-info d-flex align-items-center position-relative">';
    $user_box .= '<div class="avatar-img">' . $avatar . '</div>';
    $user_box .= '<div class="user-right flex-fill overflow-hidden ml-2">';
    $user_box .= '<b>' . $user_name . '</b><div class="text-xs line1">' . $desc . '</div>';
    $user_box .= '</div>';
    $user_box .= '</div>';
    $user_box .= $badge;
    $user_box .= '</div>';

    return $user_box;
}

/**
 * 获取用户操作按钮
 * @param mixed $link
 * @param mixed $icon
 * @param mixed $text
 * @param mixed $color
 * @param bool $target
 * @param string $class
 * @param string $type
 * @return string
 */
function io_get_menu_user_btn($link, $icon, $text, $color, $target = true, $class = '', $type = 'box') {
    $target = $target ? 'target="_blank"' : '';
    $icon   = $icon ? '<i class="iconfont ' . $icon . '"></i>' : '';

    if ($type == 'box') {
        $btn = '<button js-href="' . esc_url($link) . '" class="btn menu-user-btn text-xs flex-fill ' . $class . ' ' . $color . '" ' . $target . ' rel="nofollow">' . $icon . '<span class="white-nowrap">' . $text . '</span></button>';
    } else {
        $btn = '<button js-href="' . esc_url($link) . '" class="menu-user-btn" ' . $target . ' rel="nofollow"><div class="badge ' . $color . ' i-btn">' . $icon . '</div><div class="' . $color . ' text-color">' . $text . '</div></button>';
    }
    return $btn;
}

/**
 * 获取用户收藏的已发布文章 ID 列表。
 * 
 * 验证文章状态
 * 缓存验证结果
 *
 * @param int|string|null $user_id    用户 ID，如果为空则默认获取当前用户 ID。
 * @param string          $post_type  文章类型，默认 'sites'，可为 'app'、'book'、'post' 等。
 * @return int[]                      返回已发布状态的文章 ID 数组。
 */
function io_get_user_star_post_ids($user_id = null, $post_type = 'sites')
{
    $user_id = absint($user_id ?: get_current_user_id());

    if (!$user_id) {
        return [];
    }

    $cache_key    = 'user_star_' . $user_id . '_' . sanitize_key($post_type);
    $cached_posts = wp_cache_get($cache_key, 'user_stars');

    if ($cached_posts) {
        return $cached_posts;
    }

    $meta_key      = 'io_star_' . sanitize_key($post_type);
    $starred_posts = get_user_meta($user_id, $meta_key, true) ?: [];

    // 空数组直接返回
    if (empty($starred_posts)) {
        return [];
    }

    // 批量查询所有文章状态
    $valid_posts = get_posts([
        'post__in'               => $starred_posts,
        'post_status'            => 'publish',
        'post_type'              => $post_type,
        'fields'                 => 'ids',      // 只返回ID
        'posts_per_page'         => -1,         // 获取全部
        'no_found_rows'          => true,       // 禁用分页计数
        'update_post_meta_cache' => false,      // 不更新meta缓存
        'update_post_term_cache' => false,      // 不更新term缓存
    ]);
    wp_cache_set($cache_key, $valid_posts, 'user_stars', DAY_IN_SECONDS);

    // 只在有变化时更新（比较数组差异）
    if (array_diff($starred_posts, $valid_posts)) {
        update_user_meta($user_id, $meta_key, $valid_posts);
    }


    return $valid_posts;
}

/**
 * 用户收藏变更时清除所有缓存层
 */
add_action('updated_user_meta', function ($meta_id, $user_id, $meta_key, $_meta_value) {
    if (strpos($meta_key, 'io_star_') === 0) {
        $post_type = substr($meta_key, 8);
        $cache_key = 'user_star_' . $user_id . '_' . $post_type;

        wp_cache_delete($cache_key, 'user_stars'); // 清除对象缓存
    }
}, 10, 4);

/**
 * 获取文章收藏数量.
 * @param mixed $post_id 文章ID
 * @return int
 */
function io_get_posts_star_count($post_id)
{
    $post_id = $post_id ? $post_id : get_the_ID();

    $star = get_post_meta($post_id, '_star_count', true);

    if ('' === $star) {
        $star = 0;
        // 初始化收藏数
        update_post_meta($post_id, '_like_count', 0);
    }
    return $star;
}

/**
 * 判断是否文章是否被当前用户收藏.
 * @param mixed $post_id
 * @param mixed $post_type 文章类型
 * @return bool
 */
function io_is_my_star($post_id = '', $post_type = '')
{
    if (!is_user_logged_in()) {
        return false;
    }

    if (empty($post_type)) {
        $post_type = get_post_type($post_id);
    }

    $user_star_post = (array)get_user_meta(get_current_user_id(), 'io_star_' . $post_type, true);
    if (in_array($post_id, $user_star_post)) {
        return true;
    }
    return false;
}

/**
 * 统计用户收藏的文章数.
 * @param mixed $user_id
 * @param mixed $post_type
 * @return int
 */
function io_count_user_star_posts($user_id, $post_type = "sites")
{
    $ids = io_get_user_star_post_ids($user_id, $post_type);
    if ($ids) {
        return count($ids);
    }
    return 0;
}
/**
 * 统计用户收藏的所有文章数.
 * @param mixed $user_id
 * @return int
 */
function io_count_user_star_all_posts($user_id)
{
    $count      = 0;
    $post_types = array('sites', 'app', 'book', 'post');
    foreach ($post_types as $post_type) {
        $count += io_count_user_star_posts($user_id, $post_type);
    }
    return $count;
}
