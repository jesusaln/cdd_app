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
    host: '0.0.0.0', // Usar 0.0.0.0 para escuchar en todas las interfaces
    port: 5173,
    cors: true,
    strictPort: true, // Forzar el uso del puerto exacto
    hmr: {
      host: 'localhost',
    }, // Configurar HMR para usar localhost explícitamente
    https: false, // Deshabilitar HTTPS para desarrollo local para coincidir con Laravel
  },
  base: process.env.NODE_ENV === 'production' ? '/build/' : '/',
  build: {
    chunkSizeWarningLimit: 1000,
    rollupOptions: {
      output: {
        manualChunks: (id) => {
          if (id.includes('node_modules')) {
            if (id.includes('vue') || id.includes('@inertiajs')) {
              return 'vendor';
            }
            if (id.includes('ziggy-js')) {
              return 'ziggy';
            }
            if (id.includes('@fortawesome') || id.includes('fontawesome')) {
              return 'ui';
            }
            if (id.includes('jspdf') || id.includes('html2canvas')) {
              return 'pdf';
            }
            if (id.includes('axios') || id.includes('notyf')) {
              return 'utils';
            }
          }
        }
      }
    }
  },
  optimizeDeps: {
    include: ['vue', '@inertiajs/vue3', 'axios', 'ziggy-js']
  }
});
