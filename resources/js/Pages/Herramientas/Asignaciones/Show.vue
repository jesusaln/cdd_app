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
  asignacion: { type: Object, required: true },
  herramienta: { type: Object, default: null },
  tecnico: { type: Object, default: null },
  estadosHerramientas: { type: Array, default: () => [] }
})

// Estado
const loading = ref(false)

// Computed
const estadosHerramientasMap = {
  'excelente': { label: 'Excelente', color: 'green' },
  'buena': { label: 'Buena', color: 'blue' },
  'regular': { label: 'Regular', color: 'yellow' },
  'mala': { label: 'Mala', color: 'orange' },
  'critica': { label: 'Crítica', color: 'red' }
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

// Métodos
const goBack = () => {
  router.visit(route('herramientas.asignaciones.index'))
}

const editAsignacion = () => {
  router.visit(route('herramientas.asignaciones.edit', props.asignacion.id))
}

const deleteAsignacion = () => {
  if (confirm('¿Estás seguro de que deseas eliminar esta asignación? Esta acción no se puede deshacer.')) {
    loading.value = true
    router.delete(route('herramientas.asignaciones.destroy', props.asignacion.id), {
      onSuccess: () => {
        notyf.success('Asignación eliminada correctamente')
        goBack()
      },
      onError: () => {
        notyf.error('Error al eliminar la asignación')
        loading.value = false
      }
    })
  }
}

const printAsignacion = () => {
  window.print()
}
</script>

<template>
  <Head :title="`Asignación #${asignacion.id}`" />

  <div class="asignacion-show min-h-screen bg-gray-50">
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
              Asignación #{{ asignacion.id }}
            </h1>
            <span :class="getEstadoBadgeClass(asignacion.estado)" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium">
              {{ asignacion.estado }}
            </span>
          </div>

          <div class="flex gap-3">
            <button
              @click="printAsignacion"
              class="inline-flex items-center gap-2 px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
              </svg>
              <span>Imprimir</span>
            </button>
            <button
              @click="editAsignacion"
              class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
              </svg>
              <span>Editar</span>
            </button>
            <button
              @click="deleteAsignacion"
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
        <!-- Información de la Asignación -->
        <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
          <h2 class="text-lg font-semibold text-slate-900 mb-4">Información de la Asignación</h2>
          <dl class="space-y-3">
            <div>
              <dt class="text-sm font-medium text-gray-500">Tipo de Asignación</dt>
              <dd class="mt-1">
                <span :class="getTipoBadgeClass(asignacion.tipo_asignacion)" class="inline-flex items-center px-2 py-1 rounded-full text-sm font-medium">
                  {{ asignacion.tipo_asignacion === 'entrega' ? 'Entrega' : 'Recepción' }}
                </span>
              </dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-gray-500">Estado</dt>
              <dd class="mt-1">
                <span :class="getEstadoBadgeClass(asignacion.estado)" class="inline-flex items-center px-2 py-1 rounded-full text-sm font-medium">
                  {{ asignacion.estado }}
                </span>
              </dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-gray-500">Fecha de Creación</dt>
              <dd class="mt-1 text-sm text-gray-900">{{ formatDate(asignacion.created_at) }}</dd>
            </div>
            <div v-if="asignacion.updated_at !== asignacion.created_at">
              <dt class="text-sm font-medium text-gray-500">Última Actualización</dt>
              <dd class="mt-1 text-sm text-gray-900">{{ formatDate(asignacion.updated_at) }}</dd>
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
                <span v-if="herramienta?.estado" :class="`inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-${estadosHerramientasMap[herramienta.estado]?.color}-100 text-${estadosHerramientasMap[herramienta.estado]?.color}-700`">
                  {{ estadosHerramientasMap[herramienta.estado]?.label || herramienta.estado }}
                </span>
                <span v-else class="text-sm text-gray-500">N/A</span>
              </dd>
            </div>
          </dl>
        </div>
      </div>

      <!-- Información del Técnico -->
      <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6 mb-6">
        <h2 class="text-lg font-semibold text-slate-900 mb-4">Información del Técnico</h2>
        <dl class="space-y-3">
          <div>
            <dt class="text-sm font-medium text-gray-500">Nombre</dt>
            <dd class="mt-1 text-sm text-gray-900">
              {{ tecnico?.nombre || 'N/A' }} {{ tecnico?.apellido || '' }}
            </dd>
          </div>
          <div v-if="tecnico?.email">
            <dt class="text-sm font-medium text-gray-500">Email</dt>
            <dd class="mt-1 text-sm text-gray-900">{{ tecnico.email }}</dd>
          </div>
          <div v-if="tecnico?.telefono">
            <dt class="text-sm font-medium text-gray-500">Teléfono</dt>
            <dd class="mt-1 text-sm text-gray-900">{{ tecnico.telefono }}</dd>
          </div>
          <div v-if="tecnico?.especialidad">
            <dt class="text-sm font-medium text-gray-500">Especialidad</dt>
            <dd class="mt-1 text-sm text-gray-900">{{ tecnico.especialidad }}</dd>
          </div>
        </dl>
      </div>

      <!-- Observaciones -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div v-if="asignacion.observaciones_entrega" class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
          <h2 class="text-lg font-semibold text-slate-900 mb-4">Observaciones de Entrega</h2>
          <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ asignacion.observaciones_entrega }}</p>
        </div>

        <div v-if="asignacion.observaciones_recepcion" class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
          <h2 class="text-lg font-semibold text-slate-900 mb-4">Observaciones de Recepción</h2>
          <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ asignacion.observaciones_recepcion }}</p>
        </div>
      </div>

      <!-- Estados de la Herramienta -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div v-if="asignacion.estado_herramienta_entrega" class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
          <h2 class="text-lg font-semibold text-slate-900 mb-4">Estado al Entregar</h2>
          <div class="space-y-3">
            <div>
              <dt class="text-sm font-medium text-gray-500">Condición</dt>
              <dd class="mt-1">
                <span :class="`inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-${estadosHerramientasMap[asignacion.estado_herramienta_entrega]?.color}-100 text-${estadosHerramientasMap[asignacion.estado_herramienta_entrega]?.color}-700`">
                  {{ estadosHerramientasMap[asignacion.estado_herramienta_entrega]?.label || asignacion.estado_herramienta_entrega }}
                </span>
              </dd>
            </div>
            <div v-if="asignacion.foto_estado_entrega">
              <dt class="text-sm font-medium text-gray-500">Foto del Estado</dt>
              <dd class="mt-2">
                <img
                  :src="formatFileUrl(asignacion.foto_estado_entrega)"
                  :alt="`Estado de entrega - ${herramienta?.nombre}`"
                  class="w-full max-w-sm rounded-lg shadow-sm"
                />
              </dd>
            </div>
          </div>
        </div>

        <div v-if="asignacion.estado_herramienta_recepcion" class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
          <h2 class="text-lg font-semibold text-slate-900 mb-4">Estado al Recibir</h2>
          <div class="space-y-3">
            <div>
              <dt class="text-sm font-medium text-gray-500">Condición</dt>
              <dd class="mt-1">
                <span :class="`inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-${estadosHerramientasMap[asignacion.estado_herramienta_recepcion]?.color}-100 text-${estadosHerramientasMap[asignacion.estado_herramienta_recepcion]?.color}-700`">
                  {{ estadosHerramientasMap[asignacion.estado_herramienta_recepcion]?.label || asignacion.estado_herramienta_recepcion }}
                </span>
              </dd>
            </div>
            <div v-if="asignacion.foto_estado_recepcion">
              <dt class="text-sm font-medium text-gray-500">Foto del Estado</dt>
              <dd class="mt-2">
                <img
                  :src="formatFileUrl(asignacion.foto_estado_recepcion)"
                  :alt="`Estado de recepción - ${herramienta?.nombre}`"
                  class="w-full max-w-sm rounded-lg shadow-sm"
                />
              </dd>
            </div>
          </div>
        </div>
      </div>

      <!-- Firmas -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div v-if="asignacion.firma_entrega" class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
          <h2 class="text-lg font-semibold text-slate-900 mb-4">Firma de Entrega</h2>
          <div class="border rounded-lg p-4 bg-gray-50">
            <img
              :src="formatFileUrl(asignacion.firma_entrega)"
              alt="Firma de entrega"
              class="w-full max-w-sm rounded-lg shadow-sm"
            />
          </div>
        </div>

        <div v-if="asignacion.firma_recepcion" class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
          <h2 class="text-lg font-semibold text-slate-900 mb-4">Firma de Recepción</h2>
          <div class="border rounded-lg p-4 bg-gray-50">
            <img
              :src="formatFileUrl(asignacion.firma_recepcion)"
              alt="Firma de recepción"
              class="w-full max-w-sm rounded-lg shadow-sm"
            />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.asignacion-show {
  min-height: 100vh;
  background-color: #f9fafb;
}

@media print {
  .asignacion-show {
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
