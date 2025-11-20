<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2024-04-27 00:59:59
 * @LastEditors: iowen
 * @LastEditTime: 2024-04-27 01:00:05
 * @FilePath: /onenav/inc/configs/theme/optimize-speed.php
 * @Description: 
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

return array(
    'title'  => '优化加速', 
    'icon'   => 'fa fa-envira',
    'fields' => array(
        array(
            'type'    => 'submessage',
            'style'   => 'danger',
            'content' => '<p style="font-size:18px">' . $tip_ico . '注意</p>
            <p>如果不了解下面选项的作用，请保持原样！</p>',
        ),
        array(
            'id'      => 'remove_head_links',
            'type'    => 'switcher',
            'title'   => '移除头部代码', 
            'label'   => 'WordPress会在页面的头部输出了一些link和meta标签代码，这些代码没什么作用，并且存在安全隐患，建议移除WordPress页面头部中无关紧要的代码。', 
            'default' => true
        ),

        array(
            'id'      => 'remove_admin_bar',
            'type'    => 'switcher',
            'title'   => '移除admin bar', 
            'label'   => 'WordPress用户登录的情况下会出现Admin Bar，此选项可以帮助你全局移除工具栏，所有人包括管理员都看不到。', 
            'default' => true
        ),
        array(
            'id'      => 'ioc_category',
            'type'    => 'switcher',
            'title'   => '去除分类标志', 
            'label'   => '去除链接中的分类category标志，有利于SEO优化，每次开启或关闭此功能，都需要重新保存一下<a href="' . admin_url('options-permalink.php') . '">固定链接</a>！', 
            'default' => true
        ),
        array(
            'id'      => 'gravatar_cdn',
            'type'    => 'radio',
            'title'   => 'Gravatar头像加速',
            'inline'  => true,
            'options' => array(
                'gravatar' => 'Gravatar 官方服务',
                'cravatar' => 'Cravatar 国内镜像',
                'iocdn'    => '一为云 加速服务（cdn.iocdn.cc）',
            ),
            'default' => 'iocdn',
            'after'   => '自定义修改：inc/wp-optimization.php',
        ),
        array(
            'id'      => 'remove_help_tabs',
            'type'    => 'switcher',
            'title'   => '移除帮助按钮', 
            'label'   => '移除后台界面右上角的帮助', 
            'default' => false
        ),
        array(
            'id'      => 'remove_screen_options',
            'type'    => 'switcher',
            'title'   => '移除选项按钮', 
            'label'   => '移除后台界面右上角的选项', 
            'default' => false
        ),
        array(
            'id'      => 'no_admin',
            'type'    => 'switcher',
            'title'   => '禁用 admin', 
            'label'   => '禁止使用 admin 用户名尝试登录 WordPress。', 
            'default' => false
        ),
        array(
            'id'      => 'compress_html',
            'type'    => 'switcher',
            'title'   => '压缩 html 源码', 
            'label'   => '压缩网站源码，提高加载速度。（如果启用发现网站布局错误，请禁用。）', 
            'default' => false
        ),
    )
);