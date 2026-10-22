<?php
/**
 * Robots Rules — robots.txt rules specific to the Events site.
 *
 * Merged with the shared rules by Sites\Shared\Modules\Robots_Txt.
 *
 * @package UCSC_Primary_Sites
 */

namespace UCSC\PrimarySites\Sites\Events\Modules;

use UCSC\PrimarySites\Robots_Rules_Module;

class Robots_Rules extends Robots_Rules_Module {

	public function get_name(): string {
		return 'Events Robots Rules';
	}

	/**
	 * Events-specific rules. None yet; see Robots_Rules_Module::get_rules() for the format.
	 *
	 * @return array<string, string[]>
	 */
	public function get_rules(): array {
		return array(
			'*' => array(
				'Disallow: /events/list/',
				'Disallow: /events/month/',
				'Disallow: /events/week/',
				'Disallow: /events/day/',
				'Disallow: /events/map/',
				'Disallow: /events/summary/',
				'Disallow: /events/photo/',
				'Disallow: /events/today/',
				'Disallow: /events/category/',
				'Disallow: /events/tag/',
				'Disallow: /events/org/',
				'Disallow: /category/',
				'Disallow: /tag/',
				'Disallow: /page/',
				'Disallow: /*/page/',
				'Disallow: /*?*tribe-bar-date=',
				'Disallow: /*?*eventDate=',
				'Disallow: /*?*eventDisplay=',
				'Disallow: /*?*tribe_events_cat=',
				'Disallow: /*?*tribe_filter_bar_state=',
				'Disallow: /*?*hide_subsequent_recurrences=',
				'Disallow: /*?*post_type=tribe_events',
				'Disallow: /*?*paged=',
				'Disallow: /*?*tag=',
				'Disallow: /*?*event_types',
				'Disallow: /*?*related_series=',
				'Disallow: /wp-json/tribe/views/',
			 ),
			);
	}
}