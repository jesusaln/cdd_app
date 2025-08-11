<template>
  <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <!-- Header -->
    <div class="bg-gradient-to-r from-gray-50 to-gray-100/50 px-6 py-4 border-b border-gray-200/60">
      <div class="flex items-center justify-between">
        <h2 class="text-lg font-semibold text-gray-900 tracking-tight">{{ config.titulo }}</h2>
        <div class="text-sm text-gray-600 bg-white/70 px-3 py-1 rounded-full border border-gray-200/50">
          {{ items.length }} de {{ total }} {{ config.titulo.toLowerCase() }}
        </div>
      </div>
    </div>

    <!-- Tooltip -->
    <Teleport to="body">
      <div
        v-if="showTooltip && hoveredDoc"
        class="fixed z-[9999] bg-white rounded-xl shadow-xl border border-gray-200/50 backdrop-blur-sm w-80 max-h-96 pointer-events-none transform transition-all duration-200 ease-out"
        :style="tooltipStyle"
      >
        <div class="p-4 border-b border-gray-100">
          <div class="flex items-center justify-between">
            <h3 class="text-sm font-semibold text-gray-900">Productos</h3>
            <span class="text-xs text-gray-500 bg-gray-100 px-2 py-0.5 rounded-full font-medium">
              {{ hoveredDoc.productos?.length || 0 }}
            </span>
          </div>
        </div>

        <div class="max-h-72 overflow-y-auto px-4 pb-4 custom-scrollbar">
          <div v-if="hoveredDoc.productos?.length" class="space-y-2 pt-2">
            <div
              v-for="(producto, index) in hoveredDoc.productos"
              :key="index"
              class="group p-3 bg-gray-50/70 rounded-lg hover:bg-gray-100/70 hover:shadow-sm transition-all duration-150"
            >
              <div class="flex items-start justify-between">
                <div class="flex-1 min-w-0 mr-3">
                  <p class="text-sm font-medium text-gray-900 truncate group-hover:text-gray-800">
                    {{ producto.nombre || 'Sin nombre' }}
                  </p>
                  <div class="flex items-center mt-1.5 space-x-2 text-xs">
                    <span class="text-gray-600 bg-white/60 px-2 py-0.5 rounded-md">
                      {{ producto.cantidad || 0 }} und
                    </span>
                    <span class="text-gray-400">•</span>
                    <span class="text-gray-600">
                      ${{ formatearMoneda(producto.precio || 0) }}
                    </span>
                  </div>
                  <p v-if="producto.descripcion" class="text-xs text-gray-500 mt-1.5 line-clamp-2">
                    {{ producto.descripcion }}
                  </p>
                </div>
                <div class="text-right flex-shrink-0">
                  <p class="text-sm font-semibold text-gray-900">
                    ${{ formatearMoneda((producto.cantidad || 0) * (producto.precio || 0)) }}
                  </p>
                </div>
              </div>
            </div>
          </div>
          <div v-else class="text-center py-8">
            <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-gray-100 flex items-center justify-center">
              <svg class="w-6 h-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2M4 13h2m0 0V9a2 2 0 012-2h2m2 2v4" />
              </svg>
            </div>
            <p class="text-sm text-gray-500">Sin productos registrados</p>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Table -->
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200/60">
        <thead class="bg-gray-50/60">
          <tr>
            <th
              class="group px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider cursor-pointer hover:bg-gray-100/60 transition-colors duration-150"
              @click="onSort('fecha')"
            >
              <div class="flex items-center space-x-1">
                <span>Fecha</span>
                <svg
                  v-if="sortBy.startsWith('fecha')"
                  :class="['w-4 h-4 transition-transform duration-200', sortBy === 'fecha-desc' ? 'rotate-180' : '']"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </div>
            </th>
            <th
              class="group px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider cursor-pointer hover:bg-gray-100/60 transition-colors duration-150"
              @click="onSort(isCompra ? 'proveedor' : 'cliente')"
            >
              <div class="flex items-center space-x-1">
                <span>{{ isCompra ? 'Proveedor' : 'Cliente' }}</span>
                <svg
                  v-if="sortBy.startsWith(isCompra ? 'proveedor' : 'cliente')"
                  :class="['w-4 h-4 transition-transform duration-200', sortBy === `${isCompra ? 'proveedor' : 'cliente'}-desc` ? 'rotate-180' : '']"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </div>
            </th>
            <th
              v-if="config.mostrarCampoExtra"
              class="group px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider cursor-pointer hover:bg-gray-100/60 transition-colors duration-150"
              @click="onSort(config.campoExtra.key)"
            >
              <div class="flex items-center space-x-1">
                <span>{{ config.campoExtra.label }}</span>
                <svg
                  v-if="sortBy.startsWith(config.campoExtra.key)"
                  :class="['w-4 h-4 transition-transform duration-200', sortBy === `${config.campoExtra.key}-desc` ? 'rotate-180' : '']"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </div>
            </th>
            <th
              class="group px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider cursor-pointer hover:bg-gray-100/60 transition-colors duration-150"
              @click="onSort('total')"
            >
              <div class="flex items-center space-x-1">
                <span>Total</span>
                <svg
                  v-if="sortBy.startsWith('total')"
                  :class="['w-4 h-4 transition-transform duration-200', sortBy === 'total-desc' ? 'rotate-180' : '']"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </div>
            </th>
            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
              Productos
            </th>
            <th
              class="group px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider cursor-pointer hover:bg-gray-100/60 transition-colors duration-150"
              @click="onSort('estado')"
            >
              <div class="flex items-center space-x-1">
                <span>Estado</span>
                <svg
                  v-if="sortBy.startsWith('estado')"
                  :class="['w-4 h-4 transition-transform duration-200', sortBy === 'estado-desc' ? 'rotate-180' : '']"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </div>
            </th>
            <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
              Acciones
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200/40">
          <template v-if="items.length > 0">
            <tr
              v-for="doc in items"
              :key="doc.id"
              class="group hover:bg-gray-50/60 transition-all duration-150 hover:shadow-sm"
            >
              <!-- Fecha -->
              <td class="px-6 py-4">
                <div class="flex flex-col space-y-0.5">
                  <div class="text-sm font-medium text-gray-900">
                    {{ formatearFecha(doc.created_at || doc.fecha) }}
                  </div>
                  <div class="text-xs text-gray-500">
                    {{ formatearHora(doc.created_at || doc.fecha) }}
                  </div>
                </div>
              </td>

              <!-- Proveedor/Cliente -->
              <td class="px-6 py-4">
                <div class="flex flex-col space-y-0.5">
                  <div class="text-sm font-medium text-gray-900 group-hover:text-gray-800">
                    {{ isCompra ? (doc.proveedor?.nombre_razon_social || 'Sin proveedor') : (doc.cliente?.nombre || 'Sin cliente') }}
                  </div>
                  <div v-if="isCompra ? doc.proveedor?.email : doc.cliente?.email" class="text-xs text-gray-500 truncate max-w-48">
                    {{ isCompra ? doc.proveedor?.email : doc.cliente?.email }}
                  </div>
                </div>
              </td>

              <!-- Campo extra (ID/Número) -->
              <td v-if="config.mostrarCampoExtra" class="px-6 py-4">
                <div class="text-sm font-mono font-medium text-gray-700 bg-gray-100/60 px-2 py-1 rounded-md inline-block">
                  #{{ doc[config.campoExtra.key] || 'N/A' }}
                </div>
              </td>

              <!-- Total -->
              <td class="px-6 py-4">
                <div class="text-sm font-semibold text-gray-900">
                  ${{ formatearMoneda(doc.total) }}
                </div>
              </td>

              <!-- Productos -->
              <td
                class="px-6 py-4 relative"
                @mouseenter="showProductTooltip(doc, $event)"
                @mouseleave="hideProductTooltip"
                @mousemove="updateTooltipPosition($event)"
              >
                <div class="flex items-center text-sm text-gray-600 cursor-help hover:text-gray-800 transition-colors duration-150">
                  <div class="w-8 h-8 bg-blue-50 rounded-lg flex items-center justify-center mr-2 group-hover:bg-blue-100 transition-colors duration-150">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"
                      />
                    </svg>
                  </div>
                  <span class="font-medium">{{ doc.productos?.length || 0 }}</span>
                  <span class="text-gray-400 ml-1">items</span>
                </div>
              </td>

              <!-- Estado -->
              <td class="px-6 py-4">
                <span
                  :class="obtenerClasesEstado(doc.estado)"
                  class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-medium transition-all duration-150 hover:shadow-sm"
                >
                  <span
                    class="w-2 h-2 rounded-full mr-2 transition-all duration-150"
                    :class="obtenerColorPuntoEstado(doc.estado)"
                  ></span>
                  {{ obtenerLabelEstado(doc.estado) }}
                </span>
              </td>

              <!-- Acciones -->
              <td class="px-6 py-4">
                <div class="flex items-center justify-end space-x-1">
                  <!-- Ver -->
                  <button
                    @click="onVerDetalles(doc)"
                    class="group/btn relative inline-flex items-center justify-center w-9 h-9 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 hover:text-blue-700 hover:shadow-sm transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:ring-offset-1"
                    title="Ver detalles"
                  >
                    <font-awesome-icon icon="eye" class="w-4 h-4 transition-transform duration-200 group-hover/btn:scale-110" />
                  </button>

                  <!-- Editar -->
                  <button
                    v-if="config.acciones.editar"
                    @click="onEditar(doc.id)"
                    class="group/btn relative inline-flex items-center justify-center w-9 h-9 rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-100 hover:text-amber-700 hover:shadow-sm transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:ring-offset-1"
                    title="Editar"
                  >
                    <font-awesome-icon icon="edit" class="w-4 h-4 transition-transform duration-200 group-hover/btn:scale-110" />
                  </button>

                  <!-- Duplicar -->
                  <button
                    v-if="config.acciones.duplicar && tipo === 'cotizaciones'"
                    @click="onDuplicar(doc)"
                    class="group/btn relative inline-flex items-center justify-center w-9 h-9 rounded-lg bg-green-50 text-green-600 hover:bg-green-100 hover:text-green-700 hover:shadow-sm transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-green-500/50 focus:ring-offset-1"
                    title="Duplicar"
                  >
                    <font-awesome-icon icon="copy" class="w-4 h-4 transition-transform duration-200 group-hover/btn:scale-110" />
                  </button>

                  <!-- Imprimir -->
                  <button
                    v-if="config.acciones.imprimir"
                    @click="onImprimir(doc)"
                    class="group/btn relative inline-flex items-center justify-center w-9 h-9 rounded-lg bg-purple-50 text-purple-600 hover:bg-purple-100 hover:text-purple-700 hover:shadow-sm transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-purple-500/50 focus:ring-offset-1"
                    title="Imprimir"
                  >
                    <font-awesome-icon icon="print" class="w-4 h-4 transition-transform duration-200 group-hover/btn:scale-110" />
                  </button>

                  <!-- Eliminar -->
                  <button
                    v-if="config.acciones.eliminar"
                    @click="onEliminar(doc.id)"
                    class="group/btn relative inline-flex items-center justify-center w-9 h-9 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 hover:text-red-700 hover:shadow-sm transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-red-500/50 focus:ring-offset-1"
                    title="Eliminar"
                  >
                    <font-awesome-icon icon="trash" class="w-4 h-4 transition-transform duration-200 group-hover/btn:scale-110" />
                  </button>
                </div>
              </td>
            </tr>
          </template>

          <!-- Empty State -->
          <tr v-else>
            <td :colspan="config.mostrarCampoExtra ? 7 : 6" class="px-6 py-16 text-center">
              <div class="flex flex-col items-center space-y-4">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center">
                  <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                  </svg>
                </div>
                <div class="space-y-1">
                  <p class="text-gray-700 font-medium">No hay {{ config.titulo.toLowerCase() }}</p>
                  <p class="text-sm text-gray-500">Los documentos aparecerán aquí cuando se creen</p>
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
import { computed, ref } from 'vue';

const props = defineProps({
  documentos: {
    type: Array,
    default: () => []
  },
  tipo: {
    type: String,
    required: true,
    validator: (value) => ['cotizaciones', 'pedidos', 'ventas', 'compras', 'ordenescompra'].includes(value)
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

// Determine if tipo is 'compras'
const isCompra = computed(() => props.tipo === 'compras');

// Tooltip State
const showTooltip = ref(false);
const hoveredDoc = ref(null);
const tooltipPosition = ref({ x: 0, y: 0 });
let tooltipTimeout = null;

// Tooltip Positioning
const tooltipStyle = computed(() => {
  const OFFSET = 20;
  const TOOLTIP_WIDTH = 320;
  const TOOLTIP_HEIGHT = 384;
  const VIEWPORT_PADDING = 16;

  const viewportWidth = window.innerWidth;
  const viewportHeight = window.innerHeight;

  let x = tooltipPosition.value.x + OFFSET;
  let y = tooltipPosition.value.y - (TOOLTIP_HEIGHT / 2);

  // Horizontal adjustment
  if (x + TOOLTIP_WIDTH > viewportWidth - VIEWPORT_PADDING) {
    x = tooltipPosition.value.x - TOOLTIP_WIDTH - OFFSET;
  }
  if (x < VIEWPORT_PADDING) {
    x = VIEWPORT_PADDING;
  }

  // Vertical adjustment
  if (y < VIEWPORT_PADDING) {
    y = VIEWPORT_PADDING;
  } else if (y + TOOLTIP_HEIGHT > viewportHeight - VIEWPORT_PADDING) {
    y = viewportHeight - TOOLTIP_HEIGHT - VIEWPORT_PADDING;
  }

  return {
    left: `${x}px`,
    top: `${y}px`,
    transform: showTooltip.value ? 'scale(1) translateY(0)' : 'scale(0.95) translateY(-10px)',
    opacity: showTooltip.value ? '1' : '0'
  };
});

// Tooltip Functions
const showProductTooltip = (doc, event) => {
  clearTimeout(tooltipTimeout);
  hoveredDoc.value = doc;
  updateTooltipPosition(event);
  tooltipTimeout = setTimeout(() => {
    showTooltip.value = true;
  }, 500);
};

const hideProductTooltip = () => {
  clearTimeout(tooltipTimeout);
  tooltipTimeout = setTimeout(() => {
    showTooltip.value = false;
    hoveredDoc.value = null;
  }, 150);
};

const updateTooltipPosition = (event) => {
  tooltipPosition.value = {
    x: event.clientX,
    y: event.clientY
  };
};

// Configuration
const configCache = new Map();

const config = computed(() => {
  if (configCache.has(props.tipo)) {
    return configCache.get(props.tipo);
  }

  const configs = {
    cotizaciones: {
      titulo: 'Cotizaciones',
      mostrarCampoExtra: true,
      campoExtra: { key: 'id', label: 'N° Cotización' },
      acciones: { editar: true, duplicar: true, imprimir: true, eliminar: true },
      estados: {
        'borrador': { label: 'Borrador', classes: 'bg-yellow-100 text-yellow-700', color: 'bg-yellow-400' },
        'pendiente': { label: 'Pendiente', classes: 'bg-gray-100 text-gray-700', color: 'bg-gray-400' },
        'aprobada': { label: 'Aprobada', classes: 'bg-blue-100 text-blue-700', color: 'bg-blue-400' },
        'rechazada': { label: 'Rechazada', classes: 'bg-red-100 text-red-700', color: 'bg-red-400' },
        'enviada': { label: 'Enviada', classes: 'bg-purple-100 text-purple-700', color: 'bg-purple-400' },
        'enviado_a_pedido': { label: 'Enviado a Pedido', classes: 'bg-orange-100 text-orange-700', color: 'bg-orange-400' },
        'convertida_pedido': { label: 'Convertida a Pedido', classes: 'bg-green-100 text-green-700', color: 'bg-green-400' },
        'enviado_pedido': { label: 'Enviado a Pedido', classes: 'bg-indigo-100 text-indigo-700', color: 'bg-indigo-400' },
        'sin_estado': { label: 'Sin Estado', classes: 'bg-gray-100 text-gray-500', color: 'bg-gray-400' }
      }
    },
    pedidos: {
      titulo: 'Pedidos',
      mostrarCampoExtra: true,
      campoExtra: { key: 'numero_pedido', label: 'N° Pedido' },
      acciones: { editar: true, duplicar: false, imprimir: true, eliminar: true },
      estados: {
        'borrador': { label: 'Borrador', classes: 'bg-gray-100 text-gray-700', color: 'bg-gray-400' },
        'pendiente': { label: 'Pendiente', classes: 'bg-yellow-100 text-yellow-700', color: 'bg-yellow-400' },
        'confirmado': { label: 'Confirmado', classes: 'bg-blue-100 text-blue-700', color: 'bg-blue-400' },
        'en_preparacion': { label: 'En Preparación', classes: 'bg-orange-100 text-orange-700', color: 'bg-orange-400' },
        'listo_entrega': { label: 'Listo para Entrega', classes: 'bg-purple-100 text-purple-700', color: 'bg-purple-400' },
        'entregado': { label: 'Entregado', classes: 'bg-green-100 text-green-700', color: 'bg-green-400' },
        'cancelado': { label: 'Cancelado', classes: 'bg-red-100 text-red-700', color: 'bg-red-400' }
      }
    },
    ventas: {
      titulo: 'Ventas',
      mostrarCampoExtra: true,
      campoExtra: { key: 'numero_venta', label: 'N° Venta' },
      acciones: { editar: true, duplicar: true, imprimir: true, eliminar: true },
      estados: {
        'borrador': { label: 'Borrador', classes: 'bg-gray-100 text-gray-700', color: 'bg-gray-400' },
        'pendiente': { label: 'Pendiente', classes: 'bg-yellow-100 text-yellow-700', color: 'bg-yellow-400' },
        'aprobado': { label: 'Aprobado', classes: 'bg-blue-100 text-blue-700', color: 'bg-blue-400' },
        'facturado': { label: 'Facturado', classes: 'bg-green-100 text-green-700', color: 'bg-green-400' },
        'pagado': { label: 'Pagado', classes: 'bg-emerald-100 text-emerald-700', color: 'bg-emerald-400' },
        'vencido': { label: 'Vencido', classes: 'bg-red-100 text-red-700', color: 'bg-red-400' },
        'anulado': { label: 'Anulado', classes: 'bg-gray-100 text-gray-500', color: 'bg-gray-400' }
      }
    },
    compras: {
      titulo: 'Compras',
      mostrarCampoExtra: true,
      campoExtra: { key: 'numero_compra', label: 'N° Compra' },
      acciones: { editar: true, duplicar: false, imprimir: true, eliminar: true },
      estados: {
        'borrador': { label: 'Borrador', classes: 'bg-gray-100 text-gray-700', color: 'bg-gray-400' },
        'pendiente': { label: 'Pendiente', classes: 'bg-yellow-100 text-yellow-700', color: 'bg-yellow-400' },
        'confirmado': { label: 'Confirmado', classes: 'bg-blue-100 text-blue-700', color: 'bg-blue-400' },
        'en_preparacion': { label: 'En Preparación', classes: 'bg-orange-100 text-orange-700', color: 'bg-orange-400' },
        'listo_entrega': { label: 'Listo para Entrega', classes: 'bg-purple-100 text-purple-700', color: 'bg-purple-400' },
        'entregado': { label: 'Entregado', classes: 'bg-green-100 text-green-700', color: 'bg-green-400' },
        'cancelado': { label: 'Cancelado', classes: 'bg-red-100 text-red-700', color: 'bg-red-400' }
      }
    }
  };

  const result = configs[props.tipo] || configs.cotizaciones;
  configCache.set(props.tipo, result);
  return result;
});

// Formatting Functions
const formatCache = new Map();

const formatearFecha = (date) => {
  if (!date) return 'Fecha no disponible';
  const cacheKey = `fecha-${date}`;
  if (formatCache.has(cacheKey)) {
    return formatCache.get(cacheKey);
  }
  try {
    const formatted = new Date(date).toLocaleDateString('es-MX', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric'
    });
    formatCache.set(cacheKey, formatted);
    return formatted;
  } catch (error) {
    console.error('Error formatting date:', date, error);
    return 'Fecha inválida';
  }
};

const formatearHora = (date) => {
  if (!date) return '';
  const cacheKey = `hora-${date}`;
  if (formatCache.has(cacheKey)) {
    return formatCache.get(cacheKey);
  }
  try {
    const formatted = new Date(date).toLocaleTimeString('es-MX', {
      hour: '2-digit',
      minute: '2-digit'
    });
    formatCache.set(cacheKey, formatted);
    return formatted;
  } catch (error) {
    console.error('Error formatting time:', date, error);
    return '';
  }
};

const formatearMoneda = (num) => {
  const value = parseFloat(num) || 0;
  const cacheKey = `moneda-${value}`;
  if (formatCache.has(cacheKey)) {
    return formatCache.get(cacheKey);
  }
  const formatted = new Intl.NumberFormat('es-MX', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(value);
  formatCache.set(cacheKey, formatted);
  return formatted;
};

// Estado Functions
const obtenerClasesEstado = (estado) => {
  return config.value.estados[estado]?.classes || 'bg-gray-100 text-gray-700';
};

const obtenerColorPuntoEstado = (estado) => {
  return config.value.estados[estado]?.color || 'bg-gray-400';
};

const obtenerLabelEstado = (estado) => {
  return config.value.estados[estado]?.label || 'Pendiente';
};

// Filtered and Sorted Items
const items = computed(() => {
  if (!Array.isArray(props.documentos)) {
    console.warn('⚠️ Documentos is not an array:', props.documentos);
    return [];
  }

  let filtered = props.documentos.slice();

  // Search Filter
  if (props.searchTerm) {
    const term = props.searchTerm.toLowerCase();
    filtered = filtered.filter(doc => {
      if (isCompra.value) {
        return (
          (doc.proveedor?.nombre_razon_social || '').toLowerCase().includes(term) ||
          doc.productos?.some(p => (p.nombre || '').toLowerCase().includes(term)) ||
          (doc.numero_compra || doc.id || '').toString().toLowerCase().includes(term)
        );
      } else {
        return (
          (doc.cliente?.nombre || '').toLowerCase().includes(term) ||
          doc.productos?.some(p => (p.nombre || '').toLowerCase().includes(term)) ||
          (doc.numero_pedido || doc.numero_factura || doc.id || '').toString().toLowerCase().includes(term)
        );
      }
    });
  }

  // Estado Filter
  if (props.filtroEstado) {
    filtered = filtered.filter(doc => doc.estado === props.filtroEstado);
  }

  // Sorting
  const [field, direction] = props.sortBy.split('-');

  return filtered.sort((a, b) => {
    let aVal, bVal;

    switch (field) {
      case 'fecha':
        aVal = new Date(a.created_at || a.fecha).getTime();
        bVal = new Date(b.created_at || b.fecha).getTime();
        break;
      case 'cliente':
        aVal = (a.cliente?.nombre || '').toLowerCase();
        bVal = (b.cliente?.nombre || '').toLowerCase();
        break;
      case 'proveedor':
        aVal = (a.proveedor?.nombre_razon_social || '').toLowerCase();
        bVal = (b.proveedor?.nombre_razon_social || '').toLowerCase();
        break;
      case 'total':
        aVal = parseFloat(a.total) || 0;
        bVal = parseFloat(b.total) || 0;
        break;
      case 'estado':
        aVal = a.estado || '';
        bVal = b.estado || '';
        break;
      default:
        aVal = a[field] || 0;
        bVal = b[field] || 0;
    }

    const comparison = aVal < bVal ? -1 : aVal > bVal ? 1 : 0;
    return direction === 'desc' ? -comparison : comparison;
  });
});

const total = computed(() => props.documentos?.length || 0);

// Event Handlers
const onVerDetalles = (doc) => emit('ver-detalles', doc);
const onEditar = (id) => emit('editar', id);
const onEliminar = (id) => emit('eliminar', id);
const onDuplicar = (doc) => emit('duplicar', doc);
const onImprimir = (doc) => emit('imprimir', doc);
const onSort = (field) => {
  const current = props.sortBy.startsWith(field) ? props.sortBy : `${field}-desc`;
  const newOrder = current === `${field}-desc` ? `${field}-asc` : `${field}-desc`;
  emit('sort', newOrder);
};
</script>

<style scoped>
.custom-scrollbar {
  scrollbar-width: thin;
  scrollbar-color: #d1d5db #f3f4f6;
}

.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
  background: #f3f4f6;
  border-radius: 3px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #d1d5db;
  border-radius: 3px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #9ca3af;
}

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  line-height: 1.4;
}

@media (prefers-contrast: high) {
  .bg-gray-50 {
    background-color: #f9fafb;
  }

  .border-gray-200 {
    border-color: #d1d5db;
  }
}

button:focus-visible {
  outline: 2px solid;
  outline-offset: 2px;
}

@media (hover: none) {
  .hover\:bg-gray-50:hover {
    background-color: transparent;
  }

  .group:hover {
    transform: none;
  }
}
</style>
