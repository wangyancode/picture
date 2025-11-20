<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2024-04-27 00:08:24
 * @LastEditors: iowen
 * @LastEditTime: 2025-06-03 17:28:08
 * @FilePath: /onenav/inc/configs/theme/basic-views.php
 * @Description: 
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

$views_use_ajax = (defined('WP_CACHE') && WP_CACHE) ? '' : 'csf-depend-visible csf-depend-on';

return array(
    'title'  => '统计浏览',
    'icon'   => 'fa fa-eye',
    'fields' => array(
        array(
            'id'      => 'post_views',
            'type'    => 'switcher',
            'title'   => '访问统计',
            'label'   => '启用前先禁用WP-PostViews插件，因为功能重叠',
            'default' => true,
        ),
        array(
            'type'       => 'notice',
            'style'      => 'danger',
            'content'    => '注意：关闭“访问统计”后，以下功能会受影响！',
            'dependency' => array('post_views', '==', false)
        ),
        array(
            'id'         => 'views_n',
            'type'       => 'text',
            'title'      => '访问基数',
            'after'      => '随机访问基数，取值范围：(0~10)*访问基数<br>设置大于0的整数启用，会导致访问统计虚假，酌情开启，关闭请填0',
            'default'    => 0,
            'class'      => 'compact min',
            'dependency' => array('post_views', '==', true)
        ),
        array(
            'id'         => 'views_r',
            'type'       => 'text',
            'title'      => '访问随机计数',
            'after'      => '访问一次随机增加访问次数，比如访问一次，增加5次<br>取值范围：(1~10)*访问随机数<br>设置大于0的数字启用，可以是小数，如：0.5，但小于0.5会导致取0值<br>会导致访问统计虚假，酌情开启，关闭请填0',
            'default'    => 0,
            'class'      => 'compact min',
            'dependency' => array('post_views', '==', true)
        ),
        array(
            'id'         => 'like_n',
            'type'       => 'text',
            'title'      => '点赞基数',
            'after'      => '随机点赞基数，取值范围：(0~10)*点赞基数<br>设置大于0的整数启用，会导致点赞统计虚假，酌情开启，关闭请填0',
            'default'    => 0,
            'dependency' => array('user_center', '==', false, 'all')
        ),
        array(
            'id'      => 'leader_board',
            'type'    => 'switcher',
            'title'   => '按天记录统计数据',
            'default' => true,
        ),
        array(
            'id'         => 'details_chart',
            'type'       => 'switcher',
            'title'      => '网址详情页显示统计图表',
            'class'      => 'compact min',
            'default'    => true,
            'dependency' => array('leader_board', '==', true)
        ),
        array(
            'id'         => 'how_long',
            'type'       => 'spinner',
            'title'      => '统计数据保留天数',
            'after'      => '最少30天',
            'unit'       => '天',
            'step'       => 1,
            'default'    => 30,
            'class'      => 'compact min',
            'dependency' => array('leader_board', '==', true)
        ),
        array(
            'id'         => 'views_options',
            'type'       => 'fieldset',
            'title'      => '浏览计数设置',
            'fields'     => array(
                array(
                    'id'      => 'count',
                    'type'    => 'select',
                    'title'   => '计数来源',
                    'options' => array(
                        '0' => '所有人',
                        '1' => '只有访客',
                        '2' => '只有注册用户',
                    ),
                ),
                array(
                    'id'    => 'exclude_bots',
                    'type'  => 'switcher',
                    'title' => '排除机器人(爬虫等)',
                ),
                array(
                    'id'      => 'template',
                    'type'    => 'select',
                    'title'   => '显示模板',
                    'options' => array(
                        '0' => '正常显示计数',
                        '1' => '以千单位显示',
                    ),
                ),
                array(
                    'id'    => 'use_ajax',
                    'type'  => 'switcher',
                    'title' => '使用Ajax更新浏览次数',
                    'class' => $views_use_ajax,
                    'label' => '如果启用了静态缓存，将使用AJAX更新浏览计数，且“随机计数”失效。',
                ),
            ),
            'default'    => array(
                'count'        => '0',
                'exclude_bots' => true,
                'template'     => '0',
                'use_ajax'     => true,
            ),
            'dependency' => array('post_views', '==', true)
        ),
    )
);