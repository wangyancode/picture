<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2024-04-27 00:22:28
 * @LastEditors: iowen
 * @LastEditTime: 2025-03-22 22:11:23
 * @FilePath: /onenav/inc/configs/theme/seo-basic.php
 * @Description: 
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

return array(
    'title'       => '基础设置', 
    'icon'        => 'fab fa-slack',
    'description' => '主题seo获取规则：<br>标题：页面、文章的标题<br>关键词：默认获取文章的标签，如果没有，则为标题加网址名称<br>描述：默认获取文章简介', 
    'fields'      => array(
        array(
            'id'    => 'seo_home_keywords',
            'type'  => 'text',
            'title' => '首页关键词（多语言）', 
            'after' => '其他页面如果获取不到关键词，默认调取此设置', 
        ),
        array(
            'id'    => 'seo_home_desc',
            'type'  => 'textarea',
            'title' => '首页描述（多语言）', 
            'after' => '其他页面如果获取不到描述，默认调取此设置', 
        ),
        array(
            'id'      => 'seo_linker',
            'type'    => 'text',
            'title'   => '连接符', 
            'after'   => '一般用“-”“|”，如果需要左右留空，请自己左右留空格。', 
            'default' => ' - ',
        ),
        array(
            'id'      => 'og_switcher',
            'type'    => 'switcher',
            'title'   => 'OG标签', 
            'label'   => '在头部显示OG标签', 
            'default' => true,
        ),
        array(
            'id'         => 'og_img',
            'type'       => 'upload',
            'title'      => 'og 标签默认图片', 
            'add_title'  => '上传', 
            'after'      => 'QQ、微信分享时显示的缩略图<br>主题会默认获取文章、网址等内容的图片，但是如果内容没有图片，则获取此设置', 
            'default'    => get_theme_file_uri('/screenshot.jpg'),
            'class'      => 'compact',
            'dependency' => array('og_switcher', '==', 'true'),
        ),
        array(
            'id'       => 'tag_c',
            'type'     => 'fieldset',
            'title'    => '关键词链接', 
            'subtitle' => '自动为文章中的关键词添加链接',
            'fields'   => array(
                array(
                    'id'      => 'switcher',
                    'type'    => 'switcher',
                    'title'   => '开启', 
                    'default' => true,
                ),
                array(
                    'id'         => 'tags',
                    'type'       => 'group',
                    'title'      => '自定义关键词',
                    'fields'     => array(
                        array(
                            'id'    => 'tag',
                            'type'  => 'text',
                            'title' => '关键词',
                        ),
                        array(
                            'id'    => 'url',
                            'type'  => 'text',
                            'title' => '链接地址'
                        ),
                        array(
                            'id'    => 'describe',
                            'type'  => 'text',
                            'title' => '描述',
                            'after' => '为链接title属性',
                        ),
                    ),
                    'dependency' => array('switcher', '==', 'true', '', 'visible'),
                ),
                array(
                    'id'         => 'auto',
                    'type'       => 'switcher',
                    'title'      => '自动关键词', 
                    'label'      => '自动将文章的标签当作关键词',
                    'default'    => true,
                    'dependency' => array('switcher', '==', 'true', '', 'visible'),
                ),
                array(
                    'id'         => 'chain_n',
                    'title'      => '链接数量', 
                    'default'    => '1',
                    'type'       => 'number',
                    'desc'       => '一篇文章中同一个标签最多自动链接几次，建议不大于2', 
                    'dependency' => array('switcher', '==', 'true', '', 'visible'),
                ),
            )
        ),
    )
);