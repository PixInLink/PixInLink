<?php
/**
 * Plugin Name: PixInLink — AI Image Generator
 * Plugin URI: https://pixinlink.ru
 * Description: Generate AI images from URL and insert them into posts, pages, and WooCommerce products.
 * Version: 1.2.0
 * Author: PixInLink
 * Author URI: https://pixinlink.ru
 * License: GPL-2.0+
 * Text Domain: pixinlink
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 *
 * @package PixInLink
 */

defined('ABSPATH') || exit;

define('PIXINLINK_VERSION', '1.2.0');
define('PIXINLINK_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('PIXINLINK_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once PIXINLINK_PLUGIN_DIR . 'includes/class-api.php';
require_once PIXINLINK_PLUGIN_DIR . 'includes/class-settings.php';
require_once PIXINLINK_PLUGIN_DIR . 'includes/class-block.php';
require_once PIXINLINK_PLUGIN_DIR . 'includes/class-shortcode.php';
require_once PIXINLINK_PLUGIN_DIR . 'includes/class-woocommerce.php';

add_action('init', function () {
	PixInLink\Settings::init();
	PixInLink\Block::init();
	PixInLink\Shortcode::init();
});

add_action('plugins_loaded', function () {
	if (class_exists('WooCommerce')) {
		PixInLink\WooCommerce::init();
	}
});

register_activation_hook(__FILE__, function () {
	add_option('pixinlink_flush_rewrite', true);
});

load_plugin_textdomain('pixinlink', false, dirname(plugin_basename(__FILE__)) . '/languages');
