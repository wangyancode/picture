<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2024-04-27 00:12:52
 * @LastEditors: iowen
 * @LastEditTime: 2025-03-26 17:08:48
 * @FilePath: /onenav/inc/configs/theme/content-sites.php
 * @Description: 
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

$is_server_load = !empty(IOTOOLS::get_server_load());

return array(
    'title'  => '网址设置', 
    'icon'   => 'fa fa-sitemap',
    'fields' => array(
        array(
            'id'      => 'sites_card_mode',
            'type'    => 'image_select',
            'title'   => '首页网址卡片样式', 
            'options' => io_get_posts_card_style('sites'),
            'default' => 'default',
            'after'   => '选择首页网址块显示风格：大、中、小<br>' . $tip_ico . '分类设置中的样式优先级最高，如发现此设置无效，请检查分类设置。',
        ),
        array(
            'id'         => 'sites_dominant_color',
            'type'       => 'switcher',
            'title'      => '同色调背景色', 
            'default'    => true,
            'label'      => '卡片背景色使用图片主色调，如果浏览器卡顿，请关闭此选项。',
            'dependency' => array('sites_card_mode', '==', 'big'),
            'class'      => 'compact min',
        ),
        array(
            'id'      => 'no_ico',
            'type'    => 'switcher',
            'title'   => '无图标模式', 
            'default' => false,
            'class'      => 'compact min',
        ),
        array(
            'id'      => 'po_prompt',
            'type'    => 'radio',
            'title'   => '网址块弹窗提示(悬停、hover)', 
            'desc'    => '第4种卡片样式不支持', 
            'default' => 'url',
            'inline'  => true,
            'options' => array(
                'null'    => '无', 
                'url'     => '链接', 
                'summary' => '简介', 
                'qr'      => '二维码', 
            ),
            'after'   => '如果网址添加了自定义二维码，此设置无效', 
            'class'      => 'compact min',
        ),
        array(
            'id'      => 'sites_keywords_meta',
            'type'    => 'checkbox',
            'title'   => '网址卡片 KEY', 
            'options' => array(
                'favorites' => '分类', 
                'sitetag'   => '标签', 
            ),
            'default' => array('favorites', 'sitetag'),
            'inline'  => true,
            'dependency' => array('sites_card_mode', 'any', 'max,big'),
        ),
        array(
            'id'      => 'site_archive_n',
            'type'    => 'number',
            'title'   => '网址分类页显示数量', 
            'default' => 15,
            'after'   => '填写需要显示的数量。填写 0 为根据<a href="' . home_url() . '/wp-admin/options-reading.php">系统设置数量显示</a>',
        ),
        array(
            'id'      => 'sites_archive_order',
            'type'    => 'switcher',
            'title'   => '网址分类页"失效链接"排最后', 
            'class'   => 'compact min new',
            'default' => true,
        ),
        array(
            'id'      => 'sites_sortable',
            'type'    => 'switcher',
            'title'   => '网址拖拽排序', 
            'label'   => '在后台网址列表使用拖拽排序,请同时选择“首页网址分类排序”为“自定义排序字段”', 
            'desc'    => '如果想继续使用老版的排序字段，请关闭此功能', 
            'class'   => '',
            'default' => true,
        ),
        array(
            'id'         => 'is_letter_ico',
            'type'       => 'switcher',
            'title'      => '首字图标', 
            'label'      => '未手动上传图标的网址使用首字图标',
            'default'    => false,
            'dependency' => array('no_ico', '==', false)
        ),
        array(
            'id'         => 'first_api_ico',
            'type'       => 'switcher',
            'title'      => '优先 api 图标', 
            'label'      => '如果 api 图标获取失败，则使用首字图标',
            'default'    => false,
            'class'      => 'compact',
            'dependency' => array('no_ico|is_letter_ico', '==|==', 'false|true')
        ),
        array(
            'id'         => 'letter_ico_s',
            'type'       => 'slider',
            'title'      => '首字图标饱和度',
            'min'        => 0,
            'max'        => 100,
            'step'       => 1,
            'unit'       => '%',
            'default'    => 40,
            'class'      => 'compact',
            'dependency' => array('no_ico|is_letter_ico|first_api_ico', '==|==|==', 'false|true|false')
        ),
        array(
            'id'         => 'letter_ico_b',
            'type'       => 'slider',
            'title'      => '首字图标亮度',
            'min'        => 0,
            'max'        => 100,
            'step'       => 1,
            'unit'       => '%',
            'default'    => 90,
            'class'      => 'compact',
            'dependency' => array('no_ico|is_letter_ico|first_api_ico', '==|==|==', 'false|true|false')
        ),
        array(
            'id'       => 'sites_columns',
            'type'     => 'fieldset',
            'title'    => '首页网址列数',
            'subtitle' => '网址块列表一行显示的个数', 
            'before'   => '根据屏幕大小设置',
            'fields'   => io_get_screen_item_count( array(
                'sm' => 2,
                'md' => 2,
                'lg' => 3,
                'xl' => 5,
                'xxl' => 6
            )),
            'after'    => $tip_ico . '注意：如果内容没有根据此设置变化，请检查对应分类的设置。',
        ),
        array(
            'type'    => 'subheading',
            'content' => '详情页设置',
        ),
        array(
            'id'       => 'global_goto',
            'type'     => 'switcher',
            'title'    => '直达目标站', 
            'label'    => '网址块直达目标站，不经过详情页。', 
            'default'  => false,
        ),
        array(
            'id'         => 'url_rank',
            'type'       => 'switcher',
            'title'      => '网址权重', 
            'label'      => '详情页显示网址权重', 
            'default'    => true,
            'class'      => 'compact min',
        ),
        array(
            'id'         => 'togo_btn',
            'type'       => 'switcher',
            'title'      => '[网址块] 直达按钮', 
            'label'      => '[网址块] 显示直达按钮', 
            'default'    => true,
            'class'      => 'compact min',
            'dependency' => array('global_goto', '==', false)
        ),
        array(
            'id'         => 'sites_default_content',
            'type'       => 'switcher',
            'title'      => '网址详情页“数据评估”开关', 
            'label'      => '内容可在主题文件夹里的 inc\functions\io-single-site.php get_data_evaluation()方法里修改，或者在子主题中重写此方法。', 
            'class'      => 'compact min',
        ),
        array(
            'id'         => 'mobile_view_btn',
            'type'       => 'switcher',
            'title'      => '手机查看按钮', 
            'default'    => true,
            'class'      => 'compact min',
        ),
        array(
            'id'      => 'report_button',
            'type'    => 'switcher',
            'title'   => '举报反馈按钮',
            'label'   => '在详情页显示举报反馈按钮',
            'class'   => 'compact min',
            'default' => true,
        ),
        array(
            'id'      => 'sites_preview',
            'type'    => 'switcher',
            'title'   => '预览图',
            'label'   => '在详情页显示预览图',
            'class'   => 'compact min',
            'default' => true,
        ),
        array(
            'id'         => 'sites_top_right',
            'type'       => 'textarea',
            'title'      => '右上角内容',
            'subtitle'   => '预览图位置广告位',
            'default'    => '<a href="https://www.iotheme.cn/store/onenav.html" target="_blank"><img src="' . get_theme_file_uri('/assets/images/ad_box.jpg') . '" alt="广告也精彩" /></a>',
            'class'      => 'compact min',
            'sanitize'   => false,
            'after'      => '支持 HTML，注意代码规范。<br><b>注意：</b>留空则不显示。',
            'dependency' => array('sites_preview', '==', false),
        ),
        array(
            'id'      => 'sites_breadcrumb',
            'type'    => 'switcher',
            'title'   => '正文头部面包屑菜单', 
            'default' => true,
            'class'   => 'new'
        ),
        array(
            'id'      => 'open_sites_title',
            'type'    => 'text',
            'title'   => '打开网站按钮标题', 
            'default' => 'zh=*=打开网站|*|en=*=Open site',
            'class'   => 'new'
        ),
        array(
            'id'      => 'sites_related',
            'type'    => 'switcher',
            'title'   => '相关网址', 
            'default' => true,
        ),
        array(
            'id'      => 'server_link_check',
            'type'    => 'switcher',
            'title'   => '自动检查网址状态',
            'label'   => '服务器定时检查网址状态（1小时一次），会占用部分服务器资源，',
            'default' => false,
        ),
        //array(  选项无效？？？？？？？？？
        //    'id'        => 'check_rate',
        //    'type'      => 'radio',
        //    'title'     => '自动检查频率',
        //    'inline'    => true,
        //    'options'   => array(
        //        '10min'         => '10分钟一次',
        //        'hourly'        => '一小时一次',
        //        'twicedaily'    => '每天两次',
        //        'daily'         => '每天一次',
        //    ),
        //    'default'   => "hourly",
        //    'class'     => 'compact',
        //    'dependency' => array( 'server_link_check', '==', true )
        //),
        array(
            'id'         => 'link_check_options',
            'type'       => 'fieldset',
            'title'      => '自动检查设置', 
            'fields'     => array(
                array(
                    'id'    => 'check_threshold',
                    'type'  => 'number',
                    'title' => '检查频率',
                    'unit'  => '小时',
                    'after' => '设置单个链接每隔多久检查一次，新增链接将立即检测。',
                ),
                array(
                    'id'    => 'timeout',
                    'type'  => 'number',
                    'title' => '超时',
                    'unit'  => '秒',
                    'after' => '若链接在检测时超时多久被视为失效。',
                    'class' => 'compact min',
                ),
                array(
                    'id'    => 'max_execution_time',
                    'type'  => 'number',
                    'title' => '最大执行时间',
                    'unit'  => '秒',
                    'after' => '设置后台每次检查最多可以运行时长。',
                    'class' => 'compact min',
                ),
                array(
                    'id'    => 'server_load_limit',
                    'type'  => 'number',
                    'title' => '服务器负载限制',
                    'after' => $is_server_load ? '当前负载：<span id="io_current_load" data-url="' . esc_url(admin_url('admin-ajax.php', 'relative')) . '">0.00</span><br><br>
                                    当平均服务器负载超过此值时，链接检查将会停止。此栏填0以关闭负载限制。<br>' . $tip_ico . '如果不懂意思，请保持默认，默认值：4 。' :
                        '当前服务器不支持负载限制设置！（一般只支持 Linux 系统）',
                    'class' => ($is_server_load ? '' : 'disabled') . ' compact min',
                ),
                array(
                    'id'     => 'exclusion_list',
                    'type'   => 'textarea',
                    'title'  => '排除列表', 
                    'before' => '不检测含有以下关键字的链接（每个关键字用<b>英语逗号</b>分隔，不能有空格）',
                    'class'  => 'compact min',
                ),
            ),
            'default'    => array(
                'check_threshold'    => 72,
                'timeout'            => 30,
                'max_execution_time' => 420,
                'server_load_limit'  => 4,
                'exclusion_list'     => 'baidu.com,google.com,iowen.cn,iotheme.cn',
            ),
            'class'      => 'compact',
            'dependency' => array('server_link_check', '==', true)
        ),
    )
);