(function( $ ) {
	'use strict';

	/**
	 * Admin-facing JavaScript source.
	 */

   $( document ).ready(function() {
     // Handle the embed external resource display
     embed_external_display();  // initial
     $( 'input[type=radio][name=oirl-mb-embed-external]' ).change( embed_external_display );

    $( '.oirl *' ).tooltip({
      track: true
    });

  });

  function embed_external_display() {
    // Show/hide Garmin embed div.
    if( true === $( '#oirl-mb-embed-external-garmin' ).prop("checked") ) {
      $( '#oirl-div-embed-external-garmin' ).show();
    } else {
      $( '#oirl-div-embed-external-garmin' ).hide();
    }
    // Show/hide endomondo embed div.
    if( true === $( '#oirl-mb-embed-external-endomondo' ).prop("checked") ) {
      $( '#oirl-div-embed-external-endomondo' ).show();
    } else {
      $( '#oirl-div-embed-external-endomondo' ).hide();
    }
  }

})( jQuery );
