<template>
    <div> <h1 class="text-2xl font-semibold mb-6">{{ titulo }}</h1> <!-- Muestra el título en la vista -->
    <div>
      <!-- Título de la página -->
      <h1 class="text-3xl font-bold mb-6 text-center">Registro de Clientes</h1>
      <!-- Título de la página -->
      <!-- <h1 class="text-2xl font-semibold mb-6">Registro de Clientes</h1> -->

      <!-- Botón para crear un nuevo cliente y búsqueda -->
      <div class="mb-4 flex justify-between items-center">
        <Link :href="route('clientes.create')" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition duration-300">
          Crear Cliente
        </Link>
        <input
          v-model="searchTerm"
          type="text"
          placeholder="Buscar por nombre o RFC"
          class="px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
        />
      </div>

      <!-- Tabla de clientes -->
      <div v-if="clientesFiltrados.length > 0" class="overflow-x-auto bg-white rounded-lg shadow-md">
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
            <tr v-for="cliente in clientesFiltrados" :key="cliente.id" class="hover:bg-gray-100">
              <td class="px-4 py-3 text-sm text-gray-700">{{ cliente.nombre_razon_social }}</td>
              <td class="px-4 py-3 text-sm text-gray-700">{{ cliente.rfc }}</td>
              <td class="px-4 py-3 text-sm text-gray-700">{{ cliente.regimen_fiscal }}</td>
              <td class="px-4 py-3 text-sm text-gray-700">{{ cliente.uso_cfdi }}</td>
              <td class="px-4 py-3 text-sm text-gray-700">{{ cliente.email }}</td>
              <td class="px-4 py-3 text-sm text-gray-700">{{ cliente.telefono }}</td>
              <td class="px-4 py-3 text-sm text-gray-700">
                {{ cliente.calle }} {{ cliente.numero_exterior }} {{ cliente.numero_interior }},
                {{ cliente.colonia }}, {{ cliente.codigo_postal }},
                {{ cliente.municipio }}, {{ cliente.estado }}, {{ cliente.pais }}
              </td>
              <td class="px-4 py-3 flex space-x-2">
                <button @click="openModal(cliente)" class="bg-green-500 text-white px-4 py-2 rounded-md hover:bg-green-600">
                  Ver
                </button>
                <Link :href="route('clientes.edit', cliente.id)" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">
                  Editar
                </Link>
                <button @click="confirmarEliminacion(cliente)" class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600">
                  Eliminar
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Mensaje si no hay clientes -->
      <div v-else class="text-center text-gray-500 mt-4">
        No hay clientes registrados.
      </div>

      <!-- Spinner de carga -->
      <div v-if="loading" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500"></div>
      </div>

      <!-- Modal de Cliente -->
      <ClienteModal :cliente="clienteSeleccionado" :isOpen="isModalOpen" @close="closeModal" />

      <!-- Modal de confirmación -->
      <div v-if="isConfirmOpen" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
        <div class="bg-white p-6 rounded-lg shadow-lg w-96">
          <h2 class="text-xl font-semibold mb-4">Confirmar eliminación</h2>
          <p class="text-gray-700 mb-4">¿Estás seguro de que deseas eliminar el cliente <strong>{{ clienteAEliminar.nombre_razon_social }}</strong>?</p>
          <div class="flex justify-end space-x-4">
            <button @click="isConfirmOpen = false" class="bg-gray-400 text-white px-4 py-2 rounded hover:bg-gray-500 transition">
              Cancelar
            </button>
            <button @click="eliminarClienteConfirmado" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 transition">
              Eliminar
            </button>
          </div>
        </div>
      </div>
    </div>
</div>
  </template>

  <script setup>
  import { Link, router } from '@inertiajs/vue3';
  import { ref, computed } from 'vue';
  import { Notyf } from 'notyf';
  import 'notyf/notyf.min.css';
  import Dashboard from '@/Pages/Dashboard.vue';
  import ClienteModal from '@/Components/ClienteModal.vue';
  import { defineProps } from 'vue';

const props = defineProps({
  titulo: String,  // Recibes el título desde el controlador
  clientes: Array
});

document.title = props.titulo;  // Cambias el título de la página dinámicamente

  defineOptions({ layout: Dashboard });
  const headers = ['Nombre/Razón Social', 'RFC', 'Régimen Fiscal', 'Uso CFDI', 'Email', 'Teléfono', 'Dirección'];
  const loading = ref(false);
  const searchTerm = ref('');
  const clienteSeleccionado = ref(null);
  const isModalOpen = ref(false);
  const isConfirmOpen = ref(false);
  const clienteAEliminar = ref(null);

  const clientes = ref(props.clientes);
  const clientesFiltrados = computed(() => {
    return clientes.value.filter(cliente => {
      return cliente.nombre_razon_social.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
             cliente.rfc.toLowerCase().includes(searchTerm.value.toLowerCase());
    });
  });

  const notyf = new Notyf({ duration: 3000, position: { x: 'right', y: 'top' } });

  const openModal = (cliente) => {
    clienteSeleccionado.value = cliente;
    isModalOpen.value = true;
  };
  const closeModal = () => {
    isModalOpen.value = false;
  };

  const confirmarEliminacion = (cliente) => {
    clienteAEliminar.value = cliente;
    isConfirmOpen.value = true;
  };

  const eliminarClienteConfirmado = async () => {
    if (!clienteAEliminar.value) return;

    loading.value = true;
    try {
      await router.delete(route('clientes.destroy', clienteAEliminar.value.id), {
        onSuccess: () => {
          notyf.success('Cliente eliminado exitosamente.');
          clientes.value = clientes.value.filter(c => c.id !== clienteAEliminar.value.id);
        },
        onError: () => notyf.error('Error al eliminar el cliente.')
      });
    } catch (error) {
      notyf.error('Ocurrió un error inesperado.');
    } finally {
      isConfirmOpen.value = false;
      loading.value = false;
    }
  };
  </script>
