<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2023-03-12 14:20:49
 * @LastEditors: iowen
 * @LastEditTime: 2024-05-08 20:01:09
 * @FilePath: /onenav/iopay/admin/page/ad.php
 * @Description: 
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

$user = wp_get_current_user();
if (!is_user_logged_in()) {
    exit;
}  

$post_title = '自动广告列表'; 

//表头
$column_info = array(
    array(
        'cb'       => '<input type="checkbox" />',
        'name'     => '名称',
        'url'      => '链接',
        'time'     => '添加时间',
        'expiry'   => '过期时间',
        'loc'      => '位置',
        'other'    => '时长&用户',
        'status'   => '支付状态',
        'nofollow' => 'nofollow',
        'check'    => '审核状态'
    ),
    array(),
    array(
        'name'   => array('name', 'asc'),
        'expiry' => array('expiry', 'asc'),
        'check'  => array('check', 'asc'),
        'status' => array('status', 'asc'),
        'loc'    => array('loc', 'asc'),
    ),
    'name'
);

//数据
$_data = array(
    'all_data'    => iopay_get_valid_auto_ad_url('AHP','all','all', true),
    'valid_data'  => iopay_get_valid_auto_ad_url('AHP','all','all'),
    'paid_data'   => iopay_get_valid_auto_ad_url('AHP','1'),
    'unpaid_data' => iopay_get_valid_auto_ad_url('AHP','0'),
    'home_data'   => iopay_get_valid_auto_ad_url('home', 'all'),
    'page_data'   => iopay_get_valid_auto_ad_url('page', 'all'),
    'check_data'  => iopay_get_valid_auto_ad_url('AHP', 'all', '0'),
    'uncheck_data'=> iopay_get_valid_auto_ad_url('AHP', 'all', 2),
    'expiry_data' => iopay_get_valid_auto_ad_url('', '', '', true),
);

$list_data = $_data['all_data'];
if(isset($_GET['ad_status'])){
    $list_data = $_data[$_GET['ad_status'] . '_data'];
}

$list_table = new io_auto_ad_list($list_data, $column_info);

$view_list = array(
    'all'    => array(
        'title' => '全部',
        'count' => count($_data['all_data']),
    ),
    'valid'  => array(
        'title' => '有效',
        'count' => count($_data['valid_data']),
    ),
    'paid'   => array(
        'title' => '已支付',
        'count' => count($_data['paid_data']),
    ),
    'unpaid' => array(
        'title' => '未支付',
        'count' => count($_data['unpaid_data']),
    ),
    'home'   => array(
        'title' => '首页',
        'count' => count($_data['home_data']),
    ),
    'page'   => array(
        'title' => '内页',
        'count' => count($_data['page_data']),
    ),
    'check'   => array(
        'title' => '未审核',
        'count' => count($_data['check_data']),
    ),
    'uncheck'   => array(
        'title' => '驳回',
        'count' => count($_data['uncheck_data']),
    ),
    'expiry'  => array(
        'title' => '已过期',
        'count' => count($_data['expiry_data']),
    ),
);









$bulk_counts = array(
	'updated'   => isset( $_REQUEST['updated'] ) ? absint( $_REQUEST['updated'] ) : 0,
	'deleted'   => isset( $_REQUEST['deleted'] ) ? absint( $_REQUEST['deleted'] ) : 0,
);
$bulk_messages = array(
	'updated'   => '已更新'. $bulk_counts['updated'] .'条广告。',
    'deleted'   => '已永久删除'. $bulk_counts['deleted'] .'条广告。',
); 
$bulk_counts   = array_filter( $bulk_counts );


?>
<div class="wrap">
    <h1 class="wp-heading-inline"><?php echo esc_html( $post_title  ); ?></h1>
    <a href="javascript:" class="page-title-action add-auto-url">添加</a>
    <p>状态：<?php echo (io_get_option('auto_ad_s',true)?'已开启':'已关闭') ?> | <a href="<?php echo io_get_admin_iocf_url('商城设置/自助自动') ?>">立即前往</a></p>
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

<?php iopay_admin_list_views($view_list, 'admin.php?page=io_ad') ?>

<form id="posts-filter" method="get">

    <input type="hidden" name="ad_status" value="<?php echo ! empty( $_REQUEST['ad_status'] ) ? esc_attr( $_REQUEST['ad_status'] ) : 'all'; ?>" />

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
</form>

<div id="ajax-response"></div>
<div class="clear"></div>

</div>
<div id="io_model_info" class="io-model" style="display:none">
    <div class="io-model-bg"></div>
    <div class="io-model-wind">
        <div class="io-model-doc">
            <h3 class="io-model-title">修改</h3>
            <form class="ajax-form" ajax-url="<?php echo wp_nonce_url(admin_url('admin.php?page=io_ad'), 'io_ad_action') ?>">
                <div class="form-group">
                    <label for="auto_loc">位置</label>
                    <select class="form-control" id="auto_loc" name="loc">
                        <option value="home">首页</option>
                        <option value="page">内页</option>
                        <option value="all">所有</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="auto_name">名称</label>
                    <input type="text" id="auto_name" name="name" class="form-control" value="">
                </div>
                <div class="form-group">
                    <label for="auto_url">网址</label>
                    <input type="text" id="auto_url" name="url" class="form-control" placeholder="https://" value="">
                </div>
                <div class="form-group">
                    <label for="auto_url">过期时间</label>
                    <input type="datetime-local" step="1" id="auto_expiry" name="expiry" class="form-control" value="">
                </div>
                <div class="form-check">
                    <input type="checkbox" name="nofollow" id="auto_nofollow" class="form-check-input" value="1">
                    <label class="form-check-label" for="auto_nofollow">nofollow</label>
                </div>
                <div class="form-check">
                    <input type="checkbox" name="status" id="auto_status" class="form-check-input" value="1">
                    <label class="form-check-label" for="auto_status">已支付</label>
                </div>
                <div class="footer-btn">
                    <input type="hidden" name="ajax" value="1">
                    <input type="hidden" name="check" id="auto_check" value="0">
                    <input type="hidden" name="action" id="auto_action" value="update">
                    <input type="hidden" name="id" id="auto_id" value="">
                    <input type="submit" class="button button-primary ajax-submit button-large" value="确定">
                    <input type="button" class="button button-large io-model-close" value="取消">
                </div>
                <div class="ajax-notice"></div>
            </form>
        </div>
    </div>
</div>
