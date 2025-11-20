<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2021-11-26 23:51:17
 * @LastEditors: iowen
 * @LastEditTime: 2024-10-07 00:38:23
 * @FilePath: /onenav/inc/widgets/w.hot.api.php
 * @Description: 
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

$default_data = array(
    'name'      => '百度热点',
    'hot_type'  => 'api',
    'rule_id'   => 100000,
    'icon'      => get_theme_file_uri('/assets/images/hotico/baidu.png'),
    'is_iframe' => 0,
);

IOCF::createWidget('iow_hot_api', array(
    'title'       => 'IO 今日热榜',
    'classname'   => 'io-widget-hot-api',
    'description' => '按条件显示热门网址，可选“浏览数”“点赞收藏数”“评论量”',
    'fields'      => array(
        array(
            'id'      => 'type',
            'type'    => 'button_set',
            'title'   => '显示模式',
            'options' => array(
                'tab'  => 'TAB',
                'card' => '卡片',
            ),
            'inline'  => true,
            'default' => 'tab',
        ),
        array(
            'id'           => 'tabs',
            'type'         => 'group',
            'fields'       => get_hot_list_option($default_data),
            'button_title' => '添加热榜',
        )
    )
));

function iow_hot_api($args, $instance) {
    $type = $instance['type'];
    $tabs = $instance['tabs'];

    if (empty($tabs)) {
        return;
    }
    switch ($type) {
        case 'tab':
            $html = io_get_hot_api_tab_html($tabs);
            break;
        case 'card':
            $html = io_get_hot_api_cards_html($tabs);
            break;
    }
    
    echo $args['before_widget'];
    echo $html;
    //hot_search($instance);
    echo $args['after_widget'];
}

