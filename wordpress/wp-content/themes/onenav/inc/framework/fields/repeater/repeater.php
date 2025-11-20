<?php if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access directly.
/**
 *
 * Field: repeater
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
if ( ! class_exists( 'IOCF_Field_repeater' ) ) {
  class IOCF_Field_repeater extends IOCF_Fields {

    public function __construct( $field, $value = '', $unique = '', $where = '', $parent = '' ) {
      parent::__construct( $field, $value, $unique, $where, $parent );
    }

    public function render() {

      $args = wp_parse_args( $this->field, array(
        'max'          => 0,
        'min'          => 0,
        'button_title' => '<i class="fas fa-plus-circle"></i>',
        'accordion_title_number' => false,// iowen 增加序号
      ) );

      $title_number    = ( ! empty( $args['accordion_title_number'] ) ) ? true : false;// iowen 增加序号

      if ( preg_match( '/'. preg_quote( '['. $this->field['id'] .']' ) .'/', $this->unique ) ) {

        echo '<div class="csf-notice csf-notice-danger">'. esc_html__( 'Error: Field ID conflict.', 'csf' ) .'</div>';

      } else {

        echo $this->field_before();

        echo '<div class="csf-repeater-item csf-repeater-hidden" data-depend-id="'. esc_attr( $this->field['id'] ) .'">';
        echo ( $title_number ) ? '<div class="csf-repeater-left"><span class="csf-repeater-title-number"></span></div>' : '';// iowen 增加序号
        echo '<div class="csf-repeater-content">';
        foreach ( $this->field['fields'] as $field ) {

          $field_default = ( isset( $field['default'] ) ) ? $field['default'] : '';
          $field_unique  = ( ! empty( $this->unique ) ) ? $this->unique .'['. $this->field['id'] .'][0]' : $this->field['id'] .'[0]';

          IOCF::field( $field, $field_default, '___'. $field_unique, 'field/repeater' );

        }
        echo '</div>';
        echo '<div class="csf-repeater-helper">';
        echo '<div class="csf-repeater-helper-inner">';
        echo '<i class="csf-repeater-sort fas fa-arrows-alt"></i>';
        echo '<i class="csf-repeater-clone far fa-clone"></i>';
        echo '<i class="csf-repeater-remove csf-confirm fas fa-times" data-confirm="'. esc_html__( 'Are you sure to delete this item?', 'csf' ) .'"></i>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        // iowen 增加序号,按钮上增加 data-title-number="'. esc_attr( $title_number ) .'"
        echo '<div class="csf-repeater-wrapper csf-data-wrapper" data-field-id="['. esc_attr( $this->field['id'] ) .']" data-title-number="'. esc_attr( $title_number ) .'" data-max="'. esc_attr( $args['max'] ) .'" data-min="'. esc_attr( $args['min'] ) .'">';

        if ( ! empty( $this->value ) && is_array( $this->value ) ) {

          $num = 0;

          foreach ( $this->value as $key => $value ) {

            echo '<div class="csf-repeater-item">';
            echo ( $title_number ) ? '<div class="csf-repeater-left"><span class="csf-repeater-title-number">'. esc_attr( $num+1 ) .'</span></div>' : '';// iowen 增加序号
            echo '<div class="csf-repeater-content">';
            foreach ( $this->field['fields'] as $field ) {

              $field_unique = ( ! empty( $this->unique ) ) ? $this->unique .'['. $this->field['id'] .']['. $num .']' : $this->field['id'] .'['. $num .']';
              $field_value  = ( isset( $field['id'] ) && isset( $this->value[$key][$field['id']] ) ) ? $this->value[$key][$field['id']] : '';

              IOCF::field( $field, $field_value, $field_unique, 'field/repeater' );

            }
            echo '</div>';
            echo '<div class="csf-repeater-helper">';
            echo '<div class="csf-repeater-helper-inner">';
            echo '<i class="csf-repeater-sort fas fa-arrows-alt"></i>';
            echo '<i class="csf-repeater-clone far fa-clone"></i>';
            echo '<i class="csf-repeater-remove csf-confirm fas fa-times" data-confirm="'. esc_html__( 'Are you sure to delete this item?', 'csf' ) .'"></i>';
            echo '</div>';
            echo '</div>';
            echo '</div>';

            $num++;

          }

        }

        echo '</div>';

        echo '<div class="csf-repeater-alert csf-repeater-max">'. esc_html__( 'You cannot add more.', 'csf' ) .'</div>';
        echo '<div class="csf-repeater-alert csf-repeater-min">'. esc_html__( 'You cannot remove more.', 'csf' ) .'</div>';
        echo '<a href="#" class="button button-primary csf-repeater-add">'. $args['button_title'] .'</a>';

        echo $this->field_after();

      }

    }

    public function enqueue() {

      if ( ! wp_script_is( 'jquery-ui-sortable' ) ) {
        wp_enqueue_script( 'jquery-ui-sortable' );
      }

    }

  }
}
