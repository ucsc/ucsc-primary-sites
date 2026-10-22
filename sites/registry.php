<?php
/**
 * Site Registry — maps named site groups to URL patterns and modules.
 *
 * Each group has a slug, label, URL patterns, and module list. Patterns support
 * wildcards: * (single segment) and ** (greedy). Scheme is ignored during matching.
 *
 * @package UCSC_Primary_Sites
 */

namespace UCSC\PrimarySites\Sites;

use UCSC\PrimarySites\Site_Registry;
use UCSC\PrimarySites\Sites\Events\Modules\Block_Bindings;
use UCSC\PrimarySites\Sites\Events\Modules\Robots_Rules as Events_Robots_Rules;
use UCSC\PrimarySites\Sites\Events\Modules\Users_Events_Column;
use UCSC\PrimarySites\Sites\News\Modules\Block_Variations;
use UCSC\PrimarySites\Sites\News\Modules\Dashboard_Widgets as News_Dashboard_Widgets;
use UCSC\PrimarySites\Sites\News\Modules\Media_Coverage;
use UCSC\PrimarySites\Sites\News\Modules\Robots_Rules as News_Robots_Rules;
use UCSC\PrimarySites\Sites\Shared\Modules\Robots_Txt;
use UCSC\PrimarySites\Sites\Www\Modules\Dashboard_Widgets as Www_Dashboard_Widgets;
use UCSC\PrimarySites\Sites\Www\Modules\Front_Page;
use UCSC\PrimarySites\Sites\Www\Modules\Robots_Rules as Www_Robots_Rules;

add_action( 'ucsc_primary_sites_register', function ( Site_Registry $registry ) {

	// ── Shared ───────────────────────────────────────────────────────────
	// Modules in this group load on all UCSC primary sites.
	$registry->add_group( 'shared', 'Shared (all sites)', array(
		'https://www.ucsc.edu',
		'https://*-www-ucsc.pantheonsite.io',
		'https://news.ucsc.edu',
		'https://*-news-ucsc.pantheonsite.io',
		'https://events.ucsc.edu',
		'https://*-events-ucsc.pantheonsite.io',
	), array(
		Robots_Txt::class,
	) );

	// ── WWW ──────────────────────────────────────────────────────────────
	$registry->add_group( 'www', 'WWW', array(
		'https://www.ucsc.edu',
		'https://*-www-ucsc.pantheonsite.io',
	), array(
		Front_Page::class,
		Www_Dashboard_Widgets::class,
		Www_Robots_Rules::class,
	) );

	// ── News ─────────────────────────────────────────────────────────────
	$registry->add_group( 'news', 'News', array(
		'https://news.ucsc.edu',
		'https://*-news-ucsc.pantheonsite.io',
	), array(
		Media_Coverage::class,
		News_Dashboard_Widgets::class,
		Block_Variations::class,
		News_Robots_Rules::class,
	) );

	// ── Events ───────────────────────────────────────────────────────────
	$registry->add_group( 'events', 'Events', array(
		'https://events.ucsc.edu',
		'https://*-events-ucsc.pantheonsite.io',
	), array(
		Block_Bindings::class,
		Users_Events_Column::class,
		Events_Robots_Rules::class,
	) );

} );
