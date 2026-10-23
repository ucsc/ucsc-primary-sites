<?php
/**
 * Block Bindings — registers a block bindings source that provides the post ID.
 *
 * Migrated from ucsc-events-functionality.
 *
 * @package UCSC_Primary_Sites
 */

namespace UCSC\PrimarySites\Sites\Events\Modules;

use UCSC\PrimarySites\Module;

/**
 * Registers a block bindings source for the current post ID.
 */
class Block_Bindings extends Module {

	/**
	 * Get the human-readable name of this module.
	 *
	 * @return string
	 */
	public function get_name(): string {
		return 'Block Bindings';
	}

	/**
	 * Register this module's hooks.
	 *
	 * @return void
	 */
	public function boot(): void {
		add_action( 'init', array( $this, 'register_post_id_binding' ) );
	}

	/**
	 * Register a block bindings source that exposes the current post ID.
	 *
	 * @return void
	 */
	public function register_post_id_binding(): void {
		register_block_bindings_source(
			'ucsc-events-functionality/post-id',
			array(
				'label'              => __( 'Post ID', 'ucsc-primary-sites' ),
				'get_value_callback' => function () {
					return get_the_ID();
				},
				'uses_context'       => array( 'postId' ),
			)
		);
	}
}
