<template>
  <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
    <!-- Header Section -->
    <div class="bg-gradient-to-r from-green-500 to-green-600 px-6 py-4">
      <div class="flex justify-between items-center">
        <h2 class="text-lg font-semibold text-white flex items-center">
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
          </svg>
          Productos y Servicios
        </h2>
        <button
          @click="handleVerificarPrecios"
          type="button"
          class="text-white hover:text-green-200 transition-colors duration-200 flex items-center text-sm"
          title="Verificar precios actuales"
        >
          <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          Verificar Precios
        </button>
      </div>
    </div>

    <!-- Content Section -->
    <div class="p-6">
      <BuscarProducto
        ref="buscarProductoRef"
        :productos="productos"
        :servicios="servicios"
        @agregar-producto="handleAgregarProducto"
      />
      <ProductosSeleccionados
        :selectedProducts="selectedProducts"
        :productos="productos"
        :servicios="servicios"
        :quantities="quantities"
        :prices="prices"
        :discounts="discounts"
        :mostrarCalculadoraMargen="mostrarCalculadoraMargen"
        @eliminar-producto="handleEliminarProducto"
        @update-quantity="handleUpdateQuantity"
        @update-discount="handleUpdateDiscount"
        @calcular-total="handleCalcularTotal"
        @mostrar-margen="handleMostrarMargen"
      />
    </div>
  </div>
</template>

<script setup>
// Importación de componentes
import BuscarProducto from '@/Components/BuscarProducto.vue';
import ProductosSeleccionados from '@/Components/ProductosSeleccionados.vue';

// Definición de props
const props = defineProps({
  productos: {
    type: Array,
    required: true,
    default: () => [],
  },
  servicios: {
    type: Array,
    required: true,
    default: () => [],
  },
  selectedProducts: {
    type: Array,
    required: true,
    default: () => [],
  },
  quantities: {
    type: Object,
    required: true,
    default: () => ({}),
  },
  prices: {
    type: Object,
    required: true,
    default: () => ({}),
  },
  discounts: {
    type: Object,
    required: true,
    default: () => ({}),
  },
  mostrarCalculadoraMargen: {
    type: Boolean,
    required: true,
    default: false,
  },
});

// Definición de eventos emitidos
const emit = defineEmits([
  'agregar-producto',
  'eliminar-producto',
  'update-quantity',
  'update-discount',
  'calcular-total',
  'mostrar-margen',
  'verificar-precios'
]);

// Manejadores de eventos
const handleAgregarProducto = (producto) => {
  emit('agregar-producto', producto);
};

const handleEliminarProducto = (producto) => {
  emit('eliminar-producto', producto);
};

const handleUpdateQuantity = ({ key, quantity }) => {
  emit('update-quantity', { key, quantity });
};

const handleUpdateDiscount = ({ key, discount }) => {
  emit('update-discount', { key, discount });
};

const handleCalcularTotal = () => {
  emit('calcular-total');
};

const handleMostrarMargen = () => {
  emit('mostrar-margen');
};

const handleVerificarPrecios = () => {
  emit('verificar-precios');
};
</script>
