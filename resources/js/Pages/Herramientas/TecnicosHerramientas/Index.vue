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
  tecnicos: { type: Array, required: true },
  stats: { type: Object, default: () => ({}) },
  filters: { type: Object, default: () => ({}) }
})

// Estado
const searchTerm = ref(props.filters?.search ?? '')
const showModal = ref(false)
const selectedTecnico = ref(null)

// Computed
const estadisticas = computed(() => props.stats)

// Métodos
const handleSearchChange = (newSearch) => {
  searchTerm.value = newSearch
  router.get(route('herramientas.tecnicos-herramientas.index'), {
    search: newSearch
  }, { preserveState: true, preserveScroll: true })
}

const verHerramientasTecnico = (tecnico) => {
  router.visit(route('herramientas.tecnicos-herramientas.show', tecnico.id))
}

const verDetallesTecnico = (tecnico) => {
  selectedTecnico.value = tecnico
  showModal.value = true
}

const actualizarResponsabilidad = (tecnico) => {
  router.post(route('herramientas.tecnicos-herramientas.actualizar-responsabilidad', tecnico.id), {}, {
    onSuccess: () => {
      notyf.success('Responsabilidades actualizadas correctamente')
      router.reload()
    },
    onError: () => {
      notyf.error('Error al actualizar responsabilidades')
    }
  })
}

const generarReporte = (tecnico) => {
  router.visit(route('herramientas.tecnicos-herramientas.reporte', tecnico.id))
}

const formatNumber = (num) => new Intl.NumberFormat('es-ES').format(num)
const formatCurrency = (num) => new Intl.NumberFormat('es-MX', {
  style: 'currency',
  currency: 'MXN'
}).format(num)

const formatearFecha = (fecha) => {
  if (!fecha) return 'N/A'
  return new Date(fecha).toLocaleDateString('es-MX', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const getAlertClass = (tecnico) => {
  if (tecnico.herramientas_vencidas > 0) return 'border-l-4 border-red-500 bg-red-50'
  if (tecnico.alertas && tecnico.alertas.length > 0) return 'border-l-4 border-yellow-500 bg-yellow-50'
  return 'border-l-4 border-green-500 bg-white'
}

const getTipoAlerta = (tecnico) => {
  if (tecnico.herramientas_vencidas > 0) return 'error'
  if (tecnico.alertas && tecnico.alertas.length > 0) return 'warning'
  return 'success'
}
</script>

<template>
  <Head title="Herramientas por Técnico" />

  <div class="tecnicos-herramientas-index min-h-screen bg-gray-50">
    <div class="max-w-8xl mx-auto px-6 py-8">
      <!-- Header -->
      <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-8 mb-6">
        <div class="flex flex-col lg:flex-row gap-8 items-start lg:items-center justify-between">
          <!-- Izquierda -->
          <div class="flex flex-col gap-6 w-full lg:w-auto">
            <div class="flex items-center gap-3">
              <h1 class="text-2xl font-bold text-slate-900">Control de Herramientas por Técnico</h1>
              <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-700">
                {{ formatNumber(estadisticas.total_tecnicos) }} técnicos
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
                :href="route('herramientas.tecnicos-herramientas.alertas')"
                class="inline-flex items-center gap-2 px-4 py-3 bg-red-50 text-red-700 rounded-xl hover:bg-red-100 transition-all duration-200 border border-red-200"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                </svg>
                <span class="text-sm font-medium">Ver Alertas</span>
              </Link>

              <Link
                :href="route('herramientas.asignaciones-masivas.index')"
                class="inline-flex items-center gap-2 px-4 py-3 bg-purple-50 text-purple-700 rounded-xl hover:bg-purple-100 transition-all duration-200 border border-purple-200"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span class="text-sm font-medium">Asignaciones Masivas</span>
              </Link>
            </div>

            <!-- Estadísticas -->
            <div class="flex flex-wrap items-center gap-4 text-sm">
              <div class="flex items-center gap-2 px-4 py-3 bg-slate-50 rounded-xl border border-slate-200">
                <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <span class="font-medium text-slate-700">Total Técnicos:</span>
                <span class="font-bold text-slate-900 text-lg">{{ formatNumber(estadisticas.total_tecnicos) }}</span>
              </div>

              <div class="flex items-center gap-2 px-4 py-3 bg-green-50 rounded-xl border border-green-200">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                </svg>
                <span class="font-medium text-slate-700">Con Herramientas:</span>
                <span class="font-bold text-green-700 text-lg">{{ formatNumber(estadisticas.tecnicos_con_herramientas) }}</span>
              </div>

              <div class="flex items-center gap-2 px-4 py-3 bg-blue-50 rounded-xl border border-blue-200">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                </svg>
                <span class="font-medium text-slate-700">Herramientas Asignadas:</span>
                <span class="font-bold text-blue-700 text-lg">{{ formatNumber(estadisticas.total_herramientas_asignadas) }}</span>
              </div>

              <div class="flex items-center gap-2 px-4 py-3 bg-orange-50 rounded-xl border border-orange-200">
                <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                </svg>
                <span class="font-medium text-slate-700">Valor Total:</span>
                <span class="font-bold text-orange-700 text-lg">{{ formatCurrency(estadisticas.valor_total_asignado) }}</span>
              </div>

              <div v-if="estadisticas.tecnicos_con_alertas > 0" class="flex items-center gap-2 px-4 py-3 bg-red-50 rounded-xl border border-red-200">
                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                </svg>
                <span class="font-medium text-slate-700">Con Alertas:</span>
                <span class="font-bold text-red-700 text-lg">{{ formatNumber(estadisticas.tecnicos_con_alertas) }}</span>
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
                placeholder="Buscar técnico..."
                class="w-full sm:w-64 lg:w-80 pl-4 pr-10 py-3 border border-slate-300 rounded-xl bg-white text-slate-900 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all duration-200"
              />
              <svg class="absolute right-3 top-3.5 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </div>
          </div>
        </div>
      </div>

      <!-- Grid de Técnicos -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <div
          v-for="tecnico in tecnicos"
          :key="tecnico.id"
          :class="getAlertClass(tecnico)"
          class="rounded-xl shadow-sm overflow-hidden transition-all duration-200 hover:shadow-md"
        >
          <!-- Header de la tarjeta -->
          <div class="p-6">
            <div class="flex items-start justify-between">
              <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                  <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                  </svg>
                </div>
                <div>
                  <h3 class="text-lg font-semibold text-gray-900">
                    {{ tecnico.nombre }} {{ tecnico.apellido }}
                  </h3>
                  <p class="text-sm text-gray-500">{{ tecnico.email }}</p>
                </div>
              </div>

              <!-- Indicador de alertas -->
              <div v-if="tecnico.herramientas_vencidas > 0" class="w-3 h-3 bg-red-500 rounded-full animate-pulse" title="Tiene herramientas vencidas"></div>
              <div v-else-if="tecnico.alertas && tecnico.alertas.length > 0" class="w-3 h-3 bg-yellow-500 rounded-full animate-pulse" title="Tiene alertas"></div>
            </div>

            <!-- Estadísticas del técnico -->
            <div class="mt-6 space-y-4">
              <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600">Herramientas Asignadas</span>
                <span class="text-lg font-bold text-gray-900">{{ tecnico.total_herramientas }}</span>
              </div>

              <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600">Valor Total</span>
                <span class="text-sm font-semibold text-green-600">{{ formatCurrency(tecnico.valor_total) }}</span>
              </div>

              <div v-if="tecnico.herramientas_vencidas > 0" class="flex items-center justify-between">
                <span class="text-sm text-red-600">Herramientas Vencidas</span>
                <span class="text-sm font-bold text-red-600">{{ tecnico.herramientas_vencidas }}</span>
              </div>

              <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600">Asignaciones Activas</span>
                <span class="text-sm font-semibold text-blue-600">{{ tecnico.asignaciones_activas }}</span>
              </div>

              <div v-if="tecnico.dias_promedio_uso > 0" class="flex items-center justify-between">
                <span class="text-sm text-gray-600">Promedio de Uso</span>
                <span class="text-sm text-gray-700">{{ tecnico.dias_promedio_uso }} días</span>
              </div>
            </div>

            <!-- Preview de herramientas -->
            <div v-if="tecnico.herramientas_preview && tecnico.herramientas_preview.length > 0" class="mt-6">
              <h4 class="text-sm font-medium text-gray-700 mb-3">Herramientas Recientes</h4>
              <div class="space-y-2">
                <div
                  v-for="herramienta in tecnico.herramientas_preview"
                  :key="herramienta.id"
                  class="flex items-center gap-2 p-2 bg-gray-50 rounded-lg"
                >
                  <div v-if="herramienta.necesita_atencion" class="w-2 h-2 bg-red-500 rounded-full" title="Necesita atención"></div>
                  <div class="flex-1 min-w-0">
                    <div class="text-xs font-medium text-gray-900 truncate">{{ herramienta.nombre }}</div>
                    <div class="text-xs text-gray-500 truncate">{{ herramienta.numero_serie }}</div>
                  </div>
                  <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-700">
                    {{ herramienta.categoria }}
                  </span>
                </div>
              </div>
              <div v-if="tecnico.total_herramientas > 3" class="text-xs text-gray-500 mt-2 text-center">
                +{{ tecnico.total_herramientas - 3 }} más...
              </div>
            </div>

            <!-- Última actualización -->
            <div v-if="tecnico.ultima_actualizacion" class="mt-4 pt-4 border-t border-gray-200">
              <div class="text-xs text-gray-500">
                Última actualización: {{ formatearFecha(tecnico.ultima_actualizacion) }}
              </div>
            </div>
          </div>

          <!-- Footer de la tarjeta -->
          <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
            <div class="flex items-center justify-between gap-2">
              <button
                @click="verDetallesTecnico(tecnico)"
                class="flex-1 inline-flex items-center justify-center gap-1 px-3 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 transition-colors"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span>Detalles</span>
              </button>

              <button
                @click="verHerramientasTecnico(tecnico)"
                class="flex-1 inline-flex items-center justify-center gap-1 px-3 py-2 bg-purple-600 text-white text-sm rounded-lg hover:bg-purple-700 transition-colors"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                </svg>
                <span>Herramientas</span>
              </button>

              <button
                @click="actualizarResponsabilidad(tecnico)"
                class="w-10 h-10 bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 transition-colors"
                title="Actualizar responsabilidades"
              >
                <svg class="w-4 h-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
              </button>
            </div>
          </div>
        </div>

        <!-- Estado vacío -->
        <div v-if="tecnicos.length === 0" class="col-span-full">
          <div class="bg-white rounded-xl border border-gray-200 p-12 text-center">
            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
              <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>
            </div>
            <div class="space-y-1">
              <p class="text-gray-700 font-medium">No hay técnicos con herramientas</p>
              <p class="text-sm text-gray-500">Los técnicos con herramientas asignadas aparecerán aquí</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Modal de detalles del técnico -->
      <div v-if="showModal && selectedTecnico" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" @click.self="showModal = false">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
          <!-- Header del modal -->
          <div class="flex items-center justify-between p-6 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">
              Resumen de {{ selectedTecnico.nombre }} {{ selectedTecnico.apellido }}
            </h3>
            <button @click="showModal = false" class="text-gray-400 hover:text-gray-600 transition-colors">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <div class="p-6">
            <div class="space-y-6">
              <!-- Información básica -->
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700">Email</label>
                  <p class="mt-1 text-sm text-gray-900">{{ selectedTecnico.email || 'N/A' }}</p>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700">Teléfono</label>
                  <p class="mt-1 text-sm text-gray-900">{{ selectedTecnico.telefono || 'N/A' }}</p>
                </div>
              </div>

              <!-- Estadísticas de herramientas -->
              <div class="bg-gray-50 rounded-lg p-4">
                <h4 class="text-md font-medium text-gray-900 mb-3">Estadísticas de Herramientas</h4>
                <div class="grid grid-cols-2 gap-4">
                  <div class="text-center">
                    <div class="text-2xl font-bold text-blue-600">{{ selectedTecnico.total_herramientas }}</div>
                    <div class="text-sm text-gray-600">Total Asignadas</div>
                  </div>
                  <div class="text-center">
                    <div class="text-2xl font-bold text-green-600">{{ formatCurrency(selectedTecnico.valor_total) }}</div>
                    <div class="text-sm text-gray-600">Valor Total</div>
                  </div>
                  <div class="text-center">
                    <div class="text-2xl font-bold" :class="selectedTecnico.herramientas_vencidas > 0 ? 'text-red-600' : 'text-gray-400'">
                      {{ selectedTecnico.herramientas_vencidas }}
                    </div>
                    <div class="text-sm text-gray-600">Vencidas</div>
                  </div>
                  <div class="text-center">
                    <div class="text-2xl font-bold text-purple-600">{{ selectedTecnico.asignaciones_activas }}</div>
                    <div class="text-sm text-gray-600">Asign. Activas</div>
                  </div>
                </div>
              </div>

              <!-- Alertas -->
              <div v-if="selectedTecnico.alertas && selectedTecnico.alertas.length > 0" class="space-y-2">
                <h4 class="text-md font-medium text-gray-900">Alertas</h4>
                <div
                  v-for="(alerta, index) in selectedTecnico.alertas"
                  :key="index"
                  :class="alerta.tipo === 'warning' ? 'bg-yellow-50 border-yellow-200 text-yellow-800' : 'bg-blue-50 border-blue-200 text-blue-800'"
                  class="p-3 rounded-lg border"
                >
                  <div class="flex items-center gap-2">
                    <svg v-if="alerta.tipo === 'warning'" class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                    <svg v-else class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="text-sm">{{ alerta.mensaje }}</span>
                  </div>
                </div>
              </div>

              <!-- Preview de herramientas -->
              <div v-if="selectedTecnico.herramientas_preview && selectedTecnico.herramientas_preview.length > 0">
                <h4 class="text-md font-medium text-gray-900 mb-3">Herramientas Asignadas</h4>
                <div class="space-y-2">
                  <div
                    v-for="herramienta in selectedTecnico.herramientas_preview"
                    :key="herramienta.id"
                    class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg"
                  >
                    <div v-if="herramienta.necesita_atencion" class="w-2 h-2 bg-red-500 rounded-full" title="Necesita atención"></div>
                    <div class="flex-1 min-w-0">
                      <div class="text-sm font-medium text-gray-900">{{ herramienta.nombre }}</div>
                      <div class="text-xs text-gray-500">{{ herramienta.numero_serie }}</div>
                    </div>
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-700">
                      {{ herramienta.categoria }}
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Footer del modal -->
          <div class="flex justify-end gap-3 px-6 py-4 border-t border-gray-200 bg-gray-50">
            <button @click="showModal = false" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors">
              Cerrar
            </button>
            <button @click="verHerramientasTecnico(selectedTecnico)" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
              Ver Todas las Herramientas
            </button>
            <button @click="generarReporte(selectedTecnico)" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
              Generar Reporte
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.tecnicos-herramientas-index {
  min-height: 100vh;
  background-color: #f9fafb;
}
</style>
