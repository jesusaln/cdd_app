import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import { resolve } from 'path';

// Leer dinámicamente la URL del servidor de desarrollo
const viteUrl = process.env.VITE_DEV_SERVER_URL || 'http://localhost:5173';

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
      // Esta línea soluciona el error de compilación de templates de Vue
      'vue': 'vue/dist/vue.esm-bundler.js'
    },
  },
  // Configuraciones adicionales para mejorar el rendimiento
  build: {
    // Habilita la división de código para mejor caching
    rollupOptions: {
      output: {
        manualChunks: {
          vendor: ['vue', '@inertiajs/vue3'],
          utils: ['axios', 'lodash']
        }
      }
    }
  },
  // Configuración del servidor de desarrollo
  server: {
    host: true, // Escuchar en todas las interfaces disponibles
    port: 5173,
    cors: true,
    strictPort: false,
    https: false,
    hmr: {
      host: viteUrl.replace('http://', '').replace(':5173', '').replace('0.0.0.0', 'localhost'), // Usar localhost para HMR estable
      port: 5173,
      clientPort: 5173,
      protocol: 'ws'
    },
    watch: {
      usePolling: false,
    },
  },
  // Configuración para producción - corregir rutas de assets
  base: process.env.NODE_ENV === 'production' ? '/build/' : '/',
  // Optimización de dependencias
  optimizeDeps: {
    include: ['vue', '@inertiajs/vue3', 'axios', 'ziggy-js']
  }
});
