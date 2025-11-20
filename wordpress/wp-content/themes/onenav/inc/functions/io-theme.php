<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2024-08-26 20:03:06
 * @LastEditors: iowen
 * @LastEditTime: 2025-06-05 14:21:29
 * @FilePath: /onenav/inc/functions/io-theme.php
 * @Description: 
 */

/**
 * 收藏按钮
 * @param mixed $post_id
 * @param mixed $display
 * @return mixed
 */
function get_posts_star_btn($post_id, $class = 'mr-3', $max = false)
{
    if (!io_user_center_enable() || !function_exists('io_get_posts_star_count')) {
        return '';
    }
    $post_type = get_post_type($post_id);

    $count = io_get_posts_star_count($post_id);
    $liked = io_is_my_star($post_id, $post_type);
    $title = __('收藏', 'i_theme');

    $class .= $liked ? ' liked' : '';

    $ico = '<i class="iconfont icon-collection' . ($liked ? '' : '-line') . ' mr-1" data-class="icon-collection icon-collection-line"></i>';

    $btn = sprintf('
        <a href="javascript:;" data-type="favorite" data-post_type="%1$s" data-post_id="%2$s" data-ticket="%3$s" class="io-posts-like %4$s" data-toggle="tooltip" title="%5$s">%6$s
            <small class="star-count text-xs">%7$s</small>
        </a>',
        $post_type,
        $post_id,
        wp_create_nonce('posts_like_nonce'),
        $class,
        __('收藏', 'i_theme'),
        $ico . ($max ? $title : ''),
        io_number_format($count)
    );

    return $btn;
}
 
/**
 * 获取文章点赞按钮
 * @param mixed $class
 * @param mixed $pid
 * @param mixed $count
 * @return mixed
 */
function get_posts_like_btn($post_id = '', $class = 'mr-3', $max = false)
{
    $post_id   = $post_id ? $post_id : get_the_ID();
    $post_type = get_post_type($post_id);
    $count     = io_get_posts_like_count($post_id);

    if (io_is_my_like($post_id)) {
        $class .= ' liked';
    }
    $title = __('点赞', 'i_theme');
    $ico   = '<i class="iconfont icon-like-line mr-1"></i>';

    $btn = sprintf('
        <a href="javascript:;" data-type="like" data-post_type="%1$s" data-post_id="%2$s" data-ticket="%3$s" class="io-posts-like %4$s" data-toggle="tooltip" title="%5$s">%6$s
            <small class="star-count text-xs">%7$s</small>
        </a>',
        $post_type,
        $post_id,
        wp_create_nonce('posts_like_nonce'),
        $class,
        $title,
        $ico . ($max ? $title : ''),
        io_number_format($count)
    );

    return $btn;
}

function io_get_posts_like_count($post_id)
{
    $post_id = $post_id ? $post_id : get_the_ID();

    $like = get_post_meta($post_id, '_like_count', true);

    if ('' === $like) {
        // 初始化点赞数
        $like_n = io_get_option('like_n', 0);
        if ($like_n > 0) {
            $like = mt_rand(0, 10) * $like_n;
        } else {
            $like = 0;
        }
        update_post_meta($post_id, '_like_count', $like);
    }
    return $like;
}

/**
 * 判断是否文章点赞
 * @param mixed $post_id
 * @param mixed $post_type 文章类型
 * @return bool
 */
function io_is_my_like($post_id = '', $post_type = '')
{
    if (!is_user_logged_in()) {
        return isset($_COOKIE['liked_' . $post_id]);
    }

    if (empty($post_type)) {
        $post_type = get_post_type($post_id);
    }

    $user_like_post = (array) get_user_meta(get_current_user_id(), 'io_like_' . $post_type, true);
    if (in_array($post_id, $user_like_post)) {
        return true;
    }
    return false;
}

/**
 * 获取文章数量
 * @param mixed $type
 * @param mixed $force_refresh 强制刷新
 * @return mixed
 */
function io_count_posts($type = 'post', $force_refresh = false)
{
    $cache_key = "posts-{$type}";

    $count = wp_cache_get($cache_key, 'io_counts');

    if (false === $count || $force_refresh) {
        $count = wp_count_posts($type);
        wp_cache_set($cache_key, $count, 'io_counts', DAY_IN_SECONDS); // 缓存1天
    }

    return $count;
}

/**
 * 获取用户文章的mate求和
 * @param mixed $user_id 用户ID  all 为所有用户
 * @param mixed $meta
 * @return mixed
 */
function get_user_posts_meta_sum($user_id, $meta)
{
    global $wpdb;
    if (!$user_id || !$meta) {
        return 0;
    }
    $cache_key = 'user_' . $user_id . '_posts_' . $meta . '_sum';
    $num       = wp_cache_get($cache_key, 'user_posts_meta_sum', true);
    if (false === $num) {
        $sql = "SELECT SUM(`meta_value`+0)
                FROM $wpdb->posts
                LEFT JOIN $wpdb->postmeta ON ($wpdb->posts.`ID` = $wpdb->postmeta.`post_id`)
                WHERE `meta_key` = %s 
                AND `post_status` = 'publish'";

        // 所有用户
        if ($user_id !== 'all') {
            $sql .= $wpdb->prepare(" AND `post_author` = %d", $user_id);
        }

        $num = $wpdb->get_var($wpdb->prepare($sql, $meta));

        wp_cache_set($cache_key, $num, 'user_posts_meta_sum', HOUR_IN_SECONDS * 12);//缓存12小时
    }
    return $num ? $num : 0;
}
/**
 * 作者评论数
 * @param mixed $user_id
 * @param mixed $comments_status
 * @return mixed
 */
function get_user_comment_count($user_id, $comments_status = 'approve') {
    if (!$user_id) {
        return 0;
    }

    $count = wp_cache_get($user_id, 'user_comment_' . $comments_status . '_count', true);
    if (false === $count) {
        $args = array(
            'user_id' => $user_id,
            'status'  => $comments_status,
            'type'    => 'comment',   // 排除 trackback 和 pingback
            'count'   => true,
        );
        $count = get_comments($args);
        wp_cache_set($user_id, $count, 'user_comment_' . $comments_status . '_count', HOUR_IN_SECONDS * 12);
    }
    return $count;
}

/**
 * 获取页面模式 class
 * 全宽还是容器
 * @param mixed $type 类型 <body> 和其他
 * @return string
 */
function get_page_mode_class($type = 'content') {
    $page_mode = io_get_option('page_mode', 'full');
    $class     = '';
    if ('body' === $type) {
        $class = $page_mode == 'full' ? ' full-container' : '';
    } else {
        $class = $page_mode == 'full' ? ' container-fluid' : ' container';
    }
    return $class;
}

/**
 * 获取页面模块配置信息
 * @param mixed $page_id 页面ID   默认 0 首页
 * @return bool|array
 */
function get_page_module_config($page_id = 0)
{
    $config = array();
    if ($page_id) {
        if (!is_mininav()) {
            return false;
        }
        $id = get_post_meta($page_id, 'page_module_id', true);
        if (empty($id)) {
            // 提示错误
            if(is_super_admin()){
                wp_die('页面模块错误，请编辑并<a href="' . admin_url('post.php?post=' . $page_id . '&action=edit') . '">指定配置</a>');
            }else{
                wp_die('页面模块错误，请联系管理员');
            }
        }
        $id = (int) $id - 1;

        $seconds = (array)io_home_option('second_page_list', array());
        if (count($seconds) > $id) {
            // 设置查询变量
            set_query_var('module_list_id', $id);
            $config = $seconds[$id];
        } else {
            // 提示错误
            if(is_super_admin()){
                wp_die('页面模块错误，请编辑并<a href="' . admin_url('post.php?post=' . $page_id . '&action=edit') . '">指定配置</a>');
            }else{
                wp_die('页面模块错误，请联系管理员');
            }
        }
    } else {
        $config = array(
            'aside_show'  => io_home_option('aside_show', false),
            'page_module' => io_home_option('page_module'),
        );
    }
    return $config;
}

/**
 * 为编辑器添加允许的标签
 * @param mixed $allowed_tags 数据
 * @param mixed $context 上下文
 * @return mixed
 */
function io_allow_html_iframe_attributes($allowed_tags, $context)
{
    if ($context !== 'post' || !io_get_option('allow_iframe', false)) {
        return $allowed_tags;
    }
    $allowedAttributes = array(
        'id'              => true,
        'src'             => true,
        'class'           => true,
        'border'          => true,
        'height'          => true,
        'width'           => true,
        'frameborder'     => true,
        'allowfullscreen' => true,
        'contenteditable' => true,
        'data-*'          => true,
    );

    if (isset($allowed_tags['iframe'])) {
        $allowed_tags['iframe'] = array_merge($allowed_tags['iframe'], $allowedAttributes);
    } else {
        $allowed_tags['iframe'] = $allowedAttributes;
    }

    return $allowed_tags;
}
/**
 * 自定义允许的 HTML 标签和属性
 * @param mixed $allowed_tags
 * @return mixed
 */
function io_custom_allowed_html($allowed_tags, $context)
{
    if ($context !== 'post') {
        return $allowed_tags;
    }
    $extra_attributes = array(
        'id'              => true,
        'class'           => true,
        'height'          => true,
        'width'           => true,
        'allowfullscreen' => true,
        'contenteditable' => true,
        'data-*'          => true,
    );

    foreach (['div', 'span'] as $tag) {
        if (isset($allowed_tags[$tag])) {
            $allowed_tags[$tag] = array_merge($allowed_tags[$tag], $extra_attributes);
        } else {
            $allowed_tags[$tag] = $extra_attributes;
        }
    }

    return $allowed_tags;
}
add_filter( 'wp_kses_allowed_html', 'io_custom_allowed_html', 10, 2 );

/**
 * 获取自定义搜索列表序号
 * @return array 
 */
function get_search_min_list()
{
    $search_min_list  = get_option('io_search_list', false);
    $is_custom_search = io_get_option('custom_search', false);
    if (!$is_custom_search || !$search_min_list) {
        return array('默认搜索列表');
    }
    if (!isset($search_min_list['search_list']) || empty($search_min_list['search_list']) || !isset($search_min_list['custom_search_list'])) {
        return array('没有添加搜索项');
    }

    $list = array('默认搜索列表');
    if (isset($search_min_list['custom_search_list']) && !empty($search_min_list['custom_search_list'])) {
        foreach ($search_min_list['custom_search_list'] as $v) {
            $list[] = $v['search_list_id'];
        }
    }
    return $list;
}

/**
 * 是否设置了自定义搜索
 * 
 * @return bool
 */
function is_custom_search()
{
    $search_min_list = get_option('io_search_list', false);
    if (!$search_min_list || !isset($search_min_list['custom_search_list']) || !isset($search_min_list['search_list'])){// || empty($search_min_list['search_list'])) {
        return false;
    }
    return true;
}

function get_seconds_module_list(){
    $seconds = io_home_option('second_page_list', array());
    if (empty($seconds)) {
        return array('没有添加次级页面配置项');
    }
    $list = array('选择页面模块配置');
    foreach ($seconds as $k => $v) {
        $list[] = $v['second_id'];
    }
    return $list;
}
/**
 * 获取空内容提示
 * 
 * @param mixed $text 提示文字
 * @param mixed $class 类名 max-svg min-svg
 * @param mixed $type 类型 none, error
 * @param mixed $row 是否行内
 * @return string
 */
function get_none_html($text = '', $class='', $type='none', $row = true) {
    if (empty($text)) {
        $text = __('没有内容', 'i_theme');
    }
    if(isset($_POST['page']) && $_POST['page'] > 1){
        $text = __('没有更多内容了', 'i_theme');
        $type = 'null';
    }

    $ico = '<img src="' . get_template_directory_uri() . '/assets/images/svg/wp_' . $type . '.svg' . '" alt="' . $type . '" class="nothing-svg">';

    $class = $class ? ' ' . $class . ' nothing-type-' . $type : ' nothing-type-' . $type;

    $html = '';
    $html .= $row ? '<div class="col-1a-i nothing-box' . $class . '">' : '';
    $html .= '<div class="nothing' . ($row ? '' : $class) . '">';
    $html .= $ico;
    $html .= '<div class="nothing-msg text-sm text-muted">' . $text . '</div>';
    $html .= '</div>';
    $html .= $row ? '</div>' : '';
    return $html;
}

/**
 * 获取面包屑导航
 * @param mixed $type 文章类型
 * @return string html
 */
function get_single_breadcrumb($type = 'post', $class = 'mb-3 mb-md-4')
{
    if (!io_get_option($type . '_breadcrumb', true)) {
        return '';
    }

    $taxonomy  = posts_to_cat($type);
    $separator = '<i class="text-color vc-theme px-1">•</i>';

    $html = '<nav class="text-xs ' . $class . '" aria-label="breadcrumb">';

    // 首页
    $html .= '<i class="iconfont icon-home"></i> ';
    $html .= '<a class="crumbs" href="' . esc_url(home_url()) . '/">';
    $html .= __('首页', 'i_theme');
    $html .= "</a>";

    // 次级导航链接
    if (isset($_GET['mininav-id'])) {// TODO 优化 修改 mininav-id 记录方法
        $nav_url  = get_permalink(intval($_GET['mininav-id']));
        $nav_name = get_post(intval($_GET['mininav-id']))->post_title;

        $html .= $separator;
        $html .= '<a class="crumbs" href="' . esc_url($nav_url) . '">' . $nav_name . '</a>';
    }

    // 分类
    $html .= get_the_terms_with_filtered_parents(get_the_ID(), $taxonomy, $separator);

    // 文章标题
    $html .= $separator;
    $html .= '<span aria-current="page">';
    $html .= wp_is_mobile() ? __('正文', 'i_theme') : get_the_title();
    $html .= '</span>';

    $html .= '</nav>';

    return $html;
}

/**
 * 递归获取分类的父级分类
 * @param int $term_id 当前分类ID
 * @param string $taxonomy 分类法名称
 * @return array 父级分类数组
 */
function get_term_parents_recursive($term_id, $taxonomy)
{
    $parents = array();
    $term    = get_term($term_id, $taxonomy);

    while ($term && !is_wp_error($term) && $term->parent != 0) {
        $term = get_term($term->parent, $taxonomy);
        if ($term && !is_wp_error($term)) {
            $parents[] = $term;
        }
    }

    return array_reverse($parents); // 反转数组，确保从顶级到当前分类
}

/**
 * 获取最下层分类（排除多余的父级分类）
 * @param int $post_id 文章ID
 * @param string $taxonomy 分类法名称
 * @return array 返回最下层分类数组
 */
function get_bottom_level_terms($post_id, $taxonomy)
{
    // 获取文章的所有分类
    $terms = get_the_terms($post_id, $taxonomy);
    if (empty($terms) || is_wp_error($terms)) {
        return array();
    }

    // 存储没有子分类的分类
    $bottom_level_terms = array();

    // 遍历每个分类，检查它是否有子分类
    foreach ($terms as $term) {
        // 查询是否有子分类
        $child_terms = get_terms(array(
            'taxonomy'   => $taxonomy,
            'parent'     => $term->term_id,
            'hide_empty' => false,
        ));

        // 如果没有子分类，则这是最下层分类
        if (empty($child_terms)) {
            $bottom_level_terms[] = $term;
        }
    }

    return $bottom_level_terms;
}

/**
 * 获取带有父级分类过滤的最下层分类路径
 * @param int $post_id 文章ID
 * @param string $taxonomy 分类法名称
 * @param string $separator 分隔符
 * @return string 返回面包屑HTML字符串
 */
function get_the_terms_with_filtered_parents($post_id, $taxonomy, $separator)
{
    $bottom_level_terms = get_bottom_level_terms($post_id, $taxonomy);

    $html = '';

    if (!empty($bottom_level_terms)) {
        foreach ($bottom_level_terms as $term) {
            // 获取当前分类的父级分类并输出路径
            $parents = get_term_parents_recursive($term->term_id, $taxonomy);
            foreach ($parents as $parent) {
                $html .= $separator;
                $html .= '<a href="' . esc_url(get_term_link($parent)) . '">' . esc_html($parent->name) . '</a>';
            }

            $html .= $separator;
            $html .= '<a href="' . esc_url(get_term_link($term)) . '">' . esc_html($term->name) . '</a>';
        }
    }

    return $html;
}

/**
 * 获取发布文章按钮名称
 * 
 * @param mixed $post_type
 * @return string
 */
function get_new_post_name($post_type = '')
{
    $type_name = io_get_post_type_name($post_type);

    $data = array(
        'post'  => sprintf(__('发布%s', 'i_theme'), $type_name),
        'sites' => sprintf(__('提交%s', 'i_theme'), $type_name),
        'book'  => sprintf(__('提交%s', 'i_theme'), $type_name),
        'app'   => sprintf(__('提交%s', 'i_theme'), $type_name),
    );

    return isset($data[$post_type]) ? $data[$post_type] : __('发布文章', 'i_theme');
}

/**
 * 获取新建文章按钮
 * @param array $types 文章类型 
 * @param mixed $class
 * @param mixed $btn 显示的按钮
 * @return array|string|null
 */
function io_get_new_posts_btn($types = array('post'), $class = '', $btn = '')
{
    $html = '<div class="new-posts-btn hover-show ' . $class . '">';
    $html .= $btn;
    $html .= '<div class="sub-menu">';

    foreach ($types as $index => $type) {
        $href  = io_get_template_page_url('template-contribute.php');
        $href  = add_query_arg('type', $type, $href);
        $color = get_theme_color($index + 2, 'j');
        $title = get_new_post_name($type);

        $icon = '<span class="tips-box tips-icon ' . $color . '"><i class="iconfont icon-' . $type . '"></i></span>';
        $html .= '<a rel="nofollow" class="btn-new-posts menu-item" href="' . $href . '">' . $icon . '<span>' . $title . '</span></a>';
    }

    $html .= '</div>';
    $html .= '</div>';

    return $html;
}

/**
 * 获取子页面导航按钮
 * 
 * @param mixed $nav_menu_list
 * @return string
 */
function io_get_min_nav_menu_btn($nav_menu_list)
{
    $html = '<div class="sub-menu">';
    foreach ($nav_menu_list as $index => $menu) {
        $target = $menu['url']['target'] ? 'target="_blank"' : '';
        $color  = get_theme_color($index + 1, 'j');
        $html .= sprintf(
            '<a class="menu-item" href="%s" %s><span class="tips-box tips-icon %s"><i class="%s"></i></span><span class="line1 text-center w-100">%s</span></a>',
            esc_url($menu['url']['url']),
            $target,
            $color,
            $menu['icon'],
            $menu['url']['text']
        );
    }
    $html .= '</div>';

    return $html;
}

/**
 * 获取文章类型对应的名称
 * 
 * @param string $key 可选参数，例如'sites', 'post', 'app', 'book'
 *                    为空时返回所有文章类型名称
 * @return mixed 
 */
function io_get_post_type_name($key = '') {
    $default = array(
        'sites' => '网站',
        'post'  => '文章',
        'app'   => '软件',
        'book'  => '书籍',
    );

    $name = wp_parse_args((array) io_get_option('posts_title', $default), $default);

    $name = array_map(function ($value) {
        return _iol($value);
    }, $name);
    
    /**
     * 过滤文章类型名称
     * 
     * @var mixed $name
     */
    $name = apply_filters('io_post_type_name_filters', $name);
    if (empty($key)) {
        return $name;
    }
    if (!isset($name[$key])) {
        return '';
    }
    return $name[$key];
}

/**
 * 获取排序数据列表
 * 
 * @param mixed $type
 * @return mixed
 */
function io_get_sort_data($type = ''){
    $data = array(
        'modified' => '最近更新',
        'date'     => '最近添加',
        'views'    => '浏览数',
        'like'     => '点赞数',
        'start'    => '收藏数',
        'comment'  => '评论量',
        'down'     => '下载量',
        'rand'     => '随机',
    );
    /**
     * 过滤排序数据
     * 
     * @var mixed $data
     */
    $data = apply_filters('io_get_sort_data_filters', $data);
    if(empty($type)){
        return $data;
    }
    return isset($data[$type]) ? $data[$type] : '';
}
/**
 * 
 * @param mixed $location
 * @param mixed $bg
 * @param mixed $class
 * @param mixed $content
 * @param mixed $echo
 * @return string|void
 */
function io_box_head_html($location, $bg, $content = '', $class = '', $echo = true)
{
    $bg   = get_lazy_img_bg($bg);
    $html = '<div class="box-head box-head-' . $location . ' mb-4 ' . $class . '">';
    $html .= '<div class="box-head-bg">';
    $html .= '<div class="box-head-img" ' . $bg . '></div>';
    $html .= '</div>';
    $html .= '<div class="box-head-content page-head-content my-2 my-md-4">';
    $html .= $content;
    $html .= '</div>';
    $html .= '</div>';

    if ($echo) {
        echo $html;
    } else {
        return $html;
    }
}

/**
 * 
 * @param mixed $location
 * @param mixed $img_class
 * @param mixed $content
 * @param bool $echo
 * @return string|void
 */
function io_get_color_head_html($location, $content = '', $class = '', $echo = true)
{
    $html = '<div class="color-head color-head-' . $location . ' ' . $class . '">';
    $html .= '<div class="color-head-bg">';
    $html .= '<span class="color01"></span>';
    $html .= '</div>';
    $html .= '<div class="color-head-content page-head-content my-2 my-md-4">';
    $html .= $content;
    $html .= '</div>';
    $html .= '</div>';

    if ($echo) {
        echo $html;
    } else {
        return $html;
    }
}
