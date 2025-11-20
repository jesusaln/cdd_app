<!-- /resources/js/Pages/Ventas/Create.vue -->
<template>
  <Head title="Crear Venta" />
  <div class="ventas-create min-h-screen bg-gradient-to-br from-slate-50 to-blue-50 p-6">
    <div class="max-w-6xl mx-auto">
      <!-- Header -->
      <Header
        title="Nueva Venta"
        description="Crea una nueva venta para tus clientes"
        :can-preview="clienteSeleccionado && selectedProducts.length > 0"
        :back-url="route('ventas.index')"
        :show-shortcuts="mostrarAtajos"
        @preview="handlePreview"
        @close-shortcuts="closeShortcuts"
      />

      <form @submit.prevent="crearVenta" class="space-y-8">
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
          <div class="p-6 grid grid-cols-1 md:grid-cols-4 gap-6">
            <!-- Número de Venta -->
            <div>
              <label for="numero_venta" class="block text-sm font-medium text-gray-700 mb-2 flex items-center gap-2">
                Número de Venta *
                <span class="inline-flex items-center gap-1 px-2 py-1 bg-blue-100 text-blue-700 text-xs font-medium rounded-full">
                  <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                  Número fijo
                </span>
              </label>
              <div class="relative">
                <input
                  id="numero_venta"
                  v-model="form.numero_venta"
                  type="text"
                  class="w-full bg-gray-50 text-gray-500 cursor-not-allowed border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                  placeholder="V0001"
                  readonly
                  required
                />
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                  <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                  </svg>
                </div>
              </div>
              <p class="mt-1 text-xs text-gray-500">
                Este número es fijo para todas las ventas
              </p>
            </div>

            <!-- Fecha de Venta -->
            <div>
              <label for="fecha" class="block text-sm font-medium text-gray-700 mb-2 flex items-center gap-2">
                Fecha de Venta *
                <span class="inline-flex items-center gap-1 px-2 py-1 bg-blue-100 text-blue-700 text-xs font-medium rounded-full">
                  <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
                  Automática
                </span>
              </label>
              <div class="relative">
                <input
                  id="fecha"
                  v-model="form.fecha"
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

            <!-- Almacén -->
            <div>
              <label for="almacen_id" class="block text-sm font-medium text-gray-700 mb-2 flex items-center gap-2">
                Almacén *
                <span class="inline-flex items-center gap-1 px-2 py-1 bg-green-100 text-green-700 text-xs font-medium rounded-full">
                  <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                  </svg>
                  Requerido
                </span>
              </label>
              <div class="relative">
                <select
                  id="almacen_id"
                  v-model="form.almacen_id"
                  class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                  required
                >
                  <option value="">Selecciona un almacén</option>
                  <option
                    v-for="almacen in almacenes"
                    :key="almacen.id"
                    :value="almacen.id"
                    :selected="almacen.id === userAlmacenPredeterminado"
                  >
                    {{ almacen.nombre }}
                    <span v-if="almacen.id === userAlmacenPredeterminado" class="text-xs text-blue-600">(Predeterminado)</span>
                  </option>
                </select>
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                  <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                  </svg>
                </div>
              </div>
              <p class="mt-1 text-xs text-gray-500">
                Selecciona el almacén desde donde se venderán los productos
                <span v-if="userAlmacenPredeterminado" class="text-blue-600">
                  · Tu almacén predeterminado está preseleccionado
                </span>
              </p>
            </div>

            <!-- Método de Pago -->
            <div>
              <label for="metodo_pago" class="block text-sm font-medium text-gray-700 mb-2 flex items-center gap-2">
                Método de Pago *
                <span class="inline-flex items-center gap-1 px-2 py-1 bg-green-100 text-green-700 text-xs font-medium rounded-full">
                  <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                  Requerido
                </span>
              </label>
              <div class="relative">
                <select
                  id="metodo_pago"
                  v-model="form.metodo_pago"
                  class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                  required
                >
                  <option value="">Selecciona un método</option>
                  <option value="efectivo">Efectivo</option>
                  <option value="transferencia">Transferencia</option>
                  <option value="cheque">Cheque</option>
                  <option value="tarjeta">Tarjeta</option>
                  <option value="otros">Otros</option>
                </select>
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                  <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                  </svg>
                </div>
              </div>
              <p class="mt-1 text-xs text-gray-500">Selecciona cómo se paga la venta.</p>
            </div>
          </div>
        </div>

        <!-- Cliente -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
          <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-4">
            <h2 class="text-lg font-semibold text-white flex items-center">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
              </svg>
              Información del Cliente
            </h2>
          </div>
          <div class="p-6">
            <BuscarCliente
              ref="buscarClienteRef"
              :clientes="clientesList"
              :cliente-seleccionado="clienteSeleccionado"
              @cliente-seleccionado="onClienteSeleccionado"
              @crear-nuevo-cliente="crearNuevoCliente"
            />
          </div>
        </div>

        <!-- Productos y Servicios -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
          <div class="bg-gradient-to-r from-green-500 to-green-600 px-6 py-4">
            <h2 class="text-lg font-semibold text-white flex items-center">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
              </svg>
              Productos y Servicios
            </h2>
          </div>
          <div class="p-6">
            <BuscarProducto
              ref="buscarProductoRef"
              :productos="productos"
              :servicios="servicios"
              @agregar-producto="agregarProducto"
            />
            <PySSeleccionados
              :selectedProducts="selectedProducts"
              :productos="productos"
              :servicios="servicios"
              :quantities="quantities"
              :prices="prices"
              :discounts="discounts"
              :serials="serialsMap"
              @eliminar-producto="eliminarProducto"
              @update-quantity="updateQuantity"
              @update-discount="updateDiscount"
              @update-serials="updateSerials"
              @open-serials="openSerials"
            />
          </div>
        </div>

        <!-- Notas -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
          <div class="bg-gradient-to-r from-purple-500 to-purple-600 px-6 py-4">
            <h2 class="text-lg font-semibold text-white flex items-center">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
              </svg>
              Notas Adicionales
            </h2>
          </div>
          <div class="p-6">
            <textarea
              v-model="form.notas"
              class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-vertical"
              rows="4"
              placeholder="Agrega notas adicionales, términos y condiciones, o información relevante para la venta..."
            ></textarea>
          </div>
        </div>

        <!-- Advertencia de Márgenes -->
        <div v-if="requiereConfirmacionMargen" class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
          <div class="flex items-start">
            <svg class="w-5 h-5 text-yellow-600 mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
            </svg>
            <div class="flex-1">
              <h3 class="text-sm font-medium text-yellow-800 mb-2">⚠️ Productos con margen insuficiente</h3>
              <div class="text-sm text-yellow-700 mb-3 whitespace-pre-line">{{ mensajeAdvertenciaMargen }}</div>
              <div class="flex gap-3">
                <button
                  @click="aceptarAjusteMargen"
                  class="inline-flex items-center gap-2 px-4 py-2 bg-yellow-600 text-white text-sm font-medium rounded-lg hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 transition-colors"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                  </svg>
                  Ajustar automáticamente
                </button>
                <button
                  @click="cancelarAjusteMargen"
                  class="inline-flex items-center gap-2 px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                  </svg>
                  Revisar precios
                </button>
              </div>
            </div>
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
          :back-url="route('ventas.index')"
          :is-processing="form.processing"
          :can-submit="form.cliente_id && form.almacen_id && form.metodo_pago && selectedProducts.length > 0"
          :button-text="form.processing ? 'Guardando...' : 'Crear Venta'"
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
    </div>
  </div>

  <!-- Modal Vista Previa -->
  <VistaPreviaModal
    :show="mostrarVistaPrevia"
    type="venta"
    :cliente="clienteSeleccionado"
    :items="selectedProducts"
    :totals="totales"
    :notas="form.notas"
    @close="mostrarVistaPrevia = false"
    @print="() => window.print()"
  />

  <!-- Modal Crear Cliente -->
  <CrearClienteModal
    :show="mostrarModalCliente"
    :catalogs="catalogs"
    :nombre-inicial="nombreClienteBuscado"
    @close="mostrarModalCliente = false"
    @cliente-creado="onClienteCreado"
  />

  <!-- Modal Seleccionar Series -->
  <div v-if="showSeriesPicker" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" @click.self="closeSeriesPicker">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-2xl">
      <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
        <h3 class="text-lg font-semibold text-gray-900">Seleccionar series: {{ pickerProducto?.nombre || '' }}</h3>
        <button @click="closeSeriesPicker" class="text-gray-400 hover:text-gray-600 transition-colors">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
      </div>
      <div class="p-6">
        <div class="mb-3 text-sm text-gray-700">
          Selecciona exactamente {{ pickerRequired }} {{ pickerRequired === 1 ? 'serie' : 'series' }}. Seleccionadas: {{ selectedSeries.length }}.
        </div>
        <div class="mb-3 grid grid-cols-1 md:grid-cols-2 gap-3">
          <input v-model.trim="pickerSearch" type="text" placeholder="Buscar número de serie" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" />
          <div class="text-xs text-gray-500 self-center">
            <span class="inline-block px-2 py-1 bg-emerald-100 text-emerald-700 rounded">En stock: {{ pickerSeries.length }}</span>
          </div>
        </div>
        <div class="max-h-72 overflow-y-auto border border-gray-200 rounded-lg">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600">Sel</th>
                <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600">Número de serie</th>
                <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600">Almacén</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="s in filteredPickerSeries" :key="s.id">
                <td class="px-4 py-2 text-sm">
                  <input type="checkbox" :checked="selectedSeries.includes(s.numero_serie)" @change="toggleSerie(s.numero_serie)" :disabled="!selectedSeries.includes(s.numero_serie) && selectedSeries.length >= pickerRequired" />
                </td>
                <td class="px-4 py-2 text-sm font-medium text-gray-900">{{ s.numero_serie }}</td>
                <td class="px-4 py-2 text-sm text-gray-700">{{ nombreAlmacen(s.almacen_id) }}</td>
              </tr>
              <tr v-if="filteredPickerSeries.length === 0">
                <td colspan="3" class="px-4 py-6 text-center text-sm text-gray-500">Sin series disponibles</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 text-right">
        <button @click="closeSeriesPicker" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors mr-2">Cancelar</button>
        <button @click="confirmSeries" :disabled="selectedSeries.length !== pickerRequired" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors disabled:opacity-50">Usar {{ selectedSeries.length }}/{{ pickerRequired }} series</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { Head, useForm, router, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { Notyf } from 'notyf';
import 'notyf/notyf.min.css';
import AppLayout from '@/Layouts/AppLayout.vue';
import Header from '@/Components/CreateComponents/Header.vue';
import BuscarCliente from '@/Components/CreateComponents/BuscarCliente.vue';
import BuscarProducto from '@/Components/CreateComponents/BuscarProducto.vue';
import PySSeleccionados from '@/Components/CreateComponents/PySSeleccionados.vue';
import Totales from '@/Components/CreateComponents/Totales.vue';
import BotonesAccion from '@/Components/CreateComponents/BotonesAccion.vue';
import VistaPreviaModal from '@/Components/Modals/VistaPreviaModal.vue';
import CrearClienteModal from '@/Components/Modals/CrearClienteModal.vue';

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
  clientes: Array,
  productos: { type: Array, default: () => [] },
  servicios: { type: Array, default: () => [] },
  catalogs: { type: Object, default: () => ({}) },
  almacenes: { type: Array, default: () => [] },
  user: { type: Object, default: () => ({}) },
});

// Copia reactiva de clientes para evitar mutación de props
const clientesList = ref([...props.clientes]);

// Catalogs para el modal
const catalogs = computed(() => props.catalogs);

// Almacén predeterminado del usuario
const userAlmacenPredeterminado = computed(() => props.user?.almacen_venta?.id || null);

// Número de venta (se obtiene del backend)
const numeroVentaFijo = ref('V0001');

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
  numero_venta: numeroVentaFijo,
  fecha: getCurrentDate(),
  cliente_id: '',
  almacen_id: userAlmacenPredeterminado.value || '',
  metodo_pago: 'efectivo',
  subtotal: 0,
  descuento_items: 0,
  iva: 0,
  total: 0,
  productos: [],
  notas: '',
  estado: 'borrador',
});

// Referencias
const buscarClienteRef = ref(null);
const buscarProductoRef = ref(null);

// Estado
const selectedProducts = ref([]);
const quantities = ref({});
const prices = ref({});
const discounts = ref({});
const serialsMap = ref({});
const clienteSeleccionado = ref(null);
const mostrarVistaPrevia = ref(false);
const mostrarAtajos = ref(true);
const requiereConfirmacionMargen = ref(false);
const mensajeAdvertenciaMargen = ref('');
const mostrarModalCliente = ref(false);
const nombreClienteBuscado = ref('');

// Función para manejar localStorage de forma segura
const saveToLocalStorage = (key, data) => {
  try {
    localStorage.setItem(key, JSON.stringify(data));
  } catch (error) {
    console.warn('No se pudo guardar en localStorage:', error);
  }
};

const loadFromLocalStorage = (key) => {
  try {
    const item = localStorage.getItem(key);
    return item ? JSON.parse(item) : null;
  } catch (error) {
    console.warn('No se pudo cargar desde localStorage:', error);
    return null;
  }
};

const removeFromLocalStorage = (key) => {
  try {
    localStorage.removeItem(key);
  } catch (error) {
    console.warn('No se pudo eliminar de localStorage:', error);
  }
};

// --- FUNCIONES ---

// Obtener el siguiente número de venta del backend
const fetchNextNumeroVenta = async () => {
  try {
    const response = await axios.get('/api/ventas/next-numero-venta');
    if (response.data && response.data.numero_venta) {
      numeroVentaFijo.value = response.data.numero_venta;
      form.numero_venta = response.data.numero_venta;
      showNotification(`Número de venta generado: ${response.data.numero_venta}`, 'info');
    }
  } catch (error) {
    console.error('Error al obtener el número de venta:', error);
    showNotification('Error al generar el número de venta', 'error');
  }
};

// Header
const handlePreview = () => {
  if (clienteSeleccionado.value && selectedProducts.value.length > 0) {
    mostrarVistaPrevia.value = true;
  } else {
    showNotification('Selecciona un cliente y al menos un producto', 'error');
  }
};

// Actualizar series desde el componente hijo
const updateSerials = (key, serials) => {
  serialsMap.value[key] = serials;
};

// Selector de series
const showSeriesPicker = ref(false);
const pickerKey = ref('');
const pickerProducto = ref(null);
const pickerSeries = ref([]); // en_stock
const pickerSearch = ref('');
const selectedSeries = ref([]);
const pickerRequired = computed(() => {
  if (!pickerKey.value) return 0;
  const q = quantities.value[pickerKey.value];
  return Number.parseFloat(q) || 0;
});

const nombreAlmacen = (id) => {
  if (!id) return '—';
  const a = props.almacenes?.find(x => String(x.id) === String(id));
  return a ? a.nombre : `ID ${id}`;
};

const filteredPickerSeries = computed(() => {
  const q = (pickerSearch.value || '').toLowerCase();
  const list = pickerSeries.value || [];
  return q ? list.filter(s => (s.numero_serie || '').toLowerCase().includes(q)) : list;
});

const openSerials = async (entry) => {
  try {
    pickerKey.value = `${entry.tipo}-${entry.id}`;
    pickerProducto.value = props.productos.find(p => p.id === entry.id) || { id: entry.id, nombre: entry.nombre || 'Producto' };
    // cargar series del backend
    let url = '';
    try { url = route('productos.series', entry.id) } catch (e) { const base = typeof window !== 'undefined' ? window.location.origin : ''; url = `${base}/productos/${entry.id}/series`; }
    const res = await fetch(url, { method: 'GET', headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' });
    if (!res.ok) { notyf.error('No se pudieron cargar las series'); return; }
    const data = await res.json();
    pickerSeries.value = data?.series?.en_stock || [];
    const prev = serialsMap.value[pickerKey.value] || [];
    selectedSeries.value = Array.isArray(prev) ? prev.slice(0, pickerRequired.value) : [];
    showSeriesPicker.value = true;
  } catch (e) {
    console.error('Error al abrir selector de series:', e);
    notyf.error('Error al abrir selector de series');
  }
};

const closeSeriesPicker = () => {
  showSeriesPicker.value = false;
  pickerKey.value = '';
  pickerProducto.value = null;
  pickerSeries.value = [];
  pickerSearch.value = '';
  selectedSeries.value = [];
};

const toggleSerie = (numero) => {
  const idx = selectedSeries.value.indexOf(numero);
  if (idx >= 0) {
    selectedSeries.value.splice(idx, 1);
  } else if (selectedSeries.value.length < pickerRequired.value) {
    selectedSeries.value.push(numero);
  }
};

const confirmSeries = () => {
  if (!pickerKey.value) return;
  if (selectedSeries.value.length !== pickerRequired.value) {
    notyf.error(`Debes seleccionar ${pickerRequired.value} series`);
    return;
  }
  serialsMap.value[pickerKey.value] = selectedSeries.value.slice();
  closeSeriesPicker();
  notyf.success('Series seleccionadas');
};

const closeShortcuts = () => {
  mostrarAtajos.value = false;
};

// Cliente
const onClienteSeleccionado = (cliente) => {
  if (!cliente) {
    clienteSeleccionado.value = null;
    form.cliente_id = '';
    saveState();
    showNotification('Selección de cliente limpiada', 'info');
    return;
  }
  if (clienteSeleccionado.value?.id === cliente.id) return;
  clienteSeleccionado.value = cliente;
  form.cliente_id = cliente.id;
  saveState();
  showNotification(`Cliente seleccionado: ${cliente.nombre_razon_social}`);
};

const crearNuevoCliente = (nombreBuscado) => {
  nombreClienteBuscado.value = nombreBuscado;
  mostrarModalCliente.value = true;
};

const onClienteCreado = (nuevoCliente) => {
  // Actualizar la copia reactiva en lugar de mutar props
  if (!clientesList.value.some(c => c.id === nuevoCliente.id)) {
    clientesList.value.push(nuevoCliente);
  }

  onClienteSeleccionado(nuevoCliente);
};

// Productos
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

    // Validar precios con fallbacks seguros
    let precio = 0;
    if (item.tipo === 'producto') {
      precio = typeof item.precio_venta === 'number' ? item.precio_venta : 0;
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
    (item) => !(item.id === entry.id && item.tipo === item.tipo)
  );
  delete quantities.value[key];
  delete prices.value[key];
  delete discounts.value[key];
  calcularTotal();
  saveState();
  showNotification(`Producto eliminado: ${entry.nombre || entry.descripcion || 'Item'}`, 'info');
};

// En el script del componente padre:
// En el script
const limpiarFormulario = () => {
  // Limpiar cliente
  clienteSeleccionado.value = null;
  form.cliente_id = '';

  // Limpiar productos
  selectedProducts.value = [];

  // Reiniciar cantidades, precios y descuentos
  quantities.value = {};
  prices.value = {};
  discounts.value = {};

  // Limpiar almacén
  form.almacen_id = '';

  // Limpiar notas
  form.notas = '';

  // Restablecer número y fecha fijos
  form.numero_venta = numeroVentaFijo;
  form.fecha = getCurrentDate();

  // Limpiar variables de margen
  requiereConfirmacionMargen.value = false;
  mensajeAdvertenciaMargen.value = '';

  // Limpiar localStorage si es necesario
  localStorage.removeItem(`venta_edit_${props.venta?.id}`);

  // Notificación
  notyf.success('Formulario limpiado correctamente');

  // Si necesitas forzar actualización de algún componente
  // keyComponent.value += 1;
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

// Cálculos
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

  const subtotalConDescuentos = Math.max(0, subtotal - descuentoItems);
  const iva = subtotalConDescuentos * 0.16;
  const total = subtotalConDescuentos + iva;

  return {
    subtotal: parseFloat(subtotal.toFixed(2)),
    descuentoItems: parseFloat(descuentoItems.toFixed(2)),
    subtotalConDescuentos: parseFloat(subtotalConDescuentos.toFixed(2)),
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

// Validar datos antes de crear venta
const validarDatos = () => {
  if (!form.cliente_id) {
    return false;
  }

  if (!form.almacen_id) {
    showNotification('Selecciona un almacén', 'error');
    return false;
  }

  if (selectedProducts.value.length === 0) {
    showNotification('Agrega al menos un producto o servicio', 'error');
    return false;
  }

  if (!form.metodo_pago) {
    showNotification('Selecciona un método de pago', 'error');
    return false;
  }

  // Validar descuentos
  for (const entry of selectedProducts.value) {
    const key = `${entry.tipo}-${entry.id}`;
    const discount = parseFloat(discounts.value[key]) || 0;
    const quantity = parseFloat(quantities.value[key]) || 0;
    const price = parseFloat(prices.value[key]) || 0;
    const producto = props.productos.find(p => p.id === entry.id);

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

    if (entry.tipo === 'producto' && producto && producto.requiere_serie) {
      const serials = serialsMap.value[key] || [];
      if (!Array.isArray(serials) || serials.length !== quantity) {
        showNotification(`El producto "${producto.nombre}" requiere ${quantity} series.`, 'error');
        return false;
      }
      const unique = new Set(serials.map(s => (s || '').trim()).filter(Boolean));
      if (unique.size !== serials.length) {
        showNotification(`Las series del producto "${producto.nombre}" deben ser únicas.`, 'error');
        return false;
      }
    }
  }

  return true;
};

// Funciones de margen
const aceptarAjusteMargen = () => {
  // Agregar el parámetro para ajustar márgenes automáticamente
  form.ajustar_margen = true;
  requiereConfirmacionMargen.value = false;
  mensajeAdvertenciaMargen.value = '';
  crearVenta();
};

const cancelarAjusteMargen = () => {
  requiereConfirmacionMargen.value = false;
  mensajeAdvertenciaMargen.value = '';
  showNotification('Revisa los precios de los productos antes de continuar', 'info');
};

// Crear venta
const crearVenta = () => {
  if (!validarDatos()) {
    return;
  }

  // Asignar productos al formulario
  form.productos = selectedProducts.value.map((entry) => {
    const key = `${entry.tipo}-${entry.id}`;
    const item = {
      id: entry.id,
      tipo: entry.tipo,
      cantidad: parseFloat(quantities.value[key]) || 1,
      precio: parseFloat(prices.value[key]) || 0,
      descuento: parseFloat(discounts.value[key]) || 0,
    };
    const seriales = serialsMap.value[key];
    if (seriales && Array.isArray(seriales) && seriales.length > 0) {
      item.seriales = seriales;
    }
    return item;
  });

  // Calcular totales
  calcularTotal();

  // Enviar formulario
  form.post(route('ventas.store'), {
    onSuccess: () => {
      removeFromLocalStorage('ventaEnProgreso');
      selectedProducts.value = [];
      quantities.value = {};
      prices.value = {};
      discounts.value = {};
      serialsMap.value = {};
      clienteSeleccionado.value = null;
      form.reset();
      requiereConfirmacionMargen.value = false;
      mensajeAdvertenciaMargen.value = '';
      showNotification('Venta creada con éxito');
    },
    onError: (errors) => {
      // Verificar si es un error de margen
      if (errors.warning && errors.requiere_confirmacion_margen) {
        requiereConfirmacionMargen.value = true;
        mensajeAdvertenciaMargen.value = errors.warning;
        showNotification('Se requiere confirmación de márgenes', 'warning');
        return;
      }

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

// Manejo de eventos del navegador
const handleBeforeUnload = (event) => {
  if (form.cliente_id || selectedProducts.value.length > 0) {
    event.preventDefault();
    event.returnValue = 'Tienes cambios sin guardar. ¿Estás seguro de que quieres salir?';
  }
};

// Guardar estado automáticamente
const saveState = () => {
  const stateToSave = {
    numero_venta: numeroVentaFijo,
    fecha: form.fecha,
    cliente_id: form.cliente_id,
    cliente: clienteSeleccionado.value,
    selectedProducts: selectedProducts.value,
    quantities: quantities.value,
    prices: prices.value,
    discounts: discounts.value,
    serials: serialsMap.value,
  };
  saveToLocalStorage('ventaEnProgreso', stateToSave);
};

// Función para asegurar que la fecha sea siempre la actual
const asegurarFechaActual = () => {
  const fechaActual = getCurrentDate();
  if (form.fecha !== fechaActual) {
    form.fecha = fechaActual;
  }
};

// Lifecycle hooks
onMounted(async () => {
  // Mostrar mensajes flash del servidor (errores de backend)
  try {
    const page = usePage();
    const flash = page?.props?.flash;
    if (flash?.success) showNotification(flash.success, 'success');
    if (flash?.error) showNotification(flash.error, 'error');
    if (flash?.warning) showNotification(flash.warning, 'warning');
  } catch (e) { /* noop */ }

  // Obtener el siguiente número de venta
  await fetchNextNumeroVenta();

  const savedData = loadFromLocalStorage('ventaEnProgreso');
  if (savedData && typeof savedData === 'object') {
    try {
      // Siempre usar número y fecha fijos
      form.numero_venta = numeroVentaFijo;
      form.fecha = getCurrentDate(); // Siempre usar fecha actual

      form.cliente_id = savedData.cliente_id || '';
      clienteSeleccionado.value = savedData.cliente || null;
      selectedProducts.value = Array.isArray(savedData.selectedProducts) ? savedData.selectedProducts : [];
      quantities.value = savedData.quantities || {};
      prices.value = savedData.prices || {};
      discounts.value = savedData.discounts || {};
      serialsMap.value = savedData.serials || {};
      calcularTotal();
    } catch (error) {
      console.warn('Error al cargar datos guardados:', error);
      removeFromLocalStorage('ventaEnProgreso');
    }
  }

  // Verificar la fecha cada 5 minutos para mantenerla actual
  const fechaInterval = setInterval(() => {
    asegurarFechaActual();
  }, 5 * 60 * 1000); // 5 minutos

  window.addEventListener('beforeunload', handleBeforeUnload);
});

onBeforeUnmount(() => {
  window.removeEventListener('beforeunload', handleBeforeUnload);

  // Limpiar el intervalo de fecha si existe
  if (typeof fechaInterval !== 'undefined') {
    clearInterval(fechaInterval);
  }
});
</script>
