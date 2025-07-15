<template>
  <Head title="Técnicos" />
  <div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100">
    <!-- Header Section -->
    <div class="relative overflow-hidden bg-white/80 backdrop-blur-md border-b border-gray-200/50 shadow-lg">
      <div class="absolute inset-0 bg-gradient-to-r from-blue-600/10 to-indigo-600/10"></div>
      <div class="relative max-w-7xl mx-auto px-4 py-8">
        <div class="flex items-center justify-between">
          <div class="flex items-center space-x-4">
            <div class="p-3 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl shadow-lg">
              <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>
            </div>
            <div>
              <h1 class="text-3xl font-bold bg-gradient-to-r from-gray-900 to-gray-600 bg-clip-text text-transparent">
                {{ titulo }}
              </h1>
              <p class="text-gray-600 mt-1">Gestiona tu equipo de técnicos de manera eficiente</p>
            </div>
          </div>
          <div class="flex items-center space-x-2 text-sm text-gray-500">
            <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full font-medium">
              {{ tecnicos.length }} técnicos
            </span>
            <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full font-medium">
              {{ tecnicosFiltrados.length }} visibles
            </span>
          </div>
        </div>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 py-8">
      <!-- Controls Section -->
      <div class="mb-8">
        <div class="flex flex-col lg:flex-row gap-4 items-start lg:items-center justify-between">
          <!-- Search Bar -->
          <div class="relative flex-1 max-w-md">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </div>
            <input
              v-model="searchTerm"
              type="text"
              placeholder="Buscar técnicos..."
              class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white/80 backdrop-blur-sm shadow-sm transition-all duration-200 hover:shadow-md"
            />
            <div v-if="searchTerm" class="absolute inset-y-0 right-0 pr-3 flex items-center">
              <button @click="searchTerm = ''" class="text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="flex items-center space-x-3">
            <button @click="exportData" class="flex items-center space-x-2 px-4 py-2 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition-all duration-200 hover:scale-105">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
              <span>Exportar</span>
            </button>
            <Link :href="route('tecnicos.create')" class="flex items-center space-x-2 px-6 py-3 bg-gradient-to-r from-blue-500 to-indigo-600 text-white rounded-xl hover:from-blue-600 hover:to-indigo-700 transition-all duration-200 hover:scale-105 shadow-lg hover:shadow-xl">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
              </svg>
              <span class="font-medium">Crear Técnico</span>
            </Link>
          </div>
        </div>
      </div>

      <!-- Table Section -->
      <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl overflow-hidden border border-gray-200/50">
        <div v-if="tecnicosFiltrados.length > 0" class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gradient-to-r from-gray-50 to-blue-50">
              <tr>
                <th v-for="header in headers" :key="header" class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                  {{ header }}
                </th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                  Estado
                </th>
                <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                  Acciones
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="(tecnico, index) in tecnicosFiltrados" :key="tecnico.id"
                  class="hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 transition-all duration-200 group"
                  :style="{ animationDelay: `${index * 50}ms` }"
                  :class="'animate-fade-in-up'">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full flex items-center justify-center text-white font-semibold shadow-lg">
                      {{ getInitials(tecnico.nombre, tecnico.apellido) }}
                    </div>
                    <div>
                      <div class="text-sm font-medium text-gray-900">
                        {{ tecnico.nombre }} {{ tecnico.apellido }}
                      </div>
                      <div class="text-xs text-gray-500">ID: {{ tecnico.id }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center space-x-2">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                    </svg>
                    <span class="text-sm text-gray-600">{{ tecnico.email }}</span>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center space-x-2">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                    </svg>
                    <span class="text-sm text-gray-600">{{ tecnico.telefono }}</span>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <div class="flex items-center space-x-2">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span class="text-sm text-gray-600 max-w-xs truncate">{{ tecnico.direccion }}</span>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                    Activo
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center">
                  <div class="flex items-center justify-center space-x-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                    <button @click="openModal(tecnico)" class="p-2 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-lg transition-all duration-200 hover:scale-110" title="Ver detalles">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                      </svg>
                    </button>
                    <Link :href="route('tecnicos.edit', tecnico.id)" class="p-2 text-yellow-600 hover:text-yellow-800 hover:bg-yellow-50 rounded-lg transition-all duration-200 hover:scale-110" title="Editar">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                      </svg>
                    </Link>
                    <button @click="confirmarEliminacion(tecnico)" class="p-2 text-red-600 hover:text-red-800 hover:bg-red-50 rounded-lg transition-all duration-200 hover:scale-110" title="Eliminar">
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

        <!-- Empty State -->
        <div v-else class="text-center py-16">
          <div class="w-24 h-24 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
            <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
          </div>
          <h3 class="text-lg font-medium text-gray-900 mb-2">No hay técnicos registrados</h3>
          <p class="text-gray-500 mb-6">Comienza agregando tu primer técnico al sistema</p>
          <Link :href="route('tecnicos.create')" class="inline-flex items-center space-x-2 px-6 py-3 bg-gradient-to-r from-blue-500 to-indigo-600 text-white rounded-xl hover:from-blue-600 hover:to-indigo-700 transition-all duration-200 hover:scale-105 shadow-lg">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            <span>Crear Primer Técnico</span>
          </Link>
        </div>
      </div>
    </div>

    <!-- Loading Overlay -->
    <div v-if="loading" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50">
      <div class="bg-white/90 backdrop-blur-md rounded-2xl p-8 shadow-2xl">
        <div class="flex items-center space-x-4">
          <div class="w-8 h-8 border-4 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
          <span class="text-lg font-medium text-gray-700">Procesando...</span>
        </div>
      </div>
    </div>

    <!-- Enhanced Modal -->
    <TecnicoModal :tecnico="tecnicoSeleccionado" :isOpen="isModalOpen" @close="closeModal" />

    <!-- Enhanced Confirmation Dialog -->
    <div v-if="isConfirmOpen" class="fixed inset-0 flex items-center justify-center bg-black/50 backdrop-blur-sm z-50">
      <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden animate-scale-in">
        <div class="bg-gradient-to-r from-red-500 to-pink-600 px-6 py-4">
          <h2 class="text-xl font-bold text-white flex items-center space-x-2">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.994-.833-2.764 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
            </svg>
            <span>Confirmar Eliminación</span>
          </h2>
        </div>
        <div class="p-6">
          <p class="text-gray-700 mb-4">
            ¿Estás seguro de que deseas eliminar a
            <strong class="text-red-600">{{ tecnicoAEliminar?.nombre }} {{ tecnicoAEliminar?.apellido }}</strong>?
          </p>
          <p class="text-sm text-gray-500 mb-6">Esta acción no se puede deshacer.</p>
          <div class="flex justify-end space-x-3">
            <button @click="isConfirmOpen = false" class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors duration-200">
              Cancelar
            </button>
            <button @click="eliminarTecnicoConfirmado" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors duration-200">
              Eliminar
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { Notyf } from 'notyf';
import 'notyf/notyf.min.css';
import TecnicoModal from '@/Components/TecnicoModal.vue';
import AppLayout from '@/Layouts/AppLayout.vue';

// Define el layout del dashboard
defineOptions({ layout: AppLayout });

const props = defineProps({
  titulo: String,
  tecnicos: Array
});

document.title = props.titulo;

const headers = ['Nombre', 'Email', 'Teléfono', 'Dirección'];
const loading = ref(false);
const searchTerm = ref('');
const tecnicoSeleccionado = ref(null);
const isModalOpen = ref(false);
const isConfirmOpen = ref(false);
const tecnicoAEliminar = ref(null);

const tecnicos = ref(props.tecnicos);

const tecnicosFiltrados = computed(() => {
  if (!searchTerm.value) return tecnicos.value;

  return tecnicos.value.filter(tecnico => {
    const searchLower = searchTerm.value.toLowerCase();
    return tecnico.nombre.toLowerCase().includes(searchLower) ||
           tecnico.apellido.toLowerCase().includes(searchLower) ||
           tecnico.email.toLowerCase().includes(searchLower) ||
           tecnico.telefono.toLowerCase().includes(searchLower);
  });
});

const notyf = new Notyf({
  duration: 4000,
  position: { x: 'right', y: 'top' },
  types: [
    {
      type: 'success',
      background: 'linear-gradient(135deg, #10b981, #059669)',
      icon: {
        className: 'notyf__icon--success',
        tagName: 'i'
      }
    },
    {
      type: 'error',
      background: 'linear-gradient(135deg, #ef4444, #dc2626)',
      icon: {
        className: 'notyf__icon--error',
        tagName: 'i'
      }
    }
  ]
});

const getInitials = (nombre, apellido) => {
  return `${nombre.charAt(0)}${apellido.charAt(0)}`.toUpperCase();
};

const openModal = (tecnico) => {
  tecnicoSeleccionado.value = tecnico;
  isModalOpen.value = true;
};

const closeModal = () => {
  isModalOpen.value = false;
  tecnicoSeleccionado.value = null;
};

const confirmarEliminacion = (tecnico) => {
  tecnicoAEliminar.value = tecnico;
  isConfirmOpen.value = true;
};

const eliminarTecnicoConfirmado = async () => {
  if (!tecnicoAEliminar.value) return;

  loading.value = true;
  try {
    await router.delete(route('tecnicos.destroy', tecnicoAEliminar.value.id), {
      onSuccess: () => {
        notyf.success('Técnico eliminado exitosamente');
        tecnicos.value = tecnicos.value.filter(t => t.id !== tecnicoAEliminar.value.id);
        isConfirmOpen.value = false;
        tecnicoAEliminar.value = null;
      },
      onError: (errors) => {
        notyf.error('Error al eliminar el técnico');
        console.error('Error:', errors);
      }
    });
  } catch (error) {
    notyf.error('Ocurrió un error inesperado');
    console.error('Error:', error);
  } finally {
    loading.value = false;
  }
};

const exportData = () => {
  const csvContent = [
    ['Nombre', 'Apellido', 'Email', 'Teléfono', 'Dirección'],
    ...tecnicos.value.map(t => [t.nombre, t.apellido, t.email, t.telefono, t.direccion])
  ].map(row => row.join(',')).join('\n');

  const blob = new Blob([csvContent], { type: 'text/csv' });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = `tecnicos-${new Date().toISOString().split('T')[0]}.csv`;
  a.click();
  URL.revokeObjectURL(url);

  notyf.success('Datos exportados exitosamente');
};
</script>

<style scoped>
.animate-fade-in-up {
  animation: fadeInUp 0.6s ease-out forwards;
}

.animate-scale-in {
  animation: scaleIn 0.3s ease-out forwards;
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes scaleIn {
  from {
    opacity: 0;
    transform: scale(0.9);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}

/* Scrollbar personalizada */
::-webkit-scrollbar {
  width: 8px;
}

::-webkit-scrollbar-track {
  background: #f1f5f9;
}

::-webkit-scrollbar-thumb {
  background: linear-gradient(135deg, #3b82f6, #6366f1);
  border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
  background: linear-gradient(135deg, #2563eb, #4f46e5);
}
</style>
