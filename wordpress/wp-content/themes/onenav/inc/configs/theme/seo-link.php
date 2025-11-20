<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2024-04-27 00:24:43
 * @LastEditors: iowen
 * @LastEditTime: 2025-06-03 17:32:27
 * @FilePath: /onenav/inc/configs/theme/seo-link.php
 * @Description: 
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

return array(
    'title'  => '链接规则', 
    'icon'   => 'fas fa-link',
    'fields' => array(
        array(
            'id'       => 'posts_title',
            'type'     => 'fieldset',
            'title'    => '类型名称重命名', 
            'fields'   => array(
                array(
                    'id'      => 'sites',
                    'type'    => 'text',
                    'title'   => '网址', 
                ),
                array(
                    'id'      => 'book',
                    'type'    => 'text',
                    'title'   => '书籍', 
                    'class'   => 'compact min',
                ),
                array(
                    'id'      => 'app',
                    'type'    => 'text',
                    'title'   => '软件', 
                    'class'   => 'compact min',
                ),
                array(
                    'id'      => 'post',
                    'type'    => 'text',
                    'title'   => '文章', 
                    'class'   => 'compact min',
                ),
            ),
            'default'  => array(
                'sites' => 'zh=*=网址|*|en=*=sites',
                'book'  => 'zh=*=书籍|*|en=*=book',
                'app'   => 'zh=*=软件|*|en=*=app',
                'post'  => 'zh=*=文章|*|en=*=posts',
            ),
            'class'    => 'new',
        ),
        array(
            'type'    => 'submessage',
            'style'   => 'info',
            'content' => '<p style="font-size:18px"><i class="fa fa-fw fa-info-circle fa-fw"></i> 下面内容修改后必须重新保存一次固定链接，且所有选项不能为<b>空</b>。前往<a href="' . admin_url('/options-permalink.php') . '">wp设置</a>保存</p>',
        ),
        array(
            'id'       => 'rewrites_types',
            'type'     => 'button_set',
            'title'    => '网址&软件&书籍固定链接模式', 
            'subtitle' => '<span style="color:#f00">设置后需重新保存一次固定链接</span>',
            'desc'     => '<span style="color:#000"><i class="fa fa-fw fa-info-circle fa-fw"></i> “网址”“app”“书籍”的<a href="' . admin_url('options-permalink.php') . '">固定链接</a>模式<br>
                            默认文章的固定链接设置请前往<a href="' . admin_url('/options-permalink.php') . '">wp设置</a>中设置，推荐 <code>/%post_id%.html</code></span>',
            'options'  => array(
                'post_id'  => '/%post_id%/',
                'postname' => '/%postname%/',
            ),
            'default'  => 'post_id'
        ),
        array(
            'id'       => 'rewrites_end',
            'type'     => 'switcher',
            'title'    => 'html 结尾', 
            'subtitle' => '<span style="color:#f00">设置后需重新保存一次固定链接</span>',
            'label'    => '如：http://w.w.w/123.html', 
            'default'  => true,
        ),
        array(
            'id'       => 'new_page_type',
            'type'     => 'switcher',
            'title'    => '新分页格式【（看清描述）】',
            'subtitle' => '<span style="color:#f00">设置后需重新保存一次固定链接</span>',
            'label'    => '看清描述',
            'desc'     => '<span style="color:#000">文章：<code>http://w.w.w/123.html/2</code>  改为 <code>http://w.w.w/123-2.html</code> (需设置链接以 .html 结尾)<br>
                            分类：<code>http://w.w.w/tag/123.html/page/2</code>  改为 <code>http://w.w.w/tag/123-2.html</code> (需开启下方 [分类&标签固定链接模式] 的选项)</span><br>
                            <p style="color:#f00;margin-top:10px"><i class="fa fa-fw fa-info-circle fa-fw"></i> 注意：此选项可能会和其他插件不兼容而造成404，出现问题请关闭</p>',
            'default'  => false,
            'class'    => '',
        ),
        array(
            'id'       => 'rewrites_category_types',
            'type'     => 'fieldset',
            'title'    => '分类&标签固定链接模式',
            'subtitle' => '<span style="color:#f00">设置后需重新保存一次固定链接</span>',
            'after'    => '<span style="color:#f00"><i class="fa fa-fw fa-info-circle fa-fw"></i> 注意：此选项可能会和其他插件不兼容而造成404，出现问题请关闭</span>',
            'fields'   => array(
                array(
                    'type'       => 'submessage',
                    'style'      => 'success',
                    'content'    => '<i class="fa fa-fw fa-info-circle fa-fw"></i> <b>警告：</b>[优化设置]->[优化加速]中“<b>去除分类标志</b>”选项失效。',
                    'dependency' => array('types|rewrites', 'any|!=', 'cat|default'),
                ),
                array(
                    'id'      => 'rewrites',
                    'type'    => 'button_set',
                    'title'   => '模式', 
                    'options' => array(
                        'default'   => '默认规则',
                        'term_id'   => 'id.html',
                        'term_name' => 'name.html',
                    ),
                ),
                array(
                    'id'         => 'types',
                    'type'       => 'checkbox',
                    'title'      => '启用类型', 
                    'inline'     => true,
                    'options'    => array(
                        'tag' => '标签', 
                        'cat' => '分类', 
                    ),
                    'class'      => 'compact min',
                    'after'      => '默认位置在首页内容前面和分类内容前面显示搜索框', 
                    'dependency' => array('rewrites', '!=', 'default'),
                ),
                array(
                    'type'       => 'submessage',
                    'style'      => 'danger',
                    'content'    => '<i class="fa fa-fw fa-info-circle fa-fw"></i> <b>警告：</b>必须选一项。',
                    'dependency' => array('types|rewrites', '==|!=', '|default'),
                    'class'      => 'compact'
                ),
            ),
            'default'  => array(
                'rewrites' => 'default',
                'types'    => array('tag'),
            ),
        ),
        array(
            'id'       => 'sites_rewrite',
            'type'     => 'fieldset',
            'title'    => '网址文章固定链接前缀',
            'subtitle' => '<span style="color:#f00">设置后需重新保存一次固定链接</span>',
            'fields'   => array(
                array(
                    'id'    => 'post',
                    'type'  => 'text',
                    'title' => '网址',
                ),
                array(
                    'id'    => 'taxonomy',
                    'type'  => 'text',
                    'title' => '网址分类',
                    'class' => 'compact min',
                ),
                array(
                    'id'    => 'tag',
                    'type'  => 'text',
                    'title' => '网址标签',
                    'class' => 'compact min',
                ),
            ),
            'default'  => array(
                'post'     => 'sites',
                'taxonomy' => 'favorites',
                'tag'      => 'sitetag',
            ),
            'after'    => '<i class="fa fa-fw fa-info-circle fa-fw"></i> 设置后需重新保存一次<a href="' . admin_url('options-permalink.php') . '">固定链接</a>，且所有选项不能为空', 
            'dependency' => array('posts_type_s', 'any', 'sites', 'all')
        ),
        array(
            'id'       => 'app_rewrite',
            'type'     => 'fieldset',
            'title'    => 'app文章固定链接前缀',
            'subtitle' => '<span style="color:#f00">设置后需重新保存一次固定链接</span>',
            'fields'   => array(
                array(
                    'id'    => 'post',
                    'type'  => 'text',
                    'title' => 'app',
                ),
                array(
                    'id'    => 'taxonomy',
                    'type'  => 'text',
                    'title' => 'app分类',
                    'class' => 'compact min',
                ),
                array(
                    'id'    => 'tag',
                    'type'  => 'text',
                    'title' => 'app标签',
                    'class' => 'compact min',
                ),
            ),
            'default'  => array(
                'post'     => 'app',
                'taxonomy' => 'apps',
                'tag'      => 'apptag',
            ),
            'after'    => '<i class="fa fa-fw fa-info-circle fa-fw"></i> 设置后需重新保存一次<a href="' . admin_url('options-permalink.php') . '">固定链接</a>，且所有选项不能为空', 
            'dependency' => array('posts_type_s', 'any', 'app', 'all')
        ),
        array(
            'id'       => 'book_rewrite',
            'type'     => 'fieldset',
            'title'    => '书籍文章固定链接前缀',
            'subtitle' => '<span style="color:#f00">设置后需重新保存一次固定链接</span>',
            'fields'   => array(
                array(
                    'id'    => 'post',
                    'type'  => 'text',
                    'title' => '书籍',
                ),
                array(
                    'id'    => 'taxonomy',
                    'type'  => 'text',
                    'title' => '书籍分类',
                    'class' => 'compact min',
                ),
                array(
                    'id'    => 'tag',
                    'type'  => 'text',
                    'title' => '书籍标签',
                    'class' => 'compact min',
                ),
                array(
                    'id'      => 'series',
                    'type'    => 'text',
                    'title'   => '书籍系列',
                    'default' => 'series',
                    'class'   => 'compact min',
                ),
            ),
            'default'  => array(
                'post'     => 'book',
                'taxonomy' => 'books',
                'tag'      => 'booktag',
                'series'   => 'series',
            ),
            'after'    => '<i class="fa fa-fw fa-info-circle fa-fw"></i> ' . '设置后需重新保存一次<a href="' . admin_url('options-permalink.php') . '">固定链接</a>，且所有选项不能为空', 
            'dependency' => array('posts_type_s', 'any', 'book', 'all')
        ),
    )
);