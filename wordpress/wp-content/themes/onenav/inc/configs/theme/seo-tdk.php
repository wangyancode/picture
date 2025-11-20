<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2024-10-12 13:22:49
 * @LastEditors: iowen
 * @LastEditTime: 2025-03-22 23:55:08
 * @FilePath: /onenav/inc/configs/theme/seo-tdk.php
 * @Description: 标题配置
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

return array(
    'title'  => 'TDK 配置', 
    'icon'   => 'fa fa-circle-o',
    'fields' => io_get_tdk_config()
);


function io_get_tdk_config()
{
    $config = array(
        array(
            'type'    => 'submessage',
            'style'   => 'info',
            'content' => '<p style="font-size:18px"><i class="fa fa-fw fa-info-circle fa-fw"></i> 本页设置不影响SEO插件，如果使用了SEO插件，此页设置可能无效</p>
            注意：如果对应内容设置了自定义SEO选项的 <b>标题</b>、<b>关键词</b>、<b>描述</b>，下方 <b>组成</b> 里的对应项将用自定义SEO选项的内容替换',
        ),
        array(
            'id'      => 'seo_desc_count',
            'type'    => 'number',
            'title'   => '简介字数',
            'default' => 150,
            'unit'    => '字',
            'after'   => '简介截取的最大字数',
        ),
        array(
            'id'      => 'tdk_linker',
            'type'    => 'text',
            'title'   => '标题组成分隔符',
            'default' => ' - ',
        ),
    );

    $type = array(
        'sites' => '网址',
        'app'   => '软件',
        'book'  => '书籍',
        'post'  => '文章'
    );

    foreach ($type as $key => $value) {
        switch ($key) {
            case 'sites':
                $main_title_custom_default = '%title%官网';
                $main_title_make_option = array(
                    'title'  => '标题',
                    'desc'   => '简介',
                    'custom' => '自定义内容',
                    'url'    => 'URL',
                    'host'   => '域名'
                );
                $main_title_make_default = array('custom', 'desc');
                $main_key_custom_default = '%title%官网,%title%推荐,%title%分享,%cat%资源,网址推荐,有用网站,%blogname%';
                $main_desc_custom_default = '';
                $term_title_custom_default = '%blogname%%title%官网，%title%导航为您提供服务，精心挑选，安全无毒，找%title%网址就来%blogname%，这里收集全网最全的网站资源。';
                break;
            case 'app':
                $main_title_custom_default = '%title%下载与使用指南';
                $main_title_make_option = array(
                    'title'   => '标题',
                    'version' => '版本',
                    'ad'      => '是否有广告',
                    'status'  => '状态',
                    'time'    => '更新时间',
                    'desc'    => '简介',
                    'custom'  => '自定义内容',
                );
                $main_title_make_default = array('title', 'version', 'ad', 'status', 'time', 'desc');
                $main_key_custom_default = '%title%,%title%下载,%title%教程,软件推荐,软件评测,使用指南';
                $main_desc_custom_default = '在%blogname%上了解%title%的详细介绍、主要功能特点和使用指南。%title%是%cat%中的领先工具，点击下载并查看使用教程。';
                $term_title_custom_default = '在%blogname%上，获取关于%title%类型的软件，了解%title%类软件的详细介绍、主要功能特点和使用指南。';
                break;
            case 'book':
                $main_title_custom_default = '';
                $main_title_make_option = array(
                    'title'   => '标题',
                    'journal' => '期刊类型',
                    'desc'    => '简介',
                    'custom'  => '自定义内容',
                );
                $main_title_make_default = array('title', 'custom', 'desc');
                $main_key_custom_default = '%title%,%title%推荐,%title%分享,%blogname%,图书推荐,阅读分享,书评';
                $main_desc_custom_default = '在%blogname%上，阅读关于%title%的详细分享和推荐。%title%是%cat%中不可错过的一本好书，了解它的精华内容和推荐理由。';
                $term_title_custom_default = '在%blogname%上，获取关于%title%类型的书籍，了解%title%类书籍的精华内容和推荐理由。';
                break;
            default:
                $main_title_custom_default = '';
                $main_title_make_option = array(
                    'title'  => '标题',
                    'desc'   => '简介',
                    'custom' => '自定义内容',
                );
                $main_title_make_default = array('title');
                $main_key_custom_default = '%blogname%';
                $main_desc_custom_default = '';
                $term_title_custom_default = '';
                break;
        }

        $config[] = array(
            'id'      => $key . '_seo_tdk',
            'type'    => 'tabbed',
            'title'   => $value . '配置',
            'class'   => 'new',
            'tabs'    => array(
                array(
                    'title'  => '正文页',
                    'fields' => array(
                        array(
                            'type'    => 'heading',
                            'content' => '标题',
                            'style'   => 'info',
                            'class'   => 'min-heading'
                        ),
                        array(
                            'id'         => 'main_title_custom',
                            'type'       => 'textarea',
                            'title'      => '自定义内容',
                            'after'      => '<b>参数：</b><br><code>%title%</code> 文章标题<br><code>%blogname%</code> 网站名称<br><code>%cat%</code> 分类名称',
                            'attributes' => array(
                                'rows' => 1,
                            ),
                        ),
                        array(
                            'id'          => 'main_title_make',
                            'type'        => 'select',
                            'title'       => '标题组成',
                            'chosen'      => true,
                            'multiple'    => true,
                            'sortable'    => true,
                            'options'     => $main_title_make_option,
                            'placeholder' => '选择组成',
                        ),
                        array(
                            'type'    => 'heading',
                            'content' => '关键词',
                            'style'   => 'info',
                            'class'   => 'min-heading'
                        ),
                        array(
                            'id'         => 'main_key_custom',
                            'type'       => 'textarea',
                            'title'      => '自定义内容',
                            'after'      => '<b>参数：</b><br><code>%title%</code> 文章标题<br><code>%blogname%</code> 网站名称<br><code>%cat%</code> 分类名称',
                            'attributes' => array(
                                'rows' => 1,
                            ),
                        ),
                        array(
                            'id'          => 'main_key_make',
                            'type'        => 'select',
                            'title'       => '关键词组成',
                            'chosen'      => true,
                            'multiple'    => true,
                            'sortable'    => true,
                            'options'     => array(
                                'title'  => '标题',
                                'key'    => '关键词',
                                'tag'    => '标签列表',
                                'cat'    => '分类列表',
                                'custom' => '自定义内容',
                            ),
                            'placeholder' => '选择组成',
                        ),
                        array(
                            'type'    => 'heading',
                            'content' => '简介',
                            'style'   => 'info',
                            'class'   => 'min-heading'
                        ),
                        array(
                            'id'         => 'main_desc_custom',
                            'type'       => 'textarea',
                            'title'      => '自定义内容',
                            'after'      => '<b>参数：</b><br><code>%title%</code> 文章标题<br><code>%blogname%</code> 网站名称<br><code>%cat%</code> 分类名称',
                            'attributes' => array(
                                'rows' => 1,
                            ),
                        ),
                        array(
                            'id'          => 'main_desc_make',
                            'type'        => 'select',
                            'title'       => '简介组成',
                            'chosen'      => true,
                            'multiple'    => true,
                            'sortable'    => true,
                            'options'     => array(
                                'title'   => '标题',
                                'desc'    => '简介',
                                'custom'  => '自定义内容',
                                'content' => '正文摘要',
                            ),
                            'placeholder' => '选择组成',
                        ),
                    )
                ),
                array(
                    'title'  => '归档页(分类or标签)',
                    'fields' => array(
                        array(
                            'type'    => 'heading',
                            'content' => '标题',
                            'style'   => 'info',
                            'class'   => 'min-heading'
                        ),
                        array(
                            'id'         => 'term_title_custom',
                            'type'       => 'textarea',
                            'title'      => '自定义内容',
                            'after'      => '<b>参数：</b><br><code>%title%</code> 分类名称<br><code>%blogname%</code> 网站名称',
                            'attributes' => array(
                                'rows' => 1,
                            ),
                        ),
                        array(
                            'id'          => 'term_title_make',
                            'type'        => 'select',
                            'title'       => '标题组成',
                            'chosen'      => true,
                            'multiple'    => true,
                            'sortable'    => true,
                            'options'     => array(
                                'title'  => '分类名称',
                                'desc'   => '简介',
                                'custom' => '自定义内容',
                            ),
                            'placeholder' => '选择组成',
                        ),
                        array(
                            'type'    => 'heading',
                            'content' => '关键词',
                            'style'   => 'info',
                            'class'   => 'min-heading'
                        ),
                        array(
                            'id'         => 'term_key_custom',
                            'type'       => 'textarea',
                            'title'      => '自定义内容',
                            'after'      => '<b>参数：</b><br><code>%title%</code> 分类名称<br><code>%blogname%</code> 网站名称',
                            'attributes' => array(
                                'rows' => 1,
                            ),
                        ),
                        array(
                            'id'          => 'term_key_make',
                            'type'        => 'select',
                            'title'       => '关键词组成',
                            'chosen'      => true,
                            'multiple'    => true,
                            'sortable'    => true,
                            'options'     => array(
                                'title'  => '分类名称',
                                'key'    => '关键词',
                                'custom' => '自定义内容',
                            ),
                            'placeholder' => '选择组成',
                        ),
                        array(
                            'type'    => 'heading',
                            'content' => '简介',
                            'style'   => 'info',
                            'class'   => 'min-heading'
                        ),
                        array(
                            'id'         => 'term_desc_custom',
                            'type'       => 'textarea',
                            'title'      => '自定义内容',
                            'after'      => '<b>参数：</b><br><code>%title%</code> 分类名称<br><code>%blogname%</code> 网站名称',
                            'attributes' => array(
                                'rows' => 1,
                            ),
                        ),
                        array(
                            'id'          => 'term_desc_make',
                            'type'        => 'select',
                            'title'       => '简介组成',
                            'chosen'      => true,
                            'multiple'    => true,
                            'sortable'    => true,
                            'options'     => array(
                                'title'  => '分类名称',
                                'desc'   => '简介',
                                'custom' => '自定义内容',
                            ),
                            'placeholder' => '选择组成',
                        ),
                    )
                ),
            ),
            'default' => array(
                'main_title_custom' => $main_title_custom_default,
                'main_title_make'   => $main_title_make_default,
                'main_key_custom'   => $main_key_custom_default,
                'main_key_make'     => array('key', 'tag', 'cat', 'custom'),
                'main_desc_custom'  => $main_desc_custom_default,
                'main_desc_make'    => array('desc', 'custom'),
                'term_title_custom' => $term_title_custom_default,
                'term_title_make'   => array('title', 'custom', 'desc'),
                'term_key_custom'   => '%title%,%blogname%',
                'term_key_make'     => array('key', 'title', 'custom'),
                'term_desc_custom'  => $term_title_custom_default,
                'term_desc_make'    => array('desc', 'custom'),
            ),
        );
    }
    return $config;
}