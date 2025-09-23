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
  tecnico: { type: Object, required: true },
  herramientas: { type: Object, required: true },
  asignaciones_masivas: { type: Array, default: () => [] },
  historial_reciente: { type: Array, default: () => [] },
  responsabilidad: { type: Object, default: () => null },
  estadisticas: { type: Object, default: () => ({}) },
  filters: { type: Object, default: () => ({}) }
})

// Estado
const searchTerm = ref(props.filters?.search ?? '')
const categoriaFilter = ref(props.filters?.categoria ?? '')
const estadoFilter = ref(props.filters?.estado ?? '')
const showModal = ref(false)
const modalMode = ref('herramienta')
const selectedItem = ref(null)
const showDevolucionModal = ref(false)
const herramientaADevolver = ref(null)

// Computed
const herramientasData = computed(() => props.herramientas?.data || [])
const estadisticas = computed(() => props.estadisticas)

// Estados de herramientas
const estadosHerramientas = {
  'disponible': { label: 'Disponible', color: 'green', icon: 'check-circle' },
  'asignada': { label: 'Asignada', color: 'blue', icon: 'user' },
  'mantenimiento': { label: 'Mantenimiento', color: 'yellow', icon: 'wrench' },
  'baja': { label: 'De Baja', color: 'red', icon: 'x-circle' },
  'perdida': { label: 'Perdida', color: 'red', icon: 'exclamation-triangle' }
}

// Métodos
const handleSearchChange = (newSearch) => {
  searchTerm.value = newSearch
  aplicarFiltros()
}

const handleCategoriaChange = (newCategoria) => {
  categoriaFilter.value = newCategoria
  aplicarFiltros()
}

const handleEstadoChange = (newEstado) => {
  estadoFilter.value = newEstado
  aplicarFiltros()
}

const aplicarFiltros = () => {
  router.get(route('herramientas.tecnicos-herramientas.show', props.tecnico.id), {
    search: searchTerm.value,
    categoria: categoriaFilter.value,
    estado: estadoFilter.value,
    page: 1
  }, { preserveState: true, preserveScroll: true })
}

const verDetallesHerramienta = (herramienta) => {
  selectedItem.value = herramienta
  modalMode.value = 'herramienta'
  showModal.value = true
}

const verDetallesAsignacion = (asignacion) => {
  selectedItem.value = asignacion
  modalMode.value = 'asignacion'
  showModal.value = true
}

const verDetallesHistorial = (historial) => {
  selectedItem.value = historial
  modalMode.value = 'historial'
  showModal.value = true
}

const devolverHerramienta = (herramienta) => {
  herramientaADevolver.value = herramienta
  showDevolucionModal.value = true
}

const confirmarDevolucion = (tipoDevolucion, observaciones) => {
  const herramienta = herramientaADevolver.value

  if (herramienta.info_asignacion_completa?.tipo === 'masiva') {
    // Devolver de asignación masiva
    router.post(route('herramientas.asignaciones-masivas.devolver-herramienta', [
      herramienta.info_asignacion_completa.asignacion_id,
      herramienta.id
    ]), {
      observaciones: observaciones,
      tipo_devolucion: tipoDevolucion
    }, {
      onSuccess: () => {
        notyf.success('Herramienta devuelta correctamente')
        showDevolucionModal.value = false
        router.reload()
      },
      onError: () => {
        notyf.error('Error al devolver la herramienta')
      }
    })
  } else {
    // Devolver de asignación individual
    router.post(route('herramientas.recibir', herramienta.id), {
      observaciones: observaciones
    }, {
      onSuccess: () => {
        notyf.success('Herramienta devuelta correctamente')
        showDevolucionModal.value = false
        router.reload()
      },
      onError: () => {
        notyf.error('Error al devolver la herramienta')
      }
    })
  }
}

const getEstadoClasses = (estado) => {
  const estadoInfo = estadosHerramientas[estado] || estadosHerramientas['disponible']
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
const formatCurrency = (num) => new Intl.NumberFormat('es-MX', {
  style: 'currency',
  currency: 'MXN'
}).format(num)

// Paginación
const paginationData = computed(() => ({
  current_page: props.herramientas?.current_page || 1,
  last_page: props.herramientas?.last_page || 1,
  per_page: props.herramientas?.per_page || 15,
  from: props.herramientas?.from || 0,
  to: props.herramientas?.to || 0,
  total: props.herramientas?.total || 0,
  prev_page_url: props.herramientas?.prev_page_url,
  next_page_url: props.herramientas?.next_page_url,
  links: props.herramientas?.links || []
}))

const handlePageChange = (newPage) => {
  router.get(route('herramientas.tecnicos-herramientas.show', props.tecnico.id), {
    ...props.filters,
    page: newPage
  }, { preserveState: true, preserveScroll: true })
}
</script>

<template>
  <Head :title="`Herramientas de ${tecnico.nombre} ${tecnico.apellido}`" />

  <div class="tecnico-herramientas-show min-h-screen bg-gray-50">
    <div class="max-w-8xl mx-auto px-6 py-8">
      <!-- Header -->
      <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-8 mb-6">
        <div class="flex flex-col lg:flex-row gap-8 items-start lg:items-center justify-between">
          <!-- Izquierda -->
          <div class="flex flex-col gap-6 w-full lg:w-auto">
            <div class="flex items-center gap-3">
              <Link
                :href="route('herramientas.tecnicos-herramientas.index')"
                class="inline-flex items-center gap-2 px-4 py-2 text-gray-600 hover:text-gray-900 transition-colors"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                <span>Volver</span>
              </Link>
              <div class="h-6 w-px bg-gray-300"></div>
              <h1 class="text-2xl font-bold text-slate-900">
                Herramientas de {{ tecnico.nombre }} {{ tecnico.apellido }}
              </h1>
            </div>

            <!-- Estadísticas del técnico -->
            <div class="flex flex-wrap items-center gap-4 text-sm">
              <div class="flex items-center gap-2 px-4 py-3 bg-blue-50 rounded-xl border border-blue-200">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                </svg>
                <span class="font-medium text-slate-700">Total:</span>
                <span class="font-bold text-blue-700 text-lg">{{ formatNumber(estadisticas.total_herramientas) }}</span>
              </div>

              <div class="flex items-center gap-2 px-4 py-3 bg-green-50 rounded-xl border border-green-200">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                </svg>
                <span class="font-medium text-slate-700">Valor:</span>
                <span class="font-bold text-green-700 text-lg">{{ formatCurrency(estadisticas.valor_total) }}</span>
              </div>

              <div v-if="estadisticas.herramientas_vencidas > 0" class="flex items-center gap-2 px-4 py-3 bg-red-50 rounded-xl border border-red-200">
                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                </svg>
                <span class="font-medium text-slate-700">Vencidas:</span>
                <span class="font-bold text-red-700 text-lg">{{ formatNumber(estadisticas.herramientas_vencidas) }}</span>
              </div>

              <div class="flex items-center gap-2 px-4 py-3 bg-purple-50 rounded-xl border border-purple-200">
                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span class="font-medium text-slate-700">Asign. Masivas:</span>
                <span class="font-bold text-purple-700 text-lg">{{ formatNumber(estadisticas.asignaciones_masivas_activas) }}</span>
              </div>

              <div v-if="estadisticas.promedio_dias_uso > 0" class="flex items-center gap-2 px-4 py-3 bg-gray-50 rounded-xl border border-gray-200">
                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="font-medium text-slate-700">Promedio Uso:</span>
                <span class="font-bold text-gray-700 text-lg">{{ estadisticas.promedio_dias_uso }} días</span>
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
                placeholder="Buscar herramienta..."
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
              <option value="asignada">Asignadas</option>
              <option value="mantenimiento">En Mantenimiento</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Asignaciones Masivas Activas -->
      <div v-if="asignaciones_masivas.length > 0" class="bg-white border border-slate-200 rounded-xl shadow-sm p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Asignaciones Masivas Activas</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <div
            v-for="asignacion in asignaciones_masivas"
            :key="asignacion.id"
            class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 cursor-pointer transition-colors"
            @click="verDetallesAsignacion(asignacion)"
          >
            <div class="flex items-center justify-between mb-2">
              <span class="text-sm font-medium text-gray-900">{{ asignacion.codigo_asignacion }}</span>
              <span :class="`bg-${asignacion.estado_color}-100 text-${asignacion.estado_color}-700`" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium">
                {{ asignacion.estado_label }}
              </span>
            </div>
            <div class="text-sm text-gray-600 mb-2">{{ asignacion.proyecto_trabajo || 'Sin proyecto' }}</div>
            <div class="flex items-center justify-between text-xs text-gray-500">
              <span>{{ asignacion.total_herramientas }} herramientas</span>
              <span>{{ formatearFecha(asignacion.fecha_asignacion) }}</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-1.5 mt-2">
              <div
                class="bg-blue-600 h-1.5 rounded-full transition-all duration-300"
                :style="{ width: asignacion.porcentaje_completado + '%' }"
              ></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Tabla de Herramientas -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Herramienta</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Categoría</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Estado</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Asignación</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Fecha Asignación</th>
                <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Acciones</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="herramienta in herramientasData" :key="herramienta.id" class="hover:bg-gray-50 transition-colors duration-150">
                <td class="px-6 py-4">
                  <div class="flex items-center gap-3">
                    <div v-if="herramienta.necesita_mantenimiento || herramienta.vida_util_proxima_a_vencer" class="w-2 h-2 bg-red-500 rounded-full animate-pulse" title="Necesita atención"></div>
                    <div class="flex flex-col">
                      <div class="text-sm font-medium text-gray-900">{{ herramienta.nombre }}</div>
                      <div class="text-xs text-gray-500">{{ herramienta.numero_serie }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">
                    {{ herramienta.categoria_herramienta?.nombre || 'Sin categoría' }}
                  </span>
                </td>
                <td class="px-6 py-4">
                  <span :class="getEstadoClasses(herramienta.estado)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                    {{ herramienta.estado_label }}
                  </span>
                </td>
                <td class="px-6 py-4">
                  <div class="text-sm text-gray-700">
                    <div v-if="herramienta.info_asignacion_completa">
                      <div class="font-medium">
                        {{ herramienta.info_asignacion_completa.tipo === 'masiva' ? 'Masiva' : 'Individual' }}
                      </div>
                      <div class="text-xs text-gray-500">
                        {{ herramienta.info_asignacion_completa.codigo }}
                      </div>
                      <div v-if="herramienta.info_asignacion_completa.proyecto" class="text-xs text-blue-600">
                        {{ herramienta.info_asignacion_completa.proyecto }}
                      </div>
                    </div>
                    <div v-else class="text-gray-500">Sin asignación</div>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <div class="text-sm text-gray-500">
                    {{ formatearFecha(herramienta.fecha_asignacion) }}
                  </div>
                </td>
                <td class="px-6 py-4 text-right">
                  <div class="flex items-center justify-end space-x-1">
                    <!-- Ver detalles -->
                    <button @click="verDetallesHerramienta(herramienta)" class="w-8 h-8 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 transition-colors duration-150" title="Ver detalles">
                      <svg class="w-4 h-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      </svg>
                    </button>

                    <!-- Devolver -->
                    <button @click="devolverHerramienta(herramienta)" class="w-8 h-8 bg-green-50 text-green-600 rounded-lg hover:bg-green-100 transition-colors duration-150" title="Devolver herramienta">
                      <svg class="w-4 h-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                      </svg>
                    </button>

                    <!-- Inspeccionar -->
                    <button @click="router.visit(route('herramientas.estados.create', { herramienta_id: herramienta.id }))" class="w-8 h-8 bg-yellow-50 text-yellow-600 rounded-lg hover:bg-yellow-100 transition-colors duration-150" title="Inspeccionar">
                      <svg class="w-4 h-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="herramientasData.length === 0">
                <td colspan="6" class="px-6 py-16 text-center">
                  <div class="flex flex-col items-center space-y-4">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center">
                      <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                      </svg>
                    </div>
                    <div class="space-y-1">
                      <p class="text-gray-700 font-medium">No hay herramientas asignadas</p>
                      <p class="text-sm text-gray-500">Este técnico no tiene herramientas asignadas actualmente</p>
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
            </nav>
          </div>
        </div>
      </div>

      <!-- Historial Reciente -->
      <div v-if="historial_reciente.length > 0" class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Historial Reciente</h2>
        <div class="space-y-3">
          <div
            v-for="historial in historial_reciente.slice(0, 5)"
            :key="historial.id"
            class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 cursor-pointer transition-colors"
            @click="verDetallesHistorial(historial)"
          >
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                </svg>
              </div>
              <div>
                <div class="text-sm font-medium text-gray-900">{{ historial.herramienta?.nombre }}</div>
                <div class="text-xs text-gray-500">{{ historial.herramienta?.numero_serie }}</div>
              </div>
            </div>
            <div class="text-right">
              <div class="text-sm text-gray-700">{{ formatearFecha(historial.fecha_asignacion) }}</div>
              <div v-if="historial.fecha_devolucion" class="text-xs text-green-600">
                Devuelta: {{ formatearFecha(historial.fecha_devolucion) }}
              </div>
              <div v-else class="text-xs text-blue-600">Activa</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Modal de devolución -->
      <div v-if="showDevolucionModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" @click.self="showDevolucionModal = false">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-lg">
          <div class="flex items-center justify-between p-6 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Devolver Herramienta</h3>
            <button @click="showDevolucionModal = false" class="text-gray-400 hover:text-gray-600 transition-colors">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <div class="p-6">
            <div v-if="herramientaADevolver" class="space-y-4">
              <div class="bg-gray-50 rounded-lg p-4">
                <h4 class="font-medium text-gray-900">{{ herramientaADevolver.nombre }}</h4>
                <p class="text-sm text-gray-600">{{ herramientaADevolver.numero_serie }}</p>
              </div>

              <div class="space-y-3">
                <button
                  @click="confirmarDevolucion('normal', 'Devolución normal')"
                  class="w-full flex items-center gap-3 p-3 bg-green-50 border border-green-200 rounded-lg hover:bg-green-100 transition-colors"
                >
                  <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  <div class="text-left">
                    <div class="font-medium text-green-800">Devolución Normal</div>
                    <div class="text-sm text-green-600">La herramienta está en buen estado</div>
                  </div>
                </button>

                <button
                  @click="confirmarDevolucion('dañada', 'Herramienta dañada')"
                  class="w-full flex items-center gap-3 p-3 bg-orange-50 border border-orange-200 rounded-lg hover:bg-orange-100 transition-colors"
                >
                  <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                  </svg>
                  <div class="text-left">
                    <div class="font-medium text-orange-800">Herramienta Dañada</div>
                    <div class="text-sm text-orange-600">Requiere mantenimiento o reparación</div>
                  </div>
                </button>

                <button
                  @click="confirmarDevolucion('perdida', 'Herramienta perdida')"
                  class="w-full flex items-center gap-3 p-3 bg-red-50 border border-red-200 rounded-lg hover:bg-red-100 transition-colors"
                >
                  <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  <div class="text-left">
                    <div class="font-medium text-red-800">Herramienta Perdida</div>
                    <div class="text-sm text-red-600">La herramienta se ha extraviado</div>
                  </div>
                </button>
              </div>
            </div>
          </div>

          <div class="flex justify-end gap-3 px-6 py-4 border-t border-gray-200 bg-gray-50">
            <button @click="showDevolucionModal = false" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors">
              Cancelar
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.tecnico-herramientas-show {
  min-height: 100vh;
  background-color: #f9fafb;
}
</style>
