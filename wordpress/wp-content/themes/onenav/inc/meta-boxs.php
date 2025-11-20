<?php
/*!
 * Theme Name:One Nav
 * Theme URI:https://www.iotheme.cn/
 * Author:iowen
 * Author URI:https://www.iowen.cn/
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

if(!is_admin()) return;

$tip_ico = '<i class="fa fa-fw fa-info-circle"></i> ';

$post_meta_base_list = apply_filters('io_post_meta_base_filters', array('post','page','sites','app','book'));

//SEO meta
if( class_exists( 'IOCF' ) ){
    $post_options = 'post-seo_post_meta';
    IOCF::createMetabox($post_options, array(
        'title'     => 'SEO',
        'post_type' => $post_meta_base_list,
        'data_type' => 'unserialize',
        'theme'     => 'light',
        'context'   => 'side',
        'priority'  => 'default',
    ));
    $fields = apply_filters('io_post_seo_meta_filters',
        array(
            array(
                'id'    => '_seo_title',
                'type'  => 'text',
                'title' => __('自定义标题','io_setting'),
                'desc' => 'Title 一般建议15到30个字符',
                'after' => __('留空则获取文章标题','io_setting'),
                'attributes' => array(
                    'style'    => 'width: 100%'
                ),
            ),
            array(
                'id'    => '_seo_metakey',
                'type'  => 'text',
                'title' => __('自定义关键词','io_setting'),
                'desc' => 'Keywords 每个关键词用英语逗号隔开',
                'after' => __('留空则获取文章标签','io_setting'),
                'attributes' => array(
                    'style'    => 'width: 100%'
                ),
            ),
            array(
                'id'    => '_seo_desc',
                'type'  => 'textarea',
                'title' => __('自定义描述','io_setting'),
                'desc' => 'Description 一般建议50到150个字符',
                'after' => __('留空则获取文章简介或摘要','io_setting'),
                'attributes' => array(
                    'style'    => 'width: 100%'
                ),
            ),
        )
    );
    IOCF::createSection( $post_options, array( 'fields' => $fields ));
}

//文章参数
if( class_exists( 'IOCF' ) ) {
    $page_options = 'page-parameter_post_meta';
    IOCF::createMetabox($page_options, array(
        'title'     => '文章参数',
        'post_type' => $post_meta_base_list,
        'context'   => 'side',
        'data_type' => 'unserialize',
    ));
    $fields =  apply_filters('io_post_parameter_meta_filters',
        array(
            array(
                'id'    => 'views',
                'type'  => 'text',
                'title' => __('浏览量','io_setting'), 
                'class' => 'io-horizontal',
                'default' => '0',
            ),
            array(
                'id'    => '_like_count',
                'type'  => 'text',
                'title' => __('点赞量','io_setting'), 
                'class' => 'io-horizontal',
                'default' => '0',
            ),
            array(
                'id'    => '_down_count',
                'type'  => 'text',
                'title' => __('下载量','io_setting'), 
                'class' => 'io-horizontal',
                'default' => '0',
            ),
            array(
                'type'    => 'submessage',
                'style'   => 'normal',
                'content' => '<i class="fa fa-fw fa-info-circle fa-fw"></i>此文章类型不一定包含以上所有数据',
            ),
        )
    );
    IOCF::createSection( $page_options, array( 'fields' => $fields ));
}

//页面扩展
if( class_exists( 'IOCF' ) && IO_PRO ) {
    $page_options = 'page-option_post_meta';
    IOCF::createMetabox($page_options, array(
        'title'     => '页面扩展',
        'post_type' => array('post','page','sites','app','book','bulletin'),
        'context'   => 'side',
        'data_type' => 'unserialize',
        'priority'  => 'high',
    ));
    $fields = apply_filters('io_page_extend_option_meta_filters',
        array(
            array(
                'id'      => 'sidebar_layout',
                'type'    => 'radio',
                'title'   => '侧边栏布局',
                'options' => array(
                    'default'       => '跟随主题设置',
                    'sidebar_no'    => '无侧边栏',
                    'sidebar_left'  => '侧边栏靠左',
                    'sidebar_right' => '侧边栏靠右',
                ),
                'default' => 'default',
            )
        )
    );
    IOCF::createSection( $page_options, array( 'fields' => $fields ));
}

if (class_exists('IOCF') && IO_PRO) {
    $post_options = 'post_post_meta';
    IOCF::createMetabox($post_options, array(
        'title'     => __('查看权限','io_setting'),
        'post_type' => 'post',
        'data_type' => 'unserialize',
        'theme'     => 'light',
        'priority'  => 'high',
        'nav'       => 'inline',
    ));
    IOCF::createSection( $post_options, array( 'fields' => get_user_purview_filters() ));
}

$sortable = '';
if(io_get_option('sites_sortable',false)){
    $sortable = 'disabled';
}
// 网站
if( class_exists( 'IOCF' ) && IO_PRO ) {
    $site_options = 'sites_post_meta';
    IOCF::createMetabox($site_options, array(
        'title'     => __('站点信息','io_setting'),
        'post_type' => 'sites',
        'data_type' => 'unserialize',
        'theme'     => 'light',
        'priority'  => 'high',
        'nav'       => 'inline',
    ));
    $fields = apply_filters(
        'io_sites_post_meta_filters',
        array(
            array(
                'id'      => '_sites_type',
                'type'    => 'button_set',
                'title'   => __('类型', 'io_setting'),
                'options' => array(
                    'sites'  => __('网站', 'io_setting'),
                    'wechat' => __('公众号/小程序', 'io_setting'),
                ),
                'default' => 'sites',
            ),
            array(
                'id'      => '_sites_order',
                'type'    => 'text',
                'title'   => __('排序', 'io_setting'),
                'desc'    => $sortable == '' ? __('网址排序数值越大越靠前', 'io_setting') : '您已经启用拖动排序，请前往列表拖动内容排序',
                'default' => '0',
                'class'   => $sortable,
            ),
            array(
                'id'         => '_goto',
                'type'       => 'switcher',
                'title'      => __('直接跳转', 'io_setting'),
                'default'    => false,
                'class'      => 'compact min',
                'dependency' => array('_sites_type', '==', 'sites'),
            ),
            array(
                'id'         => '_nofollow',
                'type'       => 'switcher',
                'title'      => '不添加 go 跳转和 nofollow',
                'label'      => '依赖于主题相关设置',
                'default'    => false,
                'class'      => 'compact min',
                'dependency' => array('_sites_type|_goto', '==|==', 'sites|true'),
            ),
            array(
                'id'         => '_wechat_id',
                'type'       => 'text',
                'title'      => __('微信号', 'io_setting'),
                'dependency' => array('_sites_type', '==', 'wechat', 'all'),
            ),
            array(
                'id'         => '_is_min_app',
                'type'       => 'switcher',
                'title'      => __('小程序', 'io_setting'),
                'default'    => false,
                'dependency' => array('_sites_type', '==', 'wechat', 'all'),
            ),
            array(
                'id'         => '_sites_link',
                'type'       => 'text',
                'class'      => 'sites_link',
                'title'      => __('链接', 'io_setting'),
                'desc'       => __('需要带上http://或者https://', 'io_setting'),
            ),
            array(
                'id'         => '_spare_sites_link',
                'type'       => 'group',
                'title'      => __('备用链接地址（其他站点）', 'io_setting'),
                'subtitle'   => __('如果有多个链接地址，请在这里添加。', 'io_setting'),
                'fields'     => array(
                    array(
                        'id'    => 'spare_name',
                        'type'  => 'text',
                        'title' => __('站点名称', 'io_setting'),
                    ),
                    array(
                        'id'    => 'spare_url',
                        'type'  => 'text',
                        'title' => __('站点链接', 'io_setting'),
                        'desc'  => __('需要带上http://或者https://', 'io_setting'),
                    ),
                    array(
                        'id'    => 'spare_note',
                        'type'  => 'text',
                        'title' => __('备注', 'io_setting'),
                    ),
                ),
                'class'      => 'compact min',
                'dependency' => array('_sites_type|_sites_link', '==|!=', 'sites|', 'all'),
            ),
            array(
                'id'         => '_sites_sescribe',
                'type'       => 'textarea',
                'title'      => __('一句话描述（简介）', 'io_setting'),
                'after'      => __('推荐不要超过80个字符，详细介绍加正文。', 'io_setting'),
                'class'      => 'sites_sescribe auto-height',
                'attributes' => array(
                    'rows' => "2",
                ),
            ),
            array(
                'id'         => '_sites_language',
                'type'       => 'text',
                'title'      => __('站点语言', 'io_setting'),
                'after'      => __('站点支持的语言，多个用英语逗号分隔，请使用缩写，如：zh,en ，', 'io_setting') . '<a href="https://zh.wikipedia.org/wiki/ISO_639-1" target="_blank">各国语言缩写参考</a>',
                'dependency' => array('_sites_type', '==', 'sites', 'all'),
            ),
            array(
                'id'         => '_sites_country',
                'type'       => 'text',
                'class'      => 'sites_country',
                'title'      => __('站点所在国家或地区', 'io_setting'),
                'dependency' => array('_sites_type', '==', 'sites', 'all'),
            ),
            array(
                'id'      => '_thumbnail',
                'type'    => 'upload',
                'title'   => __('LOGO，标志', 'io_setting'),
                'library' => 'image',
                'class'   => 'sites-ico',
                'before'  => '① <b>获取图标：</b>可以自动下载目标图标到本地。 <br>② <b>生成图标：</b>可生成名字首字图标。（“data:image”图片信息，不会有预览。）<br><span class="sites-ico-msg" style="display:none;color:#dc1e1e;"></span>',
                'desc'    => __('留空则使用api自动获取图标', 'io_setting'),
            ),
            array(
                'id'         => '_sites_preview',
                'type'       => 'upload',
                'title'      => __('网站预览截图', 'io_setting'),
                'class'      => 'sites-preview',
                'before'     => '优先级高于主题设置中的<a href="' . io_get_admin_iocf_url('其他功能') . '" target="_blank">网址预览 API</a><br><span class="sites-preview-msg" style="display:none;color:#dc1e1e;"></span>',
                'dependency' => array('_sites_type', '==', 'sites', 'all'),
            ),
            array(
                'id'         => '_wechat_qr',
                'type'       => 'upload',
                'title'      => __('公众号二维码', 'io_setting'),
            ),
        )
    );
    IOCF::createSection( $site_options, array( 
        'title'  => '基础信息',
        'icon'   => 'fas fa-dice-d6',
        'fields' => $fields )
    );
    IOCF::createSection( $site_options, array( 
        'title'  => '权限&商品',
        'icon'   => 'fa fa-shopping-cart',
        'fields' => get_user_purview_filters() )
    );
}

// app
if( class_exists( 'IOCF' ) && IO_PRO ) {
    $app_options = 'app_post_meta';
    IOCF::createMetabox($app_options, array(
        'title'     => __('APP 信息','io_setting'),
        'post_type' => 'app',
        'data_type' => 'unserialize',
        'theme'     => 'light',
        'priority'  => 'high', 
        'nav'       => 'inline',
    ));

    $fields_basal = apply_filters(
        'io_app_post_basal_meta_filters',
        array(
            array(
                'id'      => '_app_type',
                'type'    => 'button_set',
                'title'   => __('类型', 'io_setting'),
                'options' => array(
                    'app'  => __('软件', 'io_setting'),
                    'down' => __('资源', 'io_setting'),
                ),
                'default' => 'app',
            ),
            array(
                'id'    => '_app_name',
                'type'  => 'text',
                'title' => __('名称', 'io_setting'),
                'after' => 'SEO title 取值用，留空则获取文章标题',
                'class' => 'compact min',
            ),
            array(
                'id'         => '_app_sescribe',
                'type'       => 'text',
                'title'      => __('简介', 'io_setting'),
                'after'      => __('推荐不要超过150个字符，详细介绍加正文。', 'io_setting'),
                'attributes' => array(
                    'style' => 'width: 100%'
                ),
                'class'      => 'compact min',
            ),
            array(
                'id'       => '_app_ico',
                'type'     => 'upload',
                'title'    => __('图标 *', 'io_setting'),
                'subtitle' => __('推荐256x256 必填', 'io_setting'),
                'library'  => 'image',
                'class'    => 'cust_app_ico',
                'desc'     => __('添加图标地址，调用自定义图标', 'io_setting'),
            ),
            array(
                'id'     => 'app_ico_o',
                'type'   => 'fieldset',
                'title'  => __('图标选项', 'io_setting'),
                'fields' => array(
                    array(
                        'type'       => 'content',
                        'content'    => __('预览', 'io_setting'),
                        'dependency' => array('ico_a', '==', true)
                    ),
                    array(
                        'id'    => 'ico_a',
                        'type'  => 'switcher',
                        'title' => __('透明', 'io_setting'),
                        'label' => __('图片是否透明？', 'io_setting'),
                        'class' => 'compact min',
                    ),
                    array(
                        'id'         => 'ico_color',
                        'type'       => 'color_group',
                        'title'      => __('背景颜色', 'io_setting'),
                        'options'    => array(
                            'color-1' => __('颜色 1', 'io_setting'),
                            'color-2' => __('颜色 2', 'io_setting'),
                        ),
                        'default'    => array(
                            'color-1' => '#f9f9f9',
                            'color-2' => '#e8e8e8',
                        ),
                        'class'      => 'compact min',
                        'dependency' => array('ico_a', '==', true)
                    ),
                    array(
                        'id'         => 'ico_size',
                        'type'       => 'slider',
                        'title'      => __('缩放', 'io_setting'),
                        'min'        => 20,
                        'max'        => 100,
                        'step'       => 1,
                        'unit'       => '%',
                        'default'    => 70,
                        'class'      => 'compact min',
                        'dependency' => array('ico_a', '==', true)
                    ),
                ),
            ),
            array(
                'id'      => '_app_platform',
                'type'    => 'checkbox',
                'title'   => __('支持平台', 'io_setting'),
                'inline'  => true,
                'options' => 'get_app_platform',
            ),
            array(
                'id'         => '_down_formal',
                'type'       => 'text',
                'title'      => __('官方地址', 'io_setting'),
                'attributes' => array(
                    'style' => 'width: 100%'
                ),
            ),
            array(
                'id'           => '_screenshot',
                'type'         => 'repeater',
                'title'        => '截图',
                'fields'       => array(
                    array(
                        'id'      => 'img',
                        'type'    => 'upload',
                        'preview' => true,
                    ),
                ),
                'button_title' => '添加截图',
            ),
            array(
                'id'         => '_down_default',
                'type'       => 'accordion',
                'title'      => '',
                'accordions' => array(
                    array(
                        'title'  => '默认版本信息（配置后需保存才能生效）',
                        'fields' => array(
                            array(
                                'id'    => 'app_size',
                                'type'  => 'text',
                                'title' => 'APP 大小',
                                'after' => '填写单位：KB,MB,GB,TB',
                            ),
                            array(
                                'id'      => 'app_status',
                                'type'    => 'radio',
                                'title'   => __('APP状态', 'io_setting'),
                                'inline'  => true,
                                'options' => array(
                                    'official' => __('官方版', 'io_setting'),
                                    'cracked'  => __('开心版', 'io_setting'),
                                    'other'    => __('自定义', 'io_setting'),
                                ),
                                'default' => 'official',
                            ),
                            array(
                                'id'         => 'status_custom',
                                'type'       => 'text',
                                'title'      => __('自定义状态', 'io_setting'),
                                'class'      => 'compact min',
                                'desc'       => '留空则不显示',
                                'dependency' => array('app_status', '==', 'other')
                            ),
                            array(
                                'id'      => 'cpu',
                                'type'    => 'checkbox',
                                'title'   => __('支持的 CPU', 'io_setting'),
                                'class'   => 'compact min',
                                'options' => array(
                                    'x86' => 'X86',
                                    'arm' => 'ARM'
                                ),
                                'inline'  => true,
                                'default' => array('x86'),
                                'desc'    => '苹果M系列芯片也是 ARM'
                            ),
                            array(
                                'id'    => 'app_ad',
                                'type'  => 'switcher',
                                'title' => __('是否有广告', 'io_setting'),
                            ),
                            array(
                                'id'      => 'app_language',
                                'type'    => 'text',
                                'title'   => __('支持语言', 'io_setting'),
                                'default' => __('中文', 'io_setting'),
                            ),
                        ),
                    ),
                ),
                'after'      => '添加版本时，默认显示的版本信息',
            ),
        )
    );
    IOCF::createSection( $app_options, array( 
        'title'  => '基础信息',
        'icon'   => 'fas fa-dice-d6',
        'fields' => $fields_basal )
    );
    $fields_ver = apply_filters('io_app_post_ver_meta_filters',
        array(
            array(
                'content' => '<h4>填写资源下载地址和版本控制</h4>如果需要开启付费下载，请到【权限&商品】选项卡开启“付费”-“附件下载”',
                'style'   => 'info',
                'type'    => 'submessage',
            ), 
            array(
                'id'     => 'app_down_list',
                'type'   => 'group', 
                'before' => __('APP 版本信息（添加版本，可构建历史版本）', 'io_setting'),
                'fields' => array(
                    array(
                        'id'    => 'app_version',
                        'type'  => 'text',
                        'title' => __('版本号','io_setting'),
                        'placeholder'=>__('添加版本号','io_setting'),
                    ),
                    array(
                        'id'      => 'index',
                        'type'    => 'spinner',
                        'title'   => '商品 ID',
                        'min'     => 1,
                        'max'     => 1000,
                        'step'    => 1,
                        'after'   => 'ID 不能小于1，且必须唯一，也不要随意修改，因为购买凭证和此ID关联。',
                        'class'   => 'compact min',
                        'dependency' => array('price_type', '==', 'multi', 'all'),
                    ),
                    array(
                        'id'    => 'app_date',
                        'type'  => 'date',
                        'title' => __('更新日期','io_setting'),
                        'settings' => array(
                            'dateFormat'      => 'yy-m-d',
                            'changeMonth'     => true,
                            'changeYear'      => true, 
                            'showButtonPanel' => true,
                        ),
                        'class'      => 'compact min',
                        'default'    => current_time( 'Y-m-d' ),
                    ),
                    array(
                        'id'     => 'app_size',
                        'type'   => 'text',
                        'title'  => __('APP 大小', 'io_setting'),
                        'after'  => __('填写单位：KB,MB,GB,TB' ,'io_setting'),
                        'class'  => 'compact min',
                        'default' => io_get_app_default_down('app_size',''),
                    ),
                    array(
                        'id'         => 'pay_price',
                        'type'       => 'number',
                        'title'      => '销售价格',
                        'class'      => 'compact min',
                        'default'    => 0,
                        'dependency' => array( 'price_type', '==', 'multi', 'all' ), 
                    ),
                    array(
                        'id'         => 'price',
                        'type'       => 'number',
                        'title'      => '原价',
                        'class'      => 'compact min',
                        'dependency' => array( 'price_type', '==', 'multi', 'all' ), 
                    ),
                    array(
                        'id'     => 'down_url',
                        'type'   => 'group',
                        'before' => __('下载地址信息','io_setting'),
                        'fields' => array(
                            array(
                                'id'    => 'down_btn_name',
                                'type'  => 'text',
                                'title' => __('按钮名称','io_setting'),
                            ),
                            array(
                                'id'    => 'down_btn_url',
                                'type'  => 'text',
                                'title' => __('下载地址','io_setting'),
                                'class' => 'compact min',
                            ),
                            array(
                                'id'    => 'down_btn_tqm',
                                'type'  => 'text',
                                'title' => __('提取码','io_setting'),
                                'class' => 'compact min',
                            ),
                            array(
                                'id'    => 'down_btn_info',
                                'type'  => 'text',
                                'title' => __('描述','io_setting'),
                                'class' => 'compact min',
                            ),
                        ), 
                    ),
                    array(
                        'id'      => 'app_status',
                        'type'    => 'radio',
                        'title'   => __('APP状态','io_setting'),
                        'inline'  => true,
                        'options' => array(
                            'official'  => __('官方版','io_setting'),
                            'cracked'   => __('开心版','io_setting'),
                            'other'     => __('自定义','io_setting'),
                        ),
                        'default' => io_get_app_default_down('app_status','official'),
                    ),
                    array(
                        'id'      => 'status_custom',
                        'type'    => 'text',
                        'title'   => __('自定义状态','io_setting'),
                        'class'   => 'compact min',
                        'desc'    => '留空则不显示',
                        'default' => io_get_app_default_down('status_custom',''),
                        'dependency' => array( 'app_status', '==', 'other' )
                    ),
                    array(
                        'id'      => 'cpu',
                        'type'    => 'checkbox',
                        'title'   => __('支持的 CPU','io_setting'),
                        'class'   => 'compact min',
                        'options' => array(
                            'x86' => 'X86',
                            'arm' => 'ARM'
                        ),
                        'inline'  => true,
                        'desc'    => '苹果M系列芯片也是 ARM',
                        'default' => io_get_app_default_down('cpu',array('x86')),
                    ),
                    array(
                        'id'    => 'app_ad',
                        'type'  => 'switcher',
                        'title' => __('是否有广告','io_setting'),
                        'default' => io_get_app_default_down('app_ad',false),
                    ),
                    array(
                        'id'      => 'app_language',
                        'type'    => 'text',
                        'title'   => __('支持语言','io_setting'),
                        'default' => io_get_app_default_down('app_language',__('中文','io_setting')),
                    ),
                    array(
                        'id'            => 'version_describe',
                        'type'          => 'textarea',
                        'title'         => __('版本描述','io_setting'), 
                        //'tinymce'       => true,
                        //'quicktags'     => true,
                        //'media_buttons' => false,
                        //'height'        => '100px',
                    ),
                ),
                'button_title' => '添加版本信息和下载地址',
                'sanitize' => false,
                'default' => array(
                    array( 
                        'app_version' => '最新版',
                        'index'       => 1,
                        'app_date'    => date('Y-m-d',current_time( 'timestamp' )),
                        'down_url'    => array(
                            array(
                                'down_btn_name' => __('百度网盘','io_setting'),
                            )
                        ), 
                        'app_status'   => 'official',
                        'app_language' => __('中文','io_setting')
                    ),
                )
            ),
        )
    );
    IOCF::createSection( $app_options, array( 
        'title'  => '下载地址',
        'icon'   => 'fab fa-vine',
        'fields' => $fields_ver )
    );
    IOCF::createSection( $app_options, array( 
        'title'  => '权限&商品',
        'icon'   => 'fa fa-shopping-cart',
        'fields' => get_user_purview_filters() )
    );
}

// 书籍
if( class_exists( 'IOCF' ) && IO_PRO ) {
    $book_options = 'book_post_meta';
    IOCF::createMetabox($book_options, array(
        'title'     => __('书籍信息','io_setting'),
        'post_type' => 'book',
        'data_type' => 'unserialize',
        'theme'     => 'light',
        'priority'  => 'high',
        'nav'       => 'inline',
    ));
    $fields = apply_filters('io_book_post_meta_filters',
        array(
            array(
                'id'           => '_book_type',
                'type'         => 'button_set',
                'title'        => __('类型','io_setting'),
                'options'      => array(
                    'books'      => __('图书','io_setting'),
                    'periodical' => __('期刊','io_setting'),
                    'movie'      => __('电影','io_setting'),
                    'tv'         => __('电视剧','io_setting'),
                    'video'      => __('小视频','io_setting'),
                ),
                'default'      => 'books',
            ),
            array(
                'id'      => '_thumbnail',
                'type'    => 'upload',
                'title'   => __('封面','io_setting'),
                'library' => 'image',
            ),
            array(
                'id'      => '_summary',
                'type'    => 'text',
                'title'   => __('一句话描述（简介）','io_setting'),
                'after'   => '<br>'.__('推荐不要超过150个字符，详细介绍加正文。','io_setting'),
                'attributes' => array(
                    'style'    => 'width: 100%'
                ),
            ),
            array(
                'id'      => '_journal',
                'type'    => 'radio',
                'title'   => __('期刊类型','io_setting'),
                'default' => '3',
                'inline'  => true,
                'options' => 'io_get_journal_name',
                'dependency' => array( '_book_type', '==', 'periodical' ),
            ),
            array(
                'id'      => '_score_type',
                'type'    => 'text',
                'title'   => __('评分','io_setting'),
                'default' => '豆瓣',
            ),
            array(
                'id'      => '_score',
                'type'    => 'text',
                'title'   => __('评分值','io_setting'),
                'class'   => 'compact min',
                'default' => '0',
            ),
            array(
                'id'     => '_books_data',
                'type'   => 'group',
                'title'  => __('元数据','io_setting'),
                'fields' => array(
                    array(
                        'id'    => 'term',
                        'type'  => 'text',
                        'title' => __('项目(控制在5个字内)','io_setting'),
                    ),
                    array(
                        'id'    => 'detail',
                        'type'  => 'text',
                        'title' => __('内容','io_setting'),
                        'placeholder' => __('如留空，请删除项','io_setting'),
                    ),
                ), 
                'default' => io_get_option('books_metadata',array()),
            ),
            array(
                'id'     => '_buy_list',
                'type'   => 'group',
                'title'  => __('获取列表','io_setting'),
                'fields' => array(
                    array(
                        'id'      => 'term',
                        'type'    => 'text',
                        'title'   => __('按钮名称','io_setting'),
                        'default' => __('当当网','io_setting'),
                    ),
                    array(
                        'id'    => 'url',
                        'type'  => 'text',
                        'title' => __('URL地址','io_setting'),
                    ),
                    array(
                        'id'    => 'price',
                        'type'  => 'text',
                        'title' => __('价格(可忽略)','io_setting'),
                    ),
                ), 
            ),
            array(
                'id'     => '_down_list',
                'type'   => 'group',
                'title'  => __('下载地址列表','io_setting'),
                'before' => __('添加下载地址，提取码等信息','io_setting'),
                'fields' => array(
                    array(
                        'id'    => 'name',
                        'type'  => 'text',
                        'title' => __('按钮名称','io_setting'),
                        'default' => __('百度网盘','io_setting'),
                    ),
                    array(
                        'id'    => 'url',
                        'type'  => 'text',
                        'title' => __('下载地址','io_setting'),
                    ),
                    array(
                        'id'    => 'tqm',
                        'type'  => 'text',
                        'title' => __('提取码','io_setting'),
                    ),
                    array(
                        'id'    => 'info',
                        'type'  => 'text',
                        'title' => __('描述','io_setting'),
                        'placeholder' => __('格式、大小等','io_setting'),
                    ),
                ), 
            ),
        )
    );
    IOCF::createSection( $book_options, array( 
        'title'  => '基础信息',
        'icon'   => 'fas fa-dice-d6',
        'fields' => $fields )
    );
    IOCF::createSection( $book_options, array( 
        'title'  => '权限&商品',
        'icon'   => 'fa fa-shopping-cart',
        'fields' => get_user_purview_filters() )
    );
}

// 公告
if( class_exists( 'IOCF' ) && IO_PRO ) {
    $site_options = 'bulletin_post_meta';
    IOCF::createMetabox($site_options, array(
        'title'     => __('公告设置','io_setting'),
        'post_type' => 'bulletin',
        'data_type' => 'unserialize',
        'theme'     => 'light',
        'priority'  => 'high',
    ));
    $fields = apply_filters('io_bulletin_post_meta_filters',
        array(
            array(
                'id'      => '_goto',
                'type'    => 'text',
                'title'   => __('直达地址','io_setting'),
                'after'   => '<br>'.__('添加直达地址，如：https://www.baidu.com','io_setting'),
                'attributes' => array(
                    'style'    => 'width: 100%'
                ),
            ),
            array(
                'id'    => '_is_go',
                'type'  => 'switcher', 
                'title'   => __('GO 跳转','io_setting'),
                'text_on' => '启用',
                'text_off'=> '禁用',
                'default' => false,
                'dependency' => array( '_goto', '!=', '' )
            ),
            array(
                'id'    => '_nofollow',
                'type'  => 'switcher', 
                'title'   => __('nofollow','io_setting'),
                'text_on' => '启用',
                'text_off'=> '禁用',
                'default' => false,
                'dependency' => array( '_goto', '!=', '' )
            ),
        )
    );
    IOCF::createSection( $site_options, array( 'fields' => $fields ));
}




// 友情链接选项
if( class_exists( 'IOCF' ) && IO_PRO ) {
    $prefix = 'links_post_options';
    IOCF::createMetabox( $prefix, array(
        'title'           => '友情链接选项',
        'post_type'       => 'page',
        'page_templates'  => 'template-links.php',
        'context'         => 'side',
        'data_type'       => 'unserialize'
    ) );
    IOCF::createSection( $prefix, array(
        'fields' => array(
            array(
                'id'      => '_disable_links_content',
                'type'    => 'switcher', 
                'title'   => '默认内容',
                'text_on' => '启用',
                'text_off'=> '禁用',
                'default' => true,
            ),
            array(
                'id'      => '_links_form',
                'type'    => 'switcher', 
                'title'   => '投稿表单',
                'text_on' => '启用',
                'text_off'=> '禁用',
                'default' => true,
            ),
        )
    ));
}
// 投稿页面选项
if( class_exists( 'IOCF' ) && IO_PRO ) {
    $prefix = 'contribute_post_options';
    IOCF::createMetabox( $prefix, array(
        'title'           => '投稿页面选项',
        'post_type'       => 'page',
        'page_templates'  => 'template-contribute.php',
        'priority'        => 'high',
        'data_type'       => 'unserialize'
    ) );
    IOCF::createSection($prefix, array(
        'fields' => array(
            array(
                'id'      => '_top_menu',
                'type'    => 'switcher',
                'title'   => '收录投稿页顶部菜单',
                'default' => true,
            ),
            array(
                'id'      => '_content_s',
                'type'    => 'switcher',
                'title'   => '投稿须知',
                'default' => true,
                'desc'    => '启用则显示正文内容',
            ),
            array(
                'id'         => '_tip_loc',
                'type'       => 'select',
                'title'      => '投稿须知位置',
                'options'    => array(
                    'before' => '投稿表单前',
                    'after'  => '投稿表单后',
                ),
                'default'    => 'after',
                'class'      => 'compact min',
                'dependency' => array('_content_s', '==', true)
            ),
            array(
                'id'         => '_notice_text',
                'type'       => 'text',
                'title'      => '投稿须知文字',
                'default'    => '投稿须知',
                'class'      => 'compact min',
                'dependency' => array('_content_s', '==', true)
            ),
            array(
                'id'      => '_sub_title',
                'type'    => 'textarea',
                'title'   => '一句话',
                'default' => '把你的发现记录下来，让每一份灵感与收获都成为你成长的基石。',
            ),
        )
    ));
}

// 次级导航选项
if( class_exists( 'IOCF' ) && IO_PRO ) {
    $prefix = 'mininav_post_options';
    IOCF::createMetabox( $prefix, array(
        'title'           => '次级页面选项',
        'post_type'       => 'page',
        'page_templates'  => 'template-mininav.php',
        'priority'        => 'high',
        'data_type'       => 'unserialize'
    ) );
    $fields = apply_filters('io_template_mininav_meta_filters',
        array(
            array(
                'id'          => 'page_module_id',
                'type'        => 'select',
                'title'       => '请选择页面布局模块',
                'options'     => get_seconds_module_list(),
                'after'       => '<a href="' . io_get_admin_iocf_url('子页面布局', 'home_module') . '" target="_blank">前往设置</a>',
            ),
            array(
                'id'           => '_count_type',
                'type'         => 'button_set',
                'title'        => __('显示数量','io_setting'),
                'options'      => array(
                    '0' => __('继承首页设置','io_setting'),
                    '1' => __('自定义','io_setting'),
                ),
                'default'      => '0',
            ),
            array(
                'id'        => 'card_n',
                'type'      => 'fieldset',
                'title'     => __('内容数量配置','io_setting'),
                'fields'    => array(
                    array(
                        'id'    => 'favorites',
                        'type'  => 'spinner',
                        'title' => __('网址数量','io_setting'),
                        'step'       => 1,
                    ),
                    array(
                        'id'    => 'apps',
                        'type'  => 'spinner',
                        'title' => __('App 数量','io_setting'),
                        'step'       => 1,
                    ),
                    array(
                        'id'    => 'books',
                        'type'  => 'spinner',
                        'title' => __('书籍数量','io_setting'),
                        'step'       => 1,
                    ),
                    array(
                        'id'    => 'category',
                        'type'  => 'spinner',
                        'title' => __('文章数量','io_setting'),
                        'step'       => 1,
                    ),
                ),
                'default'        => array(
                    'favorites'   => 20,
                    'apps'        => 16,
                    'books'       => 16,
                    'category'    => 16,
                ),
                'after'      => '填写需要显示的数量。<br>-1 为显示分类下所有网址<br>&nbsp;0 为根据<a href="'.home_url().'/wp-admin/options-reading.php">系统设置数量显示</a>',
                'class'      => 'compact',
                'dependency' => array( '_count_type', '==', '1' )
            ),
        )
    );
    IOCF::createSection( $prefix, array( 'fields' => $fields ));
}

