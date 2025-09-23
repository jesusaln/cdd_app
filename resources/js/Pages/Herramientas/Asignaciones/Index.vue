<script setup>
import { ref, computed, onMounted } from 'vue'
import { Head, router, usePage } from '@inertiajs/vue3'
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
  asignaciones: { type: [Object, Array], default: () => ({}) },
  herramientas: { type: Array, default: () => [] },
  tecnicos: { type: Array, default: () => [] },
  stats: { type: Object, default: () => ({}) },
  filters: { type: Object, default: () => ({}) }
})

// Estado
const search = ref('')
const tipoFilter = ref('')
const estadoFilter = ref('')
const currentPage = ref(1)
const perPage = ref(10)

// Computed
const asignacionesData = computed(() => {
  // Si asignaciones es un objeto paginado, usar los datos
  if (props.asignaciones && props.asignaciones.data) {
    return props.asignaciones.data
  }
  // Si asignaciones es un array directo, usarlo
  return Array.isArray(props.asignaciones) ? props.asignaciones : []
})

const filteredAsignaciones = computed(() => {
  let filtered = asignacionesData.value || []

  // Filtro por búsqueda
  if (search.value) {
    filtered = filtered.filter(asignacion => {
      const herramienta = props.herramientas?.find(h => h.id === asignacion.herramienta_id)
      const tecnico = props.tecnicos?.find(t => t.id === asignacion.tecnico_id)
      const searchTerm = search.value.toLowerCase()

      return (
        (herramienta?.nombre || '').toLowerCase().includes(searchTerm) ||
        (herramienta?.numero_serie || '').toLowerCase().includes(searchTerm) ||
        (tecnico?.nombre || '').toLowerCase().includes(searchTerm) ||
        (tecnico?.apellido || '').toLowerCase().includes(searchTerm) ||
        (asignacion.observaciones_entrega || '').toLowerCase().includes(searchTerm) ||
        (asignacion.observaciones_recepcion || '').toLowerCase().includes(searchTerm)
      )
    })
  }

  // Filtro por tipo
  if (tipoFilter.value) {
    filtered = filtered.filter(asignacion => asignacion.tipo_asignacion === tipoFilter.value)
  }

  // Filtro por estado
  if (estadoFilter.value) {
    filtered = filtered.filter(asignacion => asignacion.activo === (estadoFilter.value === 'activa'))
  }

  return filtered
})

const paginatedAsignaciones = computed(() => {
  const start = (currentPage.value - 1) * perPage.value
  const end = start + perPage.value
  return filteredAsignaciones.value.slice(start, end)
})

const totalPages = computed(() => {
  return Math.ceil(filteredAsignaciones.value.length / perPage.value)
})

const estadisticas = computed(() => {
  // Usar estadísticas del controlador si están disponibles
  if (props.stats && Object.keys(props.stats).length > 0) {
    return {
      total: props.stats.total || 0,
      entregas: props.stats.entregas || 0,
      recepciones: props.stats.recepciones || 0,
      activas: props.stats.activas || 0
    }
  }

  // Calcular desde los datos si no hay stats del controlador
  const total = asignacionesData.value.length
  const entregas = asignacionesData.value.filter(a => a.tipo_asignacion === 'entrega').length
  const recepciones = asignacionesData.value.filter(a => a.tipo_asignacion === 'recepcion').length
  const activas = asignacionesData.value.filter(a => a.activo === true).length

  return { total, entregas, recepciones, activas }
})

// Métodos
const getHerramientaNombre = (herramientaId) => {
  if (!props.herramientas || !Array.isArray(props.herramientas)) return 'Herramienta no encontrada'
  const herramienta = props.herramientas.find(h => h.id === herramientaId)
  return herramienta ? `${herramienta.nombre} (${herramienta.numero_serie})` : 'Herramienta no encontrada'
}

const getTecnicoNombre = (tecnicoId) => {
  if (!props.tecnicos || !Array.isArray(props.tecnicos)) return 'Técnico no encontrado'
  const tecnico = props.tecnicos.find(t => t.id === tecnicoId)
  return tecnico ? `${tecnico.nombre} ${tecnico.apellido}` : 'Técnico no encontrado'
}

const getEstadoBadgeClass = (estado) => {
  const classes = {
    'activa': 'bg-green-100 text-green-700',
    'completada': 'bg-blue-100 text-blue-700',
    'cancelada': 'bg-red-100 text-red-700'
  }
  return classes[estado] || 'bg-gray-100 text-gray-700'
}

const getTipoBadgeClass = (tipo) => {
  const classes = {
    'entrega': 'bg-blue-100 text-blue-700',
    'recepcion': 'bg-purple-100 text-purple-700'
  }
  return classes[tipo] || 'bg-gray-100 text-gray-700'
}

const formatDate = (date) => {
  if (!date) return 'N/A'
  return new Date(date).toLocaleDateString('es-MX', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const clearFilters = () => {
  search.value = ''
  tipoFilter.value = ''
  estadoFilter.value = ''
  currentPage.value = 1
}

const changePage = (page) => {
  currentPage.value = page
}

const viewAsignacion = (asignacion) => {
  router.visit(route('herramientas.asignaciones.show', asignacion.id))
}

const createAsignacion = (tipo) => {
  router.visit(route('herramientas.asignaciones.create', { tipo }))
}

// Estados de herramientas
const estadosHerramientas = {
  'excelente': { label: 'Excelente', color: 'green' },
  'buena': { label: 'Buena', color: 'blue' },
  'regular': { label: 'Regular', color: 'yellow' },
  'mala': { label: 'Mala', color: 'orange' },
  'critica': { label: 'Crítica', color: 'red' }
}
</script>

<template>
  <Head title="Asignaciones de Herramientas" />

  <div class="asignaciones-index min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-6 py-8">
      <!-- Header -->
      <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6 mb-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
          <div class="flex items-center gap-3">
            <h1 class="text-2xl font-bold text-slate-900">Asignaciones de Herramientas</h1>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-700">
              {{ estadisticas.total }} total
            </span>
            <!-- Debug info -->
            <span v-if="false" class="text-xs text-gray-500 ml-2">
              Debug: asignaciones={{ asignacionesData.length }}, herramientas={{ props.herramientas.length }}, tecnicos={{ props.tecnicos.length }}
            </span>
          </div>

          <div class="flex flex-col sm:flex-row gap-3">
            <button
              @click="createAsignacion('entrega')"
              class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
              </svg>
              <span>Nueva Entrega</span>
            </button>
            <button
              @click="createAsignacion('recepcion')"
              class="inline-flex items-center gap-2 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M9 21h6m-6 0v-4m6 4v-4" />
              </svg>
              <span>Nueva Recepción</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Estadísticas -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white border border-slate-200 rounded-xl p-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600">Total Asignaciones</p>
              <p class="text-2xl font-bold text-gray-900">{{ estadisticas.total }}</p>
            </div>
            <div class="p-2 bg-blue-100 rounded-lg">
              <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
            </div>
          </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl p-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600">Entregas</p>
              <p class="text-2xl font-bold text-blue-600">{{ estadisticas.entregas }}</p>
            </div>
            <div class="p-2 bg-blue-100 rounded-lg">
              <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
              </svg>
            </div>
          </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl p-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600">Recepciones</p>
              <p class="text-2xl font-bold text-purple-600">{{ estadisticas.recepciones }}</p>
            </div>
            <div class="p-2 bg-purple-100 rounded-lg">
              <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M9 21h6m-6 0v-4m6 4v-4" />
              </svg>
            </div>
          </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl p-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600">Activas</p>
              <p class="text-2xl font-bold text-green-600">{{ estadisticas.activas }}</p>
            </div>
            <div class="p-2 bg-green-100 rounded-lg">
              <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
          </div>
        </div>
      </div>

      <!-- Filtros -->
      <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6 mb-6">
        <div class="flex flex-col lg:flex-row gap-4">
          <!-- Búsqueda -->
          <div class="flex-1">
            <label class="block text-sm font-medium text-gray-700 mb-2">Buscar</label>
            <input
              v-model="search"
              type="text"
              placeholder="Buscar por herramienta, técnico o observaciones..."
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
          </div>

          <!-- Filtro por Tipo -->
          <div class="w-full lg:w-48">
            <label class="block text-sm font-medium text-gray-700 mb-2">Tipo</label>
            <select
              v-model="tipoFilter"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="">Todos los tipos</option>
              <option value="entrega">Entregas</option>
              <option value="recepcion">Recepciones</option>
            </select>
          </div>

          <!-- Filtro por Estado -->
          <div class="w-full lg:w-48">
            <label class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
            <select
              v-model="estadoFilter"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="">Todos los estados</option>
              <option value="activa">Activa</option>
              <option value="completada">Completada</option>
              <option value="cancelada">Cancelada</option>
            </select>
          </div>

          <!-- Botón Limpiar Filtros -->
          <div class="flex items-end">
            <button
              @click="clearFilters"
              class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors"
            >
              Limpiar
            </button>
          </div>
        </div>
      </div>

      <!-- Tabla -->
      <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Herramienta
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Técnico
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Tipo
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Estado
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Fecha
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Observaciones
                </th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Acciones
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr
                v-for="asignacion in paginatedAsignaciones"
                :key="asignacion.id"
                class="hover:bg-gray-50"
              >
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-900">
                    {{ getHerramientaNombre(asignacion.herramienta_id) || 'Herramienta no encontrada' }}
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">
                    {{ getTecnicoNombre(asignacion.tecnico_id) || 'Técnico no encontrado' }}
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="getTipoBadgeClass(asignacion.tipo_asignacion)" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium">
                    {{ asignacion.tipo_asignacion === 'entrega' ? 'Entrega' : 'Recepción' }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="getEstadoBadgeClass(asignacion.estado)" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium">
                    {{ asignacion.estado }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ formatDate(asignacion.created_at) }}
                </td>
                <td class="px-6 py-4">
                  <div class="text-sm text-gray-900 max-w-xs truncate">
                    {{ asignacion.observaciones_entrega || asignacion.observaciones_recepcion || 'Sin observaciones' }}
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <button
                    @click="viewAsignacion(asignacion)"
                    class="text-blue-600 hover:text-blue-900 mr-3"
                  >
                    Ver
                  </button>
                </td>
              </tr>

              <!-- Estado vacío -->
              <tr v-if="paginatedAsignaciones.length === 0">
                <td colspan="7" class="px-6 py-12 text-center">
                  <div class="flex flex-col items-center">
                    <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="text-sm font-medium text-gray-900 mb-1">No hay asignaciones</h3>
                    <p class="text-sm text-gray-500">
                      {{ filteredAsignaciones.length === 0 && (search || tipoFilter || estadoFilter) ?
                        'No se encontraron asignaciones con los filtros aplicados.' :
                        'Comienza creando una nueva asignación de herramienta.' }}
                    </p>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Paginación -->
        <div v-if="totalPages > 1" class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200">
          <div class="flex-1 flex justify-between sm:hidden">
            <button
              @click="changePage(currentPage - 1)"
              :disabled="currentPage === 1"
              class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:bg-gray-100 disabled:text-gray-400 disabled:cursor-not-allowed"
            >
              Anterior
            </button>
            <button
              @click="changePage(currentPage + 1)"
              :disabled="currentPage === totalPages"
              class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:bg-gray-100 disabled:text-gray-400 disabled:cursor-not-allowed"
            >
              Siguiente
            </button>
          </div>

          <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
              <p class="text-sm text-gray-700">
                Mostrando
                <span class="font-medium">{{ (currentPage - 1) * perPage + 1 }}</span>
                a
                <span class="font-medium">{{ Math.min(currentPage * perPage, filteredAsignaciones.length) }}</span>
                de
                <span class="font-medium">{{ filteredAsignaciones.length }}</span>
                resultados
              </p>
            </div>

            <div>
              <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                <button
                  v-for="page in totalPages"
                  :key="page"
                  @click="changePage(page)"
                  :class="[
                    'relative inline-flex items-center px-4 py-2 border text-sm font-medium',
                    page === currentPage
                      ? 'z-10 bg-blue-50 border-blue-500 text-blue-600'
                      : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'
                  ]"
                >
                  {{ page }}
                </button>
              </nav>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.asignaciones-index {
  min-height: 100vh;
  background-color: #f9fafb;
}
</style>
