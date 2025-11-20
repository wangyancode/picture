<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2022-02-20 18:28:17
 * @LastEditors: iowen
 * @LastEditTime: 2025-06-04 17:50:24
 * @FilePath: /onenav/inc/admin/sites/functions.php
 * @Description: 
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

$functions = array();

foreach ($functions as $function) {
    $path = 'inc/admin/' . $function . '.php';
    require get_theme_file_path($path);
}

add_action('admin_menu', 'io_sites_invalid_submenu_page');
add_action('admin_notices', 'io_sites_invalid_apply_admin_notice');


function io_sites_invalid_apply_admin_notice() {
    $screen = get_current_screen();
    if (!($screen->post_type == 'sites' || $screen->id == 'dashboard') ) {
        return;
    } 
    $sites_count    = get_invalid_count(0);

    if ($sites_count > 0) {
        $html = '<div class="notice notice-error is-dismissible">';
        $html .= '<h3>失效网址待处理</h3>';
        $html .= '<p>您有' . $sites_count . '个失效网址待处理</p>';
        $html .= '<p><a class="button" href="' . add_query_arg(array('page' => 'invalid-sites', 'post_type' => 'sites'), admin_url('edit.php')) . '">立即处理</a></p>';
        $html .= '</div>';
        echo $html;
    }

}

//后台网址链接管理
function io_sites_invalid_submenu_page() {
    $sites_count    = get_invalid_count(0);

    $menu_title     = '失效网址';
    if($sites_count > 0){
        $menu_title .= sprintf(
            ' <span class="update-plugins"><span class="update-count menu-bubble">%d</span></span>',
            $sites_count
        );
    }
    add_submenu_page('edit.php?post_type=sites', '失效的网址链接', $menu_title, 'administrator', 'invalid-sites', 'io_require_sites_invalid_submenu_page');
}

function io_require_sites_invalid_submenu_page() {
    require get_theme_file_path('inc/admin/pages/page-sites-invalid.php');
}
# 后台检测失效网址状态
# --------------------------------------------------------------------
function sites_invalid_prompt_menu() {
    if( ! is_admin() ) { return; }
    global $wp_admin_bar; 
    $sites_count    = get_invalid_count(0);
    if ($sites_count) : 
        $wp_admin_bar->add_menu(array(
            'id' => 'sites_invalid',  
            'title' => '<span class="update-plugins count-2" style="display: inline-block;background-color: #d54e21;color: #fff;font-size: 9px;font-weight: 600;border-radius: 10px;z-index: 26;height: 18px;margin-right: 5px;"><span class="update-count" style="display: block;padding: 0 6px;line-height: 17px;">'.$sites_count.'</span></span>个失效网址', 
            'href' => admin_url('/edit.php?post_type=sites&page=invalid-sites')
        ));     
    endif; 
    wp_reset_postdata();
}
add_action('admin_bar_menu', 'sites_invalid_prompt_menu', 2000);
function sites_revive_prompt_menu() {
    if( ! is_admin() ) { return; }
    global $wp_admin_bar; 
    $sites_count    = get_revive_url_count();
    if ($sites_count) : 
        $wp_admin_bar->add_menu(array(
            'id' => 'sites_revive',  
            'title' => '<span class="update-plugins count-2" style="display: inline-block;background-color: #d54e21;color: #fff;font-size: 9px;font-weight: 600;border-radius: 10px;z-index: 26;height: 18px;margin-right: 5px;"><span class="update-count" style="display: block;padding: 0 6px;line-height: 17px;">'.$sites_count.'</span></span>个复活网址', 
            'href' => admin_url('/edit.php?post_type=sites&sites_status=5&page=invalid-sites')
        ));     
    endif; 
    wp_reset_postdata();
}
add_action('admin_bar_menu', 'sites_revive_prompt_menu', 2000);

function get_column_info(){
    $columns = array(
        'cb'          => '<input type="checkbox" />',
        'title'       => '标题',
        'url'         => '链接', 
		'redirect'    => '重定向', 
        'type'        => '失效类型', 
        'handle'      => '处理', 
    );
    $hidden = array();
    $sortable = array(
        'title' => array('title','asc'),
    );
    $primary = 'title';
    return array( $columns, $hidden, $sortable, $primary );
    
} 
/**
 * 排队插入JS文件
 */
add_action('admin_enqueue_scripts', 'invalid_setting_scripts');
function invalid_setting_scripts() {
    if ( isset( $_REQUEST['page'] ) && 'invalid-sites'===$_REQUEST['page']) {
        wp_enqueue_style('io_page', get_theme_file_uri('/inc/admin/assets/page-list.css'), array(), IO_VERSION );
        wp_enqueue_script('io_page', get_theme_file_uri('/inc/admin/assets/page-list.js'), array(), IO_VERSION );
    }
}
/**
 * 移出失效链接列表
 * 
 * @param mixed $post_id
 * @return void
 */
function move_out_invalid_url($post_id){
    delete_post_meta($post_id,'invalid');
    delete_post_meta($post_id,'report');
    delete_post_meta($post_id,'_invalid_reason');//无效原因
    delete_post_meta($post_id,'_dead_link');
    //delete_post_meta($post_id,'_redirect_url');
}


function invalid_post_init() { 
    if ( isset( $_REQUEST['page'] ) && 'invalid-sites'===$_REQUEST['page'] ) {
        
        global $post_screen, $post_args, $wpdb;
		$args = array(
			'plural'   => 'invalid',
			'singular' => '',
			'ajax'     => false,
			'screen'   => null,
		);

		$post_screen = convert_to_screen( $args['screen'] );
		
		if ( ! $args['plural'] ) {
			$args['plural'] = $post_screen->base;
		}

		$args['plural']   = sanitize_key( $args['plural'] );
		$args['singular'] = sanitize_key( $args['singular'] );

        $post_type = 'sites';
		$post_args = $args;
        $parent_file = "edit.php?post_type=$post_type";
        
        $doaction = io_current_action();
        if ( $doaction ) { 
            check_admin_referer( 'bulk-invalid' );
        
            $sendback = remove_query_arg( array( 'trashed', 'untrashed', 'deleted', 'locked', 'regaind', 'updated', 'confirmd', 'ids' ), wp_get_referer() );
            if ( ! $sendback ) {
                $sendback = admin_url( $parent_file );
            }
        
            $post_ids = array();
        
            if ( 'delete_all' === $doaction ) {
                // 准备删除所有具有指定状态的文章。 (即清空垃圾箱).
                $post_status = preg_replace( '/[^a-z0-9_-]+/i', '', $_REQUEST['post_status'] );
                // 验证文章状态是否存在。
                if ( get_post_status_object( $post_status ) ) {
                    $post_ids = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type=%s AND post_status = %s", $post_type, $post_status ) );
                }
                $doaction = 'delete';
            } elseif ( isset( $_REQUEST['media'] ) ) {
                $post_ids = $_REQUEST['media'];
            } elseif ( isset( $_REQUEST['ids'] ) ) {
                $post_ids = explode( ',', $_REQUEST['ids'] );
            } elseif ( ! empty( $_REQUEST['post'] ) ) {
                if(is_array($_REQUEST['post']))
                    $post_ids = array_map( 'intval', $_REQUEST['post'] );
                else
                    $post_ids[] = intval($_REQUEST['post']);
            }
        
            if ( empty( $post_ids ) ) {
                wp_redirect( $sendback );
                exit;
            }
        
            switch ( $doaction ) {
                case 'trash':
                    $trashed = 0;
                    $locked  = 0;
        
                    foreach ( (array) $post_ids as $post_id ) {
                        if ( ! current_user_can( 'delete_post', $post_id ) ) {
                            wp_die( __( 'Sorry, you are not allowed to move this item to the Trash.' ) );
                        }
        
                        if ( wp_check_post_lock( $post_id ) ) {
                            $locked++;
                            continue;
                        }
        
                        if ( ! wp_trash_post( $post_id ) ) {
                            wp_die( __( 'Error in moving the item to Trash.' ) );
                        }
        
                        $trashed++;
                    }
        
                    $sendback = add_query_arg(
                        array(
                            'trashed' => $trashed,
                            'ids'     => implode( ',', $post_ids ),
                            'locked'  => $locked,
                        ),
                        $sendback
                    );
                    break;
                case 'update': //更新重定向
                    $updated = 0;
        
                    foreach ( (array) $post_ids as $post_id ) { 
                        if($redirect = get_post_meta($post_id, '_redirect_url', true)){
                            update_post_meta($post_id, "_sites_link", $redirect );
                            move_out_invalid_url($post_id);

                            delete_post_meta($post_id,'_redirect_url');
                            delete_post_meta($post_id,'_affirm_dead_url');
                            delete_post_meta($post_id,'_revive_url_m');
                            $updated++;
                        }
                    }
        
                    $sendback = add_query_arg(array('updated' => $updated),$sendback);
                    break;
                case 'regain': //恢复正常
                    $regaind = 0;
        
                    foreach ( (array) $post_ids as $post_id ) { 
                        move_out_invalid_url($post_id);
                        
                        delete_post_meta($post_id,'_affirm_dead_url');
                        delete_post_meta($post_id,'_revive_url_m');
                        $regaind++;
                    }
        
                    $sendback = add_query_arg(array('regaind' => $regaind,'updated' => ''),$sendback);
                    break;
                case 'delete':
                    $deleted = 0;
                    foreach ( (array) $post_ids as $post_id ) {
                        $post_del = get_post( $post_id );
        
                        if ( ! current_user_can( 'delete_post', $post_id ) ) {
                            wp_die( __( 'Sorry, you are not allowed to delete this item.' ) );
                        }
        
                        if ( 'attachment' === $post_del->post_type ) {
                            if ( ! wp_delete_attachment( $post_id ) ) {
                                wp_die( __( 'Error in deleting the attachment.' ) );
                            }
                        } else {
                            if ( ! wp_delete_post( $post_id ) ) {
                                wp_die( __( 'Error in deleting the item.' ) );
                            }
                        }
                        $deleted++;
                    }
                    $sendback = add_query_arg( 'deleted', $deleted, $sendback );
                    break;
                case 'blacklist': //添加到确认失效列表
                    $confirmd = 0;
                    foreach ( (array) $post_ids as $post_id ) {
                        if (!get_post_meta($post_id, '_affirm_dead_url', true)) {
                            add_post_meta($post_id, '_affirm_dead_url', 1);
                            $wpdb->update($wpdb->posts, array('post_mime_type' => 1), array('ID' => $post_id));
                        }
                        move_out_invalid_url($post_id);
                        delete_post_meta($post_id,'_revive_url_m');
                        $confirmd++;
                    }
                    $sendback = add_query_arg(array('confirmd' => $confirmd,'updated' => ''),$sendback);
                    break;
                default:
                    break;
            }
        
            $sendback = remove_query_arg( array( 'action', 'action2', 'tags_input', 'post_author', 'comment_status', 'ping_status', '_status', 'post', 'bulk_edit', 'post_view' ), $sendback );
            wp_redirect( $sendback );
            exit;
        } elseif ( ! empty( $_REQUEST['_wp_http_referer'] ) ) {
            wp_redirect( remove_query_arg( array( '_wp_http_referer', '_wpnonce' ), wp_unslash( $_SERVER['REQUEST_URI'] ) ) );
            exit;
        }
        
    }
} 
add_action('admin_init', 'invalid_post_init',1);

$_invalid_count = false;
/**
 * 获取无效的网站数量
 *  
 * @param int  $threshold
 * @return int
 */
function get_invalid_count($threshold = 5){
    global $_invalid_count;
    if(!$_invalid_count){
        global $wpdb;
        $sql = "SELECT COUNT(DISTINCT $wpdb->posts.ID) 
        FROM $wpdb->posts
        INNER JOIN $wpdb->postmeta
        ON ( $wpdb->posts.ID = $wpdb->postmeta.post_id )
        WHERE 1=1
        AND ( 
            ( $wpdb->postmeta.meta_key = 'invalid' AND CAST($wpdb->postmeta.meta_value AS SIGNED) > '$threshold' )
            OR ( $wpdb->postmeta.meta_key = 'report' AND CAST($wpdb->postmeta.meta_value AS SIGNED) > '0' ) 
        )
        AND $wpdb->posts.post_type = 'sites'
        AND (($wpdb->posts.post_status = 'publish'))";
        $_invalid_count = intval($wpdb->get_var($sql));
    }
    return $_invalid_count;
}
/**
 * 获取确实失效的网站数量
 *  
 * @return int
 */
function get_affirm_dead_url_count(){
    global $wpdb;
    $sql = "SELECT COUNT(*)
    FROM $wpdb->posts
    INNER JOIN $wpdb->postmeta
    ON ( $wpdb->posts.ID = $wpdb->postmeta.post_id )
    WHERE 1=1
    AND ( ( $wpdb->postmeta.meta_key = '_affirm_dead_url' AND $wpdb->postmeta.meta_value = '1' ) )
    AND $wpdb->posts.post_type = 'sites'
    AND (($wpdb->posts.post_status = 'publish'))";
    return intval($wpdb->get_var($sql));
}

/**
 * 获取可能复活的网站数量
 *  
 * @return int
 */
function get_revive_url_count(){
    global $wpdb;
    $sql = "SELECT COUNT(*)
    FROM $wpdb->posts
    INNER JOIN $wpdb->postmeta
    ON ( $wpdb->posts.ID = $wpdb->postmeta.post_id )
    WHERE 1=1
    AND ( ( $wpdb->postmeta.meta_key = '_revive_url_m' AND $wpdb->postmeta.meta_value = '666' ) )
    AND $wpdb->posts.post_type = 'sites'
    AND (($wpdb->posts.post_status = 'publish'))";
    return intval($wpdb->get_var($sql));
}
/**
 * 获取无效的网站数量
 *  
 * @return int
 */
function get_invalid_dead_count(){
    global $wpdb;
    $sql = "SELECT COUNT(*)
    FROM $wpdb->posts
    INNER JOIN $wpdb->postmeta
    ON ( $wpdb->posts.ID = $wpdb->postmeta.post_id )
    WHERE 1=1
    AND ( ( $wpdb->postmeta.meta_key = '_dead_link' AND $wpdb->postmeta.meta_value = '1' ) )
    AND $wpdb->posts.post_type = 'sites'
    AND (($wpdb->posts.post_status = 'publish'))";
    return intval($wpdb->get_var($sql));
}
/**
 * 获取重定向的网站数量
 *  
 * @return int
 */
function get_invalid_redirect_count(){
    global $wpdb;
    $sql = "SELECT COUNT(*)
    FROM $wpdb->posts
    INNER JOIN $wpdb->postmeta
    ON ( $wpdb->posts.ID = $wpdb->postmeta.post_id )
    WHERE 1=1
    AND ( ( $wpdb->postmeta.meta_key = '_redirect_url' AND $wpdb->postmeta.meta_value NOT IN ('') ) )
    AND $wpdb->posts.post_type = 'sites'
    AND (($wpdb->posts.post_status = 'publish'))"; 
    return intval($wpdb->get_var($sql));
}
/**
 * 获取注意的网站数量
 *  
 * @return int
 */
function get_invalid_warn_count(){
    global $wpdb;
    $sql = "SELECT COUNT(*)
    FROM $wpdb->posts
    INNER JOIN $wpdb->postmeta
    ON ( $wpdb->posts.ID = $wpdb->postmeta.post_id )
    WHERE 1=1
    AND ( ( $wpdb->postmeta.meta_key = '_warning_link' AND $wpdb->postmeta.meta_value = '1' ) )
    AND $wpdb->posts.post_type = 'sites'
    AND (($wpdb->posts.post_status = 'publish'))"; 
    return intval($wpdb->get_var($sql));
}
/**
 * 获取单页无效的网站
 *  
 * @param int  $page 当前页
 * @param int  $count 一页数量
 * @return object
 */
function get_invalid_sites($page = 0,$count=20){
    global $wpdb;
    $sql = "SELECT * FROM $wpdb->posts INNER JOIN $wpdb->postmeta
    ON ( $wpdb->posts.ID = $wpdb->postmeta.post_id )
    WHERE ( ( $wpdb->postmeta.meta_key = 'invalid'
    AND CAST($wpdb->postmeta.meta_value AS SIGNED) > '2' ) )
    AND $wpdb->posts.post_type = 'sites'
    AND (($wpdb->posts.post_status = 'publish'))
    GROUP BY $wpdb->posts.ID
    ORDER BY $wpdb->posts.menu_order , $wpdb->posts.post_date DESC
    LIMIT $page, $count";
    return  (object)$wpdb->get_results($sql);
}
/**
 * 显示表格。
 */
function io_display() {
    global $post_screen, $post_args;
    $singular = $post_args['singular']; 

    io_display_tablenav( 'top' );

    $post_screen->render_screen_reader_content( 'heading_list' );
    ?>
<table class="wp-list-table widefat fixed striped">
<thead>
<tr>
    <?php io_print_column_headers(get_column_info()); ?>
</tr>
</thead>

<tbody id="the-list"
    <?php
    if ( $singular ) {
        echo " data-wp-lists='list:$singular'";
    }
    ?>
    >
    <?php display_rows_or_placeholder(); ?>
</tbody>

<tfoot>
<tr>
    <?php io_print_column_headers(get_column_info(), false ); ?>
</tr>
</tfoot>

</table>
    <?php
    io_display_tablenav( 'bottom' );
}
/**
 * 在表格上方或下方生成表格导航
 * 
 * @param string $which
 */
function io_display_tablenav( $which ) {
    
    global $invalid_items,$post_args;
    if ( 'top' === $which ) {
        wp_nonce_field( 'bulk-' . $post_args['plural'] );
    }
    ?>
<div class="tablenav <?php echo esc_attr( $which ); ?>">

    <?php if ( $invalid_items ) : ?>
    <div class="alignleft actions bulkactions">
        <?php io_bulk_actions( $which ); ?>
    </div>
        <?php
    endif; 
    io_pagination( $which );
    ?>

    <br class="clear" />
</div>
    <?php
}
/**
 * 生成 tbody 元素。
 */
function display_rows_or_placeholder() {
    global $invalid_items;
    if ( $invalid_items ) { 
        foreach ( $invalid_items as $item ) {
            echo '<tr>';
            io_single_row_columns( $item );
            echo '</tr>';
        }
    } else {
        echo '<tr class="no-items"><td class="colspanchange" colspan="' . io_get_column_count(get_column_info()) . '">';
        no_items();
        echo '</td></tr>';
    }
}
/**
 * 没有项目时要显示的讯息
 */
function no_items() {
    global $post_screen;
    if ( isset( $_REQUEST['post_status'] ) && 'trash' === $_REQUEST['post_status'] ) {
        echo get_post_type_object( $post_screen->post_type )->labels->not_found_in_trash;
    } else {
        echo get_post_type_object( $post_screen->post_type )->labels->not_found;
    }
}
/**
 * 处理复选框列输出。
 *
 * @since 4.3.0
 * @since 5.9.0 将`$post`改名为`$item`，以配合父类对PHP 8命名参数的支持。
 *
 * @param WP_Post $item 当前的WP_Post对象。
 */
function io_column_cb( $item ) {
    $post = $item;
    $show = current_user_can( 'edit_post', $post->ID );
    ?>
        <label class="screen-reader-text" for="cb-select-<?php echo $post->ID ?>">
            <?php
                /* translators: %s: Post title. */
                printf( __( 'Select %s' ), _draft_or_post_title($post) );
            ?>
        </label>
        <input id="cb-select-<?php echo $post->ID ?>" type="checkbox" name="post[]" value="<?php echo $post->ID ?>" />
        <div class="locked-indicator">
            <span class="locked-indicator-icon" aria-hidden="true"></span>
            <span class="screen-reader-text">
            <?php
            printf(
                /* translators: %s: Post title. */
                __( '&#8220;%s&#8221; is locked' ),
                _draft_or_post_title($post)
            );
            ?>
            </span>
        </div>
    <?php 
}
/**
 * 处理默认列输出。
 *
 * @since 4.3.0
 * @since 5.9.0 将`$post`改名为`$item`，以配合父类对PHP 8命名参数的支持。
 * @param WP_Post $item        当前的WP_Post对象。
 * @param string  $column_name 当前的列名。
 */
function io_column_default( $item, $column_name ) {
    $post = $item;
    if ( 'categories' === $column_name ) {
        $taxonomy = 'category';
    } elseif ( 'tags' === $column_name ) {
        $taxonomy = 'post_tag';
    } elseif ( 0 === strpos( $column_name, 'taxonomy-' ) ) {
        $taxonomy = substr( $column_name, 9 );
    } elseif ( 'url' === $column_name ) {
        $sites_url = get_post_meta($post->ID,'_sites_link',true);
        if($sites_url){
            echo '<a href="'.$sites_url.'" target="_blank" rel="external noopener">' . $sites_url . '</a>';
        }else{
            echo '<span aria-hidden="true">—</span><span class="screen-reader-text">没有标签</span>';
        }
        return;
    } elseif ( 'redirect' === $column_name ) {
        $redirect_url = get_post_meta($post->ID,'_redirect_url',true);
        if($redirect_url){
            echo '<a href="'.$redirect_url.'" target="_blank" rel="external noopener">' . $redirect_url . '</a>';
        }else{
            echo '<span aria-hidden="true">—</span><span class="screen-reader-text">没有标签</span>';
        }
        return;
    } elseif ( 'type' === $column_name ) {
        $sites_invalid  = get_post_meta($post->ID,'invalid',true);
        $sites_report   = get_post_meta($post->ID,'_invalid_reason',true);
        if(io_get_option('server_link_check',false)){
            $link           = new IOLINK( $post->ID );
            $http_code      = $link->analyse_status();
            echo '<span class="invalid-type invalid-'.$http_code['code'].'">'.( empty( $link->http_code ) ? '' : $link->http_code ).' '.$http_code['text'].'</span>';
        }
        if(!empty($sites_report) && is_array($sites_report)){
            foreach($sites_report as $report){
                if(is_numeric($report)){
                    echo '<span class="invalid-type invalid-'.$report.'">'.get_report_reason()[$report].'</span>';
                }else{
                    echo  '<span class="invalid-type invalid-0">'.$report.'</span>';
                }
            }
        }
        return;
    } elseif ( 'handle' === $column_name ) {
        global $post_args;
        $nonce = wp_create_nonce( 'bulk-invalid' );
        if (!isset($_GET['sites_status']) || (isset($_GET['sites_status']) && !in_array($_GET['sites_status'],array(4)))) {
            echo '<a href="' . esc_url(wp_nonce_url("edit.php?post_type=sites&page=invalid-sites&post=$post->ID&action=blacklist", 'bulk-invalid')) . '" title="保留链接，提示用户失效（避免SEO掉链）">确认失效</a> | ';
        }
        echo '<a href="'.get_delete_post_link( $post->ID,'',true ) .'" title="彻底删除网址">删除</a> | ';
        echo '<a href="'.esc_url( wp_nonce_url( "edit.php?post_type=sites&page=invalid-sites&post=$post->ID&action=regain", 'bulk-invalid' ) ).'" title="恢复网址到正常状态">恢复</a>';
        if($redirect_url = get_post_meta($post->ID,'_redirect_url',true)){
            echo ' | <a href="'.esc_url( wp_nonce_url( "edit.php?post_type=sites&page=invalid-sites&post=$post->ID&action=update", 'bulk-invalid' ) ).'" title="更新网址为重定向地址并恢复状态">更新并恢复</a>';
        }
        return;
    } else {
        $taxonomy = false;
    }

    if ( $taxonomy ) {
        $taxonomy_object = get_taxonomy( $taxonomy );
        $terms           = get_the_terms( $post->ID, $taxonomy );

        if ( is_array( $terms ) ) {
            $term_links = array();

            foreach ( $terms as $t ) {
                $posts_in_term_qv = array();

                if ( 'post' !== $post->post_type ) {
                    $posts_in_term_qv['post_type'] = $post->post_type;
                }

                if ( $taxonomy_object->query_var ) {
                    $posts_in_term_qv[ $taxonomy_object->query_var ] = $t->slug;
                } else {
                    $posts_in_term_qv['taxonomy'] = $taxonomy;
                    $posts_in_term_qv['term']     = $t->slug;
                }

                $label = esc_html( sanitize_term_field( 'name', $t->name, $t->term_id, $taxonomy, 'display' ) );

                $term_links[] = io_get_edit_link( $posts_in_term_qv, $label );
            }

            /* translators: Used between list items, there is a space after the comma. */
            echo implode( __( ', ' ), $term_links );
        } else {
            echo '<span aria-hidden="true">&#8212;</span><span class="screen-reader-text">' . $taxonomy_object->labels->no_terms . '</span>';
        }
        return;
    }
}
/**
 * 处理标题列输出。 
 *
 * @param WP_Post $post
 * @param string  $classes
 * @param string  $data
 * @param string  $primary
 */
function _column_title( $post, $classes, $data, $primary ) {
    echo '<td class="' . $classes . ' page-title" ', $data, '>';
    echo io_column_title( $post );
    echo io_handle_row_actions( $post, 'title', $primary );
    echo '</td>';
}

/**
 * 处理标题列输出。 
 *
 * @param WP_Post $post 当前的WP_Post对象。
 */
function io_column_title( $post ) { 

    $can_edit_post = current_user_can( 'edit_post', $post->ID );

    echo '<strong>';

    $title = _draft_or_post_title($post);

    if ( $can_edit_post && 'trash' !== $post->post_status ) {
        printf(
            '<a class="row-title" href="%s" aria-label="%s">%s</a>',
            get_edit_post_link( $post->ID ),
            /* translators: %s: Post title. */
            esc_attr( sprintf( __( '&#8220;%s&#8221; (Edit)' ), $title ) ),
            $title
        );
    } else {
        printf(
            '<span>%s%s</span>', 
            $title
        );
    }
    _post_states( $post );

    echo "</strong>\n";
        if ( post_password_required( $post ) ) {
            echo '<span class="protected-post-excerpt">' . esc_html( get_the_excerpt() ) . '</span>';
        } else {
            echo esc_html( get_the_excerpt() );
        }

    get_inline_data(  $post );
}
	
/**
 * 生成并显示列表表的行操作链接。
 *
 * @since 4.3.0
 *
 * @param object|array $item        当前项目。
 * @param string       $column_name 当前列名。
 * @param string       $primary     主列名称。
 * @return string 当前行的可执行按钮HTML，如果当前列不是主列，则为空字符串。
 */
function io_handle_row_actions( $item, $column_name, $primary ) {
    if ( $primary !== $column_name ) {
        return '';
    }

    $post             = $item;
    $post_type_object = get_post_type_object( $post->post_type );
    $can_edit_post    = current_user_can( 'edit_post', $post->ID );
    $actions          = array();
    $title            = _draft_or_post_title();

    if ( $can_edit_post && 'trash' !== $post->post_status ) {
        $actions['edit'] = sprintf(
            '<a href="%s" aria-label="%s">%s</a>',
            get_edit_post_link( $post->ID ),
            /* translators: %s: Post title. */
            esc_attr( sprintf( __( 'Edit &#8220;%s&#8221;' ), $title ) ),
            __( 'Edit' )
        );
    }

    if ( current_user_can( 'delete_post', $post->ID ) ) {
        if ( 'trash' === $post->post_status ) {
            $actions['untrash'] = sprintf(
                '<a href="%s" aria-label="%s">%s</a>',
                wp_nonce_url( admin_url( sprintf( $post_type_object->_edit_link . '&amp;action=untrash', $post->ID ) ), 'untrash-post_' . $post->ID ),
                /* translators: %s: Post title. */
                esc_attr( sprintf( __( 'Restore &#8220;%s&#8221; from the Trash' ), $title ) ),
                __( 'Restore' )
            );
        } elseif ( EMPTY_TRASH_DAYS ) {
            $actions['trash'] = sprintf(
                '<a href="%s" class="submitdelete" aria-label="%s">%s</a>',
                get_delete_post_link( $post->ID ),
                /* translators: %s: Post title. */
                esc_attr( sprintf( __( 'Move &#8220;%s&#8221; to the Trash' ), $title ) ),
                _x( 'Trash', 'verb' )
            );
        }

        if ( 'trash' === $post->post_status || ! EMPTY_TRASH_DAYS ) {
            $actions['delete'] = sprintf(
                '<a href="%s" class="submitdelete" aria-label="%s">%s</a>',
                get_delete_post_link( $post->ID, '', true ),
                /* translators: %s: Post title. */
                esc_attr( sprintf( __( 'Delete &#8220;%s&#8221; permanently' ), $title ) ),
                __( 'Delete Permanently' )
            );
        }
    }

    if ( is_post_type_viewable( $post_type_object ) ) {
        if ( in_array( $post->post_status, array( 'pending', 'draft', 'future' ), true ) ) {
            if ( $can_edit_post ) {
                $preview_link    = get_preview_post_link( $post );
                $actions['view'] = sprintf(
                    '<a href="%s" rel="bookmark" aria-label="%s">%s</a>',
                    esc_url( $preview_link ),
                    /* translators: %s: Post title. */
                    esc_attr( sprintf( __( 'Preview &#8220;%s&#8221;' ), $title ) ),
                    __( 'Preview' )
                );
            }
        } elseif ( 'trash' !== $post->post_status ) {
            $actions['view'] = sprintf(
                '<a href="%s" rel="bookmark" aria-label="%s">%s</a>',
                get_permalink( $post->ID ),
                /* translators: %s: Post title. */
                esc_attr( sprintf( __( 'View &#8220;%s&#8221;' ), $title ) ),
                __( 'View' )
            );
        }
    }
    if(io_get_option('server_link_check',false)){
        //添加重新检查按钮
        $actions['recheck'] = '<a href="javascript:;" ajax-url="'.esc_url(admin_url('admin-ajax.php')).'" data-post_id="'.$post->ID.'" data-action="io_recheck_link" class="io-recheck-button">重新检查</a>';
    }
    $actions = apply_filters( 'post_row_actions', $actions, $post );
    return row_actions( $actions );
}
/**
 * 为行操作链接列表生成所需的 HTML。
 *
 * @param string[] $actions        动作链接数组
 * @param bool     $always_visible 操作是否应该始终可见。
 * @return string 操作链接 HTML。
 */
function row_actions( $actions, $always_visible = false ) {
    $action_count = count( $actions );

    if ( ! $action_count ) {
        return '';
    }

    $out = '<div class="' . ( $always_visible ? 'row-actions visible' : 'row-actions' ) . '">';

    $i = 0;

    foreach ( $actions as $action => $link ) {
        ++$i;

        $sep = ( $i < $action_count ) ? ' | ' : '';

        $out .= "<span class='$action'>$link$sep</span>";
    }

    $out .= '</div>';

    $out .= '<button type="button" class="toggle-row"><span class="screen-reader-text">' . __( 'Show more details' ) . '</span></button>';

    return $out;
}

/**
 * 获取此表上可用的视图列表。
 *
 * 格式是一个关联数组：
 * - `'id' => 'link'`
 *
 * @return array
 */
function io_list_get_views() {
    global $post_screen;

    $post_type    = $post_screen->post_type;  

    $status_links = array();
    $num_posts    = get_invalid_count(0); 
    $class        = ''; 
    
    $all_args     = array( 'post_type' => $post_type,'page'=>'invalid-sites' ); 

    if ( empty( $class ) && ( is_base_request() || isset( $_REQUEST['all_posts'] ) ) ) {
        $class = 'current';
    }

    $all_inner_html = sprintf(
        /* translators: %s: Number of posts. */
        _nx(
            'All <span class="count">(%s)</span>',
            'All <span class="count">(%s)</span>',
            $num_posts,
            'posts'
        ).'(不含确认无效)',
        number_format_i18n( $num_posts )
    );
    $status_links['all'] = io_get_edit_link( $all_args, $all_inner_html, $class );

    $list = array(
        "dead" => 1, 
        "redirect" => 2, 
        "warning" => 3,
        "affirm" => 4,
        "revive" => 5
    );
    foreach($list as $name => $status){
        $class = '';
        $status_args = array(
            'post_type' => $post_type,
            'sites_status' => $status,
            'page' => 'invalid-sites'
        );
        if ( isset( $_REQUEST['sites_status'] ) && $status == $_REQUEST['sites_status'] ) {
            $class = 'current';
        }
        switch($name){
            case "dead": 
                $status_label = sprintf(
                    '失效 %s',
                    '<span class="count">('.get_invalid_dead_count().')</span>'
                );
                break;
            case "redirect": 
                $status_label = sprintf(
                    '重定向 %s',
                    '<span class="count">('.get_invalid_redirect_count().')</span>'
                );
                break;
            case "warning": 
                $status_label = sprintf(
                    '注意 %s',
                    '<span class="count">('.get_invalid_warn_count().')</span>'
                );
                break;
            case "affirm": 
                $status_label = sprintf(
                    '确认无效 %s',
                    '<span class="count">('.get_affirm_dead_url_count().')</span>'
                );
                break;
            case "revive": 
                $status_label = sprintf(
                    '反馈复活 %s',
                    '<span class="count">('.get_revive_url_count().')</span>'
                );
                break;
        }
        $status_links[$name] = io_get_edit_link( $status_args, $status_label, $class );
    }
    return $status_links;
}

/**
 * 显示此表上可用的视图列表。
 */
function io_list_views() {
    global $post_screen;
    $views = io_list_get_views();

    if ( empty( $views ) ) {
        return;
    }

    $post_screen->render_screen_reader_content( 'heading_views' );

    echo "<ul class='subsubsub'>\n";
    foreach ( $views as $class => $view ) {
        $views[ $class ] = "\t<li class='$class'>$view";
    }
    echo implode( " |</li>\n", $views ) . "</li>\n";
    echo '</ul>';
}
/**
 * 确定当前视图是否为 "全部 "视图。
 *
 * @return bool 当前视图是否为 "全部 "视图。
 */
function is_base_request() {
    global $post_screen;
    $vars = $_GET;
    unset( $vars['paged'] ); 
    if ( empty( $vars ) ) {
        return true;
    } elseif ( 1 === count( $vars ) && ! empty( $vars['post_type'] ) ) {
        return $post_screen->post_type === $vars['post_type'];
    } elseif ( 2 === count( $vars ) && ! empty( $vars['post_type'] ) && ! empty( $vars['page'] ) ) {
        return $post_screen->post_type === $vars['post_type'];
    }

    return 1 === count( $vars ) && ! empty( $vars['mode'] );
}


