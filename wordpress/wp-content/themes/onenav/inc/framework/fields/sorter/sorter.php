<?php if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access directly.
/**
 *
 * Field: sorter
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
if ( ! class_exists( 'IOCF_Field_sorter' ) ) {
  class IOCF_Field_sorter extends IOCF_Fields {

    public function __construct( $field, $value = '', $unique = '', $where = '', $parent = '' ) {
      parent::__construct( $field, $value, $unique, $where, $parent );
    }

    public function render() {

      $args = wp_parse_args( $this->field, array(
        'disabled'       => true,
        'enabled_title'  => esc_html__( 'Enabled', 'csf' ),
        'disabled_title' => esc_html__( 'Disabled', 'csf' ),
      ) );

      echo $this->field_before();

      $this->value      = ( ! empty( $this->value ) ) ? $this->value : $this->field['default'];
      $enabled_options  = ( ! empty( $this->value['enabled'] ) ) ? $this->value['enabled'] : array();
      $disabled_options = ( ! empty( $this->value['disabled'] ) ) ? $this->value['disabled'] : array();

      echo '<div class="csf-sorter" data-depend-id="'. esc_attr( $this->field['id'] ) .'"></div>';
      
      // iowen.cn 
      $new_all    = '';
      $is_enabled = false; // false 添加到禁用区 true 添加到启用区
      if( isset($this->field['refresh']) && $this->field['refresh'] ){
        $options_type  = isset( $this->field['options_id'] ) ? $this->field['options_id'] : 'term';
        $is_enabled    = isset($this->field['is_enabled']) ? $this->field['is_enabled'] : true;
        $new_all       = get_sorter_options($options_type); 
      }
      if($new_all != ''){
        $old_all  = array_merge($disabled_options,$enabled_options);
        $keep_all = array_intersect_key($old_all,$new_all);
        $enabled_options  = array_intersect_key($enabled_options, $keep_all); //差集,去除删除的项
        $disabled_options = array_intersect_key($disabled_options, $keep_all); //差集,去除删除的项
        $old_all  = array_merge($disabled_options,$enabled_options);//新的旧选项（去除删除项）
        $new_options = array_diff_key($new_all, $old_all); //获取新增的项 
        $add_options = array_keys($new_options); //获取新增的项键名 
        if($is_enabled){
          $enabled_options  = array_merge($enabled_options, $new_options);
        }else{
          $disabled_options = array_merge($disabled_options, $new_options);
        } 
      }
      // ------------------------------------------------------------------------

      echo ( $args['disabled'] ) ? '<div class="csf-modules">' : '';

      echo ( ! empty( $args['enabled_title'] ) ) ? '<div class="csf-sorter-title">'. esc_attr( $args['enabled_title'] ) .'</div>' : '';
      echo '<ul class="csf-enabled">';
      if ( ! empty( $enabled_options ) ) {
        foreach ( $enabled_options as $key => $value ) {
          // iowen.cn 追加新增的项到列表，并且标记 增加if判断
          if(isset($add_options) && $is_enabled && in_array($key,$add_options))
            echo '<li style="background: rgb(208, 255, 173);"><input type="hidden" name="'. $this->field_name( '[enabled]['. $key .']' ) .'" value="'. $value .'"/><label>'. $value .'</label></li>';
          else
            echo '<li><input type="hidden" name="'. esc_attr( $this->field_name( '[enabled]['. $key .']' ) ) .'" value="'. esc_attr( $value ) .'"/><label>'. esc_attr( $value ) .'</label></li>';
        }
      }
      echo '</ul>';

      // Check for hide/show disabled section
      if ( $args['disabled'] ) {

        echo '</div>';

        echo '<div class="csf-modules">';
        echo ( ! empty( $args['disabled_title'] ) ) ? '<div class="csf-sorter-title">'. esc_attr( $args['disabled_title'] ) .'</div>' : '';
        echo '<ul class="csf-disabled">';
        if ( ! empty( $disabled_options ) ) {
          foreach ( $disabled_options as $key => $value ) {
            // iowen.cn 追加新增的项到列表，并且标记 增加if判断
            if(isset($add_options) && !$is_enabled && in_array($key,$add_options))
              echo '<li style="background: rgb(208, 255, 173);"><input type="hidden" name="'. $this->field_name( '[disabled]['. $key .']' ) .'" value="'. $value .'"/><label>'. $value .'</label></li>';
            else
              echo '<li><input type="hidden" name="'. esc_attr( $this->field_name( '[disabled]['. $key .']' ) ) .'" value="'. esc_attr( $value ) .'"/><label>'. esc_attr( $value ) .'</label></li>';
          }
        }
        echo '</ul>';
        echo '</div>';

      }


      echo $this->field_after();

    }

    public function enqueue() {

      if ( ! wp_script_is( 'jquery-ui-sortable' ) ) {
        wp_enqueue_script( 'jquery-ui-sortable' );
      }

    }

  }
}
