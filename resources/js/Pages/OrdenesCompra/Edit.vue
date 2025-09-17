<template>
  <Head title="Editar Orden de Compra" />

  <div class="compras-edit min-h-screen bg-gradient-to-br from-slate-50 to-blue-50 p-6">
    <div class="max-w-6xl mx-auto">
      <!-- Header -->
      <Header
        title="Editar Orden de Compra"
        description="Modifica los detalles de la orden de compra"
        :can-preview="proveedorSeleccionado && form.items.length > 0"
        :back-url="route('ordenescompra.index')"
        :show-shortcuts="mostrarAtajos"
        @preview="handlePreview"
        @close-shortcuts="closeShortcuts"
      />

      <form @submit.prevent="updatePurchaseOrder" class="space-y-8">
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
              ref="buscarProveedorRef"
              :proveedores="proveedoresActivos"
              :proveedor-seleccionado="proveedorSeleccionado"
              @proveedor-seleccionado="onProveedorSeleccionado"
              @crear-nuevo-proveedor="crearNuevoProveedor"
            />
          </div>
        </div>

        <!-- Productos -->
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
              placeholder="Agrega notas adicionales, términos y condiciones, o información relevante para la orden de compra..."
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
          :can-submit="canSubmit"
          :button-text="form.processing ? 'Actualizando...' : 'Actualizar Orden de Compra'"
        />

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
      </form>

      <!-- Modal Vista Previa -->
      <VistaPreviaModal
        :show="mostrarVistaPrevia"
        type="ordencompra"
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
            <div class="form-group">
              <label for="buscarProveedor" class="form-label required">
                Buscar Proveedor
              </label>
              <div class="relative">
                <input
                  id="buscarProveedor"
                  v-model="supplierSearchQuery"
                  type="text"
                  placeholder="Escriba el nombre del proveedor..."
                  @focus="showSuppliers = true"
                  @blur="hideSuppliersAfterDelay"
                  @input="onSupplierSearch"
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
                  <div v-if="showSuppliers && (filteredSuppliers.length > 0 || loadingSuppliers)"
                       class="dropdown-menu">
                    <div v-if="loadingSuppliers" class="px-4 py-3 text-center text-gray-500">
                      <div class="inline-flex items-center">
                        <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24">
                          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Buscando...
                      </div>
                    </div>
                    <div v-else-if="filteredSuppliers.length === 0" class="px-4 py-3 text-center text-gray-500">
                      No se encontraron proveedores
                    </div>
                    <div v-else class="max-h-60 overflow-y-auto">
                      <button
                        v-for="supplier in filteredSuppliers"
                        :key="supplier.id"
                        type="button"
                        @click="selectSupplier(supplier)"
                        class="dropdown-item proveedor-item"
                      >
                        <div class="flex flex-col">
                          <span class="font-medium">{{ supplier.nombre_razon_social }}</span>
                          <span v-if="supplier.codigo" class="text-sm text-gray-500">
                            Código: {{ supplier.codigo }}
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
              <div v-if="selectedSupplierName" class="mt-3 p-3 bg-green-50 border border-green-200 rounded-lg">
                <div class="flex items-center">
                  <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                  <div>
                    <p class="font-medium text-green-800">{{ selectedSupplierName }}</p>
                    <p v-if="selectedSupplier?.codigo" class="text-sm text-green-600">
                      Código: {{ selectedSupplier.codigo }}
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
                  v-model="itemSearchQuery"
                  type="text"
                  placeholder="Buscar por nombre, código o código de barras..."
                  @focus="showItems = true"
                  @blur="hideItemsAfterDelay"
                  @input="onItemSearch"
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
                  <div v-if="showItems && (filteredItems.length > 0 || loadingItems)"
                       class="dropdown-menu">
                    <div v-if="loadingItems" class="px-4 py-3 text-center text-gray-500">
                      <div class="inline-flex items-center">
                        <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24">
                          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Buscando...
                      </div>
                    </div>
                    <div v-else-if="filteredItems.length === 0" class="px-4 py-3 text-center text-gray-500">
                      No se encontraron productos/servicios
                    </div>
                    <div v-else class="max-h-60 overflow-y-auto">
                      <button
                        v-for="item in filteredItems"
                        :key="`${item.tipo}-${item.id}`"
                        type="button"
                        @click="addItem(item)"
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
        <div v-if="form.items.length > 0" class="card">
          <div class="card-header">
            <div class="flex items-center justify-between">
              <h2 class="card-title">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                Items Seleccionados ({{ form.items.length }})
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
                  <tr v-for="(entry, index) in form.items" :key="`${entry.tipo}-${entry.id}`"
                      class="hover:bg-gray-50 transition-colors duration-150">
                    <td class="table-cell">
                      <div class="flex flex-col">
                        <span class="font-medium text-gray-900">
                          {{ getItemDetails(entry)?.nombre || 'Item no encontrado' }}
                        </span>
                        <span v-if="getItemDetails(entry)?.codigo" class="text-sm text-gray-500">
                          Código: {{ getItemDetails(entry).codigo }}
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
                        v-model.number="entry.cantidad"
                        type="number"
                        class="form-input-sm"
                        min="1"
                        step="1"
                        @input="calculateTotal"
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
                          v-model.number="entry.precio"
                          type="number"
                          class="form-input-sm"
                          min="0"
                          step="0.01"
                          @input="calculateTotal"
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
                        @click="removeItem(entry)"
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
            <p v-if="form.errors.items" class="form-error">
              {{ form.errors.items }}
            </p>
          </div>
        </div>

        <!-- Resumen y Acciones -->
        <div class="card">
          <div class="card-body">
            <div class="flex items-center justify-between">
              <div class="flex items-center space-x-4">
                <div class="text-sm text-gray-600">
                  <span class="font-medium">Items:</span> {{ form.items.length }}
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
                  :disabled="form.processing"
                >
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                  Cancelar
                </Link>

                <button
                  type="submit"
                  class="btn-primary"
                  :disabled="!canSubmit || form.processing"
                >
                  <svg v-if="form.processing" class="animate-spin -ml-1 mr-3 h-4 w-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  <svg v-else class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                  {{ form.processing ? 'Actualizando...' : 'Actualizar Orden de Compra' }}
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
import { ref, computed, onMounted } from 'vue';
import { Head, useForm, Link, router } from '@inertiajs/vue3';
import axios from 'axios';
import { Notyf } from 'notyf';
import 'notyf/notyf.min.css';
import AppLayout from '@/Layouts/AppLayout.vue';
import Header from '@/Components/CreateComponents/Header.vue';
import BuscarProveedor from '@/Components/CreateComponents/BuscarProveedor.vue';
import BuscarProducto from '@/Components/CreateComponents/BuscarProducto.vue';
import ProductosSeleccionados from '@/Components/CreateComponents/ProductosSeleccionados.vue';
import Totales from '@/Components/CreateComponents/Totales.vue';
import BotonesAccion from '@/Components/CreateComponents/BotonesAccion.vue';
import VistaPreviaModal from '@/Components/Modals/VistaPreviaModal.vue';

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

// Estado reactivo
const selectedProducts = ref([]);
const quantities = ref({});
const prices = ref({});
const discounts = ref({});
const proveedorSeleccionado = ref(null);
const mostrarVistaPrevia = ref(false);
const mostrarAtajos = ref(false);

// Funciones necesarias
const handlePreview = () => {
  if (proveedorSeleccionado.value && selectedProducts.value.length > 0) {
    mostrarVistaPrevia.value = true;
  } else {
    showNotification('Selecciona un proveedor y al menos un producto', 'error');
  }
};

const closeShortcuts = () => {
  mostrarAtajos.value = false;
};

const onProveedorSeleccionado = (proveedor) => {
  if (!proveedor) {
    proveedorSeleccionado.value = null;
    return;
  }
  proveedorSeleccionado.value = proveedor;
  showNotification(`Proveedor: ${proveedor.nombre_razon_social || proveedor.nombre || 'Sin nombre'}`);
};

const crearNuevoProveedor = async (nombreBuscado) => {
  if (!nombreBuscado?.trim()) {
    showNotification('El nombre del proveedor es requerido', 'error');
    return;
  }

  try {
    const response = await axios.post(route('proveedores.store'), {
      nombre_razon_social: nombreBuscado.trim()
    });

    if (response.data) {
      const nuevoProveedor = response.data;
      showNotification(`Proveedor creado: ${nuevoProveedor.nombre_razon_social || nuevoProveedor.nombre || 'Sin nombre'}`);
    }
  } catch (error) {
    if (error.response?.status === 409) {
      showNotification('Ya existe un proveedor con ese nombre', 'error');
    } else {
      showNotification('No se pudo crear el proveedor', 'error');
    }
  }
};

const agregarProducto = (item) => {
  if (!item?.id || !item?.tipo) {
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
    prices.value[key] = item.tipo === 'producto' ? (item.precio_compra || item.precio || 0) : (item.precio || 0);
    discounts.value[key] = 0;
    showNotification(`Añadido: ${item.nombre || 'Producto'}`);
  } else {
    showNotification(`${item.nombre || 'Producto'} ya está agregado`, 'info');
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
};

const updateQuantity = (key, quantity) => {
  if (quantity <= 0) {
    showNotification('La cantidad debe ser mayor a 0', 'error');
    return;
  }
  quantities.value[key] = parseFloat(quantity);
};

const updateDiscount = (key, discount) => {
  if (discount < 0 || discount > 100) {
    showNotification('El descuento debe estar entre 0% y 100%', 'error');
    return;
  }
  discounts.value[key] = parseFloat(discount);
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

    const subtotalItem = cantidad * precio;
    const descuentoItem = subtotalItem * (descuento / 100);

    subtotal += subtotalItem;
    descuentoItems += descuentoItem;
  });

  const subtotalConDescuentos = subtotal - descuentoItems;
  const iva = subtotalConDescuentos * 0.16;
  const total = subtotalConDescuentos + iva;

  return {
    subtotal: Number(subtotal.toFixed(2)),
    descuentoItems: Number(descuentoItems.toFixed(2)),
    subtotalConDescuentos: Number(subtotalConDescuentos.toFixed(2)),
    iva: Number(iva.toFixed(2)),
    total: Number(total.toFixed(2)),
  };
});

const canSubmit = computed(() => {
  return proveedorSeleccionado.value && selectedProducts.value.length > 0;
});

// Filtrar solo proveedores activos
const proveedoresActivos = computed(() => {
  return props.proveedores.filter(proveedor => {
    const estado = proveedor.estado || proveedor.status || proveedor.activo;
    if (typeof estado === 'string') {
      return estado.toLowerCase() === 'activo' || estado.toLowerCase() === 'active';
    } else if (typeof estado === 'boolean') {
      return estado === true;
    } else if (typeof estado === 'number') {
      return estado === 1;
    }
    return true;
  });
});

// Formulario
const form = useForm({
  proveedor_id: props.ordenCompra?.proveedor_id || null,
  total: props.ordenCompra?.total || 0,
  items: [],
  notas: props.ordenCompra?.notas || '',
});

// Inicializar datos existentes
onMounted(() => {
  if (props.ordenCompra?.items) {
    props.ordenCompra.items.forEach(item => {
      const key = `${item.pivot?.item_type === 'App\\\\Models\\\\Producto' ? 'producto' : 'servicio'}-${item.id}`;
      selectedProducts.value.push({ id: item.id, tipo: item.pivot?.item_type === 'App\\\\Models\\\\Producto' ? 'producto' : 'servicio' });
      quantities.value[key] = item.pivot?.cantidad || 1;
      prices.value[key] = item.pivot?.precio || 0;
      discounts.value[key] = item.pivot?.descuento || 0;
    });
  }
});

// Función para actualizar la orden de compra
const updatePurchaseOrder = () => {
  if (!form.proveedor_id) {
    showNotification('Selecciona un proveedor', 'error');
    return;
  }

  if (selectedProducts.value.length === 0) {
    showNotification('Agrega al menos un producto o servicio', 'error');
    return;
  }

  form.items = selectedProducts.value.map(entry => {
    const key = `${entry.tipo}-${entry.id}`;
    return {
      id: entry.id,
      tipo: entry.tipo,
      cantidad: quantities.value[key] || 1,
      precio: prices.value[key] || 0,
      descuento: discounts.value[key] || 0,
    };
  });

  form.put(route('ordenescompra.update', props.ordenCompra.id), {
    onSuccess: () => {
      showNotification('Orden de compra actualizada con éxito');
      router.visit(route('ordenescompra.index'));
    },
    onError: (errors) => {
      console.error('Errores de validación:', errors);
      if (typeof errors === 'object' && errors !== null) {
        const errorMessages = Object.values(errors).flat().join(', ');
        showNotification(`Errores: ${errorMessages}`, 'error');
      } else {
        showNotification('Hubo errores al actualizar la orden de compra', 'error');
      }
    },
  });
};

// Configuración del layout
defineOptions({ layout: AppLayout });

// Propiedades del componente
const props = defineProps({
  ordenCompra: {
    type: Object,
    required: true,
  },
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
});

// Función auxiliar para parsear flotantes de forma segura
const safeParseFloat = (value) => {
  const parsed = parseFloat(value);
  return isNaN(parsed) ? 0 : parsed;
};

// Inicialización del formulario con datos de la orden de compra existente
const form = useForm({
  proveedor_id: props.ordenCompra?.proveedor_id || null,
  total: safeParseFloat(props.ordenCompra?.total || 0).toFixed(2),
  items: props.ordenCompra?.items?.map(item => ({
    id: item.id,
    tipo: item.pivot?.item_type === 'App\\Models\\Producto' ? 'producto' : 'servicio',
    cantidad: item.pivot?.cantidad || 1,
    precio: safeParseFloat(item.pivot?.precio || 0),
  })) || [],
});

// Estado reactivo
const supplierSearchQuery = ref(props.ordenCompra?.proveedor?.nombre_razon_social || '');
const itemSearchQuery = ref('');
const showSuppliers = ref(false);
const showItems = ref(false);
const selectedSupplierName = ref(props.ordenCompra?.proveedor?.nombre_razon_social || null);
const selectedSupplier = computed(() => props.proveedores.find(s => s.id === form.proveedor_id) || null);
const loadingSuppliers = ref(false);
const loadingItems = ref(false);
const quantityErrors = ref({});
const priceErrors = ref({});

// Temporizadores para búsqueda con debounce
let supplierSearchTimer = null;
let itemSearchTimer = null;

// Propiedades computadas
const filteredSuppliers = computed(() => {
  if (!supplierSearchQuery.value || supplierSearchQuery.value.length < 2) return [];
  const searchTerm = supplierSearchQuery.value.toLowerCase();
  return props.proveedores.filter((proveedor) =>
    proveedor.nombre_razon_social.toLowerCase().includes(searchTerm) ||
    (proveedor.codigo && proveedor.codigo.toLowerCase().includes(searchTerm))
  ).slice(0, 10);
});

const filteredItems = computed(() => {
  if (!itemSearchQuery.value || itemSearchQuery.value.length < 2) return [];
  const searchTerm = itemSearchQuery.value.toLowerCase();

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
  ).slice(0, 15);
});

const canSubmit = computed(() => {
  return form.proveedor_id &&
         form.items.length > 0 &&
         !hasValidationErrors.value &&
         !form.processing;
});

const hasValidationErrors = computed(() => {
  return Object.keys(quantityErrors.value).length > 0 ||
         Object.keys(priceErrors.value).length > 0;
});

// Métodos
const formatCurrency = (value) => {
  return new Intl.NumberFormat('es-MX', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(value || 0);
};

const getItemDetails = (entry) => {
  if (!entry || typeof entry.id === 'undefined' || !entry.tipo) {
    console.error('Entrada inválida para getItemDetails:', entry);
    return null;
  }

  const items = entry.tipo === 'producto' ? props.productos : props.servicios;
  return items.find(item => item.id === entry.id) || null;
};

const getSubtotal = (entry) => {
  const cantidad = Number.parseFloat(entry.cantidad) || 0;
  const precio = Number.parseFloat(entry.precio) || 0;
  return cantidad * precio;
};

const calculateTotal = () => {
  let total = 0;
  for (const entry of form.items) {
    total += getSubtotal(entry);
  }
  form.total = Number.parseFloat(total.toFixed(2));
};

const validateQuantity = (entry) => {
  const key = `${entry.tipo}-${entry.id}`;
  const quantity = entry.cantidad;

  if (!quantity || quantity <= 0) {
    quantityErrors.value[key] = 'La cantidad debe ser mayor a 0';
  } else {
    delete quantityErrors.value[key];
  }
};

const validatePrice = (entry) => {
  const key = `${entry.tipo}-${entry.id}`;
  const price = entry.precio;

  if (price < 0) {
    priceErrors.value[key] = 'El precio no puede ser negativo';
  } else {
    delete priceErrors.value[key];
  }
};

const isItemSelected = (item) => {
  return form.items.some(entry =>
    entry.id === item.id && entry.tipo === item.tipo
  );
};

const onSupplierSearch = () => {
  if (supplierSearchTimer) {
    clearTimeout(supplierSearchTimer);
  }

  loadingSuppliers.value = true;
  supplierSearchTimer = setTimeout(() => {
    loadingSuppliers.value = false;
  }, 300);
};

const onItemSearch = () => {
  if (itemSearchTimer) {
    clearTimeout(itemSearchTimer);
  }

  loadingItems.value = true;
  itemSearchTimer = setTimeout(() => {
    loadingItems.value = false;
  }, 300);
};

const selectSupplier = (supplier) => {
  form.proveedor_id = supplier.id;
  supplierSearchQuery.value = supplier.nombre_razon_social;
  selectedSupplierName.value = supplier.nombre_razon_social;
  showSuppliers.value = false;
};

const addItem = (item) => {
  if (isItemSelected(item)) return;

  form.items.push({
    id: item.id,
    tipo: item.tipo,
    cantidad: 1,
    precio: item.tipo === 'producto' ? (safeParseFloat(item.precio_compra) || 0) : (safeParseFloat(item.precio) || 0),
  });

  itemSearchQuery.value = '';
  showItems.value = false;
  calculateTotal();
};

const removeItem = (entry) => {
  form.items = form.items.filter(
    (item) => !(item.id === entry.id && item.tipo === entry.tipo)
  );
  const key = `${entry.tipo}-${entry.id}`;
  delete quantityErrors.value[key];
  delete priceErrors.value[key];
  calculateTotal();
};

const limpiarItems = () => {
  form.items = [];
  quantityErrors.value = {};
  priceErrors.value = {};
  calculateTotal();
};

const limpiarFormulario = () => {
  form.reset();
  supplierSearchQuery.value = '';
  itemSearchQuery.value = '';
  selectedSupplierName.value = null;
  limpiarItems();
  localStorage.removeItem(`ordenCompraEnProgreso_edit_${props.ordenCompra.id}`);
};

const hideSuppliersAfterDelay = (event) => {
  setTimeout(() => {
    if (!event.relatedTarget || !event.relatedTarget.classList.contains('proveedor-item')) {
      showSuppliers.value = false;
    }
  }, 200);
};

const hideItemsAfterDelay = (event) => {
  setTimeout(() => {
    if (!event.relatedTarget || !event.relatedTarget.classList.contains('producto-item')) {
      showItems.value = false;
    }
  }, 200);
};

const saveToLocalStorage = () => {
  const dataToSave = {
    proveedor_id: form.proveedor_id,
    proveedor_nombre: selectedSupplierName.value,
    items: form.items,
    timestamp: Date.now(),
  };
  localStorage.setItem(`ordenCompraEnProgreso_edit_${props.ordenCompra.id}`, JSON.stringify(dataToSave));
};

const loadFromLocalStorage = () => {
  try {
    const savedData = localStorage.getItem(`ordenCompraEnProgreso_edit_${props.ordenCompra.id}`);
    if (!savedData) return;

    const parsedData = JSON.parse(savedData);

    // Verificar si los datos no están vencidos (24 horas)
    const isDataExpired = Date.now() - (parsedData.timestamp || 0) > 24 * 60 * 60 * 1000;
    if (isDataExpired) {
      localStorage.removeItem(`ordenCompraEnProgreso_edit_${props.ordenCompra.id}`);
      return;
    }

    form.proveedor_id = parsedData.proveedor_id || '';
    selectedSupplierName.value = parsedData.proveedor_nombre || '';
    supplierSearchQuery.value = parsedData.proveedor_nombre || '';

    form.items = Array.isArray(parsedData.items)
      ? parsedData.items.filter(entry => {
          if (!entry || typeof entry !== 'object' || !entry.id || !entry.tipo) {
            console.warn('Entrada de ítem inválida en localStorage:', entry);
            return false;
          }
          const itemExists = getItemDetails(entry) !== null;
          if (!itemExists) {
            console.warn(`Ítem no encontrado en props: ID ${entry.id}, Tipo ${entry.tipo}`);
          }
          return itemExists;
        })
      : [];

    // Validar cantidades y precios al cargar
    form.items.forEach(entry => {
      validateQuantity(entry);
      validatePrice(entry);
    });

    calculateTotal();
    console.log('Datos cargados desde localStorage:', parsedData);
  } catch (error) {
    console.error('Error al cargar datos desde localStorage:', error);
    localStorage.removeItem(`ordenCompraEnProgreso_edit_${props.ordenCompra.id}`);
  }
};

const handleBeforeUnload = (event) => {
  if (form.isDirty) {
    event.preventDefault();
    event.returnValue = '¿Estás seguro de que quieres salir? Los cambios no guardados se perderán.';
  }
};

const updatePurchaseOrder = async () => {
  const hasErrors = form.items.some(entry => {
    validateQuantity(entry);
    validatePrice(entry);
    return quantityErrors.value[`${entry.tipo}-${entry.id}`] ||
           priceErrors.value[`${entry.tipo}-${entry.id}`];
  });

  if (hasErrors) {
    console.error('Hay errores de validación en el formulario');
    return;
  }

  try {
    await form.put(route('ordenescompra.update', props.ordenCompra.id), {
      preserveScroll: true,
      onSuccess: () => {
        localStorage.removeItem(`ordenCompraEnProgreso_edit_${props.ordenCompra.id}`);
        console.log('Orden de compra actualizada exitosamente');
        router.visit(route('ordenescompra.index'));
      },
      onError: (errors) => {
        console.error('Error al actualizar la orden de compra:', errors);
      },
      onFinish: () => {
        form.processing = false;
      },
    });
  } catch (error) {
    console.error('Error inesperado:', error);
    form.processing = false;
  }
};

// Observadores
watch(
  [() => form.proveedor_id, () => form.items, selectedSupplierName],
  () => {
    if (form.proveedor_id || form.items.length > 0) {
      saveToLocalStorage();
    }
  },
  { deep: true }
);

watch(() => form.items, calculateTotal, { deep: true });

// Ciclo de vida
onMounted(() => {
  console.log('Componente montado');
  console.log('Orden de Compra:', props.ordenCompra);
  console.log('Proveedores:', props.proveedores?.length || 0);
  console.log('Productos:', props.productos?.length || 0);
  console.log('Servicios:', props.servicios?.length || 0);

  loadFromLocalStorage();
  window.addEventListener('beforeunload', handleBeforeUnload);
});

onBeforeUnmount(() => {
  window.removeEventListener('beforeunload', handleBeforeUnload);
  if (supplierSearchTimer) clearTimeout(supplierSearchTimer);
  if (itemSearchTimer) clearTimeout(itemSearchTimer);
});
</script>

<style scoped>
/* Estilos base */
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

/* Estilos de formulario */
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

/* Estilos de dropdown */
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

/* Estilos de botones */
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

/* Estilos de badges */
.badge {
  @apply inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium;
}

.badge-blue {
  @apply bg-blue-100 text-blue-800;
}

.badge-green {
  @apply bg-green-100 text-green-800;
}

/* Estilos de tabla */
.table-header {
  @apply px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider;
}

.table-cell {
  @apply px-6 py-4 whitespace-nowrap text-sm text-gray-900;
}

/* Estilos de animación */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

/* Mejoras responsivas */
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

/* Animación de carga */
@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.animate-spin {
  animation: spin 1s linear infinite;
}

/* Barra de desplazamiento personalizada para dropdowns */
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

/* Estilos de foco para accesibilidad */
.dropdown-item:focus {
  @apply ring-2 ring-blue-500 ring-inset;
}

/* Efectos de hover */
.hover-lift:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

/* Estilos para impresión */
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
