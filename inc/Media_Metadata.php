<?php

namespace Stanford\Homesite;

/**
 * Media_Metadata
 * Specify additional metadata for Media Library items.
 * Provide a public API for accessing the metadata.
 *
 * @package Stanford\Homesite
 */
class Media_Metadata {

  /** @var Panels singleton instance of this class */
  protected static $instance = null;

  /** @var string $ver class version */
  protected $version = '17.0.0';


  /******************************************************************************
   *
   * Public API
   *
   ******************************************************************************/

  /**
   * Get the media credit string, optionally with link to Shutterstock
   *
   * @param  int $id - media item we want the credit for
   * @return string
   */
  public static function get_media_credit( $id ) {
    $media_source = trim( get_post_meta($id, "_media_source", TRUE) );
    $media_credit = trim( get_post_meta($id, "_media_credit", TRUE) );

    switch ( $media_source ) {
      case 'lacicero':
        $credit =  "L.A. Cicero";
        break;
      default:
        $credit = $media_credit;
        break;
    }

    return $credit;
  }


  /******************************************************************************
   *
   * Class setup
   *
   ******************************************************************************/

  /**
   * Create singleton instance, if necessary.
   */
  public static function init() {
    if ( !is_a( self::$instance, __CLASS__ ) ) {
      self::$instance = new Media_Metadata();
    }
    return self::$instance;
  }

  /**
   * Called once when singleton instance is created.
   * Declared as protected to prevent using new to instantiate instances other than the singleton.
   */
  protected function __construct() {
    add_filter('attachment_fields_to_edit', array($this, 'filter_attachment_fields_to_edit'), 5, 2);
    add_filter('attachment_fields_to_save', array($this, 'filter_attachment_fields_to_save'), 5, 2);
  }


  /******************************************************************************
   *
   * Internals
   *
   ******************************************************************************/

  /**
   * Emit markup for additional metadata on media library items.
   * Invoked via the attachment_fields_to_edit filter.
   * @see https://codex.wordpress.org/Plugin_API/Filter_Reference/attachment_fields_to_edit
   *
   * @param array $form_fields
   * @param WP_Post $post
   * @return array
   */
  public function filter_attachment_fields_to_edit($form_fields, $post) {
    $screen = get_current_screen();
    if(!empty($screen) && $screen->base == 'post' && $screen->id == 'attachment' && $screen->post_type == 'attachment') {
      ?>
      <style type="text/css">
        .compat-attachment-fields,
        .compat-field-media_credit input{
          width:100%;
        }
        .compat-field-media_source th,
        .compat-field-media_credit th{
          vertical-align: top;
        }
        .compat-field-media_source input[type=radio]{
          margin: 3px 6px 5px 3px;
        }
      </style>
      <?php
    }

    $media_source   = get_post_meta($post->ID, "_media_source", TRUE);
    $media_credit   = get_post_meta($post->ID, "_media_credit", TRUE);
    if ( empty( $media_source ) ) $media_source = "other";

    $source_html = '';
    $sources = [
        'lacicero' => 'L.A. Cicero'
      , 'other'    => 'Other'
    ];
    foreach ( $sources as $source => $source_desc ) {
      $source_html .= "<input type=\"radio\" name=\"attachments[{$post->ID}][media_source]\" value=\"{$source}\"";
      $source_html .= checked($media_source, $source, FALSE);
      $source_html .= "/>{$source_desc}";
      if ( $source != 'other') {
        $source_html .= "<br/>";
      }
      $source_html .= "\n";
    }

    $form_fields["media_source"] = array(
        "label" => __('Media Source', 'stanford_text_domain')
      , "input" => "html"
      , "html"  => $source_html
    );
    $form_fields["media_credit"] = array(
        "label" => __('Media Credit', 'stanford_text_domain')
      , "input" => "text"
      , "value" => $media_credit
    );
    return $form_fields;
  }

  /**
   * Save custom metadata for media library items.
   * Invoked via the attachment_fields_to_save filter.
   * @see https://codex.wordpress.org/Plugin_API/Filter_Reference/attachment_fields_to_save
   *
   * @param array $post
   * @param array $attachment
   * @return array
   */
  public function filter_attachment_fields_to_save($post, $attachment) {
    if ( isset( $attachment['media_source'] ) ){
      update_post_meta($post[ 'ID' ], '_media_source', sanitize_text_field( trim( $attachment['media_source'] ) ) );
    }
    if ( isset( $attachment['media_credit'] ) ){
      update_post_meta($post[ 'ID' ], '_media_credit', sanitize_text_field( trim( $attachment['media_credit'] ) ) );
    }
    return $post;
  }

}