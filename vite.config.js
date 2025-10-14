import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import { resolve } from 'path';

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
    host: '192.168.191.226',
    hmr: {
      host: '192.168.191.226',
      port: 5173,
    },
    // Puerto por defecto, puedes cambiarlo si es necesario
    port: 5173,
    // Abre automáticamente el navegador
    open: false,
    // Forzar HTTPS en desarrollo si es necesario
    https: false,
    // Configuración adicional para desarrollo en red
    cors: true,
    origin: 'http://192.168.191.226:5173'
  },
  // Configuración para producción - corregir rutas de assets
  base: process.env.NODE_ENV === 'production' ? '/build/' : '/',
  // Optimización de dependencias
  optimizeDeps: {
    include: ['vue', '@inertiajs/vue3', 'axios', 'ziggy-js']
  }
});
