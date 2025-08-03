<template>
  <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
    <!-- Header Section -->
    <div class="bg-gradient-to-r from-green-500 to-green-600 px-6 py-4">
      <div class="flex justify-between items-center">
        <h2 class="text-lg font-semibold text-white flex items-center">
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"
            />
          </svg>
          Productos y Servicios
        </h2>
        <button
          type="button"
          @click="handleVerificarPrecios"
          :disabled="verificandoPrecios"
          class="flex items-center text-sm text-white hover:text-green-200 disabled:opacity-70 disabled:cursor-not-allowed transition-colors duration-200"
          :title="verificandoPrecios ? 'Verificando...' : 'Verificar precios actuales'"
        >
          <svg v-if="!verificandoPrecios" class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
            />
          </svg>
          <div v-else class="animate-spin w-4 h-4 mr-1 border-2 border-white border-t-transparent rounded-full"></div>
          {{ verificandoPrecios ? 'Verificando...' : 'Verificar Precios' }}
        </button>
      </div>
    </div>

    <!-- Content Section -->
    <div class="p-6">
      <!-- Búsqueda de producto -->
      <BuscarProducto
        ref="buscarProductoRef"
        :productos="productos"
        :servicios="servicios"
        :disabled="verificandoPrecios"
        @agregar-producto="handleAgregarProducto"
      />

      <!-- Lista de productos seleccionados -->
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
import { ref } from 'vue';

// Componentes
import BuscarProducto from '@/Components/BuscarProducto.vue';
import ProductosSeleccionados from '@/Components/ProductosSeleccionados.vue';

// Props
const props = defineProps({
  productos: {
    type: Array,
    required: true,
    default: () => [],
    validator: (value) => Array.isArray(value)
  },
  servicios: {
    type: Array,
    required: true,
    default: () => [],
    validator: (value) => Array.isArray(value)
  },
  selectedProducts: {
    type: Array,
    required: true,
    default: () => [],
    validator: (value) => Array.isArray(value)
  },
  quantities: {
    type: Object,
    required: true,
    default: () => ({}),
    validator: (value) => value !== null && typeof value === 'object'
  },
  prices: {
    type: Object,
    required: true,
    default: () => ({}),
    validator: (value) => value !== null && typeof value === 'object'
  },
  discounts: {
    type: Object,
    required: true,
    default: () => ({}),
    validator: (value) => value !== null && typeof value === 'object'
  },
  mostrarCalculadoraMargen: {
    type: Boolean,
    default: false
  }
});

// Emits
const emit = defineEmits([
  'agregar-producto',
  'eliminar-producto',
  'update-quantity',
  'update-discount',
  'calcular-total',
  'mostrar-margen',
  'verificar-precios'
]);

// Estado
const verificandoPrecios = ref(false);

// Manejadores de eventos
const handleAgregarProducto = (producto) => {
  emit('agregar-producto', producto);
};

const handleEliminarProducto = (producto) => {
  emit('eliminar-producto', producto);
};

const handleUpdateQuantity = (key, quantity) => {
  console.log(`ProductosServicios - handleUpdateQuantity:`, { key, quantity, quantityType: typeof quantity });

  // Validar parámetros
  if (!key) {
    console.error('ProductosServicios - Key no proporcionada');
    return;
  }

  if (quantity === undefined || quantity === null) {
    console.error('ProductosServicios - Quantity es undefined/null:', quantity);
    return;
  }

  console.log(`ProductosServicios - Emitiendo a Edit.vue:`, { key, quantity });

  // Emitir al componente padre (Edit.vue)
  emit('update-quantity', key, quantity);
};

const handleUpdateDiscount = (payload) => {
  emit('update-discount', payload);
};

const handleCalcularTotal = () => {
  emit('calcular-total');
};

const handleMostrarMargen = () => {
  emit('mostrar-margen');
};

const handleVerificarPrecios = async () => {
  verificandoPrecios.value = true;
  try {
    emit('verificar-precios');
    // Si el evento es asíncrono, puedes esperar una promesa
    // await nextTick();
  } catch (error) {
    console.error('Error al verificar precios:', error);
  } finally {
    verificandoPrecios.value = false;
  }
};

// Exponer referencias y métodos
defineExpose({
  focus: () => {
    if (buscarProductoRef.value?.focus) {
      buscarProductoRef.value.focus();
    }
  }
});

// Referencias
const buscarProductoRef = ref(null);
</script>
