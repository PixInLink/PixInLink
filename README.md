# 🖼️ PixInLink: URL-First AI Image Generator

PixInLink is a developer-friendly B2B SaaS that allows you to generate AI images directly through simple URL parameters. It features a unique **placeholder-first approach**: it instantly returns a lightweight SVG placeholder (under 50ms) while generating the final AI image (via Kandinsky or FLUX) in the background. Once ready, the image is cached and served via a fast CDN (Yandex Object Storage).

## ✨ Key Features
- **URL-First API**: Generate images simply by calling a URL (e.g., `https://pixinlink.com/800x600/bg/fg?prompt=...`).
- **Zero Layout Shift**: Instant placeholders prevent layout jumps while the image generates.
- **Fast CDN Delivery**: Global edge caching with 1-month TTL for optimal performance.
- **CMS Ready**: Seamless integration with WordPress, ModX, Ghost, and static sites.
- **Highly Customizable**: Control dimensions, colors, prompt, styles (realistic, artistic, cartoon), seed, and output format (WebP/PNG/JPEG).
