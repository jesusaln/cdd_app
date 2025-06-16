<template>
  <Head title="Productos" />
  <div class="p-6">
    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900 mb-2">Gestión de Productos</h1>
      <p class="text-gray-600">Administra tu inventario de productos</p>
    </div>

    <!-- Acciones principales -->
    <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
      <Link
        :href="route('productos.create')"
        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-200"
      >
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
        </svg>
        Nuevo Producto
      </Link>

      <!-- Barra de búsqueda -->
      <div class="relative w-full sm:w-80">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
          <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
          </svg>
        </div>
        <input
          v-model="searchTerm"
          type="text"
          placeholder="Buscar por nombre o código..."
          class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
          @input="filtrarProductos"
        />
      </div>
    </div>

    <!-- Estadísticas rápidas -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
      <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
        <div class="p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4-8-4m16 0v10l-8 4-8-4V7"></path>
                </svg>
              </div>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Total Productos</p>
              <p class="text-2xl font-semibold text-gray-900">{{ productos.length }}</p>
            </div>
          </div>
        </div>
      </div>

      <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
        <div class="p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
              </div>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Productos Activos</p>
              <p class="text-2xl font-semibold text-gray-900">{{ productosActivos }}</p>
            </div>
          </div>
        </div>
      </div>

      <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
        <div class="p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
              </div>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Stock Bajo</p>
              <p class="text-2xl font-semibold text-gray-900">{{ productosStockBajo }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Tabla de productos -->
    <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
      <div v-if="productosFiltrados.length > 0" class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th v-for="header in headers" :key="header"
                  class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                {{ header }}
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Acciones
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="producto in productosFiltrados" :key="producto.id"
                class="hover:bg-gray-50 transition-colors duration-200">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">{{ producto.codigo }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">{{ producto.nombre }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900 font-medium">${{ formatearPrecio(producto.precio_venta) }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="getStockClass(producto.stock, producto.stock_minimo)"
                      class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                  {{ producto.stock }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="getEstadoClass(producto.estado)"
                      class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                  {{ producto.estado }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <div class="flex items-center space-x-3">
                  <!-- Botón Ver -->
                  <button @click="openModal(producto)"
                          class="inline-flex items-center px-3 py-1.5 bg-green-100 text-green-700 text-xs font-medium rounded-md hover:bg-green-200 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition duration-200">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                    Ver
                  </button>

                  <!-- Botón Editar -->
                  <Link :href="route('productos.edit', producto.id)"
                        class="inline-flex items-center px-3 py-1.5 bg-blue-100 text-blue-700 text-xs font-medium rounded-md hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-200">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Editar
                  </Link>

                  <!-- Botón Eliminar -->
                  <button @click="confirmarEliminacion(producto)"
                          :disabled="loading"
                          class="inline-flex items-center px-3 py-1.5 bg-red-100 text-red-700 text-xs font-medium rounded-md hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                    <span v-if="!loading">Eliminar</span>
                    <span v-else>Eliminando...</span>
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
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2 2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">No hay productos</h3>
        <p class="mt-1 text-sm text-gray-500">
          {{ searchTerm ? 'No se encontraron productos con ese criterio de búsqueda.' : 'Comienza creando tu primer producto.' }}
        </p>
        <div class="mt-6" v-if="!searchTerm">
          <Link :href="route('productos.create')"
                class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
            Crear Producto
          </Link>
        </div>
      </div>
    </div>

    <!-- Modal de confirmación de eliminación -->
    <div v-if="isConfirmOpen" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="cancelarEliminacion"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
          <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
            <div class="sm:flex sm:items-start">
              <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
              </div>
              <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                <h3 class="text-lg leading-6 font-medium text-gray-900">
                  Confirmar eliminación
                </h3>
                <div class="mt-2">
                  <p class="text-sm text-gray-500">
                    ¿Estás seguro de que deseas eliminar el producto "{{ productoAEliminar?.nombre }}"?
                    Esta acción no se puede deshacer.
                  </p>
                </div>
              </div>
            </div>
          </div>
          <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
            <button @click="eliminarProductoConfirmado"
                    :disabled="loading"
                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed">
              <span v-if="!loading">Eliminar</span>
              <span v-else class="flex items-center">
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Eliminando...
              </span>
            </button>
            <button @click="cancelarEliminacion"
                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
              Cancelar
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal para mostrar los detalles del producto -->
    <ProductoModal
      v-if="isModalOpen"
      :producto="selectedProducto"
      :isOpen="isModalOpen"
      @close="closeModal"
    />
  </div>
</template>

<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed, watchEffect } from 'vue';
import { Notyf } from 'notyf';
import 'notyf/notyf.min.css';
import ProductoModal from '@/Components/ProductoModal.vue';
import AppLayout from '@/Layouts/AppLayout.vue';

// Define el layout del dashboard
defineOptions({ layout: AppLayout });

const props = defineProps({
  productos: {
    type: Array,
    default: () => []
  }
});

const headers = ['Código', 'Nombre', 'Precio de Venta', 'Stock', 'Estado'];
const loading = ref(false);
const searchTerm = ref('');
const selectedProducto = ref(null);
const isModalOpen = ref(false);
const isConfirmOpen = ref(false);
const productoAEliminar = ref(null);

const productos = ref(props.productos);

// Computed properties
const productosFiltrados = computed(() => {
  if (!searchTerm.value) return productos.value;

  const term = searchTerm.value.toLowerCase();
  return productos.value.filter(producto => {
    return producto.nombre.toLowerCase().includes(term) ||
           producto.codigo.toLowerCase().includes(term);
  });
});

const productosActivos = computed(() => {
  return productos.value.filter(p => p.estado.toLowerCase() === 'activo').length;
});

const productosStockBajo = computed(() => {
  return productos.value.filter(p => {
    const stock = Number(p.stock);
    const stockMinimo = Number(p.stock_minimo || 0);
    return stock <= stockMinimo;
  }).length;
});

// Configuración de notificaciones
const notyf = new Notyf({
  duration: 3000,
  position: { x: 'right', y: 'top' },
  types: [
    {
      type: 'success',
      background: '#10b981',
      icon: {
        className: 'material-icons',
        tagName: 'i',
        text: 'check'
      }
    },
    {
      type: 'error',
      background: '#ef4444',
      icon: {
        className: 'material-icons',
        tagName: 'i',
        text: 'error'
      }
    }
  ]
});

const page = usePage();

// Watch para mensajes flash
watchEffect(() => {
  if (page.props.flash?.success) {
    notyf.success(page.props.flash.success);
  }
  if (page.props.flash?.error) {
    notyf.error(page.props.flash.error);
  }
});

// Métodos
const formatearPrecio = (precio) => {
  return new Intl.NumberFormat('es-MX', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(precio);
};

const getStockClass = (stock, stockMinimo = 0) => {
  const stockNum = Number(stock);
  const stockMin = Number(stockMinimo);

  if (stockNum <= stockMin) return 'bg-red-100 text-red-800'; // Stock crítico
  if (stockNum <= stockMin * 1.5) return 'bg-yellow-100 text-yellow-800'; // Stock bajo
  return 'bg-green-100 text-green-800'; // Stock normal
};

const getEstadoClass = (estado) => {
  const estadoLower = estado.toLowerCase();
  return estadoLower === 'activo'
    ? 'bg-green-100 text-green-800'
    : 'bg-red-100 text-red-800';
};

const filtrarProductos = () => {
  // La función se ejecuta automáticamente por el computed
};

const openModal = (producto) => {
  selectedProducto.value = producto;
  isModalOpen.value = true;
};

const closeModal = () => {
  isModalOpen.value = false;
  selectedProducto.value = null;
};

const confirmarEliminacion = (producto) => {
  productoAEliminar.value = producto;
  isConfirmOpen.value = true;
};

const cancelarEliminacion = () => {
  isConfirmOpen.value = false;
  productoAEliminar.value = null;
};

const eliminarProductoConfirmado = async () => {
  if (!productoAEliminar.value) return;

  loading.value = true;

  try {
    await router.delete(route('productos.destroy', productoAEliminar.value.id), {
      onSuccess: () => {
        notyf.success('Producto eliminado exitosamente.');
        productos.value = productos.value.filter(p => p.id !== productoAEliminar.value.id);
        isConfirmOpen.value = false;
        productoAEliminar.value = null;
      },
      onError: (errors) => {
        console.error('Errores:', errors);
        notyf.error('Error al eliminar el producto.');
      }
    });
  } catch (error) {
    console.error('Error inesperado:', error);
    notyf.error('Ocurrió un error inesperado.');
  } finally {
    loading.value = false;
  }
};
</script>
