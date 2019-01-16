<?php
/**
 * Class AMP_Analytics_Options_Submenu_Page
 *
 * @package AMP
 */

/**
 * Class AMP_Analytics_Options_Submenu_Page
 */
class AMP_Analytics_Options_Submenu_Page {

	/**
	 * Render entry.
	 *
	 * @param string $id     Entry ID.
	 * @param string $type   Entry type.
	 * @param string $config Entry config as serialized JSON.
	 */
	private function render_entry( $id = '', $type = '', $config = '{}' ) {
		$is_existing_entry = ! empty( $id );

		if ( $is_existing_entry ) {
			$entry_slug = sprintf( '%s%s', ( $type ? $type . '-' : '' ), substr( $id, - 6 ) );
			/* translators: %s: the entry slug. */
			$analytics_title = sprintf( __( 'Analytics: %s', 'amp' ), $entry_slug );
		} else {
			$analytics_title = __( 'Add new entry:', 'amp' );
			$id              = '__new__';
		}

		// Tidy-up the JSON for display.
		if ( $config ) {
			$options = ( 128 /* JSON_PRETTY_PRINT */ | 64 /* JSON_UNESCAPED_SLASHES */ );
			$config  = wp_json_encode( json_decode( $config ), $options );
		}

		$id_base = sprintf( '%s[analytics][%s]', AMP_Options_Manager::OPTION_NAME, $id );
		?>
		<div class="analytics-data-container">
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<h2>
					<?php echo esc_html( $analytics_title ); ?>
				</h2>
				<div class="options">
					<p>
						<label>
							<?php esc_html_e( 'Type:', 'amp' ); ?>
							<input class="option-input" type="text" required name="<?php echo esc_attr( $id_base . '[type]' ); ?>" placeholder="<?php esc_attr_e( 'e.g. googleanalytics', 'amp' ); ?>" value="<?php echo esc_attr( $type ); ?>" />
						</label>
						<label>
							<?php esc_html_e( 'ID:', 'amp' ); ?>
							<input type="text" value="<?php echo esc_attr( $is_existing_entry ? $id : '' ); ?>" readonly />
						</label>
							<input type="hidden" name="<?php echo esc_attr( $id_base . '[id]' ); ?>" value="<?php echo esc_attr( $id ); ?>" />
					</p>
					<p>
						<label>
							<?php esc_html_e( 'JSON Configuration:', 'amp' ); ?>
							<br />
							<textarea
								rows="10"
								cols="100"
								name="<?php echo esc_attr( $id_base . '[config]' ); ?>"
								class="amp-analytics-input"
								placeholder="{...}"
								title="<?php esc_attr_e( 'A JSON object begins with a &#8220;{&#8221; and ends with a &#8220;}&#8221;. Do not include any HTML tags like "<amp-analytics>" or "<script>".', 'amp' ); ?>"
								required
								><?php echo esc_textarea( $config ); ?></textarea>
						</label>
					</p>
					<input type="hidden" name="action" value="amp_analytics_options">
				</div>
				<p>
					<?php
					wp_nonce_field( 'analytics-options', 'analytics-options' );
					submit_button( esc_html__( 'Save', 'amp' ), 'primary', 'save', false );
					if ( $is_existing_entry ) {
						submit_button( esc_html__( 'Delete', 'amp' ), 'delete button-primary', esc_attr( $id_base . '[delete]' ), false );
					}
					?>
				</p>
			</form>
		</div><!-- #analytics-data-container -->
		<?php
	}

	/**
	 * Render title.
	 *
	 * @param bool $has_entries Whether there are entries.
	 */
	public function render_title( $has_entries = false ) {
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<?php settings_errors(); ?>

			<details <?php echo ! $has_entries ? 'open' : ''; ?>>
				<summary>
					<?php esc_html_e( 'Learn about analytics for AMP.', 'amp' ); ?>
				</summary>
				<p>
					<?php echo wp_kses_post( __( 'For Google Analytics, please see <a href="https://developers.google.com/analytics/devguides/collection/amp-analytics/" target="_blank">Adding Analytics to your AMP pages</a>; see also the <a href="https://github.com/ampproject/amp-wp/wiki/Analytics" target="_blank">Analytics wiki page</a> and the AMP project\'s <a href="https://www.ampproject.org/docs/reference/components/amp-analytics" target="_blank">amp-analytics documentation</a>. The analytics configuration supplied below must take the form of JSON objects, which begin with a &#8220;{&#8221; and end with a &#8220;}&#8221;. Do not include any HTML tags like &#8220;<code>&lt;amp-analytics&gt;</code>&#8221; or &#8220;<code>&lt;script&gt;</code>&#8221;. A common entry would have the type &#8220;<code>googleanalytics</code>&#8221; (see <a href="https://www.ampproject.org/docs/analytics/analytics-vendors" target="_blank">available vendors</a>) and a configuration that looks like the following (where <code>UA-XXXXX-Y</code> is replaced with your own site\'s account number):', 'amp' ) ); ?>

					<pre>{
	"vars": {
		"account": "UA-XXXXX-Y"
	},
	"triggers": {
		"trackPageview": {
			"on": "visible",
			"request": "pageview"
		}
	}
}</pre>
				</p>
			</details>
		</div><!-- .wrap -->
		<?php
	}

	/**
	 * Render styles.
	 */
	protected function render_styles() {
		?>
		<style>
			.amp-analytics-input {
				font-family: monospace;
			}
			.amp-analytics-input:invalid {
				border-color: red;
			}
		</style>
		<?php
	}

	/**
	 * Render scripts.
	 */
	protected function render_scripts() {
		?>
		<script>
			Array.prototype.forEach.call( document.querySelectorAll( '.amp-analytics-input' ), function( textarea ) {
				textarea.addEventListener( 'input', function() {
					if ( ! this.value ) {
						this.setCustomValidity( '' );
						return;
					}
					try {
						var value = JSON.parse( this.value );
						if ( null === value || typeof value !== 'object' || Array.isArray( value ) ) {
							this.setCustomValidity( <?php echo wp_json_encode( __( 'A JSON object is required, e.g. {...}', 'amp' ) ); ?> )
						} else {
							this.setCustomValidity( '' );
						}
					} catch ( e ) {
						this.setCustomValidity( e.message )
					}
				} );
			} );
		</script>
		<?php
	}

	/**
	 * Render.
	 */
	public function render() {
		$this->render_styles();

		$analytics_entries = AMP_Options_Manager::get_option( 'analytics', array() );

		$this->render_title( ! empty( $analytics_entries ) );

		// Render entries stored in the DB.
		foreach ( $analytics_entries as $entry_id => $entry ) {
			$this->render_entry( $entry_id, $entry['type'], $entry['config'] );
		}

		// Empty form for adding more entries.
		$this->render_entry();

		$this->render_scripts();
	}
}
