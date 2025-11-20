<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2024-10-07 21:56:32
 * @LastEditors: iowen
 * @LastEditTime: 2025-03-25 21:26:37
 * @FilePath: /onenav/inc/configs/theme/content-taxonomy.php
 * @Description: 
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

return array(
    'title'  => '分类页面', 
    'icon'   => 'fa fa-folder-open-o',
    'fields' => array(
        array(
            'id'      => 'taxonomy_head_bg',
            'type'    => 'upload',
            'title'   => '分类页头部默认图片', 
            'desc'    => '分类页头部背景图片，建议尺寸：1920*300',
            'default' => get_theme_file_uri('/assets/images/banner/banner015.jpg'),
        ),
        array(
            'id'      => 'taxonomy_head_type',
            'type'    => 'radio',
            'title'   => '分类页头部样式', 
            'options' => array(
                'none'      => '无图', 
                'fill'      => '全宽', 
                'card'      => '卡片', 
                'card-blur' => '模糊卡片', 
            ),
            'default' => 'fill',
            'inline'  => true,
            'class'   => 'compact min',
        ),
        array(
            'id'      => 'taxonomy_cat_by',
            'type'    => 'switcher',
            'title'   => '分类筛选', 
            'default' => 'true',
            'label'   => '开启后显示分类列表，用于筛选文章',
        ),
        array(
            'id'          => 'taxonomy_select_by',
            'type'        => 'select',
            'title'       => '排序筛选',
            'options'     => 'get_select_by',
            'chosen'      => true,
            'multiple'    => true,
            'sortable'    => true,
            'default'     => array('date', 'modified', 'views', 'like'),
            'placeholder' => '选择筛选项',
            'after'       => '选择显示筛选项，留空则不显示',
        ),
        array(
            'id'      => 'ajax_next_page',
            'type'    => 'switcher',
            'title'   => 'ajax 加载下一页', 
            'default' => 'true',
            'class'   => 'new'
        ),
    )
);
