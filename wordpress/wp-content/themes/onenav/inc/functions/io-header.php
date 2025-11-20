<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2024-08-20 17:50:15
 * @LastEditors: iowen
 * @LastEditTime: 2025-06-04 16:49:09
 * @FilePath: /onenav/inc/functions/io-header.php
 * @Description: 
 */


function io_header()
{
    if (is_bookmark()) {
        return;
    }
    io_loading_fx();
    io_show_header();
    io_show_mobile_header();

    /**
     * -----------------------------------------------------------------------
     * HOOK : ACTION HOOK
     * io_content_header_after_code
     * 
     * 在内容顶部菜单下后挂载其他内容
     * @since   
     * -----------------------------------------------------------------------
     */
    do_action('io_content_header_after_code', get_queried_object_id());
}

function io_loading_fx(){
    //蜘蛛爬虫则不显示
    if (io_is_spider()) {
        return;
    }
    
    if (io_get_option('loading_fx', false)) {
        echo '<div id="loading_fx">';
        loading_type();
        echo '<script type="text/javascript"> document.addEventListener("DOMContentLoaded",()=>{const loader=document.querySelector("#loading_fx");if(loader){loader.classList.add("close");setTimeout(()=>loader.remove(),600)}}); </script>';
        echo '</div>';
    }
}

/**
 * 显示头部导航栏
 * @return void
 */
function io_show_header(){
    $more = '';
    $logo_class = '';
    $nav_menu_list = io_get_option('nav_menu_list', '');
    if (!empty($nav_menu_list)) {
        $logo_class = 'more-menu-logo';
        $more       = '<div class="more-menu-list"><i></i><i></i><i></i><i></i></div>';
        $more .= io_get_min_nav_menu_btn($nav_menu_list);
    }
    
    echo '<header class="main-header header-fixed">';
    echo '<div class="header-nav blur-bg">';
    echo '<nav class="switch-container container-header nav-top ' . io_get_option('mobile_header_layout', 'header-center') . ' d-flex align-items-center h-100' . get_page_mode_class() . '">';

    echo '<div class="navbar-logo d-flex mr-4">';
    echo io_get_logo_html();
    echo '<div class="' . $logo_class . '">';
    echo $more;
    echo '</div>';
    echo '</div>';

    if (io_get_option('weather', false) && io_get_option('weather_location', 'header') == 'header') {
        echo '<div class="header-weather d-none d-md-block mr-4">';
        $placeholder = '<div class="header-weather-p"><span></span><span></span><span></span></div>';
        io_get_weather_widget($placeholder);
        echo '</div>';
    }
    echo '<div class="navbar-header-menu">';
    
    echo '<ul class="nav navbar-header d-none d-md-flex mr-3">';
    io_nav_menu('main_menu');
    echo '<li class="menu-item io-menu-fold hide"><a href="javascript:void(0);"><i class="iconfont icon-dian"></i></a><ul class="sub-menu"></ul></li>';
    echo '</ul>';
    echo '</div>';

    echo '<div class="flex-fill"></div>';

    /**
     * -----------------------------------------------------------------------
     * HOOK : ACTION HOOK
     * io_header_after_code
     * 
     * 顶部菜单后面挂载其他内容
     * @since 5.0
     * -----------------------------------------------------------------------
     */
    do_action( 'io_header_after_code' , get_queried_object_id() ); 

    echo '<ul class="nav header-tools position-relative">';
    if (io_get_option('hitokoto', false)) {
        echo '<li class="nav-item mr-2 d-none d-xxl-block">';
        echo '<div class="text-sm line1">';
        echo io_get_option('hitokoto_code', '');
        echo '</div>';
        echo '</li>';
    }

    if (io_get_option('nav_login', false)) {
        $login_url = esc_url(wp_login_url(io_get_current_url()));
        if(is_user_logged_in()){
            $login_url = 'javascript:;';
        }
        $menu_user_box = io_get_menu_user_box();
        $menu_user_box = $menu_user_box ? '<ul class="sub-menu mt-5">' . $menu_user_box . '</ul>' : '';

        echo '<li class="header-icon-btn nav-login d-none d-md-block">';
        echo '<a href="' . $login_url . '"><i class="iconfont icon-user icon-lg"></i></a>';
        echo $menu_user_box;
        echo '</li>';
    }
    
    // 搜索按钮
    echo '<li class="header-icon-btn nav-search">';
    echo '<a href="javascript:" class="search-ico-btn nav-search-icon" data-toggle-div data-target="#search-modal" data-z-index="101"><i class="search-bar"></i></a>';
    echo '</li>';

    echo '</ul>';

    echo '<div class="d-block d-md-none menu-btn" data-toggle-div data-target=".mobile-nav" data-class="is-mobile" aria-expanded="false">';
    echo '<span class="menu-bar"></span>';
    echo '<span class="menu-bar"></span>';
    echo '<span class="menu-bar"></span>';
    echo '</div>';
    echo '</nav>';
    echo '</div>';
    echo '</header>';
}


function io_show_mobile_header(){

    $html = '<div class="mobile-header">';
    $html .= '<nav class="mobile-nav">';
    $html .= '<ul class="menu-nav mb-4">';
    $html .= io_nav_menu('mobile_menu', false);
    $html .= '</ul>';
    $html .= io_get_menu_user_box('mb-4');
    $html .= '</nav>';
    $html .= '</div>';

    echo $html;
}


function io_get_logo_html($h1 = false)
{
    $logo_light = io_get_option('logo_normal_light', '');
    $logo_dark  = io_get_option('logo_normal', '');
    $blog_name  = get_bloginfo('name');
    $is_home    = (is_home() || is_front_page() || is_mininav());
    $html       = '';

    if ($is_home || $h1) {
        $html .= '<h1 class="text-hide position-absolute">' . $blog_name . '</h1>';
    }

    $html .= '<a href="' . esc_url(home_url()) . '" class="logo-expanded">';
    if(is_io_login()){
        $html .= '<img src="' . $logo_dark . '" height="42" alt="' . $blog_name . '">';
    } else {
        if (theme_mode() == "io-black-mode") {
            $html .= '<img src="' . $logo_dark . '" height="36" switch-src="' . $logo_light . '" is-dark="true" alt="' . $blog_name . '">';
        } else {
            $html .= '<img src="' . $logo_light . '" height="36" switch-src="' . $logo_dark . '" is-dark="false" alt="' . $blog_name . '">';
        }
    }
    $html .= '</a>';

    return $html;
}

/**
 * 获取 header 内容
 * @return void
 */
function io_get_head_content(){
    io_auto_theme_mode();
    io_custom_theme_css();

    //自定义头部代码
    echo io_get_option('code_2_header','');
}
add_action('wp_head','io_get_head_content');

function io_auto_theme_mode(){
    if(get_query_var('bookmark_id')){
        return '';
    }
    $auto_mode = io_get_option('theme_auto_mode', 'manual-theme');
    if ($auto_mode=='auto-system' || (defined( 'WP_CACHE' ) && WP_CACHE && $auto_mode!='null')) {
        $ars = '';
        if($auto_mode=='auto-system')
            $ars = ' || (!__night && window.matchMedia("(prefers-color-scheme: dark)").matches)';
        echo '<script>
    var __default_c = "'. io_get_option('theme_mode','') .'";
    var __night = document.cookie.replace(/(?:(?:^|.*;\s*)io_night_mode\s*\=\s*([^;]*).*$)|^.*$/, "$1"); 
    try {
        if (__night === "0"'.$ars.') {
            document.documentElement.classList.add("io-black-mode");
        }
    } catch (_) {}
</script>';
    }
}

function io_custom_theme_css() {
    $root  = '';
    $color = io_get_option('theme_color', '');
    if (!empty($color)) {
        $rgb = extractRGB($color);
        $root .= '--theme-color:' . $color . ';';
        $root .= '--theme-color-rgb:' . $rgb['r'] . ',' . $rgb['g'] . ',' . $rgb['b'] . ';';
        $root .= '--hover-color:' . get_color_deepen($color) . ';';
        $root .= '--focus-shadow-color:' . set_color_opacity(get_color_deepen($color), 0.6) . ';';
    }
    if (io_get_option('sidebar_width', '')) {
        $root .= '--main-aside-basis-width:' . io_get_option('sidebar_width', 218) . 'px;';
    }
    if (io_get_option('home_width', '')) {
        $root .= '--home-max-width:' . io_get_option('home_width', 1900) . 'px;';
    }
    
    if (io_get_option('main_radius', '') >= 0) {
        $root .= '--main-radius:' . io_get_option('main_radius', 12) . 'px;';
    }
    $page_mode = io_get_option('page_mode', 'full');
    if('box' === $page_mode && (is_home() || is_front_page() || is_mininav()) ){
        $root .= '--main-max-width:' . io_get_option('home_width', 1900) . 'px;';
    }else{
        $root .= '--main-max-width:' . io_get_option('main_width', 1260) . 'px;';
    }
    
    $css = '';
    if (!empty($root)) {
        $css .= ':root{' . $root . '}';
    }
    if (io_get_option("custom_css", '')) {
        $css .= substr(io_get_option("custom_css", ''), 0);
    }
    echo "<style>" . $css . "</style>";
}
