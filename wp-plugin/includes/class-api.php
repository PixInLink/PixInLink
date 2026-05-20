<?php
namespace PixInLink;

class API {

	const BASE_URL = 'https://api.pixinlink.ru/api/v1';

	public static function get_api_key(): string {
		return get_option('pixinlink_api_key', '');
	}

	public static function generate_image( array $args ): array {
		$key = self::get_api_key();
		if ( ! $key ) {
			return array(
				'success' => false,
				'message' => __( 'API key not configured.', 'pixinlink' ),
			);
		}

		$width  = absint( $args['width'] ?? 800 );
		$height = absint( $args['height'] ?? 400 );
		$prompt = $args['prompt'] ?? '';
		$style  = sanitize_text_field( $args['style'] ?? 'realistic' );
		$format = sanitize_text_field( $args['format'] ?? 'webp' );
		$seed   = isset( $args['seed'] ) ? absint( $args['seed'] ) : '';

		if ( empty( $prompt ) ) {
			return array(
				'success' => false,
				'message' => __( 'Prompt is required.', 'pixinlink' ),
			);
		}

		$query_params = array(
			'prompt' => $prompt,
			'style'  => $style,
			'format' => $format,
		);

		if ( '' !== $seed ) {
			$query_params['seed'] = $seed;
		}

		$url = self::BASE_URL . '/' . $width . 'x' . $height . '/ffffff/000000';

		$response = wp_remote_get(
			add_query_arg( $query_params, $url ),
			array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $key,
				),
				'timeout' => 45,
			)
		);

		if ( is_wp_error( $response ) ) {
			return array(
				'success' => false,
				'message' => $response->get_error_message(),
			);
		}

		$code = wp_remote_retrieve_response_code( $response );

		if ( 200 === $code || 302 === $code ) {
			if ( 302 === $code ) {
				$headers  = wp_remote_retrieve_headers( $response );
				$image_url = $headers['location'] ?? '';
			} else {
				$body      = wp_remote_retrieve_body( $response );
				$image_url = trim( $body );
			}

			if ( empty( $image_url ) ) {
				return array(
					'success' => false,
					'message' => __( 'Empty image URL in response.', 'pixinlink' ),
				);
			}

			$attachment_id = self::download_image( $image_url, $prompt, $format );
			if ( ! $attachment_id ) {
				return array(
					'success' => false,
					'message' => __( 'Failed to download image.', 'pixinlink' ),
				);
			}

			return array(
				'success'       => true,
				'attachment_id' => $attachment_id,
				'url'           => wp_get_attachment_url( $attachment_id ),
			);
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		return array(
			'success' => false,
			'message' => $data['message'] ?? sprintf(
				/* translators: %d: HTTP status code */
				__( 'API error: HTTP %d', 'pixinlink' ),
				$code
			),
		);
	}

	public static function generate_image_url( array $args ): string {
		$width  = absint( $args['width'] ?? 800 );
		$height = absint( $args['height'] ?? 400 );
		$prompt = urlencode( $args['prompt'] ?? '' );
		$style  = sanitize_text_field( $args['style'] ?? 'realistic' );
		$format = sanitize_text_field( $args['format'] ?? 'webp' );

		return self::BASE_URL . '/' . $width . 'x' . $height . '/ffffff/000000?prompt=' . $prompt . '&style=' . $style . '&format=' . $format;
	}

	public static function validate_key( string $key ): bool {
		$url      = self::BASE_URL . '/1x1/ffffff/000000?prompt=test';
		$response = wp_remote_get(
			$url,
			array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $key,
				),
				'timeout' => 15,
			)
		);
		$code = wp_remote_retrieve_response_code( $response );
		return in_array( $code, array( 200, 302 ), true );
	}

	private static function download_image( string $image_url, string $prompt, string $format ): int {
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		$tmp = download_url( $image_url, 60 );
		if ( is_wp_error( $tmp ) ) {
			return 0;
		}

		$ext = 'webp';
		if ( 'avif' === $format ) {
			$ext = 'avif';
		} elseif ( 'png' === $format ) {
			$ext = 'png';
		}

		$file_array = array(
			'name'     => sanitize_title( $prompt ) . '-' . wp_generate_password( 6, false ) . '.' . $ext,
			'tmp_name' => $tmp,
		);

		$attachment_id = media_handle_sideload( $file_array, 0, $prompt );
		if ( is_wp_error( $attachment_id ) ) {
			@unlink( $tmp );
			return 0;
		}

		update_post_meta( $attachment_id, '_pixinlink_generated', 1 );
		update_post_meta( $attachment_id, '_pixinlink_prompt', $prompt );

		return $attachment_id;
	}

	public static function get_styles(): array {
		return array(
			'realistic'     => __( 'Realistic', 'pixinlink' ),
			'illustration'  => __( 'Illustration', 'pixinlink' ),
			'anime'         => __( 'Anime', 'pixinlink' ),
			'oil-painting'  => __( 'Oil Painting', 'pixinlink' ),
			'watercolor'    => __( 'Watercolor', 'pixinlink' ),
			'pixel-art'     => __( 'Pixel Art', 'pixinlink' ),
			'3d-render'     => __( '3D Render', 'pixinlink' ),
			'line-art'      => __( 'Line Art', 'pixinlink' ),
		);
	}

	public static function get_formats(): array {
		return array(
			'webp' => __( 'WebP', 'pixinlink' ),
			'avif' => __( 'AVIF', 'pixinlink' ),
			'png'  => __( 'PNG', 'pixinlink' ),
		);
	}
}
