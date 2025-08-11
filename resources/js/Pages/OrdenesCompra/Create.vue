<!-- Resources/js/Pages/OrdenesCompra/CrearOrdenCompra.vue -->
<template>
  <Head title="Crear Orden de Compra" />
  <div class="ordenes-compra-create min-h-screen bg-gradient-to-br from-slate-50 to-blue-50 p-6">
    <div class="max-w-6xl mx-auto">
      <!-- Header -->
      <Header
        title="Nueva Orden de Compra"
        description="Crea una nueva orden de compra para tus proveedores"
        :can-preview="proveedorSeleccionado && selectedProducts.length > 0"
        :back-url="route('ordenescompra.index')"
        :show-shortcuts="mostrarAtajos"
        @preview="handlePreview"
        @close-shortcuts="mostrarAtajos = false"
      />

      <form @submit.prevent="crearOrdenCompra" class="space-y-8">
        <!-- Información General -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
          <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 px-6 py-4">
            <h2 class="text-lg font-semibold text-white flex items-center">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
              </svg>
              Información General
            </h2>
          </div>
          <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Número de Orden -->
            <div>
              <label for="numero_orden" class="block text-sm font-medium text-gray-700 mb-2">
                Número de Orden *
              </label>
              <input
                id="numero_orden"
                v-model="form.numero_orden"
                type="text"
                class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                placeholder="Ej: OC-2024-001"
                required
              />
            </div>

            <!-- Fecha de Orden -->
            <div>
              <label for="fecha_orden" class="block text-sm font-medium text-gray-700 mb-2">
                Fecha de Orden *
              </label>
              <input
                id="fecha_orden"
                v-model="form.fecha_orden"
                type="date"
                class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                required
              />
            </div>

            <!-- Fecha de Entrega Esperada -->
            <div>
              <label for="fecha_entrega_esperada" class="block text-sm font-medium text-gray-700 mb-2">
                Fecha de Entrega Esperada
              </label>
              <input
                id="fecha_entrega_esperada"
                v-model="form.fecha_entrega_esperada"
                type="date"
                class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
              />
            </div>

            <!-- Prioridad -->
            <div>
              <label for="prioridad" class="block text-sm font-medium text-gray-700 mb-2">
                Prioridad
              </label>
              <select
                id="prioridad"
                v-model="form.prioridad"
                class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
              >
                <option value="baja">Baja</option>
                <option value="media">Media</option>
                <option value="alta">Alta</option>
                <option value="urgente">Urgente</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Proveedor -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
          <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-4">
            <h2 class="text-lg font-semibold text-white flex items-center">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
              </svg>
              Información del Proveedor
            </h2>
          </div>
          <div class="p-6">
            <BuscarProveedor
              :proveedores="proveedoresList"
              :proveedor-seleccionado="proveedorSeleccionado"
              label-busqueda="Proveedor"
              placeholder-busqueda="Buscar proveedor por nombre, RFC, email..."
              requerido
              @proveedor-seleccionado="onProveedorSeleccionado"
            />
          </div>
        </div>

        <!-- Productos y Servicios -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
          <div class="bg-gradient-to-r from-green-500 to-green-600 px-6 py-4">
            <h2 class="text-lg font-semibold text-white flex items-center">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012 2v2M7 7h10"/>
              </svg>
              Productos y Servicios
            </h2>
          </div>
          <div class="p-6">
            <BuscarProducto
              ref="buscarProductoRef"
              :productos="props.productos"
              :servicios="props.servicios"
              @agregar-producto="agregarProducto"
            />
            <ProductosSeleccionados
              :selected-products="selectedProducts"
              :productos="props.productos"
              :servicios="props.servicios"
              :quantities="quantities"
              :prices="prices"
              :discounts="discounts"
              @eliminar-producto="eliminarProducto"
              @update-quantity="updateQuantity"
              @update-discount="updateDiscount"
            />
          </div>
        </div>

        <!-- Condiciones de Entrega -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
          <div class="bg-gradient-to-r from-orange-500 to-orange-600 px-6 py-4">
            <h2 class="text-lg font-semibold text-white flex items-center">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
              </svg>
              Condiciones de Entrega
            </h2>
          </div>
          <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Dirección de Entrega -->
            <div class="md:col-span-2">
              <label for="direccion_entrega" class="block text-sm font-medium text-gray-700 mb-2">
                Dirección de Entrega
              </label>
              <textarea
                id="direccion_entrega"
                v-model="form.direccion_entrega"
                class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-orange-500 focus:border-transparent resize-vertical"
                rows="3"
                placeholder="Especifica la dirección donde se debe entregar el pedido..."
              ></textarea>
            </div>

            <!-- Términos de Pago -->
            <div>
              <label for="terminos_pago" class="block text-sm font-medium text-gray-700 mb-2">
                Términos de Pago
              </label>
              <select
                id="terminos_pago"
                v-model="form.terminos_pago"
                class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-orange-500 focus:border-transparent"
              >
                <option value="contado">Contado</option>
                <option value="15_dias">15 días</option>
                <option value="30_dias">30 días</option>
                <option value="45_dias">45 días</option>
                <option value="60_dias">60 días</option>
                <option value="90_dias">90 días</option>
              </select>
            </div>

            <!-- Método de Pago -->
            <div>
              <label for="metodo_pago" class="block text-sm font-medium text-gray-700 mb-2">
                Método de Pago
              </label>
              <select
                id="metodo_pago"
                v-model="form.metodo_pago"
                class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-orange-500 focus:border-transparent"
              >
                <option value="transferencia">Transferencia Bancaria</option>
                <option value="cheque">Cheque</option>
                <option value="efectivo">Efectivo</option>
                <option value="tarjeta">Tarjeta de Crédito</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Notas y Observaciones -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
          <div class="bg-gradient-to-r from-purple-500 to-purple-600 px-6 py-4">
            <h2 class="text-lg font-semibold text-white flex items-center">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
              </svg>
              Notas y Observaciones
            </h2>
          </div>
          <div class="p-6">
            <textarea
              v-model="form.observaciones"
              class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-purple-500 focus:border-transparent resize-vertical"
              rows="4"
              placeholder="Agrega observaciones, especificaciones técnicas, términos y condiciones especiales..."
            ></textarea>
          </div>
        </div>

        <!-- Totales -->
        <Totales
          :show-margin-calculator="false"
          :margin-data="{ costoTotal: 0, precioVenta: 0, ganancia: 0, margenPorcentaje: 0 }"
          :totals="totales"
          :item-count="selectedProducts.length"
          :total-quantity="Object.values(quantities).reduce((sum, qty) => sum + (qty || 0), 0)"
          @update:descuento-general="val => form.descuento_general = val"
        />

        <!-- Botones -->
        <BotonesAccion
          :back-url="route('ordenescompra.index')"
          :is-processing="form.processing"
          :can-submit="form.numero_orden && form.proveedor_id && selectedProducts.length > 0"
          :button-text="form.processing ? 'Guardando...' : 'Crear Orden de Compra'"
          @limpiar="limpiarFormulario"
        />
      </form>

      <!-- Atajos de teclado -->
      <button
        @click="mostrarAtajos = !mostrarAtajos"
        class="fixed bottom-4 left-4 bg-gray-600 text-white p-3 rounded-full shadow-lg hover:bg-gray-700 transition-colors duration-200"
        title="Mostrar atajos de teclado"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
      </button>

      <!-- Modal Vista Previa -->
      <VistaPreviaModal
        :show="mostrarVistaPrevia"
        type="ordenescompra"
        :proveedor="proveedorSeleccionado"
        :items="selectedProducts"
        :totals="totales"
        :notas="form.observaciones"
        :orden-data="{
          numero_orden: form.numero_orden,
          fecha_orden: form.fecha_orden,
          fecha_entrega_esperada: form.fecha_entrega_esperada,
          prioridad: form.prioridad,
          direccion_entrega: form.direccion_entrega,
          terminos_pago: form.terminos_pago,
          metodo_pago: form.metodo_pago
        }"
        @close="mostrarVistaPrevia = false"
        @print="() => window.print()"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Notyf } from 'notyf';
import AppLayout from '@/Layouts/AppLayout.vue';
import Header from '@/Components/CreateComponents/Header.vue';
import BuscarProveedor from '@/Components/CreateComponents/BuscarProveedor.vue';
import BuscarProducto from '@/Components/CreateComponents/BuscarProducto.vue';
import ProductosSeleccionados from '@/Components/CreateComponents/ProductosSeleccionados.vue';
import Totales from '@/Components/CreateComponents/Totales.vue';
import BotonesAccion from '@/Components/CreateComponents/BotonesAccion.vue';
import VistaPreviaModal from '@/Components/Modals/VistaPreviaModal.vue';

// Inicializar notificaciones
const notyf = new Notyf({
  duration: 5000,
  position: { x: 'right', y: 'top' },
  types: [
    { type: 'success', background: '#10B981', icon: { className: 'notyf__icon--success', tagName: 'i', text: '✓' } },
    { type: 'error', background: '#EF4444', icon: { className: 'notyf__icon--error', tagName: 'i', text: '✗' } },
    { type: 'info', background: '#3B82F6', icon: { className: 'notyf__icon--info', tagName: 'i', text: 'ℹ' } },
  ],
});

const showNotification = (message, type = 'success') => {
  notyf.open({ type, message });
};

// Usar layout
defineOptions({ layout: AppLayout });

// Props
const props = defineProps({
  proveedores: { type: Array, default: () => [] },
  productos: { type: Array, default: () => [] },
  servicios: { type: Array, default: () => [] },
});

// Copia reactiva de proveedores para evitar mutación de props
const proveedoresList = ref([...props.proveedores]);

// Obtener fecha actual en formato YYYY-MM-DD
const getCurrentDate = () => {
  const today = new Date();
  return today.toISOString().split('T')[0];
};

// Formulario
const form = useForm({
  numero_orden: '',
  fecha_orden: getCurrentDate(),
  fecha_entrega_esperada: '',
  prioridad: 'media',
  proveedor_id: '',
  direccion_entrega: '',
  terminos_pago: '30_dias',
  metodo_pago: 'transferencia',
  subtotal: 0,
  descuento_items: 0,
  descuento_general: 0,
  iva: 0,
  total: 0,
  productos: [],
  observaciones: '',
  estado: 'pendiente',
});

// Referencias
const buscarProductoRef = ref(null);
const proveedorSeleccionado = ref(null);
const selectedProducts = ref([]);
const quantities = ref({});
const prices = ref({});
const discounts = ref({});
const mostrarVistaPrevia = ref(false);
const mostrarAtajos = ref(true);

// Funciones de localStorage (comentadas porque no están disponibles en Claude)
const saveToLocalStorage = (key, data) => {
  // try {
  //   localStorage.setItem(key, JSON.stringify(data));
  // } catch (error) {
  //   console.warn('No se pudo guardar en localStorage:', error);
  // }
};

const loadFromLocalStorage = (key) => {
  // try {
  //   const item = localStorage.getItem(key);
  //   return item ? JSON.parse(item) : null;
  // } catch (error) {
  //   console.warn('No se pudo cargar desde localStorage:', error);
  //   return null;
  // }
  return null;
};

const removeFromLocalStorage = (key) => {
  // try {
  //   localStorage.removeItem(key);
  // } catch (error) {
  //   console.warn('No se pudo eliminar de localStorage:', error);
  // }
};

// Funciones
const handlePreview = () => {
  if (proveedorSeleccionado.value && selectedProducts.value.length > 0 && form.numero_orden) {
    mostrarVistaPrevia.value = true;
  } else {
    showNotification('Completa el número de orden, selecciona un proveedor y al menos un producto', 'error');
  }
};

const onProveedorSeleccionado = (proveedor) => {
  if (!proveedor) {
    proveedorSeleccionado.value = null;
    form.proveedor_id = '';
    saveState();
    showNotification('Selección de proveedor limpiada', 'info');
    return;
  }
  if (proveedorSeleccionado.value?.id === proveedor.id) return;
  proveedorSeleccionado.value = proveedor;
  form.proveedor_id = proveedor.id;
  saveState();
  showNotification(`Proveedor seleccionado: ${proveedor.nombre_razon_social}`);
};

const agregarProducto = (item) => {
  if (!item || typeof item.id === 'undefined' || !item.tipo) {
    showNotification('Producto inválido', 'error');
    return;
  }

  const itemEntry = { id: item.id, tipo: item.tipo };
  const exists = selectedProducts.value.some(
    (entry) => entry.id === item.id && entry.tipo === item.tipo
  );

  if (!exists) {
    selectedProducts.value.push(itemEntry);
    const key = `${item.tipo}-${item.id}`;
    quantities.value[key] = 1;

    let precio = 0;
    if (item.tipo === 'producto') {
      precio = typeof item.precio_compra === 'number' ? item.precio_compra : 0;
    } else {
      precio = typeof item.precio === 'number' ? item.precio : 0;
    }

    prices.value[key] = precio;
    discounts.value[key] = 0;
    calcularTotal();
    saveState();
    showNotification(`Producto añadido: ${item.nombre || item.descripcion || 'Item'}`);
  }
};

const eliminarProducto = (entry) => {
  if (!entry || typeof entry.id === 'undefined' || !entry.tipo) {
    return;
  }

  const key = `${entry.tipo}-${entry.id}`;
  selectedProducts.value = selectedProducts.value.filter(
    (item) => !(item.id === entry.id && item.tipo === entry.tipo)
  );
  delete quantities.value[key];
  delete prices.value[key];
  delete discounts.value[key];
  calcularTotal();
  saveState();
  showNotification(`Producto eliminado: ${entry.nombre || entry.descripcion || 'Item'}`, 'info');
};

const updateQuantity = (key, quantity) => {
  const numQuantity = parseFloat(quantity);
  if (isNaN(numQuantity) || numQuantity < 0) {
    return;
  }
  quantities.value[key] = numQuantity;
  calcularTotal();
  saveState();
};

const updateDiscount = (key, discount) => {
  const numDiscount = parseFloat(discount);
  if (isNaN(numDiscount) || numDiscount < 0 || numDiscount > 100) {
    return;
  }
  discounts.value[key] = numDiscount;
  calcularTotal();
  saveState();
};

const totales = computed(() => {
  let subtotal = 0;
  let descuentoItems = 0;

  selectedProducts.value.forEach(entry => {
    const key = `${entry.tipo}-${entry.id}`;
    const cantidad = parseFloat(quantities.value[key]) || 0;
    const precio = parseFloat(prices.value[key]) || 0;
    const descuento = parseFloat(discounts.value[key]) || 0;

    if (cantidad > 0 && precio >= 0) {
      const subtotalItem = cantidad * precio;
      descuentoItems += subtotalItem * (descuento / 100);
      subtotal += subtotalItem;
    }
  });

  const descuentoGeneral = parseFloat(form.descuento_general) || 0;
  const subtotalConDescuentos = Math.max(0, subtotal - descuentoItems);
  const subtotalConDescuentoGeneral = Math.max(0, subtotalConDescuentos - (subtotalConDescuentos * descuentoGeneral / 100));
  const iva = subtotalConDescuentoGeneral * 0.16;
  const total = subtotalConDescuentoGeneral + iva;

  return {
    subtotal: parseFloat(subtotal.toFixed(2)),
    descuentoItems: parseFloat(descuentoItems.toFixed(2)),
    descuentoGeneral: parseFloat((subtotalConDescuentos * descuentoGeneral / 100).toFixed(2)),
    subtotalConDescuentos: parseFloat(subtotalConDescuentoGeneral.toFixed(2)),
    iva: parseFloat(iva.toFixed(2)),
    total: parseFloat(total.toFixed(2)),
  };
});

const calcularTotal = () => {
  form.subtotal = totales.value.subtotal;
  form.descuento_items = totales.value.descuentoItems;
  form.iva = totales.value.iva;
  form.total = totales.value.total;
};

const validarDatos = () => {
  if (!form.numero_orden.trim()) {
    showNotification('Ingresa el número de orden', 'error');
    return false;
  }

  if (!form.fecha_orden) {
    showNotification('Selecciona la fecha de orden', 'error');
    return false;
  }

  if (!form.proveedor_id) {
    showNotification('Selecciona un proveedor', 'error');
    return false;
  }

  if (selectedProducts.value.length === 0) {
    showNotification('Agrega al menos un producto o servicio', 'error');
    return false;
  }

  for (const entry of selectedProducts.value) {
    const key = `${entry.tipo}-${entry.id}`;
    const discount = parseFloat(discounts.value[key]) || 0;
    const quantity = parseFloat(quantities.value[key]) || 0;
    const price = parseFloat(prices.value[key]) || 0;

    if (discount < 0 || discount > 100) {
      showNotification('Los descuentos deben estar entre 0% y 100%.', 'error');
      return false;
    }

    if (quantity <= 0) {
      showNotification('Las cantidades deben ser mayores a 0', 'error');
      return false;
    }

    if (price < 0) {
      showNotification('Los precios no pueden ser negativos', 'error');
      return false;
    }
  }

  return true;
};

const crearOrdenCompra = () => {
  if (!validarDatos()) {
    return;
  }

  form.productos = selectedProducts.value.map((entry) => {
    const key = `${entry.tipo}-${entry.id}`;
    return {
      id: entry.id,
      tipo: entry.tipo,
      cantidad: parseFloat(quantities.value[key]) || 1,
      precio: parseFloat(prices.value[key]) || 0,
      descuento: parseFloat(discounts.value[key]) || 0,
    };
  });

  calcularTotal();

  form.post(route('ordenescompra.store'), {
    onSuccess: () => {
      removeFromLocalStorage('ordenCompraEnProgreso');
      selectedProducts.value = [];
      quantities.value = {};
      prices.value = {};
      discounts.value = {};
      proveedorSeleccionado.value = null;
      form.reset();
      form.fecha_orden = getCurrentDate();
      form.prioridad = 'media';
      form.terminos_pago = '30_dias';
      form.metodo_pago = 'transferencia';
      form.estado = 'pendiente';
      showNotification('Orden de compra creada con éxito');
    },
    onError: (errors) => {
      console.error('Errores de validación:', errors);
      const firstError = Object.values(errors)[0];
      if (Array.isArray(firstError)) {
        showNotification(firstError[0], 'error');
      } else {
        showNotification('Hubo errores de validación', 'error');
      }
    },
  });
};

const limpiarFormulario = () => {
  proveedorSeleccionado.value = null;
  form.numero_orden = '';
  form.fecha_orden = getCurrentDate();
  form.fecha_entrega_esperada = '';
  form.prioridad = 'media';
  form.proveedor_id = '';
  form.direccion_entrega = '';
  form.terminos_pago = '30_dias';
  form.metodo_pago = 'transferencia';
  form.observaciones = '';
  form.descuento_general = 0;
  selectedProducts.value = [];
  quantities.value = {};
  prices.value = {};
  discounts.value = {};
  removeFromLocalStorage('ordenCompraEnProgreso');
  showNotification('Formulario limpiado correctamente');
};

const saveState = () => {
  const stateToSave = {
    numero_orden: form.numero_orden,
    fecha_orden: form.fecha_orden,
    fecha_entrega_esperada: form.fecha_entrega_esperada,
    prioridad: form.prioridad,
    proveedor_id: form.proveedor_id,
    proveedor: proveedorSeleccionado.value,
    direccion_entrega: form.direccion_entrega,
    terminos_pago: form.terminos_pago,
    metodo_pago: form.metodo_pago,
    observaciones: form.observaciones,
    descuento_general: form.descuento_general,
    selectedProducts: selectedProducts.value,
    quantities: quantities.value,
    prices: prices.value,
    discounts: discounts.value,
  };
  saveToLocalStorage('ordenCompraEnProgreso', stateToSave);
};

const handleBeforeUnload = (event) => {
  if (form.numero_orden || form.proveedor_id || selectedProducts.value.length > 0) {
    event.preventDefault();
    event.returnValue = 'Tienes cambios sin guardar. ¿Estás seguro de que quieres salir?';
  }
};

// Lifecycle hooks
onMounted(() => {
  const savedData = loadFromLocalStorage('ordenCompraEnProgreso');
  if (savedData && typeof savedData === 'object') {
    try {
      form.numero_orden = savedData.numero_orden || '';
      form.fecha_orden = savedData.fecha_orden || getCurrentDate();
      form.fecha_entrega_esperada = savedData.fecha_entrega_esperada || '';
      form.prioridad = savedData.prioridad || 'media';
      form.proveedor_id = savedData.proveedor_id || '';
      form.direccion_entrega = savedData.direccion_entrega || '';
      form.terminos_pago = savedData.terminos_pago || '30_dias';
      form.metodo_pago = savedData.metodo_pago || 'transferencia';
      form.observaciones = savedData.observaciones || '';
      form.descuento_general = savedData.descuento_general || 0;
      proveedorSeleccionado.value = savedData.proveedor || null;
      selectedProducts.value = Array.isArray(savedData.selectedProducts) ? savedData.selectedProducts : [];
      quantities.value = savedData.quantities || {};
      prices.value = savedData.prices || {};
      discounts.value = savedData.discounts || {};
      calcularTotal();
    } catch (error) {
      console.warn('Error al cargar datos guardados:', error);
      removeFromLocalStorage('ordenCompraEnProgreso');
    }
  }

  window.addEventListener('beforeunload', handleBeforeUnload);
});

onBeforeUnmount(() => {
  window.removeEventListener('beforeunload', handleBeforeUnload);
});
</script>
