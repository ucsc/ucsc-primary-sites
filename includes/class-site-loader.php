<?php
/**
 * Site Loader — resolves the current site URL and boots matching modules.
 *
 * @package UCSC_Primary_Sites
 */

namespace UCSC\PrimarySites;

class Site_Loader {

	private Site_Registry $registry;

	/**
	 * Modules that were successfully instantiated during this request.
	 *
	 * @var Module[]
	 */
	private array $active_modules = array();

	public function __construct( Site_Registry $registry ) {
		$this->registry = $registry;
	}

	/**
	 * Determine the current site URL, find matching modules, and boot them.
	 *
	 * @return void
	 */
	public function load(): void {
		$current_url = $this->get_current_url();
		$classes     = $this->registry->get_modules_for_url( $current_url );

		if ( empty( $classes ) ) {
			return;
		}

		foreach ( $classes as $class ) {
			$module = $this->instantiate_module( $class );
			if ( $module ) {
				$this->active_modules[] = $module;
			}
		}

		/**
		 * Fires after all matched modules have been booted.
		 *
		 * @param Module[] $active_modules The modules that were loaded.
		 * @param string   $current_url    The resolved home_url().
		 */
		do_action( 'ucsc_primary_sites_loaded', $this->active_modules, $current_url );
	}

	/**
	 * Get the list of modules that were loaded for the current request.
	 *
	 * @return Module[]
	 */
	public function get_active_modules(): array {
		return $this->active_modules;
	}

	/**
	 * Resolve the current site URL.
	 *
	 * Filterable so tests or edge-case environments can override.
	 *
	 * @return string
	 */
	private function get_current_url(): string {
		/**
		 * Filters the URL used for site matching.
		 *
		 * @param string $url The value of home_url().
		 */
		return apply_filters( 'ucsc_primary_sites_current_url', home_url() );
	}

	/**
	 * Validate and instantiate a single module.
	 *
	 * @param string $class Fully-qualified class name.
	 * @return Module|null The booted module, or null on failure.
	 */
	private function instantiate_module( string $class ): ?Module {
		if ( ! class_exists( $class ) ) {
			$this->log_error( "Module class not found: {$class}" );
			return null;
		}

		if ( ! is_subclass_of( $class, Module::class ) ) {
			$this->log_error( "Class {$class} does not extend " . Module::class );
			return null;
		}

		/** @var Module $module */
		$module = new $class();

		// Modules can declare dependencies; skip if unmet.
		if ( ! $module->can_load() ) {
			return null;
		}

		$module->boot();

		return $module;
	}

	/**
	 * Log an error when WP_DEBUG is enabled.
	 *
	 * @param string $message Error message.
	 * @return void
	 */
	private function log_error( string $message ): void {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( '[UCSC Primary Sites] ' . $message );
		}
	}
}
