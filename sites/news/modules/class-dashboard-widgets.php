<?php
/**
 * Dashboard Widgets — customizes the WordPress admin dashboard for news.ucsc.edu.
 *
 * Removes default meta boxes and adds UC Santa Cruz News help widget
 * and Popular Tags widget.
 *
 * Migrated from ucsc-news-functionality/lib/functions/dashboard-widgets.php.
 *
 * @package UCSC_Primary_Sites
 */

namespace UCSC\PrimarySites\Sites\News\Modules;

use UCSC\PrimarySites\Module;

class Dashboard_Widgets extends Module {

	public function get_name(): string {
		return 'Dashboard Widgets';
	}

	public function boot(): void {
		add_action( 'admin_init', array( $this, 'remove_dashboard_meta' ) );
		add_action( 'wp_dashboard_setup', array( $this, 'register_widgets' ) );
	}

	/**
	 * Remove default and plugin dashboard meta boxes.
	 *
	 * @return void
	 */
	public function remove_dashboard_meta(): void {
		remove_meta_box( 'dashboard_plugins', 'dashboard', 'normal' );
		remove_meta_box( 'dashboard_primary', 'dashboard', 'side' );
		remove_meta_box( 'dashboard_secondary', 'dashboard', 'normal' );
		remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );
		remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );
		remove_meta_box( 'wpseo-dashboard-overview', 'dashboard', 'normal' );
		remove_meta_box( 'wpseo-wincher-dashboard-overview', 'dashboard', 'normal' );
	}

	/**
	 * Register custom dashboard widgets.
	 *
	 * @return void
	 */
	public function register_widgets(): void {
		// Help / intro widget.
		wp_add_dashboard_widget(
			'ucsc_intro_meta_box_widget',
			'UC Santa Cruz News',
			array( $this, 'render_intro_widget' ),
			null,
			null,
			'normal',
			'high'
		);

		// Popular Tags widget.
		add_meta_box(
			'ucsc_taxonomy_terms_meta_box',
			'Popular Tags',
			array( $this, 'render_popular_tags_widget' ),
			'dashboard',
			'side'
		);
	}

	/**
	 * Render the news help / intro widget.
	 *
	 * @return void
	 */
	public function render_intro_widget(): void {
		echo '<p>'
			. '<i class="dashicons dashicons-welcome-write-blog"></i> '
			. 'See the <a href="https://communications.ucsc.edu/editorial/editorial-style-guide/">'
			. 'UCSC editorial style guide</a> for writing guidelines.'
			. '</p>'
			. '<p>'
			. '<i class="dashicons dashicons-tag"></i> '
			. 'See the <a href="https://docs.google.com/document/d/1gGwGWUMPyNMk9ddqGx3Tdp805W3uJTfvpZudOUjf2Xk/">'
			. 'taxonomy guide</a> for help categorizing your posts.'
			. '</p>'
			. '<p>'
			. '<i class="dashicons dashicons-format-image"></i> '
			. 'Images are available in the campus <a href="https://photos.ucsc.edu">photo library</a>.'
			. '</p>'
			. '<p>'
			. '<i class="dashicons dashicons-flag"></i> '
			. 'For help, use the <a href="https://app.slack.com/client/T044B8579/C08PC13JN11">'
			. '<code>#news-help</code></a> channel in Slack, or '
			. '<a href="mailto:news-help-aaaap74fpvb7grwfsyojvp6uhy@ucsc-marcomm.slack.com">'
			. 'send an email</a>.'
			. '</p>';
	}

	/**
	 * Render the Popular Tags dashboard widget.
	 *
	 * @return void
	 */
	public function render_popular_tags_widget(): void {
		$terms = get_terms( array(
			'taxonomy'   => 'post_tag',
			'hide_empty' => 1,
			'number'     => 16,
			'orderby'    => 'count',
			'order'      => 'DESC',
		) );

		if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
			echo '<ul>';
			foreach ( $terms as $term ) {
				printf(
					'<li><a href="%s">%s (%d)</a></li>',
					esc_url( get_term_link( $term->term_id, 'post_tag' ) ),
					esc_html( $term->name ),
					$term->count
				);
			}
			echo '</ul>';
		} else {
			echo '<p>No terms found in this taxonomy.</p>';
		}
	}
}
