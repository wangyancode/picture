<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2023-03-12 15:51:59
 * @LastEditors: iowen
 * @LastEditTime: 2024-10-24 23:58:31
 * @FilePath: /onenav/iopay/admin/functions/f-order.php
 * @Description: 
 */

class io_auto_order_list{

    private $items;
    private $column_info;
    private $per_page = 0;
    private $page_id = '';
    

    public function __construct( $items , $column_info ){
        $this->items = $items;
        $this->column_info = $column_info;
        $this->page_id = 'io_order';
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
        $id      = $item->id;
        $actions = array();

        $actions['delete'] = sprintf(
            '<a onclick="return confirm(\'确定删除吗？删除后不能恢复！\')" href="%s" aria-label="%s">%s</a>',
            wp_nonce_url(add_query_arg(array('action' => 'delete', 'id' => $id), admin_url('admin.php?page='.$this->page_id)), $this->page_id.'_action'),
            '删除' . $id,
            '删除'
        );

        return row_actions($actions);
    }
    
    public function column_status($item){
        return $item->status?'已支付':'未支付';
    }

    public function column_user($item){
        if($item->user_id)
            return get_the_author_meta('nickname',$item->user_id);
        return '游客';
    }

    public function column_price($item){
        if($item->order_price)
            return '￥'.$item->order_price;
        return '免费';
    }

    public function column_meta($item){
        if ($item->order_meta && 'auto_ad_url' == $item->order_type) {
            $data = maybe_unserialize($item->order_meta);
            $_c = '名称：'.$data['name'].'<br>';
            $_c .= '网址：'.$data['url'].'<br>';
            $_c .= '时间：'.$data['limit'].'<br>';
            return $_c;
        } elseif('pay_publish' === $item->order_type){
            $post = get_post($item->post_id);
            return '发布文章<br>'.'<a href="'.get_edit_post_link( $item->post_id ).'" target="_blank" title="查看文章">'.$post->post_title.'</a><br>';
        }
        return '-';
    }

    public function column_time($item){
        if(!empty( $item->pay_time) && '0000-00-00 00:00:00' != $item->pay_time){
            return '支付时间：<br>' . $item->pay_time;
        }
        return '创建时间：<br>'.$item->create_time;
    }

    public function column_goods($item){
        $n = iopay_get_buy_type_name($item->order_type);
        if ($item->post_id && 'pay_publish' !== $item->order_type) {
            $post = get_post($item->post_id);
            return '<a href="'.get_edit_post_link( $item->post_id ).'" target="_blank" title="查看文章">'.$post->post_title.'</a><br>' . $n;
        }
        return $n;
    }

    public function column_default($item, $column_name){
        if($item->$column_name)
            return $item->$column_name;
        return '--';
    }

    public function column_cb($item){
        $id = $item->id;
        $name = isset($item->name)?strip_tags($item->name):$id;
        return '<label class="screen-reader-text" for="cb-select-' . $id . '">' . sprintf( __( 'Select %s' ), $name ) . '</label>'
                . '<input type="checkbox" name="ids[]" value="' . $id . '" id="cb-select-' . $id . '" />';
    }

}
