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
      vue: 'vue/dist/vue.esm-bundler.js',
    },
  },
  // Configuraciones adicionales para mejorar el rendimiento
  build: {
    // División de código personalizada para módulos pesados
    rollupOptions: {
      output: {
        manualChunks(id) {
          if (id.includes('resources/js/Pages/Reportes')) {
            return 'module-reportes';
          }
          if (id.includes('resources/js/Pages/Mantenimientos')) {
            return 'module-mantenimientos';
          }
          if (id.includes('node_modules/notyf')) {
            return 'vendor-notyf';
          }
          if (id.includes('node_modules/date-fns')) {
            return 'vendor-date-fns';
          }
          if (id.includes('node_modules/vue') || id.includes('node_modules/@inertiajs')) {
            return 'vendor';
          }
          if (id.includes('node_modules/axios') || id.includes('node_modules/lodash')) {
            return 'utils';
          }
        },
      },
    },
  },
  // Configuración del servidor de desarrollo
  server: {
    hmr: {
      host: 'localhost',
    },
    // Puerto por defecto, puedes cambiarlo si es necesario
    port: 5173,
    // Abre automáticamente el navegador
    open: false,
  },
  // Optimización de dependencias
  optimizeDeps: {
    include: ['vue', '@inertiajs/vue3', 'axios'],
  },
});
