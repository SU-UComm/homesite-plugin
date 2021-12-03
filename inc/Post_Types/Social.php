<?php

namespace Stanford\Homesite\Post_Types;

/**
 * Class Social
 *
 * @package Stanford\Homesite\Post_Types
 */
class Social {

  /** @var string post type slug */
  const NAME         = 'social';

  /** @var  string metadata prefix for CMB */
  const META_PREFIX  = '_stanford_social_';


  public function __construct( ) {

    add_action( 'init',            [ $this, 'register_post_type' ] );
    add_action( 'cmb2_admin_init', [ $this, 'add_metaboxes'      ] );

    add_filter( 'manage_' . self::NAME . '_posts_columns',       [ $this, 'post_columns' ] );
    add_action( 'manage_' . self::NAME . '_posts_custom_column', [ $this, 'post_column_content' ], 10, 2);
    add_action( 'bulk_edit_custom_box',        [ $this, 'bulk_quick_edit_custom_box' ], 10, 2 );
    add_action( 'quick_edit_custom_box',       [ $this, 'bulk_quick_edit_custom_box' ], 10, 2 );
    add_action( 'save_post',                   [ $this, 'quick_edit_save'], 10, 2 );
    add_action( 'wp_ajax_hs17_bulk_edit_save', [ $this, 'bulk_edit_save' ] );

    add_filter( 'panels_query_post_type_options', [ $this, 'add_to_panels_post_picker' ], 15, 3 );

  }

  /**
   * Register our post type
   */
  public function register_post_type() {

    $singular_name = "Social Post";
    $plural_name   = "Social Posts";

    $labels = array(
        'name'                  => _x( "{$plural_name}", 'Post Type General Name', 'stanford-text-domain' ),
        'singular_name'         => _x( "{$singular_name}", 'Post Type Singular Name', 'stanford-text-domain' ),
        'menu_name'             => __( "{$plural_name}", 'stanford-text-domain' ),
        'name_admin_bar'        => __( "{$singular_name}", 'stanford-text-domain' ),
        'archives'              => __( "{$plural_name}", 'stanford-text-domain' ),
        'attributes'            => __( "{$singular_name} Attributes", 'stanford-text-domain' ),
        'parent_item_colon'     => __( "Parent Item:", 'stanford-text-domain' ),
        'all_items'             => __( "All {$plural_name}", 'stanford-text-domain' ),
        'add_new_item'          => __( "Add {$singular_name}", 'stanford-text-domain' ),
        'add_new'               => __( "Add {$singular_name}", 'stanford-text-domain' ),
        'new_item'              => __( "New {$singular_name}", 'stanford-text-domain' ),
        'edit_item'             => __( "Edit {$singular_name}", 'stanford-text-domain' ),
        'update_item'           => __( "Update {$singular_name}", 'stanford-text-domain' ),
        'view_item'             => __( "View {$singular_name}", 'stanford-text-domain' ),
        'view_items'            => __( "View {$plural_name}", 'stanford-text-domain' ),
        'search_items'          => __( "Search {$plural_name}", 'stanford-text-domain' ),
        'not_found'             => __( "Not found", 'stanford-text-domain' ),
        'not_found_in_trash'    => __( "Not found in Trash", 'stanford-text-domain' ),
        'featured_image'        => __( "Featured Image", 'stanford-text-domain' ),
        'set_featured_image'    => __( "Set featured image", 'stanford-text-domain' ),
        'remove_featured_image' => __( "Remove featured image", 'stanford-text-domain' ),
        'use_featured_image'    => __( "Use as featured image", 'stanford-text-domain' ),
        'insert_into_item'      => __( "Insert into {$singular_name}", 'stanford-text-domain' ),
        'uploaded_to_this_item' => __( "Uploaded to this {$singular_name}", 'stanford-text-domain' ),
        'items_list'            => __( "{$plural_name} list", 'stanford-text-domain' ),
        'items_list_navigation' => __( "{$plural_name} list navigation", 'stanford-text-domain' ),
        'filter_items_list'     => __( "Filter {$plural_name} list", 'stanford-text-domain' ),
    );
    $args = array(
        'label'                 => __( "{$plural_name}", 'stanford-text-domain' ),
        'description'           => __( "", 'stanford-text-domain' ),
        'labels'                => $labels,
        'supports'              => [ 'title', 'editor', 'thumbnail', 'revisions' ],
        'taxonomies'            => [],
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 6,
        'menu_icon'             => 'dashicons-format-status',
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => false,
        'can_export'            => true,
        'has_archive'           => true,
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        'capability_type'       => 'page',
        'show_in_rest'          => true,
    );
    register_post_type( self::NAME, $args );
  }

  /**
   * Allow items of this type to be selected in panel-builder's post picker field
   * Invoked via the 'panels_query_post_type_options' filter
   *
   * @var array $post_types
   * @var \ModularContent\Fields\Field $field
   */
  public function add_to_panels_post_picker( $post_types, $field  ) {
    $post_types[ self::NAME ] = get_post_type_object( self::NAME );
    return $post_types;
  }

  /**
   * Add CMB2 metabox to teaser edit pages. Metabox contains the following fields:
   *   + url
   *   + source
   * Invoked via the cmb2_admin_init action
   */
  public function add_metaboxes() {
    $metabox = new_cmb2_box( [
        'id'           => 'Source'
      , 'title'        => __( 'Source', 'stanford-text-domain' )
      , 'object_types' => [ self::NAME ]
      , 'context'      => 'normal'
      , 'priority'     => 'low'
      , 'show_names'   => TRUE
    ] );

    $url_field = $metabox->add_field( [
        'id'         => self::META_PREFIX . 'url'
      , 'name'       => __( 'URL', 'stanford-text-domain' )
      , 'desc'       => __( 'Link to original social post', 'stanford-text-domain' )
      , 'type'       => 'text_url'
      , 'attributes' => [
            'required'    => 'required'
        ]
    ] );

    return;
  }

  /**
   * Display the social post's url on the admin list page
   * Inovked via 'the manage_' . self::NAME . '_posts_columns' filter.
   *
   * @param  array $columns - WordPress's idea of what the columns should be
   * @return array our idea of what the columns should be
   */
  public function post_columns ( $columns ) {
    $new_columns = [];
    foreach ( $columns as $key => $value ) {
      $new_columns[ $key ] = $value;
      if ( $key == 'title' ) {
        $new_columns[ 'url' ] = __( 'URL', 'stanford-text-domain' );
      }
    }
    return $new_columns;
  }

  /**
   * Display content in custom columns
   *
   * @param string  $column_name
   * @param integer $post_id
   */
  public function post_column_content( $column_name, $post_id ) {
    switch ( $column_name ) {
      case 'url':
        $external_url = esc_url_raw( get_post_meta( $post_id, self::META_PREFIX . 'url' , TRUE ) );
        echo "<a href='{$external_url}' target='_blank'>{$external_url}</a>";
        break;
    };
  }

  /**
   * Add a custom box for the URL field to bulk and quick edit
   * Invoked via the bulk_edit_custom_box and quick_edit_custom_box actions
   *
   * @param string $column    name of the column being edited
   * @param string $post_type type of post being edited
   */
  public function bulk_quick_edit_custom_box( $column, $post_type ) {
    if ( $post_type == self::NAME && $column == 'url' ) {
?>
<fieldset class="inline-edit-col-left">
  <div class="inline-edit-col">
    <label>
      <span class="title">URL</span>
      <span class="input-text-wrap"><input type="text" value="" name="url"></span>
    </label>
  </div>
</fieldset>
<?php
    }
  }

  /**
   * Save the URL field when edited with quick edit
   * Invoked via the save_post action
   *
   * @param int      $post_id
   * @param \WP_Post $post
   */
  public function quick_edit_save( $post_id, $post ) {
    // if no POST data, there's nothing to do
    if ( empty( $_POST ) ) return;
    // verify quick edit nonce
    if ( isset( $_POST[ '_inline_edit' ] ) && !wp_verify_nonce( $_POST[ '_inline_edit' ], 'inlineeditnonce' ) ) return;
    // don't save for autosave
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    // dont save for revisions
    if ( isset( $post->post_type ) && $post->post_type == 'revision' ) return;

    // if we're, actually save the data
    if ( $post->post_type == self::NAME ) {
      if ( isset( $_POST[ 'url' ] ) ) {
        update_post_meta( $post_id, self::META_PREFIX . 'url', sanitize_text_field( $_POST[ 'url' ] ) );
      }
    }
  }

  /**
   * Save the URL field when edited with bulk edit
   * Invoked via the wp_ajax_hs17_bulk_edit_save action, which is triggered by AJAX from our js
   */
  public function bulk_edit_save() {
    $post_ids = ( !empty( @$_POST[ 'post_ids' ] ) ) ? $_POST[ 'post_ids' ] : NULL;

    if ( is_array( $post_ids ) ) {
      // update all the specified posts with the same value
      foreach( $post_ids as $post_id ) {
        if ( isset( $_POST[ 'url' ] ) && !empty( $_POST[ 'url' ] ) ) {
          update_post_meta( $post_id, self::META_PREFIX . 'url', sanitize_text_field( $_POST[ 'url' ] ) );
        }
      }
    }
  }

  /**
   * Return the correct index to use in the array returned by get_post_meta() to retrieve
   * the specified field.
   *
   * @param string $field to be retrieved
   *
   * @return string index
   */
  static public function get_field_id( $field ) {
    return self::META_PREFIX . $field;
  }

}
