<template>
  <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
      <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex justify-between items-center">
        <h3 class="text-xl font-bold text-gray-900">Plantillas de Cotización</h3>
        <button @click="$emit('cerrar')" class="text-gray-400 hover:text-gray-600">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>
      <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div v-for="plantilla in plantillas" :key="plantilla.id"
               class="border border-gray-200 rounded-lg p-4 hover:shadow-lg transition-shadow duration-200 cursor-pointer"
               @click="$emit('aplicar-plantilla', plantilla)">
            <div class="flex justify-between items-start mb-3">
              <h4 class="font-semibold text-gray-900">{{ plantilla.nombre }}</h4>
              <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded">
                {{ plantilla.productos.length }} items
              </span>
            </div>
            <p class="text-sm text-gray-600 mb-3">{{ plantilla.descripcion }}</p>
            <div class="flex justify-between items-center text-sm">
              <span class="text-gray-500">Última modificación:</span>
              <span class="font-medium">${{ plantilla.total.toLocaleString('es-MX', { minimumFractionDigits: 2 }) }}</span>
            </div>
          </div>
        </div>
        <div v-if="selectedProducts.length > 0" class="mt-6 p-4 border-2 border-dashed border-gray-300 rounded-lg">
          <h4 class="font-semibold text-gray-900 mb-3">Guardar como Nueva Plantilla</h4>
          <div class="flex gap-3">
            <input
              v-model="nuevaPlantilla.nombre"
              type="text"
              placeholder="Nombre de la plantilla"
              class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
            <input
              v-model="nuevaPlantilla.descripcion"
              type="text"
              placeholder="Descripción (opcional)"
              class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
            <button
              @click="$emit('guardar-plantilla')"
              :disabled="!nuevaPlantilla.nombre"
              class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Guardar
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  plantillas: Array,
  selectedProducts: Array,
  nuevaPlantilla: Object
});

defineEmits(['cerrar', 'aplicar-plantilla', 'guardar-plantilla']);
</script>
