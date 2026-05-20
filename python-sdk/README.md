# PixInLink Python SDK

`pip install pixinlink`

```python
from pixinlink import PixInLink

client = PixInLink(api_key="pk_your_key")

# Generate an image URL
url = client.generate(prompt="modern office workspace", width=1200, height=630)

# Get an HTML <img> tag
tag = client.img_tag(prompt="sunset over mountains", alt="Sunset")
```

## Features
- Zero dependencies (stdlib only)
- Cyrillic prompt support (auto-transliterated server-side)
- 6 image styles: realistic, illustration, anime, 3d, pixel-art, cyberpunk
- Multiple formats: WebP (default), AVIF, PNG
- Reproducible generation with `seed` parameter

## License
MIT
