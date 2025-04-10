<template>
    <Head title="Cotizaciones" />
    <div class="cotizaciones-index">
      <h1 class="text-2xl font-semibold mb-6">Listado de Cotizaciones</h1>

      <!-- Botón de crear y campo de búsqueda -->
      <div class="mb-4 flex justify-between items-center">
        <Link :href="route('cotizaciones.create')" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition">
          Crear Cotización
        </Link>
        <div class="flex flex-col">
          <label for="searchTerm" class="sr-only">Buscar</label>
          <input
            id="searchTerm"
            v-model="searchTerm"
            type="text"
            placeholder="Buscar por cliente, producto o servicio"
            class="px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>
      </div>

      <!-- Tabla de cotizaciones -->
      <div v-if="cotizacionesFiltradas.length" class="overflow-x-auto bg-white rounded-lg shadow-md">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Cliente</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Productos/Servicios</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr v-for="cotizacion in cotizacionesFiltradas" :key="cotizacion.id" class="hover:bg-gray-100">
                <td class="px-4 py-3 text-sm text-gray-700">{{ formatearFechaHora(cotizacion.fecha) }}</td>
              <td class="px-4 py-3 text-sm text-gray-700">{{ cotizacion.id }}</td>
              <td class="px-4 py-3 text-sm text-gray-700">{{ cotizacion.cliente.nombre_razon_social }}</td>

              <td class="px-4 py-3 text-sm text-gray-700">
                <ul>
                  <li
                    v-for="item in cotizacion.productos"
                    :key="item.id"
                    :class="item.tipo === 'producto' ? 'text-blue-600' : 'text-green-600'"
                  >
                    [{{ item.tipo === 'producto' ? 'Producto' : 'Servicio' }}] {{ item.nombre }} - ${{ item.pivot.precio }} (Cantidad: {{ item.pivot.cantidad }})
                  </li>
                </ul>
              </td>
              <td class="px-4 py-3 text-sm text-gray-700">${{ cotizacion.total }}</td>
              <td class="px-4 py-3 flex flex-wrap gap-2">
                <button @click="editarCotizacion(cotizacion.id)" class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600">Editar</button>
                <button @click="confirmarEliminacion(cotizacion.id)" class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">Eliminar</button>
                <button @click="verDetalles(cotizacion)" class="bg-green-500 text-white px-3 py-1 rounded hover:bg-green-600">Ver Detalles</button>
                <button @click="generarPDFVenta(cotizacion)" class="bg-purple-500 text-white px-3 py-1 rounded hover:bg-purple-600">Generar PDF</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-else class="text-center text-gray-500 mt-4">No hay cotizaciones registradas.</div>

      <!-- Spinner -->
      <div v-if="loading" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500"></div>
      </div>

      <!-- Confirmación de eliminación -->
      <div v-if="showConfirmationDialog" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg shadow-lg">
          <p class="mb-4">¿Estás seguro de que deseas eliminar esta cotización?</p>
          <div class="flex justify-end">
            <button @click="cancelarEliminacion" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600 mr-2">Cancelar</button>
            <button @click="eliminarCotizacion" class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600">Eliminar</button>
          </div>
        </div>
      </div>

      <!-- Detalles -->
      <div v-if="showDetailsDialog" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-2xl">
          <Show :cotizacion="selectedCotizacion" @convertir-a-pedido="handleConvertirAPedido" />
          <div class="flex justify-end mt-4">
            <button @click="cerrarDetalles" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">Cerrar</button>
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
  import { generarPDF } from '@/Utils/pdfGenerator';
  import Show from './Show.vue';
  import AppLayout from '@/Layouts/AppLayout.vue';

  defineOptions({ layout: AppLayout });

  const props = defineProps({ cotizaciones: Array });

  const searchTerm = ref('');
  const loading = ref(false);
  const showConfirmationDialog = ref(false);
  const cotizacionIdToDelete = ref(null);
  const showDetailsDialog = ref(false);
  const selectedCotizacion = ref(null);
  const notyf = new Notyf({ duration: 3000, position: { x: 'right', y: 'top' } });

  const cotizaciones = ref([...props.cotizaciones]);

  const cotizacionesFiltradas = computed(() => {
    return cotizaciones.value.filter(cotizacion =>
      cotizacion.cliente.nombre_razon_social.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
      cotizacion.productos.some(item =>
        item.nombre.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
        item.tipo.includes(searchTerm.value.toLowerCase())
      )
    );
  });

  const formatearFechaHora = (fechaHora) => {
    const fecha = new Date(fechaHora);
    return fecha.toLocaleString('es-MX', { dateStyle: 'medium', timeStyle: 'short' });
  };

  const editarCotizacion = (id) => router.get(`/cotizaciones/${id}/edit`);
  const confirmarEliminacion = (id) => { cotizacionIdToDelete.value = id; showConfirmationDialog.value = true; };
  const cancelarEliminacion = () => { cotizacionIdToDelete.value = null; showConfirmationDialog.value = false; };

  const eliminarCotizacion = async () => {
    if (!cotizacionIdToDelete.value) return;
    loading.value = true;
    try {
      await router.delete(`/cotizaciones/${cotizacionIdToDelete.value}`, {
        onSuccess: () => {
          notyf.success('Cotización eliminada exitosamente.');
          cotizaciones.value = cotizaciones.value.filter(c => c.id !== cotizacionIdToDelete.value);
          showConfirmationDialog.value = false;
        },
        onError: () => notyf.error('Error al eliminar la cotización.')
      });
    } catch (e) {
      notyf.error('Ocurrió un error inesperado.');
    } finally {
      loading.value = false;
    }
  };

  const verDetalles = (cotizacion) => { selectedCotizacion.value = cotizacion; showDetailsDialog.value = true; };
  const cerrarDetalles = () => { selectedCotizacion.value = null; showDetailsDialog.value = false; };

  const handleConvertirAPedido = async (cotizacionData) => {
    try {
      await router.post(`/cotizaciones/${cotizacionData.id}/convertir-a-pedido`, {
        onSuccess: () => {
          alert('Conversión correcta. ¿Deseas ir al índice de pedidos?');
          cerrarDetalles();
        },
        onError: (errors) => console.error('Error al convertir:', errors)
      });
    } catch (e) {
      console.error('Error inesperado:', e);
    }
  };

  const generarPDFVenta = (cotizacion) => generarPDF('Cotización', cotizacion);
  </script>

  <style scoped>
  .text-blue-600 { color: #2563eb; }
  .text-green-600 { color: #16a34a; }
  </style>
