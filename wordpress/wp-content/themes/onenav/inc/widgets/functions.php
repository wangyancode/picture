<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2021-06-10 21:26:22
 * @LastEditors: iowen
 * @LastEditTime: 2024-10-10 13:40:48
 * @FilePath: /onenav/inc/widgets/functions.php
 * @Description: 
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
function loadWidget($path, $safe = false)
{    
    require_once get_theme_file_path('/inc/widgets/'.$path.'.php');
}

/* 载入小工具 */
$widgets = array(
    'w.single.posts',
    'w.hot.api',
    'w.about.website',
    'w.about.author',
    'w.ranking',
    'w.pay.box',
    'w.tag.cloud',
    'w.code',
    'w.big.posts',
    'w.big.carousel',
    //'w.tabs',
    //'w.tabs.box',
    'w.carousel'
);
foreach ($widgets as $widget) {
    loadWidget($widget);
}

function widget_icon() {
?>
<style type="text/css">   
.widget-title h3,
.widget-title h4{
    position: relative;
    overflow: hidden;
}
[id*="iow_"] h3::before{
    content:''; 
    display: inline-block;
    margin-right: 6px;
    width: 0.625em;
    height: 0.625em;
    vertical-align: -0.1em;
    border-radius: 10px;
    border: 2px solid #f1404b;
}
[id*="iow_"] h3::after{
    content:'通';
    position: absolute;
    top: -0px;
    left: -13px;
    font-size: 12px;
    padding: 2px 15px;
    color: #fff;
    background: #15b955;
    display: inline-block;
    transform: rotateZ(-45deg);
    text-align: center;
}
[id*="_min"] h3::after{
    content:'窄';
    background: #168df0;
}
[id*="_max"] h3::after{
    content:'宽';
    background: #e8456a;
}
[id^="max-"]::before,
[id^="sidebar-"]::before{
    content:'窄';
    position: absolute;
    top: 2px;
    left: 2px;
    font-size: 10px;
    padding: 3px 6px;
    line-height: 1;
    color: #fff;
    background: #168df0;
    display: inline-block;
    border-radius: 4px;
}
[id^="max-"]::before{
    content:'宽';
    background: #e8456a;
}
#available-widgets-list [id*="iow_"] h3::after{
    transform: rotateZ(45deg);
    left: auto;
    right: -13px;
}
</style>
<?php 
}
add_action('customize_controls_enqueue_scripts', 'widget_icon');
add_action('admin_head', 'widget_icon');

/**
 * 添加 MAX 小工具默认参数
 * @param mixed $args
 * @return array
 */
function add_max_widget($args)
{
    $default = array(
        'id'            => '',
        'name'          => '',
        'description'   => '',
        'before_widget' => '<div id="%1$s" class="module-sidebar-widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<div class="module-header widget-header"><h3 class="text-md mb-0">',
        'after_title'   => '</h3></div>',
    );
    $args = wp_parse_args($args, $default);
    return $args;
}

/**
 * 添加侧边栏
 * @param mixed $sidebars
 * @return mixed
 */
function io_add_sidebar_list_filters($sidebars)
{
    $sidebars[] = add_max_widget(array(
        'id'          => 'max-blog-top-full-sidebar',
        'name'        => '博客上方小全宽工具',
        'description' => '显示在「博客模版页」上方的内容，推荐轮播小工具。',
    ));
    $sidebars[] = add_max_widget(array(
        'id'          => 'max-blog-bottom-sidebar',
        'name'        => '博客正文下面小工具',
        'description' => '显示在「博客模版页」正文下的内容。',
    ));
    $sidebars[] = add_max_widget(array(
        'id'          => 'max-blog-bottom-full-sidebar',
        'name'        => '博客下面全宽小工具',
        'description' => '显示在「博客模版页」下的内容。',
    ));

    if (io_get_option('local_search_config', false, 'gadget')) {
        $sidebars[] = array(
            'id'            => 'sidebar-modal-search',
            'name'          => '弹窗搜索框小工具',
            'description'   => '显示在站内搜索弹窗下的内容，最多放一个小工具。',
            'before_widget' => '<div id="%1$s" class="search-gadget-box search-card io-sidebar-widget p-0 mt-3 %2$s">',
            'after_widget'  => '</div>',
            'before_title'  => '<div class="card-header widget-header"><h3 class="text-md mb-0">',
            'after_title'   => '</h3></div>',
        );
    }
    $sidebars = io_add_home_content_sidebar_list($sidebars);
    $sidebars = io_add_second_sidebar_list($sidebars);
    return $sidebars;
}
add_filter('io_sidebar_list_filters', 'io_add_sidebar_list_filters'); 

/**
 * 添加首页内容模块侧边栏
 * @param mixed $sidebars
 * @param mixed $config
 * @param string $type
 * @param string $name
 * @param string $second_id  二级页面ID
 * @return mixed
 */
function io_add_home_content_sidebar_list($sidebars, $config = '', $type = 'home', $name = '✿ 首页', $second_id = ''){
    if (empty($config)) {
        $config = io_home_option('page_module');
    }
    
    foreach ($config as $index => $value) {
        $name_prefix = $name . '-模块' . ($index + 1);
        if(!isset($value['type']) || !isset($value[$value['type'] . '_config'])){
            continue;
        }
        $_config     = $value[$value['type'] . '_config'];
        $type_name  = io_custom_module_list($value['type']);

        // 注册模块侧边栏
        if (isset($_config['sidebar_tools']) && $_config['sidebar_tools'] != 'none') {
            $_id        = '' !==$second_id ? $second_id . '-' . $_config['sidebar_id'] : $_config['sidebar_id'];
            $sidebars[] = array(
                'id'          => 'sidebar-' . $type . '-content-' . $_id,
                'name'        => $name_prefix . ' - [' . $type_name . ' ' . $_config['sidebar_id'] . '] - ' . io_get_module_sidebar_name($_config['sidebar_tools']) . '边栏',
                'description' => '显示在' . $name_prefix . '“' . $type_name . '”模块的侧边栏。',
            );
        }
        // 注册模块小工具内容
        if ($value['type'] == 'tools') {
            $_id        = '' !==$second_id ? $second_id . '-' . $_config['tool_id'] : $_config['tool_id'];
            $sidebars[] = array(
                'id'            => 'max-' . $type . '-tools-' . $_id,
                'name'          => $name_prefix . ' - [小工具 ' . $_config['tool_id'] . '] - 正文模块',
                'description'   => '显示在' . $name_prefix . '“小工具”模块内的内容正文。',
                'before_widget' => '<div id="%1$s" class="module-sidebar-widget %2$s">',
                'after_widget'  => '</div>',
                'before_title'  => '<div class="module-header widget-header"><h3 class="text-md mb-0">',
                'after_title'   => '</h3></div>',
            );
        }
    }
    return $sidebars;
}
/**
 * 添加子页面模块侧边栏
 * @param mixed $sidebars
 * @return mixed
 */
function io_add_second_sidebar_list($sidebars){
    $lists = io_home_option('second_page_list');

    if(!$lists)
        return $sidebars;

    foreach ($lists as $index => $list) {
        $config = $list['page_module'];
        $name   = $list['second_id'];
        $sidebars = io_add_home_content_sidebar_list($sidebars, $config, 'second', '✡ 子《' . $name . '》', $index);
    }
    return $sidebars;
}


function io_get_module_sidebar_name($type){
    $names = array(
        'none'  => '无',
        'left'  => '左',
        'right' => '右',
    );
    return $names[$type];
}

/**
 * 获取小工具模块标题
 * @param mixed $args
 * @param mixed $instance
 * @param mixed $other
 * @return string
 */
function get_widget_title($args, $instance, $class = '', $other = ''){
    $title = '';
    if (!empty($instance['title'])) {
        $title_ico = (isset($instance['title_ico']) && !empty($instance['title_ico'])) ? '<i class="mr-2 ' . $instance['title_ico'] . '"></i>' : '';
        $title     = '<div class="sidebar-header ' . $class . '">' . $args['before_title'] . $title_ico . $instance['title'] . $args['after_title'] . $other . '</div>';
    }
    return $title;
}
/**
 * 获取小工具配置
 * @param mixed $widget_id_str
 * @return mixed
 */
function get_widget_config($widget_id_str) {
    $widget_id_str = ltrim($widget_id_str, '#');

    if (preg_match('/^(.+)-(\d+)$/', $widget_id_str, $matches)) {
        $widget_name = $matches[1];
        $widget_id   = (int) $matches[2];

        $widget_options = get_option('widget_' . $widget_name);

        if (isset($widget_options[$widget_id])) {
            return $widget_options[$widget_id];
        } else {
            return false;
        }
    } else {
        return false;
    }
}

/**
 * 显示小工具
 * @param mixed $index 小工具ID
 * @param mixed $class 类名
 * @param mixed $tips 是否提示设置指引
 * @return void
 */
function io_show_sidebar($index, $class = '', $tips = false)
{
    if (is_active_sidebar($index)) {
        echo '<div class="' . $index . ' ' . $class . '">';
        dynamic_sidebar($index);
        echo '</div>';
    } elseif ($tips) {
        echo '';
    }
}
