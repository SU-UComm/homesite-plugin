<?php

namespace Stanford\Homesite;

// CMB2 - Custom Meta Boxes (see https://github.com/WebDevStudios/CMB2)
require_once 'cmb2/init.php';

require_once 'Endpoints.php';
require_once 'Media_Metadata.php';
require_once 'Panels.php';
require_once 'Post_Types/AtoZ.php';
require_once 'Post_Types/News.php';
require_once 'Post_Types/Social.php';


/**
 * Class Core
 *
 * @package Stanford\Homesite
 */
class Core {

  /** @var Core singleton instance of this class */
  protected static $instance = null;

  /** @var string $ver plugin version */
  protected $version = '17.0.0';

  /** @var Panels custom panels */
  protected $panels = null;

  /** @var array all the post types we've defined */
  protected $types = [];

  /**
   * @var  string full path to plugin's main file, i.e. /.../plugins/stanford-homesite/stanford-homesite.php
   *              used by methods in Homesite_Panels to locate various directories (icons, templates, ...)
   */
  protected $plugin_file;


  public function after_init() {  // set up our custom endpoints
    ( new Endpoints() )->init();
    $this->panels = function_exists('modular_content_load')
        ? Panels::init( $this->plugin_file )
        : NULL;
    Media_Metadata::init();
  }


  /**
   * Enqueue custom CSS to make CMB's text fields, esp. text_url, be wide enough
   * Invoked via the admin_enqueue_scripts action
   *
   * @param string $hook - admin page being displayed
   */
  public function admin_enqueue_css( $hook ) {
    switch ( $hook ) {
      case 'post-new.php':
      case 'post.php':
        wp_enqueue_style( 'homesite-admin-css', plugins_url( 'css/admin.css', $this->plugin_file ), [], $this->version );
        break;
    }
  }

  /**
   * Enqueue js to run on the backend
   */
  public function admin_enqueue_js() {
    wp_enqueue_script( 'hs17-quickedit-js', plugins_url( 'js/quick-edit.js', $this->plugin_file ) , array( 'jquery', 'inline-edit-post' ), '', TRUE );
  }


  /******************************************************************************
   *
   * Class setup
   *
   ******************************************************************************/

  /**
   * Constructor.
   * Called once when singleton instance is created.
   * Declared as protected to prevent using new to instantiate instances other than the singleton.
   *
   * @param string $plugin_file full path to plugin's main file
   */
  protected function __construct( $plugin_file ) {
    $this->plugin_file = $plugin_file;
    $this->types[ Post_Types\AtoZ::NAME ]    = new Post_Types\AtoZ();
    $this->types[ Post_Types\News::NAME ]    = new Post_Types\News();
    $this->types[ Post_Types\Social::NAME ]  = new Post_Types\Social();

    add_action( 'init', [ $this, 'after_init' ] );
    add_action( 'admin_enqueue_scripts',        [ $this, 'admin_enqueue_css'  ], 99 );
    add_action( 'admin_print_scripts-edit.php', [ $this, 'admin_enqueue_js'   ]     );
  }

  /**
   * Create singleton instance, if necessary.
   *
   * @param string $plugin_file full path to plugin's main file
   */
  public static function init( $plugin_file ) {
    if ( !is_a( self::$instance, __CLASS__ ) ) {
      self::$instance = new Core( $plugin_file );
    }
    return self::$instance;
  }

}