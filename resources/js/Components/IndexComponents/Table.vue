<!-- CotizacionesTable.vue con debugging -->
<template>
  <div class="bg-white rounded-lg shadow-sm overflow-hidden">
    <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
      <div class="flex items-center justify-between">
        <h2 class="text-lg font-semibold text-gray-900">Cotizaciones</h2>
        <div class="text-sm text-gray-500">
          Mostrando {{ items.length }} de {{ total }} cotizaciones
        </div>
      </div>
    </div>

    <!-- DEBUG INFO - Puedes comentar esta sección después de debuggear -->
    <!-- <div class="bg-yellow-50 p-4 border-b border-yellow-200">
      <h3 class="text-sm font-medium text-yellow-800 mb-2">🐛 Debug Info:</h3>
      <div class="text-xs text-yellow-700 space-y-1">
        <div><strong>Props cotizaciones length:</strong> {{ cotizaciones.length }}</div>
        <div><strong>Items computed length:</strong> {{ items.length }}</div>
        <div><strong>Search term:</strong> "{{ searchTerm }}"</div>
        <div><strong>Sort by:</strong> {{ sortBy }}</div>
        <div><strong>Filtro estado:</strong> "{{ filtroEstado }}"</div>
        <div><strong>Raw cotizaciones:</strong></div>
        <pre class="bg-yellow-100 p-2 rounded text-xs overflow-auto max-h-32">{{ JSON.stringify(cotizaciones, null, 2) }}</pre>
        <div><strong>Processed items:</strong></div>
        <pre class="bg-yellow-100 p-2 rounded text-xs overflow-auto max-h-32">{{ JSON.stringify(items, null, 2) }}</pre>
      </div>
    </div> -->

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
              v-for="c in items"
              :key="c.id"
              class="hover:bg-gray-50 transition-colors"
            >
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex flex-col">
                  <div class="text-sm font-medium text-gray-900">
                    {{ formatearFecha(c.created_at || c.fecha) }}
                  </div>
                  <div class="text-xs text-gray-500">
                    {{ formatearHora(c.created_at || c.fecha) }}
                  </div>
                </div>
              </td>
              <td class="px-6 py-4">
                <div class="text-sm font-medium text-gray-900">
                  {{ c.cliente?.nombre_razon_social || 'Sin cliente' }}
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">
                  ${{ formatearMoneda(c.total) }}
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center text-sm text-gray-600">
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
                  {{ c.productos?.length || 0 }} items
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span
                  :class="obtenerClasesEstado(c.estado)"
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                >
                  <span
                    class="w-1.5 h-1.5 rounded-full mr-1.5"
                    :class="obtenerColorPuntoEstado(c.estado)"
                  ></span>
                  {{ obtenerLabelEstado(c.estado) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <button
                  @click="onVerDetalles(c)"
                  class="text-blue-600 hover:text-blue-800 mr-2"
                >
                  Ver
                </button>
                <button
                  @click="onEditar(c.id)"
                  class="text-amber-600 hover:text-amber-800 mr-2"
                >
                  Editar
                </button>
                <button
                  @click="onEliminar(c.id)"
                  class="text-red-600 hover:text-red-800"
                >
                  Eliminar
                </button>
              </td>
            </tr>
          </template>
          <tr v-else>
            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
              <div class="space-y-2">
                <div>No hay Documentos</div>
                <div class="text-xs">
                  Props length: {{ cotizaciones.length }} |
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
import { computed } from 'vue';

const props = defineProps({
  cotizaciones: {
    type: Array,
    default: () => []
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

const emit = defineEmits(['ver-detalles', 'editar', 'eliminar', 'sort']);

// Debug logs (puedes comentar después)
// console.log('🔍 CotizacionesTable Debug:');
// console.log('Props cotizaciones:', props.cotizaciones);
// console.log('Props cotizaciones length:', props.cotizaciones?.length);
// console.log('Search term:', props.searchTerm);
// console.log('Sort by:', props.sortBy);
// console.log('Filtro estado:', props.filtroEstado);

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

// Estados
const obtenerClasesEstado = (estado) => ({
  'pendiente': 'bg-yellow-100 text-yellow-800',
  'enviado_pedido': 'bg-blue-100 text-blue-800',
  'enviado_venta': 'bg-indigo-100 text-indigo-800',
  'aprobado': 'bg-green-100 text-green-800',
  'rechazado': 'bg-red-100 text-red-800',
  'cancelado': 'bg-gray-100 text-gray-800'
}[estado] || 'bg-gray-100 text-gray-800');

const obtenerColorPuntoEstado = (estado) => ({
  'pendiente': 'bg-yellow-400',
  'enviado_pedido': 'bg-blue-400',
  'enviado_venta': 'bg-indigo-400',
  'aprobado': 'bg-green-400',
  'rechazado': 'bg-red-400',
  'cancelado': 'bg-gray-400'
}[estado] || 'bg-gray-400');

const obtenerLabelEstado = (estado) => ({
  'pendiente': 'Pendiente',
  'enviado_pedido': 'Enviado a Pedido',
  'enviado_venta': 'Enviado a Venta',
  'aprobado': 'Aprobada',
  'rechazado': 'Rechazada',
  'cancelado': 'Cancelada'
}[estado] || 'Pendiente');

// Computed: datos filtrados y ordenados
const items = computed(() => {
  console.log('🔄 Computing items...');
  console.log('Raw cotizaciones in computed:', props.cotizaciones);

  if (!props.cotizaciones || !Array.isArray(props.cotizaciones)) {
    console.warn('⚠️ Cotizaciones is not an array:', props.cotizaciones);
    return [];
  }

  // Procesamos los datos de manera más defensiva
  let filtered = props.cotizaciones.map((c, index) => {
    console.log(`Processing cotización ${index}:`, c);

    const processed = {
      id: c.id,
      cliente: c.cliente ? { ...c.cliente } : null,
      productos: Array.isArray(c.productos) ? [...c.productos] : [],
      total: parseFloat(c.total) || 0,
      estado: c.estado || 'pendiente',
      created_at: c.created_at,
      fecha: c.fecha
    };

    console.log(`Processed cotización ${index}:`, processed);
    return processed;
  });

  console.log('🔄 After mapping:', filtered);

  // Filtro por término de búsqueda
  if (props.searchTerm) {
    const term = props.searchTerm.toLowerCase();
    console.log('🔍 Filtering by search term:', term);

    const beforeFilter = filtered.length;
    filtered = filtered.filter(c => {
      const clienteMatch = (c.cliente?.nombre_razon_social || '').toLowerCase().includes(term);
      const productosMatch = c.productos.some(p => (p.nombre || '').toLowerCase().includes(term));
      return clienteMatch || productosMatch;
    });

    console.log(`🔍 Filter result: ${beforeFilter} -> ${filtered.length}`);
  }

  // Filtro por estado
  if (props.filtroEstado) {
    console.log('🏷️ Filtering by estado:', props.filtroEstado);

    const beforeFilter = filtered.length;
    filtered = filtered.filter(c => c.estado === props.filtroEstado);

    console.log(`🏷️ Estado filter result: ${beforeFilter} -> ${filtered.length}`);
  }

  // Ordenamiento
  console.log('🔄 Sorting by:', props.sortBy);
  const sorted = filtered.sort((a, b) => {
    const order = props.sortBy.endsWith('desc') ? -1 : 1;
    const field = props.sortBy.split('-')[0];

    if (field === 'fecha') {
      const dateA = new Date(a.created_at || a.fecha);
      const dateB = new Date(b.created_at || b.fecha);
      return order * (dateB - dateA);
    }

    if (field === 'total') {
      return order * (b.total - a.total);
    }

    if (field === 'cliente') {
      const nameA = (a.cliente?.nombre_razon_social || '').toLowerCase();
      const nameB = (b.cliente?.nombre_razon_social || '').toLowerCase();
      return order * nameA.localeCompare(nameB);
    }

    if (field === 'estado') {
      const labelA = obtenerLabelEstado(a.estado).toLowerCase();
      const labelB = obtenerLabelEstado(b.estado).toLowerCase();
      return order * labelA.localeCompare(labelB);
    }

    return 0;
  });

  console.log('✅ Final items:', sorted);
  return sorted;
});

// Total general (para mostrar en el encabezado)
const total = computed(() => props.cotizaciones?.length || 0);

// Emits
const onVerDetalles = (c) => emit('ver-detalles', c);
const onEditar = (id) => emit('editar', id);
const onEliminar = (id) => emit('eliminar', id);
const onSort = (field) => {
  const current = props.sortBy.startsWith(field) ? props.sortBy : `${field}-desc`;
  const newOrder = current === `${field}-desc` ? `${field}-asc` : `${field}-desc`;
  emit('sort', newOrder);
};
</script>
