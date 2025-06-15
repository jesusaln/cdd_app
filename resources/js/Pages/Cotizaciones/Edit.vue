<template>
  <Head title="Editar cotizaciones" />
  <div class="cotizaciones-edit">
    <div class="mb-6">
      <h1 class="text-3xl font-bold text-gray-900">Editar Cotización</h1>
      <p class="text-gray-600 mt-2">Cotización #{{ props.cotizacion?.id || 'N/A' }}</p>
    </div>

    <!-- Alertas de validación -->
    <div v-if="form.errors && Object.keys(form.errors).length > 0" class="bg-red-50 border border-red-200 rounded-md p-4 mb-6">
      <div class="flex">
        <div class="flex-shrink-0">
          <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
          </svg>
        </div>
        <div class="ml-3">
          <h3 class="text-sm font-medium text-red-800">
            Por favor corrige los siguientes errores:
          </h3>
          <div class="mt-2 text-sm text-red-700">
            <ul class="list-disc pl-5 space-y-1">
              <li v-for="(error, field) in form.errors" :key="field">{{ error }}</li>
            </ul>
          </div>
        </div>
      </div>
    </div>

    <form @submit.prevent="actualizarCotizacion" class="space-y-8">
      <!-- Información del cliente -->
      <div class="bg-white shadow-sm rounded-lg p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Información del Cliente</h2>

        <div class="relative">
          <label for="buscarCliente" class="block text-sm font-medium text-gray-700 mb-2">
            Cliente <span class="text-red-500">*</span>
          </label>
          <div class="relative">
            <input
              id="buscarCliente"
              v-model="buscarCliente"
              type="text"
              placeholder="Buscar cliente por nombre..."
              @focus="mostrarClientes = true"
              @blur="ocultarClientesDespuesDeTiempo"
              @input="filtrarClientes"
              class="block w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
              :class="{ 'border-red-300': form.errors.cliente_id }"
            />
            <div v-if="clienteSeleccionado" class="absolute inset-y-0 right-0 flex items-center pr-3">
              <svg class="h-5 w-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
            </div>
          </div>

          <!-- Lista desplegable de clientes -->
          <div v-if="mostrarClientes && clientesFiltrados.length > 0"
               class="absolute z-20 mt-1 w-full bg-white border border-gray-200 rounded-md shadow-lg max-h-60 overflow-y-auto">
            <ul class="py-1">
              <li
                v-for="cliente in clientesFiltrados"
                :key="cliente.id"
                @mousedown="seleccionarCliente(cliente)"
                class="px-4 py-3 cursor-pointer hover:bg-blue-50 border-b border-gray-100 last:border-b-0"
              >
                <div class="font-medium text-gray-900">{{ cliente.nombre_razon_social }}</div>
                <div v-if="cliente.email" class="text-sm text-gray-500">{{ cliente.email }}</div>
              </li>
            </ul>
          </div>

          <div v-if="mostrarClientes && clientesFiltrados.length === 0 && buscarCliente"
               class="absolute z-20 mt-1 w-full bg-white border border-gray-200 rounded-md shadow-lg p-4">
            <div class="text-center text-gray-500">
              <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>
              <p class="mt-2 text-sm">No se encontraron clientes</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Productos y servicios -->
      <div class="bg-white shadow-sm rounded-lg p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Productos y Servicios</h2>

        <!-- Buscador de productos -->
        <div class="relative mb-6">
          <label for="buscarProducto" class="block text-sm font-medium text-gray-700 mb-2">
            Agregar Producto/Servicio
          </label>
          <div class="relative">
            <input
              id="buscarProducto"
              v-model="buscarProducto"
              type="text"
              placeholder="Buscar por nombre, código, serie o código de barras..."
              @focus="mostrarProductos = true"
              @blur="ocultarProductosDespuesDeTiempo"
              @input="filtrarProductos"
              class="block w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
            />
            <div class="absolute inset-y-0 right-0 flex items-center pr-3">
              <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </div>
          </div>

          <!-- Lista desplegable de productos -->
          <div v-if="mostrarProductos && productosFiltrados.length > 0"
               class="absolute z-20 mt-1 w-full bg-white border border-gray-200 rounded-md shadow-lg max-h-60 overflow-y-auto">
            <ul class="py-1">
              <li
                v-for="item in productosFiltrados"
                :key="`${item.tipo}-${item.id}`"
                @mousedown="agregarProducto(item)"
                class="px-4 py-3 cursor-pointer hover:bg-blue-50 border-b border-gray-100 last:border-b-0"
              >
                <div class="flex items-center justify-between">
                  <div>
                    <div class="font-medium text-gray-900">{{ item.nombre }}</div>
                    <div class="text-sm text-gray-500">
                      {{ item.tipo === 'producto' ? 'Producto' : 'Servicio' }}
                      <span v-if="item.codigo"> • Código: {{ item.codigo }}</span>
                      <span v-if="item.tipo === 'producto' && item.stock !== undefined"> • Stock: {{ item.stock }}</span>
                    </div>
                  </div>
                  <div class="text-right">
                    <div class="font-medium text-green-600">
                      ${{ (item.precio_venta || item.precio || 0).toFixed(2) }}
                    </div>
                  </div>
                </div>
              </li>
            </ul>
          </div>

          <div v-if="mostrarProductos && productosFiltrados.length === 0 && buscarProducto"
               class="absolute z-20 mt-1 w-full bg-white border border-gray-200 rounded-md shadow-lg p-4">
            <div class="text-center text-gray-500">
              <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
              </svg>
              <p class="mt-2 text-sm">No se encontraron productos o servicios</p>
            </div>
          </div>
        </div>

        <!-- Lista de productos seleccionados -->
        <div v-if="selectedProducts.length > 0" class="space-y-4">
          <h3 class="text-md font-medium text-gray-900">Items Seleccionados</h3>

          <div class="overflow-hidden border border-gray-200 rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Producto/Servicio
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Cantidad
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Precio Unitario
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Subtotal
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Acciones
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="(entry, index) in selectedProducts" :key="`${entry.tipo}-${entry.id}`"
                    class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <div class="flex-shrink-0 h-10 w-10">
                        <div class="h-10 w-10 rounded-full flex items-center justify-center"
                             :class="entry.tipo === 'producto' ? 'bg-blue-100 text-blue-600' : 'bg-green-100 text-green-600'">
                          <svg v-if="entry.tipo === 'producto'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                          </svg>
                          <svg v-else class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                          </svg>
                        </div>
                      </div>
                      <div class="ml-4">
                        <div class="text-sm font-medium text-gray-900">
                          {{ getProductById(entry)?.nombre || 'Item no encontrado' }}
                        </div>
                        <div class="text-sm text-gray-500">
                          {{ entry.tipo === 'producto' ? 'Producto' : 'Servicio' }}
                        </div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <input
                      v-model.number="quantities[`${entry.tipo}-${entry.id}`]"
                      type="number"
                      min="1"
                      step="0.01"
                      class="w-20 px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                      @input="calcularTotal"
                    />
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="relative">
                      <span class="absolute left-3 top-2 text-gray-500 text-sm">$</span>
                      <input
                        v-model.number="prices[`${entry.tipo}-${entry.id}`]"
                        type="number"
                        min="0"
                        step="0.01"
                        class="w-28 pl-6 pr-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        @input="calcularTotal"
                      />
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                    ${{ ((quantities[`${entry.tipo}-${entry.id}`] || 0) * (prices[`${entry.tipo}-${entry.id}`] || 0)).toFixed(2) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <button
                      type="button"
                      @click="eliminarProducto(entry)"
                      class="text-red-600 hover:text-red-900 transition-colors"
                      title="Eliminar item"
                    >
                      <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                      </svg>
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Mensaje cuando no hay productos -->
        <div v-else class="text-center py-8">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
          </svg>
          <h3 class="mt-2 text-sm font-medium text-gray-900">No hay productos seleccionados</h3>
          <p class="mt-1 text-sm text-gray-500">Comienza buscando y agregando productos o servicios a la cotización.</p>
        </div>
      </div>

      <!-- Resumen y total -->
      <div class="bg-white shadow-sm rounded-lg p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Resumen</h2>

        <div class="space-y-4">
          <div class="flex justify-between text-sm">
            <span class="text-gray-600">Subtotal:</span>
            <span class="font-medium">${{ subtotal.toFixed(2) }}</span>
          </div>
          <div class="flex justify-between text-sm">
            <span class="text-gray-600">IVA (16%):</span>
            <span class="font-medium">${{ iva.toFixed(2) }}</span>
          </div>
          <div class="border-t pt-4">
            <div class="flex justify-between text-lg font-semibold">
              <span>Total:</span>
              <span class="text-blue-600">${{ form.total }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Botones de acción -->
      <div class="flex justify-between items-center pt-6">
        <Link
          :href="route('cotizaciones.index')"
          class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
        >
          <svg class="mr-2 -ml-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          Cancelar
        </Link>

        <div class="flex space-x-3">
          <button
            type="button"
            @click="guardarBorrador"
            class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors"
            :disabled="form.processing"
          >
            <svg class="mr-2 -ml-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3-3m0 0l-3 3m3-3v12" />
            </svg>
            Guardar Borrador
          </button>

          <button
            type="submit"
            class="inline-flex items-center px-6 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
            :disabled="!puedeGuardar || form.processing"
          >
            <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <svg v-else class="mr-2 -ml-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            {{ form.processing ? 'Guardando...' : 'Actualizar Cotización' }}
          </button>
        </div>
      </div>
    </form>
  </div>
</template>

  <script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

// Define el layout del dashboard
defineOptions({ layout: AppLayout });

const props = defineProps({
  cotizacion: Object,
  clientes: {
    type: Array,
    default: () => [],
  },
  productos: {
    type: Array,
    default: () => [],
  },
  servicios: {
    type: Array,
    default: () => [],
  },
});

const form = useForm({
  cliente_id: props.cotizacion?.cliente_id || '',
  total: props.cotizacion?.total || 0,
  productos: [],
});

// Estados reactivos
const buscarCliente = ref(props.cotizacion?.cliente?.nombre_razon_social || '');
const buscarProducto = ref('');
const mostrarClientes = ref(false);
const mostrarProductos = ref(false);

const selectedProducts = ref(
  props.cotizacion?.productos?.map(item => ({
    id: item.id,
    tipo: item.tipo || 'producto',
  })) || []
);

const quantities = ref(
  props.cotizacion?.productos?.reduce((acc, item) => {
    acc[`${item.tipo || 'producto'}-${item.id}`] = item.pivot?.cantidad || item.cantidad || 1;
    return acc;
  }, {}) || {}
);

const prices = ref(
  props.cotizacion?.productos?.reduce((acc, item) => {
    acc[`${item.tipo || 'producto'}-${item.id}`] = item.pivot?.precio || item.precio || item.precio_venta || 0;
    return acc;
  }, {}) || {}
);

const clienteSeleccionado = ref(props.cotizacion?.cliente?.nombre_razon_social || null);

// Computed properties
const clientesFiltrados = computed(() => {
  if (!buscarCliente.value) return props.clientes;
  return props.clientes.filter((cliente) =>
    cliente.nombre_razon_social.toLowerCase().includes(buscarCliente.value.toLowerCase()) ||
    (cliente.email && cliente.email.toLowerCase().includes(buscarCliente.value.toLowerCase()))
  );
});

const productosFiltrados = computed(() => {
  if (!buscarProducto.value) return [];

  const productosYServicios = [
    ...(props.productos || []).map(producto => ({
      ...producto,
      tipo: 'producto',
    })),
    ...(props.servicios || []).map(servicio => ({
      ...servicio,
      tipo: 'servicio',
    })),
  ];

  const termino = buscarProducto.value.toLowerCase();
  return productosYServicios.filter(item => {
    // Excluir productos ya seleccionados
    const yaSeleccionado = selectedProducts.value.some(
      selected => selected.id === item.id && selected.tipo === item.tipo
    );

    if (yaSeleccionado) return false;

    return (
      item.nombre.toLowerCase().includes(termino) ||
      (item.codigo && item.codigo.toLowerCase().includes(termino)) ||
      (item.numero_de_serie && item.numero_de_serie.toLowerCase().includes(termino)) ||
      (item.codigo_barras && item.codigo_barras.toLowerCase().includes(termino))
    );
  });
});

const subtotal = computed(() => {
  let total = 0;
  for (const entry of selectedProducts.value) {
    const key = `${entry.tipo}-${entry.id}`;
    const cantidad = parseFloat(quantities.value[key]) || 0;
    const precio = parseFloat(prices.value[key]) || 0;
    total += cantidad * precio;
  }
  return total;
});

const iva = computed(() => subtotal.value * 0.16);

const puedeGuardar = computed(() => {
  return form.cliente_id && selectedProducts.value.length > 0 && !form.processing;
});

// Métodos
const getProductById = (entry) => {
  if (!entry || !entry.id || !entry.tipo) {
    console.error('Entrada inválida para getProductById:', entry);
    return null;
  }

  if (entry.tipo === 'producto') {
    if (!Array.isArray(props.productos)) {
      console.error('props.productos no es un array:', props.productos);
      return null;
    }
    return props.productos.find((p) => p.id === entry.id) || null;
  }

  if (entry.tipo === 'servicio') {
    if (!Array.isArray(props.servicios)) {
      console.error('props.servicios no es un array:', props.servicios);
      return null;
    }
    return props.servicios.find((s) => s.id === entry.id) || null;
  }

  console.error(`No se encontró item con ID: ${entry.id} y tipo: ${entry.tipo}`);
  return null;
};

const calcularTotal = () => {
  const totalConIva = subtotal.value + iva.value;
  form.total = totalConIva.toFixed(2);
};

const seleccionarCliente = (cliente) => {
  form.cliente_id = cliente.id;
  buscarCliente.value = cliente.nombre_razon_social;
  clienteSeleccionado.value = cliente.nombre_razon_social;
  mostrarClientes.value = false;
};

const agregarProducto = (item) => {
  console.log('Item seleccionado:', item);
  const itemEntry = { id: item.id, tipo: item.tipo };

  selectedProducts.value.push(itemEntry);
  quantities.value[`${item.tipo}-${item.id}`] = 1;
  prices.value[`${item.tipo}-${item.id}`] = item.tipo === 'producto' ? (item.precio_venta || 0) : (item.precio || 0);

  buscarProducto.value = '';
  mostrarProductos.value = false;
  calcularTotal();
};

const eliminarProducto = (entry) => {
  selectedProducts.value = selectedProducts.value.filter(
    (item) => !(item.id === entry.id && item.tipo === entry.tipo)
  );
  delete quantities.value[`${entry.tipo}-${entry.id}`];
  delete prices.value[`${entry.tipo}-${entry.id}`];
  calcularTotal();
};

const filtrarClientes = () => {
  // Resetear cliente seleccionado si el campo está vacío
  if (!buscarCliente.value) {
    form.cliente_id = '';
    clienteSeleccionado.value = null;
  }
};

const filtrarProductos = () => {
  // Lógica adicional si es necesaria
};

const ocultarClientesDespuesDeTiempo = (event) => {
  setTimeout(() => {
    if (!event.relatedTarget || !event.relatedTarget.closest('.cliente-item')) {
      mostrarClientes.value = false;
    }
  }, 200);
};

const ocultarProductosDespuesDeTiempo = (event) => {
  setTimeout(() => {
    if (!event.relatedTarget || !event.relatedTarget.closest('.producto-item')) {
      mostrarProductos.value = false;
    }
  }, 200);
};

const guardarBorrador = () => {
  // Función para guardar como borrador
  const dataToSave = {
    cliente_id: form.cliente_id,
    cliente_nombre: clienteSeleccionado.value,
    selectedProducts: selectedProducts.value,
    quantities: quantities.value,
    prices: prices.value,
    total: form.total,
  };

  try {
    // No usamos localStorage debido a las restricciones
    console.log('Guardando borrador:', dataToSave);
    // Aquí podrías hacer una petición al servidor para guardar como borrador
    alert('Borrador guardado correctamente');
  } catch (error) {
    console.error('Error al guardar borrador:', error);
    alert('Error al guardar el borrador');
  }
};

const actualizarCotizacion = () => {
  form.productos = selectedProducts.value.map((entry) => ({
    id: entry.id,
    tipo: entry.tipo,
    cantidad: quantities.value[`${entry.tipo}-${entry.id}`] || 1,
    precio: prices.value[`${entry.tipo}-${entry.id}`] || 0,
  }));

  form.put(route('cotizaciones.update', props.cotizacion.id), {
    onSuccess: () => {
      console.log('Cotización actualizada con éxito');
      // Limpiar datos guardados localmente si los hubiera
    },
    onError: (error) => {
      console.error('Error al actualizar la cotización:', error);
    },
  });
};

// Watchers
watch(quantities, calcularTotal, { deep: true });
watch(prices, calcularTotal, { deep: true });

watch(
  [() => form.cliente_id, selectedProducts, quantities, prices, clienteSeleccionado],
  () => {
    const dataToSave = {
      cliente_id: form.cliente_id,
      cliente_nombre: clienteSeleccionado.value,
      selectedProducts: selectedProducts.value,
      quantities: quantities.value,
      prices: prices.value,
    };
    // Aquí podrías guardar en una variable temporal o hacer una petición al servidor
    console.log('Datos actualizados:', dataToSave);
  },
  { deep: true }
);

// Lifecycle hooks
onMounted(() => {
  console.log('Cotización inicial:', props.cotizacion);
  console.log('Clientes:', props.clientes);
  console.log('Productos:', props.productos);
  console.log('Servicios:', props.servicios);

  // Calcular total inicial
  calcularTotal();

  window.addEventListener('beforeunload', handleBeforeUnload);
});

onBeforeUnmount(() => {
  window.removeEventListener('beforeunload', handleBeforeUnload);
});

const handleBeforeUnload = (event) => {
  if (form.dirty) {
    const confirmationMessage = 'Tienes cambios no guardados. ¿Estás seguro de que quieres salir?';
    event.returnValue = confirmationMessage;
    return confirmationMessage;
  }
};
</script>

<style>
/* Estilos específicos para el componente de edición de cotizaciones */
.cotizaciones-edit {
  max-width: 80rem; /* max-w-7xl */
  margin-left: auto; /* mx-auto */
  margin-right: auto;
  padding-left: 1rem; /* px-4 */
  padding-right: 1rem;
  padding-top: 2rem; /* py-8 */
  padding-bottom: 2rem;
}

@media (min-width: 640px) {
  .cotizaciones-edit {
    padding-left: 1.5rem; /* sm:px-6 */
    padding-right: 1.5rem;
  }
}

@media (min-width: 1024px) {
  .cotizaciones-edit {
    padding-left: 2rem; /* lg:px-8 */
    padding-right: 2rem;
  }
}

/* Animaciones para las transiciones */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

/* Estilos para los dropdowns */
.dropdown-enter-active {
  transition: all 0.2s ease-out;
}

.dropdown-leave-active {
  transition: all 0.2s ease-in;
}

.dropdown-enter-from {
  transform: translateY(-10px);
  opacity: 0;
}

.dropdown-leave-to {
  transform: translateY(-10px);
  opacity: 0;
}

/* Mejoras en la accesibilidad y focus */
.cotizaciones-edit input:focus,
.cotizaciones-edit button:focus {
  outline: none;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

/* Estilos para los iconos de validación */
.validation-icon {
  position: absolute;
  right: 0.75rem; /* right-3 */
  top: 50%;       /* top-1/2 */
  transform: translateY(-50%); /* -translate-y-1/2 */
}

/* Estilos para las alertas de error */
.error-alert {
  @apply bg-red-50 border border-red-200 rounded-md p-4 mb-6;
  animation: slideDown 0.3s ease-out;
}

@keyframes slideDown {
  from {
    transform: translateY(-10px);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}

/* Estilos para los estados de loading */
.loading-spinner {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

/* Estilos para la tabla responsive */
.productos-table {
  @apply min-w-full divide-y divide-gray-200;
}

.productos-table thead {
  @apply bg-gray-50;
}

.productos-table th {
  @apply px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider;
}

.productos-table td {
  @apply px-6 py-4 whitespace-nowrap text-sm text-gray-900;
}

.productos-table tbody tr:hover {
  @apply bg-gray-50;
  transition: background-color 0.15s ease-in-out;
}

/* Estilos para los botones de acción */
.btn-primary {
  @apply inline-flex items-center px-6 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed;
  transition: all 0.2s ease-in-out;
}

.btn-secondary {
  @apply inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500;
  transition: all 0.2s ease-in-out;
}

.btn-danger {
  @apply text-red-600 hover:text-red-900;
  transition: color 0.15s ease-in-out;
}

/* Estilos para las tarjetas */
.card {
  @apply bg-white shadow-sm rounded-lg p-6;
  border: 1px solid rgba(229, 231, 235, 0.8);
}

.card:hover {
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  transition: box-shadow 0.2s ease-in-out;
}

/* Estilos para los campos de búsqueda */
.search-input {
  @apply block w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500;
  transition: all 0.2s ease-in-out;
}

.search-input:focus {
  border-color: #3B82F6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

/* Estilos para los elementos de lista desplegable */
.dropdown-item {
  @apply px-4 py-3 cursor-pointer hover:bg-blue-50 border-b border-gray-100 last:border-b-0;
  transition: background-color 0.15s ease-in-out;
}

.dropdown-item:hover {
  background-color: rgba(239, 246, 255, 0.8);
}

/* Estilos para los badges de tipo */
.badge-producto {
  @apply inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800;
}

.badge-servicio {
  @apply inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800;
}

/* Estilos para los inputs numéricos */
.numeric-input {
  @apply px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500;
  transition: all 0.2s ease-in-out;
}

.numeric-input:focus {
  border-color: #3B82F6;
  box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
}

/* Estilos para el resumen de totales */
.summary-section {
  @apply bg-gray-50 rounded-lg p-4 border border-gray-200;
}

.summary-row {
  @apply flex justify-between items-center py-2;
}

.summary-total {
  @apply border-t border-gray-300 pt-4 mt-4;
}

/* Estilos para estados vacíos */
.empty-state {
  @apply text-center py-8;
}

.empty-state svg {
  @apply mx-auto h-12 w-12 text-gray-400 mb-4;
}

.empty-state h3 {
  @apply text-sm font-medium text-gray-900 mb-2;
}

.empty-state p {
  @apply text-sm text-gray-500;
}

/* Responsividad mejorada */
@media (max-width: 768px) {
  .cotizaciones-edit {
    @apply px-2 py-4;
  }

  .card {
    @apply p-4;
  }

  .productos-table {
    font-size: 12px;
  }

  .productos-table th,
  .productos-table td {
    @apply px-3 py-2;
  }

  .btn-primary,
  .btn-secondary {
    @apply px-4 py-2 text-xs;
  }
}

@media (max-width: 640px) {
  .productos-table {
    @apply block overflow-x-auto whitespace-nowrap;
  }

  .productos-table thead,
  .productos-table tbody,
  .productos-table th,
  .productos-table td,
  .productos-table tr {
    @apply block;
  }

  .productos-table thead tr {
    @apply absolute -top-full -left-full;
  }

  .productos-table tbody tr {
    @apply border border-gray-200 rounded-lg mb-4 p-4;
  }

  .productos-table td {
    @apply border-none relative pl-16 text-right;
  }

  .productos-table td:before {
    content: attr(data-label);
    @apply absolute left-2 w-12 text-left font-medium text-gray-600;
  }
}

/* Animación para elementos agregados dinámicamente */
.item-enter-active {
  transition: all 0.3s ease-out;
}

.item-leave-active {
  transition: all 0.3s ease-in;
}

.item-enter-from {
  transform: translateX(-30px);
  opacity: 0;
}

.item-leave-to {
  transform: translateX(30px);
  opacity: 0;
}

/* Mejoras visuales adicionales */
.shadow-soft {
  box-shadow: 0 2px 4px 0 rgba(0, 0, 0, 0.05);
}

.border-soft {
  border-color: rgba(229, 231, 235, 0.6);
}

/* Estados de validación mejorados */
.input-valid {
  @apply border-green-300 focus:border-green-500 focus:ring-green-500;
}

.input-invalid {
  @apply border-red-300 focus:border-red-500 focus:ring-red-500;
}

/* Estilos para tooltips simples */
.tooltip {
  @apply relative;
}

.tooltip:hover::after {
  content: attr(data-tooltip);
  @apply absolute -top-8 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white text-xs rounded py-1 px-2 whitespace-nowrap z-50;
}
</style>
