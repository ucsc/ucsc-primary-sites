<?php
/**
 * Site Registry — maps named site groups to URL patterns and module classes.
 *
 * @package UCSC_Primary_Sites
 */

namespace UCSC\PrimarySites;

class Site_Registry {

	/**
	 * Registered site groups.
	 *
	 * Keyed by group slug. Each value is an associative array:
	 *   'label'    => string   Human-readable name (e.g. "WWW").
	 *   'patterns' => string[] URL patterns for automatic matching.
	 *   'modules'  => string[] Fully-qualified class names extending Module.
	 *
	 * @var array<string, array>
	 */
	private array $groups = array();

	/**
	 * Register a named site group.
	 *
	 * @param string   $slug     Unique identifier (e.g. 'www', 'news').
	 * @param string   $label    Human-readable label for the admin UI.
	 * @param string[] $patterns URL patterns for automatic matching.
	 *                           Supports * (single-segment) and ** (greedy) wildcards.
	 * @param string[] $modules  Fully-qualified Module subclass names.
	 * @return self
	 */
	public function add_group( string $slug, string $label, array $patterns, array $modules ): self {
		$this->groups[ $slug ] = array(
			'label'    => $label,
			'patterns' => $patterns,
			'modules'  => $modules,
		);
		return $this;
	}

	/**
	 * Get all registered groups.
	 *
	 * @return array<string, array>
	 */
	public function get_groups(): array {
		return $this->groups;
	}

	/**
	 * Return every module class that should load for the given URL, taking
	 * admin overrides into account.
	 *
	 * @param string $url The URL to test (typically home_url()).
	 * @return string[] Unique, fully-qualified module class names.
	 */
	public function get_modules_for_url( string $url ): array {
		$overrides = get_option( 'ucsc_primary_sites_overrides', array() );
		$matched   = array();

		foreach ( $this->groups as $slug => $group ) {
			// Check admin override first.
			if ( isset( $overrides[ $slug ] ) ) {
				if ( 'on' === $overrides[ $slug ] ) {
					$matched = array_merge( $matched, $group['modules'] );
				}
				// 'off' means explicitly disabled — skip URL matching.
				continue;
			}

			// No override — fall back to URL pattern matching.
			foreach ( $group['patterns'] as $pattern ) {
				if ( $this->url_matches_pattern( $url, $pattern ) ) {
					$matched = array_merge( $matched, $group['modules'] );
					break; // One matching pattern is enough for this group.
				}
			}
		}

		return array_unique( $matched );
	}

	/**
	 * Test whether a URL matches a pattern.
	 *
	 * The pattern is converted to a regex where:
	 *   *  → zero or more non-slash characters
	 *   ** → one or more characters including slashes
	 *
	 * Matching is case-insensitive. Scheme (http/https) and trailing slashes
	 * are normalized before comparison.
	 *
	 * @param string $url     The URL to test.
	 * @param string $pattern The pattern to match against.
	 * @return bool
	 */
	private function url_matches_pattern( string $url, string $pattern ): bool {
		// Strip scheme so http vs https never causes a mismatch.
		$url     = preg_replace( '#^https?://#i', '', $url );
		$pattern = preg_replace( '#^https?://#i', '', $pattern );

		// Normalize trailing slashes.
		$url     = rtrim( $url, '/' );
		$pattern = rtrim( $pattern, '/' );

		// Escape regex special chars, then convert wildcards.
		$regex = preg_quote( $pattern, '#' );
		$regex = str_replace( '\\*\\*', '(.+)', $regex );    // ** → greedy match
		$regex = str_replace( '\\*', '([^/]*)', $regex );     // *  → zero-or-more non-slash chars

		return (bool) preg_match( '#^' . $regex . '$#i', $url );
	}
}
