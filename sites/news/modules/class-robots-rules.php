<?php
/**
 * Robots Rules — robots.txt rules specific to the News site.
 *
 * Merged with the shared rules by Sites\Shared\Modules\Robots_Txt.
 *
 * @package UCSC_Primary_Sites
 */

namespace UCSC\PrimarySites\Sites\News\Modules;

use UCSC\PrimarySites\Robots_Rules_Module;

/**
 * Adds the robots.txt rules for the News site.
 */
class Robots_Rules extends Robots_Rules_Module {

	/**
	 * Get the human-readable name of this module.
	 *
	 * @return string
	 */
	public function get_name(): string {
		return 'News Robots Rules';
	}

	/**
	 * News-specific rules. None yet; see Robots_Rules_Module::get_rules() for the format.
	 *
	 * @return array<string, string[]>
	 */
	public function get_rules(): array {
		return array();
	}
}
