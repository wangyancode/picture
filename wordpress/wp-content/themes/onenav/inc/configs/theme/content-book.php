<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2024-04-27 00:18:41
 * @LastEditors: iowen
 * @LastEditTime: 2025-06-04 16:54:52
 * @FilePath: /onenav/inc/configs/theme/content-book.php
 * @Description: 
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

return array(
    'title'  => '书籍设置',
    'icon'   => 'fa fa-book',
    'fields' => array(
        array(
            'id'      => 'book_card_mode',
            'type'    => 'image_select',
            'title'   => '首页图书卡片样式',
            'options' => io_get_posts_card_style('book'),
            'after'   => $tip_ico . '分类设置中的样式优先级最高，如发现此设置无效，请检查分类设置。',
            'default' => 'v1',
        ),
        array(
            'id'      => 'book_archive_n',
            'type'    => 'number',
            'title'   => '书籍分类页显示数量',
            'default' => 20,
            'after'   => '填写需要显示的数量。<br>填写 0 为根据<a href="' . home_url() . '/wp-admin/options-reading.php">系统设置数量显示</a>',
        ),
        array(
            'id'      => 'books_metadata',
            'type'    => 'group',
            'title'   => '书籍&影视元数据默认值',
            'fields'  => array(
                array(
                    'id'    => 'term',
                    'type'  => 'text',
                    'title' => '项目(控制在5个字内)',
                ),
                array(
                    'id'          => 'detail',
                    'type'        => 'text',
                    'title'       => '内容',
                    'placeholder' => '如留空，请删除此项',
                ),
            ),
            'default' => array(
                array(
                    'term' => '作者',
                ),
                array(
                    'term' => '出版社',
                ),
                array(
                    'term'   => '发行日期',
                    'detail' => date('Y-m', current_time('timestamp')),
                ),
            ),
        ),
        array(
            'id'       => 'book_columns',
            'type'     => 'fieldset',
            'title'    => '书籍列数',
            'subtitle' => '首页书籍块列表一行显示的个数',
            'before'   => '根据屏幕大小设置',
            'fields'   => io_get_screen_item_count(array(
                'sm'  => 2,
                'md'  => 3,
                'lg'  => 5,
                'xl'  => 6,
                'xxl' => 7
            )),
            'after'    => $tip_ico . '注意：如果内容没有根据此设置变化，请检查对应分类的设置。',
        ),
        array(
            'id'      => 'book_breadcrumb',
            'type'    => 'switcher',
            'title'   => '正文头部面包屑菜单',
            'default' => true,
        ),
        array(
            'id'         => 'book_top_right',
            'type'       => 'textarea',
            'title'      => '右上角内容',
            'subtitle'   => '预览图位置广告位',
            'default'    => '<a href="https://www.iotheme.cn/store/onenav.html" target="_blank"><img src="' . get_theme_file_uri('/assets/images/ad_box.jpg') . '" alt="广告也精彩" /></a>',
            'class'      => 'new',
            'sanitize'   => false,
            'after'      => '支持 HTML，注意代码规范。<br><b>注意：</b>留空则不显示。',
        ),
        array(
            'id'      => 'book_related',
            'type'    => 'switcher',
            'title'   => '相关book',
            'default' => true,
            'class'   => '',
        ),
    )
);