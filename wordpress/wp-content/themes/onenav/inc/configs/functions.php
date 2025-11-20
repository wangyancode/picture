<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2022-02-09 21:11:15
 * @LastEditors: iowen
 * @LastEditTime: 2025-06-03 17:39:56
 * @FilePath: /onenav/inc/configs/functions.php
 * @Description: 
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

if (!is_admin()) return;

$functions = array(
    'config-theme',
    'config-home',
    'config-hot',
    'config-search'
);

foreach ($functions as $function) {
    $path = 'inc/configs/' . $function . '.php';
    require get_theme_file_path($path);
}

/**
 * 加载配置文件
 * @param mixed $file 配置文件名 (文件夹/文件名)
 * @param mixed $parent 父级
 * @param mixed $default 默认值
 * @return mixed
 */
function io_get_option_data($file, $parent = '', $default = ''){
	$file = ltrim( $file, '/' );
    $path = 'inc/configs/' . $file . '.php';
    $path = get_theme_file_path($path);

    $tip_ico = '<i class="fa fa-fw fa-info-circle"></i> ';

    if (file_exists($path)) {
        $fields = include $path;
        if ($parent) {
            $fields['parent'] = $parent;
        }
        return $fields;
    } else {
        return $default;
    }
}

/**
 * Summary of get_sorter_options
 * @param mixed $type
 * @return mixed
 */
function get_sorter_options($type = 'term'){
    if($type == 'term'){
        return get_all_taxonomy();
    }else if($type == 'top_widget'){
        return  apply_filters('io_home_widget_list_filters',array(
            'carousel-post' => '文章轮播模块',
            'tab'           => 'Tab 内容模块',
            'swiper'        => 'Big 轮播模块',  
        ));
    }
}


function io_home_option($option, $default = null, $key = ''){
    static $config = null;
    if (null === $config) {
        $config = (array)get_option('io_home_config');
    }
    $_v = $default;
    if (isset($config[$option])) {
        if ($key) {
            $_v = isset($config[$option][$key]) ? $config[$option][$key] : $default;
        } else {
            $_v = $config[$option];
        }
    }
    return $_v;
}

/**
 * 获取内容显示数量配置
 * @param mixed $config
 * @return array[]
 */
function io_get_screen_item_count($config, $tips = ''){
    $default = array(
        'sm'   => 2,
        'md'   => 2,
        'lg'   => 3,
        'xl'   => 5,
        'xxl'  => 6,
    );
    $config = wp_parse_args($config, $default);

    $fields = array(
        array(
            'type'    => 'submessage',
            'style'   => 'success',
            'content' => '<i class="fa fa-fw fa-info-circle"></i> 注意：有效值范围只有<b>1-12</b>' . $tips,
        ),
        array(
            'id'       => 'sm',
            'type'     => 'number',
            'title'    => '小屏幕(≥576px)',
            'unit'     => '个',
            'default'  => $config['sm'],
            'class'    => 'compact min'
        ),
        array(
            'id'       => 'md',
            'type'     => 'number',
            'title'    => '中等屏幕(≥768px)',
            'unit'     => '个',
            'default'  => $config['md'],
            'class'    => 'compact min'
        ),
        array(
            'id'       => 'lg',
            'type'     => 'number',
            'title'    => '大屏幕(≥992px)',
            'unit'     => '个',
            'default'  => $config['lg'],
            'class'    => 'compact min'
        ),
        array(
            'id'       => 'xl',
            'type'     => 'number',
            'title'    => '加大屏幕(≥1200px)',
            'unit'     => '个',
            'default'  => $config['xl'],
            'class'    => 'compact min'
        ),
        array(
            'id'       => 'xxl',
            'type'     => 'number',
            'title'    => '超大屏幕(≥1400px)',
            'unit'     => '个',
            'default'  => $config['xxl'],
            'class'    => 'compact min'
        ),
    );

    return $fields;
}

/**
 * 获取文章类型对应的名称
 * 
 * @param string $key 可选参数，例如'sites', 'post', 'app', 'book'
 *                    为空时返回所有文章类型名称
 * @return mixed 
 */
function io_get_post_type_name_option($key = '')
{
    $name = array(
        'sites' => '网站',
        'post'  => '文章',
        'app'   => '软件',
        'book'  => '书籍',
    );
    return $name[$key];
}
