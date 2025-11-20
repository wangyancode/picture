<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2022-02-20 18:28:17
 * @LastEditors: iowen
 * @LastEditTime: 2025-06-04 22:19:50
 * @FilePath: /onenav/inc/admin/functions.php
 * @Description: 
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

$functions = array(
    'sites/functions',
);

foreach ($functions as $function) {
    $path = 'inc/admin/' . $function . '.php';
    require get_theme_file_path($path);
}


function get_table_classes($added='') {
    $mode = get_user_setting( 'posts_list_mode', 'list' );

    $mode_class = esc_attr( 'table-view-' . $mode );

    return array( 'widefat', 'fixed', 'striped', $mode_class, $added );
}

/**
 * 打印屏幕的列头。
 *
 * @param string|WP_Screen $screen  屏幕钩子的名称或屏幕对象。
 * @param bool             $with_id 是否要设置ID属性。
 */
function io_print_column_headers($column_list, $with_id = true ) {
    list( $columns, $hidden, $sortable, $primary ) = $column_list;

    $current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
    $current_url = remove_query_arg( 'paged', $current_url );

    if ( isset( $_GET['orderby'] ) ) {
        $current_orderby = $_GET['orderby'];
    } else {
        $current_orderby = '';
    }

    if ( isset( $_GET['order'] ) && 'desc' === $_GET['order'] ) {
        $current_order = 'desc';
    } else {
        $current_order = 'asc';
    }

    if ( ! empty( $columns['cb'] ) ) {
        static $cb_counter = 1;
        $columns['cb']     = '<label class="screen-reader-text" for="cb-select-all-' . $cb_counter . '">' . __( 'Select All' ) . '</label>'
            . '<input id="cb-select-all-' . $cb_counter . '" type="checkbox" />';
        $cb_counter++;
    }

    foreach ( $columns as $column_key => $column_display_name ) {
        $class = array( 'manage-column', "column-$column_key" );

        if ( in_array( $column_key, $hidden, true ) ) {
            $class[] = 'hidden';
        }

        if ( 'cb' === $column_key ) {
            $class[] = 'check-column';
        } elseif ( in_array( $column_key, array( 'posts', 'comments', 'links' ), true ) ) {
            $class[] = 'num';
        }

        if ( $column_key === $primary ) {
            $class[] = 'column-primary';
        }

        if ( isset( $sortable[ $column_key ] ) ) { 
            list( $orderby, $desc_first ) = $sortable[ $column_key ];

            if ( $current_orderby === $orderby ) {
                $order = 'asc' === $current_order ? 'desc' : 'asc';

                $class[] = 'sorted';
                $class[] = $current_order;
            } else {
                $order = strtolower( $desc_first );

                if ( ! in_array( $order, array( 'desc', 'asc' ), true ) ) {
                    $order = $desc_first ? 'desc' : 'asc';
                }

                $class[] = 'sortable';
                $class[] = 'desc' === $order ? 'asc' : 'desc';
            }

            $column_display_name = sprintf(
                '<a href="%s"><span>%s</span><span class="sorting-indicator"></span></a>',
                esc_url( add_query_arg( compact( 'orderby', 'order' ), $current_url ) ),
                $column_display_name
            );
        }

        $tag   = ( 'cb' === $column_key ) ? 'td' : 'th';
        $scope = ( 'th' === $tag ) ? 'scope="col"' : '';
        $id    = $with_id ? "id='$column_key'" : '';

        if ( ! empty( $class ) ) {
            $class = "class='" . implode( ' ', $class ) . "'";
        }

        echo "<$tag $scope $id $class>$column_display_name</$tag>";
    }
} 


function io_get_column_count($column_list) {
    list ( $columns, $hidden ) = $column_list;
    $hidden                    = array_intersect( array_keys( $columns ), array_filter( $hidden ) );
    return count( $columns ) - count( $hidden );
}

/**
 * 生成一行表格内容。
 *
 * @param object|array $item 当前的项目
 */
function io_single_row_columns( $item ) {
    list( $columns, $hidden, $sortable, $primary ) = get_column_info();

    foreach ( $columns as $column_name => $column_display_name ) {
        $classes = "$column_name column-$column_name";
        if ( $primary === $column_name ) {
            $classes .= ' has-row-actions column-primary';
        }

        if ( in_array( $column_name, $hidden, true ) ) {
            $classes .= ' hidden';
        }
        
        $data = 'data-colname="' . esc_attr( wp_strip_all_tags( $column_display_name ) ) . '"';

        $attributes = "class='$classes' $data";

        if ( 'cb' === $column_name ) {
            echo '<th scope="row" class="check-column">';
            echo io_column_cb( $item );
            echo '</th>';
        } elseif ( function_exists( '_column_' . $column_name ) ) {
            echo call_user_func(
                '_column_' . $column_name ,
                $item,
                $classes,
                $data,
                $primary
            );
        } elseif ( function_exists( 'column_' . $column_name ) ) {
            echo "<td $attributes>";
            echo call_user_func(  'column_' . $column_name , $item );
            echo io_handle_row_actions( $item, $column_name, $primary );
            echo '</td>';
        } else {
            echo "<td $attributes>";
            echo io_column_default( $item, $column_name );
            echo io_handle_row_actions( $item, $column_name, $primary );
            echo '</td>';
        }
    }
}

/**
 * 显示搜索框。
 *
 * @param string $text     “提交”按钮的名称。
 * @param string $input_id 搜索框输入字段的ID属性值。
 */
function io_search_box($text, $input_id,$items){
    if (empty($_REQUEST['s']) && ! $items) {
        return;
    }

    $input_id = $input_id . '-search-input';

    if (! empty($_REQUEST['orderby'])) {
        echo '<input type="hidden" name="orderby" value="' . esc_attr($_REQUEST['orderby']) . '" />';
    }
    if (! empty($_REQUEST['order'])) {
        echo '<input type="hidden" name="order" value="' . esc_attr($_REQUEST['order']) . '" />';
    }
    if (! empty($_REQUEST['post_mime_type'])) {
        echo '<input type="hidden" name="post_mime_type" value="' . esc_attr($_REQUEST['post_mime_type']) . '" />';
    }
    if (! empty($_REQUEST['detached'])) {
        echo '<input type="hidden" name="detached" value="' . esc_attr($_REQUEST['detached']) . '" />';
    } ?>
<p class="search-box">
	<label class="screen-reader-text" for="<?php echo esc_attr($input_id); ?>"><?php echo $text; ?>:</label>
	<input type="search" id="<?php echo esc_attr($input_id); ?>" name="s" value="<?php _admin_search_query(); ?>" />
		<?php submit_button($text, '', '', false, array( 'id' => 'search-submit' )); ?>
</p>
		<?php
}

function get_bulk_actions(){

    global $post_type_object, $actions;
    $actions = array();
    if (current_user_can($post_type_object->cap->edit_posts)) {
        $actions['delete'] = __('Delete');
    }

    if (current_user_can($post_type_object->cap->edit_posts)) {
        $actions['regain'] = __('恢复');
    }

    if (current_user_can($post_type_object->cap->edit_posts)) {
        $actions['update'] = __('更新并恢复');
    }

    if (current_user_can($post_type_object->cap->edit_posts) && (!isset($_GET['sites_status']) || (isset($_GET['sites_status']) && $_GET['sites_status'] != 4))) {
        $actions['blacklist'] = __('确认失效');
    }

    if (current_user_can($post_type_object->cap->delete_posts)) {
        $actions['trash'] = __('Move to Trash');
    }

    return $actions;
}

/**
 * 显示“批量操作”下拉列表。
 *
 * @param string $which 批量操作的位置。'顶部'或'底部'。
 *                      这被指定为可选的，以便向后兼容。
 */
function io_bulk_actions($which = ''){
    global $actions ;
    if (is_null($actions )) {
        $actions  = get_bulk_actions(); 
        $two = '';
    } else {
        $two = '2';
    }

    if (empty($actions )) {
        return;
    }

    echo '<label for="bulk-action-selector-' . esc_attr($which) . '" class="screen-reader-text">' . __('Select bulk action') . '</label>';
    echo '<select name="action' . $two . '" id="bulk-action-selector-' . esc_attr($which) . "\">\n";
    echo '<option value="-1">' . __('Bulk actions') . "</option>\n";

    foreach ($actions  as $key => $value) {
        if (is_array($value)) {
            echo "\t" . '<optgroup label="' . esc_attr($key) . '">' . "\n";

            foreach ($value as $name => $title) {
                $class = ('edit' === $name) ? ' class="hide-if-no-js"' : '';

                echo "\t\t" . '<option value="' . esc_attr($name) . '"' . $class . '>' . $title . "</option>\n";
            }
            echo "\t" . "</optgroup>\n";
        } else {
            $class = ('edit' === $key) ? ' class="hide-if-no-js"' : '';

            echo "\t" . '<option value="' . esc_attr($key) . '"' . $class . '>' . $value . "</option>\n";
        }
    }

    echo "</select>\n";

    submit_button(__('Apply'), 'action', '', false, array( 'id' => "doaction$two" ));
    echo "\n";
}

function io_current_action() {
    if ( isset( $_REQUEST['filter_action'] ) && ! empty( $_REQUEST['filter_action'] ) ) {
        return false;
    }

    if ( isset( $_REQUEST['action'] ) && -1 != $_REQUEST['action'] ) {
        return $_REQUEST['action'];
    }

    return false;
}

function get_pagination_args( $args ) {
    global $pagination_args;
    $args = wp_parse_args(
        $args,
        array(
            'total_items' => 0,
            'total_pages' => 0,
            'per_page'    => 0,
        )
    );

    if ( ! $args['total_pages'] && $args['per_page'] > 0 ) {
        $args['total_pages'] = ceil( $args['total_items'] / $args['per_page'] );
    }

    // 如果页码无效且标题尚未发送，则重定向.
    if ( ! headers_sent() && ! wp_doing_ajax() && $args['total_pages'] > 0 && get_pagenum() > $args['total_pages'] ) {
        wp_redirect( add_query_arg( 'paged', $args['total_pages'] ) );
        exit;
    }
    $pagination_args = $args;
    return $args;
}

/**
 * 显示分页
 *
 * @param string $which
 */
function io_pagination($which){ 
    global $pagination_args,  $pagination ;
    if (empty($pagination_args)) {
        return;
    }

    $total_items     = $pagination_args['total_items'];
    $total_pages     = $pagination_args['total_pages'];
    $infinite_scroll = false;
    if (isset($pagination_args['infinite_scroll'])) {
        $infinite_scroll = $pagination_args['infinite_scroll'];
    }
 

    $output = '<span class="displaying-num">' . sprintf(
            /* translators: %s: Number of items. */
            _n('%s item', '%s items', $total_items),
        number_format_i18n($total_items)
    ) . '</span>';

    $current              = get_pagenum();
    $removable_query_args = wp_removable_query_args();

    $current_url = set_url_scheme('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);

    $current_url = remove_query_arg($removable_query_args, $current_url);

    $page_links = array();

    $total_pages_before = '<span class="paging-input">';
    $total_pages_after  = '</span></span>';

    $disable_first = false;
    $disable_last  = false;
    $disable_prev  = false;
    $disable_next  = false;

    if (1 == $current) {
        $disable_first = true;
        $disable_prev  = true;
    }
    if ($total_pages == $current) {
        $disable_last = true;
        $disable_next = true;
    }

    if ($disable_first) {
        $page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&laquo;</span>';
    } else {
        $page_links[] = sprintf(
            "<a class='first-page button' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
            esc_url(remove_query_arg('paged', $current_url)),
            __('First page'),
            '&laquo;'
        );
    }

    if ($disable_prev) {
        $page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&lsaquo;</span>';
    } else {
        $page_links[] = sprintf(
            "<a class='prev-page button' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
            esc_url(add_query_arg('paged', max(1, $current - 1), $current_url)),
            __('Previous page'),
            '&lsaquo;'
        );
    }

    if ('bottom' === $which) {
        $html_current_page  = $current;
        $total_pages_before = '<span class="screen-reader-text">' . __('Current Page') . '</span><span id="table-paging" class="paging-input"><span class="tablenav-paging-text">';
    } else {
        $html_current_page = sprintf(
            "%s<input class='current-page' id='current-page-selector' type='text' name='paged' value='%s' size='%d' aria-describedby='table-paging' /><span class='tablenav-paging-text'>",
            '<label for="current-page-selector" class="screen-reader-text">' . __('Current Page') . '</label>',
            $current,
            strlen($total_pages)
        );
    }
    $html_total_pages = sprintf("<span class='total-pages'>%s</span>", number_format_i18n($total_pages));
    $page_links[]     = $total_pages_before . sprintf(
            /* translators: 1: Current page, 2: Total pages. */
            _x('%1$s of %2$s', 'paging'),
        $html_current_page,
        $html_total_pages
    ) . $total_pages_after;

    if ($disable_next) {
        $page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&rsaquo;</span>';
    } else {
        $page_links[] = sprintf(
            "<a class='next-page button' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
            esc_url(add_query_arg('paged', min($total_pages, $current + 1), $current_url)),
            __('Next page'),
            '&rsaquo;'
        );
    }

    if ($disable_last) {
        $page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&raquo;</span>';
    } else {
        $page_links[] = sprintf(
            "<a class='last-page button' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
            esc_url(add_query_arg('paged', $total_pages, $current_url)),
            __('Last page'),
            '&raquo;'
        );
    }

    $pagination_links_class = 'pagination-links';
    if (! empty($infinite_scroll)) {
        $pagination_links_class .= ' hide-if-js';
    }
    $output .= "\n<span class='$pagination_links_class'>" . implode("\n", $page_links) . '</span>';

    if ($total_pages) {
        $page_class = $total_pages < 2 ? ' one-page' : '';
    } else {
        $page_class = ' no-pages';
    }
    $pagination = "<div class='tablenav-pages{$page_class}'>$output</div>";

    echo $pagination;
}

/**
 * 获取当前页码。
 *
 * @return int
 */
function get_pagenum() {
    global $pagination_args;
	$pagenum = isset( $_REQUEST['paged'] ) ? absint( $_REQUEST['paged'] ) : 0;
	if ( isset( $pagination_args['total_pages'] ) && $pagenum > $pagination_args['total_pages'] ) {
		$pagenum = $pagination_args['total_pages'];
	}
	return max( 1, $pagenum );
}
/**
 * 使用参数创建指向 edit.php 的链接。
 *
 * @param string[] $args  链接的 URL 参数的关联数组。
 * @param string   $label 链接文本.
 * @param string   $class 可选的。类属性。默认空字符串。
 * @return string 格式化的链接字符串。
 */
function io_get_edit_link( $args, $label, $class = '' ) {
    $url = add_query_arg( $args, 'edit.php' );

    $class_html   = '';
    $aria_current = '';

    if ( ! empty( $class ) ) {
        $class_html = sprintf(
            ' class="%s"',
            esc_attr( $class )
        );

        if ( 'current' === $class ) {
            $aria_current = ' aria-current="page"';
        }
    }

    return sprintf(
        '<a href="%s"%s%s>%s</a>',
        esc_url( $url ),
        $class_html,
        $aria_current,
        $label
    );
}
// 后台添加自定义CSS
function io_custom_admin_css() {
    echo '
    <style>
        #toplevel_page_home_module .wp-menu-name {
            position: relative;
        }
        #toplevel_page_home_module .wp-menu-name::after {
            content: "NEW";
            display: inline-block;
            background: #f00;
            color: #fff;
            font-size: 0.8em;
            padding: 2px 3px;
            line-height: 1;
            border-radius: 3px;
            margin-left: 3px;
        }
        /*** 古腾堡分类高度修复 ***/
        .interface-interface-skeleton__sidebar .components-panel__body>div{
            height: auto;
        }
    </style>
    ';
}
add_action('admin_head', 'io_custom_admin_css');


/**
 * 隐藏多余的用户设置项目
 * @param mixed $user
 * @return void
 */
function io_user_profile_css($user)
{

    $html = '<style>
    .user-first-name-wrap,
    .user-last-name-wrap,
    .user-admin-bar-front-wrap,
    .user-comment-shortcuts-wrap,
    .user-admin-color-wrap,
    .user-syntax-highlighting-wrap,
    .user-rich-editing-wrap,
    .user-profile-picture .description,
    .user-language-wrap
     {
        display: none
    }
    </style>';
    echo $html;

}
add_action('show_user_profile', 'io_user_profile_css');
add_action('edit_user_profile', 'io_user_profile_css');

/**
 * 后台加载js全局变量
 * @return void
 */
function io_admin_global_js()
{
    $vars = array(
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'mceCss'  => get_theme_file_uri('/assets/css/editor-style.css')
    );

    // 判断是不是编辑页
    //$screen = get_current_screen();
    //if ($screen && in_array($screen->post_type, ['post', 'app', 'book', 'sites', 'bulletin'])) {
    add_filter('io_admin_js_var', 'io_contribute_js_var', 10, 1);
    //}

    $vars = apply_filters('io_admin_js_var', $vars);

    echo '<script type="text/javascript">window.IO = ' . json_encode($vars) . ';</script>';
}
add_action('admin_footer', 'io_admin_global_js');
