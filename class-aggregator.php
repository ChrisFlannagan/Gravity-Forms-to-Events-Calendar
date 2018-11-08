<?php

class adjust_aggregator {

	public function hook() {
		add_filter( 'tribe_aggregator_csv_column_mapping', [ $this, 'set_default_mappings' ] );
	}

	public function set_default_mappings( $mappings ) {
		if ( ! empty( $mappings['events'] ) ) {
			return $mappings;
		}

		$mappings['events'][0] = 'event_tags';
		$mappings['events'][1] = 'event_tags';
		$mappings['events'][2] = 'event_start_date';
		$mappings['events'][3] = 'event_end_date';
		$mappings['events'][4] = 'event_start_time';
		$mappings['events'][5] = 'event_end_time';
		$mappings['events'][6] = 'event_name';
		$mappings['events'][7] = 'event_website';
		$mappings['events'][8] = 'event_description';

		return $mappings;
	}
}
