<template>
  <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
      <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.5a.5.5 0 11-1 0 .5.5 0 011 0zm5 5a.5.5 0 11-1 0 .5.5 0 011 0z"/>
      </svg>
      Descuento General
    </h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <!-- Descuento Input -->
      <div class="space-y-2">
        <label for="descuento-general" class="block text-sm font-medium text-gray-700">
          Descuento General (%)
        </label>
        <div class="relative">
          <input
            id="descuento-general"
            type="number"
            :value="descuentoGeneral"
            @input="actualizarDescuento"
            min="0"
            max="100"
            step="0.01"
            class="block w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
            placeholder="0.00"
          />
          <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
            <span class="text-gray-500 text-sm">%</span>
          </div>
        </div>
        <p class="text-xs text-gray-500">
          Aplicado después de descuentos individuales
        </p>
      </div>
      <!-- Preview del descuento -->
      <div class="space-y-3">
        <h4 class="text-sm font-medium text-gray-700">Vista previa del descuento</h4>
        <div class="bg-gray-50 rounded-lg p-4 space-y-2">
          <div class="flex justify-between text-sm">
            <span class="text-gray-600">Subtotal con desc. individuales:</span>
            <span class="font-medium">${{ subtotalConDescuentoItems.toLocaleString() }}</span>
          </div>
          <div class="flex justify-between text-sm">
            <span class="text-gray-600">Descuento general ({{ descuentoGeneral }}%):</span>
            <span class="font-medium text-red-600">
              -${{ montoDescuentoGeneral.toLocaleString() }}
            </span>
          </div>
          <div class="border-t border-gray-200 pt-2">
            <div class="flex justify-between text-sm font-semibold">
              <span class="text-gray-900">Subtotal final:</span>
              <span class="text-green-600">${{ subtotalFinal.toLocaleString() }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- Atajos de descuento rápido -->
    <div class="mt-4 pt-4 border-t border-gray-200">
      <p class="text-xs text-gray-500 mb-2">Descuentos rápidos:</p>
      <div class="flex flex-wrap gap-2">
        <button
        type="button"
          v-for="descuento in descuentosRapidos"
          :key="descuento"
          @click="aplicarDescuentoRapido(descuento)"
          :class="[
            'px-3 py-1 text-xs rounded-md border transition-colors duration-200',
            descuentoGeneral === descuento
              ? 'bg-blue-100 border-blue-300 text-blue-700'
              : 'bg-gray-50 border-gray-200 text-gray-600 hover:bg-gray-100'
          ]"
        >
          {{ descuento }}%
        </button>
        <button
        type="button"
          @click="limpiarDescuento"
          class="px-3 py-1 text-xs rounded-md border bg-red-50 border-red-200 text-red-600 hover:bg-red-100 transition-colors duration-200"
        >
          Limpiar
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

// Props
const props = defineProps({
  descuentoGeneral: {
    type: Number,
    default: 0
  },
  totales: {
    type: Object,
    required: true,
    default: () => ({
      subtotal: 0,
      descuentoItems: 0,
      subtotalConDescuentos: 0,
    })
  }
});

// Emits
const emit = defineEmits(['update:descuentoGeneral', 'calcular-total']);

// Descuentos rápidos predefinidos
const descuentosRapidos = [5, 10, 15, 20, 25, 30];

// Computed properties
const subtotalConDescuentoItems = computed(() => {
  return props.totales.subtotal - props.totales.descuentoItems;
});

const montoDescuentoGeneral = computed(() => {
  return subtotalConDescuentoItems.value * (props.descuentoGeneral / 100);
});

const subtotalFinal = computed(() => {
  return subtotalConDescuentoItems.value - montoDescuentoGeneral.value;
});

// Methods
const actualizarDescuento = (event) => {
  let valor = parseFloat(event.target.value) || 0;
  // Validar rango
  if (valor < 0) valor = 0;
  if (valor > 100) valor = 100;
  emit('update:descuentoGeneral', valor);
  emit('calcular-total');
};

const aplicarDescuentoRapido = (descuento) => {
  emit('update:descuentoGeneral', descuento);
  emit('calcular-total');
};

const limpiarDescuento = () => {
  emit('update:descuentoGeneral', 0);
  emit('calcular-total');
};
</script>

<style scoped>
/* Estilos adicionales si son necesarios */
input[type="number"]::-webkit-outer-spin-button,
input[type="number"]::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

input[type="number"] {
  -moz-appearance: textfield;
}
</style>
