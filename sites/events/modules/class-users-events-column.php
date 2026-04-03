<?php
/**
 * Users Events Column — adds an "Events" count column to the admin users list.
 *
 * Migrated from ucsc-events-functionality.
 *
 * @package UCSC_Primary_Sites
 */

namespace UCSC\PrimarySites\Sites\Events\Modules;

use UCSC\PrimarySites\Module;

class Users_Events_Column extends Module {

	public function get_name(): string {
		return 'Users Events Column';
	}

	public function boot(): void {
		add_filter( 'manage_users_columns', array( $this, 'add_column' ) );
		add_action( 'manage_users_custom_column', array( $this, 'populate_column' ), 10, 3 );
	}

	/**
	 * Insert an "Events" column before the "Posts" column in the users table.
	 *
	 * @param array $columns Existing columns.
	 * @return array Modified columns.
	 */
	public function add_column( array $columns ): array {
		$new_columns = array();

		foreach ( $columns as $key => $value ) {
			if ( 'posts' === $key ) {
				$new_columns['tribe_events'] = __( 'Events', 'ucsc-primary-sites' );
			}
			$new_columns[ $key ] = $value;
		}

		return $new_columns;
	}

	/**
	 * Render the event count for each user, linked to a filtered admin view.
	 *
	 * @param string $output      Custom column output.
	 * @param string $column_name Column identifier.
	 * @param int    $user_id     User ID.
	 * @return string Column content.
	 */
	public function populate_column( string $output, string $column_name, int $user_id ): string {
		if ( 'tribe_events' !== $column_name ) {
			return $output;
		}

		$count = count_user_posts( $user_id, 'tribe_events', true );

		if ( $count > 0 ) {
			return sprintf(
				'<a href="%s">%d</a>',
				admin_url( 'edit.php?post_type=tribe_events&author=' . $user_id ),
				$count
			);
		}

		return '0';
	}
}
