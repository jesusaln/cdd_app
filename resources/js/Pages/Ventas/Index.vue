<template>
    <Head title="Ventas" />
    <div class="ventas-index">
      <!-- Título de la página -->
      <h1 class="text-2xl font-semibold mb-6">Listado de Ventas</h1>

      <!-- Botón de crear venta y campo de búsqueda -->
      <div class="mb-4 flex justify-between items-center">
        <Link :href="route('ventas.create')" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition duration-300">
          Crear Venta
        </Link>
        <input
          v-model="searchTerm"
          type="text"
          placeholder="Buscar por cliente o producto"
          class="px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
        />
      </div>

      <!-- Tabla de ventas -->
      <div v-if="ventasFiltradas.length > 0" class="overflow-x-auto bg-white rounded-lg shadow-md">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Cliente</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Productos</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr v-for="venta in ventasFiltradas" :key="venta.id" class="hover:bg-gray-100">
              <td class="px-4 py-3 text-sm text-gray-700">{{ venta.id }}</td>
              <td class="px-4 py-3 text-sm text-gray-700">{{ venta.cliente.nombre_razon_social }}</td>
              <td class="px-4 py-3 text-sm text-gray-700">
                <ul>
                  <li v-for="producto in venta.productos" :key="producto.id">
                    {{ producto.nombre }} - ${{ producto.pivot.precio }} (Cantidad: {{ producto.pivot.cantidad }})
                  </li>
                </ul>
              </td>
              <td class="px-4 py-3 text-sm text-gray-700">${{ venta.total }}</td>
              <td class="px-4 py-3 text-sm text-gray-700">{{ venta.estado }}</td>
              <td class="px-4 py-3 flex space-x-2">
                <button @click="editarVenta(venta.id)" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">
                  Editar
                </button>
                <button @click="confirmarEliminacion(venta.id)" class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600">
                  Eliminar
                </button>
                <button @click="verDetalles(venta)" class="bg-green-500 text-white px-4 py-2 rounded-md hover:bg-green-600">
                  Ver Detalles
                </button>
                <button @click="generarPDFVenta(venta)" class="bg-purple-500 text-white px-4 py-2 rounded-md hover:bg-purple-600">
                  Generar PDF
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Mensaje si no hay ventas -->
      <div v-else class="text-center text-gray-500 mt-4">
        No hay ventas registradas.
      </div>

      <!-- Spinner de carga -->
      <div v-if="loading" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500"></div>
      </div>

      <!-- Diálogo de confirmación de eliminación -->
      <div v-if="showConfirmationDialog" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg shadow-lg">
          <p class="mb-4">¿Estás seguro de que deseas eliminar esta venta?</p>
          <div class="flex justify-end">
            <button @click="cancelarEliminacion" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600 mr-2">
              Cancelar
            </button>
            <button @click="eliminarVenta" class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600">
              Eliminar
            </button>
          </div>
        </div>
      </div>

      <!-- Diálogo para mostrar detalles de la venta -->
      <div v-if="showDetailsDialog" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg shadow-lg w-1/2">
          <Show :venta="selectedVenta" />
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
  import Show from './Show.vue'; // Asegúrate de que la ruta sea correcta
  import { generarPDF } from '@/Utils/pdfGenerator'; // Asegúrate de que la ruta sea correcta




  // Define el layout del dashboard
  defineOptions({ layout: Dashboard });

  // Propiedades
  const props = defineProps({ ventas: Array });
  const searchTerm = ref('');
  const loading = ref(false);
  const showConfirmationDialog = ref(false);
  const ventaIdToDelete = ref(null);
  const showDetailsDialog = ref(false);
  const selectedVenta = ref(null);

  // Configuración de Notyf para notificaciones
  const notyf = new Notyf({ duration: 3000, position: { x: 'right', y: 'top' } });

  // Variable reactiva local para almacenar las ventas
  const ventas = ref([...props.ventas]);

  // Filtrado de ventas
  const ventasFiltradas = computed(() => {
    return ventas.value.filter(venta => {
      return venta.cliente.nombre_razon_social.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
             venta.productos.some(producto => producto.nombre.toLowerCase().includes(searchTerm.value.toLowerCase()));
    });
  });

  // Función para editar una venta
  const editarVenta = (id) => {
    router.get(`/ventas/${id}/edit`);
  };

  // Función para confirmar la eliminación de una venta
  const confirmarEliminacion = (id) => {
    ventaIdToDelete.value = id;
    showConfirmationDialog.value = true;
  };

  // Función para cancelar la eliminación
  const cancelarEliminacion = () => {
    ventaIdToDelete.value = null;
    showConfirmationDialog.value = false;
  };

  // Función para eliminar una venta
  const eliminarVenta = async () => {
    if (ventaIdToDelete.value) {
      loading.value = true;
      try {
        await router.delete(`/ventas/${ventaIdToDelete.value}`, {
          onSuccess: () => {
            notyf.success('Venta eliminada exitosamente.');
            ventas.value = ventas.value.filter(venta => venta.id !== ventaIdToDelete.value);
            showConfirmationDialog.value = false;
          },
          onError: () => notyf.error('Error al eliminar la venta.')
        });
      } catch (error) {
        notyf.error('Ocurrió un error inesperado.');
      } finally {
        loading.value = false;
      }
    }
  };

  // Función para mostrar detalles de la venta
  const verDetalles = (venta) => {
    selectedVenta.value = venta;
    showDetailsDialog.value = true;
  };

  // Función para cerrar el diálogo de detalles
  const cerrarDetalles = () => {
    selectedVenta.value = null;
    showDetailsDialog.value = false;
  };

  // Función para generar el PDF de la venta
  const generarPDFVenta = (venta) => {
    generarPDF('Nota de Venta', venta);
  };
  </script>

  <style scoped>
  /* Aquí van tus estilos personalizados */
  </style>
