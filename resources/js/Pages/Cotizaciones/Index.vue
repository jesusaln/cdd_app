<template>
  <Head title="Cotizaciones" />
  <div class="cotizaciones-index min-h-screen bg-gray-50 p-4">
    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900 mb-2">Gestión de Cotizaciones</h1>
      <p class="text-gray-600">Administra y gestiona todas tus cotizaciones de manera eficiente</p>
    </div>

    <!-- Controles superiores -->
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
      <div class="flex flex-col lg:flex-row gap-4 items-start lg:items-center justify-between">
        <!-- Botón crear y estadísticas -->
        <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center">
          <Link
            :href="route('cotizaciones.create')"
            class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-medium rounded-lg hover:from-blue-700 hover:to-blue-800 transform hover:scale-105 transition-all duration-200 shadow-lg"
          >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Nueva Cotización
          </Link>

          <div class="flex items-center gap-4 text-sm text-gray-600">
            <span class="flex items-center">
              <span class="w-2 h-2 bg-blue-500 rounded-full mr-2"></span>
              Total: {{ cotizacionesOriginales.length }}
            </span>
            <span class="flex items-center">
              <span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span>
              Filtradas: {{ cotizacionesFiltradas.length }}
            </span>
          </div>
        </div>

        <!-- Búsqueda y filtros -->
        <div class="flex flex-col sm:flex-row gap-3 w-full lg:w-auto">
          <div class="relative">
            <svg class="w-5 h-5 absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <input
              id="searchTerm"
              v-model="searchTerm"
              type="text"
              placeholder="Buscar por cliente, ID o producto..."
              class="pl-10 pr-10 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 w-full sm:w-80"
            />
            <button
              v-if="searchTerm"
              @click="limpiarBusqueda"
              class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <select v-model="sortBy" class="px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
  <option value="fecha-desc">Más recientes</option>
  <option value="fecha-asc">Más antiguas</option>
  <option value="total-desc">Mayor valor</option>
  <option value="total-asc">Menor valor</option>
  <option value="cliente">Por cliente</option>
  <option value="id-desc">ID descendente</option>
  <option value="id-asc">ID ascendente</option>
</select>


          <!-- Filtro por estado (opcional) -->
          <select
            v-model="filtroEstado"
            class="px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          >
            <option value="">Todos los estados</option>
            <option value="pendiente">Pendientes</option>
            <option value="aprobado">Aprobadas</option>
            <option value="rechazado">Rechazadas</option>
          </select>
        </div>
      </div>

      <!-- Indicadores de filtros activos -->
      <div v-if="hayFiltrosActivos" class="mt-4 flex flex-wrap gap-2">
        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
          Filtros activos
        </span>
        <button
          v-if="searchTerm"
          @click="limpiarBusqueda"
          class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 hover:bg-gray-200"
        >
          Búsqueda: "{{ searchTerm }}"
          <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
        <button
          v-if="filtroEstado"
          @click="filtroEstado = ''"
          class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 hover:bg-gray-200"
        >
          Estado: {{ filtroEstado }}
          <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
        <button
          @click="limpiarTodosFiltros"
          class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 hover:bg-red-200"
        >
          Limpiar todos
          <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
    </div>

    <!-- Tabla de cotizaciones -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
      <div v-if="cotizacionesFiltradas.length" class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                <button @click="toggleSort('fecha')" class="flex items-center hover:text-gray-700">
                  Fecha
                  <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                  </svg>
                </button>
              </th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">ID</th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Cliente</th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Productos/Servicios</th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                <button @click="toggleSort('total')" class="flex items-center hover:text-gray-700">
                  Total
                  <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                  </svg>
                </button>
              </th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Estado</th>
              <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Acciones</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr
              v-for="cotizacion in cotizacionesFiltradas"
              :key="cotizacion.id"
              class="hover:bg-gray-50 transition-colors duration-150"
            >
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">{{ formatearFecha(cotizacion.fecha) }}</div>
                <div class="text-sm text-gray-500">{{ formatearHora(cotizacion.fecha) }}</div>
              </td>

              <td class="px-6 py-4 whitespace-nowrap">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                  #{{ cotizacion.id }}
                </span>
              </td>

              <td class="px-6 py-4">
                <div class="flex items-center">
                  <div class="flex-shrink-0 h-10 w-10">
                    <div class="h-10 w-10 rounded-full bg-gradient-to-r from-blue-400 to-blue-600 flex items-center justify-center">
                      <span class="text-sm font-medium text-white">
                        {{ cotizacion.cliente.nombre_razon_social.charAt(0).toUpperCase() }}
                      </span>
                    </div>
                  </div>
                  <div class="ml-4">
                    <div class="text-sm font-medium text-gray-900">{{ cotizacion.cliente.nombre_razon_social }}</div>
                    <div class="text-sm text-gray-500" v-if="cotizacion.cliente.email">{{ cotizacion.cliente.email }}</div>
                  </div>
                </div>
              </td>

              <td class="px-6 py-4">
                <div class="max-w-xs">
                  <div class="text-sm text-gray-900 font-medium mb-1">
                    {{ cotizacion.productos.length }} elemento(s)
                  </div>
                  <div class="space-y-1">
                    <div
                      v-for="(item, index) in cotizacion.productos.slice(0, 2)"
                      :key="item.id"
                      class="flex items-center text-xs"
                    >
                      <span
                        :class="item.tipo === 'producto' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'"
                        class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium mr-2"
                      >
                        {{ item.tipo === 'producto' ? 'P' : 'S' }}
                      </span>
                      <span class="text-gray-600 truncate">{{ item.nombre }}</span>
                    </div>
                    <div v-if="cotizacion.productos.length > 2" class="text-xs text-gray-500">
                      +{{ cotizacion.productos.length - 2 }} más...
                    </div>
                  </div>
                </div>
              </td>

              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-bold text-gray-900">${{ formatearMoneda(cotizacion.total) }}</div>
                <div class="text-xs text-gray-500">{{ cotizacion.productos.length }} items</div>
              </td>

              <td class="px-6 py-4 whitespace-nowrap">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                  Pendiente
                </span>
              </td>

              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <div class="flex items-center justify-end space-x-2">
                  <button
                    @click="verDetalles(cotizacion)"
                    class="text-blue-600 hover:text-blue-900 transition-colors p-1 rounded-full hover:bg-blue-50"
                    title="Ver detalles"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                  </button>

                  <button
                    @click="editarCotizacion(cotizacion.id)"
                    class="text-indigo-600 hover:text-indigo-900 transition-colors p-1 rounded-full hover:bg-indigo-50"
                    title="Editar"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                  </button>

                  <button
                    @click="generarPDFVenta(cotizacion)"
                    class="text-purple-600 hover:text-purple-900 transition-colors p-1 rounded-full hover:bg-purple-50"
                    title="Generar PDF"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                  </button>

                  <button
                    @click="confirmarEliminacion(cotizacion.id)"
                    class="text-red-600 hover:text-red-900 transition-colors p-1 rounded-full hover:bg-red-50"
                    title="Eliminar"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Estado vacío -->
      <div v-else class="text-center py-12">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">No hay cotizaciones</h3>
        <p class="mt-1 text-sm text-gray-500">
          {{ hayFiltrosActivos ? 'No se encontraron cotizaciones con los criterios seleccionados.' : 'Comienza creando tu primera cotización.' }}
        </p>
        <div class="mt-6">
          <button
            v-if="hayFiltrosActivos"
            @click="limpiarTodosFiltros"
            class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 mr-3"
          >
            Limpiar filtros
          </button>
          <Link
            v-if="!hayFiltrosActivos"
            :href="route('cotizaciones.create')"
            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Nueva Cotización
          </Link>
        </div>
      </div>
    </div>

    <!-- Spinner de carga -->
    <Transition name="fade">
      <div v-if="loading" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 flex items-center space-x-4">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
          <span class="text-gray-700 font-medium">Procesando...</span>
        </div>
      </div>
    </Transition>

    <!-- Modal de confirmación de eliminación -->
    <Transition name="modal">
      <div v-if="showConfirmationDialog" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
          <div class="p-6">
            <div class="flex items-center justify-center w-12 h-12 mx-auto bg-red-100 rounded-full mb-4">
              <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
              </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 text-center mb-2">Confirmar eliminación</h3>
            <p class="text-sm text-gray-500 text-center mb-6">
              ¿Estás seguro de que deseas eliminar esta cotización? Esta acción no se puede deshacer.
            </p>
            <div class="flex space-x-3">
              <button
                @click="cancelarEliminacion"
                class="flex-1 px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
              >
                Cancelar
              </button>
              <button
                @click="eliminarCotizacion"
                class="flex-1 px-4 py-2 bg-red-600 text-white rounded-md text-sm font-medium hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
              >
                Eliminar
              </button>
            </div>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Modal de detalles -->
    <Transition name="modal">
      <div v-if="showDetailsDialog" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
          <div class="p-6">
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-lg font-medium text-gray-900">Detalles de la Cotización</h3>
              <button
                @click="cerrarDetalles"
                class="text-gray-400 hover:text-gray-600 transition-colors"
              >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>

            <Show :cotizacion="selectedCotizacion" @convertir-a-pedido="handleConvertirAPedido" />

            <div class="flex justify-end mt-6 space-x-3">
              <button
                @click="cerrarDetalles"
                class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
              >
                Cerrar
              </button>
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import { Notyf } from 'notyf';
import 'notyf/notyf.min.css';
import { generarPDF } from '@/Utils/pdfGenerator';
import Show from './Show.vue';
import AppLayout from '@/Layouts/AppLayout.vue';

defineOptions({ layout: AppLayout });

const props = defineProps({
  cotizaciones: {
    type: Array,
    default: () => []
  }
});

// Estado reactivo
const searchTerm = ref('');
const sortBy = ref('id-desc');
const filtroEstado = ref('');
const loading = ref(false);
const showConfirmationDialog = ref(false);
const cotizacionIdToDelete = ref(null);
const showDetailsDialog = ref(false);
const selectedCotizacion = ref(null);

// Configuración de notificaciones
const notyf = new Notyf({
  duration: 4000,
  position: { x: 'right', y: 'top' },
  types: [
    {
      type: 'success',
      background: '#10b981',
      icon: false
    },
    {
      type: 'error',
      background: '#ef4444',
      icon: false
    }
  ]
});

// Data reactiva - CORREGIDO: Mantener referencia a los datos originales
const cotizacionesOriginales = ref([...props.cotizaciones]);

// Actualizar datos cuando cambien las props (útil para actualizaciones dinámicas)
watch(() => props.cotizaciones, (newCotizaciones) => {
  cotizacionesOriginales.value = [...newCotizaciones];
}, { deep: true });

const cotizacionesFiltradas = computed(() => {
  let filtered = [...cotizacionesOriginales.value];

  // Aplicar filtro de búsqueda
  if (searchTerm.value.trim()) {
    const searchLower = searchTerm.value.trim().toLowerCase();
    filtered = filtered.filter(cotizacion => {
      const nombreCliente = cotizacion.cliente?.nombre_razon_social?.toLowerCase() || '';
      const emailCliente = cotizacion.cliente?.email?.toLowerCase() || '';
      const idMatch = cotizacion.id.toString().includes(searchLower);
      const productosMatch = cotizacion.productos?.some(item =>
        item.nombre?.toLowerCase().includes(searchLower) ||
        item.tipo?.toLowerCase().includes(searchLower)
      ) || false;

      return nombreCliente.includes(searchLower) ||
             emailCliente.includes(searchLower) ||
             idMatch ||
             productosMatch;
    });
  }

  // Aplicar filtro de estado
  if (filtroEstado.value) {
    filtered = filtered.filter(cotizacion => {
      const estado = cotizacion.estado || 'pendiente';
      return estado.toLowerCase() === filtroEstado.value.toLowerCase();
    });
  }

  // Aplicar ordenamiento
  return filtered.sort((a, b) => {
    switch (sortBy.value) {
      case 'fecha-desc':
        return new Date(b.fecha || b.created_at) - new Date(a.fecha || a.created_at);
      case 'fecha-asc':
        return new Date(a.fecha || a.created_at) - new Date(b.fecha || b.created_at);
      case 'total-desc':
        return parseFloat(b.total || 0) - parseFloat(a.total || 0);
      case 'total-asc':
        return parseFloat(a.total || 0) - parseFloat(b.total || 0);
      case 'cliente':
        const nombreA = a.cliente?.nombre_razon_social || '';
        const nombreB = b.cliente?.nombre_razon_social || '';
        return nombreA.localeCompare(nombreB);
      case 'id-desc': // Asegúrate de que este caso esté incluido
        return b.id - a.id;
      case 'id-asc':
        return a.id - b.id;
      default:
        return b.id - a.id; // Ordenar por ID descendente por defecto
    }
  });
});


// Computed para verificar si hay filtros activos
const hayFiltrosActivos = computed(() => {
  return searchTerm.value.trim() !== '' || filtroEstado.value !== '';
});

// Métodos de formateo
const formatearFecha = (fechaHora) => {
  const fecha = new Date(fechaHora);
  return fecha.toLocaleDateString('es-MX', {
    day: '2-digit',
    month: 'short',
    year: 'numeric'
  });
};

const formatearHora = (fechaHora) => {
  const fecha = new Date(fechaHora);
  return fecha.toLocaleTimeString('es-MX', {
    hour: '2-digit',
    minute: '2-digit'
  });
};

const formatearMoneda = (amount) => {
  return new Intl.NumberFormat('es-MX', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(amount);
};

// Métodos de navegación
const editarCotizacion = (id) => {
  router.get(`/cotizaciones/${id}/edit`);
};

// Métodos de eliminación
const confirmarEliminacion = (id) => {
  cotizacionIdToDelete.value = id;
  showConfirmationDialog.value = true;
};

const cancelarEliminacion = () => {
  cotizacionIdToDelete.value = null;
  showConfirmationDialog.value = false;
};

const eliminarCotizacion = async () => {
  if (!cotizacionIdToDelete.value) return;

  loading.value = true;

  try {
    await router.delete(`/cotizaciones/${cotizacionIdToDelete.value}`, {
      onSuccess: () => {
        notyf.success('Cotización eliminada exitosamente');
        cotizaciones.value = cotizaciones.value.filter(c => c.id !== cotizacionIdToDelete.value);
        showConfirmationDialog.value = false;
        cotizacionIdToDelete.value = null;
      },
      onError: (errors) => {
        console.error('Error al eliminar:', errors);
        notyf.error('Error al eliminar la cotización');
      }
    });
  } catch (error) {
    console.error('Error inesperado:', error);
    notyf.error('Ocurrió un error inesperado');
  } finally {
    loading.value = false;
  }
};

// Métodos de detalles
const verDetalles = (cotizacion) => {
  selectedCotizacion.value = cotizacion;
  showDetailsDialog.value = true;
};

const cerrarDetalles = () => {
  selectedCotizacion.value = null;
  showDetailsDialog.value = false;
};

// Conversión a pedido
const handleConvertirAPedido = async (cotizacionData) => {
  loading.value = true;

  try {
    await router.post(`/cotizaciones/${cotizacionData.id}/convertir-a-pedido`, {
      onSuccess: () => {
        notyf.success('Cotización convertida a pedido exitosamente');
        if (confirm('¿Deseas ir al índice de pedidos?')) {
          router.get('/pedidos');
        }
        cerrarDetalles();
      },
      onError: (errors) => {
        console.error('Error al convertir:', errors);
        notyf.error('Error al convertir la cotización');
      }
    });
  } catch (error) {
    console.error('Error inesperado:', error);
    notyf.error('Ocurrió un error inesperado');
  } finally {
    loading.value = false;
  }
};

// Generación de PDF
const generarPDFVenta = async (cotizacion) => {
  try {
    loading.value = true;
    await generarPDF('Cotización', cotizacion);
    notyf.success('PDF generado exitosamente');
  } catch (error) {
    console.error('Error al generar PDF:', error);
    notyf.error('Error al generar el PDF');
  } finally {
    loading.value = false;
  }
};

// Métodos de ordenamiento
const toggleSort = (field) => {
  if (sortBy.value === `${field}-desc`) {
    sortBy.value = `${field}-asc`;
  } else {
    sortBy.value = `${field}-desc`;
  }
};
</script>

<style scoped>
/* Transiciones */
.fade-enter-active, .fade-leave-active {
 transition: opacity 0.3s ease;
}

.fade-enter-from, .fade-leave-to {
 opacity: 0;
}

.modal-enter-active, .modal-leave-active {
 transition: all 0.3s ease;
}

.modal-enter-from, .modal-leave-to {
 opacity: 0;
 transform: scale(0.9);
}

/* Estilos para scroll personalizado */
::-webkit-scrollbar {
 width: 6px;
}

::-webkit-scrollbar-track {
 background: #f1f1f1;
 border-radius: 3px;
}

::-webkit-scrollbar-thumb {
 background: #c1c1c1;
 border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
 background: #a8a8a8;
}

/* Animaciones adicionales */
@keyframes pulse {
 0%, 100% {
   opacity: 1;
 }
 50% {
   opacity: 0.5;
 }
}

.pulse {
 animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

/* Hover effects mejorados */
.table-row-hover:hover {
 background-color: #f8fafc;
 transform: translateY(-1px);
 box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
 transition: all 0.2s ease;
}

/* Botones con efectos mejorados */
.btn-primary {
 background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
 transition: all 0.3s ease;
}

.btn-primary:hover {
 background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
 transform: translateY(-2px);
 box-shadow: 0 8px 25px rgba(59, 130, 246, 0.3);
}

/* Efectos de loading */
.loading-shimmer {
 background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
 background-size: 200% 100%;
 animation: shimmer 1.5s infinite;
}

@keyframes shimmer {
 0% {
   background-position: -200% 0;
 }
 100% {
   background-position: 200% 0;
 }
}

/* Efectos de focus mejorados */
.focus-ring:focus {
 outline: 2px solid #3b82f6;
 outline-offset: 2px;
 border-color: #3b82f6;
}

/* Animación de entrada para elementos */
.slide-up-enter-active {
 transition: all 0.4s ease;
}

.slide-up-enter-from {
 opacity: 0;
 transform: translateY(20px);
}

/* Efectos para badges */
.badge-animate {
 transition: all 0.2s ease;
}

.badge-animate:hover {
 transform: scale(1.05);
}
</style>
