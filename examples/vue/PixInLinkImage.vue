<!-- Vue.js component example — PixInLink Image -->
<template>
  <img
    :src="imageUrl"
    :alt="alt || prompt"
    :class="className"
    loading="lazy"
    style="max-width: 100%; height: auto; border-radius: 8px"
  />
</template>

<script>
export default {
  name: 'PixInLinkImage',
  props: {
    prompt: { type: String, required: true },
    width: { type: Number, default: 800 },
    height: { type: Number, default: 400 },
    bg: { type: String, default: 'ffffff' },
    fg: { type: String, default: '000000' },
    style: { type: String, default: 'realistic' },
    seed: { type: Number, default: null },
    format: { type: String, default: 'webp' },
    alt: { type: String, default: '' },
    className: { type: String, default: '' },
  },
  computed: {
    imageUrl() {
      const params = new URLSearchParams({ prompt: this.prompt, style: this.style, format: this.format });
      if (this.seed) params.set('seed', this.seed.toString());
      return `https://pixinlink.ru/api/v1/${this.width}x${this.height}/${this.bg}/${this.fg}?${params}`;
    },
  },
};
</script>
