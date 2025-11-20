<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2024-04-27 00:17:09
 * @LastEditors: iowen
 * @LastEditTime: 2025-06-03 17:28:35
 * @FilePath: /onenav/inc/configs/theme/content-app.php
 * @Description: 
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }


return array(
    'title'  => 'app设置',
    'icon'   => 'fa fa-shopping-bag',
    'fields' => array(
        array(
            'id'      => 'app_card_mode',
            'type'    => 'image_select',
            'title'   => '首页 app 卡片样式',
            'options' => io_get_posts_card_style('app'),
            'default' => 'default',
            'after'   => '选择首页app块显示风格<br>' . $tip_ico . '分类设置中的样式优先级最高，如发现此设置无效，请检查分类设置。',
        ),
        array(
            'id'         => 'app_keywords_meta',
            'type'       => 'checkbox',
            'title'      => 'APP 卡片 KEY',
            'options'    => array(
                'apps'   => '分类',
                'apptag' => '标签',
            ),
            'default'    => array('apps', 'apptag'),
            'inline'     => true,
            'dependency' => array('app_card_mode', 'any', 'max'),
        ),
        array(
            'id'      => 'app_archive_n',
            'type'    => 'number',
            'title'   => 'App 分类页显示数量',
            'default' => 30,
            'after'   => '填写需要显示的数量。填写 0 为根据<a href="' . home_url() . '/wp-admin/options-reading.php">系统设置数量显示</a>。',
        ),
        array(
            'id'         => 'is_app_down_nogo',
            'type'       => 'switcher',
            'title'      => '下载地址禁止GO跳转',
            'label'      => '依赖 “seo设置”->“Go 跳转” 中的 “内链跳转(go跳转)”',
            'desc'       => $tip_ico . '可以通过go跳转白名单解决单个控制',
            'class'      => '',
            'dependency' => array('is_go', '==', true, 'all', 'visible'),
        ),
        //array(
        //    'id'        => 'default_app_screen',
        //    'type'      => 'upload',
        //    'title'     => 'app 默认截图',
        //    'add_title' => '添加',
        //    'after'     => 'app截图为空时显示这项设置的内容',
        //    'default'   => get_theme_file_uri('/screenshot.jpg'),
        //),
        array(
            'id'       => 'app_columns',
            'type'     => 'fieldset',
            'title'    => 'APP 列数',
            'subtitle' => '首页 APP 块列表一行显示的个数',
            'before'   => '根据屏幕大小设置',
            'fields'   => io_get_screen_item_count(array(
                'sm'  => 3,
                'md'  => 5,
                'lg'  => 7,
                'xl'  => 9,
                'xxl' => 11
            )),
            'after'    => $tip_ico . '注意：如果内容没有根据此设置变化，请检查对应分类的设置。',
        ),
        array(
            'type'    => 'subheading',
            'content' => '详情页设置',
        ),
        array(
            'id'      => 'app_breadcrumb',
            'type'    => 'switcher',
            'title'   => '正文头部面包屑菜单',
            'default' => true,
            'class'   => 'new'
        ),
        array(
            'id'      => 'app_down_list_count',
            'type'    => 'number',
            'title'   => '历史版本显示数量',
            'default' => 6,
            'after'   => '详情页历史版本显示的数量。填写 0 显示全部。',
            'class'   => 'new',
        ),
        array(
            'id'      => 'app_down_list_location',
            'type'    => 'select',
            'title'   => '历史版本显示位置',
            'options' => array(
                'before' => '正文上面',
                'after'  => '正文下面',
            ),
            'default' => 'after',
            'class'   => 'compact min new',
        ),
        array(
            'id'      => 'app_related',
            'type'    => 'switcher',
            'title'   => '相关app',
            'default' => true,
            'class'   => '',
        ),
    )
);