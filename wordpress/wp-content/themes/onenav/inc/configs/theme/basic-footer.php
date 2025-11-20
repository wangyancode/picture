<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2024-04-27 00:51:48
 * @LastEditors: iowen
 * @LastEditTime: 2025-06-03 17:24:59
 * @FilePath: /onenav/inc/configs/theme/basic-footer.php
 * @Description: 
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

return array(
    'title'  => '页脚设置',
    'icon'   => 'fa fa-caret-square-o-down',
    'fields' => array(
        array(
            'id'       => 'footer_copyright',
            'type'     => 'wp_editor',
            'title'    => '自定义页脚版权',
            'height'   => '100px',
            'sanitize' => false,
        ),
        array(
            'id'         => 'icp',
            'type'       => 'text',
            'title'      => ' ',
            'subtitle'   => '备案号',
            'after'      => '<i class="fa fa-fw fa-info-circle fa-fw"></i> 此选项“自定义页脚版权”非空则禁用',
            'class'      => 'compact',
            'dependency' => array('footer_copyright', '==', '', '', 'visible'),
        ),
        array(
            'id'         => 'police_icp',
            'type'       => 'text',
            'title'      => ' ',
            'subtitle'   => '公安备案号',
            'after'      => '<i class="fa fa-fw fa-info-circle fa-fw"></i> 此选项“自定义页脚版权”非空则禁用',
            'dependency' => array('footer_copyright', '==', '', '', 'visible'),
            'class'      => 'compact',
        ),

        array(
            'id'      => 'io_aff',
            'type'    => 'switcher',
            'title'   => '显示一为推广按钮',
            'label'   => '添加推广链接获取佣金',
            'after'   => '<br><p>显示： 由 OneNav 强力驱动</p>',
            'default' => true,
        ),
        array(
            'id'         => 'io_id',
            'type'       => 'text',
            'title'      => '一为推广ID',
            'after'      => 'iotheme.cn上的用户ID，6位数纯数字ID。<a href="https://www.iotheme.cn/user" target="_blank">获取ID</a>',
            'dependency' => array('io_aff', '==', true),
            'class'      => 'compact',
        ),

        array(
            'id'            => 'footer_statistics',
            'type'          => 'wp_editor',
            'title'         => '统计代码',
            'tinymce'       => false,
            'quicktags'     => true,
            'media_buttons' => false,
            'height'        => '100px',
            'sanitize'      => false,
            'after'         => '<i class="fa fa-fw fa-info-circle fa-fw"></i> 注意：显示在页脚的统计代码，如需要添加到 &lt;/head&gt; 前，请到“添加代码”中添加。',
        ),

        array(
            'id'      => 'footer_layout',
            'type'    => "image_select",
            'title'   => '页脚布局',
            'options' => array(
                'def' => get_theme_file_uri('/assets/images/option/op_footer_layout_def.png'),
                'big' => get_theme_file_uri('/assets/images/option/op_footer_layout_big.png'),
            ),
            'default' => "big",
            'class'   => '',
        ),

        array(
            'id'         => 'footer_t1',
            'type'       => 'switcher',
            'title'      => '板块一',
            'help'       => '如果不勾选则仅仅在电脑端显示此板块',
            'default'    => true,
            'label'      => '移动端显示',
            'class'      => '',
            'dependency' => array('footer_layout', '==', 'big'),
        ),

        array(
            'id'         => 'footer_t1_code',
            'type'       => 'textarea',
            'title'      => ' ',
            'subtitle'   => '更多内容',
            'class'      => 'compact',
            'default'    => 'OneNav 一为导航主题，集网址、资源、资讯于一体的 WordPress 导航主题，简约优雅的设计风格，全面的前端用户功能，简单的模块化配置，欢迎您的体验',
            'sanitize'   => false,
            'attributes' => array(
                'rows' => 3,
            ),
            'dependency' => array('footer_layout', '==', 'big'),
        ),
        array(
            'id'       => 'footer_social',
            'type'     => 'group',
            'title'    => ' ',
            'subtitle' => '社交信息',
            'class'    => 'compact',
            'before'   => '<span style="color: #f00;"><i class="fa fa-fw fa-info-circle fa-fw"></i> <b>悬浮小工具</b> 也在此处设置</span>',
            'fields'   => array(
                array(
                    'id'    => 'name',
                    'type'  => 'text',
                    'title' => '名称',
                ),
                array(
                    'id'      => 'loc',
                    'type'    => 'button_set',
                    'title'   => '显示位置',
                    'options' => array(
                        'all'    => 'All',
                        'footer' => '仅 footer',
                        'tools'  => '仅悬浮小工具',
                    ),
                    'class'   => 'compact min',
                    'default' => 'footer',
                ),
                array(
                    'id'      => 'ico',
                    'type'    => 'icon',
                    'title'   => '图标',
                    'default' => 'iconfont icon-related',
                    'class'   => 'compact min'
                ),
                array(
                    'id'      => 'type',
                    'type'    => 'button_set',
                    'title'   => '类型',
                    'options' => array(
                        'url' => 'URL连接',
                        'img' => '图片弹窗（如微信二维码）',
                    ),
                    'default' => 'url',
                    'class'   => 'compact min'
                ),
                array(
                    'id'    => 'url',
                    'type'  => 'text',
                    'title' => '地址：',
                    'after' => '<p class="cs-text-muted">【图片弹窗】请填图片地址<br><i class="fa fa-fw fa-info-circle fa-fw"></i> 如果要填QQ，请转换为URL地址，格式为：<br><code>http://wpa.qq.com/msgrd?V=3&uin=xxxxxxxx&Site=QQ&Menu=yes</code><br>将xxxxxx改为您自己的QQ号</p>',
                    'class' => 'compact min'
                ),
            ),
            'default'  => array(
                array(
                    'name' => '微信',
                    'loc'  => 'all',
                    'ico'  => 'iconfont icon-wechat',
                    'type' => 'img',
                    'url'  => get_theme_file_uri('/assets/images/wechat_qrcode.png'),
                ),
                array(
                    'name' => 'QQ',
                    'loc'  => 'all',
                    'ico'  => 'iconfont icon-qq',
                    'type' => 'url',
                    'url'  => 'http://wpa.qq.com/msgrd?V=3&uin=xxxxxxxx&Site=QQ&Menu=yes',
                ),
                array(
                    'name' => '微博',
                    'loc'  => 'footer',
                    'ico'  => 'iconfont icon-weibo',
                    'type' => 'url',
                    'url'  => 'https://www.iotheme.cn',
                ),
                array(
                    'name' => 'GitHub',
                    'loc'  => 'footer',
                    'ico'  => 'iconfont icon-github',
                    'type' => 'url',
                    'url'  => 'https://www.iotheme.cn',
                ),
                array(
                    'name' => 'Email',
                    'loc'  => 'footer',
                    'ico'  => 'iconfont icon-email',
                    'type' => 'url',
                    'url'  => 'mailto:1234567788@QQ.COM',
                )
            ),
        ),

        array(
            'id'         => 'footer_t2_code',
            'type'       => 'textarea',
            'title'      => '板块二',
            'subtitle'   => '建议为友情链接，或者站内链接',
            'default'    => '<a href="https://www.iotheme.cn">友链申请</a>' . PHP_EOL . '<a href="https://www.iotheme.cn">免责声明</a>' . PHP_EOL . '<a href="https://www.iotheme.cn">广告合作</a>' . PHP_EOL . '<a href="https://www.iotheme.cn">关于我们</a>',
            'sanitize'   => false,
            'attributes' => array(
                'rows' => 4,
            ),
            'class'      => '',
            'dependency' => array('footer_layout', '==', 'big'),
        ),
        array(
            'id'          => 'footer_t2_nav',
            'type'        => 'select',
            'title'       => ' ',
            'subtitle'    => '请选择菜单',
            'placeholder' => '选择菜单',
            'after'       => '<i class="fa fa-fw fa-info-circle fa-fw"></i> 推荐4个一级菜单加各3个子菜单',
            'options'     => 'menus',
            'class'       => 'compact',
            'dependency'  => array('footer_layout', '==', 'big'),
        ),

        array(
            'id'         => 'footer_t3',
            'type'       => 'switcher',
            'title'      => '板块三',
            'label'      => '移动端显示',
            'class'      => '',
            'dependency' => array('footer_layout', '==', 'big'),
        ),

        array(
            'id'           => 'footer_t3_img',
            'type'         => 'group',
            'max'          => 4,
            'button_title' => '添加图片',
            'class'        => 'compact',
            'title'        => ' ',
            'subtitle'     => '页脚图片',
            'placeholder'  => '显示在板块3的图片内容',
            'fields'       => array(
                array(
                    'id'    => 'text',
                    'type'  => 'text',
                    'title' => '显示文字',
                ),
                array(
                    'id'      => 'image',
                    'type'    => 'upload',
                    'title'   => '显示图片',
                    'library' => 'image',
                    'class'   => 'compact min'
                ),
            ),
            'default'      => array(
                array(
                    'image' => get_theme_file_uri('/assets/images/qr.png'),
                    'text'  => '扫码加QQ群',
                ),
                array(
                    'image' => get_theme_file_uri('/assets/images/qr.png'),
                    'text'  => '扫码加微信',
                ),
            ),
            'dependency'   => array('footer_layout', '==', 'big'),
        ),

        array(
            'id'       => 'down_statement',
            'type'     => 'wp_editor',
            'title'    => '下载页版权声明',
            'default'  => '本站大部分下载资源收集于网络，只做学习和交流使用，版权归原作者所有。若您需要使用非免费的软件或服务，请购买正版授权并合法使用。本站发布的内容若侵犯到您的权益，请联系站长删除，我们将及时处理。',
            'height'   => '100px',
            'sanitize' => false,
        ),
    )
);