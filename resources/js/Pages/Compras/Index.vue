<template>
    <Head title="Compras" />
    <div class="compras-index">
      <!-- Título de la página -->
      <h1 class="text-2xl font-semibold mb-6">Listado de Compras</h1>

      <!-- Botón de crear compra y campo de búsqueda -->
      <div class="mb-4 flex justify-between items-center">
        <Link :href="route('compras.create')" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition duration-300">
          Crear Compra
        </Link>
        <input
          v-model="searchTerm"
          type="text"
          placeholder="Buscar por proveedor o producto"
          class="px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
        />
      </div>

      <!-- Tabla de compras -->
      <div v-if="comprasFiltradas.length > 0" class="overflow-x-auto bg-white rounded-lg shadow-md">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Proveedor</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Productos</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr v-for="compra in comprasFiltradas" :key="compra.id" class="hover:bg-gray-100">
              <td class="px-4 py-3 text-sm text-gray-700">{{ compra.id }}</td>
              <td class="px-4 py-3 text-sm text-gray-700">{{ compra.proveedor.nombre }}</td>
              <td class="px-4 py-3 text-sm text-gray-700">
                <ul>
                  <li v-for="producto in compra.productos" :key="producto.id">
                    {{ producto.nombre }} - ${{ producto.pivot.precio }} (Cantidad: {{ producto.pivot.cantidad }})
                  </li>
                </ul>
              </td>
              <td class="px-4 py-3 text-sm text-gray-700">${{ compra.total }}</td>
              <td class="px-4 py-3 flex space-x-2">
                <button @click="editarCompra(compra.id)" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">
                  Editar
                </button>
                <button @click="confirmarEliminacion(compra.id)" class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600">
                  Eliminar
                </button>
                <button @click="verDetalles(compra)" class="bg-green-500 text-white px-4 py-2 rounded-md hover:bg-green-600">
                  Ver Detalles
                </button>
                <button @click="generarPDFCompra(compra)" class="bg-purple-500 text-white px-4 py-2 rounded-md hover:bg-purple-600">
                  Generar PDF
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Mensaje si no hay compras -->
      <div v-else class="text-center text-gray-500 mt-4">
        No hay compras registradas.
      </div>

      <!-- Spinner de carga -->
      <div v-if="loading" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500"></div>
      </div>

      <!-- Diálogo de confirmación de eliminación -->
      <div v-if="showConfirmationDialog" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg shadow-lg">
          <p class="mb-4">¿Estás seguro de que deseas eliminar esta compra?</p>
          <div class="flex justify-end">
            <button @click="cancelarEliminacion" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600 mr-2">
              Cancelar
            </button>
            <button @click="eliminarCompra" class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600">
              Eliminar
            </button>
          </div>
        </div>
      </div>

      <!-- Diálogo para mostrar detalles de la compra -->
      <div v-if="showDetailsDialog" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg shadow-lg w-1/2">
          <Show :compra="selectedCompra"/>
          <div class="flex justify-end mt-4">
            <button @click="cerrarDetalles" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">
              Cerrar
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
  import Dashboard from '@/Pages/Dashboard.vue';
  import { generarPDF } from '@/Utils/pdfGenerator';  // Asegúrate de que la ruta sea correcta
  import Show from './Show.vue'; // Asegúrate de que la ruta sea correcta

  // Define el layout del dashboard
  defineOptions({ layout: Dashboard });

  // Propiedades
  const props = defineProps({ compras: Array });
  const searchTerm = ref('');
  const loading = ref(false);
  const showConfirmationDialog = ref(false);
  const compraIdToDelete = ref(null);
  const showDetailsDialog = ref(false);
  const selectedCompra = ref(null);

  // Configuración de Notyf para notificaciones
  const notyf = new Notyf({ duration: 3000, position: { x: 'right', y: 'top' } });

  // Variable reactiva local para almacenar las compras
  const compras = ref([...props.compras]);

  // Filtrado de compras
  const comprasFiltradas = computed(() => {
    return compras.value.filter(compra => {
      return compra.proveedor.nombre.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
             compra.productos.some(producto => producto.nombre.toLowerCase().includes(searchTerm.value.toLowerCase()));
    });
  });

  // Función para editar una compra
  const editarCompra = (id) => {
    router.get(`/compras/${id}/edit`);
  };

  // Función para confirmar la eliminación de una compra
  const confirmarEliminacion = (id) => {
    compraIdToDelete.value = id;
    showConfirmationDialog.value = true;
  };

  // Función para cancelar la eliminación
  const cancelarEliminacion = () => {
    compraIdToDelete.value = null;
    showConfirmationDialog.value = false;
  };

  // Función para eliminar una compra
  const eliminarCompra = async () => {
    if (compraIdToDelete.value) {
      loading.value = true;
      try {
        await router.delete(`/compras/${compraIdToDelete.value}`, {
          onSuccess: () => {
            notyf.success('Compra eliminada exitosamente.');
            compras.value = compras.value.filter(compra => compra.id !== compraIdToDelete.value);
            showConfirmationDialog.value = false;
          },
          onError: () => notyf.error('Error al eliminar la compra.')
        });
      } catch (error) {
        notyf.error('Ocurrió un error inesperado.');
      } finally {
        loading.value = false;
      }
    }
  };

  // Función para mostrar detalles de la compra
  const verDetalles = (compra) => {
    selectedCompra.value = compra;
    showDetailsDialog.value = true;
  };

  // Función para cerrar el diálogo de detalles
  const cerrarDetalles = () => {
    selectedCompra.value = null;
    showDetailsDialog.value = false;
  };



  // Función para generar el PDF de la compra
  const generarPDFCompra = (compra) => {
    generarPDF('Compra', compra);
  };
  </script>

  <style scoped>
  /* Aquí van tus estilos personalizados */
  </style>
