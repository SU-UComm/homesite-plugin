<?php

namespace Stanford\Homesite\Post_Types;

/**
 * Class News
 *
 * @package Stanford\Homesite\Post_Types
 */
class News {

  /** @var string post type slug */
  const NAME         = 'news';

  /** @var  string metadata prefix for CMB */
  const META_PREFIX  = '_stanford_news_';

  /** @var string action to trigger via cron */
  const DELETE_ACTION = 'homesite_delete_old_news';

  /** @var string when to run event that deletes old news items */
  const DELETE_TIME = 'tomorrow 3am';

  /** @var int how many days to keep news stories around */
  const DAYS_TO_KEEP = 30;

  /** @var array panel typess that have Post_List fields (so we don't delete news stories that are referenced in those panels) */
  protected $panels_with_posts = [];


  public function __construct( ) {
    // keep track of which fields in which panels might contain news stories
    // so we don't delete old news items that are still referenced in those panels
    $this->panels_with_posts[ 'posts' ] = [ 'the_posts' ];

    add_action( 'init',            [ $this, 'register_post_type' ] );
    add_action( 'init',            [ $this, 'schedule_delete'    ] );
    add_action( 'cmb2_admin_init', [ $this, 'add_metaboxes'      ] );
    add_filter( 'post_type_link',  [ $this, 'post_type_link'     ], 99, 4 ); // only called for custom post types

    add_filter( 'manage_' . self::NAME . '_posts_columns',       [ $this, 'post_columns' ] );
    add_action( 'manage_' . self::NAME . '_posts_custom_column', [ $this, 'post_column_content' ], 10, 2);

    add_filter( 'panels_query_post_type_options', [ $this, 'add_to_panels_post_picker' ], 10, 2 );
  }

  /**
   * Register our post type
   */
  public function register_post_type() {

    $singular_name = "News Story";
    $plural_name   = "News Stories";

    $labels = array(
        'name'                  => _x( "{$plural_name}", 'Post Type General Name', 'stanford-text-domain' ),
        'singular_name'         => _x( "{$singular_name}", 'Post Type Singular Name', 'stanford-text-domain' ),
        'menu_name'             => __( "News", 'stanford-text-domain' ),
        'name_admin_bar'        => __( "{$singular_name}", 'stanford-text-domain' ),
        'archives'              => __( "{$plural_name}", 'stanford-text-domain' ),
        'attributes'            => __( "{$singular_name} attributes", 'stanford-text-domain' ),
        'parent_item_colon'     => __( "Parent Item:", 'stanford-text-domain' ),
        'all_items'             => __( "All {$plural_name}", 'stanford-text-domain' ),
        'add_new_item'          => __( "Add new {$singular_name}", 'stanford-text-domain' ),
        'add_new'               => __( "Add new {$singular_name}", 'stanford-text-domain' ),
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
        'insert_into_item'      => __( "Insert into story", 'stanford-text-domain' ),
        'uploaded_to_this_item' => __( "Uploaded to this story", 'stanford-text-domain' ),
        'items_list'            => __( "{$plural_name} list", 'stanford-text-domain' ),
        'items_list_navigation' => __( "{$plural_name} list navigation", 'stanford-text-domain' ),
        'filter_items_list'     => __( "Filter {$plural_name} list", 'stanford-text-domain' ),
    );
    $args = array(
        'label'                 => __( "{$singular_name}", 'stanford-text-domain' ),
        'description'           => __( "Stories for the News section of the homepage", 'stanford-text-domain' ),
        'labels'                => $labels,
        'supports'              => array( 'title', 'editor', 'thumbnail', 'revisions' ),
        'taxonomies'            => array( 'category', 'post_tag' ),
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 5,
        'menu_icon'             => 'dashicons-media-document',
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => false,
        'can_export'            => true,
        'has_archive'           => false,
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        'capability_type'       => 'page',
        'show_in_rest'          => true,
    );
    register_post_type( self::NAME, $args );

    add_theme_support( 'post-thumbnails', [ self::NAME ] );
  }

  /**
   * Add CMB2 metabox to teaser edit pages. Metabox contains the following fields:
   *   + url
   *   + source
   * Invoked via the cmb2_admin_init action
   */
  public function add_metaboxes() {
    $link_box = new_cmb2_box( [
        'id'           => 'link'
      , 'title'        => __( 'Link', 'stanford-text-domain' )
      , 'object_types' => [ self::NAME ]
      , 'context'      => 'normal'
      , 'priority'     => 'low'
      , 'show_names'   => TRUE
    ] );

    $link_box->add_field( [
        'id'         => self::get_field_id( 'url' )
      , 'name'       => __( 'URL', 'stanford-text-domain' )
      , 'desc'       => __( 'URL of story', 'stanford-text-domain' )
      , 'type'       => 'text_url'
      , 'attributes' => [
            'placeholder' => 'e.g. http://news.stanford.edu/election-2016'
          , 'required'    => 'required'
        ]
    ] );

    $link_box->add_field( [
        'id'         => self::get_field_id( 'source' )
      , 'name'       => __( 'Source', 'stanford-text-domain' )
      , 'desc'       => __( 'Name of website where the story is hosted', 'stanford-text-domain' )
      , 'type'       => 'text'
      , 'attributes' => [
            'placeholder' => 'e.g. Stanford News'
        ]
    ] );


    $img_box = new_cmb2_box( [
        'id'           => 'feature'
      , 'title'        => __( 'When this is a featured story', 'stanford-text-domain' )
      , 'object_types' => [ self::NAME ]
      , 'context'      => 'normal'
      , 'priority'     => 'low'
      , 'show_names'   => TRUE
    ] );

    $img_box->add_field( [
        'id'         => self::get_field_id( 'theme' )
      , 'name'       => __( 'Text overlay', 'stanford-text-domain' )
      , 'desc'       => __( 'If the featured image is darker, choose \'White text on dark overlay\'. If the featured image is lighter, choose \'Dark text on white overlay\'.', 'stanford-text-domain' )
      , 'type'       => 'Select'
      , 'options'    => [
            'choco'  => 'White text on dark overlay'
          , 'white'  => 'Dark text on white overlay'
        ]
      , 'default'    => 'choco'
      , 'show_option_none' => false
    ] );

    $img_box->add_field( [
        'id'         => self::get_field_id( 'anchor-v' )
      , 'name'       => __( 'Keep visible - vertical', 'stanford-text-domain' )
      , 'desc'       => __( 'If it\'s necessary to crop the featured image vertically, which portion of the image should be kept visible?', 'stanford-text-domain' )
      , 'type'       => 'radio_inline'
      , 'options'    => [
            'top'    => 'Top'
          , 'center' => 'Center'
          , 'bottom' => 'Bottom'
        ]
      , 'default'    => 'center'
    ] );

    $img_box->add_field( [
        'id'         => self::get_field_id( 'deprecated' )
      , 'name'       => __( 'Deprecated', 'stanford-text-domain' )
      , 'desc'       => __( 'The options below apply to the old format for featured stories.', 'stanford-text-domain' )
      , 'type'       => 'title'
    ] );

    $img_box->add_field( [
        'id'         => self::get_field_id( 'lg-img' )
      , 'name'       => __( 'Image', 'stanford-text-domain' )
      , 'desc'       => __( 'A 5:2 image, at least 1449x600, preferably 2998x1200.', 'stanford-text-domain' )
      , 'type'       => 'file'
      , 'allow'      => [ 'attachment' ]
    ] );

    $img_box->add_field( [
      'id'         => self::get_field_id( 'anchor-h' )
      , 'name'       => __( 'Keep visible - horizontal', 'stanford-text-domain' )
      , 'desc'       => __( 'If it\'s necessary to crop the featured image horizontally, which portion of the image should be kept visible?', 'stanford-text-domain' )
      , 'type'       => 'radio_inline'
      , 'options'    => [
        'left'   => 'Left'
        , 'center' => 'Center'
        , 'right'  => 'Right'
      ]
      , 'default'    => 'center'
    ] );

    $img_box->add_field( [
        'id'         => self::get_field_id( 'text-placement-v' )
      , 'name'       => __( 'Text box location - vertical', 'stanford-text-domain' )
      , 'type'       => 'radio_inline'
      , 'options'    => [
            'top'    => 'Top'
          , 'center' => 'Center'
          , 'bottom' => 'Bottom'
        ]
      , 'default'    => 'bottom'
    ] );

    $img_box->add_field( [
        'id'         => self::get_field_id( 'text-placement-h' )
      , 'name'       => __( 'Text box location - horizontal', 'stanford-text-domain' )
      , 'type'       => 'radio_inline'
      , 'options'    => [
            'left'   => 'Left'
          , 'center' => 'Center'
          , 'right'  => 'Right'
        ]
      , 'default'    => 'left'
    ] );

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
   * Set columns to display on posts page
   *
   * @param array $columns - WordPress's idea of what the columns should be
   * @return array our idea of what the columns should be
   */
  public function post_columns ( $columns ) {
    $new_columns = [
        'cb'         => $columns['cb']
      , 'title'      => $columns['title']
      , 'evergreen'  => __( 'Evergreen?', 'stanford-text-domain' )
      , 'categories' => $columns['categories']
      , 'source'     => _x( 'Source', 'Column head for source of an external story', 'stanford-text-domain' )
      , 'url'        => __( 'URL', 'stanford-text-domain' )
      , 'date'       => $columns['date']
    ];
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
      case 'evergreen':
        if ( has_term( 'evergreen', 'post_tag', $post_id ) ) echo "Evergreen";
        break;
      case 'source':
        $source = get_post_meta( $post_id, self::get_field_id( 'source' ), TRUE );
        echo $source;
        break;
      case 'url':
        $external_url = esc_url_raw( get_post_meta( $post_id, self::get_field_id( 'url' ), TRUE ) );
        echo "<a href='{$external_url}' target='_blank'>{$external_url}</a>";
        break;
    };
  }

  /**
   * Make news items link directly to the external source.
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
      $story_url = get_post_meta( $post->ID, self::get_field_id( 'url' ), TRUE );
    }

    return empty( $story_url ) ? $post_link : esc_url_raw( $story_url, [ 'http', 'https' ] );
  }


  /**
   * If we haven't already scheduled a daily event to delete old news, do so.
   * Invoked via 'init' action.
   */
  public function schedule_delete() {
    add_action( self::DELETE_ACTION, [ $this, 'delete_old_news' ] );

    /*  DEBUG: code to delete all scheduled occurences of our delete action
    while ( $scheduled_delete = wp_next_scheduled( self::DELETE_ACTION )) {
      wp_unschedule_event( $scheduled_delete, self::DELETE_ACTION );
    }
    */
    $scheduled_delete = wp_next_scheduled( self::DELETE_ACTION );
    if ( $scheduled_delete === FALSE ) {
      $del_time = gmdate( 'U', strtotime( self::DELETE_TIME ) - ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS ) );
      wp_schedule_event( $del_time, 'daily', self::DELETE_ACTION );
    }
  }

  /**
   * Delete news items older than our cuttoff, unless:
   * - they are still referenced by a panel on some page or post; or
   * - they are tagged 'evergreen'.
   * Invoked via the self::DELETE_ACTION action, which is triggred by cron.
   *
   * @see schedule_delete()
   *
   * @global wpdb $wpdb WordPress database abstraction object.
   */
  public function delete_old_news() {
    global $wpdb;
    error_log( "\\Stanford\\Homesite\\Post_Types\\News::delete_old_news(): invoked" );

    // first, find published pages and posts that have panels
    $posts_used_in_panels = [];
    $query  = "SELECT post_content_filtered from {$wpdb->posts}\n";
    $query .= " WHERE ( post_type = 'page' OR post_type = 'post' )\n";
    $query .= "   AND post_status = 'publish'\n";
    $query .= "   AND post_content_filtered != '';";
    $panel_defs =$wpdb->get_col( $query );

    // next, look for panels that contain posts, and collect the ID's of posts that appear in a panel
    foreach ( $panel_defs as $panel_def ) {
      $panel_data = json_decode( $panel_def );
      foreach ( $panel_data->panels as $panel ) {
        if ( !in_array( $panel->type, array_keys( $this->panels_with_posts ) ) ) continue;
        foreach ( $panel->data->the_posts->posts as $panel_post ) {
          $posts_used_in_panels[] = $panel_post->id;
        }
      }
    }

    // now find all the news stories older than our cutoff that aren't used in panels
    $query  = "SELECT ID from {$wpdb->posts}\n";
    $query .= " WHERE post_type = '" . self::NAME . "'\n";
    $query .= "   AND post_date < DATE_SUB( CURDATE(), INTERVAL " . self::DAYS_TO_KEEP . " day )\n";
    if ( !empty( $posts_used_in_panels ) ) {
      $query .= "   AND ID NOT IN ( " . implode( ', ', $posts_used_in_panels ) . " )";
    }
    $query .= ";";
    $posts_to_be_deleted = $wpdb->get_col( $query );

    // finally, delete the posts that aren't tagged evergreen
    foreach ( $posts_to_be_deleted as $post_id ) {
      if ( !has_term( 'evergreen', 'post_tag', $post_id ) ) {
        $featured_img_id = get_post_thumbnail_id( $post_id );
        if ( $featured_img_id )  {
          wp_delete_attachment( $featured_img_id, TRUE );
        }
        wp_delete_post( $post_id, TRUE ); // really delete the post, don't just move it to Trash
        error_log( "\\Stanford\\Homesite\\Post_Types\\News::delete_old_news(): deleted post {$post_id} and featured image {$featured_img_id}" );
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
