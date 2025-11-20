<?php
/*
 * @Theme Name:One Nav
 * @Theme URI:https://www.iotheme.cn/
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2021-04-15 04:33:41
 * @LastEditors: iowen
 * @LastEditTime: 2025-03-25 18:11:49
 * @FilePath: /onenav/inc/user-functions.php
 * @Description: 
 */
if ( ! defined( 'ABSPATH' ) ) { exit; } 


/* 默认登录页的主重定向 */
function redirect_login_page()
{
    $login_page  = home_url() . '/login/';
    $page_viewed = basename($_SERVER['REQUEST_URI']);
    $action      = !empty($_REQUEST['action']) ? $_REQUEST['action'] : '';
    $page        = array('login', 'register', 'lostpassword', 'retrievepassword');//, 'resetpass', 'rp');

    if (get_option('permalink_structure') && in_array($action, $page)) {
        if (!(in_array($action, array('lostpassword', 'retrievepassword')) && $_SERVER['REQUEST_METHOD'] == 'POST')) {
            $redirect_to = !empty($_REQUEST['redirect_to']) ? $_REQUEST['redirect_to'] : '';
            $url         = add_query_arg('action', $action, $login_page);
            if ($redirect_to)
                wp_safe_redirect(add_query_arg('redirect_to', urlencode($redirect_to), $url));
            else
                wp_safe_redirect($url);
            exit();
        }
    }
}
add_action('login_init','redirect_login_page');
/* 登录失败该去哪里 */
function custom_login_failed() {
    $login_page  = home_url().'/login/';
    wp_safe_redirect($login_page . '?login=failed');
    exit;
}
//add_action('wp_login_failed', 'custom_login_failed');
/* 如果任何字段为空，该去哪里 */
function verify_user_pass($user, $username, $password) {
    $login_page  = home_url().'/login/';
    if($username == "" || $password == "") {
        wp_safe_redirect($login_page);// . "?login=empty");
        exit;
    }
}
add_filter('authenticate', 'verify_user_pass', 1, 3);
/* 注销时该怎么办 */
function logout_redirect() {
    $login_page  = home_url().'/login/';
    wp_safe_redirect($login_page . "?login=false");
    exit;
}
//add_action('wp_logout','logout_redirect');


/**
 * 登录/注册/注销等动作页路由().
 * --------------------------------------------------------------------------------------
 */
function io_handle_action_page_rewrite_rules($wp_rewrite)
{
    if (get_option('permalink_structure')) {
        $new_rules['login/?$'] = 'index.php?custom_action=login';

        $lang = io_get_lang_rules(); 
        if($lang){
            $new_rules[$lang . 'login/?$']      = 'index.php?lang=$matches[1]&custom_action=login';
        }

        $wp_rewrite->rules = $new_rules + $wp_rewrite->rules;
    }   
}
add_action('generate_rewrite_rules', 'io_handle_action_page_rewrite_rules');

/**
 * 为自定义的Action页添加query_var白名单.
 * --------------------------------------------------------------------------------------
 */
function io_add_action_page_query_vars($public_query_vars)
{
    if (!is_admin()) {
        $public_query_vars[] = 'custom_action'; // 添加参数白名单action，代表是各种动作页
    }

    return $public_query_vars;
}
add_filter('query_vars', 'io_add_action_page_query_vars');

/**
 * 登录/注册/注销等动作页模板
 * --------------------------------------------------------------------------------------
 */
function io_handle_action_page_template()
{
    $action = strtolower(get_query_var('custom_action'));
    $allowed_actions = array(
        'login' => 'login',
    );
    if ($action && in_array($action, array_keys($allowed_actions))) {
        global $wp_query;
        $wp_query->is_home = false;
        $template = get_theme_file_path('/templates/login.php');
        include($template);
        exit;
    } elseif ($action && !in_array($action, array_keys($allowed_actions))) {
        // 非法路由处理
        set404();
        return;
    }
}
add_action('template_redirect', 'io_handle_action_page_template', 5);

/**
 * /user主路由处理.
 * --------------------------------------------------------------------------------------
 */
function io_redirect_user_main_route()
{
    $slug = 'user';
    if (preg_match('/^\/'.$slug.'$/i', $_SERVER['REQUEST_URI'])) {
        if ($user_id = get_current_user_id()) {
            //$nickname = get_user_meta(get_current_user_id(), 'nickname', true);
            wp_redirect(get_author_posts_url($user_id), 302);
        } else {
            wp_redirect(io_add_redirect(home_url().'/login/', io_get_current_url()), 302);
        }
        exit;
    }
}
add_action('init', 'io_redirect_user_main_route');

/**
 * /user子路由处理 - Rewrite.
 * --------------------------------------------------------------------------------------
 */
function io_handle_user_child_routes_rewrite($wp_rewrite)
{
    if (get_option('permalink_structure')) {
        $slug = 'user';
        // user子路由与孙路由必须字母组成，不区分大小写
        $new_rules[$slug.'$']             = 'index.php?is_user_route=1';
        $new_rules[$slug.'/([a-zA-Z]+)$']             = 'index.php?user_child_route=$matches[1]&is_user_route=1';
        $new_rules[$slug.'/([a-zA-Z]+)/([a-zA-Z]+)$'] = 'index.php?user_child_route=$matches[1]&user_grandchild_route=$matches[2]&is_user_route=1';
        // 分页
        $new_rules[$slug.'/([a-zA-Z]+)/page/?([0-9]{1,})/?$']             = 'index.php?user_child_route=$matches[1]&is_user_route=1&page=$matches[2]';
        $new_rules[$slug.'/([a-zA-Z]+)/([a-zA-Z]+)/page/?([0-9]{1,})/?$'] = 'index.php?user_child_route=$matches[1]&user_grandchild_route=$matches[2]&is_user_route=1&page=$matches[3]';

        $lang = io_get_lang_rules(); // 获取语言规则， 如 (zh)/
        if($lang){
            $new_rules[$lang . $slug.'$']             = 'index.php?lang=$matches[1]&is_user_route=1';
            $new_rules[$lang . $slug.'/([a-zA-Z]+)$']             = 'index.php?lang=$matches[1]&user_child_route=$matches[2]&is_user_route=1';
            $new_rules[$lang . $slug.'/([a-zA-Z]+)/([a-zA-Z]+)$'] = 'index.php?lang=$matches[1]&user_child_route=$matches[2]&user_grandchild_route=$matches[3]&is_user_route=1';
            // 分页
            $new_rules[$lang . $slug.'/([a-zA-Z]+)/page/?([0-9]{1,})/?$']             = 'index.php?lang=$matches[1]&user_child_route=$matches[2]&is_user_route=1&page=$matches[3]';
            $new_rules[$lang . $slug.'/([a-zA-Z]+)/([a-zA-Z]+)/page/?([0-9]{1,})/?$'] = 'index.php?lang=$matches[1]&user_child_route=$matches[2]&user_grandchild_route=$matches[3]&is_user_route=1&page=$matches[4]';
        }

        $wp_rewrite->rules = $new_rules + $wp_rewrite->rules;
    }

    return $wp_rewrite;
}
add_filter('generate_rewrite_rules', 'io_handle_user_child_routes_rewrite');

/**
 * 为自定义的当前用户页(user)添加query_var白名单.
 * --------------------------------------------------------------------------------------
 */
function io_add_user_page_query_vars($public_query_vars)
{
    if (!is_admin()) {
        $public_query_vars[] = 'is_user_route';
        $public_query_vars[] = 'user_child_route';
        $public_query_vars[] = 'user_grandchild_route';
    }

    return $public_query_vars;
}
add_filter('query_vars', 'io_add_user_page_query_vars');

function allowed_user_routes()
{
    $user_allow_routes = array(
        'spoor' => 'spoor',
        'info'  => 'info',
        'order' => 'order',
        'safe'  => 'safe',
        'msgs'  => array(
            'all',
            'comment',
            'star',
            'update',
            'cash'
        ),
    );
    return $user_allow_routes;
}
/**
 * /user子路由处理 - Template.
 * --------------------------------------------------------------------------------------
 */
function io_handle_user_child_routes_template()
{
    $is_user_route         = strtolower(get_query_var('is_user_route'));
    $user_child_route      = strtolower(get_query_var('user_child_route'));
    $user_grandchild_route = strtolower(get_query_var('user_grandchild_route'));
    if ($is_user_route) {
        global $wp_query;
        if ($wp_query->is_404()) {
            exit;
        }

        if (!io_user_center_enable()) {
            set404();
            exit;
        }

        $wp_query->is_home = false;

        if (!empty($user_child_route)) {
            $allow_routes = allowed_user_routes();
            $allow_child  = array_keys($allow_routes);

            if (!in_array($user_child_route, $allow_child)) {
                // 非法的子路由处理
                set404();
                exit;
            }

            $allow_grandchild = $allow_routes[$user_child_route];
            if (is_array($allow_grandchild)) {
                // 对于可以有孙路由的一般不允许直接子路由，必须访问孙路由，比如/user/notifications 必须跳转至/user/notifications/all
                if (empty($user_grandchild_route)) {
                    wp_redirect(home_url() . '/user/' . $user_child_route . '/' . $allow_grandchild[0], 302);
                    exit;
                }
                if (!in_array($user_grandchild_route, $allow_grandchild)) {
                    // 非法孙路由处理
                    set404();
                    exit;
                }
            }
        }
        $template = get_theme_file_path('/templates/user-center.php');
        load_template($template);
        exit;
    }
}
add_action('template_redirect', 'io_handle_user_child_routes_template', 5);


/**
 * 条件判断类名.
 * --------------------------------------------------------------------------------------
 */
function io_conditional_class($base_class, $condition, $active_class = 'active'){
    if ($condition) {
        return $base_class.' '.$active_class;
    }

    return $base_class;
}
/**
 * 给上传的图片生成独一无二的图片名.
 * 
 * @param string $filename 名称
 * @param string $type 文件类型
 * @return string
 */
function io_uniioque_img_name($filename, $type){
    $tmp_name = mt_rand(10, 25).time().$filename;
    $ext = str_replace('image/', '', $type);

    return md5($tmp_name).'.'.$ext;
}

/**
 * 裁剪图片并转换为JPG.
 * 
 * 绝对路径, 带文件名
 * @param string $origin        原图路径
 * @param string $dst        目标路径
 * @param int $dst_width      目标宽度
 * @param int $dst_height     目标高度
 * @param bool $delete_origin    是否删除原图
 * @return void
 */
function io_resize_img($origin, $dst = '', $dst_width = 100, $dst_height = 100, $delete_origin = false)
{ 
    // 检查原图是否存在
    if (!file_exists($origin)) {
        __echo(3, sprintf(__('原图文件不存在：%s', 'i_theme'), $origin));
        return;
    }
    $original_ratio = $dst_width / $dst_height;
    $info           = io_get_img_info($origin);

    // 获取当前内存限制
    $original_memory_limit = ini_get('memory_limit');

    // 临时将内存限制提高到 256M
    ini_set('memory_limit', '256M');

    if ($info) {
        // 根据图像类型创建图像资源
        switch (strtolower($info['type'])) {
            case 'jpg':
            case 'jpeg':
                $im = imagecreatefromjpeg($origin);
                break;
            case 'gif':
                $im = imagecreatefromgif($origin);
                break;
            case 'png':
                $im = imagecreatefrompng($origin);
                break;
            case 'bmp':
                $im = imagecreatefromwbmp($origin);
                break;
            default:
                __echo(3, sprintf(__('不支持的图像格式：%s', 'i_theme'), $info['type']));
                return;
        }
        if (!$im) {
            __echo(3, __('图像资源创建失败！', 'i_theme'));
            return;
        }
        // 计算裁剪区域
        if ($info['width'] / $info['height'] > $original_ratio) {
            $height = intval($info['height']);
            $width = intval($height * $original_ratio);
            $x = ($info['width'] - $width) / 2;
            $y = 0;
        } else {
            $width = intval($info['width']);
            $height = intval($width / $original_ratio);
            $x = 0;
            $y = ($info['height'] - $height) / 2;
        }
        // 创建新图像
        $new_img = imagecreatetruecolor($width, $height);
        imagecopy($new_img, $im, 0, 0, $x, $y, $width, $height);
        // 释放原图资源
        imagedestroy($im);

        // 缩放
        $target = imagecreatetruecolor($dst_width, $dst_height);
        imagecopyresampled($target, $new_img, 0, 0, 0, 0, $dst_width, $dst_height, $width, $height);

        // 保存
        imagejpeg($target, $dst ?: $origin, 86);

        // 删除资源
        imagedestroy($new_img);
        imagedestroy($target);

        if ($delete_origin) {
            unlink($origin);
        }
    }

    // 恢复原始内存限制
    ini_set('memory_limit', $original_memory_limit);

    return;
}


/**
 * 根据上传配置用户头像类型.
 * --------------------------------------------------------------------------------------
 */
function io_update_user_avatar_by_upload($user_id = 0){
    $user_id = $user_id ?: get_current_user_id();
    update_user_meta($user_id, 'avatar_type', 'custom');
}
/**
 * 根据上传配置用户背景图类型.
 * --------------------------------------------------------------------------------------
 */
function io_update_user_cover_by_upload($user_id , $meta){
    $user_id = $user_id ?: get_current_user_id();
    update_user_meta($user_id, 'io_user_cover', $meta);
}

/**
 * 获取图片信息.
 * --------------------------------------------------------------------------------------
 */
function io_get_img_info($img){
    $imageInfo = getimagesize($img);
    if ($imageInfo !== false) {
        $imageType = strtolower(substr(image_type_to_extension($imageInfo[2]), 1));
        $info = array(
            'width' => $imageInfo[0],
            'height' => $imageInfo[1],
            'type' => $imageType,
            'mime' => $imageInfo['mime'],
        );

        return $info;
    } else {
        return false;
    }
}


/**
 * 标记消息阅读状态
 * --------------------------------------------------------------------------------------
 */
function io_mark_message($id, $read = 1){
    $id = absint($id);
    $user_id = get_current_user_id(); //确保只能标记自己的消息

    if ((!$id || !$user_id)) {
        return false;
    }

    $read = $read == 0 ?: 1;

    global $wpdb;
    $table_name = $wpdb->iomessages;

    if ($wpdb->query($wpdb->prepare("UPDATE $table_name SET `msg_read` = %d WHERE `id` = %d AND `user_id` = %d", $read, $id, $user_id))) {
        return true;
    }

    return false;
}

/**
 * 标记所有未读消息已读.
 * --------------------------------------------------------------------------------------
 */
function io_mark_all_message_read($sender_id) {
    $user_id = get_current_user_id();
    if(!$user_id) return false;

    global $wpdb;
    $table_name = $wpdb->iomessages;

    if($wpdb->query( $wpdb->prepare("UPDATE $table_name SET `msg_read` = 1 WHERE `user_id` = %d AND `msg_read` = 0 AND `sender_id` = %d", $user_id, $sender_id) )) {
        return true;
    }
    return false;
}

/**
 * 获取单条消息.
 * --------------------------------------------------------------------------------------
 */
function io_get_message($msg_id){
    $user_id = get_current_user_id();
    if (!$user_id) {
        return false;
    } // 用于防止获取其他用户的消息

    global $wpdb;
    $table_name = $wpdb->iomessages;

    $row = $wpdb->get_row(sprintf("SELECT * FROM $table_name WHERE `id`=%d AND `user_id`=%d OR `sender_id`=%d", $msg_id, $user_id, $user_id));
    if ($row) {
        return $row;
    }

    return false;
}

/**
 * 查询消息
 * 
 * @global WPDB $wpdb
 * @param mixed $user_id 用户ID
 * @param mixed $type 消息类型 cash|货币 comment|评论 credit|积分 notification|通知 star|收藏
 * @param mixed $limit 每页最多显示数量
 * @param mixed $offset 偏移量
 * @param mixed $read 阅读状态 0未读 1已读 9全部
 * @param mixed $msg_status 消息状态
 * @param mixed $sender_id 发送者ID
 * @param mixed $count 是否计数
 * @return mixed
 */
function io_get_messages($user_id, $type = 'chat', $limit = 20, $offset = 0, $read = 0, $msg_status = 'publish', $sender_id = 0, $count = false)
{
    $user_id = $user_id ?: get_current_user_id();

    if (!$user_id) {
        return false;
    }

    if (!in_array($read, array(1, 0, 9))) {
        $read = 0;
    }
    if (!in_array($msg_status, array('publish', 'trash', 'all'))) {
        $msg_status = 'publish';
    }

    global $wpdb;

    $where = "`user_id` = $user_id";
    if ('all' !== $type) {
        $where .= $wpdb->prepare(" AND `msg_type`='%s'", $type);
    }
    if ($sender_id) {
        $where .= " AND `sender_id` = $sender_id";
    }
    if ($read != 9) {
        $where .= " AND `msg_read` = $read";
    }
    if ($msg_status != 'all') {
        $where .= $wpdb->prepare(" AND `msg_status` = '%s'", $msg_status);
    }

    $_s  = $count ? 'COUNT(*)' : '*';
    $_o  = $count ? '' : "LIMIT $offset, $limit";
    $sql = "SELECT $_s FROM $wpdb->iomessages WHERE $where ORDER BY id DESC, `id` DESC $_o";

    $results = $count ? $wpdb->get_var($sql) : $wpdb->get_results($sql);
    if ($results) {
        return $results;
    }

    return 0;
}

/**
 * 指定类型消息计数.
 * --------------------------------------------------------------------------------------
 */
function io_count_messages($user_id, $type = 'chat', $read = 0, $msg_status = 'publish', $sender_id = 0){
    return io_get_messages($user_id, $type, 0, 0, $read, $msg_status, $sender_id, true);
}

/**
 * 获取未读消息.
 * --------------------------------------------------------------------------------------
 */
function io_get_unread_messages($user_id, $type = 'chat', $limit = 20, $offset = 0, $msg_status = 'publish'){
    return io_get_messages($user_id, $type, $limit, $offset, 0, $msg_status);
}

/**
 * 未读消息计数.
 * --------------------------------------------------------------------------------------
 */
function io_count_unread_messages($user_id, $type = 'chat', $msg_status = 'publish'){
    return io_count_messages($user_id, $type, 0, $msg_status);
}


/**
 * 获取消息
 *
 * @param   int    $user_id   用户ID
 * @param   string $type      通知类型
 * @param   int    $page      分页
 * @param   int    $limit     每页最多显示数量
 * @return  object
 * --------------------------------------------------------------------------------------
 */
function io_get_notification_data($user_id = 0, $type = 'all', $page = 1, $limit = 10)
{
    if(!in_array($type, array('comment', 'star', 'credit', 'cash', 'update'))){
        $type = 'all';
    }
    $notifications   = io_get_messages($user_id, $type, $limit, ($page - 1) * $limit, 9);
    $count           = $notifications ? count($notifications) : 0;
    $total           = io_count_messages($user_id, $type, 9);
    $max_pages       = ceil($total / $limit);
    $pagination_base = home_url() . (is_array($type) ? '/user/msgs/all/page/%#%' : '/user/msgs/' . $type . '/page/%#%');
    return (object) array(
        'count'           => $count,
        'notifications'   => $notifications,
        'total'           => $total,
        'max_pages'       => $max_pages,
        'pagination_base' => $pagination_base,
        'prev_page'       => str_replace('%#%', max(1, $page - 1), $pagination_base),
        'next_page'       => str_replace('%#%', min($max_pages, $page + 1), $pagination_base)
    );
}

/**
 * 给自定义页面Body添加额外的class.
 *
 * @param array $classes
 * @return array
 * --------------------------------------------------------------------------------------
 */
function ct_modify_body_classes($classes)
{
    if ($query_var = get_query_var('user')) {
        $classes[] = 'user-body-' . $query_var;
    } elseif ($query_var = get_query_var('custom_action')) {
        $classes[] = 'login-body-' . $query_var;
    } elseif (get_query_var('is_user_route')) {
        $query_var = get_query_var('user_child_route');
        $classes[] = 'user-center user-center-' . $query_var;
    }

    return $classes;
}
add_filter('body_class', 'ct_modify_body_classes');

function get_bookmark_bg(){
    $bg=array(
        'bing'      => get_theme_file_uri('/assets/images/bg/bing.jpg'),
        'custom'    => get_theme_file_uri('/assets/images/bg/custom.png'),
        '07'        => get_theme_file_uri('/assets/images/bg/07.png'),
        '08'        => get_theme_file_uri('/assets/images/bg/08.png'),
        '09'        => get_theme_file_uri('/assets/images/bg/09.png'),
        '10'        => get_theme_file_uri('/assets/images/bg/10.png'),
        '11'        => get_theme_file_uri('/assets/images/bg/11.png'),
        '12'        => get_theme_file_uri('/assets/images/bg/12.png'),
        '13'        => get_theme_file_uri('/assets/images/bg/13.png'),
        '14'        => get_theme_file_uri('/assets/images/bg/14.png'),
        '15'        => get_theme_file_uri('/assets/images/bg/15.png'),
        '16'        => get_theme_file_uri('/assets/images/bg/16.png'),
        '17'        => get_theme_file_uri('/assets/images/bg/17.png'),
    );
    return $bg;
}
/**
 * 个人书签设置
 * --------------------------------------------------------------------------------------
 */
function get_bookmark_seting( $type, $seting=''){
    if($seting==''){
        global $bookmark_set;
        $seting == $bookmark_set;
    }
    $value='';
    switch ($type){
		case 'share_bookmark':
            if($seting){
                $value=$seting['share_bookmark']?'checked':'';
            }else{
                $value='checked';
            }
			break;
        case 'hide_title': 
            if($seting){
                $value=$seting['hide_title']?'checked':'';
            }else{
                $value='checked';
            }
			break;  
        case 'is_go': 
            if($seting){
                $value=(isset($seting['is_go']) && $seting['is_go'])?'checked':'';
            }else{
                $value='';
            }
			break; 
        case 'sites_title':
            if($seting){
                $value=$seting['sites_title'];
            }else{
                $value=_iol(io_get_option('search_skin','','big_title'))?:get_bloginfo('name');
            }
            break;
        case 'quick_nav':  
            if($seting){
                $value=$seting['quick_nav'];
            }else{
                $value='';
            }
            break;
        case 'bg': 
            if($seting){
                $value=$seting['bg'];
            }else{
                $value='bing';
            }
            break;
        case 'custom_img': 
            if($seting){
                $value=$seting['custom_img'];
            }else{
                $value='';
            }
            break;
    }
    return $value;
}


function io_login_page( $login_url, $redirect, $force_reauth ) {
    if(empty($redirect)){
        return home_url().'/login/';
    }else{
        return home_url().'/login/?redirect_to=' . $redirect;
    }
}
add_filter( 'login_url', 'io_login_page', 10, 3 );

function io_register_page() {
    return home_url().'/login/?action=register';
}
add_filter( 'register_url', 'io_register_page' );

function io_lostpassword_page($redirect) {
    if(empty($redirect)){
        return home_url().'/login/?action=lostpassword';
    }else{
        return home_url().'/login/?action=lostpassword&redirect_to=' . $redirect;
    }
}
add_filter( 'lostpassword_url', 'io_lostpassword_page' );
