<?php
/**
 * Abstract Robots Rules Module — base class for per-site robots.txt rules.
 *
 * Subclasses return their rules from get_rules(); this class merges them into
 * the shared Robots_Txt module's output via the `ucsc_primary_sites_robots_rules`
 * filter. The shared group must be active for these rules to be rendered.
 *
 * @package UCSC_Primary_Sites
 */

namespace UCSC\PrimarySites;

abstract class Robots_Rules_Module extends Module {

	/**
	 * Return this site's robots.txt rules.
	 *
	 * Keys are user agents ('*' for all crawlers); values are full directive
	 * lines. Rules under '*' are also applied to the shared rate-limited agents.
	 *
	 * Example:
	 *     return array(
	 *         '*' => array( 'Disallow: /search/' ),
	 *     );
	 *
	 * @return array<string, string[]>
	 */
	abstract public function get_rules(): array;

	public function boot(): void {
		add_filter( 'ucsc_primary_sites_robots_rules', array( $this, 'add_rules' ) );
	}

	/**
	 * Merge this site's rules into the shared rules.
	 *
	 * @param array<string, string[]> $rules Directives keyed by user agent.
	 * @return array<string, string[]>
	 */
	public function add_rules( array $rules ): array {
		foreach ( $this->get_rules() as $agent => $directives ) {
			$rules[ $agent ] = array_merge( $rules[ $agent ] ?? array(), $directives );
		}

		return $rules;
	}
}
