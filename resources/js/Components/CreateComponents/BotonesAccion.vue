<template>
  <div class="flex flex-col sm:flex-row gap-4 justify-end">
    <!-- Botón Cancelar -->
    <Link
      :href="backUrl"
      class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200"
    >
      <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
      </svg>
      Cancelar
    </Link>

    <!-- Botón Guardar -->
    <button
      type="submit"
      :disabled="isProcessing || !canSubmit"
      class="inline-flex items-center justify-center px-6 py-3 border border-transparent rounded-lg text-sm font-medium text-white bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 shadow-lg hover:shadow-xl"
    >
      <!-- Spinner de carga -->
      <svg v-if="isProcessing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
      </svg>
      <span>{{ isProcessing ? 'Guardando...' : buttonText }}</span>
    </button>
  </div>
</template>

<script setup>
// ✅ Importa Link explícitamente
import { Link } from '@inertiajs/vue3';

defineProps({
  backUrl: {
    type: String,
    required: true
  },
  isProcessing: {
    type: Boolean,
    default: false
  },
  canSubmit: {
    type: Boolean,
    default: true
  },
  buttonText: {
    type: String,
    default: 'Crear'
  }
});
</script>
