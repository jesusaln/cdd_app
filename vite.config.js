import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import { resolve } from 'path';

// Configuración simplificada para desarrollo
export default defineConfig({
  plugins: [
    laravel({
      input: 'resources/js/app.js',
      refresh: true,
    }),
    vue({
      template: {
        transformAssetUrls: {
          base: null,
          includeAbsolute: false,
        },
      },
    }),
  ],
  resolve: {
    alias: {
      '@': resolve('resources/js'),
      'vue': 'vue/dist/vue.esm-bundler.js'
    },
  },
  server: {
    host: '0.0.0.0', // Escuchar en todas las interfaces (IPv4 e IPv6)
    port: 5173,
    cors: true,
    strictPort: true, // Forzar el uso del puerto exacto
    hmr: {
      host: 'localhost',
      port: 5173,
    },
  },
  base: process.env.NODE_ENV === 'production' ? '/build/' : '/',
  optimizeDeps: {
    include: ['vue', '@inertiajs/vue3', 'axios', 'ziggy-js']
  }
});
