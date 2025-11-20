<?php
/*!
 * Theme Name:One Nav
 * Theme URI:https://www.iotheme.cn/
 * Author:iowen
 * Author URI:https://www.iowen.cn/
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * 禁止自动生成 768px 缩略图
 */
function shapeSpace_customize_image_sizes($sizes) {
    unset($sizes['medium_large']);
    return $sizes;
}
add_filter('intermediate_image_sizes_advanced', 'shapeSpace_customize_image_sizes');
/**
 * wordpress禁用图片属性srcset和sizes
 */
add_filter( 'add_image_size', function(){return 1;} );
add_filter( 'wp_calculate_image_srcset_meta', '__return_false' );
add_filter( 'big_image_size_threshold', '__return_false' );

/**
 * 禁止WordPress自动生成缩略图
 */
function ztmao_remove_image_size($sizes) {
    unset( $sizes['small'] );
    unset( $sizes['medium'] );
    unset( $sizes['large'] );
    return $sizes;
}
add_filter('image_size_names_choose', 'ztmao_remove_image_size');
/**
 * 古腾堡编辑器样式
 */
function block_editor_styles() {
    wp_enqueue_style( 'block-editor-style', get_theme_file_uri( '/assets/css/editor-blocks.css' ), array(), IO_VERSION );
}
function initialization(){
    io_add_db_table();
} 
function io_add_db_table() {
    global $wpdb;
    //if($wpdb->has_cap('collation')) {
    //    if(!empty($wpdb->charset)) {
    //        $table_charset = "DEFAULT CHARACTER SET $wpdb->charset";
    //    }
    //    if(!empty($wpdb->collate)) {
    //        $table_charset .= " COLLATE $wpdb->collate";
    //    }
    //}
    $charset_collate = $wpdb->get_charset_collate();
    // TODO `meta` text DEFAULT NULL,
    if (!io_is_table($wpdb->iomessages)) {
        $sql = "CREATE TABLE $wpdb->iomessages (
            `id` bigint(20) NOT NULL AUTO_INCREMENT,
            `user_id` bigint(20) DEFAULT NULL COMMENT '收件人',
            `sender_id` bigint(20) DEFAULT NULL COMMENT '发件人',
            `sender` varchar(50) DEFAULT NULL COMMENT '发件人名称',
            `msg_type` varchar(20) DEFAULT NULL COMMENT '消息类型',
            `msg_date` datetime DEFAULT NULL COMMENT '消息时间',
            `msg_title` text COMMENT '消息标题',
            `msg_content` text COMMENT '消息内容',
            `meta` text DEFAULT NULL COMMENT '消息元数据',
            `msg_read` int(11) DEFAULT 0 COMMENT '消息阅读状态',
            `msg_status` varchar(20) DEFAULT NULL COMMENT '消息状态',
            PRIMARY KEY (`id`),
            KEY `user_id` (`user_id`),
            KEY `sender_id` (`sender_id`),
            KEY `msg_read` (`msg_read`)
        )$charset_collate;";
        $wpdb->query($sql);
    }
    
    if(!io_is_table($wpdb->iocustomurl)) {
        $sql = "CREATE TABLE $wpdb->iocustomurl (
            `id` bigint(20) NOT NULL AUTO_INCREMENT,
            `user_id` bigint(20) DEFAULT NULL,
            `term_id` bigint(20) NOT NULL DEFAULT 0,
            `post_id` bigint(20) DEFAULT NULL,
            `url` text DEFAULT NULL,
            `url_name` varchar(50) DEFAULT NULL,
            `url_ico` text DEFAULT NULL,
            `summary` varchar(255) DEFAULT NULL,
            `date` datetime DEFAULT NULL,
            `order` int(11) NOT NULL DEFAULT 0,
            `status` int(11) NOT NULL DEFAULT 1,
            PRIMARY KEY (`id`),
            KEY `user_id` (`user_id`),
            KEY `term_id` (`term_id`),
            KEY `url_name` (`url_name`)
        )$charset_collate;";
        $wpdb->query($sql);
    }
    
    if(!io_is_table($wpdb->iocustomterm)) {
        $sql = "CREATE TABLE $wpdb->iocustomterm (
            `id` bigint(20) NOT NULL AUTO_INCREMENT,
            `name` varchar(50) DEFAULT NULL,
            `ico` varchar(255) DEFAULT NULL,
            `user_id` bigint(20) DEFAULT NULL,
            `parent` bigint(20) NOT NULL DEFAULT 0,
            `order` int(11) NOT NULL DEFAULT 0,
            PRIMARY KEY (`id`),
            KEY `user_id` (`user_id`)
        )$charset_collate;";
        $wpdb->query($sql);
    }
    
    if(!io_is_table($wpdb->ioviews)) {
        $sql = "CREATE TABLE $wpdb->ioviews (
            `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            `time` date NOT NULL,
            `post_id` bigint(20) NOT NULL,
            `type` varchar(50) NOT NULL,
            `desktop` int(11) NOT NULL,
            `mobile` int(11) NOT NULL,
            `download` int(11) NOT NULL,
            `count` int(11) NOT NULL,
			`favorite` int(11) NOT NULL,
			`like` int(11) NOT NULL,
			`comment` int(11) NOT NULL,
            `buy` int(11) NOT NULL,
            PRIMARY KEY (`id`),
            KEY `post_id` (`post_id`),
            KEY `type` (`type`),
            KEY `time` (`time`),
            UNIQUE KEY `post_time_unique` (`post_id`, `time`)
        )$charset_collate;";
        $wpdb->query($sql);
    }
    
    if(!column_in_db_table($wpdb->users,'io_id')){
        $wpdb->query("ALTER TABLE $wpdb->users ADD io_id varchar(100)");
    }
    update_option('io_add_db_tables', IO_VERSION );
}

# 激活友情链接模块
# --------------------------------------------------------------------
if(io_get_option('show_friendlink',false))add_filter( 'pre_option_link_manager_enabled', '__return_true' );
require_once get_theme_file_path('/inc/post-type.php');
if(io_get_option('save_image',false)) require_once get_theme_file_path('/inc/save-image.php');
if( io_get_option('post_views',false) ) require_once get_theme_file_path('/inc/postviews/postviews.php');

# 获取IOCF框架图片
# --------------------------------------------------------------------
function get_post_meta_img($post_id, $key, $single){
    $metas = get_post_meta($post_id, $key, $single);
    if(is_array($metas)){
        return $metas['url'];
    } else {
        return $metas;
    }
}
function get_search_list() {
    if (is_custom_search()) {
        /**
         * 次级导航自定义搜索
         */
        $search_id   = get_query_var('search_list_id');
        $custom_list = get_option('io_search_list');
        $args        = array(
            'id'   => 'home',
            'list' => $custom_list['search_list']
        );
        if ($search_id) {
            if (isset($custom_list['custom_search_list'][$search_id - 1])) {
                $_list = array();
                if(isset($custom_list['custom_search_list'][$search_id - 1]['search_list'])){
                    $_list = $custom_list['custom_search_list'][$search_id - 1]['search_list'];
                }
                $args = array(
                    'id'   => $search_id - 1,
                    'list' => $_list
                );
            }
        }
        return $args;
    } else {
        include(get_theme_file_path('/inc/search-list.php'));
        return array(
            'id'   => 'home',
            'list' => $search_list
        );
    }
}

# 网站块类型（兼容1.0）
# --------------------------------------------------------------------
function io_sites_before_class($post_id){
    $metas      = get_post_meta_img($post_id, '_wechat_qr', true);
    $sites_type = get_post_meta($post_id, '_sites_type', true);
    if($metas != '' || $sites_type == "wechat"){
        return 'wechat';
    } elseif($sites_type == "down") {
        return 'down';
    } else {
        return '';
    }
}
# 添加菜单
# --------------------------------------------------------------------
function io_nav_menu($location, $echo = true){
    static $io_nav_menu = array();
    if (isset($io_nav_menu[$location])) {
        $nav_menu = $io_nav_menu[$location];
        if ($echo) {
            echo $nav_menu;
            return;
        } else {
            return $nav_menu;
        }
    }
 
    $nav_menu = '';
    if ( function_exists( 'wp_nav_menu' ) && has_nav_menu($location) ) {
        $nav_menu = wp_nav_menu(array(
            'container'      => false,
            'items_wrap'     => '%3$s',
            'theme_location' => $location,
            'echo'           => false
        ));
    } else {
        if (current_user_can('manage_options')) { 
            $nav_menu = '<li><a href="'.get_option('siteurl').'/wp-admin/nav-menus.php">'.__('请到[后台->外观->菜单]中设置菜单。','i_theme').'</a></li>';
        }
    }
    $io_nav_menu[$location] = $nav_menu;

    if ($echo) {
        echo $nav_menu;
    } else {
        return $nav_menu;
    }
}
/**
 * 添加统计数据
 * 
 * @param int $post_id
 * @param int $count 增加的值
 * @param string $action 数据类型 view down favorite like comment buy
 * @return mixed
 */
function io_add_post_view($post_id, $count = 1, $action = 'view')
{
    if (!io_get_option('leader_board', false))
        return;

    global $ioview;

    $post_type = get_post_type($post_id);

    switch ($action) {
        case 'down':
            $args = array(
                'download' => $count
            );
            break;
        case 'favorite':
            $args = array(
                'favorite' => $count
            );
            break;
        case 'like':
            $args = array(
                'like' => $count
            );
            break;
        case 'comment':
            $args = array(
                'comment' => $count
            );
            break;
        case 'buy':
            $args = array(
                'buy' => $count
            );
            break;
        case 'view':
            $desktop = 0;
            $mobile = 0;
            if (wp_is_mobile())
                $mobile = $count;
            else
                $desktop = $count;

            $args = array(
                'desktop' => $desktop,
                'mobile'  => $mobile,
                'count'   => $count
            );
            break;
        
        default:
            return;
    }

    return $ioview->addViews($post_id, $post_type, $args);
}

/**
 * 获取排行榜
 * 
 * @param string $time 时间 today yesterday month all
 * @param string $type 类型 sites app book post
 * @param int $count 数量，默认10
 * @param int|string|array $term 分类id
 * @param bool $is_post 返回$post
 * @return array|object|bool
 */
function io_get_post_rankings($time, $type, $count = 10, $term = '', $is_post = false)
{
    global $ioview;

    $tax_query = array();
    if (is_array($term) && isset($term['relation'])) {
        $tax_query = $term;
        $term      = [];
        foreach ($tax_query as $key => $value) {
            if (is_array($value)) {
                $term += $value['terms'];
            }
        }
    }
    switch ($time) {
        case "today":
            if (empty($term)) {
                $sql = $ioview->getDayRankings(date('Y-m-d', current_time('timestamp')), $type, $count);
            } else {
                $sql = $ioview->getDayRankingsByTerm(date('Y-m-d', current_time('timestamp')), $type, $term, $count);
            }
            break;
        case "yesterday":
            if (empty($term)) {
                $sql = $ioview->getDayRankings(date("Y-m-d", strtotime("-1 day", current_time('timestamp'))), $type, $count);
            } else {
                $sql = $ioview->getDayRankingsByTerm(date("Y-m-d", strtotime("-1 day", current_time('timestamp'))), $type, $term, $count);
            }
            break;

        case "week":
        case "last_week":
        case "month":
            if (empty($term)) {
                $sql = $ioview->getRangeRankings($time, $type, $count);
            } else {
                $sql = $ioview->getRangeRankingsByTerm($time, $type, $term, $count);
            }
            break;
        case "all":
        default:
            $sql = 'all';
            break;
    }
    if ($sql != 'all') {
        $m_post = $sql;
        if ($m_post) {
            $_post_ids = array();
            $post_view = array();
            foreach ($m_post as $post) {
                $_post_ids[]               = $post->post_id;
                $post_view[$post->post_id] = $post->count;
            }
            $args    = array(
                'post_type'           => $type,
                'post__in'            => $_post_ids,
                'orderby'             => 'post__in',
                'ignore_sticky_posts' => 1,
                //'nopaging'            => true,
                'posts_per_page'      => $count,
            );
            $myposts = new WP_Query($args);
            $_post   = $is_post ? $myposts : get_rankings_data($myposts, $type, $post_view);
            return $_post;
        } else {
            return false;
        }
    } else {
        $args    = array(
            'post_type'           => $type,
            'posts_per_page'      => $count,
            'ignore_sticky_posts' => 1,
            'meta_key'            => 'views',
            'orderby'             => array('meta_value_num' => 'DESC', 'date' => 'DESC'),
            'tax_query'           => $tax_query,
        );
        $myposts = new WP_Query($args);
        $_post   = $is_post ? $myposts : get_rankings_data($myposts, $type);
        return $_post;
    }
}
/**
 * 返回排行榜文章元数据数组
 * @param mixed $myposts 
 * @param mixed $type 
 * @param int|array $post_view 查看次数
 * @return array 
 */
function get_rankings_data($myposts, $type, $post_view = 0)
{
    $index = 1;
    $_post = array();
    if ($myposts->have_posts()):
        while ($myposts->have_posts()):
            $myposts->the_post();
            $url     = get_permalink();
            $post_id = get_the_ID();
            $_is_go  = get_post_meta($post_id, '_url_go', true);
            $is_go   = false;
            if ($type == 'sites') {
                $sites_type = get_post_meta($post_id, '_sites_type', true);
                if ($sites_type == 'sites') {
                    if (io_get_option('global_goto', false) || (!io_get_option('global_goto', false) && $_is_go)) {
                        $url   = get_post_meta($post_id, '_sites_link', true);
                        $is_go = true;
                    }
                }
            }
            if ($post_view == 0)
                $views = get_post_meta($post_id, 'views', true);
            else
                $views = $post_view[$post_id];
            $_post[] = array(
                "index" => $index,
                "id"    => $post_id,
                "title" => get_the_title(),
                "url"   => $url,
                "is_go" => $is_go,
                "views" => io_number_format($views),
            );
            $index++;
        endwhile;
        wp_reset_postdata();
    endif;
    return $_post;
}

function io_secret_key()
{
    $key = get_option('iotheme_encode_key', '');
    if (!$key) {
        $key = IOTOOLS::getKm();
        update_option('iotheme_encode_key', $key);
    }
    return $key;
}
/**
 * 加密内容
 * @param string $input 需加密的内容
 * @return string
 */
function base64_io_encode($input, $key=''){
    $url = htmlspecialchars_decode($input); 
    if ($key == '') {
        $key = io_secret_key();
    }
    $url = str_rot_pass($url, $key);
    return rtrim(strtr(base64_encode($url), '+/', '-_'), '=');
}
/**
 * 解密内容 
 * @param string $input 需解密的内容
 * @return string
 */
function base64_io_decode($input, $key=''){
    $url = base64_decode(str_pad(strtr($input, '-_', '+/'), strlen($input) % 4, '=', STR_PAD_RIGHT));
    if ($key == '') {
        $key = io_secret_key();
    }
    return str_rot_pass($url, $key, true);
}
/**
 * 根据 KEY 相应的ascii值旋转每个字符串 
 * @param string $str
 * @param string $key 
 * @param bool $decrypt 
 * @return string
 */
function str_rot_pass($str, $key, $decrypt = false){
    // if key happens to be shorter than the data
    $key_len = strlen($key);
    $result = str_repeat(' ', strlen($str));
    for($i=0; $i<strlen($str); $i++){
        if($decrypt){
            $ascii = ord($str[$i]) - ord($key[$i % $key_len]);
        } else {
            $ascii = ord($str[$i]) + ord($key[$i % $key_len]);
        }
    
        $result[$i] = chr($ascii);
    }
    return $result;
}
/**
 * 对于部分链接，拒绝搜索引擎索引.
 * 
 * @param string $output Robots.txt内容
 * @param bool   $public
 * @return string
 */
function io_robots_modification($output, $public)
{
    $site_url = parse_url( home_url() );
    $path     = ( ! empty( $site_url['path'] ) ) ? $site_url['path'] : '';
    $output  .= "Disallow: $path/go/\n";
    $output  .= "Disallow: $path/user\n";
    $output  .= "Disallow: $path/hotnews\n";
    return $output;
}
add_filter('robots_txt', 'io_robots_modification', 10, 2);

/**
 * 过滤公告帖子地址
 * 
 * @description: post_type_link 自定义帖子，post_link post帖子
 * @param * $permalink
 * @param * $post
 * @return *
 */
function io_suppress_post_link( $permalink, $post ) {
    if($post->post_type=='bulletin'){
        if($goto = get_post_meta($post->ID,'_goto',true)){
            if(get_post_meta($post->ID,'_is_go',true)){
                $permalink = go_to($goto);
            }else{
                $permalink = $goto;
            }
        }
    }else{
        //global $queried_object_id; 
        //if(is_mininav() || ($queried_object_id && defined( 'DOING_AJAX' ) && DOING_AJAX) ){
        //    $post_id = get_queried_object_id()?:$queried_object_id;
        //    $permalink = $permalink.'?menu-id='.get_post_meta( $post_id, 'nav-id', true ).'&mininav-id='.$post_id;
        //}
    }
    return $permalink;
}
add_filter( 'post_type_link', 'io_suppress_post_link', 10, 2 );
add_filter( 'post_link', 'io_suppress_post_link', 10, 2 );

/**
 * 编辑器增强
 * ******************************************************************************************************
 */
add_action('init','io_tinymce_button');
function io_tinymce_button() {
    add_filter( 'mce_external_plugins', 'io_add_tinymce_button' );
    add_filter( 'mce_buttons', 'io_register_tinymce_button' );
    add_filter( 'mce_buttons_2', 'io_register_tinymce_button2' );
}
add_filter( 'mce_css', 'io_plugin_mce_css' );
function io_plugin_mce_css( $mce_css ) {
    if ( ! empty( $mce_css ) )
        $mce_css .= ',';
    $mce_css .= get_theme_file_uri('/assets/css/editor-style.css');
    return $mce_css;
}
function io_register_tinymce_button( $buttons ) {
    $buttons = ['formatselect', 'bold', 'bullist', 'numlist', 'blockquote', 'alignleft', 'aligncenter', 'alignright', 'link', 'spellchecker','wp_page'];
    if (!is_admin() && wp_is_mobile()) {
        $buttons = ['io_h2', 'io_h3', 'bold', 'bullist', 'link', 'spellchecker'];
    }
    if (!is_admin()) {
        $buttons[] = 'io_img';
    }
    
    if(is_admin()){
        $buttons[] = 'io_ad';
        $buttons[] = 'io_hide';
        $buttons[] = 'io_post_card';
    }
    $buttons[] = 'wp_adv';
    $buttons[] = 'dfw';
    if(!is_admin()){
        $buttons[] = 'fullscreen';
    }

    return $buttons;
}
function io_register_tinymce_button2($buttons) { 
    if(!is_admin() && wp_is_mobile()){
        $buttons = ['styleselect', 'fontsizeselect', 'forecolor', 'removeformat', 'undo', 'redo'];
        return $buttons;
    }
    $io_btn = array('styleselect'); 
    if(is_admin()){
        $io_btn[] = 'fontselect';
        $io_btn[] = 'fontsizeselect';
    }
    return array_merge($io_btn, $buttons);
}
function io_add_tinymce_button( $plugin_array ) {
    $plugin_array['io_button_script'] = get_theme_file_uri('/assets/js/mce-buttons.js');
    return $plugin_array;
}    
//为编辑器 mce 加入body class
function io_tiny_mce_before_init_filter($settings, $editor_id)
{
    if ('post_content' === $editor_id) {
        $settings['body_class'] .= ' front-edit ' . theme_mode();
    }
    return $settings;
}
add_filter('tiny_mce_before_init', 'io_tiny_mce_before_init_filter', 10, 2);
/**
 * 主题切换
 * ******************************************************************************************************
 */
function theme_mode() {
    $type  = io_get_option('theme_auto_mode', 'manual-theme');
    $class = io_get_option('theme_mode', 'io-grey-mode');
    if ($type != 'null') {
        if ($class == 'io-grey-mode') {
            $class = '';
        }
        if (isset($_COOKIE['io_night_mode']) && $_COOKIE['io_night_mode'] != '') {
            $class = trim($_COOKIE['io_night_mode']) == '0' ? 'io-black-mode' : '';
        } else {
            if ('time-auto' == $type) {
                $time      = current_time('G');
                $time_auto = io_get_option('time_auto', array('from' => '07', 'to' => '18'));
                if ($time > $time_auto['to'] || $time < $time_auto['from']) {
                    $class = 'io-black-mode';
                }
            }
            if ('auto-system' === $type) {
                $auto = isset($_COOKIE['prefers-color-scheme']) ? trim($_COOKIE['prefers-color-scheme']) : '';
                if ('dark' == $auto) {
                    $class = 'io-black-mode';
                }
            }
        }
    }
    return apply_filters('io_theme_mode_class', $class);
}

function io_body_class(){
    $class = 'container-body' . get_page_mode_class('body');

    $post_id = 98761;
    if(is_home() || is_front_page()){
        $post_id = 0;
    }elseif(is_mininav()){
        $post_id = get_queried_object_id();
    }
    $page_config = get_page_module_config($post_id);
    if($page_config && $page_config['aside_show']){
        $class .= ' aside-show';
    }
    if($page_config && $page_config['page_module'][0]['type'] === 'search'){
        $class .= ' have-banner';
    }
    

    $class .= io_is_show_sidebar(); 
    if(io_get_option('min_nav',false)) $class .= ' aside-min'; 
    if ((is_single() || is_page()) && get_post_format()) {
        $class .= ' postformat-' . get_post_format();
    }
    return apply_filters('io_add_body_class', trim($class));
}
function io_html_class(){
    echo 'class="'.theme_mode().'"';
}

/**
 * 侧边栏显示判断
 * 
 * sidebar_no sidebar_left sidebar_right
 * @return string 
 */
function io_is_show_sidebar(){
    global $sidebar_class;

    if( !$sidebar_class ){
        $sidebar_class = '';
        if(is_io_user()){
            return $sidebar_class;
        }
        // 
        if(apply_filters('io_show_sidebar', false)){
            $sidebar_class = ' sidebar_right';
            return $sidebar_class;
        }
        if(wp_is_mobile()){ // 移动端不显示侧边栏
            $sidebar_class = ' sidebar_no';
            return $sidebar_class;
        }
        $class = io_get_option( 'sidebar_layout','sidebar_right');
        if( is_home() || is_front_page() || is_mininav()){
                $sidebar_class = "";
            return $sidebar_class;
        } 
        if(is_single() || is_page()){ 
            $post_id        = get_queried_object_id();
            $post_type      = get_post_type();
            $show_layout    = get_post_meta($post_id, 'sidebar_layout', true);
            $page_template  = str_replace('.php', '', get_page_template_slug($post_id));

            if($show_layout){
                $class = $show_layout=='default' ? $class : $show_layout;
            }
            if ( is_blog() ) {
                if(is_active_sidebar('sidebar-h')){
                    $sidebar_class = " ".$class; 
                }else{
                    $sidebar_class = " sidebar_no";
                }
                return $sidebar_class;
            }
            if( is_mininav() ){ // 次级导航
                if(is_active_sidebar('sidebar-page-'.$post_id)){
                    $sidebar_class = " ".$class; 
                }else{
                    $sidebar_class = " sidebar_no";
                }
                return $sidebar_class;
            }
            if(is_page() && is_page_template()){
                if(is_active_sidebar('sidebar-'.$page_template)){
                    $sidebar_class = " ".$class; 
                }else{
                    if (is_active_sidebar('sidebar-s')) {
                        $sidebar_class =  " ".$class; 
                    }else {
                        $sidebar_class = " sidebar_no";
                    }
                }
                return $sidebar_class;
            }
            switch ($post_type){
                case "page":
                case "post":
                    if(!is_active_sidebar( 'sidebar-s' ))
                        $class = 'sidebar_no';
                    break;
                default: 
                    if(!is_active_sidebar( 'sidebar-'.$post_type.'-r' ))
                        $class = 'sidebar_no';
                    break;
            }
            if('sidebar_no'!==$class){
            }
            $sidebar_class = " " . $class . " " . $post_type;
            return $sidebar_class;
        }
        if(is_author() || is_io_user()){
            $sidebar_class = '';
            return $sidebar_class;
        }
        if( (is_archive() || is_search() || is_404()) ){
            if( is_active_sidebar( 'sidebar-a' ) ) { 
                $sidebar_class = " ".$class;
            }else{
                $sidebar_class = " sidebar_no";
            }
            return $sidebar_class;
        }
        $sidebar_class = " ".$class;
    }
    return $sidebar_class;
}

function get_ref_url($args, $url, $raw){
    if($raw) return $url;
    if(is_array($args)&& count($args)>0){
        $temp = array();
        foreach($args as $v){
            $temp[$v['key']] = $v['value'];
        }
        return add_query_arg( $temp, $url );
    }else{
        return $url;
    }
}
/**
 * 在启用WP_CACHE的情况下切换主题状态
 */
function dark_mode_js(){
    if( !defined( 'WP_CACHE' ) || !WP_CACHE )
        return; 
    echo '<script type="text/javascript">
    var default_c = "'.io_get_option('theme_mode','').'";
    var night = document.cookie.replace(/(?:(?:^|.*;\s*)io_night_mode\s*\=\s*([^;]*).*$)|^.*$/, "$1"); 
    if(night == "1"){
        document.body.classList.remove("io-black-mode");
        document.body.classList.add(default_c);
    }else if(night == "0"){
        document.body.classList.remove(default_c);
        document.body.classList.add("io-black-mode");
    }
    </script> '; 
}

/**
 * 新窗口访问
 * @param mixed $forced 强制开启
 * @return string
 */
function new_window($forced = false) {
    if (io_get_option('new_window', false) || $forced)
        return 'target="_blank"';
    else
        return '';
}
/**
 * 网址块添加 nofollow
 * noopener external nofollow noopener
 * @param mixed $url 网址
 * @param mixed $details 是否开启详情页
 * @param mixed $is_blank 是否新窗口打开
 * @return string
 */
function nofollow($url, $details = false, $is_blank = false) {
    $ret = '';
    if ($details)
        return $ret;

    if (io_get_option('is_nofollow', false) && !is_go_exclude($url)) {
        $ret .= 'external nofollow';
    }
    if (io_get_option('new_window', false) || $is_blank) {
        $ret .= ' noopener';
    }
    if ($ret != '') {
        $ret = 'rel="' . $ret . '"';
    }
    return $ret;
}
/**
 * 网址块 go 跳转
 * @param string $url 外链地址
 * @param bool $forced 强制转换
 * @return string
 */
function go_to($url, $forced=false){
    if($forced)
        return esc_url(home_url()).'/go/?url='.urlencode(base64_encode($url)) ;
    if(io_get_option('is_go',false)){
        if(is_go_exclude($url))
            return $url;
        else
            return esc_url(home_url()).'/go/?url='.urlencode(base64_encode($url)) ;
    }
    else
        return $url;
}
/**
 * 添加go跳转，排除白名单
 * ******************************************************************************************************
 */
function is_go_exclude($url){ 
    $exclude_links = array();
    $site = esc_url(home_url());
    if (!$site)
        $site = get_option('siteurl');
    $site = str_replace(array("http://", "https://"), '', $site);
    $p = strpos($site, '/');
    if ($p !== FALSE)
        $site = substr($site, 0, $p);/*网站根目录被排除在屏蔽之外，不仅仅是博客网址*/
    $exclude_links[] = "http://" . $site;
    $exclude_links[] = "https://" . $site;
    $exclude_links[] = 'javascript';
    $exclude_links[] = 'mailto';
    $exclude_links[] = 'skype';
    $exclude_links[] = '/';/* 有关相对链接*/
    $exclude_links[] = '#';/*用于内部链接*/

    if(io_get_option('exclude_links',false)){
        $a = explode(PHP_EOL , io_get_option('exclude_links',false));
        $exclude_links = array_merge($exclude_links, $a);
    }
    foreach ($exclude_links as $val){
        if (stripos(trim($url), trim($val)) === 0) {
            return true;
        }
    }
    return false;
}
/**
 * app下载js地址预处理
 * @see io_ajax_get_app_down_btn()
 * @deprecated 4.0 将被弃用
 * @param array $metadata 下载数据json
 * @return string
 */
function io_js_down_goto_pretreatment($metadata){
    $data = array();
    foreach($metadata as $m){
        $data[] = array(
            'app_version' => $m['app_version'],
            'down_url'    => $m['down_url']
        );
    }
    $meta_string = json_encode($data);
    if( io_get_option('is_go',false) && !io_get_option('is_app_down_nogo',false)){
        //"down_btn_url":"https://www.iowen.cn/"
        $regexp = 'down_btn_url":"([^"]+)';
        if(preg_match_all("/$regexp/i", $meta_string, $matches, PREG_SET_ORDER)) { // s 匹配换行
            if( !empty($matches) ) {
                $srcUrl = get_option('siteurl'); 
                for ($i=0; $i < count($matches); $i++)
                { 
                    $url = $matches[$i][1];
                    $url_goto = go_to(stripslashes($matches[$i][1]));
                    $meta_string = str_replace($url,$url_goto,$meta_string);  
                }
            }
        }
    }
    return $meta_string;
}
add_filter( 'query_vars',  'wp_link_pages_all_parameter_queryvars'  );
add_action( 'the_post',  'wp_link_pages_all_the_post'  , 0 );
function wp_link_pages_all_parameter_queryvars( $queryvars ) {
    $queryvars[] = 'view';
    return( $queryvars );
}
function wp_link_pages_all_the_post( $post ) {
    global $pages, $multipage, $wp_query;
    if ( isset( $wp_query->query_vars[ 'view' ] ) && ( 'all' === $wp_query->query_vars[ 'view' ] ) ) {
        $multipage = true;
        $post->post_content = str_replace( '<!--nextpage-->', '', $post->post_content );
        $pages = array( $post->post_content );
    }
}

# 后台检测投稿状态
# --------------------------------------------------------------------
add_action('admin_bar_menu', 'pending_prompt_menu', 2000);
function pending_prompt_menu() {
    if( ! is_admin() ) { return; }
    global $wp_admin_bar;
    $menu_id = 'pending';
    $args = array(
        'post_type' => 'sites',// 文章类型
        'post_status' => 'pending',
    );
    $pending_items = new WP_Query( $args ); 
    if ($pending_items->have_posts()) : 
        $wp_admin_bar->add_menu(array(
            'id' => $menu_id,  
            'title' => '<span class="update-plugins count-2" style="display: inline-block;background-color: #d54e21;color: #fff;font-size: 9px;font-weight: 600;border-radius: 10px;z-index: 26;height: 18px;margin-right: 5px;"><span class="update-count" style="display: block;padding: 0 6px;line-height: 17px;">'.$pending_items->found_posts.'</span></span>个网址待审核', 
            'href' => get_option('siteurl')."/wp-admin/edit.php?post_status=pending&post_type=sites"
        ));     
    endif; 
    wp_reset_postdata();
}

/**
 * 获取url的host
 * @param mixed $url
 * @return mixed
 */
function get_url_host($url)
{
    if (empty($url)) {
        return;
    }

    // 如果 URL 没有协议头，则默认添加 'http://'
    if (!preg_match("/^https?:\/\//", $url)) {
        $url = 'http://' . $url;
    }

    $host = parse_url($url, PHP_URL_HOST);

    return $host ? $host : $url;
}


//用户资料
function io_author_con_datas($user_id = '', $class = 'col-sm-6 p-2', $title_class = 'text-muted', $value_class = '')
{
    if (!$user_id) return;
    $current_id = get_current_user_id();
    $udata = get_userdata($user_id);
    if (!$udata) return;
    $privacy = get_user_meta($user_id, 'privacy', true);

    $datas = array(
        array(
            'title' => '昵称',
            'value' => esc_attr($udata->display_name),
            'show' => false,
        ),
        array(
            'title' => '签名',
            'value' => get_user_desc($user_id),
            'show' => false,
        ), array(
            'title' => '注册时间',
            'value' => get_date_from_gmt($udata->user_registered),
            'spare' => '未知',
            'show' => false,
        ), array(
            'title' => '最后登录',
            'value' => get_user_meta($user_id, 'last_login', true),
            'spare' => '未知',
            'show' => false,
        ), array(
            'title' => '邮箱',
            'value' => esc_attr($udata->user_email),
            'spare' => '未知',
            'show' => true,
        ), array(
            'title' => '个人网站',
            'value' => io_get_url_link($user_id),
            'spare' => '未知',
            'show' => true,
        )
    );
    foreach ($datas as $data) {
        if (!is_super_admin() && $data['show'] && $privacy != 'public' && $current_id != $user_id) {
            if (($privacy == 'just_logged' && !$current_id) || $privacy != 'just_logged') {
                $data['value'] = '用户未公开';
            }
        }
        echo '<div class="' . $class . '">';
        echo '<ul class="list-inline list-author-data">';
        echo '<li class="author-set-left ' . $title_class . '">' . $data['title'] . '</li>';
        echo '<li class="author-set-right ' . $value_class . '">' . ($data['value'] ? $data['value'] : $data['spare']) . '</li>';
        echo '</ul>';
        echo '</div>';
    }
}
function io_get_url_link($user_id, $class = 'focus-color'){
    $user_url =  get_userdata($user_id)->user_url;
    $url_name = get_user_meta($user_id, 'url_name', true) ? get_user_meta($user_id, 'url_name', true) : $user_url;
    $user_url =  go_to($user_url);
    return $user_url ? '<a class="' . $class . '" href="' . esc_url($user_url) . '" target="_blank">' . esc_attr($url_name) . '</a>' : 0;
}

/**
 * 菜单允许的类型
 * 
 * @description:
 * @param 
 * @return array
 */
function get_menu_category_list(){
    $terms = apply_filters('io_category_list', array(
        'favorites',
        'apps',
        'category',
        'books',
        "series",
        "apptag",
        "sitetag",
        "booktag",
        "post_tag"
    ));
    return $terms;
}
# 获取分类下文章数量
# --------------------------------------------------------------------
function io_get_category_count($cat_ID = '',$taxonomy = '') {
    if($cat_ID == '' || $taxonomy == '' ){
        global $wp_query;
        $cat_ID = get_query_var('cat');
        $category = get_category($cat_ID);
    }else{
        $category = get_term( $cat_ID, $taxonomy );
    }
    return $category->count;
}

//add_action('publish_sites', 'io_add_post_data_fields');
//add_action('publish_book', 'io_add_post_data_fields');
//add_action('publish_app', 'io_add_post_data_fields');
//add_action('publish_post', 'io_add_post_data_fields');
//add_action('publish_page', 'io_add_post_data_fields');//wp_insert_post
add_action('save_post_sites', 'io_add_post_data_fields');
add_action('save_post_book', 'io_add_post_data_fields');
add_action('save_post_app', 'io_add_post_data_fields');
add_action('save_post_post', 'io_add_post_data_fields');
add_action('save_post_page', 'io_add_post_data_fields');
function io_add_post_data_fields($post_ID) {
    // 检查是否为新帖子或更新帖子
    if ( wp_is_post_autosave( $post_ID ) || wp_is_post_revision( $post_ID ) ) {
        return;
    }
    add_post_meta($post_ID, 'views', 0, true);
    add_post_meta($post_ID, '_down_count', 0, true);
    add_post_meta($post_ID, '_like_count', 0, true);
    add_post_meta($post_ID, '_star_count', 0, true);
    add_post_meta($post_ID, '_user_purview_level', 'all', true);
    wp_set_post_terms($post_ID, ['public'], 'content_visibility', false);
}

/**
 * 获取用户权限等级
 * @param int $user_id
 * @return int
 */
function io_get_user_level($user_id = -1)
{
    if($user_id == -1){
        global $current_user;
        $user_id = $current_user->ID;
    }
    if (user_can($user_id, 'manage_options')) {
        return 10;
    }
    if (user_can($user_id, 'edit_others_posts')) {
        return 7;
    }
    if (user_can($user_id, 'publish_posts')) {
        return 2;
    }
    if (user_can($user_id, 'edit_posts')) {
        return 1;
    }
    return 0;
}

/**
 * 获取bing图片
 * https://cn.bing.com/th?id=OHR.YoshinoyamaSpring_ZH-CN5545606722_UHD.jpg&pid=hp&w=2880&h=1620&rs=1&c=4&r=0
 * https://cn.bing.com/th?id=OHR.YoshinoyamaSpring_ZH-CN5545606722_1920x1080.jpg&rf=LaDigue_1920x1080.jpg&pid=hp"
 * set_url_scheme
 * 
 * @param  int      $idx 序号
 * @param  string   $size 尺寸 full 1080p uhd 2880x1620 ro 4476x2518
 * 
 * @return string
 */
function get_bing_img_cache($idx=0,$size='uhd'){ 
    $today = strtotime(date("Y-m-d",current_time( 'timestamp' )));// mktime(0,0,0,date('m'),date('d'),date('Y'));
    $yesterday = strtotime(date("Y-m-d",strtotime("-1 day",current_time( 'timestamp' ))));
    if($size=='full'){
        $suffix = '_1920x1080.jpg';
        $url_add = "_1920x1080.jpg";
    }else{
        $suffix = '_UHD.jpg';
        $url_add = "_UHD.jpg&pid=hp&w=2880&h=1620&rs=1&c=4&r=0";
    }
    if(io_get_option('bing_cache',false)){
        $imgDir = wp_upload_dir();
        $bingDir = $imgDir['basedir'].'/bing';
        if (!file_exists($bingDir)) {
            if(!mkdir($bingDir, 0755)){
                wp_die('创建必应图片缓存文件夹失败，请检测文件夹权限！', '创建文件夹失败', array('response'=>403));
            }
        }
        if (!file_exists($bingDir.'/'.$today.$suffix)) {
            $bing_url = 'http:'.bing_img_url($idx).$url_add;
            //$content = file_get_contents($bing_url, false, stream_context_create(array('http' => array('method' => 'GET', 'timeout' => 5))));

            $response = wp_remote_get($bing_url);
            $content = wp_remote_retrieve_body($response);

            file_put_contents($bingDir.'/'.$today.$suffix, $content); // 写入今天的
            $yesterdayimg = $bingDir.'/'.$yesterday.$suffix;
            if (file_exists($yesterdayimg)) {
                unlink($yesterdayimg); //删除昨天的 
            }
            $src = $imgDir['baseurl'].'/bing/'.$today.$suffix;
        } else {
            $src = $imgDir['baseurl'].'/bing/'.$today.$suffix;
        }
    }else{
        $src = bing_img_url($idx).$url_add;
    }
    return $src;
}
function bing_img_url($idx=0,$n=1){
    //$res = file_get_contents('http://cn.bing.com/HPImageArchive.aspx?format=js&idx='.$idx.'&n='.$n, false, stream_context_create(array('http' => array('method' => 'GET', 'timeout' => 5))));
    $response = wp_remote_get('http://cn.bing.com/HPImageArchive.aspx?format=js&idx='.$idx.'&n='.$n);
    $res = wp_remote_retrieve_body($response);
    $bingArr = json_decode($res, true);
    $bing_url = "//cn.bing.com{$bingArr['images'][0]['urlbase']}";
    return $bing_url;
}
/**
 * 获取简介 
 * @param int $count
 * @param string $meta_key
 * @param string $trimmarker
 * @return string
 */
function io_get_excerpt($count = 90, $meta_key = '_seo_desc', $trimmarker = '...', $post = '')
{
    if ('' === $post) {
        global $post;
    }
    $excerpt = get_post_meta($post->ID, $meta_key, true);
    if (empty($excerpt)) {
        if (!empty($post->post_excerpt)) {
            $excerpt = $post->post_excerpt;
        } else {
            $excerpt = $post->post_content;
        }
    }
    return io_strimwidth($excerpt, $count, $trimmarker);
}
/**
 * 截取内容
 * @param string $excerpt
 * @param int $count
 * @param string $trimmarker
 * @return string
 */
function io_strimwidth($excerpt, $count = 90, $trimmarker = '...')
{
    $excerpt = strip_shortcodes($excerpt);       // 移除短代码
    $excerpt = trim(strip_tags($excerpt));       // 移除 HTML 标签
    $excerpt = preg_replace('/(?:&nbsp;|[\r\n\s\xA0]+)/u', ' ', $excerpt); // 替换多余空白为单个空格
    // 判断是否需要添加 $trimmarker
    if (mb_strlen($excerpt) > $count) {
        $excerpt = io_rtrim_mark(mb_substr($excerpt, 0, $count)) . $trimmarker;
    }
    return $excerpt;
}
/**
 * 删除字符串结尾的标点符号
 * @param mixed $text
 * @return array|string|null
 */
function io_rtrim_mark($text) {
    if (!is_string($text)) {
        return false; // 处理非法输入
    }

    $cleanedText = preg_replace('/[\p{P}\p{S}]+$/u', '', $text);

    return $cleanedText === '' ? null : $cleanedText;
}
/**
 * 保存外链图片到本地
 * @param string $src
 * @param int $post_id 文章ID
 * @param string $type 类型 default, favicon, icon, cover, screenshot, preview
 * @return array
 */
function io_save_img($src, $post_id = 0, $type = 'favicon')
{
    // 本地上传路径信息(数组)，用来构造url
    $wp_upload_dir = wp_upload_dir();


    $return_data = array(
        'status' => false,
        'url'    => '',
        'msg'    => '',
    );

    // 先吧$src中的&#038;符号转换回来&
    $src = html_entity_decode($src, ENT_QUOTES);

    if (!isset($src) || !unexclude_image($src)) {
        $return_data['msg'] = '已经是本地图片了';
        return $return_data;
    }


    // 检查src中的url有无扩展名，没有则重新给定文件名
    // 注意：如果url中有扩展名但格式为webp，那么返回的file_info数组为 ['ext' =>'','type' =>'']
    $file_info = wp_check_filetype(basename($src), null);
    if ($file_info['ext'] == false) {
        // 无扩展名和webp格式的图片会被作为无扩展名文件处理 
        $file_name = date('YmdHis-', current_time('timestamp')) . dechex(mt_rand(100000, 999999)) . '.tmp';
    } else {
        // 有扩展名的图片重新给定文件名防止与本地文件名冲突
        //判断是不是后缀是不是 .html，如果是就替换成 .png
        if (in_array($file_info['ext'], ['png', 'jpg', 'jpeg', 'gif', 'webp'])) {
            $file_name = dechex(mt_rand(100000, 999999)) . '-' . basename($src);
        } else {
            $file_name = date('YmdHis-', current_time('timestamp')) . dechex(mt_rand(100000, 999999)) . '.png';
        }
    }

    // 抓取图片, 将图片写入本地文件
    $file_path = $wp_upload_dir['path'] . '/' . $file_name;
    $response  = wp_remote_get($src, [
        'timeout'   => 10,
        'stream'    => true,
        'filename'  => $file_path,
        'sslverify' => false,
    ]);

    if (is_wp_error($response)) {
        // 如果下载失败，删除已创建的文件
        @unlink($file_path);
        $return_data['msg'] = '图片获取失败: ' . $response->get_error_message();
        return $return_data;
    }

    // 确保文件存在且有效
    if (!file_exists($file_path) || filesize($file_path) <= 0) {
        @unlink($file_path);
        $return_data['msg'] = '图片文件无效或下载失败';
        return $return_data;
    }

    $content_type = wp_remote_retrieve_header($response, 'content-type');
    $arr          = explode('/', $content_type);

    if (in_array($arr[1], ['html', 'gif'])) {
        $return_data['msg'] = '图片获取失败，请 10s 后重试，或者手动上传！';
        @unlink($file_path); // 插入失败时删除文件
        return $return_data;
    }

    // 对url地址中没有扩展名或扩展名为webp的图片进行处理
    if (pathinfo($file_path, PATHINFO_EXTENSION) == 'tmp') {
        $file_path = io_handle_ext($file_path, $arr[1], $wp_upload_dir['path'], $file_name, 'tmp');
    } elseif (pathinfo($file_path, PATHINFO_EXTENSION) == 'webp') {
        $file_path = io_handle_ext($file_path, $arr[1], $wp_upload_dir['path'], $file_name, 'webp');
    }

    // 本地src
    $url = $wp_upload_dir['url'] . '/' . basename($file_path);
    // 构造附件post参数并插入媒体库(作为一个post插入到数据库)
    $attachment = io_get_attachment_post(basename($file_path), $url);
    // 生成并更新图片的metadata信息
    $attach_id = wp_insert_attachment($attachment, ltrim($wp_upload_dir['subdir'] . '/' . basename($file_path), '/'), $post_id);
    if (!is_wp_error($attach_id)) {
        $attach_data = wp_generate_attachment_metadata($attach_id, $file_path);
        // 直接调用wordpress函数，将metadata信息写入数据库
        wp_update_attachment_metadata($attach_id, $attach_data);
        update_post_meta($attach_id, '_media_type', $type); 
        $return_data['status'] = true;
        $return_data['url']    = $url;
        $return_data['msg']    = '图片保存成功';
    } else {
        $return_data['msg'] = '插入媒体库失败';
        @unlink($file_path); // 插入失败时删除文件
    }

    return $return_data;
}

/**
 * 图片白名单
 * @param string $url
 * @return bool
 */
function unexclude_image($url){
    if(io_get_option('exclude_image','')){
        $exclude = explode(PHP_EOL , io_get_option('exclude_image',''));
        $exclude[] = $_SERVER['HTTP_HOST']; 
        foreach($exclude as $v){
            if(strpos($url, $v) !== false){
                return false;
            }
        }
        return true;
    }
    return true;
}
/**
 * 处理没有扩展名的图片:转换格式或更改扩展名
 *
 * @param string $file 图片本地绝对路径
 * @param string $type 图片mimetype
 * @param string $file_dir 图片在本地的文件夹
 * @param string $file_name 图片名称
 * @param string $ext 图片扩展名
 * @return string 处理后的本地图片绝对路径
 */
function io_handle_ext($file, $type, $file_dir, $file_name, $ext) {
    switch ($ext) {
        case 'tmp':
            if('x-icon' == $type){
                if (rename($file, str_replace('tmp', 'png', $file))) {
                    return $file_dir . '/' . str_replace('tmp', 'png', $file_name);
                }
            }else{
                if (rename($file, str_replace('tmp', $type, $file))) {
                    if ('webp' == $type) {
                        // 将webp格式的图片转换为jpeg格式
                        return io_image_convert('webp', 'jpeg', $file_dir . '/' . str_replace('tmp', $type, $file_name));
                    }
                    return $file_dir . '/' . str_replace('tmp', $type, $file_name);
                }
            }
        case 'webp':
            if ('webp' == $type) {
                // 将webp格式的图片转换为jpeg格式
                return io_image_convert('webp', 'jpeg', $file);
            } else {
                if (rename($file, str_replace('webp', $type, $file))) {
                    return $file_dir . '/' . str_replace('webp', $type, $file_name);
                }
            }
        default:
            return $file;
    }
}
/**
 * 图片格式转换，暂只能从webp转换为jpeg
 *
 * @param string $from
 * @param string $to
 * @param string $image 图片本地绝对路径
 * @return string 转换后的图片绝对路径
 */
function io_image_convert($from='webp', $to='jpeg', $image='') {
    // 加载 WebP 文件
    $im = imagecreatefromwebp($image);
    // 以 100% 的质量转换成 jpeg 格式并将原webp格式文件删除
    if (imagejpeg($im, str_replace('webp', 'jpeg', $image), 100)) {
        try {
            unlink($image);
        } catch (Exception $e) {
            $error_msg = sprintf('Error removing local file %s: %s', $image,
                $e->getMessage());
            error_log($error_msg);
        }
    }
    imagedestroy($im);

    return str_replace('webp', 'jpeg', $image);
}
/**
 * 构造图片post参数
 *
 * @param string $filename
 * @param string $url
 * @return array 图片post参数数组
 */
function io_get_attachment_post($filename, $url) {
    $file_info  = wp_check_filetype($filename, null);
    return array(
        'guid'           => $url,
        'post_type'      => 'attachement',
        'post_mime_type' => $file_info['type'],
        'post_title'     => preg_replace('/\.[^.]+$/', '', $filename),
        'post_content'   => '',
        'post_status'    => 'inherit'
    );
} 

function io_head_favicon(){
    if (io_get_option('favicon','')) {
        echo "<link rel='shortcut icon' href='" . io_get_option('favicon','') . "'>";
    } else {
        echo "<link rel='shortcut icon' href='" . home_url('/favicon.ico') . "'>";
    }
    if (io_get_option('apple_icon','')) {
        echo "<link rel='apple-touch-icon' href='" . io_get_option('apple_icon','') . "'>";
    }
}
add_action('admin_head', 'io_head_favicon');


/**
 * 输出lazy图片
 * 
 * @param  string   $src       图片地址
 * @param  string   $alt       名称
 * @param  int|string|array $size      大小
 * @param  string   $class     class
 * @param  string   $def_src   默认图片
 * @param  string   $error_src 触发错误输出error图片
 * @param  string   $attr      其他属性
 * @return string 
 */
function get_lazy_img($src, $alt, $size = 'auto', $class = '', $def_src = '', $error_src = '', $attr = '') {
    if ($def_src == '') {
        $def_src = get_theme_file_uri('/assets/images/t1.svg');
    }
    $onerror = '';
    if ($error_src) {
        $onerror = $error_src ?: 'onerror="javascript:this.src=\'' . $def_src . '\'"';
    }

    if (is_array($size)) {
        $_size = 'height="' . $size[1] . '" width="' . $size[0] . '"';
    } else {
        $_size = 'height="' . $size . '" width="' . $size . '"';
    }
    if (io_get_option('lazyload', false)) {
        return '<img class="' . $class . ' lazy unfancybox" src="' . $def_src . '" data-src="' . $src . '" ' . $onerror . ' ' . $_size . ' ' . $attr . ' alt="' . $alt . '">';
    } else {
        return '<img class="' . $class . ' unfancybox" src="' . $src . '" ' . $onerror . ' ' . $_size . ' ' . $attr . ' alt="' . $alt . '">';
    }
}
/**
 * 输出lazy图片BG
 * 
 * @param  string $src   图片地址
 * @param  string $style 其他样式
 * @return string 
 */
function get_lazy_img_bg($src, $style=''){ 
    if (io_get_option('lazyload',false)) {
        return 'data-bg="'.$src.'"'.($style==''?'':' style="'.$style.'"');
    }else{
        return 'style="background-image: url('.$src.')'.';'.$style.'"';
    }
}
if(!function_exists('get_columns')):
/**
 * 首页卡片一行个数样式
 * 
 * @param  string $type    文章类型
 * @param  string $cat_id  分类id
 * @param  bool   $display
 * @param  bool   $is_sidebar  有侧边栏，自动减1
 * @param  string $mode    特殊模块样式
 * @return string|null
 */
function get_columns($type = 'sites', $cat_id = '', $display = true, $is_sidebar = false, $mode = '') {
    $default = array(
        'sm'  => 2,
        'md'  => 2,
        'lg'  => 3,
        'xl'  => 5,
        'xxl' => 6
    );
    if ('mini' === $mode) {
        $class = " col-2a col-sm-2a col-md-4a col-lg-5a col-xl-6a col-xxl-10a ";
    } else {
        $card_mode = get_term_meta($cat_id, 'card_mode', true);
        if ('' !== $cat_id && $card_mode && 'null' !== $card_mode && 'none' !== $card_mode) {
            $columns = get_term_meta($cat_id, 'columns', true);
        } else {
            $columns = io_get_option($type . '_columns', $default);
        }
        $columns = wp_parse_args($columns, $default);
        
        if ($is_sidebar) {
            $columns['xxl'] -= 1;
            $columns['xl'] -= 1;
            $columns['lg'] -= 1;
        }
        if ($mode == 'max') {
            $columns['sm'] = 1;
        }
        $class = " col-{$columns['sm']}a col-sm-{$columns['sm']}a col-md-{$columns['md']}a col-lg-{$columns['lg']}a col-xl-{$columns['xl']}a col-xxl-{$columns['xxl']}a ";
    }

    if ($display)
        echo $class;
    else
        return $class;
}
endif;
# 时间格式转化
# --------------------------------------------------------------------
function io_time_ago( $ptime ) {
    if(!io_get_option('post_data_format', true)){
        return io_date_time($ptime, false);
    }
    if (!is_numeric($ptime)) {
        $ptime = strtotime($ptime);
    }
    $etime = current_time( 'timestamp' ) - $ptime;
    if($etime < 1) return __('刚刚', 'i_theme');
    $interval = array (
        12 * 30 * 24 * 60 * 60  =>  __('年前', 'i_theme'),
        30 * 24 * 60 * 60       =>  __('个月前', 'i_theme'),
        7  * 24 * 60 * 60       =>  __('周前', 'i_theme'),
        24 * 60 * 60            =>  __('天前', 'i_theme'),
        60 * 60                 =>  __('小时前', 'i_theme'),
        60                      =>  __('分钟前', 'i_theme'),
        1                       =>  __('秒前', 'i_theme')
    );
    foreach ($interval as $secs => $str) {
        $d = $etime / $secs;
        if ($d >= 1) {
            $r = round($d);
            return $r . $str;
        }
    };
}
/**
 * 根据WP设置显示日期时间。
 *
 * @param  integer|string   $datetime   DateTime或UNIX时间戳。
 * @param  boolean          $time       如果要显示时间部分，则为True。
 * @return string                       格式化的日期时间。
 * --------------------------------------------------------------------------
 */
function io_date_time( $datetime, $time = true ) {
    if( ! is_numeric($datetime) ) {
        $datetime = strtotime($datetime);
    }
    $date_time_format = get_option( 'date_format' );
    if( $time ) {
        $date_time_format .= ' ';
        $date_time_format .= get_option( 'time_format' );
    }
    return date( $date_time_format, $datetime );
}
# 评论高亮作者
# --------------------------------------------------------------------
function is_master($email = '') {
    if( empty($email) ) return;
    $handsome = array( '1' => ' ', );
    $adminEmail = get_option( 'admin_email' );
    if( $email == $adminEmail ||  in_array( $email, $handsome )  )
    return '<span class="is-author"  data-toggle="tooltip" data-placement="right" title="'.__('博主','i_theme').'"><i class="iconfont icon-user icon-fw"></i></span>';
}
/**
 * 首页标签图标,菜单图标
 * @description: 
 * @param string $terms   分类法
 * @param array $mid      分类对象
 * @param string $default 默认图标
 * @return string
 */
function get_tag_ico($terms, $mid, $default = 'iconfont icon-tag')
{
    if ($terms == "favorites") {
        $icon = 'iconfont icon-tag';
    } elseif ($terms == "apps") {
        $icon = 'iconfont icon-app';
    } elseif ($terms == "books") {
        $icon = 'iconfont icon-book';
    } elseif ($terms == "category") {
        $icon = 'iconfont icon-publish';
    } else {
        $icon = $default;
    }
    if (!is_array($mid))
        return $icon;

    if (isset($mid['ID']) || (isset($mid['classes']) && is_array($mid['classes']))) {
        if (!$icon = get_post_meta($mid['ID'], 'menu_ico', true)) {
            $classes = preg_grep('/^(fa[b|s]?|io)(-\S+)?$/i', $mid['classes']);
            if (!empty($classes)) {
                $icon = implode(" ", $mid['classes']);
            } else {
                $icon = $default;
            }
        }
    }

    return $icon;
}
# 评论头衔
# --------------------------------------------------------------------
function site_rank( $comment_author_email, $user_id ) {
    $adminEmail = get_option( 'admin_email' );
    if($comment_author_email ==$adminEmail) 
        return;

    $rank = io_get_user_cap_string($user_id);
    return $rank = '<span class="rank" title="'.__('头衔：','i_theme') . $rank .'">'. $rank .'</span>';

    //$v1 = 'Vip1';
    //$v2 = 'Vip2';
    //$v3 = 'Vip3';
    //$v4 = 'Vip4';
    //$v5 = 'Vip5';
    //$v6 = 'Vip6'; 
    //global $wpdb;
    //$num = count( $wpdb->get_results( "SELECT comment_ID as author_count FROM $wpdb->comments WHERE comment_author_email = '$comment_author_email' " ) );
    //
    //if ( $num > 0 && $num < 6 ) {
    //    $rank = $v1;
    //}
    //elseif ( $num > 5 && $num < 11 ) {
    //    $rank = $v2;
    //}
    //elseif ( $num > 10 && $num < 16 ) {
    //    $rank = $v3;
    //}
    //elseif ($num > 15 && $num < 21) {
    //    $rank = $v4;
    //}
    //elseif ( $num > 20 && $num < 26 ) {
    //    $rank = $v5;
    //}
    //elseif ( $num > 25 ) {
    //    $rank = $v6;
    //}

    //if( $comment_author_email != $adminEmail )
    //    return $rank = '<span class="rank" data-toggle="tooltip" data-placement="right" title="'.__('头衔：','i_theme') . $rank .'，'.__('累计评论：','i_theme') . $num .'">'. $rank .'</span>';
}
# 评论格式
# --------------------------------------------------------------------
if(!function_exists('io_comment_default_format')){
    function io_comment_default_format($comment, $args, $depth){
        $GLOBALS['comment'] = $comment;
        ?>
        <li <?php comment_class('comment'); ?> id="li-comment-<?php comment_ID() ?>">
            <div id="comment-<?php comment_ID(); ?>" class="comment_body d-flex flex-fill">    
                <div class="avatar-img profile mr-2 mr-md-3"> 
                    <?php 
                    echo  get_avatar( $comment, 96, '', get_comment_author() );
                    ?>
                </div>                    
                <section class="comment-text d-flex flex-fill flex-column">
                    <div class="comment-info d-flex align-items-center mb-1">
                        <div class="comment-author text-sm w-100"><?php comment_author_link(); ?>
                        <?php echo is_master( $comment->comment_author_email ); echo site_rank( $comment->comment_author_email, $comment->user_id ); ?>
                        </div>                                        
                    </div>
                    <div class="comment-content d-inline-block text-sm">
                        <?php comment_text(); ?> 
                        <?php
                        if ($comment->comment_approved == '0'){
                            echo '<span class="cl-approved">('.__('您的评论需要审核后才能显示！','i_theme').')</span><br>';
                        } 
                        ?>
                    </div>
                    <div class="d-flex flex-fill text-xs text-muted pt-2">
                        <div class="comment-meta">
                            <?php
                            echo '<span class="info mr-2"><i class="iconfont icon-time mr-1"></i><time itemprop="datePublished" datetime="' . get_comment_date('c') . '">' . io_time_ago(get_comment_date('Y-m-d G:i:s')) . '</time></span>';
                            if (io_get_option('ip_location', false, 'comment')) {
                                echo '<span class="info-location mr-2"><i class="iconfont icon-location mr-1"></i>'. io_get_ip_location(get_comment_author_ip()) .'</span>';
                            }
                            if($comment->comment_parent){
                                echo '<span class="info-at badge">@ <a class="smooth" href="#comment-'. $comment->comment_parent .'">'.get_comment_author( $comment->comment_parent ) .'</a></span>';
                            }
                            ?>
                        </div>
                        <div class="flex-fill"></div>
                        <?php comment_reply_link(array_merge($args, array('depth' => $depth, 'max_depth' => $args['max_depth']))); ?>
                    </div>
                </section>
            </div>
        <?php
    }
}

/**
 * 禁止冒充管理员评论
 * ******************************************************************************************************
 */
function usercheck($incoming_comment) {
    $isSpam = false;
    $administrator = io_get_option( 'io_administrator', array( 'admin_name'=>'', 'admin_email'=>'') );
    if (trim($incoming_comment['comment_author']) == $administrator['admin_name'] )
        $isSpam = true;
    if (trim($incoming_comment['comment_author_email']) == $administrator['admin_email'] )
        $isSpam = true;
    if(!$isSpam)
        return $incoming_comment;
    io_error('{"status":3,"msg":"'.__('请勿冒充管理员发表评论！' , 'i_theme' ).'"}', true);
}
if (!is_user_logged_in()) { add_filter('preprocess_comment', 'usercheck'); }
/**
 * 过滤纯英文、日文和一些其他内容
 * ******************************************************************************************************
 */
function io_refused_spam_comments($comment_data) {
    $pattern = '/[一-龥]/u';
    $jpattern = '/[ぁ-ん]+|[ァ-ヴ]+/u';
    $links = '/http:\/\/|https:\/\/|www\./u';
    $commentset = io_get_option('io_comment_set',array('no_url' => true, 'no_chinese' => false,));
    if ($commentset['no_url'] && (preg_match($links, $comment_data['comment_author']) || preg_match($links, $comment_data['comment_content']))) {
        io_error('{"status":3,"msg":"'.__('别啊，昵称和评论里面添加链接会怀孕的哟！！' , 'i_theme').'"}', true);
    }
    if($commentset['no_chinese']){
        if (!preg_match($pattern, $comment_data['comment_content'])) {
            io_error('{"status":3,"msg":"'.__('评论必须含中文！' , 'i_theme' ).'"}', true);
        }
        if (preg_match($jpattern, $comment_data['comment_content'])) {
            io_error('{"status":3,"msg":"'.__('评论必须含中文！' , 'i_theme' ).'"}', true);
        }
    }
    if (wp_check_comment_disallowed_list($comment_data['comment_author'], $comment_data['comment_author_email'], $comment_data['comment_author_url'], $comment_data['comment_content'], isset($comment_data['comment_author_IP']), isset($comment_data['comment_agent']))) {
        header("Content-type: text/html; charset=utf-8");
        io_error('{"status":3,"msg":"'.sprintf(__('不好意思，您的评论违反了%s博客评论规则' , 'i_theme'), get_option('blogname')).'"}', true);
    }
    return ($comment_data);
}
add_filter('preprocess_comment', 'io_refused_spam_comments');
/**
 * 禁止评论自动超链接
 * ******************************************************************************************************
 */
remove_filter('comment_text', 'make_clickable', 9);   
/**
 * 屏蔽长链接转垃圾评论
 * ******************************************************************************************************
 */
function lang_url_spamcheck($approved, $commentdata) {
    return (strlen($commentdata['comment_author_url']) > 50) ? 'spam' : $approved;
}
add_filter('pre_comment_approved', 'lang_url_spamcheck', 99, 2);


/**
 * 首页置顶靠前
 * 
 * @param WP_Query $myposts
 * @param string $post_type
 * @param string $taxonomy
 * @param string $terms
 * @return WP_Query
 */
function sticky_posts_to_top($myposts, $post_type, $taxonomy, $terms)
{
    $sticky_posts = get_option('sticky_posts');
    if (is_array($sticky_posts) && !empty($sticky_posts)) {
        $num_posts     = count($myposts->posts);
        $sticky_offset = 0;
        // 循环文章，将置顶文章移到最前面。
        for ($i = 0; $i < $num_posts; $i++) {
            if (in_array($myposts->posts[$i]->ID, $sticky_posts, true)) {
                $sticky_post = $myposts->posts[$i];
                // 从当前位置移除置顶文章。
                array_splice($myposts->posts, $i, 1);
                // 移到前面，在其他置顶文章之后。
                array_splice($myposts->posts, $sticky_offset, 0, array($sticky_post));
                // 增加置顶文章偏移量。下一个置顶文章将被放置在此偏移处。
                $sticky_offset++;
                // 从置顶文章数组中删除文章。
                $offset = array_search($sticky_post->ID, $sticky_posts, true);
                unset($sticky_posts[$offset]);
            }
        }
    }
    // 获取查询结果中没有的置顶文章
    if (!empty($sticky_posts)) {
        $stickies = get_posts(array(
            'post__in'    => $sticky_posts,
            'post_status' => 'publish',
            'post_type'   => $post_type,
            'nopaging'    => true,
            'tax_query'   => array(
                array(
                    'taxonomy' => $taxonomy,
                    'field'    => 'id',
                    'terms'    => $terms,
                )
            ),
        ));
        foreach ($stickies as $sticky_post) {
            array_splice($myposts->posts, $sticky_offset, 0, array($sticky_post));
            $sticky_offset++;
        }
    }

    return $myposts;
}

# 编辑菜单后删除相应菜单缓存
# --------------------------------------------------------------------
add_action( 'wp_update_nav_menu', 'io_delete_menu_cache', 10, 1 );
function io_delete_menu_cache($menu_id) {  
    //if (wp_using_ext_object_cache()){
    //    //$_menu = wp_get_nav_menu_object( $menu_id );
    //    wp_cache_delete('io_menu_list_'.$menu_id);
    //    wp_cache_delete('io_menu_list_main_'.$menu_id);
    //}
    //delete_transient('io_menu_list_'.$menu_id);
    //delete_transient('io_menu_list_main_'.$menu_id);
}
# 主题设置项变更排序相关选项后删除缓存
# --------------------------------------------------------------------
add_action( 'iocf_io_get_option_saved', 'io_delete_home_post_cache', 10, 2 );
function io_delete_home_post_cache($data,$_this) {  
    if( io_get_option('user_center')                            != $data['user_center']                             || 
        io_get_option('rewrites_types')                         != $data['rewrites_types']                          || 
        io_get_option('rewrites_end')                           != $data['rewrites_end']                            || 
        io_get_option('sites_rewrite')['post']                  != $data['sites_rewrite']['post']                   || 
        io_get_option('sites_rewrite')['taxonomy']              != $data['sites_rewrite']['taxonomy']               || 
        io_get_option('sites_rewrite')['tag']                   != $data['sites_rewrite']['tag']                    || 
        io_get_option('app_rewrite')['post']                    != $data['app_rewrite']['post']                     || 
        io_get_option('app_rewrite')['taxonomy']                != $data['app_rewrite']['taxonomy']                 || 
        io_get_option('app_rewrite')['tag']                     != $data['app_rewrite']['tag']                      || 
        io_get_option('book_rewrite')['post']                   != $data['book_rewrite']['post']                    || 
        io_get_option('book_rewrite')['taxonomy']               != $data['book_rewrite']['taxonomy']                || 
        io_get_option('book_rewrite')['tag']                    != $data['book_rewrite']['tag']                     || 
        io_get_option('ioc_category')                           != $data['ioc_category']                            || 
        io_get_option('rewrites_category_types')['rewrites']    != $data['rewrites_category_types']['rewrites']     || 
        io_get_option('rewrites_category_types')['types']       != $data['rewrites_category_types']['types'])
    {
        //wp_safe_redirect( admin_url( 'options-permalink.php?settings-updated=true' ) );
        io_refresh_rewrite();
        flush_rewrite_rules();
    }
    if (wp_using_ext_object_cache()){
        //添加判断条件
        if( io_get_option('home_sort')['favorites'] != $data['home_sort']['favorites']  || 
            io_get_option('home_sort')['apps']      != $data['home_sort']['apps']       || 
            io_get_option('home_sort')['books']     != $data['home_sort']['books']      || 
            io_get_option('home_sort')['category']  != $data['home_sort']['category']   || 
            io_get_option('home_sort_sort')['favorites'] != $data['home_sort_sort']['favorites'] || 
            io_get_option('home_sort_sort')['apps']      != $data['home_sort_sort']['apps']      || 
            io_get_option('home_sort_sort')['books']     != $data['home_sort_sort']['books']     || 
            io_get_option('home_sort_sort')['category']  != $data['home_sort_sort']['category']  || 
            io_get_option('show_sticky')            != $data['show_sticky']             || 
            io_get_option('category_sticky')        != $data['category_sticky']         || 
            io_get_option('sites_sortable')         != $data['sites_sortable'])
        {
            wp_cache_flush();
        }else{
            wp_cache_delete('io_options_cache', 'options');
        }
    }
}
/* 
 * 编辑文章排序后删除对应缓存 id
 * --------------------------------------------------------------------
 */
function io_edit_post_delete_home_cache( $terms, $taxonomy='favorites' )
{
    if (wp_using_ext_object_cache()){
        $site_n= io_get_option('card_n',16,$taxonomy);
        $ajax = 'ajax-url';

        $sorts = io_get_sort_data();
        foreach ($sorts as $sort) {
            $cache_key      = 'io_home_posts_' . md5($terms . '_' . $taxonomy . '_' . $site_n . '_' . '_' . $sort);
            $cache_ajax_key = 'io_home_posts_' . md5($terms . '_' . $taxonomy . '_' . $site_n . '_' . $ajax . '_' . $sort);
            wp_cache_delete($cache_key, 'home-card');
            wp_cache_delete($cache_ajax_key, 'home-card');
        }
    }
} 
add_action( "iocf_sites_post_meta_save_before", 'io_meta_saved_delete_home_cache_article',10,2 );
function io_meta_saved_delete_home_cache_article( $data, $post_id )
{
    if (wp_using_ext_object_cache()){
        //添加判断条件
        if( get_post_meta($post_id, '_sites_order', true) != $data['_sites_order']){
            // 删除缓存
            $terms = get_the_terms($post_id,'favorites');
            foreach($terms as $term){
                io_edit_post_delete_home_cache($term->term_id,'favorites');
            } 
        }
    }
}
//删除分类后删除对应缓存
add_action("delete_term", "io_delete_term_delete_cache",10,5);
function io_delete_term_delete_cache($term, $tt_id, $taxonomy, $deleted_term, $object_ids){
    io_edit_post_delete_home_cache($tt_id, $taxonomy);
}

# 替换用户链接
# --------------------------------------------------------------------
add_filter('author_link', 'author_link', 10, 2);
function author_link( $link, $author_id) {
    global $wp_rewrite;
    $author_id = (int) $author_id;
    $link = $wp_rewrite->get_author_permastruct();
    if ( empty($link) ) {
        $link = home_url() . '/?author=' . $author_id;
    } else {
        $link = str_replace('%author%', $author_id, $link);
        $link = home_url() . user_trailingslashit($link);
    }
    return $link;
}
add_filter('request', 'author_link_request');
function author_link_request( $query_vars ) {
    if ( array_key_exists( 'author_name', $query_vars ) ) {
        global $wpdb;
        $author_id=$query_vars['author_name'];
        if ( $author_id ) {
            $query_vars['author'] = $author_id;
            unset( $query_vars['author_name'] );    
        }
    }
    return $query_vars;
}
# 屏蔽用户名称类
# --------------------------------------------------------------------
add_filter('comment_class','remove_comment_body_author_class');
add_filter('body_class','remove_comment_body_author_class');
function remove_comment_body_author_class( $classes ) {
    foreach( $classes as $key => $class ) {
    if(strstr($class, "comment-author-")||strstr($class, "author-")) {
            unset( $classes[$key] );
        }
    }
    return $classes;
}
function chack_name($filename){
    $filename     = remove_accents( $filename );
    $special_chars = array( '?', '[', ']', '/', '\\', '=', '<', '>', ':', ';', ',', "'", '"', '$', '#', '*', '(', ')', '~', '`', '!', '{', '}', '%', '+', '’', '«', '»', '”', '“', chr( 0 ) );
    static $utf8_pcre = null;
    if ( ! isset( $utf8_pcre ) ) {
        $utf8_pcre = @preg_match( '/^./u', 'a' );
    }
    if ( !seems_utf8( $filename ) ) {
        $_ext     = pathinfo( $filename, PATHINFO_EXTENSION );
        $_name    = pathinfo( $filename, PATHINFO_FILENAME );
        $filename = sanitize_title_with_dashes( $_name ) . '.' . $_ext;
    }
    if ( $utf8_pcre ) {
        $filename = preg_replace( "#\x{00a0}#siu", ' ', $filename );
    }
    $filename = str_replace( $special_chars, '', $filename );
    $filename = str_replace( array( '%20', '+' ), '', $filename );
    $filename = preg_replace( '/[\r\n\t -]+/', '', $filename );
    return esc_attr($filename);
}
function loading_type($id=0){
    if($id!=0){
        $type = $id;
    }else{
        $type = io_get_option('loading_type','1')?:'rand';
        if($type == 'rand')
            $type = wp_rand(1,7);
    }
    include( get_theme_file_path("/templates/loadfx/loading-{$type}.php") );
}
# 禁止谷歌字体
# --------------------------------------------------------------------
add_action( 'init', 'remove_open_sans' );
function remove_open_sans() {
    wp_deregister_style( 'open-sans' );
    wp_register_style( 'open-sans', false );
    wp_enqueue_style('open-sans','');
}
# 字体增加
# --------------------------------------------------------------------
add_filter('tiny_mce_before_init', 'custum_fontfamily');
function custum_fontfamily($initArray){  
    $initArray['font_formats'] = "微软雅黑='微软雅黑';宋体='宋体';黑体='黑体';仿宋='仿宋';楷体='楷体';隶书='隶书';幼圆='幼圆';";  
    return $initArray;  
} 
/**
 * 轮播HTML
 * ******************************************************************************************************
 */
function io_get_swiper($swiper_data, $class = '')
{
    $output = '<div class="swiper swiper-post-module br-xl ' . $class . '">';
    $output .= '<div class="swiper-wrapper">';
    foreach ($swiper_data as $v) {
        $bg_img = get_lazy_img_bg($v['img']);
        $rel    = $v['is_ad'] ? 'rel="noopener"' : 'rel="external noopener nofollow"';
        $terget = new_window($v['is_ad']);

        $output .= '<div class="swiper-slide media media-21x9">';
        $output .= '<a class="media-content media-title-bg" href="' . $v['url'] . '" ' . $terget . ' ' . $rel . ' ' . $bg_img . '><span class="carousel-caption d-none d-md-block">' . $v['title'] . '</span></a>';
        $output .= '</div>';
    }
    $output .= '</div>';
    $output .= '<div class="swiper-pagination carousel-blog"></div>';
    $output .= ' </div>';
    return $output;
}
/**
 * 关键词加链接
 * ******************************************************************************************************
 */
if (io_get_option('tag_c', false, 'switcher')) {
    add_filter('the_content', 'io_auto_tag_link', 8);
}
/**
 * 自动为文章内容中的关键词添加链接
 * @param mixed $content 文章内容
 * @return mixed 文章内容
 */
function io_auto_tag_link($content)
{
    $match_num_from = 1; //配置：一个关键字少于多少不替换  
    $match_num_to   = io_get_option('tag_c', 1, 'chain_n'); //配置：一个关键字最多替换，建议不大于2  
    $case           = false ? "i" : ""; //配置：忽略大小写 true是开，false是关  

    $all_tags = io_get_content_tags();

    if (empty($all_tags)) {
        return $content;
    }

    foreach ($all_tags as $tag) {
        if (isset($tag['tag']) && empty($tag['tag'])) {
            continue;
        }

        $link         = isset($tag['url']) ? $tag['url'] : get_tag_link($tag['term_id']);
        $keyword      = isset($tag['tag']) ? $tag['tag'] : $tag['name'];
        $cleankeyword = stripslashes($keyword);
        $describe     = isset($tag['describe']) ? $tag['describe'] : str_replace('%s', addcslashes($cleankeyword, '$'), __('查看与 %s 相关的文章', 'i_theme'));
        $limit        = rand($match_num_from, $match_num_to);
        $content      = tag_to_url($link, $cleankeyword, $describe, $limit, $content, $case);
    }
    return $content;
}

/**
 * 获取文章内容标签
 * @return array 标签数组
 */
function io_get_content_tags()
{
    global $post;
    $option = io_get_option('tag_c', array());

    $random_terms = array();
    $terms_array  = array();

    if ($option['auto']) {
        // 获取随机标签
        $taxonomys    = array('post_tag', 'apptag', 'sitetag', 'booktag');
        $random_terms = get_random_terms($taxonomys, 10, ARRAY_A);

        // 获取当前文章的标签
        $post_type = get_post_type($post);
        $taxonomy  = posts_to_tag($post_type);
        $post_tags = get_the_terms($post, $taxonomy);
        if (!empty($post_tags) && !is_wp_error($post_tags)) {
            foreach ($post_tags as $term) {
                $terms_array[] = array(
                    'term_id'  => $term->term_id,
                    'name'     => $term->name,
                    'slug'     => $term->slug,
                    'taxonomy' => $term->taxonomy,
                    'count'    => $term->count,
                );
            }
        }
    }

    // 获取自定义关键词
    $custom_tags = (array) (isset($option['tags']) ? $option['tags'] : array());

    // 合并随机标签和当前文章标签
    $all_tags = array_merge($custom_tags, $random_terms, $terms_array);

    // 返回标签
    return $all_tags;
}

/**
 * 关键词加链接
 * @param mixed $link 链接
 * @param mixed $cleankeyword 关键词
 * @param mixed $describe 描述
 * @param mixed $limit 限制
 * @param mixed $content 内容
 * @param mixed $case 大小写
 * @param mixed $ex_word 额外单词
 * @return array|string
 */
function tag_to_url($link, $cleankeyword, $describe, $limit, $content, $case, $ex_word = '')
{
    $url          = "<a class=\"external\" href=\"$link\" title=\"" . $describe . "\"";
    $url .= ' target="_blank"';
    $url .= ">" . addcslashes($cleankeyword, '$') . "</a>";
    $ex_word      = preg_quote($cleankeyword, '\'');
    $content      = preg_replace('|(<a[^>]+>)(.*)<pre.*?>(' . $ex_word . ')(.*)<\/pre>(</a[^>]*>)|U' . $case, '$1$2%&&&&&%$4$5', $content);//a标签，免混淆处理  
    $content      = preg_replace('|(<img)(.*?)(' . $ex_word . ')(.*?)(>)|U' . $case, '$1$2%&&&&&%$4$5', $content);//img标签
    $content      = preg_replace('|(\[)(.*?)(' . $ex_word . ')(.*?)(\])|U' . $case, '$1$2%&&&&&%$4$5', $content);//短代码标签
    $cleankeyword = preg_quote($cleankeyword, '\'');
    $regEx        = '\'(?!((<.*?)|(<a.*?)))(' . $cleankeyword . ')(?!(([^<>]*?)>)|([^>]*?</a>))\'s' . $case;
    $content      = preg_replace($regEx, $url, $content, $limit);
    $content      = str_replace('%&&&&&%', stripslashes($ex_word), $content);//免混淆还原处理  
    return $content;
}

# 移除 WordPress 文章标题前的“私密/密码保护”提示文字
# --------------------------------------------------------------------
add_filter('private_title_format', 'remove_title_prefix');//私密
add_filter('protected_title_format', 'remove_title_prefix');//密码保护
function remove_title_prefix($content) {
    return '%s';
}
# FancyBox图片灯箱
# --------------------------------------------------------------------
//add_filter('the_content', 'io_fancybox');
function io_fancybox($content){ 
    global $post;
    $title = $post->post_title;
    $pattern = array("/<a(.*?)href=('|\")([^>]*).(bmp|gif|jpeg|jpg|png|swf)('|\")(.*?)>(.*?)<\/a>/i","/<img(.*?)src=('|\")([^>]*).(bmp|gif|jpeg|jpg|png|swf)('|\")(.*?)>/i");
    $replacement = array('<a$1href=$2$3.$4$5 data-fancybox="images"$6 data-caption="'.$title.'">$7</a>','<a$1href=$2$3.$4$5 data-fancybox="images" data-caption="'.$title.'"><img$1src=$2$3.$4$5$6></a>');
    $content = preg_replace($pattern, $replacement, $content);
    return $content;
}
/**
 * 去掉正文图片外围标签p、自动添加 a 标签和 data-original
 * ******************************************************************************************************
 */
function lazyload_fancybox($content)
{
    global $post;
    //判断是否为文章页或者页面
    if (!is_single() || is_feed() || is_robots())
        return $content;

    $title       = isset($post->post_title) ? esc_attr($post->post_title) : '';
    $loadimg_url = get_template_directory_uri() . '/assets/images/t.png';

    // 去掉 p 标签
    $content = preg_replace('/<p>\s*(<a .*>)?\s*(<img .* \/>)\s*(<\/a>)?\s*<\/p>/iU', '$1$2$3', $content);

    // 为图片链接添加 fancybox
    $pattern     = '/<a([^>]*)href=(\'|")([^\'"]+\.(?:bmp|gif|jpeg|jpg|png|swf)(?:\?\S*)?)(\\2)([^>]*)>(.*?)<\/a>/i';
    $replacement = '<a$1href=$2$3$4 data-fancybox="images" data-caption="' . $title . '"$5>$6</a>';
    $content     = preg_replace($pattern, $replacement, $content);

    // 图片懒加载并保留 alt（空 alt 也补全）
    $imgpattern = '/<img([^>]*?)src=(\'|")([^\'"]+)(\\2)([^>]*)>/i';
    $content    = preg_replace_callback($imgpattern, function ($matches) use ($title, $loadimg_url) {
        $src   = $matches[3];
        $attrs = trim($matches[1] . ' ' . $matches[5]); // src 前后的属性合并

        // 检查 alt 是否缺失或为空
        if (!preg_match('/\salt\s*=\s*["\'].*?["\']/i', $attrs)) {
            // 没有 alt，添加
            $attrs .= ' alt="' . esc_attr($title) . '"';
        } else {
            // 有 alt 但为空，替换
            $attrs = preg_replace(
                '/\salt\s*=\s*["\']\s*["\']/i',
                ' alt="' . esc_attr($title) . '"',
                $attrs
            );
        }

        // 懒加载逻辑
        if (io_get_option('lazyload', false)) {
            return '<img data-src="' . $src . '" src="' . $loadimg_url . '" ' . $attrs . '>';
        } else {
            return '<img src="' . $src . '" ' . $attrs . '>';
        }
    }, $content);

    return $content;
}
add_filter ('the_content', 'lazyload_fancybox',10);

# 正文外链跳转和自动nofollow
# --------------------------------------------------------------------
add_filter( 'the_content', 'ioc_seo_wl',10);
function ioc_seo_wl( $content ) {
    //$regexp = "<a\s[^>]*href=(\"??)([^\" >]*?)\\1[^>]*>";
    $regexp = "<a(.*?)href=('|\")([^>]*?)('|\")(.*?)>(.*?)<\/a>";
    if(preg_match_all("/$regexp/i", $content, $matches, PREG_SET_ORDER)) { // s 匹配换行
        if( !empty($matches) ) {
            $srcUrl = get_option('siteurl')?:home_url(); 
            for ($i=0; $i < count($matches); $i++)
            { 
                $url = $matches[$i][3];
                if ( "#" !==substr($url, 0, 1) && false === strpos($url,$srcUrl) ) {
                    $_url=$matches[$i][3];
                    if(io_get_option('is_go',false) && is_go_exclude($_url)===false && !preg_match('/\.(jpg|jpeg|png|ico|bmp|gif|tiff)$/i',$_url) && !preg_match('/(ed2k|thunder|Flashget|flashget|qqdl):\/\//i',$_url)) {
                        $_url= go_to($_url);
                    }
                    $tag = '<a'.$matches[$i][1].'href='.$matches[$i][2].$_url.$matches[$i][4].$matches[$i][5].'>';
                    $tag2 = '<a'.$matches[$i][1].'href='.$matches[$i][2].$url.$matches[$i][4].$matches[$i][5].'>';
                    $noFollow = '';
                    $pattern = '/target\s*=\s*"\s*_blank\s*"/';
                    preg_match($pattern, $tag2, $match, PREG_OFFSET_CAPTURE);
                    if( count($match) < 1 ){
                        $noFollow .= ' target="_blank" ';
                    }
                    $pattern = '/rel\s*=\s*"\s*[n|d]ofollow\s*"/';
                    preg_match($pattern, $tag2, $match, PREG_OFFSET_CAPTURE);
                    if( count($match) < 1 ){
                        $noFollow .= ' rel="nofollow noopener" ';
                    }
                    if(strpos($matches[$i][6],'<img') === false){
                        $pattern = '/class\s*=\s*"\s*(.*?)\s*"/';  //追加class的方法-------------------------------------------------------------
                        preg_match($pattern, $tag2, $match, PREG_OFFSET_CAPTURE); 
                        if( count($match) > 0 ){
                            $tag = str_replace($match[1][0],'external '.$match[1][0],$tag); 
                        }else{
                            $noFollow .= ' class="external" ';
                        }
                    }
                    $tag = rtrim ($tag,'>');
                    $tag .= $noFollow.'>';
                    $content = str_replace($tag2,$tag,$content); 
                }
            }
        }
    }
    return $content;
}

# 评论作者链接跳转 or 评论作者链接新窗口打开
# --------------------------------------------------------------------
if (io_get_option('is_go',false)) {
    add_filter('get_comment_author_link', 'comment_author_link_to');
    function comment_author_link_to() {
        $encodeurl = get_comment_author_url();
        $url = go_to($encodeurl);
        $author = get_comment_author();
        if ( empty( $encodeurl ) || 'http://' == $encodeurl )
            return $author;
        else
            return "<a href='$url' target='_blank' rel='nofollow noopener noreferrer' class='url'>$author</a>";
    }
} else {
    add_filter('get_comment_author_link', 'comment_author_link_blank');
    function comment_author_link_blank() {
        $url    = get_comment_author_url();
        $author = get_comment_author();
        if ( empty( $url ) || 'http://' == $url )
            return $author;
        else
            return "<a target='_blank' href='$url' rel='nofollow noopener noreferrer' class='url'>$author</a>";
    }
}



/**
 * 检查当前日期是否在给定时间范围内
 * 
 * @param string $begin_time 开始日期 (格式: 'Y/m/d')
 * @param string $end_time 结束日期 (格式: 'Y/m/d')
 * @return bool 是否在有效期内
 */
function validity_inspection($begin_time, $end_time) {
    $today            = current_time('Y-m-d H:i:s');
    $start_datetime   = strtotime($begin_time . " 00:00:00");
    $end_datetime     = strtotime($end_time . " 23:59:59");
    $current_datetime = strtotime($today);

    return $current_datetime >= $start_datetime && $current_datetime <= $end_datetime;
}

# 重写规则
# --------------------------------------------------------------------
add_action('generate_rewrite_rules', 'io_rewrite_rules' );  
if ( ! function_exists( 'io_rewrite_rules' ) ){ 
    function io_rewrite_rules( $wp_rewrite ){
        $lang = io_get_lang_rules();
        $new_rules = array(    
            'go/?$'         => 'index.php?custom_page=go',
            'hotnews/?$'    => 'index.php?custom_page=hotnews',
            'qr/?$'         => 'index.php?custom_page=qr',
            //'login/?$'       => 'index.php?custom_page=login',
        ); //添加翻译规则   
        if($lang){
            $new_rules[$lang . 'go/?$']      = 'index.php?lang=$matches[1]&custom_page=go';
            $new_rules[$lang . 'hotnews/?$'] = 'index.php?lang=$matches[1]&custom_page=hotnews';
            $new_rules[$lang . 'qr/?$']      = 'index.php?lang=$matches[1]&custom_page=qr';
        }
        $wp_rewrite->rules = $new_rules + $wp_rewrite->rules;   
        //php数组相加   
    }   
} 
add_action('query_vars', 'io_add_query_vars');  
if ( ! function_exists( 'io_add_query_vars' ) ){ 
    function io_add_query_vars($public_query_vars){
        if (!is_admin()) {
            //往数组中添加自定义查询 custom_page 
            $public_query_vars[] = 'custom_page';
        }
        return $public_query_vars;     
    }  
} 
add_action("template_redirect", 'io_template_redirect');   //模板载入规则  
if ( ! function_exists( 'io_template_redirect' ) ){ 
    function io_template_redirect(){   
        global $wp, $wp_query, $wp_rewrite;  
        if( !isset($wp_query->query_vars['custom_page']) )   
            return;    
        $reditect_page =  $wp_query->query_vars['custom_page'];   
        $wp_query->is_home = false;
        switch ($reditect_page) {
            case 'go':
                include(get_theme_file_path('/go.php'));
                exit();
            case 'hotnews':
                include(get_theme_file_path('/templates/hot/hot-home.php'));
                exit();
            case 'qr':
                // TODO:权限判断？
                //$key = get_query_var('qr_data');
                //if($key && get_transient($key)){
                //    $_d = maybe_unserialize(get_transient($key));
                //    io_show_qrcode($_d['u'],$_d['s'],$_d['m']);
                //}
                $text = urldecode($_GET['text']);
                $size = isset($_GET['size']) ? $_GET['size'] : 256;
                $margin = isset($_GET['margin']) ? $_GET['margin'] : 10;
                io_show_qrcode($text,$size,$margin);
                exit();
        }
    }
} 
# 激活主题更新重写规则
# --------------------------------------------------------------------
add_action( 'load-themes.php', 'io_flush_rewrite_rules' );   
function io_flush_rewrite_rules() {   
    global $pagenow;   
    if ( 'themes.php' == $pagenow && isset( $_GET['activated'] ) )   
        io_refresh_rewrite();   
}
function io_refresh_rewrite()
{
    // 如果启用了memcache等对象缓存，固定链接的重写规则缓存对应清除
    if (wp_using_ext_object_cache()){
        wp_cache_flush();
    }
    // 刷新固定链接
    global $wp_rewrite;
    $wp_rewrite->flush_rules();
}
# 搜索只查询文章和网址。
# --------------------------------------------------------------------
//add_filter('pre_get_posts','searchfilter');
function searchfilter($query) {
    //限定对搜索查询和非后台查询设置
    if ($query->is_search && !is_admin() ) {
        $query->set('post_type',array('sites','post','app'));
    }
    return $query;
}
# --------------------------- LEFT JOIN 方法查询自定义字段   -------------------------------------- #

# 修改搜索查询的sql代码，将postmeta表左链接进去。
# --------------------------------------------------------------------
//add_filter('posts_join', 'io_search_join',10,2 );
function io_search_join( $join, $query ) {
    global $wpdb;
    if ( is_search() && $query->is_main_query() && !empty($query->query['s']) ) {
        $join .=' LEFT JOIN '. $wpdb->postmeta . ' AS post_metas ON ' . $wpdb->posts . '.ID = post_metas.post_id ';
    }
    return $join;
}
//add_filter('posts_where', 'io_search_where',10,2);// 在wordpress查询代码中加入自定义字段值的查询。
function io_search_where( $where, $query ) {
    global $pagenow, $wpdb;
    if ( is_search() && $query->is_main_query() && !empty($query->query['s']) ) {
        $meta_key = "'_sites_link','_spare_sites_link','_seo_desc','_sites_sescribe','_app_sescribe','app_down_list','_summary','_books_data','_down_list'";
        $where = preg_replace("/\(\s*".$wpdb->posts.".post_title\s+LIKE\s*(\'[^\']+\')\s*\)/","({$wpdb->posts}.post_title LIKE $1) OR ((post_metas.meta_value LIKE $1) AND (post_metas.meta_key IN ({$meta_key})))", $where ); 
    }
    return $where;
}
// 去重
function io_search_distinct( $where, $query) {
    global $wpdb;
    if ( is_search() && $query->is_main_query() && !empty($query->query['s']) )  {
        return 'DISTINCT';
    }
    return $where;
}
//add_filter( "posts_distinct", "io_search_distinct",10,2 );
# --------------------------- LEFT JOIN 方法查询自定义字段 END -------------------------------------- #

# --------------------------- EXISTS 方法查询自定义字段 --------------------------------------------- #
// EXISTS 方法查询自定义字段
add_action('posts_search', 'io_posts_search_where_exists',10,2);
function io_posts_search_where_exists($search, $query){
    global $wpdb; 
    if (is_search() && $query->is_main_query() && !empty($query->query['s'])) {
        $meta_key = "'_sites_link','_spare_sites_link','_seo_desc','_sites_sescribe','_app_sescribe','app_down_list','_summary','_books_data','_down_list'";
        $sql = " OR EXISTS (SELECT * FROM {$wpdb->postmeta} WHERE post_id={$wpdb->posts}.ID AND meta_key IN ({$meta_key}) AND meta_value like %s)"; 
        $like = '%' . $wpdb->esc_like($query->query['s']) . '%'; 
        $where = $wpdb->prepare($sql, $like);
        $search = preg_replace("/\(\s*".$wpdb->posts.".post_title\s+LIKE\s*(\'[^\']+\')\s*\)/","({$wpdb->posts}.post_title LIKE $1) {$where}", $search ); 
    } 
    return $search; 
}
# --------------------------- EXISTS 方法查询自定义字段 END ------------------------------------------ #


/**
 * 判断是否在微信APP内 
 */
function io_is_wechat_app()
{
    return strripos($_SERVER['HTTP_USER_AGENT'], 'micromessenger');
}

/**
 * 获取当前页面url.
 * TODO: cdn不按协议回源会增加端口号
 */
function io_get_current_url($method = 'wp'){
    static $url = null;
    if (null !== $url) {
        return $url;
    }
    if ($method === 'wp') {
        global $wp;
        $url = get_option('permalink_structure') == '' ? add_query_arg($wp->query_string, '', home_url($wp->request) ) : home_url(add_query_arg(array(), $wp->request));
        return $url;
    }

    $scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $port_str = ($_SERVER['SERVER_PORT'] == '80' && $scheme == 'http') || ($_SERVER['SERVER_PORT'] == '443' && $scheme == 'https') ? '' : ':' . $_SERVER['SERVER_PORT'];
    $link = $scheme . '://' . $_SERVER['HTTP_HOST'] . $port_str . $_SERVER["REQUEST_URI"];
    $url = esc_url($link);
    
    return $url;
}

/**
 * 为链接添加重定向链接.
 * --------------------------------------------------------------------------------------
 */
function io_add_redirect($url, $redirect = '')
{
    if ($redirect) {
        return add_query_arg('redirect_to', urlencode($redirect), $url);
    } elseif (isset($_GET['redirect_to'])) {
        return add_query_arg('redirect_to', urlencode(esc_url_raw($_GET['redirect_to'])), $url);
    } elseif (isset($_GET['redirect'])) {
        return add_query_arg('redirect_to', urlencode(esc_url_raw($_GET['redirect'])), $url);
    }

    return add_query_arg('redirect_to', urlencode(home_url()), $url);
}
function have_http($url){
    $preg = "/^http(s)?:\\/\\/.+/";
    if(preg_match($preg,$url)){
        return true;
    }else{
        return false;
    }
}

/**
 * 文章分页
 * @global WP_Query $wp_query
 * @global int $numpages
 * @return void
 */
function the_post_page()
{
    global $post, $wp_query, $numpages;
    if (isset($wp_query->query_vars['view']) && $wp_query->query_vars['view'] === 'all') {
        echo '<div class="page-nav text-center my-3"><a href="' . get_permalink() . '"><span class="all">' . __('分页阅读', 'i_theme') . '</span></a></div>';
    } elseif (1 < $numpages) {
        echo '<div class="page-nav text-center my-3">';
        //wp_link_pages(array('before' => '', 'after' => '', 'next_or_number' => 'next', 'previouspagelink' => '<span><i class="iconfont icon-arrow-l"></i></span>', 'nextpagelink' => "")); 
        //wp_link_pages(array('before' => '', 'after' => '', 'next_or_number' => 'number', 'link_before' =>'<span>', 'link_after'=>'</span>'));
        //wp_link_pages(array('before' => '', 'after' => '', 'next_or_number' => 'next', 'previouspagelink' => '', 'nextpagelink' => ' <span><i class="iconfont icon-arrow-r"></i></span>')); 
        echo io_post_paging();
        echo '<a href="' . get_pagenum_link(1) . (preg_match('/\?/', get_pagenum_link(1)) ? '&' : '?') . 'view=all' . '"><span class="all">' . __('阅读全文', 'i_theme') . '</span></a></div>';
    }
}
/**
 * 文章分页辅助函数
 * @global WP_query $wp_query
 * @global int $numpages
 * @global WP_Post $post
 * @return string
 */
function io_post_paging()
{
    global $wp_query, $numpages, $post;
    $max_page = $numpages;
    $paged    = $wp_query->get('page');
    $begin    = 1;
    $end      = 2;
    if ($max_page <= 1)
        return '';
    if (empty($paged))
        $paged = 1;

    $html = '';
    if ($paged > 1) {
        $url  = io_get_post_page_link($paged - 1);
        $link = '<a href="' . esc_url($url) . '" class="post-page-numbers"><span><i class="iconfont icon-arrow-l"></i></span></a>';
        $html .= apply_filters('wp_link_pages_link', $link, $paged - 1);
    }
    if ($paged > ($begin + 1)) {
        $url  = io_get_post_page_link(1);
        $link = '<a href="' . esc_url($url) . '" class="post-page-numbers"><span>1</span></a>';
        $html .= $link;
    }
    if ($paged > ($begin + 2))
        $html .= "<span> ... </span>";
    for ($i = $paged - $begin; $i <= $paged + $end; $i++) {
        $link = '';
        if ($i > 0 && $i <= $max_page) {
            if ($i == $paged) {
                $link = "<span class='post-page-numbers current' aria-current='page'><span>{$i}</span></span>";
            } else {
                $url  = io_get_post_page_link($i);
                $link = '<a href="' . esc_url($url) . '" class="post-page-numbers"><span>' . $i . '</span></a>';
            }
        }
        $html .= apply_filters('wp_link_pages_link', $link, $i);
    }
    if ($paged < $max_page - ($end + 1))
        $html .= "<span> ... </span>";
    if ($paged < $max_page - $end) {
        $url  = io_get_post_page_link($max_page);
        $link = '<a href="' . esc_url($url) . '" class="post-page-numbers"><span>' . $max_page . '</span></a>';
        $html .= apply_filters('wp_link_pages_link', $link, $max_page);
    }
    if ($paged < $max_page) {
        $url  = io_get_post_page_link($paged + 1);
        $link = '<a href="' . esc_url($url) . '" class="post-page-numbers"><span><i class="iconfont icon-arrow-r"></i></span></a>';
        $html .= apply_filters('wp_link_pages_link', $link, $paged + 1);
    }
    return $html;
}

/**
 * 获取文章分页链接
 * @global WP_Rewrite $wp_rewrite
 * @global WP_Post $post
 * @param int $page 页码
 * @return string
 */
function io_get_post_page_link($page)
{
    global $wp_rewrite, $post;
    if (1 == $page) {
        $url = get_permalink();
    } else {
        if (!get_option('permalink_structure')) {
            $url = add_query_arg('page', $page, get_permalink());
        } elseif ('page' === get_option('show_on_front') && get_option('page_on_front') == $post->ID) {
            $url = trailingslashit(get_permalink()) . user_trailingslashit("$wp_rewrite->pagination_base/" . $page, 'single_paged');
        } else {
            $url = trailingslashit(get_permalink()) . user_trailingslashit($page, 'single_paged');
        }
    }
    return $url;
}

/**
 * 查询IP地址
 * @param mixed $ip
 * @param mixed $level 1国家 2省 3市 4国家加省 5省加市 6详细
 * @return mixed
 */
function io_get_ip_location($ip, $level = ''){
    if (empty($ip))
        return '无记录';
    $option = io_get_option('ip_location', array('level' => 2, 'v4_type' => 'qqwry'));
    $level  = $level ?: (int) $option['level'];
    require_once get_theme_file_path('/inc/classes/ip/function.php');
    $isQQwry = $option['v4_type'] == 'qqwry';
    //$url     = 'http://freeapi.ipip.net/' . $ip;
    $data    = itbdw\Ip\IpLocation::getLocation($ip, $isQQwry);
    if (isset($data['error'])) {
        return '错误：' . $data['msg'];
    }
    switch ($level) {
        case 1:
            $loc = $data['country'];
            break;
        case 2:
            $loc = $data['province'];
            break;
        case 3:
            $loc = $data['city'];
            break;
        case 4:
            $loc = $data['country'] . $data['province'];
            break;
        case 5:
            $loc = $data['province'] . $data['city'];
            break;
        case 6:
            $loc = $data['area'];
            break;
        default:
            $loc = $data['province'];
            break;
    }
    if (empty($loc))
        $loc = '未知';
    return $loc;
}

/**
 * 记录用户登录时间和IP
 */
function user_last_login($user_login){
    $user = get_user_by('login', $user_login);
    $time = current_time('mysql');
    update_user_meta($user->ID, 'last_login', $time);
    $login_ip = IOTOOLS::get_ip();  
    update_user_meta( $user->ID, 'last_login_ip', $login_ip);  
}
add_action('wp_login', 'user_last_login');

/**
 * 判断是否有非法名称
 */
function is_disable_username($name)
{
    $disable_reg_keywords = io_get_option('user_nickname_stint','');
    $disable_reg_keywords = preg_split("/,|，|\s|\n/", $disable_reg_keywords);

    if (!$disable_reg_keywords || !$name) {
        return false;
    }
    foreach ($disable_reg_keywords as $keyword) {
        if (stristr($name, $keyword) || $keyword == $name) {
            return true;
        }
    }
    return false;
}
/**
 * 中文文字计数
 */
function _new_strlen($str, $charset = 'utf-8')
{
    //中文算一个，英文算半个
    return (int)((strlen($str) + mb_strlen($str, $charset)) / 4);
}
/**
 * 判断是否是重复昵称
 */
function io_nicename_exists($name)
{
    $db_name = false;
    if ($name) {
        global $wpdb;
        $db_name = $wpdb->get_var("SELECT id FROM $wpdb->users WHERE `user_nicename`='" . $name . "' OR `display_name`='" . $name . "' ");
        // 查询已登录用户
        $current_user_id = get_current_user_id();
        if($db_name && $current_user_id && $db_name == $current_user_id){
            $db_name = false;
        }
    }
    return $db_name;
}
/**
 * 判断用户名合法 
 * @param string $user_name
 * @param string $logn_in 登录流程
 * @return array
 */
function is_username_legal($user_name, $logn_in = false)
{

    if (!$user_name) {
        return array('error' => 1, 'msg' => '请输入用户名');
    }

    if (_new_strlen($user_name) < 2) {
        return array('error' => 1, 'msg' => '用户名太短');
    }
    if (_new_strlen($user_name) > 10) {
        return array('error' => 1, 'msg' => '用户名太长');
    }
    if (!$logn_in) {
        if (is_numeric($user_name)) {
            return array('error' => 1, 'msg' => '用户名不能为纯数字');
        }
        if (filter_var($user_name, FILTER_VALIDATE_EMAIL)) {
            return array('error' => 1, 'msg' => '请勿使用邮箱帐号作为用户名');
        }
        if (is_disable_username($user_name)) {
            return array('error' => 1, 'msg' => '昵称含保留或非法字符');
        }
        //重复昵称判断
        if (io_get_option('nickname_exists', true)) {
            if (io_nicename_exists($user_name)) {
                return array('error' => 1, 'msg' => '昵称已存在，请换一个试试');
            }
        }
    }

    return array('error' => 0);
}

function my_avatar( $avatar, $id_or_email,  $size = 96, $default = '', $alt = '' ,$args=NULL){  
    if ( is_numeric( $id_or_email ) )
        $user_id = (int) $id_or_email;
    elseif ( is_string( $id_or_email ) && ( $user = get_user_by( 'email', $id_or_email ) ) )
        $user_id = $user->ID;
    elseif ( is_object( $id_or_email ) && ! empty( $id_or_email->user_id ) )
        $user_id = (int) $id_or_email->user_id;
        
    if ( empty( $user_id ) )
        return $avatar;
    $type = get_user_meta( $user_id, 'avatar_type', true );
    $author_class = is_author( $user_id ) ? ' current-author' : '' ;
    if( empty($type) || 'gravatar' === $type || 'letter' === $type){
        return $avatar;
    }else{
        return "<img alt='" . esc_attr( $alt ) . "' src='" . format_http(esc_url( get_user_meta( $user_id, "{$type}_avatar", true )) ) . "' class='{$args['class']} avatar avatar-{$size}{$author_class} photo' height='{$size}' width='{$size}' />";
    }
}

function format_http($url){  
        $pattern = '@^(?:https?:)?(.*)@i';
        $result = preg_match($pattern, $url, $matches);
        return $matches[1];
} 

/**
 * 显示广告
 * 
 * @param mixed $loc 广告位
 * @param mixed $is_tow 是否双栏
 * @param mixed $begin
 * @param mixed $end
 * @param mixed $echo
 * @return mixed
 */
function show_ad($loc, $is_tow = true, $class='', $begin = '<div class="apd-body">', $end = '</div>', $echo = true)
{
    $config = io_get_option($loc, array('switch' => false, 'tow' => false));
    $ad    = '';
    if (
        $config['switch'] && (
            $config['loc'] === '1' ||
            ($config['loc'] === '3' && !wp_is_mobile()) ||
            ($config['loc'] === '2' && wp_is_mobile())
        )
    ) {
        if (!$is_tow) {
            $ad = stripslashes($config['content']) ;
        } else {
            if (isset($config['tow']) && $config['tow']) {
                $ad = '<div class="row-a row-col-1a row-col-xl-2a"><div class="apd-col">' . stripslashes($config['content']) . '</div>
                <div class="apd-col d-none d-xl-block">' . stripslashes($config['content2']) . '</div></div>';
            } else {
                $ad = '<div class="row-a row-col-1a"><div class="apd-col">' . stripslashes($config['content']) . '</div></div>';
            }
        }
    }

    $html = '';
    if($ad) {
        $html .= '<div class="apd my-3 '.$class.'">';
        $html .= $begin . $ad . $end;
        $html .=  '</div>';
    }

    if ($echo)
        echo $html;
    else
        return $html;
}
/**
 * 根据页面模板获取页面链接
 * 没用就自动创建
 * @param string $template 模板文件名称,需要.php后缀
 * @param int $is_id 返回文章id
 * @param array $args 其他模板参数
 * @return string|int|bool
 */
function io_get_template_page_url($template, $is_id = false, $args = array())
{
    if (empty($template) || !is_string($template)) {
        return false;
    }
    $cache_key = $template . ($is_id ? '_is_id' : '');
    $cache     = wp_cache_get($cache_key, 'page_url');
    if ($cache !== false) {
        return $cache;
    }

    $templates = array(
        'template-blog.php'       => array('博客', 'blog'),
        'template-bulletin.php'   => array('公告列表', 'bulletins'),
        'template-contribute.php' => array('投稿', 'contribute'),
        'template-links.php'      => array('友情链接', 'links'),
        'template-rankings.php'   => array('排行榜', 'rankings'),
    );
    $templates = array_merge($templates, $args);

    if (!isset($templates[$template])) {
        return false;
    }

    $pages_args = array(
        'meta_key'    => '_wp_page_template',
        'meta_value'  => $template,
        'post_type'   => 'page',
        'post_status' => 'publish',
        'numberposts' => 1, // 仅需要找到一个匹配的页面
    );
    $pages = get_pages($pages_args);

    $page_id = !empty($pages[0]->ID) ? $pages[0]->ID : 0;

    if (!$page_id && !empty($templates[$template][0]) && is_super_admin()) {//创建
        $one_page = array(
            'post_title'  => sanitize_text_field($templates[$template][0]),
            'post_name'   => sanitize_title($templates[$template][1]),
            'post_status' => 'publish',
            'post_type'   => 'page',
            'post_author' => get_current_user_id(),
        );

        $page_id = wp_insert_post($one_page);

        if (is_wp_error($page_id) || !$page_id) {
            return false; // 创建失败，返回 false
        }

        update_post_meta($page_id, '_wp_page_template', $template);
    }

    if ($page_id) {
        if ($is_id) {
            wp_cache_set($cache_key, $page_id, 'page_url');
            return $page_id;
        }
        $url = esc_url(get_permalink($page_id));
        wp_cache_set($cache_key, $url, 'page_url');
        return $url;
    }
    return false;
}


//主题更新
function io_theme_update_checker(){
    if (io_get_option('update_theme', false)) {
        /**
         * @var string 子主题支持的最大版本
         * HOOK : io_sub_max_version_filters
         */
        $max_v = apply_filters('io_sub_max_version_filters', '');
        require_once get_theme_file_path('/inc/classes/theme.update.checker.class.php');
        new ThemeUpdateChecker(
            'onenav',
            'https://www.iotheme.cn/update/',
            $max_v
        );
    }
}
add_action('init', 'io_theme_update_checker');

/**
 * go跳转广告位
 * @return void
 */
function io_go_page_content_ad_html(){
    $html = show_ad('ad_go_page_content',false);
    echo $html; 
}
add_action('io_go_page_content_ad', 'io_go_page_content_ad_html');




/**
 * 刷新固定连接
 * @return void
 */
function io_admin_init(){
    if (isset($_REQUEST['page']) &&isset($_REQUEST['action']) && 'theme_settings' === $_REQUEST['page'] && 'rewrite_rules' === $_REQUEST['action']) {
        flush_rewrite_rules();
    }
}
add_action('admin_init', 'io_admin_init', 1);
