<template>
    <Head title="Empresas" />
    <div>
      <h1 class="text-2xl font-semibold mb-6">{{ titulo }}</h1>
      <div>
        <!-- Título de la página -->
        <h1 class="text-3xl font-bold mb-6 text-center">Registro de Empresas</h1>

        <!-- Botón para crear una nueva empresa y búsqueda -->
        <div class="mb-4 flex justify-between items-center">
          <Link :href="route('empresas.create')" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition duration-300">
            Crear Empresa
          </Link>
          <input
            v-model="searchTerm"
            type="text"
            placeholder="Buscar por nombre o RFC"
            class="px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>

        <!-- Tabla de empresas -->
        <div v-if="empresasFiltradas.length > 0" class="overflow-x-auto bg-white rounded-lg shadow-md">
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
              <tr v-for="empresa in empresasFiltradas" :key="empresa.id" class="hover:bg-gray-100">
                <td class="px-4 py-3 text-sm text-gray-700">{{ empresa.nombre_razon_social }}</td>
                <td class="px-4 py-3 text-sm text-gray-700">{{ empresa.rfc }}</td>
                <td class="px-4 py-3 text-sm text-gray-700">{{ empresa.regimen_fiscal }}</td>
                <td class="px-4 py-3 text-sm text-gray-700">{{ empresa.uso_cfdi }}</td>
                <td class="px-4 py-3 text-sm text-gray-700">{{ empresa.email }}</td>
                <td class="px-4 py-3 text-sm text-gray-700">{{ empresa.telefono }}</td>
                <td class="px-4 py-3 text-sm text-gray-700">
                  {{ empresa.calle }} {{ empresa.numero_exterior }} {{ empresa.numero_interior }},
                  {{ empresa.colonia }}, {{ empresa.codigo_postal }},
                  {{ empresa.municipio }}, {{ empresa.estado }}, {{ empresa.pais }}
                </td>
                <td class="px-4 py-3 flex space-x-2">
                  <button @click="openModal(empresa)" class="bg-green-500 text-white px-4 py-2 rounded-md hover:bg-green-600">
                    Ver
                  </button>
                  <Link :href="route('empresas.edit', empresa.id)" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">
                    Editar
                  </Link>
                  <button @click="confirmarEliminacion(empresa)" class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600">
                    Eliminar
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Mensaje si no hay empresas -->
        <div v-else class="text-center text-gray-500 mt-4">
          No hay empresas registradas.
        </div>

        <!-- Spinner de carga -->
        <div v-if="loading" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
          <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500"></div>
        </div>

        <!-- Modal de Empresa -->
        <EmpresaModal :empresa="empresaSeleccionada" :isOpen="isModalOpen" @close="closeModal" />

        <!-- Modal de confirmación -->
        <div v-if="isConfirmOpen" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
          <div class="bg-white p-6 rounded-lg shadow-lg w-96">
            <h2 class="text-xl font-semibold mb-4">Confirmar eliminación</h2>
            <p class="text-gray-700 mb-4">¿Estás seguro de que deseas eliminar la empresa <strong>{{ empresaAEliminar.nombre_razon_social }}</strong>?</p>
            <div class="flex justify-end space-x-4">
              <button @click="isConfirmOpen = false" class="bg-gray-400 text-white px-4 py-2 rounded hover:bg-gray-500 transition">
                Cancelar
              </button>
              <button @click="eliminarEmpresaConfirmado" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 transition">
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

  import EmpresaModal from '@/Components/EmpresaModal.vue';
  import { defineProps } from 'vue';

  import AppLayout from '@/Layouts/AppLayout.vue';

  // Define el layout del dashboard
  defineOptions({ layout: AppLayout });

  const props = defineProps({
    titulo: String,  // Recibes el título desde el controlador
    empresas: Array
  });

  document.title = props.titulo;  // Cambias el título de la página dinámicamente

  const headers = ['Nombre/Razón Social', 'RFC', 'Régimen Fiscal', 'Uso CFDI', 'Email', 'Teléfono', 'Dirección'];
  const loading = ref(false);
  const searchTerm = ref('');
  const empresaSeleccionada = ref(null);
  const isModalOpen = ref(false);
  const isConfirmOpen = ref(false);
  const empresaAEliminar = ref(null);

  const empresas = ref(props.empresas);
  const empresasFiltradas = computed(() => {
    return empresas.value.filter(empresa => {
      return empresa.nombre_razon_social.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
             empresa.rfc.toLowerCase().includes(searchTerm.value.toLowerCase());
    });
  });

  const notyf = new Notyf({ duration: 3000, position: { x: 'right', y: 'top' } });

  const openModal = (empresa) => {
    empresaSeleccionada.value = empresa;
    isModalOpen.value = true;
  };
  const closeModal = () => {
    isModalOpen.value = false;
  };

  const confirmarEliminacion = (empresa) => {
    empresaAEliminar.value = empresa;
    isConfirmOpen.value = true;
  };

  const eliminarEmpresaConfirmado = async () => {
    if (!empresaAEliminar.value) return;

    loading.value = true;
    try {
      await router.delete(route('empresas.destroy', empresaAEliminar.value.id), {
        onSuccess: () => {
          notyf.success('Empresa eliminada exitosamente.');
          empresas.value = empresas.value.filter(e => e.id !== empresaAEliminar.value.id);
        },
        onError: () => notyf.error('Error al eliminar la empresa.')
      });
    } catch (error) {
      notyf.error('Ocurrió un error inesperado.');
    } finally {
      isConfirmOpen.value = false;
      loading.value = false;
    }
  };
  </script>
