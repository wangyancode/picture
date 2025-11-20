<?php if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access directly.
/**
 *
 * Field: code_editor
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
if ( ! class_exists( 'IOCF_Field_code_editor' ) ) {
  class IOCF_Field_code_editor extends IOCF_Fields {

    public $version = '5.65.17';//'6.65.7';
    public $cdn_url =  'https://cdn.iocdn.cc/npm/codemirror@';// 'https://cdn.staticfile.net/codemirror/'; // ---- iowen.cn

    public function __construct( $field, $value = '', $unique = '', $where = '', $parent = '' ) {
      //$this->cdn_url = IOCF::include_plugin_url('fields/code_editor/');
      parent::__construct( $field, $value, $unique, $where, $parent );
    }

    public function render() {

      $default_settings = array(
        'tabSize'       => 2,
        'lineNumbers'   => true,
        'theme'         => 'default',
        'mode'          => 'htmlmixed',
        'cdnURL'        => $this->cdn_url . $this->version,
      );

      $settings = ( ! empty( $this->field['settings'] ) ) ? $this->field['settings'] : array();
      $settings = wp_parse_args( $settings, $default_settings );

      echo (empty($this->field['title'])) ? '<div class="io--code-editor">' : '';
      echo $this->field_before();
      echo '<textarea name="'. esc_attr( $this->field_name() ) .'"'. $this->field_attributes() .' data-config="'. esc_attr( json_encode( $settings ) ) .'">'. $this->value .'</textarea>';
      echo $this->field_after();
      echo (empty($this->field['title'])) ? '</div>' : '';

    }

    public function enqueue() {

      $page = ( ! empty( $_GET[ 'page' ] ) ) ? sanitize_text_field( wp_unslash( $_GET[ 'page' ] ) ) : '';

      // Do not loads CodeMirror in revslider page.
      if ( in_array( $page, array( 'revslider' ) ) ) { return; }

      if ( ! wp_script_is( 'csf-codemirror' ) ) {
        wp_enqueue_script( 'csf-codemirror', esc_url( $this->cdn_url . $this->version .'/lib/codemirror.min.js' ), array( 'csf' ), $this->version, true );// ---- iowen.cn
        wp_enqueue_script( 'csf-codemirror-loadmode', esc_url( $this->cdn_url . $this->version .'/addon/mode/loadmode.min.js' ), array( 'csf-codemirror' ), $this->version, true );
      }

      if ( ! wp_style_is( 'csf-codemirror' ) ) {
        wp_enqueue_style( 'csf-codemirror', esc_url( $this->cdn_url . $this->version .'/lib/codemirror.min.css' ), array(), $this->version );// ---- iowen.cn
      }

    }

  }
}
