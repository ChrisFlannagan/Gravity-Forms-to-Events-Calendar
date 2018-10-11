<?php

class TEC_GF_Neighborhoods {

	public static function register() {

		$labels = [
			'singular_name'     => __( 'Neighborhood' ),
			'plural_name'       => __( 'Neighborhoods' ),
			'search_items'      => __( 'Search Neighborhoods' ),
			'all_items'         => __( 'All Neighborhoods' ),
			'parent_item'       => __( 'Parent Neighborhood' ),
			'parent_item_colon' => __( 'Parent Neighborhood:' ),
			'edit_item'         => __( 'Edit Neighborhood' ),
			'update_item'       => __( 'Update Neighborhood' ),
			'add_new_item'      => __( 'Add New Neighborhood' ),
			'new_item_name'     => __( 'New Neighborhood Name' ),
			'menu_name'         => __( 'Neighborhood' ),
		];

		register_taxonomy(
			'event_boroughs',
			'tribe_events',
			array(
				'label'             => 'Neighborhoods',
				'labels'            => $labels,
				'show_ui'           => true,
				'show_admin_column' => true,
				'query_var'         => true,
				'hierarchical'      => true,
			)
		);
	}

}
