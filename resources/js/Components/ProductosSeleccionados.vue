<template>
  <div v-if="selectedProducts.length > 0" class="space-y-4">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-lg font-medium text-gray-900">Items Seleccionados</h3>
      <span class="text-sm text-gray-500">{{ selectedProducts.length }} item(s)</span>
    </div>

    <div class="space-y-3">
      <div v-for="(entry, index) in selectedProducts"
           :key="`${entry.tipo}-${entry.id}`"
           class="bg-gray-50 border border-gray-200 rounded-lg p-4 hover:shadow-sm transition-shadow duration-200">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-center">
          <!-- Información del producto -->
          <div class="lg:col-span-4">
            <div class="flex items-start space-x-3">
              <div class="flex-shrink-0">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                      :class="entry.tipo === 'producto' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800'">
                  {{ entry.tipo }}
                </span>
              </div>
              <div class="min-w-0 flex-1">
                <p class="font-medium text-gray-900 truncate">
                  {{ getProductById(entry)?.nombre || 'Item no encontrado' }}
                </p>
                <p v-if="getProductById(entry)?.codigo" class="text-sm text-gray-500">
                  Código: {{ getProductById(entry).codigo }}
                </p>
              </div>
            </div>
          </div>

          <!-- Cantidad -->
          <div class="lg:col-span-2">
            <label :for="`cantidad-${entry.tipo}-${entry.id}`" class="block text-sm font-medium text-gray-700 mb-1">
              Cantidad
            </label>
            <input
              :id="`cantidad-${entry.tipo}-${entry.id}`"
              v-model.number="quantities[`${entry.tipo}-${entry.id}`]"
              type="number"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-green-500 focus:border-green-500"
              min="1"
              @input="calcularTotal"
            />
          </div>

          <!-- Precio -->
          <div class="lg:col-span-2">
            <label :for="`precio-${entry.tipo}-${entry.id}`" class="block text-sm font-medium text-gray-700 mb-1">
              Precio
            </label>
            <input
              :id="`precio-${entry.tipo}-${entry.id}`"
              v-model.number="prices[`${entry.tipo}-${entry.id}`]"
              type="number"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-green-500 focus:border-green-500"
              min="0"
              step="0.01"
              @input="calcularTotal"
            />
          </div>

          <!-- Subtotal -->
          <div class="lg:col-span-3">
            <label :for="`subtotal-${entry.tipo}-${entry.id}`" class="block text-sm font-medium text-gray-700 mb-1">
              Subtotal
            </label>
            <div class="px-3 py-2 bg-white border border-gray-300 rounded-md text-gray-900 font-medium">
              ${{ ((quantities[`${entry.tipo}-${entry.id}`] || 0) * (prices[`${entry.tipo}-${entry.id}`] || 0)).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}
            </div>
          </div>

          <!-- Botón eliminar -->
          <div class="lg:col-span-1 flex justify-end">
            <button
              type="button"
              @click="eliminarProducto(entry)"
              class="p-2 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-full transition-all duration-200"
              title="Eliminar item"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
              </svg>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Mensaje cuando no hay productos -->
  <div v-else class="text-center py-8">
    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
    </svg>
    <h3 class="mt-2 text-sm font-medium text-gray-900">No hay productos seleccionados</h3>
    <p class="mt-1 text-sm text-gray-500">Busca y agrega productos o servicios para la cotización</p>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
  selectedProducts: Array,
  productos: Array,
  servicios: Array,
});

const emit = defineEmits(['eliminar-producto', 'calcular-total']);

const quantities = ref({});
const prices = ref({});

const getProductById = (entry) => {
  if (!entry || !entry.id || !entry.tipo) {
    console.error('Entrada inválida para getProductById:', entry);
    return null;
  }
  if (entry.tipo === 'producto') {
    const producto = props.productos.find((p) => p.id === entry.id);
    return producto || null;
  }
  if (entry.tipo === 'servicio') {
    const servicio = props.servicios.find((s) => s.id === entry.id);
    return servicio || null;
  }
  console.error(`No se encontró item con ID: ${entry.id} y tipo: ${entry.tipo}`);
  return null;
};

const calcularTotal = () => {
  emit('calcular-total', quantities.value, prices.value);
};

const eliminarProducto = (entry) => {
  emit('eliminar-producto', entry);
};

watch(quantities, calcularTotal, { deep: true });
watch(prices, calcularTotal, { deep: true });
</script>
