"""PixInLink Python SDK — AI Image Generation from URL.

Usage:
    from pixinlink import PixInLink
    client = PixInLink(api_key="pk_your_key")
    url = client.generate(prompt="a beautiful sunset", width=1200, height=630)
    # Returns the CDN URL of the generated image
"""

import urllib.parse
from typing import Optional


class PixInLink:
    """Client for the PixInLink image generation API."""

    BASE_URL = "https://api.pixinlink.ru/api/v1"

    def __init__(self, api_key: Optional[str] = None):
        self.api_key = api_key

    def generate_url(
        self,
        prompt: str,
        width: int = 800,
        height: int = 400,
        bg_color: str = "ffffff",
        fg_color: str = "000000",
        style: str = "realistic",
        seed: Optional[int] = None,
        fmt: str = "webp",
    ) -> str:
        """Build a PixInLink generation URL.

        Returns the full URL that can be used in <img src> or fetched directly.
        No API call is made — just URL construction.

        Args:
            prompt: Image description (supports Cyrillic).
            width: Image width in pixels (1–4096).
            height: Image height in pixels (1–4096).
            bg_color: Background HEX color (without #).
            fg_color: Foreground HEX color (without #).
            style: One of realistic, illustration, anime, 3d, pixel-art, cyberpunk.
            seed: Optional integer seed for reproducibility.
            fmt: Output format — webp, avif, or png.

        Returns:
            Full URL string for PixInLink image generation.
        """
        encoded_prompt = urllib.parse.quote(prompt)
        params = {}
        if style != "realistic":
            params["style"] = style
        if seed is not None:
            params["seed"] = str(seed)
        if fmt != "webp":
            params["format"] = fmt

        url = f"{self.BASE_URL}/{width}x{height}/{bg_color}/{fg_color}?prompt={encoded_prompt}"
        if params:
            url += "&" + urllib.parse.urlencode(params)
        return url

    def generate(self, prompt: str, **kwargs) -> str:
        """Generate an image and return the CDN URL.

        Shortcut for generate_url() — returns a URL that will trigger
        image generation on first access and serve cached on subsequent.
        """
        return self.generate_url(prompt, **kwargs)

    def img_tag(self, prompt: str, alt: str = "", **kwargs) -> str:
        """Return a ready-to-use HTML <img> tag."""
        url = self.generate_url(prompt, **kwargs)
        return f'<img src="{url}" alt="{alt or prompt}" />'

    @staticmethod
    def version() -> str:
        return "1.0.0"
