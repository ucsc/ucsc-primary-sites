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
		return array();
	}
}
