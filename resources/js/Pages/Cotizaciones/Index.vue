<!-- /resources/js/Pages/Cotizaciones/Index.vue -->
<script setup>
import { ref, computed, watch } from 'vue'
import { router, Head } from '@inertiajs/vue3'
import axios from 'axios'
import { Notyf } from 'notyf'
import 'notyf/notyf.min.css'

import { generarPDF } from '@/Utils/pdfGenerator'
import AppLayout from '@/Layouts/AppLayout.vue'
import UniversalHeader from '@/Components/IndexComponents/UniversalHeader.vue'
import DocumentosTable from '@/Components/IndexComponents/DocumentosTable.vue'
import Modal from '@/Components/IndexComponents/Modales.vue' // Asegúrate que esta ruta exista y exporte default
// import Modal from '@/Components/DocumentoModal.vue' // <- Si el anterior falla, usa este

defineOptions({ layout: AppLayout })

const props = defineProps({
  cotizaciones: {
    type: Array,
    default: () => []
  }
})

/* =========================
   Estado local y modal
========================= */
const showModal = ref(false)
const fila = ref(null)               // Fila seleccionada para el modal
const modalMode = ref('details')     // 'details' | 'confirm'
const selectedId = ref(null)         // Para eliminar

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

/* Auditoría segura para el modal */
const auditoriaForModal = computed(() => {
  const r = fila.value
  if (!r) return null
  const meta = r.metadata || {}
  return {
    creado_por:       r.creado_por_nombre       || r.created_by_user_name || meta.creado_por       || null,
    actualizado_por:  r.actualizado_por_nombre  || r.updated_by_user_name || meta.actualizado_por  || null,
    eliminado_por:    r.eliminado_por_nombre    || r.deleted_by_user_name || meta.eliminado_por    || null,
    creado_en:        r.created_at              || meta.creado_en         || null,
    actualizado_en:   r.updated_at              || meta.actualizado_en    || null,
    eliminado_en:     r.deleted_at              || meta.eliminado_en      || null,
  }
})

/* =========================
   Filtros, orden y datos
========================= */
const searchTerm = ref('')
const sortBy = ref('fecha-desc')
const filtroEstado = ref('')

const notyf = new Notyf({
  duration: 4000,
  position: { x: 'right', y: 'top' },
  types: [
    { type: 'success', background: '#10b981', icon: false },
    { type: 'error', background: '#ef4444', icon: false }
  ]
})

const cotizacionesOriginales = ref([...props.cotizaciones])

watch(() => props.cotizaciones, (newVal) => {
  console.log('Cotizaciones recibidas:', newVal)
  cotizacionesOriginales.value = [...newVal]
}, { deep: true, immediate: true })

const estadisticas = computed(() => {
  const stats = { total: cotizacionesOriginales.value.length, aprobadas: 0, pendientes: 0 }
  cotizacionesOriginales.value.forEach(c => {
    if (c.estado === 'aprobado') stats.aprobadas++
    else if (c.estado === 'pendiente') stats.pendientes++
  })
  return stats
})

const handleLimpiarFiltros = () => {
  searchTerm.value = ''
  sortBy.value = 'fecha-desc'
  filtroEstado.value = ''
  notyf.success('Filtros limpiados correctamente')
}

watch([searchTerm, sortBy, filtroEstado], ([newSearch, newSort, newEstado]) => {
  console.log('Filtros cambiaron:', { search: newSearch, sort: newSort, estado: newEstado })
})

const updateSort = (newSort) => {
  if (newSort && typeof newSort === 'string') {
    sortBy.value = newSort
    console.log('Ordenamiento actualizado:', newSort)
  }
}

/* =========================
   Reglas/acciones
========================= */
function puedeEnviarAPedido(_cotizacion) {
  // Por ahora permitimos siempre. Ajusta con tu regla real
  return true
}

const verDetalles = (cotizacion) => {
  if (!cotizacion) {
    notyf.error('Cotización no válida')
    return
  }
  abrirDetalles(cotizacion)
}

const editarCotizacion = (id) => {
  if (!id) return notyf.error('ID de cotización no válido')
  router.visit(`/cotizaciones/${id}/edit`)
}

const editarFila = (id) => {
  // usado por el Modal @editar
  editarCotizacion(id || fila.value?.id)
}

const duplicarCotizacion = (cotizacion) => {
  if (!cotizacion?.id) return notyf.error('Documento no válido')
  if (confirm(`¿Duplicar cotización #${cotizacion.id}?`)) {
    router.post(`/cotizaciones/${cotizacion.id}/duplicate`, {}, {
      onSuccess: () => {
        notyf.success('Cotización duplicada')
      },
      onError: () => {
        notyf.error('Error al duplicar')
      }
    })
  }
}

const imprimirCotizacion = async (cotizacion) => {
  const doc = {
    ...cotizacion,
    fecha: cotizacion.fecha || cotizacion.created_at || new Date().toISOString()
  }

  if (!doc.id)              return notyf.error('Error: ID del documento no encontrado')
  if (!doc.cliente?.nombre) return notyf.error('Error: Datos del cliente no encontrados')
  if (!Array.isArray(doc.productos) || !doc.productos.length) return notyf.error('Error: Lista de productos no válida')
  if (!doc.fecha)           return notyf.error('Error: Fecha no especificada')

  try {
    notyf.success('Generando PDF...')
    await generarPDF('Cotización', doc)
    notyf.success('PDF generado correctamente')
  } catch (e) {
    console.error(e)
    notyf.error(`Error al generar el PDF: ${e.message}`)
  }
}

const imprimirFila = () => {
  if (!fila.value) return
  imprimirCotizacion(fila.value)
}

const confirmarEliminacion = (id) => {
  if (!id) return notyf.error('ID de cotización no válido')
  selectedId.value = id
  modalMode.value = 'confirm'
  showModal.value = true
}

const eliminarCotizacion = () => {
  if (!selectedId.value) return notyf.error('No se seleccionó ninguna cotización')

  router.delete(`/cotizaciones/${selectedId.value}`, {
    onSuccess: () => {
      notyf.success('Cotización eliminada exitosamente')
      cotizacionesOriginales.value = cotizacionesOriginales.value.filter(c => c.id !== selectedId.value)
      cerrarModal()
    },
    onError: (errors) => {
      console.error('Error al eliminar:', errors)
      notyf.error('Error al eliminar la cotización')
    }
  })
}

const enviarAPedido = async (cotizacionData) => {
  const doc = cotizacionData?.id ? cotizacionData : fila.value
  if (!doc?.id) return notyf.error('Documento no válido')
  if (!puedeEnviarAPedido(doc)) return notyf.error('No se puede enviar a pedido en este momento.')

  try {
    notyf.success('Enviando cotización a pedido...')
    const { data } = await axios.post(`/cotizaciones/${doc.id}/enviar-pedido`, {
      forzarReenvio: !!cotizacionData?.forzarReenvio
    })

    if (data?.success) {
      notyf.success(data.message || 'Cotización enviada a pedido')
      // marcar localmente
      const i = cotizacionesOriginales.value.findIndex(c => c.id === doc.id)
      if (i !== -1) cotizacionesOriginales.value[i] = { ...cotizacionesOriginales.value[i], estado: 'enviado_a_pedido' }
      cerrarModal()
      // Navega si quieres:
      // router.visit(route('pedidos.index'))
    } else {
      notyf.error(data?.error || 'No se pudo enviar a pedido')
    }
  } catch (error) {
    console.error(error)
    let msg = 'Error desconocido al enviar a pedido'
    if (error.response?.data?.error) msg = error.response.data.error
    else if (error.response?.data?.message) msg = error.response.data.message
    else if (error.message) msg = error.message
    notyf.error(msg)
  }
}

/* No aplica convertir a venta desde cotizaciones, pero el Modal lo emite.
   Dejamos handler con aviso elegante. */
const enviarAVenta = () => {
  notyf.error('Esta acción no está disponible desde Cotizaciones.')
}

const crearNuevaCotizacion = () => router.visit('/cotizaciones/create')
</script>

<template>
  <Head title="Cotizaciones" />

  <div class="cotizaciones-index min-h-screen bg-gray-50">
    <!-- Header principal -->
    <div class="bg-white shadow-sm border-b border-gray-200 px-6 py-8">
      <div class="max-w-7xl mx-auto">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Gestión de Cotizaciones</h1>
            <p class="text-gray-600">Administra y gestiona todas tus cotizaciones de manera eficiente</p>
          </div>
          <button
            class="px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white"
            @click="crearNuevaCotizacion"
          >
            Nueva Cotización
          </button>
        </div>
      </div>
    </div>

    <!-- Contenido principal -->
    <div class="max-w-8xl mx-auto px-6 py-8">
      <!-- Header de filtros y estadísticas -->
      <UniversalHeader
        :total="estadisticas.total"
        :aprobadas="estadisticas.aprobadas"
        :pendientes="estadisticas.pendientes"
        v-model:search-term="searchTerm"
        v-model:sort-by="sortBy"
        v-model:filtro-estado="filtroEstado"
        :config="{ module: 'cotizaciones', createButtonText: 'Nueva Cotización', searchPlaceholder: 'Buscar por cliente, número...' }"
        @limpiar-filtros="handleLimpiarFiltros"
      />

      <!-- Tabla de documentos -->
      <div class="mt-6">
        <DocumentosTable
          :documentos="cotizacionesOriginales"
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
      @imprimir="imprimirFila"
      @editar="(id) => editarFila(id)"
      @enviar-pedido="enviarAPedido"
      @enviar-a-venta="enviarAVenta"
    />
  </div>
</template>

<style scoped>
.cotizaciones-index { min-height: 100vh; background-color: #f9fafb; }
@media (max-width: 640px) {
  .cotizaciones-index .max-w-7xl { padding-left: 1rem; padding-right: 1rem; }
  .cotizaciones-index h1 { font-size: 1.5rem; }
}
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
.cotizaciones-index > * { animation: fadeIn 0.3s ease-out; }
</style>
