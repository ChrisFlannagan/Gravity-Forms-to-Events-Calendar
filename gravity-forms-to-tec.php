<?php
/**
 * Plugin Name: Gravity Forms to Events Calendar
 * Plugin Author: Chris Flannagan
 * Author: Chris Flannagan
 * Version: 1.0.0
 */

define( 'GF_TO_TEC_ADD_ON', '1.0.0' );

add_action( 'init', function() {
	require_once( 'taxonomies/cities.php' );
	TEC_GF_Cities::register();
	require_once( 'taxonomies/neighborhoods.php' );
	TEC_GF_Neighborhoods::register();
} );

add_action( 'gform_loaded', array( 'Gravity_Forms_To_TEC', 'load' ), 5 );
class Gravity_Forms_To_TEC {

	public static function load() {

		if ( ! method_exists( 'GFForms', 'include_addon_framework' ) ) {
			return;
		}

		require_once( 'class-gfsimpleaddon.php' );

		GFAddOn::register( 'GF_TEC_AddOn' );
	}

}

add_role(
	'approved_evt_sub',
	'Approved Event Submitter',
	array(
		'read'       => true,
		'edit_posts' => false,
	)
);

function gf_simple_addon() {
	return GF_TEC_AddOn::get_instance();
}
