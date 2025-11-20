<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2025-01-21 10:00:00
 * @LastEditors: iowen
 * @LastEditTime: 2025-06-11 00:49:00
 * @FilePath: /onenav/inc/functions/io-admin-visibility.php
 * @Description: 后台内容可见性筛选和统计功能
 */
if(!defined('ABSPATH')){ exit; }

/**
 * 为后台列表页添加内容可见性筛选下拉框
 */
function io_add_visibility_filter_dropdown($post_type)
{
    // 只对支持内容可见性的文章类型显示筛选器
    $supported_post_types = ['post', 'sites', 'book', 'app'];
    if (!in_array($post_type, $supported_post_types)) {
        return;
    }

    $selected = isset($_GET['content_visibility']) ? $_GET['content_visibility'] : '';
    
    // 按预定义顺序显示权限选项
    $visibility_order = ['public', 'logged_in', 'purchase', 'vip1', 'vip2', 'administrator'];
    $terms_map = [];
    
    $all_terms = get_terms([
        'taxonomy'   => 'content_visibility',
        'hide_empty' => false,
    ]);
    
    if (!empty($all_terms) && !is_wp_error($all_terms)) {
        foreach ($all_terms as $term) {
            $terms_map[$term->slug] = $term;
        }
        
        echo '<select name="content_visibility" id="content_visibility_filter">';
        echo '<option value="">' . __('所有可见性', 'i_theme') . '</option>';
        
        foreach ($visibility_order as $slug) {
            if (isset($terms_map[$slug])) {
                $term = $terms_map[$slug];
                $count = io_get_visibility_post_count($term->slug, $post_type);
                printf(
                    '<option value="%s" %s>%s (%d)</option>',
                    esc_attr($term->slug),
                    selected($selected, $term->slug, false),
                    esc_html($term->name),
                    $count
                );
            }
        }
        
        echo '</select>';
    }
}
add_action('restrict_manage_posts', 'io_add_visibility_filter_dropdown');

/**
 * 处理内容可见性筛选查询
 */
function io_handle_visibility_filter_query($query)
{
    global $pagenow;
    
    if (!is_admin() || 'edit.php' !== $pagenow || !$query->is_main_query()) {
        return;
    }

    // 处理内容可见性筛选
    if (isset($_GET['content_visibility']) && !empty($_GET['content_visibility'])) {
        $visibility = sanitize_text_field($_GET['content_visibility']);
        
        $tax_query = $query->get('tax_query') ?: [];
        $tax_query[] = [
            'taxonomy' => 'content_visibility',
            'field'    => 'slug',
            'terms'    => $visibility,
        ];
        
        $query->set('tax_query', $tax_query);
    }
}
add_filter('pre_get_posts', 'io_handle_visibility_filter_query');

/**
 * 为后台列表页添加内容可见性统计视图
 */
function io_add_visibility_views($views)
{
    global $post_type;
    
    $terms = get_terms([
        'taxonomy'   => 'content_visibility',
        'hide_empty' => false,
    ]);

    if (empty($terms) || is_wp_error($terms)) {
        return $views;
    }

    $current_visibility = isset($_GET['content_visibility']) ? $_GET['content_visibility'] : '';
    
    foreach ($terms as $term) {
        $count = io_get_visibility_post_count($term->slug, $post_type);
        
        if ($count > 0) {
            $class = ($current_visibility === $term->slug) ? 'current' : '';
            $url = add_query_arg(['content_visibility' => $term->slug], admin_url("edit.php?post_type={$post_type}"));
            
            $views["visibility_{$term->slug}"] = sprintf(
                '<a href="%s" class="%s">%s <span class="count">(%d)</span></a>',
                esc_url($url),
                $class,
                esc_html($term->name),
                $count
            );
        }
    }

    return $views;
}
add_filter('views_edit-post', 'io_add_visibility_views');
add_filter('views_edit-sites', 'io_add_visibility_views');
add_filter('views_edit-book', 'io_add_visibility_views');
add_filter('views_edit-app', 'io_add_visibility_views');

/**
 * 获取指定可见性类型的文章数量
 * 
 * @param string $visibility_slug 可见性类型slug
 * @param string $post_type 文章类型
 * @return int 文章数量
 */
function io_get_visibility_post_count($visibility_slug, $post_type = 'post')
{
    $cache_key = "visibility_count_{$visibility_slug}_{$post_type}";
    $count = wp_cache_get($cache_key, 'io_visibility');
    
    if ($count === false) {
        $args = [
            'post_type'      => $post_type,
            'post_status'    => ['publish', 'draft', 'pending', 'private'],
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'tax_query'      => [
                [
                    'taxonomy' => 'content_visibility',
                    'field'    => 'slug',
                    'terms'    => $visibility_slug,
                ],
            ],
        ];
        
        $query = new WP_Query($args);
        $count = $query->found_posts;
        
        wp_cache_set($cache_key, $count, 'io_visibility', HOUR_IN_SECONDS);
    }
    
    return (int) $count;
}

/**
 * 获取所有内容可见性统计信息
 * 
 * @param string $post_type 文章类型
 * @return array 统计信息数组
 */
function io_get_all_visibility_stats($post_type = 'post')
{
    $cache_key = "all_visibility_stats_{$post_type}";
    $stats = wp_cache_get($cache_key, 'io_visibility');
    
    if ($stats === false) {
        $stats = [];
        
        $terms = get_terms([
            'taxonomy'   => 'content_visibility',
            'hide_empty' => false,
        ]);
        
        if (!empty($terms) && !is_wp_error($terms)) {
            foreach ($terms as $term) {
                $count = io_get_visibility_post_count($term->slug, $post_type);
                $stats[$term->slug] = [
                    'name'  => $term->name,
                    'count' => $count,
                    'slug'  => $term->slug,
                ];
            }
        }
        
        // 获取无可见性设置的文章数量
        $args = [
            'post_type'      => $post_type,
            'post_status'    => ['publish', 'draft', 'pending', 'private'],
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'tax_query'      => [
                [
                    'taxonomy' => 'content_visibility',
                    'operator' => 'NOT EXISTS',
                ],
            ],
        ];
        
        $query = new WP_Query($args);
        $stats['no_visibility'] = [
            'name'  => '未设置权限',
            'count' => $query->found_posts,
            'slug'  => '',
        ];
        
        wp_cache_set($cache_key, $stats, 'io_visibility', HOUR_IN_SECONDS);
    }
    
    return $stats;
}

/**
 * 在文章列表中显示内容可见性列
 */
function io_add_visibility_column($columns)
{
    // 在日期列之前插入可见性列
    $new_columns = [];
    foreach ($columns as $key => $title) {
        if ($key === 'date') {
            $new_columns['content_visibility'] = '内容权限';
        }
        $new_columns[$key] = $title;
    }
    
    return $new_columns;
}
add_filter('manage_posts_columns', 'io_add_visibility_column');
add_filter('manage_sites_posts_columns', 'io_add_visibility_column'); 
add_filter('manage_book_posts_columns', 'io_add_visibility_column');
add_filter('manage_app_posts_columns', 'io_add_visibility_column');

/**
 * 显示内容可见性列的内容
 */
function io_display_visibility_column($column, $post_id)
{
    if ($column !== 'content_visibility') {
        return;
    }
    
    // 使用静态变量防止重复输出
    static $processed = [];
    $key = $post_id . '_' . $column;
    
    if (isset($processed[$key])) {
        return;
    }
    $processed[$key] = true;
    
    $terms = wp_get_post_terms($post_id, 'content_visibility');
    
    if (!empty($terms) && !is_wp_error($terms)) {
        $term = $terms[0]; // 只显示第一个分类
        $color_class = io_get_visibility_color_class($term->slug);
        echo sprintf(
            '<span class="visibility-badge %s">%s</span>',
            esc_attr($color_class),
            esc_html($term->name)
        );
    } else {
        echo '<span class="visibility-badge visibility-default">所有人可见</span>';
    }
}
add_action('manage_posts_custom_column', 'io_display_visibility_column', 5, 2);
add_action('manage_sites_posts_custom_column', 'io_display_visibility_column', 5, 2);
add_action('manage_book_posts_custom_column', 'io_display_visibility_column', 5, 2);
add_action('manage_app_posts_custom_column', 'io_display_visibility_column', 5, 2);

/**
 * 根据可见性类型获取颜色样式类
 * 
 * @param string $visibility_slug
 * @return string
 */
function io_get_visibility_color_class($visibility_slug)
{
    $color_map = [
        'public'        => 'public',
        'logged_in'     => 'logged-in',
        'purchase'      => 'purchase',
        'vip1'          => 'vip1',
        'vip2'          => 'vip2',
        'administrator' => 'admin',
    ];
    
    return isset($color_map[$visibility_slug]) ? 'visibility-' . $color_map[$visibility_slug] : 'visibility-default';
}

/**
 * 加载内容可见性管理后台样式和脚本
 */
function io_visibility_admin_assets()
{
    $screen = get_current_screen();
    if (!$screen || ($screen->base !== 'edit' && $screen->base !== 'dashboard' && $screen->base !== 'edit-tags')) {
        return;
    }
    
    wp_enqueue_style(
        'io-visibility-admin', 
        get_theme_file_uri('/inc/admin/assets/visibility-admin.css'), 
        [], 
        IO_VERSION
    );
}
add_action('admin_enqueue_scripts', 'io_visibility_admin_assets');

/**
 * 清理内容可见性统计缓存
 */
function io_clear_visibility_cache($post_id = null)
{
    if ($post_id) {
        $post_type = get_post_type($post_id);
        wp_cache_delete("all_visibility_stats_{$post_type}", 'io_visibility');
        
        // 清理所有可见性类型的缓存
        $terms = get_terms(['taxonomy' => 'content_visibility', 'hide_empty' => false]);
        if (!empty($terms) && !is_wp_error($terms)) {
            foreach ($terms as $term) {
                wp_cache_delete("visibility_count_{$term->slug}_{$post_type}", 'io_visibility');
            }
        }
    }
}
add_action('wp_insert_post', 'io_clear_visibility_cache');
add_action('before_delete_post', 'io_clear_visibility_cache');
add_action('transition_post_status', 'io_clear_visibility_cache_on_status_change', 10, 3);

function io_clear_visibility_cache_on_status_change($new_status, $old_status, $post)
{
    if ($new_status !== $old_status) {
        io_clear_visibility_cache($post->ID);
    }
}



/**
 * 批量为现有文章设置默认权限
 */
function io_batch_set_default_visibility()
{
    $supported_post_types = ['post', 'sites', 'book', 'app'];
    $processed = 0;
    
    foreach ($supported_post_types as $post_type) {
        // 查询没有content_visibility分类的文章
        $posts = get_posts([
            'post_type'      => $post_type,
            'post_status'    => ['publish', 'draft', 'pending', 'private'],
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'tax_query'      => [
                [
                    'taxonomy' => 'content_visibility',
                    'operator' => 'NOT EXISTS',
                ],
            ],
        ]);
        
        if (!empty($posts)) {
            // 获取public分类ID
            $public_term = get_term_by('slug', 'public', 'content_visibility');
            
            if ($public_term && !is_wp_error($public_term)) {
                foreach ($posts as $post_id) {
                    wp_set_post_terms($post_id, [$public_term->term_id], 'content_visibility', false);
                    $processed++;
                }
            }
        }
    }
    
    // 清理所有缓存
    foreach ($supported_post_types as $post_type) {
        wp_cache_delete("all_visibility_stats_{$post_type}", 'io_visibility');
    }
    
    return $processed;
}

/**
 * 添加内容可见性统计小工具到仪表板
 */
function io_add_visibility_dashboard_widget()
{
    wp_add_dashboard_widget(
        'io_visibility_stats',
        '内容可见性统计',
        'io_display_visibility_dashboard_widget'
    );
}
add_action('wp_dashboard_setup', 'io_add_visibility_dashboard_widget');

function io_display_visibility_dashboard_widget()
{
    $post_types = ['post' => '文章', 'sites' => '网址', 'book' => '图书', 'app' => '应用'];
    
    // 处理批量设置操作
    if (isset($_POST['batch_set_visibility']) && wp_verify_nonce($_POST['_wpnonce'], 'batch_set_visibility')) {
        $processed = io_batch_set_default_visibility();
        echo '<div class="notice notice-success"><p>已为 ' . $processed . ' 篇文章设置默认权限（所有人可见）</p></div>';
    }
    
    echo '<div class="visibility-dashboard-widget">';
    
    // 检查是否有未设置权限的文章
    $total_unset = 0;
    foreach ($post_types as $post_type => $label) {
        $stats = io_get_all_visibility_stats($post_type);
        if (isset($stats['no_visibility'])) {
            $total_unset += $stats['no_visibility']['count'];
        }
    }
    
    if ($total_unset > 0) {
        echo '<div class="notice notice-warning" style="margin: 10px 0; padding: 10px;">';
        echo '<p><strong>发现 ' . $total_unset . ' 篇文章未设置权限，</strong>可能会导致用户无法访问</p>';
        echo '<form method="post" style="margin: 0;">';
        wp_nonce_field('batch_set_visibility');
        echo '<button type="submit" name="batch_set_visibility" class="button button-primary button-small">批量设置为所有人可见</button>';
        echo '</form>';
        echo '</div>';
    }
    
    foreach ($post_types as $post_type => $label) {
        $stats = io_get_all_visibility_stats($post_type);
        $total = array_sum(array_column($stats, 'count'));
        
        if ($total > 0) {
            echo "<h4>{$label} (总计: {$total})</h4>";
            echo '<table class="widefat striped">';
            
            foreach ($stats as $stat) {
                if ($stat['count'] > 0) {
                    $percentage = round(($stat['count'] / $total) * 100, 1);
                    
                    if ($stat['slug']) {
                        $url = add_query_arg([
                            'post_type' => $post_type,
                            'content_visibility' => $stat['slug']
                        ], admin_url('edit.php'));
                        $link_start = '<a href="' . esc_url($url) . '">';
                        $link_end = '</a>';
                    } else {
                        $link_start = '<span style="color: #d63638;">';
                        $link_end = '</span>';
                    }
                    
                    echo '<tr>';
                    echo '<td>' . $link_start . esc_html($stat['name']) . $link_end . '</td>';
                    echo '<td class="text-right">' . $stat['count'] . ' (' . $percentage . '%)</td>';
                    echo '</tr>';
                }
            }
            
            echo '</table><br>';
        }
    }
    
    echo '</div>';
    
    echo '<style>
        .visibility-dashboard-widget table { margin-bottom: 15px; }
        .visibility-dashboard-widget .text-right { text-align: right; }
        .visibility-dashboard-widget h4 { margin: 10px 0 5px 0; color: #23282d; }
        .visibility-dashboard-widget .notice { border-left: 4px solid #ffb900; }
    </style>';
} 