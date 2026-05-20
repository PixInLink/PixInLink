<?php
namespace PixInLink;

class Settings {

	public static function init(): void {
		add_action( 'admin_menu', array( __CLASS__, 'add_settings_page' ) );
		add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_admin_scripts' ) );
		add_action( 'wp_ajax_pixinlink_test_connection', array( __CLASS__, 'ajax_test_connection' ) );
	}

	public static function add_settings_page(): void {
		add_options_page(
			__( 'PixInLink Settings', 'pixinlink' ),
			__( 'PixInLink', 'pixinlink' ),
			'manage_options',
			'pixinlink',
			array( __CLASS__, 'render_settings_page' )
		);
	}

	public static function register_settings(): void {
		register_setting( 'pixinlink_settings', 'pixinlink_api_key', array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'default'           => '',
		) );

		register_setting( 'pixinlink_settings', 'pixinlink_default_width', array(
			'type'              => 'integer',
			'sanitize_callback' => 'absint',
			'default'           => 800,
		) );

		register_setting( 'pixinlink_settings', 'pixinlink_default_height', array(
			'type'              => 'integer',
			'sanitize_callback' => 'absint',
			'default'           => 400,
		) );

		register_setting( 'pixinlink_settings', 'pixinlink_default_style', array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'default'           => 'realistic',
		) );

		register_setting( 'pixinlink_settings', 'pixinlink_default_format', array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'default'           => 'webp',
		) );

		register_setting( 'pixinlink_settings', 'pixinlink_auto_insert', array(
			'type'              => 'boolean',
			'sanitize_callback' => array( __CLASS__, 'sanitize_checkbox' ),
			'default'           => false,
		) );

		register_setting( 'pixinlink_settings', 'pixinlink_cache_days', array(
			'type'              => 'integer',
			'sanitize_callback' => 'absint',
			'default'           => 30,
		) );
	}

	public static function sanitize_checkbox( $value ): bool {
		return (bool) $value;
	}

	public static function enqueue_admin_scripts( string $hook ): void {
		if ( 'settings_page_pixinlink' !== $hook ) {
			return;
		}

		wp_add_inline_script(
			'jquery',
			"jQuery(function($){
				$('#pixinlink-test-connection').on('click', function(e){
					e.preventDefault();
					var btn = $(this);
					var result = $('#pixinlink-test-result');
					btn.prop('disabled', true).val('" . esc_js( __( 'Testing...', 'pixinlink' ) ) . "');
					result.html('');
					$.post(ajaxurl, {
						action: 'pixinlink_test_connection',
						api_key: $('#pixinlink_api_key').val(),
						_wpnonce: '" . wp_create_nonce( 'pixinlink_test_connection' ) . "'
					}, function(resp){
						if (resp.success) {
							result.html('<span style=\"color:green;font-weight:bold;\">" . esc_js( __( 'Connection successful!', 'pixinlink' ) ) . "</span>');
						} else {
							result.html('<span style=\"color:red;font-weight:bold;\">' + resp.data.message + '</span>');
						}
					}).fail(function(){
						result.html('<span style=\"color:red;font-weight:bold;\">" . esc_js( __( 'Request failed.', 'pixinlink' ) ) . "</span>');
					}).always(function(){
						btn.prop('disabled', false).val('" . esc_js( __( 'Test Connection', 'pixinlink' ) ) . "');
					});
				});
			});"
		);
	}

	public static function ajax_test_connection(): void {
		check_ajax_referer( 'pixinlink_test_connection' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'pixinlink' ) ) );
			return;
		}

		$key = sanitize_text_field( $_POST['api_key'] ?? '' );
		if ( empty( $key ) ) {
			wp_send_json_error( array( 'message' => __( 'API key is empty.', 'pixinlink' ) ) );
			return;
		}

		$valid = API::validate_key( $key );
		if ( $valid ) {
			wp_send_json_success();
		} else {
			wp_send_json_error( array( 'message' => __( 'Invalid API key or API unreachable.', 'pixinlink' ) ) );
		}
	}

	public static function render_settings_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php echo esc_html__( 'PixInLink Settings', 'pixinlink' ); ?></h1>
			<form method="post" action="options.php">
				<?php settings_fields( 'pixinlink_settings' ); ?>
				<?php do_settings_sections( 'pixinlink_settings' ); ?>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row">
							<label for="pixinlink_api_key"><?php esc_html_e( 'API Key', 'pixinlink' ); ?></label>
						</th>
						<td>
							<input type="password"
								id="pixinlink_api_key"
								name="pixinlink_api_key"
								value="<?php echo esc_attr( get_option( 'pixinlink_api_key', '' ) ); ?>"
								class="regular-text"
								autocomplete="off"
							/>
							<p class="description">
								<?php esc_html_e( 'Your PixInLink API key. Get it at', 'pixinlink' ); ?>
								<a href="https://pixinlink.ru" target="_blank" rel="noopener noreferrer">pixinlink.ru</a>
							</p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Test Connection', 'pixinlink' ); ?></th>
						<td>
							<input type="button"
								id="pixinlink-test-connection"
								class="button button-secondary"
								value="<?php esc_attr_e( 'Test Connection', 'pixinlink' ); ?>"
							/>
							<span id="pixinlink-test-result" style="margin-left:10px;"></span>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="pixinlink_default_width"><?php esc_html_e( 'Default Width', 'pixinlink' ); ?></label>
						</th>
						<td>
							<input type="number"
								id="pixinlink_default_width"
								name="pixinlink_default_width"
								value="<?php echo esc_attr( get_option( 'pixinlink_default_width', 800 ) ); ?>"
								min="1"
								max="4096"
								class="small-text"
							/>
							<span class="description">px</span>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="pixinlink_default_height"><?php esc_html_e( 'Default Height', 'pixinlink' ); ?></label>
						</th>
						<td>
							<input type="number"
								id="pixinlink_default_height"
								name="pixinlink_default_height"
								value="<?php echo esc_attr( get_option( 'pixinlink_default_height', 400 ) ); ?>"
								min="1"
								max="4096"
								class="small-text"
							/>
							<span class="description">px</span>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="pixinlink_default_style"><?php esc_html_e( 'Default Style', 'pixinlink' ); ?></label>
						</th>
						<td>
							<select id="pixinlink_default_style" name="pixinlink_default_style">
								<?php foreach ( API::get_styles() as $value => $label ) : ?>
									<option value="<?php echo esc_attr( $value ); ?>" <?php selected( get_option( 'pixinlink_default_style', 'realistic' ), $value ); ?>>
										<?php echo esc_html( $label ); ?>
									</option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="pixinlink_default_format"><?php esc_html_e( 'Default Format', 'pixinlink' ); ?></label>
						</th>
						<td>
							<select id="pixinlink_default_format" name="pixinlink_default_format">
								<?php foreach ( API::get_formats() as $value => $label ) : ?>
									<option value="<?php echo esc_attr( $value ); ?>" <?php selected( get_option( 'pixinlink_default_format', 'webp' ), $value ); ?>>
										<?php echo esc_html( $label ); ?>
									</option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="pixinlink_auto_insert"><?php esc_html_e( 'Auto-Insert', 'pixinlink' ); ?></label>
						</th>
						<td>
							<label for="pixinlink_auto_insert">
								<input type="checkbox"
									id="pixinlink_auto_insert"
									name="pixinlink_auto_insert"
									value="1"
									<?php checked( get_option( 'pixinlink_auto_insert', false ) ); ?>
								/>
								<?php esc_html_e( 'Auto-generate featured images for posts without one', 'pixinlink' ); ?>
							</label>
							<p class="description">
								<?php esc_html_e( 'When enabled, posts saved without a featured image will automatically receive a generated image based on the post title.', 'pixinlink' ); ?>
							</p>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="pixinlink_cache_days"><?php esc_html_e( 'Cache Duration', 'pixinlink' ); ?></label>
						</th>
						<td>
							<input type="number"
								id="pixinlink_cache_days"
								name="pixinlink_cache_days"
								value="<?php echo esc_attr( get_option( 'pixinlink_cache_days', 30 ) ); ?>"
								min="1"
								max="365"
								class="small-text"
							/>
							<span class="description"><?php esc_html_e( 'days', 'pixinlink' ); ?></span>
							<p class="description">
								<?php esc_html_e( 'Generated images older than this will be eligible for cleanup.', 'pixinlink' ); ?>
							</p>
						</td>
					</tr>
				</table>
				<?php submit_button(); ?>
			</form>
			<hr />
			<h2><?php esc_html_e( 'Shortcode Usage', 'pixinlink' ); ?></h2>
			<table class="widefat fixed striped" style="max-width:700px;">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Shortcode', 'pixinlink' ); ?></th>
						<th><?php esc_html_e( 'Description', 'pixinlink' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><code>[pixinlink prompt="Sunset over mountains" width=800 height=400 style=realistic]</code></td>
						<td><?php esc_html_e( 'Generate and insert an image', 'pixinlink' ); ?></td>
					</tr>
					<tr>
						<td><code>[pixinlink_url prompt="Sunset"]</code></td>
						<td><?php esc_html_e( 'Output just the image URL', 'pixinlink' ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<?php
	}
}
