<?php

GFForms::include_addon_framework();

class GF_TEC_AddOn extends GFAddOn {

	protected $_version = GF_TO_TEC_ADD_ON;
	protected $_min_gravityforms_version = '1.9';
	protected $_slug = 'gravity-forms-to-tec';
	protected $_path = 'gravity-forms-to-tec/gravity-forms-to-tec.php';
	protected $_full_path = __FILE__;
	protected $_title = 'Gravity Forms To Events Calendar';
	protected $_short_title = 'Events Calendar';

	private static $_instance = null;

	/**
	 * Get an instance of this class.
	 *
	 * @return GF_TEC_AddOn
	 */
	public static function get_instance() {
		if ( self::$_instance == null ) {
			self::$_instance = new GF_TEC_AddOn();
		}

		return self::$_instance;
	}

	/**
	 * Handles hooks and loading of language files.
	 */
	public function init() {
		parent::init();
		add_action( 'gform_after_submission', array( $this, 'after_submission' ), 10, 2 );
		add_filter( 'gform_pre_render', [ $this, 'populate_posts' ] );
		add_filter( 'gform_pre_validation', [ $this, 'populate_posts' ] );
		add_filter( 'gform_pre_submission_filter', [ $this, 'populate_posts' ] );
		add_filter( 'gform_admin_pre_render', [ $this, 'populate_posts' ] );
		add_action( 'save_post_tribe_events', [ $this, 'maybe_create_venue' ], 10, 2 );
	}

	public function populate_posts( $form ) {
		if ( ! isset( $form['gravity-forms-to-tec']['enabled'] ) || $form['gravity-forms-to-tec']['enabled'] !== '1' ) {
			return $form;
		}

		$venue_field_id         = ! isset( $form['gravity-forms-to-tec']['event_venue_exists'] ) ? 0 : $form['gravity-forms-to-tec']['event_venue_exists'];
		$neighborhoods_field_id = ! isset( $form['gravity-forms-to-tec']['event_tax_neighborhood'] ) ? 0 : $form['gravity-forms-to-tec']['event_tax_neighborhood'];
		$cities_field_id        = ! isset( $form['gravity-forms-to-tec']['event_tax_city'] ) ? 0 : $form['gravity-forms-to-tec']['event_tax_city'];
		foreach ( $form['fields'] as &$field ) {

			if ( $field->type != 'select' ) {
				continue;
			}

			switch ( $field['id'] ) {
				case $venue_field_id :
					$field->choices = $this->get_venue_options();
					break;
				case $neighborhoods_field_id :
					$field->choices = $this->get_tax_options( 'event_boroughs' );
					break;
				case $cities_field_id :
					$field->choices = $this->get_tax_options( 'event_cities' );
					break;
			}

		}

		return $form;
	}

	private function get_tax_options( $tax ) {
		$terms = get_terms( [
			'taxonomy'   => $tax,
			'hide_empty' => false,
			'orderby'    => 'name',
			'order'      => 'ASC',
		] );

		$choices = [
			[
				'text'  => 'Select a City',
				'value' => '0',
			],
		];
		foreach ( $terms as $term ) {
			$choices[] = [
				'text'  => $term->name,
				'value' => $term->term_id,
			];
		}

		return $choices;
	}

	private function get_venue_options() {

		// you can add additional parameters here to alter the posts that are retrieved
		// more info: http://codex.wordpress.org/Template_Tags/get_posts
		$posts = get_posts( 'numberposts=-1&orderyby=post_title&post_status=publish&post_type=tribe_venue' );

		$choices = array(
			[
				'text'  => 'Insert New Venue Or Select One',
				'value' => '0',
			],
		);

		foreach ( $posts as $post ) {
			$choices[] = array( 'text' => $post->post_title, 'value' => $post->ID );
		}

		return $choices;
	}

	/**
	 * Creates a custom page for this add-on.
	 */
	public function plugin_page() {
		include 'admin-approval.php';
	}

	/**
	 * Configures the settings which should be rendered on the Form Settings > Simple Add-On tab.
	 *
	 * @return array
	 */
	public function form_settings_fields( $form ) {
		return array(
			array(
				'title'  => esc_html__( 'Events Calendar Settings', 'simpleaddon' ),
				'fields' => array(
					array(
						'label'   => esc_html__( 'Enable To Events Calendar', 'simpleaddon' ),
						'type'    => 'checkbox',
						'name'    => 'enabled',
						'tooltip' => esc_html__( 'When form is submitted try to create a Events Calendar Event', 'simpleaddon' ),
						'choices' => array(
							array(
								'label' => esc_html__( 'Enabled', 'simpleaddon' ),
								'name'  => 'enabled',
							),
						),
					),
					array(
						'label' => esc_html__( 'Event Title', 'simpleaddon' ),
						'type'  => 'field_select',
						'name'  => 'event_title',
					),
					array(
						'label' => esc_html__( 'Event Content', 'simpleaddon' ),
						'type'  => 'field_select',
						'name'  => 'event_content',
					),
					array(
						'label' => esc_html__( 'Event Start Date', 'simpleaddon' ),
						'type'  => 'field_select',
						'name'  => 'event_start_date',
					),
					array(
						'label' => esc_html__( 'Event End Date', 'simpleaddon' ),
						'type'  => 'field_select',
						'name'  => 'event_end_date',
					),
					array(
						'label' => esc_html__( 'All Day Event', 'simpleaddon' ),
						'type'  => 'field_select',
						'name'  => 'event_all_day',
					),
					array(
						'label' => esc_html__( 'City', 'simpleaddon' ),
						'type'  => 'field_select',
						'name'  => 'event_tax_city',
					),
					array(
						'label' => esc_html__( 'Neighborhood', 'simpleaddon' ),
						'type'  => 'field_select',
						'name'  => 'event_tax_neighborhood',
					),
					array(
						'label' => esc_html__( 'Pre-existing Venue', 'simpleaddon' ),
						'type'  => 'field_select',
						'name'  => 'event_venue_exists',
					),
					array(
						'label' => esc_html__( 'Venue Name', 'simpleaddon' ),
						'type'  => 'field_select',
						'name'  => 'event_venue_name',
					),
					array(
						'label' => esc_html__( 'Venue Address', 'simpleaddon' ),
						'type'  => 'field_select',
						'name'  => 'event_venue_address',
					),
					array(
						'label' => esc_html__( 'Venue Address 2', 'simpleaddon' ),
						'type'  => 'field_select',
						'name'  => 'event_venue_address_2',
					),
					array(
						'label' => esc_html__( 'Venue City', 'simpleaddon' ),
						'type'  => 'field_select',
						'name'  => 'event_venue_city',
					),
					array(
						'label' => esc_html__( 'Venue State', 'simpleaddon' ),
						'type'  => 'field_select',
						'name'  => 'event_venue_state',
					),
					array(
						'label' => esc_html__( 'Venue Zip', 'simpleaddon' ),
						'type'  => 'field_select',
						'name'  => 'event_venue_zip',
					),
					array(
						'label' => esc_html__( 'Event Link or Purchase Tickets Link', 'simpleaddon' ),
						'type'  => 'field_select',
						'name'  => 'event_link',
					),
					array(
						'label' => esc_html__( 'Cost', 'simpleaddon' ),
						'type'  => 'field_select',
						'name'  => 'event_cost',
					),
				),
			),
		);
	}

	/**
	 * Performing a custom action at the end of the form submission process.
	 *
	 * @param array $entry The entry currently being processed.
	 * @param array $form The form currently being processed.
	 */
	public function after_submission( $entry, $form ) {

		// Evaluate the rules configured for the custom_logic setting.
		//$result = $this->is_custom_logic_met( $form, $entry );
		$result = true;

		if ( $result ) {
			$settings = $this->get_form_settings( $form );
			$enabled  = 1 === (int) rgar( $settings, 'enabled' );

			if ( ! $enabled ) {
				return;
			}

			$event_id = $this->create_event( $entry, $settings );
		}
	}

	private function create_event( $entry, $settings ) {
		$title                = $entry[ rgar( $settings, 'event_title' ) ];
		$content              = $entry[ rgar( $settings, 'event_content' ) ];
		$start_date           = strtotime( $entry[ rgar( $settings, 'event_start_date' ) ] );
		$start_date_formatted = date( 'Y-m-d h:i:s', $start_date );
		$end_date             = strtotime( $entry[ rgar( $settings, 'event_end_date' ) ] );
		$end_date_formatted   = date( 'Y-m-d h:i:s', $end_date );
		if ( $end_date < $start_date ) {
			$end_date_formatted = $start_date_formatted;
		}

		$all_day          = ! isset( $entry[ rgar( $settings, 'event_all_day' ) ] );
		$city_tax         = $entry[ rgar( $settings, 'event_tax_city' ) ];
		$neighborhood_tax = $entry[ rgar( $settings, 'event_tax_neighborhood' ) ];
		$venue_exists_id  = $entry[ rgar( $settings, 'event_venue_exists' ) ];
		$venue_name       = $entry[ rgar( $settings, 'event_venue_name' ) ];
		$address          = $entry[ rgar( $settings, 'event_venue_address' ) ];
		$address2         = $entry[ rgar( $settings, 'event_venue_address_2' ) ];
		$city             = $entry[ rgar( $settings, 'event_venue_city' ) ];
		$state            = $entry[ rgar( $settings, 'event_venue_state' ) ];
		$zip              = $entry[ rgar( $settings, 'event_venue_zip' ) ];
		$link             = $entry[ rgar( $settings, 'event_link' ) ];
		$cost             = $entry[ rgar( $settings, 'event_cost' ) ];

		$status = 'draft';
		if ( is_user_logged_in() ) {
			$user   = get_user_by( 'ID', get_current_user_id() );
			$status = in_array( 'approved_evt_sub', $user->roles ) ? 'publish' : 'draft';
		}

		if ( current_user_can( 'manage_options' ) ) {
			$status = 'draft';
		}

		$event_id = tribe_create_event( [
			'post_title'     => $title,
			'post_content'   => $content,
			'post_status'    => $status,
			'EventStartDate' => (string) $start_date_formatted,
			'EventEndDate'   => (string) $end_date_formatted,
		] );

		update_post_meta( $event_id, '_EventStartDate', $start_date_formatted );
		update_post_meta( $event_id, '_EventEndDate', $end_date_formatted );
		update_post_meta( $event_id, '_EventStartDateUTC', $start_date_formatted );
		update_post_meta( $event_id, '_EventEndDateUTC', $end_date_formatted );

		if ( (bool) $all_day ) {
			update_post_meta( $event_id, '_EventAllDay', 'yes' );
		}

		if ( $venue_exists_id !== '0' && ! empty( get_post_status( $venue_exists_id ) ) ) {
			update_post_meta( $event_id, '_EventVenueID', $venue_exists_id );
		} elseif ( ! empty( $venue_name ) ) {
			update_post_meta( $event_id, '_gf_to_tec_new_venue', [
				'name'     => $venue_name,
				'address'  => $address,
				'address2' => $address2,
				'city'     => $city,
				'state'    => $state,
				'zip'      => $zip,
			] );
		}

		if ( ! empty( $cost ) ) {
			update_post_meta( $event_id, '_EventCurrencySymbol', '$' );
			update_post_meta( $event_id, '_EventCurrencyPosition', 'prefix' );
			update_post_meta( $event_id, '_EventCost', (float) $cost );
		}

		if ( ! empty( $link ) ) {
			update_post_meta( $event_id, '_EventURL', $link );
		}

		if ( $status === 'draft' ) {
			update_post_meta( $event_id, '_gf_to_tec_needs_approval', 'yes' );
		}

		if ( ! empty( $city_tax ) ) {
			wp_set_post_terms( $event_id, $city_tax, 'event_cities' );
		}

		if ( ! empty( $neighborhood_tax ) ) {
			wp_set_post_terms( $event_id, $neighborhood_tax, 'event_boroughs' );
		}

		update_post_meta( $event_id, 'submitter_ip', $this->get_user_ip() );

		return $event_id;
	}

	public function maybe_create_venue( $post_id ) {
		if ( get_post_status( $post_id ) !== 'publish' || empty( get_post_meta( $post_id, '_gf_to_tec_new_venue', true ) ) ) {
			return;
		}

		$address_data = get_post_meta( $post_id, '_gf_to_tec_new_venue', true );
		$new_venue    = wp_insert_post( [
			'post_title'    => $address_data['name'],
			'post_type'     => 'tribe_venue',
			'post_status'   => 'publish',
			'_VenueAddress' => $address_data['address'],
			'_VenueCity'    => $address_data['city'],
			'_VenueState'   => $address_data['state'],
			'_VenueZip'     => $address_data['zip'],
		] );

		update_post_meta( $post_id, '_EventVenueID', $new_venue );
		delete_post_meta( $post_id, '_gf_to_tec_new_venue' );
	}


	// # HELPERS -------------------------------------------------------------------------------------------------------

	/**
	 * The feedback callback for the 'mytextbox' setting on the plugin settings page and the 'mytext' setting on the form settings page.
	 *
	 * @param string $value The setting value.
	 *
	 * @return bool
	 */
	public function is_valid_setting( $value ) {
		return strlen( $value ) < 10;
	}

	private function get_user_ip() {
		if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) )   //check ip from share internet
		{
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) )   //to check ip is pass from proxy
		{
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
			$ip = $_SERVER['REMOTE_ADDR'];
		}

		return $ip;
	}

}
