=== PixInLink — AI Image Generator ===
Contributors: pixinlink
Donate link: https://pixinlink.ru
Tags: ai, image, generate, gutenberg, block, woocommerce, featured image, placeholder, media
Requires at least: 5.8
Tested up to: 6.5
Requires PHP: 7.4
Stable tag: 1.2.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Generate AI images on the fly and insert them into posts, pages, and WooCommerce products using the PixInLink API.

== Description ==

PixInLink — AI Image Generator lets you create unique AI-generated images directly from your WordPress admin. Use a Gutenberg block to insert images into posts and pages, embed with a shortcode, or automatically generate product images for your WooCommerce store.

= Features =

* **Gutenberg Block** — Add the "PixInLink Image" block, enter a prompt, choose style and dimensions, and generate.
* **Shortcodes** — Use `[pixinlink]` and `[pixinlink_url]` anywhere shortcodes are processed.
* **WooCommerce Integration** — Replace placeholder product images with AI-generated ones. One-click generation from the product edit screen.
* **8 Image Styles** — Realistic, Illustration, Anime, Oil Painting, Watercolor, Pixel Art, 3D Render, Line Art.
* **Multiple Formats** — WebP, AVIF, and PNG output.
* **Caching** — Generated images are stored in the WordPress Media Library with configurable cache duration.
* **Auto-Insert** — Automatically generate featured images for posts that don't have one.

= Usage =

== Gutenberg Block ==

1. Edit any post or page with the block editor.
2. Add the "PixInLink Image" block from the Media category.
3. In the inspector panel (right sidebar), enter a prompt and adjust settings.
4. Click "Generate Image".

== Shortcodes ==

`[pixinlink prompt="A serene mountain landscape" width=1024 height=576 style=watercolor]`

Available attributes: `prompt`, `width`, `height`, `style`, `format`, `seed`, `class`, `alt`.

`[pixinlink_url prompt="Sunset"]`

Outputs just the generated image URL.

== WooCommerce ==

1. Edit a product.
2. Find the "PixInLink — Generate Image" metabox in the sidebar.
3. Select style and format, then click "Generate Image".
4. Check "Override product image" to use the generated image on the frontend.

== Frequently Asked Questions ==

= Where do I get an API key? =

Sign up at [pixinlink.ru](https://pixinlink.ru) to obtain your API key. Enter it under Settings → PixInLink.

= What image sizes are supported? =

The API supports widths and heights from 1 to 4096 pixels.

= Are generated images stored in my Media Library? =

Yes. Each generated image is downloaded and stored as a standard WordPress attachment, so it persists even if the API is unavailable later.

= Does the plugin work without WooCommerce? =

Yes. The WooCommerce integration is optional and only activates when WooCommerce is installed.

== Changelog ==

= 1.2.0 =
* Initial release.
* Gutenberg block, shortcodes, WooCommerce integration.
* Admin settings page with API key and defaults.
* 8 image styles, 3 output formats.

== Upgrade Notice ==

= 1.2.0 =
Initial release.
