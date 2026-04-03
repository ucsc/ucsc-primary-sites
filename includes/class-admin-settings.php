<?php
/**
 * Admin Settings — provides a settings page for toggling site module groups.
 *
 * Each registered site group appears as a row with three states:
 *   - Auto (default): modules load only when home_url() matches a pattern.
 *   - On:  modules always load, regardless of URL.
 *   - Off: modules never load, regardless of URL.
 *
 * @package UCSC_Primary_Sites
 */

namespace UCSC\PrimarySites;

class Admin_Settings {

	private const OPTION_KEY = 'ucsc_primary_sites_overrides';
	private const PAGE_SLUG  = 'ucsc-primary-sites';
	private const NONCE_NAME = 'ucsc_primary_sites_nonce';

	private Site_Registry $registry;

	public function __construct( Site_Registry $registry ) {
		$this->registry = $registry;
	}

	/**
	 * Register hooks for the admin settings page.
	 *
	 * @return void
	 */
	public function boot(): void {
		add_action( 'admin_menu', array( $this, 'add_menu_page' ) );
		add_action( 'admin_init', array( $this, 'handle_save' ) );
	}

	/**
	 * Add the settings page under the Settings menu.
	 *
	 * @return void
	 */
	public function add_menu_page(): void {
		add_options_page(
			'UCSC Primary Sites',
			'UCSC Primary Sites',
			'manage_options',
			self::PAGE_SLUG,
			array( $this, 'render_page' )
		);
	}

	/**
	 * Process form submission.
	 *
	 * @return void
	 */
	public function handle_save(): void {
		if ( ! isset( $_POST[ self::NONCE_NAME ] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $_POST[ self::NONCE_NAME ], self::PAGE_SLUG ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$groups    = $this->registry->get_groups();
		$overrides = array();

		foreach ( array_keys( $groups ) as $slug ) {
			$value = isset( $_POST['ucsc_sites'][ $slug ] ) ? sanitize_text_field( $_POST['ucsc_sites'][ $slug ] ) : 'auto';
			if ( 'auto' !== $value ) {
				$overrides[ $slug ] = $value;
			}
		}

		update_option( self::OPTION_KEY, $overrides );

		// Redirect back to the settings page with a success flag (Post/Redirect/Get).
		wp_safe_redirect( add_query_arg( 'settings-updated', 'true', menu_page_url( self::PAGE_SLUG, false ) ) );
		exit;
	}

	/**
	 * Render the settings page.
	 *
	 * @return void
	 */
	public function render_page(): void {
		$groups    = $this->registry->get_groups();
		$overrides = get_option( self::OPTION_KEY, array() );
		?>
		<div class="wrap">
			<h1>UCSC Primary Sites</h1>
			<p>
				By default, site modules load automatically when the site URL matches
				a registered pattern. Use the overrides below to force modules on or off
				regardless of the current domain.
			</p>

			<?php if ( isset( $_GET['settings-updated'] ) ) : ?>
				<div class="notice notice-success is-dismissible">
					<p>Settings saved.</p>
				</div>
			<?php endif; ?>

			<form method="post">
				<?php wp_nonce_field( self::PAGE_SLUG, self::NONCE_NAME ); ?>
				<table class="widefat fixed striped" style="max-width: 700px;">
					<thead>
						<tr>
							<th style="width: 30%;">Site</th>
							<th style="width: 40%;">Patterns</th>
							<th style="width: 30%;">Override</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $groups as $slug => $group ) :
							$current = $overrides[ $slug ] ?? 'auto';
						?>
						<tr>
							<td><strong><?php echo esc_html( $group['label'] ); ?></strong></td>
							<td>
								<code style="font-size: 12px;">
									<?php echo esc_html( implode( ', ', $group['patterns'] ) ); ?>
								</code>
							</td>
							<td>
								<select name="ucsc_sites[<?php echo esc_attr( $slug ); ?>]">
									<option value="auto" <?php selected( $current, 'auto' ); ?>>Auto (match URL)</option>
									<option value="on" <?php selected( $current, 'on' ); ?>>On (always load)</option>
									<option value="off" <?php selected( $current, 'off' ); ?>>Off (never load)</option>
								</select>
							</td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
				<p class="submit">
					<?php submit_button( 'Save Changes', 'primary', 'submit', false ); ?>
				</p>
			</form>
		</div>
		<?php
	}
}
