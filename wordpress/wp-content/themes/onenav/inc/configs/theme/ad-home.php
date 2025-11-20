<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2024-04-27 00:34:46
 * @LastEditors: iowen
 * @LastEditTime: 2025-06-03 17:21:42
 * @FilePath: /onenav/inc/configs/theme/ad-home.php
 * @Description: 
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

$default_ad = '<a href="https://www.iotheme.cn/store/onenav.html" target="_blank"><img src="' . get_theme_file_uri('/assets/images/ad.jpg') . '" alt="广告也精彩" /></a>';

return array(
    'title'  => '首页广告',
    'icon'   => 'fa fa-google',
    'fields' => array(
        //首页顶部广告位
        array(
            'id'       => 'ad_home_top',
            'type'     => 'fieldset',
            'title'    => '首页顶部广告位',
            'subtitle' => '注意：需关掉‘big搜索’才能显示“首页顶部广告位”内容',
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
                    'id'         => 'tow',
                    'type'       => 'switcher',
                    'title'      => '第二广告位',
                    'label'      => '大屏并排显示2个广告位，小屏幕自动隐藏',
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
                array(
                    'id'         => 'content2',
                    'type'       => 'code_editor',
                    'title'      => '第二广告位内容',
                    'subtitle'   => '第二个广告位的内容',
                    'settings'   => array(
                        'theme' => 'dracula',
                        'mode'  => 'javascript',
                    ),
                    'sanitize'   => false,
                    'class'      => 'compact',
                    'dependency' => array('switch|tow', '==|==', 'true|true')
                ),
            ),
            'sanitize' => false,
            'default'  => array(
                'switch'   => false,
                'loc'      => '1',
                'tow'      => false,
                'content'  => $default_ad,
                'content2' => $default_ad,
            ),
        ),
        //首页网址块上方广告位
        array(
            'id'       => 'ad_home_card_top',
            'type'     => 'fieldset',
            'title'    => '首页网址块上方广告位',
            'subtitle' => '网址块上方广告位',
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
                    'id'         => 'tow',
                    'type'       => 'switcher',
                    'title'      => '第二广告位',
                    'label'      => '大屏并排显示2个广告位，小屏幕自动隐藏',
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
                array(
                    'id'         => 'content2',
                    'type'       => 'code_editor',
                    'title'      => '第二广告位内容',
                    'subtitle'   => '第二个广告位的内容',
                    'settings'   => array(
                        'theme' => 'dracula',
                        'mode'  => 'javascript',
                    ),
                    'sanitize'   => false,
                    'class'      => 'compact',
                    'dependency' => array('switch|tow', '==|==', 'true|true')
                ),
            ),
            'sanitize' => false,
            'default'  => array(
                'switch'   => false,
                'loc'      => '1',
                'tow'      => false,
                'content'  => $default_ad,
                'content2' => $default_ad,
            ),
        ),
        //友链上方广告位
        array(
            'id'       => 'ad_home_link_top',
            'type'     => 'fieldset',
            'title'    => '友链上方广告位',
            'subtitle' => '首页底部友链上方广告位',
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
                    'id'         => 'tow',
                    'type'       => 'switcher',
                    'title'      => '第二广告位',
                    'label'      => '大屏并排显示2个广告位，小屏幕自动隐藏',
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
                array(
                    'id'         => 'content2',
                    'type'       => 'code_editor',
                    'title'      => '第二广告位内容',
                    'subtitle'   => '第二个广告位的内容',
                    'settings'   => array(
                        'theme' => 'dracula',
                        'mode'  => 'javascript',
                    ),
                    'sanitize'   => false,
                    'class'      => 'compact',
                    'dependency' => array('switch|tow', '==|==', 'true|true')
                ),
            ),
            'sanitize' => false,
            'default'  => array(
                'switch'   => false,
                'loc'      => '1',
                'tow'      => false,
                'content'  => $default_ad,
                'content2' => $default_ad,
            ),
        ),
        //footer 广告位
        array(
            'id'       => 'ad_footer_top',
            'type'     => 'fieldset',
            'title'    => 'footer 广告位',
            'subtitle' => '全站 footer 位广告',
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