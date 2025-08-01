<template>
  <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
    <div class="bg-gradient-to-r from-yellow-500 to-yellow-600 px-6 py-4">
      <div class="flex justify-between items-center">
        <h2 class="text-lg font-semibold text-white flex items-center">
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
          </svg>
          Calculadora de Márgenes
        </h2>
        <button @click="$emit('cerrar')" class="text-white hover:text-yellow-200">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>
    </div>
    <div class="p-6">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-blue-50 p-4 rounded-lg">
          <div class="text-sm font-medium text-blue-700 mb-2">Costo Total</div>
          <div class="text-2xl font-bold text-blue-900">
            ${{ calculadoraMargen.costoTotal.toLocaleString('es-MX', { minimumFractionDigits: 2 }) }}
          </div>
        </div>
        <div class="bg-green-50 p-4 rounded-lg">
          <div class="text-sm font-medium text-green-700 mb-2">Precio de Venta</div>
          <div class="text-2xl font-bold text-green-900">
            ${{ calculadoraMargen.precioVenta.toLocaleString('es-MX', { minimumFractionDigits: 2 }) }}
          </div>
        </div>
        <div class="bg-purple-50 p-4 rounded-lg">
          <div class="text-sm font-medium text-purple-700 mb-2">Margen Bruto</div>
          <div class="text-2xl font-bold text-purple-900">
            {{ calculadoraMargen.margenPorcentaje.toFixed(1) }}%
          </div>
        </div>
        <div class="bg-yellow-50 p-4 rounded-lg">
          <div class="text-sm font-medium text-yellow-700 mb-2">Ganancia</div>
          <div class="text-2xl font-bold text-yellow-900">
            ${{ calculadoraMargen.ganancia.toLocaleString('es-MX', { minimumFractionDigits: 2 }) }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  calculadoraMargen: Object
});

defineEmits(['cerrar']);
</script>
