<template>
    <Head title="Cotizaciones" />
    <div class="cotizaciones-index">
      <!-- Título de la página -->
      <h1 class="text-2xl font-semibold mb-6">Listado de Cotizaciones</h1>

      <!-- Botón de crear cotización y campo de búsqueda -->
      <div class="mb-4 flex justify-between items-center">
        <Link :href="route('cotizaciones.create')" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition duration-300">
          Crear Cotización
        </Link>
        <input
          v-model="searchTerm"
          type="text"
          placeholder="Buscar por cliente o producto"
          class="px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
        />
      </div>

      <!-- Tabla de cotizaciones -->
      <div v-if="cotizacionesFiltradas.length > 0" class="overflow-x-auto bg-white rounded-lg shadow-md">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Cliente</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Productos</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr v-for="cotizacion in cotizacionesFiltradas" :key="cotizacion.id" class="hover:bg-gray-100">
              <td class="px-4 py-3 text-sm text-gray-700">{{ cotizacion.id }}</td>
              <td class="px-4 py-3 text-sm text-gray-700">{{ cotizacion.cliente.nombre_razon_social }}</td>
              <td class="px-4 py-3 text-sm text-gray-700">
                <ul>
                  <li v-for="producto in cotizacion.productos" :key="producto.id">
                    {{ producto.nombre }} - ${{ producto.pivot.precio }} (Cantidad: {{ producto.pivot.cantidad }})
                  </li>
                </ul>
              </td>
              <td class="px-4 py-3 text-sm text-gray-700">${{ cotizacion.total }}</td>
              <td class="px-4 py-3 flex space-x-2">
                <button @click="editarCotizacion(cotizacion.id)" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">
                  Editar
                </button>
                <button @click="confirmarEliminacion(cotizacion.id)" class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600">
                  Eliminar
                </button>
                <button @click="verDetalles(cotizacion)" class="bg-green-500 text-white px-4 py-2 rounded-md hover:bg-green-600">
                  Ver Detalles
                </button>
                <button @click="generarPDFVenta(cotizacion)" class="bg-purple-500 text-white px-4 py-2 rounded-md hover:bg-purple-600">
                  Generar PDF
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Mensaje si no hay cotizaciones -->
      <div v-else class="text-center text-gray-500 mt-4">
        No hay cotizaciones registradas.
      </div>

      <!-- Spinner de carga -->
      <div v-if="loading" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500"></div>
      </div>

      <!-- Diálogo de confirmación de eliminación -->
      <div v-if="showConfirmationDialog" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg shadow-lg">
          <p class="mb-4">¿Estás seguro de que deseas eliminar esta cotización?</p>
          <div class="flex justify-end">
            <button @click="cancelarEliminacion" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600 mr-2">
              Cancelar
            </button>
            <button @click="eliminarCotizacion" class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600">
              Eliminar
            </button>
          </div>
        </div>
      </div>

      <!-- Diálogo para mostrar detalles de la cotización -->
      <div v-if="showDetailsDialog" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg shadow-lg w-1/2">
          <Show :cotizacion="selectedCotizacion" @convertir-a-pedido="handleConvertirAPedido" />
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
  const props = defineProps({ cotizaciones: Array });
  const searchTerm = ref('');
  const loading = ref(false);
  const showConfirmationDialog = ref(false);
  const cotizacionIdToDelete = ref(null);
  const showDetailsDialog = ref(false);
  const selectedCotizacion = ref(null);



  // Configuración de Notyf para notificaciones
  const notyf = new Notyf({ duration: 3000, position: { x: 'right', y: 'top' } });

  // Variable reactiva local para almacenar las cotizaciones
  const cotizaciones = ref([...props.cotizaciones]);

  // Filtrado de cotizaciones
  const cotizacionesFiltradas = computed(() => {
    return cotizaciones.value.filter(cotizacion => {
      return cotizacion.cliente.nombre_razon_social.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
             cotizacion.productos.some(producto => producto.nombre.toLowerCase().includes(searchTerm.value.toLowerCase()));
    });
  });

  // Función para editar una cotización
  const editarCotizacion = (id) => {
    router.get(`/cotizaciones/${id}/edit`);
  };

  // Función para confirmar la eliminación de una cotización
  const confirmarEliminacion = (id) => {
    cotizacionIdToDelete.value = id;
    showConfirmationDialog.value = true;
  };

  // Función para cancelar la eliminación
  const cancelarEliminacion = () => {
    cotizacionIdToDelete.value = null;
    showConfirmationDialog.value = false;
  };

  // Función para eliminar una cotización
  const eliminarCotizacion = async () => {
    if (cotizacionIdToDelete.value) {
      loading.value = true;
      try {
        await router.delete(`/cotizaciones/${cotizacionIdToDelete.value}`, {
          onSuccess: () => {
            notyf.success('Cotización eliminada exitosamente.');
            cotizaciones.value = cotizaciones.value.filter(cotizacion => cotizacion.id !== cotizacionIdToDelete.value);
            showConfirmationDialog.value = false;
          },
          onError: () => notyf.error('Error al eliminar la cotización.')
        });
      } catch (error) {
        notyf.error('Ocurrió un error inesperado.');
      } finally {
        loading.value = false;
      }
    }
  };

  // Función para mostrar detalles de la cotización
  const verDetalles = (cotizacion) => {
    selectedCotizacion.value = cotizacion;
    showDetailsDialog.value = true;
  };

  // Función para cerrar el diálogo de detalles
  const cerrarDetalles = () => {
    selectedCotizacion.value = null;
    showDetailsDialog.value = false;
  };

  // Función para manejar la conversión a pedido
  const handleConvertirAPedido = async (cotizacionData) => {
    try {
      await router.post(`/cotizaciones/${cotizacionData.id}/convertir-a-pedido`, {
        onSuccess: () => {
          // Mostrar alerta de éxito
          alert('Conversión correcta. ¿Deseas ir al índice de pedidos?');
          // Opcional: Cerrar el diálogo de detalles
          cerrarDetalles();
        },
        onError: (errors) => {
          // Manejar el error, por ejemplo, mostrar una notificación de error
          console.error('Error al convertir la cotización a pedido:', errors);
        }
      });
    } catch (error) {
      console.error('Ocurrió un error inesperado:', error);
    }
  };

  // Función para generar el PDF de la cotización
  const generarPDFVenta = (cotizacion) => {
    generarPDF('Cotización', cotizacion);
  };
  </script>

  <style scoped>
  /* Aquí van tus estilos personalizados */
  </style>
