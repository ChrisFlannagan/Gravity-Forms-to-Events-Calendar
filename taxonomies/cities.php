<?php

class TEC_GF_Cities {

	public static function register() {

		$labels = [
			'singular_name'     => __( 'City' ),
			'plural_name'       => __( 'Cities' ),
			'search_items'      => __( 'Search Cities' ),
			'all_items'         => __( 'All Cities' ),
			'parent_item'       => __( 'Parent City' ),
			'parent_item_colon' => __( 'Parent City:' ),
			'edit_item'         => __( 'Edit City' ),
			'update_item'       => __( 'Update City' ),
			'add_new_item'      => __( 'Add New City' ),
			'new_item_name'     => __( 'New City Name' ),
			'menu_name'         => __( 'Citie' ),
		];

		register_taxonomy(
			'event_cities',
			'tribe_events',
			array(
				'label'             => 'Cities',
				'labels'            => $labels,
				'show_ui'           => true,
				'show_admin_column' => true,
				'query_var'         => true,
				'hierarchical'      => true,
			)
		);
	}

}
