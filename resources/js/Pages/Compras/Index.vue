<!-- /resources/js/Pages/Compras/Index.vue -->
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
  compras: {
    type: Array,
    default: () => []
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

/* =========================
   Filtros, orden y datos
========================= */
const searchTerm = ref('')
const sortBy = ref('fecha-desc')
const filtroEstado = ref('')
const comprasOriginales = ref([...props.compras])

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
const comprasFiltradas = computed(() => {
  let result = [...comprasOriginales.value]

  // Aplicar filtro de búsqueda
  if (searchTerm.value.trim()) {
    const search = searchTerm.value.toLowerCase().trim()
    result = result.filter(compra => {
      const proveedor = compra.proveedor?.nombre_razon_social?.toLowerCase() || ''
      const numero = String(compra.numero_compra || compra.id || '').toLowerCase()
      const estado = compra.estado?.toLowerCase() || ''

      return proveedor.includes(search) ||
             numero.includes(search) ||
             estado.includes(search)
    })
  }

  // Aplicar filtro de estado
  if (filtroEstado.value) {
    result = result.filter(compra => compra.estado === filtroEstado.value)
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
        case 'proveedor':
          valueA = a.proveedor?.nombre_razon_social || ''
          valueB = b.proveedor?.nombre_razon_social || ''
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
  const filtered = comprasFiltradas.value
  return Math.ceil((filtered?.length || 0) / itemsPerPage.value)
})

const paginatedCompras = computed(() => {
  const filtered = comprasFiltradas.value
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
watch(() => props.compras, (newVal) => {
  if (Array.isArray(newVal)) {
    comprasOriginales.value = [...newVal]
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

// Estadísticas calculadas
const estadisticas = computed(() => {
  const stats = {
    total: comprasOriginales.value.length,
    recibidas: 0,
    pendientes: 0,
    borrador: 0,
    aprobadas: 0,
    canceladas: 0,
  }

  comprasOriginales.value.forEach(c => {
    switch (String(c.estado || '').toLowerCase()) {
      case 'recibida':
      case 'recibido':
        stats.recibidas++
        break
      case 'pendiente':
        stats.pendientes++
        break
      case 'borrador':
        stats.borrador++
        break
      case 'aprobada':
      case 'aprobado':
        stats.aprobadas++
        break
      case 'cancelada':
      case 'cancelado':
        stats.canceladas++
        break
    }
  })

  return stats
})

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
    currentPage.value = 1
  }
}

/* =========================
   Validaciones y utilidades
========================= */
function puedeRecibirCompra(compra) {
  if (!compra) return false
  return compra.estado === 'aprobada' || compra.estado === 'aprobado'
}

function validarCompra(compra) {
  if (!compra?.id) {
    throw new Error('ID de compra no válido')
  }
  return true
}

function validarCompraBasica(compra) {
  if (!compra?.id) {
    throw new Error('ID de compra no válido')
  }
  if (!compra.proveedor?.nombre_razon_social) {
    throw new Error('Datos del proveedor no encontrados')
  }
  if (!Array.isArray(compra.productos) || !compra.productos.length) {
    throw new Error('Lista de productos no válida')
  }
  if (!compra.fecha && !compra.created_at) {
    throw new Error('Fecha no especificada')
  }
  return true
}

function validarCompraParaPDF(doc) {
  if (!doc.id) throw new Error('ID del documento no encontrado')
  if (!doc.cliente?.nombre_razon_social) throw new Error('Datos del cliente no encontrados')
  if (!Array.isArray(doc.productos) || !doc.productos.length) {
    throw new Error('Lista de productos no válida')
  }
  if (!doc.fecha) throw new Error('Fecha no especificada')
  return true
}

/* =========================
   Acciones CRUD
========================= */
const verDetalles = (compra) => {
  try {
    validarCompra(compra)
    fila.value = compra
    modalMode.value = 'details'
    showModal.value = true
  } catch (error) {
    notyf.error(error.message)
  }
}

const editarCompra = (id) => {
  try {
    const compraId = id || fila.value?.id
    if (!compraId) throw new Error('ID de compra no válido')

    router.visit(`/compras/${compraId}/edit`)
  } catch (error) {
    notyf.error(error.message)
  }
}

const editarFila = (id) => {
  editarCompra(id)
}

const duplicarCompra = async (compra) => {
  try {
    validarCompra(compra)

    if (!confirm(`¿Duplicar compra #${compra.numero_compra || compra.id}?`)) {
      return
    }

    loading.value = true

    router.post(`/compras/${compra.id}/duplicate`, {}, {
      onStart: () => {
        notyf.success('Duplicando compra...')
      },
      onSuccess: () => {
        notyf.success('Compra duplicada exitosamente')
      },
      onError: (errors) => {
        console.error('Error al duplicar:', errors)
        notyf.error('Error al duplicar la compra')
      },
      onFinish: () => {
        loading.value = false
      }
    })
  } catch (error) {
    notyf.error(error.message)
    loading.value = false
  }
}

const imprimirCompra = async (compra) => {
  try {
    const doc = {
      ...compra,
      cliente: compra.proveedor, // Mapear proveedor a cliente para el PDF
      productos: compra.productos,   // Mapear productos para el PDF
      fecha: compra.fecha || compra.created_at || new Date().toISOString()
    }

    validarCompraParaPDF(doc)

    loading.value = true
    notyf.success('Generando PDF...')

    await generarPDF('Compra', doc)
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
    imprimirCompra(fila.value)
  }
}

const confirmarEliminacion = (id) => {
  try {
    if (!id) throw new Error('ID de compra no válido')

    selectedId.value = id
    modalMode.value = 'confirm'
    showModal.value = true
  } catch (error) {
    notyf.error(error.message)
  }
}

const eliminarCompra = async () => {
  try {
    if (!selectedId.value) throw new Error('No se seleccionó ninguna compra')

    loading.value = true

    router.post(`/compras/${selectedId.value}/cancel`, {}, {
      onStart: () => {
        notyf.success('Cancelando compra...')
      },
      onSuccess: (response) => {
        notyf.success('Compra cancelada exitosamente')

        // Actualizar datos locales
        const index = comprasOriginales.value.findIndex(c => c.id === selectedId.value)
        if (index !== -1) {
          comprasOriginales.value[index] = {
            ...comprasOriginales.value[index],
            estado: 'cancelado',
            eliminado_por: response?.data?.eliminado_por || 'Usuario actual',
            deleted_at: new Date().toISOString()
          }
        }

        showModal.value = false
        selectedId.value = null
      },
      onError: (errors) => {
        console.error('Error al cancelar:', errors)
        notyf.error('Error al cancelar la compra')
      },
      onFinish: () => {
        loading.value = false
      }
    })
  } catch (error) {
    notyf.error(error.message)
    loading.value = false
  }
}

// Estado para recibir compra
const isReceivingCompra = ref(false)

/**
 * Recibir una compra
 * @param {Object} compraData - La compra (opcional; si no viene, toma `fila.value`)
 */
const recibirCompra = async (compraData, { redirectTo = 'index' } = {}) => {
  try {
    const docRaw = compraData?.id ? compraData : fila.value
    validarCompraBasica(docRaw)

    const doc = { ...docRaw, fecha: docRaw.fecha || docRaw.created_at || new Date().toISOString() }

    loading.value = true
    notyf.success('Recibiendo compra...')

    const { data } = await axios.post(`/compras/${doc.id}/recibir`, {
      forzarReenvio: !!compraData?.forzarReenvio
    })

    if (!data?.success) throw new Error(data?.error || 'No se pudo recibir la compra')

    // Actualiza estado local
    const i = comprasOriginales.value.findIndex(c => c.id === doc.id)
    if (i !== -1) comprasOriginales.value[i] = { ...comprasOriginales.value[i], estado: 'recibida' }

    showModal.value = false
    notyf.success(data.message || 'Compra recibida exitosamente')

    // Ir al index de compras
    router.visit(route ? route('compras.index') : '/compras')

  } catch (err) {
    console.error(err)
    notyf.error(err.response?.data?.error || err.response?.data?.message || err.message || 'Error al recibir compra')
  } finally {
    loading.value = false
  }
}

const crearNuevaCompra = () => {
  router.visit('/compras/create')
}
</script>

<template>
  <Head title="Compras" />

  <div class="compras-index min-h-screen bg-gray-50">
    <!-- Contenido principal -->
    <div class="max-w-8xl mx-auto px-6 py-8">
      <!-- Header de filtros y estadísticas -->
      <UniversalHeader
        :total="estadisticas.total"
        :recibidas="estadisticas.recibidas"
        :aprobadas="estadisticas.aprobadas"
        :pendientes="estadisticas.pendientes"
        :borrador="estadisticas.borrador"
        :canceladas="estadisticas.canceladas"
        v-model:search-term="searchTerm"
        v-model:sort-by="sortBy"
        v-model:filtro-estado="filtroEstado"
        :config="{
          module: 'compras',
          createButtonText: 'Nueva Compra',
          searchPlaceholder: 'Buscar por proveedor, número...'
        }"
        @limpiar-filtros="handleLimpiarFiltros"
      />

      <!-- Información de paginación -->
      <div class="flex justify-between items-center mb-4 text-sm text-gray-600">
        <div>
          Mostrando {{ (currentPage - 1) * itemsPerPage + 1 }} -
          {{ Math.min(currentPage * itemsPerPage, comprasFiltradas.length) }}
          de {{ comprasFiltradas.length }} compras
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
          :documentos="paginatedCompras"
          tipo="compras"
          :search-term="searchTerm"
          :sort-by="sortBy"
          :filtro-estado="filtroEstado"
          @ver-detalles="verDetalles"
          @editar="editarCompra"
          @duplicar="duplicarCompra"
          @imprimir="imprimirCompra"
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
      tipo="compras"
      :selected="fila || {}"
      :auditoria="auditoriaForModal"
      @close="() => { showModal = false; fila = null; selectedId = null }"
      @confirm-delete="eliminarCompra"
      @imprimir="imprimirFila"
      @editar="editarFila"
      @recibir-compra="recibirCompra"
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
.compras-index {
  min-height: 100vh;
  background-color: #f9fafb;
}

@media (max-width: 640px) {
  .compras-index .max-w-7xl {
    padding-left: 1rem;
    padding-right: 1rem;
  }

  .compras-index h1 {
    font-size: 1.5rem;
  }
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

.compras-index > * {
  animation: fadeIn 0.3s ease-out;
}
</style>
