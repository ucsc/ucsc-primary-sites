<?php
/**
 * Robots Txt — appends per-agent rules to the WordPress-generated robots.txt.
 *
 * Loaded on every UCSC primary site (www, news, events) via the "shared" group.
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
		add_filter( 'robots_txt', array( $this, 'filter_robots_txt' ), 10, 2 );
	}

	/**
	 * Append our rules to the default WordPress robots.txt output.
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

		$lines = array();

		foreach ( self::LIMITED_AGENTS as $agent ) {
			$lines[] = '';
			$lines[] = 'User-agent: ' . $agent;
			$lines[] = 'Crawl-delay: ' . (int) self::CRAWL_DELAY;
		}

		foreach ( self::BANNED_AGENTS as $agent ) {
			$lines[] = '';
			$lines[] = 'User-agent: ' . $agent;
			$lines[] = 'Disallow: /';
		}

		if ( empty( $lines ) ) {
			return $output;
		}

		return rtrim( $output ) . "\n" . implode( "\n", $lines ) . "\n";
	}
}
