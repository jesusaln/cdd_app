<template>
  <Head title="Crear cotizaciones" />
  <div class="cotizaciones-create min-h-screen bg-gradient-to-br from-slate-50 to-blue-50 p-6">
    <div class="max-w-6xl mx-auto">
      <!-- Header -->
      <div class="mb-8">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Nueva Cotización</h1>
            <p class="text-gray-600">Crea una nueva cotización para tus clientes</p>
          </div>
          <Link
            :href="route('cotizaciones.index')"
            class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 hover:border-gray-400 transition-all duration-200 shadow-sm"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Volver
          </Link>
        </div>
      </div>

      <form @submit.prevent="crearCotizacion" class="space-y-8">
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
          <div class="p-6">
            <!-- Componente de búsqueda de clientes -->
            <BuscarCliente
              :clientes="clientes"
              :cliente-seleccionado="clienteSeleccionado"
              @cliente-seleccionado="onClienteSeleccionado"
              @crear-nuevo-cliente="crearNuevoCliente"
            />

            <!-- Información del cliente seleccionado - Visible y destacada -->
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
                <!-- Nombre -->
                <div class="space-y-2">
                  <div class="flex items-center text-sm font-medium text-blue-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    Nombre
                  </div>
                  <div class="text-lg font-semibold text-gray-900">{{ clienteSeleccionado.nombre_razon_social }}</div>
                </div>

                <!-- Email -->
                <div class="space-y-2" v-if="clienteSeleccionado.email">
                  <div class="flex items-center text-sm font-medium text-blue-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    Email
                  </div>
                  <div class="text-gray-900">{{ clienteSeleccionado.email }}</div>
                </div>

                <!-- Teléfono -->
                <div class="space-y-2" v-if="clienteSeleccionado.telefono">
                  <div class="flex items-center text-sm font-medium text-blue-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                    </svg>
                    Teléfono
                  </div>
                  <div class="text-gray-900">{{ clienteSeleccionado.telefono }}</div>
                </div>



                <!-- Dirección -->
                <div class="space-y-2" v-if="clienteSeleccionado.calle">
                  <div class="flex items-center text-sm font-medium text-blue-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Dirección
                  </div>
                  <div class="text-gray-900">{{ clienteSeleccionado.calle }}</div>
                </div>

                <!-- Empresa -->
                <div class="space-y-2" v-if="clienteSeleccionado.numero_exterior">
                  <div class="flex items-center text-sm font-medium text-blue-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    Numero Exterior
                  </div>
                  <div class="text-gray-900">{{ clienteSeleccionado.numero_exterior }}</div>
                </div>

                <!-- RFC -->
                <div class="space-y-2" v-if="clienteSeleccionado.rfc">
                  <div class="flex items-center text-sm font-medium text-blue-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    RFC
                  </div>
                  <div class="text-gray-900 font-mono">{{ clienteSeleccionado.rfc }}</div>
                </div>
              </div>
            </div>

            <!-- Mensaje cuando no hay cliente seleccionado -->
            <div v-else class="mt-6 p-6 border-2 border-dashed border-gray-300 rounded-lg text-center">
              <svg class="w-12 h-12 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
              </svg>
              <p class="text-gray-500 text-lg font-medium">Selecciona un cliente</p>
              <p class="text-gray-400 text-sm mt-1">Busca y selecciona un cliente para continuar con la cotización</p>
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
              @agregar-producto="agregarProducto"
            />
            <ProductosSeleccionados
              :selectedProducts="selectedProducts"
              :productos="productos"
              :servicios="servicios"
              :quantities="quantities"
              :prices="prices"
              :discounts="discounts"
              @eliminar-producto="eliminarProducto"
              @update-quantity="updateQuantity"
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
                    ${{ calcularDescuentoGeneral().toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}
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
              Resumen de la Cotización
            </h2>
          </div>
          <div class="p-6">
            <!-- Estadísticas -->
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

            <!-- Desglose de totales -->
            <div class="bg-gray-50 rounded-lg p-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-4">Desglose de Precios</h3>
              <div class="space-y-3">
                <div class="flex justify-between items-center text-gray-700">
                  <span>Subtotal:</span>
                  <span class="font-semibold">${{ totales.subtotal.toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}</span>
                </div>
                <div class="flex justify-between items-center text-orange-600" v-if="totales.descuentoGeneral > 0">
                  <span>Descuento General ({{ descuentoGeneral }}%):</span>
                  <span class="font-semibold">-${{ totales.descuentoGeneral.toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}</span>
                </div>
                <div class="flex justify-between items-center text-orange-600" v-if="totales.descuentoItems > 0">
                  <span>Descuentos por item:</span>
                  <span class="font-semibold">-${{ totales.descuentoItems.toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}</span>
                </div>
                <div class="flex justify-between items-center text-gray-700">
                  <span>Subtotal con descuentos:</span>
                  <span class="font-semibold">${{ totales.subtotalConDescuentos.toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}</span>
                </div>
                <div class="flex justify-between items-center text-blue-600">
                  <span>IVA (16%):</span>
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
          </div>
        </div>

        <!-- Botones de Acción -->
        <div class="flex flex-col sm:flex-row gap-4 justify-end">
          <Link
            :href="route('cotizaciones.index')"
            class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            Cancelar
          </Link>
     <button
  type="submit"
  :disabled="!form.cliente_id || selectedProducts.length === 0 || form.processing"
  class="inline-flex items-center justify-center px-6 py-3 border border-transparent rounded-lg text-sm font-medium text-white bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 shadow-lg hover:shadow-xl"
>
  <svg v-if="form.processing" class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
  </svg>
  <svg v-else class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
  </svg>
  {{ form.processing ? 'Guardando...' : 'Crear Cotización' }}
</button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, computed, onMounted } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import axios from 'axios'
import AppLayout from '@/Layouts/AppLayout.vue';
import BuscarCliente from '@/Components/BuscarCliente.vue';
import BuscarProducto from '@/Components/BuscarProducto.vue';
import ProductosSeleccionados from '@/Components/ProductosSeleccionados.vue';

// Define el layout del dashboard
defineOptions({ layout: AppLayout });

const emailDisponible = ref(true)
const revisandoEmail = ref(false)

const props = defineProps({
  clientes: Array,
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
    nombre_razon_social: '',
    email: '',
  cliente_id: '',
  subtotal: 0,
  descuento_general: 0,
  descuento_items: 0,
  iva: 0,
  total: 0,
  productos: [],
});

const selectedProducts = ref([]);
const quantities = ref({});
const prices = ref({});
const discounts = ref({}); // Descuentos por item
const descuentoGeneral = ref(0); // Descuento general
const clienteSeleccionado = ref(null);

// Constante para el IVA
const IVA_RATE = 0.16;

const onClienteSeleccionado = (cliente) => {
  clienteSeleccionado.value = cliente;
  form.cliente_id = cliente.id;
};

const limpiarCliente = () => {
  clienteSeleccionado.value = null;
  form.cliente_id = '';
  form.clearError('cliente_id'); // Limpia el error específico
};

const crearNuevoCliente = (nombreBuscado) => {
  console.log('Crear nuevo cliente con nombre:', nombreBuscado);
  form.clearErrors(); // Limpia todos los errores del formulario
};

const agregarProducto = (item) => {
  const itemEntry = { id: item.id, tipo: item.tipo };
  const exists = selectedProducts.value.some(
    (entry) => entry.id === item.id && entry.tipo === item.tipo
  );

  if (!exists) {
    selectedProducts.value.push(itemEntry);
    const key = `${item.tipo}-${item.id}`;

    // Establecer cantidad inicial de 1
    quantities.value[key] = 1;

    // Establecer precio automáticamente (no editable)
    const precio = item.tipo === 'producto' ? (item.precio_venta || 0) : (item.precio || 0);
    prices.value[key] = precio;

    // Establecer descuento inicial de 0
    discounts.value[key] = 0;

    // Recalcular totales
    calcularTotal();
  }
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
  quantities.value[key] = quantity;
  calcularTotal();
};

const updateDiscount = (key, discount) => {
  discounts.value[key] = discount;
  calcularTotal();
};

const calcularDescuentoGeneral = () => {
  const subtotal = calcularSubtotal();
  return subtotal * (descuentoGeneral.value / 100);
};

const calcularSubtotal = () => {
  let subtotal = 0;
  for (const entry of selectedProducts.value) {
    const key = `${entry.tipo}-${entry.id}`;
    const cantidad = Number.parseFloat(quantities.value[key]) || 0;
    const precio = Number.parseFloat(prices.value[key]) || 0;
    const descuentoItem = Number.parseFloat(discounts.value[key]) || 0;

    const subtotalItem = cantidad * precio;
    const descuentoItemMonto = subtotalItem * (descuentoItem / 100);
    subtotal += subtotalItem - descuentoItemMonto;
  }
  return subtotal;
};

// Computed para los totales
const totales = computed(() => {
  let subtotal = 0;
  let descuentoItems = 0;

  // Calcular subtotal y descuentos por item
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

  // Calcular descuento general
  const subtotalConDescuentoItems = subtotal - descuentoItems;
  const descuentoGeneralMonto = subtotalConDescuentoItems * (descuentoGeneral.value / 100);

  // Calcular subtotal con todos los descuentos
  const subtotalConDescuentos = subtotalConDescuentoItems - descuentoGeneralMonto;

  // Calcular IVA
  const iva = subtotalConDescuentos * IVA_RATE;

  // Calcular total final
  const total = subtotalConDescuentos + iva;

  return {
    subtotal: subtotal,
    descuentoItems: descuentoItems,
    descuentoGeneral: descuentoGeneralMonto,
    subtotalConDescuentos: subtotalConDescuentos,
    iva: iva,
    total: total
  };
});

// Verifica el email en tiempo real
watch(() => form.email, async (nuevoEmail) => {
  if (!nuevoEmail || nuevoEmail.length < 5) {
    emailDisponible.value = true
    return
  }

  revisandoEmail.value = true
  try {
    const response = await axios.get(route('clientes.checkEmail'), {
      params: { email: nuevoEmail }
    })
    emailDisponible.value = !response.data.exists
  } catch (error) {
    console.error('Error al verificar el email:', error)
    emailDisponible.value = true // fallback
  } finally {
    revisandoEmail.value = false
  }
})

const calcularTotal = () => {
  // Actualizar el formulario con los nuevos valores
  form.subtotal = totales.value.subtotal;
  form.descuento_general = totales.value.descuentoGeneral;
  form.descuento_items = totales.value.descuentoItems;
  form.iva = totales.value.iva;
  form.total = totales.value.total;
};

const crearCotizacion = () => {
  form.clearErrors(); // 🔄 Limpia errores previos

  form.productos = selectedProducts.value.map((entry) => {
    const key = `${entry.tipo}-${entry.id}`;
    const cantidad = quantities.value[key] || 1;
    const precio = prices.value[key] || 0;
    const descuento = discounts.value[key] || 0;
    return {
      id: entry.id,
      tipo: entry.tipo,
      cantidad: cantidad,
      precio: precio,
      descuento: descuento,
      subtotal: cantidad * precio,
      descuento_monto: (cantidad * precio) * (descuento / 100)
    };
  });

  calcularTotal();

  form.post(route('cotizaciones.store'), {
    onSuccess: () => {
      selectedProducts.value = [];
      quantities.value = {};
      prices.value = {};
      discounts.value = {};
      descuentoGeneral.value = 0;
      clienteSeleccionado.value = null;
      form.reset();
    },
    onError: (errors) => {
      console.error('Errores de validación:', errors);
      alert("Hubo errores de validación. Por favor, corrige los campos.");
    },
    // ❗ NO es necesario manipular form.processing manualmente
    // onFinish se encarga automáticamente
  });
};


onMounted(() => {
  console.log('Clientes:', props.clientes);
  console.log('Productos:', props.productos);
  console.log('Servicios:', props.servicios);

  // Inicializar el cálculo
  calcularTotal();
});
</script>
