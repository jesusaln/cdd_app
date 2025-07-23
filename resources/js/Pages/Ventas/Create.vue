<template>
  <Head title="Crear Venta" />
  <div class="ventas-create min-h-screen bg-gradient-to-br from-slate-50 to-blue-50 p-6">
    <div class="max-w-6xl mx-auto">
      <!-- Header -->
      <div class="mb-8">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Nueva Venta</h1>
            <p class="text-gray-600">Crea una nueva venta de productos y servicios</p>
          </div>
          <Link
            :href="route('ventas.index')"
            class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 hover:border-gray-400 transition-all duration-200 shadow-sm"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Volver
          </Link>
        </div>
      </div>

      <form @submit.prevent="crearVenta" class="space-y-8">
        <!-- Información del Cliente -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
          <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-4">
            <h2 class="text-lg font-semibold text-white flex items-center">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
              </svg>
              Información del Cliente
            </h2>
          </div>
          <div class="relative p-6">
            <BuscarCliente
              :clientes="clientes"
              :cliente-seleccionado="clienteSeleccionado"
              @cliente-seleccionado="seleccionarCliente"
              @crear-nuevo-cliente="crearNuevoCliente"
            />
            <div v-if="clienteSeleccionado" class="mt-6 p-6 bg-gradient-to-r from-blue-50 to-indigo-50 border-2 border-blue-200 rounded-lg shadow-sm">
              <div class="flex items-start justify-between mb-4">
                <h3 class="text-lg font-semibold text-blue-900 flex items-center">
                  <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                  Cliente Seleccionado
                </h3>
                <button
                  type="button"
                  @click="limpiarCliente"
                  class="text-red-500 hover:text-red-700 hover:bg-red-100 p-1 rounded-full transition-colors duration-200"
                  title="Cambiar cliente"
                >
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                  </svg>
                </button>
              </div>
              <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="space-y-2">
                  <div class="flex items-center text-sm font-medium text-blue-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    Nombre
                  </div>
                  <div class="text-lg font-semibold text-gray-900">{{ clienteSeleccionado.nombre_razon_social }}</div>
                </div>
                <div class="space-y-2" v-if="clienteSeleccionado.email">
                  <div class="flex items-center text-sm font-medium text-blue-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    Email
                  </div>
                  <div class="text-gray-900">{{ clienteSeleccionado.email }}</div>
                </div>
                <div class="space-y-2" v-if="clienteSeleccionado.telefono">
                  <div class="flex items-center text-sm font-medium text-blue-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                    </svg>
                    Teléfono
                  </div>
                  <div class="text-gray-900">{{ clienteSeleccionado.telefono }}</div>
                </div>
              </div>
            </div>
            <div v-else class="mt-6 p-6 border-2 border-dashed border-gray-300 rounded-lg text-center">
              <svg class="w-12 h-12 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
              </svg>
              <p class="text-gray-500 text-lg font-medium">Selecciona un cliente</p>
              <p class="text-gray-400 text-sm mt-1">Busca y selecciona un cliente para continuar con la venta</p>
            </div>
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
              :productos="productos"
              :servicios="servicios"
              :vista-productos="vistaProductos"
              @agregar-producto="agregarProducto"
              @update-vista="vistaProductos = $event"
            />
            <ProductosSeleccionados
              :selected-products="selectedProducts"
              :productos="productos"
              :servicios="servicios"
              :quantities="quantities"
              :prices="prices"
              :discounts="discounts"
              @eliminar-producto="eliminarProducto"
              @update-quantity="updateQuantity"
              @update-price="updatePrice"
              @update-discount="updateDiscount"
              @calcular-total="calcularTotal"
            />
          </div>
        </div>

        <!-- Descuento General -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
          <div class="bg-gradient-to-r from-orange-500 to-orange-600 px-6 py-4">
            <h2 class="text-lg font-semibold text-white flex items-center">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
              </svg>
              Descuento General
            </h2>
          </div>
          <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Descuento General (%)
                </label>
                <div class="relative">
                  <input
                    type="number"
                    v-model="descuentoGeneral"
                    @input="calcularTotal"
                    min="0"
                    max="100"
                    step="0.01"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="0.00"
                  />
                  <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                    <span class="text-gray-500 text-sm">%</span>
                  </div>
                </div>
              </div>
              <div class="flex items-end">
                <div class="text-right">
                  <div class="text-sm text-gray-600">Descuento aplicado:</div>
                  <div class="text-2xl font-bold text-orange-600">
                    ${{ totales.descuentoGeneral.toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Resumen Total -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
          <div class="bg-gradient-to-r from-purple-500 to-purple-600 px-6 py-4">
            <h2 class="text-lg font-semibold text-white flex items-center">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
              </svg>
              Resumen de la Venta
            </h2>
          </div>
          <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
              <div class="text-center p-6 bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl border border-blue-200">
                <div class="text-3xl font-bold text-blue-600 mb-2">{{ selectedProducts.length }}</div>
                <div class="text-sm text-blue-600 font-medium">Items Seleccionados</div>
              </div>
              <div class="text-center p-6 bg-gradient-to-br from-green-50 to-green-100 rounded-xl border border-green-200">
                <div class="text-3xl font-bold text-green-600 mb-2">
                  {{ Object.values(quantities).reduce((sum, qty) => sum + (qty || 0), 0) }}
                </div>
                <div class="text-sm text-green-600 font-medium">Cantidad Total</div>
              </div>
              <div class="text-center p-6 bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl border border-purple-200">
                <div class="text-3xl font-bold text-purple-600 mb-2">
                  ${{ totales.total.toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}
                </div>
                <div class="text-sm text-purple-600 font-medium">Total Final</div>
              </div>
            </div>
            <div class="bg-gray-50 rounded-lg p-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-4">Desglose de Precios</h3>
              <div class="space-y-3">
                <div class="flex justify-between items-center text-gray-700">
                  <span>Subtotal:</span>
                  <span class="font-semibold">${{ totales.subtotal.toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}</span>
                </div>
                <div class="flex justify-between items-center text-orange-600" v-if="totales.descuentoItems > 0">
                  <span>Descuentos por item:</span>
                  <span class="font-semibold">-${{ totales.descuentoItems.toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}</span>
                </div>
                <div class="flex justify-between items-center text-orange-600" v-if="totales.descuentoGeneral > 0">
                  <span>Descuento General ({{ descuentoGeneral }}%):</span>
                  <span class="font-semibold">-${{ totales.descuentoGeneral.toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}</span>
                </div>
                <div class="flex justify-between items-center text-gray-700">
                  <span>Subtotal con descuentos:</span>
                  <span class="font-semibold">${{ totales.subtotalConDescuentos.toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}</span>
                </div>
                <div class="flex justify-between items-center text-blue-600">
                  <span>IVA ({{ ivaPercentage }}%):</span>
                  <span class="font-semibold">${{ totales.iva.toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}</span>
                </div>
                <div class="border-t border-gray-300 pt-3">
                  <div class="flex justify-between items-center text-lg font-bold text-gray-900">
                    <span>Total Final:</span>
                    <span>${{ totales.total.toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}</span>
                  </div>
                </div>
              </div>
            </div>
            <div class="mt-6">
              <label class="block text-sm font-medium text-gray-700 mb-2">Método de Pago</label>
              <select
                v-model="metodoPago"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                @change="calcularTotal"
              >
                <option value="">Seleccionar método</option>
                <option value="efectivo">Efectivo</option>
                <option value="tarjeta">Tarjeta</option>
                <option value="transferencia">Transferencia</option>
                <option value="credito">Crédito</option>
              </select>
            </div>
            <div class="mt-6">
              <label class="block text-sm font-medium text-gray-700 mb-2">Notas (opcional)</label>
              <textarea
                v-model="notas"
                rows="3"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none"
                placeholder="Notas adicionales sobre la venta..."
              ></textarea>
            </div>
          </div>
        </div>

        <!-- Botones de Acción -->
        <div class="flex flex-col sm:flex-row gap-4 justify-end">
          <button
            type="button"
            @click="guardarBorrador"
            :disabled="form.processing"
            class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
            </svg>
            Guardar Borrador
          </button>
          <Link
            :href="route('ventas.index')"
            class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            Cancelar
          </Link>
          <button
            type="submit"
            :disabled="!puedeGuardar || form.processing"
            class="inline-flex items-center justify-center px-6 py-3 border border-transparent rounded-lg text-sm font-medium text-white bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 shadow-lg hover:shadow-xl"
          >
            <svg v-if="form.processing" class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <svg v-else class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            {{ form.processing ? 'Guardando...' : 'Crear Venta' }}
          </button>
        </div>
      </form>

      <!-- Modal de Confirmación -->
      <div v-if="mostrarModalConfirmacion" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl p-6 max-w-md w-full mx-4 transform transition-all animate-slideIn">
          <div class="flex items-center mb-4">
            <div class="bg-yellow-100 p-2 rounded-full mr-3">
              <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
              </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900">Confirmar Venta</h3>
          </div>
          <p class="text-gray-600 mb-6">
            ¿Estás seguro de que deseas crear esta venta por un total de <strong>${{ totales.total.toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}</strong>?
          </p>
          <div class="flex space-x-3">
            <button
              type="button"
              @click="mostrarModalConfirmacion = false"
              class="flex-1 py-2 px-4 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
            >
              Cancelar
            </button>
            <button
              type="button"
              @click="confirmarVenta"
              class="flex-1 py-2 px-4 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg hover:from-blue-600 hover:to-blue-700 transition-colors"
            >
              Confirmar
            </button>
          </div>
        </div>
      </div>

      <!-- Modal de Éxito -->
      <div v-if="mostrarModalExito" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl p-6 max-w-md w-full mx-4 transform transition-all animate-slideIn">
          <div class="text-center">
            <div class="bg-green-100 p-3 rounded-full w-fit mx-auto mb-4">
              <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
              </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">¡Venta Creada!</h3>
            <p class="text-gray-600 mb-6">La venta se ha registrado correctamente.</p>
            <div class="flex space-x-3">
              <button
                type="button"
                @click="crearNuevaVenta"
                class="flex-1 py-2 px-4 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
              >
                Nueva Venta
              </button>
              <Link
                :href="route('ventas.index')"
                class="flex-1 py-2 px-4 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg hover:from-blue-600 hover:to-blue-700 transition-colors text-center"
              >
                Ver Ventas
              </Link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import BuscarCliente from '@/Components/BuscarCliente.vue';
import BuscarProducto from '@/Components/BuscarProducto.vue';
import ProductosSeleccionados from '@/Components/ProductosSeleccionados.vue';

defineOptions({ layout: AppLayout });

const props = defineProps({
  clientes: { type: Array, default: () => [] },
  productos: { type: Array, default: () => [] },
  servicios: { type: Array, default: () => [] },
});

const form = useForm({
  cliente_id: '',
  subtotal: 0,
  descuento_general: 0,
  descuento_items: 0,
  iva: 0,
  total: 0,
  metodo_pago: '',
  notas: '',
  productos: [],
});

const buscarCliente = ref('');
const buscarProducto = ref('');
const mostrarClientes = ref(false);
const mostrarProductos = ref(false);
const mostrarModalConfirmacion = ref(false);
const mostrarModalExito = ref(false);
const vistaProductos = ref('todos');
const metodoPago = ref('');
const notas = ref('');
const clienteSeleccionado = ref(null);
const selectedProducts = ref([]);
const quantities = ref({});
const prices = ref({});
const discounts = ref({});
const descuentoGeneral = ref(0);
const ivaPercentage = ref(16);

const clientesFiltrados = computed(() => {
  if (!buscarCliente.value || buscarCliente.value.length < 2) return [];
  return props.clientes.filter((cliente) =>
    cliente.nombre_razon_social.toLowerCase().includes(buscarCliente.value.toLowerCase()) ||
    (cliente.email && cliente.email.toLowerCase().includes(buscarCliente.value.toLowerCase()))
  ).slice(0, 10);
});

const productosFiltrados = computed(() => {
  if (!buscarProducto.value || buscarProducto.value.length < 2) return [];

  let items = [];
  if (vistaProductos.value === 'productos' || vistaProductos.value === 'todos') {
    items.push(...(props.productos || []).map(producto => ({ ...producto, tipo: 'producto' })));
  }
  if (vistaProductos.value === 'servicios' || vistaProductos.value === 'todos') {
    items.push(...(props.servicios || []).map(servicio => ({ ...servicio, tipo: 'servicio' })));
  }

  return items.filter(item =>
    (item.nombre.toLowerCase().includes(buscarProducto.value.toLowerCase()) ||
     (item.codigo && item.codigo.toLowerCase().includes(buscarProducto.value.toLowerCase()))) &&
    !selectedProducts.value.some(selected => selected.id === item.id && selected.tipo === item.tipo) &&
    (item.tipo === 'servicio' || item.stock > 0)
  ).slice(0, 20);
});

const totales = computed(() => {
  let subtotal = 0;
  let descuentoItems = 0;

  for (const entry of selectedProducts.value) {
    const key = `${entry.tipo}-${entry.id}`;
    const cantidad = Number.parseFloat(quantities.value[key]) || 0;
    const precio = Number.parseFloat(prices.value[key]) || 0;
    const descuentoItem = Number.parseFloat(discounts.value[key]) || 0;

    const subtotalItem = cantidad * precio;
    const descuentoItemMonto = subtotalItem * (descuentoItem / 100);

    subtotal += subtotalItem;
    descuentoItems += descuentoItemMonto;
  }

  const subtotalConDescuentoItems = subtotal - descuentoItems;
  const descuentoGeneralMonto = subtotalConDescuentoItems * (descuentoGeneral.value / 100);
  const subtotalConDescuentos = subtotalConDescuentoItems - descuentoGeneralMonto;
  const iva = subtotalConDescuentos * (ivaPercentage.value / 100);
  const total = subtotalConDescuentos + iva;

  return {
    subtotal,
    descuentoItems,
    descuentoGeneral: descuentoGeneralMonto,
    subtotalConDescuentos,
    iva,
    total
  };
});

const puedeGuardar = computed(() => {
  return form.cliente_id &&
         selectedProducts.value.length > 0 &&
         !form.processing &&
         metodoPago.value;
});

const getProductById = (entry) => {
  if (!entry || !entry.id || !entry.tipo) return null;
  if (entry.tipo === 'producto') {
    return props.productos.find((p) => p.id === entry.id) || null;
  }
  if (entry.tipo === 'servicio') {
    return props.servicios.find((s) => s.id === entry.id) || null;
  }
  return null;
};

const calcularTotal = () => {
  form.subtotal = totales.value.subtotal;
  form.descuento_general = totales.value.descuentoGeneral;
  form.descuento_items = totales.value.descuentoItems;
  form.iva = totales.value.iva;
  form.total = totales.value.total;
  form.metodo_pago = metodoPago.value;
  form.notas = notas.value;
};

const seleccionarCliente = (cliente) => {
  form.cliente_id = cliente.id;
  buscarCliente.value = cliente.nombre_razon_social;
  clienteSeleccionado.value = cliente;
  mostrarClientes.value = false;
};

const limpiarCliente = () => {
  form.cliente_id = '';
  buscarCliente.value = '';
  clienteSeleccionado.value = null;
  form.clearErrors();
};

const crearNuevoCliente = (nombreBuscado) => {
  console.log('Crear nuevo cliente con nombre:', nombreBuscado);
  form.clearErrors();
};

const agregarProducto = (item) => {
  if (item.tipo === 'producto' && item.stock <= 0) {
    alert(`El producto ${item.nombre} no tiene stock disponible.`);
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
    prices.value[key] = item.tipo === 'producto' ? (item.precio_venta || 0) : (item.precio || 0);
    discounts.value[key] = 0;
    calcularTotal();
  }

  buscarProducto.value = '';
  mostrarProductos.value = false;
};

const eliminarProducto = (entry) => {
  selectedProducts.value = selectedProducts.value.filter(
    (item) => !(item.id === entry.id && item.tipo === entry.tipo)
  );
  const key = `${entry.tipo}-${entry.id}`;
  delete quantities.value[key];
  delete prices.value[key];
  delete discounts.value[key];
  calcularTotal();
};

const updateQuantity = (key, quantity) => {
  quantities.value[key] = Number.parseFloat(quantity) || 1;
  calcularTotal();
};

const updatePrice = (key, price) => {
  prices.value[key] = Number.parseFloat(price) || 0;
  calcularTotal();
};

const updateDiscount = (key, discount) => {
  discounts.value[key] = Number.parseFloat(discount) || 0;
  calcularTotal();
};

const guardarBorrador = () => {
  const dataToSave = {
    cliente_id: form.cliente_id,
    cliente_nombre: clienteSeleccionado.value?.nombre_razon_social || '',
    selectedProducts: selectedProducts.value,
    quantities: quantities.value,
    prices: prices.value,
    discounts: discounts.value,
    descuentoGeneral: descuentoGeneral.value,
    metodoPago: metodoPago.value,
    notas: notas.value,
    timestamp: Date.now()
  };

  window.ventaBorrador = dataToSave;
  alert('Borrador guardado correctamente');
};

const cargarBorrador = () => {
  const savedData = window.ventaBorrador;
  if (savedData) {
    form.cliente_id = savedData.cliente_id || '';
    buscarCliente.value = savedData.cliente_nombre || '';
    if (savedData.cliente_id) {
      clienteSeleccionado.value = props.clientes.find(c => c.id === savedData.cliente_id);
    }
    selectedProducts.value = Array.isArray(savedData.selectedProducts)
      ? savedData.selectedProducts.filter(entry => entry && typeof entry === 'object' && 'id' in entry && 'tipo' in entry)
      : [];
    quantities.value = savedData.quantities || {};
    prices.value = savedData.prices || {};
    discounts.value = savedData.discounts || {};
    descuentoGeneral.value = savedData.descuentoGeneral || 0;
    metodoPago.value = savedData.metodoPago || '';
    notas.value = savedData.notas || '';
    calcularTotal();
  }
};

const crearVenta = () => {
  if (!puedeGuardar.value) {
    alert('Por favor, completa todos los campos requeridos.');
    return;
  }
  mostrarModalConfirmacion.value = true;
};

const confirmarVenta = () => {
  mostrarModalConfirmacion.value = false;

  if (selectedProducts.value.length === 0) {
    alert('Debes agregar al menos un producto o servicio.');
    return;
  }

  try {
    form.productos = selectedProducts.value.map((entry) => {
      const key = `${entry.tipo}-${entry.id}`;
      const cantidad = quantities.value[key] || 1;
      const precio = prices.value[key] || 0;
      const descuento = discounts.value[key] || 0;
      const stockDisponible = getProductById(entry)?.stock;

      if (entry.tipo === 'producto' && cantidad > stockDisponible) {
        throw new Error(`La cantidad de ${getProductById(entry).nombre} excede el stock disponible (${stockDisponible}).`);
      }

      return {
        id: entry.id,
        tipo: entry.tipo,
        cantidad,
        precio,
        descuento,
        subtotal: cantidad * precio,
        descuento_monto: (cantidad * precio) * (descuento / 100)
      };
    });

    form.post(route('ventas.store'), {
      preserveScroll: true,
      onSuccess: () => {
        window.ventaBorrador = null;
        mostrarModalExito.value = true;
      },
      onError: (errors) => {
        console.error('Error al crear la venta:', errors);
        alert('Error al crear la venta. Por favor, revisa los datos e intenta nuevamente.');
      }
    });
  } catch (error) {
    console.error('Error en confirmarVenta:', error.message);
    alert(error.message);
  }
};

const crearNuevaVenta = () => {
  mostrarModalExito.value = false;
  selectedProducts.value = [];
  quantities.value = {};
  prices.value = {};
  discounts.value = {};
  descuentoGeneral.value = 0;
  clienteSeleccionado.value = null;
  buscarCliente.value = '';
  buscarProducto.value = '';
  metodoPago.value = '';
  notas.value = '';
  form.reset();
  window.ventaBorrador = null;
};

const handleKeyboardShortcuts = (event) => {
  if (event.ctrlKey || event.metaKey) {
    switch (event.key) {
      case '1':
        event.preventDefault();
        document.getElementById('buscarCliente')?.focus();
        break;
      case '2':
        event.preventDefault();
        document.getElementById('buscarProducto')?.focus();
        break;
      case 's':
        event.preventDefault();
        if (puedeGuardar.value) {
          crearVenta();
        }
        break;
    }
  }
};

const handleBeforeUnload = (event) => {
  if (form.cliente_id || selectedProducts.value.length > 0) {
    event.preventDefault();
    event.returnValue = 'Tienes una venta en progreso. ¿Estás seguro de que quieres salir?';
  }
};

watch([quantities, prices, discounts, descuentoGeneral], () => {
  calcularTotal();
}, { deep: true });

onMounted(() => {
  cargarBorrador();
  window.addEventListener('beforeunload', handleBeforeUnload);
  window.addEventListener('keydown', handleKeyboardShortcuts);
});

onUnmounted(() => {
  window.removeEventListener('beforeunload', handleBeforeUnload);
  window.removeEventListener('keydown', handleKeyboardShortcuts);
});
</script>

<style scoped>
@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.cliente-dropdown,
.producto-dropdown {
  animation: slideIn 0.2s ease-out;
}

::-webkit-scrollbar {
  width: 6px;
}

::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 3px;
}

::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
  background: #a8a8a8;
}

.animate-spin {
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
</style>
