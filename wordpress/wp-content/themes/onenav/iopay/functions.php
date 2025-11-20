<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2023-02-25 22:40:30
 * @LastEditors: iowen
 * @LastEditTime: 2024-05-08 21:01:14
 * @FilePath: /onenav/iopay/functions.php
 * @Description: 
 */

require get_theme_file_path('iopay/functions/functions.php');
require get_theme_file_path('iopay/action/ajax.php');
require get_theme_file_path('iopay/admin/admin.php');
require get_theme_file_path('iopay/widgets/functions.php');


function io_add_pay_db(){
    global $wpdb;
    if (!io_is_table($wpdb->iopayorder)) {
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $wpdb->iopayorder (
            `id` bigint(20) NOT NULL AUTO_INCREMENT,
            `user_id` bigint(20) DEFAULT NULL COMMENT '下单用户id',
            `post_id` bigint(20) DEFAULT NULL COMMENT '文章id',
            `merch_index` int(11) DEFAULT 0 COMMENT '商品序号',
            `post_user_id` bigint(20) DEFAULT NULL COMMENT '文章作者id',
            `create_time` datetime DEFAULT NULL COMMENT '创建时间',
            `order_num` varchar(50) DEFAULT NULL COMMENT '订单号',
            `order_price` double(10,2) DEFAULT 0 COMMENT '订单价格',
            `order_type` varchar(50) DEFAULT NULL COMMENT '订单类型',
            `order_meta` longtext DEFAULT NULL COMMENT '订单详情',
            `ip_address` varchar(50) DEFAULT NULL COMMENT 'ip地址',
            `pay_num` varchar(50) DEFAULT NULL COMMENT '支付订单号',
            `pay_type` varchar(50) DEFAULT NULL COMMENT '支付类型',
            `pay_price` double(10,2) DEFAULT NULL COMMENT '支付金额',
            `pay_meta` longtext DEFAULT NULL COMMENT '支付详情',
            `pay_time` datetime DEFAULT NULL COMMENT '支付时间',
            `status` int(11) DEFAULT 0 COMMENT '订单状态',
            `other` longtext DEFAULT NULL COMMENT '其它',
            PRIMARY KEY (`id`),
            KEY `user_id` (`user_id`),
            KEY `post_id` (`post_id`),
            KEY `post_user_id` (`post_user_id`),
            KEY `status` (`status`)
        )$charset_collate;";
        $wpdb->query($sql);
    }
    if(!io_is_table($wpdb->ioautoad)){
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $wpdb->ioautoad (
            `id` bigint(20) NOT NULL AUTO_INCREMENT,
            `user_id` bigint(20) DEFAULT 0 COMMENT '用户id',
            `url` text DEFAULT NULL COMMENT 'url',
            `name` varchar(200) DEFAULT NULL COMMENT '名称',
            `icon` text DEFAULT NULL COMMENT '图标',
            `color` varchar(50) DEFAULT NULL COMMENT '颜色',
            `contact` varchar(50) DEFAULT NULL COMMENT '联系方式',
            `nofollow` int(11) DEFAULT 0 COMMENT '',
            `loc` varchar(50) DEFAULT NULL COMMENT '位置',
            `token` varchar(50) DEFAULT NULL COMMENT '',
            `time` datetime DEFAULT NULL COMMENT '添加时间',
            `pay_time` datetime DEFAULT NULL COMMENT '支付时间',
            `limit` varchar(50) DEFAULT NULL COMMENT '限制',
            `expiry` datetime DEFAULT NULL COMMENT '过期时间',
            `order_num` varchar(50) DEFAULT NULL COMMENT '支付订单',
            `order` int(11) DEFAULT 0 COMMENT '排序',
            `check` int(11) DEFAULT 0 COMMENT '0待审核 1审核通过 2拒绝',
            `status` int(11) DEFAULT 0 COMMENT '支付状态',
            PRIMARY KEY (`id`),
            KEY `expiry` (`expiry`),
            KEY `check` (`check`),
            KEY `status` (`status`)
        )$charset_collate;";
        $wpdb->query($sql);

        $old_data = maybe_unserialize(get_option('auto_ad_url_list', array()));
        if($old_data){
            $_order = 1;
            foreach($old_data as $data){
                $data['order'] = $_order;
                $wpdb->insert($wpdb->ioautoad, $data);
                $_order++;
            }
        }
        
    }
}
add_action('admin_menu', 'io_add_pay_db', 1);
