<!-- /resources/js/Components/IndexComponents/UniversalHeader.vue -->
<script setup>
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

// ✅ Props configurables para cualquier módulo
const props = defineProps({
  // Datos estadísticos
  total: {
    type: Number,
    default: 0
  },
  aprobadas: {
    type: Number,
    default: 0
  },
  pendientes: {
    type: Number,
    default: 0
  },

  // Filtros actuales
  searchTerm: {
    type: String,
    default: ''
  },
  sortBy: {
    type: String,
    default: 'fecha-desc'
  },
  filtroEstado: {
    type: String,
    default: ''
  },

  // Configuración del módulo
  config: {
    type: Object,
    required: true,
    validator(value) {
      return value.module;
    }
  }
});

// ✅ Configuraciones por defecto según el módulo
const defaultConfigs = {
  cotizaciones: {
    module: 'cotizaciones',
    title: 'Cotizaciones',
    createRoute: '/cotizaciones/create',
    createButtonText: 'Nueva Cotización',
    searchPlaceholder: 'Buscar cotizaciones...',
    estadisticas: {
      total: { label: 'Total', icon: 'document' },
      aprobadas: { label: 'Aprobadas', icon: 'check-circle', color: 'green' },
      pendientes: { label: 'Pendientes', icon: 'clock', color: 'yellow' }
    },
    estados: [
      { value: '', label: 'Todos los Estados' },
      { value: 'pendiente', label: 'Pendientes' },
      { value: 'enviado_pedido', label: 'Enviado a Pedido' },
      { value: 'enviado_venta', label: 'Enviado a Venta' },
      { value: 'aprobado', label: 'Aprobadas' },
      { value: 'rechazado', label: 'Rechazadas' },
      { value: 'cancelado', label: 'Canceladas' }
    ],
    sortOptions: [
      { value: 'fecha-desc', label: 'Fecha ↓' },
      { value: 'fecha-asc', label: 'Fecha ↑' },
      { value: 'total-desc', label: 'Total ↓' },
      { value: 'total-asc', label: 'Total ↑' },
      { value: 'cliente-asc', label: 'Cliente A-Z' },
      { value: 'cliente-desc', label: 'Cliente Z-A' }
    ]
  },

  pedidos: {
    module: 'pedidos',
    title: 'Pedidos',
    createRoute: '/pedidos/create',
    createButtonText: 'Nuevo Pedido',
    searchPlaceholder: 'Buscar pedidos...',
    estadisticas: {
      total: { label: 'Total', icon: 'document' },
      aprobadas: { label: 'Completados', icon: 'check-circle', color: 'green' },
      pendientes: { label: 'En Proceso', icon: 'clock', color: 'blue' }
    },
    estados: [
      { value: '', label: 'Todos los Estados' },
      { value: 'pendiente', label: 'Pendientes' },
      { value: 'confirmado', label: 'Confirmados' },
      { value: 'preparando', label: 'En Preparación' },
      { value: 'enviado', label: 'Enviados' },
      { value: 'entregado', label: 'Entregados' },
      { value: 'cancelado', label: 'Cancelados' }
    ],
    sortOptions: [
      { value: 'fecha-desc', label: 'Fecha ↓' },
      { value: 'fecha-asc', label: 'Fecha ↑' },
      { value: 'total-desc', label: 'Total ↓' },
      { value: 'total-asc', label: 'Total ↑' },
      { value: 'cliente-asc', label: 'Cliente A-Z' },
      { value: 'cliente-desc', label: 'Cliente Z-A' }
    ]
  },

  ventas: {
    module: 'ventas',
    title: 'Ventas',
    createRoute: '/ventas/create',
    createButtonText: 'Nueva Venta',
    searchPlaceholder: 'Buscar ventas...',
    estadisticas: {
      total: { label: 'Total', icon: 'document' },
      aprobadas: { label: 'Facturadas', icon: 'check-circle', color: 'green' },
      pendientes: { label: 'Pendientes', icon: 'clock', color: 'orange' }
    },
    estados: [
      { value: '', label: 'Todos los Estados' },
      { value: 'borrador', label: 'Borrador' },
      { value: 'pendiente', label: 'Pendientes' },
      { value: 'facturado', label: 'Facturadas' },
      { value: 'pagado', label: 'Pagadas' },
      { value: 'cancelado', label: 'Canceladas' }
    ],
    sortOptions: [
      { value: 'fecha-desc', label: 'Fecha ↓' },
      { value: 'fecha-asc', label: 'Fecha ↑' },
      { value: 'total-desc', label: 'Total ↓' },
      { value: 'total-asc', label: 'Total ↑' },
      { value: 'cliente-asc', label: 'Cliente A-Z' },
      { value: 'cliente-desc', label: 'Cliente Z-A' }
    ]
  }
};

// ✅ Configuración final combinando defaults con props
const finalConfig = computed(() => {
  const defaultConfig = defaultConfigs[props.config.module] || defaultConfigs.cotizaciones;
  return { ...defaultConfig, ...props.config };
});

// ✅ Emitimos eventos para v-model
const emit = defineEmits([
  'update:searchTerm',
  'update:sortBy',
  'update:filtroEstado',
  'limpiar-filtros'
]);

// ✅ Verifica si hay filtros activos
const hayFiltrosActivos = computed(() => {
  return !!props.searchTerm || !!props.filtroEstado;
});

// ✅ Limpia todos los filtros
const limpiarFiltros = () => {
  emit('update:searchTerm', '');
  emit('update:sortBy', 'fecha-desc');
  emit('update:filtroEstado', '');
  emit('limpiar-filtros');
};

// ✅ Iconos SVG
const icons = {
  document: `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />`,
  'check-circle': `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />`,
  clock: `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />`,
  plus: `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />`,
  search: `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />`,
  x: `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />`
};

// ✅ Clases de color por estado
const getColorClass = (color) => {
  const colors = {
    green: 'text-green-500',
    blue: 'text-blue-500',
    yellow: 'text-yellow-500',
    orange: 'text-orange-500'
  };
  return colors[color] || 'text-gray-400';
};
</script>

<template>
  <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
    <div class="flex flex-col lg:flex-row gap-4 items-start lg:items-center justify-between">
      <!-- Botón crear y estadísticas -->
      <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center">
        <!-- Botón crear -->
       <Link
  :href="finalConfig.createRoute"
  class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-medium rounded-lg hover:from-blue-700 hover:to-blue-800 transform hover:scale-105 transition-all duration-200 shadow-lg"
>
  <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
  </svg>
  {{ finalConfig.createButtonText }}
</Link>

        <!-- Estadísticas -->
        <div class="flex items-center gap-4 text-sm text-gray-600">
          <span class="flex items-center">
            <svg class="w-4 h-4 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path v-html="icons[finalConfig.estadisticas.total.icon]"></path>
            </svg>
            {{ finalConfig.estadisticas.total.label }}: {{ total }}
          </span>
          <span class="flex items-center">
            <svg
              class="w-4 h-4 mr-1"
              :class="getColorClass(finalConfig.estadisticas.aprobadas.color)"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path v-html="icons[finalConfig.estadisticas.aprobadas.icon]"></path>
            </svg>
            {{ finalConfig.estadisticas.aprobadas.label }}: {{ aprobadas }}
          </span>
          <span class="flex items-center">
            <svg
              class="w-4 h-4 mr-1"
              :class="getColorClass(finalConfig.estadisticas.pendientes.color)"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path v-html="icons[finalConfig.estadisticas.pendientes.icon]"></path>
            </svg>
            {{ finalConfig.estadisticas.pendientes.label }}: {{ pendientes }}
          </span>
        </div>
      </div>

      <!-- Búsqueda y filtros -->
      <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
        <!-- Búsqueda -->
        <div class="relative flex-1">
          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path v-html="icons.search"></path>
            </svg>
          </div>
          <input
            :value="searchTerm"
            @input="$emit('update:searchTerm', $event.target.value)"
            type="text"
            :placeholder="finalConfig.searchPlaceholder"
            class="pl-10 pr-4 py-2.5 w-full border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
          <button
            v-if="searchTerm"
            @click="$emit('update:searchTerm', '')"
            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path v-html="icons.x"></path>
            </svg>
          </button>
        </div>

        <!-- Ordenamiento -->
        <select
          :value="sortBy"
          @change="$emit('update:sortBy', $event.target.value)"
          class="px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
          <option
            v-for="option in finalConfig.sortOptions"
            :key="option.value"
            :value="option.value"
          >
            {{ option.label }}
          </option>
        </select>

        <!-- Filtro por estado -->
        <select
          :value="filtroEstado"
          @change="$emit('update:filtroEstado', $event.target.value)"
          class="px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
          <option
            v-for="estado in finalConfig.estados"
            :key="estado.value"
            :value="estado.value"
          >
            {{ estado.label }}
          </option>
        </select>

        <!-- Botón limpiar filtros -->
        <button
          v-if="hayFiltrosActivos"
          @click="limpiarFiltros"
          class="px-3 py-2.5 bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 flex items-center whitespace-nowrap"
        >
          <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path v-html="icons.x"></path>
          </svg>
          Limpiar
        </button>
      </div>
    </div>
  </div>
</template>
