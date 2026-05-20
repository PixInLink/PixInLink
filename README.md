<p align="center">
  <img src="https://pixinlink.ru/favicons/favicon-96x96.png" width="72" alt="PixInLink" />
</p>

<h1 align="center">PixInLink</h1>
<p align="center"><strong>AI Image Generation from URL</strong><br>One line of HTML — unique image. No designer, no stock photos.</p>

<p align="center">
  <a href="https://pixinlink.ru"><img src="https://img.shields.io/badge/Website-pixinlink.ru-blue" alt="Website" /></a>
  <a href="https://pixinlink.com"><img src="https://img.shields.io/badge/International-pixinlink.com-green" alt="International" /></a>
  <img src="https://img.shields.io/badge/Status-Production-brightgreen" alt="Status" />
  <img src="https://img.shields.io/badge/License-MIT-yellow" alt="License" />
  <img src="https://img.shields.io/badge/Python-3.12-blue" alt="Python" />
  <img src="https://img.shields.io/badge/WordPress-5.8+-0073aa" alt="WordPress" />
</p>

---

## What is PixInLink?

PixInLink generates AI images directly from a URL. Paste the link into `<img src>` — the image appears. No separate tab, no Photoshop, no stock photo subscriptions.

```html
<img src="https://pixinlink.ru/api/v1/800x400/hello+world+from+pixinlink" alt="Hello World" />
```

**How it works:**
1. You write a URL with dimensions and a prompt
2. PixInLink generates the image via AI (GigaChat / Flux)
3. Returns WebP-optimized image via CDN
4. Caches for future requests (sub-millisecond responses)

---

## Open Source Components

This repository contains the open-source parts of PixInLink:

| Component | Description |
|---|---|
| [`wp-plugin/`](wp-plugin/) | WordPress plugin — Gutenberg block + shortcode + WooCommerce |
| [`python-sdk/`](python-sdk/) | Python SDK — `pip install pixinlink` |
| [`examples/`](examples/) | HTML, React, Next.js, Vue examples |

The core API backend is proprietary and hosted at [pixinlink.ru](https://pixinlink.ru).

---

## Quick Start

### WordPress

1. Install plugin → `wp-plugin/`
2. Settings → PixInLink → enter API key from [pixinlink.ru/account/api-keys](https://pixinlink.ru/account/api-keys)
3. Use Gutenberg block "PixInLink Image" or shortcode `[pixinlink prompt="sunset" style=anime]`

### Python

```bash
pip install pixinlink
```

```python
from pixinlink import PixInLink
client = PixInLink()
print(client.generate(prompt="modern office", width=1200, height=630))
```

### Plain HTML

```html
<img src="https://pixinlink.ru/api/v1/800x400/ffffff/000000?prompt=modern+office&style=illustration" />
```

---

## Features

- 🎨 **6 AI styles**: Realistic, Illustration, Anime, 3D Render, Pixel Art, Cyberpunk
- 📐 **Any dimensions**: 64×64 to 4096×4096 px
- 🖼️ **Multiple formats**: WebP (default), AVIF, PNG
- 🌐 **CDN delivery**: Yandex Cloud CDN, 1-year cache
- 🔄 **Reproducible**: `seed` parameter for identical results
- 🇷🇺 **Cyrillic support**: Russian prompts auto-translated server-side
- 🏷️ **Watermark-free** on paid plans

---

## Links

| Resource | URL |
|---|---|
| Russian version | [pixinlink.ru](https://pixinlink.ru) |
| International version | [pixinlink.com](https://pixinlink.com) |
| API Documentation | [pixinlink.ru/docs](https://pixinlink.ru/docs) |
| Pricing | [pixinlink.ru/pricing](https://pixinlink.ru/pricing) |
| Blog | [pixinlink.ru/blog](https://pixinlink.ru/blog) |

---

<p align="center">
  <sub>Built with ❤️ by the PixInLink team. Serving images since 2026.</sub>
</p>
