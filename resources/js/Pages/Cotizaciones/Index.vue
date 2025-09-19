<!-- /resources/js/Pages/Cotizaciones/Index.vue -->
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
import Pagination from '@/Components/Pagination.vue'

defineOptions({ layout: AppLayout })

const props = defineProps({
  cotizaciones: {
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
const cotizacionesOriginales = ref([...props.cotizaciones])

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
const cotizacionesFiltradasYOrdenadas = computed(() => {
  let result = [...cotizacionesOriginales.value]

  // Aplicar filtro de búsqueda
  if (searchTerm.value.trim()) {
    const search = searchTerm.value.toLowerCase().trim()
    result = result.filter(cotizacion => {
      const cliente = cotizacion.cliente?.nombre?.toLowerCase() || ''
      const numero = String(cotizacion.numero_cotizacion || cotizacion.id || '').toLowerCase()
      const estado = cotizacion.estado?.toLowerCase() || ''

      return cliente.includes(search) ||
             numero.includes(search) ||
             estado.includes(search)
    })
  }

  // Aplicar filtro de estado
  if (filtroEstado.value) {
    result = result.filter(cotizacion => cotizacion.estado === filtroEstado.value)
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
    Paginación del lado del cliente
========================= */
const currentPage = ref(1)
const perPage = ref(10)

// Documentos para mostrar (con paginación del lado del cliente)
const documentosCotizaciones = computed(() => {
  const startIndex = (currentPage.value - 1) * perPage.value
  const endIndex = startIndex + perPage.value
  return cotizacionesFiltradasYOrdenadas.value.slice(startIndex, endIndex)
})

// Información de paginación
const totalPages = computed(() => Math.ceil(cotizacionesFiltradasYOrdenadas.value.length / perPage.value))
const totalFiltered = computed(() => cotizacionesFiltradasYOrdenadas.value.length)

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
watch(() => props.cotizaciones, (newVal) => {
  if (Array.isArray(newVal)) {
    cotizacionesOriginales.value = [...newVal]
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
    total: cotizacionesOriginales.value.length,
    aprobadas: 0,
    pendientes: 0,
    borrador: 0,
    enviado_pedido: 0,
    cancelado: 0,
  };

  cotizacionesOriginales.value.forEach(c => {
    switch (String(c.estado || '').toLowerCase()) {
      case 'aprobado':
      case 'aprobada':
        stats.aprobadas++; break;
      case 'pendiente':
        stats.pendientes++; break;
      case 'borrador':
        stats.borrador++; break;
      case 'enviado_pedido':
        stats.enviado_pedido++; break;
      case 'cancelado':
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
   Validaciones y utilidades
========================= */
function puedeEnviarAPedido(cotizacion) {
  if (!cotizacion) return false
  return cotizacion.estado === 'aprobado' || cotizacion.estado === 'aprobada'
}

function validarCotizacion(cotizacion) {
  if (!cotizacion?.id) {
    throw new Error('ID de cotización no válido')
  }
  return true
}

function validarCotizacionBasica(cotizacion) {
  if (!cotizacion?.id) {
    throw new Error('ID de cotización no válido')
  }
  if (!cotizacion.cliente?.nombre) {
    throw new Error('Datos del cliente no encontrados')
  }
  if (!Array.isArray(cotizacion.productos) || !cotizacion.productos.length) {
    throw new Error('Lista de productos no válida')
  }
  if (!cotizacion.fecha && !cotizacion.created_at) {
    throw new Error('Fecha no especificada')
  }
  return true
}

function validarCotizacionParaPDF(doc) {
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
const verDetalles = (cotizacion) => {
  try {
    validarCotizacion(cotizacion)
    abrirDetalles(cotizacion)
  } catch (error) {
    notyf.error(error.message)
  }
}

const editarCotizacion = (id) => {
  try {
    const cotizacionId = id || fila.value?.id
    if (!cotizacionId) throw new Error('ID de cotización no válido')

    router.visit(`/cotizaciones/${cotizacionId}/edit`)
  } catch (error) {
    notyf.error(error.message)
  }
}

const editarFila = (id) => {
  editarCotizacion(id)
}

const duplicarCotizacion = async (cotizacion) => {
  try {
    validarCotizacion(cotizacion)

    // Usar modal de confirmación personalizado
    fila.value = cotizacion
    modalMode.value = 'confirm-duplicate'
    showModal.value = true
  } catch (error) {
    notyf.error(error.message)
  }
}

const confirmarDuplicarCotizacion = async () => {
  try {
    const cotizacion = fila.value
    if (!cotizacion?.id) throw new Error('Cotización no válida')

    loading.value = true
    cerrarModal()

    router.post(`/cotizaciones/${cotizacion.id}/duplicate`, {}, {
      onStart: () => {
        notyf.success('Duplicando cotización...')
      },
      onSuccess: () => {
        notyf.success('Cotización duplicada exitosamente')
      },
      onError: (errors) => {
        console.error('Error al duplicar:', errors)
        notyf.error('Error al duplicar la cotización')
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

const imprimirCotizacion = async (cotizacion) => {
  try {
    const doc = {
      ...cotizacion,
      fecha: cotizacion.fecha || cotizacion.created_at || new Date().toISOString()
    }

    validarCotizacionParaPDF(doc)

    loading.value = true
    notyf.success('Generando PDF...')

    await generarPDF('Cotización', doc)
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
    imprimirCotizacion(fila.value)
  }
}

const confirmarEliminacion = (id) => {
  try {
    if (!id) throw new Error('ID de cotización no válido')

    selectedId.value = id
    modalMode.value = 'confirm'
    showModal.value = true
  } catch (error) {
    notyf.error(error.message)
  }
}

const eliminarCotizacion = async () => {
  try {
    if (!selectedId.value) throw new Error('No se seleccionó ninguna cotización')

    loading.value = true

    router.post(`/cotizaciones/${selectedId.value}/cancel`, {}, {
      onStart: () => {
        notyf.success('Cancelando cotización...')
      },
      onSuccess: (response) => {
        notyf.success('Cotización cancelada exitosamente')

        // Actualizar datos locales - marcar como cancelada en lugar de eliminar
        const index = cotizacionesOriginales.value.findIndex(c => c.id === selectedId.value)
        if (index !== -1) {
          cotizacionesOriginales.value[index] = {
            ...cotizacionesOriginales.value[index],
            estado: 'cancelado',
            eliminado_por: response?.data?.eliminado_por || 'Usuario actual',
            deleted_at: new Date().toISOString()
          }
        }

        cerrarModal()
      },
      onError: (errors) => {
        console.error('Error al cancelar:', errors)
        notyf.error('Error al cancelar la cotización')
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

// Pon este ref junto a tus otros estados
const isSendingPedido = ref(false)

/**
 * Enviar una cotización a Pedido
 * @param {Object} cotizacionData - La cotización (opcional; si no viene, toma `fila.value`)
 * @param {Object} options
 * @param {'index'|'show'|'edit'|null} options.redirectTo - A dónde navegar tras crear el pedido.
 *    'show' o 'edit' requieren que el backend devuelva `pedido_id`. Si es null, no redirige.
 *    Default: 'show' si hay `pedido_id`, de lo contrario 'index'.
 */
const enviarAPedido = async (cotizacionData, { redirectTo = 'index' } = {}) => {
  try {
    const docRaw = cotizacionData?.id ? cotizacionData : fila.value
    validarCotizacionBasica(docRaw)

    const doc = { ...docRaw, fecha: docRaw.fecha || docRaw.created_at || new Date().toISOString() }

    loading.value = true
    notyf.success('Enviando cotización a pedido...')

    const { data } = await axios.post(`/cotizaciones/${doc.id}/enviar-pedido`, {
      forzarReenvio: !!cotizacionData?.forzarReenvio
    })

    if (!data?.success) throw new Error(data?.error || 'No se pudo enviar a pedido')

    // Actualiza estado local
    const i = cotizacionesOriginales.value.findIndex(c => c.id === doc.id)
    if (i !== -1) cotizacionesOriginales.value[i] = { ...cotizacionesOriginales.value[i], estado: 'enviado_pedido' }

    cerrarModal()
    notyf.success(data.message || 'Cotización enviada a pedido exitosamente')

    // 🔁 Ir siempre al index de pedidos (usa el helper de rutas si lo tienes)
    router.visit(route ? route('pedidos.index') : '/pedidos')

  } catch (err) {
    console.error(err)
    notyf.error(err.response?.data?.error || err.response?.data?.message || err.message || 'Error al enviar a pedido')
  } finally {
    loading.value = false
  }
}




const enviarAVenta = () => {
  notyf.warning('Esta acción no está disponible desde Cotizaciones.')
}

const crearNuevaCotizacion = () => {
  router.visit('/cotizaciones/create')
}
</script>

<template>
  <Head title="Cotizaciones" />

  <div class="cotizaciones-index min-h-screen bg-gray-50">
    <!-- Contenido principal -->
    <div class="max-w-8xl mx-auto px-6 py-8">
      <!-- Header de filtros y estadísticas -->
      <UniversalHeader
        :total="estadisticas.total"
        :aprobadas="estadisticas.aprobadas"
        :pendientes="estadisticas.pendientes"
        :borrador="estadisticas.borrador"
        :enviado_pedido="estadisticas.enviado_pedido"
        :cancelado="estadisticas.cancelado"
        v-model:search-term="searchTerm"
        v-model:sort-by="sortBy"
        v-model:filtro-estado="filtroEstado"
        :config="{
          module: 'cotizaciones',
          createButtonText: 'Nueva Cotización',
          searchPlaceholder: 'Buscar por cliente, número...'
        }"
        @limpiar-filtros="handleLimpiarFiltros"
      />

      <!-- Tabla de documentos -->
      <div class="mt-6">
        <DocumentosTable
          :documentos="documentosCotizaciones"
          tipo="cotizaciones"
          :search-term="searchTerm"
          :sort-by="sortBy"
          :filtro-estado="filtroEstado"
          @ver-detalles="verDetalles"
          @editar="editarCotizacion"
          @duplicar="duplicarCotizacion"
          @imprimir="imprimirCotizacion"
          @eliminar="confirmarEliminacion"
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
      tipo="cotizaciones"
      :selected="fila || {}"
      :auditoria="auditoriaForModal"
      @close="cerrarModal"
      @confirm-delete="eliminarCotizacion"
      @confirm-duplicate="confirmarDuplicarCotizacion"
      @imprimir="imprimirFila"
      @editar="editarFila"
      @enviar-pedido="enviarAPedido"
      @enviar-a-venta="enviarAVenta"
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
.cotizaciones-index {
  min-height: 100vh;
  background-color: #f9fafb;
}

@media (max-width: 640px) {
  .cotizaciones-index .max-w-8xl {
    padding-left: 1rem;
    padding-right: 1rem;
  }
  .cotizaciones-index h1 {
    font-size: 1.5rem;
  }
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.cotizaciones-index > * {
  animation: fadeIn 0.3s ease-out;
}

/* Estilos adicionales para la paginación */
.pagination-info {
  font-size: 0.875rem;
  color: #4b5563;
}

.pagination-controls button:focus {
  outline: none;
  /* Approximate Tailwind's ring-2 + ring-blue-500 + ring-offset-2 */
  box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.18);
}

.loading-overlay {
  backdrop-filter: blur(2px);
}
</style>
