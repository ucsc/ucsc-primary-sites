<?php
/**
 * Uninstall — clean up plugin data when deleted via the admin.
 *
 * @package UCSC_Primary_Sites
 */

// Exit if not called by WordPress.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'ucsc_primary_sites_overrides' );
