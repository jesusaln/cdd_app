<!-- Resources/js/Pages/Compras/CrearCompra.vue -->
<template>
  <Head title="Crear Compra" />
  <div class="compras-create min-h-screen bg-gradient-to-br from-slate-50 to-blue-50 p-6">
    <div class="max-w-6xl mx-auto">
      <!-- Header -->
      <div class="mb-6">
        <Header
          title="Nueva Compra"
          description="Crea una nueva compra para tus proveedores"
          :can-preview="proveedorSeleccionado && form.almacen_id && selectedProducts.length > 0"
          :back-url="route('compras.index')"
          :show-shortcuts="mostrarAtajos"
          @preview="handlePreview"
          @close-shortcuts="mostrarAtajos = false"
        />
        <div class="mt-4 bg-blue-50 border border-blue-200 rounded-md p-3">
          <div class="flex">
            <div class="flex-shrink-0">
              <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
              </svg>
            </div>
            <div class="ml-3">
              <p class="text-sm text-blue-700">
                <strong>Nota:</strong> El IVA se calcula automáticamente según la configuración de la empresa. Ingrese los precios SIN IVA.
              </p>
            </div>
          </div>
        </div>
      </div>

      <form @submit.prevent="crearCompra" class="space-y-8">
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
            <!-- Número de Compra -->
            <div>
              <label for="numero_compra" class="block text-sm font-medium text-gray-700 mb-2 flex items-center gap-2">
                Número de Compra *
                <span class="inline-flex items-center gap-1 px-2 py-1 bg-blue-100 text-blue-700 text-xs font-medium rounded-full">
                  <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                  Número fijo
                </span>
              </label>
              <div class="relative">
                <input
                  id="numero_compra"
                  v-model="form.numero_compra"
                  type="text"
                  class="w-full bg-gray-50 text-gray-500 cursor-not-allowed border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                  placeholder="C0001"
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
                Este número es fijo para todas las compras
              </p>
            </div>

            <!-- Fecha de Compra -->
            <div>
              <label for="fecha_compra" class="block text-sm font-medium text-gray-700 mb-2 flex items-center gap-2">
                Fecha de Compra *
                <span class="inline-flex items-center gap-1 px-2 py-1 bg-blue-100 text-blue-700 text-xs font-medium rounded-full">
                  <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
                  Automática
                </span>
              </label>
              <div class="relative">
                <input
                  id="fecha_compra"
                  v-model="form.fecha_compra"
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

        <!-- Almacén de Recepción -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
          <div class="bg-gradient-to-r from-orange-500 to-orange-600 px-6 py-4">
            <h2 class="text-lg font-semibold text-white flex items-center">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
              </svg>
              Almacén de Recepción
            </h2>
          </div>
          <div class="p-6">
            <div>
              <label for="almacen_id" class="block text-sm font-medium text-gray-700 mb-2">
                Almacén donde se recibirán los productos *
              </label>
              <select
                id="almacen_id"
                v-model="form.almacen_id"
                required
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all duration-200"
              >
                <option value="">Seleccionar almacén</option>
                <option v-for="almacen in props.almacenes" :key="almacen.id" :value="almacen.id">
                  {{ almacen.nombre }}
                </option>
              </select>
              <p class="mt-1 text-xs text-gray-500">
                Los productos comprados se agregarán automáticamente al inventario de este almacén
              </p>
              <!-- Recordatorio del almacén predeterminado -->
              <div v-if="props.recordatorio_almacen" class="mt-2 p-2 bg-blue-50 border border-blue-200 rounded-md">
                <p class="text-xs text-blue-700">
                  <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                  {{ props.recordatorio_almacen }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Productos Disponibles -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
          <div class="bg-gradient-to-r from-green-500 to-green-600 px-6 py-4">
            <h2 class="text-lg font-semibold text-white flex items-center">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
              </svg>
              Productos Disponibles
            </h2>
          </div>
          <div class="p-6">
            <!-- Productos para selección directa -->
            <div v-if="productosDisponibles.length > 0" class="mb-6">
              <h3 class="text-sm font-medium text-gray-700 mb-3">Selecciona productos para comprar:</h3>
              <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                <div
                  v-for="producto in productosDisponibles"
                  :key="producto.id"
                  @click="seleccionarProducto(producto)"
                  :class="[
                    'p-4 border-2 rounded-lg cursor-pointer transition-all duration-200 hover:shadow-md',
                    productoSeleccionado?.id === producto.id
                      ? 'border-green-500 bg-green-50'
                      : 'border-gray-200 hover:border-green-300 hover:bg-green-50'
                  ]"
                >
                  <div class="flex items-center justify-between mb-2">
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                      {{ producto.codigo || 'N/A' }}
                    </span>
                    <span class="text-xs text-gray-500">{{ producto.unidad_medida }}</span>
                  </div>

                  <h4 class="font-medium text-gray-900 text-sm mb-1">{{ producto.nombre }}</h4>

                  <div v-if="producto.categoria" class="text-xs text-gray-500 mb-2">
                    {{ typeof producto.categoria === 'string' ? producto.categoria : producto.categoria.nombre }}
                  </div>

                  <div class="space-y-1">
                    <div class="flex justify-between text-sm">
                      <span class="text-gray-600">Precio compra (SIN IVA):</span>
                      <span class="font-semibold text-green-600">${{ formatearPrecio(producto.precio_compra) }}</span>
                    </div>

                    <div class="flex justify-between text-sm">
                      <span class="text-gray-600">Precio venta:</span>
                      <span class="font-semibold text-blue-600">${{ formatearPrecio(producto.precio_venta) }}</span>
                    </div>

                    <div class="flex justify-between text-sm">
                      <span class="text-gray-600">Stock disponible:</span>
                      <span :class="[
                        'font-semibold',
                        producto.stock_total > 10 ? 'text-green-600' :
                        producto.stock_total > 0 ? 'text-yellow-600' : 'text-red-600'
                      ]">
                        {{ producto.stock_total }}
                      </span>
                    </div>
                  </div>

                  <!-- Indicador de stock por almacén -->
                  <div v-if="producto.stock_por_almacen" class="mt-2 pt-2 border-t border-gray-100">
                    <div class="text-xs text-gray-500 mb-1">Stock por almacén:</div>
                    <div class="space-y-1">
                      <div v-for="(stockInfo, almacenId) in producto.stock_por_almacen" :key="almacenId" class="flex justify-between text-xs">
                        <span class="text-gray-600">{{ stockInfo.almacen_nombre }}:</span>
                        <span class="font-medium">{{ stockInfo.cantidad }}</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Mensaje cuando no hay productos -->
            <div v-else class="text-center py-8">
              <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
              </svg>
              <p class="text-gray-500 text-lg font-medium">No hay productos disponibles</p>
              <p class="text-gray-400 text-sm mt-1">Los productos aparecerán aquí cuando tengan información completa</p>
            </div>

            <!-- Buscador tradicional (como respaldo) -->
            <div class="mt-6 pt-6 border-t border-gray-200">
              <BuscarProducto
                ref="buscarProductoRef"
                :productos="props.productos"
                :servicios="props.servicios"
                @agregar-producto="agregarProducto"
              />
            </div>

            <!-- Productos seleccionados -->
       <ProductosSeleccionados
         :selected-products="selectedProducts"
         :productos="props.productos"
         :servicios="props.servicios"
         :quantities="quantities"
         :prices="prices"
         :discounts="discounts"
         @open-serials="openSerials"
         @eliminar-producto="eliminarProducto"
         @update-quantity="updateQuantity"
         @update-discount="updateDiscount"
         @update-serials="updateSerials"
       />
    
       <!-- Modal para capturar series -->
       <div v-if="showSerialsModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
         <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
           <div class="p-6">
             <h3 class="text-lg font-semibold text-gray-900 mb-4">
               Capturar Series - {{ currentSerialProduct?.nombre }}
             </h3>
    
             <div class="mb-4">
               <p class="text-sm text-gray-600">
                 Cantidad: {{ currentSerialQty }} unidades
               </p>
               <p class="text-xs text-gray-500 mt-1">
                 Debe capturar exactamente {{ currentSerialQty }} series únicas
               </p>
             </div>
    
             <div class="space-y-2 max-h-60 overflow-y-auto">
               <div
                 v-for="(serie, index) in serialsForEntry"
                 :key="index"
                 class="flex items-center space-x-2"
               >
                 <span class="text-sm font-medium text-gray-500 w-6">{{ index + 1 }}.</span>
                 <input
                   v-model="serialsForEntry[index]"
                   type="text"
                   :placeholder="`Serie ${index + 1}`"
                   class="flex-1 px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                   required
                 />
               </div>
             </div>
    
             <div class="flex justify-end space-x-3 mt-6">
               <button
                 @click="cancelSerials"
                 class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200"
               >
                 Cancelar
               </button>
               <button
                 @click="saveSerials"
                 class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700"
               >
                 Guardar Series
               </button>
             </div>
           </div>
         </div>
       </div>
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
              placeholder="Agrega notas adicionales, términos y condiciones, o información relevante para la compra..."
            ></textarea>
          </div>
        </div>

        <!-- Descuento General -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
          <div class="bg-gradient-to-r from-red-500 to-red-600 px-6 py-4">
            <h2 class="text-lg font-semibold text-white flex items-center">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
              </svg>
              Descuento General
            </h2>
          </div>
          <div class="p-6">
            <div class="space-y-4">
              <div>
                <label for="descuento_general" class="block text-sm font-medium text-gray-700 mb-2">
                  Descuento General ($)
                </label>
                <input
                  id="descuento_general"
                  type="number"
                  step="0.01"
                  min="0"
                  v-model="form.descuento_general"
                  class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-red-500 focus:border-transparent"
                  placeholder="0.00"
                />
                <p class="mt-1 text-xs text-gray-500">
                  Este descuento se aplica al subtotal después de los descuentos por item
                </p>
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
          :back-url="route('compras.index')"
          :is-processing="form.processing"
          :can-submit="form.proveedor_id && form.almacen_id && selectedProducts.length > 0"
          :button-text="form.processing ? 'Guardando...' : 'Crear Compra'"
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
        type="compra"
        :proveedor="proveedorSeleccionado"
        :items="selectedProducts"
        :totals="totales"
        :notas="form.notas"
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
  almacenes: { type: Array, default: () => [] },
  almacen_predeterminado: { type: [String, Number], default: null },
  recordatorio_almacen: { type: String, default: null },
});

// Copia reactiva de proveedores para evitar mutación de props
const proveedoresList = ref([...props.proveedores]);

// Número de compra fijo
const numeroCompraFijo = 'C0001';

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
  numero_compra: numeroCompraFijo,
  fecha_compra: getCurrentDate(),
  proveedor_id: '',
  almacen_id: props.almacen_predeterminado || '',
  descuento_general: 0,
  subtotal: 0,
  descuento_items: 0,
  iva: 0,
  total: 0,
  productos: [],
  notas: '',
  estado: 'borrador',
});

// Referencias
const buscarProductoRef = ref(null);
const proveedorSeleccionado = ref(null);
const selectedProducts = ref([]);
const quantities = ref({});
const prices = ref({});
const discounts = ref({});
// Series por producto seleccionado
const serialsMap = ref({});
const showSerialsModal = ref(false);
const serialsForEntry = ref([]);
const currentSerialKey = ref('');
const currentSerialQty = ref(1);
const currentSerialProduct = ref(null);
const mostrarVistaPrevia = ref(false);
const mostrarAtajos = ref(true);
const productoSeleccionado = ref(null);

// Productos disponibles para selección directa
const productosDisponibles = computed(() => {
  return props.productos.filter(producto => {
    // Filtrar productos que tienen información completa
    return producto.nombre &&
           producto.precio_compra > 0 &&
           producto.codigo &&
           producto.categoria;
  });
});

// Función para seleccionar producto con click
const seleccionarProducto = (producto) => {
  productoSeleccionado.value = producto;

  // Agregar automáticamente a la lista de productos seleccionados
  const itemEntry = { id: producto.id, tipo: 'producto' };
  const exists = selectedProducts.value.some(
    (entry) => entry.id === producto.id && entry.tipo === 'producto'
  );

  if (!exists) {
    selectedProducts.value.push(itemEntry);
    const key = `producto-${producto.id}`;
    quantities.value[key] = 1;
    prices.value[key] = producto.precio_compra || 0;
    discounts.value[key] = 0;
    calcularTotal();
    saveState();
    showNotification(`Producto seleccionado: ${producto.nombre}`);
  } else {
    showNotification('Este producto ya está seleccionado', 'info');
  }
};

// Función para formatear precios
const formatearPrecio = (precio) => {
  const precioNum = Number.parseFloat(precio) || 0;
  return precioNum.toLocaleString('es-MX', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  });
};

// Guardar y cargar estado en localStorage
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

// Funciones
const handlePreview = () => {
  if (proveedorSeleccionado.value && form.almacen_id && selectedProducts.value.length > 0) {
    mostrarVistaPrevia.value = true;
  } else {
    showNotification('Selecciona un proveedor, almacén y al menos un producto', 'error');
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
    (item) => !(item.id === entry.id && item.tipo === item.tipo)
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

const updateSerials = (key, serials) => {
  serialsMap.value[key] = serials;
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
  const subtotalConDescuentos = Math.max(0, subtotal - descuentoItems - descuentoGeneral);
  const ivaRate = 0.16; // Usar IVA configurable de la empresa
  const iva = subtotalConDescuentos * ivaRate;
  const total = subtotalConDescuentos + iva;

  return {
    subtotal: parseFloat(subtotal.toFixed(2)),
    descuentoItems: parseFloat(descuentoItems.toFixed(2)),
    descuentoGeneral: parseFloat(descuentoGeneral.toFixed(2)),
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

const validarDatos = () => {
  if (!form.proveedor_id) {
    showNotification('Selecciona un proveedor', 'error');
    return false;
  }

  if (!form.almacen_id) {
    showNotification('Selecciona un almacén de recepción', 'error');
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

const crearCompra = () => {
  if (!validarDatos()) {
    return;
  }

  form.productos = selectedProducts.value.map((entry) => {
    const key = `${entry.tipo}-${entry.id}`;
    const seriales = serialsMap.value[key];
    const productoData = {
      id: entry.id,
      tipo: entry.tipo,
      cantidad: parseFloat(quantities.value[key]) || 1,
      precio: parseFloat(prices.value[key]) || 0,
      descuento: parseFloat(discounts.value[key]) || 0,
    };

    // Solo incluir seriales si existen y no están vacíos
    if (seriales && Array.isArray(seriales) && seriales.length > 0) {
      productoData.seriales = seriales;
    }

    return productoData;
  });

  calcularTotal();

  form.post(route('compras.store'), {
    onSuccess: () => {
      removeFromLocalStorage('compraEnProgreso');
      selectedProducts.value = [];
      quantities.value = {};
      prices.value = {};
      discounts.value = {};
      proveedorSeleccionado.value = null;
      form.reset();
      showNotification('Compra creada con éxito');
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

// Captura de series
const openSerials = (entry) => {
  const key = `${entry.tipo}-${entry.id}`;
  currentSerialKey.value = key;
  currentSerialQty.value = Number(quantities.value[key]) || 1;

  // Encontrar el producto para mostrar su nombre
  const producto = props.productos.find(p => p.id === entry.id);
  currentSerialProduct.value = producto || null;

  const existentes = serialsMap.value[key] || [];
  serialsForEntry.value = Array.from({ length: currentSerialQty.value }, (_, i) => existentes[i] || '');
  showSerialsModal.value = true;
};

const saveSerials = () => {
  const serials = serialsForEntry.value.map(s => (s || '').trim()).filter(Boolean);
  if (serials.length !== currentSerialQty.value) {
    showNotification(`Debes capturar exactamente ${currentSerialQty.value} series.`, 'error');
    return;
  }
  if ((new Set(serials)).size !== serials.length) {
    showNotification('Las series deben ser únicas.', 'error');
    return;
  }
  serialsMap.value[currentSerialKey.value] = serials;
  showSerialsModal.value = false;
  showNotification(`Series capturadas correctamente para ${currentSerialProduct.value?.nombre || 'el producto'}`, 'success');
};

const cancelSerials = () => {
  showSerialsModal.value = false;
  currentSerialProduct.value = null;
  serialsForEntry.value = [];
  currentSerialKey.value = '';
  currentSerialQty.value = 1;
};

const limpiarFormulario = () => {
  proveedorSeleccionado.value = null;
  form.numero_compra = numeroCompraFijo;
  form.fecha_compra = getCurrentDate();
  form.proveedor_id = '';
  form.descuento_general = 0;
  selectedProducts.value = [];
  quantities.value = {};
  prices.value = {};
  discounts.value = {};
  serialsMap.value = {};
  form.notas = '';
  removeFromLocalStorage('compraEnProgreso');
  showNotification('Formulario limpiado correctamente');
};

const saveState = () => {
  const stateToSave = {
    numero_compra: numeroCompraFijo,
    fecha_compra: form.fecha_compra,
    proveedor_id: form.proveedor_id,
    descuento_general: form.descuento_general,
    proveedor: proveedorSeleccionado.value,
    selectedProducts: selectedProducts.value,
    quantities: quantities.value,
    prices: prices.value,
    discounts: discounts.value,
  };
  saveToLocalStorage('compraEnProgreso', stateToSave);
};

const handleBeforeUnload = (event) => {
  if (form.proveedor_id || selectedProducts.value.length > 0) {
    event.preventDefault();
    event.returnValue = 'Tienes cambios sin guardar. ¿Estás seguro de que quieres salir?';
  }
};

// Función para asegurar que la fecha sea siempre la actual
const asegurarFechaActual = () => {
  const fechaActual = getCurrentDate();
  if (form.fecha_compra !== fechaActual) {
    form.fecha_compra = fechaActual;
  }
};

// Lifecycle hooks
onMounted(() => {
  // Mostrar información sobre el número de compra fijo
  showNotification(`Número de compra fijo: ${numeroCompraFijo}`, 'info');

  const savedData = loadFromLocalStorage('compraEnProgreso');
  if (savedData && typeof savedData === 'object') {
    try {
      form.numero_compra = numeroCompraFijo;
      form.fecha_compra = getCurrentDate(); // Siempre usar fecha actual
      form.proveedor_id = savedData.proveedor_id || '';
      form.descuento_general = savedData.descuento_general || 0;
      proveedorSeleccionado.value = savedData.proveedor || null;
      selectedProducts.value = Array.isArray(savedData.selectedProducts) ? savedData.selectedProducts : [];
      quantities.value = savedData.quantities || {};
      prices.value = savedData.prices || {};
      discounts.value = savedData.discounts || {};
      calcularTotal();
    } catch (error) {
      console.warn('Error al cargar datos guardados:', error);
      removeFromLocalStorage('compraEnProgreso');
    }
  }

  // Verificar la fecha cada 5 minutos para mantenerla actual
  const fechaInterval = setInterval(() => {
    asegurarFechaActual();
  }, 5 * 60 * 1000); // 5 minutos

  // Limpiar el intervalo cuando el componente se desmonte
  onBeforeUnmount(() => {
    clearInterval(fechaInterval);
  });

  window.addEventListener('beforeunload', handleBeforeUnload);
});

onBeforeUnmount(() => {
  window.removeEventListener('beforeunload', handleBeforeUnload);
});
</script>
