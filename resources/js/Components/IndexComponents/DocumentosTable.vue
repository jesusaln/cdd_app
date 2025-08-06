<template>
  <div class="bg-white rounded-lg shadow-sm overflow-hidden">
    <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
      <div class="flex items-center justify-between">
        <h2 class="text-lg font-semibold text-gray-900">{{ config.titulo }}</h2>
        <div class="text-sm text-gray-500">
          Mostrando {{ items.length }} de {{ total }} {{ config.titulo.toLowerCase() }}
        </div>
      </div>
    </div>

    <!-- Tooltip/Modal de productos -->
    <div
      v-if="showTooltip && hoveredDoc"
      class="fixed z-50 bg-white rounded-lg shadow-2xl border border-gray-200 p-4 w-80 max-h-96 pointer-events-none"
      :style="tooltipStyle"
    >
      <div class="flex items-center justify-between mb-3">
        <h3 class="text-sm font-semibold text-gray-900">Productos</h3>
        <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded-full">
          {{ hoveredDoc.productos?.length || 0 }} items
        </span>
      </div>

      <!-- Contenedor con scroll -->
      <div class="max-h-72 overflow-y-auto pr-2 scrollbar-thin scrollbar-thumb-gray-300 scrollbar-track-gray-100">
        <div v-if="hoveredDoc.productos && hoveredDoc.productos.length > 0" class="space-y-2">
          <div
            v-for="(producto, index) in hoveredDoc.productos"
            :key="index"
            class="flex items-start justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors"
          >
            <div class="flex-1 min-w-0 mr-3">
              <p class="text-sm font-medium text-gray-900 truncate">
                {{ producto.nombre || 'Sin nombre' }}
              </p>
              <div class="flex items-center mt-1 space-x-2">
                <span class="text-xs text-gray-500">
                  Cant: {{ producto.cantidad || 0 }}
                </span>
                <span class="text-xs text-gray-300">•</span>
                <span class="text-xs text-gray-500">
                  ${{ formatearMoneda(producto.precio || 0) }}
                </span>
              </div>
              <div v-if="producto.descripcion" class="mt-1">
                <p class="text-xs text-gray-600 line-clamp-2">
                  {{ producto.descripcion }}
                </p>
              </div>
            </div>
            <div class="text-right flex-shrink-0">
              <p class="text-sm font-semibold text-gray-900">
                ${{ formatearMoneda((producto.cantidad || 0) * (producto.precio || 0)) }}
              </p>
            </div>
          </div>
        </div>
        <div v-else class="text-center py-8">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2M4 13h2m0 0V9a2 2 0 012-2h2m2 2v4" />
          </svg>
          <p class="text-sm text-gray-500 mt-2">Sin productos</p>
        </div>
      </div>
    </div>

    <div class="min-h-96">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th
              class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider cursor-pointer"
              @click="onSort('fecha')"
            >
              Fecha
              <svg
                v-if="sortBy.startsWith('fecha')"
                :class="sortBy === 'fecha-desc' ? 'rotate-180' : ''"
                class="w-4 h-4 ml-1 inline transition-transform"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M19 9l-7 7-7-7"
                />
              </svg>
            </th>
            <th
              class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider cursor-pointer"
              @click="onSort('cliente')"
            >
              Cliente
              <svg
                v-if="sortBy.startsWith('cliente')"
                :class="sortBy === 'cliente-desc' ? 'rotate-180' : ''"
                class="w-4 h-4 ml-1 inline transition-transform"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M19 9l-7 7-7-7"
                />
              </svg>
            </th>
            <th
              v-if="config.mostrarCampoExtra"
              class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider cursor-pointer"
              @click="onSort(config.campoExtra.key)"
            >
              {{ config.campoExtra.label }}
              <svg
                v-if="sortBy.startsWith(config.campoExtra.key)"
                :class="sortBy === `${config.campoExtra.key}-desc` ? 'rotate-180' : ''"
                class="w-4 h-4 ml-1 inline transition-transform"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M19 9l-7 7-7-7"
                />
              </svg>
            </th>
            <th
              class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider cursor-pointer"
              @click="onSort('total')"
            >
              Total
              <svg
                v-if="sortBy.startsWith('total')"
                :class="sortBy === 'total-desc' ? 'rotate-180' : ''"
                class="w-4 h-4 ml-1 inline transition-transform"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M19 9l-7 7-7-7"
                />
              </svg>
            </th>
            <th
              class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider"
            >
              Productos
            </th>
            <th
              class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider cursor-pointer"
              @click="onSort('estado')"
            >
              Estado
              <svg
                v-if="sortBy.startsWith('estado')"
                :class="sortBy === 'estado-desc' ? 'rotate-180' : ''"
                class="w-4 h-4 ml-1 inline transition-transform"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M19 9l-7 7-7-7"
                />
              </svg>
            </th>
            <th
              class="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider"
            >
              Acciones
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <template v-if="items.length > 0">
            <tr
              v-for="doc in items"
              :key="doc.id"
              class="hover:bg-gray-50 transition-colors"
            >
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex flex-col">
                  <div class="text-sm font-medium text-gray-900">
                    {{ formatearFecha(doc.created_at || doc.fecha) }}
                  </div>
                  <div class="text-xs text-gray-500">
                    {{ formatearHora(doc.created_at || doc.fecha) }}
                  </div>
                </div>
              </td>
              <td class="px-6 py-4">
                <div class="text-sm font-medium text-gray-900">
                  {{ doc.cliente?.nombre || 'Sin cliente' }}
                </div>
                <div v-if="doc.cliente?.email" class="text-xs text-gray-500">
                  {{ doc.cliente.email }}
                </div>
              </td>
              <td v-if="config.mostrarCampoExtra" class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">
                  {{ doc.id }}
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">
                  ${{ formatearMoneda(doc.total) }}
                </div>
              </td>
              <td
                class="px-6 py-4 whitespace-nowrap relative"
                @mouseenter="showProductTooltip(doc, $event)"
                @mouseleave="hideProductTooltip"
                @mousemove="updateTooltipPosition($event)"
              >
                <div class="flex items-center text-sm text-gray-600 cursor-help">
                  <svg
                    class="w-4 h-4 mr-1 text-gray-400"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                    />
                  </svg>
                  {{ doc.productos?.length || 0 }} items
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span
                  :class="obtenerClasesEstado(doc.estado)"
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                >
                  <span
                    class="w-1.5 h-1.5 rounded-full mr-1.5"
                    :class="obtenerColorPuntoEstado(doc.estado)"
                  ></span>
                  {{ obtenerLabelEstado(doc.estado) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right">
                <div class="flex items-center justify-end space-x-1">
                  <!-- Botón Ver -->
                  <button
                    @click="onVerDetalles(doc)"
                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 hover:text-blue-700 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1"
                    title="Ver detalles"
                  >
                    <font-awesome-icon icon="eye" class="w-4 h-4" />
                  </button>

                  <!-- Botón Editar -->
                  <button
                    v-if="config.acciones.editar"
                    @click="onEditar(doc.id)"
                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-100 hover:text-amber-700 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-1"
                    title="Editar"
                  >
                    <font-awesome-icon icon="edit" class="w-4 h-4" />
                  </button>

                  <!-- Botón Duplicar -->
                  <button
                    v-if="config.acciones.duplicar && tipo === 'cotizaciones'"
                    @click="onDuplicar(doc)"
                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-green-50 text-green-600 hover:bg-green-100 hover:text-green-700 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-1"
                    title="Duplicar"
                  >
                    <font-awesome-icon icon="copy" class="w-4 h-4" />
                  </button>

                  <!-- Botón Imprimir -->
                  <button
                    v-if="config.acciones.imprimir"
                    @click="onImprimir(doc)"
                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-purple-50 text-purple-600 hover:bg-purple-100 hover:text-purple-700 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-1"
                    title="Imprimir"
                  >
                    <font-awesome-icon icon="print" class="w-4 h-4" />
                  </button>

                  <!-- Botón Eliminar -->
                  <button
                    v-if="config.acciones.eliminar"
                    @click="onEliminar(doc.id)"
                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 hover:text-red-700 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-1"
                    title="Eliminar"
                  >
                    <font-awesome-icon icon="trash" class="w-4 h-4" />
                  </button>
                </div>
              </td>
            </tr>
          </template>
          <tr v-else>
            <td :colspan="config.mostrarCampoExtra ? 7 : 6" class="px-6 py-12 text-center text-gray-500">
              <div class="space-y-2">
                <div>No hay {{ config.titulo.toLowerCase() }}</div>
                <div class="text-xs">
                  Props length: {{ documentos.length }} |
                  Items length: {{ items.length }}
                </div>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, nextTick } from 'vue';

const props = defineProps({
  documentos: {
    type: Array,
    default: () => []
  },
  tipo: {
    type: String,
    required: true,
    validator: (value) => ['cotizaciones', 'pedidos', 'ventas'].includes(value)
  },
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
  }
});

const emit = defineEmits([
  'ver-detalles',
  'editar',
  'eliminar',
  'duplicar',
  'imprimir',
  'sort'
]);

// Estado del tooltip
const showTooltip = ref(false);
const hoveredDoc = ref(null);
const tooltipPosition = ref({ x: 0, y: 0 });
let tooltipTimeout = null;

// Estilo computed para el tooltip
const tooltipStyle = computed(() => {
  const offset = 20; // Offset desde el cursor
  const tooltipWidth = 320; // Ancho del tooltip (w-80 = 320px)
  const tooltipHeight = 384; // Altura máxima del tooltip (max-h-96 = 384px)

  // Obtener dimensiones de la ventana
  const windowWidth = window.innerWidth;
  const windowHeight = window.innerHeight;

  let x = tooltipPosition.value.x + offset;
  let y = tooltipPosition.value.y;

  // Ajustar posición X si se sale de la pantalla
  if (x + tooltipWidth > windowWidth) {
    x = tooltipPosition.value.x - tooltipWidth - offset;
  }

  // Ajustar posición Y para centrar en el cursor
  y = tooltipPosition.value.y - (tooltipHeight / 2);

  // Asegurar que no se salga por arriba o abajo
  if (y < 10) {
    y = 10;
  } else if (y + tooltipHeight > windowHeight - 10) {
    y = windowHeight - tooltipHeight - 10;
  }

  return {
    left: `${x}px`,
    top: `${y}px`,
  };
});

// Funciones del tooltip
const showProductTooltip = (doc, event) => {
  clearTimeout(tooltipTimeout);

  hoveredDoc.value = doc;
  tooltipPosition.value = {
    x: event.clientX,
    y: event.clientY
  };

  tooltipTimeout = setTimeout(() => {
    showTooltip.value = true;
  }, 300); // Delay de 300ms para evitar tooltips accidentales
};

const hideProductTooltip = () => {
  clearTimeout(tooltipTimeout);

  tooltipTimeout = setTimeout(() => {
    showTooltip.value = false;
    hoveredDoc.value = null;
  }, 100); // Delay pequeño para permitir hover sobre el tooltip
};

const updateTooltipPosition = (event) => {
  if (showTooltip.value) {
    tooltipPosition.value = {
      x: event.clientX,
      y: event.clientY
    };
  }
};

// Configuración específica para cada tipo de documento
const config = computed(() => {
  const configs = {
    cotizaciones: {
      titulo: 'Cotizaciones',
      mostrarCampoExtra: true,
      campoExtra: { key: 'id', label: 'N° Cotización' },
      acciones: {
        editar: true,
        duplicar: true,
        imprimir: true,
        eliminar: true
      },
      estados: {
        'borrador': { label: 'Borrador', classes: 'bg-gray-100 text-gray-800', color: 'bg-gray-400' },
        'pendiente': { label: 'Pendiente', classes: 'bg-yellow-100 text-yellow-800', color: 'bg-yellow-400' },
        'enviado_pedido': { label: 'Enviado a Pedido', classes: 'bg-blue-100 text-blue-800', color: 'bg-blue-400' },
        'enviado_venta': { label: 'Enviado a Venta', classes: 'bg-indigo-100 text-indigo-800', color: 'bg-indigo-400' },
        'aprobado': { label: 'Aprobada', classes: 'bg-green-100 text-green-800', color: 'bg-green-400' },
        'rechazado': { label: 'Rechazada', classes: 'bg-red-100 text-red-800', color: 'bg-red-400' },
        'cancelado': { label: 'Cancelada', classes: 'bg-gray-100 text-gray-800', color: 'bg-gray-400' }
      }
    },
    pedidos: {
      titulo: 'Pedidos',
      mostrarCampoExtra: true,
      campoExtra: { key: 'numero_pedido', label: 'N° Pedido' },
      acciones: {
        editar: true,
        duplicar: false,
        imprimir: true,
        eliminar: true
      },
      estados: {
        'borrador': { label: 'Borrador', classes: 'bg-gray-100 text-gray-800', color: 'bg-gray-400' },
        'pendiente': { label: 'Pendiente', classes: 'bg-yellow-100 text-yellow-800', color: 'bg-yellow-400' },
        'confirmado': { label: 'Confirmado', classes: 'bg-blue-100 text-blue-800', color: 'bg-blue-400' },
        'en_preparacion': { label: 'En Preparación', classes: 'bg-orange-100 text-orange-800', color: 'bg-orange-400' },
        'listo_entrega': { label: 'Listo para Entrega', classes: 'bg-purple-100 text-purple-800', color: 'bg-purple-400' },
        'entregado': { label: 'Entregado', classes: 'bg-green-100 text-green-800', color: 'bg-green-400' },
        'cancelado': { label: 'Cancelado', classes: 'bg-red-100 text-red-800', color: 'bg-red-400' }
      }
    },
    ventas: {
      titulo: 'Ventas',
      mostrarCampoExtra: true,
      campoExtra: { key: 'numero_factura', label: 'N° Factura' },
      acciones: {
        editar: false,
        duplicar: false,
        imprimir: true,
        eliminar: false
      },
      estados: {
        'borrador': { label: 'Borrador', classes: 'bg-gray-100 text-gray-800', color: 'bg-gray-400' },
        'facturado': { label: 'Facturado', classes: 'bg-blue-100 text-blue-800', color: 'bg-blue-400' },
        'pagado': { label: 'Pagado', classes: 'bg-green-100 text-green-800', color: 'bg-green-400' },
        'vencido': { label: 'Vencido', classes: 'bg-red-100 text-red-800', color: 'bg-red-400' },
        'anulado': { label: 'Anulado', classes: 'bg-gray-100 text-gray-800', color: 'bg-gray-400' }
      }
    }
  };
  return configs[props.tipo] || configs.cotizaciones;
});

// Métodos de formateo
const formatearFecha = (date) => {
  if (!date) return 'Fecha no disponible';
  try {
    return new Date(date).toLocaleDateString('es-MX');
  } catch (error) {
    console.error('Error formatting date:', date, error);
    return 'Fecha inválida';
  }
};

const formatearHora = (date) => {
  if (!date) return '';
  try {
    return new Date(date).toLocaleTimeString('es-MX', {
      hour: '2-digit',
      minute: '2-digit'
    });
  } catch (error) {
    console.error('Error formatting time:', date, error);
    return '';
  }
};

const formatearMoneda = (num) => {
  const value = parseFloat(num) || 0;
  return new Intl.NumberFormat('es-MX', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(value);
};

// Estados dinámicos
const obtenerClasesEstado = (estado) => {
  const estadoConfig = config.value.estados[estado];
  return estadoConfig ? estadoConfig.classes : 'bg-gray-100 text-gray-800';
};

const obtenerColorPuntoEstado = (estado) => {
  const estadoConfig = config.value.estados[estado];
  return estadoConfig ? estadoConfig.color : 'bg-gray-400';
};

const obtenerLabelEstado = (estado) => {
  const estadoConfig = config.value.estados[estado];
  return estadoConfig ? estadoConfig.label : 'Sin estado';
};

// Computed: datos filtrados y ordenados
const items = computed(() => {
  if (!props.documentos || !Array.isArray(props.documentos)) {
    console.warn('⚠️ Documentos is not an array:', props.documentos);
    return [];
  }

  let filtered = props.documentos.map((doc) => ({
    id: doc.id,
    cliente: doc.cliente ? { ...doc.cliente } : { nombre: 'Sin cliente' },
    productos: Array.isArray(doc.productos) ? [...doc.productos] : [],
    total: parseFloat(doc.total) || 0,
    estado: doc.estado || 'borrador',
    created_at: doc.created_at,
    fecha: doc.fecha,
    numero_pedido: doc.numero_pedido,
    numero_factura: doc.numero_factura,
    numero_cotizacion: doc.numero_cotizacion
  }));

  // Filtro por término de búsqueda
  if (props.searchTerm) {
    const term = props.searchTerm.toLowerCase();
    filtered = filtered.filter(doc => {
      const clienteMatch = (doc.cliente?.nombre || '').toLowerCase().includes(term);
      const productosMatch = doc.productos.some(p => (p.nombre || '').toLowerCase().includes(term));
      const numeroMatch = (doc.numero_pedido || doc.numero_factura || doc.id || '').toString().toLowerCase().includes(term);
      return clienteMatch || productosMatch || numeroMatch;
    });
  }

  // Filtro por estado
  if (props.filtroEstado) {
    filtered = filtered.filter(doc => doc.estado === props.filtroEstado);
  }

  // Ordenamiento por el campo extra (id) de manera descendente
  return filtered.sort((a, b) => {
    const field = config.value.campoExtra.key;
    return b[field] - a[field];
  });
});

// Total general
const total = computed(() => props.documentos?.length || 0);

// Emits
const onVerDetalles = (doc) => emit('ver-detalles', doc);
const onEditar = (id) => emit('editar', id);
const onEliminar = (id) => emit('eliminar', id);
const onDuplicar = (doc) => {
  console.log('Duplicar documento:', doc);
  emit('duplicar', doc);
};
const onImprimir = (doc) => {
  console.log('Imprimir documento:', doc);
  emit('imprimir', doc);
};
const onSort = (field) => {
  const current = props.sortBy.startsWith(field) ? props.sortBy : `${field}-desc`;
  const newOrder = current === `${field}-desc` ? `${field}-asc` : `${field}-desc`;
  emit('sort', newOrder);
};
</script>

<style scoped>
/* Estilos personalizados para el scrollbar */
.scrollbar-thin {
  scrollbar-width: thin;
}

.scrollbar-thumb-gray-300::-webkit-scrollbar-thumb {
  background-color: #d1d5db;
  border-radius: 0.375rem;
}

.scrollbar-track-gray-100::-webkit-scrollbar-track {
  background-color: #f3f4f6;
}

.scrollbar-thin::-webkit-scrollbar {
  width: 6px;
}

/* Clase para truncar texto en múltiples líneas */
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
