<?php
namespace PixInLink;

class WooCommerce {

	public static function init(): void {
		add_filter( 'woocommerce_product_get_image', array( __CLASS__, 'filter_product_image' ), 10, 2 );
		add_action( 'add_meta_boxes', array( __CLASS__, 'add_product_meta_box' ) );
		add_action( 'wp_ajax_pixinlink_wc_generate', array( __CLASS__, 'ajax_generate_product_image' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_product_scripts' ) );
	}

	public static function filter_product_image( string $image, \WC_Product $product ): string {
		$override = get_post_meta( $product->get_id(), '_pixinlink_override_image', true );
		if ( ! $override ) {
			return $image;
		}

		$pixinlink_image_id = get_post_meta( $product->get_id(), '_pixinlink_product_image_id', true );
		if ( ! $pixinlink_image_id ) {
			return $image;
		}

		$product_image_id = $product->get_image_id();
		if ( $product_image_id && 0 === strpos( get_post_meta( $product_image_id, '_wp_attachment_metadata', true ) ? 'a' : '', '' ) ) {
		}

		$size = apply_filters( 'single_product_archive_thumbnail_size', 'woocommerce_thumbnail' );
		$html = wp_get_attachment_image( $pixinlink_image_id, $size, false, array(
			'class' => 'attachment-woocommerce_thumbnail size-woocommerce_thumbnail pixinlink-product-image',
		) );

		return $html ?: $image;
	}

	public static function add_product_meta_box(): void {
		add_meta_box(
			'pixinlink_product_image',
			__( 'PixInLink — Generate Image', 'pixinlink' ),
			array( __CLASS__, 'render_product_meta_box' ),
			'product',
			'side',
			'low'
		);
	}

	public static function render_product_meta_box( \WP_Post $post ): void {
		wp_nonce_field( 'pixinlink_product', 'pixinlink_product_nonce' );

		$override     = get_post_meta( $post->ID, '_pixinlink_override_image', true );
		$generated_id = get_post_meta( $post->ID, '_pixinlink_product_image_id', true );
		?>
		<div class="pixinlink-wc-meta">
			<p>
				<label>
					<input type="checkbox"
						name="pixinlink_override_image"
						value="1"
						<?php checked( $override, '1' ); ?>
					/>
					<?php esc_html_e( 'Override product image', 'pixinlink' ); ?>
				</label>
			</p>
			<p>
				<label for="pixinlink_wc_style"><?php esc_html_e( 'Style:', 'pixinlink' ); ?></label>
				<select id="pixinlink_wc_style" style="width:100%;">
					<?php foreach ( API::get_styles() as $value => $label ) : ?>
						<option value="<?php echo esc_attr( $value ); ?>" <?php selected( get_post_meta( $post->ID, '_pixinlink_wc_style', true ), $value ); ?>>
							<?php echo esc_html( $label ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</p>
			<p>
				<label for="pixinlink_wc_format"><?php esc_html_e( 'Format:', 'pixinlink' ); ?></label>
				<select id="pixinlink_wc_format" style="width:100%;">
					<?php foreach ( API::get_formats() as $value => $label ) : ?>
						<option value="<?php echo esc_attr( $value ); ?>" <?php selected( get_post_meta( $post->ID, '_pixinlink_wc_format', true ), 'webp' ); ?>>
							<?php echo esc_html( $label ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</p>
			<p>
				<button type="button" id="pixinlink-wc-generate" class="button button-primary" style="width:100%;">
					<?php esc_html_e( 'Generate Image', 'pixinlink' ); ?>
				</button>
				<span class="spinner" style="float:none;margin-top:8px;"></span>
			</p>
			<div id="pixinlink-wc-result"></div>
			<?php if ( $generated_id ) : ?>
				<div id="pixinlink-wc-preview" style="margin-top:8px;">
					<?php echo wp_get_attachment_image( $generated_id, 'thumbnail', false, array( 'style' => 'max-width:100%;' ) ); ?>
					<p>
						<small>
							<?php
							printf(
								/* translators: %d: attachment ID */
								esc_html__( 'Generated image ID: %d', 'pixinlink' ),
								$generated_id
							);
							?>
						</small>
					</p>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}

	public static function ajax_generate_product_image(): void {
		check_ajax_referer( 'pixinlink_product', 'nonce' );

		if ( ! current_user_can( 'edit_products' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'pixinlink' ) ) );
			return;
		}

		$product_id = absint( $_POST['product_id'] ?? 0 );
		if ( ! $product_id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid product ID.', 'pixinlink' ) ) );
			return;
		}

		$product = wc_get_product( $product_id );
		if ( ! $product ) {
			wp_send_json_error( array( 'message' => __( 'Product not found.', 'pixinlink' ) ) );
			return;
		}

		$style  = sanitize_text_field( $_POST['style'] ?? 'realistic' );
		$format = sanitize_text_field( $_POST['format'] ?? 'webp' );

		update_post_meta( $product_id, '_pixinlink_wc_style', $style );
		update_post_meta( $product_id, '_pixinlink_wc_format', $format );

		$prompt = $product->get_name();

		$args = array(
			'prompt' => $prompt,
			'width'  => 800,
			'height' => 800,
			'style'  => $style,
			'format' => $format,
		);

		$result = API::generate_image( $args );

		if ( ! $result['success'] ) {
			wp_send_json_error( array( 'message' => $result['message'] ) );
			return;
		}

		update_post_meta( $product_id, '_pixinlink_product_image_id', $result['attachment_id'] );
		update_post_meta( $product_id, '_pixinlink_override_image', '1' );

		wp_send_json_success( array(
			'url'           => $result['url'],
			'attachment_id' => $result['attachment_id'],
		) );
	}

	public static function enqueue_product_scripts( string $hook ): void {
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( ! $screen || 'product' !== $screen->post_type ) {
			return;
		}

		wp_add_inline_script(
			'jquery',
			"jQuery(function($){
				$('#pixinlink-wc-generate').on('click', function(e){
					e.preventDefault();
					var btn = $(this);
					var spinner = btn.siblings('.spinner');
					var result = $('#pixinlink-wc-result');
					btn.prop('disabled', true);
					spinner.addClass('is-active');
					result.html('');
					$.post(ajaxurl, {
						action: 'pixinlink_wc_generate',
						product_id: $('#post_ID').val(),
						style: $('#pixinlink_wc_style').val(),
						format: $('#pixinlink_wc_format').val(),
						nonce: '" . wp_create_nonce( 'pixinlink_product' ) . "'
					}, function(resp){
						if (resp.success) {
							result.html('<img src=\"' + resp.data.url + '\" style=\"max-width:100%;margin-top:8px;\" />');
							$('#pixinlink-wc-preview').remove();
						} else {
							result.html('<p style=\"color:red;\">' + resp.data.message + '</p>');
						}
					}).fail(function(){
						result.html('<p style=\"color:red;\">" . esc_js( __( 'Request failed.', 'pixinlink' ) ) . "</p>');
					}).always(function(){
						btn.prop('disabled', false);
						spinner.removeClass('is-active');
					});
				});
			});"
		);
	}
}
