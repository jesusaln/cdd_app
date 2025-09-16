<script setup>
import { Link } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import ProductosSeleccionados from '../CreateComponents/ProductosSeleccionados.vue';

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
  },

  // Nuevas props para mayor flexibilidad
  loading: {
    type: Boolean,
    default: false
  },
  disabled: {
    type: Boolean,
    default: false
  },
  showExport: {
    type: Boolean,
    default: false
  },
  showImport: {
    type: Boolean,
    default: false
  }
});

// Estados reactivos
const searchInput = ref(null);
const isSearchFocused = ref(false);

// Configuraciones por defecto según el módulo - Mejoradas y más completas
const defaultConfigs = {
  cotizaciones: {
    module: 'cotizaciones',
    title: 'Cotizaciones',
    createRoute: '/cotizaciones/create',
    createButtonText: 'Nueva Cotización',
    searchPlaceholder: 'Buscar por cliente, número, descripción...',
    searchFields: ['numero', 'cliente', 'descripcion'],
    estadisticas: {
      total: { label: 'Total', icon: 'document', description: 'Total de cotizaciones' },
      aprobadas: { label: 'Aprobadas', icon: 'check-circle', color: 'green', description: 'Cotizaciones aprobadas' },
      pendientes: { label: 'Pendientes', icon: 'clock', color: 'yellow', description: 'Cotizaciones pendientes' }
    },
    estados: [
      { value: '', label: 'Todos los Estados', color: 'slate' },
      { value: 'pendiente', label: 'Pendientes', color: 'yellow' },
      { value: 'enviado_pedido', label: 'Enviado a Pedido', color: 'blue' },
      { value: 'enviado_venta', label: 'Enviado a Venta', color: 'indigo' },
      { value: 'aprobado', label: 'Aprobadas', color: 'green' },
      { value: 'rechazado', label: 'Rechazadas', color: 'red' },
      { value: 'cancelado', label: 'Canceladas', color: 'gray' }
    ],
    sortOptions: [
      { value: 'fecha-desc', label: 'Más Recientes', icon: 'arrow-down' },
      { value: 'fecha-asc', label: 'Más Antiguos', icon: 'arrow-up' },
      { value: 'total-desc', label: 'Mayor Monto', icon: 'currency-dollar' },
      { value: 'total-asc', label: 'Menor Monto', icon: 'currency-dollar' },
      { value: 'cliente-asc', label: 'Cliente A-Z', icon: 'sort-ascending' },
      { value: 'cliente-desc', label: 'Cliente Z-A', icon: 'sort-descending' }
    ]
  },

  pedidos: {
    module: 'pedidos',
    title: 'Pedidos',
    createRoute: '/pedidos/create',
    createButtonText: 'Nuevo Pedido',
    searchPlaceholder: 'Buscar por cliente, número, producto...',
    searchFields: ['numero', 'cliente', 'productos'],
    estadisticas: {
      total: { label: 'Total', icon: 'document', description: 'Total de pedidos' },
      aprobadas: { label: 'Completados', icon: 'check-circle', color: 'green', description: 'Pedidos completados' },
      pendientes: { label: 'En Proceso', icon: 'clock', color: 'blue', description: 'Pedidos en proceso' }
    },
    estados: [
      { value: '', label: 'Todos los Estados', color: 'slate' },
      { value: 'pendiente', label: 'Pendientes', color: 'yellow' },
      { value: 'confirmado', label: 'Confirmados', color: 'blue' },
      { value: 'preparando', label: 'En Preparación', color: 'orange' },
      { value: 'enviado', label: 'Enviados', color: 'indigo' },
      { value: 'entregado', label: 'Entregados', color: 'green' },
      { value: 'cancelado', label: 'Cancelados', color: 'red' }
    ],
    sortOptions: [
      { value: 'fecha-desc', label: 'Más Recientes', icon: 'arrow-down' },
      { value: 'fecha-asc', label: 'Más Antiguos', icon: 'arrow-up' },
      { value: 'total-desc', label: 'Mayor Monto', icon: 'currency-dollar' },
      { value: 'total-asc', label: 'Menor Monto', icon: 'currency-dollar' },
      { value: 'cliente-asc', label: 'Cliente A-Z', icon: 'sort-ascending' },
      { value: 'cliente-desc', label: 'Cliente Z-A', icon: 'sort-descending' }
    ]
  },

  clientes: {
    module: 'clientes',
    title: 'Clientes',
    createRoute: '/clientes/create',
    createButtonText: 'Nuevo Cliente',
    searchPlaceholder: 'Buscar por nombre, RFC, email, teléfono...',
    searchFields: ['nombre', 'rfc', 'email', 'telefono'],
    estadisticas: {
      total: { label: 'Total', icon: 'document', description: 'Total de clientes' },
      aprobadas: { label: 'Activos', icon: 'check-circle', color: 'green', description: 'Clientes activos' },
      pendientes: { label: 'Inactivos', icon: 'x-circle', color: 'red', description: 'Clientes inactivos' }
    },
    estados: [
      { value: '', label: 'Todos', color: 'slate' },
      { value: '1', label: 'Activos', color: 'green' },
      { value: '0', label: 'Inactivos', color: 'red' }
    ],
    sortOptions: [
      { value: 'fecha-desc', label: 'Más Recientes', icon: 'arrow-down' },
      { value: 'fecha-asc', label: 'Más Antiguos', icon: 'arrow-up' },
      { value: 'nombre-asc', label: 'Nombre A-Z', icon: 'sort-ascending' },
      { value: 'nombre-desc', label: 'Nombre Z-A', icon: 'sort-descending' },
      { value: 'rfc-asc', label: 'RFC A-Z', icon: 'identification' },
      { value: 'estado-asc', label: 'Estado', icon: 'status-online' }
    ]
  },

  // Agregar más configuraciones según sea necesario...
  ventas: {
    module: 'ventas',
    title: 'Ventas',
    createRoute: '/ventas/create',
    createButtonText: 'Nueva Venta',
    searchPlaceholder: 'Buscar ventas...',
    searchFields: ['numero', 'cliente', 'productos'],
    estadisticas: {
      total: { label: 'Total', icon: 'document', description: 'Total de ventas' },
      aprobadas: { label: 'Facturadas', icon: 'check-circle', color: 'green', description: 'Ventas facturadas' },
      pendientes: { label: 'Pendientes', icon: 'clock', color: 'orange', description: 'Ventas pendientes' }
    },
    estados: [
      { value: '', label: 'Todos los Estados', color: 'slate' },
      { value: 'borrador', label: 'Borrador', color: 'gray' },
      { value: 'pendiente', label: 'Pendientes', color: 'yellow' },
      { value: 'facturado', label: 'Facturadas', color: 'green' },
      { value: 'pagado', label: 'Pagadas', color: 'emerald' },
      { value: 'cancelado', label: 'Canceladas', color: 'red' }
    ],
    sortOptions: [
      { value: 'fecha-desc', label: 'Más Recientes', icon: 'arrow-down' },
      { value: 'fecha-asc', label: 'Más Antiguos', icon: 'arrow-up' },
      { value: 'total-desc', label: 'Mayor Monto', icon: 'currency-dollar' },
      { value: 'total-asc', label: 'Menor Monto', icon: 'currency-dollar' },
      { value: 'cliente-asc', label: 'Cliente A-Z', icon: 'sort-ascending' },
      { value: 'cliente-desc', label: 'Cliente Z-A', icon: 'sort-descending' }
    ]
  },

   productos: {
  module: 'productos',
  title: 'Productos',
  createRoute: '/productos/create',
  createButtonText: 'Nuevo Producto',
  searchPlaceholder: 'Buscar por nombre, SKU, descripción…',
  searchFields: ['nombre', 'sku', 'descripcion'],
  estadisticas: {
    total:      { label: 'Total',      icon: 'document',     description: 'Total de productos' },
    aprobadas:  { label: 'Disponibles', icon: 'check-circle', color: 'green',  description: 'Productos con stock' },
    pendientes: { label: 'Agotados',    icon: 'x-circle',     color: 'red',    description: 'Productos sin stock' }
  },
  estados: [
    { value: '',               label: 'Todos',         color: 'slate'   },
    { value: 'disponible',     label: 'Disponibles',   color: 'green'   },
    { value: 'agotado',        label: 'Agotados',      color: 'red'     },
    { value: 'descontinuado',  label: 'Descontinuados',color: 'gray'    }
  ],
  sortOptions: [
    { value: 'fecha-desc',   label: 'Más Recientes', icon: 'arrow-down' },
    { value: 'fecha-asc',    label: 'Más Antiguos',  icon: 'arrow-up'   },
    { value: 'nombre-asc',   label: 'Nombre A-Z',    icon: 'sort-ascending' },
    { value: 'nombre-desc',  label: 'Nombre Z-A',    icon: 'sort-descending' },
    { value: 'precio-desc',  label: 'Precio Mayor',  icon: 'currency-dollar' },
    { value: 'precio-asc',   label: 'Precio Menor',  icon: 'currency-dollar' }
  ]
},



};

// Configuración final combinando defaults con props
const finalConfig = computed(() => {
  const defaultConfig = defaultConfigs[props.config.module] || defaultConfigs.cotizaciones;
  return { ...defaultConfig, ...props.config };
});

// Emitimos eventos para v-model y nuevas funciones
const emit = defineEmits([
  'update:searchTerm',
  'update:sortBy',
  'update:filtroEstado',
  'limpiar-filtros',
  'exportar',
  'importar',
  'search-focus',
  'search-blur'
]);

// Computed properties mejoradas
const hayFiltrosActivos = computed(() => {
  return !!props.searchTerm || !!props.filtroEstado;
});

const estadisticasConPorcentaje = computed(() => {
  const total = props.total || 1; // Evitar división por cero
  return {
    aprobadas: {
      ...finalConfig.value.estadisticas.aprobadas,
      porcentaje: Math.round((props.aprobadas / total) * 100)
    },
    pendientes: {
      ...finalConfig.value.estadisticas.pendientes,
      porcentaje: Math.round((props.pendientes / total) * 100)
    }
  };
});

// Funciones mejoradas
const limpiarFiltros = () => {
  emit('update:searchTerm', '');
  emit('update:sortBy', 'fecha-desc');
  emit('update:filtroEstado', '');
  emit('limpiar-filtros');
};

const focusSearch = () => {
  if (searchInput.value) {
    searchInput.value.focus();
  }
};

const handleSearchFocus = () => {
  isSearchFocused.value = true;
  emit('search-focus');
};

const handleSearchBlur = () => {
  isSearchFocused.value = false;
  emit('search-blur');
};

// Atajos de teclado
const handleKeydown = (event) => {
  // Ctrl/Cmd + K para enfocar búsqueda
  if ((event.ctrlKey || event.metaKey) && event.key === 'k') {
    event.preventDefault();
    focusSearch();
  }

  // Escape para limpiar búsqueda si está enfocada
  if (event.key === 'Escape' && isSearchFocused.value) {
    emit('update:searchTerm', '');
  }
};

// Watch para atajos de teclado
watch(() => props.searchTerm, (newValue) => {
  if (!newValue && isSearchFocused.value) {
    searchInput.value?.blur();
  }
});

// Iconos SVG mejorados y más completos
const icons = {
  document: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
  'check-circle': 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
  'x-circle': 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z',
  clock: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
  plus: 'M12 4v16m8-8H4',
  search: 'M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z',
  x: 'M6 18L18 6M6 6l12 12',
  'filter-x': 'M6 18L18 6M6 6l12 12',
  'chevron-down': 'M19 9l-7 7-7-7',
  'arrow-down': 'M19 14l-7 7m0 0l-7-7m7 7V3',
  'arrow-up': 'M5 10l7-7m0 0l7 7m-7-7v18',
  'currency-dollar': 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1',
  'sort-ascending': 'M3 4h6m0 0l4-4m-4 4l4 4M3 8h11m-11 4h14m-14 4h11m-11 4h6',
  'sort-descending': 'M3 4h14m-14 4h11m-11 4h6m0 0l-4 4m4-4l4-4M3 16h11m-11 4h14',
  export: 'M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2z',
  import: 'M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10',
  loading: 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15',
  identification: 'M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2',
  'status-online': 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'
};

// Clases de color mejoradas con más variaciones
const colorClasses = {
  // Colores principales
  green: {
    text: 'text-emerald-600',
    bg: 'bg-emerald-50',
    border: 'border-emerald-200',
    ring: 'ring-emerald-500/20'
  },
  blue: {
    text: 'text-blue-600',
    bg: 'bg-blue-50',
    border: 'border-blue-200',
    ring: 'ring-blue-500/20'
  },
  yellow: {
    text: 'text-amber-600',
    bg: 'bg-amber-50',
    border: 'border-amber-200',
    ring: 'ring-amber-500/20'
  },
  orange: {
    text: 'text-orange-600',
    bg: 'bg-orange-50',
    border: 'border-orange-200',
    ring: 'ring-orange-500/20'
  },
  red: {
    text: 'text-red-600',
    bg: 'bg-red-50',
    border: 'border-red-200',
    ring: 'ring-red-500/20'
  },
  slate: {
    text: 'text-slate-600',
    bg: 'bg-slate-50',
    border: 'border-slate-200',
    ring: 'ring-slate-500/20'
  },
  indigo: {
    text: 'text-indigo-600',
    bg: 'bg-indigo-50',
    border: 'border-indigo-200',
    ring: 'ring-indigo-500/20'
  },
  emerald: {
    text: 'text-emerald-600',
    bg: 'bg-emerald-50',
    border: 'border-emerald-200',
    ring: 'ring-emerald-500/20'
  },
  gray: {
    text: 'text-gray-600',
    bg: 'bg-gray-50',
    border: 'border-gray-200',
    ring: 'ring-gray-500/20'
  }
};

const getColorClasses = (color) => colorClasses[color] || colorClasses.slate;

// Formatear números con separadores de miles y moneda
const formatNumber = (num, tipo = 'number') => {
  if (tipo === 'currency') {
    return new Intl.NumberFormat('es-MX', {
      style: 'currency',
      currency: 'MXN'
    }).format(num);
  }
  return new Intl.NumberFormat('es-ES').format(num);
};

// Función para obtener el ícono correcto según el contexto
const getIconPath = (iconName) => {
  return icons[iconName] || icons.document;
};
</script>

<template>
  <div
    class="bg-white border border-slate-200 rounded-xl shadow-sm p-8 mb-6 transition-all duration-300 hover:shadow-lg"
    @keydown="handleKeydown"
    tabindex="-1"
  >
    <div class="flex flex-col lg:flex-row gap-8 items-start lg:items-center justify-between">

      <!-- Sección izquierda: Título, botón crear y estadísticas -->
      <div class="flex flex-col gap-6 w-full lg:w-auto">

        <!-- Título del módulo -->
        <div class="flex items-center gap-3">
          <h1 class="text-2xl font-bold text-slate-900">
            {{ finalConfig.title }}
          </h1>
          <div
            v-if="loading"
            class="animate-spin w-5 h-5 text-blue-500"
          >
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                :d="getIconPath('loading')"
              />
            </svg>
          </div>
        </div>

        <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center">

          <!-- Botón crear mejorado con estado de carga -->
          <Link
            :href="finalConfig.createRoute"
            :class="{
              'opacity-50 cursor-not-allowed pointer-events-none': disabled,
              'hover:from-blue-700 hover:via-blue-700 hover:to-blue-800 hover:scale-[1.02] hover:shadow-xl': !disabled
            }"
            class="group inline-flex items-center gap-2.5 px-6 py-3 bg-gradient-to-r from-blue-600 via-blue-600 to-blue-700 text-white font-semibold rounded-xl focus:outline-none focus:ring-4 focus:ring-blue-500/20 active:scale-[0.98] transition-all duration-200 shadow-lg flex-shrink-0"
          >
            <svg
              :class="{
                'animate-spin': loading,
                'group-hover:rotate-90': !loading && !disabled
              }"
              class="w-5 h-5 transition-transform duration-200"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2.5"
            >
              <path :d="loading ? getIconPath('loading') : getIconPath('plus')" />
            </svg>
            <span class="tracking-wide">
              {{ loading ? 'Cargando...' : finalConfig.createButtonText }}
            </span>
          </Link>

          <!-- Botones de exportar/importar -->
          <div v-if="showExport || showImport" class="flex gap-2">
            <button
              v-if="showExport"
              @click="$emit('exportar')"
              :disabled="disabled"
              class="group inline-flex items-center gap-2 px-4 py-3 bg-green-50 text-green-700 rounded-xl hover:bg-green-100 focus:outline-none focus:ring-4 focus:ring-green-500/20 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed border border-green-200"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="getIconPath('export')" />
              </svg>
              <span class="text-sm font-medium">Exportar</span>
            </button>

            <button
              v-if="showImport"
              @click="$emit('importar')"
              :disabled="disabled"
              class="group inline-flex items-center gap-2 px-4 py-3 bg-indigo-50 text-indigo-700 rounded-xl hover:bg-indigo-100 focus:outline-none focus:ring-4 focus:ring-indigo-500/20 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed border border-indigo-200"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="getIconPath('import')" />
              </svg>
              <span class="text-sm font-medium">Importar</span>
            </button>
          </div>
        </div>

        <!-- Estadísticas mejoradas con tooltips y porcentajes -->
        <div class="flex flex-wrap items-center gap-4 text-sm">
          <!-- Total -->
          <div
            class="group flex items-center gap-2 px-4 py-3 bg-slate-50 rounded-xl border border-slate-200 flex-shrink-0 hover:bg-slate-100 transition-all duration-200 cursor-default"
            :title="finalConfig.estadisticas.total.description"
          >
            <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="getIconPath(finalConfig.estadisticas.total.icon)" />
            </svg>
            <span class="font-medium text-slate-700">{{ finalConfig.estadisticas.total.label }}:</span>
            <span class="font-bold text-slate-900 text-lg">{{ formatNumber(total) }}</span>
          </div>

          <!-- Aprobadas/Completados con porcentaje -->
          <div
            :class="getColorClasses(finalConfig.estadisticas.aprobadas.color).bg + ' ' + getColorClasses(finalConfig.estadisticas.aprobadas.color).border"
            class="group flex items-center gap-2 px-4 py-3 rounded-xl border flex-shrink-0 hover:shadow-sm transition-all duration-200 cursor-default"
            :title="finalConfig.estadisticas.aprobadas.description"
          >
            <svg
              :class="getColorClasses(finalConfig.estadisticas.aprobadas.color).text"
              class="w-5 h-5"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="getIconPath(finalConfig.estadisticas.aprobadas.icon)" />
            </svg>
            <span class="font-medium text-slate-700">{{ finalConfig.estadisticas.aprobadas.label }}:</span>
            <span
              :class="getColorClasses(finalConfig.estadisticas.aprobadas.color).text"
              class="font-bold text-lg"
            >
              {{ formatNumber(aprobadas) }}
            </span>
            <span
              v-if="total > 0"
              :class="getColorClasses(finalConfig.estadisticas.aprobadas.color).text"
              class="text-xs font-medium opacity-75"
            >
              ({{ estadisticasConPorcentaje.aprobadas.porcentaje }}%)
            </span>
          </div>

          <!-- Pendientes con porcentaje -->
          <div
            :class="getColorClasses(finalConfig.estadisticas.pendientes.color).bg + ' ' + getColorClasses(finalConfig.estadisticas.pendientes.color).border"
            class="group flex items-center gap-2 px-4 py-3 rounded-xl border flex-shrink-0 hover:shadow-sm transition-all duration-200 cursor-default"
            :title="finalConfig.estadisticas.pendientes.description"
          >
            <svg
              :class="getColorClasses(finalConfig.estadisticas.pendientes.color).text"
              class="w-5 h-5"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="getIconPath(finalConfig.estadisticas.pendientes.icon)" />
            </svg>
            <span class="font-medium text-slate-700">{{ finalConfig.estadisticas.pendientes.label }}:</span>
            <span
              :class="getColorClasses(finalConfig.estadisticas.pendientes.color).text"
              class="font-bold text-lg"
            >
              {{ formatNumber(pendientes) }}
            </span>
            <span
              v-if="total > 0"
              :class="getColorClasses(finalConfig.estadisticas.pendientes.color).text"
              class="text-xs font-medium opacity-75"
            >
              ({{ estadisticasConPorcentaje.pendientes.porcentaje }}%)
            </span>
          </div>
        </div>
      </div>

      <!-- Sección derecha: Búsqueda y filtros -->
      <div class="flex flex-col sm:flex-row gap-3 w-full lg:w-auto lg:flex-shrink-0 lg:min-w-0">

        <!-- Campo de búsqueda mejorado con atajo de teclado -->
        <div class="relative group">
          <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
            <svg
              :class="{
                'text-blue-500': isSearchFocused,
                'text-slate-400': !isSearchFocused
              }"
              class="w-4 h-4 transition-colors duration-200"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="getIconPath('search')" />
            </svg>
          </div>
          <input
            ref="searchInput"
            :value="searchTerm"
            @input="$emit('update:searchTerm', $event.target.value)"
            @focus="handleSearchFocus"
            @blur="handleSearchBlur"
            type="text"
            :placeholder="finalConfig.searchPlaceholder"
            :disabled="disabled"
            :class="{
              'border-blue-500 ring-4 ring-blue-500/10 bg-blue-50/50': isSearchFocused,
              'border-slate-300': !isSearchFocused,
              'opacity-50 cursor-not-allowed': disabled
            }"
            class="w-full sm:w-64 lg:w-80 pl-11 pr-20 py-3 border rounded-xl bg-white text-slate-900 placeholder-slate-400 focus:outline-none transition-all duration-200"
          />

          <!-- Indicador de atajo de teclado -->
          <div
            v-if="!searchTerm && !isSearchFocused && !disabled"
            class="absolute inset-y-0 right-12 flex items-center pointer-events-none"
          >
            <kbd class="hidden sm:inline-flex items-center gap-1 px-2 py-1 text-xs font-mono text-slate-400 bg-slate-100 rounded border border-slate-200">
              <span class="text-xs">⌘</span>K
            </kbd>
          </div>

          <!-- Botón limpiar búsqueda -->
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
              class="absolute inset-y-0 right-4 flex items-center text-slate-400 hover:text-slate-600 focus:text-blue-500 focus:outline-none transition-colors duration-200 p-1 rounded-md hover:bg-slate-100"
              title="Limpiar búsqueda (Esc)"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="getIconPath('x')" />
              </svg>
            </button>
          </Transition>
        </div>

        <!-- Select de ordenamiento mejorado con iconos -->
        <div class="relative flex-shrink-0">
          <select
            :value="sortBy"
            @change="$emit('update:sortBy', $event.target.value)"
            :disabled="disabled"
            :class="{
              'opacity-50 cursor-not-allowed': disabled,
              'hover:bg-slate-50 cursor-pointer': !disabled
            }"
            class="appearance-none w-full sm:w-auto px-4 py-3 pr-10 border border-slate-300 rounded-xl bg-white text-slate-900 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all duration-200"
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
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="getIconPath('chevron-down')" />
            </svg>
          </div>
        </div>

        <!-- Select de estado mejorado con indicadores visuales -->
        <div class="relative flex-shrink-0">
          <select
            :value="filtroEstado"
            @change="$emit('update:filtroEstado', $event.target.value)"
            :disabled="disabled"
            :class="{
              'border-blue-500 bg-blue-50/50 text-blue-700': filtroEstado && !disabled,
              'border-slate-300': !filtroEstado || disabled,
              'opacity-50 cursor-not-allowed': disabled,
              'hover:bg-slate-50 cursor-pointer': !disabled
            }"
            class="appearance-none w-full sm:w-auto px-4 py-3 pr-10 border rounded-xl bg-white text-slate-900 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all duration-200"
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
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="getIconPath('chevron-down')" />
            </svg>
          </div>
          <!-- Indicador de filtro activo -->
          <div
            v-if="filtroEstado"
            class="absolute -top-1 -right-1 w-3 h-3 bg-blue-500 rounded-full border-2 border-white"
          ></div>
        </div>

        <!-- Botón limpiar filtros mejorado con contador -->
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
            :disabled="disabled"
            class="group relative inline-flex items-center gap-2 px-4 py-3 bg-slate-100 text-slate-600 rounded-xl hover:bg-slate-200 hover:text-slate-700 focus:outline-none focus:ring-4 focus:ring-slate-500/10 transition-all duration-200 whitespace-nowrap border border-slate-200 flex-shrink-0 disabled:opacity-50 disabled:cursor-not-allowed"
            title="Limpiar todos los filtros"
          >
            <svg class="w-4 h-4 transition-transform duration-200 group-hover:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="getIconPath('filter-x')" />
            </svg>
            <span class="font-medium">Limpiar</span>
            <!-- Contador de filtros activos -->
            <div class="absolute -top-1 -right-1 min-w-[1.25rem] h-5 bg-red-500 text-white text-xs font-bold rounded-full flex items-center justify-center px-1">
              {{ (searchTerm ? 1 : 0) + (filtroEstado ? 1 : 0) }}
            </div>
          </button>
        </Transition>
      </div>
    </div>

    <!-- Barra de progreso para mostrar porcentajes de manera visual (opcional) -->
    <div v-if="total > 0" class="mt-6 pt-4 border-t border-slate-100">
      <div class="flex items-center gap-4 text-xs text-slate-500 mb-2">
        <span>Distribución:</span>
        <div class="flex items-center gap-1">
          <div class="w-3 h-3 rounded-full bg-emerald-500"></div>
          <span>{{ estadisticasConPorcentaje.aprobadas.porcentaje }}% {{ finalConfig.estadisticas.aprobadas.label }}</span>
        </div>
        <div class="flex items-center gap-1">
          <div
            class="w-3 h-3 rounded-full"
            :class="getColorClasses(finalConfig.estadisticas.pendientes.color).text.replace('text-', 'bg-')"
          ></div>
          <span>{{ estadisticasConPorcentaje.pendientes.porcentaje }}% {{ finalConfig.estadisticas.pendientes.label }}</span>
        </div>
      </div>

      <div class="w-full bg-slate-200 rounded-full h-2 overflow-hidden">
        <div
          class="h-full bg-emerald-500 rounded-full transition-all duration-500 ease-out"
          :style="{ width: `${estadisticasConPorcentaje.aprobadas.porcentaje}%` }"
        ></div>
        <div
          :class="getColorClasses(finalConfig.estadisticas.pendientes.color).text.replace('text-', 'bg-')"
          class="h-full rounded-full transition-all duration-500 ease-out -mt-2"
          :style="{
            width: `${estadisticasConPorcentaje.pendientes.porcentaje}%`,
            marginLeft: `${estadisticasConPorcentaje.aprobadas.porcentaje}%`
          }"
        ></div>
      </div>
    </div>

    <!-- Indicador de filtros activos más detallado -->
    <Transition
      enter-active-class="transition-all duration-300"
      enter-from-class="opacity-0 -translate-y-2"
      enter-to-class="opacity-100 translate-y-0"
      leave-active-class="transition-all duration-200"
      leave-from-class="opacity-100 translate-y-0"
      leave-to-class="opacity-0 -translate-y-2"
    >
      <div v-if="hayFiltrosActivos" class="mt-4 pt-4 border-t border-slate-100">
        <div class="flex flex-wrap items-center gap-2 text-sm">
          <span class="text-slate-500 font-medium">Filtros activos:</span>

          <div v-if="searchTerm" class="inline-flex items-center gap-1 px-3 py-1 bg-blue-100 text-blue-700 rounded-full">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="getIconPath('search')" />
            </svg>
            <span class="font-medium">Búsqueda:</span>
            <span class="max-w-32 truncate">{{ searchTerm }}</span>
            <button
              @click="$emit('update:searchTerm', '')"
              class="ml-1 hover:bg-blue-200 rounded-full p-0.5 transition-colors duration-150"
            >
              <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="getIconPath('x')" />
              </svg>
            </button>
          </div>

          <div v-if="filtroEstado" class="inline-flex items-center gap-1 px-3 py-1 bg-green-100 text-green-700 rounded-full">
            <div
              class="w-2 h-2 rounded-full"
              :class="getColorClasses(finalConfig.estados.find(e => e.value === filtroEstado)?.color || 'slate').text.replace('text-', 'bg-')"
            ></div>
            <span class="font-medium">Estado:</span>
            <span>{{ finalConfig.estados.find(e => e.value === filtroEstado)?.label }}</span>
            <button
              @click="$emit('update:filtroEstado', '')"
              class="ml-1 hover:bg-green-200 rounded-full p-0.5 transition-colors duration-150"
            >
              <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="getIconPath('x')" />
              </svg>
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>

<style scoped>
/* Animación personalizada para el spinner de carga */
@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

.animate-spin {
  animation: spin 1s linear infinite;
}

/* Mejoras en la accesibilidad y focus */
.focus-visible:focus {
  outline: 2px solid theme('colors.blue.500');
  outline-offset: 2px;
}

/* Transiciones suaves para los elementos interactivos */
button, select, input {
  transition-property: all;
  transition-duration: 200ms;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
}

/* Estilo mejorado para los kbd elementos */
kbd {
  font-family: ui-monospace, SFMono-Regular, "SF Mono", Consolas, "Liberation Mono", Menlo, monospace;
  font-size: 0.75rem;
  font-weight: 500;
}
</style>
