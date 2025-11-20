<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2024-04-27 00:58:53
 * @LastEditors: iowen
 * @LastEditTime: 2024-12-09 23:11:43
 * @FilePath: /onenav/inc/configs/theme/optimize-disable.php
 * @Description: 
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

return array(
    'title'  => '禁用功能', 
    'icon'   => 'fa fa-wordpress',
    'fields' => array(
        array(
            'type'    => 'submessage',
            'style'   => 'danger',
            'content' => '<p style="font-size:18px">' . $tip_ico . '注意：如果不了解下面选项的作用，请保持原样！</p>
            <p>请认真理解选项的语意，谢谢！</p>',
        ),
        array(
            'id'         => 'disable_auto_update',
            'type'       => 'switcher',
            'title'      => '屏蔽自动更新', 
            'label'      => '关闭自动更新功能，通过手动更新。', 
            'default'    => false,
        ),
        array(
            'id'         => 'disable_rest_api',
            'type'       => 'switcher',
            'title'      => '禁用REST API', 
            'label'      => '禁用REST API、移除wp-json链接（默认关闭，如果你的网站没有开启标签页、小程序、APP，建议禁用REST API）', 
            'default'    => false
        ),

        array(
            'id'         => 'disable_revision',
            'type'       => 'switcher',
            'title'      => '禁用文章修订功能', 
            'label'      => '禁用文章修订功能，精简 Posts 表数据。(如果古滕堡报错，请关闭该选项)', 
            'default'    => false
        ),


        array(
            'id'         => 'disable_texturize',
            'type'       => 'switcher',
            'title'      => '禁用字符转码', 
            'label'      => '禁用字符换成格式化的 HTML 实体功能。', 
            'default'    => true
        ),

        array(
            'id'         => 'disable_feed',
            'type'       => 'switcher',
            'title'      => '禁用站点Feed', 
            'label'      => '禁用站点Feed，防止文章快速被采集。', 
            'default'    => true
        ),

        array(
            'id'         => 'disable_trackbacks',
            'type'       => 'switcher',
            'title'      => '禁用Trackbacks', 
            'label'      => 'Trackbacks协议被滥用，会给博客产生大量垃圾留言，建议彻底关闭Trackbacks。', 
            'default'    => true
        ),

        array(
            'id'         => 'disable_gutenberg',
            'type'       => 'switcher',
            'title'      => '禁用古腾堡编辑器', 
            'label'      => '禁用Gutenberg编辑器，换回经典编辑器。', 
            'desc'       => $tip_ico . '注意：古腾堡如果出现json错误，可以重新保存一下<a href="' . admin_url('options-permalink.php') . '">固定链接</a>试试。',
            'default'    => true
        ),

        array(
            'id'         => 'disable_xml_rpc',
            'type'       => 'switcher',
            'title'      => '禁用XML-RPC', 
            'label'      => '关闭XML-RPC功能，只在后台发布文章。(如果古滕堡报错，请关闭该选项)', 
            'default'    => false,
        ),

        array(
            'id'         => 'disable_privacy',
            'type'       => 'switcher',
            'title'      => '禁用后台隐私（GDPR）', 
            'label'      => '移除为欧洲通用数据保护条例而生成的隐私页面，如果只是在国内运营博客，可以移除后台隐私相关的页面。', 
            'default'    => true
        ),
        array(
            'id'         => 'emoji_switcher',
            'type'       => 'switcher',
            'title'      => '禁用Emoji代码', 
            'label'      => 'WordPress 为了兼容在一些比较老旧的浏览器能够显示 Emoji 表情图标，而准备的功能。屏蔽Emoji图片转换功能，直接使用Emoji。', 
            'default'    => true
        ),
        array(
            'id'         => 'disable_autoembed',
            'type'       => 'switcher',
            'title'      => '禁用Auto Embeds', 
            'label'      => '禁用 Auto Embeds 功能，加快页面解析速度。 Auto Embeds 支持的网站大部分都是国外的网站，建议禁用。', 
            'default'    => true
        ),
        array(
            'id'         => 'disable_post_embed',
            'type'       => 'switcher',
            'title'      => '禁用文章Embed', 
            'label'      => '禁用可嵌入其他 WordPress 文章的Embed功能', 
            'default'    => false
        ),
        array(
            'id'         => 'remove_dns_prefetch',
            'type'       => 'switcher',
            'title'      => '禁用s.w.org', 
            'label'      => '移除 WordPress 头部加载 DNS 预获取（s.w.org 国内根本无法访问）', 
            'default'    => false
        ),
    )
);