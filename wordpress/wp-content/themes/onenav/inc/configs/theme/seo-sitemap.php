<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2024-04-27 00:26:05
 * @LastEditors: iowen
 * @LastEditTime: 2024-04-27 00:26:14
 * @FilePath: /onenav/inc/configs/theme/seo-sitemap.php
 * @Description: 
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

return array(
    'title'  => 'SiteMAP&推送', 
    'icon'   => 'fas fa-sitemap',
    'fields' => array(
        array(
            'id'      => 'site_map',
            'title'   => 'SiteMAP', 
            'type'    => 'switcher',
            'label'   => '启用主题 sitemap，生成 sitemap.xml 文件', 
            'desc'    => '不适应于多站点模式，请改用其他插件。', 
            'default' => false,
        ),
        array(
            'id'         => 'site_options',
            'type'       => 'fieldset',
            'title'      => 'SiteMAP选项', 
            'fields'     => array(
                array(
                    'type'    => 'content',
                    'content' => '<span>自动生成xml文件，遵循Sitemap协议，用于指引搜索引擎快速、全面的抓取或更新网站上内容及处理错误信息。兼容百度、google、360等主流搜索引擎。</span><span style="display:block; margin-top: 10px;">注意：参数需要保存后才生效，请设置完参数保存后再点击&quot;生成sitemap&quot;按钮。</span>',
                ),
                array(
                    'id'      => 'baidu-post-types',
                    'type'    => 'checkbox',
                    'title'   => '生成链接文章类型', 
                    'options' => 'post_types',
                    'inline'  => true,
                    'after'   => '例：如果仅希望生成文章的sitemap，则只勾选文章即可。'
                ),
                array(
                    'id'      => 'baidu-taxonomies',
                    'type'    => 'checkbox',
                    'title'   => '生成链接分类', 
                    'options' => 'setting_get_taxes',
                    'inline'  => true,
                ),
                array(
                    'id'    => 'baidu-num',
                    'type'  => 'text',
                    'title' => '生成链接数量', 
                    'after' => '链接数越大所占用的资源也越大，根据自己的服务器配置情况设置数量。最新发布的文章首先排在最前。 <br>-1 表示所有。如果文章太多生成失败，请使用第三方插件。<br>此数量仅指post type的数量总和，不包括分类，勾选的分类是全部生成链接。',
                ),
                array(
                    'id'    => 'baidu-auto-update',
                    'type'  => 'switcher',
                    'title' => '自动更新', 
                    'label' => '勾选则发布新文章或者删除文章时自动更新sitemap。',
                ),
                array(
                    'type'     => 'callback',
                    'function' => 'io_site_map_but',
                ),
            ),
            'default'    => array(
                'sitemap-file'      => 'sitemap',
                'baidu-post-types'  => array('post', 'page'),
                'baidu-taxonomies'  => array('category'),
                'baidu-num'         => '500',
                'baidu-auto-update' => true,
            ),
            'class'      => 'compact min',
            'dependency' => array('site_map', '==', true)
        ),
        array(
            'id'      => 'baidu_submit',
            'type'    => 'fieldset',
            'title'   => '百度主动推送', 
            'fields'  => array(
                array(
                    'id'    => 'switcher',
                    'type'  => 'switcher',
                    'title' => '开启', 
                ),
                array(
                    'id'         => 'token_p',
                    'type'       => 'text',
                    'title'      => '推送token值', 
                    'after'      => '输入百度主动推送token值', 
                    'class'      => 'compact min',
                    'dependency' => array('switcher', '==', 'true')
                ),
            ),
            'default' => array(
                'switcher' => false,
            ),
        ),
        array(
            'id'      => 'baidu_xzh',
            'type'    => 'fieldset',
            'title'   => '百度熊掌号推送', 
            'fields'  => array(
                array(
                    'id'    => 'switcher',
                    'type'  => 'switcher',
                    'title' => '开启', 
                ),
                array(
                    'id'         => 'xzh_id',
                    'title'      => '熊掌号 appid', 
                    'type'       => 'text',
                    'class'      => 'compact min',
                    'dependency' => array('switcher', '==', 'true')
                ),
                array(
                    'id'         => 'xzh_token',
                    'title'      => '熊掌号 token', 
                    'type'       => 'text',
                    'class'      => 'compact min',
                    'dependency' => array('switcher', '==', 'true')
                ),
            ),
            'default' => array(
                'switcher' => false,
            ),
        ),
    )
);