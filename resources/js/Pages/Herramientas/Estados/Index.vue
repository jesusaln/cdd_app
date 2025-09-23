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
  estados: { type: Array, default: () => [] },
  herramientas: { type: Array, default: () => [] },
  tecnicos: { type: Array, default: () => [] },
  filters: { type: Object, default: () => ({}) }
})

// Estado
const search = ref('')
const condicionFilter = ref('')
const prioridadFilter = ref('')
const currentPage = ref(1)
const perPage = ref(10)

// Computed
const filteredEstados = computed(() => {
  let filtered = props.estados

  // Filtro por búsqueda
  if (search.value) {
    filtered = filtered.filter(estado => {
      const herramienta = props.herramientas.find(h => h.id === estado.herramienta_id)
      const searchTerm = search.value.toLowerCase()

      return (
        herramienta?.nombre.toLowerCase().includes(searchTerm) ||
        herramienta?.numero_serie.toLowerCase().includes(searchTerm) ||
        estado.condicion_general?.toLowerCase().includes(searchTerm) ||
        estado.observaciones?.toLowerCase().includes(searchTerm)
      )
    })
  }

  // Filtro por condición
  if (condicionFilter.value) {
    filtered = filtered.filter(estado => estado.condicion_general === condicionFilter.value)
  }

  // Filtro por prioridad
  if (prioridadFilter.value) {
    filtered = filtered.filter(estado => estado.prioridad_mantenimiento === prioridadFilter.value)
  }

  return filtered
})

const paginatedEstados = computed(() => {
  const start = (currentPage.value - 1) * perPage.value
  const end = start + perPage.value
  return filteredEstados.value.slice(start, end)
})

const totalPages = computed(() => {
  return Math.ceil(filteredEstados.value.length / perPage.value)
})

const estadisticas = computed(() => {
  const total = props.estados.length
  const excelente = props.estados.filter(e => e.condicion_general === 'excelente').length
  const buena = props.estados.filter(e => e.condicion_general === 'buena').length
  const regular = props.estados.filter(e => e.condicion_general === 'regular').length
  const mala = props.estados.filter(e => e.condicion_general === 'mala').length
  const critica = props.estados.filter(e => e.condicion_general === 'critica').length

  const alta = props.estados.filter(e => e.prioridad_mantenimiento === 'alta').length
  const media = props.estados.filter(e => e.prioridad_mantenimiento === 'media').length
  const baja = props.estados.filter(e => e.prioridad_mantenimiento === 'baja').length

  return { total, excelente, buena, regular, mala, critica, alta, media, baja }
})

// Métodos
const getHerramientaNombre = (herramientaId) => {
  const herramienta = props.herramientas.find(h => h.id === herramientaId)
  return herramienta ? `${herramienta.nombre} (${herramienta.numero_serie})` : 'N/A'
}

const getCondicionBadgeClass = (condicion) => {
  const classes = {
    'excelente': 'bg-green-100 text-green-700',
    'buena': 'bg-blue-100 text-blue-700',
    'regular': 'bg-yellow-100 text-yellow-700',
    'mala': 'bg-orange-100 text-orange-700',
    'critica': 'bg-red-100 text-red-700'
  }
  return classes[condicion] || 'bg-gray-100 text-gray-700'
}

const getPrioridadBadgeClass = (prioridad) => {
  const classes = {
    'alta': 'bg-red-100 text-red-700',
    'media': 'bg-yellow-100 text-yellow-700',
    'baja': 'bg-green-100 text-green-700'
  }
  return classes[prioridad] || 'bg-gray-100 text-gray-700'
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

const formatPercentage = (value) => {
  return `${value}%`
}

const clearFilters = () => {
  search.value = ''
  condicionFilter.value = ''
  prioridadFilter.value = ''
  currentPage.value = 1
}

const changePage = (page) => {
  currentPage.value = page
}

const viewEstado = (estado) => {
  router.visit(route('herramientas.estados.show', estado.id))
}

const createEstado = () => {
  router.visit(route('herramientas.estados.create'))
}

const getCondicionLabel = (condicion) => {
  const labels = {
    'excelente': 'Excelente',
    'buena': 'Buena',
    'regular': 'Regular',
    'mala': 'Mala',
    'critica': 'Crítica'
  }
  return labels[condicion] || condicion
}

const getPrioridadLabel = (prioridad) => {
  const labels = {
    'alta': 'Alta',
    'media': 'Media',
    'baja': 'Baja'
  }
  return labels[prioridad] || prioridad
}
</script>

<template>
  <Head title="Estados de Herramientas" />

  <div class="estados-index min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-6 py-8">
      <!-- Header -->
      <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6 mb-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
          <div class="flex items-center gap-3">
            <h1 class="text-2xl font-bold text-slate-900">Estados de Herramientas</h1>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-700">
              {{ estadisticas.total }} total
            </span>
          </div>

          <div class="flex gap-3">
            <button
              @click="createEstado"
              class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
              </svg>
              <span>Nueva Inspección</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Estadísticas -->
      <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4 mb-6">
        <div class="bg-white border border-slate-200 rounded-xl p-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600">Excelente</p>
              <p class="text-2xl font-bold text-green-600">{{ estadisticas.excelente }}</p>
            </div>
            <div class="p-2 bg-green-100 rounded-lg">
              <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
            </div>
          </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl p-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600">Buena</p>
              <p class="text-2xl font-bold text-blue-600">{{ estadisticas.buena }}</p>
            </div>
            <div class="p-2 bg-blue-100 rounded-lg">
              <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
          </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl p-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600">Regular</p>
              <p class="text-2xl font-bold text-yellow-600">{{ estadisticas.regular }}</p>
            </div>
            <div class="p-2 bg-yellow-100 rounded-lg">
              <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
              </svg>
            </div>
          </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl p-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600">Mala</p>
              <p class="text-2xl font-bold text-orange-600">{{ estadisticas.mala }}</p>
            </div>
            <div class="p-2 bg-orange-100 rounded-lg">
              <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
          </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl p-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600">Crítica</p>
              <p class="text-2xl font-bold text-red-600">{{ estadisticas.critica }}</p>
            </div>
            <div class="p-2 bg-red-100 rounded-lg">
              <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
              </svg>
            </div>
          </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl p-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600">Alta Prioridad</p>
              <p class="text-2xl font-bold text-red-600">{{ estadisticas.alta }}</p>
            </div>
            <div class="p-2 bg-red-100 rounded-lg">
              <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
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
              placeholder="Buscar por herramienta o observaciones..."
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
          </div>

          <!-- Filtro por Condición -->
          <div class="w-full lg:w-48">
            <label class="block text-sm font-medium text-gray-700 mb-2">Condición</label>
            <select
              v-model="condicionFilter"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="">Todas las condiciones</option>
              <option value="excelente">Excelente</option>
              <option value="buena">Buena</option>
              <option value="regular">Regular</option>
              <option value="mala">Mala</option>
              <option value="critica">Crítica</option>
            </select>
          </div>

          <!-- Filtro por Prioridad -->
          <div class="w-full lg:w-48">
            <label class="block text-sm font-medium text-gray-700 mb-2">Prioridad</label>
            <select
              v-model="prioridadFilter"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="">Todas las prioridades</option>
              <option value="alta">Alta</option>
              <option value="media">Media</option>
              <option value="baja">Baja</option>
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
                  Condición
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Desgaste
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Prioridad
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
                v-for="estado in paginatedEstados"
                :key="estado.id"
                class="hover:bg-gray-50"
              >
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-900">
                    {{ getHerramientaNombre(estado.herramienta_id) }}
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="getCondicionBadgeClass(estado.condicion_general)" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium">
                    {{ getCondicionLabel(estado.condicion_general) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                      <div
                        :class="[
                          'h-2 rounded-full',
                          estado.porcentaje_desgaste >= 80 ? 'bg-red-500' :
                          estado.porcentaje_desgaste >= 60 ? 'bg-orange-500' :
                          estado.porcentaje_desgaste >= 40 ? 'bg-yellow-500' :
                          estado.porcentaje_desgaste >= 20 ? 'bg-blue-500' : 'bg-green-500'
                        ]"
                        :style="{ width: `${estado.porcentaje_desgaste}%` }"
                      ></div>
                    </div>
                    <span class="text-sm text-gray-900">{{ formatPercentage(estado.porcentaje_desgaste) }}</span>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="getPrioridadBadgeClass(estado.prioridad_mantenimiento)" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium">
                    {{ getPrioridadLabel(estado.prioridad_mantenimiento) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ formatDate(estado.created_at) }}
                </td>
                <td class="px-6 py-4">
                  <div class="text-sm text-gray-900 max-w-xs truncate">
                    {{ estado.observaciones || 'Sin observaciones' }}
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <button
                    @click="viewEstado(estado)"
                    class="text-blue-600 hover:text-blue-900 mr-3"
                  >
                    Ver
                  </button>
                </td>
              </tr>

              <!-- Estado vacío -->
              <tr v-if="paginatedEstados.length === 0">
                <td colspan="7" class="px-6 py-12 text-center">
                  <div class="flex flex-col items-center">
                    <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="text-sm font-medium text-gray-900 mb-1">No hay inspecciones</h3>
                    <p class="text-sm text-gray-500">
                      {{ filteredEstados.length === 0 && (search || condicionFilter || prioridadFilter) ?
                        'No se encontraron inspecciones con los filtros aplicados.' :
                        'Comienza creando una nueva inspección de herramienta.' }}
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
                <span class="font-medium">{{ Math.min(currentPage * perPage, filteredEstados.length) }}</span>
                de
                <span class="font-medium">{{ filteredEstados.length }}</span>
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
.estados-index {
  min-height: 100vh;
  background-color: #f9fafb;
}
</style>
