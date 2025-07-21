<template>
  <Head title="Ventas - Dashboard" />
  <div class="ventas-dashboard min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100">
    <!-- Header con estadísticas -->
    <div class="bg-white shadow-lg border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-6 py-8">
        <div class="flex items-center justify-between mb-8">
          <div class="flex items-center space-x-4">
            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 p-3 rounded-xl">
              <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
              </svg>
            </div>
            <div>
              <h1 class="text-4xl font-bold bg-gradient-to-r from-gray-900 to-gray-700 bg-clip-text text-transparent">
                Centro de Ventas
              </h1>
              <p class="text-gray-600 mt-1">Gestiona y monitorea todas tus transacciones</p>
            </div>
          </div>

          <!-- Estadísticas rápidas -->
          <div class="flex space-x-6">
            <div class="text-center">
              <div class="text-3xl font-bold text-blue-600">{{ estadisticas.totalVentas }}</div>
              <div class="text-sm text-gray-500">Total Ventas</div>
            </div>
            <div class="text-center">
              <div class="text-3xl font-bold text-green-600">${{ estadisticas.ingresoTotal.toLocaleString() }}</div>
              <div class="text-sm text-gray-500">Ingresos</div>
            </div>
            <div class="text-center">
              <div class="text-3xl font-bold text-purple-600">{{ estadisticas.ventasHoy }}</div>
              <div class="text-sm text-gray-500">Hoy</div>
            </div>
          </div>
        </div>

        <!-- Controles avanzados -->
        <div class="flex flex-wrap gap-4 items-center justify-between">
          <div class="flex items-center space-x-4">
            <Link
              :href="route('ventas.create')"
              class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-300"
            >
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
              </svg>
              Nueva Venta
            </Link>

            <button
              @click="exportarExcel"
              class="inline-flex items-center px-4 py-3 bg-green-600 text-white font-medium rounded-xl hover:bg-green-700 transition-colors duration-200"
            >
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
              </svg>
              Exportar
            </button>
          </div>

          <div class="flex items-center space-x-4">
            <!-- Búsqueda avanzada -->
            <div class="relative">
              <input
                v-model="searchTerm"
                type="text"
                placeholder="Buscar ventas..."
                class="w-80 pl-12 pr-4 py-3 bg-white border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent shadow-sm transition-all duration-200"
              />
              <svg class="absolute left-4 top-4 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
              </svg>
            </div>

            <!-- Filtros -->
            <select
              v-model="filtroEstado"
              class="px-4 py-3 bg-white border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="">Todos los estados</option>
              <option value="completada">Completadas</option>
              <option value="pendiente">Pendientes</option>
              <option value="cancelada">Canceladas</option>
            </select>

            <!-- Vista -->
            <div class="flex bg-gray-100 rounded-xl p-1">
              <button
                @click="vistaActual = 'tabla'"
                :class="['px-4 py-2 rounded-lg font-medium transition-all duration-200', vistaActual === 'tabla' ? 'bg-white shadow-sm text-blue-600' : 'text-gray-500 hover:text-gray-700']"
              >
                Tabla
              </button>
              <button
                @click="vistaActual = 'cards'"
                :class="['px-4 py-2 rounded-lg font-medium transition-all duration-200', vistaActual === 'cards' ? 'bg-white shadow-sm text-blue-600' : 'text-gray-500 hover:text-gray-700']"
              >
                Cards
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Contenido principal -->
    <div class="max-w-7xl mx-auto px-6 py-8">
      <!-- Vista de tabla -->
      <div v-if="vistaActual === 'tabla' && ventasFiltradas.length > 0" class="bg-white rounded-2xl shadow-xl overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full">
            <thead>
              <tr class="bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                <th class="px-6 py-4 text-left text-sm font-bold text-gray-700 uppercase tracking-wider">
                  <button @click="ordenarPor('id')" class="flex items-center space-x-1 hover:text-blue-600 transition-colors">
                    <span>ID</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                    </svg>
                  </button>
                </th>
                <th class="px-6 py-4 text-left text-sm font-bold text-gray-700 uppercase tracking-wider">Cliente</th>
                <th class="px-6 py-4 text-left text-sm font-bold text-gray-700 uppercase tracking-wider">Productos/Servicios</th>
                <th class="px-6 py-4 text-left text-sm font-bold text-gray-700 uppercase tracking-wider">
                  <button @click="ordenarPor('total')" class="flex items-center space-x-1 hover:text-blue-600 transition-colors">
                    <span>Total</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                    </svg>
                  </button>
                </th>
                <th class="px-6 py-4 text-left text-sm font-bold text-gray-700 uppercase tracking-wider">Estado</th>
                <th class="px-6 py-4 text-center text-sm font-bold text-gray-700 uppercase tracking-wider">Acciones</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr
                v-for="venta in ventasFiltradas"
                :key="venta.id"
                class="hover:bg-blue-50 transition-all duration-200 group"
              >
                <td class="px-6 py-4">
                  <div class="flex items-center">
                    <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center text-white font-bold text-sm">
                      {{ venta.id }}
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <div class="flex items-center">
                    <div class="w-12 h-12 bg-gradient-to-r from-purple-400 to-pink-400 rounded-full flex items-center justify-center text-white font-bold text-lg mr-4">
                      {{ venta.cliente.nombre_razon_social.charAt(0).toUpperCase() }}
                    </div>
                    <div>
                      <div class="text-sm font-semibold text-gray-900">{{ venta.cliente.nombre_razon_social }}</div>
                      <div class="text-xs text-gray-500">Cliente #{{ venta.cliente.id }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <div class="space-y-1">
                    <template v-for="item in venta.productos" :key="item.id">
                      <div class="flex items-center space-x-2">
                        <span :class="['inline-flex items-center px-2 py-1 rounded-full text-xs font-medium',
                          item.tipo === 'producto' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800']">
                          {{ item.tipo === 'producto' ? 'Producto' : 'Servicio' }}
                        </span>
                        <span class="text-sm text-gray-700">{{ item.nombre }}</span>
                        <span class="text-xs text-gray-500">({{ item.pivot.cantidad }}x)</span>
                      </div>
                    </template>
                    <template v-for="servicio in venta.servicios" :key="servicio.id">
                      <div class="flex items-center space-x-2">
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                          Servicio
                        </span>
                        <span class="text-sm text-gray-700">{{ servicio.nombre }}</span>
                        <span class="text-xs text-gray-500">({{ servicio.pivot.cantidad }}x)</span>
                      </div>
                    </template>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <div class="text-2xl font-bold text-green-600">${{ venta.total.toLocaleString() }}</div>
                </td>
                <td class="px-6 py-4">
                  <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                    <div class="w-2 h-2 bg-green-400 rounded-full mr-2 animate-pulse"></div>
                    Completada
                  </span>
                </td>
                <td class="px-6 py-4">
                  <div class="flex items-center justify-center space-x-2">
                    <button
                      @click="verDetalles(venta)"
                      class="p-2 text-blue-600 hover:text-blue-800 hover:bg-blue-100 rounded-lg transition-all duration-200"
                      title="Ver detalles"
                    >
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                      </svg>
                    </button>
                    <button
                      @click="editarVenta(venta.id)"
                      class="p-2 text-amber-600 hover:text-amber-800 hover:bg-amber-100 rounded-lg transition-all duration-200"
                      title="Editar"
                    >
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                      </svg>
                    </button>
                    <button
                      @click="generarPDFVenta(venta)"
                      class="p-2 text-purple-600 hover:text-purple-800 hover:bg-purple-100 rounded-lg transition-all duration-200"
                      title="Generar PDF"
                    >
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                      </svg>
                    </button>
                    <button
                      @click="confirmarEliminacion(venta.id)"
                      class="p-2 text-red-600 hover:text-red-800 hover:bg-red-100 rounded-lg transition-all duration-200"
                      title="Eliminar"
                    >
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Vista de cards -->
      <div v-if="vistaActual === 'cards' && ventasFiltradas.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div
          v-for="venta in ventasFiltradas"
          :key="venta.id"
          class="bg-white rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1 overflow-hidden group"
        >
          <div class="bg-gradient-to-r from-blue-500 to-indigo-600 p-4">
            <div class="flex items-center justify-between text-white">
              <div>
                <div class="text-2xl font-bold">Venta #{{ venta.id }}</div>
                <div class="text-blue-100">{{ new Date().toLocaleDateString() }}</div>
              </div>
              <div class="w-12 h-12 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                </svg>
              </div>
            </div>
          </div>

          <div class="p-6">
            <div class="flex items-center mb-4">
              <div class="w-12 h-12 bg-gradient-to-r from-purple-400 to-pink-400 rounded-full flex items-center justify-center text-white font-bold text-lg mr-4">
                {{ venta.cliente.nombre_razon_social.charAt(0).toUpperCase() }}
              </div>
              <div>
                <div class="font-semibold text-gray-900">{{ venta.cliente.nombre_razon_social }}</div>
                <div class="text-sm text-gray-500">Cliente #{{ venta.cliente.id }}</div>
              </div>
            </div>

            <div class="mb-4">
              <div class="text-sm text-gray-600 mb-2">Productos/Servicios:</div>
              <div class="space-y-1 max-h-24 overflow-y-auto">
                <template v-for="item in venta.productos" :key="item.id">
                  <div class="flex items-center justify-between text-sm">
                    <span>{{ item.nombre }}</span>
                    <span class="text-gray-500">{{ item.pivot.cantidad }}x</span>
                  </div>
                </template>
                <template v-for="servicio in venta.servicios" :key="servicio.id">
                  <div class="flex items-center justify-between text-sm">
                    <span>{{ servicio.nombre }}</span>
                    <span class="text-gray-500">{{ servicio.pivot.cantidad }}x</span>
                  </div>
                </template>
              </div>
            </div>

            <div class="flex items-center justify-between mb-4">
              <span class="text-sm text-gray-600">Total:</span>
              <span class="text-2xl font-bold text-green-600">${{ venta.total.toLocaleString() }}</span>
            </div>

            <div class="flex space-x-2">
              <button
                @click="verDetalles(venta)"
                class="flex-1 bg-blue-50 text-blue-600 py-2 px-3 rounded-lg text-sm font-medium hover:bg-blue-100 transition-colors duration-200"
              >
                Ver
              </button>
              <button
                @click="editarVenta(venta.id)"
                class="flex-1 bg-amber-50 text-amber-600 py-2 px-3 rounded-lg text-sm font-medium hover:bg-amber-100 transition-colors duration-200"
              >
                Editar
              </button>
              <button
                @click="generarPDFVenta(venta)"
                class="flex-1 bg-purple-50 text-purple-600 py-2 px-3 rounded-lg text-sm font-medium hover:bg-purple-100 transition-colors duration-200"
              >
                PDF
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Estado vacío -->
      <div v-if="ventasFiltradas.length === 0" class="text-center py-16">
        <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
          <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
          </svg>
        </div>
        <h3 class="text-xl font-medium text-gray-900 mb-2">No hay ventas registradas</h3>
        <p class="text-gray-500 mb-8">Comienza creando tu primera venta</p>
        <Link
          :href="route('ventas.create')"
          class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-300"
        >
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
          </svg>
          Crear Primera Venta
        </Link>
      </div>
    </div>

    <!-- Modales -->
    <!-- Spinner de carga -->
    <div v-if="loading" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 backdrop-blur-sm">
      <div class="bg-white p-8 rounded-2xl shadow-2xl">
        <div class="flex items-center space-x-4">
          <div class="animate-spin rounded-full h-8 w-8 border-4 border-blue-500 border-t-transparent"></div>
          <span class="text-lg font-medium text-gray-700">Procesando...</span>
        </div>
      </div>
    </div>

    <!-- Modal de confirmación de eliminación -->
    <div v-if="showConfirmationDialog" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 backdrop-blur-sm">
      <div class="bg-white p-8 rounded-2xl shadow-2xl max-w-md w-full mx-4 transform transition-all duration-300 scale-100">
        <div class="text-center">
          <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
            </svg>
          </div>
          <h3 class="text-xl font-bold text-gray-900 mb-2">¿Confirmar eliminación?</h3>
          <p class="text-gray-600 mb-6">Esta acción no se puede deshacer. La venta será eliminada permanentemente.</p>
          <div class="flex space-x-3">
            <button
              @click="cancelarEliminacion"
              class="flex-1 px-4 py-3 bg-gray-100 text-gray-700 font-medium rounded-xl hover:bg-gray-200 transition-colors duration-200"
            >
              Cancelar
            </button>
            <button
              @click="eliminarVenta"
              class="flex-1 px-4 py-3 bg-red-600 text-white font-medium rounded-xl hover:bg-red-700 transition-colors duration-200"
            >
              Eliminar
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de detalles -->
    <div v-if="showDetailsDialog" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 backdrop-blur-sm p-4">
      <div class="bg-white rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-hidden">
        <div class="bg-gradient-to-r from-blue-500 to-indigo-600 p-6">
          <div class="flex items-center justify-between text-white">
            <h3 class="text-2xl font-bold">Detalles de la Venta #{{ selectedVenta?.id }}</h3>
            <button @click="cerrarDetalles" class="p-2 hover:bg-white hover:bg-opacity-20 rounded-lg transition-colors duration-200">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>
        </div>
        <div class="p-6 overflow-y-auto max-h-[calc(90vh-140px)]">
          <Show v-if="selectedVenta" :venta="selectedVenta" />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import { Notyf } from 'notyf';
import 'notyf/notyf.min.css';
import { generarPDF } from '@/Utils/pdfGenerator';
import Show from './Show.vue';
import AppLayout from '@/Layouts/AppLayout.vue';

// Define el layout del dashboard
defineOptions({ layout: AppLayout });

// Props
const props = defineProps({
  ventas: Array
});

// Estado reactivo
const searchTerm = ref('');
const loading = ref(false);
const showConfirmationDialog = ref(false);
const ventaIdToDelete = ref(null);
const showDetailsDialog = ref(false);
const selectedVenta = ref(null);
const filtroEstado = ref('');
const vistaActual = ref('tabla');
const ordenActual = ref({ campo: 'id', direccion: 'desc' });

// Configuración de notificaciones
const notyf = new Notyf({
  duration: 4000,
  position: { x: 'right', y: 'top' },
  types: [
    {
      type: 'success',
      background: '#10b981',
      duration: 3000,
      dismissible: true
    },
    {
      type: 'error',
      background: '#ef4444',
      duration: 4000,
      dismissible: true
    }
  ]
});

// Variable reactiva para ventas
const ventas = ref([...props.ventas]);

// Estadísticas computadas
const estadisticas = computed(() => {
  const total = ventas.value.length;
  const ingresoTotal = ventas.value.reduce((sum, venta) => sum + parseFloat(venta.total), 0);
  const hoy = new Date().toDateString();
  const ventasHoy = ventas.value.filter(venta =>
    new Date(venta.created_at || new Date()).toDateString() === hoy
  ).length;

  return {
    totalVentas: total,
    ingresoTotal,
    ventasHoy
  };
});

// Ventas filtradas y ordenadas
const ventasFiltradas = computed(() => {
  let resultado = ventas.value.filter(venta => {
    const cumpleBusqueda = !searchTerm.value ||
      venta.cliente.nombre_razon_social.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
      venta.productos.some(item => item.nombre.toLowerCase().includes(searchTerm.value.toLowerCase())) ||
      venta.servicios.some(servicio => servicio.nombre.toLowerCase().includes(searchTerm.value.toLowerCase())) ||
      venta.id.toString().includes(searchTerm.value);

    const cumpleEstado = !filtroEstado.value || venta.estado === filtroEstado.value;

    return cumpleBusqueda && cumpleEstado;
  });

  // Aplicar ordenamiento
  if (ordenActual.value.campo) {
    resultado.sort((a, b) => {
      let valorA = a[ordenActual.value.campo];
      let valorB = b[ordenActual.value.campo];

      if (ordenActual.value.campo === 'total') {
        valorA = parseFloat(valorA);
        valorB = parseFloat(valorB);
      }

      if (ordenActual.value.direccion === 'asc') {
        return valorA > valorB ? 1 : -1;
      } else {
        return valorA < valorB ? 1 : -1;
      }
    });
  }

  return resultado;
});

// Funciones
const ordenarPor = (campo) => {
  if (ordenActual.value.campo === campo) {
    ordenActual.value.direccion = ordenActual.value.direccion === 'asc' ? 'desc' : 'asc';
  } else {
    ordenActual.value.campo = campo;
    ordenActual.value.direccion = 'desc';
  }
};

const editarVenta = (id) => {
  loading.value = true;
  router.get(`/ventas/${id}/edit`, {}, {
    onFinish: () => loading.value = false
  });
};

const confirmarEliminacion = (id) => {
  ventaIdToDelete.value = id;
  showConfirmationDialog.value = true;
};

const cancelarEliminacion = () => {
  ventaIdToDelete.value = null;
  showConfirmationDialog.value = false;
};

const eliminarVenta = async () => {
  if (ventaIdToDelete.value) {
    loading.value = true;
    try {
      await router.delete(`/ventas/${ventaIdToDelete.value}`, {
        onSuccess: () => {
          notyf.success('✅ Venta eliminada exitosamente');
          ventas.value = ventas.value.filter(venta => venta.id !== ventaIdToDelete.value);
          showConfirmationDialog.value = false;
          ventaIdToDelete.value = null;
        },
        onError: (errors) => {
          console.error('Error al eliminar:', errors);
          notyf.error('❌ Error al eliminar la venta');
        },
        onFinish: () => loading.value = false
      });
    } catch (error) {
      console.error('Error inesperado:', error);
      notyf.error('❌ Ocurrió un error inesperado');
      loading.value = false;
    }
  }
};

const verDetalles = (venta) => {
  selectedVenta.value = venta;
  showDetailsDialog.value = true;
};

const cerrarDetalles = () => {
  selectedVenta.value = null;
  showDetailsDialog.value = false;
};

const generarPDFVenta = (venta) => {
  try {
    loading.value = true;
    generarPDF('Factura de Venta', venta);
    notyf.success('📄 PDF generado exitosamente');
  } catch (error) {
    console.error('Error al generar PDF:', error);
    notyf.error('❌ Error al generar el PDF');
  } finally {
    loading.value = false;
  }
};

const exportarExcel = () => {
  try {
    loading.value = true;

    // Preparar datos para Excel
    const datosExcel = ventasFiltradas.value.map(venta => ({
      'ID': venta.id,
      'Cliente': venta.cliente.nombre_razon_social,
      'Total': venta.total,
      'Estado': venta.estado || 'Completada',
      'Fecha': new Date(venta.created_at || new Date()).toLocaleDateString(),
      'Productos': venta.productos.map(p => `${p.nombre} (${p.pivot.cantidad})`).join('; '),
      'Servicios': venta.servicios.map(s => `${s.nombre} (${s.pivot.cantidad})`).join('; ')
    }));

    // Crear CSV
    const csvContent = [
      Object.keys(datosExcel[0]).join(','),
      ...datosExcel.map(row => Object.values(row).map(val => `"${val}"`).join(','))
    ].join('\n');

    // Descargar archivo
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    link.setAttribute('href', url);
    link.setAttribute('download', `ventas_${new Date().toISOString().split('T')[0]}.csv`);
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);

    notyf.success('📊 Datos exportados exitosamente');
  } catch (error) {
    console.error('Error al exportar:', error);
    notyf.error('❌ Error al exportar los datos');
  } finally {
    loading.value = false;
  }
};

// Efectos de montaje
onMounted(() => {
  // Animaciones de entrada
  const elements = document.querySelectorAll('.ventas-dashboard > *');
  elements.forEach((el, index) => {
    el.style.opacity = '0';
    el.style.transform = 'translateY(20px)';
    setTimeout(() => {
      el.style.transition = 'all 0.6s ease';
      el.style.opacity = '1';
      el.style.transform = 'translateY(0)';
    }, index * 100);
  });

  // Mostrar notificación de bienvenida
  setTimeout(() => {
    notyf.success('🎉 ¡Bienvenido al Centro de Ventas!');
  }, 1000);
});

// Atajos de teclado
onMounted(() => {
  const handleKeyPress = (event) => {
    // Ctrl/Cmd + N para nueva venta
    if ((event.ctrlKey || event.metaKey) && event.key === 'n') {
      event.preventDefault();
      router.get('/ventas/create');
    }

    // Escape para cerrar modales
    if (event.key === 'Escape') {
      if (showDetailsDialog.value) cerrarDetalles();
      if (showConfirmationDialog.value) cancelarEliminacion();
    }

    // Ctrl/Cmd + F para buscar
    if ((event.ctrlKey || event.metaKey) && event.key === 'f') {
      event.preventDefault();
      document.querySelector('input[placeholder="Buscar ventas..."]')?.focus();
    }
  };

  document.addEventListener('keydown', handleKeyPress);

  return () => {
    document.removeEventListener('keydown', handleKeyPress);
  };
});
</script>
