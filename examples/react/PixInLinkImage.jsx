// React component example — PixInLink Image Generator
// Usage: <PixInLinkImage prompt="sunset" width={1200} height={630} alt="Sunset" />

import React from 'react';

export function PixInLinkImage({ prompt, width = 800, height = 400, bg = 'ffffff', fg = '000000', style = 'realistic', seed, format = 'webp', alt = '', className = '' }) {
  const params = new URLSearchParams({ prompt, style, format });
  if (seed) params.set('seed', seed.toString());

  const src = `https://pixinlink.ru/api/v1/${width}x${height}/${bg}/${fg}?${params.toString()}`;

  return (
    <img
      src={src}
      alt={alt || prompt}
      className={className}
      loading="lazy"
      style={{ maxWidth: '100%', height: 'auto', borderRadius: 8 }}
    />
  );
}

// Hook for generating og:image meta tag
export function useOpenGraphImage(prompt, width = 1200, height = 630) {
  return `https://pixinlink.ru/api/v1/${width}x${height}/ffffff/333333?prompt=${encodeURIComponent(prompt)}&style=illustration&format=webp`;
}
