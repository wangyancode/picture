<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2023-03-25 01:30:36
 * @LastEditors: iowen
 * @LastEditTime: 2023-06-01 10:49:11
 * @FilePath: \onenav\iopay\admin\page\order.php
 * @Description: 
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }
global $post_type, $invalid_items, $post_args;

$user = wp_get_current_user();
if (!is_user_logged_in()) {
    exit;
}  

$post_title = '订单列表'; 

$failure_n   = 0;
$total_items = get_invalid_count( $failure_n );
$per_page    = 20;


$pagenum     = get_pagenum();

//表头
$column_info = array(
    array(
        'cb'        => '<input type="checkbox" />',
        'order_num' => '订单号',
        'goods'     => '商品',
        'meta'      => '详细',
        'user'      => '用户',
        'price'     => '订单价格',
        'time'      => '时间',
        'pay_num'   => '支付号',
        'status'    => '支付状态'
    ),
    array(),
    array(
        'status' => array('status', 'asc'),
        'goods'  => array('post_id', 'asc'),
        'user'   => array('user_id', 'asc'),
    ),
    'order_num'
);

//数据
$s     = !empty($_REQUEST['s']) ? $_REQUEST['s'] : false;
$where = '';

if ($s) {
    $where = "WHERE
    `pay_num` LIKE '%$s%' OR
    `order_num` LIKE '%$s%' OR
    `other` LIKE '%$s%' OR
    `user_id` LIKE '%$s%' OR
    `post_id` LIKE '%$s%'";
}

if (isset($_GET['status'])) {
    $where_status = (int) $_GET['status'];
    $where        = "WHERE
    `status` = $where_status";
}
$where_order_type = !empty($_GET['order_status']) ? $_GET['order_status'] : false;
if ($where_order_type) {
    switch ($where_order_type) {
        case 'unpaid':
            $where = "WHERE `status` = 0";
            break;

        case 'auto_ad_url':
        case 'view':
        case 'part':
        case 'annex':
            $where = "WHERE `order_type` = '$where_order_type'";
            break;
        
        default:
            break;
    }
}

if (isset($_GET['user_id'])) {
    $user_id = (int) $_GET['user_id'];
    $where = $where ?: 'WHERE 1=1';
    $where .= " AND `user_id` = $user_id";
}

if (isset($_GET['post_id'])) {
    $post_id = (int) $_GET['post_id'];
    $where = $where ?: 'WHERE 1=1';
    $where .= " AND `post_id` = $post_id";
}

global $wpdb;
//统计数据
$_data = array(
    'current_data'      => $wpdb->get_var("SELECT COUNT(id) FROM $wpdb->iopayorder $where"),
    'all_data'          => $wpdb->get_var("SELECT COUNT(id) FROM $wpdb->iopayorder WHERE 1=1"),
    'unpaid_data'       => $wpdb->get_var("SELECT COUNT(id) FROM $wpdb->iopayorder WHERE `status` = 0"),
    'auto_ad_url_data'  => $wpdb->get_var("SELECT COUNT(id) FROM $wpdb->iopayorder WHERE `order_type` = 'auto_ad_url'"),
    'view_data'         => $wpdb->get_var("SELECT COUNT(id) FROM $wpdb->iopayorder WHERE `order_type` = 'view'"),
    'part_data'         => $wpdb->get_var("SELECT COUNT(id) FROM $wpdb->iopayorder WHERE `order_type` = 'part'"),
    'annex_data'        => $wpdb->get_var("SELECT COUNT(id) FROM $wpdb->iopayorder WHERE `order_type` = 'annex'"),
);

get_pagination_args(
    array(
        'total_items' => $_data['current_data'],
        'per_page'    => $per_page,
    )
);

//分页计算
$pages   = ceil($_data['current_data'] / $per_page);
$offset  = $per_page * ($pagenum - 1);
$orderby = !empty($_GET['orderby']) ? $_GET['orderby'] : 'pay_time';
$order   = !empty($_GET['order']) ? $_GET['order'] : 'DESC';

$list_data = $wpdb->get_results("SELECT * FROM $wpdb->iopayorder $where order by $orderby $order limit $offset,$per_page");

$list_table = new io_auto_order_list($list_data, $column_info);

/*
        'view'        => __('付费内容', 'i_theme'),
        'part'        => __('付费阅读', 'i_theme'),
        'annex'       => __('付费资源', 'i_theme'),
        'auto_ad_url' => __('自动广告', 'i_theme'),
*/

$view_list = array(
    'all'    => array(
        'title' => '全部',
        'count' => ($_data['all_data']),
    ),
    'unpaid'   => array(
        'title' => '未支付',
        'count' => ($_data['unpaid_data']),
    ),
    'auto_ad_url'   => array(
        'title' => '自动广告',
        'count' => ($_data['auto_ad_url_data']),
    ),
    'view'   => array(
        'title' => '付费内容',
        'count' => ($_data['view_data']),
    ),
    'part'   => array(
        'title' => '付费阅读',
        'count' => ($_data['part_data']),
    ),
    'annex'   => array(
        'title' => '付费资源',
        'count' => ($_data['annex_data']),
    ),
);






$bulk_counts = array(
    'deleted'   => isset( $_REQUEST['deleted'] ) ? absint( $_REQUEST['deleted'] ) : 0,
);
$bulk_messages = array(
    'deleted'   => '已永久删除'. $bulk_counts['deleted'] .'条订单。',
); 
$bulk_counts   = array_filter( $bulk_counts );


?>
<div class="wrap">
    <h1 class="wp-heading-inline"><?php echo esc_html( $post_title  ); ?></h1>
    <a class="page-title-action" onclick="return confirm('清理订单会删除2周前所有未支付的订单，不可恢复！确认清理订单？')" href="<?php echo wp_nonce_url(add_query_arg(array('action' => 'clear_order', 'id' => 0), admin_url('admin.php?page=io_order')), 'io_order_action') ?>">清理订单</a>
<?php 
if ( isset( $_REQUEST['s'] ) && strlen( $_REQUEST['s'] ) ) {
    echo '<span class="subtitle">';
    printf(
        /* translators: %s: Search query. */
        __( 'Search results for: %s' ),
        '<strong>' . $_REQUEST['s'] . '</strong>' //get_search_query()
    );
    echo '</span>';
}
?>
<hr class="wp-header-end">
<!------------------------- 批处理的信息 ---------------------------->
<?php 

// 如果我们有一个批处理的信息要发布。
$messages = array();
foreach ( $bulk_counts as $message => $count ) {
    if ( isset( $bulk_messages[ $message ] ) ) {
        $messages[] = sprintf( $bulk_messages[ $message ], number_format_i18n( $count ) );
    }
}

if ( $messages ) {
    echo '<div id="message" class="updated notice is-dismissible"><p>' . implode( ' ', $messages ) . '</p></div>';
}
unset( $messages );

$_SERVER['REQUEST_URI'] = remove_query_arg( array( 'locked', 'skipped', 'updated', 'deleted', 'regaind', 'confirmd', 'trashed', 'untrashed' ), $_SERVER['REQUEST_URI'] );

?>
<!------------------------- 批处理的信息 END------------------------->

<?php iopay_admin_list_views($view_list, 'admin.php?page=io_order', 'order') ?>

<form id="posts-filter" method="get">

    <p class="search-box">
        <label class="screen-reader-text" for="sites-search-input">搜索订单:</label>
        <input type="search" id="sites-search-input" name="s" value="" placeholder="订单号|用户ID|文章ID">
        <input type="submit" id="search-submit" class="button" value="搜索订单">
    </p>

    <input type="hidden" name="order_status" value="<?php echo ! empty( $_REQUEST['order_status'] ) ? esc_attr( $_REQUEST['order_status'] ) : 'all'; ?>" />

    <?php if ( ! empty( $_REQUEST['author'] ) ) { ?>
    <input type="hidden" name="author" value="<?php echo esc_attr( $_REQUEST['author'] ); ?>" />
    <?php } ?>
    
    <?php if ( ! empty( $_REQUEST['show_sticky'] ) ) { ?>
    <input type="hidden" name="show_sticky" value="1" />
    <?php } ?>
    
    <?php if ( ! empty( $_REQUEST['page'] ) ) { ?>
    <input type="hidden" name="page" value="<?php echo esc_attr( $_REQUEST['page'] ); ?>" />
    <?php } ?>
    <table class="wp-list-table widefat fixed striped">
        <thead>
        <tr>
            <?php io_print_column_headers($column_info); ?>
        </tr>
        </thead>

        <tbody id="the-list">
            <?php $list_table->display_rows_or_placeholder(); ?>
        </tbody>

        <tfoot>
        <tr>
            <?php io_print_column_headers($column_info, false ); ?>
        </tr>
        </tfoot>

    </table>
    <?php io_display_tablenav( 'bottom' ) ?>
</form>

<div id="ajax-response"></div>
<div class="clear"></div>

</div>
