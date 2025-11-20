<?php 
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2021-03-01 10:19:06
 * @LastEditors: iowen
 * @LastEditTime: 2025-05-10 17:15:26
 * @FilePath: /onenav/inc/theme-start.php
 * @Description: 
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * 注册边栏.
 *
 * @since   2.0.0
 */
function io_register_sidebars(){
    $sidebars = array(
        array(
            'id'            => 'sidebar-h',
            'name'          => '博客布局侧边栏',
            'description'   => '显示在博客模板侧边栏，留空不显示。',
        ),
        array(
            'id'            => 'sidebar-s',
            'name'          => '文章正文侧边栏',
            'description'   => '显示在文章正文侧边栏',
        ),
        array(
            'id'            => 'sidebar-page',
            'name'          => '页面侧边栏',
            'description'   => '显示在页面侧边栏',
        ),
        array(
            'id'            => 'sidebar-a',
            'name'          => '分类归档侧边栏',
            'description'   => '显示在分类归档页、搜索、404页侧边栏',
        ),
        array(
            'id'            => 'sidebar-sites-r',
            'name'          => '网站详情页侧边栏',
            'description'   => '显示在网站详情页侧边栏',
        ),
        array(
            'id'            => 'sidebar-app-r',
            'name'          => '软件&资源详情页侧边栏',
            'description'   => '显示在软件&资源详情页侧边栏',
        ),
        array(
            'id'            => 'sidebar-book-r',
            'name'          => '书籍&影视详情页侧边栏',
            'description'   => '显示在书籍&影视详情页侧边栏',
        ),
        array(
            'id'            => 'sidebar-bulletin-r',
            'name'          => '公告详情页侧边栏',
            'description'   => '显示在公告详情页侧边栏',
        ),
        array(
            'id'            => 'sidebar-bull',
            'name'          => '公告归档页侧边栏',
            'description'   => '显示在公告归档页侧边栏',
        ),
    );
    /*
     * HOOK 过滤钩子
     * io_sidebar_list_filters
     * 
     * 自定义文章侧边栏ID规则 sidebar-{post_type}-r
     */
    $sidebars = apply_filters('io_sidebar_list_filters', $sidebars); 
    foreach ($sidebars as $value) {
        $default = array(
            'name'          => '',
            'id'            => '',
            'description'   => '',
            'before_widget' => '<div id="%1$s" class="card io-sidebar-widget %2$s">',
            'after_widget'  => '</div>',
            'before_title'  => '<div class="card-header widget-header"><h3 class="text-md mb-0">',
            'after_title'   => '</h3></div>',
        );
        $value = wp_parse_args($value, $default);
        register_sidebar($value);
    }
}
add_action('widgets_init', 'io_register_sidebars');

# 注册菜单
# --------------------------------------------------------------------
io_register_menus();
function io_register_menus(){
    $navs=array(
        'main_menu'   => 'PC 顶部菜单',
        'mobile_menu' => '移动端菜单(最多支持两级菜单)' 
    );
    /*
     * HOOK 过滤钩子
     * io_nav_list_filters
     */
    $navs = apply_filters('io_nav_list_filters', $navs);
    register_nav_menus($navs);
}
function io_theme_languages_setup(){
    if ('zh_CN' != get_locale()) {
        load_theme_textdomain('i_theme', get_template_directory() . '/languages');
        if (is_admin()) {
            load_theme_textdomain('io_setting', get_template_directory() . '/languages');
        }
    }
}
add_action('after_setup_theme', 'io_theme_languages_setup');

function io_theme_locale($locale){
    switch ($locale) {
        case 'en_AU':
        case 'en_GB':
        case 'en_US':
            $locale = 'en';
            break;
    }
    return $locale;
}
//add_action('determine_locale', 'io_theme_locale',10 ,1);

/**
 * 启用主题后进仪表盘 
 */
add_action('load-themes.php', 'Init_theme');
function Init_theme(){
    //强制启用伪静态
    if (!get_option('permalink_structure')) {
        update_option('permalink_structure', '/%post_id%.html');
    }
    global $pagenow;
    if ( 'themes.php' == $pagenow && isset( $_GET['activated'] ) ) {
        initialization();
        update_option( 'thumbnail_size_w',0 );
        update_option( 'thumbnail_size_h', 0 );
        update_option( 'thumbnail_crop', 0 );
        update_option( 'medium_size_w',0 );
        update_option( 'medium_size_h', 0 );
        update_option( 'large_size_w',0 );
        update_option( 'large_size_h', 0 );
        wp_redirect( io_get_admin_iocf_url() );
    }
}
# 支持自定义功能
# ------------------------------------------------------------------------------
if(!get_option('permalink_structure'))
add_action( 'admin_notices', 'webstacks_init_check' );
function webstacks_init_check(){
    $html = '<div id="notice-warning-tgmpa" class="notice notice-warning is-dismissible" style="padding: 20px 12px;background-color: #ffeacf;">
                <p>
                    <b>警告：</b> 站点固定链接没有设置，请前往设置为非第一项的选项，推荐 “/%post_id%.html”。 
                    <a href="'.admin_url( 'options-permalink.php' ).'"> 立即前往设置</a>
                </p>
            </div>';
    echo $html;
}
//add_action( 'after_switch_theme', 'active_webstacks_notice');
function active_webstacks_notice() {
    $notice = '<div id="setting-error-tgmpa" class="notice notice-info is-dismissible"> 
				<p>
					<b>通知：</b> onenav 主题已激活，鉴于之前很多用户使用时都遇到了问题，请您先去 
                    <a href="'.admin_url( 'index.php' ).'">仪表盘</a>仔细阅读使用说明，谢谢！ 
                </p> 
            <button type="button" class="notice-dismiss"><span class="screen-reader-text">忽略此通知。</span></button> 
        </div>';
    echo $notice;
}
function get_root_host($url){
    $url = strtolower($url);
    $hosts = parse_url($url);
    $host = isset($hosts['host'])?:$url;
    $data = explode('.', $host);
    $n = count($data);
    $preg = '/[\w].+\.(com|net|org|gov|edu)\.cn$/';
    if(($n > 2) && preg_match($preg,$host)){
        $host = $data[$n-3].'.'.$data[$n-2].'.'.$data[$n-1];
    }else{
        $host = $data[$n-2].'.'.$data[$n-1];
    }
    return $host;
}

global $wpdb;
$wpdb->iomessages   = $wpdb->prefix . 'io_messages';
$wpdb->iocustomurl  = $wpdb->prefix . 'io_custom_url';
$wpdb->iocustomterm = $wpdb->prefix . 'io_custom_term';
$wpdb->ioviews      = $wpdb->prefix . 'io_views';
$wpdb->iopayorder   = $wpdb->prefix . 'io_pay_order';
$wpdb->ioautoad     = $wpdb->prefix . 'io_pay_auto_ad';

require get_theme_file_path('/inc/primary.php');
require get_theme_file_path('/inc/classes/sms.class.php');
require get_theme_file_path('/vendor/autoload.php');
require get_theme_file_path('/inc/classes/io.set.modal.class.php');
require get_theme_file_path('/inc/classes/iodb.class.php');
require get_theme_file_path('/inc/classes/io.view.class.php');
require get_theme_file_path('/inc/theme-update.php');
require get_theme_file_path('/inc/classes/menuico.class.php');
require get_theme_file_path('/iopay/functions.php');
require get_theme_file_path('/inc/admin/functions.php');
require get_theme_file_path('/inc/meta-menu.php');
if(io_get_option('site_map',false))      require get_theme_file_path('/inc/classes/do.sitemap.class.php'); 

global $iodb, $ioview; 
$iodb = new IODB();
$ioview = new IOVIEW();

if (!defined('IO_PRO') || !function_exists('isActive') ){
    wp_die('禁止破解！否则冻结订单，享受完整功能与专属服务请<a href="https://www.iotheme.cn" target="_blank">购买正版</a>！', '禁止破解！', array('response'=>403));
}
getAlibabaIco('ico');

/**
 * 获取静态文件版本
 * @param string $path 
 * @param bool $v 是否添加查询key
 * @return mixed 
 */
function get_assets_version($path, $v=false){
    if (preg_match('/'. str_replace('/', '\/', get_url_root(home_url())) .'/i', $path) && !strstr($path, 'cdn.iocdn.cc')) {
        if($v){
            return '?ver='. IO_VERSION;
        }
        return IO_VERSION;
    }
    return null;
}
/**
 * 加载主题样式和脚本
 * @return void
 */
function theme_load_scripts() {
    if (is_admin())
        return;

    if (io_get_option('disable_gutenberg', false)) {
        wp_dequeue_style('wp-block-library');
        wp_dequeue_style('wp-block-library-theme');
        wp_dequeue_style('wc-block-style'); // 移除WOO插件区块样式
        wp_dequeue_style('global-styles'); // 移除 THEME.JSON
    }
    wp_deregister_script('jquery');


    $_min       = WP_DEBUG === true ? '' : '.min';
    $is_icon    = io_get_option('is_iconfont', false);
        
    $css = array(
        'bootstrap' => 'bootstrap.min.css',
        'swiper'    => 'swiper-bundle.min.css',
        'lightbox'  => 'jquery.fancybox.min.css',
        'iconfont'  => 'iconfont.css',
    );

    if ($is_icon) {
        $urls = io_get_option('iconfont_url', '');
        $urls = io_split_str($urls);
        if (!empty($urls)) {
            foreach ($urls as $index => $url) {
                $css['iconfont-io-' . $index] = $url;
            }
        }
    } else {
        //$css['font-awesome']  = '//lf9-cdn-tos.bytecdntp.com/cdn/expire-1-M/font-awesome/5.15.4/css/all.min.css';
        //$css['font-awesome4'] = '//lf6-cdn-tos.bytecdntp.com/cdn/expire-1-M/font-awesome/5.15.4/css/v4-shims.min.css';
        $css['font-awesome']  = '//cdn.bootcdn.net/ajax/libs/font-awesome/5.15.4/css/all.min.css';
        $css['font-awesome4'] = '//cdn.bootcdn.net/ajax/libs/font-awesome/5.15.4/css/v4-shims.min.css';
        //移除本地fa图标
        //$css['font-awesome']  = 'all.min.css';
        //$css['font-awesome4'] = 'v4-shims.min.css';
    }
    $css['main'] = 'main' . $_min . '.css';

    foreach ($css as $key => $value) {
        $href = $value;
        if (strstr($href, '//') === false) {
            $href = get_theme_file_uri('/assets/css/' . $value);
        }
        wp_enqueue_style($key, $href, array(), get_assets_version($href), 'all');
    }

    $js = array(
        'jquery'       => ['jquery.min.js', [], false],
        'bootstrap-js' => ['bootstrap.bundle.min.js', ['jquery'], true],
        'require'      => ['require.js', [], true],
    );
    foreach ($js as $key => $value) {
        $src = $value[0];
        if (strstr($src, '//') === false) {
            $src = get_theme_file_uri('/assets/js/' . $src);
        }
        wp_enqueue_script($key, $src, $value[1], get_assets_version($src), $value[2]);
    }

    // 注册 webuploader.html5only.js
    wp_register_script('webuploader', get_theme_file_uri('/assets/js/webuploader.html5only' . $_min . '.js'), array('jquery'), IO_VERSION, true);

    //wp_localize_script('require', 'window.theme', io_js_var());
}
/**
 * 加载主题js变量
 * @return mixed
 */
function io_js_var()
{
    $_min    = WP_DEBUG === true ? '' : '.min';
    $user_id = get_current_user_id();

    $var = array(
        'ajaxurl'        => admin_url('admin-ajax.php'),
        'uri'            => get_template_directory_uri(),
        'homeUrl'        => home_url(),
        'minAssets'      => $_min,
        'uid'            => $user_id ? base64_io_encode(sprintf("%08d", $user_id)) : '',
        'homeWidth'      => 'full' === io_get_option('page_mode', 'full') ? io_get_option('home_width', 1900) : io_get_option('main_width', 1260),
        'loginurl'       => esc_url(wp_login_url(io_get_current_url())),
        'sitesName'      => get_bloginfo('name'),
        'addico'         => get_theme_file_uri('/assets/images/add.png'),
        'order'          => get_option('comment_order'),
        'formpostion'    => 'top',
        'defaultclass'   => io_get_option('theme_mode', 'io-black-mode') == "io-black-mode" ? '' : io_get_option('theme_mode', 'io-black-mode'),
        'isCustomize'    => io_get_option('customize_card', false) ? true : false,
        'faviconApi'     => io_get_option('favicon_api', ''),
        'customizemax'   => io_get_option('customize_n', 10),
        'newWindow'      => io_get_option('new_window', false) ? true : false,
        'lazyload'       => io_get_option('lazyload', false) ? true : false,
        'minNav'         => io_get_option('min_nav', false) ? true : false,
        'loading'        => io_get_option('loading_fx', false) ? true : false,
        'hotWords'       => io_get_option('baidu_hot_words', 'baidu'),
        'classColumns'   => get_columns('sites', '', false),
        'apikey'         => iowenKey(),
        'isHome'         => (is_home() || is_front_page() || is_mininav()),
        'themeType'      => io_get_option('theme_auto_mode', 'manual-theme'),
        'mceCss'         => get_theme_file_uri('/assets/css/editor-style.css'),
        'version'        => IO_VERSION,
        'isShowAsideSub' => io_get_option('show_aside_sub', false) ? true : false,
        'asideWidth'     => io_get_option('sidebar_width', 140),
        'localize'       => io_get_js_localize(),
    );
    if (is_singular()) { // 只在文章或页面的单独内容页执行
        global $post;
        $var['postData'] = array(
            'postId'   => $post->ID,
            'postType' => get_post_type($post)
        );
    }
    return apply_filters('io_js_var', $var);
}
/**
 * 获取js本地化参数
 * @return mixed
 */
function io_get_js_localize(){
    $l10n = array(
        'liked'          => __('您已经赞过了!', 'i_theme'),
        'like'           => __('谢谢点赞!', 'i_theme'),
        'networkError'   => __('网络错误 --.', 'i_theme'),
        'parameterError' => __('参数错误 --.', 'i_theme'),
        'selectCategory' => __('为什么不选分类。', 'i_theme'),
        'addSuccess'     => __('添加成功。', 'i_theme'),
        'timeout'        => __('访问超时，请再试试，或者手动填写。', 'i_theme'),
        'lightMode'      => __('日间模式', 'i_theme'),
        'nightMode'      => __('夜间模式', 'i_theme'),
        'editBtn'        => __('编辑', 'i_theme'),
        'okBtn'          => __('确定', 'i_theme'),
        'urlExist'       => __('该网址已经存在了 --.', 'i_theme'),
        'cancelBtn'      => __('取消', 'i_theme'),
        'successAlert'   => __('成功', 'i_theme'),
        'infoAlert'      => __('信息', 'i_theme'),
        'warningAlert'   => __('警告', 'i_theme'),
        'errorAlert'     => __('错误', 'i_theme'),
        'extractionCode' => __('网盘提取码已复制，点“确定”进入下载页面。', 'i_theme'),
        'wait'           => __('请稍候', 'i_theme'),
        'loading'        => __('正在处理请稍后...', 'i_theme'),
        'userAgreement'  => __('请先阅读并同意用户协议', 'i_theme'),
        'reSend'         => __('秒后重新发送', 'i_theme'),
        'weChatPay'      => __('微信支付', 'i_theme'),
        'alipay'         => __('支付宝', 'i_theme'),
        'scanQRPay'      => __('请扫码支付', 'i_theme'),
        'payGoto'        => __('支付成功，页面跳转中', 'i_theme'),
        'clearFootprint' => __('确定要清空足迹记录吗？', 'i_theme'), 
    );
    return apply_filters('io_js_localize', $l10n);
}
/**
 * 加载验证码js
 * @return void
 */
function add_captcha_js(){
    $captcha = io_get_option('captcha_type','null');
    switch ($captcha) {
        case 'tcaptcha':
            //wp_enqueue_script( 'captcha-007','//ssl.captcha.qq.com/TCaptcha.js',array(),null,true ); //通过js加载
            break;
        case 'geetest':
            wp_enqueue_script('captcha-007', '//static.geetest.com/v4/gt4.js', array(), null, true);
            break;
        case 'vaptcha':
            wp_enqueue_script('captcha-007', '//v-cn.vaptcha.com/v3.js', array(), null, true);
            break;
        case 'slider':
            wp_localize_script('require', 'slidercaptcha', array(
                'loading' => __('加载中...', 'i_theme'),
                'retry'   => __('再试一次', 'i_theme'),
                'slider'  => __('向右滑动填充拼图', 'i_theme'),
                'failed'  => __('加载失败', 'i_theme'),
            ));
            break;
    }
}
/**
 * 加载jquery-ui
 * @return void
 */
function add_jquery_ui_js()
{
    // wp 默认的jquery-ui
    wp_enqueue_script('jquery-ui-sortable');
    wp_enqueue_script('jquery-ui-droppable');
    wp_enqueue_script('jquery-touch-punch');
}
/**
 * 加载部分后台样式和脚本
 * @param mixed $hook
 * @return void
 */
function io_admin_load_scripts($hook) {
    if( !is_admin() )return;
	if( $hook == 'post.php' || $hook == 'post-new.php' || $hook == 'toplevel_page_theme_settings' ) {
        wp_register_style( 'add-hot',  get_theme_file_uri('/assets/css/add-hot.css'), array(), IO_VERSION );
        wp_register_script( 'add-hot', get_theme_file_uri('/assets/js/add-hot.js'), array('jquery'), IO_VERSION, true );
        wp_enqueue_style('add-hot'); 
        wp_enqueue_script('add-hot');
        wp_localize_script('add-hot', 'io_news' , array(
            'ajaxurl'      => admin_url( 'admin-ajax.php' ),
            'apikey'       => iowenKey(),
        )); 
    }
}
add_action('admin_enqueue_scripts', 'io_admin_load_scripts');

function io_iocf_enqueue(){
    if(io_get_option('is_iconfont',false)){
        //wp_register_style( 'iconfont-io',  io_get_option('iconfont_url',''), array(), '' );
        //wp_enqueue_style('iconfont-io'); 
        $urls = io_split_str(io_get_option('iconfont_url',''));
        $index = 1;
        if(!empty($urls)&&is_array($urls)){
            foreach($urls as $url){
                wp_enqueue_style( 'iconfont-io-'.$index,  $url, array(), get_assets_version($url) );
                $index++;
            }
        }else{
            wp_enqueue_style( 'iconfont-io',  $urls, array(), get_assets_version($urls) );
        }
    }
    wp_enqueue_style( 'iconfont', get_theme_file_uri('/assets/css/iconfont.css') , array(), IO_VERSION );
}
add_action('iocf_enqueue', 'io_iocf_enqueue');

//为编辑器添加全局变量
add_action('wp_enqueue_editor', function () {
    echo '<script type="text/javascript">var mce = {
            is_admin:"' . is_admin() . '",
            ajax_url:"' . esc_url(admin_url('admin-ajax.php')) . '",
            post_img_allow_upload:"' . apply_filters('io_tinymce_upload_img', false) . '",
            post_img_max:"' . io_get_option('posts_img_size', 1) . '",
            upload_nonce:"' . wp_create_nonce('upload_user_attachments') . '",
            local : {
                post_img_max_msg:"' . sprintf(__('图片大小不能超过 %s MB','i_theme'),io_get_option('posts_img_size',1)) . '",
            }
        }</script>';
});
