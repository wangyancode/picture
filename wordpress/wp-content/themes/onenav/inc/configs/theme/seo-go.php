<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2024-04-27 00:23:34
 * @LastEditors: iowen
 * @LastEditTime: 2025-03-26 11:41:38
 * @FilePath: /onenav/inc/configs/theme/seo-go.php
 * @Description: 
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

return array(
    'title'  => 'GO 跳转', 
    'icon'   => 'fas fa-user-secret',
    'fields' => array(
        array(
            'type'    => 'subheading',
            'content' => '内链跳转，效果：http://您的域名/go/?url=外链', 
        ),
        array(
            'id'      => 'is_go',
            'type'    => 'switcher',
            'title'   => '内链跳转(go跳转)', 
            'label'   => '站点所有外链跳转，效果：http://您的域名/go/?url=外链', 
            'default' => false,
        ),
        array(
            'id'         => 'is_must_on_site',
            'type'       => 'switcher',
            'title'      => '必须从站内点击才跳转',
            'label'      => '通过 referer 判断域名，如果不是在站内点击则跳转到网站首页。',
            'desc'       => $tip_ico . '如果开启选项导致所有链接都跳转到首页，请关闭！<br>一些 <b>WP插件</b> 或者 <b>浏览器插件</b> 可能会关闭 referer ，比如<b>采集插件</b>。',
            'default'    => false,
            'class'      => 'compact',
            'dependency' => array('is_go', '==', 'true'),
        ),
        array(
            'id'         => 'ref_id',
            'type'       => 'group',
            'title'      => '自定义来源id',
            'before'     => '在收藏网址的URL后面添加参数，如：https://www.iotheme.cn?key1=value1',
            'fields'     => array(
                array(
                    'id'    => 'key',
                    'type'  => 'text',
                    'title' => '键名(key)',
                ),
                array(
                    'id'    => 'value',
                    'type'  => 'text',
                    'title' => '值(value)',
                ),
            ),
            'default'    => array(
                array(
                    'key'   => 'ref',
                    'value' => parse_url(home_url())['host'],
                ),
            ),
            'class'      => 'compact',
            'dependency' => array('is_go', '==', 'true'),
        ),
        array(
            'id'         => 'go_tip',
            'type'       => 'fieldset',
            'title'      => '跳转提示', 
            'fields'     => array(
                array(
                    'id'       => 'switch',
                    'type'     => 'switcher',
                    'title'    => '启用', 
                    'label'    => '提示用户即将离开本站，注意账号和财产安全。', 
                    'subtitle' => '颜色效果中的“全屏加载效果”选项无效',
                ),
                array(
                    'id'    => 'time',
                    'type'  => 'spinner',
                    'title' => '等待跳转', 
                    'after' => '等待多少秒自动跳转<br>注意：填0为手动点击按钮跳转',
                    'unit'  => '秒',
                    'step'  => 1,
                ),
            ),
            'default'    => array(
                'switch' => true,
                'time'   => 0,
            ),
            'class'      => 'compact',
            'dependency' => array('is_go', '==', 'true'),
        ),
        array(
            'id'      => 'is_nofollow',
            'type'    => 'switcher',
            'title'   => '网址块添加nofollow', 
            'label'   => '详情页开启则不添加', 
            'default' => true,
        ),
        array(
            'id'         => 'exclude_links',
            'type'       => 'textarea',
            'title'      => 'go跳转白名单', 
            'subtitle'   => 'go跳转和正文nofollow白名单', 
            'after'      => '一行一个地址，注意不要有空格。<br>需要包含http(s)://<br>iowen.cn和www.iowen.cn为不同的网址<br>此设置同时用于 nofollow 的排除。', 
            'attributes' => array(
                'rows' => 4,
            ),
        ),
    )
);