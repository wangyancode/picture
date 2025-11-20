<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2021-08-20 10:24:32
 * @LastEditors: iowen
 * @LastEditTime: 2024-11-05 21:31:19
 * @FilePath: /onenav/inc/mailfunc/mailfunc.php
 * @Description: 
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }


$functions = array(
    'class.Async.Task',
    'class.Async.Email',
    'option',
    'templet',
    'notice',
    'email',
);

foreach ($functions as $function) {
    $path = 'inc/mailfunc/' . $function . '.php';
    require get_theme_file_path($path);
}

/* 实例化异步任务类实现注册异步任务钩子 */
new AsyncEmail();

/**
 * 获取所有管理员ID
 * @return array
 */
function io_get_admin_ids()
{
    $admin_ids = get_users(array(
        'role'   => 'administrator', // 指定角色为管理员
        'fields' => 'ID' // 仅获取用户ID
    ));
    return $admin_ids;
}
