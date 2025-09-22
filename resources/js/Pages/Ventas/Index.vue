<!-- /resources/js/Pages/Ventas/Index.vue -->
<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { router, Head } from '@inertiajs/vue3'
import axios from 'axios'
import { Notyf } from 'notyf'
import 'notyf/notyf.min.css'
import { generarPDF } from '@/Utils/pdfGenerator'
import AppLayout from '@/Layouts/AppLayout.vue'
import VentasHeader from '@/Components/IndexComponents/VentasHeader.vue'
import VentasTable from '@/Components/IndexComponents/VentasTable.vue'
import Modal from '@/Components/IndexComponents/Modales.vue'
import Pagination from '@/Components/Pagination.vue'

defineOptions({ layout: AppLayout })

const props = defineProps({
  ventas: {
    type: Array,
    default: () => []
  },
  estadisticas: {
    type: Object,
    default: () => ({
      total: 0,
      borrador: 0,
      aprobadas: 0,
      pendientes: 0,
      cancelada: 0
    })
  }
})

/* =========================
   Configuración de notificaciones
========================= */
const notyf = new Notyf({
  duration: 4000,
  position: { x: 'right', y: 'top' },
  types: [
    { type: 'success', background: '#10b981', icon: false },
    { type: 'error', background: '#ef4444', icon: false },
    { type: 'warning', background: '#f59e0b', icon: false }
  ]
})

/* =========================
   Estado local y modal
========================= */
const showModal = ref(false)
const fila = ref(null)
const modalMode = ref('details')
const selectedId = ref(null)
const loading = ref(false)

// Modal de pago
const showPaymentModal = ref(false)
const selectedVenta = ref(null)
const metodoPago = ref('')
const notasPago = ref('')

const abrirDetalles = (row) => {
  fila.value = row || null
  modalMode.value = 'details'
  showModal.value = true
}

const cerrarModal = () => {
  showModal.value = false
  fila.value = null
  selectedId.value = null
}

/* =========================
   Filtros, orden y datos
========================= */
const searchTerm = ref('')
const sortBy = ref('fecha-desc')
const filtroEstado = ref('')
const ventasOriginales = ref([...props.ventas])

/* =========================
   Auditoría segura para el modal
========================= */
const auditoriaForModal = computed(() => {
  const r = fila.value
  if (!r) return null

  const meta = r.metadata || {}
  return {
    creado_por: r.creado_por_nombre || r.created_by_user_name || meta.creado_por || 'N/A',
    actualizado_por: r.actualizado_por_nombre || r.updated_by_user_name || meta.actualizado_por || 'N/A',
    eliminado_por: r.eliminado_por_nombre || r.deleted_by_user_name || meta.eliminado_por || null,
    creado_en: r.created_at || meta.creado_en || null,
    actualizado_en: r.updated_at || meta.actualizado_en || null,
    eliminado_en: r.deleted_at || meta.eliminado_en || null,
  }
})

/* =========================
    Datos para los componentes
 ========================= */
const documentosVentas = computed(() => {
  return [...ventasOriginales.value]
})

/* =========================
    Paginación del lado del cliente
========================= */
const currentPage = ref(1)
const perPage = ref(10)

// Ventas filtradas y ordenadas (sin paginación)
const ventasFiltradasYOrdenadas = computed(() => {
  let result = [...ventasOriginales.value]

  // Aplicar filtro de búsqueda
  if (searchTerm.value.trim()) {
    const search = searchTerm.value.toLowerCase().trim()
    result = result.filter(venta => {
      const cliente = venta.cliente?.nombre?.toLowerCase() || ''
      const numero = String(venta.numero_venta || venta.id || '').toLowerCase()
      const estado = venta.estado?.toLowerCase() || ''

      return cliente.includes(search) ||
             numero.includes(search) ||
             estado.includes(search)
    })
  }

  // Aplicar filtro de estado
  if (filtroEstado.value) {
    result = result.filter(venta => venta.estado === filtroEstado.value)
  }

  // Aplicar ordenamiento
  if (sortBy.value) {
    const [field, order] = sortBy.value.split('-')
    const isDesc = order === 'desc'

    result.sort((a, b) => {
      let valueA, valueB

      switch (field) {
        case 'fecha':
          valueA = new Date(a.fecha || a.created_at || 0)
          valueB = new Date(b.fecha || b.created_at || 0)
          break
        case 'cliente':
          valueA = a.cliente?.nombre || ''
          valueB = b.cliente?.nombre || ''
          break
        case 'total':
          valueA = parseFloat(a.total || 0)
          valueB = parseFloat(b.total || 0)
          break
        case 'estado':
          valueA = a.estado || ''
          valueB = b.estado || ''
          break
        default:
          valueA = a[field] || ''
          valueB = b[field] || ''
      }

      if (valueA < valueB) return isDesc ? 1 : -1
      if (valueA > valueB) return isDesc ? -1 : 1
      return 0
    })
  }

  return result
})

// Documentos para mostrar (con paginación del lado del cliente)
const documentosVentasPaginados = computed(() => {
  const startIndex = (currentPage.value - 1) * perPage.value
  const endIndex = startIndex + perPage.value
  return ventasFiltradasYOrdenadas.value.slice(startIndex, endIndex)
})

// Información de paginación
const totalPages = computed(() => Math.ceil(ventasFiltradasYOrdenadas.value.length / perPage.value))
const totalFiltered = computed(() => ventasFiltradasYOrdenadas.value.length)

// Datos de paginación simulados para el componente Pagination
const paginationData = computed(() => ({
  current_page: currentPage.value,
  last_page: totalPages.value,
  per_page: perPage.value,
  from: totalFiltered.value > 0 ? ((currentPage.value - 1) * perPage.value) + 1 : 0,
  to: Math.min(currentPage.value * perPage.value, totalFiltered.value),
  total: totalFiltered.value,
  prev_page_url: currentPage.value > 1 ? '#' : null,
  next_page_url: currentPage.value < totalPages.value ? '#' : null,
  links: [] // No necesitamos links para client-side
}))

// Watch para resetear página cuando cambien filtros
watch([searchTerm, sortBy, filtroEstado, perPage], () => {
  currentPage.value = 1
}, { deep: true })

// Manejo de paginación
const handlePerPageChange = (newPerPage) => {
  perPage.value = newPerPage
  currentPage.value = 1 // Reset to first page when changing per_page
}

const handlePageChange = (newPage) => {
  currentPage.value = newPage
}

// Watchers para props y filtros
watch(() => props.ventas, (newVal) => {
  if (Array.isArray(newVal)) {
    ventasOriginales.value = [...newVal]
  }
}, { deep: true, immediate: true })

// Watcher para estadísticas del backend
watch(() => props.estadisticas, (newVal) => {
  if (newVal && typeof newVal === 'object') {
    // Las estadísticas se actualizarán automáticamente en el computed
    console.log('Estadísticas del backend actualizadas:', newVal)
  }
}, { deep: true, immediate: true })

// Resetear página al cambiar filtros
watch([searchTerm, filtroEstado], () => {
  currentPage.value = 1
})

// Ajustar página si se queda sin elementos después de eliminar
watch(totalPages, (newTotal) => {
  if (currentPage.value > newTotal && newTotal > 0) {
    currentPage.value = newTotal
  }
})

// Estadísticas calculadas (usar backend si está disponible, sino calcular localmente)
const estadisticas = computed(() => {
  // Si tenemos estadísticas del backend, usarlas
  if (props.estadisticas && props.estadisticas.total > 0) {
    return {
      total: props.estadisticas.total || 0,
      borrador: props.estadisticas.borrador || 0,
      aprobados: props.estadisticas.aprobadas || 0,
      pendientes: props.estadisticas.pendientes || 0,
      cancelado: props.estadisticas.cancelada || 0,
    };
  }

  // Calcular localmente como fallback
  const stats = {
    total: ventasOriginales.value.length,
    aprobados: 0,
    pendientes: 0,
    borrador: 0,
    cancelado: 0,
  };

  ventasOriginales.value.forEach(v => {
    switch (String(v.estado || '').toLowerCase()) {
      case 'aprobado':
      case 'aprobada':
        stats.aprobados++; break;
      case 'pendiente':
        stats.pendientes++; break;
      case 'borrador':
        stats.borrador++; break;
      case 'cancelado':
      case 'cancelada':
        stats.cancelado++; break;
    }
  });

  return stats;
});

const handleLimpiarFiltros = () => {
  searchTerm.value = ''
  sortBy.value = 'fecha-desc'
  filtroEstado.value = ''
  perPage.value = 10
  currentPage.value = 1
  notyf.success('Filtros limpiados correctamente')
}

const updateSort = (newSort) => {
  if (newSort && typeof newSort === 'string') {
    sortBy.value = newSort
    currentPage.value = 1 // Resetear página al cambiar ordenamiento
  }
}

/* =========================
    Helpers
========================= */
const formatearMoneda = (num) => {
  const value = parseFloat(num)
  const safe = Number.isFinite(value) ? value : 0
  return new Intl.NumberFormat('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(safe)
}

/* =========================
   Validaciones y utilidades
========================= */
function validarVenta(venta) {
  if (!venta?.id) {
    throw new Error('ID de venta no válido')
  }
  return true
}

function validarVentaParaPDF(doc) {
  if (!doc.id) throw new Error('ID del documento no encontrado')
  if (!doc.cliente?.nombre) throw new Error('Datos del cliente no encontrados')
  if (!Array.isArray(doc.productos) || !doc.productos.length) {
    throw new Error('Lista de productos no válida')
  }
  if (!doc.fecha) throw new Error('Fecha no especificada')
  return true
}

/* =========================
   Acciones CRUD
========================= */
const verDetalles = (venta) => {
  try {
    validarVenta(venta)
    abrirDetalles(venta)
  } catch (error) {
    notyf.error(error.message)
  }
}

const editarVenta = (id) => {
  try {
    const ventaId = id || fila.value?.id
    if (!ventaId) throw new Error('ID de venta no válido')

    router.visit(`/ventas/${ventaId}/edit`)
  } catch (error) {
    notyf.error(error.message)
  }
}

const editarFila = (id) => {
  editarVenta(id)
}

const duplicarVenta = async (venta) => {
  try {
    validarVenta(venta)

    if (!confirm(`¿Duplicar venta #${venta.numero_venta || venta.id}?`)) {
      return
    }

    loading.value = true
    notyf.success('Duplicando venta...')

    const { data } = await axios.post(`/ventas/${venta.id}/duplicate`)

    if (data?.success) {
      notyf.success(data.message || 'Venta duplicada exitosamente')

      // Recargar la página para mostrar la venta duplicada
      router.visit('/ventas', {
        method: 'get',
        replace: true
      })
    } else {
      throw new Error(data?.error || 'Error al duplicar la venta')
    }

  } catch (error) {
    console.error('Error al duplicar:', error)
    let mensaje = 'Error al duplicar la venta'
    if (error.response?.data?.error) mensaje = error.response.data.error
    else if (error.response?.data?.message) mensaje = error.response.data.message
    else if (error.message) mensaje = error.message

    notyf.error(mensaje)
  } finally {
    loading.value = false
  }
}

const imprimirVenta = async (venta) => {
  try {
    const doc = {
      ...venta,
      fecha: venta.fecha || venta.created_at || new Date().toISOString()
    }

    validarVentaParaPDF(doc)

    loading.value = true
    notyf.success('Generando PDF...')

    await generarPDF('Venta', doc)
    notyf.success('PDF generado correctamente')

  } catch (error) {
    console.error('Error al generar PDF:', error)
    notyf.error(`Error al generar el PDF: ${error.message}`)
  } finally {
    loading.value = false
  }
}

const imprimirFila = () => {
  if (fila.value) {
    imprimirVenta(fila.value)
  }
}

const confirmarEliminacion = (id) => {
  try {
    if (!id) throw new Error('ID de venta no válido')

    selectedId.value = id
    modalMode.value = 'confirm'
    showModal.value = true
  } catch (error) {
    notyf.error(error.message)
  }
}

const eliminarVenta = async () => {
  try {
    if (!selectedId.value) throw new Error('No se seleccionó ninguna venta para cancelar')

    loading.value = true
    notyf.success('Cancelando venta...')

    const { data } = await axios.post(`/ventas/${selectedId.value}/cancel`)

    if (data?.success) {
      notyf.success(data.message || 'Venta cancelada exitosamente')

      // Actualizar datos locales - marcar como cancelada en lugar de eliminar
      const index = ventasOriginales.value.findIndex(v => v.id === selectedId.value)
      if (index !== -1) {
        ventasOriginales.value[index] = {
          ...ventasOriginales.value[index],
          estado: 'cancelada',
          eliminado_por: data?.eliminado_por || 'Usuario actual',
          deleted_at: new Date().toISOString()
        }
      }

      cerrarModal()
    } else {
      throw new Error(data?.error || 'Error al cancelar la venta')
    }

  } catch (error) {
    console.error('Error al cancelar:', error)
    let mensaje = 'Error al cancelar la venta'
    if (error.response?.data?.error) mensaje = error.response.data.error
    else if (error.response?.data?.message) mensaje = error.response.data.message
    else if (error.message) mensaje = error.message

    notyf.error(mensaje)
  } finally {
    loading.value = false
  }
}

const crearNuevaVenta = () => {
  router.visit('/ventas/create')
}

const marcarComoPagado = (venta) => {
  selectedVenta.value = venta
  metodoPago.value = ''
  notasPago.value = ''
  showPaymentModal.value = true
}

const cerrarPaymentModal = () => {
  showPaymentModal.value = false
  selectedVenta.value = null
  metodoPago.value = ''
  notasPago.value = ''
}

const confirmarPago = async () => {
  if (!metodoPago.value) {
    notyf.error('Debe seleccionar un método de pago')
    return
  }

  try {
    loading.value = true
    notyf.success('Procesando pago...')

    const { data } = await axios.post(`/ventas/${selectedVenta.value.id}/marcar-pagado`, {
      metodo_pago: metodoPago.value,
      notas_pago: notasPago.value
    })

    if (data?.success) {
      notyf.success(data.message || 'Venta marcada como pagada exitosamente')

      // Actualizar datos locales
      const index = ventasOriginales.value.findIndex(v => v.id === selectedVenta.value.id)
      if (index !== -1) {
        ventasOriginales.value[index] = {
          ...ventasOriginales.value[index],
          estado: 'aprobada',
          pagado: true,
          metodo_pago: metodoPago.value,
          fecha_pago: new Date().toISOString().split('T')[0],
          notas_pago: notasPago.value
        }
      }

      cerrarPaymentModal()

      // Recargar la página para actualizar estadísticas y datos del backend
      router.visit('/ventas', {
        method: 'get',
        replace: true
      })
    } else {
      throw new Error(data?.error || 'Error al procesar el pago')
    }

  } catch (error) {
    console.error('Error al marcar como pagado:', error)
    let mensaje = 'Error al procesar el pago'
    if (error.response?.data?.error) mensaje = error.response.data.error
    else if (error.response?.data?.message) mensaje = error.response.data.message
    else if (error.message) mensaje = error.message

    notyf.error(mensaje)
  } finally {
    loading.value = false
  }
}

const cancelarVenta = async (id) => {
  try {
    if (!confirm('¿Está seguro de que desea cancelar esta venta? Esta acción devolverá el pago y el inventario.')) {
      return
    }

    loading.value = true
    notyf.success('Cancelando venta...')

    const { data } = await axios.post(`/ventas/${id}/cancel`)

    if (data?.success) {
      notyf.success(data.message || 'Venta cancelada exitosamente')

      // Recargar la página para actualizar estadísticas y datos del backend
      router.visit('/ventas', {
        method: 'get',
        replace: true
      })
    } else {
      throw new Error(data?.error || 'Error al cancelar la venta')
    }

  } catch (error) {
    console.error('Error al cancelar venta:', error)
    let mensaje = 'Error al cancelar la venta'
    if (error.response?.data?.error) mensaje = error.response.data.error
    else if (error.response?.data?.message) mensaje = error.response.data.message
    else if (error.message) mensaje = error.message

    notyf.error(mensaje)
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <Head title="Ventas" />

  <div class="ventas-index min-h-screen bg-gray-50">
    <!-- Contenido principal -->
    <div class="max-w-8xl mx-auto px-6 py-8">
      <!-- Header específico de ventas -->
      <VentasHeader
        :total="estadisticas.total"
        :borrador="estadisticas.borrador"
        :aprobadas="estadisticas.aprobados"
        :pendientes="estadisticas.pendientes"
        :cancelada="estadisticas.cancelado"
        v-model:search-term="searchTerm"
        v-model:sort-by="sortBy"
        v-model:filtro-estado="filtroEstado"
        :config="{
          module: 'ventas',
          createButtonText: 'Nueva Venta',
          searchPlaceholder: 'Buscar por cliente, número...'
        }"
        @limpiar-filtros="handleLimpiarFiltros"
        @crear-nuevo="crearNuevaVenta"
      />

      <!-- Tabla específica de ventas -->
      <div class="mt-6">
        <VentasTable
          :documentos="documentosVentasPaginados"
          :search-term="searchTerm"
          :sort-by="sortBy"
          @ver-detalles="verDetalles"
          @editar="editarVenta"
          @eliminar="confirmarEliminacion"
          @marcar-pagado="marcarComoPagado"
          @cancelar="cancelarVenta"
          @imprimir="imprimirVenta"
          @sort="updateSort"
        />

        <!-- Componente de paginación -->
        <Pagination
          :pagination-data="paginationData"
          @per-page-change="handlePerPageChange"
          @page-change="handlePageChange"
        />
      </div>

    </div>

    <!-- Modal de detalles / confirmación -->
    <Modal
      :show="showModal"
      :mode="modalMode"
      tipo="ventas"
      :selected="fila || {}"
      :auditoria="auditoriaForModal"
      @close="cerrarModal"
      @confirm-delete="eliminarVenta"
      @editar="editarFila"
    />

    <!-- Modal de Pago -->
    <div v-if="showPaymentModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" @click.self="cerrarPaymentModal">
      <div class="bg-white rounded-lg shadow-xl w-full max-w-md max-h-[90vh] overflow-y-auto">
        <!-- Header del modal -->
        <div class="flex items-center justify-between p-6 border-b border-gray-200">
          <h3 class="text-lg font-medium text-gray-900">Marcar como Pagado</h3>
          <button @click="cerrarPaymentModal" class="text-gray-400 hover:text-gray-600 transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <div class="p-6">
          <div v-if="selectedVenta" class="space-y-4">
            <!-- Información de la venta -->
            <div class="bg-gray-50 p-4 rounded-lg">
              <div class="flex justify-between items-center">
                <span class="text-sm font-medium text-gray-700">Venta:</span>
                <span class="text-sm font-mono text-gray-900">{{ selectedVenta.numero_venta }}</span>
              </div>
              <div class="flex justify-between items-center mt-2">
                <span class="text-sm font-medium text-gray-700">Total:</span>
                <span class="text-lg font-bold text-gray-900">${{ formatearMoneda(selectedVenta.total) }}</span>
              </div>
            </div>

            <!-- Método de pago -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Método de Pago *</label>
              <select
                v-model="metodoPago"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              >
                <option value="">Seleccionar método...</option>
                <option value="efectivo">Efectivo</option>
                <option value="transferencia">Transferencia</option>
                <option value="cheque">Cheque</option>
                <option value="tarjeta">Tarjeta</option>
                <option value="otros">Otros</option>
              </select>
            </div>

            <!-- Notas del pago -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Notas (opcional)</label>
              <textarea
                v-model="notasPago"
                rows="3"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="Agregar notas sobre el pago..."
              ></textarea>
            </div>
          </div>
        </div>

        <!-- Footer del modal -->
        <div class="flex justify-end gap-3 px-6 py-4 border-t border-gray-200 bg-gray-50">
          <button @click="cerrarPaymentModal" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors">
            Cancelar
          </button>
          <button
            @click="confirmarPago"
            :disabled="!metodoPago || loading"
            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
          >
            <span v-if="loading" class="flex items-center">
              <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Procesando...
            </span>
            <span v-else>Confirmar Pago</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Loading overlay -->
    <div v-if="loading" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white p-6 rounded-lg shadow-lg">
        <div class="flex items-center space-x-3">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
          <span class="text-gray-700">Procesando...</span>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.ventas-index {
  min-height: 100vh;
  background-color: #f9fafb;
}

/* Responsive */
@media (max-width: 640px) {
  .ventas-index .max-w-7xl {
    padding-left: 1rem;
    padding-right: 1rem;
  }

  .ventas-index h1 {
    font-size: 1.5rem;
  }
}

/* Animaciones suaves */
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

.ventas-index > * {
  animation: fadeIn 0.3s ease-out;
}
</style>
