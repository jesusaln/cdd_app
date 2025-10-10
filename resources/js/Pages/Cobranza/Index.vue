<!-- /resources/js/Pages/Cobranza/Index.vue -->
<script setup>
import { ref, computed, onMounted } from 'vue'
import { Head, router, usePage, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { Notyf } from 'notyf'
import 'notyf/notyf.min.css'

import CobranzaHeader from '@/Components/IndexComponents/CobranzaHeader.vue'

defineOptions({ layout: AppLayout })

// Notificaciones
const notyf = new Notyf({
  duration: 4000,
  position: { x: 'right', y: 'top' },
  types: [
    { type: 'success', background: '#10b981', icon: false },
    { type: 'error', background: '#ef4444', icon: false },
    { type: 'warning', background: '#f59e0b', icon: false }
  ]
})

const page = usePage()
onMounted(() => {
  const flash = page.props.flash
  if (flash?.success) notyf.success(flash.success)
  if (flash?.error) notyf.error(flash.error)
})

// Props
const props = defineProps({
  cobranzas: { type: [Object, Array], required: true },
  stats: { type: Object, default: () => ({}) },
  filters: { type: Object, default: () => ({}) },
  sorting: { type: Object, default: () => ({ sort_by: 'fecha_cobro', sort_direction: 'desc' }) },
})

// Estado UI
const showModal = ref(false)
const modalMode = ref('details')
const selectedCobranza = ref(null)
const selectedId = ref(null)
const showGenerarModal = ref(false)

// Modal de pago
const showPaymentModal = ref(false)
const selectedCobranzaPago = ref(null)
const fechaPago = ref('')
const montoPagado = ref('')
const metodoPago = ref('')
const referenciaPago = ref('')
const notasPago = ref('')
const recibidoPor = ref('')

// Filtros
const searchTerm = ref(props.filters?.search ?? '')
const sortBy = ref('fecha_cobro-desc')
const filtroEstado = ref('')
const filtroMes = ref('')
const filtroAnio = ref(new Date().getFullYear().toString())

// Paginación
const perPage = ref(10)

// Función para crear nueva cobranza
const crearNuevaCobranza = () => {
  router.visit(route('cobranza.create'))
}

// Función para limpiar filtros
const limpiarFiltros = () => {
  searchTerm.value = ''
  sortBy.value = 'fecha_cobro-desc'
  filtroEstado.value = ''
  filtroMes.value = ''
  filtroAnio.value = new Date().getFullYear().toString()
  router.visit(route('cobranza.index'))
  notyf.success('Filtros limpiados correctamente')
}

// Datos
const cobranzasPaginator = computed(() => props.cobranzas)
const cobranzasData = computed(() => cobranzasPaginator.value?.data || [])

// Estadísticas
const estadisticas = computed(() => ({
  total: props.stats?.total ?? 0,
  pendientes: props.stats?.pendientes ?? 0,
  pagadas: props.stats?.pagadas ?? 0,
  vencidas: props.stats?.vencidas ?? 0,
  totalPendiente: props.stats?.total_pendiente ?? 0,
  totalPagado: props.stats?.total_pagado ?? 0,
  pendientesPorcentaje: props.stats?.total > 0 ? Math.round((props.stats.pendientes / props.stats.total) * 100) : 0,
  pagadasPorcentaje: props.stats?.total > 0 ? Math.round((props.stats.pagadas / props.stats.total) * 100) : 0
}))

// Transformación de datos
const cobranzasDocumentos = computed(() => {
  return cobranzasData.value.map(c => {
    return {
      id: c.id,
      titulo: c.concepto || 'Cobranza',
      subtitulo: c.renta?.cliente?.nombre_razon_social || 'Sin cliente',
      contrato: c.renta?.numero_contrato || 'N/A',
      estado: c.estado,
      monto: c.monto_cobrado || 0,
      montoPagado: c.monto_pagado || 0,
      fechaCobro: c.fecha_cobro,
      fechaPago: c.fecha_pago,
      metodoPago: c.metodo_pago,
      raw: c
    }
  })
})

// Handlers
function handleSearchChange(newSearch) {
  searchTerm.value = newSearch
  router.get(route('cobranza.index'), {
    search: newSearch,
    sort_by: sortBy.value.split('-')[0],
    sort_direction: sortBy.value.split('-')[1] || 'desc',
    estado: filtroEstado.value,
    mes: filtroMes.value,
    anio: filtroAnio.value,
    per_page: perPage.value,
    page: 1
  }, { preserveState: true, preserveScroll: true })
}

function handleEstadoChange(newEstado) {
  filtroEstado.value = newEstado
  router.get(route('cobranza.index'), {
    search: searchTerm.value,
    sort_by: sortBy.value.split('-')[0],
    sort_direction: sortBy.value.split('-')[1] || 'desc',
    estado: newEstado,
    mes: filtroMes.value,
    anio: filtroAnio.value,
    per_page: perPage.value,
    page: 1
  }, { preserveState: true, preserveScroll: true })
}

function handleMesChange(newMes) {
  filtroMes.value = newMes
  router.get(route('cobranza.index'), {
    search: searchTerm.value,
    sort_by: sortBy.value.split('-')[0],
    sort_direction: sortBy.value.split('-')[1] || 'desc',
    estado: filtroEstado.value,
    mes: newMes,
    anio: filtroAnio.value,
    per_page: perPage.value,
    page: 1
  }, { preserveState: true, preserveScroll: true })
}

function handleAnioChange(newAnio) {
  filtroAnio.value = newAnio
  router.get(route('cobranza.index'), {
    search: searchTerm.value,
    sort_by: sortBy.value.split('-')[0],
    sort_direction: sortBy.value.split('-')[1] || 'desc',
    estado: filtroEstado.value,
    mes: filtroMes.value,
    anio: newAnio,
    per_page: perPage.value,
    page: 1
  }, { preserveState: true, preserveScroll: true })
}

function handleSortChange(newSort) {
  sortBy.value = newSort
  router.get(route('cobranza.index'), {
    search: searchTerm.value,
    sort_by: newSort.split('-')[0],
    sort_direction: newSort.split('-')[1] || 'desc',
    estado: filtroEstado.value,
    mes: filtroMes.value,
    anio: filtroAnio.value,
    per_page: perPage.value,
    page: 1
  }, { preserveState: true, preserveScroll: true })
}

const verDetalles = (doc) => {
  selectedCobranza.value = doc.raw
  modalMode.value = 'details'
  showModal.value = true
}

const editarCobranza = (id) => {
  router.visit(route('cobranza.edit', id))
}

const confirmarEliminacion = (id) => {
  selectedId.value = id
  modalMode.value = 'confirm'
  showModal.value = true
}

const eliminarCobranza = () => {
  router.delete(route('cobranza.destroy', selectedId.value), {
    preserveScroll: true,
    onSuccess: () => {
      notyf.success('Cobranza eliminada correctamente')
      showModal.value = false
      selectedId.value = null
      router.reload()
    },
    onError: (errors) => {
      notyf.error('No se pudo eliminar la cobranza')
    }
  })
}

const marcarPagada = (cobranza) => {
  selectedCobranzaPago.value = cobranza
  fechaPago.value = new Date().toISOString().split('T')[0]
  montoPagado.value = cobranza.monto_cobrado.toString()
  metodoPago.value = ''
  referenciaPago.value = ''
  notasPago.value = ''
  recibidoPor.value = ''
  showPaymentModal.value = true
}

const cerrarPaymentModal = () => {
  showPaymentModal.value = false
  selectedCobranzaPago.value = null
  fechaPago.value = ''
  montoPagado.value = ''
  metodoPago.value = ''
  referenciaPago.value = ''
  notasPago.value = ''
  recibidoPor.value = ''
}

const confirmarPago = () => {
  if (!fechaPago.value) {
    notyf.error('Debe seleccionar una fecha de pago')
    return
  }
  if (!montoPagado.value || isNaN(montoPagado.value) || parseFloat(montoPagado.value) <= 0) {
    notyf.error('Debe ingresar un monto válido')
    return
  }
  if (!metodoPago.value) {
    notyf.error('Debe seleccionar un método de pago')
    return
  }

  router.post(route('cobranza.marcar-pagada', selectedCobranzaPago.value.id), {
    fecha_pago: fechaPago.value,
    monto_pagado: parseFloat(montoPagado.value),
    metodo_pago: metodoPago.value,
    referencia_pago: referenciaPago.value,
    notas_pago: notasPago.value,
    recibido_por: recibidoPor.value,
  }, {
    onSuccess: () => {
      notyf.success('Cobranza marcada como pagada')
      cerrarPaymentModal()
      router.reload()
    },
    onError: (errors) => {
      notyf.error('Error al marcar como pagada')
    }
  })
}

const generarAutomaticas = () => {
  const mes = prompt('Mes (1-12):', new Date().getMonth() + 1)
  const anio = prompt('Año:', new Date().getFullYear())

  if (!mes || !anio || isNaN(mes) || isNaN(anio)) return

  router.post(route('cobranza.generar-automaticas'), {
    mes: parseInt(mes),
    anio: parseInt(anio),
  }, {
    onSuccess: () => {
      notyf.success('Cobranzas generadas automáticamente')
      router.reload()
    },
    onError: (errors) => {
      notyf.error('Error al generar cobranzas')
    }
  })
}

// Paginación
const paginationData = computed(() => {
  const p = cobranzasPaginator.value || {}
  return {
    currentPage: p.current_page ?? 1,
    lastPage:    p.last_page ?? 1,
    perPage:     p.per_page ?? 10,
    from:        p.from ?? 0,
    to:          p.to ?? 0,
    total:       p.total ?? 0,
    prevPageUrl: p.prev_page_url ?? null,
    nextPageUrl: p.next_page_url ?? null,
    links:       p.links ?? []
  }
})

const handlePerPageChange = (newPerPage) => {
  router.get(route('cobranza.index'), {
    ...props.filters,
    ...props.sorting,
    per_page: newPerPage,
    page: 1
  }, { preserveState: true, preserveScroll: true })
}

const handlePageChange = (newPage) => {
  router.get(route('cobranza.index'), {
    ...props.filters,
    ...props.sorting,
    page: newPage
  }, { preserveState: true, preserveScroll: true })
}

// Helpers
const formatNumber = (num) => new Intl.NumberFormat('es-ES').format(num)
const formatearFecha = (date) => {
  if (!date) return 'Fecha no disponible'
  try {
    const d = new Date(date)
    return d.toLocaleDateString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric' })
  } catch {
    return 'Fecha inválida'
  }
}

const obtenerClasesEstado = (estado) => {
  const clases = {
    'pendiente': 'bg-yellow-100 text-yellow-700',
    'pagado': 'bg-green-100 text-green-700',
    'parcial': 'bg-blue-100 text-blue-700',
    'vencido': 'bg-red-100 text-red-700',
    'cancelado': 'bg-gray-100 text-gray-600'
  }
  return clases[estado] || 'bg-gray-100 text-gray-700'
}

const obtenerLabelEstado = (estado) => {
  const labels = {
    'pendiente': 'Pendiente',
    'pagado': 'Pagado',
    'parcial': 'Parcial',
    'vencido': 'Vencido',
    'cancelado': 'Cancelado'
  }
  return labels[estado] || 'Pendiente'
}
</script>

<template>
  <Head title="Cobranza" />
  <div class="cobranza-index min-h-screen bg-gray-50">
    <div class="max-w-8xl mx-auto px-6 py-8">
      <!-- Header específico de cobranza -->
      <CobranzaHeader
        :total="estadisticas.total"
        :pendientes="estadisticas.pendientes"
        :pagadas="estadisticas.pagadas"
        :vencidas="estadisticas.vencidas"
        :total-pendiente="estadisticas.totalPendiente"
        :total-pagado="estadisticas.totalPagado"
        v-model:search-term="searchTerm"
        v-model:sort-by="sortBy"
        v-model:filtro-estado="filtroEstado"
        v-model:filtro-mes="filtroMes"
        v-model:filtro-anio="filtroAnio"
        @crear-nueva="crearNuevaCobranza"
        @generar-automaticas="generarAutomaticas"
        @search-change="handleSearchChange"
        @filtro-estado-change="handleEstadoChange"
        @filtro-mes-change="handleMesChange"
        @filtro-anio-change="handleAnioChange"
        @sort-change="handleSortChange"
        @limpiar-filtros="limpiarFiltros"
      />

      <!-- Tabla -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Fecha Cobro</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Cliente</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Contrato</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Concepto</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Monto</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Estado</th>
                <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Acciones</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="cobranza in cobranzasDocumentos" :key="cobranza.id" class="hover:bg-gray-50 transition-colors duration-150">
                <td class="px-6 py-4">
                  <div class="text-sm text-gray-900">{{ formatearFecha(cobranza.fechaCobro) }}</div>
                </td>
                <td class="px-6 py-4">
                  <div class="text-sm font-medium text-gray-900">{{ cobranza.subtitulo }}</div>
                </td>
                <td class="px-6 py-4">
                  <div class="text-sm text-gray-700">{{ cobranza.contrato }}</div>
                </td>
                <td class="px-6 py-4">
                  <div class="text-sm text-gray-700">{{ cobranza.titulo }}</div>
                </td>
                <td class="px-6 py-4">
                  <div class="text-sm font-medium text-gray-900">${{ formatNumber(cobranza.monto) }}</div>
                  <div v-if="cobranza.montoPagado > 0 && cobranza.montoPagado < cobranza.monto" class="text-xs text-blue-600">
                    Pagado: ${{ formatNumber(cobranza.montoPagado) }}
                  </div>
                </td>
                <td class="px-6 py-4">
                  <span :class="obtenerClasesEstado(cobranza.estado)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                    {{ obtenerLabelEstado(cobranza.estado) }}
                  </span>
                </td>
                <td class="px-6 py-4 text-right">
                  <div class="flex items-center justify-end space-x-1">
                    <button @click="verDetalles(cobranza)" class="w-8 h-8 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 transition-colors duration-150" title="Ver detalles">
                      <svg class="w-4 h-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                      </svg>
                    </button>
                    <button @click="editarCobranza(cobranza.id)" class="w-8 h-8 bg-amber-50 text-amber-600 rounded-lg hover:bg-amber-100 transition-colors duration-150" title="Editar">
                      <svg class="w-4 h-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                      </svg>
                    </button>
                    <button
                      v-if="cobranza.estado === 'pendiente' || cobranza.estado === 'parcial'"
                      @click="marcarPagada(cobranza.raw)"
                      class="w-8 h-8 bg-green-50 text-green-600 rounded-lg hover:bg-green-100 transition-colors duration-150"
                      title="Marcar como pagada"
                    >
                      <svg class="w-4 h-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                      </svg>
                    </button>
                    <button @click="confirmarEliminacion(cobranza.id)" class="w-8 h-8 bg-red-50 text-red-600 rounded-lg hover:bg-red-100 transition-colors duration-150" title="Eliminar">
                      <svg class="w-4 h-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="cobranzasDocumentos.length === 0">
                <td colspan="7" class="px-6 py-16 text-center">
                  <div class="flex flex-col items-center space-y-4">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center">
                      <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                      </svg>
                    </div>
                    <div class="space-y-1">
                      <p class="text-gray-700 font-medium">No hay cobranzas</p>
                      <p class="text-sm text-gray-500">Las cobranzas aparecerán aquí cuando se creen</p>
                    </div>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Paginación -->
        <div v-if="paginationData.lastPage > 1" class="bg-white border-t border-gray-200 px-4 py-3 sm:px-6">
          <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-4">
              <p class="text-sm text-gray-700">
                Mostrando {{ paginationData.from }} - {{ paginationData.to }} de {{ paginationData.total }} resultados
              </p>
              <select
                :value="paginationData.perPage"
                @change="handlePerPageChange(parseInt($event.target.value))"
                class="border border-gray-300 rounded-md text-sm py-1 px-2 bg-white"
              >
                <option value="10">10</option>
                <option value="15">15</option>
                <option value="25">25</option>
                <option value="50">50</option>
              </select>
            </div>

            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
              <button
                v-if="paginationData.prevPageUrl"
                @click="handlePageChange(paginationData.currentPage - 1)"
                class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
              >
                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
              </button>

              <span v-else class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-gray-100 text-sm font-medium text-gray-400">
                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
              </span>

              <button
                v-for="page in [paginationData.currentPage - 1, paginationData.currentPage, paginationData.currentPage + 1].filter(p => p > 0 && p <= paginationData.lastPage)"
                :key="page"
                @click="handlePageChange(page)"
                :class="page === paginationData.currentPage ? 'bg-blue-50 border-blue-500 text-blue-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'"
                class="relative inline-flex items-center px-4 py-2 border text-sm font-medium"
              >
                {{ page }}
              </button>

              <button
                v-if="paginationData.nextPageUrl"
                @click="handlePageChange(paginationData.currentPage + 1)"
                class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
              >
                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                </svg>
              </button>

              <span v-else class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-gray-100 text-sm font-medium text-gray-400">
                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                </svg>
              </span>
            </nav>
          </div>
        </div>
      </div>

      <!-- Modal mejorado -->
      <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" @click.self="showModal = false">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
          <!-- Header del modal -->
          <div class="flex items-center justify-between p-6 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">
              {{ modalMode === 'details' ? 'Detalles de la Cobranza' : 'Confirmar Eliminación' }}
            </h3>
            <button @click="showModal = false" class="text-gray-400 hover:text-gray-600 transition-colors">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <div class="p-6">
            <div v-if="modalMode === 'details' && selectedCobranza">
              <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div class="space-y-3">
                    <div>
                      <label class="block text-sm font-medium text-gray-700">Concepto</label>
                      <p class="mt-1 text-sm text-gray-900 bg-gray-50 px-3 py-2 rounded-md">{{ selectedCobranza.concepto || 'N/A' }}</p>
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700">Cliente</label>
                      <p class="mt-1 text-sm text-gray-900 bg-gray-50 px-3 py-2 rounded-md">{{ selectedCobranza.renta?.cliente?.nombre_razon_social || 'Sin cliente' }}</p>
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700">Contrato</label>
                      <p class="mt-1 text-sm text-gray-900 bg-gray-50 px-3 py-2 rounded-md">{{ selectedCobranza.renta?.numero_contrato || 'N/A' }}</p>
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700">Fecha de Cobro</label>
                      <p class="mt-1 text-sm text-gray-900 bg-gray-50 px-3 py-2 rounded-md">{{ formatearFecha(selectedCobranza.fecha_cobro) }}</p>
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700">Estado</label>
                      <span :class="obtenerClasesEstado(selectedCobranza.estado)" class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium mt-1">
                        {{ obtenerLabelEstado(selectedCobranza.estado) }}
                      </span>
                    </div>
                  </div>
                  <div class="space-y-3">
                    <div>
                      <label class="block text-sm font-medium text-gray-700">Monto Cobrado</label>
                      <p class="mt-1 text-sm text-gray-900 bg-gray-50 px-3 py-2 rounded-md">${{ formatNumber(selectedCobranza.monto_cobrado) }}</p>
                    </div>
                    <div v-if="selectedCobranza.fecha_pago">
                      <label class="block text-sm font-medium text-gray-700">Fecha de Pago</label>
                      <p class="mt-1 text-sm text-gray-900 bg-gray-50 px-3 py-2 rounded-md">{{ formatearFecha(selectedCobranza.fecha_pago) }}</p>
                    </div>
                    <div v-if="selectedCobranza.monto_pagado">
                      <label class="block text-sm font-medium text-gray-700">Monto Pagado</label>
                      <p class="mt-1 text-sm text-gray-900 bg-gray-50 px-3 py-2 rounded-md">${{ formatNumber(selectedCobranza.monto_pagado) }}</p>
                    </div>
                    <div v-if="selectedCobranza.metodo_pago">
                      <label class="block text-sm font-medium text-gray-700">Método de Pago</label>
                      <p class="mt-1 text-sm text-gray-900 bg-gray-50 px-3 py-2 rounded-md">{{ selectedCobranza.metodo_pago }}</p>
                    </div>
                    <div v-if="selectedCobranza.referencia_pago">
                      <label class="block text-sm font-medium text-gray-700">Referencia</label>
                      <p class="mt-1 text-sm text-gray-900 bg-gray-50 px-3 py-2 rounded-md">{{ selectedCobranza.referencia_pago }}</p>
                    </div>
                    <div v-if="selectedCobranza.recibido_por">
                      <label class="block text-sm font-medium text-gray-700">Recibido Por</label>
                      <p class="mt-1 text-sm text-gray-900 bg-gray-50 px-3 py-2 rounded-md">{{ selectedCobranza.recibido_por }}</p>
                    </div>
                  </div>
                </div>
                <div v-if="selectedCobranza.notas">
                  <label class="block text-sm font-medium text-gray-700">Notas</label>
                  <p class="mt-1 text-sm text-gray-900 bg-gray-50 px-3 py-2 rounded-md">{{ selectedCobranza.notas }}</p>
                </div>
                <div v-if="selectedCobranza.notas_pago">
                  <label class="block text-sm font-medium text-gray-700">Notas de Pago</label>
                  <p class="mt-1 text-sm text-gray-900 bg-gray-50 px-3 py-2 rounded-md">{{ selectedCobranza.notas_pago }}</p>
                </div>
              </div>
            </div>

            <div v-if="modalMode === 'confirm'">
              <div class="text-center">
                <div class="w-12 h-12 mx-auto bg-red-100 rounded-full flex items-center justify-center mb-4">
                  <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                  </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">¿Eliminar Cobranza?</h3>
                <p class="text-sm text-gray-500 mb-4">
                  ¿Estás seguro de que deseas eliminar esta cobranza?
                  Esta acción no se puede deshacer.
                </p>
              </div>
            </div>
          </div>

          <!-- Footer del modal -->
          <div class="flex justify-end gap-3 px-6 py-4 border-t border-gray-200 bg-gray-50">
            <button @click="showModal = false" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors">
              {{ modalMode === 'details' ? 'Cerrar' : 'Cancelar' }}
            </button>
            <div v-if="modalMode === 'details'" class="flex gap-2">
              <button
                v-if="selectedCobranza.estado === 'pendiente' || selectedCobranza.estado === 'parcial'"
                @click="marcarPagada(selectedCobranza)"
                class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors"
              >
                Marcar Pagada
              </button>
              <button @click="editarCobranza(selectedCobranza.id)" class="px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition-colors">
                Editar
              </button>
            </div>
            <button v-if="modalMode === 'confirm'" @click="eliminarCobranza" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
              Eliminar
            </button>
          </div>
        </div>
      </div>

      <!-- Modal de Pago -->
      <div v-if="showPaymentModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" @click.self="cerrarPaymentModal">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
          <!-- Header del modal -->
          <div class="flex items-center justify-between p-6 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Marcar Cobranza como Pagada</h3>
            <button @click="cerrarPaymentModal" class="text-gray-400 hover:text-gray-600 transition-colors">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <div class="p-6">
            <div v-if="selectedCobranzaPago" class="space-y-4">
              <!-- Información de la cobranza -->
              <div class="bg-gray-50 p-4 rounded-lg">
                <div class="flex justify-between items-center">
                  <span class="text-sm font-medium text-gray-700">Concepto:</span>
                  <span class="text-sm text-gray-900">{{ selectedCobranzaPago.concepto || 'Cobranza' }}</span>
                </div>
                <div class="flex justify-between items-center mt-2">
                  <span class="text-sm font-medium text-gray-700">Monto Total:</span>
                  <span class="text-lg font-bold text-gray-900">${{ formatNumber(selectedCobranzaPago.monto_cobrado) }}</span>
                </div>
              </div>

              <!-- Fecha de pago -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Fecha de Pago *</label>
                <input
                  v-model="fechaPago"
                  type="date"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                />
              </div>

              <!-- Monto pagado -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Monto Pagado *</label>
                <input
                  v-model="montoPagado"
                  type="number"
                  step="0.01"
                  min="0"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  placeholder="0.00"
                />
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

              <!-- Referencia -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Referencia (opcional)</label>
                <input
                  v-model="referenciaPago"
                  type="text"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  placeholder="Número de referencia, folio, etc."
                />
              </div>

              <!-- Recibido por -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Recibido Por (opcional)</label>
                <input
                  v-model="recibidoPor"
                  type="text"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  placeholder="Nombre de quien recibió el pago"
                />
              </div>

              <!-- Notas del pago -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Notas de Pago (opcional)</label>
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
              :disabled="!fechaPago || !montoPagado || !metodoPago"
              class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
            >
              Confirmar Pago
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.cobranza-index {
  min-height: 100vh;
  background-color: #f9fafb;
}
</style>
