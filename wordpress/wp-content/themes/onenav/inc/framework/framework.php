<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2022-06-08 18:32:05
 * @LastEditors: iowen
 * @LastEditTime: 2025-03-27 12:55:59
 * @FilePath: /onenav/inc/framework/framework.php
 * @Description:  csf_ => iocf_   CSF_ => IOCF_  CSF => IOCF
 * Version: 2.0.3
 * Text Domain: csf
 * Domain Path: /languages
 */
if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access directly.
require_once plugin_dir_path( __FILE__ ) .'classes/setup.class.php';
require_once plugin_dir_path( __FILE__ ) .'customize/options-function.php';
require_once plugin_dir_path( __FILE__ ) .'customize/iosf.class.php';

/**
 * 获取选项值
 * @param mixed $option 选项名称
 * @param mixed $default 默认值
 * @param mixed $key 数字选项的键值
 * @return mixed
 */
function io_get_option($option, $default = null, $key = ''){
    static $options = null;
    if ($options === null) {
        $options = get_option('io_get_option');
    }
    
    $_v = $default;
    if (isset($options[$option])) {
        if ($key) {
            $_v = isset($options[$option][$key]) ? $options[$option][$key] : $default;
        } else {
            $_v = $options[$option];
        }
    }
    $_v = _iol($_v, $option, isset($options['m_language']) ? $options['m_language'] : false);
    return $_v;
}

/**
 * 多语言选项输出
 * 支持数组类型和字符串类型
 * 
 * zh=*=中文|*|en=*=English
 * 
 * @param mixed $value    选项内容
 * @param mixed $key      选项名称
 * @param mixed $is_multi 是否开启多语言
 * @return mixed
 */
function _iol($value, $key = '', $is_multi = null){
    if (empty($value)) {
        return $value;
    }

    if($is_multi === null){
        $is_multi = io_get_option('m_language', false);
    }

    $data = array();
    if ( $key != '' && strpos($key, 'multi') !== false) {
        // 多语言数组型值
        $content = $value[0]['content'];
        if (!$is_multi) {
            return $content;
        }

        foreach ($value as $v) {
            $data[$v['language']] = $v['content'];
        }
    } elseif (is_array($value)) {
        // 如果选项值是数组，则直接返回
        return $value;
    }

    if ( empty($data) && strpos($value, '|*|') !== false) {
        // 如果选项值是字符串，且包含|*|，则进行多语言处理
        // 准备多语言数据
        $_data = explode('|*|', $value);
        foreach ($_data as $v) {
            $d = explode('=*=', $v);
            $data[$d[0]] = $d[1];
        }
    }

    if (!empty($data)) {
        // 如果有多语言数据，则进行多语言处理
        $language = determine_locale();
        if (isset($data[$language])) {
            // 如果当前语言有对应的选项值，则直接返回
            $content = $data[$language];
        } else {
            // 如果当前语言没有对应的选项值，则使用第一个选项值
            $language = explode('_', $language)[0];
            $content  = isset($data[$language]) ? $data[$language] : reset($data);
        }
        return $content;
    }
    return $value;
}
