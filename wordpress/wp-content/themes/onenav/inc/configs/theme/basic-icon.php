<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2024-04-26 23:42:44
 * @LastEditors: iowen
 * @LastEditTime: 2025-06-03 17:26:35
 * @FilePath: /onenav/inc/configs/theme/basic-icon.php
 * @Description: 
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

return array(
    'title'  => '图标设置',
    'icon'   => 'fa fa-star',
    'fields' => array(
        array(
            'id'        => 'logo_normal',
            'type'      => 'upload',
            'title'     => '暗色主题Logo',
            'add_title' => '上传',
            'after'     => '建议高80px，长随意',
            'default'   => get_theme_file_uri('/assets/images/logo@2x.png'),
        ),
        array(
            'id'        => 'logo_normal_light',
            'type'      => 'upload',
            'title'     => '亮色主题Logo',
            'add_title' => '上传',
            'after'     => '建议高80px，长随意',
            'default'   => get_theme_file_uri('/assets/images/logo_l@2x.png'),
            'class'     => 'compact min',
        ),
        array(
            'id'        => 'favicon',
            'type'      => 'upload',
            'title'     => '上传 Favicon',
            'add_title' => '上传',
            'default'   => get_theme_file_uri('/assets/images/favicon.png'),
            'after'     => '建议尺寸 64x64px',
            'class'     => 'compact min',
        ),
        array(
            'id'        => 'apple_icon',
            'type'      => 'upload',
            'title'     => '上传 apple_icon',
            'add_title' => '上传',
            'default'   => get_theme_file_uri('/assets/images/app-ico.png'),
            'after'     => '建议尺寸 180x180px',
            'class'     => 'compact min',
        ),
    )
);