<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2024-10-10 16:39:51
 * @LastEditors: iowen
 * @LastEditTime: 2024-11-16 17:21:21
 * @FilePath: /onenav/inc/configs/theme/content-ranking.php
 * @Description: 排行榜单
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

return array(
    'title'  => '排行榜单', 
    'icon'   => 'fa fa-bar-chart',
    'fields' => array(
        array(
            'type'       => 'submessage',
            'style'      => 'danger',
            'content'    => '<div style="margin: 20px 0;font-size:20px">您已关掉 <b>[按天记录统计数据]</b>，无法生成有效数据，前往开启 <a href="'.io_get_admin_iocf_url('全局功能/统计浏览').'">全局功能/统计浏览</a> 。</div>',
            'dependency' => array('leader_board', '==', 'false', 'all'),
        ),
        array(
            'id'       => 'ranking_show_list',
            'type'     => 'select',
            'title'    => '显示榜单',
            'subtitle' => '查看<a href="'.io_get_template_page_url('template-rankings.php').'" target="_blank">排行榜单</a>',
            'chosen'   => true,
            'multiple' => true,
            'sortable' => true,
            'options'  => 'get_ranking_lists',
            'default'  => array('sites', 'post', 'book', 'app'),
            'class'    => 'new',
        ),
        array(
            'id'         => 'ranking_url_go',
            'type'       => 'switcher',
            'title'      => '网址类型直达目标网址',
            'dependency' => array(
                array('ranking_show_list', 'any', 'sites'),
            ),
            'class'      => 'compact min',
        ),
        array(
            'id'         => 'ranking_url_nofollow',
            'type'       => 'switcher',
            'title'      => '使用 go 跳转和 nofollow',
            'default'    => true,
            'dependency' => array(
                array('ranking_show_list', 'any', 'sites'),
                array('ranking_url_go', '==', 'true')
            ),
            'class'      => 'compact min'
        ),
        array(
            'id'      => 'ranking_range',
            'type'    => 'select',
            'title'   => '榜单范围',
            'options' => 'get_ranking_range',
            'chosen'      => true,
            'multiple'    => true,
            'sortable'    => true,
            'default' => array('today', 'week', 'month'),
        ),
        array(
            'id'      => 'ranking_show_count',
            'type'    => 'spinner',
            'title'   => '数量',
            'after'   => '列表显示数量', 
            'step'    => 1,
            'default' => 10,
        ),
    )
);
