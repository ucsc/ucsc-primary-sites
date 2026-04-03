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
use UCSC\PrimarySites\Sites\Events\Modules\Users_Events_Column;
use UCSC\PrimarySites\Sites\News\Modules\Block_Variations;
use UCSC\PrimarySites\Sites\News\Modules\Dashboard_Widgets as News_Dashboard_Widgets;
use UCSC\PrimarySites\Sites\News\Modules\Media_Coverage;
use UCSC\PrimarySites\Sites\Www\Modules\Dashboard_Widgets as Www_Dashboard_Widgets;
use UCSC\PrimarySites\Sites\Www\Modules\Front_Page;

add_action( 'ucsc_primary_sites_register', function ( Site_Registry $registry ) {

	// ── WWW ──────────────────────────────────────────────────────────────
	$registry->add_group( 'www', 'WWW', array(
		'https://www.ucsc.edu',
		'https://*www-ucsc*',
	), array(
		Front_Page::class,
		Www_Dashboard_Widgets::class,
	) );

	// ── News ─────────────────────────────────────────────────────────────
	$registry->add_group( 'news', 'News', array(
		'https://news.ucsc.edu',
		'https://*news-ucsc*',
	), array(
		Media_Coverage::class,
		News_Dashboard_Widgets::class,
		Block_Variations::class,
	) );

	// ── Events ───────────────────────────────────────────────────────────
	$registry->add_group( 'events', 'Events', array(
		'https://events.ucsc.edu',
		'https://*events-ucsc*',
	), array(
		Block_Bindings::class,
		Users_Events_Column::class,
	) );

} );
