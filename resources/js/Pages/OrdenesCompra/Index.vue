<!-- /resources/js/Pages/OrdenesCompra/Index.vue -->
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
  ordenesCompra: {
    type: Object,
    default: () => ({ data: [] })
  },
  pagination: {
    type: Object,
    default: () => ({})
  },
  stats: {
    type: Object,
    default: () => ({})
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
const ordenesOriginales = ref([...(props.ordenesCompra?.data || [])])

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
    Paginación del servidor
========================= */
const visiblePages = computed(() => {
  const pages = []
  const total = props.pagination.last_page || 1
  const current = props.pagination.current_page || 1

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
  const query = {
    page,
    search: searchTerm.value,
    estado: filtroEstado.value,
    sort_by: sortBy.value.split('-')[0],
    sort_direction: sortBy.value.split('-')[1] || 'desc'
  }
  router.visit('/ordenescompra', { data: query })
}

const nextPage = () => {
  if (props.pagination.current_page < props.pagination.last_page) {
    goToPage(props.pagination.current_page + 1)
  }
}

const prevPage = () => {
  if (props.pagination.current_page > 1) {
    goToPage(props.pagination.current_page - 1)
  }
}

// Watchers para actualizar datos locales
watch(() => props.ordenesCompra, (newVal) => {
  if (newVal && newVal.data && Array.isArray(newVal.data)) {
    ordenesOriginales.value = [...newVal.data]
  }
}, { deep: true, immediate: true })

// Estadísticas del servidor
const estadisticas = computed(() => {
  return props.stats || {
    total: 0,
    pendientes: 0,
    enviadas: 0,
    procesadas: 0,
    canceladas: 0,
  }
})

const handleLimpiarFiltros = () => {
  searchTerm.value = ''
  sortBy.value = 'fecha-desc'
  filtroEstado.value = ''
  router.visit('/ordenescompra')
  notyf.success('Filtros limpiados correctamente')
}

const updateSort = (newSort) => {
  if (newSort && typeof newSort === 'string') {
    sortBy.value = newSort
    const query = {
      sort_by: newSort.split('-')[0],
      sort_direction: newSort.split('-')[1] || 'desc',
      search: searchTerm.value,
      estado: filtroEstado.value
    }
    router.visit('/ordenescompra', { data: query })
  }
}

const changePerPage = (event) => {
  const perPage = event.target.value
  const query = {
    per_page: perPage,
    search: searchTerm.value,
    estado: filtroEstado.value,
    sort_by: sortBy.value.split('-')[0],
    sort_direction: sortBy.value.split('-')[1] || 'desc'
  }
  router.visit('/ordenescompra', { data: query })
}

const handleSearch = () => {
  const query = {
    search: searchTerm.value,
    estado: filtroEstado.value,
    sort_by: sortBy.value.split('-')[0],
    sort_direction: sortBy.value.split('-')[1] || 'desc'
  }
  router.visit('/ordenescompra', { data: query })
}

const handleFilter = () => {
  const query = {
    search: searchTerm.value,
    estado: filtroEstado.value,
    sort_by: sortBy.value.split('-')[0],
    sort_direction: sortBy.value.split('-')[1] || 'desc'
  }
  router.visit('/ordenescompra', { data: query })
}

/* =========================
   Validaciones y utilidades
========================= */
function puedeEnviarOrden(orden) {
  if (!orden) return false
  return orden.estado === 'aprobada' || orden.estado === 'aprobado'
}

function puedeRecibirOrden(orden) {
  if (!orden) return false
  return orden.estado === 'enviada' || orden.estado === 'enviado'
}

function validarOrden(orden) {
  if (!orden?.id) {
    throw new Error('ID de orden no válido')
  }
  return true
}

function validarOrdenBasica(orden) {
  if (!orden?.id) {
    throw new Error('ID de orden no válido')
  }
  if (!orden.proveedor?.nombre_razon_social) {
    throw new Error('Datos del proveedor no encontrados')
  }
  const productos = orden.productos || orden.items || [];
  if (!Array.isArray(productos) || !productos.length) {
    throw new Error('Lista de productos no válida')
  }
  if (!orden.fecha_orden && !orden.created_at) {
    throw new Error('Fecha no especificada')
  }
  return true
}

const actualizarEstadoLocal = (id, nuevoEstado) => {
  const indice = ordenesOriginales.value.findIndex(o => o.id === id)
  if (indice !== -1) {
    ordenesOriginales.value[indice] = {
      ...ordenesOriginales.value[indice],
      estado: nuevoEstado
    }
  }

  if (fila.value?.id === id) {
    fila.value = { ...fila.value, estado: nuevoEstado }
  }
}
function validarOrdenParaPDF(doc) {
  if (!doc.id) throw new Error('ID del documento no encontrado')
  if (!doc.proveedor?.nombre_razon_social) throw new Error('Datos del proveedor no encontrados')
  const productos = doc.productos || doc.items || [];
  if (!Array.isArray(productos) || !productos.length) {
    throw new Error('Lista de productos no válida')
  }
  if (!doc.fecha_orden) throw new Error('Fecha no especificada')
  return true
}

/* =========================
   Acciones CRUD
========================= */
const verDetalles = (orden) => {
  try {
    validarOrden(orden)
    fila.value = orden
    modalMode.value = 'details'
    showModal.value = true
  } catch (error) {
    notyf.error(error.message)
  }
}

const editarOrden = (id) => {
  try {
    const ordenId = id || fila.value?.id
    if (!ordenId) throw new Error('ID de orden no válido')

    router.visit(`/ordenescompra/${ordenId}/edit`)
  } catch (error) {
    notyf.error(error.message)
  }
}

const editarFila = (id) => {
  editarOrden(id)
}

const duplicarOrden = async (orden) => {
  try {
    validarOrden(orden)

    if (!confirm(`¿Duplicar orden #${orden.numero_orden || orden.id}?`)) {
      return
    }

    loading.value = true

    router.post(`/ordenescompra/${orden.id}/duplicate`, {}, {
      onStart: () => {
        notyf.success('Duplicando orden...')
      },
      onSuccess: () => {
        notyf.success('Orden duplicada exitosamente')
      },
      onError: (errors) => {
        console.error('Error al duplicar:', errors)
        notyf.error('Error al duplicar la orden')
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

const imprimirOrden = async (orden) => {
  try {
    const doc = {
      ...orden,
      fecha: orden.fecha_orden || orden.created_at || new Date().toISOString()
    }

    validarOrdenParaPDF(doc)

    loading.value = true
    notyf.success('Generando PDF...')

    await generarPDF('Orden de Compra', doc)
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
    imprimirOrden(fila.value)
  }
}

const confirmarEliminacion = (id) => {
  try {
    if (!id) throw new Error('ID de orden no válido')

    selectedId.value = id
    modalMode.value = 'confirm'
    showModal.value = true
  } catch (error) {
    notyf.error(error.message)
  }
}

const eliminarOrden = async () => {
  try {
    if (!selectedId.value) throw new Error('No se seleccionó ninguna orden')

    loading.value = true

    router.post(`/ordenescompra/${selectedId.value}/cancel`, {}, {
      onStart: () => {
        notyf.success('Cancelando orden...')
      },
      onSuccess: (response) => {
        notyf.success('Orden cancelada exitosamente')

        // Actualizar datos locales
        const index = ordenesOriginales.value.findIndex(o => o.id === selectedId.value)
        if (index !== -1) {
          ordenesOriginales.value[index] = {
            ...ordenesOriginales.value[index],
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
        notyf.error('Error al cancelar la orden')
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

const enviarOrden = (ordenData) => {
  try {
    const doc = ordenData?.id ? ordenData : fila.value
    validarOrdenBasica(doc)

    loading.value = true

    router.post(`/ordenescompra/${doc.id}/enviar-compra`, {}, {
      preserveScroll: true,
      onSuccess: (page) => {
        const flash = page?.props?.flash || {}

        if (flash.error) {
          notyf.error(flash.error)
          return
        }

        const mensaje = flash.success || 'Orden enviada al proveedor'
        notyf.success(mensaje)
        actualizarEstadoLocal(doc.id, 'enviado_a_proveedor')
        showModal.value = false
      },
      onError: (errors) => {
        const firstError = Object.values(errors || {})[0]
        notyf.error(firstError || 'Error al enviar la orden a compra')
      },
      onFinish: () => {
        loading.value = false
      }
    })
  } catch (err) {
    console.error(err)
    notyf.error(err.message || 'Error al enviar la orden a compra')
    loading.value = false
  }
}

// Estado para recibir orden
const isReceivingOrden = ref(false)

const recibirOrden = async (ordenData) => {
  try {
    const doc = ordenData?.id ? ordenData : fila.value
    validarOrdenBasica(doc)

    loading.value = true
    notyf.success('Recibiendo orden...')

    const { data } = await axios.post(`/ordenescompra/${doc.id}/recibir-mercancia`, {
      forzarReenvio: !!ordenData?.forzarReenvio
    })

    if (!data?.success) throw new Error(data?.error || 'No se pudo recibir la orden')

    // Actualiza estado local
    const i = ordenesOriginales.value.findIndex(o => o.id === doc.id)
    if (i !== -1) ordenesOriginales.value[i] = { ...ordenesOriginales.value[i], estado: 'recibida' }

    showModal.value = false
    notyf.success(data.message || 'Orden recibida exitosamente')

    // Redirigir a la página de compras
    setTimeout(() => {
      router.visit('/compras')
    }, 1500)

  } catch (err) {
    console.error(err)
    notyf.error(err.response?.data?.error || err.response?.data?.message || err.message || 'Error al recibir orden')
  } finally {
    loading.value = false
  }
}

const crearNuevaOrden = () => {
  router.visit('/ordenescompra/create')
}
</script>

<template>
  <Head title="Órdenes de Compra" />

  <div class="ordenes-compra-index min-h-screen bg-gray-50">
    <!-- Contenido principal -->
    <div class="max-w-8xl mx-auto px-6 py-8">
      <!-- Header de filtros y estadísticas -->
      <UniversalHeader
        :total="estadisticas.total"
        :pendientes="estadisticas.pendientes"
        :enviadas="estadisticas.enviadas"
        :procesadas="estadisticas.procesadas"
        :canceladas="estadisticas.canceladas"
        v-model:search-term="searchTerm"
        v-model:sort-by="sortBy"
        v-model:filtro-estado="filtroEstado"
        :config="{
          module: 'ordenescompra',
          createButtonText: 'Nueva Orden de Compra',
          searchPlaceholder: 'Buscar por proveedor, número...'
        }"
        @limpiar-filtros="handleLimpiarFiltros"
        @search="handleSearch"
        @filter="handleFilter"
      />

      <!-- Información de paginación -->
      <div class="flex justify-between items-center mb-4 text-sm text-gray-600">
        <div>
          Mostrando {{ props.pagination.from || 0 }} -
          {{ props.pagination.to || 0 }}
          de {{ props.pagination.total || 0 }} órdenes
        </div>
        <div class="flex items-center space-x-2">
          <span>Elementos por página:</span>
          <select
            :value="props.pagination.per_page || 10"
            @change="changePerPage"
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
          :documentos="props.ordenesCompra.data || []"
          tipo="ordenescompra"
          :search-term="searchTerm"
          :sort-by="sortBy"
          :filtro-estado="filtroEstado"
          @ver-detalles="verDetalles"
          @editar="editarOrden"
          @duplicar="duplicarOrden"
          @imprimir="imprimirOrden"
          @eliminar="confirmarEliminacion"
          @enviar-compra="enviarOrden"
          @enviar-orden="enviarOrden"
          @recibir-orden="recibirOrden"
          @sort="updateSort"
        />
      </div>

      <!-- Controles de paginación -->
      <div v-if="props.pagination.last_page > 1" class="flex justify-center items-center space-x-2 mt-6">
        <button
          @click="prevPage"
          :disabled="props.pagination.current_page === 1"
          class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          Anterior
        </button>

        <div class="flex space-x-1">
          <!-- Primera página (solo si no está en visiblePages) -->
          <template v-if="!visiblePages.includes(1) && props.pagination.last_page > 7">
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
              page === props.pagination.current_page
                ? 'bg-blue-500 text-white border-blue-500'
                : 'text-gray-700 bg-white hover:bg-gray-50'
            ]"
          >
            {{ page }}
          </button>

          <!-- Última página (solo si no está en visiblePages) -->
          <template v-if="!visiblePages.includes(props.pagination.last_page) && props.pagination.last_page > 7">
            <span class="px-3 py-2 text-sm text-gray-500">...</span>
            <button
              @click="goToPage(props.pagination.last_page)"
              class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
            >
              {{ props.pagination.last_page }}
            </button>
          </template>
        </div>

        <button
          @click="nextPage"
          :disabled="props.pagination.current_page === props.pagination.last_page"
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
      tipo="ordenescompra"
      :selected="fila || {}"
      :auditoria="auditoriaForModal"
      @close="() => { showModal = false; fila = null; selectedId = null }"
      @confirm-delete="eliminarOrden"
      @imprimir="imprimirFila"
      @editar="editarFila"
      @enviar-orden="enviarOrden"
      @recibir-orden="recibirOrden"
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
.ordenes-compra-index {
  min-height: 100vh;
  background-color: #f9fafb;
}

@media (max-width: 640px) {
  .ordenes-compra-index .max-w-7xl {
    padding-left: 1rem;
    padding-right: 1rem;
  }

  .ordenes-compra-index h1 {
    font-size: 1.5rem;
  }
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

.ordenes-compra-index > * {
  animation: fadeIn 0.3s ease-out;
}
</style>
