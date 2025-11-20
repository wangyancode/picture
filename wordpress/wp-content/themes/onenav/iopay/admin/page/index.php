<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2023-03-12 14:21:17
 * @LastEditors: iowen
 * @LastEditTime: 2023-03-31 23:23:04
 * @FilePath: \onenav\iopay\admin\page\index.php
 * @Description: 
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

$user = wp_get_current_user();
if (!is_user_logged_in()) {
    exit;
}  

$post_title      = '商城数据'; 

$this_month_time = current_time('Y-m');
$today           = io_get_order_stats_by_time('today');
$yester          = io_get_order_stats_by_time('yester');
$this_month      = io_get_order_stats_by_time('this_month');
$last_month      = io_get_order_stats_by_time('last_month');
$all             = io_get_order_stats_by_time('all');
$this_year       = io_get_order_stats_by_time('this_year');

$data = array(
    array(
        'top'    => '今日订单',
        'val'    => $today['count'],
        'bottom' => '昨日订单：' . $yester['count'],
    ),
    array(
        'top'    => '今日收款',
        'val'    => ($today['sum'] > 1000) ? (int)$today['sum'] : $today['sum'],
        'bottom' => '昨日收款：' . $yester['sum'],
    ),
    array(
        'top'    => '本月订单',
        'val'    => $this_month['count'],
        'bottom' => '上月订单：' . $last_month['count'],
    ),
    array(
        'top'    => '本月收款',
        'val'    => ($this_month['sum'] > 10000) ? (int) $this_month['sum']: $this_month['sum'],
        'bottom' => '上月收款：' . $last_month['sum'],
    ),
    array(
        'top'    => '总有效订单',
        'val'    => $all['count'],
        'bottom' => '今年订单：' . $this_year['count'],
    ),
    array(
        'top'    => '总有效收款',
        'val'    => ($all['sum'] > 10000) ? (int) $all['sum'] : $all['sum'],
        'bottom' => '今年收款：' . $this_year['sum'],
    ),
);

$card = '';
foreach ($data as $v) {
    $card .= '<div class="row-3">
            <div class="pay-body">
                <span class="count-top">' . $v['top'] . '</span>
                <div class="count">' . $v['val'] . '</div>
                <span class="count-bottom">' . $v['bottom'] . '</span>
            </div>
        </div>';
}

?>
<div class="wrap">
    <h1 class="wp-heading-inline"><?php echo esc_html( $post_title  ); ?></h1>
    <p>商城设置：<a href="<?php echo io_get_admin_iocf_url('商城设置') ?>">立即前往</a></p>
    <hr class="wp-header-end">
    <div class="pay-container">
        <?php echo $card ?> 
    </div>
    <div class="clear"></div>
</div>
