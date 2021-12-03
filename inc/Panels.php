<?php

namespace Stanford\Homesite;

use \ModularContent\PanelType
  , \ModularContent\PanelViewFinder
  , \ModularContent\Fields
  ;

/**
 * Class Panels
 *
 * @package Stanford\Homesite
 */
class Panels {

  /** @var Panels singleton instance of this class */
  protected static $instance = null;

  /** @var string $ver plugin version */
  protected $version = '17.0.0';

  /** @var  string path to plugin's root directory, e.g. /.../wp-content/plugins/stanford-homesite */
  protected $plugin_dir;

  /** @var  string directory containing panel icons */
  protected $icon_dir;

  /**
   * @var  PanelViewFinder directories where Panel Builder should look for panel templates
   *                       Look first in theme's template-parts/panels directory
   *                       If not there, look in this plugin's panels/templates directory
   */
  protected $template_dirs;


  /******************************************************************************
   *
   * Panels - all method names should end in _panel to be automatically registered
   *
   ******************************************************************************/

  /******************************************************************************
   *  Accordion panel
   */
  protected function accordion_panel() {
    $panel = $this->panel_template( [
        'slug'        => 'accordion'
      , 'label'       => 'Accordion'
      , 'description' => 'Display collapsible content sections'
      , 'settings'     => [
            'title'    => 'yes'
          , 'width'    => 'full'
          , 'theme'    => 'inherit'
          , 'location' => 'main'
        ]
    ] );
    $panel->set_max_children(20);
    $panel->set_child_labels( 'Panel', 'Panels' );

    $group = new Fields\Group([
        'name'        => 'initial-state'
      , 'label'       => 'Initial states by breakpoint'
    ]);
    $breakpoints = [
        'xs' => 'Extra small'
      , 'sm' => 'Small'
      , 'md' => 'Medium'
      , 'lg' => 'Large'
      , 'xl' => 'X-Large'
    ];

    foreach ( $breakpoints as $breakpoint => $description ) {
      switch ( $breakpoint ) {
        case 'xs':
          $default_state = 'closed';
          break;
        case 'sm':
        case 'md':
          $default_state = 'open_first';
          break;
        default:
          $default_state = 'open';
          break;
      }
      $group->add_field( new Fields\Select( [
          'name'    => $breakpoint
        , 'label'   => "Initial state - {$description} devices"
        , 'options' => [
              'closed'     => 'All closed'
            , 'open_first' => 'First panel open'
            , 'open'       => 'All open'
          ]
        , 'default' => $default_state
      ] ) );
    }
    $panel->add_field( $group );

    $panel->add_settings_field( $this->panel_location() );

    return $panel;
  }

  /******************************************************************************
   *  Accordion panel panel
   */
  protected function accordion_panel_panel() {
    $panel = $this->panel_template( [
        'slug'        => 'accordion-panel'
      , 'label'       => 'Accordion Panel'
      , 'description' => 'Display collapsible content sections'
      , 'settings'     => [
            'title'    => 'yes'
          , 'width'    => FALSE
          , 'theme'    => 'inherit'
        ]
    ] );
    $panel->set_context( 'accordion',TRUE );
    $panel->set_max_depth( 2 );
    $panel->set_child_labels( 'Panel', 'Panels' );


    $panel->add_field( new Fields\TextArea( [
        'name'     => 'content'
      , 'label'    => 'Content'
      , 'richtext' => TRUE
    ] ) );

    return $panel;
  }

  /******************************************************************************
   *  Call to Action panel
   */
  protected function call_to_action_panel() {
    $panel = $this->panel_template( [
          'slug'     => 'call-to-action'
        , 'label'    => 'Call to Action'
        , 'settings' => [
              'title' => FALSE
            , 'width' => FALSE
            , 'theme' => FALSE
          ]
      ] );

    $panel->add_field( new Fields\Link( [
         'name'  => 'link'
       , 'label' => 'Link'
       ] ) );

    return $panel;
  }

  /******************************************************************************
   *  Localist panel
   */
  protected function localist_panel() {
    $panel = $this->panel_template( [
          'slug'        => 'localist'
        , 'label'       => 'Localist Events'
        , 'description' => 'Display Localist event widget.'
      ] );

    $panel->add_field( new Fields\TextArea( [
        'name'          => 'localist_widget_html'
      , 'label'         => __( 'Widget HTML', 'stanford-text-domain' )
      , 'description'   => __( 'Paste the HTML from Localist\'s Widget Builder', 'stanford-text-domain' )
      , 'richtext'      => FALSE
      , 'media_buttons' => FALSE
    ] ) );

    return $panel;
  }

  /******************************************************************************
   *  Events panel
   */
  protected function events_panel() {
    $panel = $this->panel_template( [
          'slug'     => 'events'
        , 'label'    => 'Featured Events'
      ] );

    $panel->add_field( new Fields\Text( [
        'name'        => 'feed-url'
      , 'label'       => 'URL of events XML feed'
      , 'default'     => 'http://events.stanford.edu/xml/homepage/feed.php'
    ] ) );

    $panel->add_field( new Fields\Text( [
        'name'        => 'cache-duration'
      , 'label'       => 'Time to cache'
      , 'description' => 'Enter the number of minutes to store the events'
      , 'default'     => '30'
    ] ) );

    $panel->add_field( new Fields\Select( [
        'label'   => 'Number of events to display'
      , 'name'    => 'num-posts'
      , 'options' => [
             '1' =>  '1'
          ,  '2' =>  '2'
          ,  '3' =>  '3'
          ,  '4' =>  '4'
          ,  '5' =>  '5'
          ,  '6' =>  '6'
          ,  '7' =>  '7'
          ,  '8' =>  '8'
          ,  '9' =>  '9'
          , '10' => '10'
          , '11' => '11'
          , '12' => '12'
          , '13' => '13'
          , '14' => '14'
          , '15' => '15'
          , '16' => '16'
        ]
      , 'default' => "4"
    ] ) );

    $panel->add_field( $this->posts_across( 'Number of events in a row' ), 4 );

    return $panel;
  }

  /******************************************************************************
   *  Facts panel
   */
  protected function facts_panel() {
    $panel = $this->panel_template( [
          'slug'     => 'facts'
        , 'label'    => 'Facts'
      ] );

    $panel->add_field( $this->posts_across( 'Number of facts in a row' ) );

    /*********************************
     *  Individual facts
     */
    $repeater = new Fields\Repeater( [
        'name'        => 'the-facts'
      , 'label'       => 'Facts'
      , 'min'         => '1'
      , 'max'         => '6'
      , 'strings'     => [
            'button.new'      => 'Add fact'
          , 'button.delete'   => 'Delete fact'
          , 'label.row_index' => 'Fact %{index} |||| Fact %{index}'
        ]
    ] );

    $repeater->add_field( new Fields\Text( [
        'name'        => 'line1'
      , 'label'       => 'Line one'
    ] ) );

    $repeater->add_field( new Fields\Text( [
        'name'        => 'line2'
      , 'label'       => 'Line two'
    ] ) );

    $repeater->add_field( new Fields\Radio( [
        'label'   => 'Which line should be bold?'
      , 'name'    => 'bold'
      , 'options' => [ 'line1' => 'Line one', 'line2' => 'Line two' ]
      , 'default' => 'line1'
    ] ) );

    $panel->add_field( $repeater );

    /*********************************/

    $panel->add_settings_field( new Fields\Radio( [
        'label'   => 'Show top rule?'
      , 'name'    => 'show-top-rule'
      , 'options' => [ 'yes' => 'Yes', 'no' => 'No' ]
      , 'default' => 'yes'
    ] ) );

    $panel->add_settings_field( new Fields\Radio( [
        'label'   => 'Show bottom rule?'
      , 'name'    => 'show-bottom-rule'
      , 'options' => [ 'yes' => 'Yes', 'no' => 'No' ]
      , 'default' => 'yes'
    ] ) );

    return $panel;
  }

  /******************************************************************************
   *  Hero image panel
   */
  protected function hero_image_panel() {
    $panel = $this->panel_template( [
        'slug'         => 'hero-image'
      , 'label'        => 'Hero - Image'
      , 'description'  => 'Hero image with caption.'
      , 'settings'     => [
            'title'    => FALSE
        ]
    ] );

    $panel->add_field( new Fields\Image( [
        'name'        => 'image-large'
      , 'label'       => 'Image for larger devices'
      , 'description' => 'Choose a 5:2 image to be displayed on tablets and desktops'
    ] ) );

    $panel->add_field( new Fields\Image( [
        'name'        => 'image-small'
      , 'label'       => 'Image for phones'
      , 'description' => 'Choose a 16:9 image to be displayed on phones'
    ] ) );

    $panel->add_field( new Fields\TextArea( [
        'name'     => 'text'
      , 'label'    => 'Text'
      , 'richtext' => TRUE
    ] ) );


    $panel->add_settings_field( new Fields\Radio( [
        'label'       => 'Display text'
      , 'name'        => 'text-display'
      , 'description' => 'Should the text always display, or should it only display on hover?'
      , 'options'     => [ 'always' => 'Always', 'hover' => 'Only on hover' ]
      , 'default'     => 'always'
    ] ) );

    $panel->add_settings_field( new Fields\Radio( [
        'label'   => 'Text placement - vertical'
      , 'name'    => 'text-placement-vertical'
      , 'options' => [ 'top' => 'Top', 'center' => 'Center', 'bottom' => 'Bottom' ]
      , 'default' => 'bottom'
    ] ) );

    $panel->add_settings_field( new Fields\Radio( [
        'label'   => 'Text placement - horizontal'
      , 'name'    => 'text-placement-horizontal'
      , 'options' => [ 'left' => 'Left', 'center' => 'Center', 'right' => 'Right' ]
      , 'default' => 'left'
    ] ) );

    return $panel;
  }

  /******************************************************************************
   *  Highlights panel
   */
  protected function highlights_panel() {
    $panel = $this->panel_template( [
          'slug'        => 'highlights'
        , 'label'       => 'Highlights'
        , 'description' => 'Display selects posts / teasers as highlighted content'
      ] );

    $panel->add_field( $this->posts_across( 'Number of highlights in a row' ) );

    /*********************************
     *  Individual highlights
     */
    $repeater = new Fields\Repeater( [
        'name'        => 'the-highlights'
      , 'label'       => 'Highlights'
      , 'min'         => '1'
      , 'max'         => '9'
      , 'strings'     => [
            'button.new'      => 'Add highlight'
          , 'button.delete'   => 'Delete highlight'
          , 'label.row_index' => 'Highlight %{index} |||| Highlight %{index}'
      ]
    ] );

    $repeater->add_field( new Fields\Text( [
        'name'        => 'highlight-title'
      , 'label'       => 'Title'
    ] ) );

    $repeater->add_field( new Fields\Image( [
        'name'  => 'highlight-image'
      , 'label' => 'Image'
    ] ) );

    $repeater->add_field( new Fields\TextArea( [
        'name'          => 'highlight-content'
      , 'label'         => 'Content'
      , 'richtext'      => TRUE
      , 'media_buttons' => FALSE
    ] ) );

    $repeater->add_field( new Fields\Link( [
        'name'     => 'highlight-link'
      , 'label'    => 'Link'
    ] ) );

    $panel->add_field( $repeater );

    /*********************************/

    $panel->add_field( $this->wrapper_text() );

    return $panel;
  }

  /******************************************************************************
   *  Image overlapping content panel
   */
  protected function image_content_panel() {
    $panel = $this->panel_template( [
        'slug'         => 'image-content'
      , 'label'        => 'Image overlapping content'
      , 'description'  => 'Large image overlapping WYSIWYG content.'
      , 'settings'     => [
            'title'    => 'yes'
          , 'width'    => FALSE
          , 'theme'    => 'white'
        ]
    ] );

    $panel->add_field( new Fields\TextArea( [
        'name'     => 'content'
      , 'label'    => 'Content'
      , 'richtext' => TRUE
    ] ) );

    $panel->add_field( new Fields\Image( [
        'name'  => 'img'
      , 'label' => 'Image'
    ] ) );

    $panel->add_field( new Fields\Radio( [
        'label'   => 'Image placement'
      , 'name'    => 'img-loc'
      , 'description'  => 'Should the image appear to the left or to the right of the content?'
      , 'options' => [ 'left' => 'Left', 'right' => 'Right' ]
      , 'default' => 'left'
    ] ) );

    $panel->add_field( new Fields\Radio( [
        'label'   => 'Show image caption?'
      , 'name'    => 'show-caption'
      , 'options' => [ 'yes' => 'Yes', 'no' => 'No' ]
      , 'default' => 'yes'
    ] ) );

    $panel->add_field( new Fields\Radio( [
        'label'   => 'Show image credit?'
      , 'name'    => 'show-credit'
      , 'options' => [ 'yes' => 'Yes', 'no' => 'No' ]
      , 'default' => 'yes'
    ] ) );

    return $panel;
  }

  /******************************************************************************
   *  Interstitial text panel
   */
  protected function interstitial_text_panel() {
    $panel = $this->panel_template( [
        'slug'         => 'interstitial-text'
      , 'label'        => 'Interstitial text'
      , 'description'  => 'Text to be displayed between sections.'
      , 'settings'     => [
            'title'    => FALSE
          , 'width'    => 'content'
        ]
    ] );

    $panel->add_field( new Fields\TextArea( [
        'name'     => 'text'
      , 'label'    => 'Text'
      , 'richtext' => TRUE
    ] ) );

    return $panel;
  }

  /******************************************************************************
   *  Multi-column panel
   */
  protected function multi_column_panel() {
    $panel = $this->panel_template( [
        'slug'         => 'multi-column'
      , 'label'        => 'Multi-column'
      , 'description'  => '1-6 columns of WYSIWYG content.'
    ] );

    $panel->add_field( $this->posts_across( 'Number of columns in a row' ) );

    $repeater = new Fields\Repeater( [
        'name'        => 'columns'
      , 'label'       => 'Columns'
      , 'min'         => '1'
      , 'max'         => '9'
      , 'strings'     => [
            'button.new'      => 'Add column'
          , 'button.delete'   => 'Delete column'
          , 'label.row_index' => 'Column %{index} |||| Column %{index}'
        ]
    ] );

    $repeater->add_field( new Fields\TextArea( [
        'name'          => 'content'
      , 'label'         => 'Content'
      , 'richtext'      => TRUE
      , 'media_buttons' => TRUE
    ] ) );

    $panel->add_field( $repeater );

    $panel->add_field( $this->wrapper_text() );

    return $panel;
  }

  /******************************************************************************
   *  Position statement panel
   */
  protected function position_stmt_panel() {
    $panel = $this->panel_template( [
        'slug'         => 'position-stmt'
      , 'label'        => 'Positioning statement'
      , 'description'  => 'Text to be displayed on the home page, after the splash screen.'
      , 'settings'     => [
            'title'    => FALSE
          , 'theme'    => 'white'
          , 'location' => 'main'
        ]
    ] );

    $panel->add_field( new Fields\TextArea( [
        'name'     => 'text'
      , 'label'    => 'Text'
      , 'richtext' => TRUE
    ] ) );

    return $panel;
  }

  /******************************************************************************
   *  Posts panel
   */
  protected function posts_panel() {
    $panel = $this->panel_template( [
        'slug'        => 'posts'
      , 'label'       => 'Posts'
      , 'description' => 'Select posts, stories, teasers, ....'
    ] );

    $panel->add_field( new Fields\Post_List( [
        'name'             => 'the-posts'
      , 'label'            => 'Posts'
      , 'min'              => '1'
      , 'max'              => '20'
      , 'suggested'        => '2'
      , 'show_max_control' => TRUE
    ] ) );

    $panel->add_field( $this->posts_across( 'Number of posts in a row' ), 4 );

    $panel->add_field( $this->wrapper_text() );


    /*********************************
     *  Settings
     */
    $panel->add_settings_field( new Fields\Radio( [
        'label'   => 'Feature post:'
      , 'name'    => 'featured-post'
      , 'options' => [ 'none' => 'None', 'first' => 'First', 'first-last' => 'First and last'  ]
      , 'default' => 'none'
    ] ) );

    $panel->add_settings_field( new Fields\Radio( [
        'label'   => 'Show post categories?'
      , 'name'    => 'show-categories'
      , 'options' => [ 'yes' => 'Yes', 'no' => 'No' ]
      , 'default' => 'yes'
    ] ) );

    $panel->add_settings_field( new Fields\Radio( [
        'label'   => 'Show post dates?'
      , 'name'    => 'show-dates'
      , 'options' => [ 'yes' => 'Yes', 'no' => 'No' ]
      , 'default' => 'no'
    ] ) );

    $panel->add_settings_field( new Fields\Radio( [
        'label'   => 'Show post excerpts?'
      , 'name'    => 'show-excerpts'
      , 'options' => [ 'yes' => 'Yes', 'no' => 'No' ]
      , 'default' => 'no'
    ] ) );

    /*********************************/

    return $panel;
  }

  /******************************************************************************
   *  Profile panel
   */
  protected function profile_panel() {
    $panel = $this->panel_template( [
        'slug'         => 'profile'
      , 'label'        => 'Profile'
      , 'description'  => 'Quote and attribution on a background image.'
      , 'settings'     => [
            'title'    => FALSE
          , 'width'    => FALSE
          , 'theme'    => 'choco'
        ]
    ] );

    $panel->add_field( new Fields\Text( [
        'name'        => 'topic'
      , 'label'       => 'Topic'
    ] ) );

    $panel->add_field( new Fields\TextArea( [
        'name'     => 'text'
      , 'label'    => 'Text'
      , 'richtext' => TRUE
    ] ) );

    /*********************************
     *  Attribution
     */
    $group = new Fields\Group([
        'name'        => 'attrib'
      , 'label'       => 'Attribution'
    ]);

    $group->add_field( new Fields\Image( [
        'name'  => 'photo'
      , 'label' => 'Photo'
    ] ) );

    $group->add_field( new Fields\Text( [
        'name'        => 'headline'
      , 'label'       => 'Headline'
    ] ) );

    $group->add_field( new Fields\Text( [
        'name'        => 'details'
      , 'label'       => 'Details'
    ] ) );

    $group->add_field( new Fields\Link( [
        'name'  => 'link'
      , 'label' => 'Link'
    ] ) );

    $panel->add_field( $group );
    /*********************************/

    //  Background images
    $panel->add_field( $this->bg_images() );


    return $panel;
  }

  /******************************************************************************
   *  Search panel
   */
  protected function search_panel() {
    $panel = $this->panel_template( [
        'slug'         => 'search'
      , 'label'        => 'Search'
      , 'description'  => 'Search web / people form'
      , 'settings'     => [
            'title'    => 'yes'
        ]
    ] );

    $panel->add_field( new Fields\Radio( [
        'label'   => 'Default domain'
      , 'name'    => 'domain'
      , 'options' => [ 'people' => 'People', 'web' => 'Web' ]
      , 'default' => 'people'
    ] ) );


    $panel->add_settings_field( $this->panel_location() );

    return $panel;
  }

  /******************************************************************************
   *  Section panel
   */
  protected function section_panel() {
    $panel = $this->panel_template( [
        'slug'        => 'section'
      , 'label'       => 'Section'
      , 'description' => 'Container for panels included in a section of the home page'
      , 'settings'    => [
            'title' => 'yes'
          , 'width' => FALSE
          , 'theme' => 'white'
        ]
    ] );
    $panel->set_max_depth(0); // section panels cannot be child panels
    $panel->set_max_children(10); // section panels can have children
    $panel->set_child_labels( 'Panel', 'Panels' );

    $panel->add_field( $this->wrapper_text() );

    return $panel;
  }

  /******************************************************************************
   *  Section BG panel - section with a background image
   */
  protected function section_bg_panel() {
    $panel = $this->panel_template( [
        'slug'        => 'section-bg'
      , 'label'       => 'Section with Background Image'
      , 'description' => 'Container for panels, with a background image'
      , 'settings'    => [
            'title' => 'yes'
          , 'width' => FALSE
          , 'theme' => 'stone'
        ]
    ] );
    $panel->set_max_depth(0); // section panels cannot be child panels
    $panel->set_max_children(10); // section panels can have children
    $panel->set_child_labels( 'Panel', 'Panels' );

    $bg_group = $this->bg_images();

    $bg_group->add_field( new Fields\Select( [
        'label'   => 'Attach image to which edge?'
      , 'name'    => 'anchor-v'
      , 'options' => [ 'top' => 'Top', 'center' => 'Center', 'bottom' => 'Bottom' ]
      , 'default' => 'center'
    ] ) );

    $bg_group->add_field( new Fields\Select( [
        'label'   => 'Attach image to which side?'
      , 'name'    => 'anchor-h'
      , 'options' => [ 'left' => 'Left', 'center' => 'Center', 'right' => 'Right' ]
      , 'default' => 'center'
    ] ) );

    $bg_group->add_field( new Fields\Select( [
        'label'   => 'Gradient'
      , 'name'    => 'gradient'
      , 'options' => [ 'none' => 'None', 'bottom' => 'Bottom', 'top' => 'Top' ]
      , 'default' => 'none'
    ] ) );

    $panel->add_field( $bg_group );

    $panel->add_field( $this->wrapper_text() );


    $panel->add_settings_field( new Fields\Select( [
        'label'   => 'Padding - top'
      , 'name'    => 'padding-top'
      , 'options' => [ 'sm' => 'Small', 'md' => 'Medium', 'lg' => 'Large' ]
      , 'default' => 'md'
    ] ) );

    $panel->add_settings_field( new Fields\Select( [
        'label'   => 'Padding - bottom'
      , 'name'    => 'padding-bottom'
      , 'options' => [ 'sm' => 'Small', 'md' => 'Medium', 'lg' => 'Large' ]
      , 'default' => 'med'
    ] ) );

    $panel->add_settings_field( $this->panel_location() );

    return $panel;
  }

  /******************************************************************************
   *  Subscribe to SR panel
   */
  protected function subscribe_sr_panel() {
    $panel = $this->panel_template( [
        'slug'         => 'subscribe-sr'
      , 'label'        => 'Subscribe to SR'
      , 'description'  => 'Form to allow people to subscribe to Stanford Report.'
      , 'settings'     => [
            'title'    => 'yes'
        ]
    ] );

    $panel->add_field( new Fields\Text( [
        'name'        => 'btn-text'
      , 'label'       => 'Button Ttext'
    ] ) );


    return $panel;
  }

  /******************************************************************************
   *  Splash Image panel
   */
  protected function splash_image_panel() {
    $panel = $this->panel_template( [
        'slug'         => 'splash-image'
      , 'label'        => 'Splash - Image'
      , 'description'  => 'Homepage splash screen with static image.'
      , 'settings'     => [
            'title'    => FALSE
          , 'width'    => FALSE
          , 'theme'    => TRUE
          , 'location' => 'header'
        ]
    ] );


    $panel->add_field( new Fields\Image( [
        'name'        => 'image-landscape'
      , 'label'       => 'Landscape image'
      , 'description' => 'Image for desktops and mobile devices in landscape orientation.'
    ] ) );

    $panel->add_field( new Fields\Image( [
        'name'        => 'image-portrait'
      , 'label'       => 'Portrait image'
      , 'description' => 'Image for mobile devices in portrait orientation.'
    ] ) );

    $panel->add_field( new Fields\Text( [
    	'name'  => 'video-url'
	  , 'label' => 'Video URL'
	  , 'description' => 'Optional URL from Vimeo for looping background.'
    ] ) );

    $panel->add_field( new Fields\Text( [
        'name'        => 'scroll-cta'
      , 'label'       => 'Scroll Text'
      , 'description' => 'Text to encourage people to scroll down. Will be followed by a downward pointing arrow.'
      , 'default'     => 'Explore Stanford'
    ] ) );


    $panel->add_settings_field( new Fields\Radio( [
        'label'       => 'Wordmark visibility'
      , 'name'        => 'logo'
      , 'options'     => [ 'show-logo' => 'Normal', 'watermark-logo' => 'Watermark', 'no-logo' => 'None' ]
      , 'default'     => 'show-logo'
    ] ) );


    $panel->add_settings_field( new Fields\Radio( [
        'label'       => 'Wordmark placement - vertical'
      , 'name'        => 'logo-placement-vertical'
      , 'options'     => [ 'top' => 'Top', 'middle' => 'Middle', 'bottom' => 'Bottom' ]
      , 'default'     => 'middle'
    ] ) );

    $panel->add_settings_field( new Fields\Radio( [
        'label'       => 'Wordmark placement - horizontal'
      , 'name'        => 'logo-placement-horizontal'
      , 'description' => 'Landscape orientation only'
      , 'options'     => [ 'left' => 'Left', 'center' => 'Center', 'right' => 'Right' ]
      , 'default'     => 'center'
    ] ) );

    $panel->add_settings_field( new Fields\Radio( [
        'label'       => 'Scroll behavior'
      , 'name'        => 'scroll-type'
      , 'options'     => [ 'curtain' => 'Curtain reveal', 'parallax' => 'Parallax' ]
      , 'default'     => 'curtain'
    ] ) );


    return $panel;
  }

  /******************************************************************************
   *  Well panel
   */
  protected function well_panel() {
    $panel = $this->panel_template( [
        'slug'        => 'well'
      , 'label'       => 'Well'
      , 'description' => 'Display stories or events in a well'
      , 'settings'     => [
            'title'    => 'yes'
          , 'width'    => FALSE
          , 'theme'    => FALSE
          , 'location' => 'main'
        ]
    ] );
    $panel->set_max_children(5); // well panels can have children
    $panel->set_child_labels( 'Panel', 'Panels' );

    $panel->add_field( new Fields\Text( [
        'name'        => 'fa-icon'
      , 'label'       => 'Icon'
      , 'description' => 'Optional - Full class name of Font Awesome icon, e.g. fa-envelope'
      , 'default'     => ''
    ] ) );

    $panel->add_field( new Fields\Link( [
        'name'        => 'more'
      , 'label'       => 'More link'
      , 'description' => 'Optional more link'
    ] ) );

    /*********************************
     *  Settings
     */

    $panel->add_settings_field( new Fields\Select( [
        'name'    => 'stroke-color'
      , 'label'   => 'Stroke color'
      , 'options' => [
            'red'   =>  'Red'
          , 'green' =>  'Green'
          , 'blue'  =>  'Blue'
          , 'black' =>  'Black'
        ]
      , 'default' => "red"
    ] ) );

    $panel->add_settings_field( $this->panel_location( 'sidebar' ) );

    return $panel;
  }

	/******************************************************************************
   *  WYSIWIG panel
   */
  protected function wysiwyg_panel() {
    $panel = $this->panel_template( [
          'slug'     => 'wysiwyg'
        , 'label'    => 'WYSIWIG'
      ] );

    $panel->add_field( new Fields\TextArea( [
            'name'     => 'content'
          , 'label'    => 'Content'
          , 'richtext' => TRUE
        ] ) );

    return $panel;
  }


  /******************************************************************************
   *
   * Utilities
   *
   ******************************************************************************/

  /**
   * panel_template() - Do the common things we need to do for every panel
   *
   * @param array $opts Specify at least 'slug' and 'label', and 'description'
   *                    if applicable. You may also pass a 'settings' array of
   *                    of defaults for standard panel settings fields. Pass
   *                    FALSE to suppress the setting field.
   *
   * @return PanelType
   */
  protected function panel_template( $opts = [] ) {
    $defaults = [
        'slug'         => 'default-panel-slug'
      , 'label'        => 'Default Panel Label'
      , 'description'  => ''
      , 'settings'     => [
            'title'    => 'no'
          , 'width'    => 'full'
          , 'theme'    => 'inherit'
          , 'location' => 'main'
        ]
    ];
    $opts = wp_parse_args( $opts, $defaults );
    // wp_parse_args doesn't recurse into the array, so do the settings array manually
    foreach ( $defaults[ 'settings' ] as $setting => $value ) {
      if ( !isset( $opts[ 'settings' ][ $setting ] ) ) {
        $opts[ 'settings' ][ $setting ] = $value;
      }
    }

    $panel = new \ModularContent\PanelType( $opts[ 'slug' ] );
    $panel->set_label( $opts[ 'label' ] );
    $panel->set_description( $opts[ 'description' ] );
    $panel->set_thumbnail( "{$this->icon_dir}/{$opts[ 'slug' ]}.png" );
    $panel->set_template_dir( $this->template_dirs );
    $panel->set_max_depth(1); // by default, panel can be a child panel

    // Default settings fields

    // make this a regular field so we don't force a Settings tab on panels that have no visible settings
    $panel->add_field( new Fields\Hidden( [
        'name'    => 'page_location'
      , 'default' => $opts[ 'settings' ][ 'location' ]
    ] ) );

    // Setting: panel width
    if ( $opts[ 'settings' ][ 'width' ] ) {
      $panel->add_settings_field( new Fields\Select( [
          'label'       => 'Panel content width'
        , 'name'        => 'content_width'
        , 'options'     => [
              'content' => 'Content width'
            , 'full'    => 'Full width (with gutters)'
            , 'edges'   => 'Edge to edge (no gutters)'
          ]
        , 'default' => $opts[ 'settings' ][ 'width' ]
      ] ) );
    }

    // Setting: panel theme
    if ( $opts[ 'settings' ][ 'theme' ] ) {
      $panel->add_settings_field( new Fields\Select( [
          'label'   => 'Panel theme'
        , 'name'    => 'theme'
        , 'options' => [
              'inherit'   => 'Inherit'
            , 'white'     => 'White'
            , 'fog'       => 'Fog'
            , 'sandstone' => 'Sandstone'
            , 'stone'     => 'Stone'
            , 'choco'     => 'Dark Chocolate'
          ]
        , 'default' => $opts[ 'settings' ][ 'theme' ]
      ] ) );
    }

    // Setting: panel title
    // TODO: When MT fixes rendering of Select field, move title options to top
    if ( $opts[ 'settings' ][ 'title' ] ) {
      $panel->add_settings_field( new Fields\Radio( [
            'label'   => 'Show panel title?'
          , 'name'    => 'show_title'
          , 'options' => [ 'yes' => 'Yes', 'no' => 'No' ]
          , 'default' => $opts[ 'settings' ][ 'title' ]
        ] ) );

      $panel->add_settings_field( new Fields\Radio( [
            'label'   => 'Center panel title?'
          , 'name'    => 'center_title'
          , 'options' => [ 'yes' => 'Yes', 'no' => 'No' ]
          , 'default' => 'yes'
        ] ) );
    }

    return $panel;
  }

  /**
   * Return a group containing fields specifying background images for a panel
   *
   * @return Fields\Group
   */
  protected function bg_images() {
    $group = new Fields\Group([
        'name'        => 'bg-images'
      , 'label'       => 'Background images'
    ]);

    $group->add_field( new Fields\Image( [
        'name'        => 'landscape'
      , 'label'       => 'Landscape image'
      , 'description' => 'Image for desktops and mobile devices in landscape orientation.'
    ] ) );

    $group->add_field( new Fields\Image( [
        'name'        => 'portrait'
      , 'label'       => 'Portrait image'
      , 'description' => 'Image for mobile devices in portrait orientation.'
    ] ) );

    return $group;
  }

  /**
   * Return a Select field to specify what section of the page the panel should render in
   *
   * @param string $default
   *
   * @return Fields\Select
   */
  protected function panel_location( $default = 'main' ) {
    return new Fields\Select( [
        'label'   => 'In what region of the page body should this panel appear?'
      , 'name'    => 'page_region'
      , 'options' => [
            'main-header' => 'Page header'
          , 'main'        => 'Main content'
          , 'sidebar'     => 'Sidebar'
          , 'main-footer' => 'Page footer'
        ]
      , 'default' => "{$default}"
    ] );
  }

  /**
   * Return a Select field to specify how many items should appear in a row
   *
   * @param string $label
   * @param int    $default
   *
   * @return Fields\Select
   */
  protected function posts_across( $label = 'Number of items in a row', $default = 3 ) {
    return new Fields\Select( [
        'label'   => $label
      , 'name'    => 'posts-across'
      , 'options' => [
            '1' => '1'
          , '2' => '2'
          , '3' => '3'
          , '4' => '4'
          , '5' => '5'
          , '6' => '6'
        ]
      , 'default' => "{$default}"
    ] );
  }

  /**
   * Return a group containing rich text fields for intro and closing text
   *
   * @return \ModularContent\Fields\Group
   */
  protected function wrapper_text() {
    $group = new Fields\Group([
        'name'        => 'wrapper-text'
      , 'label'       => 'Intro / Closing Text'
    ]);

    $group->add_field( new Fields\TextArea( [
        'name'        => 'intro'
      , 'label'       => 'Intro Text'
      , 'description' => 'Content to display before the panel\'s content (optional)'
      , 'richtext'    => TRUE
    ] ) );

    $group->add_field( new Fields\TextArea( [
        'name'        => 'closing'
      , 'label'       => 'Closing Text'
      , 'description' => 'Content to display after the panel\'s content (optional)'
      , 'richtext'    => TRUE
    ] ) );

    return $group;
  }

  /**
   * Look in our special places for the markup used to wrap panels and panel collections.
   * Invoked via the panels_collection_wrapper_template and panels_panel_wrapper_template filters.
   *
   * @param string $default_template file system path to Panel Builder's default wrapper template
   * @param \ModularContent\ViewFinder $viewfinder useless Viewfinder that doesn't look in the right places
   *
   * @return string
   */
  public function wrapper_templates( $default_template, $viewfinder ) {
    $template_name   = basename( $default_template );
    $custom_template = $this->template_dirs->locate_theme_file( $template_name );
    return $custom_template ? $custom_template : $default_template;
  }

  /**
   * Specify what post types we want used for the Posts field
   * Invoked via the 'panels_query_post_type_options' filter
   *
   * @param array $post_types
   * @param \ModularContent\Fields\Field $field
   * @return array
   */
  public function post_picker_post_types( $post_types, $field ) {
    $types = [];
    if ( isset( $post_types[ \Stanford\Homesite\Post_Types\News::NAME ] ) ) {
      $types[ \Stanford\Homesite\Post_Types\News::NAME ] = $post_types[ \Stanford\Homesite\Post_Types\News::NAME ] ;
    } else {
      $types[ 'post' ] = $post_types[ 'post' ];
    }

    $teaser_type = get_post_type_object( 'teaser' );
    if ( is_object( $teaser_type ) ) {
      $types[ 'teaser' ] = get_post_type_object( 'teaser' );
    }

    return $types;
  }

  /**
   * Specify what taxonomies we want used for the Posts field
   * Invoked via the 'modular_content_posts_field_taxonomy_options' filter
   *
   * @param array $taxonomies
   * @return array
   */
  public function post_picker_taxonomies( $taxonomies ) {
    array_unshift( $taxonomies, 'category' );
    return $taxonomies;
  }

  /**
   * Set overall panel options and register all panels defined in this class.
   * Invoked via the panels_init action
   *
   * @param \ModularContent\TypeRegistry $registry
   */
  public function panels_init( $registry ) {
    // specify which post types can have panels in general (allowed on posts only by default)
    // Note: individual panels can limit themselves to only specific post types
    // remove_post_type_support( 'post', 'modular-content' );
    add_post_type_support( 'page', 'modular-content' );

    // register all the panels we've defined
    // each panel should be defined in a method of this class whose name ends in _panel
    $methods = get_class_methods( __CLASS__ );
    foreach ( $methods as $method ) {
      if ( preg_match( '/_panel$/', $method ) ) {
        $panel = call_user_func( [ $this, $method ] );
        $registry->register( $panel );
      }
    }

    // don't include panels as part of the_content()
    // we'll put panels where we want them
    \ModularContent\Plugin::instance()->do_not_filter_the_content();

    // provide our own markup to wrap panels and panel collections
    add_filter( 'panels_collection_wrapper_template',           [ $this, 'wrapper_templates'      ], 10, 2 );
    add_filter( 'panels_panel_wrapper_template',                [ $this, 'wrapper_templates'      ], 10, 2 );
    // only include news stories (and possibly teasers) in post pickers
    add_filter( 'panels_query_post_type_options',               [ $this, 'post_picker_post_types' ], 10, 2 );
    add_filter( 'modular_content_posts_field_taxonomy_options', [ $this, 'post_picker_taxonomies' ], 10, 1 );
  }

  /******************************************************************************
   *
   * Class setup
   *
   ******************************************************************************/

  /**
   * Called once when singleton instance is created.
   * Declared as protected to prevent using new to instantiate instances other than the singleton.
   *
   * @param string $plugin_file full path to plugin's main file
   */
  protected function __construct( $plugin_file ) {
    $this->icon_dir      = plugins_url( "panels/icons", $plugin_file );

    /**
     * Set directories where Panel Builder should look for panel templates
     * Look first in the current theme's template-parts/panels directory
     * If not there and we're running a child theme, look in the parent theme's template-parts/panels directory
     * If still not found, look in this plugin's panels/templates directory
     */
    $this->template_dirs = new PanelViewFinder( get_stylesheet_directory() . '/template-parts/panels' );
    if ( is_child_theme() ) { // if we're running a child theme, also look for templates in the parent theme
      $this->template_dirs->add_directory( get_template_directory() . '/template-parts/panels' );
    }
    $this->template_dirs->add_directory( plugin_dir_path( $plugin_file ) . 'panels/templates' );

    add_action( 'panels_init', array( $this, 'panels_init' ), 10, 1 );
  }

  /**
   * Create singleton instance, if necessary.
   *
   * @param string $plugin_file full path to plugin's main file
   */
  public static function init( $plugin_file ) {
    if ( !is_a( self::$instance, __CLASS__ ) ) {
      self::$instance = new Panels( $plugin_file );
    }
    return self::$instance;
  }

}