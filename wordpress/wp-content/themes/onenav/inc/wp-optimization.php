<?php if ( ! defined( 'ABSPATH' )  ) { die; }
/*
 * WordPress 优化
 */
 
# --------------------------------------------------------------------
# 屏蔽文章修订
# --------------------------------------------------------------------
if(io_get_option('disable_revision', false)){
	if(!defined('WP_POST_REVISIONS')){
		define('WP_POST_REVISIONS', false);
	}
	remove_action('pre_post_update', 'wp_save_post_revision');
	add_filter('register_meta_args', 'io_filter_register_meta_args', 10, 4);
}
function io_filter_register_meta_args($args, $defaults, $object_type, $meta_key){
    if($object_type == 'post' && !empty($args['object_subtype']) && in_array($args['object_subtype'], ['post', 'page'])){
        return array_merge($args, ['revisions_enabled'=>false]);
    }

    return $args;
}
# --------------------------------------------------------------------
# 移除admin bar
# --------------------------------------------------------------------
if(io_get_option('remove_admin_bar',true)){
    add_filter('show_admin_bar', '__return_false');
}
# --------------------------------------------------------------------
# 屏蔽字符转码
# --------------------------------------------------------------------
if(io_get_option('disable_texturize',true)){
    add_filter('run_wptexturize', '__return_false');
}
# --------------------------------------------------------------------
# 禁用古腾堡
# --------------------------------------------------------------------
if(io_get_option('disable_gutenberg',true)){
	remove_action('wp_enqueue_scripts', 'wp_common_block_scripts_and_styles');
	remove_action('admin_enqueue_scripts', 'wp_common_block_scripts_and_styles');
	remove_filter('the_content', 'do_blocks', 9);
    if(is_admin()){
        add_filter('use_block_editor_for_post_type', '__return_false');
    }
    //移除wp5.9增加的内容
	add_action('after_setup_theme', function() {
		// 移除 SVG 和全局样式
		remove_action('wp_enqueue_scripts', 'wp_enqueue_global_styles');
		// 删除添加全局内联样式的 wp_footer 操作
		remove_action('wp_footer', 'wp_enqueue_global_styles', 1);
		// 删除render_block 过滤器
		remove_filter('render_block', 'wp_render_duotone_support');
		remove_filter('render_block', 'wp_restore_group_inner_container');
		remove_filter('render_block', 'wp_render_layout_support_flag');
	});
}
# --------------------------------------------------------------------
# 禁用登录页语言切换
# --------------------------------------------------------------------
add_filter('login_display_language_dropdown', '__return_false');
# --------------------------------------------------------------------
# 屏蔽站点Feed
# --------------------------------------------------------------------
add_action('template_redirect', 'io_feed_template_redirect');
function io_feed_template_redirect(){
    if(is_feed()){
        // 屏蔽站点 Feed
        if(io_get_option('disable_feed',true)){
            wp_die('Feed已经关闭, 请访问<a href="'.get_bloginfo('url').'">网站首页</a>！', 'Feed关闭'	, 200);
        }
    }elseif(io_get_option('ioc_category',true) && !in_array('cat', io_get_option('rewrites_category_types', array('tag'), 'types'))){
        // 开启去掉URL中category，跳转到 no base 的 link
        if( is_category() ){
            if(strpos($_SERVER['REQUEST_URI'], '/category/') !== false){
                wp_redirect(site_url(str_replace('/category', '', $_SERVER['REQUEST_URI'])), 301);
                exit;
            }
        }
    }		
}
# --------------------------------------------------------------------
# 禁用 XML-RPC 接口
# --------------------------------------------------------------------
if(io_get_option('disable_xml_rpc',true)){
    add_filter( 'xmlrpc_enabled', '__return_false' );
	add_filter( 'xmlrpc_methods', '__return_empty_array' );
    remove_action( 'xmlrpc_rsd_apis', 'rest_output_rsd' );
}
# --------------------------------------------------------------------
# ----------
# --------------------------------------------------------------------
add_filter('register_taxonomy_args', function($args){
    // 屏蔽 REST API
    if(io_get_option('disable_rest_api',true)){
        $args['show_in_rest']    = false;
    }

    return $args;
});

function io_filter_register_post_type_args($args, $post_type){
    // 屏蔽 Trackback 
    // 禁用日志修订功能 diable_revision
    if(did_action('init') && !empty($args['supports']) && is_array($args['supports']) && in_array($post_type, ['post', 'page'])){
        foreach(['trackbacks'=>'disable_trackbacks', 'revisions'=>'disable_revision'] as $support => $setting_name){
            //echo $setting_name;
            if(io_get_option($setting_name, false) && in_array($support, $args['supports'])){
                $args['supports']	= array_diff($args['supports'], [$support]);

                remove_post_type_support($post_type, $support);	
            }
        }
    }

    return $args;
}
add_filter('register_post_type_args', 'io_filter_register_post_type_args', 10, 2);

# --------------------------------------------------------------------
# 屏蔽Trackbacks
# --------------------------------------------------------------------
if(io_get_option('disable_trackbacks',true)){
    if(!io_get_option('disable_xml_rpc',true)){
        //彻底关闭 pingback
        add_filter('xmlrpc_methods',function($methods){
            $methods['pingback.ping'] = '__return_false';
            $methods['pingback.extensions.getPingbacks'] = '__return_false';
            return $methods;
        });
    }

    //禁用 pingbacks, enclosures, trackbacks
    remove_action( 'do_pings', 'do_all_pings', 10 );

    //去掉 _encloseme 和 do_ping 操作。
    remove_action( 'publish_post','_publish_post_hook',5 );
}
# --------------------------------------------------------------------
# 屏蔽 REST API
# --------------------------------------------------------------------
if(io_get_option('disable_rest_api',true)){
    remove_action('init',            'rest_api_init' );
    remove_action('rest_api_init',    'rest_api_default_filters', 10 );
    remove_action('parse_request',    'rest_api_loaded' );

    add_filter('rest_enabled',        '__return_false');
    add_filter('rest_jsonp_enabled','__return_false');

    // 移除头部 wp-json 标签和 HTTP header 中的 link 
    remove_action('wp_head',            'rest_output_link_wp_head', 10 );
    remove_action('template_redirect',    'rest_output_link_header', 11);

    remove_action('xmlrpc_rsd_apis',    'rest_output_rsd');

    remove_action('auth_cookie_malformed',        'rest_cookie_collect_status');
    remove_action('auth_cookie_expired',        'rest_cookie_collect_status');
    remove_action('auth_cookie_bad_username',    'rest_cookie_collect_status');
    remove_action('auth_cookie_bad_hash',        'rest_cookie_collect_status');
    remove_action('auth_cookie_valid',            'rest_cookie_collect_status');
    remove_filter('rest_authentication_errors',    'rest_cookie_check_errors', 100 );
}
# --------------------------------------------------------------------
# 移除 WP_Head 无关紧要的代码
# --------------------------------------------------------------------
if(io_get_option('remove_head_links',true)){
    remove_action( 'wp_head', 'wp_generator');                    //删除 head 中的 WP 版本号
    foreach (['rss2_head', 'commentsrss2_head', 'rss_head', 'rdf_header', 'atom_head', 'comments_atom_head', 'opml_head', 'app_head'] as $action) {
        remove_action( $action, 'the_generator' );
    }
    remove_action( 'wp_head', 'rsd_link' );                        //删除 head 中的 RSD LINK
    remove_action( 'wp_head', 'wlwmanifest_link' );                //删除 head 中的 Windows Live Writer 的适配器？
    remove_action( 'wp_head', 'feed_links_extra', 3 );              //删除 head 中的 Feed 相关的link
    remove_action( 'wp_head', 'index_rel_link' );                //删除 head 中首页，上级，开始，相连的日志链接
    remove_action( 'wp_head', 'parent_post_rel_link', 10);
    remove_action( 'wp_head', 'start_post_rel_link', 10);
    remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10);
    remove_action( 'wp_head', 'wp_shortlink_wp_head', 10, 0 );    //删除 head 中的 shortlink
    remove_action( 'wp_head', 'rest_output_link_wp_head', 10);    // 删除头部输出 WP RSET API 地址
    remove_action( 'template_redirect',    'wp_shortlink_header', 11);        //禁止短链接 Header 标签。
    remove_action( 'template_redirect',    'rest_output_link_header', 11);    // 禁止输出 Header Link 标签。
} 
# --------------------------------------------------------------------
# 屏蔽后台隐私
# --------------------------------------------------------------------
if(io_get_option('disable_privacy',true)){
	remove_action('user_request_action_confirmed', '_wp_privacy_account_request_confirmed');
	remove_action('user_request_action_confirmed', '_wp_privacy_send_request_confirmation_notification', 12);
	remove_action('wp_privacy_personal_data_exporters', 'wp_register_comment_personal_data_exporter');
	remove_action('wp_privacy_personal_data_exporters', 'wp_register_media_personal_data_exporter');
	remove_action('wp_privacy_personal_data_exporters', 'wp_register_user_personal_data_exporter', 1);
	remove_action('wp_privacy_personal_data_erasers', 'wp_register_comment_personal_data_eraser');
	remove_action('init', 'wp_schedule_delete_old_privacy_export_files');
	remove_action('wp_privacy_delete_old_export_files', 'wp_privacy_delete_old_export_files');

	add_filter('option_wp_page_for_privacy_policy', '__return_zero');

    if(is_admin()){
		add_action('admin_menu', function(){
			remove_submenu_page('options-general.php', 'options-privacy.php');
			remove_submenu_page('tools.php', 'export-personal-data.php');
			remove_submenu_page('tools.php', 'erase-personal-data.php');
		}, 11);
		add_action('admin_init', function(){
            // Privacy policy text changes check.
			remove_action('admin_init', array( 'WP_Privacy_Policy_Content', 'text_change_check' ), 100);
            // Show a "postbox" with the text suggestions for a privacy policy.
			remove_action('edit_form_after_title', array( 'WP_Privacy_Policy_Content', 'notice' ));
            // Add the suggested policy text from WordPress.
			remove_action('admin_init',  array( 'WP_Privacy_Policy_Content', 'add_suggested_content' ), 1);
            // Update the cached policy info when the policy page is updated.
			remove_action('post_updated', array( 'WP_Privacy_Policy_Content', '_policy_page_updated' ));
			remove_filter('list_pages', '_wp_privacy_settings_filter_draft_page_titles', 10);
		}, 1);
    }
}
# --------------------------------------------------------------------
# 屏蔽 Emoji
# --------------------------------------------------------------------
if(io_get_option('emoji_switcher',true)){
	add_action('admin_init', function(){
		remove_action('admin_print_scripts',	'print_emoji_detection_script');
		remove_action('admin_print_styles',		'print_emoji_styles');
	}); 

	remove_action('wp_head',			'print_emoji_detection_script',	7);
	remove_action('wp_print_styles',	'print_emoji_styles');

	remove_action('embed_head',			'print_emoji_detection_script');

	remove_filter('the_content_feed',	'wp_staticize_emoji');
	remove_filter('comment_text_rss',	'wp_staticize_emoji');
	remove_filter('wp_mail',			'wp_staticize_emoji_for_email');

	add_filter('emoji_svg_url',		'__return_false');

	add_filter('tiny_mce_plugins',	function($plugins){ 
		return array_diff($plugins, ['wpemoji']); 
	});
}
# --------------------------------------------------------------------
# 屏蔽文章Embed
# --------------------------------------------------------------------
if(io_get_option('disable_post_embed',true)){  
	remove_action('wp_head', 'wp_oembed_add_discovery_links');
	remove_action('wp_head', 'wp_oembed_add_host_js');
}
# --------------------------------------------------------------------
# 去掉URL中category
# --------------------------------------------------------------------
if( io_get_option('ioc_category',true) && !in_array( 'cat', io_get_option('rewrites_category_types', array('tag'), 'types')) ) {
    add_action('created_category', 'no_category_base_refresh_rules');
    add_action('edited_category', 'no_category_base_refresh_rules');
    add_action('delete_category', 'no_category_base_refresh_rules');
    function no_category_base_refresh_rules() {
        global $wp_rewrite;
        $wp_rewrite -> flush_rules();
    }
    // 删除类别库
    add_action('init', 'no_category_base_permastruct');
    function no_category_base_permastruct() {
        global $wp_rewrite, $wp_version;
        if (version_compare($wp_version, '3.4', '<')) {
            // For pre-3.4 support
            $wp_rewrite->extra_permastructs['category'][0] = '%category%';
        } else {
            $wp_rewrite->extra_permastructs['category']['struct'] = '%category%';
        }
    }
    // 添加自定义类别重写规则
    add_filter('category_rewrite_rules', 'no_category_base_rewrite_rules');
    function no_category_base_rewrite_rules($category_rewrite) {
        //var_dump($category_rewrite); // 用于调试
        $category_rewrite = array();
        $categories = get_categories(array('hide_empty' => false));
        foreach ($categories as $category) {
            $category_nicename = $category -> slug;
            if ($category -> parent == $category -> cat_ID)// recursive recursion
                $category -> parent = 0;
            elseif ($category -> parent != 0)
                $category_nicename = get_category_parents($category -> parent, false, '/', true) . $category_nicename;
            $category_rewrite['(' . $category_nicename . ')/(?:feed/)?(feed|rdf|rss|rss2|atom)/?$'] = 'index.php?category_name=$matches[1]&feed=$matches[2]';
            $category_rewrite['(' . $category_nicename . ')/page/?([0-9]{1,})/?$'] = 'index.php?category_name=$matches[1]&paged=$matches[2]';
            $category_rewrite['(' . $category_nicename . ')/?$'] = 'index.php?category_name=$matches[1]';
        }
        // 重定向来自Old Category Base的支持
        global $wp_rewrite;
        $old_category_base = get_option('category_base') ? get_option('category_base') : 'category';
        $old_category_base = trim($old_category_base, '/');
        $category_rewrite[$old_category_base . '/(.*)$'] = 'index.php?category_redirect=$matches[1]';
        //var_dump($category_rewrite); // 用于调试
        return $category_rewrite;
    }
    // 添加'category_redirect'查询变量
    add_filter('query_vars', 'no_category_base_query_vars');
    function no_category_base_query_vars($public_query_vars) {
        $public_query_vars[] = 'category_redirect';
        return $public_query_vars;
    }
    // 如果设置了'category_redirect'，则重定向
    add_filter('request', 'no_category_base_request');
    function no_category_base_request($query_vars) {
        //print_r($query_vars); // 用于调试
        if (isset($query_vars['category_redirect'])) {
            $catlink = trailingslashit(home_url()) . user_trailingslashit($query_vars['category_redirect'], 'category');
            status_header(301);
            header("Location: $catlink");
            exit();
        }
        return $query_vars;
    }
}
# --------------------------------------------------------------------
# 禁用 Auto OEmbed
# --------------------------------------------------------------------
if(io_get_option('disable_autoembed',true)){ 
    remove_filter('the_content',			[$GLOBALS['wp_embed'], 'autoembed'], 8);
    remove_filter('widget_text_content',	[$GLOBALS['wp_embed'], 'autoembed'], 8);
    remove_filter('widget_block_content',	[$GLOBALS['wp_embed'], 'autoembed'], 8);

    remove_action('edit_form_advanced',	[$GLOBALS['wp_embed'], 'maybe_run_ajax_cache']);
    remove_action('edit_page_form',		[$GLOBALS['wp_embed'], 'maybe_run_ajax_cache']);
}
# --------------------------------------------------------------------
# Gravatar加速
# --------------------------------------------------------------------
add_filter('pre_get_avatar_data', function($args, $id_or_email){
    $gravatar_cdn = io_get_option('gravatar_cdn','iocdn');
    if($gravatar_cdn=='gravatar'){
        return $args;
    }
    $email_hash = '';
    $user       = $email = false;
    
    if(is_object($id_or_email) && isset($id_or_email->comment_ID)){
        $id_or_email    = get_comment($id_or_email);
    }

    if(is_numeric($id_or_email)){
        $user    = get_user_by('id', absint($id_or_email));
    }elseif($id_or_email instanceof WP_User){    // User Object
        $user    = $id_or_email;
    }elseif($id_or_email instanceof WP_Post){    // Post Object
        $user    = get_user_by('id', intval($id_or_email->post_author));
    }elseif($id_or_email instanceof WP_Comment){    // Comment Object
        if(!empty($id_or_email->user_id)){
            $user    = get_user_by('id', intval($id_or_email->user_id));
        }elseif(!empty($id_or_email->comment_author_email)){
            $email    = $id_or_email->comment_author_email;
        }
    }elseif(is_string($id_or_email)){
        if(strpos($id_or_email, '@md5.gravatar.com')){
            list($email_hash)    = explode('@', $id_or_email);
        } else {
            $email    = $id_or_email;
        }
    }

    if($user){
        $args    = apply_filters('io_default_avatar_data', $args, $user->ID);
        if($args['found_avatar']){
            return $args;
        }else{
            $email = $user->user_email;
        }
    }
    
    if(!$email_hash){
        if($email){
            $email_hash = md5(strtolower(trim($email)));
        }
    }

    if($email_hash){
        $args['found_avatar']    = true;
    }
    
    switch ($gravatar_cdn){
        case "cravatar":
            $url    = '//cravatar.cn/avatar/'.$email_hash;
            break;
        case "sep":
            $url    = '//cdn.sep.cc/avatar/'.$email_hash;
            break;
        case "loli":
            $url    = '//gravatar.loli.net/avatar/'.$email_hash;
            break;
        case "chinayes":
            $url    = '//gravatar.wp-china-yes.net/avatar/'.$email_hash;
            break;
        case "iocdn":
            $url    = '//cdn.iocdn.cc/avatar/'.$email_hash;
            break;
        case "qiniu":
            $url    = '//dn-qiniu-avatar.qbox.me/avatar/'.$email_hash;
            break;
        default:
            $url    = '//cdn.iocdn.cc/avatar/'.$email_hash;
    }

    $url_args    = array_filter([
        's'    => $args['size'],
        'd'    => $args['default'],
        'f'    => $args['force_default'] ? 'y' : false,
        'r'    => $args['rating'],
    ]);

    $url            = add_query_arg(rawurlencode_deep($url_args), set_url_scheme($url, $args['scheme']));
    $args['url']    = apply_filters('get_avatar_url', $url, $id_or_email, $args);

    return $args;

}, 10, 2);
# --------------------------------------------------------------------
# 移除后台界面右上角的帮助
# --------------------------------------------------------------------
if(io_get_option('remove_help_tabs',true)){  
    if(is_admin()){
        add_action('in_admin_header', function(){
            global $current_screen;
        $current_screen->remove_help_tabs();
        });
    }
}
# --------------------------------------------------------------------
# 移除后台界面右上角的选项
# --------------------------------------------------------------------
if(io_get_option('remove_screen_options',true)){  
    if(is_admin()){
        add_filter('screen_options_show_screen', '__return_false');
        add_filter('hidden_columns', '__return_empty_array');
    }
}
# --------------------------------------------------------------------
# 禁止使用 admin 用户名尝试登录
# --------------------------------------------------------------------
if(io_get_option('no_admin',true)){
    add_filter( 'wp_authenticate',  function ($user){
        if($user == 'admin') exit;
    });

    add_filter('sanitize_user', function ($username, $raw_username, $strict){
        if($raw_username == 'admin' || $username == 'admin'){
            exit;
        }
        return $username;
    }, 10, 3);
}
# --------------------------------------------------------------------
# 压缩网站源码
# --------------------------------------------------------------------
if (io_get_option('compress_html', true)) {
    add_action('get_header', 'io_enable_html_compression');

    function io_enable_html_compression()
    {
        ob_start('io_compress_html_output');
    }

    function io_compress_html_output($buffer)
    {
        $initial_size = strlen($buffer);

        // 分离出不需要压缩的内容
        $buffer_parts = explode("<!--wp-compress-html-->", $buffer);
        foreach ($buffer_parts as &$part) {
            if (strpos($part, '<!--wp-compress-html no compression-->') !== false) {
                // 保留不需要压缩的部分
                $part = str_replace("<!--wp-compress-html no compression-->", '', $part);
            } else {
                // 删除多余的换行和空格，压缩HTML内容
                $part = preg_replace([
                    '/\t+/',               // 删除制表符
                    '/\s{2,}/',            // 删除连续空格
                    '/\n|\r/',             // 删除换行符
                    '/>\s+</'              // 删除标签之间的空格
                ], [' ', ' ', '', '><'], $part);
            }
        }

        $compressed_output = implode('', $buffer_parts);
        $final_size        = strlen($compressed_output);

        // 计算节省的字节数和百分比
        $savings = ($initial_size - $final_size) / $initial_size * 100;
        $savings = round($savings, 2);

        // 添加压缩信息的注释
        $compressed_output .= "\n<!-- 压缩前: {$initial_size} bytes; 压缩后: {$final_size} bytes; 节省: {$savings}% -->";
        return $compressed_output;
    }

    // 代码高亮部分不启用压缩
    function io_exclude_compression_for_highlighted_code($content)
    {
        // 只对<pre>标签进行处理
        return preg_replace_callback('/<pre.*?>.*?<\/pre>/is', function ($matches) {
            // 给 <pre> 标签及其内容添加 no compression 注释
            return "<!--wp-compress-html--><!--wp-compress-html no compression-->\n" . $matches[0] . "\n<!--wp-compress-html no compression--><!--wp-compress-html-->";
        }, $content);
        // 如果正文中包含代码高亮插件的代码块，则不进行压缩
        //if (preg_match('/(crayon-|<\/pre>)/i', $content)) {
        //    $content = '<!--wp-compress-html--><!--wp-compress-html no compression-->' . $content;
        //    $content .= '<!--wp-compress-html no compression--><!--wp-compress-html-->';
        //}
        //return $content;
    }
    add_filter('the_content', 'io_exclude_compression_for_highlighted_code');


    // 编辑器js代码禁止压缩
    add_action('before_wp_tiny_mce', 'io_before_wp_tiny_mce');
    add_action('after_wp_tiny_mce', 'io_after_wp_tiny_mce');
    function io_before_wp_tiny_mce()
    {
        echo '<!--wp-compress-html--><!--wp-compress-html no compression-->';
    }
    function io_after_wp_tiny_mce()
    {
        echo '<!--wp-compress-html no compression--><!--wp-compress-html-->';
    }
}

# --------------------------------------------------------------------
# 屏蔽默认小工具
# --------------------------------------------------------------------
add_action( 'widgets_init', 'my_unregister_widgets' );   
function my_unregister_widgets() {   
    unregister_widget( 'WP_Widget_Archives' );   
    unregister_widget( 'WP_Widget_Calendar' );   
    unregister_widget( 'WP_Widget_Categories' );   
    unregister_widget( 'WP_Widget_Links' );   
    unregister_widget( 'WP_Widget_Meta' );   
    unregister_widget( 'WP_Widget_Pages' );   
    unregister_widget( 'WP_Widget_Recent_Comments' );     
    unregister_widget( 'WP_Widget_Recent_Posts' );   
    unregister_widget( 'WP_Widget_RSS' );   
    unregister_widget( 'WP_Widget_Block' );  
    //unregister_widget( 'WP_Widget_Search' );   
    unregister_widget( 'WP_Widget_Tag_Cloud' );   
    unregister_widget( 'WP_Widget_Text' );   
    unregister_widget( 'WP_Nav_Menu_Widget' ); 
    unregister_widget( 'WP_Widget_Media_Audio' );
    unregister_widget( 'WP_Widget_Media_Image' );
    unregister_widget( 'WP_Widget_Media_Gallery' );
    unregister_widget( 'WP_Widget_Media_Video' );  
    //unregister_widget( 'WP_Widget_Custom_HTML' );
}  
# --------------------------------------------------------------------
# 429
# --------------------------------------------------------------------
//if($vpc=io_get_option('vpc_ip','')){
//    $vpc = explode(':',$vpc);
//    if(!defined('WP_PROXY_HOST') && !defined('WP_PROXY_PORT')){
//        define('WP_PROXY_HOST',$vpc[0]);
//        define('WP_PROXY_PORT',$vpc[1]);
//    }
//}
# --------------------------------------------------------------------
# 移除 WordPress 头部加载 DNS 预获取（dns-prefetch）
# --------------------------------------------------------------------
function io_remove_dns_prefetch( $hints, $relation_type ) {
    if ( 'dns-prefetch' === $relation_type ) {
        return array_diff( wp_dependencies_unique_hosts(), $hints );
    }
 
    return $hints;
}
if(io_get_option('remove_dns_prefetch',true)) add_filter( 'wp_resource_hints', 'io_remove_dns_prefetch', 10, 2 );

add_filter( 'wp_lazy_loading_enabled', function( $default, $tag_name, $context ){
		if ( 'img' === $tag_name && 'the_content' === $context ){
			return false;
		}
		return $default;
}, 10, 3 );

//add_action( 'admin_menu', 'remove_site_health_menu' );
//add_action( 'wp_dashboard_setup', 'remove_site_health_dashboard_widget');
//add_action( 'current_screen', 'block_site_health_access' );
//add_filter( 'site_status_tests', 'prefix_remove_site_health', 100 );
//禁用站点健康电子邮件通知：
add_filter( 'wp_fatal_error_handler_enabled', '__return_false' );
//删除站点健康菜单项：	
function remove_site_health_menu(){
	remove_submenu_page( 'tools.php','site-health.php' ); 
}
//删除仪表盘站点健康状态面板：
function remove_site_health_dashboard_widget()
{
    remove_meta_box('dashboard_site_health', 'dashboard', 'normal');
}
//阻止站点运行站点健康检查页
function block_site_health_access() {
    if ( is_admin() ) {
        $screen = get_current_screen();
        if ( 'site-health' == $screen->id ) {
            wp_redirect( admin_url() );
            exit;
        }
    }
} 
//彻底禁用站点健康检
function prefix_remove_site_health( $tests ) {
	$hidden_tests =  array(
        'php_version'               => 'direct', //PHP 版本
        'wordpress_version'         => 'direct', //WordPress 版本
        'plugin_version'            => 'direct', //插件版本
        'theme_version'             => 'direct', //主题版本
        'sql_server'                => 'direct', //数据库服务器版本
        'php_extensions'            => 'direct', //PHP 扩展
        'php_default_timezone'      => 'direct', //PHP 默认时区
        'php_sessions'              => 'direct', //PHP Sessions
        'utf8mb4_support'           => 'direct', //MySQL utf8mb4 支持
        'https_status'              => 'direct', //HTTPS 状态
        'ssl_support'               => 'direct', //安全通讯
        'scheduled_events'          => 'direct', //计划的事件
        'http_requests'             => 'direct', //HTTP请求
        'debug_enabled'             => 'direct', //启用调试
        'file_uploads'              => 'direct', //文件上传
        'plugin_theme_auto_updates' => 'direct', //插件和主题自动更新
        'dotorg_communication'      => 'async',  //与WordPress.org联通状态
        'background_updates'        => 'async',  //后台更新
        'loopback_requests'         => 'async',  //Loopback request
        'authorization_header'      => 'async',  //Authorization header
        'rest_availability'         => 'direct', //REST API 可用性
    );
	foreach ( $hidden_tests as $test=>$type ) {
		unset( $tests[$type][$test] );
	}
	return $tests;
}




// 禁用块编辑器管理(Gutenberg)插件中的小部件。
add_filter( 'gutenberg_use_widgets_block_editor', '__return_false' );
// 禁用块编辑器管理小部件。
add_filter( 'use_widgets_block_editor', '__return_false' );

// 屏蔽自动更新和更新检查作业
if(io_get_option('disable_auto_update',true)){  
	add_filter('automatic_updater_disabled', '__return_true');

	remove_action('init', 'wp_schedule_update_checks');
	remove_action('wp_version_check', 'wp_version_check');
	remove_action('wp_update_plugins', 'wp_update_plugins');
	remove_action('wp_update_themes', 'wp_update_themes');

    if(is_admin()){
		remove_action('admin_init', '_maybe_update_core');
		remove_action('admin_init', '_maybe_update_plugins');
		remove_action('admin_init', '_maybe_update_themes');
    }
}

//禁用 WordPress 附件页面
function io_redirect_attachment_to_post() {
    // 如果是附件页面
    if ( is_attachment() ) {
        global $post;
        if( empty( $post ) ) $post = get_queried_object();
        // 如果附件有所在的页面
        if ($post->post_parent) {
            $link = get_permalink( $post->post_parent );
            wp_redirect( $link, '301' );
            exit();
        }
        // 如果没有页面
        else {
            wp_redirect( home_url(), '301' );
            exit();
        }
    }
}
add_action( 'template_redirect', 'io_redirect_attachment_to_post' );



// 修正任意文件删除漏洞
function io_filter_update_attachment_metadata($data){
    if(isset($data['thumb'])){
        $data['thumb'] = basename($data['thumb']);
    }

    return $data;
}
add_filter('wp_update_attachment_metadata', 'io_filter_update_attachment_metadata');

// 给上传的图片加上时间戳，防止大量的SQL查询
function io_filter_pre_upload($file){
    if (empty($file['md5_filename']) && io_get_option('timestamp_file_name', true)) {
        return array_merge($file, ['name' => time() . '-' . $file['name']]);
    }
    return $file;
}
add_filter('wp_handle_sideload_prefilter', 'io_filter_pre_upload');
add_filter('wp_handle_upload_prefilter', 'io_filter_pre_upload');

// 移除块编辑器
function io_remove_patterns_menu()
{
    remove_submenu_page('themes.php', 'edit.php?post_type=wp_block');
    remove_submenu_page('themes.php', 'site-editor.php?path=/patterns');
}
add_action( 'admin_menu', 'io_remove_patterns_menu' );
