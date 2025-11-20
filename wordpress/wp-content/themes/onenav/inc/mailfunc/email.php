<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2024-11-05 17:24:23
 * @LastEditors: iowen
 * @LastEditTime: 2024-11-05 23:14:41
 * @FilePath: /onenav/inc/mailfunc/email.php
 * @Description: 自动通知邮件
 */

/**
 * 评论回复邮件通知功能
 *
 * 此功能将发送邮件通知给相关用户和管理员，以告知有新的评论或回复。
 *
 * @since 2.0.0
 * @param int $comment_id 评论的ID
 * @param WP_Comment $comment_object 评论对象
 * @return void
 */
function io_comment_mail_notify($comment_id, $comment_object)
{
    $admin_notify         = 1; // 用户正常评论是否通知管理员（1表示通知；0表示不通知）
    $notify               = 1; // 用户评论回复是否通知作者（1表示通知；0表示不通知）
    $admin_email          = get_bloginfo('admin_email'); // 管理员邮箱，可更改为指定邮箱
    $comment              = get_comment($comment_id); // 获取当前评论信息
    $comment_author       = trim($comment->comment_author); // 评论者姓名
    $comment_date         = trim($comment->comment_date); // 评论时间
    $comment_link         = htmlspecialchars(get_comment_link($comment_id)); // 评论链接
    $comment_content      = nl2br($comment->comment_content); // 评论内容，带换行
    $comment_author_email = trim($comment->comment_author_email); // 评论者邮箱
    $parent_id            = $comment->comment_parent ?: ''; // 获取父评论ID（如果有）
    $parent_comment       = !empty($parent_id) ? get_comment($parent_id) : null; // 获取父评论信息
    $parent_email         = $parent_comment ? trim($parent_comment->comment_author_email) : ''; // 父评论者邮箱
    $post                 = get_post($comment_object->comment_post_ID); // 获取评论所在文章信息
    $post_author_email    = get_user_by('id', $post->post_author)->user_email; // 获取文章作者邮箱

    // 如果评论尚未审核，通过管理员邮箱发送审核通知
    if ($comment_object->comment_approved != 1) {
        $args = array(
            'postTitle'      => $post->post_title,
            'commentAuthor'  => $comment_author,
            'commentContent' => $comment_content,
            'commentLink'    => $comment_link,
        );
        io_async_mail($admin_email, sprintf(__('%s上的文章有了新的回复', 'i_theme'), get_bloginfo('name')), io_template_comment_admin($args));
        return;
    }

    $spam_confirmed = $comment->comment_approved; // 评论状态

    // 如果存在父评论且评论非垃圾评论，通知父评论的作者
    if ($parent_id && $spam_confirmed != 'spam' && $notify && $parent_email != $comment_author_email) {
        $parent_author          = trim($parent_comment->comment_author); // 父评论作者姓名
        $parent_comment_date    = trim($parent_comment->comment_date); // 父评论时间
        $parent_comment_content = nl2br($parent_comment->comment_content); // 父评论内容，带换行
        $args                   = array(
            'parentAuthor'         => $parent_author,
            'parentCommentDate'    => $parent_comment_date,
            'parentCommentContent' => $parent_comment_content,
            'postTitle'            => $post->post_title,
            'commentAuthor'        => $comment_author,
            'commentDate'          => $comment_date,
            'commentContent'       => $comment_content,
            'commentLink'          => $comment_link
        );

        $msg_title = sprintf(__('%s在「%s」中回复了你', 'i_theme'), $comment_object->comment_author, $post->post_title);
        // 如果父评论者邮箱有效，则发送邮件通知
        if (filter_var($parent_email, FILTER_VALIDATE_EMAIL)) {
            io_async_mail($parent_email, $msg_title, io_template_reply($args));
        }

        if ($parent_comment->user_id) {
            io_comment_notify($parent_comment->user_id, $comment->user_id, $comment_author, $msg_title, $comment_content, ['post_id' => $post->ID]);
        }
    }

    // 通知文章作者（确保作者与评论者不同）
    if ($post_author_email != $comment_author_email && $post_author_email != $parent_email) {
        $args      = array(
            'postTitle'      => $post->post_title,
            'commentAuthor'  => $comment_author,
            'commentContent' => $comment_content,
            'commentLink'    => $comment_link
        );
        $msg_title = sprintf(__('%s在你的文章「%s」中发表了评论', 'i_theme'), $comment_author, $post->post_title);
        // 验证邮箱有效性后发送邮件
        if (filter_var($post_author_email, FILTER_VALIDATE_EMAIL)) {
            io_async_mail($post_author_email, $msg_title, io_template_comment($args));
        }

        io_system_notify($post->post_author, $msg_title, $comment_content, ['post_id' => $post->ID]);
    }

    // 通知管理员
    if ($post_author_email != $admin_email && $parent_id && $admin_notify) {
        $args = array(
            'postTitle'      => $post->post_title,
            'commentAuthor'  => $comment_author,
            'commentContent' => $comment_content,
            'commentLink'    => $comment_link,
            'verify'         => '0' // 标记为未审核
        );
        io_async_mail($admin_email, sprintf(__('%s上的文章有了新的回复', 'i_theme'), get_bloginfo('name')), io_template_comment_admin($args));

        // 给管理员发送消息
        $msg_title = sprintf(__('文章「%s」中有新评论', 'i_theme'), $comment_author, $post->post_title);
        io_admin_notify_msg($msg_title, $comment_content, ['post_id' => $post->ID]);
    }
}
add_action('wp_insert_comment', 'io_comment_mail_notify', 99, 2);



/**
 * WP登录提醒
 *
 * @since 2.0.0
 * @param string $user_login
 * @return void
 */
function io_wp_login_notify($user_login){ 
    $admin_email = get_bloginfo ('admin_email');
    $subject = __('你的博客空间登录提醒', 'i_theme');
    $args = array(
        'loginName' => $user_login,
        'ip' => IOTOOLS::get_ip()
    );
    io_async_mail( $admin_email, $subject, io_template_login($args));
}
//add_action('wp_login', 'io_wp_login_notify', 10, 1);

/**
 * WP登录错误提醒
 *
 * @since 2.0.0
 * @param string $login_name
 * @return void
 */
function io_wp_login_failure_notify($login_name){
    $admin_email = get_bloginfo ('admin_email');
    $subject = __('你的博客空间登录错误警告', 'i_theme');
    $args = array(
        'loginName' => $login_name,
        'ip' => IOTOOLS::get_ip()
    );
    io_async_mail( $admin_email, $subject, io_template_login_fail($args));
}
//add_action('wp_login_failed', 'io_wp_login_failure_notify', 10, 1);


/**
 * 更改找回密码邮件中的内容
 *
 * @since 2.0.0
 * @param $message
 * @param $key
 */
function io_reset_password_message($message, $key, $user_login, $user_data)
{
    if (!is_admin())
        io_ajax_is_robots();

    if (!$user_data) {
        if (strpos($_POST['user_login'], '@')) {
            $user_data = get_user_by('email', trim($_POST['user_login']));
        } else {
            $login     = trim($_POST['user_login']);
            $user_data = get_user_by('login', $login);
        }
    }
    $user_login = $user_data->user_login;
    $user_email = $user_data->user_email;
    $reset_link = network_site_url('wp-login.php?action=rp&key=' . $key . '&login=' . rawurlencode($user_login), 'login');

    $args = array('home' => home_url(), 'userLogin' => $user_login, 'resetPassLink' => $reset_link);

    io_mail($user_email, sprintf(__('你的登录密码重置链接-%1$s', 'i_theme'), get_bloginfo('name')), io_template_findpass($args));
}
add_filter('retrieve_password_message', 'io_reset_password_message', 10, 4);

/**
 * 用户提交链接向管理员发送邮件 
 */
function io_add_links_submit_email_to_admin($data)
{
    $args = array(
        'link_name'        => esc_attr($data['link_name']),
        'link_url'         => esc_url($data['link_url']),
        'link_description' => !empty($data['link_description']) ? esc_attr($data['link_description']) : '无',
        'link_image'       => !empty($data['link_image']) ? esc_attr($data['link_image']) : '空',
        'link_admin'       => admin_url('link-manager.php?orderby=visible&order=asc'),
    );
    io_async_mail(get_option('admin_email'), sprintf(__('[%s]新的友情链接待审核', 'i_theme'), get_bloginfo('name')), io_template_add_links($args));

    io_admin_notify_msg(__('新的友情链接待审核', 'i_theme'), $data['link_name']);
}
add_action('io_ajax_add_links_submit_success', 'io_add_links_submit_email_to_admin', 99);


/**
 * 用户绑定手机号或者邮件成功后通知
 * 
 * @param mixed $user_id
 * @param mixed $type
 * @param mixed $new_to
 * @param mixed $old_to
 * @return void
 */
function io_user_bind_new_email_or_phone_notice($user_id, $type, $new_to, $old_to)
{
    $user = get_userdata($user_id);

    $blog_name = get_bloginfo('name');
    $new_to    = io_get_hide_info($new_to, $type);
    $old_to    = $old_to ? io_get_hide_info($old_to, $type) : false;

    if ('email' === $type) {
        $title       = $old_to ? __('邮箱修改成功', 'i_theme') : __('邮箱绑定成功', 'i_theme');
        $info_text   = $old_to ? __('您的账号绑定的邮箱已修改', 'i_theme') : __('您的账号已成功绑定邮箱', 'i_theme');
        $action_text = $old_to ? sprintf(__('由 %s 修改为 %s', 'i_theme'), $old_to, $new_to) : __('邮箱：', 'i_theme') . $new_to;
    } else {
        $title       = $old_to ? __('手机号修改成功', 'i_theme') : __('手机号绑定成功', 'i_theme');
        $info_text   = $old_to ? __('您的账号绑定的手机号已修改', 'i_theme') : __('您的账号已成功绑定手机号', 'i_theme');
        $action_text = $old_to ? sprintf(__('由 %s 修改为 %s', 'i_theme'), $old_to, $new_to) : __('手机号：', 'i_theme') . $new_to;
    }
    $message = __('您好，', 'i_theme') . $user->display_name . '!<br>';
    $message .= $info_text . '<br>';
    $message .= $action_text . '<br><br>';
    $message .= __('如非您本人操作，请及时与客服联系！', 'i_theme');

    io_mail($user->user_email, '[' . $blog_name . ']' . $title, $message);

    io_system_notify($user_id, $title, $info_text . ',' . $action_text);
}
add_action('io_user_bind_new_email_or_phone', 'io_user_bind_new_email_or_phone_notice', 99, 4);

/**
 * 评论通过 通知评论者
 * 
 * @param WP_Comment $comment
 * @return void
 */
function io_comment_approved($comment)
{
    if (is_email($comment->comment_author_email)) {
        $post_link = get_permalink($comment->comment_post_ID);
        // 邮件标题，可自行更改
        $title                = sprintf(__('您在 [%s] 的评论已通过审核', 'i_theme'), get_option('blogname'));
        $comment_author_email = trim($comment->comment_author_email);
        $post                 = get_post($comment->comment_post_ID);
        $args                 = array(
            'parentAuthor'         => $comment->comment_author,
            'parentCommentDate'    => $comment->comment_date,
            'parentCommentContent' => $comment->comment_content,
            'postTitle'            => $post->post_title,
            'commentLink'          => get_comment_link($comment->comment_ID)
        );
        if (is_email($comment_author_email)) {
            io_async_mail($comment_author_email, $title, io_template_comment_pass($args));
        }
        $user_id = $comment->user_id;
        if($user_id){
            io_system_notify($user_id, $title, $comment->comment_content, ['post_id' => $post->ID]);
        }
    }
    $post_id = $comment->comment_post_ID;
    io_add_post_view($post_id, 1, 'comment');
}
add_action('comment_unapproved_to_approved', 'io_comment_approved');

/**
 * 用户账户被删除通知用户
 * @param mixed $user_id
 * @return void
 */
function iwilling_delete_user( $user_id ) {
    global $wpdb;
    $site_name = get_bloginfo('name');
    $user_obj = get_userdata( $user_id );
    $email = $user_obj->user_email;
    $subject = "帐号删除提示：".$site_name."";
    $message = '您在' .$site_name. '的账户已被管理员删除！'.'<p style="color: #6e6e6e;font-size:13px;line-height:24px;">如果您对本次操作有什么异议，请联系管理员反馈！<br>我们会在第一时间处理您反馈的问题.</p>';
    io_mail( $email, $subject, $message);
}
//add_action( 'delete_user', 'iwilling_delete_user' );


/**
 * 投稿文章邮件通知管理员审核.
 * @param $post
 */
function io_pending_notice_publish($post)
{
    $user_id = $post->post_author;
    if ($user_id) {
        $u_data = get_userdata($user_id);
        /**判断是否是管理员或者作者 */
        if (in_array('administrator', $u_data->roles)) {
            return false;
        }
    }
    $post_type = $post->post_type;

    $admin_email = get_bloginfo('admin_email');
    $subject     = sprintf(__('[%s]上有新的待审投稿', 'i_theme'), get_bloginfo('name'));

    if ('post' === $post_type) {
        $summary = io_strimwidth($post->post_content, 150, '...');
    } else {
        $summary = io_get_excerpt(150, '_sites_sescribe', '...', $post);
    }
    $args = array(
        'postTitle' => $post->post_title,
        'summary'   => $summary,
        'link'      => admin_url("/edit.php?post_status=pending&post_type=$post_type"),
        'time'      => get_the_time('Y-m-d H:i:s', $post)
    );

    //发送邮件
    if (is_email($admin_email)) {
        io_async_mail($admin_email, $subject, io_template_contribute_post($args));
    }

    // 给管理员发送消息,获取管理员ID
    io_admin_notify_msg(__('有新的待审投稿，请及时处理', 'i_theme'), $post->post_title, ['post_id' => $post->ID, 'type' => 'contribute']);
}
add_action('io_contribute_to_publish', 'io_pending_notice_publish', 10, 1);


/**
 * 投稿文章通过审核后通知作者
 * 
 * @param string $new_status
 * @param string $old_status
 * @param WP_Post $post
 * @return void
 */
function io_contribute_post_approved_notice_author($new_status, $old_status, $post)
{
    $supported_post_types = ['post', 'app', 'sites', 'book'];

    if ('publish' === $new_status && 'pending' === $old_status && in_array($post->post_type, $supported_post_types)) {

        // 获取文章作者的用户信息
        $author_id    = $post->post_author;
        $author       = get_userdata($author_id);
        $author_email = '';
        // 构建邮件内容
        $subject = sprintf(__('您在 [%s] 的投稿已通过审核', 'i_theme'), get_bloginfo('name'));
        $data = array(
            'author_name' => $author->display_name,
            'post_title'  => get_the_title($post->ID),
            'post_link'   => get_permalink($post->ID),
            'time'        => get_the_time('Y-m-d H:i:s', $post)
        );

        // 判断是否是管理员
        if (in_array('administrator', $author->roles)) {
            $guest_info = (array) get_post_meta($post->ID, 'guest_info', true);
            if(isset($guest_info['contact'])){
                $author_email = $guest_info['contact'];
            }
        } else {
            $author_email = $author->user_email;

            io_system_notify($author_id, __('您的投稿已通过审核', 'i_theme'), $data['post_title'], ['post_id' => $post->ID, 'type' => 'contribute']);
        }

        // 发送邮件通知作者
        if(is_email($author_email)){
            io_async_mail($author_email, $subject, io_template_contribute_approved_notice_author($data));
        }
    }
}
add_action('transition_post_status', 'io_contribute_post_approved_notice_author', 10, 3);

/**
 * 投稿文章重新编辑后通知审核
 * 
 * @param mixed $post_id
 * @param mixed $new_status
 * @param mixed $old_status
 * @return void
 */
function io_contribute_post_edit_notice_admin($post_id, $new_status, $old_status)
{
    if ('pending' === $new_status && 'publish' === $old_status) {
        $post = get_post($post_id);
        io_pending_notice_publish($post);
    }
    if ('publish' === $new_status && 'publish' === $old_status && io_get_option('again_notice_admin', true)) {
        $post = get_post($post_id);
        $author = get_userdata($post->post_author);

        $subject = sprintf(__('[%s]上用户 %s 的投稿有更新', 'i_theme'), get_bloginfo('name'), $author->display_name);
        $data = array(
            'author_name' => $author->display_name,
            'post_title'  => get_the_title($post->ID),
            'post_link'   => get_permalink($post->ID),
            'time'        => current_time('Y-m-d H:i:s')
        );

        io_mail_to_admin($subject, io_template_contribute_edit_notice_admin($data));
        // 通知管理员文章有更新
        io_admin_notify_msg(sprintf(__('用户 %s 的投稿有更新', 'i_theme'), $author->display_name), $post->post_title, ['post_id' => $post_id, 'type' => 'update']);
    }
}
add_action('io_new_edit_posts', 'io_contribute_post_edit_notice_admin', 10, 3);
