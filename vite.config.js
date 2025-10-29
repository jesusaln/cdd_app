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
    host: 'cdd.local', // Usar el mismo dominio que Laravel
    port: 5173,
    cors: true,
    strictPort: true, // Forzar el uso del puerto exacto
    hmr: false, // Deshabilitar HMR para evitar errores de canal de mensajes
    https: true, // Habilitar HTTPS para desarrollo local para coincidir con Laravel
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
