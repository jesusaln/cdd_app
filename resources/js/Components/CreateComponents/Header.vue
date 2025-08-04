<template>
  <div class="header-section mb-8">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ title }}</h1>
        <p class="text-gray-600">{{ description }}</p>
      </div>
      <div class="flex gap-3">
        <!-- Botón Vista Previa -->
        <button
          @click="$emit('preview')"
          :disabled="!canPreview"
          class="inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-lg text-sm font-medium text-white hover:bg-purple-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 shadow-sm"
        >
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
          </svg>
          Vista Previa
        </button>



        <!-- Botón Volver -->
        <Link
          :href="backUrl"
          class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 hover:border-gray-400 transition-all duration-200 shadow-sm"
        >
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
          </svg>
          Volver
        </Link>
      </div>
    </div>


  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
  title: {
    type: String,
    default: 'Nueva'
  },
  description: {
    type: String,
    default: 'Crea un documento para tus clientes'
  },
  canPreview: {
    type: Boolean,
    default: true
  },
  backUrl: {
    type: String,
    required: true
  },
  showShortcuts: {
    type: Boolean,
    default: false
  }
});

defineEmits(['preview', 'templates', 'close-shortcuts']);
</script>
