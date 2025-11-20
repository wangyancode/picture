<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2024-04-26 23:52:27
 * @LastEditors: iowen
 * @LastEditTime: 2025-06-06 15:13:06
 * @FilePath: /onenav/inc/configs/theme/basic-global.php
 * @Description: 
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

return array(
    'title'  => '基础设置',
    'icon'   => 'fa fa-th-large',
    'fields' => array(
        array(
            'id'      => 'posts_type_s',
            'type'    => 'checkbox',
            'title'   => '启用文章类型',
            'options' => array(
                'sites' => '网址',
                'app'   => 'APP',
                'book'  => '图书',
            ),
            'inline'  => true,
            'default' => array('sites', 'app', 'book'),
            'after'   => '选择要启用的文章类型，配置后请检查需要类型配置的小工具、选项等设置是否正常。'
        ),
        array(
            'id'      => 'nav_login',
            'type'    => 'switcher',
            'title'   => '顶部登录按钮',
            'default' => true,
        ),
        array(
            'id'      => 'nav_comment',
            'type'    => 'switcher',
            'title'   => '站点评论',
            'default' => true,
            'label'   => '全局开关',
            'class'   => 'compact min',
        ),
        array(
            'id'      => 'min_nav',
            'type'    => 'switcher',
            'title'   => 'mini 边栏（收缩边栏菜单）',
            'label'   => '开启后，左侧菜单默认收缩，开启前请设置好菜单项图标',
            'default' => false,
        ),
        array(
            'id'      => 'show_aside_sub',
            'type'    => 'switcher',
            'title'   => '边栏子菜单',
            'label'   => '开启后，显示边栏子菜单',
            'default' => false,
            'class'   => 'compact min new',
        ),
        array(
            'id'      => 'new_window',
            'type'    => 'switcher',
            'title'   => '新标签中打开内链',
            'label'   => '站点所有内部链接在新标签中打开',
            'default' => true,
        ),
        array(
            'id'      => 'm_language',
            'type'    => 'switcher',
            'title'   => '多语言',
            'default' => false,
            'class'   => 'compact min',
            'desc'    => '设置项启用多语言支持。<br>单语言站请不要开启，如：只有中文、只有英语等。<br><br>
                        <b>注意</b>：开启后需配合“多语言”插件，插件请自行查找（推荐：Polylang）；开启此选项仅用于主题设置选项填写的内容增加翻译。<br><br>
                        <b>词条翻译</b> 如选项名称有“多语言”字样，可填多语言内容，格式为：<code>zh<b>=*=</b>服务内容<b>|*|</b>en<b>=*=</b>Service Content</code> <br>如果不需要翻译，直接填内容，如：服务内容'
        ),
        array(
            'id'         => 'lang_list',
            'type'       => 'text',
            'title'      => '语言列表',
            'after'      => '填语言代码 - 最好是2个字母ISO 639-1（例如：en），如有很多，用<code>|</code>分割（例如：en|ja）<br><br>
                            <span style="color:#f00">默认语言不需要填</span>，如：中文和英文，则填<code>en</code>，英文、中文和日语，则填<code>zh|ja</code><br>
                            <a href="https://zh.wikipedia.org/wiki/ISO_639-1" target="_blank">各国语言缩写参考</a><br><br>
                        <span style="color:#f00">警告：设置后需重新保存一次<a href="' . admin_url('options-permalink.php') . '">固定链接</a></span>',
            'default'    => 'en',
            'class'      => 'compact min',
            'dependency' => array('m_language', '==', 'true')
        ),
        array(
            'id'      => 'post_date_show',
            'type'    => 'switcher',
            'title'   => '内容日期显示',
            'default' => true,
            'label'   => '用于所有文章类型',
            'class'   => 'new',
        ),
        array(
            'id'      => 'post_date_type',
            'type'    => 'radio',
            'title'   => '日期类型',
            'options' => array(
                'update'  => '更新时间',
                'publish'  => '发布时间',
            ),
            'inline'  => true,
            'default' => 'update',
            'class'   => 'compact min',
            'dependency' => array('post_date_show', '==', 'true')
        ),
        array(
            'id'      => 'post_data_format',
            'type'    => 'switcher',
            'title'   => '自然语言日期',
            'default' => true,
            'class'   => 'compact min',
            'label'   => '如：1小时前，1天前，1个月前，1年前等',
            'dependency' => array('post_date_show', '==', 'true')
        ),
        array(
            'id'     => 'nav_menu_list',
            'type'   => 'group',
            'title'  => '子页面导航',
            'after'  => '设置顶部导航菜单',
            'fields' => array(
                array(
                    'id'    => 'url',
                    'type'  => 'link',
                    'title' => '链接',
                    //'class' => 'compact min',
                ),
                array(
                    'id'      => 'icon',
                    'type'    => 'icon',
                    'title'   => '图标',
                    'default' => 'iconfont icon-publish',
                    'class'   => 'compact min',
                ),
                array(
                    'id'    => 'desc',
                    'type'  => 'text',
                    'title' => '介绍',
                    'class' => 'compact min',
                ),
            ),
        ),
        array(
            'id'      => 'no_dead_url',
            'type'    => 'switcher',
            'title'   => '首页屏蔽“确认失效的链接”',
            'default' => false,
            'class'   => 'new',
        ),
        array(
            'id'      => 'global_remove',
            'type'    => 'radio',
            'title'   => '内容显示权限',
            'options' => array(
                'admin' => '仅查看对应权限内容',
                'user'  => '仅限制未登录用户',
                'point' => '不限制仅提示',
                'close' => '关闭',
            ),
            'default' => 'close',
            'after'   => '<div class="ajax-form"><p>' . $tip_ico . '注意：开启此选项可能会导致文章404，解决方法：</p>
                                <li>手动编辑文章，在“权限&商品”选项卡中选择“所有”</li>
                                <!--<li>点击自动添加，会自动扫描全部文章内容添加对应字段，注意：先备份数据库，有问题请恢复。<a class="ajax-get" href="' . add_query_arg(array('action' => 'io_update_post_purview'), admin_url('admin-ajax.php')) . '">立即添加-></a></li>-->
                            <div class="ajax-notice"></div>
                            </div>
                            <p><b>选项说明：</b></p><ol><li><b>仅查看对应权限内容</b>：根据权限，彻底阻止访问未授权的内容，访问高权资源会404，相当于网站不存在对应资源。</li>
                            <li><b>不限制仅提示</b>：在首页等列表能看到站点所有资源，进入高权资源会提示引导相关操作，如登录，升级用户组等。</li>
                            <li><b>仅限制未登录用户</b>：未登录用户同[仅查看对应权限内容]选项，登录用户同[不限制仅提示]选项</li>
                            <li><b>关闭</b>：内容权限等级不生效</li></ol>
                            <p>内容权限请到内容编辑页内修改</p>
                            注意：(必看)<br>1、管理员可见资源永远只能管理员看到。<br>2、采集类直接写库的任务需增加字段<code>_user_purview_level</code>，取值<code>all</code>。',
        ),
        array(
            'id'      => 'sidebar_layout',
            'type'    => 'radio',
            'title'   => '默认侧边栏布局',
            'inline'  => true,
            'options' => array(
                'sidebar_left'  => '靠左',
                'sidebar_right' => '靠右',
            ),
            'default' => "sidebar_right",
            'after'   => '<p style="color:#4abd23"><i class="fa fa-fw fa-info-circle fa-fw"></i> 如需要关掉小工具，可尝试清空对应小工具内容或者到对应页面的选项里关掉小工具。</p>',
        ),
        array(
            'id'      => 'bing_cache',
            'type'    => 'switcher',
            'title'   => '必应背景图片本地缓存',
            'label'   => '文明获取，避免每次都访问 bing 服务器',
            'desc'    => '使用了oss等图床插件的请关闭此功能',
            'default' => false,
        ),
        array(
            'id'       => 'sticky_tag',
            'type'     => 'fieldset',
            'title'    => '置顶标签',
            'fields'   => array(
                array(
                    'id'    => 'switcher',
                    'type'  => 'switcher',
                    'title' => '显示',
                ),
                array(
                    'id'         => 'icon',
                    'type'       => 'text',
                    'title'      => 'icon',
                    'class'      => 'compact min',
                    'dependency' => array('switcher', '==', 'true')
                ),
                array(
                    'id'         => 'name',
                    'type'       => 'text',
                    'title'      => '名称',
                    'class'      => 'compact min',
                    'dependency' => array('switcher', '==', 'true')
                ),
            ),
            'sanitize' => false,
            'default'  => array(
                'switcher' => false,
                'icon'     => '<i class="iconfont icon-top"></i>',
                'name'     => '置顶',
            ),
        ),
        array(
            'id'       => 'new_tag',
            'type'     => 'fieldset',
            'title'    => 'NEW 标签',
            'fields'   => array(
                array(
                    'id'    => 'switcher',
                    'type'  => 'switcher',
                    'title' => '显示',
                ),
                array(
                    'id'         => 'icon',
                    'type'       => 'text',
                    'title'      => 'icon',
                    'class'      => 'compact min',
                    'dependency' => array('switcher', '==', 'true')
                ),
                array(
                    'id'         => 'name',
                    'type'       => 'text',
                    'title'      => '名称',
                    'class'      => 'compact min',
                    'dependency' => array('switcher', '==', 'true')
                ),
                array(
                    'id'         => 'date',
                    'type'       => 'spinner',
                    'title'      => '时间',
                    'after'      => '几天内的内容标记为新内容',
                    'unit'       => '天',
                    'step'       => 1,
                    'class'      => 'compact min',
                    'dependency' => array('switcher', '==', 'true')
                ),
            ),
            'sanitize' => false,
            'default'  => array(
                'switcher' => false,
                'icon'     => '新',
                'name'     => '新增',
                'date'     => 7,
            ),
            'class'    => 'compact min',
        ),
        array(
            'id'      => 'lazyload',
            'type'    => 'switcher',
            'title'   => '图片懒加载',
            'label'   => '所有图片懒加载',
            'default' => true,
        ),
        array(
            'id'      => 'show_friendlink',
            'type'    => 'switcher',
            'title'   => '启用友链',
            'label'   => '启用自定义文章类型“链接(友情链接)”，启用后需刷新页面',
            'default' => true,
        ),
        array(
            'id'         => 'links',
            'type'       => 'switcher',
            'title'      => '友情链接',
            'label'      => '在首页底部添加友情链接',
            'default'    => true,
            'class'      => 'compact min',
            'dependency' => array('show_friendlink', '==', true)
        ),
        array(
            'id'         => 'home_links',
            'type'       => 'checkbox',
            'title'      => '首页显示分类',
            'after'      => '不选则全部显示。',
            'inline'     => true,
            'class'      => 'compact min',
            'options'    => 'categories',
            'query_args' => array(
                'taxonomy' => 'link_category',
            ),
            'dependency' => array('show_friendlink|links', '==|==', 'true|true')
        ),
        //array(
        //    'id'          => 'links_pages',
        //    'type'        => 'select',
        //    'title'       => '友情链接归档页',
        //    'after'       => ' 如果没有，新建页面，选择“友情链接”模板并保存。',
        //    'options'     => 'pages',
        //    'class'       => 'compact min',
        //    'query_args'  => array(
        //        'posts_per_page'  => -1,
        //    ),
        //    'placeholder' => '选择友情链接归档页面', 
        //    //'default'     => io_get_template_page_url('template-links.php',true),
        //    'dependency'  => array( 'show_friendlink|links', '==|==', 'true|true' )
        //),
        array(
            'id'      => 'save_image',
            'type'    => 'switcher',
            'title'   => '本地化外链图片',
            'label'   => '自动存储外链图片到本地服务器',
            'desc'    => '<p>只支持经典编辑器</p><strong>注：</strong>使用古腾堡(区块)编辑器的请不要开启，否则无法保存文章',
            'default' => false,
        ),
        array(
            'id'         => 'exclude_image',
            'type'       => 'textarea',
            'title'      => '本地化外链图片白名单',
            'after'      => '一行一个地址，注意不要有空格。<br>不需要包含http(s)://<br>如：iowen.cn',
            'class'      => 'compact',
            'default'    => 'alicdn.com',
            'dependency' => array('save_image', '==', true)
        ),
    )
);