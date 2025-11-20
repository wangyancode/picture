<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2024-10-04 01:37:49
 * @LastEditors: iowen
 * @LastEditTime: 2024-11-17 01:43:02
 * @FilePath: /onenav/inc/widgets/w.carousel.php
 * @Description: 
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

$tip_ico = '<i class="fa fa-fw fa-info-circle"></i> ';

IOCF::createWidget('iow_carousel_max', array(
    'title'       => 'IO 轮播模块',
    'classname'   => 'io-carousel-max row no-gutters mb-4',
    'description' => '轮播模块',
    'fields'      => array(
        array(
            'id'      => 'count',
            'type'    => 'spinner',
            'title'   => '幻灯片总数量',
            'max'     => 10,
            'min'     => 1,
            'step'    => 1,
            'default' => 5,
            'after'   => '显示置顶的文章，请把需要显示的文章置顶。',
        ),
        array(
            'id'           => 'imgs',
            'type'         => 'group',
            'title'        => '自定义内容',
            'fields'       => array(
                array(
                    'id'    => 'title',
                    'type'  => 'text',
                    'title' => '标题',
                ),
                array(
                    'id'      => 'img',
                    'type'    => 'upload',
                    'title'   => '图片',
                    'library' => 'image',
                    'after'   => $tip_ico . '图片尺寸为 21:9',
                    'class'   => 'compact min',
                ),
                array(
                    'id'    => 'url',
                    'type'  => 'text',
                    'title' => '目标URL',
                    'class' => 'compact min',
                ),
                array(
                    'id'    => 'is_ad',
                    'type'  => 'switcher',
                    'title' => '是广告',
                    'label' => '注意：广告将直达目标URL,不会添加跳转和nofollow',
                    'class' => 'compact min',
                ),
            ),
            'button_title' => '添加卡片',
            'before'       => '内容请填完整，否则轮播模块将显示<b>空白</b>',
        ),
        array(
            'id'    => 'is_tow',
            'type'  => 'switcher',
            'title' => '两篇文章',
        ),
        array(
            'id'         => 'two',
            'type'       => 'text',
            'title'      => ' ',
            'after'      => '自定义文章模块中间的两篇文章，留空则随机展示。<br>填写两个<b>文章id</b>，用英语逗号分开，如：11,100',
            'class'      => 'compact min',
            'dependency' => array('is_tow', '==', true)
        ),
        array(
            'id'    => 'is_hot',
            'type'  => 'switcher',
            'title' => '排行榜',
        ),
        array(
            'id'         => 'is_hot_m',
            'type'       => 'switcher',
            'title'      => ' ',
            'label'      => '移动端显示',
            'class'      => 'compact min',
            'dependency' => array('is_hot', '==', true)
        ),
        array(
            'id'         => 'hot',
            'type'       => 'accordion',
            'title'      => ' ',
            'accordions' => array(
                array(
                    'title'  => '选项',
                    'icon'   => 'fa fa-circle-o',
                    'fields' => array(
                        array(
                            'id'      => 'title',
                            'type'    => 'text',
                            'title'   => '名称(必须填)',
                            'default' => '热门',
                        ),

                        array(
                            'id'      => 'title_ico',
                            'type'    => 'icon',
                            'title'   => ' ',
                            'default' => 'iconfont icon-tools',
                            'class'   => 'compact min'
                        ),
                        array(
                            'id'      => 'post_type',
                            'type'    => 'button_set',
                            'title'   => '文章类型',
                            'options' => array(
                                'post'  => '文章',
                                'sites' => '网址',
                                'app'   => 'APP',
                                'book'  => '书籍',
                            ),
                            'default' => 'post',
                            'class'   => 'compact min'
                        ),
                        array(
                            'id'      => 'orderby',
                            'type'    => 'select',
                            'title'   => '选择数据条件',
                            'options' => 'io_get_sort_data',
                            'default' => 'views',
                            'help'    => '“下载量”只有 APP 有！！！',
                            'class'   => 'compact min'
                        ),

                        array(
                            'id'      => 'count',
                            'type'    => 'number',
                            'title'   => '显示数量',
                            'unit'    => '条',
                            'default' => 6,
                            'class'   => 'compact min'
                        ),

                        array(
                            'id'      => 'window',
                            'type'    => 'switcher',
                            'title'   => '在新窗口打开链接',
                            'default' => true,
                            'class'   => 'compact min'
                        ),

                        array(
                            'id'         => 'days',
                            'type'       => 'number',
                            'title'      => '时间周期',
                            'unit'       => '天',
                            'default'    => 0,
                            'help'       => '只显示此选项设置时间内发布的内容，填 0 则不限制。',
                            'dependency' => array('orderby', '!=', 'rand'),
                            'class'      => 'compact min'
                        ),

                        array(
                            'id'      => 'only_title',
                            'type'    => 'switcher',
                            'title'   => '只显示标题',
                            'default' => false,
                            'class'   => 'compact min'
                        ),

                        array(
                            'id'         => 'serial',
                            'type'       => 'switcher',
                            'title'      => '显示编号',
                            'default'    => true,
                            'class'      => 'compact min',
                            'dependency' => array('only_title', '==', 'true'),
                        ),

                        array(
                            'id'         => 'show_thumbs',
                            'type'       => 'switcher',
                            'title'      => '显示缩略图',
                            'default'    => true,
                            'dependency' => array('only_title|post_type', '==|==', 'false|post'),
                            'class'      => 'compact min'
                        ),

                        array(
                            'id'         => 'go',
                            'type'       => 'switcher',
                            'title'      => '直达',
                            'default'    => false,
                            'help'       => '如果主题设置中关闭了“详情页”，则默认直达',
                            'dependency' => array('only_title|post_type', '==|==', 'false|sites'),
                            'class'      => 'compact min'
                        ),

                        array(
                            'id'         => 'nofollow',
                            'type'       => 'switcher',
                            'title'      => '使用 go 跳转和 nofollow',
                            'default'    => true,
                            'dependency' => array('only_title|go|post_type', '==|==|==', 'false|true|sites'),
                            'class'      => 'compact min'
                        ),

                        array(
                            'id'      => 'ajax',
                            'type'    => 'switcher',
                            'title'   => 'AJAX 加载',
                            'default' => true,
                            'class'   => 'compact min'
                        ),

                        array(
                            'id'         => 'refresh',
                            'type'       => 'switcher',
                            'title'      => '显示刷新按钮',
                            'default'    => false,
                            'dependency' => array('ajax|orderby', '==|!=', 'true|rand'),
                            'class'      => 'compact min'
                        )
                    )
                ),
            ),
            'class'      => 'compact min',
            'dependency' => array('is_hot', '==', true)
        ),
    )
));

function iow_carousel_max($args, $instance)
{
    $swiper_data = array();

    $count = $instance['count'];
    $imgs  = $instance['imgs'];

    if ($imgs) {
        foreach ($imgs as $ad) {
            if ($count > 0) {
                $swiper_data[] = $ad;
                $count--;
            }
        }
    }
    if ($count > 0) {
        $posts = get_custom_count_top_posts($count);
        foreach ($posts as $post) {
            $swiper_data[] = array(
                'title' => $post->post_title,
                'img'   => io_theme_get_thumb($post),
                'url'   => get_permalink($post->ID),
                'is_ad' => false
            );
        }
    }
    $swiper = io_get_swiper($swiper_data, 'carousel-swiper');

    $two = '';
    if ($instance['is_tow'] && !wp_is_mobile()) {
        $post_in  = io_split_str($instance['two']);
        $two_args = [
            'posts_per_page'      => 2,
            'orderby'             => 'rand',
            'ignore_sticky_posts' => 1
        ];

        if (!empty($post_in)) {
            $two_args['post__in'] = $post_in;
            $two_args['orderby']  = 'post__in';
        }
        // 执行查询
        $tow_query = new WP_Query($two_args);
        while ($tow_query->have_posts()) {
            $tow_query->the_post();
            $img_bg = get_lazy_img_bg(io_theme_get_thumb());
            $link   = get_permalink();
            $target = new_window();
            $title  = get_the_title();

            $two .= '<div class="media media-21x9 br-xl">';
            $two .= sprintf(
                '<a class="media-content media-title-bg" href="%s" %s %s><span class="media-title text-sm d-none d-md-block line1">%s</span></a>',
                $link,
                $target,
                $img_bg,
                $title
            );
            $two .= '</div>';
        }
        wp_reset_postdata();
    }

    $hot = '';
    if ($instance['is_hot'] && (!wp_is_mobile() || $instance['is_hot_m'])) {
        $hot_ico = $instance['hot']['title_ico'] ? '<i class="' . $instance['hot']['title_ico'] . ' mr-2"></i>' : '';

        $hot .= '<div class="card posts-hot-list fx-header-bg h-100">';
        $hot .= '<h3 class="text-lg news_title p-3 m-0">' . $hot_ico . $instance['hot']['title'] . '</h3>';
        $hot .= get_widget_single_posts_html($args, $instance['hot'], 'hot');
        $hot .= '</div>';
    }

    $item_1 = '';
    $item_2 = '';
    $item_3 = '';

    if ($swiper && !$two && !$hot) {
        $item_1 = 'col-12';
    } elseif ($swiper && $two && !$hot) {
        $item_1 = 'col-12 col-lg-8';
        $item_2 = 'col-12 col-lg-4 d-none d-lg-flex pl-0 pl-md-3';
    } elseif ($swiper && !$two && $hot) {
        $item_1 = 'col-12 col-lg-8';
        $item_3 = 'col-12 col-lg-4 mt-3 mt-md-0';
    } elseif ($swiper && $two && $hot) {
        $item_1 = 'col-12 col-md-7 col-lg-8 col-xl-6';
        $item_2 = 'col-12 col-xl-3 d-none d-xl-flex pl-0 pl-md-3';
        $item_3 = 'col-12 col-md-5 col-lg-4 col-xl-3 mt-4 mt-md-0';
    } else {
        return;
    }
    $item_2 = $item_2 ? $item_2 . ' flex-column justify-content-between' : '';

    echo $args['before_widget'];
    echo '<div class="' . $item_1 . '">' . $swiper . '</div>';
    echo '<div class="' . $item_2 . '">' . $two . '</div>';
    echo '<div class="' . $item_3 . '">' . $hot . '</div>';
    echo $args['after_widget'];
}

/**
 * 获取指定数量的置顶文章和最新文章
 * @param mixed $number
 * @return array
 */
function get_custom_count_top_posts($number = 5)
{
    $sticky_posts = get_option('sticky_posts');

    $args = [
        'posts_per_page'      => $number,
        'post__in'            => $sticky_posts, // 获取置顶文章
        'ignore_sticky_posts' => 1,  // 忽略置顶规则，手动控制数量
        'orderby'             => 'date',         // 按照发布日期排序
        'order'               => 'DESC'            // 按降序排列
    ];

    $sticky_query = new WP_Query($args);
    $sticky_count = $sticky_query->post_count;

    if ($sticky_count < $number) {
        $remaining_posts = $number - $sticky_count;
        $args            = [
            'posts_per_page' => $remaining_posts,
            'post__not_in'   => $sticky_posts,  // 排除已经获取到的置顶文章
            'orderby'        => 'date',
            'order'          => 'DESC'
        ];
        $recent_query    = new WP_Query($args);

        // 合并置顶和最新文章
        $posts = array_merge($sticky_query->posts, $recent_query->posts);
    } else {
        $posts = $sticky_query->posts;
    }

    // 清除查询缓存
    wp_reset_postdata();

    return $posts;
}
