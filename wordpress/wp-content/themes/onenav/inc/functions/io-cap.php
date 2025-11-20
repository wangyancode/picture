<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2024-10-28 10:03:02
 * @LastEditors: iowen
 * @LastEditTime: 2025-06-05 15:51:00
 * @FilePath: /onenav/inc/functions/io-cap.php
 * @Description: 
 */

/**
 * 判断当前用户是否拥有某个权限
 * 
 * 如果当前用户是超级管理员，直接返回true
 * @param mixed $capability 自定义权限名称
 * @param array $args 参数  $args[0] 文章ID 或者 文章对象
 * @return mixed
 */
function io_current_user_can($capability, ...$args)
{
    return io_user_can(get_current_user_id(), $capability, ...$args);
}

/**
 * 判断用户是否拥有某个权限
 * 
 * @param mixed $user_id 用户ID
 * @param mixed $capability 权限名称
 * @param array $args 参数 $args[0] 文章ID
 * @return mixed
 */
function io_user_can($user_id, $capability, ...$args)
{
    $is_can = false;
    if (is_super_admin($user_id)) {
        $is_can = true;
    }

    if (!$is_can) {
        $cap_roles = io_get_cap_roles($capability);
        switch ($capability) {
            case 'new_post_delete':
            case 'new_post_edit':
                if (!empty($args[0])) {
                    if (!is_object($args[0])) {
                        $post = get_post($args[0]);
                    } else {
                        $post = $args[0];
                    }

                    //自己必须是本人
                    if (isset($post->post_author) && $post->post_author == $user_id) {
                        if ('draft' === $post->post_status) {
                            //草稿允许直接删除或编辑
                            $is_can = true;
                        } else {
                            $is_can = io_is_can_roles($user_id, $cap_roles);
                        }
                    }
                }
                break;


            default:
                //默认执行参数
                $is_can = io_is_can_roles($user_id, $cap_roles);
                break;
        }
    }

    return apply_filters('zib_user_can', $is_can, $user_id, $capability, $args);
}

/**
 * 判断用户是否是对应的角色，用于zib_user_can的基本判断
 * @param mixed $user_id 用户ID
 * @param mixed $cap_roles 角色能力数组
 * @return bool
 */
function io_is_can_roles($user_id, $cap_roles = array())
{
    if (!empty($cap_roles['all'])) {
        //如果该能力包含all或者logged，这直接return true;
        return true;
    }
    if (!empty($cap_roles['logged']) && $user_id && get_current_user_id() == $user_id) {
        //如果该能力包含all或者logged，这直接return true;
        return true;
    }

    //添加判断挂钩
    if (apply_filters('is_can_roles', false, $user_id, $cap_roles)) {
        return true;
    }
    return false;
}

/**
 * 获取自定义的角色能力数组
 * 
 * TODO: 需构建完整的角色能力编辑界面
 * @param mixed $capability 能力名称
 * @param array $default 默认值
 * @return array|mixed
 */
function io_get_cap_roles($capability, $default = array())
{
    global $all_cap_roles;
    if (!isset($all_cap_roles)) {
        $all_cap_roles = ['new_post_edit'=> ['logged' => true]]; // 默认能力，登陆用户可以编辑自己的文章
    }

    if (isset($all_cap_roles[$capability])) {
        return $all_cap_roles[$capability];
    } else {
        return $default;
    }
}

/**
 * 为用户添加权限
 * @return void
 */
function io_user_add_cap() {
    foreach (array('subscriber', 'editor', 'author', 'contributor','upload_files') as $user_role) {
        $role = get_role($user_role);
        if(is_object($role)){
            //$role->add_cap('edit_posts');
            //$role->add_cap('edit_published_posts');
            $role->remove_cap('edit_posts');
            $role->remove_cap('edit_published_posts');
        }
    }
}
//add_action('init', 'io_user_add_cap');
/**
 * 为用户添加编辑其他用户文章的权限
 * 
 * 
 * @param mixed $allcaps
 * @param mixed $caps
 * @return void
 */
function io_user_has_cap($allcaps, $caps)
{
    if (!empty($allcaps['edit_posts'])) {
        return $allcaps;
    }

    $user_id = get_current_user_id();
    if(!$user_id){
        return $allcaps;
    }
    if ( in_array('edit_posts', $caps)) {
        global $wp_query;
        if ($wp_query->is_single() && isset($wp_query->posts[0]->post_author) && $wp_query->posts[0]->post_author == $user_id) {
            $allcaps['edit_posts'] = true;
        }
    }

    // 查看其他用户文章
    //if ( in_array('edit_others_posts', $caps)) {
    //}

    return $allcaps;
}
add_filter('user_has_cap','io_user_has_cap', 11, 2);
