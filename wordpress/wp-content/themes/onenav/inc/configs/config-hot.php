<?php 
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2021-08-30 14:41:57
 * @LastEditors: iowen
 * @LastEditTime: 2025-06-03 17:35:25
 * @FilePath: /onenav/inc/configs/config-hot.php
 * @Description: 
 */
if ( ! defined( 'ABSPATH' )  ) { die; }
if (!is_admin()) return;
$prefix = 'io_hot_search_list';

IOCF::createOptions($prefix, array(
    'framework_title'    => '自定义热榜',
    'menu_title'         => '◉ 自定义热榜',
    'menu_slug'          => 'hot_search_settings',
    'show_search'        => false,
    'show_reset_section' => false,
    'show_footer'        => false,
    'show_all_options'   => false,
    'show_sub_menu'      => false,
    'menu_icon'          => 'dashicons-search',
    'menu_type'          => 'submenu',
    'menu_parent'        => 'options-general.php',
    'show_bar_menu'      => false,
    'theme'              => 'light',
    'nav'                => 'inline',
    'footer_credit'      => '感谢您使用 <a href="https://www.iotheme.cn/" target="_blank">ioTheme</a> 的WordPress主题',
));

//
// 搜索设置
//
IOCF::createSection($prefix, array(
    //'title'       => '默认搜索列表', 
    'fields' => array(
        array(
            'content' => '<p>支持添加 JSON 和 RSS 源，具体使用请查看默认规则。 </p>
            <br><i class="fa fa-fw fa-info-circle fa-fw"></i> JSON 可能会因为鉴权、跨域等问题而无法获取数据。',
            'style'   => 'info',
            'type'    => 'submessage',
        ),
        array(
            'id'                     => 'json_list',
            'type'                   => 'group',
            'title'                  => 'JSON数据源',
            'fields'                 => array(
                array(
                    'id'    => 'name',
                    'type'  => 'text',
                    'title' => '名称',
                ),
                array(
                    'id'    => 'subtitle',
                    'type'  => 'text',
                    'title' => '小标题',
                    'class' => 'compact min',
                    'after' => '<i class="fa fa-fw fa-info-circle fa-fw"></i> 可留空。例：热度、阅读量、指数'
                ),
                array(
                    'id'         => 'url',
                    'type'       => 'text',
                    'title'      => '源地址',
                    'class'      => 'compact min',
                    'attributes' => array(
                        'style' => 'width: 100%'
                    ),
                ),
                array(
                    'id'         => 'datas',
                    'type'       => 'text',
                    'title'      => '数据节点',
                    'class'      => 'compact min',
                    'attributes' => array(
                        'style' => 'width: 100%'
                    ),
                    'after'      => '<i class="fa fa-fw fa-info-circle fa-fw"></i> 资讯数据根节点，如：result.data.feed.list'
                ),
                array(
                    'id'         => 'title',
                    'type'       => 'text',
                    'title'      => '标题节点',
                    'class'      => 'compact min',
                    'attributes' => array(
                        'style' => 'width: 100%'
                    ),
                ),
                array(
                    'id'         => 'link',
                    'type'       => 'text',
                    'title'      => '链接节点',
                    'class'      => 'compact min',
                    'attributes' => array(
                        'style' => 'width: 100%'
                    ),
                ),
                array(
                    'id'         => 'hot',
                    'type'       => 'text',
                    'title'      => '热度节点(可留空)',
                    'class'      => 'compact min',
                    'after'      => '阅读量，查看量，指数，热度',
                    'attributes' => array(
                        'style' => 'width: 100%'
                    ),
                ),
                array(
                    'id'         => 'link_regular',
                    'type'       => 'text',
                    'title'      => '链接补全',
                    'class'      => 'compact min',
                    'after'      => '获取的链接地址可能不全，可使用此项补全，用<b>%s%</b>替换内容，比如：获取到地址为：hot/218.html,则填写：https://www.iotheme.cn/%s%',
                    'attributes' => array(
                        'style' => 'width: 100%'
                    ),
                ),
                array(
                    'id'     => 'headers',
                    'type'   => 'group',
                    'title'  => '请求头(可留空)',
                    'class'  => 'compact min',
                    'fields' => array(
                        array(
                            'id'    => 'key',
                            'type'  => 'text',
                            'title' => 'KEY',
                        ),
                        array(
                            'id'    => 'value',
                            'type'  => 'text',
                            'title' => 'Value',
                            'class' => 'compact min',
                        ),
                    ),
                    'after'  => 'userAgent 不需要设置，默认自动添加<br>
                    常用请求头KEY：<code>accept</code>、<code>acceptLanguage</code>、<code>acceptEncoding</code>、<code>acceptRanges</code>、<code>cacheControl</code>、<code>contentType</code>、<code>range</code>、<code>referer</code>'
                ),
                array(
                    'id'     => 'cookies',
                    'type'   => 'group',
                    'title'  => 'Cookie(可留空)',
                    'class'  => 'compact min',
                    'fields' => array(
                        array(
                            'id'    => 'key',
                            'type'  => 'text',
                            'title' => 'KEY',
                        ),
                        array(
                            'id'    => 'value',
                            'type'  => 'text',
                            'title' => 'Value',
                            'class' => 'compact min',
                        ),
                    ),
                ),
                array(
                    'id'      => 'cache',
                    'type'    => 'number',
                    'title'   => '缓存时间',
                    'class'   => 'compact min',
                    'unit'    => '分钟',
                    'default' => 60,
                    'after'   => '注意：变更后请清理服务器缓存或者重启服务器才能“立即”生效'
                ),
                array(
                    'id'      => 'request_type',
                    'type'    => 'button_set',
                    'title'   => '请求类型',
                    'options' => array(
                        'get'  => 'GET',
                        'post' => 'POST',
                    ),
                    'class'   => 'compact min',
                    'default' => 'get',
                ),
                array(
                    'id'         => 'request_data',
                    'type'       => 'group',
                    'title'      => '请求参数',
                    'class'      => 'compact min',
                    'fields'     => array(
                        array(
                            'id'    => 'key',
                            'type'  => 'text',
                            'title' => 'KEY',
                        ),
                        array(
                            'id'    => 'value',
                            'type'  => 'text',
                            'title' => 'Value',
                            'class' => 'compact min',
                        ),
                    ),
                    'dependency' => array('request_type', '==', 'post')
                ),
            ),
            'class'                  => 'no-sort',
            'accordion_title_number' => true,
            'default'                => array(
                array(
                    'name'     => 'houxu',
                    'subtitle' => '',
                    'url'      => 'https://houxu.app/api/1/bundle/index/',
                    'datas'    => 'dailyHotSearches',
                    'title'    => 'live.last.link.title',
                    'link'     => 'live.last.link.url',
                    'hot'      => '',
                    'cache'    => '60',
                )
            )
        ),
        array(
            'id'                     => 'rss_list',
            'type'                   => 'group',
            'title'                  => 'RSS数据源',
            'before'                 => '<i class="fa fa-fw fa-info-circle fa-fw"></i> RSS 源一般只需要填写“名称”、“小标题”、“源地址”即可，其他的节点路径一般都相同',
            'fields'                 => array(
                array(
                    'id'    => 'name',
                    'type'  => 'text',
                    'title' => '名称',
                ),
                array(
                    'id'    => 'subtitle',
                    'type'  => 'text',
                    'title' => '小标题',
                    'class' => 'compact min',
                    'after' => '<i class="fa fa-fw fa-info-circle fa-fw"></i> 可留空。例：热度、阅读量、指数'
                ),
                array(
                    'id'         => 'url',
                    'type'       => 'text',
                    'title'      => '源地址',
                    'class'      => 'compact min',
                    'attributes' => array(
                        'style' => 'width: 100%'
                    ),
                ),
                array(
                    'id'         => 'datas',
                    'type'       => 'text',
                    'title'      => '数据节点',
                    'class'      => 'compact min',
                    'attributes' => array(
                        'style' => 'width: 100%'
                    ),
                    'default'    => 'channel.item',
                    'after'      => '<i class="fa fa-fw fa-info-circle fa-fw"></i> 资讯数据根节点，如：result.data.feed.list'
                ),
                array(
                    'id'         => 'title',
                    'type'       => 'text',
                    'title'      => '标题节点',
                    'class'      => 'compact min',
                    'attributes' => array(
                        'style' => 'width: 100%'
                    ),
                    'default'    => 'title'
                ),
                array(
                    'id'         => 'link',
                    'type'       => 'text',
                    'title'      => '链接节点',
                    'class'      => 'compact min',
                    'attributes' => array(
                        'style' => 'width: 100%'
                    ),
                    'default'    => 'link'
                ),
                array(
                    'id'         => 'hot',
                    'type'       => 'text',
                    'title'      => '热度节点(可留空)',
                    'class'      => 'compact min',
                    'after'      => '阅读量，查看量，指数，热度',
                    'attributes' => array(
                        'style' => 'width: 100%'
                    ),
                ),
                array(
                    'id'      => 'cache',
                    'type'    => 'number',
                    'title'   => '缓存时间',
                    'class'   => 'compact min',
                    'unit'    => '分钟',
                    'default' => 60,
                    'after'   => '注意：变更后请清理服务器缓存或者重启服务器才能“立即”生效'
                ),
            ),
            'class'                  => 'no-sort',
            'accordion_title_number' => true,
            'default'                => array(
                array(
                    'name'     => '知乎',
                    'subtitle' => '每日精选',
                    'url'      => 'https://www.zhihu.com/rss',
                    'datas'    => 'channel.item',
                    'title'    => 'title',
                    'link'     => 'link',
                    'hot'      => '',
                    'cache'    => '60',
                )
            )
        ),
        // 备份
        array(
            'type'   => 'backup',
            'before' => '<i class="fa fa-fw fa-info-circle fa-fw"></i> 导出数据分享。'
        ),
    )
));
