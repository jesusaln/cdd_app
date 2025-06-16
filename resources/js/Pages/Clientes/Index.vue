<template>
  <Head :title="titulo" />
  <div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Header Section -->
      <div class="mb-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ titulo }}</h1>
            <p class="text-gray-600">Gestiona la información de tus clientes</p>
          </div>
          <div class="mt-4 sm:mt-0">
            <Link
              :href="route('clientes.create')"
              class="inline-flex items-center px-4 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 shadow-sm"
            >
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
              Nuevo Cliente
            </Link>
          </div>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
          <div class="flex items-center">
            <div class="p-2 bg-blue-100 rounded-lg">
              <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm text-gray-500">Total Clientes</p>
              <p class="text-2xl font-bold text-gray-900">{{ totalClientes }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
          <div class="flex items-center">
            <div class="p-2 bg-green-100 rounded-lg">
              <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm text-gray-500">Clientes Activos</p>
              <p class="text-2xl font-bold text-gray-900">{{ clientesActivos }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
          <div class="flex items-center">
            <div class="p-2 bg-purple-100 rounded-lg">
              <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm text-gray-500">Resultados</p>
              <p class="text-2xl font-bold text-gray-900">{{ clientesFiltrados.length }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Filters and Search Section -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <!-- Search Input -->
          <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-2">Buscar Cliente</label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
              </div>
              <input
                v-model="searchTerm"
                type="text"
                placeholder="Buscar por nombre, RFC o email..."
                class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
              />
              <div v-if="searchTerm" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                <button @click="searchTerm = ''" class="text-gray-400 hover:text-gray-600">
                  <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>
            </div>
          </div>

          <!-- Regimen Filter -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Régimen Fiscal</label>
            <select
              v-model="filtroRegimen"
              class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="">Todos</option>
              <option v-for="regimen in regimenesFiscales" :key="regimen" :value="regimen">
                {{ regimen }}
              </option>
            </select>
          </div>

          <!-- Sort Options -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Ordenar por</label>
            <select
              v-model="ordenarPor"
              class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="nombre_asc">Nombre A-Z</option>
              <option value="nombre_desc">Nombre Z-A</option>
              <option value="fecha_asc">Más Antiguos</option>
              <option value="fecha_desc">Más Recientes</option>
            </select>
          </div>
        </div>

        <!-- Active Filters -->
        <div v-if="filtrosActivos.length > 0" class="mt-4 flex flex-wrap gap-2">
          <span class="text-sm text-gray-500">Filtros activos:</span>
          <span
            v-for="filtro in filtrosActivos"
            :key="filtro.key"
            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
          >
            {{ filtro.label }}
            <button @click="limpiarFiltro(filtro.key)" class="ml-1.5 h-4 w-4 rounded-full hover:bg-blue-200">
              <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </span>
          <button @click="limpiarTodosFiltros" class="text-xs text-blue-600 hover:text-blue-800 font-medium">
            Limpiar todos
          </button>
        </div>
      </div>

      <!-- Table Section -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div v-if="clientesFiltrados.length > 0">
          <!-- Desktop Table -->
          <div class="hidden md:block overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th v-for="header in headers" :key="header.key"
                      class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100"
                      @click="cambiarOrden(header.key)">
                    <div class="flex items-center space-x-1">
                      <span>{{ header.label }}</span>
                      <svg v-if="ordenActual === header.key" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              :d="direccionOrden === 'asc' ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7'" />
                      </svg>
                    </div>
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Acciones
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="cliente in clientesPaginados" :key="cliente.id"
                    class="hover:bg-gray-50 transition-colors duration-150">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <div class="flex-shrink-0 w-10 h-10">
                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                          <span class="text-sm font-medium text-blue-600">
                            {{ cliente.nombre_razon_social.charAt(0).toUpperCase() }}
                          </span>
                        </div>
                      </div>
                      <div class="ml-4">
                        <div class="text-sm font-medium text-gray-900">{{ cliente.nombre_razon_social }}</div>
                        <div class="text-sm text-gray-500">{{ cliente.email }}</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                      {{ cliente.rfc }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
    {{ cliente.regimen_fiscal_nombre }}
</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
    {{ cliente.uso_cfdi_nombre }}
</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ cliente.telefono }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <div class="max-w-xs truncate">
                      {{ formatearDireccion(cliente) }}
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <div class="flex items-center space-x-2">
                      <button @click="openModal(cliente)"
                              class="text-blue-600 hover:text-blue-900 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                      </button>
                      <Link :href="route('clientes.edit', cliente.id)"
                            class="text-green-600 hover:text-green-900 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                      </Link>
                      <button @click="confirmarEliminacion(cliente)"
                              class="text-red-600 hover:text-red-900 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Mobile Cards -->
          <div class="md:hidden space-y-4 p-4">
            <div v-for="cliente in clientesPaginados" :key="cliente.id"
                 class="bg-gray-50 rounded-lg p-4 border border-gray-200">
              <div class="flex items-start justify-between mb-3">
                <div class="flex items-center">
                  <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                    <span class="text-sm font-medium text-blue-600">
                      {{ cliente.nombre_razon_social.charAt(0).toUpperCase() }}
                    </span>
                  </div>
                  <div>
                    <h3 class="text-sm font-medium text-gray-900">{{ cliente.nombre_razon_social }}</h3>
                    <p class="text-sm text-gray-500">{{ cliente.rfc }}</p>
                  </div>
                </div>
                <div class="flex space-x-2">
                  <button @click="openModal(cliente)" class="text-blue-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                  </button>
                  <Link :href="route('clientes.edit', cliente.id)" class="text-green-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                  </Link>
                  <button @click="confirmarEliminacion(cliente)" class="text-red-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </button>
                </div>
              </div>
              <div class="space-y-2 text-sm">
                <div><span class="font-medium">Email:</span> {{ cliente.email }}</div>
                <div><span class="font-medium">Teléfono:</span> {{ cliente.telefono }}</div>
                <div><span class="font-medium">Régimen:</span> {{ cliente.regimen_fiscal }}</div>
              </div>
            </div>
          </div>

          <!-- Pagination -->
          <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
            <div class="flex items-center justify-between">
              <div class="flex-1 flex justify-between sm:hidden">
                <button @click="paginaAnterior" :disabled="paginaActual === 1"
                        class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50">
                  Anterior
                </button>
                <button @click="paginaSiguiente" :disabled="paginaActual === totalPaginas"
                        class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50">
                  Siguiente
                </button>
              </div>
              <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <div>
                  <p class="text-sm text-gray-700">
                    Mostrando
                    <span class="font-medium">{{ (paginaActual - 1) * elementosPorPagina + 1 }}</span>
                    a
                    <span class="font-medium">{{ Math.min(paginaActual * elementosPorPagina, clientesFiltrados.length) }}</span>
                    de
                    <span class="font-medium">{{ clientesFiltrados.length }}</span>
                    resultados
                  </p>
                </div>
                <div>
                  <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                    <button @click="paginaAnterior" :disabled="paginaActual === 1"
                            class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50">
                      <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                      </svg>
                    </button>
                    <button
                      v-for="pagina in paginas"
                      :key="pagina"
                      @click="irAPagina(pagina)"
                      :class="[
                        'relative inline-flex items-center px-4 py-2 border text-sm font-medium',
                        pagina === paginaActual
                          ? 'z-10 bg-blue-50 border-blue-500 text-blue-600'
                          : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'
                      ]"
                    >
                      {{ pagina }}
                    </button>
                    <button @click="paginaSiguiente" :disabled="paginaActual === totalPaginas"
                            class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50">
                      <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                      </svg>
                    </button>
                  </nav>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-else class="text-center py-12">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
          </svg>
          <h3 class="mt-2 text-sm font-medium text-gray-900">
            {{ searchTerm || filtroRegimen ? 'No se encontraron clientes' : 'No hay clientes registrados' }}
          </h3>
          <p class="mt-1 text-sm text-gray-500">
            {{ searchTerm || filtroRegimen ? 'Intenta ajustar los filtros de búsqueda' : 'Comienza agregando tu primer cliente' }}
          </p>
          <div v-if="!searchTerm && !filtroRegimen" class="mt-6">
            <Link :href="route('clientes.create')"
                  class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
              Crear primer cliente
            </Link>
          </div>
        </div>
      </div>
    </div>

    <!-- Loading Spinner -->
    <LoadingSpinner :loading="loading" />

    <!-- Modals -->
    <ClienteModal :cliente="clienteSeleccionado" :isOpen="isModalOpen" @close="closeModal" />

    <ConfirmModal
      :isOpen="isConfirmOpen"
      title="Confirmar eliminación"
      message="¿Estás seguro de que deseas eliminar este cliente? Esta acción no se puede deshacer."
      :itemName="clienteAEliminar?.nombre_razon_social"
      @cancel="isConfirmOpen = false"
      @confirm="eliminarClienteConfirmado"
    />
  </div>
</template>

<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed, watch, onMounted } from 'vue';
import { Notyf } from 'notyf';
import 'notyf/notyf.min.css';
import ClienteModal from '@/Components/ClienteModal.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import LoadingSpinner from '@/Components/LoadingSpinner.vue';
import AppLayout from '@/Layouts/AppLayout.vue';

defineOptions({ layout: AppLayout });

const props = defineProps({
  titulo: String,
  clientes: Object,
});

const page = usePage();

// Set document title
document.title = props.titulo;

// Table headers configuration
const headers = [
  { key: 'nombre_razon_social', label: 'Cliente' },
  { key: 'rfc', label: 'RFC' },
  { key: 'regimen_fiscal', label: 'Régimen Fiscal' },
  { key: 'uso_cfdi', label: 'Uso CFDI' },
  { key: 'telefono', label: 'Teléfono' },
  { key: 'direccion', label: 'Dirección' },
];

// Reactive variables
const loading = ref(false);
const searchTerm = ref('');
const filtroRegimen = ref('');
const ordenarPor = ref('nombre_asc');
const clienteSeleccionado = ref(null);
const isModalOpen = ref(false);
const isConfirmOpen = ref(false);
const clienteAEliminar = ref(null);
const ordenActual = ref('nombre_razon_social');
const direccionOrden = ref('asc');

// Pagination
const paginaActual = ref(1);
const elementosPorPagina = 10;

// Initialize Notyf
const notyf = new Notyf({
  duration: 4000,
  position: { x: 'right', y: 'top' },
  types: [
    {
      type: 'success',
      background: '#10b981',
      icon: { className: 'fas fa-check-circle', tagName: 'i', color: '#fff' }
    },
    {
      type: 'error',
      background: '#ef4444',
      icon: { className: 'fas fa-times-circle', tagName: 'i', color: '#fff' }
    },
  ],
});

// Computed properties
const totalClientes = computed(() => props.clientes?.data?.length || 0);

const clientesActivos = computed(() => {
  return props.clientes?.data?.filter(cliente => cliente.activo !== false).length || 0;
});

const regimenesFiscales = computed(() => {
  if (!props.clientes?.data) return [];
  const regimenes = [...new Set(props.clientes.data.map(cliente => cliente.regimen_fiscal).filter(Boolean))];
  return regimenes.sort();
});

const clientesFiltrados = computed(() => {
  if (!props.clientes?.data) return [];

  let clientes = [...props.clientes.data];

  // Filtrar por término de búsqueda
  if (searchTerm.value) {
    const termino = searchTerm.value.toLowerCase();
    clientes = clientes.filter(cliente =>
      cliente.nombre_razon_social?.toLowerCase().includes(termino) ||
      cliente.rfc?.toLowerCase().includes(termino) ||
      cliente.email?.toLowerCase().includes(termino)
    );
  }

  // Filtrar por régimen fiscal
  if (filtroRegimen.value) {
    clientes = clientes.filter(cliente => cliente.regimen_fiscal === filtroRegimen.value);
  }

  // Ordenar
  clientes.sort((a, b) => {
    let valorA, valorB;

    switch (ordenActual.value) {
      case 'nombre_razon_social':
        valorA = a.nombre_razon_social?.toLowerCase() || '';
        valorB = b.nombre_razon_social?.toLowerCase() || '';
        break;
      case 'rfc':
        valorA = a.rfc?.toLowerCase() || '';
        valorB = b.rfc?.toLowerCase() || '';
        break;
      case 'created_at':
        valorA = new Date(a.created_at || 0);
        valorB = new Date(b.created_at || 0);
        break;
      default:
        valorA = a[ordenActual.value] || '';
        valorB = b[ordenActual.value] || '';
    }

    if (direccionOrden.value === 'asc') {
      return valorA > valorB ? 1 : valorA < valorB ? -1 : 0;
    } else {
      return valorA < valorB ? 1 : valorA > valorB ? -1 : 0;
    }
  });

  return clientes;
});

// Filtros activos
const filtrosActivos = computed(() => {
  const filtros = [];

  if (searchTerm.value) {
    filtros.push({ key: 'search', label: `Búsqueda: "${searchTerm.value}"` });
  }

  if (filtroRegimen.value) {
    filtros.push({ key: 'regimen', label: `Régimen: ${filtroRegimen.value}` });
  }

  return filtros;
});

// Pagination computed properties
const totalPaginas = computed(() => {
  return Math.ceil(clientesFiltrados.value.length / elementosPorPagina);
});

const clientesPaginados = computed(() => {
  const inicio = (paginaActual.value - 1) * elementosPorPagina;
  const fin = inicio + elementosPorPagina;
  return clientesFiltrados.value.slice(inicio, fin);
});

const paginas = computed(() => {
  const total = totalPaginas.value;
  const actual = paginaActual.value;
  const paginas = [];

  // Mostrar máximo 7 páginas
  let inicio = Math.max(1, actual - 3);
  let fin = Math.min(total, inicio + 6);

  // Ajustar inicio si estamos cerca del final
  if (fin - inicio < 6) {
    inicio = Math.max(1, fin - 6);
  }

  for (let i = inicio; i <= fin; i++) {
    paginas.push(i);
  }

  return paginas;
});

// Methods
const formatearDireccion = (cliente) => {
  const direccion = [
    cliente.calle,
    cliente.numero_exterior,
    cliente.numero_interior,
    cliente.colonia,
    cliente.codigo_postal,
    cliente.municipio,
    cliente.estado,
    cliente.pais
  ].filter(Boolean).join(', ');

  return direccion || 'Sin dirección';
};

const openModal = (cliente) => {
  clienteSeleccionado.value = cliente;
  isModalOpen.value = true;
};

const closeModal = () => {
  isModalOpen.value = false;
  clienteSeleccionado.value = null;
};

const confirmarEliminacion = (cliente) => {
  clienteAEliminar.value = cliente;
  isConfirmOpen.value = true;
};

const eliminarClienteConfirmado = () => {
  if (!clienteAEliminar.value) return;

  loading.value = true;
  router.delete(route('clientes.destroy', clienteAEliminar.value.id), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      notyf.success('Cliente eliminado exitosamente.');
      isConfirmOpen.value = false;
      clienteAEliminar.value = null;
      loading.value = false;
    },
    onError: (errors) => {
      console.error('Error al eliminar cliente:', errors);
      notyf.error(errors.message || 'Error al eliminar el cliente.');
      isConfirmOpen.value = false;
      loading.value = false;
    },
  });
};

const cambiarOrden = (campo) => {
  if (ordenActual.value === campo) {
    direccionOrden.value = direccionOrden.value === 'asc' ? 'desc' : 'asc';
  } else {
    ordenActual.value = campo;
    direccionOrden.value = 'asc';
  }
  paginaActual.value = 1; // Reset pagination
};

const limpiarFiltro = (tipo) => {
  switch (tipo) {
    case 'search':
      searchTerm.value = '';
      break;
    case 'regimen':
      filtroRegimen.value = '';
      break;
  }
  paginaActual.value = 1;
};

const limpiarTodosFiltros = () => {
  searchTerm.value = '';
  filtroRegimen.value = '';
  ordenActual.value = 'nombre_razon_social';
  direccionOrden.value = 'asc';
  paginaActual.value = 1;
};

// Pagination methods
const irAPagina = (pagina) => {
  if (pagina >= 1 && pagina <= totalPaginas.value) {
    paginaActual.value = pagina;
  }
};

const paginaAnterior = () => {
  if (paginaActual.value > 1) {
    paginaActual.value--;
  }
};

const paginaSiguiente = () => {
  if (paginaActual.value < totalPaginas.value) {
    paginaActual.value++;
  }
};

// Watchers
watch([searchTerm, filtroRegimen], () => {
  paginaActual.value = 1; // Reset pagination when filters change
});

watch(ordenarPor, (nuevoOrden) => {
  const [campo, direccion] = nuevoOrden.split('_');
  if (campo === 'nombre') {
    ordenActual.value = 'nombre_razon_social';
  } else if (campo === 'fecha') {
    ordenActual.value = 'created_at';
  } else {
    ordenActual.value = campo;
  }
  direccionOrden.value = direccion;
  paginaActual.value = 1;
});

// Handle flash messages
onMounted(() => {
  const flash = page.props.flash;

  if (flash?.success) {
    notyf.success(flash.success);
  }

  if (flash?.error) {
    notyf.error(flash.error);
  }

  // Handle validation errors
  if (page.props.errors && Object.keys(page.props.errors).length > 0) {
    Object.values(page.props.errors).forEach(error => {
      notyf.error(Array.isArray(error) ? error[0] : error);
    });
  }
});

// Keyboard shortcuts
onMounted(() => {
  const handleKeyboard = (event) => {
    // Ctrl/Cmd + K para enfocar búsqueda
    if ((event.ctrlKey || event.metaKey) && event.key === 'k') {
      event.preventDefault();
      const searchInput = document.querySelector('input[type="text"]');
      if (searchInput) {
        searchInput.focus();
      }
    }

    // Escape para limpiar búsqueda
    if (event.key === 'Escape' && searchTerm.value) {
      searchTerm.value = '';
    }
  };

  document.addEventListener('keydown', handleKeyboard);

  // Cleanup
  return () => {
    document.removeEventListener('keydown', handleKeyboard);
  };
});
</script>

<style scoped>
/* Animaciones personalizadas */
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateX(-20px);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

@keyframes pulse {
  0%, 100% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.05);
  }
}

@keyframes shimmer {
  0% {
    background-position: -468px 0;
  }
  100% {
    background-position: 468px 0;
  }
}

/* Clases de animación */
.fade-in {
  animation: fadeIn 0.5s ease-out;
}

.slide-in {
  animation: slideIn 0.3s ease-out;
}

.pulse-hover:hover {
  animation: pulse 0.6s ease-in-out;
}

/* Estilos para tarjetas de estadísticas */
.stats-card {
  background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
  border: 1px solid #e2e8f0;
  transition: all 0.3s ease;
}

.stats-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

.stats-icon {
  transition: transform 0.3s ease;
}

.stats-card:hover .stats-icon {
  transform: scale(1.1);
}

/* Estilos para inputs de búsqueda */
.search-input {
  transition: all 0.3s ease;
  background-color: #ffffff;
}

.search-input:focus {
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
  border-color: #3b82f6;
}

.search-input::placeholder {
  color: #9ca3af;
  transition: color 0.3s ease;
}

.search-input:focus::placeholder {
  color: #d1d5db;
}

/* Estilos para filtros activos */
.filter-tag {
  background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
  border: 1px solid #93c5fd;
  transition: all 0.2s ease;
}

.filter-tag:hover {
  background: linear-gradient(135deg, #bfdbfe 0%, #93c5fd 100%);
  transform: translateY(-1px);
}

.filter-tag button {
  transition: all 0.2s ease;
}

.filter-tag button:hover {
  background-color: rgba(59, 130, 246, 0.2);
  transform: scale(1.1);
}

/* Estilos para tabla */
.table-container {
  background: #ffffff;
  border-radius: 0.75rem;
  overflow: hidden;
  box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
}

.table-header {
  background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
  border-bottom: 2px solid #e2e8f0;
}

.table-header th {
  font-weight: 600;
  color: #374151;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  transition: background-color 0.2s ease;
}

.table-header th:hover {
  background-color: rgba(59, 130, 246, 0.05);
}

.table-row {
  transition: all 0.2s ease;
  border-bottom: 1px solid #f3f4f6;
}

.table-row:hover {
  background-color: #f9fafb;
  transform: translateY(-1px);
  box-shadow: 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.table-row:last-child {
  border-bottom: none;
}

/* Estilos para avatares */
.avatar {
  background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
  border: 2px solid #ffffff;
  box-shadow: 0 2px 4px -1px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
}

.avatar:hover {
  transform: scale(1.1);
  box-shadow: 0 4px 8px -2px rgba(0, 0, 0, 0.2);
}

/* Estilos para botones de acción */
.action-button {
  padding: 0.5rem;
  border-radius: 0.375rem;
  transition: all 0.2s ease;
  border: 1px solid transparent;
}

.action-button:hover {
  transform: translateY(-1px);
  box-shadow: 0 2px 4px -1px rgba(0, 0, 0, 0.1);
}

.action-button-view {
  color: #3b82f6;
  background-color: #eff6ff;
  border-color: #dbeafe;
}

.action-button-view:hover {
  background-color: #dbeafe;
  border-color: #bfdbfe;
}

.action-button-edit {
  color: #059669;
  background-color: #ecfdf5;
  border-color: #d1fae5;
}

.action-button-edit:hover {
  background-color: #d1fae5;
  border-color: #a7f3d0;
}

.action-button-delete {
  color: #dc2626;
  background-color: #fef2f2;
  border-color: #fecaca;
}

.action-button-delete:hover {
  background-color: #fecaca;
  border-color: #fca5a5;
}

/* Estilos para tarjetas móviles */
.mobile-card {
  background: #ffffff;
  border: 1px solid #e5e7eb;
  border-radius: 0.75rem;
  transition: all 0.3s ease;
  box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
}

.mobile-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  border-color: #d1d5db;
}

/* Estilos para paginación */
.pagination-button {
  min-width: 2.5rem;
  height: 2.5rem;
  border: 1px solid #d1d5db;
  background-color: #ffffff;
  color: #374151;
  transition: all 0.2s ease;
}

.pagination-button:hover:not(:disabled) {
  background-color: #f3f4f6;
  border-color: #9ca3af;
  transform: translateY(-1px);
}

.pagination-button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.pagination-button.active {
  background-color: #3b82f6;
  border-color: #3b82f6;
  color: #ffffff;
  box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
}

.pagination-button.active:hover {
  background-color: #2563eb;
  border-color: #2563eb;
}

/* Estilos para estados vacíos */
.empty-state {
  padding: 3rem 1rem;
  text-align: center;
}

.empty-state-icon {
  color: #9ca3af;
  transition: color 0.3s ease;
}

.empty-state:hover .empty-state-icon {
  color: #6b7280;
}

/* Estilos para loading skeleton */
.skeleton {
  background: linear-gradient(90deg, #f3f4f6 25%, #e5e7eb 50%, #f3f4f6 75%);
  background-size: 200% 100%;
  animation: shimmer 2s infinite;
}

/* Estilos para badges */
.badge {
  display: inline-flex;
  align-items: center;
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 500;
  transition: all 0.2s ease;
}

.badge-rfc {
  background-color: #f3f4f6;
  color: #374151;
  border: 1px solid #d1d5db;
}

.badge-rfc:hover {
  background-color: #e5e7eb;
  transform: scale(1.05);
}

/* Estilos para tooltips */
.tooltip {
  position: relative;
}

.tooltip::before {
  content: attr(data-tooltip);
  position: absolute;
  bottom: 100%;
  left: 50%;
  transform: translateX(-50%);
  background-color: #1f2937;
  color: #ffffff;
  padding: 0.5rem 0.75rem;
  border-radius: 0.375rem;
  font-size: 0.75rem;
  white-space: nowrap;
  opacity: 0;
  visibility: hidden;
  transition: all 0.2s ease;
  z-index: 1000;
}

.tooltip::after {
  content: '';
  position: absolute;
  bottom: 100%;
  left: 50%;
  transform: translateX(-50%);
  border: 4px solid transparent;
  border-top-color: #1f2937;
  opacity: 0;
  visibility: hidden;
  transition: all 0.2s ease;
}

.tooltip:hover::before,
.tooltip:hover::after {
  opacity: 1;
  visibility: visible;
  transform: translateX(-50%) translateY(-4px);
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .stats-card {
    padding: 1rem;
  }

  .mobile-card {
    margin-bottom: 1rem;
  }

  .action-button {
    padding: 0.375rem;
  }
}

/* Estilos para modo oscuro (opcional) */
@media (prefers-color-scheme: dark) {
  .dark-mode .stats-card {
    background: linear-gradient(135deg, #1f2937 0%, #111827 100%);
    border-color: #374151;
  }

  .dark-mode .table-container {
    background-color: #1f2937;
    border-color: #374151;
  }

  .dark-mode .table-header {
    background: linear-gradient(135deg, #374151 0%, #1f2937 100%);
    border-color: #4b5563;
  }

  .dark-mode .table-row:hover {
    background-color: #374151;
  }

  .dark-mode .mobile-card {
    background-color: #1f2937;
    border-color: #374151;
  }
}

/* Estilos para impresión */
@media print {
  .no-print {
    display: none !important;
  }

  .table-container {
    box-shadow: none;
    border: 1px solid #000;
  }

  .table-row:hover {
    background-color: transparent;
    transform: none;
    box-shadow: none;
  }

  .action-button {
    display: none;
  }
}

/* Estilos para accesibilidad */
@media (prefers-reduced-motion: reduce) {
  *,
  *::before,
  *::after {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
  }
}

/* Focus states para accesibilidad */
.focus-visible:focus {
  outline: 2px solid #3b82f6;
  outline-offset: 2px;
}

/* Estilos para scrollbars personalizados */
.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
  height: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
  background: #f1f5f9;
  border-radius: 3px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 3px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}

/* Utilidades adicionales */
.text-truncate {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.transition-all {
  transition: all 0.3s ease;
}

.shadow-hover:hover {
  box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

.border-dashed-hover:hover {
  border-style: dashed;
}

.glass-effect {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.2);
}
</style>
