<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2024-10-02 00:51:48
 * @LastEditors: iowen
 * @LastEditTime: 2025-06-03 17:27:03
 * @FilePath: /onenav/inc/configs/theme/basic-mobile-nav.php
 * @Description: 
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

return array(
    'title'  => '手机底部',
    'icon'   => 'fa fa-tablet',
    'fields' => array(
        array(
            'id'      => 'mobile_footer_nav_s',
            'type'    => 'switcher',
            'title'   => '手机底部Tab',
            'default' => true,
            'class'   => 'new',
        ),
        array(
            'id'      => 'footer_nav_scroll_hide',
            'type'    => 'switcher',
            'title'   => '页面滚动时隐藏',
            'default' => false,
            'class'   => 'compact min',
        ),
        array(
            'id'      => 'mobile_footer_nav_type',
            'type'    => 'radio',
            'title'   => '显示样式',
            'options' => array(
                'box'   => '常规',
                'round' => '圆角',
            ),
            'inline'  => true,
            'default' => 'round',
            'class'   => 'compact min',
        ),
        array(
            'id'                     => 'mobile_footer_nav',
            'type'                   => 'group',
            'title'                  => '按钮',
            'accordion_title_number' => true,
            'button_title'           => '添加按钮',
            'sanitize'               => false,
            'fields'                 => array(
                array(
                    'id'      => 'type',
                    'type'    => 'button_set',
                    'title'   => '按钮类型',
                    'options' => array(
                        'home'   => '首页',
                        'link'   => '自定义链接',
                        'user'   => '用户中心',
                        'add'    => '投稿',
                        'minnav' => '子页面导航',
                    ),
                    'default' => 'link',
                ),
                array(
                    'type'       => 'submessage',
                    'style'      => 'info',
                    'content'    => '<b>子页面导航</b>的内容去 <a href="' . io_get_admin_iocf_url('全局功能/基础设置') . '">全局功能/基础设置 > 子页面导航</a> 里添加',
                    'dependency' => array('type', '==', 'minnav'),
                ),
                array(
                    'id'         => 'link',
                    'type'       => 'text',
                    'title'      => '链接地址',
                    'default'    => '',
                    'dependency' => array('type', '==', 'link'),
                ),
                array(
                    'id'         => 'go_top',
                    'type'       => 'switcher',
                    'title'      => '返回顶部按钮',
                    'default'    => true,
                    'label'      => '页面滚动到下方时候，切换显示为返回顶部按钮',
                    'dependency' => array('type', '==', 'home'),
                ),
                array(
                    'id'         => 'go_top_text',
                    'type'       => 'text',
                    'title'      => ' ',
                    'subtitle'   => '返回顶部按钮文字',
                    'class'      => 'compact min',
                    'default'    => '',
                    'dependency' => array('type|go_top', '==|==', 'home|true'),
                ),
                array(
                    'id'          => 'btns',
                    'type'        => 'select',
                    'title'       => '选择按钮',
                    'placeholder' => '请选择按钮',
                    'options'     => 'io_get_post_type_name',
                    'default'     => array('post'),
                    'chosen'      => true,
                    'multiple'    => true,
                    'sortable'    => true,
                    'dependency'  => array('type', '==', 'add'),
                ),
                array(
                    'id'      => 'text',
                    'type'    => 'text',
                    'title'   => '按钮文字',
                    'default' => '',
                ),
                array(
                    'id'         => 'badge',
                    'type'       => 'text',
                    'title'      => '按钮徽章',
                    'desc'       => '显示在按钮右上角的红色徽章',
                    'dependency' => array('type', 'not-any', 'add,minnav'),
                ),
                array(
                    'id'         => 'icon',
                    'type'       => 'icon',
                    'title'      => '按钮图标',
                    'dependency' => array('icon_c', '==', '', '', 'visible'),
                ),
                array(
                    'id'         => 'icon_c',
                    'type'       => 'textarea',
                    'title'      => ' ',
                    'subtitle'   => '自定义图标代码',
                    'class'      => 'compact min',
                    'sanitize'   => false,
                    'attributes' => array(
                        'rows' => 2,
                    ),
                    'after'      => '支持图片 http(s):// 、图标库 iconfont icon- 、SVG代码 等',
                ),
                array(
                    'id'         => 'go_top_icon',
                    'type'       => 'textarea',
                    'title'      => ' ',
                    'subtitle'   => '自定义返回顶部图标代码',
                    'default'    => '',
                    'attributes' => array(
                        'rows' => 1,
                    ),
                    'dependency' => array('type|go_top', '==|==', 'home|true'),
                ),
                array(
                    'id'      => 'icon_size',
                    'type'    => 'slider',
                    'title'   => '图标大小',
                    'default' => 24,
                    'max'     => 50,
                    'min'     => 16,
                    'step'    => 1,
                    'unit'    => 'px',
                ),
            ),
            'default'                => array(
                array(
                    'type'        => 'home',
                    'icon'        => 'iconfont icon-home',
                    'icon_size'   => 24,
                    'go_top'      => true,
                    'text'        => '首页',
                    'go_top_text' => '返回顶部',
                ),
                array(
                    'type'      => 'minnav',
                    'icon'      => 'iconfont icon-fenlei',
                    'icon_size' => 24,
                    'text'      => '导航',
                ),
                array(
                    'type'      => 'add',
                    'icon'      => 'iconfont icon-creation',
                    'icon_size' => 24,
                    'btns'      => array('post', 'sites'),
                    'text'      => '投稿',
                ),
                array(
                    'type'      => 'user',
                    'icon'      => 'iconfont icon-user',
                    'icon_size' => 24,
                    'text'      => '我的',
                ),
            ),
        ),
    ),
);