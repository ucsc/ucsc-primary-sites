<?php
/**
 * Media Coverage — registers the Media Coverage post type, ACF fields,
 * Article Source block, permalink overrides, and redirects.
 *
 * Migrated from ucsc-news-functionality (post-types.php, acf.php, blocks.php, general.php).
 *
 * @package UCSC_Primary_Sites
 */

namespace UCSC\PrimarySites\Sites\News\Modules;

use UCSC\PrimarySites\Module;

class Media_Coverage extends Module {

	public function get_name(): string {
		return 'Media Coverage';
	}

	/**
	 * This module requires ACF Pro.
	 */
	public function can_load(): bool {
		return function_exists( 'acf_add_local_field_group' );
	}

	public function boot(): void {
		// Post type.
		add_action( 'init', array( $this, 'register_post_type' ) );

		// ACF field group.
		add_action( 'acf/init', array( $this, 'register_acf_fields' ) );

		// Article Source block.
		add_action( 'init', array( $this, 'register_blocks' ) );

		// Permalink and redirect handling.
		add_filter( 'post_type_link', array( $this, 'filter_permalink' ), 10, 2 );
		add_action( 'template_redirect', array( $this, 'redirect_to_external_url' ) );

		// Admin: custom title placeholder.
		add_filter( 'enter_title_here', array( $this, 'title_placeholder' ) );
	}

	// ─── Post Type ──────────────────────────────────────────────────────

	/**
	 * Register the Media Coverage custom post type.
	 *
	 * @return void
	 */
	public function register_post_type(): void {
		$labels = array(
			'name'               => 'Media Coverage',
			'singular_name'      => 'Media Coverage',
			'add_new'            => 'Add New',
			'add_new_item'       => 'Add New Media Coverage',
			'edit_item'          => 'Edit Media Coverage link',
			'new_item'           => 'New Media Coverage link',
			'view_item'          => 'View Media Coverage link',
			'search_items'       => 'Search Media Coverage links',
			'not_found'          => 'No Media Coverage links found',
			'not_found_in_trash' => 'No Media Coverage links found in trash',
			'parent_item_colon'  => '',
			'menu_name'          => 'Media Coverage',
		);

		$args = array(
			'labels'              => $labels,
			'public'              => true,
			'publicly_queryable'  => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_rest'        => true,
			'query_var'           => true,
			'rewrite'             => true,
			'capability_type'     => 'post',
			'has_archive'         => true,
			'hierarchical'        => false,
			'menu_icon'           => 'dashicons-welcome-view-site',
			'menu_position'       => 23,
			'supports'            => array( 'author', 'editor', 'excerpt', 'thumbnail', 'title' ),
			'taxonomies'          => array( 'post_tag', 'category' ),
		);

		register_post_type( 'media_coverage', $args );
	}

	// ─── ACF Fields ─────────────────────────────────────────────────────

	/**
	 * Register the Media Coverage ACF field group.
	 *
	 * @return void
	 */
	public function register_acf_fields(): void {
		acf_add_local_field_group( array(
			'key'                => 'group_ucsc_media_coverage_field_group',
			'title'              => 'Media Coverage',
			'fields'             => array(
				array(
					'key'          => 'field_ucsc_media_coverage_field_article_source',
					'label'        => 'Article Source',
					'name'         => 'article_source',
					'type'         => 'text',
					'required'     => 0,
				),
				array(
					'key'          => 'field_ucsc_media_coverage_field_article_url',
					'label'        => 'Article URL',
					'name'         => 'article_url',
					'type'         => 'url',
					'required'     => 0,
				),
			),
			'location'           => array(
				array(
					array(
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'media_coverage',
					),
				),
			),
			'menu_order'         => 0,
			'position'           => 'normal',
			'style'              => 'default',
			'label_placement'    => 'top',
			'instruction_placement' => 'label',
			'active'             => true,
			'show_in_rest'       => 0,
		) );
	}

	// ─── Blocks ─────────────────────────────────────────────────────────

	/**
	 * Register the Article Source ACF block and its render callback.
	 *
	 * The block.json references renderCallback "ucsc_news_article_source_block"
	 * which ACF calls directly — so we define it as a global function.
	 *
	 * @return void
	 */
	public function register_blocks(): void {
		// Define the global callback ACF expects (from block.json).
		if ( ! function_exists( 'ucsc_news_article_source_block' ) ) {
			function ucsc_news_article_source_block( $block ) {
				$post_id        = get_the_ID();
				$article_source = get_field( 'article_source', $post_id );
				$attrs          = get_block_wrapper_attributes();
				?>
				<p <?php echo $attrs; ?>>
					<?php echo esc_html( $article_source ); ?>
				</p>
				<?php
			}
		}

		register_block_type( UCSC_PRIMARY_SITES_DIR . 'sites/news/blocks/article-source' );
	}

	// ─── Permalinks & Redirects ─────────────────────────────────────────

	/**
	 * Override media_coverage permalinks with the external article URL.
	 *
	 * @param string   $post_link Default permalink.
	 * @param \WP_Post $post      Post object.
	 * @return string
	 */
	public function filter_permalink( string $post_link, \WP_Post $post ): string {
		if ( 'media_coverage' !== $post->post_type ) {
			return $post_link;
		}

		$external_url = get_field( 'article_url', $post->ID );

		if ( ! empty( $external_url ) ) {
			return esc_url( $external_url );
		}

		return $post_link;
	}

	/**
	 * Redirect single media_coverage posts to their external URL.
	 *
	 * @return void
	 */
	public function redirect_to_external_url(): void {
		if ( ! is_singular( 'media_coverage' ) ) {
			return;
		}

		$external_url = get_field( 'article_url', get_the_ID() );

		if ( ! empty( $external_url ) && filter_var( $external_url, FILTER_VALIDATE_URL ) ) {
			wp_redirect( $external_url, 301 );
			exit;
		}
	}

	// ─── Admin ──────────────────────────────────────────────────────────

	/**
	 * Change the title placeholder for Media Coverage posts.
	 *
	 * @param string $title Default placeholder text.
	 * @return string
	 */
	public function title_placeholder( string $title ): string {
		$screen = get_current_screen();

		if ( $screen && 'media_coverage' === $screen->post_type ) {
			return 'Add headline';
		}

		return $title;
	}
}
