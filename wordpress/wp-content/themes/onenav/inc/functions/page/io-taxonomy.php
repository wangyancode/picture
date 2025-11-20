<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2024-10-07 21:19:34
 * @LastEditors: iowen
 * @LastEditTime: 2025-06-03 21:53:33
 * @FilePath: /onenav/inc/functions/page/io-taxonomy.php
 * @Description: 
 */

/**
 * 获取分类页头部HTML
 * @param mixed $post_type 文章类型，post、sites、app、book
 * @param mixed $taxonomy 分类法 slug 名称, favorites、sitetag、apptag、booktag ...
 * @return string|void
 */
function io_get_taxonomy_head_html($post_type, $taxonomy, $echo = true)
{
    $type = io_get_option('taxonomy_head_type', 'fill');
    if ('none' === $type) {
        return;
    }
    $default_bg = io_get_option('taxonomy_head_bg', '');

    $taxonomy_id = get_queried_object_id();
    $bg          = get_term_meta($taxonomy_id, 'thumbnail', true);
    $bg          = get_lazy_img_bg($bg ? $bg : $default_bg);
    $class       = 'fill' === $type ? '' : ' container';
    $img_class   = 'card' === $type ? '' : ' bg-blur';


    $html = '<div class="taxonomy-head taxonomy-' . $post_type . $class . '">';
    $html .= '<div class="taxonomy-head-body taxonomy-head-' . $type . '">';
    $html .= '<div class="taxonomy-head-bg">';
    $html .= '<div class="taxonomy-head-img' . $img_class . '" ' . $bg . '></div>';
    $html .= '</div>';
    $html .= io_get_taxonomy_head_content_html($post_type, $taxonomy, $taxonomy_id, 'taxonomy-head-content page-head-content p-3', false);
    $html .= '</div>';
    $html .= '</div>';

    if ($echo) {
        echo $html;
    } else {
        return $html;
    }
}
/**
 * 获取分类页头部标题、描述、文章数量HTML
 * @param mixed $post_type
 * @param mixed $taxonomy_id
 * @param mixed $taxonomy
 * @param mixed $class
 * @param mixed $echo
 * @return string|void
 */
function io_get_taxonomy_head_content_html($post_type, $taxonomy, $taxonomy_id = 0, $class = '', $echo = true)
{
    if (empty($taxonomy_id)) {
        $type = io_get_option('taxonomy_head_type', 'fill');
        if ('none' !== $type) {
            return;
        }
        $taxonomy_id = get_queried_object_id();
    }

    $mane = single_term_title('', false);
    $desc = strip_tags(term_description());

    // 获取当前分类文章数量,包含子分类
    $count = get_cat_all_post_count($taxonomy_id, $taxonomy);//get_term($taxonomy_id)->count;

    $html = '<div class="taxonomy-title ' . $class . '">';
    $html .= '<h1 class="taxonomy-head-title h3">' . $mane . '</h1>';
    $html .= '<div class="taxonomy-head-count text-xs badge vc-l-white mb-1"><i class="iconfont icon-post mr-1"></i>' . sprintf(__('共 %s 篇%s ', 'i_theme'), $count, io_get_post_type_name($post_type)) . '</div>';
    $html .= '<div class="taxonomy-head-desc line2 text-sm">' . $desc . '</div>';
    $html .= '</div>';

    if ($echo) {
        echo $html;
    } else {
        return $html;
    }
}
/**
 * 获取分类页筛选HTML
 * @param mixed $post_type
 * @param mixed $taxonomy
 * @param mixed $echo
 * @return string|void
 */
function io_get_taxonomy_select_html($post_type, $taxonomy, $echo = true)
{
    $is_cat = is_taxonomy_cat($taxonomy) && io_get_option('taxonomy_cat_by', true);

    $cat = '';
    if ($is_cat) {
        $cat = get_taxonomy_cat_list_html($taxonomy);
    }

    $select_by = io_get_option('taxonomy_select_by', array());
    $select    = '';
    if ($select_by) {
        foreach ($select_by as $v) {
            $link   = get_term_link(get_queried_object_id());
            $link   = add_query_arg('orderby', $v, $link);
            $select .= '<a class="list-select ajax-posts-load is-tab-btn" href="' . esc_url($link) . '" ajax-method="card" data-type="' . $v . '">' . get_select_by($v) . '</a>';
        }
        if (!empty($select)) {
            $select = '<div class="list-select-line"></div><div class="list-selects no-scrollbar">' . $select . '</div>';
            $select = '<div class="d-flex align-items-center white-nowrap"><div class="list-select-title">' . __('排序', 'i_theme') . '</div>' . $select . '</div>';
        }
    }

    if (empty($cat) && empty($select)) {
        return;
    }

    $html = '<div class="taxonomy-selects card selects-box">';
    $html .= $cat;
    $html .= $select;
    $html .= '</div>';

    if ($echo) {
        echo $html;
    } else {
        return $html;
    }
}

function get_taxonomy_cat_list_html($taxonomy, $current_term_id = 0)
{
    if (!$current_term_id) {
        // 获取当前分类对象
        $current_term = get_queried_object();
        $current_term_id = $current_term->term_id;
    } else {
        $current_term = get_term($current_term_id);
    }

    $cat = get_term_btn_html(0, $current_term_id, $taxonomy, $current_parent_id);
    $cat = $cat ? '<div class="cat-selects overflow-x-auto no-scrollbar">' . $cat . '</div>' : ''; 
     
    $cat_sub = get_cat_sub_list_html($current_parent_id, $current_term_id, $taxonomy);
    if( $cat_sub){
        $cat_sub = '<div class="cat-selects-sub">' . $cat_sub . '</div>';
    }
    $cat .= $cat_sub;
 
    return $cat;
}
/**
 * 递归函数，用于显示分类及其子分类
 * @param mixed $parent_id
 * @param mixed $current_term_id
 * @param mixed $taxonomy
 * @return string
 */
function get_cat_sub_list_html($parent_id = 0, $current_term_id = 0, $taxonomy = 'category')
{
    if (!$parent_id)
        return '';

    $cat = get_term_btn_html($parent_id, $current_term_id, $taxonomy, $current_parent_id);
    $cat = $cat ? '<div class="cat-selects overflow-x-auto no-scrollbar">' . $cat . '</div>' : '';
    //$cat = $cat ? '<div class="cat-selects-sub">' . $cat . '</div>' : '';

    $cat .= get_cat_sub_list_html($current_parent_id, $current_term_id, $taxonomy);
    return $cat;
}

/**
 * 构建分类列表按钮
 * @param mixed $parent_id  发分类ID
 * @param mixed $current_term_id 当前页面分类ID
 * @param mixed $taxonomy 分类法
 * @param mixed $current_parent_id 如果 $current_term_id 不在当前分类下，返回它所属分类的父分类ID
 * @return string
 */
function get_term_btn_html($parent_id = 0, $current_term_id = 0, $taxonomy = 'category', &$current_parent_id = 0)
{
    $terms = get_terms(array(
        'taxonomy'   => $taxonomy,
        'hide_empty' => true,
        'parent'     => $parent_id,
        'orderby'    => 'term_order',
        'order'      => 'ASC',
    ));

    if (empty($terms)) {
        return '';
    }

    $current_parent_id = 0;
    $cat               = '';
    foreach ($terms as $term) {
        $active = '';
        if ($term->term_id == $current_term_id || term_is_ancestor_of($term->term_id, $current_term_id, $taxonomy)) {
            $current_parent_id = $term->term_id;
            $active            = 'active';
        }

        $link = get_term_link($term);
        $cat .= '<a href="' . esc_url($link) . '" class="cat-select ajax-posts-load is-tab-btn text-sm ' . $active . '" ajax-method="page" data-cat="' . esc_attr($term->term_id) . '">' . esc_html($term->name) . '</a>';
    }
    return $cat;
}

/**
 * 获取分类页内容HTML
 * @param mixed $post_type
 * @param mixed $echo
 * @return string|void
 */
function io_get_taxonomy_content_html($post_type, $echo = true)
{
    switch ($post_type) {
        case 'post':
            $class = 'row-col-1a row-col-md-2a';
            $style = 'min';
            $callback = 'get_post_card';
            break;
        case 'sites':
            $class = 'row-col-1a row-col-md-2a row-col-lg-3a';
            $style = 'max';
            $callback = 'get_sites_card';
            break;
        case 'app':
            $class = 'row-col-2a row-col-md-3a row-col-lg-4a';
            $style = 'max';
            $callback = 'get_app_card';
            break;
        case 'book':
            $class = 'row-col-2a row-col-md-3a row-col-lg-4a';
            $style = io_get_book_card_mode();
            $callback = 'get_book_card';
            break;
        default:
            return;
    }

    $html = '<div class="posts-row ajax-posts-row ' . $class . '" data-style="' . $post_type . '-' . $style . '">';
    if (!have_posts()) {
        $html .= get_none_html();
    } else {
        while (have_posts()) {
            the_post();
            $html .= call_user_func($callback, $style, ['class' => 'ajax-item']);
        }
    }
    $html .= '</div>';

    if ($echo) {
        echo $html;
    } else {
        return $html;
    }
}


/**
 * 修改归档页文章数量
 * 添加排序功能到分类归档页
 * @param mixed $query
 * @return void
 */
function filter_pre_get_posts($query)
{
    if (!is_archive() || !$query->is_main_query() || is_admin()) {
        return $query;
    }

    $orderby = isset($_GET['orderby']) ? sanitize_text_field($_GET['orderby']) : ''; // 默认按照日期排序
    $order   = isset($_GET['order']) ? sanitize_text_field($_GET['order']) : 'DESC'; // 默认降序

    $num       = '';
    $meta      = '';

    // 归档类型与排序元字段的映射
    $home_sort = io_get_option('home_sort', array(
        'favorites' => '_sites_order',
        'apps'      => 'modified',
        'books'     => 'modified',
        'category'  => 'date'
    ));

    // 归档类型与每页文章数量的映射
    $archive_num = array(
        'favorites' => io_get_option('site_archive_n', 12),
        'sitetag'   => io_get_option('site_archive_n', 12),
        'apps'      => io_get_option('app_archive_n', 12),
        'apptag'    => io_get_option('app_archive_n', 12),
        'books'     => io_get_option('book_archive_n', 12),
        'booktag'   => io_get_option('book_archive_n', 12),
        'series'    => io_get_option('book_archive_n', 12),
        'category'  => '',
        'post_tag'  => '',
    );

    // 根据当前页面分类获取相应的数量和元字段
    foreach ($archive_num as $tax => $posts_per_page) {
        if (is_tax($tax)) {
            $query->set('posts_per_page', $posts_per_page);
            $_tax  = get_taxonomy_type_name($tax);
            $meta  = $home_sort[$_tax];
            if (empty($orderby)) {
                // 默认排序为首页排序规则，保持首页排序规则一致
                $order = io_get_option('home_sort_sort', 'DESC', $_tax); // 获取排序规则
            }
            break;
        }
    }

    // 如果 `orderby` 传入的排序选项有效，使用此元字段排序
    $meta = get_select_to_meta_key($orderby ?: $meta);

    if (!empty($meta)) {
        // 根据不同字段进行排序
        switch ($meta) {

            case 'views':
            case '_like_count':
            case '_star_count':
            case '_sites_order':
            case '_down_count':
                // 特殊处理 `_sites_order`
                if ($meta == "_sites_order" && io_get_option('sites_sortable', false)) {
                    $query->set('orderby', array('menu_order' => 'ASC', 'ID' => $order));
                } else {
                    $query->set('meta_key', $meta);
                    $query->set('orderby', array('meta_value_num' => $order, 'date' => $order));
                }
                break;

            case 'rand':
                $query->set('orderby', 'rand');
                break;

            default:
                $query->set('orderby', $meta);
                $query->set('order', $order);
                break;
        }
    }
    return $query;
}


/**
 * 归档页置顶靠前
 * @param WP_post[] $posts
 * @param WP_Query $query
 * @return WP_post[]
 */
function io_category_sticky_to_top($posts, $query)
{
    // 快速返回条件检查
    if (!is_main_query() || is_admin() || is_home() || is_front_page() || !is_archive() || is_author()) {
        return $posts;
    }

    // 检查是否应该处理置顶
    if (isset($_GET['orderby']) || isset($_GET['order']) || get_query_var('ignore_sticky_posts')) {
        return $posts;
    }

    // 分页检查
    if ($query->query_vars['paged'] > 1) {
        return $posts;
    }

    $queried_object = get_queried_object();
    if (!$queried_object || empty($queried_object->term_id)) {
        return $posts;
    }

    $term_id  = $queried_object->term_id;
    $taxonomy = $queried_object->taxonomy;

    // 获取当前 post_type（通过 query_var 或已加载文章）
    $post_type = $query->get('post_type') ?: (isset($posts[0]) ? get_post_type($posts[0]) : 'post');

    $sticky_ids = get_option('sticky_posts');
    if (empty($sticky_ids)) {
        return $posts;
    }

    // 使用缓存
    $cache_key       = 'sticky_posts_' . $term_id . '_' . $taxonomy;
    $sticky_post_ids = wp_cache_get($cache_key);

    if (false === $sticky_post_ids) {
        // 查询该分类下的置顶文章
        $sticky_query = new WP_Query([
            'post__in'            => $sticky_ids,
            'posts_per_page'      => -1,
            'ignore_sticky_posts' => 1,
            'orderby'             => 'post__in',
            'tax_query'           => [
                [
                    'taxonomy' => $taxonomy,
                    'field'    => 'term_id',
                    'terms'    => $term_id,
                ],
            ],
            'fields'              => 'ids' // 只拿 ID，加快处理
        ]);

        $sticky_post_ids = $sticky_query->posts;
        wp_cache_set($cache_key, $sticky_post_ids, '', 60); // 缓存1分钟
    }

    if (!empty($sticky_post_ids)) {
        // 获取完整文章对象
        $sticky_posts = get_posts([
            'post__in'       => $sticky_post_ids,
            'post_type'      => $post_type,
            'post_status'    => 'publish',
            'orderby'        => 'post__in',
            'posts_per_page' => -1
        ]);

        // 防止重复显示（排除已存在的 sticky）
        $existing_ids       = wp_list_pluck($sticky_posts, 'ID');
        $final_sticky_posts = array_filter($posts, function ($p) use ($existing_ids) {
            return !in_array($p->ID, $existing_ids);
        });

        // 合并置顶 + 当前页的文章
        $posts = array_merge($sticky_posts, $final_sticky_posts);
    }

    return $posts;
}

/**
 * 排除置顶文章
 * @param WP_Query $query
 * @return void
 */
function reorder_category_query_with_sticky($query)
{
    // 快速返回条件检查
    if (!$query->is_main_query() || is_admin() || is_home() || is_front_page() || !$query->is_archive() || $query->is_author()) {
        return;
    } 

    // 排除置顶文章
    $sticky_ids = get_option('sticky_posts');
    if (empty($sticky_ids)) {
        return;
    }

    $query->set('post__not_in', $sticky_ids);
}
if (io_get_option('show_sticky', false) && io_get_option('category_sticky', false)) {
    add_filter('the_posts', 'io_category_sticky_to_top', 10, 2);
    add_action('pre_get_posts', 'reorder_category_query_with_sticky');
}

/**
 * 获取大分类文章总数（包含子分类的文章）。
 *
 * @param int $term_id 分类的 ID
 * @return int 该分类及其所有子分类的文章总数
 */
function get_cat_all_post_count($term_id = 0, $taxonomy = 'category')
{
    // 获取当前分类对象
    if (!$term_id) {
        $term = get_queried_object();
    } else {
        $term = get_term($term_id);
    }

    if (!$term || is_wp_error($term)) {
        return 0;
    }

    // 当前分类文章数
    $count = (int) $term->count;

    // 获取当前分类的所有子孙分类
    $tax_terms = get_terms(array(
        'taxonomy'   => $taxonomy,
        'child_of'   => $term_id,
        'hide_empty' => false,
    ));

    if (!is_wp_error($tax_terms)) {
        foreach ($tax_terms as $tax_term) {
            $count += (int) $tax_term->count;
        }
    }

    return $count;
}
