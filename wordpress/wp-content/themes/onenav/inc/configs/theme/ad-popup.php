<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2024-04-27 00:33:33
 * @LastEditors: iowen
 * @LastEditTime: 2025-06-03 17:22:14
 * @FilePath: /onenav/inc/configs/theme/ad-popup.php
 * @Description: 
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

return array(
    'title'  => '弹窗轮播',
    'icon'   => 'fas fa-solar-panel',
    'fields' => array(
        array(
            'id'    => 'enable_popup',
            'type'  => 'switcher',
            'title' => '启用全局弹窗',
        ),
        array(
            'id'         => 'popup_set',
            'type'       => 'fieldset',
            'title'      => '弹窗设置',
            'fields'     => array(
                array(
                    'id'    => 'delay',
                    'type'  => 'spinner',
                    'title' => '延时',
                    'after' => '延时多少秒后显示弹窗',
                    'unit'  => '秒',
                    'step'  => 1,
                ),
                array(
                    'id'    => 'only_home',
                    'type'  => 'switcher',
                    'title' => '仅首页显示',
                    'class' => 'compact min',
                ),
                array(
                    'id'      => 'show_policy',
                    'type'    => 'radio',
                    'title'   => '显示策略',
                    'options' => array(
                        'all'   => '一直显示',
                        'one'   => '显示一次',
                        'login' => '登陆后不显示',
                    ),
                    'inline'  => true,
                    'class'   => 'compact min',
                ),
                array(
                    'id'         => 'cycle',
                    'type'       => 'spinner',
                    'title'      => '周期',
                    'after'      => '为0则每次刷新页面都会弹出',
                    'unit'       => '小时',
                    'step'       => 1,
                    'class'      => 'compact min',
                    'dependency' => array('show_policy', '==', 'all'),
                ),
                array(
                    'id'    => 'width',
                    'type'  => 'slider',
                    'title' => '宽度',
                    'min'   => 340,
                    'max'   => 1024,
                    'step'  => 10,
                    'unit'  => 'px',
                    'class' => 'compact min',
                ),
                array(
                    'id'       => 'title',
                    'type'     => 'text',
                    'title'    => '标题',
                    'subtitle' => '留空不显示',
                    'class'    => 'compact min',
                ),
                array(
                    'id'       => 'content',
                    'type'     => 'wp_editor',
                    'title'    => '弹窗内容',
                    'height'   => '200px',
                    'sanitize' => false,
                    'class'    => 'compact min',
                ),
                array(
                    'id'     => 'buts',
                    'type'   => 'group',
                    'title'  => '按钮',
                    'fields' => array(
                        array(
                            'id'    => 'url',
                            'type'  => 'link',
                            'title' => '链接',
                        ),
                        array(
                            'id'      => 'class',
                            'type'    => 'radio',
                            'title'   => '按钮样式',
                            'options' => array(
                                '深背景' => array(
                                    'vc-white'  => '白色',
                                    'vc-gray'   => '灰黑色',
                                    'vc-black'  => '黑色',
                                    'vc-red'    => '红色',
                                    'vc-green'  => '绿色',
                                    'vc-blue'   => '蓝色',
                                    'vc-yellow' => '黄色',
                                    'vc-cyan'   => '青色',
                                    'vc-violet' => '紫色',
                                    'vc-purple' => '紫红色',
                                ),
                                '浅背景' => array(
                                    'vc-l-white'  => '白色',
                                    'vc-l-gray'   => '灰黑色',
                                    'vc-l-black'  => '黑色',
                                    'vc-l-red'    => '红色',
                                    'vc-l-green'  => '绿色',
                                    'vc-l-blue'   => '蓝色',
                                    'vc-l-yellow' => '黄色',
                                    'vc-l-cyan'   => '青色',
                                    'vc-l-violet' => '紫色',
                                    'vc-l-purple' => '紫红色',
                                )
                            ),
                            'default' => 'vc-blue',
                            'inline'  => true,
                            'class'   => 'compact min',
                        ),
                    ),
                    'class'  => 'compact min',
                ),
                array(
                    'id'    => 'valid',
                    'type'  => 'switcher',
                    'title' => '有效期',
                    'label' => '设置弹窗有效期',
                    'class' => 'compact min',
                ),
                array(
                    'id'         => 'time_frame',
                    'type'       => 'date',
                    'title'      => '时间范围',
                    'from_to'    => true,
                    'settings'   => array(
                        'dateFormat'      => 'yy-mm-dd',
                        'changeMonth'     => true,
                        'changeYear'      => true,
                        'showButtonPanel' => true,
                    ),
                    'dependency' => array('valid', '==', 'true'),
                    'class'      => 'compact min',
                ),
            ),
            'default'    => array(
                'delay'       => 5,
                'only_home'   => true,
                'show_policy' => 'all',
                'cycle'       => 24,
                'width'       => 600,
                'title'       => '弹窗标题',
                'content'     => '弹窗内容',
                'buts'        => array(
                    array(
                        'url'   => array(
                            'url'    => 'https://www.iotheme.cn/',
                            'text'   => '一为主题官网',
                            'target' => '_blank',
                        ),
                        'class' => 'vc-blue',
                    ),
                ),
                'valid'       => false,
                'time_frame'  => array(
                    'from' => date('Y-m-d'),
                    'to'   => date('Y-m-d', strtotime('+1 month')),
                ),
            ),
            'class'      => 'compact min',
            'dependency' => array('enable_popup', '==', 'true', '', 'visible'),
        ),
    )
);