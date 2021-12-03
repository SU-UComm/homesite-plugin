<?php

namespace Stanford\Homesite;

if ( !class_exists('Endpoints') ) {
  class Endpoints {

    /** @var string name of option that keeps track of  */
    const SCHEMA_KEY     = 'hs-endpoint-schema';

    /** @var string schema version - manually increment this when endpoints are added / changed / deleted */
    const SCHEMA_VERSION = '17.0.0';

    /** @var string endpoint to hit in order to clear all transients */
    const CLEAR_CACHE_ENDPOINT = 'clear-cache';

    /**
     * Add custom endpoint(s)
     */
    public function init() {
      $this->add_clear_transients_endpoint();
    }

    /**
     * add /clear-cache endpoint to clear all registered transients
     */
    public function add_clear_transients_endpoint() {
      add_rewrite_endpoint( self::CLEAR_CACHE_ENDPOINT, EP_ROOT );
      $this->_maybe_flush_rules();
      add_action( 'parse_request' , [ $this, 'maybe_clear_transients' ] );
    }

    /**
     * If someone hits /clear-cache, then clear all registered transients.
     * To register a transient, append it to the array passed to the 'clear_homesite_transients' filter.
     * Invoked via the 'parse_request' action.
     *
     * @param \WP $query
     */
    public function maybe_clear_transients( $query ) {
      if ( isset( $query->query_vars[ self::CLEAR_CACHE_ENDPOINT ] ) ) {
        $transients = apply_filters( 'clear_homesite_transients', [] );
        foreach ( $transients as $transient ) {
          delete_transient( $transient );
        }
        $msg = empty( $transients )
             ? "No transients registered"
             : "Transients cleared: " . implode( ', ', $transients );
             ;
        wp_die( $msg );
      }
    }

    protected function _maybe_flush_rules() {
      if ( version_compare( get_option( self::SCHEMA_KEY, '0.0.1' ), self::SCHEMA_VERSION ) == -1 ) {
        flush_rewrite_rules();
        update_option( self::SCHEMA_KEY, self::SCHEMA_VERSION );
      }
    }

  }
}
?>