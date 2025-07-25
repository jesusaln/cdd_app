<template>
  <Head title="Proveedores" />
  <div class="p-6 bg-gray-50 min-h-screen">
    <!-- Header Section -->
    <div class="mb-8">
      <div class="flex items-center justify-between mb-4">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Registro de Proveedores</h1>
          <p class="text-gray-600 mt-1">Gestiona tu base de datos de proveedores</p>
        </div>
        <div class="flex items-center space-x-3">
          <span class="text-sm text-gray-500 bg-gray-200 px-3 py-1 rounded-full">
            Total: {{ proveedoresFiltrados.length }}
          </span>
          <Link
            :href="route('proveedores.create')"
            class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg hover:from-blue-600 hover:to-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 shadow-lg"
          >
            <i class="fas fa-plus mr-2"></i>
            Nuevo Proveedor
          </Link>
        </div>
      </div>

      <!-- Search and Filter Section -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-3 md:space-y-0">
          <div class="relative flex-1 max-w-md">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <i class="fas fa-search text-gray-400"></i>
            </div>
            <input
              v-model="searchTerm"
              type="text"
              placeholder="Buscar por nombre, RFC, email o teléfono..."
              class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
              @input="filtrarProveedores"
            />
            <button
              v-if="searchTerm"
              @click="limpiarBusqueda"
              class="absolute inset-y-0 right-0 pr-3 flex items-center"
            >
              <i class="fas fa-times text-gray-400 hover:text-gray-600 cursor-pointer"></i>
            </button>
          </div>

          <div class="flex items-center space-x-3">
            <select
              v-model="sortBy"
              @change="ordenarProveedores"
              class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
            >
              <option value="nombre_razon_social">Ordenar por Nombre</option>
              <option value="rfc">Ordenar por RFC</option>
              <option value="created_at">Ordenar por Fecha</option>
            </select>

            <button
              @click="toggleSortOrder"
              class="p-2 border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <i :class="sortOrder === 'asc' ? 'fas fa-sort-alpha-down' : 'fas fa-sort-alpha-up'" class="text-gray-600"></i>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Table Section -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
      <div v-if="proveedoresFiltrados.length > 0" class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                Información Básica
              </th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                Contacto
              </th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                Dirección
              </th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                Estado
              </th>
              <th class="px-6 py-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">
                Acciones
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr
              v-for="proveedor in proveedoresFiltrados"
              :key="proveedor.id"
              class="hover:bg-gray-50 transition-colors duration-150"
            >
              <!-- Información Básica -->
              <td class="px-6 py-4">
                <div class="flex items-center">
                  <div class="flex-shrink-0 h-10 w-10">
                    <div class="h-10 w-10 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center">
                      <span class="text-white font-semibold text-sm">
                        {{ proveedor.nombre_razon_social.charAt(0).toUpperCase() }}
                      </span>
                    </div>
                  </div>
                  <div class="ml-4">
                    <div class="text-sm font-medium text-gray-900">
                      {{ proveedor.nombre_razon_social }}
                    </div>
                    <div class="text-sm text-gray-500 font-mono">
                      RFC: {{ proveedor.rfc }}
                    </div>
                  </div>
                </div>
              </td>

              <!-- Contacto -->
              <td class="px-6 py-4">
                <div class="space-y-1">
                  <div class="flex items-center text-sm text-gray-900">
                    <i class="fas fa-phone text-gray-400 mr-2 w-4"></i>
                    <span>{{ proveedor.telefono || 'No disponible' }}</span>
                  </div>
                  <div class="flex items-center text-sm text-gray-900">
                    <i class="fas fa-envelope text-gray-400 mr-2 w-4"></i>
                    <span class="truncate max-w-xs">{{ proveedor.email || 'No disponible' }}</span>
                  </div>
                </div>
              </td>

              <!-- Dirección -->
              <td class="px-6 py-4">
                <div class="text-sm text-gray-900">
                  <div class="flex items-start">
                    <i class="fas fa-map-marker-alt text-gray-400 mr-2 mt-0.5 flex-shrink-0"></i>
                    <div class="space-y-1">
                      <div>{{ proveedor.calle }} {{ proveedor.numero_exterior }}{{ proveedor.numero_interior ? ' Int. ' + proveedor.numero_interior : '' }}</div>
                      <div class="text-gray-500">{{ proveedor.colonia }}, CP {{ proveedor.codigo_postal }}</div>
                    </div>
                  </div>
                </div>
              </td>

              <!-- Estado -->
              <td class="px-6 py-4">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                  <div class="w-1.5 h-1.5 bg-green-400 rounded-full mr-1.5"></div>
                  Activo
                </span>
              </td>

              <!-- Acciones -->
              <td class="px-6 py-4">
                <div class="flex items-center justify-center space-x-2">
                  <!-- Botón Ver -->
                  <Link
                    :href="route('proveedores.show', proveedor.id)"
                    class="inline-flex items-center px-3 py-2 text-sm font-medium text-blue-700 bg-blue-100 border border-blue-200 rounded-lg hover:bg-blue-200 hover:text-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200"
                    title="Ver detalles"
                  >
                    <i class="fas fa-eye mr-1"></i>
                    <span class="hidden sm:inline">Ver</span>
                  </Link>

                  <!-- Botón Editar -->
                  <Link
                    :href="route('proveedores.edit', proveedor.id)"
                    class="inline-flex items-center px-3 py-2 text-sm font-medium text-green-700 bg-green-100 border border-green-200 rounded-lg hover:bg-green-200 hover:text-green-800 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-all duration-200"
                    title="Editar proveedor"
                  >
                    <i class="fas fa-edit mr-1"></i>
                    <span class="hidden sm:inline">Editar</span>
                  </Link>

                  <!-- Botón Eliminar -->
                  <button
                    @click="eliminarProveedor(proveedor.id)"
                    class="inline-flex items-center px-3 py-2 text-sm font-medium text-red-700 bg-red-100 border border-red-200 rounded-lg hover:bg-red-200 hover:text-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-all duration-200"
                    title="Eliminar proveedor"
                  >
                    <i class="fas fa-trash-alt mr-1"></i>
                    <span class="hidden sm:inline">Eliminar</span>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Empty State -->
      <div v-else class="text-center py-12">
        <div class="mx-auto h-24 w-24 text-gray-300 mb-4">
          <i class="fas fa-truck text-6xl"></i>
        </div>
        <h3 class="text-lg font-medium text-gray-900 mb-2">
          {{ searchTerm ? 'No se encontraron proveedores' : 'No hay proveedores registrados' }}
        </h3>
        <p class="text-gray-500 mb-6">
          {{ searchTerm ? 'Intenta con otros términos de búsqueda' : 'Comienza agregando tu primer proveedor' }}
        </p>
        <Link
          v-if="!searchTerm"
          :href="route('proveedores.create')"
          class="inline-flex items-center px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors duration-200"
        >
          <i class="fas fa-plus mr-2"></i>
          Crear Primer Proveedor
        </Link>
      </div>
    </div>

    <!-- Loading Overlay -->
    <div v-if="loading" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 flex items-center space-x-4">
        <div class="animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-blue-500"></div>
        <span class="text-gray-700 font-medium">Procesando...</span>
      </div>
    </div>

    <!-- Confirmation Dialog -->
    <ConfirmDialog ref="confirmDialog" />
  </div>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import { Notyf } from 'notyf';
import 'notyf/notyf.min.css';
import axios from 'axios';

import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import AppLayout from '@/Layouts/AppLayout.vue';

defineOptions({ layout: AppLayout });

const props = defineProps({
  proveedores: {
    type: Array,
    default: () => []
  }
});

// Estado reactivo
const loading = ref(false);
const searchTerm = ref('');
const sortBy = ref('nombre_razon_social');
const sortOrder = ref('asc');
const proveedores = ref([...props.proveedores]);

// Configuración de notificaciones
const notyf = new Notyf({
  duration: 4000,
  position: { x: 'right', y: 'top' },
  types: [
    {
      type: 'success',
      background: 'linear-gradient(135deg, #10b981 0%, #059669 100%)',
      icon: { className: 'fas fa-check-circle', tagName: 'i', color: '#fff' },
    },
    {
      type: 'error',
      background: 'linear-gradient(135deg, #ef4444 0%, #dc2626 100%)',
      icon: { className: 'fas fa-exclamation-circle', tagName: 'i', color: '#fff' },
    },
  ],
});

const confirmDialog = ref(null);

// Computed para proveedores filtrados y ordenados
const proveedoresFiltrados = computed(() => {
  let filtered = [...proveedores.value];

  // Filtrar por búsqueda
  if (searchTerm.value) {
    const term = searchTerm.value.toLowerCase();
    filtered = filtered.filter(proveedor => {
      return (
        proveedor.nombre_razon_social?.toLowerCase().includes(term) ||
        proveedor.rfc?.toLowerCase().includes(term) ||
        proveedor.email?.toLowerCase().includes(term) ||
        proveedor.telefono?.toLowerCase().includes(term)
      );
    });
  }

  // Ordenar
  filtered.sort((a, b) => {
    const aValue = a[sortBy.value] || '';
    const bValue = b[sortBy.value] || '';

    const comparison = aValue.toString().localeCompare(bValue.toString());
    return sortOrder.value === 'asc' ? comparison : -comparison;
  });

  return filtered;
});

// Métodos
const filtrarProveedores = () => {
  // La lógica ya está en el computed
};

const limpiarBusqueda = () => {
  searchTerm.value = '';
};

const ordenarProveedores = () => {
  // La lógica ya está en el computed
};

const toggleSortOrder = () => {
  sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
};

const eliminarProveedor = async (id) => {
  try {
    const proveedor = proveedores.value.find(p => p.id === id);
    const confirmed = await confirmDialog.value.show(
      '¿Eliminar proveedor?',
      `Esta acción eliminará permanentemente a "${proveedor?.nombre_razon_social}" de tu base de datos.`
    );

    if (!confirmed) return;

    loading.value = true;

    await router.delete(route('proveedores.destroy', id), {
      onSuccess: () => {
        // Actualizar la lista local
        proveedores.value = proveedores.value.filter(p => p.id !== id);

        notyf.success('Proveedor eliminado correctamente');
      },
      onError: (errors) => {
        console.error('Error al eliminar:', errors);
        notyf.error('Error al eliminar el proveedor');
      },
    });
  } catch (error) {
    console.error('Error inesperado:', error);
    notyf.error('Ocurrió un error inesperado');
  } finally {
    loading.value = false;
  }
};

// Inicialización
onMounted(() => {
  // Cualquier inicialización adicional
});
</script>

<style scoped>
/* Animaciones personalizadas */
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

.fade-in {
  animation: fadeIn 0.3s ease-out;
}

/* Mejoras en la tabla responsive */
@media (max-width: 768px) {
  .table-responsive {
    display: block;
    width: 100%;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
  }

  /* En móviles, solo mostrar iconos */
  .action-text {
    display: none;
  }
}

@media (min-width: 640px) {
  /* En desktop, mostrar texto */
  .action-text {
    display: inline;
  }
}

/* Estilo para el scroll en dispositivos webkit */
::-webkit-scrollbar {
  height: 8px;
}

::-webkit-scrollbar-track {
  background: #f1f5f9;
  border-radius: 4px;
}

::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}
</style>
