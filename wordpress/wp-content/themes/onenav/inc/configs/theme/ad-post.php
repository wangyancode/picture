<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2024-04-27 00:37:13
 * @LastEditors: iowen
 * @LastEditTime: 2025-06-03 17:22:52
 * @FilePath: /onenav/inc/configs/theme/ad-post.php
 * @Description: 
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

$default_ad = '<a href="https://www.iotheme.cn/store/onenav.html" target="_blank"><img src="' . get_theme_file_uri('/assets/images/ad.jpg') . '" alt="广告也精彩" /></a>';

return array(
    'title'  => '文章广告',
    'icon'   => 'fa fa-google',
    'fields' => array(
        //正文上方广告位
        array(
            'id'       => 'ad_post_content_top',
            'type'     => 'fieldset',
            'title'    => '正文上方广告位',
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
                'content' => $default_ad,
            ),
        ),
        //正文底部广告位
        array(
            'id'       => 'ad_post_content_bottom',
            'type'     => 'fieldset',
            'title'    => '正文底部广告位',
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
                'content' => $default_ad,
            ),
        ),

        array(
            'id'       => 'ad_po',
            'type'     => 'code_editor',
            'title'    => '文章内广告短代码',
            'default'  => $default_ad,
            'settings' => array(
                'theme' => 'dracula',
                'mode'  => 'javascript',
            ),
            'sanitize' => false,
            'subtitle' => '在文章中添加短代码 [ad] 即可调用',
        ),
        //评论上方广告位
        array(
            'id'       => 'ad_comments_top',
            'type'     => 'fieldset',
            'title'    => '评论上方广告位',
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
                'content' => $default_ad,
            ),
        ),
        //下载弹窗广告位
        array(
            'id'       => 'ad_res_down_popup',
            'type'     => 'fieldset',
            'title'    => '下载弹窗广告位',
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
                'content' => $default_ad,
            ),
        ),
    )
);