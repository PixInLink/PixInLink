<?php
namespace PixInLink;

class Shortcode {

	private static $cache = array();

	public static function init(): void {
		add_shortcode( 'pixinlink', array( __CLASS__, 'render_shortcode' ) );
		add_shortcode( 'pixinlink_url', array( __CLASS__, 'render_url_shortcode' ) );
	}

	public static function render_shortcode( array $atts, string $content = '' ): string {
		$defaults = array(
			'prompt' => '',
			'width'  => absint( get_option( 'pixinlink_default_width', 800 ) ),
			'height' => absint( get_option( 'pixinlink_default_height', 400 ) ),
			'style'  => get_option( 'pixinlink_default_style', 'realistic' ),
			'format' => get_option( 'pixinlink_default_format', 'webp' ),
			'seed'   => '',
			'class'  => '',
			'alt'    => '',
		);

		$atts = shortcode_atts( $defaults, $atts, 'pixinlink' );

		$atts = apply_filters( 'pixinlink_shortcode_atts', $atts );

		$prompt = sanitize_text_field( $atts['prompt'] );
		if ( empty( $prompt ) ) {
			return '';
		}

		$width  = absint( $atts['width'] );
		$height = absint( $atts['height'] );
		$style  = sanitize_text_field( $atts['style'] );
		$format = sanitize_text_field( $atts['format'] );
		$seed   = $atts['seed'] ? absint( $atts['seed'] ) : '';
		$class  = sanitize_html_class( $atts['class'] );
		$alt    = sanitize_text_field( $atts['alt'] ?: $prompt );

		$cache_key = md5( $prompt . $width . $height . $style . $format . $seed );

		if ( ! isset( self::$cache[ $cache_key ] ) ) {
			$args = array(
				'prompt' => $prompt,
				'width'  => $width,
				'height' => $height,
				'style'  => $style,
				'format' => $format,
				'seed'   => $seed,
			);

			$result = API::generate_image( $args );

			if ( ! $result['success'] ) {
				self::$cache[ $cache_key ] = '';
				return '';
			}

			self::$cache[ $cache_key ] = $result['attachment_id'];
		}

		$attachment_id = self::$cache[ $cache_key ];
		if ( ! $attachment_id ) {
			return '';
		}

		$image_class = 'pixinlink-image wp-block-pixinlink-image' . ( $class ? ' ' . $class : '' );

		$html = wp_get_attachment_image( $attachment_id, array( $width, $height ), false, array(
			'class' => $image_class,
			'alt'   => $alt,
		) );

		if ( ! $html ) {
			$url = wp_get_attachment_url( $attachment_id );
			if ( $url ) {
				$html = sprintf(
					'<img src="%s" alt="%s" width="%d" height="%d" class="%s" />',
					esc_url( $url ),
					esc_attr( $alt ),
					$width,
					$height,
					esc_attr( $image_class )
				);
			}
		}

		return $html ?: '';
	}

	public static function render_url_shortcode( array $atts, string $content = '' ): string {
		$defaults = array(
			'prompt' => '',
			'width'  => absint( get_option( 'pixinlink_default_width', 800 ) ),
			'height' => absint( get_option( 'pixinlink_default_height', 400 ) ),
			'style'  => get_option( 'pixinlink_default_style', 'realistic' ),
			'format' => get_option( 'pixinlink_default_format', 'webp' ),
			'seed'   => '',
		);

		$atts = shortcode_atts( $defaults, $atts, 'pixinlink_url' );
		$atts = apply_filters( 'pixinlink_shortcode_atts', $atts );

		$prompt = sanitize_text_field( $atts['prompt'] );
		if ( empty( $prompt ) ) {
			return '';
		}

		$args = array(
			'prompt' => $prompt,
			'width'  => absint( $atts['width'] ),
			'height' => absint( $atts['height'] ),
			'style'  => sanitize_text_field( $atts['style'] ),
			'format' => sanitize_text_field( $atts['format'] ),
			'seed'   => $atts['seed'] ? absint( $atts['seed'] ) : '',
		);

		return esc_url( API::generate_image_url( $args ) );
	}
}
