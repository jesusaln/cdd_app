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
              <label for="numero_orden" class="block text-sm font-medium text-gray-700 mb-2 flex items-center gap-2">
                Número de Orden *
                <span class="inline-flex items-center gap-1 px-2 py-1 bg-blue-100 text-blue-700 text-xs font-medium rounded-full">
                  <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                  Número fijo
                </span>
              </label>
              <div class="relative">
                <input
                  id="numero_orden"
                  v-model="form.numero_orden"
                  type="text"
                  class="w-full bg-gray-50 text-gray-500 cursor-not-allowed border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                  placeholder="OC0001"
                  readonly
                  required
                />
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                  <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                  </svg>
                </div>
              </div>
              <div class="mt-2 flex items-center gap-2">
                <p class="text-xs text-gray-500">
                  Este número es fijo para todas las órdenes de compra
                </p>
                <button
                  @click="copiarNumeroOrden"
                  class="inline-flex items-center gap-1 px-2 py-1 bg-gray-100 text-gray-700 text-xs font-medium rounded hover:bg-gray-200 transition-colors"
                  title="Copiar número de orden"
                >
                  <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                  </svg>
                  Copiar
                </button>
              </div>
            </div>

            <!-- Fecha de Orden -->
            <div>
              <label for="fecha_orden" class="block text-sm font-medium text-gray-700 mb-2 flex items-center gap-2">
                Fecha de Orden *
                <span class="inline-flex items-center gap-1 px-2 py-1 bg-blue-100 text-blue-700 text-xs font-medium rounded-full">
                  <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
                  Automática
                </span>
              </label>
              <div class="relative">
                <input
                  id="fecha_orden"
                  v-model="form.fecha_orden"
                  type="date"
                  class="w-full bg-gray-50 text-gray-500 cursor-not-allowed border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                  readonly
                  required
                />
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                  <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                </div>
              </div>
              <p class="mt-1 text-xs text-gray-500">
                Esta fecha se establece automáticamente con la fecha de creación
              </p>
            </div>

            <!-- Fecha de Entrega Esperada -->
            <div>
              <label for="fecha_entrega_esperada" class="block text-sm font-medium text-gray-700 mb-2 flex items-center gap-2">
                Fecha de Entrega Esperada
                <button
                  @click="mostrarInfoFechas"
                  type="button"
                  class="text-gray-400 hover:text-gray-600 transition-colors"
                  title="Opciones de fechas rápidas disponibles"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                </button>
              </label>
              <input
                id="fecha_entrega_esperada"
                v-model="form.fecha_entrega_esperada"
                type="date"
                class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
              />

              <!-- Botones de fechas rápidas -->
              <div class="mt-3 flex flex-wrap gap-2">
                <button
                  @click="setFechaRapida('hoy')"
                  type="button"
                  class="inline-flex items-center gap-1 px-3 py-2 bg-blue-50 text-blue-700 text-sm font-medium rounded-lg hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors duration-200"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  Hoy
                </button>

                <button
                  @click="setFechaRapida('manana')"
                  type="button"
                  class="inline-flex items-center gap-1 px-3 py-2 bg-green-50 text-green-700 text-sm font-medium rounded-lg hover:bg-green-100 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-colors duration-200"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                  </svg>
                  Mañana
                </button>

                <button
                  @click="setFechaRapida('3dias')"
                  type="button"
                  class="inline-flex items-center gap-1 px-3 py-2 bg-yellow-50 text-yellow-700 text-sm font-medium rounded-lg hover:bg-yellow-100 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 transition-colors duration-200"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
                  +3 días
                </button>

                <button
                  @click="setFechaRapida('semana')"
                  type="button"
                  class="inline-flex items-center gap-1 px-3 py-2 bg-purple-50 text-purple-700 text-sm font-medium rounded-lg hover:bg-purple-100 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition-colors duration-200"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                  </svg>
                  Una semana
                </button>

                <button
                  @click="setFechaRapida('mes')"
                  type="button"
                  class="inline-flex items-center gap-1 px-3 py-2 bg-red-50 text-red-700 text-sm font-medium rounded-lg hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-colors duration-200"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
                  Un mes
                </button>
              </div>
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

            <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
              <div class="flex items-center gap-2 text-blue-700 text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="font-medium">Nota:</span>
                <span>Las órdenes de compra solo incluyen productos físicos para inventario.</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Productos y Servicios -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
          <div class="bg-gradient-to-r from-green-500 to-green-600 px-6 py-4">
            <h2 class="text-lg font-semibold text-white flex items-center">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012 2v2M7 7h10"/>
              </svg>
              Productos
            </h2>
          </div>
          <div class="p-6">
            <BuscarProducto
              ref="buscarProductoRef"
              :productos="props.productos"
              :servicios="[]"
              @agregar-producto="agregarProducto"
            />
            <ProductosSeleccionados
              :selected-products="selectedProducts"
              :productos="props.productos"
              :servicios="[]"
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
          :can-submit="form.proveedor_id && selectedProducts.length > 0"
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
        :productos="selectedProducts"
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
import { Head, useForm, router } from '@inertiajs/vue3';
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
});

// Número de orden fijo
const numeroOrdenFijo = 'OC0001';

// Copia reactiva de proveedores para evitar mutación de props
const proveedoresList = ref([...props.proveedores]);

// Obtener fecha actual en formato YYYY-MM-DD (zona horaria local)
const getCurrentDate = () => {
  const today = new Date();
  const year = today.getFullYear();
  const month = String(today.getMonth() + 1).padStart(2, '0');
  const day = String(today.getDate()).padStart(2, '0');
  return `${year}-${month}-${day}`;
};

// Formulario
const form = useForm({
  numero_orden: numeroOrdenFijo,
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
  items: [],
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

// Estado para controlar cambios en número de orden
const numeroOrdenOriginal = ref(numeroOrdenFijo);

// Función para mostrar información del número de orden
const mostrarInfoNumeroOrden = () => {
  const numeroActual = form.numero_orden || numeroOrdenFijo;
  showNotification(`Número de orden actual: ${numeroActual}`, 'info');
};

// Función para copiar el número de orden al portapapeles
const copiarNumeroOrden = async () => {
  const numeroACopiar = form.numero_orden.trim() || numeroOrdenFijo;

  try {
    await navigator.clipboard.writeText(numeroACopiar);
    showNotification(`Número copiado: ${numeroACopiar}`, 'success');
  } catch (error) {
    // Fallback para navegadores antiguos
    const textArea = document.createElement('textarea');
    textArea.value = numeroACopiar;
    document.body.appendChild(textArea);
    textArea.select();
    document.execCommand('copy');
    document.body.removeChild(textArea);
    showNotification(`Número copiado: ${numeroACopiar}`, 'success');
  }
};

// Función para asegurar que la fecha sea siempre la actual
const asegurarFechaActual = () => {
  const fechaActual = getCurrentDate();
  if (form.fecha_orden !== fechaActual) {
    form.fecha_orden = fechaActual;
  }
};

// Función para establecer fechas rápidas
const setFechaRapida = (tipo) => {
  const hoy = new Date();
  let fechaCalculada = new Date(hoy);

  switch (tipo) {
    case 'hoy':
      // Ya es hoy, no cambiar
      break;
    case 'manana':
      fechaCalculada.setDate(hoy.getDate() + 1);
      break;
    case '3dias':
      fechaCalculada.setDate(hoy.getDate() + 3);
      break;
    case 'semana':
      fechaCalculada.setDate(hoy.getDate() + 7);
      break;
    case 'mes':
      fechaCalculada.setMonth(hoy.getMonth() + 1);
      break;
  }

  // Formatear la fecha como YYYY-MM-DD
  const year = fechaCalculada.getFullYear();
  const month = String(fechaCalculada.getMonth() + 1).padStart(2, '0');
  const day = String(fechaCalculada.getDate()).padStart(2, '0');
  const fechaFormateada = `${year}-${month}-${day}`;

  form.fecha_entrega_esperada = fechaFormateada;

  // Mostrar notificación
  const etiquetas = {
    hoy: 'Hoy',
    manana: 'Mañana',
    '3dias': 'En 3 días',
    semana: 'En una semana',
    mes: 'En un mes'
  };

  showNotification(`Fecha de entrega establecida: ${etiquetas[tipo]} (${fechaFormateada})`, 'success');
};

// Función para mostrar información sobre fechas rápidas
const mostrarInfoFechas = () => {
  showNotification('Usa los botones para establecer fechas de entrega comunes', 'info');
};

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
  if (proveedorSeleccionado.value && selectedProducts.value.length > 0) {
    mostrarVistaPrevia.value = true;
  } else {
    showNotification('Selecciona un proveedor y al menos un producto', 'error');
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
  if (!item || typeof item.id === 'undefined') {
    showNotification('Producto inválido', 'error');
    return;
  }

  // Solo permitir productos, no servicios
  const itemEntry = { id: item.id, tipo: 'producto' };
  const exists = selectedProducts.value.some(
    (entry) => entry.id === item.id && entry.tipo === 'producto'
  );

  if (!exists) {
    selectedProducts.value.push(itemEntry);
    const key = `producto-${item.id}`;
    quantities.value[key] = 1;

    // Solo productos, usar precio_compra
    const precio = typeof item.precio_compra === 'number' ? item.precio_compra : 0;
    prices.value[key] = precio;
    discounts.value[key] = 0;
    calcularTotal();
    saveState();
    showNotification(`Producto añadido: ${item.nombre || item.descripcion || 'Producto'}`);
  } else {
    showNotification('Este producto ya está en la lista', 'info');
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
  showNotification(`Producto eliminado: ${entry.nombre || entry.descripcion || 'Producto'}`, 'info');
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

  // Validar formato del número de orden fijo
  const numeroRegex = /^OC0001$/;
  if (!numeroRegex.test(form.numero_orden.trim())) {
    showNotification('El número de orden debe ser OC0001', 'error');
    return false;
  }

  if (!form.fecha_orden) {
    showNotification('Fecha de orden no disponible', 'error');
    return false;
  }

  if (!form.proveedor_id) {
    showNotification('Selecciona un proveedor', 'error');
    return false;
  }

  if (selectedProducts.value.length === 0) {
    showNotification('Agrega al menos un producto', 'error');
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

  // Asegurar que la fecha sea la actual antes de enviar
  asegurarFechaActual();

  form.items = selectedProducts.value.map((entry) => {
    const key = `producto-${entry.id}`;
    return {
      id: entry.id,
      tipo: 'producto',
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
      // Usar el número de orden fijo
      form.numero_orden = numeroOrdenFijo;
      // Mantener la fecha de orden como automática (siempre la fecha actual)
      form.fecha_orden = getCurrentDate();
      form.prioridad = 'media';
      form.terminos_pago = '30_dias';
      form.metodo_pago = 'transferencia';
      form.estado = 'pendiente';
      showNotification('Orden de compra creada con éxito');

      // Redirigir al index para mostrar la nueva orden
      router.visit(route('ordenescompra.index'));
    },
    onError: (errors) => {
      console.error('Errores de validación:', errors);

      // Verificar si es un error de CSRF token
      if (errors && typeof errors === 'object' && errors.message && errors.message.includes('CSRF token mismatch')) {
        showNotification('La sesión ha expirado. Refrescando la página...', 'error');
        setTimeout(() => {
          window.location.reload();
        }, 2000);
        return;
      }

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
  // Usar el número de orden fijo
  form.numero_orden = numeroOrdenFijo;
  // Asegurar que la fecha sea la actual
  asegurarFechaActual();
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
    numero_orden: numeroOrdenFijo,
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
  // Mostrar información sobre el número de orden fijo
  showNotification(`Número de orden fijo: ${numeroOrdenFijo}`, 'info');

  const savedData = loadFromLocalStorage('ordenCompraEnProgreso');
  if (savedData && typeof savedData === 'object') {
    try {
      // Mantener el número de orden fijo o usar el guardado
      form.numero_orden = savedData.numero_orden || numeroOrdenFijo;
      // Mantener la fecha de orden como automática (siempre usar fecha actual)
      form.fecha_orden = getCurrentDate();
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
      removeFromLocalStorage('ordenCompraEnProgreso');
    }
  }

  // Verificar la fecha cada 5 minutos para mantenerla actual
  const fechaInterval = setInterval(() => {
    asegurarFechaActual();
  }, 5 * 60 * 1000); // 5 minutos

  window.addEventListener('beforeunload', handleBeforeUnload);

  // Limpiar el intervalo cuando el componente se desmonte
  onBeforeUnmount(() => {
    clearInterval(fechaInterval);
  });
});

onBeforeUnmount(() => {
  window.removeEventListener('beforeunload', handleBeforeUnload);
});
</script>
