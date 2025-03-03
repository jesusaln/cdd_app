<template>
    <Head title="Técnicos" />
    <div>
      <h1 class="text-2xl font-semibold mb-6">{{ titulo }}</h1>
      <div>
        <h1 class="text-3xl font-bold mb-6 text-center">Registro de Técnicos</h1>

        <div class="mb-4 flex justify-between items-center">
          <Link :href="route('tecnicos.create')" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition duration-300">
            Crear Técnico
          </Link>
          <input
            v-model="searchTerm"
            type="text"
            placeholder="Buscar por nombre o email"
            class="px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>

        <div v-if="tecnicosFiltrados.length > 0" class="overflow-x-auto bg-white rounded-lg shadow-md">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th v-for="header in headers" :key="header" class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                  {{ header }}
                </th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr v-for="tecnico in tecnicosFiltrados" :key="tecnico.id" class="hover:bg-gray-100">
                <td class="px-4 py-3 text-sm text-gray-700">{{ tecnico.nombre }} {{ tecnico.apellido }}</td>
                <td class="px-4 py-3 text-sm text-gray-700">{{ tecnico.email }}</td>
                <td class="px-4 py-3 text-sm text-gray-700">{{ tecnico.telefono }}</td>
                <td class="px-4 py-3 text-sm text-gray-700">{{ tecnico.direccion }}</td>
                <td class="px-4 py-3 flex space-x-2">
                  <button @click="openModal(tecnico)" class="bg-green-500 text-white px-4 py-2 rounded-md hover:bg-green-600">
                    Ver
                  </button>
                  <Link :href="route('tecnicos.edit', tecnico.id)" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">
                    Editar
                  </Link>
                  <button @click="confirmarEliminacion(tecnico)" class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600">
                    Eliminar
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-else class="text-center text-gray-500 mt-4">
          No hay técnicos registrados.
        </div>

        <div v-if="loading" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
          <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500"></div>
        </div>

        <TecnicoModal :tecnico="tecnicoSeleccionado" :isOpen="isModalOpen" @close="closeModal" />

        <div v-if="isConfirmOpen" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
          <div class="bg-white p-6 rounded-lg shadow-lg w-96">
            <h2 class="text-xl font-semibold mb-4">Confirmar eliminación</h2>
            <p class="text-gray-700 mb-4">¿Estás seguro de que deseas eliminar el técnico <strong>{{ tecnicoAEliminar.nombre }} {{ tecnicoAEliminar.apellido }}</strong>?</p>
            <div class="flex justify-end space-x-4">
              <button @click="isConfirmOpen = false" class="bg-gray-400 text-white px-4 py-2 rounded hover:bg-gray-500 transition">
                Cancelar
              </button>
              <button @click="eliminarTecnicoConfirmado" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 transition">
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
  import Dashboard from '@/Pages/Dashboard.vue';
 import TecnicoModal from '@/Components/TecnicoModal.vue';


  const props = defineProps({
    titulo: String,
    tecnicos: Array
  });

  document.title = props.titulo;

  defineOptions({ layout: Dashboard });
  const headers = ['Nombre', 'Email', 'Teléfono', 'Dirección'];
  const loading = ref(false);
  const searchTerm = ref('');
  const tecnicoSeleccionado = ref(null);
  const isModalOpen = ref(false);
  const isConfirmOpen = ref(false);
  const tecnicoAEliminar = ref(null);

  const tecnicos = ref(props.tecnicos);
  const tecnicosFiltrados = computed(() => {
    return tecnicos.value.filter(tecnico => {
      return tecnico.nombre.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
             tecnico.email.toLowerCase().includes(searchTerm.value.toLowerCase());
    });
  });

  const notyf = new Notyf({ duration: 3000, position: { x: 'right', y: 'top' } });

  const openModal = (tecnico) => {
    tecnicoSeleccionado.value = tecnico;
    isModalOpen.value = true;
  };
  const closeModal = () => {
    isModalOpen.value = false;
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
          notyf.success('Técnico eliminado exitosamente.');
          tecnicos.value = tecnicos.value.filter(t => t.id !== tecnicoAEliminar.value.id);
        },
        onError: () => notyf.error('Error al eliminar el técnico.')
      });
    } catch (error) {
      notyf.error('Ocurrió un error inesperado.');
    } finally {
      isConfirmOpen.value = false;
      loading.value = false;
    }
  };
  </script>
