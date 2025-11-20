<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2024-11-05 16:40:04
 * @LastEditors: iowen
 * @LastEditTime: 2024-11-05 21:58:14
 * @FilePath: /onenav/inc/mailfunc/notice.php
 * @Description: 通知
 */

/**
 * 获取通知类型
 * @param mixed $type
 * @return bool|string|string[]
 */
function io_get_notofy_type($type = '')
{
    $data = array(
        'notification' => __('通知', 'i_theme'),
        'comment'      => __('评论', 'i_theme'),
        'star'         => __('收藏', 'i_theme'),
        'credit'       => __('积分', 'i_theme'),
        'cash'         => __('货币', 'i_theme'),
        'msg'          => __('私信', 'i_theme'),
    );
    if ($type) {
        if(isset($data[$type])){
            return $data[$type];
        }else{
            return false;
        }
    }
    return $data;
}

/**
 * 创建消息.
 *
 * @param int    $to_id     接收用户ID
 * @param int    $sender_id 发送者ID(可空)
 * @param string $sender    发送者名称
 * @param string $type      消息类型 cash|货币 comment|评论 credit|积分 notification|通知 star|收藏
 * @param string $title     消息标题
 * @param string $content   消息内容
 * @param array  $meta      消息元数据 [post_id=>文章ID,order_id=>订单ID]
 * @param string $date      消息时间
 * @return bool
 */
function io_add_notify($to_id = 0, $sender_id = 0, $sender = '', $type = '', $title = '', $content = '', $meta = [], $date = '')
{
    $to_id   = absint($to_id);
    $sender_id = absint($sender_id);
    $title     = sanitize_text_field($title);

    if (!$to_id || empty($title)) {
        return false;
    }

    $type = $type ? sanitize_text_field($type) : 'notification';
    $date = $date ?: current_time('Y-m-d H:i:s');

    $content = htmlspecialchars($content);

    global $iodb;

    if ($iodb->addMessages($to_id, $type, $title, $date, $content, $sender_id, $sender, $meta)) {
        return true;
    }

    return false;
}
/**
 * 评论通知
 * 
 * @param mixed $to_id
 * @param mixed $sender_id
 * @param mixed $sender
 * @param mixed $title
 * @param mixed $content
 * @param mixed $meta
 * @return bool
 */
function io_comment_notify($to_id, $sender_id = 0, $sender = '', $title = '', $content = '', $meta = [])
{
    return io_add_notify($to_id, $sender_id, $sender, 'comment', $title, $content, $meta);
}

/**
 * 系统通知
 * 
 * @param int $to_id
 * @param string $title
 * @param string $content
 * @param array $meta
 * @return bool
 */
function io_system_notify($to_id, $title = '', $content = '', $meta = [])
{
    return io_add_notify($to_id, 0, 'System', 'notification', $title, $content, $meta);
}





/**
 * 用户注册成功后通知
 * 
 * @param int $user_id
 * @return void
 */
function io_register_msg($user_id)
{
    io_system_notify($user_id, sprintf(__('欢迎来到%s，请首先在个人设置中完善您的账号信息。', 'i_theme'), get_bloginfo('name')));
}
add_action('user_register','io_register_msg'); 

/**
 * 给管理员发送通知
 * @param mixed $title
 * @param mixed $content
 * @param mixed $meta
 * @return void
 */
function io_admin_notify_msg($title, $content, $meta = [])
{
    $admin_ids = io_get_admin_ids();
    foreach ($admin_ids as $admin_id) {
        io_system_notify($admin_id, $title, $content, $meta);
    }
}

/**
 * 通知用户
 * 邮件 短信 或者站内信等
 * 
 * @param mixed $type 
 * @param mixed $to
 * @param mixed $msg
 * @return void
 */
function io_notify_user($type, $to = '', $msg = ''){

}
