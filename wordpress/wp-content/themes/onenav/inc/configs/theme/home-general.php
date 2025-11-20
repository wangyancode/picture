<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2024-04-26 23:41:24
 * @LastEditors: iowen
 * @LastEditTime: 2025-06-03 22:31:01
 * @FilePath: /onenav/inc/configs/theme/home-general.php
 * @Description: 
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

return array(
    'title'  => '首页设置',
    'icon'   => 'fas fa-laptop-house',
    'fields' => array(
        array(
            'type'    => 'submessage',
            'style'   => 'danger',
            'content' => '首页布局选项已移至<a href="' . admin_url('admin.php?page=home_module') . '">【首页布局】</a>中，请前往修改，<a href="https://www.iotheme.cn/shouyebujupeizhi.html" target="_blank">查看教程</a>。',
        ),
        array(
            'id'      => 'card_n',
            'type'    => 'fieldset',
            'title'   => '在首页分类下显示的内容数量',
            'fields'  => array(
                array(
                    'id'    => 'favorites',
                    'type'  => 'spinner',
                    'title' => '网址数量',
                    'step'  => 1,
                ),
                array(
                    'id'    => 'apps',
                    'type'  => 'spinner',
                    'title' => 'App 数量',
                    'step'  => 1,
                    'class' => 'compact min',
                ),
                array(
                    'id'    => 'books',
                    'type'  => 'spinner',
                    'title' => '书籍数量',
                    'step'  => 1,
                    'class' => 'compact min',
                ),
                array(
                    'id'    => 'category',
                    'type'  => 'spinner',
                    'title' => '文章数量',
                    'step'  => 1,
                    'class' => 'compact min',
                ),
            ),
            'default' => array(
                'favorites' => 20,
                'apps'      => 16,
                'books'     => 16,
                'category'  => 16,
            ),
            'after'   => $tip_ico . '填写需要显示的数量，如果分类包含内容大于显示数量，则显示“更多”按钮。<br>-1 为显示分类下所有网址<br>&nbsp;0 为根据<a href="' . home_url() . '/wp-admin/options-reading.php">系统设置数量显示</a>',
        ),
        array(
            'id'       => 'term_more_text',
            'type'     => 'text',
            'title'    => '更多按钮文案（多语言）',
            'sanitize' => false,
            'default'  => 'more+',
        ),
        array(
            'id'      => 'show_sticky',
            'type'    => 'switcher',
            'title'   => '置顶内容前置',
            'label'   => '首页置顶的内容显示在前面',
            'default' => false,
        ),
        array(
            'id'         => 'category_sticky',
            'type'       => 'switcher',
            'title'      => '分类&归档页置顶内容前置',
            'label'      => '注意：会导致第一页内容超过设置的显示数量',
            'default'    => false,
            'class'      => 'compact min',
            'dependency' => array('show_sticky', '==', true)
        ),
        array(
            'id'      => 'home_sort',
            'type'    => 'fieldset',
            'title'   => '分类排序',
            'fields'  => array(
                array(
                    'id'      => 'favorites',
                    'type'    => 'radio',
                    'title'   => '网址排序',
                    'inline'  => true,
                    'options' => io_get_home_content_term_order('favorites'),
                ),
                array(
                    'id'      => 'apps',
                    'type'    => 'radio',
                    'title'   => 'APP 排序',
                    'inline'  => true,
                    'options' => io_get_home_content_term_order('apps'),
                    'class'   => 'compact min',
                ),
                array(
                    'id'      => 'books',
                    'type'    => 'radio',
                    'title'   => '书籍排序',
                    'inline'  => true,
                    'options' => io_get_home_content_term_order('books'),
                    'class'   => 'compact min',
                ),
                array(
                    'id'      => 'category',
                    'type'    => 'radio',
                    'title'   => '文章排序',
                    'inline'  => true,
                    'options' => io_get_home_content_term_order('category'),
                    'class'   => 'compact min',
                ),
            ),
            'default' => array(
                'favorites' => '_sites_order',
                'apps'      => 'modified',
                'books'     => 'modified',
                'category'  => 'date',
            ),
            'after'   => '<p style="color: red">启用“查看次数”“下载次数”等排序方法请开启相关统计，如果对象没有相关数据，则不会显示。</p>',
        ),
        array(
            'id'      => 'home_sort_sort',
            'type'    => 'fieldset',
            'title'   => '分类排序规则',
            'fields'  => array(
                array(
                    'id'      => 'favorites',
                    'type'    => 'radio',
                    'title'   => '网址排序',
                    'inline'  => true,
                    'options' => array(
                        'asc'  => '升序',
                        'desc' => '降序',
                    ),
                ),
                array(
                    'id'      => 'apps',
                    'type'    => 'radio',
                    'title'   => 'APP 排序',
                    'inline'  => true,
                    'options' => array(
                        'asc'  => '升序',
                        'desc' => '降序',
                    ),
                    'class'   => 'compact min',
                ),
                array(
                    'id'      => 'books',
                    'type'    => 'radio',
                    'title'   => '书籍排序',
                    'inline'  => true,
                    'options' => array(
                        'asc'  => '升序',
                        'desc' => '降序',
                    ),
                    'class'   => 'compact min',
                ),
                array(
                    'id'      => 'category',
                    'type'    => 'radio',
                    'title'   => '文章排序',
                    'inline'  => true,
                    'options' => array(
                        'asc'  => '升序',
                        'desc' => '降序',
                    ),
                    'class'   => 'compact min',
                ),
            ),
            'default' => array(
                'favorites' => 'desc',
                'apps'      => 'desc',
                'books'     => 'desc',
                'category'  => 'desc',
            ),
            'class'   => 'new',
        ),
    )
);

function io_get_home_content_term_order($type)
{
    $home_content_order = array(
        'ID'            => 'ID',
        'modified'      => '修改日期',
        'date'          => '创建日期',
        'views'         => '查看次数',
        'comment_count' => '评论量',
        '_like_count'   => '点赞量',
        'rand'          => '随机',
    );
    if (io_user_center_enable()) {
        $home_content_order['_star_count'] = '收藏量';
    }
    if ($type == 'apps') {
        $home_content_order['_down_count'] = '下载次数';
    } elseif ($type == 'favorites') {
        $home_content_order['_sites_order'] = '自定义排序字段';
    }
    return apply_filters('io_set_home_sort_' . $type . '_filters', $home_content_order);
}
