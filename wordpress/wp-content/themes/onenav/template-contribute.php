<?php
/*
 * @Template Name: 投稿模板
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2021-06-03 08:56:37
 * @LastEditors: iowen
 * @LastEditTime: 2025-04-01 23:24:48
 * @FilePath: /onenav/template-contribute.php
 * @Description: 
 */

if (!io_get_option('is_contribute',true)) {
    set404();
}
// 添加webuploader.html5only.js
wp_enqueue_script('webuploader');

add_filter('io_show_sidebar', '__return_true');

$contribute_can  = io_get_option('contribute_can','user');
$contribute_type = io_get_contribute_allow_type();
if (empty($contribute_type)) {
    set404();
}
$type = isset($_GET['type']) ? $_GET['type'] : $contribute_type[0];

// 判断是否开启投稿类型
if (!in_array($type, $contribute_type) && !is_super_admin()) {
    set404();
}

if(in_array($type,['book','app'])){
    add_jquery_ui_js();
}

$edit_data     = io_get_contribute_edit_data($type);
$is_contribute = ('all' === $contribute_can || ('user' === $contribute_can && is_user_logged_in()) || ('admin' === $contribute_can && current_user_can('manage_options')));

// 投稿须知位置
$_tip_loc = get_post_meta(get_the_ID(), '_tip_loc', true) ?: 'after';
add_action('io_contribute_form_' . $_tip_loc, 'io_contribute_content_tip');

get_header();
?>
<main role="main" class="container new-post-content my-2"> 
    <?php
    echo io_get_contribute_header();

    /**
     * -----------------------------------------------------------------------
     * HOOK : ACTION HOOK
     * io_contribute_form_before
     * 
     * 投稿表单前添加钩子
     * @since  5.0
     * -----------------------------------------------------------------------
     */
    do_action('io_contribute_form_before');

    ?>
    <form class="post-tg tougao-form mb-4" method="post">
        <div class="content-wrap">
            <div class="content-layout">
                <?php
                if ($is_contribute) {
                    io_contribute_edit_content_form($type, $edit_data);
                }else{
                ?>
                <div class="panel panel-tougao card">
                    <div class="card-body"> 
                        <div class="container my-5 py-md-5 text-center">
                            <img src="<?php echo get_theme_file_uri('/assets/images/no.svg') ?>" width="300"/>
                            <?php if ('admin' === $contribute_can && is_user_logged_in() && !current_user_can('manage_options')) { ?>
                            <h3 class="text-sm text-muted mt-3"><i class="iconfont icon-crying mr-2"></i><?php _e('无权操作，请联系管理员！', 'i_theme') ?></h3>
                            <?php } else { ?>
                            <h3 class="text-sm text-muted mt-3"><i class="iconfont icon-crying mr-2"></i><?php _e('需要登录才能访问！', 'i_theme') ?></h3>
                            <?php } ?>
                        </div>
                    </div>
                </div>
                <?php
                }
                ?>
            </div>
        </div>
        <div class="sidebar show-sidebar contribute-sidebar">
            <?php 
            if ($is_contribute) {
                io_contribute_edit_sidebar_form($type, $edit_data);
            } else {
                echo '<div class="card pt-5 mt-4 mt-md-0">';
                echo io_get_menu_user_box('p-2');
                echo '</div>';
            }
            ?>
        </div>
    </form>
    <div class="clear mb-4"></div>
    <?php
    /**
     * -----------------------------------------------------------------------
     * HOOK : ACTION HOOK
     * io_contribute_form_after
     * 
     * 投稿表单后添加钩子
     * @since  5.0
     * -----------------------------------------------------------------------
     */
    do_action('io_contribute_form_after');
    ?>
</main>
<?php

/**
 * -----------------------------------------------------------------------
 * HOOK : ACTION HOOK
 * io_contribute_footer
 * 
 * 投稿页脚勾子。
 * @since  5.0
 * -----------------------------------------------------------------------
 */
do_action('io_contribute_footer');

// 加载 js 参数
add_filter('io_js_var', 'io_contribute_js_var', 10, 1);

get_footer();
