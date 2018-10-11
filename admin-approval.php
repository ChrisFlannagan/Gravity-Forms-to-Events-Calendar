<?php

global $wpdb;

if ( isset( $_POST['bulk-action'] ) && $_POST['bulk-action'] === 'delete' ) {
	foreach ( $_POST['events'] as $event_id ) {
	    wp_delete_post( $event_id );
	}
}

if ( isset( $_POST['bulk-action'] ) && $_POST['bulk-action'] === 'approve' ) {
	foreach ( $_POST['events'] as $event_id ) {
		$tec_gf = GF_TEC_AddOn::get_instance();
		$updated = wp_update_post(
			[
				'ID'          => $event_id,
				'post_status' => 'publish',
			]
		);

		if ( ! is_wp_error( $updated ) ) {
		    delete_post_meta( $event_id, '_gf_to_tec_needs_approval' );
			$tec_gf->maybe_create_venue( $event_id );
		}
	}
}

$needs_approval = $wpdb->get_col( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key='_gf_to_tec_needs_approval' AND meta_value='yes'" );
if ( empty( $needs_approval ) ) {
	echo "No events to approve at this time.";
} else { ?>
    <form action="" method="post">
        <p>
            <select name="bulk-action">
                <option value="0">Select an Action</option>
                <option value="approve">Approve</option>
                <option value="delete">Delete</option>
            </select>
            <input type="submit" class="button button-primary button-large" value="Perform Bulk Action">
        </p>
        <table class='order-results-list widefat fixed'>
            <thead>
            <tr>
                <th class='manage-column column-cb column-columnname' scope='col'>Event ID</th>
                <th class='manage-column column-cb column-columnname' scope='col'>Title</th>
                <th class='manage-column column-cb column-columnname' scope='col'>Start Date</th>
                <th class='manage-column column-cb column-columnname' scope='col'>Submitter</th>
            </tr>
			<?php foreach ( $needs_approval as $event_id ) : ?>
                <tr>
                    <td valign='top' class='column-columnname'>
                        <input type="checkbox" name="events[]" value="<?php esc_html_e( $event_id ); ?>"/>
                        <a href="<?php esc_html_e( get_edit_post_link( $event_id ) ); ?>"
                           target="_blank"><?php esc_html_e( $event_id ); ?></a>
                    </td>
                    <td valign='top' class='column-columnname'>
						<?php esc_html_e( get_the_title( $event_id ) ); ?>
                    </td>
                    <td valign='top' class='column-columnname'>
						<?php esc_html_e( get_post_meta( $event_id, '_EventStartDate', true ) ); ?>
                    </td>
                    <td valign='top' class='column-columnname'>
						<?php esc_html_e( get_post_meta( $event_id, 'submitter_ip', true ) ); ?>
                    </td>
                </tr>
			<?php endforeach; ?>
        </table>
    </form>

<?php }
