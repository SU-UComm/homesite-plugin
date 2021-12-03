<?php
/*
Plugin Name: Stanford Homesite
Description: Core functionality for Stanford's Homesite
Version:     17.0.0
Author:      JB Christy
Author URI:  http://ucomm.stanford.edu/webteam
Text Domain: stanford-text-domain
Domain Path: /languages
*/

function activate_stanford_homesite() {
  // verify our dependencies
  if ( !function_exists('modular_content_load') ) {
    deactivate_plugins( basename( __FILE__ ) );
    wp_die(
        '<p>The <strong>Stanford Homesite</strong> plugin requires the Tribe Panel Builder plugin to be installed and activated. Please activate the Tribe Panel Builder plugin and try again.</p>'
        , 'Plugin Activation Failed'
        , [ 'response'=>200, 'back_link'=>TRUE ]
    );
  }
}

function deactivate_stanford_homesite() {
  return;
}

register_activation_hook(   __FILE__, 'activate_stanford_homesite'   );
register_deactivation_hook( __FILE__, 'deactivate_stanford_homesite' );


require_once 'inc/Core.php';

global $stanford_homesite;
if ( ! is_a( $stanford_homesite, 'Stanford_Homesite') ) {
  $stanford_homesite = \Stanford\Homesite\Core::init( __FILE__ );
}


/**
 * Convert a date/time string to the corresponding date/time in the site's timezone.
 * Called by the WP All Import plugin to set the post date of items imported from RSS feeds.
 * Written as a stand-alone function cuz I don't know how to call a method using WP All Import's syntax.
 *
 * @param  string $datetime - string representation of a date/time, erroneously specified as GMT
 * @return string same date/time in this site's timezone
 */
function convert_to_site_tz($datetime, $fmt = '') {
  $dt = new DateTime($datetime);
  $tz = new DateTimeZone(get_option('timezone_string', 'UTC'));
  $dt->setTimezone($tz);
  if (empty($fmt)) $fmt = get_option('date_format', 'Y-m-d') . " " . get_option('time_format', 'H:i:s');
  return $dt->format($fmt);
}
