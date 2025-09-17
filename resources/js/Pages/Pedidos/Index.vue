<!-- /resources/js/Pages/Pedidos/Index.vue -->
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
  pedidos: {
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
const pedidosOriginales = ref([...props.pedidos])

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
const pedidosFiltrados = computed(() => {
  let result = [...pedidosOriginales.value]

  // Aplicar filtro de búsqueda
  if (searchTerm.value.trim()) {
    const search = searchTerm.value.toLowerCase().trim()
    result = result.filter(pedido => {
      const cliente = pedido.cliente?.nombre_razon_social?.toLowerCase() || ''
      const numero = String(pedido.numero_pedido || pedido.id || '').toLowerCase()
      const estado = pedido.estado?.toLowerCase() || ''

      return cliente.includes(search) ||
             numero.includes(search) ||
             estado.includes(search)
    })
  }

  // Aplicar filtro de estado
  if (filtroEstado.value) {
    result = result.filter(pedido => pedido.estado === filtroEstado.value)
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
          valueA = a.cliente?.nombre_razon_social || ''
          valueB = b.cliente?.nombre_razon_social || ''
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
  const filtered = pedidosFiltrados.value
  return Math.ceil((filtered?.length || 0) / itemsPerPage.value)
})

const paginatedPedidos = computed(() => {
  const filtered = pedidosFiltrados.value
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
watch(() => props.pedidos, (newVal) => {
  if (Array.isArray(newVal)) {
    pedidosOriginales.value = [...newVal]
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
    total: pedidosOriginales.value.length,
    pendientes: 0,
    borrador: 0,
    enviado_venta: 0,
    cancelado: 0,
  };

  pedidosOriginales.value.forEach(p => {
    const estado = String(p.estado || '').toLowerCase();
    switch (estado) {
      case 'pendiente':
        stats.pendientes++;
        break;
      case 'borrador':
        stats.borrador++;
        break;
      case 'enviado_venta':
        stats.enviado_venta++;
        break;
      case 'enviado':
        stats.pendientes++;
        break;
      case 'cancelado':
        stats.cancelado++;
        break;
      default:
        // Para cualquier otro estado, lo contamos como pendiente
        stats.pendientes++;
        break;
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
function puedeEnviarAVenta(pedido) {
  if (!pedido) return false
  const estadosValidos = ['confirmado', 'en_preparacion', 'listo_entrega', 'entregado', 'borrador']
  return estadosValidos.includes(pedido.estado)
}

function validarPedido(pedido) {
  if (!pedido?.id) {
    throw new Error('ID de pedido no válido')
  }
  return true
}

function validarPedidoParaPDF(doc) {
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
const verDetalles = (pedido) => {
  try {
    validarPedido(pedido)
    abrirDetalles(pedido)
  } catch (error) {
    notyf.error(error.message)
  }
}

const editarPedido = (id) => {
  try {
    const pedidoId = id || fila.value?.id
    if (!pedidoId) throw new Error('ID de pedido no válido')

    router.visit(`/pedidos/${pedidoId}/edit`)
  } catch (error) {
    notyf.error(error.message)
  }
}

const editarFila = (id) => {
  editarPedido(id)
}

const duplicarPedido = async (pedido) => {
  try {
    validarPedido(pedido)

    if (!confirm(`¿Duplicar pedido #${pedido.numero_pedido || pedido.id}?`)) {
      return
    }

    loading.value = true
    notyf.success('Duplicando pedido...')

    const { data } = await axios.post(`/pedidos/${pedido.id}/duplicate`)

    if (data?.success) {
      notyf.success(data.message || 'Pedido duplicado exitosamente')

      // Recargar la página para mostrar el pedido duplicado
      router.visit('/pedidos', {
        method: 'get',
        replace: true
      })
    } else {
      throw new Error(data?.error || 'Error al duplicar el pedido')
    }

  } catch (error) {
    console.error('Error al duplicar:', error)
    let mensaje = 'Error al duplicar el pedido'
    if (error.response?.data?.error) mensaje = error.response.data.error
    else if (error.response?.data?.message) mensaje = error.response.data.message
    else if (error.message) mensaje = error.message

    notyf.error(mensaje)
  } finally {
    loading.value = false
  }
}

const imprimirPedido = async (pedido) => {
  try {
    const doc = {
      ...pedido,
      fecha: pedido.fecha || pedido.created_at || new Date().toISOString()
    }

    validarPedidoParaPDF(doc)

    loading.value = true
    notyf.success('Generando PDF...')

    await generarPDF('Pedido', doc)
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
    imprimirPedido(fila.value)
  }
}

const confirmarEliminacion = (id) => {
  try {
    if (!id) throw new Error('ID de pedido no válido')

    selectedId.value = id
    modalMode.value = 'confirm'
    showModal.value = true
  } catch (error) {
    notyf.error(error.message)
  }
}

const eliminarPedido = async () => {
  try {
    if (!selectedId.value) throw new Error('No se seleccionó ningún pedido para cancelar')

    loading.value = true
    notyf.success('Cancelando pedido...')

    const { data } = await axios.post(`/pedidos/${selectedId.value}/cancel`)

    if (data?.success) {
      notyf.success(data.message || 'Pedido cancelado exitosamente')

      // Actualizar datos locales - marcar como cancelada en lugar de eliminar
      const index = pedidosOriginales.value.findIndex(p => p.id === selectedId.value)
      if (index !== -1) {
        pedidosOriginales.value[index] = {
          ...pedidosOriginales.value[index],
          estado: 'cancelado',
          eliminado_por: data?.eliminado_por || 'Usuario actual',
          deleted_at: new Date().toISOString()
        }
      }

      cerrarModal()
    } else {
      throw new Error(data?.error || 'Error al cancelar el pedido')
    }

  } catch (error) {
    console.error('Error al cancelar:', error)
    let mensaje = 'Error al cancelar el pedido'
    if (error.response?.data?.error) mensaje = error.response.data.error
    else if (error.response?.data?.message) mensaje = error.response.data.message
    else if (error.message) mensaje = error.message

    notyf.error(mensaje)
  } finally {
    loading.value = false
  }
}

const enviarAVenta = async (pedidoData) => {
  try {
    const docRaw = pedidoData?.id ? pedidoData : fila.value;

    // Validar que se pueda enviar a venta
    if (!puedeEnviarAVenta(docRaw)) {
      throw new Error('El pedido no está en un estado válido para enviar a venta')
    }

    // Normalizar fecha para el backend
    const doc = {
      ...docRaw,
      fecha: docRaw.fecha || docRaw.created_at || new Date().toISOString()
    };

    loading.value = true;
    notyf.success('Enviando pedido a venta...');

    const { data } = await axios.post(`/pedidos/${doc.id}/enviar-a-venta`, {
      forzarReenvio: !!pedidoData?.forzarReenvio
    });

    if (data?.success) {
      notyf.success(data.message || 'Pedido enviado a venta exitosamente');

      // Actualizar el estado local
      const index = pedidosOriginales.value.findIndex(p => p.id === doc.id);
      if (index !== -1) {
        pedidosOriginales.value[index] = {
          ...pedidosOriginales.value[index],
          estado: 'enviado_venta'
        };
      }

      cerrarModal();

      // Redirigir a ventas
      router.visit('/ventas');
    } else {
      throw new Error(data?.error || 'No se pudo enviar a venta');
    }

  } catch (error) {
    console.error('Error al enviar a venta:', error);

    let mensaje = 'Error desconocido al enviar a venta';
    if (error.response?.data?.error) mensaje = error.response.data.error;
    else if (error.response?.data?.message) mensaje = error.response.data.message;
    else if (error.message) mensaje = error.message;

    notyf.error(mensaje);
  } finally {
    loading.value = false;
  }
};

const enviarACotizacion = () => {
  notyf.warning('Esta acción no está disponible desde Pedidos.')
}

const crearNuevoPedido = () => {
  router.visit('/pedidos/create')
}
</script>

<template>
  <Head title="Pedidos" />

  <div class="pedidos-index min-h-screen bg-gray-50">
    <!-- Contenido principal -->
    <div class="max-w-8xl mx-auto px-6 py-8">
      <!-- Header de filtros y estadísticas -->
      <UniversalHeader
        :total="estadisticas.total"
        :pendientes="estadisticas.pendientes"
        :borrador="estadisticas.borrador"
        :enviado_venta="estadisticas.enviado_venta"
        :cancelado="estadisticas.cancelado"
        v-model:search-term="searchTerm"
        v-model:sort-by="sortBy"
        v-model:filtro-estado="filtroEstado"
        :config="{
          module: 'pedidos',
          createButtonText: 'Nuevo Pedido',
          searchPlaceholder: 'Buscar por cliente, número...'
        }"
        @limpiar-filtros="handleLimpiarFiltros"
      />

      <!-- Información de paginación -->
      <div class="flex justify-between items-center mb-4 text-sm text-gray-600">
        <div>
          Mostrando {{ (currentPage - 1) * itemsPerPage + 1 }} -
          {{ Math.min(currentPage * itemsPerPage, pedidosFiltrados.length) }}
          de {{ pedidosFiltrados.length }} pedidos
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
          :documentos="paginatedPedidos"
          tipo="pedidos"
          :search-term="searchTerm"
          :sort-by="sortBy"
          :filtro-estado="filtroEstado"
          @ver-detalles="verDetalles"
          @editar="editarPedido"
          @duplicar="duplicarPedido"
          @imprimir="imprimirPedido"
          @eliminar="confirmarEliminacion"
          @enviar-venta="enviarAVenta"
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
      tipo="pedidos"
      :selected="fila || {}"
      :auditoria="auditoriaForModal"
      @close="cerrarModal"
      @confirm-delete="eliminarPedido"
      @imprimir="imprimirFila"
      @editar="editarFila"
      @enviar-venta="enviarAVenta"
      @enviar-cotizacion="enviarACotizacion"
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
.pedidos-index {
  min-height: 100vh;
  background-color: #f9fafb;
}

@media (max-width: 640px) {
  .pedidos-index .max-w-8xl {
    padding-left: 1rem;
    padding-right: 1rem;
  }
  .pedidos-index h1 {
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

.pedidos-index > * {
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
