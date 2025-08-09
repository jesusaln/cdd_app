<template>
  <Head title="Lista de Herramientas" />

  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <!-- Header con título y estadísticas -->
    <div class="mb-8">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Lista de Herramientas</h1>
          <p class="text-gray-600 mt-1">Gestiona y supervisa todas las herramientas del sistema</p>
        </div>
        <div class="flex space-x-4 text-sm">
          <div class="bg-blue-50 px-3 py-2 rounded-lg">
            <span class="text-blue-600 font-medium">Total: {{ herramientas.length }}</span>
          </div>
          <div class="bg-green-50 px-3 py-2 rounded-lg">
            <span class="text-green-600 font-medium">Asignadas: {{ herramientasAsignadas }}</span>
          </div>
          <div class="bg-orange-50 px-3 py-2 rounded-lg">
            <span class="text-orange-600 font-medium">Sin asignar: {{ herramientasSinAsignar }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Barra de acciones y filtros -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-4 sm:space-y-0">
        <!-- Botón crear y acciones -->
        <div class="flex items-center space-x-3">
          <button
            @click="createHerramienta"
            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200"
          >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Nueva Herramienta
          </button>

          <button
            @click="exportData"
            class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-all duration-200"
          >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Exportar
          </button>
        </div>

        <!-- Filtros y búsqueda -->
        <div class="flex items-center space-x-4">
          <!-- Búsqueda -->
          <div class="relative">
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Buscar herramientas..."
              class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm w-64"
            />
            <svg class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </div>

          <!-- Filtro por técnico -->
          <select
            v-model="selectedTecnico"
            class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm bg-white"
          >
            <option value="">Todos los técnicos</option>
            <option v-for="tecnico in tecnicos" :key="tecnico.id" :value="tecnico.id">
              {{ tecnico.nombre }} {{ tecnico.apellido }}
            </option>
          </select>

          <!-- Filtro por estado -->
          <select
            v-model="selectedEstado"
            class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm bg-white"
          >
            <option value="">Todos los estados</option>
            <option value="asignada">Asignadas</option>
            <option value="sin_asignar">Sin asignar</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Mensaje cuando no hay resultados -->
    <div v-if="filteredHerramientas.length === 0" class="text-center py-12">
      <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
      </svg>
      <h3 class="mt-2 text-sm font-medium text-gray-900">No se encontraron herramientas</h3>
      <p class="mt-1 text-sm text-gray-500">Intenta ajustar los filtros o crear una nueva herramienta.</p>
    </div>

    <!-- Tabla de herramientas -->
    <div v-else class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Herramienta
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Número de Serie
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Foto
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Técnico Asignado
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Estado
              </th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                Acciones
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr
              v-for="herramienta in paginatedHerramientas"
              :key="herramienta.id"
              class="hover:bg-gray-50 transition-colors duration-200"
            >
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="flex-shrink-0 h-10 w-10">
                    <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                      <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                      </svg>
                    </div>
                  </div>
                  <div class="ml-4">
                    <div class="text-sm font-medium text-gray-900">
                      {{ herramienta.nombre }}
                    </div>
                    <div class="text-sm text-gray-500">
                      ID: {{ herramienta.id }}
                    </div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900 font-mono">{{ herramienta.numero_serie }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <img
                    v-if="herramienta.foto"
                    :src="herramienta.foto"
                    alt="Foto de la herramienta"
                    class="h-12 w-12 rounded-lg object-cover border-2 border-gray-200 cursor-pointer hover:border-blue-500 transition-colors"
                    @click="openImageModal(herramienta.foto)"
                  />
                  <div
                    v-else
                    class="h-12 w-12 rounded-lg bg-gray-100 flex items-center justify-center"
                  >
                    <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div v-if="herramienta.tecnico" class="flex items-center">
                  <div class="flex-shrink-0 h-8 w-8">
                    <div class="h-8 w-8 rounded-full bg-green-100 flex items-center justify-center">
                      <span class="text-green-600 font-medium text-sm">
                        {{ herramienta.tecnico.nombre.charAt(0) }}{{ herramienta.tecnico.apellido.charAt(0) }}
                      </span>
                    </div>
                  </div>
                  <div class="ml-3">
                    <div class="text-sm font-medium text-gray-900">
                      {{ herramienta.tecnico.nombre }} {{ herramienta.tecnico.apellido }}
                    </div>
                  </div>
                </div>
                <div v-else class="text-sm text-gray-500 italic">Sin asignar</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span
                  v-if="herramienta.tecnico"
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800"
                >
                  Asignada
                </span>
                <span
                  v-else
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800"
                >
                  Disponible
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <div class="flex items-center justify-end space-x-2">
                  <button
                    @click="openModal(herramienta)"
                    class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200"
                    title="Ver detalles"
                  >
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    Ver
                  </button>
                  <Link
                    :href="route('herramientas.edit', herramienta.id)"
                    class="inline-flex items-center px-3 py-1.5 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200"
                    title="Editar herramienta"
                  >
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Editar
                  </Link>
                  <button
                    @click="confirmarEliminacion(herramienta.id, herramienta.nombre)"
                    class="inline-flex items-center px-3 py-1.5 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-all duration-200"
                    title="Eliminar herramienta"
                  >
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Eliminar
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Paginación -->
      <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
        <div class="flex items-center justify-between">
          <div class="flex items-center">
            <p class="text-sm text-gray-700">
              Mostrando
              <span class="font-medium">{{ startIndex + 1 }}</span>
              a
              <span class="font-medium">{{ Math.min(endIndex, filteredHerramientas.length) }}</span>
              de
              <span class="font-medium">{{ filteredHerramientas.length }}</span>
              resultados
            </p>
          </div>
          <div class="flex items-center space-x-2">
            <button
              @click="currentPage--"
              :disabled="currentPage === 1"
              class="relative inline-flex items-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Anterior
            </button>
            <span class="text-sm text-gray-700">
              Página {{ currentPage }} de {{ totalPages }}
            </span>
            <button
              @click="currentPage++"
              :disabled="currentPage === totalPages"
              class="relative inline-flex items-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Siguiente
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modals -->
    <ModalHerramientas v-if="showModal" :herramienta="selectedHerramienta" @close="closeModal" />

    <!-- Modal para imagen -->
    <div v-if="showImageModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" @click="closeImageModal">
      <div class="bg-white p-4 rounded-lg max-w-2xl max-h-[90vh] overflow-auto">
        <img :src="selectedImage" alt="Imagen ampliada" class="max-w-full h-auto" />
      </div>
    </div>

    <!-- Loading overlay -->
    <div v-if="isLoading" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white p-6 rounded-lg">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
        <p class="mt-2 text-sm text-gray-600">Procesando...</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ModalHerramientas from '@/Components/Modals/ModalHerramientas.vue';

// Define el layout del dashboard
defineOptions({ layout: AppLayout });

// Props para recibir la lista de herramientas y técnicos
const props = defineProps({
  herramientas: Array,
  tecnicos: Array,
});

// Estados para filtros y búsqueda
const selectedTecnico = ref('');
const selectedEstado = ref('');
const searchQuery = ref('');
const isLoading = ref(false);

// Estados para paginación
const currentPage = ref(1);
const itemsPerPage = ref(10);

// Estados para modales
const showModal = ref(false);
const selectedHerramienta = ref(null);
const showImageModal = ref(false);
const selectedImage = ref('');

// Computed para estadísticas
const herramientasAsignadas = computed(() =>
  props.herramientas.filter(h => h.tecnico).length
);

const herramientasSinAsignar = computed(() =>
  props.herramientas.filter(h => !h.tecnico).length
);

// Herramientas filtradas
const filteredHerramientas = computed(() => {
  let filtered = props.herramientas;

  // Filtro por búsqueda
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    filtered = filtered.filter(herramienta =>
      herramienta.nombre.toLowerCase().includes(query) ||
      herramienta.numero_serie.toLowerCase().includes(query) ||
      (herramienta.tecnico &&
       `${herramienta.tecnico.nombre} ${herramienta.tecnico.apellido}`.toLowerCase().includes(query))
    );
  }

  // Filtro por técnico
  if (selectedTecnico.value) {
    filtered = filtered.filter(herramienta =>
      herramienta.tecnico && herramienta.tecnico.id === selectedTecnico.value
    );
  }

  // Filtro por estado
  if (selectedEstado.value) {
    if (selectedEstado.value === 'asignada') {
      filtered = filtered.filter(herramienta => herramienta.tecnico);
    } else if (selectedEstado.value === 'sin_asignar') {
      filtered = filtered.filter(herramienta => !herramienta.tecnico);
    }
  }

  return filtered;
});

// Computed para paginación
const totalPages = computed(() => Math.ceil(filteredHerramientas.value.length / itemsPerPage.value));
const startIndex = computed(() => (currentPage.value - 1) * itemsPerPage.value);
const endIndex = computed(() => startIndex.value + itemsPerPage.value);

const paginatedHerramientas = computed(() =>
  filteredHerramientas.value.slice(startIndex.value, endIndex.value)
);

// Resetear página cuando cambian los filtros
watch([selectedTecnico, selectedEstado, searchQuery], () => {
  currentPage.value = 1;
});

// Métodos para manejar modales
const openModal = (herramienta) => {
  selectedHerramienta.value = herramienta;
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
  selectedHerramienta.value = null;
};

const openImageModal = (imageUrl) => {
  selectedImage.value = imageUrl;
  showImageModal.value = true;
};

const closeImageModal = () => {
  showImageModal.value = false;
  selectedImage.value = '';
};

// Método para confirmar la eliminación
const confirmarEliminacion = (id, nombre) => {
  if (confirm(`¿Estás seguro de que deseas eliminar la herramienta "${nombre}"? Esta acción no se puede deshacer.`)) {
    isLoading.value = true;

    router.delete(route('herramientas.destroy', id), {
      onSuccess: () => {
        isLoading.value = false;
        // Mostrar mensaje de éxito (puedes implementar un toast aquí)
        console.log('Herramienta eliminada exitosamente');
      },
      onError: (error) => {
        isLoading.value = false;
        console.error('Error al eliminar la herramienta:', error);
        alert('Error al eliminar la herramienta. Por favor, inténtalo de nuevo.');
      },
    });
  }
};

// Método para redirigir a la página de creación
const createHerramienta = () => {
  router.visit('/herramientas/create');
};

// Método para exportar datos (implementar según necesidades)
const exportData = () => {
  // Implementar exportación a CSV/Excel
  const csvContent = "data:text/csv;charset=utf-8,"
    + "Nombre,Número de Serie,Técnico Asignado,Estado\n"
    + filteredHerramientas.value.map(h =>
        `"${h.nombre}","${h.numero_serie}","${h.tecnico ? `${h.tecnico.nombre} ${h.tecnico.apellido}` : 'Sin asignar'}","${h.tecnico ? 'Asignada' : 'Disponible'}"`
      ).join("\n");

  const encodedUri = encodeURI(csvContent);
  const link = document.createElement("a");
  link.setAttribute("href", encodedUri);
  link.setAttribute("download", "herramientas.csv");
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
};
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

/* Estilos para el loading spinner */
@keyframes spin {
  to { transform: rotate(360deg); }
}

.animate-spin {
  animation: spin 1s linear infinite;
}

/* Mejoras en hover effects */
.hover-scale {
  transition: transform 0.2s ease-in-out;
}

.hover-scale:hover {
  transform: scale(1.05);
}

/* Estilos para scrollbar personalizada */
.overflow-x-auto::-webkit-scrollbar {
  height: 8px;
}

.overflow-x-auto::-webkit-scrollbar-track {
  background: #f1f5f9;
}

.overflow-x-auto::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 4px;
}

.overflow-x-auto::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}
</style>
