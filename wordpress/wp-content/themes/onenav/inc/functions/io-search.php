<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2023-03-23 22:09:11
 * @LastEditors: iowen
 * @LastEditTime: 2025-06-03 23:33:23
 * @FilePath: /onenav/inc/functions/io-search.php
 * @Description: 
 */

/**
 * 获取搜索列表或者名称
 * 根据提供的键($key)，返回对应的搜索类型名称或者全部的搜索类型名称列表
 * 如果没有提供键或者键不存在，则返回全部搜索类型名称列表
 * 
 * @param mixed $key 可选参数，指定搜索类型的键，例如'sites', 'post', 'app', 'book'
 * @return mixed 根据$key参数的不同，返回全部搜索类型名称列表或指定搜索类型的名称
 */
function io_get_search_type_name($key = '') {
    $name = io_get_post_type_name();
    if (empty($key)) {
        return $name;
    }
    if (!isset($name[$key])) {
        $key = io_get_search_types()[0];
    }
    return $name[$key];
}

/**
 * 获取 HEAD 搜索模块
 * @param mixed $search_id
 * @param int $index 模块id
 * @return void
 */
function io_head_search($search_id ='', $index = 0) {
    $search_big = io_get_option('search_skin', '') ?: false;

    // 设置查询变量
    set_query_var('search_list_id', $search_id);

    $class  = ' module-id-' . $index . (($index) ? '' : ' header-calculate');
    $style  = '';
    $fx     = '';
    $search = '';
    if ($search_big && $search_big['search_big'] == '1') {
        $class .= ' header-big ' . $search_big['big_skin'];
        if ($search_big['big_skin'] != 'no-bg'){
            // 内容上移
            if($search_big['post_top']){
                $class .= ' post-top';
            }
            // 渐变背景
            if($search_big['bg_gradual']){
                $class .= ' bg-gradual';
            }
            // 压暗背景
            if(!$search_big['changed_bg']){
                $class .= ' unchanged';
            }
        }

        if ('css-color' === $search_big['big_skin']) {
            $style = 'style="background-image: linear-gradient(45deg, ' . $search_big['search_color']['color-1'] . ' 0%, ' . $search_big['search_color']['color-2'] . ' 50%, ' . $search_big['search_color']['color-3'] . ' 100%);"';
        } elseif ('css-img' === $search_big['big_skin']) {
            $style = 'style="background-image: url(' . $search_big['search_img'] . ')"';
        } elseif ('css-bing' === $search_big['big_skin']) {
            $style = 'style="background-image: url(' . get_bing_img_cache(rand(0, 5), 'full') . ')"';
        }

  
        if ($search_big['big_skin'] == "canvas-fx") {
            $_fx = '';
            if ($search_big['canvas_id'] == 'custom') {
                $_fx = $search_big['custom_canvas'];
            } else {
                $_fx = get_theme_file_uri('/assets/fx/io-fx' . sprintf("%02d", ($search_big['canvas_id'] == 0 ? rand(1, 17) : $search_big['canvas_id'])) . '.html');
            }
            $fx = '<iframe class="canvas-bg" scrolling="no" sandbox="allow-scripts allow-same-origin" src="' . esc_attr($_fx) . '"></iframe>';
        }
 
        // 加载搜索模块
        $search = io_big_search_html();

        // 加载公告模块
        if (is_home() || is_front_page()) {
            $search .= io_head_bulletin_box('bulletin-big');
        }

        $search .= iopay_get_auto_ad_html((is_mininav() ? 'page' : 'home'), 'my-3 my-md-5', 'search', false);
    } else {
        // 加载公告模块
        if (is_home() || is_front_page()) {
            $search .= io_head_bulletin_box();
        }
        // 加载搜索模块 
        $search .= io_simple_search_html();
        //加载广告模块
        $search .= show_ad('ad_home_top', true, 'container', '<div class="apd-body-fill text-center">', '</div>', false);
    }
    
    echo '<div class="header-banner mb-4 ' . $class . '" ' . $style . '>';
    echo $fx;
    echo '<div class="switch-container search-container content' . get_page_mode_class() . '">';
    echo $search;
    echo '</div>';
    echo '</div>';
}

/**
 * BIG 搜索
 * 
 * @since 5.0.0
 * @return string
 */
function io_big_search_html() {
    $search_data = get_search_list();
    $search_skin = io_get_option('search_skin', array());

    $search_id   = $search_data['id'];
    $search_list = $search_data['list'];

    if ($search_skin['search_station']) {
        $in_site = array(
            'id'      => 'group-onsite',
            'name'    => __('站内', 'i_theme'),
            'default' => 'type-big-zhannei',
            'list'    => array(
                array(
                    'name'        => '',
                    'placeholder' => __('输入关键字搜索', 'i_theme'),
                    'id'          => 'type-big-zhannei',
                    'url'         => esc_url(home_url('?post_type=' . io_get_search_types()[0] . '&s=')),
                ),
            )
        );
        if(empty($search_list)){
            $search_list = array($in_site);
        }else{
            array_unshift($search_list, $in_site);
        }
    }

    if (empty($search_list)) {
        return '<div class=" text-center py-5">搜索源列表为空</div>';
    }

    $default_search_default = $search_list[0]['default'];
    $default_search = array();
    foreach ($search_list[0]['list'] as $item) {
        if ($item['id'] === $default_search_default) {
            $default_search = $item;
            break;
        }
    }

    if(empty($default_search)){
        return '';
    }

    // 大标题
    $title = '';
    if (is_bookmark()) { // 书签
        global $bookmark_id, $bookmark_user, $bookmark_set;
        $big_title = get_bookmark_seting('sites_title', $bookmark_set);
        if ($big_title != '' && get_bookmark_seting('hide_title', $bookmark_set)) {
            $title = '<p class="h1" style="letter-spacing: 6px;">' . $big_title . '</p>';
        }
    } elseif ($search_skin['big_title']) {
        $title = '<h2 class="h1" style="letter-spacing: 6px;">' . _iol($search_skin['big_title']) . '</h2>';
    }
    $title = $title ? '<div class="big-title text-center mb-3 mb-md-4">' . $title . '</div>' : '';

    // 搜索菜单
    $list_menu = '';
    if (!empty($search_list) && count($search_list) > 1) {
        foreach ($search_list as $index => $value) {
            $list_menu .= '<div class="search-menu slider-li ' . ($index == 0 ? 'active' : '') . '" data-default="' . esc_attr($value['default']) . '" data-target="#' . esc_attr($value['id']) . '" data-id="' . esc_attr($value['id']) . '">' . esc_attr($value['name']) . '</div>';
        }
    }
    $menu = '<div class="search-list-menu no-scrollbar overflow-x-auto slider-ul">';
    $menu .= '<div class="anchor" data-width="30" style="position: absolute; left: 50%; opacity: 0;"></div>';
    $menu .= $list_menu;
    $menu .= '</div>';

    // 搜索表单
    $form = '<form action="' . esc_attr($default_search['url']) . '" method="get" target="_blank" data-page="' . esc_attr($search_id) . '" class="search-form">';
    $form .= '<input type="text" id="search-text" class="form-control search-key" data-smart-tips="false" placeholder="' . esc_attr($default_search['placeholder']) . '" style="outline:0" autocomplete="off" data-status="true">';
    /**
     * 搜索工具过滤器
     * 
     * @since 5.0.0
     * @var mixed $search_tools 搜索工具
     * @var mixed $search_id 搜索ID
     */
    $search_tools = apply_filters('io_search_tools', '', $search_id);
    $form .= '<div class="search-tools">' . $search_tools . '<span type="submit" class="btn vc-theme search-submit-btn"><i class="iconfont icon-search"></i></span></div>';
    $form .= '</form> ';

    // 搜索组列表
    $list_group = '';
    if (!empty($search_list)) {
        foreach ($search_list as $index => $value) {
            $list_group .= '<ul id="' . $value['id'] . '" class="search-group ' . $value['id'] . ' no-scrollbar overflow-x-auto ' . ($index == 0 ? 'active' : '') . '">';
            if (count($value['list']) > 1 || count($search_list) > 1 || 'group-onsite' === $value['id']) {
                foreach ($value['list'] as $s) {
                    $class      = ($index == 0 && $s['id'] == $value['default'] ? 'active ' . $s['id'] : $s['id']);
                    $list_group .= '<li class="search-term ' . $class . '" data-id="' . $s['id'] . '" data-value="' . $s['url'] . '" data-placeholder="' . $s['placeholder'] . '">' . $s['name'] . '</li>';
                    if ('group-onsite' === $value['id']) {
                        $list_group .= io_get_hot_search('', 'big');
                    }
                }
            }
            $list_group .= '</ul>';
        }
    }
    $group = '<div class="search-list-group">';
    $group .= $list_group;
    $group .= '</div>';

    // 智能提示
    $smart = get_smart_tips_html($search_id);

    $html = '<div id="search" class="big-search mx-auto" style="--big-search-height:' . $search_skin['height'] . 'px;--big-mobile-height:' . $search_skin['mobile_height'] . 'px">';
    $html .= $title;
    $html .= '<div class="search-box-big">';
    $html .= $menu;
    $html .= $form;
    $html .= $group;
    $html .= $smart;
    $html .= '</div>';
    $html .= '</div>';
    
    return $html;
}
/**
 * 简单搜索框
 * 
 * @since 5.0.0
 * @return string
 */
function io_simple_search_html() {
    $search_data = get_search_list();
    $search_id   = $search_data['id'];
    $search_list = $search_data['list'];

    if (empty($search_list)) {
        $in_site     = array(
            'id'      => 'group-onsite',
            'name'    => __('站内', 'i_theme'),
            'default' => 'type-big-zhannei',
            'list'    => array(
                array(
                    'name'        => '',
                    'placeholder' => __('输入关键字搜索', 'i_theme'),
                    'id'          => 'type-big-zhannei',
                    'url'         => esc_url(home_url('?post_type=' . io_get_search_types()[0] . '&s=')),
                ),
            )
        );
        $search_list = array($in_site);
    }

    $list_menu  = '';
    $list_group = '';
    $show_name  = '';
    if (!empty($search_list) && (count($search_list) > 1 || $search_list[0]['id'] !== 'group-onsite')) {
        foreach ($search_list as $index => $value) {            
            $list_menu .= '<div class="search-menu slider-li dropdown-item ' . ($index == 0 ? 'active' : '') . '" data-default="' . $value['default'] . '" data-target="#' . $value['id'] . '" data-id="' . $value['id'] . '">' . $value['name'] . '</div>';
            if ($index == 0) {
                $show_name = $value['name'];
            }
            $list_group .= '<ul id="' . $value['id'] . '" class="search-group no-scrollbar overflow-x-auto ' . $value['id'] . ' ' . ($index == 0 ? 'active' : '') . '">';
            foreach ($value['list'] as $s) {
                $class      = ($index == 0 && $s['id'] == $value['default'] ? 'active ' . $s['id'] : $s['id']);
                $list_group .= '<li class="search-term ' . $class . '" data-id="' . $s['id'] . '" data-value="' . $s['url'] . '" data-placeholder="' . $s['placeholder'] . '">' . $s['name'] . '</li>';
            }
            $list_group .= '</ul>';
        }
    }
    $menu = '';
    if (!empty($list_group)) {
        $menu = '<div class="dropdown hover" select-dropdown>';
        $menu .= '<a href="javascript:" role="button" class="btn" aria-expanded="false"><span class="select-item">' . $show_name . '</span><i class="iconfont i-arrow icon-arrow-r ml-2"></i></a>';
        $menu .= '<div class="dropdown-menu">';
        $menu .= $list_menu;
        $menu .= '</div></div>';
    }

    $in_link = esc_url(home_url('?post_type=' . io_get_search_types()[0] . '&s='));
    // 搜索表单
    $form = '<form action="' . $in_link . '" method="get" target="_blank" data-page="' . $search_id . '" class="search-form">';
    $form .= '<input type="text" id="search-text" class="form-control smart-tips search-key" data-smart-tips="false" placeholder="' . __('输入关键字搜索', 'i_theme') . '" style="outline:0" autocomplete="off" data-status="true">';
    $form .= '<div class="search-tools"><span type="submit" class="btn vc-theme search-submit-btn"><i class="iconfont icon-search"></i></span></div>';
    $form .= '</form> ';


    $html = '<div id="search" class="simple-search mx-auto my-5">';
    $html .= '<div id="search-list" class="simple-group-list">';
    $html .= $menu;
    $html .= $list_group;
    $html .= '</div>';
    $html .= $form;
    $html .= get_smart_tips_html($search_id);
    $html .= '</div>';

    return $html;
}

/**
 * 搜索智能提示窗口
 * 
 * @param mixed $search_id
 * @return mixed
 */
function get_smart_tips_html($search_id){
    $smart = '<div class="card search-smart-tips" style="display: none">';
    $smart .= '<ul></ul>';
    $smart .= '<div class="search-smart-meta d-none d-md-flex">';
    $smart .= '<span class="key">↵</span>';
    $smart .= '<span class="label mr-4">ENTER</span>';
    $smart .= '<span class="key">↓</span><span class="key">↑</span>';
    $smart .= '<span class="label">NAV</span>';
    $smart .= '</div>';
    $smart .= '</div>';
    /**
     * 搜索智能提示窗口过滤器
     * 
     * @since 5.0.0
     * @var mixed $smart 搜索智能提示 html
     * @var mixed $search_id 搜索ID
     */
    $smart = apply_filters('io_search_smart_tips', $smart, $search_id);
    return $smart;
}
/**
 * 获取搜索类型
 * @return array
 */
function io_get_search_types(){
    $search_types = (array)io_get_option('search_page_post_type', array('sites'));

    $posts_type_s = wp_parse_args((array) io_get_option('posts_type_s'), ['post']);
    $search_types = array_values(array_intersect($search_types, $posts_type_s));

    if(empty($search_types) || !is_array($search_types)){
        $search_types = array('sites');
    }
    return (array)apply_filters('io_page_search_types', $search_types);
}

/**
 * 获取搜索关键词和类型
 * @param mixed $keywords
 * @return array
 */
function io_get_key_data($keywords){
    $keywords = str_replace('&amp;', '&', $keywords);
    $data = explode('&', $keywords);
    if (count($data) > 1) {
        return [$data[0], $data[1]];
    }else{
        return [$data[0], io_get_search_types()[0]];
    }
}
/**
 * 保存搜索历史词
 * @param mixed $s 关键词
 * @param mixed $type 文章类型
 * @return void
 */
function io_set_history_search($s, $type) {
    $s = strip_tags($s);
    $is_history = !empty(io_get_option('local_search_config', '', 'history_title'));
    if ($is_history && io_strlen($s) >= 0 && io_strlen($s) < 90) {
        if(empty($type)){
            $type = io_get_search_types()[0];
        }
        $old_k = !empty($_COOKIE['io_history_search']) ? json_decode(stripslashes($_COOKIE['io_history_search'])) : array();
        if (!is_array($old_k)) {
            $old_k = array();
        }

        foreach ($old_k as $k => $v) {
            if (io_get_key_data($v)[0] == $s) {
                unset($old_k[$k]);
            }
        }

        $expire = time() + 3600 * 24 * 30;
        array_unshift($old_k, $s . '&' . $type);
        setcookie('io_history_search', json_encode($old_k), $expire, '/', '', false);
    }
}

/**
 * 获取站内搜索模块HTML
 * @param mixed $args
 * @return mixed
 */
function io_search_body_html($args = array()) {
    $config = io_get_option('local_search_config', array());

    $defaults = array(
        'class'             => '',
        'placeholder'       => _iol($config['placeholder']),
        'hot_search_title'  => _iol($config['hot_search_title']),
        'hot_search_preset' => $config['hot_search_preset'],
        'history_title'     => _iol($config['history_title']),
        'show_hot_search'   => empty($config['hot_search_title']) ? false : true,
        'show_history'      => empty($config['history_title']) ? false : true,
        'show_gadget'       => $config['gadget'],
        'echo'              => false,
    );
    $args     = wp_parse_args($args, $defaults);
    $class    = $args['class'];

    $type = '';
    $search_types  = io_get_search_types();
    $defaults_type = get_query_var('post_type') ?: $search_types[0];
    if (empty($defaults_type) || 'any' === $defaults_type) {
        $defaults_type = $search_types[0];
    }
    if (count($search_types) > 1) {
        $defaults_name = io_get_search_type_name($defaults_type);
        $type .= '<div class="dropdown" select-dropdown>';
        $type .= '<a href="javascript:" role="button" class="btn" data-toggle="dropdown" aria-expanded="false"><span class="select-item">' . $defaults_name . '</span><i class="iconfont i-arrow icon-arrow-b ml-2"></i></a>';
        $type .= '<input type="hidden" name="post_type" value="' . $defaults_type . '">';
        $type .= '<div class="dropdown-menu">';
        foreach ($search_types as $value) {
            $type .= '<a class="dropdown-item" href="javascript:" data-value="' . $value . '">' . io_get_search_type_name($value) . '</a>';
        }
        $type .= '</div>';
        $type .= '</div>';
    } else {
        $type .= '<input type="hidden" name="post_type" value="' . $defaults_type . '">';
    }

    $form_html = '<form role="search" method="get" class="search-form search-card" action="' . esc_url(home_url('/')) . '">';
    $form_html .= '<div class="search-box">';
    $form_html .= $type;
    $form_html .= '<input type="search" class="form-control" required="required" placeholder="' . esc_attr($args['placeholder']) . '" value="' . get_search_query() . '" name="s" />';
    $form_html .= '<button type="submit" class="btn vc-theme search-submit"><i class="iconfont icon-search"></i></button>';
    $form_html .= '</div>';
    $form_html .= '</form>';

    $hot_search   = '';
    if ($args['show_hot_search']) {
        $hot_link = io_get_hot_search($args['hot_search_preset']);
        if ($hot_link) {
            $hot_search = io_get_keyword_link_html($args['hot_search_title'], $hot_link, 'hot');
        }
    }

    $history_html = '';
    if ($args['show_history']) {
        $history_link = io_get_history_search();
        if ($history_link) {
            $history_html = io_get_keyword_link_html($args['history_title'], $history_link);
        }
    }
    
    $gadget_html = '';
    if (!is_search() && $args['show_gadget']) {
        $gadget = get_dynamic_sidebar('sidebar-modal-search');
        if ($gadget) {
            $gadget_html = $gadget;
        }elseif(is_super_admin()){
            $gadget_html = '<div class="search-gadget-box search-card mt-3"><div class="text-muted">请添加小工具到 “弹窗搜索框小工具” 侧边栏。</div></div>';
        }
    }
    $keywords_box = '';
    if($hot_search || $history_html){
        $keywords_box = '<div class="search-keywords-box flex-fill">';
        $keywords_box .= $hot_search;
        $keywords_box .= $history_html;
        $keywords_box .= '</div>';
    }

    $html = '<div class="search-body ' . $class . '">';
    $html .= $form_html;
    $html .= '<div class="search-body-box d-flex flex-column flex-md-row">';
    $html .= $keywords_box;
    $html .= $gadget_html;
    $html .= '</div>';
    $html .= '</div>';

    if ($args['echo']) {
        echo $html;
    } else {
        return $html;
    }
}

/**
 * 获取搜索关键词链接HTML
 * @param mixed $title
 * @param mixed $link
 * @param mixed $type
 * @param mixed $class
 * @return string
 */
function io_get_keyword_link_html($title, $link, $type = 'history', $class = '') {
    $html = '<div class="keywords-box search-card ' . $class . ' mt-3">';
    $html .= '<div class="text-muted d-flex align-items-center">';
    $html .= '<span>' . $title . '</span>';
    $html .= 'history' == $type ? '<a href="javascript:;" class="ml-auto text-ss text-muted px-2 py-1 trash-history-search">'.__('清空','i_theme').'</a>' : '';
    $html .= '</div>';
    $html .= '<div class="mt-2 search-keywords">' . $link . '</div>';
    $html .= '</div>';
    return $html;
}

/**
 * 获取搜索历史关键词
 * @return string|bool
 */
function io_get_history_search() {
    $keywords = !empty($_COOKIE['io_history_search']) ? json_decode(stripslashes($_COOKIE['io_history_search'])) : '';
    if (!is_array($keywords)) {
        return false;
    }

    $keyword_link = '';
    foreach ($keywords as $key) {
        $data = io_get_key_data($key);
        $key = $data[0] . '&post_type=' . $data[1];
        $keyword_link .= '<a class="s-key btn" href="' . esc_url(home_url('/')) . '?s=' . esc_attr($key) . '">' . esc_attr($data[0]) . '</a>';
    }
    return $keyword_link;
}

/**
 * 获取热门搜索关键词
 * @param mixed $hots
 * @return bool|string
 */
function io_get_hot_search($hots = '', $type = '') {
    if(empty($hots)){
        $hots = io_get_option('local_search_config', '', 'hot_search_preset');
    }
    $hots = str_replace('@', '&', $hots);
    $keywords = io_split_str($hots);
    if (!is_array($keywords)) {
        return false;
    }

    $count = 'big' === $type? 8 : 30;

    $keyword_link = '';
    foreach ($keywords as $index => $key) {
        if ($index >= $count) {
            break;
        }
        $data = io_get_key_data($key);
        $key = $data[0] . '&post_type=' . $data[1];
        if($type == 'big'){
            $keyword_link .= '<li><a href="' . esc_url(home_url('/')) . '?s=' . esc_attr($key) . '">' . esc_attr($data[0]) . '</a></li>';
        } else {
            $keyword_link .= '<a class="s-key btn" href="' . esc_url(home_url('/')) . '?s=' . esc_attr($key) . '">' . esc_attr($data[0]) . '</a>';
        }
    }
    return $keyword_link;
}

/**
 * 搜索频率限制
 * 
 * @param WP_Query $query
 * @return WP_Query
 */
function io_limit_search_frequency($query) {
    if (!$query->is_search() || is_admin() || !$query->is_main_query() || is_super_admin()) {
        return $query;
    }
    if ($query->is_main_query() && !is_admin()) {
        $search_page = io_get_search_types();
        $post_type   = $query->get('post_type');

        if ($post_type === 'any' || !in_array($post_type, array('sites', 'app', 'book', 'post'))) {
            $query->set('post_type', $search_page[0]);
        }
        if (is_super_admin()) {
            return $query;
        }
    }

    if(io_get_option('local_search_login', false)){
        if(!is_user_logged_in()){
            wp_die('您必须先登录才能进行搜索。');
        }
    }

    $defaults = array(
        'limit'     => 2,
        'time'      => 10,
        'black'     => '赌博,博彩,彩票',
        'count'     => 10,
        'limit_msg' => '您搜索过于频繁，请稍后再试。',
        'black_msg' => '您的搜索包含了禁止的关键词，请重新输入。',
        'count_msg' => '搜索词过长，最多10个字。',
    );

    $config = io_get_option('search_limit_config', $defaults);

    $search_term = $query->get('s');

    if(empty($search_term)){
        io_limit_search_before(__('请输入搜索关键词。', 'i_theme'));
    }

    // 判断搜索词长度，中文字算一个字
    if ($config['count'] && io_strlen($search_term) > $config['count']) {
        io_limit_search_before($config['count_msg']);
    }

    // 判断搜索词是否包含黑名单词
    if (!empty($config['black'])) {
        $blacklist = io_split_str($config['black']);
        foreach ($blacklist as $ban) {
            if (stripos($search_term, $ban) !== false) {
                io_limit_search_before($config['black_msg']);
            }
        }
    }

    // 搜索频率限制
    if (empty($config['time'])) {
        // 如果时间限制为空，则不限制
        return $query;
    }
    $limit  = $config['limit']; // 搜索限制（计数）
    $l_time = $config['time']; // 时间（秒）

    $current_time = time();
    $cookie_name  = 'search_frequency';
    $cookie_data  = isset($_COOKIE[$cookie_name]) ? json_decode(stripslashes($_COOKIE[$cookie_name]), true) : array();

    if (empty($cookie_data)) {
        $cookie_data = array('timestamp' => $current_time, 'count' => 1);
    } else {
        $timestamp = $cookie_data['timestamp'];

        if (($current_time - $timestamp) > $l_time) {
            $cookie_data['timestamp'] = $current_time;
            $cookie_data['count']     = 1;
        } else {
            $cookie_data['count']++;
        }
    }

    setcookie($cookie_name, json_encode($cookie_data), $current_time + $l_time, '/');


    if ($cookie_data['count'] > $limit) {
        io_limit_search_before($config['limit_msg']);
    }

    return $query;
}
add_action('pre_get_posts', 'io_limit_search_frequency');

function io_limit_search_before($msg) {
    if (!empty($msg)) {
        wp_die($msg);
    }
    exit;
}
