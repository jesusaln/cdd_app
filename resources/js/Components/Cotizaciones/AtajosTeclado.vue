<template>
  <div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
    <div class="flex justify-between items-start">
      <div>
        <h3 class="text-sm font-medium text-blue-900 mb-2">Atajos de Teclado</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-xs text-blue-700">
          <div><kbd class="px-2 py-1 bg-white rounded border">Ctrl+S</kbd> Guardar</div>
          <div><kbd class="px-2 py-1 bg-white rounded border">Ctrl+P</kbd> Vista Previa</div>
          <div><kbd class="px-2 py-1 bg-white rounded border">Ctrl+F</kbd> Buscar Cliente</div>
          <div><kbd class="px-2 py-1 bg-white rounded border">Ctrl+B</kbd> Buscar Producto</div>
        </div>
      </div>
      <button @click="$emit('cerrar')" class="text-blue-400 hover:text-blue-600">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
      </button>
    </div>
  </div>
</template>

<script setup>
</script>
