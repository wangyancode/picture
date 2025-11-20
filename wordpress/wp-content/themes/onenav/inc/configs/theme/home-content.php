<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2024-04-26 23:56:45
 * @LastEditors: iowen
 * @LastEditTime: 2024-10-04 01:35:19
 * @FilePath: /onenav/inc/configs/theme/home-content.php
 * @Description: 
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

$hot_list_order = array(
    'modified'      => '最新修改',
    'date'          => '最新添加',
    'views'         => '查看次数',
    'comment_count' => '评论量',
    '_down_count'   => '下载最多(APP)',
    '_like_count'   => '点赞(大家喜欢)',
    'random'        => '随机',
);


return array(
    'title'  => '首页内容', 
    'icon'   => 'fa fa-home',
    'fields' => array(
        array(
            'type'       => 'submessage',
            'style'      => 'danger',
            'content'    => '过期选项，待清理！！！！！！！！',
        ),
        array(
            'id'      => 'customize_card',
            'type'    => 'switcher',
            'title'   => '自定义网址（我的导航）', 
            'label'   => '显示游客自定义网址模块，允许游客自己添加网址和记录最近点击，数据保存于游客电脑。', 
            'default' => false,
        ),
        array(
            'id'    => 'customize_d_n',
            'type'  => 'text',
            'title' => '预设网址（每日推荐）', 
            'class' => 'compact min',
            'after' => '自定义网址模块添加预设网址，显示位置：<br>1、首页“我的导航”模块预设网址<br>2、“mini 书签页”快速导航列表<br><br>例：1,22,33,44 用英语逗号分开（填文章ID）', 
        ),
        array(
            'id'         => 'customize_show',
            'type'       => 'switcher',
            'title'      => '始终显示[预设网址（每日推荐）]', 
            'label'      => '开启用户中心后仍然显示预设网址', 
            'default'    => true,
            'class'      => 'compact min',
            'dependency' => array('customize_card', '==', true)
        ),
        array(
            'id'         => 'customize_count',
            'type'       => 'spinner',
            'title'      => '最多分类', 
            'after'      => '最多显示多少用户自定义网址分类，0 为全部显示', 
            'step'       => 1,
            'default'    => 8,
            'class'      => 'compact min',
            'dependency' => array('customize_card', '==', true)
        ),
        array(
            'id'         => 'customize_n',
            'type'       => 'spinner',
            'title'      => '最近点击', 
            'after'      => '最近点击网址记录的最大数量', 
            'max'        => 50,
            'min'        => 1,
            'step'       => 1,
            'default'    => 10,
            'class'      => 'compact min',
            'dependency' => array('customize_card', '==', true)
        ),
    )
);