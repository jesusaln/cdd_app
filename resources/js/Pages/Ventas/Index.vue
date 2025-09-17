<!-- /resources/js/Pages/Ventas/Index.vue -->
<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { router, Head } from '@inertiajs/vue3'
import axios from 'axios'
import { Notyf } from 'notyf'
import 'notyf/notyf.min.css'
import { generarPDF } from '@/Utils/pdfGenerator'
import AppLayout from '@/Layouts/AppLayout.vue'
import UniversalHeader from '@/Components/IndexComponents/UniversalHeader.vue'
import DocumentosTable from '@/Components/IndexComponents/DocumentosTable.vue'
import Modal from '@/Components/IndexComponents/Modales.vue'

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
   Filtrado y ordenamiento
========================= */
const ventasFiltradas = computed(() => {
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

/* =========================
   Paginación
========================= */
const currentPage = ref(1)
const itemsPerPage = ref(10)

const totalPages = computed(() => {
  const filtered = ventasFiltradas.value
  return Math.ceil((filtered?.length || 0) / itemsPerPage.value)
})

const paginatedVentas = computed(() => {
  const filtered = ventasFiltradas.value
  if (!filtered || !Array.isArray(filtered)) return []

  const start = (currentPage.value - 1) * itemsPerPage.value
  const end = start + itemsPerPage.value
  return filtered.slice(start, end)
})

const visiblePages = computed(() => {
  const pages = []
  const total = totalPages.value
  const current = currentPage.value

  if (total <= 7) {
    for (let i = 1; i <= total; i++) {
      pages.push(i)
    }
  } else {
    if (current <= 3) {
      for (let i = 1; i <= 5; i++) {
        pages.push(i)
      }
    } else if (current >= total - 2) {
      for (let i = total - 4; i <= total; i++) {
        pages.push(i)
      }
    } else {
      for (let i = current - 2; i <= current + 2; i++) {
        pages.push(i)
      }
    }
  }

  return pages
})

const goToPage = (page) => {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page
  }
}

const nextPage = () => {
  if (currentPage.value < totalPages.value) {
    currentPage.value++
  }
}

const prevPage = () => {
  if (currentPage.value > 1) {
    currentPage.value--
  }
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
</script>

<template>
  <Head title="Ventas" />

  <div class="ventas-index min-h-screen bg-gray-50">
    <!-- Contenido principal -->
    <div class="max-w-8xl mx-auto px-6 py-8">
      <!-- Header de filtros y estadísticas -->
      <UniversalHeader
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
      />

      <!-- Información de paginación -->
      <div class="flex justify-between items-center mb-4 text-sm text-gray-600">
        <div>
          Mostrando {{ (currentPage - 1) * itemsPerPage + 1 }} -
          {{ Math.min(currentPage * itemsPerPage, ventasFiltradas.length) }}
          de {{ ventasFiltradas.length }} ventas
        </div>
        <div class="flex items-center space-x-2">
          <span>Elementos por página:</span>
          <select
            v-model="itemsPerPage"
            @change="currentPage = 1"
            class="border border-gray-300 rounded px-2 py-1 text-sm"
          >
            <option value="10">10</option>
            <option value="25">25</option>
            <option value="50">50</option>
            <option value="100">100</option>
          </select>
        </div>
      </div>

      <!-- Tabla de documentos -->
      <div class="mt-6">
        <DocumentosTable
          :documentos="paginatedVentas"
          tipo="ventas"
          :search-term="searchTerm"
          :sort-by="sortBy"
          :filtro-estado="filtroEstado"
          @ver-detalles="verDetalles"
          @editar="editarVenta"
          @duplicar="duplicarVenta"
          @imprimir="imprimirVenta"
          @eliminar="confirmarEliminacion"
          @sort="updateSort"
        />
      </div>

      <!-- Controles de paginación -->
      <div v-if="totalPages > 1" class="flex justify-center items-center space-x-2 mt-6">
        <button
          @click="prevPage"
          :disabled="currentPage === 1"
          class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          Anterior
        </button>

        <div class="flex space-x-1">
          <!-- Primera página (solo si no está en visiblePages) -->
          <template v-if="!visiblePages.includes(1) && totalPages > 7">
            <button
              @click="goToPage(1)"
              class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
            >
              1
            </button>
            <span class="px-3 py-2 text-sm text-gray-500">...</span>
          </template>

          <!-- Páginas visibles -->
          <button
            v-for="page in visiblePages"
            :key="page"
            @click="goToPage(page)"
            :class="[
              'px-3 py-2 text-sm font-medium border border-gray-300 rounded-md',
              page === currentPage
                ? 'bg-blue-500 text-white border-blue-500'
                : 'text-gray-700 bg-white hover:bg-gray-50'
            ]"
          >
            {{ page }}
          </button>

          <!-- Última página (solo si no está en visiblePages) -->
          <template v-if="!visiblePages.includes(totalPages) && totalPages > 7">
            <span class="px-3 py-2 text-sm text-gray-500">...</span>
            <button
              @click="goToPage(totalPages)"
              class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
            >
              {{ totalPages }}
            </button>
          </template>
        </div>

        <button
          @click="nextPage"
          :disabled="currentPage === totalPages"
          class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          Siguiente
        </button>
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
      @imprimir="imprimirFila"
      @editar="editarFila"
    />

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
