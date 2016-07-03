/**
 * The main JavaScript file for Run Log plugin.
 * Activate jQuery tooltip and selective input display for embeding options.
 *
 * @summary   JavaScript commands and functions for Run Log plugin.
 *
 * @since     1.4.1
 * @requires	jquery.js, jquery-ui-tooltip.js
 */

// jQuery compatible code.
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

	/**
	 * @summary Show/Hide input fields according to radio button select.
	 *
	 * @since 1.4.1
	 */
  function embed_external_display() {
    // Show/hide Strava embed div.
    if( true === $( '#oirl-mb-embed-external-strava' ).prop("checked") ) {
      $( '#oirl-div-embed-external-strava' ).show();
    } else {
      $( '#oirl-div-embed-external-strava' ).hide();
    }
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
