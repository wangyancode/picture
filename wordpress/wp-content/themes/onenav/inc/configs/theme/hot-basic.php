<?php 
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2024-04-27 01:02:53
 * @LastEditors: iowen
 * @LastEditTime: 2024-10-10 14:31:02
 * @FilePath: /onenav/inc/configs/theme/hot-basic.php
 * @Description: 
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

return array(
    'title'  => '今日热榜', 
    'icon'   => 'fab fa-hotjar added',
    'fields' => array(
        array(
            'type'    => 'submessage',
            'style'   => 'info',
            'content' => '<h4><b>此选项卡内容留空不影响主题使用，如需要以下服务必须填。</b></h4>1、热搜榜、新闻源等卡片数据获取<br>
            <br>教程：<a href="https://www.iotheme.cn/io-api-user-manual.html"  target="_blank">api 使用手册</a>
            <br><i class="fa fa-fw fa-info-circle fa-fw"></i> 一为热榜 api 服务集成，此服务不影响主题的任何功能
            <br><i class="fa fa-fw fa-info-circle fa-fw"></i> 注意：JSON和RSS为免费服务',
        ),
        array(
            'id'    => 'iowen_key',
            'type'  => 'text',
            'title' => '一为热榜 API 激活码', 
            'after' => '一为热榜 API 为订阅服务，购买主题免费赠送一年，请先使用订单激活码<a href="//www.iotheme.cn/user?try=reg" target="_blank" title="注册域名">注册域名</a>。 如果没有购买或者过期，请访问<a href="//www.iotheme.cn/store/iowenapi.html" target="_blank" title="购买服务">iTheme</a>购买。',
        ),
        array(
            'id'      => 'is_show_hot',
            'type'    => 'switcher',
            'title'   => '使用热榜', 
            'default' => true,
            'label'   => '注意：热榜总开关，关闭后，站内不显示任何热板内容。',
        ),
        array(
            'type'       => 'callback',
            'class'      => 'csf-field-submessage',
            'function'   => 'weixin_data_html',
            'dependency' => array('is_show_hot', '==', true, '', 'visible')
        ),
        array(
            'id'         => 'hot_iframe',
            'type'       => 'switcher',
            'title'      => '热点 iframe 加载总开关', 
            'label'      => '如果开启了此选项链接还是在新窗口打开，说明对方不支持 iframe 嵌套', 
            'default'    => false,
            'dependency' => array('is_show_hot', '==', true)
        ),
        array(
            'id'                     => 'hot_home_list',
            'type'                   => 'group',
            'title'                  => '今日热榜页列表',
            'fields'                 => get_hot_list_option(),
            'before'                 => '<h4>今日热榜页显示的新闻源</h4>热榜地址：<a href="' . esc_url(home_url() . '/hotnews') . '"  target="_blank">' . esc_url(home_url() . '/hotnews') . '</a><br>最多添加30个<br><i class="fa fa-fw fa-info-circle fa-fw"></i> ID必填，ID列表：<a target="_blank" href="https://www.ionews.top/list.html">查看</a>',
            'default'                => all_topnew_list(),
            'accordion_title_number' => true,
            'max'                    => 30,
            'dependency'             => array('is_show_hot', '==', true)
        ),
    )
);