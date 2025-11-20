<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2024-04-26 23:46:20
 * @LastEditors: iowen
 * @LastEditTime: 2025-06-03 17:27:28
 * @FilePath: /onenav/inc/configs/theme/basic-style.php
 * @Description: 
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

return array(
    'title'  => '颜色布局',
    'icon'   => 'fa fa-tachometer',
    'fields' => array(
        array(
            'id'      => 'theme_mode',
            'type'    => 'radio',
            'title'   => '颜色主题',
            'inline'  => true,
            'options' => array(
                'io-black-mode' => '暗色',
                'io-grey-mode'  => '亮灰',
            ),
            'default' => 'io-grey-mode',
            'after'   => '设置好后需清除浏览器cookie才能生效'
        ),
        array(
            'type'       => 'notice',
            'style'      => 'warning',
            'content'    => '<li style="font-size:18px;color: red">自动切换模式下【颜色主题】不能选择【暗色】</li>',
            'dependency' => array('theme_mode|theme_auto_mode', '==|not-any', 'io-black-mode|manual-theme,null')
        ),
        array(
            'id'      => 'theme_auto_mode',
            'type'    => "radio",
            'title'   => '主题切换模式',
            'options' => array(
                'null'         => '不切换(关闭切换按钮)',
                'manual-theme' => '手动模式',
                'auto-system'  => '根据系统自动切换',
                'time-auto'    => '自定义时间段',
            ),
            'default' => 'auto-system',
            'after'   => '主题最高<b>优先级</b>来自<b>用户选择</b>，也就是<b>浏览器缓存</b>，只有当用户未手动切换主题时<b>自动切换</b>才有效。<br>' . $tip_ico . '<b>根据系统自动切换</b>支持win10、win11、最新Mac OS和最新的移动端操作系统的<b>夜间模式</b>。',
            'class'   => ''
        ),
        array(
            'id'         => 'time_auto',
            'type'       => 'datetime',
            'title'      => '时间段设置',
            'from_to'    => true,
            'text_from'  => '浅色',
            'text_to'    => '深色',
            'settings'   => array(
                'noCalendar' => true,
                'enableTime' => false,
                'dateFormat' => 'H',
                'time_24hr'  => true,
                'allowInput' => true,
                'allFormat'  => 'H',
            ),
            'default'    => array(
                'from' => "7",
                'to'   => '18'
            ),
            'dependency' => array('theme_auto_mode', '==', 'time-auto'),
            'after'      => $tip_ico . '填24小时时间，如浅色：7，深色：18',
            'class'      => 'compact'
        ),
        array(
            'id'      => 'page_mode',
            'type'    => 'image_select',
            'title'   => '页面模式',
            'options' => array(
                'full' => get_theme_file_uri('/assets/images/option/op-page-full.png'),
                'box'  => get_theme_file_uri('/assets/images/option/op-page-box.png'),
            ),
            'default' => 'box',
            'class'   => 'new',
            'before'  => '<b>注意：</b>切换后需要调整内容块的显示数量',
            'after'   => '默认全屏模式，支持：全屏、居中',
        ),
        array(
            'id'      => 'mobile_header_layout',
            'type'    => "image_select",
            'title'   => '移动端导航布局',
            'options' => array(
                'header-center' => get_theme_file_uri('/assets/images/option/op_header_layout_center.png'),
                'header-left'   => get_theme_file_uri('/assets/images/option/op_header_layout_left.png'),
            ),
            'default' => "header-center",
            'class'   => '',
        ),
        array(
            'id'      => 'home_width',
            'type'    => 'slider',
            'title'   => '首页内容宽度',
            'class'   => '',
            'min'     => 1020,
            'max'     => 2200,
            'step'    => 10,
            'unit'    => 'px',
            'default' => 1600,
            'after'   => '默认 1600px',
        ),
        array(
            'id'      => 'main_width',
            'type'    => 'slider',
            'title'   => '内容页宽度',
            'min'     => 620,
            'max'     => 1800,
            'step'    => 10,
            'unit'    => 'px',
            'default' => 1260,
            'after'   => '默认 1260px',
            'class'   => 'compact min',
        ),
        array(
            'id'      => 'sidebar_width',
            'type'    => 'slider',
            'title'   => '边栏菜单宽度',
            'min'     => 120,
            'max'     => 320,
            'step'    => 10,
            'unit'    => 'px',
            'default' => 140,
            'after'   => '默认 140px',
            'class'   => 'compact min',
        ),
        array(
            'id'      => 'main_radius',
            'type'    => 'spinner',
            'title'   => '内容圆角',
            'max'     => 100,
            'min'     => 0,
            'step'    => 1,
            'default' => 12,
            'unit'    => 'px',
            'after'   => '默认 12px',
            'class'   => 'compact min',
        ),
        array(
            'id'      => 'loading_fx',
            'type'    => 'switcher',
            'title'   => '全屏加载效果',
            'default' => false,
        ),
        array(
            'id'       => 'loading_type',
            'type'     => 'image_select',
            'title'    => '加载效果',
            'options'  => array(
                'rand' => get_theme_file_uri('/assets/images/loading/load0.png'),
                '1'    => get_theme_file_uri('/assets/images/loading/load1.png'),
                '2'    => get_theme_file_uri('/assets/images/loading/load2.png'),
                '3'    => get_theme_file_uri('/assets/images/loading/load3.png'),
                '4'    => get_theme_file_uri('/assets/images/loading/load4.png'),
                '5'    => get_theme_file_uri('/assets/images/loading/load5.png'),
                '6'    => get_theme_file_uri('/assets/images/loading/load6.png'),
                '7'    => get_theme_file_uri('/assets/images/loading/load7.png'),
            ),
            'default'  => '1',
            'class'    => '',
            'subtitle' => '包括go跳转页,go跳转页不受上面开关影响',
        ),
        array(
            'id'        => 'login_ico',
            'type'      => 'upload',
            'title'     => '登录页图片',
            'add_title' => '上传',
            'default'   => get_theme_file_uri('/assets/images/login.jpg'),
        ),
        array(
            'id'         => 'login_img_type',
            'type'       => 'radio',
            'title'      => ' ',
            'subtitle'   => '图片模式',
            'options'    => array(
                'min-img' => '小图',
                'max-img' => '全屏',
            ),
            'inline'     => true,
            'class'      => 'compact min',
            'default'    => 'min-img',
            'dependency' => array('login_ico', '!=', ''),
        ),
        array(
            'id'         => 'login_color',
            'type'       => 'color_group',
            'title'      => '登录页背景色',
            'class'      => 'compact min',
            'options'    => array(
                'color-l' => '左边',
                'color-r' => '右边',
            ),
            'default'    => array(
                'color-l' => '#7d00a0',
                'color-r' => '#c11b8d',
            ),
            'dependency' => array('login_img_type', '!=', 'big-img'),
        ),
        array(
            'id'    => 'theme_color',
            'type'  => 'color',
            'title' => '主题颜色',
            'after' => '清空则使用默认颜色，请不要设置透明度',
        ),
    )
);