<script setup>
import { ref, computed, onMounted } from 'vue'
import { Head, router, usePage, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { Notyf } from 'notyf'
import 'notyf/notyf.min.css'

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
  asignaciones: { type: Object, required: true },
  tecnicos: { type: Array, default: () => [] },
  stats: { type: Object, default: () => ({}) },
  filters: { type: Object, default: () => ({}) }
})

// Estado
const searchTerm = ref(props.filters?.search ?? '')
const estadoFilter = ref(props.filters?.estado ?? '')
const tecnicoFilter = ref(props.filters?.tecnico_id ?? '')
const showModal = ref(false)
const modalMode = ref('details')
const selectedAsignacion = ref(null)

// Computed
const asignacionesData = computed(() => props.asignaciones?.data || [])
const estadisticas = computed(() => props.stats)

// Estados de asignaciones masivas
const estadosAsignaciones = {
  'pendiente': { label: 'Pendiente', color: 'yellow', icon: 'clock' },
  'activa': { label: 'Activa', color: 'blue', icon: 'play' },
  'completada': { label: 'Completada', color: 'green', icon: 'check' },
  'cancelada': { label: 'Cancelada', color: 'red', icon: 'x' }
}

// Métodos
const handleSearchChange = (newSearch) => {
  searchTerm.value = newSearch
  aplicarFiltros()
}

const handleEstadoChange = (newEstado) => {
  estadoFilter.value = newEstado
  aplicarFiltros()
}

const handleTecnicoChange = (newTecnico) => {
  tecnicoFilter.value = newTecnico
  aplicarFiltros()
}

const aplicarFiltros = () => {
  router.get(route('herramientas.asignaciones-masivas.index'), {
    search: searchTerm.value,
    estado: estadoFilter.value,
    tecnico_id: tecnicoFilter.value,
    page: 1
  }, { preserveState: true, preserveScroll: true })
}

const verDetalles = (asignacion) => {
  router.visit(route('herramientas.asignaciones-masivas.show', asignacion.id))
}

const autorizarAsignacion = (asignacion) => {
  if (confirm('¿Está seguro de que desea autorizar esta asignación masiva?')) {
    router.post(route('herramientas.asignaciones-masivas.autorizar', asignacion.id), {}, {
      onSuccess: () => {
        notyf.success('Asignación autorizada correctamente')
        router.reload()
      },
      onError: (errors) => {
        notyf.error('Error al autorizar la asignación')
      }
    })
  }
}

const completarAsignacion = (asignacion) => {
  const observaciones = prompt('Observaciones de la devolución (opcional):')
  if (observaciones !== null) {
    router.post(route('herramientas.asignaciones-masivas.completar', asignacion.id), {
      observaciones_devolucion: observaciones
    }, {
      onSuccess: () => {
        notyf.success('Asignación completada correctamente')
        router.reload()
      },
      onError: (errors) => {
        notyf.error('Error al completar la asignación')
      }
    })
  }
}

const cancelarAsignacion = (asignacion) => {
  const motivo = prompt('Motivo de la cancelación:')
  if (motivo && motivo.trim()) {
    router.post(route('herramientas.asignaciones-masivas.cancelar', asignacion.id), {
      motivo_cancelacion: motivo
    }, {
      onSuccess: () => {
        notyf.success('Asignación cancelada correctamente')
        router.reload()
      },
      onError: (errors) => {
        notyf.error('Error al cancelar la asignación')
      }
    })
  }
}

const getEstadoClasses = (estado) => {
  const estadoInfo = estadosAsignaciones[estado] || estadosAsignaciones['pendiente']
  return `bg-${estadoInfo.color}-100 text-${estadoInfo.color}-700`
}

const formatearFecha = (fecha) => {
  if (!fecha) return 'N/A'
  return new Date(fecha).toLocaleDateString('es-MX', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const formatNumber = (num) => new Intl.NumberFormat('es-ES').format(num)

// Paginación
const paginationData = computed(() => ({
  current_page: props.asignaciones?.current_page || 1,
  last_page: props.asignaciones?.last_page || 1,
  per_page: props.asignaciones?.per_page || 15,
  from: props.asignaciones?.from || 0,
  to: props.asignaciones?.to || 0,
  total: props.asignaciones?.total || 0,
  prev_page_url: props.asignaciones?.prev_page_url,
  next_page_url: props.asignaciones?.next_page_url,
  links: props.asignaciones?.links || []
}))

const handlePageChange = (newPage) => {
  router.get(route('herramientas.asignaciones-masivas.index'), {
    ...props.filters,
    page: newPage
  }, { preserveState: true, preserveScroll: true })
}
</script>

<template>
  <Head title="Asignaciones Masivas de Herramientas" />

  <div class="asignaciones-masivas-index min-h-screen bg-gray-50">
    <div class="max-w-8xl mx-auto px-6 py-8">
      <!-- Header -->
      <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-8 mb-6">
        <div class="flex flex-col lg:flex-row gap-8 items-start lg:items-center justify-between">
          <!-- Izquierda -->
          <div class="flex flex-col gap-6 w-full lg:w-auto">
            <div class="flex items-center gap-3">
              <h1 class="text-2xl font-bold text-slate-900">Asignaciones Masivas</h1>
              <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-700">
                {{ formatNumber(estadisticas.total) }} total
              </span>
            </div>

            <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center">
              <Link
                :href="route('herramientas.asignaciones-masivas.create')"
                class="inline-flex items-center gap-2.5 px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-xl hover:from-blue-700 hover:to-blue-800 transition-all duration-200 shadow-lg"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Nueva Asignación Masiva</span>
              </Link>

              <Link
                :href="route('herramientas.tecnicos-herramientas.index')"
                class="inline-flex items-center gap-2 px-4 py-3 bg-purple-50 text-purple-700 rounded-xl hover:bg-purple-100 transition-all duration-200 border border-purple-200"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <span class="text-sm font-medium">Ver por Técnico</span>
              </Link>
            </div>

            <!-- Estadísticas -->
            <div class="flex flex-wrap items-center gap-4 text-sm">
              <div class="flex items-center gap-2 px-4 py-3 bg-slate-50 rounded-xl border border-slate-200">
                <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span class="font-medium text-slate-700">Total:</span>
                <span class="font-bold text-slate-900 text-lg">{{ formatNumber(estadisticas.total) }}</span>
              </div>

              <div class="flex items-center gap-2 px-4 py-3 bg-yellow-50 rounded-xl border border-yellow-200">
                <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="font-medium text-slate-700">Pendientes:</span>
                <span class="font-bold text-yellow-700 text-lg">{{ formatNumber(estadisticas.pendientes) }}</span>
              </div>

              <div class="flex items-center gap-2 px-4 py-3 bg-blue-50 rounded-xl border border-blue-200">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1.586a1 1 0 01.707.293l2.414 2.414a1 1 0 00.707.293H15M9 10V9a2 2 0 012-2h2a2 2 0 012 2v1M9 10H7a2 2 0 00-2 2v5a2 2 0 002 2h10a2 2 0 002-2v-5a2 2 0 00-2-2h-2" />
                </svg>
                <span class="font-medium text-slate-700">Activas:</span>
                <span class="font-bold text-blue-700 text-lg">{{ formatNumber(estadisticas.activas) }}</span>
              </div>

              <div class="flex items-center gap-2 px-4 py-3 bg-green-50 rounded-xl border border-green-200">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="font-medium text-slate-700">Completadas:</span>
                <span class="font-bold text-green-700 text-lg">{{ formatNumber(estadisticas.completadas) }}</span>
              </div>

              <div class="flex items-center gap-2 px-4 py-3 bg-orange-50 rounded-xl border border-orange-200">
                <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                </svg>
                <span class="font-medium text-slate-700">Herramientas Asignadas:</span>
                <span class="font-bold text-orange-700 text-lg">{{ formatNumber(estadisticas.total_herramientas_asignadas) }}</span>
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
                placeholder="Buscar por código, técnico o proyecto..."
                class="w-full sm:w-64 lg:w-80 pl-4 pr-10 py-3 border border-slate-300 rounded-xl bg-white text-slate-900 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all duration-200"
              />
              <svg class="absolute right-3 top-3.5 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </div>

            <!-- Estado -->
            <select
              v-model="estadoFilter"
              @change="handleEstadoChange($event.target.value)"
              class="px-4 py-3 border border-slate-300 rounded-xl bg-white text-slate-900 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all duration-200"
            >
              <option value="">Todos los Estados</option>
              <option value="pendiente">Pendientes</option>
              <option value="activa">Activas</option>
              <option value="completada">Completadas</option>
              <option value="cancelada">Canceladas</option>
            </select>

            <!-- Técnico -->
            <select
              v-model="tecnicoFilter"
              @change="handleTecnicoChange($event.target.value)"
              class="px-4 py-3 border border-slate-300 rounded-xl bg-white text-slate-900 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all duration-200"
            >
              <option value="">Todos los Técnicos</option>
              <option v-for="tecnico in tecnicos" :key="tecnico.id" :value="tecnico.id">
                {{ tecnico.nombre }} {{ tecnico.apellido }}
              </option>
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
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Código</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Técnico</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Proyecto</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Herramientas</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Estado</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Fecha</th>
                <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Acciones</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="asignacion in asignacionesData" :key="asignacion.id" class="hover:bg-gray-50 transition-colors duration-150">
                <td class="px-6 py-4">
                  <div class="text-sm font-medium text-gray-900">{{ asignacion.codigo_asignacion }}</div>
                </td>
                <td class="px-6 py-4">
                  <div class="text-sm text-gray-900">
                    {{ asignacion.tecnico?.nombre }} {{ asignacion.tecnico?.apellido }}
                  </div>
                </td>
                <td class="px-6 py-4">
                  <div class="text-sm text-gray-700">{{ asignacion.proyecto_trabajo || 'Sin proyecto' }}</div>
                </td>
                <td class="px-6 py-4">
                  <div class="flex items-center gap-2">
                    <span class="text-sm font-medium text-gray-900">{{ asignacion.total_herramientas }}</span>
                    <div v-if="asignacion.herramientas_devueltas > 0" class="text-xs text-gray-500">
                      ({{ asignacion.herramientas_devueltas }} devueltas)
                    </div>
                  </div>
                  <div class="w-full bg-gray-200 rounded-full h-1.5 mt-1">
                    <div
                      class="bg-blue-600 h-1.5 rounded-full transition-all duration-300"
                      :style="{ width: asignacion.porcentaje_completado + '%' }"
                    ></div>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <span :class="getEstadoClasses(asignacion.estado)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                    {{ estadosAsignaciones[asignacion.estado]?.label || asignacion.estado }}
                  </span>
                </td>
                <td class="px-6 py-4">
                  <div class="text-sm text-gray-500">{{ formatearFecha(asignacion.fecha_asignacion) }}</div>
                </td>
                <td class="px-6 py-4 text-right">
                  <div class="flex items-center justify-end space-x-1">
                    <!-- Ver detalles -->
                    <button @click="verDetalles(asignacion)" class="w-8 h-8 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 transition-colors duration-150" title="Ver detalles">
                      <svg class="w-4 h-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                      </svg>
                    </button>

                    <!-- Autorizar (solo pendientes) -->
                    <button v-if="asignacion.estado === 'pendiente'" @click="autorizarAsignacion(asignacion)" class="w-8 h-8 bg-green-50 text-green-600 rounded-lg hover:bg-green-100 transition-colors duration-150" title="Autorizar">
                      <svg class="w-4 h-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                      </svg>
                    </button>

                    <!-- Completar (solo activas) -->
                    <button v-if="asignacion.estado === 'activa'" @click="completarAsignacion(asignacion)" class="w-8 h-8 bg-purple-50 text-purple-600 rounded-lg hover:bg-purple-100 transition-colors duration-150" title="Completar">
                      <svg class="w-4 h-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                      </svg>
                    </button>

                    <!-- Cancelar (pendientes y activas) -->
                    <button v-if="['pendiente', 'activa'].includes(asignacion.estado)" @click="cancelarAsignacion(asignacion)" class="w-8 h-8 bg-red-50 text-red-600 rounded-lg hover:bg-red-100 transition-colors duration-150" title="Cancelar">
                      <svg class="w-4 h-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="asignacionesData.length === 0">
                <td colspan="7" class="px-6 py-16 text-center">
                  <div class="flex flex-col items-center space-y-4">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center">
                      <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                      </svg>
                    </div>
                    <div class="space-y-1">
                      <p class="text-gray-700 font-medium">No hay asignaciones masivas</p>
                      <p class="text-sm text-gray-500">Las asignaciones masivas aparecerán aquí cuando se creen</p>
                    </div>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Paginación -->
        <div v-if="paginationData.last_page > 1" class="bg-white border-t border-gray-200 px-4 py-3 sm:px-6">
          <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-4">
              <p class="text-sm text-gray-700">
                Mostrando {{ paginationData.from }} - {{ paginationData.to }} de {{ paginationData.total }} resultados
              </p>
            </div>

            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
              <button
                v-if="paginationData.prev_page_url"
                @click="handlePageChange(paginationData.current_page - 1)"
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
                v-for="page in [paginationData.current_page - 1, paginationData.current_page, paginationData.current_page + 1].filter(p => p > 0 && p <= paginationData.last_page)"
                :key="page"
                @click="handlePageChange(page)"
                :class="page === paginationData.current_page ? 'bg-blue-50 border-blue-500 text-blue-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'"
                class="relative inline-flex items-center px-4 py-2 border text-sm font-medium"
              >
                {{ page }}
              </button>

              <button
                v-if="paginationData.next_page_url"
                @click="handlePageChange(paginationData.current_page + 1)"
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
    </div>
  </div>
</template>

<style scoped>
.asignaciones-masivas-index {
  min-height: 100vh;
  background-color: #f9fafb;
}
</style>