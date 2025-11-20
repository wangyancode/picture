<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2024-08-20 22:31:36
 * @LastEditors: iowen
 * @LastEditTime: 2025-03-26 23:25:12
 * @FilePath: /onenav/inc/functions/io-home.php
 * @Description: 
 */

function io_home_content($config)
{
    if (empty($config['page_module'])) {
        return;
    }
    if ($config['aside_show']) {
        // 显示侧栏菜单
        io_show_layout_aside($config['page_module']);
    }
    // 显示页面内容模块
    io_show_page_module($config['page_module']);
}

/**
 * 显示侧栏菜单
 * @param mixed $modules 模块
 * @return void
 */
function io_show_layout_aside($modules){
    $nav = '';
    foreach ($modules as $index => $module) {
        switch ($module['type']) {
            case 'search':
            case 'tools':
            case 'custom':
                $config = $module[$module['type'] . '_config'];
                $url    = $module['type'] === 'search' ? '#search' : '#module_id_' . $index;
                if (!empty($config['aside_name'])) {
                    $nav .= '<li><a href="' . $url . '" class="aside-btn hide-target smooth"><i class="' . $config['aside_icon'] . ' icon-fw"></i><span class="ml-2">' . $config['aside_name'] . '</span></a></li>';
                }
                break;
            case 'content':
                $config = $module['content_config'];
                $menus  = get_menu_items_by_level($config['nav_id']);
                if ($config['aside_s']) {
                    foreach ($menus as $menu) {
                        $class  = 'smooth';
                        $url    = '#term-' . $config['nav_id'] . $menu['object_id'];
                        $target = '';
                        if ($menu['type'] != 'taxonomy' && strlen(trim($menu['url'])) > 1) {
                            $url = trim($menu['url']);
                            if (substr($url, 0, 1) == '#') {
                                $class = 'smooth';
                            } elseif (substr($url, 0, 4) == 'http') {
                                if ($menu['target']) {
                                    $target = ' target="_blank"';
                                }
                                $class = '';
                            } else {
                                continue;
                            }
                        }
                        $sub = '';
                        if (isset($menu['children']) && !empty($menu['children'])) {
                            $sub = '<ul class="aside-sub d-none">';
                            foreach ($menu['children'] as $sub_menu) {
                                $sub_class = 'smooth';
                                $sub_url = $url . '-' . $sub_menu['object_id'];
                                $sub_target = '';
                                if ($sub_menu['type'] != 'taxonomy' && strlen(trim($sub_menu['url'])) > 1) {
                                    $sub_url = trim($sub_menu['url']);
                                    if (substr($sub_url, 0, 1) == '#') {
                                        $sub_class = 'smooth';
                                    } elseif (substr($sub_url, 0, 4) == 'http') {
                                        if ($sub_menu['target']) {
                                            $sub_target = ' target="_blank"';
                                        }
                                        $sub_class = '';
                                    } else {
                                        continue;
                                    }
                                }
                                $sub .= sprintf(
                                    '<li><a href="%s" class="aside-btn change-href %s"%s>%s</a></li>',
                                    esc_url($sub_url),
                                    esc_attr($sub_class),
                                    esc_attr($sub_target),
                                    esc_html($sub_menu['title'])
                                );
                            }
                            $sub .= '</ul>';
                        }
                        $nav .= sprintf(
                            '<li class="aside-item"><a href="%s" class="aside-btn hide-target %s"%s><i class="%s icon-fw"></i><span class="ml-2">%s</span></a>%s</li>',
                            esc_url($url),
                            esc_attr($class),
                            esc_attr($target),
                            esc_attr(get_tag_ico($menu['object'], $menu)),
                            esc_html($menu['title']),
                            $sub
                        );
                    }
                }
                break;
        }
    }
    $expand_text  = __('展开', 'i_theme');
    $shrink_text  = __('收起', 'i_theme');
    $expand_icon  = 'iconfont icon-expand icon-fw';
    $shrink_icon  = 'iconfont icon-shrink icon-fw';

    $min_nav = io_get_option('min_nav', false);
    $outdent = $min_nav?
                '<a href="javascript:;" class="aside-btn btn-outdent d-none d-md-block"><i class="'.$expand_icon.'" switch-class="'.$shrink_icon.'"></i><span class="ml-2" switch-text="' . $shrink_text . '">' . $expand_text . '</span></a>':
                '<a href="javascript:;" class="aside-btn btn-outdent d-none d-md-block"><i class="'.$shrink_icon.'" switch-class="'.$expand_icon.'"></i><span class="ml-2" switch-text="' . $expand_text . '">' . $shrink_text . '</span></a>';

    $aside = '<aside class="ioui-aside switch-container' . get_page_mode_class() . '">';
    $aside .= '<div class="aside-body" id="layout_aside">';
    $aside .= '<div class="aside-card blur-bg shadow h-100">';
    $aside .= '<ul class="aside-ul overflow-y-auto no-scrollbar">';
    $aside .= $nav;
    $aside .= '</ul>';
    $aside .= '<div class="aside-bottom mt-2 pt-2">';
    $aside .= $outdent;
    $aside .= '<a href="javascript:;" class="aside-btn d-block d-md-none" data-toggle-div data-target="#layout_aside" data-class="is-mobile"><i class="iconfont icon-shrink icon-fw"></i><span class="ml-2">' .  $shrink_text . '</span></a>';
    $aside .= '</div>';
    $aside .= '</div>';
    $aside .= '</div>';
    $aside .= '</aside>';

    echo $aside;
}
/**
 * 显示页面模块
 * @param mixed $modules 模块
 * @return void
 */
function io_show_page_module($modules)
{
    foreach ($modules as $index => $module) {
        switch ($module['type']) {
            case 'search':
                io_module_search($module['search_config'], $index);
                if (!$index) {
                    do_action('io_home_content_before');
                }
                break;
            case 'content':
                if (!$index) {
                    do_action('io_home_content_before');
                }
                io_module_content($module['content_config'], $index);
                break;
            case 'tools':
                if (!$index) {
                    do_action('io_home_content_before');
                }
                io_module_tools($module['tools_config'], $index);
                break;
            case 'custom':
                if (!$index) {
                    do_action('io_home_content_before');
                }
                io_module_custom($module['custom_config'], $index);
                break;
        }
    }

    /**
     * -----------------------------------------------------------------------
     * HOOK : ACTION HOOK
     * io_home_content_after
     * 
     * 页面模块内容后显示内容
     * @since 5.0
     * -----------------------------------------------------------------------
     */
    do_action('io_home_content_after');
}

/**
 * 首页内容前显示内容
 * @return void
 */
function io_home_content_before_action(){
    iopay_get_auto_ad_html('home', 'mb-4 container home-content', 'content');
    show_ad('ad_home_card_top', true, 'container home-content');
}
add_action('io_home_content_before', 'io_home_content_before_action');
/**
 * 首页内容后显示内容
 * @return void
 */
function io_home_content_after_action(){
    show_ad('ad_home_link_top', true, 'container home-content');
}
add_action('io_home_content_after', 'io_home_content_after_action');

/**
 * 搜索模块
 * @param mixed $config
 * @param int $index 模块id
 * @return void
 */
function io_module_search($config, $index){
    io_head_search($config['search_id'], $index);
}

/**
 * 判断是否有侧边栏
 * @param mixed $config
 * @param mixed $is_sidebar 返回是否有侧边栏
 * @param mixed $sidebar_id 返回侧边栏id
 * @return void
 */
function io_module_is_there_sidebar($config, &$is_sidebar, &$sidebar_id) {
    $sidebar_id = '';
    $is_sidebar = false;
    if ('none' !== $config['sidebar_tools']) {
        $sidebar_id = 'sidebar-home-content-' . $config['sidebar_id'];
        if (is_mininav()) {
            $module_id  = get_query_var('module_list_id');
            $sidebar_id = 'sidebar-second-content-' . $module_id . '-' . $config['sidebar_id'];
        }
        if ((is_active_sidebar($sidebar_id))) {
            $is_sidebar = true;
        }
    }
}
/**
 * 内容列表模块
 * @param mixed $config
 * @param int $index 模块id
 * @return void
 */
function io_module_content($config, $index = 0){
    if (!isset($config['nav_id']) || empty($config['nav_id'])) {
        return;
    }
    $menu = get_menu_items_by_level($config['nav_id']);
    $card = $config['show_card'] ? ' show-card' : '';

    io_module_is_there_sidebar($config, $is_sidebar, $sidebar_id);
    $sidebar_class = $is_sidebar ? 'sidebar_' . $config['sidebar_tools'] : 'sidebar_no';

    $style_attr = get_div_custom_background($config, $card);

    /**
     * -----------------------------------------------------------------------
     * HOOK : ACTION HOOK
     * io_module_content_before
     * 
     * 内容模块前显示内容
     * 
     * @since 5.0
     * 
     * @param array $config 配置数据
     * @param int $index 模块id
     * -----------------------------------------------------------------------
     */
    do_action('io_module_content_before', $config, $index);

    echo '<section id="module_id_' . $index . '" class="custom-background module-id-' . $index . '" ' . $style_attr . '>';
    echo '<div class="ioui-content switch-container home-container ' . $sidebar_class . get_page_mode_class() . '">';
    echo '<div class="ioui-main">';
    echo '<div class="content-wrap">';
    echo '<div class="content-layout' . $card . '">';
    foreach($menu as $category) {
        //if (true){//get_post_meta($category['ID'], 'purview', true) <= io_get_user_level()) { // TODO 权限判断重新设计
        if ($category['menu_item_parent'] == 0) {
            if (empty($category['children'])) {
                $terms = get_menu_category_list();
                if ($category['type'] != 'taxonomy') {
                    $url = trim($category['url']);
                    if (strlen($url) > 1) {
                        if (substr($url, 0, 1) == '#' || substr($url, 0, 4) == 'http')
                            continue;
                        echo get_none_html("“{$category['title']}”不支持的菜单项，请到菜单删除", 'home-list content-card', 'error', false);
                    }
                } elseif ($category['type'] == 'taxonomy' && in_array($category['object'], $terms)) {
                    io_home_a_content($config['nav_id'], $category, $is_sidebar);
                } else {
                    echo get_none_html("“{$category['title']}”不支持的菜单项，请到菜单删除", 'home-list content-card', 'error', false);
                }
            } else {
                $is_null = true; //如果菜单内没有有效的项目，则不显示在正文中。
                foreach ($category['children'] as $mid) {
                    if ($mid['type'] != 'taxonomy') {
                        continue;
                    }
                    $is_null = false;
                }
                if ($is_null)
                    continue;

                io_home_tab_content($config['nav_id'], $category['children'], $category, $config['tab_ajax'], $is_sidebar);
            }
        }
    } 
    echo '</div>';
    echo '</div>';
    if($is_sidebar){
        echo '<div class="sidebar sidebar-tools d-none d-lg-block">';
        dynamic_sidebar($sidebar_id);
        echo '</div>';
    }
    echo '</div>';
    echo '</div>';
    echo '</section>';

    /**
     * -----------------------------------------------------------------------
     * HOOK : ACTION HOOK
     * io_module_content_after
     * 
     * 内容模块后显示内容
     * 
     * @since 5.0
     * 
     * @param array $config 配置数据
     * @param int $index 模块id
     * -----------------------------------------------------------------------
     */
    do_action('io_module_content_after', $config, $index);
}
/**
 * 小工具模块
 * @param mixed $config
 * @param int $index 模块id
 * @return void
 */
function io_module_tools($config, $index) {
    global $is_sidebar;

    io_module_is_there_sidebar($config, $is_sidebar, $sidebar_id);
    $sidebar_class = $is_sidebar ? 'sidebar_' . $config['sidebar_tools'] : 'sidebar_no';

    $style_attr = get_div_custom_background($config, $card);

    $tools     = '';
    $module_name = '首页-模块' . ($index + 1);
    $tool_id     = 'max-home-tools-' . $config['tool_id'];
    if (is_mininav()) {
        $module_id   = get_query_var('module_list_id');
        $module_name = '子ID' . $module_id . '-模块' . ($index + 1);
        $tool_id     = 'max-second-tools-' . $module_id . '-' . $config['tool_id'];
    }
    if (!is_active_sidebar($tool_id)) {
        if (is_super_admin()) {
            $tools = '<div class="card"><div class="card-body py-5 text-center"><p>' . $module_name . ' 的[小工具]内容为空，请到后台添加</p> <a href="' . admin_url('widgets.php') . '" class="btn vc-l-purple" target="_blank">小工具编辑</a></div></div>';
        } else {
            return;
        }
    }

    /**
     * -----------------------------------------------------------------------
     * HOOK : ACTION HOOK
     * io_module_tools_before
     * 
     * 小工具模块前显示内容
     * 
     * @since 5.0
     * 
     * @param array $config 配置数据
     * @param int $index 模块id
     * -----------------------------------------------------------------------
     */
    do_action('io_module_tools_before', $config, $index);

    echo '<section id="module_id_' . $index . '" class="custom-background module-id-' . $index . '" ' . $style_attr . '>';
    echo '<div class="ioui-content switch-container home-container ' . $sidebar_class . get_page_mode_class() . '">';
    echo '<div class="ioui-main">';
    echo '<div class="content-wrap">';
    echo '<div class="content-layout' . $card . '">';
    if (empty($tools)) {
        dynamic_sidebar($tool_id);
    } else {
        echo $tools;
    }
    echo '</div>';
    echo '</div>';
    if($is_sidebar){
        echo '<div class="sidebar sidebar-tools d-none d-lg-block">';
        dynamic_sidebar($sidebar_id);
        echo '</div>';
    }
    echo '</div>';
    echo '</div>';
    echo '</section>';

    /**
     * -----------------------------------------------------------------------
     * HOOK : ACTION HOOK
     * io_module_tools_after
     * 
     * 小工具模块后显示内容
     * 
     * @since 5.0
     * 
     * @param array $config 配置数据
     * @param int $index 模块id
     * -----------------------------------------------------------------------
     */
    do_action('io_module_tools_after', $config, $index);
}
/**
 * 自定义模块
 * @param mixed $config
 * @param int $index 模块id
 * @return void
 */
function io_module_custom($config, $index){
    io_module_is_there_sidebar($config, $is_sidebar, $sidebar_id);
    $sidebar_class = $is_sidebar ? 'sidebar_' . $config['sidebar_tools'] : 'sidebar_no';

    $style_attr = get_div_custom_background($config, $card);

    $html = $config['html_code'];
    if (empty($html)) {
        if (is_super_admin()) {
            $_tab = is_mininav() ? '子页面布局' : '';
            $html = '<div class="card"><div class="card-body py-5 text-center"><p>自定义模块 (id: ' . $index . ') 内容为空，请到后台编辑</p> <a href="' . io_get_admin_iocf_url($_tab, 'home_module') . '" class="btn vc-l-purple" target="_blank">后台编辑</a></div></div>';
        } else {
            return;
        }
    }

    /**
     * -----------------------------------------------------------------------
     * HOOK : ACTION HOOK
     * io_module_custom_before
     * 
     * 自定义模块前显示内容
     * 
     * @since 5.0
     * 
     * @param array $config 配置数据
     * @param int $index 模块id
     * -----------------------------------------------------------------------
     */
    do_action('io_module_custom_before', $config, $index);

    echo '<section id="module_id_' . $index . '" class="custom-background module-id-' . $index . '" ' . $style_attr . '>';
    echo '<div class="ioui-content switch-container home-container ' . $sidebar_class . get_page_mode_class() . '">';
    echo '<div class="ioui-main">';
    echo '<div class="content-wrap">';
    echo '<div class="content-layout' . $card . '">';
    echo $html;
    echo '</div>';
    echo '</div>';
    if($is_sidebar){
        echo '<div class="sidebar sidebar-tools d-none d-lg-block">';
        dynamic_sidebar($sidebar_id);
        echo '</div>';
    }
    echo '</div>';
    echo '</div>';
    echo '</section>';

    /**
     * -----------------------------------------------------------------------
     * HOOK : ACTION HOOK
     * io_module_custom_after
     * 
     * 自定义模块后显示内容
     * 
     * @since 5.0
     * 
     * @param array $config 配置数据
     * @param int $index 模块id
     * -----------------------------------------------------------------------
     */
    do_action('io_module_custom_after', $config, $index);
}

/**
 * 加载单个菜单内容
 * 用于 菜单数组
 * @param array $menu 所在的菜单ID
 * @param array $mid   菜单数组
 * @param array $parent_term 父级菜单
 * @param bool $is_sidebar  是否有侧边栏
 * @return null
 */
function io_home_a_content($menu, $mid, $is_sidebar = false){
    $taxonomy  = $mid['object'];
    $quantity  = get_card_num();
    $icon      = get_tag_ico($taxonomy, $mid);
    $parent_id = $menu . $mid['object_id'];
    $site_n    = $quantity[get_taxonomy_type_name($taxonomy)];
    $post_type = get_post_types_by_taxonomy($mid['object']);
    $columns   = io_get_columns_class($post_type, $mid['object_id'], $is_sidebar);
    $more_text = _iol(io_get_option('term_more_text', 'more+'));
    $post_card = show_card($site_n, $mid['object_id'], $taxonomy, array('echo' => false))['html'];

    /**
     * -----------------------------------------------------------------------
     * HOOK : ACTION HOOK
     * io_before_show_category_code
     * 
     * 在内容卡片前挂载其他内容。
     * 也可以在特定内容前挂载其他内容，通过判断$mid['object_id']
     * @since  3.0731
     * -----------------------------------------------------------------------
     */
    do_action('io_before_show_category_code', $mid, $menu);

    echo <<<HTML
    <div class="content-card">
        <div id="term-{$parent_id}" class="d-flex flex-fill align-items-center mb-2">
            <h4 class="tab-title text-gray text-md m-0"><i class="site-tag {$icon} icon-lg mr-1"></i>{$mid['title']}</h4>
            <div class="flex-fill tab-to-more"></div>
            <a class="btn-more text-xs ml-2" href="{$mid['url']}">{$more_text}</a>
        </div>
        <div class="row {$columns}">
            {$post_card}
        </div>
    </div>   
HTML;

    /**
     * -----------------------------------------------------------------------
     * HOOK : ACTION HOOK
     * io_after_show_category_code
     * 
     * 在内容卡片后挂载其他内容。
     * @since  5.0
     * 
     * @param array $mid 菜单数组
     * @param int $menu 所在的菜单ID
     * -----------------------------------------------------------------------
     */
    do_action('io_after_show_category_code', $mid, $menu);
} 


/**
 * 加载完整菜单tab卡片
 * @param int $menu 所在的菜单ID
 * @param array $category 子菜单
 * @param array $parent_term  父级菜单
 * @param bool $is_ajax  是否为ajax加载
 * @param bool $is_sidebar  是否有侧边栏
 * @return *
 */
function io_home_tab_content($menu, $category, $parent_term, $is_ajax = false, $is_sidebar = false) {
    $quantity  = get_card_num();
    $icon      = get_tag_ico($parent_term['object'], $parent_term);
    $page_id   = get_queried_object_id();
    $parent_id = $menu . $parent_term['object_id'];
    $more_text = _iol(io_get_option('term_more_text', 'more+'));
    $more_url  = '';

    $tab_btn   = '';
    $post_card = '';
    $index     = 0;
    foreach ($category as $mid) {
        if ($mid['type'] != 'taxonomy' && (!empty(trim($mid['url'])) && (substr($mid['url'], 0, 1) == '#' || substr($mid['url'], 0, 4) == 'http'))) {
            continue;
        }
        if($index === 0){
            $more_url = $mid['url'];
        }
        $taxonomy  = $mid['object'];
        $post_type = get_post_types_by_taxonomy($taxonomy);
        $cat_id    = $mid['object_id'];
        $load      = !$is_ajax || $index === 0 ? ' load' : '';

        $style = get_term_meta($cat_id, 'card_mode', true);
        if (!$style || 'null' === $style || 'none' === $style) {
            $style = io_get_option($post_type . '_card_mode', 'max');
        }
        
        $tab_btn .= sprintf(
            '<li id="term-%1$s-%2$s" class="slider-li tab-item %9$s" data-target="#tab-%1$s-%2$s" data-sidebar="%3$s" data-post_id="%4$s" data-action="load_home_tab" data-taxonomy="%5$s" data-id="%2$s" data-more-link="%6$s" data-style="%7$s">%8$s</li> ',
            esc_attr($parent_id),
            esc_attr($cat_id),
            $is_sidebar ? 1 : 0,
            esc_attr($page_id),
            esc_attr($taxonomy),
            esc_url($mid['url']),
            $post_type . '-' . $style,
            esc_html($mid['title']),
            ($index === 0 ? ('active' . $load) : $load)
        );

        $site_n  = $quantity[get_taxonomy_type_name($taxonomy)];
        $columns = io_get_columns_class($post_type, $cat_id, $is_sidebar);

        $post_card .= '<div id="tab-' . $parent_id . '-' . $cat_id . '" class="tab-pane  ' . ($index == 0 ? 'active' : '') . '">';
        $post_card .= '<div class="row ' . $columns . ' ajax-list-body position-relative">';
        if (!$is_ajax || $index === 0) {
            $post_card .= show_card($site_n, $cat_id, $taxonomy, array('echo' => false))['html'];
        }
        $post_card .= '</div></div>';
        $index++;
    }
    /**
     * -----------------------------------------------------------------------
     * HOOK : ACTION HOOK
     * io_before_show_category_code
     * 
     * 在内容卡片前挂载其他内容。
     * 也可以在特定内容前挂载其他内容，通过判断$parent_term['object_id']
     * @since  3.0731
     * 
     * @param array $parent_term 父级菜单
     * @param int $menu 所在的菜单ID
     * -----------------------------------------------------------------------
     */
    do_action('io_before_show_category_code', $parent_term, $menu);

    echo <<<HTML
    <div class="content-card">
        <div id="term-{$parent_id}" class="d-flex flex-column flex-md-row align-items-md-center io-slider-tab mb-2">
            <h4 class="tab-title text-gray text-md m-0 mr-2">
                <i class="site-tag {$icon} icon-lg mr-1"></i>{$parent_term['title']}
            </h4>
            <ul class="slider-tab slider-ul overflow-x-auto no-scrollbar mt-3 mt-md-0" slider-tab="slider" role="tablist"> 
                {$tab_btn}
            </ul>
            <div class="flex-fill tab-to-more d-none d-md-block"></div>
            <a class="tab-more btn-more text-xs ml-2" href="{$more_url}">{$more_text}</a>
        </div>
        <div class="tab-content">
            {$post_card}
        </div> 
    </div>
HTML;

    /**
     * -----------------------------------------------------------------------
     * HOOK : ACTION HOOK
     * io_after_show_category_code
     * 
     * 在内容卡片后挂载其他内容。
     * @since  5.0
     * 
     * @param array $parent_term 父级菜单
     * @param int $menu 所在的菜单ID
     * -----------------------------------------------------------------------
     */
    do_action('io_after_show_category_code', $parent_term, $menu);
}

/**
 * 获取内容卡片数量
 * @param mixed $post_type 文章类型
 * @param int|array $term_id 分类ID
 * @param mixed $is_sidebar 是否有侧边栏
 * @param mixed $class 额外的 class
 * @return string
 */
function io_get_columns_class($post_type, $term_id, $is_sidebar = false, $class = '') {
    $default = array(
        'sm'  => 2,
        'md'  => 2,
        'lg'  => 3,
        'xl'  => 5,
        'xxl' => 6
    );

    if (is_array($term_id)) {
        $columns = $term_id;
    } else {
        $card_mode = get_term_meta($term_id, 'card_mode', true);
        if ('' !== $term_id && $card_mode && 'null' !== $card_mode && 'none' !== $card_mode) {
            $columns = get_term_meta($term_id, 'columns', true);
        } else {
            $columns = io_get_option($post_type . '_columns', $default);
        }
        $columns = wp_parse_args($columns, $default);
    }

    if ($is_sidebar) {
        $columns['xxl'] -= 1;
        $columns['xl'] -= 1;
        $columns['lg'] -= 1;
    }

    $class = "row-col-{$columns['sm']}a row-col-sm-{$columns['sm']}a row-col-md-{$columns['md']}a row-col-lg-{$columns['lg']}a row-col-xl-{$columns['xl']}a row-col-xxl-{$columns['xxl']}a $class";

    return $class;
}
