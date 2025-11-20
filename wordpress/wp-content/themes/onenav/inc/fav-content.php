<?php
/*!
 * Theme Name:One Nav
 * Theme URI:https://www.iotheme.cn/
 * Author:iowen
 * Author URI:https://www.iowen.cn/
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
/**
 * 获取不同页面的分类显示数量
 */
function get_card_num()
{
    $post_id  = isset($_REQUEST['post_id']) ? sanitize_key($_REQUEST['post_id']) : get_queried_object_id();
    $default  = array(
        'favorites' => 20,
        'apps'      => 16,
        'books'     => 16,
        'category'  => 16,
    );
    $quantity = $default;
    if (is_home() || is_front_page() || ($post_id == 0 && defined('DOING_AJAX') && DOING_AJAX)) {
        $quantity = io_get_option('card_n', $default);
    } else {
        if (get_post_meta($post_id, '_count_type', true)) {
            // 子页面自定义显示数量
            $quantity = get_post_meta($post_id, 'card_n', true) ?: $default;
        } else {
            $quantity = io_get_option('card_n', $default);
        }
    }
    return $quantity;
}

/**
 * 加载单个分类内容
 * 用于 分类object
 * @param object $mid   分类对象
 * @param object $parent_term 父级分类
 * @return null
 */
function fav_con($mid,$parent_term = null) { 
    $taxonomy = $mid->taxonomy;
    $quantity = get_card_num();
    $icon     = get_tag_ico($taxonomy,(array)$mid);
    $tag_id   = (null !== $parent_term) ? ($parent_term->term_id . '-' . $mid->term_id) : $mid->term_id;
    ?>
        <div class="d-flex flex-fill align-items-center mb-4">
            <h4 class="text-gray text-lg m-0">
                <i class="site-tag <?php echo $icon ?> icon-lg mr-1" id="term-<?php echo $tag_id ?>"></i>
                <?php if( null !== $parent_term && io_get_option("tab_p_n",false)&& !wp_is_mobile() ){ 
                    echo $parent_term->name . '<span style="color:#f1404b"> · </span>';
                } 
                echo $mid->name; ?>
            </h4>
            <div class="flex-fill"></div>
            <?php 
            $site_n           = $quantity[get_taxonomy_type_name($taxonomy)];
            $category_count   = $mid->category_count;
            $count            = $site_n;
            if($site_n == 0)  $count = min(get_option('posts_per_page'),$category_count);
            if($site_n >= 0 && $count < $category_count){
                $link = esc_url(get_term_link($mid, $taxonomy));
                echo "<a class='btn-move text-xs' href='$link'>"._iol(io_get_option('term_more_text','more+'))."</a>";
            } 
            ?>
        </div>
        <div class="row io-mx-n2">
        <?php show_card($site_n, $mid->term_id, $taxonomy); ?>
        </div>   
<?php 
}



if(!function_exists('show_card')):
/**
 * 显示分类内容
 * @param  string $site_n 需显示的数量
 * @param  int|array $cat_id 分类id
 * @param  string $taxonomy 分类名
 * @param  array $args 参数
 */
function show_card($site_n, $cat_id, $taxonomy, $args = array())
{
    $default = array(
        'ajax_class'          => '',
        'echo'                => true,
        'orderby'             => io_get_option('home_sort', '', get_taxonomy_type_name($taxonomy)),
        'ignore_sticky_posts' => 0,
        'page'                => 1,
        'style'               => '',
    );
    $args    = wp_parse_args($args, $default);

    $html = '';
    if (!in_array($taxonomy, get_menu_category_list())) {
        $html = get_none_html('该菜单内容不是分类，请到菜单删除并重新添加正确的内容。', 'home-list content-card', 'error', false);
        if ($args['echo']) {
            echo $html;
            return;
        } else {
            return $html;
        }
    }
    $post_type  = get_post_types_by_taxonomy($taxonomy);
    $post_sort  = io_get_option('home_sort_sort', 'DESC', get_taxonomy_type_name($taxonomy));
    $order_args = get_term_order_args($args['orderby'], $post_sort);
    $post_args  = array(
        'post_type'           => $post_type,
        'post_status'         => array('publish', 'private'),//'publish',
        'perm'                => 'readable',
        'posts_per_page'      => $site_n,
        'paged'               => $args['page'],
        'ignore_sticky_posts' => $args['ignore_sticky_posts'],
    );
    if (!empty($cat_id)) {
        $post_args['tax_query'] = array(
            array(
                'taxonomy' => $taxonomy,
                'field'    => 'term_id',
                'terms'    => $cat_id,
            )
        );
    }
    if (io_get_option('no_dead_url', false) && 'sites' == $post_type) {
        $post_args['meta_query'] = array(
            array(
                'key'     => '_affirm_dead_url',
                'compare' => 'NOT EXISTS'
            )
        );
    }
    $cache_key = '';
    $myposts = false;
    $cache_time = MINUTE_IN_SECONDS;
    if ($args['orderby'] !== 'rand') {  // 非随机排序缓存
        if (is_array($cat_id)) {
            $cache_key  = 'io_home_posts_' . md5(json_encode($cat_id) . '_' . $taxonomy . '_' . $site_n . '_' . $args['ajax_class'] . '_' . $args['orderby']);
            $cache_time = MINUTE_IN_SECONDS;
        } else {
            $cache_key  = 'io_home_posts_' . md5($cat_id . '_' . $taxonomy . '_' . $site_n . '_' . $args['ajax_class'] . '_' . $args['orderby']);
            $cache_time = 24 * HOUR_IN_SECONDS;
        }
        $myposts = wp_cache_get($cache_key, 'home-card');//wp_cache_get_last_changed('home-card');
    }
    if (false === $myposts || in_array($args['orderby'], ['random', 'rand']) || $args['page'] > 1) {
        $myposts = new WP_Query(wp_parse_args($order_args, $post_args));
        if (!$args['ignore_sticky_posts'] && io_get_option('show_sticky', false)) {
            // 置顶文章换到最前面
            $myposts = sticky_posts_to_top($myposts, $post_type, $taxonomy, $cat_id);
        }

        if ($args['orderby'] == '_down_count' || $args['orderby'] == 'views') {
            $cache_time = HOUR_IN_SECONDS;
        }
        if (!empty($cache_key)) {
            wp_cache_set($cache_key, $myposts, 'home-card', $cache_time);
        }

        wp_reset_postdata();
    }
    $html = io_get_post_card($myposts, $taxonomy, $args['ajax_class'], $cat_id, $args['style']);

    if ($args['echo']) {
        echo $html;
        return;
    } else {
        return array(
            'html'     => $html,
            'max_page' => $myposts->max_num_pages,
            'count'    => $myposts->post_count
        );
    }

}
endif;
