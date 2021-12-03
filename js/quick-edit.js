/**
 * Manage bulk edit and quick edit of custom fields
 */

(function($) {

  // we create a copy of the WP inline edit post function
  var $wp_inline_edit = inlineEditPost.edit;

  // and then we overwrite the function with our own code
  inlineEditPost.edit = function( id ) {

    // "call" the original WP edit function
    $wp_inline_edit.apply( this, arguments );

    // manage our custom fields

    var $post_id = ( typeof( id ) == 'object' ) ? $post_id = parseInt( this.getId( id ) ) : 0;

    if ( $post_id > 0 ) {
      // find the row being edited
      var $edit_row = $( '#edit-' + $post_id );

      // manage the url field
      var $url = $( '#post-'+$post_id+' .url' ).text();  // get the current value of the url from the table
      $edit_row.find( 'input[name="url"]' ).val( $url ); // put the current value in the field in the quick edit box
    }

  };

  $( '#bulk_edit' ).live( 'click', function() {

    // define the bulk edit row
    var $bulk_row = $( '#bulk-edit' );

    // get the selected post ids that are being edited
    var $post_ids = new Array();
    $bulk_row.find( '#bulk-titles' ).children().each( function() {
      $post_ids.push( $( this ).attr( 'id' ).replace( /^(ttle)/i, '' ) );
    });

    // get the custom fields
    var $url = $bulk_row.find( 'input[name="url"]' ).val();

    // save the data
    $.ajax({
      url: ajaxurl, // this is a variable that WordPress has already defined for us
      type: 'POST',
      async: false,
      cache: false,
      data: {
        action: 'hs17_bulk_edit_save', // our WP AJAX action
        post_ids: $post_ids, // the parameters we're passing to our ajax handler
        url: $url
      }
    });

  });

})(jQuery);