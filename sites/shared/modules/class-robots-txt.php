<?php
/**
 * Robots Txt — merges shared and per-site rules into the WordPress-generated robots.txt.
 *
 * Loaded on every UCSC primary site (www, news, events) via the "shared" group.
 * Site-specific rules are added through the `ucsc_primary_sites_robots_rules`
 * filter, usually by a Robots_Rules_Module subclass in the site's group.
 *
 * @package UCSC_Primary_Sites
 */

namespace UCSC\PrimarySites\Sites\Shared\Modules;

use UCSC\PrimarySites\Module;

class Robots_Txt extends Module {

	/**
	 * Crawl-delay (in seconds) applied to every agent in LIMITED_AGENTS.
	 */
	private const CRAWL_DELAY = 120;

	/**
	 * Agents we want to rate-limit (not block).
	 *
	 * Each entry receives a Crawl-delay of CRAWL_DELAY seconds.
	 */
	private const LIMITED_AGENTS = array(
		// Amazon
		'Amazonbot',
		// OpenAI
		'GPTBot',
		// Anthropic
		'ClaudeBot',
		'anthropic-ai',
		// Google (AI training opt-out token)
		'Google-Extended',
		// Common Crawl (feeds many training datasets)
		'CCBot',
		// ByteDance / TikTok
		'Bytespider',
		// Apple (AI training opt-out token)
		'Applebot-Extended',
		// Meta
		'Meta-ExternalAgent',
		// Others
		'cohere-ai',
		'ImagesiftBot',
		'Omgilibot',
	);

	/**
	 * Agents we want to ban outright (Disallow: /).
	 *
	 * Aggressive SEO scrapers and known abusive crawlers that provide no value
	 * to the public-facing UCSC sites.
	 */
	private const BANNED_AGENTS = array(
		'AhrefsBot',
		'SemrushBot',
		'MJ12bot',
		'DotBot',
		'PetalBot',
		'BLEXBot',
		'DataForSeoBot',
		'SerpstatBot',
		'ZoominfoBot',
		'Barkrowler',
		'magpie-crawler',
		'MauiBot',
		'SeekportBot',
		'Nimbostratus-Bot',
	);

	public function get_name(): string {
		return 'Robots Text';
	}

	public function boot(): void {
		// Run last so we render the final file. SEO plugins that re-parse robots.txt
		// (AIOSEO at 10000, Yoast at 99999) mishandle groups with several
		// User-agent lines, so they must see WP's output, not ours.
		add_filter( 'robots_txt', array( $this, 'filter_robots_txt' ), PHP_INT_MAX, 2 );
	}

	/**
	 * Rebuild the WordPress robots.txt output with our shared and per-site rules.
	 *
	 * The incoming output (WP core defaults, the core sitemap line, and anything
	 * added by other plugins) is parsed into per-agent groups, our rules are merged
	 * in, and the result is rendered with agents that share identical rules
	 * combined into one group.
	 *
	 * @param string $output The default robots.txt contents.
	 * @param bool   $public Whether the site is set to be publicly indexable.
	 * @return string
	 */
	public function filter_robots_txt( string $output, bool $public ): string {
		// If the site is set to discourage indexing, leave WP's output alone.
		if ( ! $public ) {
			return $output;
		}

		list( $rules, $sitemaps ) = $this->parse( $output );

		foreach ( self::LIMITED_AGENTS as $agent ) {
			$rules[ $agent ][] = 'Crawl-delay: ' . (int) self::CRAWL_DELAY;
		}

		foreach ( self::BANNED_AGENTS as $agent ) {
			$rules[ $agent ][] = 'Disallow: /';
		}

		/**
		 * Filters the robots.txt rules before they are rendered.
		 *
		 * Site-specific modules use this to add their own rules. Keys are user
		 * agents ('*' for all crawlers); values are full directive lines such as
		 * 'Disallow: /search/'. Sitemap lines are handled separately.
		 *
		 * @param array<string, string[]> $rules Directives keyed by user agent.
		 */
		$rules = apply_filters( 'ucsc_primary_sites_robots_rules', $rules );

		// A crawler obeys only the most specific group that names it, so the
		// rate-limited agents would otherwise ignore every rule under '*'.
		if ( ! empty( $rules['*'] ) ) {
			foreach ( self::LIMITED_AGENTS as $agent ) {
				$rules[ $agent ] = array_merge( $rules[ $agent ] ?? array(), $rules['*'] );
			}
		}

		return $this->render( $rules, $sitemaps );
	}

	/**
	 * Parse robots.txt content into per-agent directives and sitemap lines.
	 *
	 * Consecutive User-agent lines share the directives that follow them.
	 * Comments and blank lines are dropped.
	 *
	 * @param string $output Raw robots.txt content.
	 * @return array{0: array<string, string[]>, 1: string[]}
	 */
	private function parse( string $output ): array {
		$rules    = array();
		$sitemaps = array();
		$agents   = array();
		$in_group = false;

		foreach ( preg_split( '/\r\n|\r|\n/', $output ) as $line ) {
			$line = trim( preg_replace( '/#.*$/', '', $line ) );
			if ( '' === $line || false === strpos( $line, ':' ) ) {
				continue;
			}

			list( $field, $value ) = array_map( 'trim', explode( ':', $line, 2 ) );

			if ( 0 === strcasecmp( $field, 'Sitemap' ) ) {
				$sitemaps[] = 'Sitemap: ' . $value;
				continue;
			}

			if ( 0 === strcasecmp( $field, 'User-agent' ) ) {
				// A User-agent line after directives starts a new group.
				if ( $in_group ) {
					$agents   = array();
					$in_group = false;
				}
				$agents[]        = $value;
				$rules[ $value ] = $rules[ $value ] ?? array();
				continue;
			}

			// Directives before any User-agent line are invalid; skip them.
			if ( empty( $agents ) ) {
				continue;
			}

			$in_group = true;
			foreach ( $agents as $agent ) {
				// rtrim keeps an empty value (e.g. Yoast's "Disallow:") free of trailing space.
				$rules[ $agent ][] = rtrim( $field . ': ' . $value );
			}
		}

		return array( $rules, $sitemaps );
	}

	/**
	 * Render rules and sitemap lines as robots.txt content.
	 *
	 * The '*' group is written first. Agents with identical directives share a
	 * single group (one User-agent line each), duplicate directives within a
	 * group are removed, and agents with no directives are omitted.
	 *
	 * @param array<string, string[]> $rules    Directives keyed by user agent.
	 * @param string[]                $sitemaps Sitemap lines.
	 * @return string
	 */
	private function render( array $rules, array $sitemaps ): string {
		if ( isset( $rules['*'] ) ) {
			$rules = array( '*' => $rules['*'] ) + $rules;
		}

		// Group agents by their directive set, keeping first-seen order.
		$groups = array();

		foreach ( $rules as $agent => $directives ) {
			$directives = array_values( array_unique( $directives ) );
			if ( empty( $directives ) ) {
				continue;
			}

			$key = implode( "\n", $directives );
			if ( ! isset( $groups[ $key ] ) ) {
				$groups[ $key ] = array();
			}
			$groups[ $key ][] = 'User-agent: ' . $agent;
		}

		$blocks = array();

		foreach ( $groups as $directives => $agent_lines ) {
			$blocks[] = implode( "\n", $agent_lines ) . "\n" . $directives;
		}

		$sitemaps = array_unique( $sitemaps );
		if ( ! empty( $sitemaps ) ) {
			$blocks[] = implode( "\n", $sitemaps );
		}

		return implode( "\n\n", $blocks ) . "\n";
	}
}
