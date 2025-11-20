<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2023-03-12 15:51:59
 * @LastEditors: iowen
 * @LastEditTime: 2024-05-08 19:32:38
 * @FilePath: /onenav/iopay/admin/functions/f-ad.php
 * @Description: 
 */

class io_auto_ad_list{

	private $items;
	private $column_info;
	private $per_page = 0;
    private $page_id = '';
    

    public function __construct( $items , $column_info ){
        $this->items = $items;
        $this->column_info = $column_info;
        $this->page_id = 'io_ad';
	}
    /**
     * 生成 tbody 元素。
     */
    public function display_rows_or_placeholder() {
        if ( $this->items ) { 
            foreach ($this->items as $item ) {
                echo '<tr>';
                $this->single_row_columns( $item );
                echo '</tr>';
            }
        } else {
            echo '<tr class="no-items"><td class="colspanchange" colspan="' . io_get_column_count(get_column_info()) . '">';
            echo '没有内容';
            echo '</td></tr>';
        }
    }
    /**
     * 生成一行表格内容。
     *
     * @param object|array $item 当前的项目
     */
    public function single_row_columns($item ) {
        list( $columns, $hidden, $sortable, $primary ) = $this->column_info;
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
                echo $this->column_cb($item);
                echo '</th>';
            } elseif ( method_exists( $this,  '_column_' . $column_name ) ) {
                echo call_user_func(
                    array( $this, '_column_' . $column_name ),
                    $item,
                    $classes,
                    $data,
                    $primary
                );
            } elseif ( method_exists( $this,  'column_' . $column_name ) ) {
                echo "<td $attributes>";
                echo call_user_func(  array( $this, 'column_' . $column_name ), $item );
                echo $this->handle_row_actions( $item, $column_name, $primary );
                echo '</td>';
            } else {
                echo "<td $attributes>";
                echo $this->column_default( $item, $column_name );
                echo $this->handle_row_actions( $item, $column_name, $primary );
                echo '</td>';
            }
        }
    }


    /**
     * 添加动作按钮
     * 
     * @param mixed $item
     * @param mixed $column_name
     * @param mixed $primary
     * @return string
     */
    protected function handle_row_actions($item, $column_name, $primary){
        if ($primary !== $column_name) {
            return '';
        }
        $id      = $item['id'];
        $title   = $item['name'];
        $actions = array();

        $actions['edit']   = sprintf(
            '<a href="%s" class="ajax-get-model" aria-label="%s">%s</a>',
            wp_nonce_url(add_query_arg(array('action' => 'edit', 'id' => $id, 'ajax' => 1), admin_url('admin.php?page='.$this->page_id)), $this->page_id.'_action'),
            '编辑' . $title,
            '编辑'
        );
        $actions['delete'] = sprintf(
            '<a onclick="return confirm(\'确定删除吗？删除后不能恢复！\')" href="%s" aria-label="%s">%s</a>',
            wp_nonce_url(add_query_arg(array('action' => 'delete', 'id' => $id), admin_url('admin.php?page='.$this->page_id)), $this->page_id.'_action'),
            '删除' . $title,
            '删除'
        );
        $_check = sprintf(
            '<a href="%s" aria-label="%s">%s</a>',
            wp_nonce_url(add_query_arg(array('action' => 'check', 'id' => $id), admin_url('admin.php?page='.$this->page_id)), $this->page_id.'_action'),
            '通过审核' . $title,
            '通过审核'
        );
        $_uncheck = sprintf(
            '<a href="%s" aria-label="%s" style="color:#e42f2f">%s</a>',
            wp_nonce_url(add_query_arg(array('action' => 'uncheck', 'id' => $id), admin_url('admin.php?page='.$this->page_id)), $this->page_id.'_action'),
            '驳回' . $title,
            '驳回'
        );
        if ( $item['status'] && (!isset($item['check']) || !$item['check'])) {
            $actions['check']   = $_check;
            $actions['uncheck'] = $_uncheck;
        }
        if(2==$item['check']){
            $actions['check'] = $_check;
        }
        if(1==$item['check']){
            $actions['uncheck'] = $_uncheck;
        }
        return row_actions($actions);
    }

	public function column_loc($item){
		return iopay_get_auto_loc_name($item['loc']);
	}
	public function column_status($item){
        $_t = $item['status'] ? '<span style="color:#38b61e">已支付</span>' : '未支付';
        if(empty($item['time']) && $item['status']){
            $_t = '<span style="color:#e321f0">VIP</span>';
        }
		return $_t;
	}
	public function column_check($item){
        if (isset($item['check'])) {
            switch ($item['check']) {
                case 1:
                    $_t = '已审核';
                    break;
                
                case 2:
                    $_t = '<span style="color:#e42f2f">驳回</span>';
                    break;
                
                default:
                    $_t = '未审核';
                    break;
            }
        }
        return $_t;
	}
    public function column_nofollow($item){
        if (isset($item['nofollow'])) {
            return $item['nofollow'] ? '<span style="color:#38b61e">启用</span>' : '禁用';
        }
        return '禁用';
	}
    //time
    public function column_time($item){
        if (!empty($item['time'])) {
            return $item['time'];
        }
        return '手动添加';
	}
	public function column_expiry($item){
        if ($item['check']) {
            if(strtotime($item['expiry']) < (current_time('timestamp'))){
                return '<span title="'.$item['expiry'].'" style="color:#e42f2f">已过期</span>';
            }
            return $item['expiry'];
        }
        return '-';
	}
	public function column_other($item){
        $_limit = $item['limit'] ? $item['limit'] . iopay_get_auto_unit_name(io_get_option('auto_ad_config', 'hour', 'unit')) : '-';
        $term   = '时长：' . $_limit;
        if(isset($item['user_id'])){
            $term .= '<br>用户：' . ($item['user_id'] ? get_userdata($item['user_id'])->display_name : '游客');
        }
        if (isset($item['contact'])) {
            $term .= '<br>邮箱：' . ($item['contact'] ?: '-');
        }
        return $term;
	}
	public function column_url($item){
        return '<a href="'.$item['url'].'" target="_blank" title="查看链接">'.$item['url'].'</a>';
	}

	public function column_default($item, $column_name){
		return $item[$column_name];
	}

	public function column_cb($item){
        $id = $item['id'];
		$name = isset($item['name'])?strip_tags($item['name']):$id;
		return '<label class="screen-reader-text" for="cb-select-' . $id . '">' . sprintf( __( 'Select %s' ), $name ) . '</label>'
				. '<input type="checkbox" name="ids[]" value="' . $id . '" id="cb-select-' . $id . '" />';
	}

}
