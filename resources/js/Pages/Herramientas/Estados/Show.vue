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
  estado: { type: Object, required: true },
  herramienta: { type: Object, default: null },
  tecnicos: { type: Array, default: () => [] }
})

// Estado
const loading = ref(false)

// Computed
const condicionesHerramientas = {
  'excelente': { label: 'Excelente', color: 'green', descripcion: 'Sin signos de desgaste' },
  'buena': { label: 'Buena', color: 'blue', descripcion: 'Ligero desgaste normal' },
  'regular': { label: 'Regular', color: 'yellow', descripcion: 'Desgaste moderado' },
  'mala': { label: 'Mala', color: 'orange', descripcion: 'Desgaste significativo' },
  'critica': { label: 'Crítica', color: 'red', descripcion: 'Requiere atención inmediata' }
}

const prioridadesMantenimiento = {
  'baja': { label: 'Baja', color: 'green' },
  'media': { label: 'Media', color: 'yellow' },
  'alta': { label: 'Alta', color: 'red' }
}

const getCondicionBadgeClass = (condicion) => {
  const condicionInfo = condicionesHerramientas[condicion] || { color: 'gray' }
  return `bg-${condicionInfo.color}-100 text-${condicionInfo.color}-700`
}

const getPrioridadBadgeClass = (prioridad) => {
  const prioridadInfo = prioridadesMantenimiento[prioridad] || { color: 'gray' }
  return `bg-${prioridadInfo.color}-100 text-${prioridadInfo.color}-700`
}

const formatDate = (date) => {
  if (!date) return 'N/A'
  return new Date(date).toLocaleDateString('es-MX', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const formatFileUrl = (filePath) => {
  if (!filePath) return null
  // Si es una URL completa, devolverla tal cual
  if (filePath.startsWith('http')) return filePath
  // Si es un path relativo, construir la URL completa
  return `${window.location.origin}/storage/${filePath}`
}

const getDesgasteColor = (porcentaje) => {
  if (porcentaje >= 80) return 'bg-red-500'
  if (porcentaje >= 60) return 'bg-orange-500'
  if (porcentaje >= 40) return 'bg-yellow-500'
  if (porcentaje >= 20) return 'bg-blue-500'
  return 'bg-green-500'
}

const getDesgasteLevel = (porcentaje) => {
  if (porcentaje >= 80) return 'Crítico'
  if (porcentaje >= 60) return 'Alto'
  if (porcentaje >= 40) return 'Moderado'
  if (porcentaje >= 20) return 'Bajo'
  return 'Mínimo'
}

// Métodos
const goBack = () => {
  router.visit(route('herramientas.estados.index'))
}

const editEstado = () => {
  router.visit(route('herramientas.estados.edit', props.estado.id))
}

const deleteEstado = () => {
  if (confirm('¿Estás seguro de que deseas eliminar esta inspección? Esta acción no se puede deshacer.')) {
    loading.value = true
    router.delete(route('herramientas.estados.destroy', props.estado.id), {
      onSuccess: () => {
        notyf.success('Inspección eliminada correctamente')
        goBack()
      },
      onError: () => {
        notyf.error('Error al eliminar la inspección')
        loading.value = false
      }
    })
  }
}

const printEstado = () => {
  window.print()
}
</script>

<template>
  <Head :title="`Inspección #${estado.id}`" />

  <div class="estado-show min-h-screen bg-gray-50">
    <div class="max-w-4xl mx-auto px-6 py-8">
      <!-- Header -->
      <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6 mb-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
          <div class="flex items-center gap-3">
            <button
              @click="goBack"
              class="inline-flex items-center gap-2 px-4 py-2 text-gray-600 hover:text-gray-900 transition-colors"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
              </svg>
              <span>Volver</span>
            </button>
            <div class="h-6 w-px bg-gray-300"></div>
            <h1 class="text-2xl font-bold text-slate-900">
              Inspección #{{ estado.id }}
            </h1>
            <span :class="getCondicionBadgeClass(estado.condicion_general)" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium">
              {{ condicionesHerramientas[estado.condicion_general]?.label || estado.condicion_general }}
            </span>
          </div>

          <div class="flex gap-3">
            <button
              @click="printEstado"
              class="inline-flex items-center gap-2 px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
              </svg>
              <span>Imprimir</span>
            </button>
            <button
              @click="editEstado"
              class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
              </svg>
              <span>Editar</span>
            </button>
            <button
              @click="deleteEstado"
              :disabled="loading"
              class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
              <span>{{ loading ? 'Eliminando...' : 'Eliminar' }}</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Información Principal -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Información de la Inspección -->
        <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
          <h2 class="text-lg font-semibold text-slate-900 mb-4">Información de la Inspección</h2>
          <dl class="space-y-3">
            <div>
              <dt class="text-sm font-medium text-gray-500">Condición General</dt>
              <dd class="mt-1">
                <span :class="getCondicionBadgeClass(estado.condicion_general)" class="inline-flex items-center px-2 py-1 rounded-full text-sm font-medium">
                  {{ condicionesHerramientas[estado.condicion_general]?.label || estado.condicion_general }}
                </span>
              </dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-gray-500">Porcentaje de Desgaste</dt>
              <dd class="mt-1">
                <div class="flex items-center gap-3">
                  <div class="w-24 bg-gray-200 rounded-full h-3">
                    <div
                      :class="getDesgasteColor(estado.porcentaje_desgaste)"
                      class="h-3 rounded-full transition-all"
                      :style="{ width: `${estado.porcentaje_desgaste}%` }"
                    ></div>
                  </div>
                  <span class="text-sm font-medium text-gray-900">{{ estado.porcentaje_desgaste }}%</span>
                  <span class="text-xs text-gray-500">({{ getDesgasteLevel(estado.porcentaje_desgaste) }})</span>
                </div>
              </dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-gray-500">Prioridad de Mantenimiento</dt>
              <dd class="mt-1">
                <span :class="getPrioridadBadgeClass(estado.prioridad_mantenimiento)" class="inline-flex items-center px-2 py-1 rounded-full text-sm font-medium">
                  {{ prioridadesMantenimiento[estado.prioridad_mantenimiento]?.label || estado.prioridad_mantenimiento }}
                </span>
              </dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-gray-500">Requiere Mantenimiento</dt>
              <dd class="mt-1">
                <span :class="estado.requiere_mantenimiento ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700'" class="inline-flex items-center px-2 py-1 rounded-full text-sm font-medium">
                  {{ estado.requiere_mantenimiento ? 'Sí' : 'No' }}
                </span>
              </dd>
            </div>
            <div v-if="estado.costo_estimado">
              <dt class="text-sm font-medium text-gray-500">Costo Estimado</dt>
              <dd class="mt-1 text-sm text-gray-900">${{ parseFloat(estado.costo_estimado).toFixed(2) }}</dd>
            </div>
            <div v-if="estado.proxima_inspeccion">
              <dt class="text-sm font-medium text-gray-500">Próxima Inspección</dt>
              <dd class="mt-1 text-sm text-gray-900">{{ formatDate(estado.proxima_inspeccion) }}</dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-gray-500">Fecha de Creación</dt>
              <dd class="mt-1 text-sm text-gray-900">{{ formatDate(estado.created_at) }}</dd>
            </div>
            <div v-if="estado.updated_at !== estado.created_at">
              <dt class="text-sm font-medium text-gray-500">Última Actualización</dt>
              <dd class="mt-1 text-sm text-gray-900">{{ formatDate(estado.updated_at) }}</dd>
            </div>
          </dl>
        </div>

        <!-- Información de la Herramienta -->
        <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
          <h2 class="text-lg font-semibold text-slate-900 mb-4">Información de la Herramienta</h2>
          <dl class="space-y-3">
            <div>
              <dt class="text-sm font-medium text-gray-500">Herramienta</dt>
              <dd class="mt-1 text-sm text-gray-900">
                {{ herramienta?.nombre || 'N/A' }}
              </dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-gray-500">Número de Serie</dt>
              <dd class="mt-1 text-sm text-gray-900">{{ herramienta?.numero_serie || 'N/A' }}</dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-gray-500">Categoría</dt>
              <dd class="mt-1 text-sm text-gray-900">{{ herramienta?.categoria || 'N/A' }}</dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-gray-500">Estado Actual</dt>
              <dd class="mt-1">
                <span v-if="herramienta?.estado" :class="getCondicionBadgeClass(herramienta.estado)" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium">
                  {{ condicionesHerramientas[herramienta.estado]?.label || herramienta.estado }}
                </span>
                <span v-else class="text-sm text-gray-500">N/A</span>
              </dd>
            </div>
            <div v-if="herramienta?.vida_util_meses">
              <dt class="text-sm font-medium text-gray-500">Vida Útil</dt>
              <dd class="mt-1 text-sm text-gray-900">{{ herramienta.vida_util_meses }} meses</dd>
            </div>
            <div v-if="herramienta?.fecha_ultimo_mantenimiento">
              <dt class="text-sm font-medium text-gray-500">Último Mantenimiento</dt>
              <dd class="mt-1 text-sm text-gray-900">{{ formatDate(herramienta.fecha_ultimo_mantenimiento) }}</dd>
            </div>
          </dl>
        </div>
      </div>

      <!-- Observaciones -->
      <div v-if="estado.observaciones" class="bg-white border border-slate-200 rounded-xl shadow-sm p-6 mb-6">
        <h2 class="text-lg font-semibold text-slate-900 mb-4">Observaciones</h2>
        <div class="prose prose-sm max-w-none">
          <p class="text-gray-700 whitespace-pre-wrap">{{ estado.observaciones }}</p>
        </div>
      </div>

      <!-- Foto del Estado -->
      <div v-if="estado.foto_estado" class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
        <h2 class="text-lg font-semibold text-slate-900 mb-4">Foto del Estado</h2>
        <div class="border rounded-lg p-4 bg-gray-50">
          <img
            :src="formatFileUrl(estado.foto_estado)"
            :alt="`Estado de la herramienta - ${herramienta?.nombre}`"
            class="w-full max-w-2xl rounded-lg shadow-sm"
          />
        </div>
      </div>

      <!-- Alertas y Recomendaciones -->
      <div v-if="estado.porcentaje_desgaste >= 50 || estado.requiere_mantenimiento" class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
        <h2 class="text-lg font-semibold text-slate-900 mb-4">Alertas y Recomendaciones</h2>
        <div class="space-y-3">
          <div v-if="estado.porcentaje_desgaste >= 80" class="flex items-start gap-3 p-3 bg-red-50 border border-red-200 rounded-lg">
            <svg class="w-5 h-5 text-red-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
            </svg>
            <div>
              <p class="text-sm font-medium text-red-800">Desgaste Crítico</p>
              <p class="text-sm text-red-700">La herramienta presenta un desgaste del {{ estado.porcentaje_desgaste }}%. Se recomienda reemplazo inmediato o mantenimiento urgente.</p>
            </div>
          </div>

          <div v-else-if="estado.porcentaje_desgaste >= 60" class="flex items-start gap-3 p-3 bg-orange-50 border border-orange-200 rounded-lg">
            <svg class="w-5 h-5 text-orange-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
            </svg>
            <div>
              <p class="text-sm font-medium text-orange-800">Alto Desgaste</p>
              <p class="text-sm text-orange-700">La herramienta presenta un desgaste del {{ estado.porcentaje_desgaste }}%. Considere mantenimiento preventivo en las próximas semanas.</p>
            </div>
          </div>

          <div v-if="estado.requiere_mantenimiento" class="flex items-start gap-3 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
            <svg class="w-5 h-5 text-yellow-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div>
              <p class="text-sm font-medium text-yellow-800">Mantenimiento Requerido</p>
              <p class="text-sm text-yellow-700">La herramienta requiere mantenimiento según la inspección realizada.</p>
            </div>
          </div>

          <div v-if="estado.costo_estimado" class="flex items-start gap-3 p-3 bg-blue-50 border border-blue-200 rounded-lg">
            <svg class="w-5 h-5 text-blue-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
            </svg>
            <div>
              <p class="text-sm font-medium text-blue-800">Costo Estimado</p>
              <p class="text-sm text-blue-700">Se estima un costo de ${{ parseFloat(estado.costo_estimado).toFixed(2) }} para el mantenimiento requerido.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.estado-show {
  min-height: 100vh;
  background-color: #f9fafb;
}

@media print {
  .estado-show {
    background-color: white;
  }

  button {
    display: none !important;
  }

  .bg-white {
    box-shadow: none !important;
    border: 1px solid #e5e7eb !important;
  }
}
</style>
