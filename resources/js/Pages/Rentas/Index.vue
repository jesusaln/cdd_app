<!-- /resources/js/Pages/Rentas/Index.vue -->
<script setup>
import { ref, computed, onMounted } from 'vue'
import { Head, router, usePage, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { Notyf } from 'notyf'
import 'notyf/notyf.min.css'
import { generarPDF } from '@/Utils/pdfGenerator'
import axios from 'axios'

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
  rentas: { type: [Object, Array], required: true },
  stats: { type: Object, default: () => ({}) },
  filters: { type: Object, default: () => ({}) },
  sorting: { type: Object, default: () => ({ sort_by: 'created_at', sort_direction: 'desc' }) },
})

// Estado UI
const showModal = ref(false)
const modalMode = ref('details')
const selectedRenta = ref(null)
const selectedId = ref(null)

// Filtros
const searchTerm = ref(props.filters?.search ?? '')
const sortBy = ref('created_at-desc')
const filtroEstado = ref('')

// Paginación
const perPage = ref(10)

// Header config
const headerConfig = {
  module: 'rentas',
  createButtonText: 'Nueva Renta',
  searchPlaceholder: 'Buscar por cliente, equipo, número de contrato...'
}

// Datos
const rentasPaginator = computed(() => props.rentas)
const rentasData = computed(() => rentasPaginator.value?.data || [])

// Estadísticas
const estadisticas = computed(() => ({
  total: props.stats?.total ?? 0,
  activas: props.stats?.activas ?? 0,
  vencidas: props.stats?.vencidas ?? 0,
  activasPorcentaje: props.stats?.activas > 0 ? Math.round((props.stats.activas / props.stats.total) * 100) : 0,
  vencidasPorcentaje: props.stats?.vencidas > 0 ? Math.round((props.stats.vencidas / props.stats.total) * 100) : 0
}))

// Transformación de datos
const rentasDocumentos = computed(() => {
  return rentasData.value.map(r => {
    return {
      id: r.id,
      titulo: r.numero_contrato || `Contrato #${r.id}`,
      subtitulo: r.cliente?.nombre_razon_social || 'Sin cliente',
      estado: r.estado,
      pago: r.monto_mensual || 0,
      anticipoPagado: (r.deposito_garantia && r.deposito_garantia > 0) ? 'Pagado' : 'No pagado',
      extra: `Equipos: ${r.equipos?.length || 0} | Inicio: ${r.fecha_inicio ? new Date(r.fecha_inicio).toLocaleDateString('es-MX') : 'N/A'}`,
      fecha: r.created_at,
      raw: r
    }
  })
})

// Handlers
function handleSearchChange(newSearch) {
  searchTerm.value = newSearch
  router.get(route('rentas.index'), {
    search: newSearch,
    sort_by: sortBy.value.split('-')[0],
    sort_direction: sortBy.value.split('-')[1] || 'desc',
    estado: filtroEstado.value,
    per_page: perPage.value,
    page: 1
  }, { preserveState: true, preserveScroll: true })
}

function handleEstadoChange(newEstado) {
  filtroEstado.value = newEstado
  router.get(route('rentas.index'), {
    search: searchTerm.value,
    sort_by: sortBy.value.split('-')[0],
    sort_direction: sortBy.value.split('-')[1] || 'desc',
    estado: newEstado,
    per_page: perPage.value,
    page: 1
  }, { preserveState: true, preserveScroll: true })
}

function handleSortChange(newSort) {
  sortBy.value = newSort
  router.get(route('rentas.index'), {
    search: searchTerm.value,
    sort_by: newSort.split('-')[0],
    sort_direction: newSort.split('-')[1] || 'desc',
    estado: filtroEstado.value,
    per_page: perPage.value,
    page: 1
  }, { preserveState: true, preserveScroll: true })
}

const verDetalles = (doc) => {
  selectedRenta.value = doc.raw
  modalMode.value = 'details'
  showModal.value = true
}

const editarRenta = (id) => {
  router.visit(route('rentas.edit', id))
}

const confirmarEliminacion = (id) => {
  selectedId.value = id
  modalMode.value = 'confirm'
  showModal.value = true
}

const eliminarRenta = () => {
  router.delete(route('rentas.destroy', selectedId.value), {
    preserveScroll: true,
    onSuccess: () => {
      notyf.success('Renta eliminada correctamente')
      showModal.value = false
      selectedId.value = null
      router.reload()
    },
    onError: (errors) => {
      notyf.error('No se pudo eliminar la renta')
    }
  })
}


const imprimirRenta = async (renta) => {
  const rentaConFecha = {
    ...renta,
    fecha: renta.fecha_inicio || renta.created_at || new Date().toISOString()
  }
  const validaciones = [
    [rentaConFecha.id, 'ID del documento no encontrado'],
    [rentaConFecha.cliente?.nombre, 'Datos del cliente no encontrados'],
    [Array.isArray(rentaConFecha.equipos) && rentaConFecha.equipos.length > 0, 'Lista de equipos no válida'],
    [rentaConFecha.fecha, 'Fecha no especificada']
  ]
  for (const [cond, msg] of validaciones) {
    if (!cond) return notyf.error(`Error: ${msg}`)
  }
  try {
    notyf.success('Generando PDF...')
    await generarPDF('Contrato de Renta', rentaConFecha)
    notyf.success('PDF generado correctamente')
  } catch (error) {
    notyf.error(`Error al generar PDF: ${error.message}`)
  }
}

const suspenderRenta = async (renta) => {
  if (!confirm(`¿Suspender la renta #${renta.numero_contrato}? Esto liberará los equipos.`)) return
  try {
    const response = await axios.post(route('rentas.suspender', renta.id))
    if (response.data.success) {
      notyf.success(response.data.message || 'Renta suspendida correctamente')
      router.reload()
    } else {
      notyf.error(response.data.error || 'Error al suspender la renta')
    }
  } catch (error) {
    const msg = error.response?.data?.error || 'Error al suspender la renta'
    notyf.error(msg)
  }
}

const reactivarRenta = async (renta) => {
  if (!confirm(`¿Reactivar la renta #${renta.numero_contrato}? Esto marcará los equipos como rentados.`)) return
  try {
    const response = await axios.post(route('rentas.reactivar', renta.id))
    if (response.data.success) {
      notyf.success(response.data.message || 'Renta reactivada correctamente')
      router.reload()
    } else {
      notyf.error(response.data.error || 'Error al reactivar la renta')
    }
  } catch (error) {
    const msg = error.response?.data?.error || 'Error al reactivar la renta'
    notyf.error(msg)
  }
}

const finalizarRenta = async (renta) => {
  if (!confirm(`¿Finalizar la renta #${renta.numero_contrato}? Esto liberará permanentemente los equipos.`)) return
  try {
    const response = await axios.post(route('rentas.finalizar', renta.id))
    if (response.data.success) {
      notyf.success(response.data.message || 'Renta finalizada correctamente')
      router.reload()
    } else {
      notyf.error(response.data.error || 'Error al finalizar la renta')
    }
  } catch (error) {
    const msg = error.response?.data?.error || 'Error al finalizar la renta'
    notyf.error(msg)
  }
}

const renovarRenta = async (renta) => {
  const meses = prompt('¿Cuántos meses deseas renovar?', '12')
  if (!meses || isNaN(meses) || meses <= 0) return

  try {
    const response = await axios.post(route('rentas.renovar', renta.id), {
      meses_renovacion: parseInt(meses)
    })
    if (response.data.success) {
      notyf.success(response.data.message || 'Renta renovada correctamente')
      router.reload()
    } else {
      notyf.error(response.data.error || 'Error al renovar la renta')
    }
  } catch (error) {
    const msg = error.response?.data?.error || 'Error al renovar la renta'
    notyf.error(msg)
  }
}

// Paginación
const paginationData = computed(() => {
  const p = rentasPaginator.value || {}
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
  router.get(route('rentas.index'), {
    ...props.filters,
    ...props.sorting,
    per_page: newPerPage,
    page: 1
  }, { preserveState: true, preserveScroll: true })
}

const handlePageChange = (newPage) => {
  router.get(route('rentas.index'), {
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
    'activo': 'bg-green-100 text-green-700',
    'proximo_vencimiento': 'bg-orange-100 text-orange-700',
    'vencido': 'bg-red-100 text-red-700',
    'moroso': 'bg-red-200 text-red-800',
    'suspendido': 'bg-yellow-100 text-yellow-700',
    'finalizado': 'bg-gray-100 text-gray-600',
    'anulado': 'bg-gray-100 text-gray-500'
  }
  return clases[estado] || 'bg-gray-100 text-gray-700'
}

const obtenerLabelEstado = (estado) => {
  const labels = {
    'activo': 'Activo',
    'proximo_vencimiento': 'Próximo Vencimiento',
    'vencido': 'Vencido',
    'moroso': 'Moroso',
    'suspendido': 'Suspendido',
    'finalizado': 'Finalizado',
    'anulado': 'Anulado'
  }
  return labels[estado] || 'Pendiente'
}
</script>

<template>
  <Head title="Rentas" />
  <div class="rentas-index min-h-screen bg-gray-50">
    <div class="max-w-8xl mx-auto px-6 py-8">
      <!-- Header -->
      <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-8 mb-6">
        <div class="flex flex-col lg:flex-row gap-8 items-start lg:items-center justify-between">
          <!-- Izquierda -->
          <div class="flex flex-col gap-6 w-full lg:w-auto">
            <div class="flex items-center gap-3">
              <h1 class="text-2xl font-bold text-slate-900">Rentas</h1>
            </div>

            <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center">
              <Link
                :href="route('rentas.create')"
                class="inline-flex items-center gap-2.5 px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-xl hover:from-blue-700 hover:to-blue-800 transition-all duration-200 shadow-lg"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>{{ headerConfig.createButtonText }}</span>
              </Link>
            </div>

            <!-- Estadísticas con barras de progreso -->
            <div class="flex flex-wrap items-center gap-4 text-sm">
              <div class="flex items-center gap-2 px-4 py-3 bg-slate-50 rounded-xl border border-slate-200">
                <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span class="font-medium text-slate-700">Total:</span>
                <span class="font-bold text-slate-900 text-lg">{{ formatNumber(estadisticas.total) }}</span>
              </div>

              <div class="flex items-center gap-2 px-4 py-3 bg-green-50 rounded-xl border border-green-200">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="font-medium text-slate-700">Activas:</span>
                <span class="font-bold text-green-700 text-lg">{{ formatNumber(estadisticas.activas) }}</span>
                <div class="ml-2 flex items-center gap-2">
                  <div class="w-16 h-2 bg-gray-200 rounded-full overflow-hidden">
                    <div
                      class="h-full bg-green-500 transition-all duration-300"
                      :style="{ width: estadisticas.activasPorcentaje + '%' }"
                    ></div>
                  </div>
                  <span class="text-xs text-green-600 font-medium">{{ estadisticas.activasPorcentaje }}%</span>
                </div>
              </div>

              <div class="flex items-center gap-2 px-4 py-3 bg-red-50 rounded-xl border border-red-200">
                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="font-medium text-slate-700">Vencidas:</span>
                <span class="font-bold text-red-700 text-lg">{{ formatNumber(estadisticas.vencidas) }}</span>
                <div class="ml-2 flex items-center gap-2">
                  <div class="w-16 h-2 bg-gray-200 rounded-full overflow-hidden">
                    <div
                      class="h-full bg-red-500 transition-all duration-300"
                      :style="{ width: estadisticas.vencidasPorcentaje + '%' }"
                    ></div>
                  </div>
                  <span class="text-xs text-red-600 font-medium">{{ estadisticas.vencidasPorcentaje }}%</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Derecha: Filtros -->
          <div class="flex flex-col sm:flex-row gap-3 w-full lg:w-auto lg:flex-shrink-0">
            <!-- Búsqueda -->
            <div class="relative">
              <input
                v-model="searchTerm"
                @input="handleSearchChange($event.target.value)"
                type="text"
                :placeholder="headerConfig.searchPlaceholder"
                class="w-full sm:w-64 lg:w-80 pl-4 pr-10 py-3 border border-slate-300 rounded-xl bg-white text-slate-900 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all duration-200"
              />
              <svg class="absolute right-3 top-3.5 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </div>

            <!-- Estado -->
            <select
              v-model="filtroEstado"
              @change="handleEstadoChange($event.target.value)"
              class="px-4 py-3 border border-slate-300 rounded-xl bg-white text-slate-900 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all duration-200"
            >
              <option value="">Todos los Estados</option>
              <option value="activo">Activas</option>
              <option value="proximo_vencimiento">Próximo Vencimiento</option>
              <option value="vencido">Vencidas</option>
              <option value="moroso">Morosas</option>
              <option value="suspendido">Suspendidas</option>
              <option value="finalizado">Finalizadas</option>
              <option value="anulado">Anuladas</option>
            </select>

            <!-- Orden -->
            <select
              v-model="sortBy"
              @change="handleSortChange($event.target.value)"
              class="px-4 py-3 border border-slate-300 rounded-xl bg-white text-slate-900 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all duration-200"
            >
              <option value="created_at-desc">Más Recientes</option>
              <option value="created_at-asc">Más Antiguos</option>
              <option value="numero_contrato-asc">Contrato A-Z</option>
              <option value="numero_contrato-desc">Contrato Z-A</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Tabla -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Fecha</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Contrato</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Cliente</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Mensualidad</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Anticipo</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Estado</th>
                <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Acciones</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="renta in rentasDocumentos" :key="renta.id" class="hover:bg-gray-50 transition-colors duration-150">
                <td class="px-6 py-4">
                  <div class="text-sm text-gray-900">{{ formatearFecha(renta.fecha) }}</div>
                </td>
                <td class="px-6 py-4">
                  <div class="text-sm font-medium text-gray-900">{{ renta.titulo }}</div>
                </td>
                <td class="px-6 py-4">
                  <div class="text-sm text-gray-700">{{ renta.subtitulo }}</div>
                </td>
                <td class="px-6 py-4">
                  <div class="text-sm text-gray-700">${{ formatNumber(renta.pago) }}</div>
                </td>
                <td class="px-6 py-4">
                  <span :class="renta.anticipoPagado === 'Pagado' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                    {{ renta.anticipoPagado }}
                  </span>
                </td>
                <td class="px-6 py-4">
                  <span :class="obtenerClasesEstado(renta.estado)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                    {{ obtenerLabelEstado(renta.estado) }}
                  </span>
                </td>
                <td class="px-6 py-4 text-right">
                  <div class="flex items-center justify-end space-x-1">
                    <button @click="verDetalles(renta)" class="w-8 h-8 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 transition-colors duration-150" title="Ver detalles">
                      <svg class="w-4 h-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                      </svg>
                    </button>
                    <button @click="editarRenta(renta.id)" class="w-8 h-8 bg-amber-50 text-amber-600 rounded-lg hover:bg-amber-100 transition-colors duration-150" title="Editar">
                      <svg class="w-4 h-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                      </svg>
                    </button>
                    <!-- Botones condicionales según estado -->
                    <button
                      v-if="renta.raw.estado === 'activo'"
                      @click="suspenderRenta(renta.raw)"
                      class="w-8 h-8 bg-orange-50 text-orange-600 rounded-lg hover:bg-orange-100 transition-colors duration-150"
                      title="Suspender"
                    >
                      <svg class="w-4 h-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                      </svg>
                    </button>
                    <button
                      v-if="renta.raw.estado === 'suspendido'"
                      @click="reactivarRenta(renta.raw)"
                      class="w-8 h-8 bg-emerald-50 text-emerald-600 rounded-lg hover:bg-emerald-100 transition-colors duration-150"
                      title="Reactivar"
                    >
                      <svg class="w-4 h-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1.586a1 1 0 01.707.293l.707.707A1 1 0 0012.414 11H15m-3 7.5A9.5 9.5 0 1121.5 12 9.5 9.5 0 0112 2.5"/>
                      </svg>
                    </button>
                    <button
                      v-if="['activo', 'proximo_vencimiento', 'vencido'].includes(renta.raw.estado)"
                      @click="renovarRenta(renta.raw)"
                      class="w-8 h-8 bg-indigo-50 text-indigo-600 rounded-lg hover:bg-indigo-100 transition-colors duration-150"
                      title="Renovar"
                    >
                      <svg class="w-4 h-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                      </svg>
                    </button>
                    <button
                      v-if="['activo', 'proximo_vencimiento', 'vencido'].includes(renta.raw.estado)"
                      @click="finalizarRenta(renta.raw)"
                      class="w-8 h-8 bg-purple-50 text-purple-600 rounded-lg hover:bg-purple-100 transition-colors duration-150"
                      title="Finalizar"
                    >
                      <svg class="w-4 h-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                      </svg>
                    </button>
                    <button @click="confirmarEliminacion(renta.id)" class="w-8 h-8 bg-red-50 text-red-600 rounded-lg hover:bg-red-100 transition-colors duration-150" title="Eliminar">
                      <svg class="w-4 h-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="rentasDocumentos.length === 0">
                <td colspan="7" class="px-6 py-16 text-center">
                  <div class="flex flex-col items-center space-y-4">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center">
                      <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                      </svg>
                    </div>
                    <div class="space-y-1">
                      <p class="text-gray-700 font-medium">No hay rentas</p>
                      <p class="text-sm text-gray-500">Las rentas aparecerán aquí cuando se creen</p>
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
              {{ modalMode === 'details' ? 'Detalles de la Renta' : 'Confirmar Eliminación' }}
            </h3>
            <button @click="showModal = false" class="text-gray-400 hover:text-gray-600 transition-colors">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <div class="p-6">
            <div v-if="modalMode === 'details' && selectedRenta">
              <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div class="space-y-3">
                    <div>
                      <label class="block text-sm font-medium text-gray-700">Número de Contrato</label>
                      <p class="mt-1 text-sm text-gray-900 bg-gray-50 px-3 py-2 rounded-md">{{ selectedRenta.numero_contrato || 'N/A' }}</p>
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700">Cliente</label>
                      <p class="mt-1 text-sm text-gray-900 bg-gray-50 px-3 py-2 rounded-md">{{ selectedRenta.cliente?.nombre || 'Sin cliente' }}</p>
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700">Fecha de Inicio</label>
                      <p class="mt-1 text-sm text-gray-900 bg-gray-50 px-3 py-2 rounded-md">{{ formatearFecha(selectedRenta.fecha_inicio) }}</p>
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700">Fecha de Fin</label>
                      <p class="mt-1 text-sm text-gray-900 bg-gray-50 px-3 py-2 rounded-md">{{ formatearFecha(selectedRenta.fecha_fin) }}</p>
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700">Estado</label>
                      <span :class="obtenerClasesEstado(selectedRenta.estado)" class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium mt-1">
                        {{ obtenerLabelEstado(selectedRenta.estado) }}
                      </span>
                    </div>
                  </div>
                  <div class="space-y-3">
                    <div>
                      <label class="block text-sm font-medium text-gray-700">Equipos Rentados</label>
                      <p class="mt-1 text-sm text-gray-900 bg-gray-50 px-3 py-2 rounded-md">{{ selectedRenta.equipos?.length || 0 }} equipos</p>
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700">Fecha de Creación</label>
                      <p class="mt-1 text-sm text-gray-900 bg-gray-50 px-3 py-2 rounded-md">{{ formatearFecha(selectedRenta.created_at) }}</p>
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700">Última Actualización</label>
                      <p class="mt-1 text-sm text-gray-900 bg-gray-50 px-3 py-2 rounded-md">{{ formatearFecha(selectedRenta.updated_at) }}</p>
                    </div>
                  </div>
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
                <h3 class="text-lg font-medium text-gray-900 mb-2">¿Eliminar Renta?</h3>
                <p class="text-sm text-gray-500 mb-4">
                  ¿Estás seguro de que deseas eliminar la renta <strong>{{ selectedRenta?.numero_contrato }}</strong>?
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
              <button @click="editarRenta(selectedRenta.id)" class="px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition-colors">
                Editar
              </button>
            </div>
            <button v-if="modalMode === 'confirm'" @click="eliminarRenta" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
              Eliminar
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.rentas-index {
  min-height: 100vh;
  background-color: #f9fafb;
}
</style>
