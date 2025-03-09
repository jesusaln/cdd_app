<template>
    <Head title="Usuarios" />
    <div class="usuarios-index">
      <!-- Título de la página -->
      <h1 class="text-2xl font-semibold mb-6">Listado de Usuarios</h1>

      <!-- Botón de crear usuario y campo de búsqueda -->
      <div class="mb-4 flex justify-between items-center">
        <Link :href="route('usuarios.create')" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition duration-300">
          Crear Usuario
        </Link>
        <input
          v-model="searchTerm"
          type="text"
          placeholder="Buscar por nombre o email"
          class="px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
        />
      </div>

      <!-- Tabla de usuarios -->
      <div v-if="usuariosFiltrados.length > 0" class="overflow-x-auto bg-white rounded-lg shadow-md">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Rol</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr v-for="usuario in usuariosFiltrados" :key="usuario.id" class="hover:bg-gray-100">
              <td class="px-4 py-3 text-sm text-gray-700">{{ usuario.name }}</td>
              <td class="px-4 py-3 text-sm text-gray-700">{{ usuario.email }}</td>
              <td class="px-4 py-3 text-sm text-gray-700">{{ usuario.roles.map(role => role.name).join(', ') }}</td>
              <td class="px-4 py-3 flex space-x-2">
                <Link :href="route('usuarios.edit', usuario.id)" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">
                  Editar
                </Link>
                <button @click="confirmarEliminacion(usuario.id)" class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600">
                  Eliminar
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Mensaje si no hay usuarios -->
      <div v-else class="text-center text-gray-500 mt-4">
        No hay usuarios registrados.
      </div>

      <!-- Spinner de carga -->
      <div v-if="loading" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500"></div>
      </div>

      <!-- Diálogo de confirmación -->
      <div v-if="showConfirmationDialog" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg shadow-lg">
          <p class="mb-4">¿Estás seguro de que deseas eliminar este usuario?</p>
          <div class="flex justify-end">
            <button @click="cancelarEliminacion" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600 mr-2">
              Cancelar
            </button>
            <button @click="eliminarUsuario" class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600">
              Eliminar
            </button>
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
  const notyf = new Notyf({ duration: 3000, position: { x: 'right', y: 'top' } });

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
  /* Aquí van tus estilos personalizados */
  </style>
