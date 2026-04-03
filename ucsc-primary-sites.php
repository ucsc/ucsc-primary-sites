<?php
/**
 * Plugin Name: UCSC Primary Sites
 * Description: Loads site-specific functionality based on the current site URL (home_url()).
 * Version:     1.0.0
 * Author:      UC Santa Cruz
 * Text Domain: ucsc-primary-sites
 *
 * @package UCSC_Primary_Sites
 */

namespace UCSC\PrimarySites;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Plugin constants.
define( 'UCSC_PRIMARY_SITES_VERSION', '1.0.0' );
define( 'UCSC_PRIMARY_SITES_DIR', plugin_dir_path( __FILE__ ) );
define( 'UCSC_PRIMARY_SITES_URL', plugin_dir_url( __FILE__ ) );

// Autoloader.
spl_autoload_register( function ( $class ) {
	// Only handle classes in our namespace.
	$prefix = 'UCSC\\PrimarySites\\';
	if ( strncmp( $prefix, $class, strlen( $prefix ) ) !== 0 ) {
		return;
	}

	// Strip namespace prefix and convert to file path.
	// e.g. UCSC\PrimarySites\Sites\Example\Modules\Example_Module
	//   → parts: [ 'Sites', 'Example', 'Modules', 'Example_Module' ]
	//   → file:  sites/example/modules/class-example-module.php
	$relative = substr( $class, strlen( $prefix ) );
	$parts    = explode( '\\', $relative );
	$filename = 'class-' . strtolower( str_replace( '_', '-', array_pop( $parts ) ) ) . '.php';
	$subdir   = strtolower( implode( '/', array_map( function ( $part ) {
		return str_replace( '_', '-', $part );
	}, $parts ) ) );

	// Namespace maps directly to directory structure:
	//   UCSC\PrimarySites\Admin_Settings        → includes/class-admin-settings.php
	//   UCSC\PrimarySites\Sites\Www\Modules\... → sites/www/modules/class-....php
	$paths = array(
		UCSC_PRIMARY_SITES_DIR . 'includes/' . ( $subdir ? $subdir . '/' : '' ) . $filename,
		UCSC_PRIMARY_SITES_DIR . ( $subdir ? $subdir . '/' : '' ) . $filename,
	);

	foreach ( $paths as $path ) {
		if ( file_exists( $path ) ) {
			require_once $path;
			return;
		}
	}
} );

// Load the site registry (URL patterns → modules).
require_once UCSC_PRIMARY_SITES_DIR . 'sites/registry.php';

/**
 * Bootstrap the plugin.
 *
 * Fires on `plugins_loaded` so home_url() and other WP APIs are available.
 */
add_action( 'plugins_loaded', function () {
	$registry = new Site_Registry();

	// Fire registration hooks — site register.php files and external code attach here.
	do_action( 'ucsc_primary_sites_register', $registry );

	// Admin settings page (always available so overrides can be configured).
	if ( is_admin() ) {
		$admin = new Admin_Settings( $registry );
		$admin->boot();
	}

	$loader = new Site_Loader( $registry );
	$loader->load();
} );
