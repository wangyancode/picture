<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2023-04-01 00:07:44
 * @LastEditors: iowen
 * @LastEditTime: 2025-06-04 18:54:42
 * @FilePath: /onenav/inc/functions/io-post.php
 * @Description: 
 */

/**
 * 获取文章头图
 * 
 * @param WP_Post $post
 * @return mixed
 */
function io_get_post_thumbnail($post){
    $post_id   = $post->ID;
    $post_type = $post->post_type;
    $name      = $post->post_title;

    switch ($post_type) {
        case 'sites':
            $sites_type = get_post_meta($post_id, '_sites_type', true);
            $link_url   = get_post_meta($post_id, '_sites_link', true);
            $thumbnail  = get_site_thumbnail($name, $link_url, $sites_type, false);
            break;
        case 'app':
            $thumbnail = get_post_meta_img($post_id, '_app_ico', true);
            break;
        case 'book':
            $thumbnail = get_post_meta_img($post_id, '_thumbnail', true);
            break;
        default:
            $thumbnail = io_theme_get_thumb();
    }
    return $thumbnail;
}

/**
 * 自定义网址分类帖子顺序
 * 网址分类页"失效链接"排最后
 * 
 * @param mixed $orderby
 * @param mixed $wp_query
 * @return mixed
 */
function io_custom_favorites_posts_orderby($orderby, $wp_query){
    if (!is_archive() || !$wp_query->is_main_query() || is_admin()) {
        return $orderby;
    }
    if (!io_get_option('sites_archive_order', true)) {
        return $orderby;
    }
    if (is_tax(['favorites', 'sitetag'])) {
        global $wpdb;
        // 添加一个CASE语句来调整排序逻辑
        $orderby = "CASE WHEN (SELECT COUNT(1)
                    FROM {$wpdb->postmeta}
                    WHERE {$wpdb->postmeta}.meta_key = '_affirm_dead_url'
                    AND {$wpdb->postmeta}.post_id = {$wpdb->posts}.ID) > 0 THEN 1 ELSE 0 END ASC, " . $orderby;
    }
    return $orderby;
}
add_filter('posts_orderby', 'io_custom_favorites_posts_orderby', 10, 2);


/**
 * 输出正文内容卡片
 * @param WP_Query $myposts 
 * @param string $taxonomy 分类名
 * @param string $class ajax class
 * @param string $cat_id 分类id
 * @param string $style 卡片样式
 */
function io_get_post_card($myposts, $taxonomy, $class, $cat_id, $style = '') {
    $html = '';
    $card_args = array(
        'class' => $class,
    );

    if (!$myposts->have_posts()) {
        $html = get_none_html('', 'home-list');
    } elseif ($myposts->have_posts()) {
        $post_type = get_post_types_by_taxonomy($taxonomy);
        if (empty($style)) {
            $style = get_term_meta($cat_id, 'card_mode', true);
            if (!$style || 'null' === $style || 'none' === $style) {
                $style = io_get_option($post_type . '_card_mode', 'max');
            }
        }
        while ($myposts->have_posts()) {
            $myposts->the_post();
            switch ($post_type){
                case 'sites':
                    $html .= get_sites_card($style, $card_args);
                    break;
                case 'app':
                    $html .= get_app_card($style, $card_args);
                    break;
                case 'book':
                    $html .= get_book_card($style, $card_args);
                    break;
                case 'post':
                    $html .= get_post_card($style, $card_args);
                    break;
            }
        }
        wp_reset_postdata();
    }
    /**
     * -----------------------------------------------------------------------
     * HOOK : FILTER HOOK
     * io_show_card_output
     * 
     * 分类卡片输出内容过滤
     * @since  5.0.0
     * -----------------------------------------------------------------------
     */
    return apply_filters('io_show_card_output', $html, $taxonomy, $cat_id, $class);
}

/**
 * 获取侧边栏文章列表
 * @param mixed $args
 * @param string $placeholder 占位符
 * @return string
 */
function get_widgets_posts_html($args, $placeholder='') {
    $defaults = array(
        'post_type' => 'sites',
        'before'    => '',
        'after'     => '',
        'count'     => '',
        'days'      => 0,
        'orderby'   => 'views',
        'similar'   => 0,       // 是否相似
        'exclude'   => array(), // 排除ID
        'fallback'  => 0,       // 是否补齐默认数量
        'style'     => 'min',   // 样式
        'term_id'   => array(), // 分类ID
    );

    $instance = wp_parse_args($args, $defaults);

    $exclude   = $instance['exclude'];
    $post_num  = $instance['count'];
    $post_type = $instance['post_type'];

    $columns    = !$instance['only_title'] && in_array($post_type, array('sites', 'book')) ? 2 : 1;
    $before_div = $instance['before'] ?: '<div class="posts-row row-sm row-col-' . $columns . 'a">';
    $after_div  = $instance['after'] ?: '</div>';

    if($placeholder){
        return $before_div . $placeholder . $after_div;
    }

    $tax_query = array();
    if ($instance['similar'] && empty($instance['term_id'])) {
        $c_post_id   = '';
        $c_post_type = '';
        if (is_array($instance['similar'])) {
            $c_post_id   = $instance['similar']['post_id'];
            $c_post_type = $instance['similar']['post_type'];
        } elseif (is_single()) {
            $c_post_id   = get_the_ID();
            $c_post_type = get_post_type($c_post_id);
        }

        if ($c_post_id && $c_post_type == $post_type) {
            $cat  = posts_to_cat($c_post_type);
            $tag  = posts_to_tag($c_post_type);
            $cats = get_the_terms($c_post_id, $cat);
            $tags = get_the_terms($c_post_id, $tag);

            $exclude[] = $c_post_id;

            $tax_query = array(
                'relation' => 'OR',
                array(
                    'taxonomy' => $tag,
                    'field'    => 'term_id',
                    'terms'    => array_column((array) $tags, 'term_id'),
                ),
                array(
                    'taxonomy' => $cat,
                    'field'    => 'term_id',
                    'terms'    => array_column((array) $cats, 'term_id'),
                ),
            );
        }
    } elseif (!empty($instance['term_id'])) {
        $cat       = posts_to_cat($post_type);
        $tax_query = array(
            array(
                'taxonomy' => $cat,
                'field'    => 'term_id',
                'terms'    => $instance['term_id'],
            ),
        );
    }

    $date_query = array();
    if ($instance['days'] && 'rand' !== $instance['orderby']) {
        $date_query = array(
            array(
                'column' => 'post_modified', // 默认为 post_date， 如果是更新日期，则为 post_modified
                'after'  => $instance['days'] . ' days ago',
            ),
        );
    }

    $i     = 0;
    $html  = ''; 
    $order = get_term_order_args($instance['orderby']);

    $args    = array(
        'post_type'           => $post_type,
        'post_status'         => 'publish',
        'posts_per_page'      => $post_num,
        'ignore_sticky_posts' => 1,
        'tax_query'           => $tax_query,
        'date_query'          => $date_query,
        'post__not_in'        => $exclude,
    );
    $args    = wp_parse_args($args, $order);
    $myposts = new WP_Query($args);
    if ($myposts->have_posts()) {
        switch ($post_type) {
            case 'post':
                $data = load_widgets_min_post_html($myposts, $instance, $i);
                break;
            case 'app':
                $data = load_widgets_min_app_html($myposts, $instance, $i);
                break;
            case 'book':
                $data = load_widgets_min_book_html($myposts, $instance, $i);
                break;
            case 'sites':
            default:
                $data = load_widgets_min_sites_html($myposts, $instance, $i);
                break;
        }
        $html .= $data['html'];
        $i       = $data['index'];
        $exclude = array_merge($exclude, $data['exclude']);
    }

    if ($instance['fallback'] && $i < $post_num) {
        $args    = array(
            'post_type'           => $post_type,
            'post_status'         => array('publish', 'private'),
            'perm'                => 'readable',
            'ignore_sticky_posts' => 1,
            'posts_per_page'      => $post_num - $i,
            'date_query'          => $date_query,
            'post__not_in'        => $exclude,  // 排除已经获取的文章      
        );
        $args    = wp_parse_args($args, $order);
        $myposts = new WP_Query($args);
        switch ($post_type) {
            case 'post':
                $data = load_widgets_min_post_html($myposts, $instance, $i);
                break;
            case 'app':
                $data = load_widgets_min_app_html($myposts, $instance, $i);
                break;
            case 'book':
                $data = load_widgets_min_book_html($myposts, $instance, $i);
                break;
            case 'sites':
            default:
                $data = load_widgets_min_sites_html($myposts, $instance, $i);
                break;
        }
        $html .= $data['html'];
    }
    wp_reset_postdata();

    return $before_div . $html . $after_div;
}

/**
 * 获取标签云
 * @param mixed $args
 * @return string
 */
function get_tag_cloud_html($args) {
    $defaults = array(
        'window'     => true,
        'taxonomy'   => ['favorites'],
        'count'      => 20,
        'orderby'    => 'name',
        'show_count' => false,
    );
    $instance = wp_parse_args($args, $defaults);

    $blank = is_new_window($instance) ? ' target="_blank"' : '';

    $html = '';


    $posts_type_s = wp_parse_args((array) io_get_option('posts_type_s'), ['post']); // 获取启用的文章类型
    $taxonomy     = array();
    foreach ($instance['taxonomy'] as $term) {
        if(in_array(get_post_types_by_taxonomy($term), $posts_type_s) && !isset($taxonomy[$term])) {
            // 如果文章类型在启用的文章类型中，并且标签类型未添加到 $taxonomy，则添加到标签类型中
            $taxonomy[] = $term;
        }
    }

    if ('rand' === $instance['orderby']) {
        $tags = get_random_terms($taxonomy, $instance['count']);
    } else {
        $args = array(
            'taxonomy'   => $taxonomy,
            'orderby'    => $instance['orderby'],
            'number'     => $instance['count'],
            'order'      => 'DESC',
            'hide_empty' => true,
            'count'      => true,
        );

        $tags = get_terms($args);
    }

    if (!empty($tags) && !is_wp_error($tags)) {
        foreach ($tags as $key => $tag) {
            $url       = esc_url(get_term_link(intval($tag->term_id), $tag->taxonomy));
            $name      = esc_attr($tag->name);
            $color     = get_theme_color($key, 'l', true);
            $tag_class = 'btn btn-sm flex-fill ' . $color;
            $count     = $instance['show_count'] ? '<span class="text-xs tag-count"> (' . esc_attr($tag->count) . ')</span>' : '';

            $html .= '<a href="' . $url . '"' . $blank . ' class="' . $tag_class . '">' . $name . $count . '</a>';
        }
    }

    return $html;
}

/**
 * 获取随机标签
 * @param array $taxonomies 标签类型
 * @param int $count 数量
 * @global WPDB $wpdb
 * @return mixed
 */
function get_random_terms($taxonomies = ['post_tag'], $count = 5, $output = OBJECT)
{
    global $wpdb;

    // 确保 $taxonomies 是数组
    $taxonomies = (array) $taxonomies;

    // 生成 SQL 占位符（用于 prepare 语句）
    $placeholders = implode(',', array_fill(0, count($taxonomies), '%s'));


    // 构建 SQL 查询
    $query = $wpdb->prepare("
        SELECT t.term_id, t.name, t.slug, tt.taxonomy, tt.count
        FROM {$wpdb->terms} t
        INNER JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id
        WHERE tt.taxonomy IN ($placeholders) AND tt.count > 0
        ORDER BY RAND()
        LIMIT %d
    ", array_merge($taxonomies, [$count]));

    return $wpdb->get_results($query, $output);
}

/**
 * 获取文章基础信息
 * @param bool $time 是否显示时间
 * @return string
 */
function get_post_meta_small($time = true)
{
    global $is_dead;

    $post_id = get_the_ID();
    $view    = function_exists('the_views') ? the_views(false, '<span class="views mr-3"><i class="iconfont icon-chakan-line"></i> ', '</span>') : '';

    $html = '<div class="d-flex flex-fill text-muted text-sm">';
    $html .= $time ? io_get_post_time() : '';
    $html .= $view;
    $html .= '<span class="mr-3"><a class="smooth" href="#comments"> <i class="iconfont icon-comment"></i> ' . get_comments_number() . '</a></span>';
    $html .= get_posts_like_btn($post_id);
    if ($is_dead) {
        $html .= '<span class="badge vc-l-red"><i class="iconfont icon-subtract mr-1"></i>' . __('链接已失效', 'i_theme') . '</span>';
    }
    $html .= '</div>';
    return $html;
}

/**
 * 获取文章翻页
 * 
 * @param mixed $ajax
 * @param mixed $echo
 * @return mixed
 */
function io_paging($ajax = true, $echo = true)
{
    $html = '';
    if ($ajax) {
        $next = get_next_posts_link('加载更多');
        if($next){
            $next = apply_filters('paginate_links', $next);
            $html = '<div class="next-page ajax-posts-load text-center my-3">' . $next . '</div>';
        }else{
            //if(wp_doing_ajax()){
                $html = '<div class="next-page text-center my-3"><a href="javascript:;">' . __('没有了', 'i_theme') . '</a></div>';
            //}
        }
    } else {
        $html =  paginate_links(array(
            'prev_next'          => 0,
            'before_page_number' => '',
            'mid_size'           => 2,
        ));
    }

    if ($echo) {
        echo $html;
    } else {
        return $html;
    }
}

/**
 * 获取文章状态名称
 * 
 * @param mixed $type
 * @return string
 */
function io_get_post_status_name($type){
    $status = array(
        'publish' => __('已发布', 'i_theme'),
        'draft'   => __('草稿', 'i_theme'),
        'pending' => __('待审核', 'i_theme'),
        'trash'   => __('回收站', 'i_theme'),
    );
    return isset($status[$type]) ? $status[$type] : __('未知', 'i_theme');
}
/**
 * 获取 APP 版本选项默认值
 * 
 * @param mixed $key 键名
 * @param mixed $default 默认值
 * @return mixed
 */
function io_get_app_default_down($key, $default = ''){
    if (is_admin()) {
        $post_id = isset($_GET['post']) ? $_GET['post'] : '';
    }else{
        $post_id = isset($_GET['edit']) ? $_GET['edit'] : '';
    }
    $default_down = [];
    if($post_id){
        $default_down = get_post_meta($post_id,'_down_default',true);
    }
    $value = '';
    if(isset($default_down[$key])){
        $value = $default_down[$key];
    }
    return $value != '' ? $value : $default;
}

/**
 * 获取书籍卡片样式
 * 
 * @param mixed $style 样式
 * @return string
 */
function io_get_book_card_mode($style = '')
{
    $type = io_get_option('book_card_mode');

    $t = '';
    if (strpos($type, 'v') === 0) {
        $t = 'v';
    } else {
        $t = 'h';
    }

    if (!empty($style)) {
        $t .= '-' . $style;
    }
    return $t;
}
