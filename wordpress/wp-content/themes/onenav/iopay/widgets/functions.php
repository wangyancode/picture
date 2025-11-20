<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2023-02-25 22:40:30
 * @LastEditors: iowen
 * @LastEditTime: 2023-03-11 17:12:48
 * @FilePath: \onenav\iopay\widgets\functions.php
 * @Description: 
 */

$functions = array(
    'w-auto-ad',
    'w-buy-sidebar',
);

foreach ($functions as $function) {
    $path = '/iopay/widgets/' . $function . '.php';
    require get_theme_file_path($path);
}
