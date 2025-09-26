<template>
  <div>
    <Head title="Editar Compra" />
    <div class="compras-edit min-h-screen bg-gradient-to-br from-slate-50 to-blue-50 p-6">
      <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <Header
          title="Editar Compra"
          description="Modifica los detalles de la compra existente"
          :can-preview="proveedorSeleccionado && selectedProducts.length > 0"
          :back-url="route('compras.index')"
          :show-shortcuts="mostrarAtajos"
          @preview="handlePreview"
          @close-shortcuts="mostrarAtajos = false"
        />

        <form @submit.prevent="actualizarCompra" class="space-y-8">
          <!-- Información General -->
          <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 px-6 py-4">
              <h2 class="text-lg font-semibold text-white flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Información General
                <span v-if="cargandoDatos" class="ml-2 inline-flex items-center gap-1 px-2 py-1 bg-indigo-100 text-indigo-700 text-xs font-medium rounded-full">
                  <svg class="w-3 h-3 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                  </svg>
                  Cargando...
                </span>
              </h2>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Número de Compra -->
              <div>
                <label for="numero_compra" class="block text-sm font-medium text-gray-700 mb-2 flex items-center gap-2">
                  Número de Compra
                  <span class="inline-flex items-center gap-1 px-2 py-1 bg-blue-100 text-blue-700 text-xs font-medium rounded-full">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Automático
                  </span>
                </label>
                <div class="relative">
                  <input
                    id="numero_compra"
                    v-model="form.numero_compra"
                    type="text"
                    class="w-full bg-gray-50 text-gray-500 cursor-not-allowed border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    readonly
                  />
                  <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                  </div>
                </div>
                <div class="mt-2 flex items-center gap-2">
                  <p class="text-xs text-gray-500">Este número es fijo para esta compra</p>
                  <button @click="copiarNumeroCompra" type="button" class="inline-flex items-center gap-1 px-2 py-1 bg-gray-100 text-gray-700 text-xs font-medium rounded hover:bg-gray-200 transition-colors" title="Copiar número de compra">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                    Copiar
                  </button>
                </div>
              </div>

              <!-- Fecha de Compra -->
              <div>
                <label for="fecha_compra" class="block text-sm font-medium text-gray-700 mb-2 flex items-center gap-2">
                  Fecha de Compra
                  <span class="inline-flex items-center gap-1 px-2 py-1 bg-blue-100 text-blue-700 text-xs font-medium rounded-full">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
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
                <p class="mt-1 text-xs text-gray-500">Esta fecha se establece con la fecha de creación</p>
              </div>

              <!-- Método de Pago -->
              <div>
                <label for="metodo_pago" class="block text-sm font-medium text-gray-700 mb-2">Método de Pago</label>
                <select
                  id="metodo_pago"
                  v-model="form.metodo_pago"
                  :disabled="cargandoDatos"
                  class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-transparent disabled:bg-gray-100 disabled:cursor-not-allowed"
                >
                  <option value="transferencia">Transferencia</option>
                  <option value="efectivo">Efectivo</option>
                  <option value="tarjeta">Tarjeta</option>
                  <option value="cheque">Cheque</option>
                </select>
              </div>

              <!-- Referencia de Pago (opcional) -->
              <div>
                <label for="referencia_pago" class="block text-sm font-medium text-gray-700 mb-2">Referencia de Pago</label>
                <input
                  id="referencia_pago"
                  v-model="form.referencia_pago"
                  type="text"
                  :disabled="cargandoDatos"
                  class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-transparent disabled:bg-gray-100 disabled:cursor-not-allowed"
                  placeholder="Folio/Referencia / Últimos 4 / Nota"
                />
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
                  <span>Las compras pueden incluir productos y servicios.</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Productos / Servicios -->
          <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="bg-gradient-to-r from-green-500 to-green-600 px-6 py-4">
              <h2 class="text-lg font-semibold text-white flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012 2v2M7 7h10"/>
                </svg>
                Ítems
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
                v-model="form.notas"
                :disabled="cargandoDatos"
                class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-purple-500 focus:border-transparent resize-vertical disabled:bg-gray-100 disabled:cursor-not-allowed"
                rows="4"
                placeholder="Agrega observaciones, términos y condiciones especiales..."
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
            :back-url="route('compras.index')"
            :is-processing="form.processing"
            :can-submit="form.proveedor_id && selectedProducts.length > 0"
            :button-text="form.processing ? 'Guardando...' : 'Actualizar Compra'"
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
          :productos="selectedProducts"
          :totals="totales"
          :notas="form.notas"
          @close="mostrarVistaPrevia = false"
          @print="() => window.print()"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue'
import { Head, useForm, router } from '@inertiajs/vue3'
import axios from 'axios'
import { Notyf } from 'notyf'
import AppLayout from '@/Layouts/AppLayout.vue'
import Header from '@/Components/CreateComponents/Header.vue'
import BuscarProveedor from '@/Components/CreateComponents/BuscarProveedor.vue'
import BuscarProducto from '@/Components/CreateComponents/BuscarProducto.vue'
import ProductosSeleccionados from '@/Components/CreateComponents/ProductosSeleccionados.vue'
import Totales from '@/Components/CreateComponents/Totales.vue'
import BotonesAccion from '@/Components/CreateComponents/BotonesAccion.vue'
import VistaPreviaModal from '@/Components/Modals/VistaPreviaModal.vue'

const notyf = new Notyf({
  duration: 5000,
  position: { x: 'right', y: 'top' },
  types: [
    { type: 'success', background: '#10B981', icon: { className: 'notyf__icon--success', tagName: 'i', text: '✓' } },
    { type: 'error', background: '#EF4444', icon: { className: 'notyf__icon--error', tagName: 'i', text: '✗' } },
    { type: 'info', background: '#3B82F6', icon: { className: 'notyf__icon--info', tagName: 'i', text: 'ℹ' } },
  ],
})
const showNotification = (message, type = 'success') => notyf.open({ type, message })

defineOptions({ layout: AppLayout })

const props = defineProps({
  compra: { type: Object, required: true },
  proveedores: { type: Array, default: () => [] },
  productos: { type: Array, default: () => [] },
  servicios: { type: Array, default: () => [] },
  errors: { type: Object, default: () => ({}) },
})

// Copia local para evitar mutación directa de props
const proveedoresList = ref([ ...props.proveedores ])

// Helpers de fecha
const formatearFecha = (fecha) => {
  if (!fecha) return ''
  try {
    if (typeof fecha === 'string' && /^\d{4}-\d{2}-\d{2}$/.test(fecha)) return fecha
    const d = new Date(fecha)
    if (isNaN(d.getTime())) return ''
    const y = d.getFullYear()
    const m = String(d.getMonth() + 1).padStart(2, '0')
    const dd = String(d.getDate()).padStart(2, '0')
    return `${y}-${m}-${dd}`
  } catch (_) { return '' }
}

// Form
const form = useForm({
  numero_compra: '',
  fecha_compra: '',
  proveedor_id: '',
  metodo_pago: 'transferencia',
  referencia_pago: '',
  subtotal: 0,
  descuento_items: 0,
  descuento_general: parseFloat(props.compra?.descuento_general) || 0,
  iva: 0,
  total: 0,
  items: [],
  notas: '',
})

// Estado UI
const buscarProductoRef = ref(null)
const proveedorSeleccionado = ref(null)
const selectedProducts = ref([])
const quantities = ref({})
const prices = ref({})
const discounts = ref({})
const mostrarVistaPrevia = ref(false)
const mostrarAtajos = ref(true)
const cargandoDatos = ref(true)

// Inicialización
const inicializarFormulario = () => {
  const c = props.compra || {}
  form.numero_compra = c.numero_compra || c.folio || ''
  form.fecha_compra = formatearFecha(c.fecha_compra || c.created_at || new Date())
  form.proveedor_id = c.proveedor_id || c.proveedor?.id || ''
  form.metodo_pago = c.metodo_pago || 'transferencia'
  form.referencia_pago = c.referencia_pago || ''
  form.subtotal = parseFloat(c.subtotal) || 0
  form.descuento_items = parseFloat(c.descuento_items) || 0
  form.descuento_general = parseFloat(c.descuento_general) || 0
  form.iva = parseFloat(c.iva) || 0
  form.total = parseFloat(c.total) || 0
  form.notas = c.notas || c.observaciones || ''
}

const inicializarProveedor = () => {
  if (props.compra?.proveedor) {
    proveedorSeleccionado.value = props.compra.proveedor
  }
  if (!proveedorSeleccionado.value && form.proveedor_id) {
    const p = proveedoresList.value.find(x => x.id === form.proveedor_id)
    if (p) proveedorSeleccionado.value = p
  }
}

const inicializarItems = () => {
  const items = props.compra?.productos || []
  items.forEach(item => {
    // Detectar tipo por presencia en pivot u objeto
    const tipo = item.tipo || item.pivot?.item_type?.includes('Servicio') ? 'servicio' : 'producto'
    const entry = {
      id: item.id,
      tipo,
      nombre: item.nombre || item.descripcion || (tipo + ' ' + item.id),
      descripcion: item.descripcion || '',
      precio: item.pivot?.precio ?? item.precio_compra ?? item.precio ?? 0,
      precio_compra: item.pivot?.precio ?? item.precio_compra ?? 0,
    }
    selectedProducts.value.push(entry)
    const key = `${tipo}-${item.id}`
    quantities.value[key] = item.pivot?.cantidad ?? item.cantidad ?? 1
    prices.value[key] = entry.precio
    discounts.value[key] = item.pivot?.descuento ?? item.descuento ?? 0
  })
  calcularTotal()
}

// Acciones UI
const handlePreview = () => {
  if (proveedorSeleccionado.value && selectedProducts.value.length > 0) {
    mostrarVistaPrevia.value = true
  } else {
    showNotification('Selecciona un proveedor y al menos un ítem', 'error')
  }
}

const onProveedorSeleccionado = (proveedor) => {
  if (!proveedor) {
    proveedorSeleccionado.value = null
    form.proveedor_id = ''
    showNotification('Selección de proveedor limpiada', 'info')
    return
  }
  if (proveedorSeleccionado.value?.id === proveedor.id) return
  proveedorSeleccionado.value = proveedor
  form.proveedor_id = proveedor.id
  showNotification(`Proveedor seleccionado: ${proveedor.nombre_razon_social || proveedor.nombre || 'Sin nombre'}`)
}

const copiarNumeroCompra = async () => {
  const val = (form.numero_compra || '').trim()
  try {
    await navigator.clipboard.writeText(val)
    showNotification(`Número copiado: ${val}`, 'success')
  } catch (e) {
    const ta = document.createElement('textarea')
    ta.value = val
    document.body.appendChild(ta)
    ta.select(); document.execCommand('copy'); document.body.removeChild(ta)
    showNotification(`Número copiado: ${val}`, 'success')
  }
}

const agregarProducto = (item) => {
  if (!item || typeof item.id === 'undefined') {
    showNotification('Ítem inválido', 'error')
    return
  }
  const tipo = item.tipo || (item.esServicio ? 'servicio' : 'producto')
  const exists = selectedProducts.value.some(entry => entry.id === item.id && entry.tipo === tipo)
  if (exists) {
    showNotification('Este ítem ya está en la lista', 'info')
    return
  }
  const entry = {
    id: item.id,
    tipo,
    nombre: item.nombre || item.descripcion || `${tipo} ${item.id}`,
    descripcion: item.descripcion || '',
    precio: item.precio_compra ?? item.precio ?? 0,
    precio_compra: item.precio_compra ?? item.precio ?? 0,
  }
  selectedProducts.value.push(entry)
  const key = `${tipo}-${item.id}`
  quantities.value[key] = 1
  prices.value[key] = entry.precio
  discounts.value[key] = 0
  calcularTotal()
  showNotification(`Añadido: ${entry.nombre}`)
}

const eliminarProducto = (entry) => {
  if (!entry || typeof entry.id === 'undefined' || !entry.tipo) return
  const key = `${entry.tipo}-${entry.id}`
  selectedProducts.value = selectedProducts.value.filter(i => !(i.id === entry.id && i.tipo === entry.tipo))
  delete quantities.value[key]
  delete prices.value[key]
  delete discounts.value[key]
  calcularTotal()
  showNotification(`Eliminado: ${entry.nombre || entry.descripcion || 'Ítem'}`, 'info')
}

const updateQuantity = (key, quantity) => {
  const q = parseFloat(quantity)
  if (isNaN(q) || q <= 0) { showNotification('La cantidad debe ser mayor a 0', 'error'); return }
  quantities.value[key] = q
  calcularTotal()
}

const updateDiscount = (key, discount) => {
  const d = parseFloat(discount)
  if (isNaN(d) || d < 0 || d > 100) { showNotification('El descuento debe estar entre 0% y 100%', 'error'); return }
  discounts.value[key] = d
  calcularTotal()
}

// Totales
const totales = computed(() => {
  let subtotal = 0
  let descuentoItems = 0
  selectedProducts.value.forEach(entry => {
    const key = `${entry.tipo}-${entry.id}`
    const cantidad = parseFloat(quantities.value[key]) || 0
    const precio = parseFloat(prices.value[key]) || 0
    const descuento = parseFloat(discounts.value[key]) || 0
    if (cantidad > 0 && precio >= 0) {
      const subtotalItem = cantidad * precio
      descuentoItems += subtotalItem * (descuento / 100)
      subtotal += subtotalItem
    }
  })
  const descuentoGeneral = parseFloat(form.descuento_general) || 0
  const subtotalConDescuentos = Math.max(0, subtotal - descuentoItems - descuentoGeneral)
  const iva = subtotalConDescuentos * 0.16
  const total = subtotalConDescuentos + iva
  return {
    subtotal: parseFloat(subtotal.toFixed(2)),
    descuentoItems: parseFloat(descuentoItems.toFixed(2)),
    descuentoGeneral: parseFloat(descuentoGeneral.toFixed(2)),
    subtotalConDescuentos: parseFloat(subtotalConDescuentos.toFixed(2)),
    iva: parseFloat(iva.toFixed(2)),
    total: parseFloat(total.toFixed(2)),
  }
})

const calcularTotal = () => {
  form.subtotal = totales.value.subtotal
  form.descuento_items = totales.value.descuentoItems
  form.iva = totales.value.iva
  form.total = totales.value.total
}

// Validación y envío
const validarDatos = () => {
  if (!form.fecha_compra) { showNotification('Fecha de compra no disponible', 'error'); return false }
  if (!form.proveedor_id) { showNotification('Selecciona un proveedor', 'error'); return false }
  if (selectedProducts.value.length === 0) { showNotification('Agrega al menos un ítem', 'error'); return false }
  for (const entry of selectedProducts.value) {
    const key = `${entry.tipo}-${entry.id}`
    const q = parseFloat(quantities.value[key]) || 0
    const p = parseFloat(prices.value[key]) || 0
    const d = parseFloat(discounts.value[key]) || 0
    if (q <= 0) { showNotification('Las cantidades deben ser mayores a 0', 'error'); return false }
    if (p < 0) { showNotification('Los precios no pueden ser negativos', 'error'); return false }
    if (d < 0 || d > 100) { showNotification('Los descuentos deben estar entre 0% y 100%', 'error'); return false }
  }
  return true
}

const actualizarCompra = () => {
  if (!validarDatos()) return
  form.items = selectedProducts.value.map(entry => {
    const key = `${entry.tipo}-${entry.id}`
    return {
      id: entry.id,
      tipo: entry.tipo,
      cantidad: parseFloat(quantities.value[key]) || 1,
      precio: parseFloat(prices.value[key]) || 0,
      descuento: parseFloat(discounts.value[key]) || 0,
    }
  })
  calcularTotal()
  form.put(route('compras.update', props.compra?.id), {
    onSuccess: () => {
      showNotification('Compra actualizada con éxito')
      router.visit(route('compras.index'))
    },
    onError: (errors) => {
      // CSRF expirado
      if (errors && typeof errors === 'object' && errors.message && String(errors.message).includes('CSRF token')) {
        showNotification('La sesión ha expirado. Recargando...', 'error')
        setTimeout(() => window.location.reload(), 1500)
        return
      }
      try {
        const first = Object.values(errors)[0]
        if (Array.isArray(first)) showNotification(first[0], 'error')
        else showNotification('Hubo errores de validación', 'error')
      } catch {
        showNotification('Hubo errores al actualizar la compra', 'error')
      }
    },
  })
}

// Lifecycle
onMounted(() => {
  inicializarFormulario()
  inicializarProveedor()
  inicializarItems()
  cargandoDatos.value = false

  // Mantener fecha coherente cada 5 min (por si el usuario deja la página abierta)
  const fechaInterval = setInterval(() => {
    const hoy = formatearFecha(new Date())
    if (form.fecha_compra !== hoy) form.fecha_compra = hoy
  }, 5 * 60 * 1000)

  window.addEventListener('beforeunload', (event) => {
    if (form.isDirty) {
      event.preventDefault()
      event.returnValue = '¿Salir sin guardar? Tus cambios se perderán.'
    }
  })

  onBeforeUnmount(() => {
    clearInterval(fechaInterval)
  })
})

watch(() => props.compra, (nueva) => {
  if (nueva) {
    selectedProducts.value = []
    quantities.value = {}
    prices.value = {}
    discounts.value = {}
    inicializarFormulario()
    inicializarProveedor()
    inicializarItems()
    cargandoDatos.value = false
  }
}, { immediate: false })

onBeforeUnmount(() => {
  window.removeEventListener('beforeunload', () => {})
})
</script>

<style scoped>
.compras-edit { background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%); min-height: 100vh; }
input[readonly] { background-color: #f9fafb !important; color: #6b7280 !important; cursor: not-allowed !important; border-color: #d1d5db !important; }
.status-indicator { display: inline-flex; align-items: center; gap: 0.375rem; padding: 0.25rem 0.625rem; font-size: 0.75rem; font-weight: 500; border-radius: 0.375rem; }
.status-readonly { background-color: #f3f4f6; color: #374151; }
.edit-mode-indicator { position: relative; }
.edit-mode-indicator::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px; background: linear-gradient(90deg, #3b82f6, #1d4ed8); border-radius: 0.375rem 0.375rem 0 0; }
@keyframes pulse-edit { 0%, 100% { opacity: 1 } 50% { opacity: 0.7 } }
.loading-edit { animation: pulse-edit 2s cubic-bezier(0.4, 0, 0.6, 1) infinite }
@media (max-width: 768px) { .compras-edit { padding: 1rem } .status-indicator { font-size: 0.6875rem; padding: 0.1875rem 0.5rem } }
</style>
