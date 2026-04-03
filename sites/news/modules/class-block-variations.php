<?php
/**
 * Block Variations — enqueues query loop block variations for the editor.
 *
 * Provides "Media Coverage Loop" and "News Article Loop" variations
 * of the core/query block.
 *
 * Migrated from ucsc-news-functionality/lib/functions/post-types.php
 * and lib/scripts/variations.js.
 *
 * @package UCSC_Primary_Sites
 */

namespace UCSC\PrimarySites\Sites\News\Modules;

use UCSC\PrimarySites\Module;

class Block_Variations extends Module {

	public function get_name(): string {
		return 'Block Variations';
	}

	public function boot(): void {
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_variations' ) );
	}

	/**
	 * Enqueue the block variations script in the editor.
	 *
	 * @return void
	 */
	public function enqueue_variations(): void {
		wp_enqueue_script(
			'ucsc-news-block-variations',
			UCSC_PRIMARY_SITES_URL . 'sites/news/scripts/variations.js',
			array( 'wp-blocks', 'wp-dom-ready' ),
			UCSC_PRIMARY_SITES_VERSION,
			false
		);
	}
}
