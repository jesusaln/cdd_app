<template>
  <Head title="Usuarios" />
  <div class="usuarios-index min-h-screen bg-gradient-to-br from-slate-50 to-blue-50 p-6">
    <!-- Header Section -->
    <div class="max-w-7xl mx-auto">
      <div class="mb-8">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-4xl font-bold text-gray-900 mb-2">Usuarios</h1>
            <p class="text-gray-600 text-lg">Gestiona y administra todos los usuarios del sistema</p>
            <div class="flex items-center mt-3 text-sm text-gray-500">
              <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              {{ usuarios.length }} usuarios registrados
            </div>
          </div>
          <div class="flex items-center space-x-4">
            <!-- Botón de crear usuario mejorado -->
            <Link :href="route('usuarios.create')"
                  class="group bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-6 py-3 rounded-xl font-semibold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200 flex items-center space-x-2">
              <svg class="w-5 h-5 group-hover:rotate-90 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
              </svg>
              <span>Crear Usuario</span>
            </Link>
          </div>
        </div>
      </div>

      <!-- Search and Filters Section -->
      <div class="bg-white rounded-2xl shadow-lg p-6 mb-8 border border-gray-100">
        <div class="flex flex-col sm:flex-row gap-4 items-center justify-between">
          <div class="relative flex-1 max-w-md">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
              </svg>
            </div>
            <input
              v-model="searchTerm"
              type="text"
              placeholder="Buscar por nombre o email..."
              class="block w-full pl-10 pr-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-gray-50 hover:bg-white"
            />
          </div>
          <div class="flex items-center space-x-3 text-sm text-gray-600">
            <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full font-medium">
              {{ usuariosFiltrados.length }} resultados
            </span>
          </div>
        </div>
      </div>

      <!-- Users Grid/Table -->
      <div v-if="usuariosFiltrados.length > 0">
        <!-- Vista de tarjetas para móvil -->
        <div class="block sm:hidden space-y-4">
          <div v-for="usuario in usuariosFiltrados" :key="usuario.id"
               class="bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-200 border border-gray-100">
            <div class="flex items-start justify-between mb-4">
              <div class="flex items-center space-x-3">
                <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center text-white font-semibold text-lg">
                  {{ usuario.name.charAt(0).toUpperCase() }}
                </div>
                <div>
                  <h3 class="font-semibold text-gray-900">{{ usuario.name }}</h3>
                  <p class="text-gray-500 text-sm">{{ usuario.email }}</p>
                </div>
              </div>
            </div>
            <div class="mb-4">
              <div class="flex flex-wrap gap-2">
                <span v-for="role in usuario.roles" :key="role.name"
                      class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                  <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                  </svg>
                  {{ role.name }}
                </span>
              </div>
            </div>
            <div class="flex space-x-3">
              <Link :href="route('usuarios.edit', usuario.id)"
                    class="flex-1 bg-blue-50 hover:bg-blue-100 text-blue-700 px-4 py-2 rounded-lg font-medium text-center transition-colors duration-200">
                Editar
              </Link>
              <button @click="confirmarEliminacion(usuario.id)"
                      class="flex-1 bg-red-50 hover:bg-red-100 text-red-700 px-4 py-2 rounded-lg font-medium transition-colors duration-200">
                Eliminar
              </button>
            </div>
          </div>
        </div>

        <!-- Vista de tabla para desktop -->
        <div class="hidden sm:block bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-100">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gradient-to-r from-gray-50 to-blue-50">
              <tr>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                  Usuario
                </th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                  Email
                </th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                  Roles
                </th>
                <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                  Acciones
                </th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="usuario in usuariosFiltrados" :key="usuario.id"
                  class="hover:bg-gradient-to-r hover:from-blue-50 hover:to-transparent transition-all duration-200 group">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center text-white font-semibold mr-4 group-hover:scale-110 transition-transform duration-200">
                      {{ usuario.name.charAt(0).toUpperCase() }}
                    </div>
                    <div>
                      <div class="text-sm font-semibold text-gray-900">{{ usuario.name }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-700">{{ usuario.email }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex flex-wrap gap-2">
                    <span v-for="role in usuario.roles" :key="role.name"
                          class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                      <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                      </svg>
                      {{ role.name }}
                    </span>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right">
                  <div class="flex items-center justify-end space-x-3">
                    <Link :href="route('usuarios.edit', usuario.id)"
                          class="inline-flex items-center px-4 py-2 bg-blue-50 hover:bg-blue-100 text-blue-700 text-sm font-medium rounded-lg transition-all duration-200 hover:scale-105">
                      <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                      </svg>
                      Editar
                    </Link>
                    <button @click="confirmarEliminacion(usuario.id)"
                            class="inline-flex items-center px-4 py-2 bg-red-50 hover:bg-red-100 text-red-700 text-sm font-medium rounded-lg transition-all duration-200 hover:scale-105">
                      <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                      </svg>
                      Eliminar
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="text-center py-16">
        <div class="bg-white rounded-2xl shadow-lg p-12 border border-gray-100">
          <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
          </div>
          <h3 class="text-xl font-semibold text-gray-900 mb-2">No hay usuarios</h3>
          <p class="text-gray-500 mb-6">
            {{ searchTerm ? 'No se encontraron usuarios que coincidan con tu búsqueda.' : 'Aún no hay usuarios registrados en el sistema.' }}
          </p>
          <Link v-if="!searchTerm" :href="route('usuarios.create')"
                class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition-colors duration-200">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            Crear primer usuario
          </Link>
        </div>
      </div>

      <!-- Loading Spinner -->
      <div v-if="loading" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 backdrop-blur-sm">
        <div class="bg-white p-8 rounded-2xl shadow-2xl">
          <div class="flex items-center space-x-4">
            <div class="animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-blue-500"></div>
            <span class="text-gray-700 font-medium">Procesando...</span>
          </div>
        </div>
      </div>

      <!-- Confirmation Dialog -->
      <div v-if="showConfirmationDialog" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 backdrop-blur-sm p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-8 transform animate-pulse-once">
          <div class="text-center mb-6">
            <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
              <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
              </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">¿Eliminar usuario?</h3>
            <p class="text-gray-600">Esta acción no se puede deshacer. El usuario será eliminado permanentemente del sistema.</p>
          </div>
          <div class="flex space-x-4">
            <button @click="cancelarEliminacion"
                    class="flex-1 px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-800 font-semibold rounded-xl transition-colors duration-200">
              Cancelar
            </button>
            <button @click="eliminarUsuario"
                    class="flex-1 px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-xl transition-colors duration-200">
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
import AppLayout from '@/Layouts/AppLayout.vue';

// Define el layout del dashboard
defineOptions({ layout: AppLayout });

// Propiedades
const props = defineProps({ usuarios: Array });
const searchTerm = ref('');
const loading = ref(false);
const showConfirmationDialog = ref(false);
const usuarioIdToDelete = ref(null);

// Configuración de Notyf para notificaciones
const notyf = new Notyf({
  duration: 4000,
  position: { x: 'right', y: 'top' },
  types: [
    {
      type: 'success',
      background: '#10B981',
      icon: {
        className: 'notyf__icon--success',
        tagName: 'i'
      }
    },
    {
      type: 'error',
      background: '#EF4444',
      icon: {
        className: 'notyf__icon--error',
        tagName: 'i'
      }
    }
  ]
});

// Variable reactiva local para almacenar los usuarios
const usuarios = ref([...props.usuarios]);

// Filtrado de usuarios
const usuariosFiltrados = computed(() => {
  return usuarios.value.filter(usuario => {
    return usuario.name.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
           usuario.email.toLowerCase().includes(searchTerm.value.toLowerCase());
  });
});

// Función para confirmar la eliminación de un usuario
const confirmarEliminacion = (id) => {
  usuarioIdToDelete.value = id;
  showConfirmationDialog.value = true;
};

// Función para cancelar la eliminación
const cancelarEliminacion = () => {
  usuarioIdToDelete.value = null;
  showConfirmationDialog.value = false;
};

// Función para eliminar un usuario
const eliminarUsuario = async () => {
  if (usuarioIdToDelete.value) {
    loading.value = true;
    try {
      await router.delete(route('usuarios.destroy', usuarioIdToDelete.value), {
        onSuccess: () => {
          notyf.success('Usuario eliminado exitosamente.');
          usuarios.value = usuarios.value.filter(usuario => usuario.id !== usuarioIdToDelete.value);
          showConfirmationDialog.value = false;
        },
        onError: () => notyf.error('Error al eliminar el usuario.')
      });
    } catch (error) {
      notyf.error('Ocurrió un error inesperado.');
    } finally {
      loading.value = false;
    }
  }
};
</script>

<style scoped>
@keyframes animate-pulse-once {
  0% {
    opacity: 0;
    transform: scale(0.95) translateY(10px);
  }
  100% {
    opacity: 1;
    transform: scale(1) translateY(0);
  }
}

.animate-pulse-once {
  animation: animate-pulse-once 0.2s ease-out;
}

/* Mejoras adicionales para hover effects */
.group:hover .group-hover\:scale-110 {
  transform: scale(1.1);
}
</style>
