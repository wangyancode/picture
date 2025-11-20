<?php
/*!
 * Theme Name:One Nav
 * Theme URI:https://www.iotheme.cn/
 * Author:iowen
 * Author URI:https://www.iowen.cn/
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

if(!is_admin()) return;
$tip_ico = '<i class="fa fa-fw fa-info-circle"></i> ';

/**
 * 获取分类文章卡片样式
 * 
 * @param mixed $type
 * @return array
 */
function get_tex_columns($type) {
    $default = array(
        'sm'  => 2,
        'md'  => 2,
        'lg'  => 3,
        'xl'  => 5,
        'xxl' => 6,
    );
    $config = io_get_option($type . '_columns', $default);
    $config = wp_parse_args($config, $default);

    $tips = '<br>全局设置为：≥576 - <b>' . $config['sm'] . '</b>个，≥768 - <b>' . $config['md'] . '</b>个，≥992 - <b>' . $config['lg'] . '</b>个，≥1200 - <b>' . $config['xl'] . '</b>个，≥1400 - <b>' . $config['xxl'] . '</b>个';
    $fields = io_get_screen_item_count($config, $tips);

    return $fields;
}

// 文章分类SEO设置
if (class_exists('IOCF')) {
    $prefix = 'term_io_seo';

    IOCF::createTaxonomyOptions($prefix, array(
        'taxonomy'  => array('category', 'post_tag', 'favorites', 'sitetag', 'apps', 'apptag', 'books', 'booktag', 'series'),
        'data_type' => 'serialize',
    ));


    IOCF::createSection($prefix, array(
        'fields' => array(
            array(
                'type'    => 'subheading',
                'content' => __('自定义 SEO（可留空）', 'io_setting'),
            ),
            array(
                'id'    => 'seo_title',
                'type'  => 'text',
                'title' => __('自定义标题', 'io_setting'),
                'class' => 'compact min',
            ),
            array(
                'id'    => 'seo_metakey',
                'type'  => 'text',
                'title' => __('自定义关键词', 'io_setting'),
                'class' => 'compact min',
            ),
            array(
                'id'    => 'seo_desc',
                'type'  => 'textarea',
                'title' => __('自定义描述', 'io_setting'),
                'class' => 'compact min',
            ),

        )
    ));
}

// 文章分类自定义设置
if (class_exists('IOCF') && IO_PRO) {
    $terms = array(
        'category'  => 'post',
        'favorites' => 'sites',
        'apps'      => 'app',
        'books'     => 'book',
    );
    foreach ($terms as $term => $post_type) {

        $prefix = 'term_io_' . $term;

        IOCF::createTaxonomyOptions($prefix, array(
            'taxonomy'  => $term,
            'data_type' => 'unserialize',
        ));


        IOCF::createSection($prefix, array(
            'fields' => array(
                array(
                    'type'    => 'subheading',
                    'content' => '自定义选项',
                ),
                array(
                    'id'    => 'thumbnail',
                    'type'  => 'upload',
                    'title' => __('头图', 'io_setting'),
                ),
                array(
                    'id'      => 'card_mode',
                    'type'    => 'image_select',
                    'title'   => __('文章卡片样式', 'io_setting'),
                    'options' => io_get_posts_card_style($post_type, 'taxonomy'),
                    'default' => 'none',
                    'class'   => 'mid-img-select',
                ),
                array(
                    'id'         => 'columns',
                    'type'       => 'fieldset',
                    'title'      => '列数',
                    'subtitle'   => __('文章块列表一行显示的个数', 'io_setting'),
                    'fields'     => get_tex_columns($post_type),
                    'dependency' => array('card_mode', '!=', 'none'),
                ),
            )
        ));
    }
}
