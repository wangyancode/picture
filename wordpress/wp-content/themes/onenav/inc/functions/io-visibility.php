<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2025-06-05 15:49:29
 * @LastEditors: iowen
 * @LastEditTime: 2025-06-11 15:37:14
 * @FilePath: /onenav/inc/functions/io-visibility.php
 * @Description: 
 */
if(!defined('ABSPATH')){ exit; }


/**
 * 注册内容可见性分类
 * @return void
 */
function io_register_content_visibility_taxonomy()
{
    if (taxonomy_exists('content_visibility')) return;

    $post_types = ['post', 'sites', 'book', 'app'];

    register_taxonomy('content_visibility', $post_types, [
        'labels'       => [
            'name'          => '内容可见性',
            'singular_name' => '内容权限',
        ],
        'public'       => false,
        'show_ui'      => false,
        'show_in_rest' => true,
        'hierarchical' => false,
        //'meta_box_cb' => 'io_visibility_meta_box',
    ]);

    if (!get_option('io_content_visibility_init', 0)) {
        $default_terms = [
            ['name' => '所有人可见', 'slug' => 'public'],
            ['name' => '登录用户可见', 'slug' => 'logged_in'],
            ['name' => '购买后可见', 'slug' => 'purchase'],
            ['name' => 'VIP1 可见', 'slug' => 'vip1'],
            ['name' => 'VIP2 可见', 'slug' => 'vip2'],
            ['name' => '仅管理员可见', 'slug' => 'administrator'],
        ];

        foreach ($default_terms as $term) {
            if (!term_exists($term['slug'], 'content_visibility')) {
                wp_insert_term($term['name'], 'content_visibility', [
                    'slug' => $term['slug']
                ]);
            }
        }
        add_option('io_content_visibility_init', 1);
    }
}
add_action('init', 'io_register_content_visibility_taxonomy', 0);

function io_visibility_meta_box($post) {
    $terms = get_terms(['taxonomy' => 'content_visibility', 'hide_empty' => false]);
    $current_terms = wp_get_post_terms($post->ID, 'content_visibility', ['fields' => 'slugs']);

    echo '<select name="io_visibility_term" style="width: 100%;">';
    echo '<option value="">默认（所有人）</option>';
    foreach ($terms as $term) {
        printf(
            '<option value="%s" %s>%s</option>',
            esc_attr($term->slug),
            selected(in_array($term->slug, $current_terms), true, false),
            esc_html($term->name)
        );
    }
    echo '</select>';
}
/**
 * 设置的文章可见性标签
 */
function io_save_post_visibility_term($post_id, $post, $update)
{

    // 跳过自动保存和修订版本
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (wp_is_post_revision($post_id)) {
        return;
    }

    $post_type = $post->post_type;
    // 只处理支持的文章类型
    $supported_post_types = ['post', 'sites', 'book', 'app'];
    if (!in_array($post_type, $supported_post_types)) {
        return;
    }

    $meta_key = $post_type . '_post_meta';
    if (!isset($_POST[$meta_key]))
        return;

    $data = ($_POST[$meta_key]);
    if (!isset($data['_user_purview_level'])) {
        return;
    }
    $term       = sanitize_text_field($data['_user_purview_level']);
    $comparison = [
        'all'   => 'public',
        'buy'   => 'purchase',
        'user'  => 'logged_in',
        'vip1'  => 'vip1',
        'vip2'  => 'vip2',
        'admin' => 'administrator',
    ];
    $term_id    = $comparison[$term] ?? 'public';
    wp_set_post_terms($post_id, [$term_id], 'content_visibility', false);
    // 清理缓存
    io_clear_visibility_cache($post_id);
}
add_action('save_post', 'io_save_post_visibility_term', 20, 3);
add_action('publish_post', 'io_save_post_visibility_term', 20, 3);

/**
 * 内容可见度权限
 * 
 * @param WP_Query $query
 * @return void
 */
function io_posts_purview_query_var_filter( $query ){
    global $pagenow;
    if (is_preview() || "upload.php" == $pagenow || "admin.php"== $pagenow || (isset($_REQUEST['action']) && 'query-attachments' === $_REQUEST['action'])) {
        return;
    }

    if (current_user_can('manage_options')) {
        return; // 管理员不过滤
    }

    $option = io_get_option('global_remove','close');
    if ('close' === $option) {
        return;
    }

    $post_type = $query->get('post_type');
    $types = array('sites', 'post', 'app', 'book');
    if (!empty($post_type) && is_array($post_type))
        $post_type = $post_type[0];
    if (empty($post_type) && is_single()) {
        $post_type = 'post';
    }


    if(!(
        (is_admin() && defined('DOING_AJAX') && DOING_AJAX) ||
        (!is_admin() && (
            ($post_type && in_array($post_type, $types)) || ($query->is_main_query() && is_archive())
        ))
    )){
        return;
    }

    $existing_tax_query = $query->get('tax_query') ?: [];
    if(is_tax() && empty($existing_tax_query)){
        // 获取当前查询的分类法对象
        $taxonomy = $query->get_queried_object();
        $existing_tax_query[] = [
            'taxonomy' => $taxonomy->taxonomy,
            'field'    => 'term_id',
            'terms'    => $taxonomy->term_id,
        ];
    }

    $meta_query = io_add_visibility_tax_query(['tax_query' => $existing_tax_query]);


    if (isset($meta_query['tax_query']) && !empty($meta_query['tax_query'])) {
        $query->set('tax_query', $meta_query['tax_query']);
    }
}
add_action('pre_get_posts', 'io_posts_purview_query_var_filter');//pre_get_posts parse_query



function io_check_post_tax_visibility()
{
    if (!is_singular(['post', 'book', 'sites', 'app'])) {
        return;
    }

    global $post;
    if (!$post || $post->post_status !== 'publish')
        return;

    if (io_is_posts_user_purview($post->ID)) {
        set404();
    }
}
add_action( 'template_redirect', 'io_check_post_tax_visibility' );

/**
 * 向 WP_Query 参数中添加内容可见性（content_visibility）的 tax_query 条件
 *
 * @param array $args 原始 WP_Query 参数
 * @return array 修改后的参数，包含权限过滤条件
 */
function io_add_visibility_tax_query(array $args): array
{
    $option = io_get_option('global_remove','close');
    if ('close' === $option) {
        return $args;
    }

    if (current_user_can('manage_options')) {
        return $args; // 管理员不过滤
    }
    $role_priority = [
        'public'        => 0,
        'purchase'      => 1,
        'logged_in'     => 2,
        'vip1'          => 3,
        'vip2'          => 4,
        'administrator' => 99,
    ];

    $user_id = get_current_user_id(); 
    $user_level = 0;
    if (!$user_id && in_array($option, array('admin', 'user'))) {
        // 只显示所有人可见和商品
        $user_level = 1;
    } else if ($user_id || $option == 'point') {
        // 显示登录用户可见的内容
        $user_level = 5;
    }

    $allowed_terms = [];
    foreach ($role_priority as $term => $level) {
        if ($user_level >= $level) {
            $allowed_terms[] = $term;
        }
    }

    // 构造权限条件
    $visibility_filter = [
        'relation' => 'OR',
        [
            'taxonomy' => 'content_visibility',
            'field'    => 'slug',
            'terms'    => $allowed_terms,
        ],
        [
            'taxonomy' => 'content_visibility',
            'operator' => 'NOT EXISTS',
        ],
    ];
    $visibility_filter = [
        'taxonomy' => 'content_visibility',
        'field'    => 'term_id',
        'terms'    => io_get_cached_visibility_term_ids($allowed_terms),
    ];

    // 合并已有 tax_query
    if (isset($args['tax_query']) && is_array($args['tax_query']) && !empty($args['tax_query'])) {
        $args['tax_query']['relation'] = 'AND';
        $args['tax_query'][]            = $visibility_filter;
    } else {
        $args['tax_query'] = [$visibility_filter];
    }

    return $args;
}

/**
 * 获取 content_visibility 的 term_id（支持单个 slug 或数组），并使用持久缓存。
 *
 * @param string|array $slugs 一个 slug 或 slug 数组
 * @return int[] 返回 term_id 数组
 */
function io_get_cached_visibility_term_ids($slugs)
{
    if (empty($slugs)) {
        return [];
    }

    $slugs = (array) $slugs; // 强制为数组

    $cache_key   = 'io_visibility_term_ids';
    $cache_group = 'visibility_ids';

    $term_map = io_cache_get($cache_key, $cache_group);

    // 初次加载缓存
    if ($term_map === false || !is_array($term_map)) {
        // 确保ajax请求时，taxonomy_exists('content_visibility') 为 true
        io_register_content_visibility_taxonomy();

        $known_slugs = ['public', 'logged_in', 'vip1', 'vip2', 'purchase', 'administrator'];
        $terms       = get_terms([
            'taxonomy'   => 'content_visibility',
            'hide_empty' => false,
            'slug'       => $known_slugs,
        ]);

        if(is_wp_error($terms)){
            print_r($terms);
            return [];
        }
        $term_map = [];
        foreach ($terms as $term) {
            $term_map[$term->slug] = (int) $term->term_id;
        }

        io_cache_set($cache_key, $term_map, $cache_group, DAY_IN_SECONDS);
    }

    $result = [];
    foreach ($slugs as $slug) {
        if (isset($term_map[$slug])) {
            $result[] = $term_map[$slug];
        }
    }

    return $result;
}

/**
 * 判断文章是否需要用户权限
 * @param mixed $post_id
 * @return bool true 需要权限 false 不需要权限,直接显示
 */
function io_is_posts_user_purview($post_id){ 
    $option = io_get_option('global_remove','close');
    if (current_user_can('manage_options')) {
        return false;
    }
    $user_id = get_current_user_id(); 
    // 作者不过滤
    if((int)get_post_field('post_author', $post_id) === $user_id){
        return false;
    }
    // new 
    $post_level = io_get_post_visibility_slug($post_id);
    if (!$post_level) {
        $post_level = get_post_meta($post_id, '_user_purview_level', true);
        if(!$post_level){
            update_post_meta($post_id, '_user_purview_level', 'all');
            return false;
        }
    }

    if($post_level === 'admin'){
        return true;
    }
    if ('close' === $option) {
        return false;
    }
    $post_priority = [
        'all'   => 0,
        'buy'   => 1,
        'user'  => 2,
        'vip1'  => 3,
        'vip2'  => 4,
        'admin' => 99,
    ];
    $post_level = $post_priority[$post_level];

    $user_level = 0;
    if (!$user_id ) {
        if(in_array($option, array('admin', 'user'))){
            // 只显示所有人可见和商品
            $user_level = 1;
        }elseif($option == 'point'){
            $user_level = 5;
        }
    } else {
        // 显示登录用户可见的内容
        if($option == 'point'){
            $user_level = 5;
        }else{
            $user_level = 5;
        }
    }

    if($user_level >= $post_level){
        return false;
    }
    return true;
}

/**
 * 用户授权说明提示，操作引导
 * 
 * @param string $post_type
 * @param bool $echo
 * @return bool|string|void
 */
function get_user_level_directions_html($post_type, $echo = false){
    global $post;
    $post_id = $post->ID;

    // 管理员不过滤
    if(current_user_can('manage_options')){
        return false;
    }
    $user_id = get_current_user_id();
    // 作者不过滤
    if((int)get_post_field('post_author', $post_id) === $user_id){
        return false;
    }

    // new 
    $user_level = io_get_post_visibility_slug($post_id);
    if (!$user_level) {
        $user_level = get_post_meta($post_id, '_user_purview_level', true);
        if (!$user_level) {
            update_post_meta($post_id, '_user_purview_level', 'all');
            return false;
        }
    }

    if($user_level === 'all' || empty($user_level)){
        return false;
    }


    if($user_level && 'buy'===$user_level){ 
        $buy_option = get_post_meta($post_id, 'buy_option', true);
    }
    if(isset($buy_option)){
        if('view' === $buy_option['buy_type']){
            $is_buy = iopay_is_buy($post_id);
        }
    }

    if (!$user_id && in_array($user_level, array('admin', 'user'))) {
        $title     = __('权限不足', 'i_theme');
        $tips      = __('此内容已隐藏，请登录后查看！', 'i_theme');
        $btn       = __('登录查看', 'i_theme');
        $ico       = 'icon-user';
        $color     = '';
        $url_class = '';
        $url       = esc_url(wp_login_url(io_get_current_url()));
        $meta      = '';
        $tips_b    = '';
    } elseif ($user_id && in_array($user_level, array('admin'))) {
        $title     = __('权限不足', 'i_theme');
        $tips      = __('此内容已隐藏，请联系作者！', 'i_theme');
        $btn       = __('联系作者', 'i_theme');
        $ico       = 'icon-user';
        $color     = '';
        $url_class = '';
        $url       = esc_url(get_author_posts_url($post->post_author));
        $meta      = '';
        $tips_b    = '';
    }
    if (isset($is_buy) && !$is_buy) {
        $title     = __('付费阅读', 'i_theme');
        $tips      = __('此内容已隐藏，请购买后查看！', 'i_theme');
        $btn       = __('购买查看', 'i_theme');
        $ico       = 'icon-buy_car';
        $color     = '';
        $url_class = 'io-ajax-modal-get nofx';
        $url       = esc_url(add_query_arg(array('action' => 'pay_cashier_modal', 'id' => $post_id, 'index' => 0), admin_url('admin-ajax.php'))); 
        $meta      = '';
        $buy_data  = get_post_meta($post_id, 'buy_option', true);
        $org       = '';
        $tag       = '';
        if ((float) $buy_data['pay_price'] < (float) $buy_data['price']) {
            $org = '<span class="original-price text-sm"><span class="text-xs">' . io_get_option('pay_unit', '￥') . '</span>' . $buy_data['price'] . '</span>';
            $tag = '<div class="badge vc-red"><i class="iconfont icon-time-o mr-2"></i>' . __('限时特惠', 'i_theme') . '</div>';
        }
        $meta   .= '<div class="text-32"><span class="text-xs text-danger">' . io_get_option('pay_unit', '￥') . '</span><span class="text-danger font-weight-bold">' . $buy_data['pay_price'] . '</span> ' . $org . '</div>'.$tag;
        $tips_b = iopay_pay_tips_box('end');
    }
    if(!isset($url)){
        return false;
    }
    $name      = get_the_title();
    $thumbnail = io_get_post_thumbnail($post);

    $html = '<div class="user-level-box mb-5">';
    $html .= '<div class="user-level-header br-xl modal-header-bg ' . $color . ' px-3 py-1 py-md-2">';
    $html .= '<div class="text-lg mb-5"><i class="iconfont icon-version mr-2"></i>';
    $html .= '<span>' . $title . '</span></div>';
    $html .= '</div>';

    $html .= '<div class="user-level-body d-flex br-xl shadow blur-bg p-3 mt-n5 ml-1 ml-md-3">';
    $html .= '<div class="card-thumbnail img-type-' . $post_type . ' mr-2 mr-md-3 d-none d-md-block">';
    $html .= '<div class="h-100 img-box">';
    $html .= '<img src="' . $thumbnail . '" alt="' . $name . '">';
    $html .= '</div> ';
    $html .= '</div> ';
    $html .= '<div class="d-flex flex-fill flex-column">';
    $html .= '<div class="list-body flex-fill">';
    $html .= '<h1 class="h5 line2">' . $name . '</h1>';
    $html .= '<div class="mt-2 text-xs text-muted"><i class="iconfont icon-tishi mr-1"></i>' . $tips . '</div>';
    $html .= $meta;
    $html .= '</div> ';
    $html .= '<div class="text-right">';
    $html .= '<a href="' . $url . '" class="btn vc-blue btn-outline ' . $url_class . ' btn-md-lg"><i class="iconfont ' . $ico . ' mr-2"></i>' . $btn . '</a>';
    $html .= '</div>'; 
    $html .= '</div>';
    $html .= '</div>';
    $html .= $tips_b;
    $html .= '</div>';

    if ($echo)
        echo $html;
    else
        return $html;
}
/**
 * 获取文章的权限分类content_visibility
 * @param mixed $post_id
 */
function io_get_post_visibility_slug($post_id)
{
    $slugs = wp_get_post_terms($post_id, 'content_visibility', ['fields' => 'slugs']);

    if (!empty($slugs)) {
        $comparison = [
            'public'        => 'all',
            'logged_in'     => 'user',
            'vip1'          => 'vip1',
            'vip2'          => 'vip2',
            'purchase'      => 'buy',
            'administrator' => 'admin',
        ];
        return $comparison[$slugs[0]] ?? 'all';
    }

    // 如果没有设置权限，自动设置为 'public'
    $term = get_term_by('slug', 'public', 'content_visibility');

    if ($term && !is_wp_error($term)) {
        wp_set_post_terms($post_id, [$term->term_id], 'content_visibility', false);
        return 'all';
    }

    // 如果 public term 不存在（极少见），则尝试创建它
    $result = wp_insert_term('所有人可见', 'content_visibility', [
        'slug' => 'public'
    ]);
    if (!is_wp_error($result) && isset($result['term_id'])) {
        wp_set_post_terms($post_id, [$result['term_id']], 'content_visibility', false);
        return 'all';
    }

    // 最后的兜底情况
    return 'all';
}
