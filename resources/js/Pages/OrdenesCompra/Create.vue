<template>
  <Head title="Crear Orden de Compra" />

  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="bg-white shadow-sm rounded-lg mb-8">
        <div class="px-6 py-4 border-b border-gray-200">
          <div class="flex items-center justify-between">
            <div>
              <h1 class="text-2xl font-bold text-gray-900">Nueva Orden de Compra</h1>
              <p class="mt-1 text-sm text-gray-600">
                Crea una nueva orden de compra seleccionando proveedor y productos/servicios
              </p>
            </div>
            <div class="flex items-center space-x-3">
              <button
                @click="limpiarFormulario"
                type="button"
                class="btn-secondary"
                :disabled="loading"
              >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Limpiar
              </button>
            </div>
          </div>
        </div>
      </div>

      <form @submit.prevent="crearOrdenCompra" class="space-y-8">
        <!-- Información del Proveedor -->
        <div class="card">
          <div class="card-header">
            <h2 class="card-title">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-4m-5 0H3m2 0h3M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
              </svg>
              Información del Proveedor
            </h2>
          </div>
          <div class="card-body">
            <div class="form-group">
              <label for="buscarProveedor" class="form-label required">
                Buscar Proveedor
              </label>
              <div class="relative">
                <input
                  id="buscarProveedor"
                  v-model="buscarProveedor"
                  type="text"
                  placeholder="Escriba el nombre del proveedor..."
                  @focus="mostrarProveedores = true"
                  @blur="ocultarProveedoresDespuesDeTiempo"
                  @input="onProveedorSearch"
                  autocomplete="off"
                  class="form-input"
                  :class="{ 'border-red-300': form.errors.proveedor_id }"
                />
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                  <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                  </svg>
                </div>

                <!-- Dropdown de proveedores -->
                <Transition name="fade">
                  <div v-if="mostrarProveedores && (proveedoresFiltrados.length > 0 || loadingProveedores)"
                       class="dropdown-menu">
                    <div v-if="loadingProveedores" class="px-4 py-3 text-center text-gray-500">
                      <div class="inline-flex items-center">
                        <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24">
                          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Buscando...
                      </div>
                    </div>
                    <div v-else-if="proveedoresFiltrados.length === 0" class="px-4 py-3 text-center text-gray-500">
                      No se encontraron proveedores
                    </div>
                    <div v-else class="max-h-60 overflow-y-auto">
                      <button
                        v-for="proveedor in proveedoresFiltrados"
                        :key="proveedor.id"
                        type="button"
                        @click="seleccionarProveedor(proveedor)"
                        class="dropdown-item proveedor-item"
                      >
                        <div class="flex flex-col">
                          <span class="font-medium">{{ proveedor.nombre_razon_social }}</span>
                          <span v-if="proveedor.codigo" class="text-sm text-gray-500">
                            Código: {{ proveedor.codigo }}
                          </span>
                        </div>
                      </button>
                    </div>
                  </div>
                </Transition>
              </div>
              <p v-if="form.errors.proveedor_id" class="form-error">
                {{ form.errors.proveedor_id }}
              </p>

              <!-- Proveedor seleccionado -->
              <div v-if="proveedorSeleccionado" class="mt-3 p-3 bg-green-50 border border-green-200 rounded-lg">
                <div class="flex items-center">
                  <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                  <div>
                    <p class="font-medium text-green-800">{{ proveedorSeleccionado.nombre_razon_social }}</p>
                    <p v-if="proveedorSeleccionado.codigo" class="text-sm text-green-600">
                      Código: {{ proveedorSeleccionado.codigo }}
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Búsqueda de Productos/Servicios -->
        <div class="card">
          <div class="card-header">
            <h2 class="card-title">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
              </svg>
              Productos y Servicios
            </h2>
          </div>
          <div class="card-body">
            <div class="form-group">
              <label for="buscarProducto" class="form-label">
                Buscar Producto/Servicio
              </label>
              <div class="relative">
                <input
                  id="buscarProducto"
                  v-model="buscarProducto"
                  type="text"
                  placeholder="Buscar por nombre, código o código de barras..."
                  @focus="mostrarProductos = true"
                  @blur="ocultarProductosDespuesDeTiempo"
                  @input="onProductoSearch"
                  autocomplete="off"
                  class="form-input"
                />
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                  <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                  </svg>
                </div>

                <!-- Dropdown de productos -->
                <Transition name="fade">
                  <div v-if="mostrarProductos && (productosFiltrados.length > 0 || loadingProductos)"
                       class="dropdown-menu">
                    <div v-if="loadingProductos" class="px-4 py-3 text-center text-gray-500">
                      <div class="inline-flex items-center">
                        <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24">
                          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Buscando...
                      </div>
                    </div>
                    <div v-else-if="productosFiltrados.length === 0" class="px-4 py-3 text-center text-gray-500">
                      No se encontraron productos/servicios
                    </div>
                    <div v-else class="max-h-60 overflow-y-auto">
                      <button
                        v-for="item in productosFiltrados"
                        :key="`${item.tipo}-${item.id}`"
                        type="button"
                        @click="agregarProducto(item)"
                        class="dropdown-item producto-item"
                        :disabled="isItemSelected(item)"
                      >
                        <div class="flex items-center justify-between w-full">
                          <div class="flex flex-col text-left">
                            <span class="font-medium">{{ item.nombre }}</span>
                            <div class="flex items-center space-x-4 text-sm text-gray-500">
                              <span class="badge" :class="item.tipo === 'producto' ? 'badge-blue' : 'badge-green'">
                                {{ item.tipo === 'producto' ? 'Producto' : 'Servicio' }}
                              </span>
                              <span v-if="item.codigo">Código: {{ item.codigo }}</span>
                              <span v-if="item.tipo === 'producto' && item.stock !== undefined">
                                Stock: {{ item.stock }}
                              </span>
                            </div>
                          </div>
                          <div v-if="isItemSelected(item)" class="text-green-500">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                          </div>
                        </div>
                      </button>
                    </div>
                  </div>
                </Transition>
              </div>
            </div>
          </div>
        </div>

        <!-- Lista de Items Seleccionados -->
        <div v-if="selectedItems.length > 0" class="card">
          <div class="card-header">
            <div class="flex items-center justify-between">
              <h2 class="card-title">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                Items Seleccionados ({{ selectedItems.length }})
              </h2>
              <button
                @click="limpiarItems"
                type="button"
                class="text-red-600 hover:text-red-800 text-sm font-medium"
              >
                Limpiar todo
              </button>
            </div>
          </div>
          <div class="card-body p-0">
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="table-header">Producto/Servicio</th>
                    <th class="table-header">Tipo</th>
                    <th class="table-header">Cantidad</th>
                    <th class="table-header">Precio Unitario</th>
                    <th class="table-header">Subtotal</th>
                    <th class="table-header">Acciones</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="(entry, index) in selectedItems" :key="`${entry.tipo}-${entry.id}`"
                      class="hover:bg-gray-50 transition-colors duration-150">
                    <td class="table-cell">
                      <div class="flex flex-col">
                        <span class="font-medium text-gray-900">
                          {{ getItemById(entry)?.nombre || 'Item no encontrado' }}
                        </span>
                        <span v-if="getItemById(entry)?.codigo" class="text-sm text-gray-500">
                          Código: {{ getItemById(entry).codigo }}
                        </span>
                      </div>
                    </td>
                    <td class="table-cell">
                      <span class="badge" :class="entry.tipo === 'producto' ? 'badge-blue' : 'badge-green'">
                        {{ entry.tipo === 'producto' ? 'Producto' : 'Servicio' }}
                      </span>
                    </td>
                    <td class="table-cell">
                      <input
                        v-model.number="quantities[`${entry.tipo}-${entry.id}`]"
                        type="number"
                        class="form-input-sm"
                        min="1"
                        step="0.01"
                        @input="calcularTotal"
                        @blur="validateQuantity(entry)"
                      />
                      <p v-if="quantityErrors[`${entry.tipo}-${entry.id}`]" class="text-red-500 text-xs mt-1">
                        {{ quantityErrors[`${entry.tipo}-${entry.id}`] }}
                      </p>
                    </td>
                    <td class="table-cell">
                      <div class="flex items-center">
                        <span class="text-gray-500 mr-1">$</span>
                        <input
                          v-model.number="prices[`${entry.tipo}-${entry.id}`]"
                          type="number"
                          class="form-input-sm"
                          min="0"
                          step="0.01"
                          @input="calcularTotal"
                          @blur="validatePrice(entry)"
                        />
                      </div>
                      <p v-if="priceErrors[`${entry.tipo}-${entry.id}`]" class="text-red-500 text-xs mt-1">
                        {{ priceErrors[`${entry.tipo}-${entry.id}`] }}
                      </p>
                    </td>
                    <td class="table-cell">
                      <span class="font-medium text-gray-900">
                        ${{ formatCurrency(getSubtotal(entry)) }}
                      </span>
                    </td>
                    <td class="table-cell">
                      <button
                        @click="eliminarItem(entry)"
                        type="button"
                        class="text-red-600 hover:text-red-800 p-1 rounded-md hover:bg-red-50 transition-colors duration-150"
                        title="Eliminar item"
                      >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                      </button>
                    </td>
                  </tr>
                </tbody>
                <tfoot class="bg-gray-50">
                  <tr>
                    <td colspan="4" class="table-cell font-semibold text-right">Total:</td>
                    <td class="table-cell">
                      <span class="text-lg font-bold text-gray-900">
                        ${{ formatCurrency(form.total) }}
                      </span>
                    </td>
                    <td class="table-cell"></td>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>
        </div>

        <!-- Resumen y Acciones -->
        <div class="card">
          <div class="card-body">
            <div class="flex items-center justify-between">
              <div class="flex items-center space-x-4">
                <div class="text-sm text-gray-600">
                  <span class="font-medium">Items:</span> {{ selectedItems.length }}
                </div>
                <div class="text-sm text-gray-600">
                  <span class="font-medium">Total:</span>
                  <span class="text-lg font-bold text-gray-900 ml-1">
                    ${{ formatCurrency(form.total) }}
                  </span>
                </div>
              </div>

              <div class="flex items-center space-x-4">
                <Link
                  :href="route('ordenescompra.index')"
                  class="btn-secondary"
                  :disabled="loading"
                >
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                  Cancelar
                </Link>

                <button
                  type="submit"
                  class="btn-primary"
                  :disabled="!canSubmit || loading"
                >
                  <svg v-if="loading" class="animate-spin -ml-1 mr-3 h-4 w-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  <svg v-else class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                  {{ loading ? 'Guardando...' : 'Crear Orden de Compra' }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount, nextTick } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { router } from '@inertiajs/vue3';

// Layout configuration
defineOptions({ layout: AppLayout });

// Props
const props = defineProps({
  proveedores: {
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
  errors: {
    type: Object,
    default: () => ({}),
  },
  jetstream: Object,
  auth: Object,
  errorBags: Object,
  flash: Object,
});

// Form state
const form = useForm({
  proveedor_id: '',
  total: 0,
  items: [],
});

// Reactive state
const buscarProveedor = ref('');
const buscarProducto = ref('');
const mostrarProveedores = ref(false);
const mostrarProductos = ref(false);
const selectedItems = ref([]);
const quantities = ref({});
const prices = ref({});
const quantityErrors = ref({});
const priceErrors = ref({});
const proveedorSeleccionado = ref(null);
const loading = ref(false);
const loadingProveedores = ref(false);
const loadingProductos = ref(false);

// Debounce timers
let proveedorSearchTimer = null;
let productoSearchTimer = null;

// Computed properties
const proveedoresFiltrados = computed(() => {
  if (!buscarProveedor.value || buscarProveedor.value.length < 2) return [];
  const searchTerm = buscarProveedor.value.toLowerCase();
  return props.proveedores.filter((proveedor) =>
    proveedor.nombre_razon_social.toLowerCase().includes(searchTerm) ||
    (proveedor.codigo && proveedor.codigo.toLowerCase().includes(searchTerm))
  ).slice(0, 10); // Limit results
});

const productosFiltrados = computed(() => {
  if (!buscarProducto.value || buscarProducto.value.length < 2) return [];
  const searchTerm = buscarProducto.value.toLowerCase();

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

  return productosYServicios.filter(item =>
    item.nombre.toLowerCase().includes(searchTerm) ||
    (item.codigo && item.codigo.toLowerCase().includes(searchTerm)) ||
    (item.numero_de_serie && item.numero_de_serie.toLowerCase().includes(searchTerm)) ||
    (item.codigo_barras && item.codigo_barras.toLowerCase().includes(searchTerm))
  ).slice(0, 15); // Limit results
});

const canSubmit = computed(() => {
  return form.proveedor_id &&
         selectedItems.value.length > 0 &&
         !hasValidationErrors.value &&
         !loading.value;
});

const hasValidationErrors = computed(() => {
  return Object.keys(quantityErrors.value).length > 0 ||
         Object.keys(priceErrors.value).length > 0;
});

// Methods
const formatCurrency = (value) => {
  return new Intl.NumberFormat('es-MX', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(value || 0);
};

const getItemById = (entry) => {
  if (!entry || !entry.id || !entry.tipo) {
    console.error('Entrada inválida para getItemById:', entry);
    return null;
  }

  const items = entry.tipo === 'producto' ? props.productos : props.servicios;
  return items.find(item => item.id === entry.id) || null;
};

const getSubtotal = (entry) => {
  const key = `${entry.tipo}-${entry.id}`;
  const cantidad = Number.parseFloat(quantities.value[key]) || 0;
  const precio = Number.parseFloat(prices.value[key]) || 0;
  return cantidad * precio;
};

const calcularTotal = () => {
  let total = 0;
  for (const entry of selectedItems.value) {
    total += getSubtotal(entry);
  }
  form.total = Number.parseFloat(total.toFixed(2));
};

const validateQuantity = (entry) => {
  const key = `${entry.tipo}-${entry.id}`;
  const quantity = quantities.value[key];

  if (!quantity || quantity <= 0) {
    quantityErrors.value[key] = 'La cantidad debe ser mayor a 0';
  } else {
    delete quantityErrors.value[key];
  }
};

const validatePrice = (entry) => {
  const key = `${entry.tipo}-${entry.id}`;
  const price = prices.value[key];

  if (price < 0) {
    priceErrors.value[key] = 'El precio no puede ser negativo';
  } else {
    delete priceErrors.value[key];
  }
};

const isItemSelected = (item) => {
  return selectedItems.value.some(entry =>
    entry.id === item.id && entry.tipo === item.tipo
  );
};

const onProveedorSearch = () => {
  if (proveedorSearchTimer) {
    clearTimeout(proveedorSearchTimer);
  }

  loadingProveedores.value = true;
  proveedorSearchTimer = setTimeout(() => {
    loadingProveedores.value = false;
  }, 300);
};

const onProductoSearch = () => {
  if (productoSearchTimer) {
    clearTimeout(productoSearchTimer);
  }

  loadingProductos.value = true;
  productoSearchTimer = setTimeout(() => {
    loadingProductos.value = false;
  }, 300);
};

const seleccionarProveedor = (proveedor) => {
  form.proveedor_id = proveedor.id;
  buscarProveedor.value = proveedor.nombre_razon_social;
  proveedorSeleccionado.value = proveedor;
  mostrarProveedores.value = false;
};

const agregarProducto = (item) => {
  if (isItemSelected(item)) return;

  const itemEntry = { id: item.id, tipo: item.tipo };
  selectedItems.value.push(itemEntry);

  const key = `${item.tipo}-${item.id}`;
  quantities.value[key] = 1;
  prices.value[key] = item.tipo === 'producto'
    ? (item.precio_compra || 0)
    : (item.precio || 0);

  buscarProducto.value = '';
  mostrarProductos.value = false;
  calcularTotal();
};

const eliminarItem = (entry) => {
  const index = selectedItems.value.findIndex(item =>
    item.id === entry.id && item.tipo === entry.tipo
  );

  if (index > -1) {
    selectedItems.value.splice(index, 1);
    const key = `${entry.tipo}-${entry.id}`;
    delete quantities.value[key];
    delete prices.value[key];
    delete quantityErrors.value[key];
    delete priceErrors.value[key];
    calcularTotal();
  }
};

const limpiarItems = () => {
  selectedItems.value = [];
  quantities.value = {};
  prices.value = {};
  quantityErrors.value = {};
  priceErrors.value = {};
  calcularTotal();
};

const limpiarFormulario = () => {
  form.reset();
  buscarProveedor.value = '';
  buscarProducto.value = '';
  proveedorSeleccionado.value = null;
  limpiarItems();
  localStorage.removeItem('ordenCompraEnProgreso');
};

const ocultarProveedoresDespuesDeTiempo = (event) => {
  setTimeout(() => {
    if (!event.relatedTarget || !event.relatedTarget.classList.contains('proveedor-item')) {
      mostrarProveedores.value = false;
    }
  }, 200);
};

const ocultarProductosDespuesDeTiempo = (event) => {
  setTimeout(() => {
    if (!event.relatedTarget || !event.relatedTarget.classList.contains('producto-item')) {
      mostrarProductos.value = false;
    }
  }, 200);
};

const saveToLocalStorage = () => {
  const dataToSave = {
    proveedor_id: form.proveedor_id,
    proveedor_seleccionado: proveedorSeleccionado.value,
    proveedor_nombre: buscarProveedor.value,
    selectedItems: selectedItems.value,
    quantities: quantities.value,
    prices: prices.value,
    timestamp: Date.now(),
  };
  localStorage.setItem('ordenCompraEnProgreso', JSON.stringify(dataToSave));
};

const loadFromLocalStorage = () => {
  try {
    const savedData = localStorage.getItem('ordenCompraEnProgreso');
    if (!savedData) return;

    const parsedData = JSON.parse(savedData);

    // Check if data is not too old (24 hours)
    const isDataExpired = Date.now() - (parsedData.timestamp || 0) > 24 * 60 * 60 * 1000;
    if (isDataExpired) {
      localStorage.removeItem('ordenCompraEnProgreso');
      return;
    }

    form.proveedor_id = parsedData.proveedor_id || '';
    proveedorSeleccionado.value = parsedData.proveedor_seleccionado || null;
    buscarProveedor.value = parsedData.proveedor_nombre || '';

    selectedItems.value = Array.isArray(parsedData.selectedItems)
      ? parsedData.selectedItems.filter(entry =>
          entry && typeof entry === 'object' && 'id' in entry && 'tipo' in entry
        )
      : [];

    quantities.value = parsedData.quantities || {};
    prices.value = parsedData.prices || {};

    calcularTotal();

    console.log('Datos cargados desde localStorage:', parsedData);
  } catch (error) {
    console.error('Error al cargar datos desde localStorage:', error);
    localStorage.removeItem('ordenCompraEnProgreso');
  }
};

const handleBeforeUnload = (event) => {
  if (form.proveedor_id || selectedItems.value.length > 0) {
    event.preventDefault();
    event.returnValue = '¿Estás seguro de que quieres salir? Los cambios no guardados se perderán.';
  }
};

const crearOrdenCompra = async () => {
  // Validate form before submission
  const hasErrors = selectedItems.value.some(entry => {
    validateQuantity(entry);
    validatePrice(entry);
    return quantityErrors.value[`${entry.tipo}-${entry.id}`] ||
           priceErrors.value[`${entry.tipo}-${entry.id}`];
  });

  if (hasErrors) {
    console.error('Hay errores de validación en el formulario');
    return;
  }

  loading.value = true;

  try {
    form.items = selectedItems.value.map((entry) => ({
      id: entry.id,
      tipo: entry.tipo,
      cantidad: quantities.value[`${entry.tipo}-${entry.id}`] || 1,
      precio: prices.value[`${entry.tipo}-${entry.id}`] || 0,
    }));

    await form.post(route('ordenescompra.store'), {
      preserveScroll: true,
      onSuccess: () => {
        localStorage.removeItem('ordenCompraEnProgreso');
        // Show success message
        console.log('Orden de compra creada exitosamente');
        router.visit(route('ordenescompra.index'));
      },
      onError: (errors) => {
        console.error('Error al crear la orden de compra:', errors);
        loading.value = false;
      },
      onFinish: () => {
        loading.value = false;
      },
    });
  } catch (error) {
    console.error('Error inesperado:', error);
    loading.value = false;
  }
};

// Watchers
watch(
  [() => form.proveedor_id, selectedItems, quantities, prices, proveedorSeleccionado],
  () => {
    if (form.proveedor_id || selectedItems.value.length > 0) {
      saveToLocalStorage();
    }
  },
  { deep: true }
);

watch(quantities, calcularTotal, { deep: true });
watch(prices, calcularTotal, { deep: true });

// Lifecycle hooks
onMounted(() => {
  console.log('Componente montado');
  console.log('Proveedores:', props.proveedores?.length || 0);
  console.log('Productos:', props.productos?.length || 0);
  console.log('Servicios:', props.servicios?.length || 0);

  loadFromLocalStorage();
  window.addEventListener('beforeunload', handleBeforeUnload);
});

onBeforeUnmount(() => {
  window.removeEventListener('beforeunload', handleBeforeUnload);

  // Clear timers
  if (proveedorSearchTimer) {
    clearTimeout(proveedorSearchTimer);
  }
  if (productoSearchTimer) {
    clearTimeout(productoSearchTimer);
  }
});
</script>

<style scoped>
/* Base styles */
.card {
  background-color: #fff;
  box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);
  border-radius: 0.5rem;
  border: 1px solid #e5e7eb;
}

.card-header {
  @apply px-6 py-4 border-b border-gray-200 bg-gray-50;
}

.card-title {
  @apply text-lg font-semibold text-gray-900 flex items-center;
}

.card-body {
  @apply px-6 py-4;
}

/* Form styles */
.form-group {
  @apply space-y-2;
}

.form-label {
  @apply block text-sm font-medium text-gray-700;
}

.form-label.required::after {
  content: " *";
  @apply text-red-500;
}

.form-input {
  @apply mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm
         focus:ring-2 focus:ring-blue-500 focus:border-blue-500
         transition-colors duration-200;
}

.form-input-sm {
  @apply w-full px-3 py-2 text-sm border border-gray-300 rounded-md
         focus:ring-2 focus:ring-blue-500 focus:border-blue-500
         transition-colors duration-200;
}

.form-error {
  @apply text-sm text-red-600 mt-1;
}

/* Dropdown styles */
.dropdown-menu {
  @apply absolute z-50 mt-1 w-full bg-white border border-gray-300 rounded-lg shadow-lg
         max-h-64 overflow-y-auto;
}

.dropdown-item {
  @apply w-full px-4 py-3 text-left hover:bg-gray-50 focus:bg-gray-50
         focus:outline-none border-b border-gray-100 last:border-b-0
         transition-colors duration-150;
}

.dropdown-item:disabled {
  @apply opacity-50 cursor-not-allowed;
}

/* Button styles */
.btn-primary {
  @apply inline-flex items-center px-6 py-3 border border-transparent text-sm font-medium
         rounded-lg shadow-sm text-white bg-blue-600 hover:bg-blue-700
         focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500
         disabled:opacity-50 disabled:cursor-not-allowed
         transition-colors duration-200;
}

.btn-secondary {
  @apply inline-flex items-center px-6 py-3 border border-gray-300 text-sm font-medium
         rounded-lg shadow-sm text-gray-700 bg-white hover:bg-gray-50
         focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500
         disabled:opacity-50 disabled:cursor-not-allowed
         transition-colors duration-200;
}

/* Badge styles */
.badge {
  @apply inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium;
}

.badge-blue {
  @apply bg-blue-100 text-blue-800;
}

.badge-green {
  @apply bg-green-100 text-green-800;
}

/* Table styles */
.table-header {
  @apply px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider;
}

.table-cell {
  @apply px-6 py-4 whitespace-nowrap text-sm text-gray-900;
}

/* Animation styles */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

/* Responsive improvements */
@media (max-width: 768px) {
  .card-body {
    @apply px-4 py-3;
  }

  .card-header {
    @apply px-4 py-3;
  }

  .form-input {
    @apply px-3 py-2;
  }

  .table-cell {
    @apply px-3 py-2;
  }

  .table-header {
    @apply px-3 py-2;
  }
}

/* Loading animation */
@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.animate-spin {
  animation: spin 1s linear infinite;
}

/* Custom scrollbar for dropdowns */
.dropdown-menu::-webkit-scrollbar {
  width: 6px;
}

.dropdown-menu::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 3px;
}

.dropdown-menu::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 3px;
}

.dropdown-menu::-webkit-scrollbar-thumb:hover {
  background: #a8a8a8;
}

/* Focus styles for accessibility */
.dropdown-item:focus {
  @apply ring-2 ring-blue-500 ring-inset;
}

/* Hover effects */
.hover-lift:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

/* Print styles */
@media print {
  .card {
    @apply shadow-none border;
  }

  .btn-primary,
  .btn-secondary {
    @apply hidden;
  }
}
</style>
