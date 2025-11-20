<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2022-07-20 16:23:07
 * @LastEditors: iowen
 * @LastEditTime: 2025-06-03 17:20:27
 * @FilePath: /onenav/inc/configs/config-theme.php
 * @Description: 
 */
if ( ! defined( 'ABSPATH' )  ) { die; }

$prefix = 'io_get_option';
$rewrite_rules_btn = '<a href="'.add_query_arg(array('page'=>'theme_settings','action'=>'rewrite_rules'),admin_url('admin.php')).'" class="but c-yellow ml-3" style="padding:4px 8px">刷新固定连接</a>';
IOCF::createOptions( $prefix, array(
    'framework_title' => 'OneNav <small>V'.wp_get_theme()->get('Version').' <a class="ml-3 text-help" href="https://www.iotheme.cn/one-nav-zhutishouce.html" target="_blank"><i class="fab fa-hire-a-helper"></i> 使用手册</a>'.$rewrite_rules_btn.'</small>',
    'menu_title'      => '主题设置',
    'menu_slug'       => 'theme_settings', 
    'menu_position'   => 58,
    'save_defaults'   => true,
    'ajax_save'       => true,
    'show_bar_menu'   => false,
    'theme'           => 'dark',
    'class'           => 'io-option',
    'show_search'     => true,
    'footer_text'     => '运行在： WordPress '. get_bloginfo('version') .' / PHP '. PHP_VERSION,
    'footer_credit'   => '感谢您使用 <a href="https://www.iotheme.cn/" target="_blank">ioTheme</a> 的WordPress主题',
));


//
// 开始使用
//
IOCF::createSection($prefix, io_get_option_data('theme/start'));

/**
 * -----------------------------------------------------------------------
 * HOOK : ACTION HOOK
 * io_setting_option_begin_code
 * 
 * 在主题设置菜单前挂载其他内容
 * @since   
 * -----------------------------------------------------------------------
 */
do_action('io_setting_option_begin_code', $prefix, '自定义项', 'fas fa-dot-circle');


//
// 全局功能
//
IOCF::createSection( $prefix, array(
    'id'    => 'basic_setting',
    'title' => '全局功能',
    'icon'  => 'fas fa-th-large',
));

// 图标设置
IOCF::createSection($prefix, io_get_option_data('theme/basic-icon', 'basic_setting'));

// 颜色&样式
IOCF::createSection($prefix, io_get_option_data('theme/basic-style', 'basic_setting'));

// 全局功能
IOCF::createSection($prefix, io_get_option_data('theme/basic-global', 'basic_setting'));

// 首页设置-首页常规
IOCF::createSection($prefix, io_get_option_data('theme/home-general', 'basic_setting'));

// 统计浏览
IOCF::createSection($prefix, io_get_option_data('theme/basic-views', 'basic_setting'));

// 页脚设置
IOCF::createSection($prefix, io_get_option_data('theme/basic-footer', 'basic_setting'));

// 页脚设置
IOCF::createSection($prefix, io_get_option_data('theme/basic-mobile-nav', 'basic_setting'));

// 添加代码
IOCF::createSection($prefix, io_get_option_data('theme/basic-code', 'basic_setting'));


//
// 首页设置
//
//IOCF::createSection( $prefix, array(
//    'id'    => 'home_setting',
//    'title' => '首页设置',
//    'icon'  => 'fas fa-laptop-house',
//));

// 首页设置-首页常规
//IOCF::createSection($prefix, io_get_option_data('theme/home-general', 'home_setting'));

// 首页设置-首页内容
//IOCF::createSection($prefix, io_get_option_data('theme/home-content', 'home_setting'));


//
// 搜索设置
//
IOCF::createSection($prefix, io_get_option_data('theme/search-basic'));


//
// 内容页面
//
IOCF::createSection( $prefix, array(
    'id'    => 'srticle_settings',
    'title' => '内容页面',
    'icon'  => 'fa fa-file-text',
));

// 内容页面-文章博客
IOCF::createSection($prefix, io_get_option_data('theme/content-post', 'srticle_settings'));

// 内容页面-网址设置
IOCF::createSection($prefix, io_get_option_data('theme/content-sites', 'srticle_settings'));

// 内容页面-app设置
IOCF::createSection($prefix, io_get_option_data('theme/content-app', 'srticle_settings'));

// 内容页面-书籍设置
IOCF::createSection($prefix, io_get_option_data('theme/content-book', 'srticle_settings'));

// 内容页面-公告设置
IOCF::createSection($prefix, io_get_option_data('theme/content-bulletin', 'srticle_settings'));

// 内容页面-分类页面
IOCF::createSection($prefix, io_get_option_data('theme/content-taxonomy', 'srticle_settings'));

// 内容页面-排行榜单
IOCF::createSection($prefix, io_get_option_data('theme/content-ranking', 'srticle_settings'));


//
// SEO设置
//
IOCF::createSection( $prefix, array(
    'id'    => 'seo_settings',
    'title' => 'SEO设置',
    'icon'  => 'fa fa-paw',
));

// SEO-基础设置
IOCF::createSection($prefix, io_get_option_data('theme/seo-basic', 'seo_settings'));

// SEO-GO 跳转
IOCF::createSection($prefix, io_get_option_data('theme/seo-go', 'seo_settings'));

// SEO-链接规则
IOCF::createSection($prefix, io_get_option_data('theme/seo-link', 'seo_settings'));

// SEO-TDK设置
IOCF::createSection($prefix, io_get_option_data('theme/seo-tdk', 'seo_settings'));

// SEO-SiteMAP&推送
IOCF::createSection($prefix, io_get_option_data('theme/seo-sitemap', 'seo_settings'));


//
// 其他功能
//
IOCF::createSection( $prefix, array(
    'id'    => 'other',
    'title' => '其他功能',
    'icon'  => 'fa fa-flask',
));

// 其他功能 - 其他杂项
IOCF::createSection($prefix, io_get_option_data('theme/other-basic', 'other'));

// 其他功能 - 邮箱发信
IOCF::createSection($prefix, io_get_option_data('theme/other-email', 'other'));

// 其他功能 - 短信接口
IOCF::createSection($prefix, io_get_option_data('theme/other-sms', 'other'));


//
// 轮播广告
//
IOCF::createSection( $prefix, array(
    'id'    => 'add-ad',
    'title' => '轮播&广告',
    'icon'  => 'fa fa-google',
));

// 轮播广告-弹窗轮播
IOCF::createSection($prefix, io_get_option_data('theme/ad-popup', 'add-ad'));

// 轮播广告-首页广告
IOCF::createSection($prefix, io_get_option_data('theme/ad-home', 'add-ad'));

// 轮播广告-文章广告
IOCF::createSection($prefix, io_get_option_data('theme/ad-post', 'add-ad'));

// 轮播广告-GO 跳转广告
IOCF::createSection($prefix, io_get_option_data('theme/ad-go', 'add-ad'));


//
// 用户&安全
//
IOCF::createSection( $prefix, array(
    'id'    => 'user_security',
    'title' => '用户&安全',
    'icon'  => 'fa fa-street-view',
));

// 用户&安全-用户注册
IOCF::createSection($prefix, io_get_option_data('theme/user-reg', 'user_security'));

// 用户&安全-社交登录
IOCF::createSection($prefix, io_get_option_data('theme/user-social', 'user_security'));

// 用户&安全-安全相关
IOCF::createSection($prefix, io_get_option_data('theme/user-security', 'user_security'));

// 用户&安全-用户投稿
IOCF::createSection($prefix, io_get_option_data('theme/user-contribute', 'user_security'));


//
// 商城设置
//
IOCF::createSection($prefix, array(
    'id'    => 'io_pay',
    'title' => '商城设置',
    'icon'  => 'fa fa-cart-plus',
));


//
// 优化设置
//
IOCF::createSection( $prefix, array(
    'id'    => 'optimize',
    'title' => '优化设置',
    'icon'  => 'fa fa-rocket',
));

// 优化设置-禁用功能
IOCF::createSection($prefix, io_get_option_data('theme/optimize-disable', 'optimize'));

// 优化设置-优化加速
IOCF::createSection($prefix, io_get_option_data('theme/optimize-speed', 'optimize'));
 

//
// 今日热点
//
IOCF::createSection($prefix, io_get_option_data('theme/hot-basic'));

/**
 * -----------------------------------------------------------------------
 * HOOK : ACTION HOOK
 * io_setting_option_after_code
 * 
 * 在主题设置菜单后挂载其他内容
 * @since   
 * -----------------------------------------------------------------------
 */
do_action('io_setting_option_after_code', $prefix, '自定义项', 'fas fa-dot-circle');

//
// 备份
//
IOCF::createSection( $prefix, array(
    'title'       => '备份设置',
    'icon'        => 'fa fa-undo',
    'fields'      => array( 
        array(
            'type'     => 'notice',
            'style'    => 'danger',
            'content'  => '注意：仅备份主题设置，如需备份整站数据，请去服务器数据库管理中备份！',
        ),
        array(
            'type'     => 'notice',
            'style'    => 'danger',
            'content'  => '注意：仅备份主题设置，如需备份整站数据，请去服务器数据库管理中备份！',
        ),
        array(
            'type'     => 'notice',
            'style'    => 'danger',
            'content'  => '注意：仅备份主题设置，如需备份整站数据，请去服务器数据库管理中备份！',
        ),
        array(
            'type'     => 'callback',
            'class'  => 'csf-field-submessage',
            'function' => 'io_backup',
        ),
        // 备份
        array(
            'type' => 'backup',
        ),
    )
));

