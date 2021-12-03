<?php

namespace Stanford\Homesite\Post_Types;

/**
 * Class AtoZ
 *
 * @package Stanford\Homesite\Post_Types
 */
class AtoZ {

  /** @var string post type slug */
  const NAME         = 'atoz';

  /** @var string taxonomy slug */
  const TAXONOMY     = 'list';

  /** @var  string metadata prefix for CMB */
  const META_PREFIX  = '_stanford_website_';


  public function __construct( ) {

    add_action( 'init',            [ $this, 'register_post_type' ] );
    add_action( 'init',            [ $this, 'register_taxonomy'  ] );
    add_action( 'cmb2_admin_init', [ $this, 'add_metaboxes'      ] );
    add_action( 'pre_get_posts',   [ $this, 'post_order'         ] );
    add_filter( 'post_type_link',  [ $this, 'post_type_link'     ], 99, 4 ); // only called for custom post types

    add_filter( 'manage_' . self::NAME . '_posts_columns',       [ $this, 'post_columns' ] );
    add_action( 'manage_' . self::NAME . '_posts_custom_column', [ $this, 'post_column_content' ], 10, 2);
    add_action( 'bulk_edit_custom_box',        [ $this, 'bulk_quick_edit_custom_box' ], 10, 2 );
    add_action( 'quick_edit_custom_box',       [ $this, 'bulk_quick_edit_custom_box' ], 10, 2 );
    add_action( 'save_post',                   [ $this, 'quick_edit_save'], 10, 2 );
    add_action( 'wp_ajax_hs17_bulk_edit_save', [ $this, 'bulk_edit_save' ] );

  }

  /**
   * Register our post type
   */
  public function register_post_type() {

    $singular_name = "Website";
    $plural_name   = "Websites";

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
        'label'                 => __( "List Item", 'stanford-text-domain' ),
        'description'           => __( "", 'stanford-text-domain' ),
        'labels'                => $labels,
        'supports'              => [ 'title', 'revisions' ],
        'taxonomies'            => [ self::TAXONOMY ],
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 25,
        'menu_icon'             => 'dashicons-admin-links',
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

  public function register_taxonomy() {

    $singular_name = "List";
    $plural_name   = "Lists";

    $labels = array(
        'name'                       => _x( "{$plural_name}", 'Taxonomy General Name', 'text_domain' ),
        'singular_name'              => _x( "{$singular_name}", 'Taxonomy Singular Name', 'text_domain' ),
        'menu_name'                  => __( "{$plural_name}", 'text_domain' ),
        'all_items'                  => __( "All {$plural_name}", 'text_domain' ),
        'parent_item'                => __( "Parent {$singular_name}", 'text_domain' ),
        'parent_item_colon'          => __( "Parent {$singular_name}:", 'text_domain' ),
        'new_item_name'              => __( "New {$singular_name}", 'text_domain' ),
        'add_new_item'               => __( "Add new {$singular_name}", 'text_domain' ),
        'edit_item'                  => __( "Edit {$singular_name}", 'text_domain' ),
        'update_item'                => __( "Update {$singular_name}", 'text_domain' ),
        'view_item'                  => __( "View {$singular_name}", 'text_domain' ),
        'separate_items_with_commas' => __( "Separate lists with commas", 'text_domain' ),
        'add_or_remove_items'        => __( "Add or remove lists", 'text_domain' ),
        'choose_from_most_used'      => __( "Choose from the most used", 'text_domain' ),
        'popular_items'              => __( "Popular lists", 'text_domain' ),
        'search_items'               => __( "Search lists", 'text_domain' ),
        'not_found'                  => __( "Not Found", 'text_domain' ),
        'no_terms'                   => __( "No lists", 'text_domain' ),
        'items_list'                 => __( "{$plural_name} list", 'text_domain' ),
        'items_list_navigation'      => __( "{$plural_name} list navigation", 'text_domain' ),
    );
    $args = array(
        'labels'                     => $labels,
        'hierarchical'               => true,
        'public'                     => true,
        'show_ui'                    => true,
        'show_admin_column'          => true,
        'show_in_nav_menus'          => false,
        'show_tagcloud'              => false,
        'show_in_rest'               => true,
    );
    register_taxonomy( self::TAXONOMY, [ self::NAME ], $args );

  }

  /**
   * Add CMB2 metabox to teaser edit pages. Metabox contains the following fields:
   *   + url
   *   + source
   * Invoked via the cmb2_admin_init action
   */
  public function add_metaboxes() {
    $metabox = new_cmb2_box( [
        'id'           => 'website'
      , 'title'        => __( 'Website', 'stanford-text-domain' )
      , 'object_types' => [ self::NAME ]
      , 'context'      => 'normal'
      , 'priority'     => 'low'
      , 'show_names'   => TRUE
    ] );

    $metabox->add_field( [
        'id'         => self::META_PREFIX . 'url'
      , 'name'       => __( 'URL', 'stanford-text-domain' )
      , 'desc'       => __( 'URL of website', 'stanford-text-domain' )
      , 'type'       => 'text_url'
      , 'attributes' => [
            'required'    => 'required'
        ]
    ] );

    $metabox->add_field( [
        'id'         => self::META_PREFIX . 'has-children'
      , 'name'       => __( 'Does this site have child sites?', 'stanford-text-domain' )
      , 'desc'       => __( 'Top level academic departments may have child sites. If this is a top level academic department, select Yes.', 'stanford-text-domain' )
      , 'type'       => 'radio_inline'
      , 'options'    => [
            'no'     => 'No'
          , 'yes'    => 'Yes'
        ]
      , 'default'    => 'no'
    ] );

  }

  /**
   * Display websites in alpha order
   * Invoked via the pre_get_posts action.
   *
   * @param \WP_Query $query
   */
  public function post_order( $query ) {
    if ( ( strpos( $_SERVER[ 'SCRIPT_NAME' ], '/wp-admin/edit.php' ) !== FALSE && $query->query_vars[ 'post_type' ] == self::NAME )
         || $query->is_tax( self::TAXONOMY )
         || $query->is_post_type_archive( self::NAME )
      ) {
      if ( empty( $query->get( 'orderby' ) ) ) {
        $query->set( 'orderby', 'title' );
        $query->set( 'order', 'ASC' );
      }
      if ( !is_admin() ) { // no pagination on the front-end
        $query->set( 'post_type',     self::NAME );
        $query->set( 'posts_per_page', -1 );
      }
    }
  }

  /**
   * Display the website's url on the admin list page
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
   * Make websites link directly to the external source.
   * Invoked via the post_type_link filter, which is called for custom post types
   *
   * @param string  $post_link - proposed url for post
   * @param WP_Post $post - post we want the link for
   * @param boolean $leavename - whether or not to retain permalink template tags
   * @param boolean $sample - is it a sample permalink?
   *
   * @return string
   */
  public function post_type_link( $post_link, $post, $leavename, $sample ) {
    if ( $post->post_type == self::NAME ) {
      $url = get_post_meta( $post->ID, self::META_PREFIX . 'url' , TRUE );
    }

    return empty( $url ) ? $post_link : esc_url_raw( $url, [ 'http', 'https' ] );
  }

}
