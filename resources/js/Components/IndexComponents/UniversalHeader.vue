<!-- /resources/js/Components/IndexComponents/UniversalHeader.vue -->
<script setup>
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

// Props configurables para cualquier módulo
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

// Configuraciones por defecto según el módulo
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

  ordenescompra: {
    module: 'ordenescompra',
    title: 'Ordenes de Compra',
    createRoute: '/ordenescompra/create',
    createButtonText: 'Nueva Orden de Compra',
    searchPlaceholder: 'Buscar ordenes de compra...',
    estadisticas: {
      total: { label: 'Total', icon: 'document' },
      aprobadas: { label: 'Recibidas', icon: 'check-circle', color: 'green' },
      pendientes: { label: 'Pendientes', icon: 'clock', color: 'orange' }
    },
    estados: [
      { value: '', label: 'Todos los Estados' },
      { value: 'pendiente', label: 'Pendientes' },
      { value: 'ordenado', label: 'Ordenado' },
      { value: 'recibido', label: 'Recibido' },
      { value: 'cancelado', label: 'Cancelado' }
    ],
    sortOptions: [
      { value: 'fecha-desc', label: 'Fecha ↓' },
      { value: 'fecha-asc', label: 'Fecha ↑' },
      { value: 'total-desc', label: 'Total ↓' },
      { value: 'total-asc', label: 'Total ↑' },
      { value: 'proveedor-asc', label: 'Proveedor A-Z' },
      { value: 'proveedor-desc', label: 'Proveedor Z-A' }
    ]
  },

  compras: {
    module: 'compras',
    title: 'Compras',
    createRoute: '/compras/create',
    createButtonText: 'Nueva Compra',
    searchPlaceholder: 'Buscar compras...',
    estadisticas: {
      total: { label: 'Total', icon: 'document' },
      aprobadas: { label: 'Recibidas', icon: 'check-circle', color: 'green' },
      pendientes: { label: 'Pendientes', icon: 'clock', color: 'orange' }
    },
    estados: [
      { value: '', label: 'Todos los Estados' },
      { value: 'pendiente', label: 'Pendientes' },
      { value: 'ordenado', label: 'Ordenado' },
      { value: 'recibido', label: 'Recibido' },
      { value: 'cancelado', label: 'Cancelado' }
    ],
    sortOptions: [
      { value: 'fecha-desc', label: 'Fecha ↓' },
      { value: 'fecha-asc', label: 'Fecha ↑' },
      { value: 'total-desc', label: 'Total ↓' },
      { value: 'total-asc', label: 'Total ↑' },
      { value: 'proveedor-asc', label: 'Proveedor A-Z' },
      { value: 'proveedor-desc', label: 'Proveedor Z-A' }
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

// Configuración final combinando defaults con props
const finalConfig = computed(() => {
  const defaultConfig = defaultConfigs[props.config.module] || defaultConfigs.cotizaciones;
  return { ...defaultConfig, ...props.config };
});

// Emitimos eventos para v-model
const emit = defineEmits([
  'update:searchTerm',
  'update:sortBy',
  'update:filtroEstado',
  'limpiar-filtros'
]);

// Verifica si hay filtros activos
const hayFiltrosActivos = computed(() => {
  return !!props.searchTerm || !!props.filtroEstado;
});

// Limpia todos los filtros
const limpiarFiltros = () => {
  emit('update:searchTerm', '');
  emit('update:sortBy', 'fecha-desc');
  emit('update:filtroEstado', '');
  emit('limpiar-filtros');
};

// Iconos SVG optimizados
const icons = {
  document: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
  'check-circle': 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
  clock: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
  plus: 'M12 4v16m8-8H4',
  search: 'M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z',
  x: 'M6 18L18 6M6 6l12 12',
  'filter-x': 'M6 18L18 6M6 6l12 12',
  'chevron-down': 'M19 9l-7 7-7-7'
};

// Clases de color por estado con mejores tonos
const colorClasses = {
  green: 'text-emerald-600',
  blue: 'text-blue-600',
  yellow: 'text-amber-600',
  orange: 'text-orange-600',
  default: 'text-slate-500'
};

const getColorClass = (color) => colorClasses[color] || colorClasses.default;

// Formatear números con separadores de miles
const formatNumber = (num) => {
  return new Intl.NumberFormat('es-ES').format(num);
};
</script>

<template>
  <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-8 mb-6 transition-all duration-200 hover:shadow-md">
    <div class="flex flex-col lg:flex-row gap-10 items-start lg:items-center justify-between">

      <!-- Sección izquierda: Botón crear y estadísticas -->
      <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center w-full lg:w-auto">

        <!-- Botón crear mejorado -->
        <Link
          :href="finalConfig.createRoute"
          class="group inline-flex items-center gap-2.5 px-6 py-3 bg-gradient-to-r from-blue-600 via-blue-600 to-blue-700 text-white font-semibold rounded-xl hover:from-blue-700 hover:via-blue-700 hover:to-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-500/20 transform hover:scale-[1.02] active:scale-[0.98] transition-all duration-200 shadow-lg hover:shadow-xl flex-shrink-0"
        >
          <svg
            class="w-5 h-5 transition-transform duration-200 group-hover:rotate-90"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2.5"
          >
            <path :d="icons.plus" />
          </svg>
          <span class="tracking-wide">{{ finalConfig.createButtonText }}</span>
        </Link>

        <!-- Estadísticas mejoradas -->
        <div class="flex flex-wrap items-center gap-4 text-sm">
          <!-- Total -->
          <div class="flex items-center gap-2 px-3 py-2 bg-slate-50 rounded-lg border border-slate-200 flex-shrink-0">
            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="icons[finalConfig.estadisticas.total.icon]" />
            </svg>
            <span class="font-medium text-slate-700">{{ finalConfig.estadisticas.total.label }}:</span>
            <span class="font-bold text-slate-900">{{ formatNumber(total) }}</span>
          </div>

          <!-- Aprobadas/Completados -->
          <div class="flex items-center gap-2 px-3 py-2 bg-emerald-50 rounded-lg border border-emerald-200 flex-shrink-0">
            <svg
              class="w-4 h-4"
              :class="getColorClass(finalConfig.estadisticas.aprobadas.color)"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="icons[finalConfig.estadisticas.aprobadas.icon]" />
            </svg>
            <span class="font-medium text-slate-700">{{ finalConfig.estadisticas.aprobadas.label }}:</span>
            <span class="font-bold" :class="getColorClass(finalConfig.estadisticas.aprobadas.color)">{{ formatNumber(aprobadas) }}</span>
          </div>

          <!-- Pendientes -->
          <div class="flex items-center gap-2 px-3 py-2 rounded-lg border flex-shrink-0"
               :class="finalConfig.estadisticas.pendientes.color === 'yellow' ? 'bg-amber-50 border-amber-200' :
                       finalConfig.estadisticas.pendientes.color === 'blue' ? 'bg-blue-50 border-blue-200' :
                       'bg-orange-50 border-orange-200'"
          >
            <svg
              class="w-4 h-4"
              :class="getColorClass(finalConfig.estadisticas.pendientes.color)"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="icons[finalConfig.estadisticas.pendientes.icon]" />
            </svg>
            <span class="font-medium text-slate-700">{{ finalConfig.estadisticas.pendientes.label }}:</span>
            <span class="font-bold" :class="getColorClass(finalConfig.estadisticas.pendientes.color)">{{ formatNumber(pendientes) }}</span>
          </div>
        </div>
      </div>

      <!-- Sección derecha: Búsqueda y filtros -->
      <div class="flex flex-col sm:flex-row gap-3 w-full lg:w-auto lg:flex-shrink-0">

        <!-- Campo de búsqueda mejorado -->
        <div class="relative group">
          <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
            <svg class="w-4 h-4 text-slate-400 group-focus-within:text-blue-500 transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="icons.search" />
            </svg>
          </div>
          <input
            :value="searchTerm"
            @input="$emit('update:searchTerm', $event.target.value)"
            type="text"
            :placeholder="finalConfig.searchPlaceholder"
            class="w-full sm:w-64 lg:w-72 pl-11 pr-10 py-3 border border-slate-300 rounded-xl bg-white text-slate-900 placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all duration-200"
          />
          <Transition
            enter-active-class="transition-all duration-200"
            enter-from-class="opacity-0 scale-90"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition-all duration-150"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-90"
          >
            <button
              v-if="searchTerm"
              @click="$emit('update:searchTerm', '')"
              class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-slate-600 transition-colors duration-200"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="icons.x" />
              </svg>
            </button>
          </Transition>
        </div>

        <!-- Select de ordenamiento mejorado -->
        <div class="relative flex-shrink-0">
          <select
            :value="sortBy"
            @change="$emit('update:sortBy', $event.target.value)"
            class="appearance-none w-full sm:w-auto px-4 py-3 pr-10 border border-slate-300 rounded-xl bg-white text-slate-900 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all duration-200 cursor-pointer hover:bg-slate-50"
          >
            <option
              v-for="option in finalConfig.sortOptions"
              :key="option.value"
              :value="option.value"
            >
              {{ option.label }}
            </option>
          </select>
          <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="icons['chevron-down']" />
            </svg>
          </div>
        </div>

        <!-- Select de estado mejorado -->
        <div class="relative flex-shrink-0">
          <select
            :value="filtroEstado"
            @change="$emit('update:filtroEstado', $event.target.value)"
            class="appearance-none w-full sm:w-auto px-4 py-3 pr-10 border border-slate-300 rounded-xl bg-white text-slate-900 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all duration-200 cursor-pointer hover:bg-slate-50"
            :class="{ 'border-blue-500 bg-blue-50 text-blue-700': filtroEstado }"
          >
            <option
              v-for="estado in finalConfig.estados"
              :key="estado.value"
              :value="estado.value"
            >
              {{ estado.label }}
            </option>
          </select>
          <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="icons['chevron-down']" />
            </svg>
          </div>
        </div>

        <!-- Botón limpiar filtros mejorado -->
        <Transition
          enter-active-class="transition-all duration-300"
          enter-from-class="opacity-0 scale-90 translate-x-4"
          enter-to-class="opacity-100 scale-100 translate-x-0"
          leave-active-class="transition-all duration-200"
          leave-from-class="opacity-100 scale-100 translate-x-0"
          leave-to-class="opacity-0 scale-90 translate-x-4"
        >
          <button
            v-if="hayFiltrosActivos"
            @click="limpiarFiltros"
            class="group inline-flex items-center gap-2 px-4 py-3 bg-slate-100 text-slate-600 rounded-xl hover:bg-slate-200 hover:text-slate-700 focus:outline-none focus:ring-4 focus:ring-slate-500/10 transition-all duration-200 whitespace-nowrap border border-slate-200 flex-shrink-0"
          >
            <svg class="w-4 h-4 transition-transform duration-200 group-hover:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="icons['filter-x']" />
            </svg>
            <span class="font-medium">Limpiar</span>
          </button>
        </Transition>
      </div>
    </div>
  </div>
</template>
