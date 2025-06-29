<template>
  <Head title="Órdenes de Compra" />
  <div class="ordenes-compra-index">
    <!-- Título de la página -->
    <h1 class="text-2xl font-semibold mb-6">Listado de Órdenes de Compra</h1>

    <!-- Botón de crear orden de compra y campo de búsqueda -->
    <div class="mb-4 flex justify-between items-center">
      <Link :href="route('ordenescompra.create')" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition duration-300">
        Crear Orden de Compra
      </Link>
      <div class="flex flex-col">
        <label for="searchTerm" class="sr-only">Buscar por proveedor, producto o servicio</label>
        <input
          id="searchTerm"
          v-model="searchTerm"
          type="text"
          placeholder="Buscar por proveedor, producto o servicio"
          class="px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
        />
      </div>
    </div>

    <!-- Tabla de órdenes de compra -->
    <div v-if="ordenesCompraFiltradas.length > 0" class="overflow-x-auto bg-white rounded-lg shadow-md">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Proveedor</th>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Productos/Servicios</th>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
          <tr v-for="orden in ordenesCompraFiltradas" :key="orden.id" class="hover:bg-gray-100">

            <!-- Añadido encadenamiento opcional para proveedor -->
             <td class="px-4 py-3 text-sm text-gray-700">{{ orden.id }}</td>  <!-- AÑADIDO: Muestra el ID de la orden -->
            <td class="px-4 py-3 text-sm text-gray-700">{{ orden.proveedor?.nombre_razon_social || 'N/A' }}</td>
            <td class="px-4 py-3 text-sm text-gray-700">
              <ul>
                <!-- Añadido encadenamiento opcional para item.pivot -->
                <li v-for="item in orden.items" :key="item.id + item.tipo" :class="item.tipo === 'producto' ? 'text-blue-600' : 'text-green-600'">
                  {{ item.tipo === 'producto' ? '[Producto]' : '[Servicio]' }} {{ item.nombre }} - ${{ item.pivot?.precio || '0.00' }} (Cantidad: {{ item.pivot?.cantidad || '0' }})
                </li>
              </ul>
            </td>
            <td class="px-4 py-3 text-sm text-gray-700">${{ orden.total }}</td>
            <td class="px-4 py-3 text-sm">
                <span :class="{
                    'px-2 inline-flex text-xs leading-5 font-semibold rounded-full': true,
                    'bg-yellow-100 text-yellow-800': orden.estado === 'pendiente',
                    'bg-green-100 text-green-800': orden.estado === 'recibida',
                    'bg-red-100 text-red-800': orden.estado === 'cancelada',
                }">
                    {{ orden.estado?.charAt(0).toUpperCase() + orden.estado?.slice(1) || 'Desconocido' }}
                </span>
            </td>
            <td class="px-4 py-3 flex space-x-2">
              <button @click="editarOrdenCompra(orden.id)" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">
                Editar
              </button>
              <button @click="confirmarEliminacionOrden(orden.id)" class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600">
                Eliminar
              </button>
              <button @click="verDetallesOrdenCompra(orden)" class="bg-green-500 text-white px-4 py-2 rounded-md hover:bg-green-600">
                Ver Detalles
              </button>
              <button
                v-if="orden.estado === 'pendiente'"
                @click="confirmarRecepcionOrden(orden.id)"
                class="bg-purple-500 text-white px-4 py-2 rounded-md hover:bg-purple-600"
              >
                Recibir Orden
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Mensaje si no hay órdenes de compra -->
    <div v-else class="text-center text-gray-500 mt-4">
      No hay órdenes de compra registradas.
    </div>

    <!-- Spinner de carga -->
    <div v-if="loading" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500"></div>
    </div>

    <!-- Diálogo de confirmación de eliminación -->
    <div v-if="showConfirmationDialog" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white p-6 rounded-lg shadow-lg">
        <p class="mb-4">¿Estás seguro de que deseas eliminar esta orden de compra?</p>
        <div class="flex justify-end">
          <button @click="cancelarEliminacionOrden" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600 mr-2">
            Cancelar
          </button>
          <button @click="eliminarOrdenCompra" class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600">
            Eliminar
          </button>
        </div>
      </div>
    </div>

    <!-- Diálogo de confirmación de recepción de orden -->
    <div v-if="showReceiveConfirmationDialog" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg shadow-lg">
            <p class="mb-4">¿Confirmas la recepción de esta orden de compra y la actualización del stock?</p>
            <div class="flex justify-end">
                <button @click="cancelarRecepcionOrden" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600 mr-2">
                    Cancelar
                </button>
                <button @click="recibirOrdenCompra" class="bg-purple-500 text-white px-4 py-2 rounded-md hover:bg-purple-600">
                    Confirmar Recepción
                </button>
            </div>
        </div>
    </div>

    <!-- Diálogo para mostrar detalles de la orden de compra -->
    <div v-if="showDetailsDialog" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white p-6 rounded-lg shadow-lg w-1/2">
        <h3 class="text-xl font-semibold mb-4">Detalles de la Orden de Compra #{{ selectedOrdenCompra?.id }}</h3>
        <!-- Añadido encadenamiento opcional para proveedor -->
        <p><strong>Proveedor:</strong> {{ selectedOrdenCompra?.proveedor?.nombre_razon_social || 'N/A' }}</p>
        <p><strong>Total:</strong> ${{ selectedOrdenCompra?.total || '0.00' }}</p>
        <p><strong>Estado:</strong>
            <span :class="{
                'px-2 inline-flex text-xs leading-5 font-semibold rounded-full': true,
                'bg-yellow-100 text-yellow-800': selectedOrdenCompra?.estado === 'pendiente',
                'bg-green-100 text-green-800': selectedOrdenCompra?.estado === 'recibida',
                'bg-red-100 text-red-800': selectedOrdenCompra?.estado === 'cancelada',
            }">
                {{ selectedOrdenCompra?.estado?.charAt(0).toUpperCase() + selectedOrdenCompra?.estado?.slice(1) || 'Desconocido' }}
            </span>
        </p>
        <p class="mt-2"><strong>Items:</strong></p>
        <ul>
          <!-- Añadido encadenamiento opcional para item.pivot -->
          <li v-for="item in selectedOrdenCompra?.items" :key="item.id + item.tipo">
            {{ item.tipo === 'producto' ? 'Producto' : 'Servicio' }}: {{ item.nombre }} (Cantidad: {{ item.pivot?.cantidad || '0' }}, Precio: ${{ item.pivot?.precio || '0.00' }})
          </li>
        </ul>
        <div class="flex justify-end mt-4">
          <button @click="cerrarDetallesOrdenCompra" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">
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
import AppLayout from '@/Layouts/AppLayout.vue';

  // Define el layout del dashboard
  defineOptions({ layout: AppLayout });


// Propiedades recibidas del controlador
const props = defineProps({ ordenesCompra: Array });
const searchTerm = ref('');
const loading = ref(false);

// Control de diálogos de confirmación
const showConfirmationDialog = ref(false);
const ordenCompraIdToDelete = ref(null);

const showReceiveConfirmationDialog = ref(false);
const ordenCompraIdToReceive = ref(null);

// Control de diálogo de detalles
const showDetailsDialog = ref(false);
const selectedOrdenCompra = ref(null);

// Configuración de Notyf para notificaciones
const notyf = new Notyf({ duration: 3000, position: { x: 'right', y: 'top' } });

// Variable reactiva local para almacenar las órdenes de compra (para filtrado/actualización UI)
const ordenesCompra = ref([...props.ordenesCompra]);

// Filtrado de órdenes de compra
const ordenesCompraFiltradas = computed(() => {
  const lowerCaseSearchTerm = searchTerm.value.toLowerCase();
  if (!lowerCaseSearchTerm) {
    return ordenesCompra.value;
  }
  return ordenesCompra.value.filter(orden => {
    // Búsqueda por nombre de proveedor
    const proveedorMatch = orden.proveedor?.nombre_razon_social?.toLowerCase().includes(lowerCaseSearchTerm); // Añadido encadenamiento opcional
    // Búsqueda por nombre de producto o servicio en la orden
    const itemMatch = orden.items?.some(item => // Añadido encadenamiento opcional para items
      item.nombre?.toLowerCase().includes(lowerCaseSearchTerm) || // Añadido encadenamiento opcional
      (item.tipo === 'producto' && 'producto'.includes(lowerCaseSearchTerm)) ||
      (item.tipo === 'servicio' && 'servicio'.includes(lowerCaseSearchTerm))
    );
    // Búsqueda por estado de la orden
    const estadoMatch = orden.estado?.toLowerCase().includes(lowerCaseSearchTerm); // Añadido encadenamiento opcional

    return proveedorMatch || itemMatch || estadoMatch;
  });
});

// Función para editar una orden de compra
const editarOrdenCompra = (id) => {
  router.get(route('ordenescompra.edit', id)); // Actualizado a 'ordenescompra.edit'
};

// Funciones para la confirmación de eliminación
const confirmarEliminacionOrden = (id) => {
  ordenCompraIdToDelete.value = id;
  showConfirmationDialog.value = true;
};

const cancelarEliminacionOrden = () => {
  ordenCompraIdToDelete.value = null;
  showConfirmationDialog.value = false;
};

const eliminarOrdenCompra = async () => {
  if (ordenCompraIdToDelete.value) {
    loading.value = true;
    try {
      await router.delete(route('ordenescompra.destroy', ordenCompraIdToDelete.value), { // Actualizado a 'ordenescompra.destroy'
        onSuccess: () => {
          notyf.success('Orden de compra eliminada exitosamente.');
          // Actualiza la lista reactiva después de la eliminación exitosa
          ordenesCompra.value = ordenesCompra.value.filter(orden => orden.id !== ordenCompraIdToDelete.value);
          showConfirmationDialog.value = false;
        },
        onError: (errors) => {
          console.error('Error al eliminar la orden de compra:', errors);
          notyf.error('Error al eliminar la orden de compra.');
        }
      });
    } catch (error) {
      console.error('Ocurrió un error inesperado al eliminar la orden de compra:', error);
      notyf.error('Ocurrió un error inesperado.');
    } finally {
      loading.value = false;
    }
  }
};

// Funciones para la confirmación de recepción
const confirmarRecepcionOrden = (id) => {
    ordenCompraIdToReceive.value = id;
    showReceiveConfirmationDialog.value = true;
};

const cancelarRecepcionOrden = () => {
    ordenCompraIdToReceive.value = null;
    showReceiveConfirmationDialog.value = false;
};

const recibirOrdenCompra = async () => {
    if (ordenCompraIdToReceive.value) {
        loading.value = true;
        try {
            // Nota: Esta ruta debe coincidir con la definida en tu web.php
            await router.post(route('ordenescompra.recibir', ordenCompraIdToReceive.value), { // Actualizado a 'ordenescompra.recibir'
                onSuccess: () => {
                    notyf.success('Orden de compra recibida y stock actualizado exitosamente.');
                    // Actualiza el estado de la orden en la UI (puedes recargar o actualizar el objeto)
                    const index = ordenesCompra.value.findIndex(o => o.id === ordenCompraIdToReceive.value);
                    if (index !== -1) {
                        ordenesCompra.value[index].estado = 'recibida';
                    }
                    showReceiveConfirmationDialog.value = false;
                },
                onError: (errors) => {
                    console.error('Error al recibir la orden de compra:', errors);
                    notyf.error('Error al recibir la orden de compra.');
                }
            });
        } catch (error) {
            console.error('Ocurrió un error inesperado al recibir la orden de compra:', error);
            notyf.error('Ocurrió un error inesperado.');
        } finally {
            loading.value = false;
        }
    }
};

// Funciones para mostrar detalles de la orden de compra
const verDetallesOrdenCompra = (orden) => {
  selectedOrdenCompra.value = orden;
  showDetailsDialog.value = true;
};

const cerrarDetallesOrdenCompra = () => {
  selectedOrdenCompra.value = null;
  showDetailsDialog.value = false;
};
</script>

<style scoped>
/* Estilos personalizados */
.text-blue-600 {
  color: #2563eb; /* Color para productos */
}
.text-green-600 {
  color: #16a34a; /* Color para servicios */
}
/* Estilos para el estado (similar a los de Notyf) */
.bg-yellow-100 { background-color: #fef9c3; }
.text-yellow-800 { color: #92400e; }
.bg-green-100 { background-color: #dcfce7; }
.text-green-800 { color: #166534; }
.bg-red-100 { background-color: #fee2e2; }
.text-red-800 { color: #991b1b; }
</style>
