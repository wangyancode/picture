<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2022-01-20 22:13:24
 * @LastEditors: iowen
 * @LastEditTime: 2025-06-05 11:55:53
 * @FilePath: /onenav/inc/widgets/w.about.website.php
 * @Description: 关于作者
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

IOCF::createWidget('iow_about_website_min', array(
    'title'       => 'IO 关于本站',
    'classname'   => 'io-widget-about-website',
    'description' => '本站信息数据和联系方式微信、微博、QQ等',
    'fields'      => array(
        array(
            'id'        => 'about_img',
            'type'      => 'upload',
            'title'     => '网站图标',
            'add_title' => '上传',
            'default'   => get_theme_file_uri('/assets/images/app-ico.png'),
        ),
        array(
            'id'        => 'about_back',
            'type'      => 'upload',
            'title'     => '网站图标',
            'add_title' => '上传',
            'default'   => '//cdn.iocdn.cc/gh/owen0o0/ioStaticResources@master/banner/wHoOcfQGhqvlUkd.jpg',
            'class'     => 'compact min'
        ),
        array(
            'id'      => 'show_social_icon',
            'type'    => 'switcher',
            'title'   => '显示社交图标',
            'default' => true,
            'class'   => 'compact min'
        ),
        array(
            'id'         => 'social',
            'type'       => 'group',
            'title'      => '社交信息',
            'fields'     => array(
                array(
                    'id'    => 'name',
                    'type'  => 'text',
                    'title' => '名称',
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
            'default'    => array(
                array(
                    'name' => '微信',
                    'ico'  => 'iconfont icon-wechat',
                    'type' => 'img',
                    'url'  => get_theme_file_uri('/assets/images/wechat_qrcode.png'),
                ),
                array(
                    'name' => 'QQ',
                    'ico'  => 'iconfont icon-qq',
                    'type' => 'url',
                    'url'  => 'http://wpa.qq.com/msgrd?V=3&uin=xxxxxxxx&Site=QQ&Menu=yes',
                ),
                array(
                    'name' => '微博',
                    'ico'  => 'iconfont icon-weibo',
                    'type' => 'url',
                    'url'  => 'https://www.iotheme.cn',
                ),
                array(
                    'name' => 'GitHub',
                    'ico'  => 'iconfont icon-github',
                    'type' => 'url',
                    'url'  => 'https://www.iotheme.cn',
                )
            ),
            'max'        => 5,
            'dependency' => array('show_social_icon', '==', true),
            'class'      => 'compact min'
        ),
        array(
            'id'          => 'sites_data',
            'type'        => 'select',
            'title'       => '站点数据',
            'subtitle'    => '移动端无法设置，请用PC',
            'chosen'      => true,
            'multiple'    => true,
            'sortable'    => true,
            'options'     => array(
                'sites'   => '收集网站数量',
                'post'    => '收集文章数量',
                'app'     => '收集APP数量',
                'book'    => '收集书籍数量',
                'day'     => '站点运行天数',
                'view'    => '站点访问人数',
                'comment' => '站点评论总数',
                'user'    => '站点用户人数',
            ),
            'placeholder' => '选择文章类型',
            'default'     => array('sites', 'post', 'app', 'book'),
            'after'       => '拖动排序，不需要的“x”掉。',
            'class'       => 'compact min'
        ),
        array(
            'id'       => 'favorite',
            'type'     => 'textarea',
            'title'    => '加入收藏提示',
            'default'  => '<h4 class="text-md">加入收藏夹</h4>按<code> Ctrl+D </code>可收藏本网页，方便快速打开使用。 
<h4 class="text-md mt-3">设为首页</h4>浏览器 <b>设置页面</b> > <b>启动时</b> 选项下 <b>打开特定网页或一组网页</b>。',
            'class'    => 'compact min',
            'sanitize' => false,
            'after'    => '支持 HTML，注意代码规范',
        )
    )
));

function iow_about_website_min($args, $instance) {
    $blog_name   = get_bloginfo('name');
    $description = get_bloginfo('description');
    $about_img   = get_lazy_img($instance['about_img'],  $blog_name, 'auto', 'avatar');
    $about_back  = get_lazy_img_bg($instance['about_back']);
    $add_btn     = __('按住拖入收藏夹', 'i_theme');
    $favorite    = $instance['favorite'] ? '<div class="favorites-body fx-header-bg"><div class="position-relative">' . $instance['favorite'] . '</div></div>' : '';

    $social_icon = '';
    if ($instance['show_social_icon'] && !empty($instance['social'])) {
        foreach ($instance['social'] as $social) {
            $_url   = $social['url'];
            $_title = $social['name'];
            $_attr  = 'target="_blank" data-toggle="tooltip" data-placement="top"';
            if ($social['type'] == 'img') {
                $_url   = 'javascript:;';
                $_title = '<img src="' . $social['url'] . '" height="100" width="100">';
                $_attr  = 'data-toggle="tooltip" data-placement="top" data-html="true"';
            } elseif (preg_match('|wpa.qq.com(.*)uin=([0-9]+)\&|', $_url, $matches)) {
                $_url = IOTOOLS::qq_url($matches[2]);
            }
            $social_icon .= '<div class="col"><a href="' . $_url . '" ' . $_attr . ' title="' . esc_attr($_title) . '" rel="external nofollow"><i class="' . $social['ico'] . ' icon-lg"></i></a></div>';
        }
    }
    if (!empty($social_icon)) {
        $social_icon = '<div class="row no-gutters social-icon">' . $social_icon . '</div>';
    }

    $posts_type_s = wp_parse_args((array) io_get_option('posts_type_s'), ['post','day','view','comment','user']);
    $sites_data_s = array_values(array_intersect((array)$instance['sites_data'], $posts_type_s));
    
    $sites_data = '';
    foreach ($sites_data_s as $index => $value) {
        switch ($value) {
            case 'sites':
                $_title = sprintf(__('收录%s', 'i_theme'), io_get_post_type_name($value));
                $_count = io_count_posts('sites')->publish;
                $_icon = 'iconfont icon-sites';
                break;
            case 'post':
                $_title = sprintf(__('收录%s', 'i_theme'), io_get_post_type_name($value));
                $_count = io_count_posts()->publish;
                $_icon = 'iconfont icon-article';
                break;
            case 'app':
                $_title = sprintf(__('收录%s', 'i_theme'), io_get_post_type_name($value));
                $_count = io_count_posts('app')->publish;
                $_icon = 'iconfont icon-app';
                break;
            case 'book':
                $_title = sprintf(__('收录%s', 'i_theme'), io_get_post_type_name($value));
                $_count = io_count_posts('book')->publish;
                $_icon = 'iconfont icon-book';
                break;
            case 'day':
                $_title = __('运行天数', 'i_theme');
                $_count = floor((time() - strtotime(io_get_option('site_created', '2020-11-11'))) / 86400);
                $_icon = 'iconfont icon-time';
                break;
            case 'view':
                $_title = __('访问人数', 'i_theme');
                $_count = get_user_posts_meta_sum('all', 'views');
                $_icon = 'iconfont icon-view';
                break;
            case 'comment':
                $_title = __('评论总数', 'i_theme');
                $_count = wp_count_comments()->total_comments;
                $_icon = 'iconfont icon-comment';
                break;
            case 'user':
                $_title = __('用户数量', 'i_theme');
                $_count = count_users()['total_users'];
                $_icon = 'iconfont icon-user';
                break;
        }
        $_color     = get_theme_color($index, 'l');
        $_col       = $index ? 'col-3a' : 'col-1a';
        $sites_data .= '<div class="' . $_col . ' tips-box ' . $_color . ' btn-outline bg-no-a">';
        $sites_data .= '<div class="text-xl">' . io_number_format($_count) . '</div>';
        $sites_data .= '<div class="text-ss">' . $_title . '</div>';
        $sites_data .= '</div>';
    }
    $about_meta = empty($sites_data)?'': '<div class="about-meta mt-2"><div class="posts-row">' . $sites_data . '</div></div>';

    $home_url = home_url();
    echo $args['before_widget'];
    echo <<<HTML
    <div class="about-website-body">
        <div class="about-cover bg-image media-bg p-2" {$about_back}>
            <div class="d-flex align-items-center">
                <div class="avatar-md">{$about_img}</div>
                <div class="flex-fill overflow-hidden ml-2">
                    <div class="text-md">{$blog_name}</div>
                    <div class="text-xs line1 mt-1">{$description}</div>
                </div>
                <div class="add-to-favorites text-sm">
                    <a href="{$home_url}" class="add-favorites" data-toggle="tooltip" title="{$add_btn}"><i class="iconfont icon-add"></i></a>
                    {$favorite}
                </div>
            </div>
            {$social_icon}
        </div>
        {$about_meta}
    </div>
HTML;
    echo $args['after_widget'];
}


