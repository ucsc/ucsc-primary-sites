<?php
/**
 * Icon Block — replaces the outermost/icon-block <div> with <h1> on the front page.
 *
 * Migrated from ucsc-www-functionality/lib/functions.php.
 *
 * @package UCSC_Primary_Sites
 */

namespace UCSC\PrimarySites\Sites\Www\Modules;

use UCSC\PrimarySites\Module;

class Front_Page extends Module {

	public function get_name(): string {
		return 'Front Page';
	}

	public function boot(): void {
		add_filter( 'render_block_outermost/icon-block', array( $this, 'filter_on_front_page' ), 10, 2 );
	}

	/**
	 * Replace the icon-block wrapper <div> with an <h1> on the front page.
	 *
	 * The filter removes itself after running once to avoid affecting
	 * subsequent icon blocks on the same page.
	 *
	 * @param string $block_content The rendered block HTML.
	 * @param array  $block         The parsed block data.
	 * @return string Modified block content.
	 */
	public function filter_on_front_page( string $block_content, array $block ): string {
		if ( ! is_front_page() ) {
			return $block_content;
		}

		// Replace the outermost wrapper div with h1.
		$block_content = preg_replace( '/<div([\s>])/', '<h1$1', $block_content, 1 );
		$pos = strrpos( $block_content, '</div>' );
		if ( false !== $pos ) {
			$block_content = substr_replace( $block_content, '</h1>', $pos, 6 );
		}

		// Only modify the first icon block per page load.
		remove_filter( 'render_block_outermost/icon-block', array( $this, 'filter_on_front_page' ), 10 );

		return $block_content;
	}
}
