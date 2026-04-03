<?php
/**
 * Abstract Module — base class that every site-specific feature module extends.
 *
 * @package UCSC_Primary_Sites
 */

namespace UCSC\PrimarySites;

abstract class Module {

	/**
	 * Human-readable label for this module (used in logging / debugging).
	 *
	 * @return string
	 */
	abstract public function get_name(): string;

	/**
	 * Boot the module — register hooks, filters, shortcodes, etc.
	 *
	 * Called once per request if the module's site pattern matches and can_load() returns true.
	 *
	 * @return void
	 */
	abstract public function boot(): void;

	/**
	 * Check whether this module's dependencies are met.
	 *
	 * Override in subclasses to test for required plugins, PHP extensions, etc.
	 * Return false to silently skip loading this module.
	 *
	 * @return bool
	 */
	public function can_load(): bool {
		return true;
	}
}
