<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2024-04-27 00:38:50
 * @LastEditors: iowen
 * @LastEditTime: 2025-06-03 17:20:57
 * @FilePath: /onenav/inc/configs/theme/ad-go.php
 * @Description: 
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

$default_ad = '<a href="https://www.iotheme.cn/store/onenav.html" target="_blank"><img src="' . get_theme_file_uri('/assets/images/ad.jpg') . '" alt="广告也精彩" /></a>';

return array(
    'title'  => 'GO 跳转广告',
    'icon'   => 'fa fa-google',
    'fields' => array(
        //下载弹窗广告位
        array(
            'id'       => 'ad_go_page_content',
            'type'     => 'fieldset',
            'title'    => 'GO 跳转页中间广告',
            'fields'   => array(
                array(
                    'id'    => 'switch',
                    'type'  => 'switcher',
                    'title' => '开关',
                ),
                array(
                    'id'         => 'loc',
                    'type'       => 'button_set',
                    'title'      => '显示位置',
                    'options'    => array(
                        '1' => 'All',
                        '2' => '仅移动端',
                        '3' => '仅PC端',
                    ),
                    'class'      => 'compact',
                    'dependency' => array('switch', '==', true)
                ),
                array(
                    'id'         => 'content',
                    'type'       => 'code_editor',
                    'title'      => '广告位内容',
                    'settings'   => array(
                        'theme' => 'dracula',
                        'mode'  => 'javascript',
                    ),
                    'sanitize'   => false,
                    'class'      => 'compact',
                    'dependency' => array('switch', '==', true)
                ),
            ),
            'sanitize' => false,
            'default'  => array(
                'switch'  => false,
                'loc'     => '1',
                'content' => $default_ad ,
            ),
        ),
    )
);