<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2024-04-27 00:28:42
 * @LastEditors: iowen
 * @LastEditTime: 2025-06-03 17:31:27
 * @FilePath: /onenav/inc/configs/theme/other-basic.php
 * @Description: 
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

return array(
    'title'  => '其他杂项', 
    'icon'   => 'fa fa-info-circle',
    'fields' => array(
        array(
            'id'      => 'weather',
            'type'    => 'switcher',
            'title'   => '天气', 
            'label'   => '显示天气小工具', 
            'default' => false,
        ),
        array(
            'id'         => 'weather_location',
            'type'       => 'radio',
            'title'      => '天气位置', 
            'default'    => 'footer',
            'inline'     => true,
            'options'    => array(
                'header' => '头部', 
                'footer' => '右下小工具', 
            ),
            'class'      => 'compact',
            'dependency' => array('weather', '==', true)
        ),
        array(
            'id'        => 'weather_token',
            'type'      => 'text',
            'title'     => '知心天气API token', 
            'after'     => '不填也可以使用。<a href="https://www.seniverse.com/widgetv3" target="_blank">申请地址--></a>',
            'class'      => 'compact',
            'dependency' => array('weather', '==', true)
        ),
        array(
            'id'      => 'hitokoto',
            'type'    => 'switcher',
            'title'   => '一言', 
            'label'   => '右上角显示一言', 
            'default' => false,
        ),

        array(
            'id'         => 'hitokoto_code',
            'type'       => 'textarea',
            'title'      => '一言自定义代码',
            'default'    => '<script src="//v1.hitokoto.cn/?encode=js&select=%23hitokoto" defer></script>' . PHP_EOL . '<span id="hitokoto"></span>',
            'sanitize'   => false,
            'attributes' => array(
                'rows' => 3,
            ),
            'class'      => 'compact',
            'after'      => '<i class="fa fa-fw fa-info-circle fa-fw"></i> 自己搭建：<a href="https://www.iowen.cn/hitokoto-api-single-page/" target="_blank">教程--></a>',
            'dependency' => array('hitokoto', '==', true)
        ),

        array(
            'id'      => 'is_iconfont',
            'type'    => 'button_set',
            'title'   => '字体图标源', 
            'label'   => 'fa 和阿里图标二选一，为轻量化资源，不能共用。', 
            'desc'    => $tip_ico . '使用方法：<a href="https://www.iotheme.cn/onenavzhuticaidantubiaoshezhi.html" target="_blank">教程--></a>',
            'options' => array(
                ''  => 'fa图标',
                '1' => '阿里图标',
            ),
            'default' => '1',
        ),
        array(
            'type'       => 'notice',
            'style'      => 'success',
            'content'    => 'fa图标库使用CDN，cdn地址修改请在 inc\theme-start.php 文件里修改。默认 CDN 由 staticfile.org 提供', 
            'dependency' => array('is_iconfont', '==', '')
        ),
        array(
            'id'         => 'iconfont_url',
            'type'       => 'textarea',
            'title'      => '阿里图标库地址', 
            'after'      => '<h4>输入阿里图标库在线链接，可多个，一行一个地址，注意不要有空格。</h4>图标库地址：<a href="https://www.iconfont.cn/" target="_blank">--></a><br>教程地址：<a href="https://www.iotheme.cn/one-nav-yidaohangzhutishiyongaliyuntubiaodefangfa.html" target="_blank">--></a>
            <br><p><i class="fa fa-fw fa-info-circle fa-fw"></i> 阿里图标库项目的 FontClass/Symbol前缀 必须为 “<b>io-</b>”，Font Family 必须为 “<b>io</b>”，具体看上面的教程。</p>注意：项目之间的<b>图标名称</b>不能相同，<b>彩色</b>图标不支持变色。',
            'class'      => 'compact min',
            'attributes' => array(
                'rows' => 4,
            ),
            'default'    => '//at.alicdn.com/t/font_1620678_18rbnd2homc.css',
            'dependency' => array('is_iconfont', '==', '1')
        ),
        array(
            'id'         => 'site_created',
            'type'       => 'date',
            'title'      => '建站时间', 
            'default'    => '2020-11-11',
            'settings'   => array(
                'dateFormat'   => 'yy-mm-dd',
                'changeMonth'  => true,
                'changeYear'   => true,
            ),
            'after'      => '建站时间，用于计算网站年龄',
        ),
        array(
            'id'     => 'ip_location',
            'type'   => 'fieldset',
            'title'  => 'IP归属地', 
            'fields' => array(
                array(
                    'id'      => 'level',
                    'type'    => "select",
                    'title'   => 'IP归属地显示格式',
                    'options' => array(
                        '1' => '仅国家',
                        '2' => '仅省',
                        '3' => '仅市',
                        '4' => '国家+省',
                        '5' => '省+市',
                        '6' => '详细',
                    ),
                    'default' => '2',
                ),
                array(
                    'id'      => 'v4_type',
                    'type'    => 'radio',
                    'title'   => 'IPv4归属地数据库版本',
                    'inline'  => true,
                    'options' => array(
                        'qqwry'     => 'QQwry',
                        'ip2region' => 'Ip2Region',
                    ),
                    'class'   => 'compact min',
                    'default' => 'qqwry',
                ),
                array(
                    'id'      => 'comment',
                    'type'    => 'switcher',
                    'title'   => '评论显示用户归属地',
                    'default' => false,
                    'class'   => 'compact min',
                ),
                array(
                    'type'    => 'submessage',
                    'style'   => 'info',
                    'content' => ip_db_manage(),
                ),
            ),
        ),
        array(
            'id'      => 'favicon_api',
            'type'    => 'textarea',
            'title'   => 'favicon 图标源',
            'default' => 'https://t'.rand(0,3).'.gstatic.cn/faviconV2?client=SOCIAL&type=FAVICON&fallback_opts=TYPE,SIZE,URL&size=128&url=%url%',
            'after'   => '<b>参数：</b><br><code>%url%</code> 包含http(s):// 的完整网址<br><code>%host%</code> 只是域名，如：www.iotheme.cn<br><br>
                        <b>默认：</b><br><code>https://t'.rand(0,3).'.gstatic.cn/faviconV2?client=SOCIAL&type=FAVICON&fallback_opts=TYPE,SIZE,URL&size=128&url=%url%</code> ，可以自建反代服务器<br>
                        或者使用：<code>https://api.iowen.cn/favicon/%host%.png</code> ；可以自建图标源api，源码地址：<a href="https://www.iowen.cn/favicon-api/" target="_blank">--></a>',
            'attributes' => array(
                'rows' => 1,
            ),
        ),
        array(
            'id'      => 'preview_api',
            'type'    => 'textarea',
            'title'   => '网址预览 API',
            'default' => 'https://cdn.iocdn.cc/mshots/v1/%host%?w=%width%&h=%height%',
            'after'   => '<b>参数：</b><br><code>%url%</code> 包含http(s):// 的完整网址<br><code>%host%</code> 只是域名，如：www.iotheme.cn<br><code>%width%</code>宽<br><code>%height%</code>高<br><br>
                        <b>默认：</b><br><code>https://cdn.iocdn.cc/mshots/v1/%host%</code> <br>
                        <code>cdn.iocdn.cc/mshots</code> 为反代的<code>https://s'.rand(0,5).'.wp.com/mshots</code>，可以自建反代服务器',
            'attributes' => array(
                'rows' => 1,
            ),
        ),
        array(
            'id'      => 'qr_api',
            'type'    => 'button_set',
            'title'   => '二维码api源',
            'options' => array(
                'local' => '本地',
                'other' => '第三方',
            ),
            'default' => 'local',
        ),
        array(
            'id'         => 'qr_url',
            'type'       => 'text',
            'title'      => '二维码api', 
            'subtitle'   => '可用二维码api源地址：<a href="https://www.iowen.cn/latest-qr-code-api-service-https-available/" target="_blank">--></a>',
            'default'    => '//api.qrserver.com/v1/create-qr-code/?size=$sizex$size&margin=10&data=$url',
            'after'      => '参数：<br>$size 大小 <br>$url  地址 <br>如：s=$size<span style="color: #ff0000;">x</span>$size 、 size=$size 、 width=$size&height=$size<br><br>默认内容：//api.qrserver.com/v1/create-qr-code/?size=$sizex$size&margin=10&data=$url',
            'class'      => 'compact min',
            'dependency' => array('qr_api', '==', 'other')
        ),
        array(
            'id'         => 'random_head_img',
            'type'       => 'textarea',
            'title'      => '博客随机头部图片', 
            'subtitle'   => '缩略图、文章页随机图片', 
            'after'      => '一行一个图片地址，注意不要有空格<br>', 
            'attributes' => array(
                'rows' => 10,
            ),
            'default'    => '//cdn.iocdn.cc/gh/owen0o0/ioStaticResources@master/screenshots/1.jpg' . PHP_EOL .
                            '//cdn.iocdn.cc/gh/owen0o0/ioStaticResources@master/screenshots/2.jpg' . PHP_EOL .
                            '//cdn.iocdn.cc/gh/owen0o0/ioStaticResources@master/screenshots/3.jpg' . PHP_EOL .
                            '//cdn.iocdn.cc/gh/owen0o0/ioStaticResources@master/screenshots/4.jpg' . PHP_EOL .
                            '//cdn.iocdn.cc/gh/owen0o0/ioStaticResources@master/screenshots/5.jpg' . PHP_EOL .
                            '//cdn.iocdn.cc/gh/owen0o0/ioStaticResources@master/screenshots/6.jpg' . PHP_EOL .
                            '//cdn.iocdn.cc/gh/owen0o0/ioStaticResources@master/screenshots/7.jpg' . PHP_EOL .
                            '//cdn.iocdn.cc/gh/owen0o0/ioStaticResources@master/screenshots/8.jpg' . PHP_EOL .
                            '//cdn.iocdn.cc/gh/owen0o0/ioStaticResources@master/screenshots/9.jpg' . PHP_EOL .
                            '//cdn.iocdn.cc/gh/owen0o0/ioStaticResources@master/screenshots/0.jpg',
        ),
    )
);