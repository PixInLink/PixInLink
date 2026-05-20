<?php
namespace PixInLink;

class Block {

	public static function init(): void {
		add_action( 'init', array( __CLASS__, 'register_block' ) );
		add_action( 'enqueue_block_editor_assets', array( __CLASS__, 'enqueue_block_assets' ) );
		add_action( 'wp_ajax_pixinlink_generate', array( __CLASS__, 'ajax_generate' ) );
		add_action( 'wp_ajax_pixinlink_preview', array( __CLASS__, 'ajax_preview' ) );
	}

	public static function register_block(): void {
		register_block_type( 'pixinlink/image', array(
			'editor_script'   => 'pixinlink-block',
			'editor_style'    => 'pixinlink-block-editor',
			'render_callback' => array( __CLASS__, 'render_block' ),
			'attributes'      => array(
				'prompt'    => array(
					'type'    => 'string',
					'default' => '',
				),
				'width'     => array(
					'type'    => 'integer',
					'default' => 800,
				),
				'height'    => array(
					'type'    => 'integer',
					'default' => 400,
				),
				'style'     => array(
					'type'    => 'string',
					'default' => 'realistic',
				),
				'format'    => array(
					'type'    => 'string',
					'default' => 'webp',
				),
				'seed'      => array(
					'type'    => 'string',
					'default' => '',
				),
				'imageUrl'  => array(
					'type'    => 'string',
					'default' => '',
				),
				'imageId'   => array(
					'type'    => 'integer',
					'default' => 0,
				),
				'altText'   => array(
					'type'    => 'string',
					'default' => '',
				),
			),
		) );
	}

	public static function enqueue_block_assets(): void {
		wp_enqueue_script(
			'pixinlink-block',
			PIXINLINK_PLUGIN_URL . 'assets/block.js',
			array( 'wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n', 'wp-server-side-render', 'wp-data' ),
			PIXINLINK_VERSION,
			true
		);

		wp_localize_script( 'pixinlink-block', 'pixinlinkData', array(
			'ajaxUrl'       => admin_url( 'admin-ajax.php' ),
			'nonce'         => wp_create_nonce( 'pixinlink_block' ),
			'defaultWidth'  => absint( get_option( 'pixinlink_default_width', 800 ) ),
			'defaultHeight' => absint( get_option( 'pixinlink_default_height', 400 ) ),
			'defaultStyle'  => sanitize_text_field( get_option( 'pixinlink_default_style', 'realistic' ) ),
			'defaultFormat' => sanitize_text_field( get_option( 'pixinlink_default_format', 'webp' ) ),
			'styles'        => API::get_styles(),
			'formats'       => API::get_formats(),
		) );

		wp_enqueue_style(
			'pixinlink-block-editor',
			PIXINLINK_PLUGIN_URL . 'assets/block.css',
			array(),
			PIXINLINK_VERSION
		);
	}

	public static function ajax_generate(): void {
		check_ajax_referer( 'pixinlink_block' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'pixinlink' ) ) );
			return;
		}

		$args = array(
			'prompt' => sanitize_text_field( $_POST['prompt'] ?? '' ),
			'width'  => absint( $_POST['width'] ?? 800 ),
			'height' => absint( $_POST['height'] ?? 400 ),
			'style'  => sanitize_text_field( $_POST['style'] ?? 'realistic' ),
			'format' => sanitize_text_field( $_POST['format'] ?? 'webp' ),
			'seed'   => absint( $_POST['seed'] ?? 0 ),
		);

		$result = API::generate_image( $args );

		if ( $result['success'] ) {
			wp_send_json_success( array(
				'url'         => $result['url'],
				'attachment_id' => $result['attachment_id'],
			) );
		} else {
			wp_send_json_error( array( 'message' => $result['message'] ) );
		}
	}

	public static function ajax_preview(): void {
		check_ajax_referer( 'pixinlink_block' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'pixinlink' ) ) );
			return;
		}

		$args = array(
			'prompt' => sanitize_text_field( $_POST['prompt'] ?? '' ),
			'width'  => absint( $_POST['width'] ?? 800 ),
			'height' => absint( $_POST['height'] ?? 400 ),
			'style'  => sanitize_text_field( $_POST['style'] ?? 'realistic' ),
			'format' => sanitize_text_field( $_POST['format'] ?? 'webp' ),
			'seed'   => absint( $_POST['seed'] ?? 0 ),
		);

		$url = API::generate_image_url( $args );
		wp_send_json_success( array( 'url' => $url ) );
	}

	public static function render_block( array $attributes, string $content, \WP_Block $block ): string {
		$image_url    = esc_url( $attributes['imageUrl'] ?? '' );
		$image_id     = absint( $attributes['imageId'] ?? 0 );
		$prompt       = esc_attr( $attributes['prompt'] ?? __( 'AI generated image', 'pixinlink' ) );
		$alt          = esc_attr( $attributes['altText'] ?: $prompt );
		$width        = absint( $attributes['width'] ?? 800 );
		$height       = absint( $attributes['height'] ?? 400 );
		$class        = 'wp-block-pixinlink-image';

		if ( $image_id ) {
			$size = array( $width, $height );
			$html = wp_get_attachment_image( $image_id, $size, false, array(
				'class' => $class,
				'alt'   => $alt,
			) );
			if ( $html ) {
				return $html;
			}
		}

		if ( $image_url ) {
			return sprintf(
				'<img src="%s" alt="%s" width="%d" height="%d" class="%s" />',
				$image_url,
				$alt,
				$width,
				$height,
				$class
			);
		}

		$placeholder = sprintf(
			'<div class="%s pixinlink-placeholder" style="width:%dpx;height:%dpx;background:#f0f0f0;border:1px dashed #ccc;display:flex;align-items:center;justify-content:center;color:#999;">%s</div>',
			esc_attr( $class ),
			$width,
			$height,
			esc_html( $prompt )
		);

		return $placeholder;
	}
}
