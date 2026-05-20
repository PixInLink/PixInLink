// Next.js example — PixInLink Image component with og:image support
// pages/index.js

import Head from 'next/head';

function PixInLinkImg({ prompt, width = 800, height = 400, bg = 'ffffff', fg = '000000', style = 'realistic', seed, format = 'webp', alt = '', className = '' }) {
  const params = new URLSearchParams({ prompt, style, format });
  if (seed) params.set('seed', String(seed));

  const src = `https://pixinlink.ru/api/v1/${width}x${height}/${bg}/${fg}?${params}`;

  return (
    <img src={src} alt={alt || prompt} className={className} loading="lazy"
      style={{ maxWidth: '100%', height: 'auto', borderRadius: 8 }}
    />
  );
}

export default function BlogPost({ title = "My Blog Post" }) {
  const ogImage = `https://pixinlink.ru/api/v1/1200x630/ffffff/333333?prompt=${encodeURIComponent(title)}&style=illustration&format=webp`;

  return (
    <>
      <Head>
        <title>{title}</title>
        <meta property="og:image" content={ogImage} />
        <meta property="og:title" content={title} />
      </Head>
      <article>
        <h1>{title}</h1>
        <PixInLinkImg prompt={title} width={1200} height={630} style="illustration" alt={title} />
        <p>Your blog content here...</p>
      </article>
    </>
  );
}
