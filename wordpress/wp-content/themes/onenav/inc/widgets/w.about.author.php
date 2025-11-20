<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2024-10-01 22:02:14
 * @LastEditors: iowen
 * @LastEditTime: 2024-11-02 23:45:25
 * @FilePath: /onenav/inc/widgets/w.about.author.php
 * @Description: 
 */

IOCF::createWidget('iow_about_author_min', array(
    'title'       => 'IO 关于作者',
    'classname'   => 'io-widget-about-author',
    'description' => '只显示在正文和作者页面',
    'fields'      => array(
        array(
            'id'      => 'show_posts',
            'type'    => 'switcher',
            'title'   => '显示文章',
            'default' => true,
            'class'   => 'compact min'
        ),
        array(
            'id'      => 'count',
            'type'    => 'number',
            'title'   => '数量',
            'unit'    => '条',
            'default' => 3,
            'class'   => 'compact min'
        ),
    )
));

function iow_about_author_min($args, $instance)
{
    if (!is_author() && !is_single()) {
        return;
    }

    global $wpdb, $post;

    $author_id  = $post->post_author;
    if(!$author_id){
        return;
    }
    $author_url = get_author_posts_url($author_id);

    $author_bg = get_lazy_img_bg(io_get_user_cover($author_id, "full"));
    $avatar    = get_avatar(get_the_author_meta('user_email',$author_id), '80');
    $caption   = io_get_user_cap_string($author_id);
    $name      = get_the_author_meta('nickname',$author_id);
    $badge     = io_get_user_badges($author_id, true);

    $posts = '';
    if ($instance['show_posts']) {
        $query = new WP_Query(array(
            'author'              => $author_id,
            'posts_per_page'      => $instance['count'],
            'post_status'         => 'publish',
            'ignore_sticky_posts' => 1,
        ));
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $posts .= '<div class="text-sm min-posts-card line1 my-1"><a href="' . get_permalink() . '">' . get_the_title() . '</a></div>';
            }
            wp_reset_postdata();
        }
    }
    $posts = $posts? '<div class="author-post-list mt-3">' . $posts . '</div>' : '';

    echo $args['before_widget'];
    echo <<<HTML
    <div class="widget-author-cover br-top-inherit text-center">
        <div class="author-bg bg-image br-top-inherit" {$author_bg}></div>
        <div class="widget-author-avatar mt-n5">
            <a href="{$author_url}" class="avatar-img">{$avatar}</a>     
        </div>
    </div>
    <div class="widget-author-meta p-3">
        <div class="text-center mb-3">
            <div>{$name}</div>
            <div class="badge vc-purple btn-outline text-ss mt-2">{$caption}</div>
        </div>
        <div class="author-badge mt-2 d-flex justify-content-center">{$badge}</div>
        {$posts}
    </div>
HTML;
    echo $args['after_widget'];
}


