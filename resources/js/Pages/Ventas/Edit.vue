<template>
  <Head title="Editar Venta" />
  <div class="ventas-edit max-w-6xl mx-auto p-6 bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen">
    <div class="bg-white rounded-xl shadow-lg p-8">
      <!-- Header con información de la venta -->
      <div class="mb-8 border-b pb-6">
        <div class="flex justify-between items-start">
          <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">
              Editar Venta #{{ props.venta.id }}
            </h1>
            <div class="flex gap-4 text-sm text-gray-600">
              <span>Fecha: {{ formatDate(props.venta.created_at) }}</span>
              <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full">
                Estado: {{ props.venta.estado || 'Pendiente' }}
              </span>
            </div>
          </div>
          <div class="text-right">
            <p class="text-sm text-gray-600">Total Original</p>
            <p class="text-2xl font-bold text-green-600">${{ props.venta.total }}</p>
          </div>
        </div>
      </div>

      <form @submit.prevent="actualizarVenta" class="space-y-8">
        <!-- Información del Cliente -->
        <div class="bg-gray-50 p-6 rounded-lg">
          <h2 class="text-lg font-semibold mb-4 text-gray-800 flex items-center">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
            </svg>
            Cliente
          </h2>
          <div class="form-group relative">
            <input
              v-model="buscarCliente"
              type="text"
              placeholder="Buscar cliente..."
              @input="debouncedBuscarCliente"
              @keydown.enter.prevent="seleccionarPrimerCliente"
              @keydown.arrow-down.prevent="navegarClientes(1)"
              @keydown.arrow-up.prevent="navegarClientes(-1)"
              @focus="mostrarClientes = true"
              @blur="ocultarClientesDespuesDeTiempo"
              class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
              :class="{ 'border-red-400': !form.cliente_id && form.errors.cliente_id }"
            />
            <div v-if="mostrarClientes && clientesFiltrados.length > 0"
                 class="absolute z-20 bg-white border-2 border-gray-200 rounded-lg shadow-lg w-full max-h-60 overflow-y-auto mt-2">
              <div
                v-for="(cliente, index) in clientesFiltrados"
                :key="cliente.id"
                @click="seleccionarCliente(cliente)"
                @keydown.enter="seleccionarCliente(cliente)"
                tabindex="0"
                class="px-4 py-3 cursor-pointer hover:bg-blue-50 border-b last:border-b-0 transition-colors"
                :class="{ 'bg-blue-100': index === clienteSeleccionadoIndex }"
              >
                <div class="font-medium">{{ cliente.nombre_razon_social }}</div>
                <div class="text-sm text-gray-600">{{ cliente.email || cliente.telefono || 'Sin contacto' }}</div>
              </div>
            </div>
            <p v-if="!form.cliente_id && form.errors.cliente_id" class="text-red-500 text-sm mt-2">
              {{ form.errors.cliente_id }}
            </p>
          </div>
        </div>

        <!-- Búsqueda y Agregar Productos -->
        <div class="bg-gray-50 p-6 rounded-lg">
          <h2 class="text-lg font-semibold mb-4 text-gray-800 flex items-center">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 2L3 7v11a2 2 0 002 2h10a2 2 0 002-2V7l-7-5z" clip-rule="evenodd" />
            </svg>
            Productos
          </h2>
          <div class="form-group relative">
            <div class="relative">
              <input
                v-model="buscarProducto"
                type="text"
                placeholder="Buscar productos por nombre o código..."
                @input="debouncedBuscarProducto"
                @keydown.enter.prevent="agregarPrimerProducto"
                @keydown.arrow-down.prevent="navegarProductos(1)"
                @keydown.arrow-up.prevent="navegarProductos(-1)"
                @focus="mostrarProductos = true"
                @blur="ocultarProductosDespuesDeTiempo"
                class="w-full px-4 py-3 pl-10 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
              />
              <svg class="absolute left-3 top-3.5 w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
              </svg>
            </div>
            <div v-if="mostrarProductos && productosFiltrados.length > 0"
                 class="absolute z-20 bg-white border-2 border-gray-200 rounded-lg shadow-lg w-full max-h-60 overflow-y-auto mt-2">
              <div
                v-for="(producto, index) in productosFiltrados"
                :key="producto.id"
                @click="agregarProducto(producto)"
                @keydown.enter="agregarProducto(producto)"
                tabindex="0"
                class="px-4 py-3 cursor-pointer hover:bg-blue-50 border-b last:border-b-0 transition-colors"
                :class="{ 'bg-blue-100': index === productoSeleccionadoIndex }"
              >
                <div class="flex justify-between items-center">
                  <div>
                    <div class="font-medium">{{ producto.nombre }}</div>
                    <div class="text-sm text-gray-600">
                      Código: {{ producto.codigo || 'N/A' }} |
                      Precio: ${{ producto.precio_venta }}
                    </div>
                  </div>
                  <div class="text-right">
                    <span class="px-2 py-1 rounded-full text-xs font-medium"
                          :class="stockClass(adjustedStock[producto.id] ?? producto.stock)">
                      Stock: {{ adjustedStock[producto.id] ?? producto.stock }}
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Lista de productos seleccionados -->
        <div v-if="selectedProducts.length > 0" class="bg-white border-2 border-gray-200 rounded-lg overflow-hidden">
          <div class="bg-gray-100 px-6 py-4 border-b">
            <div class="flex justify-between items-center">
              <h3 class="text-lg font-semibold text-gray-800">
                Productos Seleccionados ({{ selectedProducts.length }})
              </h3>
              <div class="flex gap-2">
                <button type="button" @click="aplicarDescuentoGlobal"
                        class="px-3 py-1 bg-yellow-500 text-white text-sm rounded-md hover:bg-yellow-600 transition-colors">
                  Descuento Global
                </button>
                <button type="button" @click="limpiarProductos"
                        class="px-3 py-1 bg-red-500 text-white text-sm rounded-md hover:bg-red-600 transition-colors">
                  Limpiar Todo
                </button>
              </div>
            </div>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-4 py-3 text-left font-medium text-gray-700">Producto</th>
                  <th class="px-4 py-3 text-center font-medium text-gray-700">Cantidad</th>
                  <th class="px-4 py-3 text-center font-medium text-gray-700">Precio Unit.</th>
                  <th class="px-4 py-3 text-center font-medium text-gray-700">Descuento</th>
                  <th class="px-4 py-3 text-center font-medium text-gray-700">Subtotal</th>
                  <th class="px-4 py-3 text-center font-medium text-gray-700">Acciones</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200">
                <tr v-for="(productoId, index) in selectedProducts" :key="index"
                    class="hover:bg-gray-50 transition-colors">
                  <td class="px-4 py-4">
                    <div class="flex items-center">
                      <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                        <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                          <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd" />
                        </svg>
                      </div>
                      <div>
                        <div class="font-medium text-gray-900">{{ getProductById(productoId)?.nombre }}</div>
                        <div class="text-sm text-gray-500">
                          Stock disponible: {{ (getProductById(productoId)?.stock || 0) + (originalQuantities[productoId] || 0) }}
                        </div>
                      </div>
                    </div>
                  </td>
                  <td class="px-4 py-4 text-center">
                    <div class="flex items-center justify-center">
                      <button type="button" @click="cambiarCantidad(productoId, -1)"
                              class="w-8 h-8 bg-gray-200 rounded-l-md hover:bg-gray-300 transition-colors">-</button>
                      <input
                        v-model.number="quantities[productoId]"
                        type="number"
                        class="w-16 px-2 py-1 border-t border-b text-center focus:ring-2 focus:ring-blue-500"
                        min="1"
                        :max="getProductById(productoId)?.stock + (originalQuantities[productoId] || 0)"
                        @input="adjustStock(productoId)"
                      />
                      <button type="button" @click="cambiarCantidad(productoId, 1)"
                              class="w-8 h-8 bg-gray-200 rounded-r-md hover:bg-gray-300 transition-colors">+</button>
                    </div>
                  </td>
                  <td class="px-4 py-4 text-center">
                    <input
                      v-model.number="prices[productoId]"
                      type="number"
                      class="w-24 px-2 py-1 border rounded-md text-center focus:ring-2 focus:ring-blue-500"
                      min="0"
                      step="0.01"
                      @input="calcularTotal"
                    />
                  </td>
                  <td class="px-4 py-4 text-center">
                    <input
                      v-model.number="discounts[productoId]"
                      type="number"
                      class="w-20 px-2 py-1 border rounded-md text-center focus:ring-2 focus:ring-blue-500"
                      min="0"
                      max="100"
                      step="0.1"
                      @input="calcularTotal"
                      placeholder="0%"
                    />
                    <span class="text-xs text-gray-500">%</span>
                  </td>
                  <td class="px-4 py-4 text-center font-medium">
                    ${{ calcularSubtotal(productoId).toFixed(2) }}
                  </td>
                  <td class="px-4 py-4 text-center">
                    <div class="flex justify-center gap-2">
                      <button type="button" @click="duplicarProducto(productoId)"
                              class="text-blue-500 hover:text-blue-700 transition-colors" title="Duplicar">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                          <path d="M7 7h8v8H7V7z M5 5v12h12V5H5z M3 3h2v2H3V3z" />
                        </svg>
                      </button>
                      <button type="button" @click="eliminarProducto(productoId)"
                              class="text-red-500 hover:text-red-700 transition-colors" title="Eliminar">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                          <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Resumen de totales -->
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 p-6 rounded-lg">
          <h3 class="text-lg font-semibold mb-4 text-gray-800">Resumen de la Venta</h3>
          <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white p-4 rounded-lg shadow-sm">
              <p class="text-sm text-gray-600">Subtotal</p>
              <p class="text-lg font-semibold text-gray-900">${{ subtotal.toFixed(2) }}</p>
            </div>
            <div class="bg-white p-4 rounded-lg shadow-sm">
              <p class="text-sm text-gray-600">Descuento Total</p>
              <p class="text-lg font-semibold text-red-600">-${{ totalDescuento.toFixed(2) }}</p>
            </div>
            <div class="bg-white p-4 rounded-lg shadow-sm">
              <p class="text-sm text-gray-600">Impuestos ({{ taxRate }}%)</p>
              <p class="text-lg font-semibold text-yellow-600">${{ impuestos.toFixed(2) }}</p>
            </div>
            <div class="bg-white p-4 rounded-lg shadow-sm border-2 border-green-200">
              <p class="text-sm text-gray-600">Total Final</p>
              <p class="text-2xl font-bold text-green-600">${{ form.total }}</p>
            </div>
          </div>

          <!-- Configuración de impuestos -->
          <div class="mt-4 pt-4 border-t border-gray-200">
            <label class="flex items-center text-sm">
              <input
                v-model="includeTax"
                type="checkbox"
                @change="calcularTotal"
                class="mr-2 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
              />
              Incluir impuestos (
              <input
                v-model.number="taxRate"
                type="number"
                class="mx-1 w-12 px-1 text-center border rounded"
                min="0"
                max="100"
                step="0.1"
                @input="calcularTotal"
              />
              %)
            </label>
          </div>
        </div>

        <!-- Notas adicionales -->
        <div class="form-group">
          <label class="block text-sm font-medium text-gray-700 mb-2">Notas adicionales</label>
          <textarea
            v-model="form.notas"
            rows="3"
            class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
            placeholder="Agregar notas o comentarios sobre la venta..."
          ></textarea>
        </div>

        <!-- Botones de acción -->
        <div class="flex flex-col sm:flex-row justify-between items-center gap-4 pt-6 border-t">
          <div class="flex gap-2">
            <button type="button" @click="previsualizarVenta"
                    class="px-4 py-2 bg-purple-500 text-white rounded-lg hover:bg-purple-600 transition-colors">
              Vista Previa
            </button>
            <button type="button" @click="guardarBorrador"
                    class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors">
              Guardar Borrador
            </button>
          </div>

          <div class="flex gap-4">
            <Link :href="route('ventas.index')"
                  class="px-6 py-3 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors">
              Cancelar
            </Link>
            <button
              type="submit"
              class="px-8 py-3 bg-gradient-to-r from-blue-500 to-indigo-600 text-white rounded-lg hover:from-blue-600 hover:to-indigo-700 transition-all transform hover:scale-105 shadow-lg"
              :disabled="!form.cliente_id || selectedProducts.length === 0 || form.processing"
              :class="{ 'opacity-50 cursor-not-allowed transform-none': !form.cliente_id || selectedProducts.length === 0 || form.processing }"
            >
              <span v-if="form.processing" class="flex items-center">
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Guardando...
              </span>
              <span v-else>Actualizar Venta</span>
            </button>
          </div>
        </div>
      </form>
    </div>

    <!-- Modal de vista previa -->
    <div v-if="mostrarPrevia" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold">Vista Previa de la Venta</h3>
            <button @click="mostrarPrevia = false" class="text-gray-500 hover:text-gray-700">
              <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
              </svg>
            </button>
          </div>

          <div class="space-y-4">
            <div>
              <h4 class="font-semibold">Cliente:</h4>
              <p>{{ buscarCliente }}</p>
            </div>

            <div>
              <h4 class="font-semibold">Productos:</h4>
              <div class="space-y-2">
                <div v-for="productoId in selectedProducts" :key="productoId"
                     class="flex justify-between items-center p-2 bg-gray-50 rounded">
                  <span>{{ getProductById(productoId)?.nombre }}</span>
                  <span>{{ quantities[productoId] }} × ${{ prices[productoId] }} = ${{ calcularSubtotal(productoId).toFixed(2) }}</span>
                </div>
              </div>
            </div>

            <div class="border-t pt-4">
              <div class="flex justify-between items-center text-lg font-bold">
                <span>Total:</span>
                <span>${{ form.total }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Notificaciones Toast -->
    <div v-if="notification.show"
         class="fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg transition-all transform"
         :class="notificationClass">
      <div class="flex items-center">
        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
          <path v-if="notification.type === 'success'" fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
          <path v-else fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
        </svg>
        {{ notification.message }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount, nextTick } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import debounce from 'lodash/debounce';

defineOptions({ layout: AppLayout });

const props = defineProps({
  venta: Object,
  clientes: Array,
  productos: Array,
});

// Formulario principal
const form = useForm({
  cliente_id: props.venta.cliente_id || '',
  total: props.venta.total || 0,
  productos: props.venta.productos || [],
  notas: props.venta.notas || '',
});

// Referencias reactivas
const buscarCliente = ref(props.venta.cliente?.nombre_razon_social || '');
const buscarProducto = ref('');
const mostrarClientes = ref(false);
const mostrarProductos = ref(false);
const mostrarPrevia = ref(false);
const clienteSeleccionadoIndex = ref(-1);
const productoSeleccionadoIndex = ref(-1);

// Estados de productos
const selectedProducts = ref(props.venta.productos.map(p => p.id) || []);
const quantities = ref(
  props.venta.productos.reduce((acc, p) => {
    acc[p.id] = p.pivot.cantidad;
    return acc;
  }, {})
);
const prices = ref(
  props.venta.productos.reduce((acc, p) => {
    acc[p.id] = p.pivot.precio;
    return acc;
  }, {})
);
const discounts = ref(
  props.venta.productos.reduce((acc, p) => {
    acc[p.id] = p.pivot.descuento || 0;
    return acc;
  }, {})
);
const originalQuantities = ref(
  props.venta.productos.reduce((acc, p) => {
    acc[p.id] = p.pivot.cantidad;
    return acc;
  }, {})
);
const adjustedStock = ref({});

// Configuración de impuestos
const includeTax = ref(false);
const taxRate = ref(16);

// Sistema de notificaciones
const notification = ref({
  show: false,
  message: '',
  type: 'success'
});

// Debounce para búsquedas
const debouncedBuscarCliente = debounce(() => {
  clienteSeleccionadoIndex.value = -1;
}, 300);

const debouncedBuscarProducto = debounce(() => {
  productoSeleccionadoIndex.value = -1;
}, 300);

// Computadas
const clientesFiltrados = computed(() =>
  buscarCliente.value
    ? props.clientes.filter(cliente =>
        cliente.nombre_razon_social.toLowerCase().includes(buscarCliente.value.toLowerCase()) ||
        (cliente.email && cliente.email.toLowerCase().includes(buscarCliente.value.toLowerCase())) ||
        (cliente.telefono && cliente.telefono.includes(buscarCliente.value))
      )
    : []
);

const productosFiltrados = computed(() =>
  buscarProducto.value
    ? props.productos.filter(
        producto =>
          (producto.nombre.toLowerCase().includes(buscarProducto.value.toLowerCase()) ||
           (producto.codigo && producto.codigo.toLowerCase().includes(buscarProducto.value.toLowerCase()))) &&
          !selectedProducts.value.includes(producto.id) &&
          (adjustedStock.value[producto.id] ?? producto.stock) > 0
      )
    : []
);

const subtotal = computed(() => {
  return selectedProducts.value.reduce((sum, id) => {
    const cantidad = quantities.value[id] || 0;
    const precio = prices.value[id] || 0;
    return sum + (cantidad * precio);
  }, 0);
});

const totalDescuento = computed(() => {
  return selectedProducts.value.reduce((sum, id) => {
    const cantidad = quantities.value[id] || 0;
    const precio = prices.value[id] || 0;
    const descuento = discounts.value[id] || 0;
    return sum + (cantidad * precio * descuento / 100);
  }, 0);
});

const impuestos = computed(() => {
  const baseImponible = subtotal.value - totalDescuento.value;
  return includeTax.value ? baseImponible * (taxRate.value / 100) : 0;
});

const notificationClass = computed(() => ({
  'bg-green-500 text-white': notification.value.type === 'success',
  'bg-red-500 text-white': notification.value.type === 'error',
  'bg-yellow-500 text-white': notification.value.type === 'warning'
}));

// Métodos principales
const getProductById = (id) => props.productos.find(producto => producto.id === id) || { nombre: 'Producto no encontrado', stock: 0 };

const stockClass = (stock) => {
  if (stock <= 0) return 'bg-red-100 text-red-800';
  if (stock <= 5) return 'bg-yellow-100 text-yellow-800';
  return 'bg-green-100 text-green-800';
};

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('es-ES', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};

const calcularSubtotal = (productoId) => {
  const cantidad = quantities.value[productoId] || 0;
  const precio = prices.value[productoId] || 0;
  const descuento = discounts.value[productoId] || 0;
  const subtotalSinDescuento = cantidad * precio;
  return subtotalSinDescuento - (subtotalSinDescuento * descuento / 100);
};

const calcularTotal = () => {
  const subtotalFinal = subtotal.value - totalDescuento.value;
  const totalConImpuestos = subtotalFinal + impuestos.value;
  form.total = totalConImpuestos.toFixed(2);
};

const adjustStock = (productoId) => {
  const producto = getProductById(productoId);
  const originalQty = originalQuantities.value[productoId] || 0;
  const newQty = quantities.value[productoId] || 0;
  const stockBase = producto.stock + originalQty;
  adjustedStock.value[productoId] = stockBase - newQty;
  calcularTotal();
};

const cambiarCantidad = (productoId, delta) => {
  const nuevaCantidad = (quantities.value[productoId] || 0) + delta;
  const maxStock = getProductById(productoId)?.stock + (originalQuantities.value[productoId] || 0);

  if (nuevaCantidad >= 1 && nuevaCantidad <= maxStock) {
    quantities.value[productoId] = nuevaCantidad;
    adjustStock(productoId);
  }
};

const seleccionarCliente = (cliente) => {
  form.cliente_id = cliente.id;
  buscarCliente.value = cliente.nombre_razon_social;
  mostrarClientes.value = false;
  clienteSeleccionadoIndex.value = -1;
  showNotification('Cliente seleccionado: ' + cliente.nombre_razon_social, 'success');
};

const navegarClientes = (direccion) => {
  if (clientesFiltrados.value.length === 0) return;

  clienteSeleccionadoIndex.value += direccion;

  if (clienteSeleccionadoIndex.value >= clientesFiltrados.value.length) {
    clienteSeleccionadoIndex.value = 0;
  } else if (clienteSeleccionadoIndex.value < 0) {
    clienteSeleccionadoIndex.value = clientesFiltrados.value.length - 1;
  }
};

const navegarProductos = (direccion) => {
  if (productosFiltrados.value.length === 0) return;

  productoSeleccionadoIndex.value += direccion;

  if (productoSeleccionadoIndex.value >= productosFiltrados.value.length) {
    productoSeleccionadoIndex.value = 0;
  } else if (productoSeleccionadoIndex.value < 0) {
    productoSeleccionadoIndex.value = productosFiltrados.value.length - 1;
  }
};

const agregarProducto = (producto) => {
  if (!selectedProducts.value.includes(producto.id) && (adjustedStock.value[producto.id] ?? producto.stock) > 0) {
    selectedProducts.value.push(producto.id);
    quantities.value[producto.id] = 1;
    prices.value[producto.id] = producto.precio_venta || 0;
    discounts.value[producto.id] = 0;
    originalQuantities.value[producto.id] = 0;
    adjustStock(producto.id);
    showNotification('Producto agregado: ' + producto.nombre, 'success');
  } else if ((adjustedStock.value[producto.id] ?? producto.stock) <= 0) {
    showNotification(`El producto ${producto.nombre} no tiene stock disponible`, 'error');
  }
  buscarProducto.value = '';
  mostrarProductos.value = false;
  productoSeleccionadoIndex.value = -1;
};

const duplicarProducto = (productoId) => {
  const producto = getProductById(productoId);
  const stockDisponible = adjustedStock.value[productoId] ?? producto.stock;

  if (stockDisponible > 0) {
    agregarProducto(producto);
  } else {
    showNotification('No hay stock suficiente para duplicar este producto', 'error');
  }
};

const eliminarProducto = (productoId) => {
  const producto = getProductById(productoId);
  selectedProducts.value = selectedProducts.value.filter(id => id !== productoId);
  delete quantities.value[productoId];
  delete prices.value[productoId];
  delete discounts.value[productoId];
  delete adjustedStock.value[productoId];
  delete originalQuantities.value[productoId];
  calcularTotal();
  showNotification('Producto eliminado: ' + producto.nombre, 'warning');
};

const limpiarProductos = () => {
  if (confirm('¿Estás seguro de que quieres eliminar todos los productos?')) {
    selectedProducts.value = [];
    quantities.value = {};
    prices.value = {};
    discounts.value = {};
    adjustedStock.value = {};
    originalQuantities.value = {};
    calcularTotal();
    showNotification('Todos los productos han sido eliminados', 'warning');
  }
};

const aplicarDescuentoGlobal = () => {
  const descuentoGlobal = prompt('Ingrese el porcentaje de descuento global (0-100):');
  if (descuentoGlobal !== null && !isNaN(descuentoGlobal)) {
    const descuento = Math.max(0, Math.min(100, parseFloat(descuentoGlobal)));
    selectedProducts.value.forEach(productoId => {
      discounts.value[productoId] = descuento;
    });
    calcularTotal();
    showNotification(`Descuento del ${descuento}% aplicado a todos los productos`, 'success');
  }
};

const seleccionarPrimerCliente = () => {
  if (clientesFiltrados.value.length > 0) {
    seleccionarCliente(clientesFiltrados.value[clienteSeleccionadoIndex.value >= 0 ? clienteSeleccionadoIndex.value : 0]);
  }
};

const agregarPrimerProducto = () => {
  if (productosFiltrados.value.length > 0) {
    agregarProducto(productosFiltrados.value[productoSeleccionadoIndex.value >= 0 ? productoSeleccionadoIndex.value : 0]);
  }
};

const previsualizarVenta = () => {
  if (!form.cliente_id) {
    showNotification('Debe seleccionar un cliente', 'error');
    return;
  }
  if (selectedProducts.value.length === 0) {
    showNotification('Debe agregar al menos un producto', 'error');
    return;
  }
  mostrarPrevia.value = true;
};

const guardarBorrador = () => {
  const borradorData = {
    cliente_id: form.cliente_id,
    cliente_nombre: buscarCliente.value,
    selectedProducts: selectedProducts.value,
    quantities: quantities.value,
    prices: prices.value,
    discounts: discounts.value,
    includeTax: includeTax.value,
    taxRate: taxRate.value,
    notas: form.notas,
    timestamp: new Date().toISOString()
  };

  // En un entorno real, esto se enviaría al servidor
  console.log('Guardando borrador:', borradorData);
  showNotification('Borrador guardado localmente', 'success');
};

const showNotification = (message, type = 'success') => {
  notification.value = {
    show: true,
    message,
    type
  };

  setTimeout(() => {
    notification.value.show = false;
  }, 3000);
};

const actualizarVenta = () => {
  // Validaciones previas
  if (!form.cliente_id) {
    showNotification('Debe seleccionar un cliente', 'error');
    return;
  }

  if (selectedProducts.value.length === 0) {
    showNotification('Debe agregar al menos un producto', 'error');
    return;
  }

  form.productos = selectedProducts.value.map(id => ({
    id,
    cantidad: quantities.value[id] || 1,
    precio: prices.value[id] || 0,
    descuento: discounts.value[id] || 0,
    original_cantidad: originalQuantities.value[id] || 0,
  }));

  // Validar stock antes de enviar
  for (const producto of form.productos) {
    const stockDisponible = getProductById(producto.id).stock + (producto.original_cantidad || 0);
    if (producto.cantidad > stockDisponible) {
      showNotification(`La cantidad de ${getProductById(producto.id).nombre} excede el stock disponible (${stockDisponible})`, 'error');
      return;
    }
  }

  // Agregar datos adicionales
  form.subtotal = subtotal.value;
  form.descuento_total = totalDescuento.value;
  form.impuestos = impuestos.value;
  form.incluye_impuestos = includeTax.value;
  form.tasa_impuestos = taxRate.value;

  form.put(route('ventas.update', props.venta.id), {
    onSuccess: () => {
      showNotification('Venta actualizada exitosamente', 'success');
      // Limpiar datos guardados localmente
      const savedKeys = ['ventaEnProgreso', 'borradorVenta'];
      savedKeys.forEach(key => {
        try {
          localStorage.removeItem(key);
        } catch (e) {
          console.warn('No se pudo acceder al localStorage');
        }
      });
      form.reset('total');
      adjustedStock.value = {};
    },
    onError: (errors) => {
      console.error('Errores de validación:', errors);
      showNotification('Error al actualizar la venta. Verifique los datos.', 'error');

      // Mostrar errores específicos
      Object.keys(errors).forEach(key => {
        if (errors[key]) {
          console.error(`${key}: ${errors[key]}`);
        }
      });
    },
  });
};

const ocultarClientesDespuesDeTiempo = () => {
  setTimeout(() => {
    mostrarClientes.value = false;
    clienteSeleccionadoIndex.value = -1;
  }, 200);
};

const ocultarProductosDespuesDeTiempo = () => {
  setTimeout(() => {
    mostrarProductos.value = false;
    productoSeleccionadoIndex.value = -1;
  }, 200);
};

const handleBeforeUnload = (event) => {
  if (form.dirty || selectedProducts.value.length > 0) {
    const dataToSave = {
      cliente_id: form.cliente_id,
      cliente_nombre: buscarCliente.value,
      selectedProducts: selectedProducts.value,
      quantities: quantities.value,
      prices: prices.value,
      discounts: discounts.value,
      includeTax: includeTax.value,
      taxRate: taxRate.value,
      notas: form.notas,
      timestamp: new Date().toISOString()
    };

    try {
      localStorage.setItem('ventaEnProgreso', JSON.stringify(dataToSave));
    } catch (e) {
      console.warn('No se pudo guardar en localStorage');
    }

    event.preventDefault();
    event.returnValue = 'Tienes cambios no guardados. ¿Seguro que quieres salir?';
  }
};

// Watchers
watch([quantities, prices, discounts, includeTax, taxRate], () => {
  calcularTotal();
}, { deep: true });

// Lifecycle hooks
onMounted(() => {
  // Inicializar stock ajustado con las cantidades originales
  for (const productoId of selectedProducts.value) {
    adjustStock(productoId);
  }
  calcularTotal();

  // Intentar recuperar datos guardados
  try {
    const savedData = localStorage.getItem('ventaEnProgreso');
    if (savedData) {
      const parsedData = JSON.parse(savedData);

      // Solo cargar si es una sesión reciente (menos de 24 horas)
      const savedTime = new Date(parsedData.timestamp);
      const now = new Date();
      const hoursDiff = (now - savedTime) / (1000 * 60 * 60);

      if (hoursDiff < 24) {
        form.cliente_id = parsedData.cliente_id;
        buscarCliente.value = parsedData.cliente_nombre;
        selectedProducts.value = parsedData.selectedProducts || [];
        quantities.value = parsedData.quantities || {};
        prices.value = parsedData.prices || {};
        discounts.value = parsedData.discounts || {};
        includeTax.value = parsedData.includeTax || false;
        taxRate.value = parsedData.taxRate || 16;
        form.notas = parsedData.notas || '';

        for (const productoId of selectedProducts.value) {
          adjustStock(productoId);
        }
        calcularTotal();

        showNotification('Datos de sesión anterior recuperados', 'success');
      } else {
        localStorage.removeItem('ventaEnProgreso');
      }
    }
  } catch (e) {
    console.warn('Error al recuperar datos guardados:', e);
  }

  window.addEventListener('beforeunload', handleBeforeUnload);
});

onBeforeUnmount(() => {
  window.removeEventListener('beforeunload', handleBeforeUnload);
});
</script>
