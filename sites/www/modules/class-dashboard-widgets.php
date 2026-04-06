<?php
/**
 * Dashboard Widgets — customizes the WordPress admin dashboard for www.ucsc.edu.
 *
 * Removes default/plugin meta boxes and adds a Campus Alert Ribbon widget.
 *
 * Migrated from ucsc-www-functionality/lib/dashboard-widgets.php.
 *
 * @package UCSC_Primary_Sites
 */

namespace UCSC\PrimarySites\Sites\Www\Modules;

use UCSC\PrimarySites\Module;

class Dashboard_Widgets extends Module {

	public function get_name(): string {
		return 'Dashboard Widgets';
	}

	public function boot(): void {
		add_action( 'admin_init', array( $this, 'remove_dashboard_meta' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) );
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
	}

	/**
	 * Enqueue dashboard widget styles on the dashboard page only.
	 *
	 * @param string $hook The current admin page hook.
	 * @return void
	 */
	public function enqueue_styles( string $hook ): void {
		if ( 'index.php' !== $hook ) {
			return;
		}

		wp_enqueue_style(
			'ucsc-dashboard-widget',
			UCSC_PRIMARY_SITES_URL . 'sites/www/css/dashboard-widget.css',
			array(),
			UCSC_PRIMARY_SITES_VERSION
		);
	}

	/**
	 * Register the Campus Alert Ribbon dashboard widget.
	 *
	 * @return void
	 */
	public function register_widgets(): void {
		wp_add_dashboard_widget(
			'ucsc_www_meta_box_widget',
			'Campus Alert Ribbon',
			array( $this, 'render_alert_ribbon_widget' ),
			null,
			null,
			'normal',
			'high'
		);
	}

	/**
	 * Render the Campus Alert Ribbon widget content.
	 *
	 * @return void
	 */
	public function render_alert_ribbon_widget(): void {
		echo '<p>'
			. '<i class="dashicons dashicons-welcome-write-blog"></i> '
			. 'Turn on and edit <a href="https://www.ucsc.edu/wp-admin/site-editor.php'
			. '?p=%2Fwp_template_part%2Fucsc-2022%2F%2Fsite-header&canvas=edit&focusMode=true">'
			. 'the <b>campus alert ribbon</b> in the appearance editor</a>.'
			. '</p>'
			. '<p>'
			. '<i class="dashicons dashicons-flag"></i> '
			. 'For help, use the <a href="https://app.slack.com/client/T044B8579/CALHUCPE2">'
			. '<code>#project-www</code></a> channel in Slack, or '
			. '<a href="mailto:project-www-aaaaau7v2f4pn7qrqr3jho3hjm@ucsc-marcomm.slack.com">'
			. 'send an email</a> directly to that channel.'
			. '</p>';
	}
}
