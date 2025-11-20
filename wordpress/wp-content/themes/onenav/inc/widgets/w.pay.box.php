<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2023-03-11 21:58:46
 * @LastEditors: iowen
 * @LastEditTime: 2024-09-13 12:12:49
 * @FilePath: /onenav/inc/widgets/w.pay.box.php
 * @Description: 
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

IOCF::createWidget('iow_pay_box_min', array(
    'title'       => 'IO 付费购买',
    'classname'   => 'io-widget-pay-box',
    'description' => '显示当前文章的付费内容，置于内容页。不支持 App 和 书籍',
    'fields'      => array(
        array(
            'type'    => 'submessage',
            'style'   => 'success',
            'content' => '无需设置，直接使用即可，如果文章有付费内容，将自动显示。',
        ),
    )
));

function iow_pay_box_min($args, $instance) {
    if (is_single()) {
        echo iopay_buy_sidebar_widgets('', $args, $instance);
    }
}

