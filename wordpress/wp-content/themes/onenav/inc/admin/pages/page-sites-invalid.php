<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2022-02-20 18:28:17
 * @LastEditors: iowen
 * @LastEditTime: 2023-02-07 17:45:14
 * @FilePath: \onenav\inc\admin\pages\page-sites-invalid.php
 * @Description: 
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }  


$user_Info = wp_get_current_user();
if (!is_user_logged_in()) {
    exit;
}  
global $typenow;

if ( isset( $_REQUEST['post_type'] ) && post_type_exists( $_REQUEST['post_type'] ) ) {
	$typenow = $_REQUEST['post_type'];
} else {
	$typenow = '';
}
if ( ! $typenow ) {
	wp_die( __( 'Invalid post type.' ) );
}

global $post_type, $post_type_object, $invalid_items, $total_items, $per_page;

$post_type          = $typenow;
$post_type_object   = get_post_type_object( $post_type );

$post_title         = '失效的网址链接'; 
$failure_n          = 0;
$total_items        = get_invalid_count( $failure_n );
$per_page           = 20;


$pagenum            = get_pagenum();




$parent_file   = "edit.php?post_type=$post_type";
$submenu_file  = "edit.php?post_type=$post_type";
$post_new_file = "post-new.php?post_type=$post_type";


// 准备内容----------
$args = array(
    'post_type' 		=> 'sites',// 文章类型
    'post_status' 		=> 'publish',        
    'posts_per_page'   	=> $per_page, 
    'paged'      		=> $pagenum, 
); 
if(isset( $_REQUEST['sites_status']) && !empty($_REQUEST['sites_status'])){
    if($_REQUEST['sites_status'] == 1){
        $args = wp_parse_args(
            $args,
            array(
                'meta_key'          => '_dead_link', 
                'meta_value'        => '1',    
            )
        );
    }elseif($_REQUEST['sites_status'] == 2){
        $args = wp_parse_args(
            $args,
            array(
                'meta_key'          => '_redirect_url', 
                'meta_value'        => array(''),   
                'meta_compare'      => 'NOT IN',
            )
        );
    }elseif($_REQUEST['sites_status'] == 3){
        $args = wp_parse_args(
            $args,
            array(
                'meta_key'          => '_warning_link', 
                'meta_value'        => '1',    
            )
        );
    }elseif($_REQUEST['sites_status'] == 4){
        $args = wp_parse_args(
            $args,
            array(
                'meta_key'          => '_affirm_dead_url', 
                'meta_value'        => '1',    
            )
        );
    }elseif($_REQUEST['sites_status'] == 5){
        $args = wp_parse_args(
            $args,
            array(
                'meta_key'          => '_revive_url_m', 
                'meta_value'        => '666',    
            )
        );
    }
}else{
    $args = wp_parse_args(
        $args,
        array(
            'meta_query' 		=> array(
                'relation' 		=> 'OR',
                array(
                    'key' 		=> 'invalid',
                    'value' 	=> $failure_n,
                    'type' 		=> 'NUMERIC',
                    'compare' 	=> '>'
                ),
                array(
                    'key' 		=> 'report',
                    'value' 	=> 0,
                    'type' 		=> 'NUMERIC',
                    'compare' 	=> '>'
                )
            )
        )
    );
}
if(isset( $_REQUEST['s']) && !empty($_REQUEST['s'])){
    $args = wp_parse_args(
        $args,
        array(
            's' => $_REQUEST['s'], 
        )
    );
}
if(isset( $_REQUEST['orderby'] )){
    $args = wp_parse_args(
        $args,
        array(
            'orderby'   => $_REQUEST['orderby'],
            'order' => $_REQUEST['order'],
        )
    );
}
$invalid_items = get_posts( $args );

get_pagination_args(
	array(
		'total_items' => $total_items,
		'per_page'    => $per_page,
	)
);

$bulk_counts = array(
	'updated'   => isset( $_REQUEST['updated'] ) ? absint( $_REQUEST['updated'] ) : 0,
	'regaind'   => isset( $_REQUEST['regaind'] ) ? absint( $_REQUEST['regaind'] ) : 0,
	'deleted'   => isset( $_REQUEST['deleted'] ) ? absint( $_REQUEST['deleted'] ) : 0,
	'trashed'   => isset( $_REQUEST['trashed'] ) ? absint( $_REQUEST['trashed'] ) : 0,
	'confirmd'  => isset( $_REQUEST['confirmd'] ) ? absint( $_REQUEST['confirmd'] ) : 0,
	'untrashed' => isset( $_REQUEST['untrashed'] ) ? absint( $_REQUEST['untrashed'] ) : 0,
);
$bulk_messages['sites']     = array(
	/* translators: %s: Number of posts. */
	'updated'   => _n( '%s post updated.', '%s posts updated.', $bulk_counts['updated'] ),
	/* translators: %s: Number of posts. */
	'regaind'   => _n( '%s 个网址恢复到正常状态。', '%s 个网址恢复到正常状态。', $bulk_counts['regaind'] ),
	/* translators: %s: Number of posts. */
	'deleted'   => _n( '%s post permanently deleted.', '%s posts permanently deleted.', $bulk_counts['deleted'] ),
	/* translators: %s: Number of posts. */
	'trashed'   => _n( '%s post moved to the Trash.', '%s posts moved to the Trash.', $bulk_counts['trashed'] ),
	/* translators: %s: Number of posts. */
	'confirmd'  => _n( '%s 个网址确认失效。', '%s 个网址确认失效。', $bulk_counts['confirmd'] ),
	/* translators: %s: Number of posts. */
	'untrashed' => _n( '%s post restored from the Trash.', '%s posts restored from the Trash.', $bulk_counts['untrashed'] ),
); 
$bulk_counts   = array_filter( $bulk_counts );
?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php echo esc_html( $post_title  ); ?></h1>
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
	if ( isset( $bulk_messages[ $post_type ][ $message ] ) ) {
		$messages[] = sprintf( $bulk_messages[ $post_type ][ $message ], number_format_i18n( $count ) );
	}

	if ( 'trashed' === $message && isset( $_REQUEST['ids'] ) ) {
		$ids        = preg_replace( '/[^0-9,]/', '', $_REQUEST['ids'] );
		$messages[] = '<a href="' . esc_url( wp_nonce_url( "edit.php?post_type=$post_type&doaction=undo&action=untrash&ids=$ids", 'bulk-posts' ) ) . '">' . __( 'Undo' ) . '</a>';
	}

	if ( 'untrashed' === $message && isset( $_REQUEST['ids'] ) ) {
		$ids = explode( ',', $_REQUEST['ids'] );

		if ( 1 === count( $ids ) && current_user_can( 'edit_post', $ids[0] ) ) {
			$messages[] = sprintf(
				'<a href="%1$s">%2$s</a>',
				esc_url( get_edit_post_link( $ids[0] ) ),
				esc_html( get_post_type_object( get_post_type( $ids[0] ) )->labels->edit_item )
			);
		}
	}
}

if ( $messages ) {
	echo '<div id="message" class="updated notice is-dismissible"><p>' . implode( ' ', $messages ) . '</p></div>';
}
unset( $messages );

$_SERVER['REQUEST_URI'] = remove_query_arg( array( 'locked', 'skipped', 'updated', 'deleted', 'regaind', 'confirmd', 'trashed', 'untrashed' ), $_SERVER['REQUEST_URI'] );

?>
<!------------------------- 批处理的信息 END------------------------->

<?php io_list_views(); ?>

<form id="posts-filter" method="get">

    <?php io_search_box( $post_type_object->labels->search_items, $post_type,$invalid_items ); ?>

    <input type="hidden" name="post_status" class="post_status_page" value="<?php echo ! empty( $_REQUEST['post_status'] ) ? esc_attr( $_REQUEST['post_status'] ) : 'all'; ?>" />
    <input type="hidden" name="post_type" class="post_type_page" value="<?php echo $post_type; ?>" />

    <?php if ( ! empty( $_REQUEST['author'] ) ) { ?>
    <input type="hidden" name="author" value="<?php echo esc_attr( $_REQUEST['author'] ); ?>" />
    <?php } ?>
    
    <?php if ( ! empty( $_REQUEST['show_sticky'] ) ) { ?>
    <input type="hidden" name="show_sticky" value="1" />
    <?php } ?>
    
    <?php if ( ! empty( $_REQUEST['page'] ) ) { ?>
    <input type="hidden" name="page" value="<?php echo esc_attr( $_REQUEST['page'] ); ?>" />
    <?php } ?>



    <?php io_display(); ?>

</form>

<div id="ajax-response"></div>
<div class="clear"></div>

</div>
<div id="invalid_info" style="display:none">
    <div class="invalid-bg"></div>
    <div class="invalid-wind">
        <div class="invalid-doc">
        </div>
        <button type="button" class="notice-dismiss invalid-close"><span class="screen-reader-text">关闭</span></button>
    </div>
</div>
