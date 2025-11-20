<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2024-04-27 00:11:35
 * @LastEditors: iowen
 * @LastEditTime: 2025-06-03 17:30:06
 * @FilePath: /onenav/inc/configs/theme/content-post.php
 * @Description: 
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

return array(
    'title'  => '文章博客',
    'icon'   => 'fas fa-newspaper',
    'fields' => array(
        array(
            'id'      => 'post_card_mode',
            'type'    => 'image_select',
            'title'   => '首页文章卡片样式',
            'options' => io_get_posts_card_style('post'),
            'after'   => $tip_ico . '分类设置中的样式优先级最高，如发现此设置无效，请检查分类设置。',
            'default' => 'card',
        ),
        array(
            'id'      => 'post_list_meta',
            'type'    => 'checkbox',
            'title'   => '文章列表元数据',
            'options' => array(
                '左:' => array(
                    'author' => '作者',
                    'date'   => '日期',
                ),
                '右:' => array(
                    'views'   => '浏览量',
                    'comment' => '评论数',
                    'like'    => '点赞数',
                )
            ),
            'default' => array('author', 'date', 'views', 'comment', 'like'),
            'inline'  => true,
            'after'   => '文章列表页显示文章元数据，如：作者、日期、浏览量、评论数等。不一定所有样式都会显示，具体显示内容以实际为准。',
        ),
        array(
            'id'      => 'post_keywords_meta',
            'type'    => 'checkbox',
            'title'   => '文章卡片 KEY',
            'options' => array(
                'category' => '分类',
                'post_tag' => '标签',
            ),
            'default' => array('category', 'post_tag'),
            'inline'  => true,
        ),
        array(
            'id'          => 'blog_index_cat',
            'type'        => 'select',
            'title'       => '博客页分类筛选',
            'chosen'      => true,
            'ajax'        => true,
            'multiple'    => true,
            'options'     => 'categories',
            'placeholder' => '搜索并选择文章分类',
            'class'       => '',
        ),
        array(
            'id'                     => 'post_copyright_multi',
            'type'                   => 'group',
            'title'                  => '版权提示内容',
            'fields'                 => array(
                array(
                    'id'    => 'language',
                    'type'  => 'text',
                    'title' => '语言缩写',
                    'after' => '如：zh  en ，<a href="https://zh.wikipedia.org/wiki/ISO_639-1" target="_blank">各国语言缩写参考</a>'
                ),
                array(
                    'id'         => 'content',
                    'type'       => 'textarea',
                    'title'      => '内容',
                    'desc'       => '支持HTML代码，请注意代码规范及标签闭合',
                    'attributes' => array(
                        'rows' => 2,
                    ),
                    'sanitize'   => false,
                    'class'      => 'compact min',
                ),
            ),
            'before'                 => '需在基础设置开启多语言(默认语言放第一个)',
            'button_title'           => '添加语言',
            'accordion_title_prefix' => '语言：',
            'default'                => array(
                array(
                    'language' => 'zh',
                    'content'  => '文章版权归作者所有，未经允许请勿转载。',
                ),
                array(
                    'language' => 'en',
                    'content'  => 'The copyright of the article belongs to the author, please do not reprint without permission.',
                ),
            ),
        ),
        array(
            'id'       => 'post_columns',
            'type'     => 'fieldset',
            'title'    => '文章列数',
            'subtitle' => '首页文章块列表一行显示的个数',
            'before'   => '根据屏幕大小设置',
            'fields'   => io_get_screen_item_count(array(
                'sm'  => 1,
                'md'  => 2,
                'lg'  => 3,
                'xl'  => 5,
                'xxl' => 6
            )),
            'after'    => $tip_ico . '注意：如果内容没有根据此设置变化，请检查对应分类的设置。',
        ),
        array(
            'id'      => 'post_breadcrumb',
            'type'    => 'switcher',
            'title'   => '正文头部面包屑菜单',
            'default' => true,
            'class'   => 'new'
        ),
        array(
            'id'      => 'post_related',
            'type'    => 'switcher',
            'title'   => '相关文章',
            'default' => true,
        ),
    )
);
