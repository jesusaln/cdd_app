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
  herramientas: { type: Array, default: () => [] },
  tecnicos: { type: Array, default: () => [] },
  herramienta_id: { type: [Number, String], default: null }
})

// Estado del formulario
const form = ref({
  herramienta_id: props.herramienta_id ? Number(props.herramienta_id) : '',
  condicion_general: '',
  porcentaje_desgaste: 0,
  observaciones: '',
  requiere_mantenimiento: false,
  prioridad_mantenimiento: 'baja',
  costo_estimado: '',
  foto_estado: null,
  proxima_inspeccion: '',
})

// Estados de herramientas
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

// Computed
const herramientaSeleccionada = computed(() => {
  return props.herramientas.find(h => h.id === form.value.herramienta_id)
})

const herramientasFiltradas = computed(() => {
  return props.herramientas.filter(h => h.estado === 'disponible' || h.estado === 'asignada')
})

const isFormValid = computed(() => {
  return form.value.herramienta_id && form.value.condicion_general && form.value.porcentaje_desgaste >= 0
})

const prioridadAutomatica = computed(() => {
  const desgaste = form.value.porcentaje_desgaste
  if (desgaste >= 80) return 'alta'
  if (desgaste >= 50) return 'media'
  return 'baja'
})

// Métodos
const handleFileChange = (event) => {
  const file = event.target.files[0]
  if (file) {
    form.value.foto_estado = file
  }
}

const removeFile = () => {
  form.value.foto_estado = null
  // Reset file input
  const input = document.getElementById('foto_estado')
  if (input) input.value = ''
}

const updatePorcentajeDesgaste = (value) => {
  form.value.porcentaje_desgaste = Math.max(0, Math.min(100, parseInt(value) || 0))
  // Auto-actualizar prioridad basada en desgaste
  if (!form.value.requiere_mantenimiento) {
    form.value.prioridad_mantenimiento = prioridadAutomatica.value
  }
}

const updateCondicion = (condicion) => {
  form.value.condicion_general = condicion
  // Auto-actualizar desgaste basado en condición
  const rangosDesgaste = {
    'excelente': 10,
    'buena': 25,
    'regular': 50,
    'mala': 75,
    'critica': 90
  }
  form.value.porcentaje_desgaste = rangosDesgaste[condicion] || 0
}

const toggleMantenimiento = () => {
  form.value.requiere_mantenimiento = !form.value.requiere_mantenimiento
  if (form.value.requiere_mantenimiento) {
    form.value.prioridad_mantenimiento = 'alta'
  } else {
    form.value.prioridad_mantenimiento = prioridadAutomatica.value
  }
}

const submitForm = () => {
  if (!isFormValid.value) {
    notyf.error('Por favor complete todos los campos requeridos')
    return
  }

  const formData = new FormData()

  // Agregar campos básicos
  Object.keys(form.value).forEach(key => {
    if (form.value[key] !== null && form.value[key] !== '') {
      if (form.value[key] instanceof File) {
        formData.append(key, form.value[key])
      } else {
        formData.append(key, form.value[key])
      }
    }
  })

  router.post(route('herramientas.estados.store'), formData, {
    onSuccess: () => {
      notyf.success('Inspección creada correctamente')
    },
    onError: (errors) => {
      console.error('Errores:', errors)
      notyf.error('Error al crear la inspección')
    }
  })
}

const cancel = () => {
  router.visit(route('herramientas.estados.index'))
}

// Helpers
const formatFileName = (file) => {
  return file ? file.name : 'Seleccionar archivo...'
}

const getCondicionClasses = (condicion) => {
  const condicionInfo = condicionesHerramientas[condicion] || { color: 'gray' }
  return `bg-${condicionInfo.color}-100 text-${condicionInfo.color}-700`
}

const getPrioridadClasses = (prioridad) => {
  const prioridadInfo = prioridadesMantenimiento[prioridad] || { color: 'gray' }
  return `bg-${prioridadInfo.color}-100 text-${prioridadInfo.color}-700`
}
</script>

<template>
  <Head title="Nueva Inspección de Herramienta" />

  <div class="estados-create min-h-screen bg-gray-50">
    <div class="max-w-4xl mx-auto px-6 py-8">
      <!-- Header -->
      <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6 mb-6">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3">
            <button
              @click="cancel"
              class="inline-flex items-center gap-2 px-4 py-2 text-gray-600 hover:text-gray-900 transition-colors"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
              </svg>
              <span>Volver</span>
            </button>
            <div class="h-6 w-px bg-gray-300"></div>
            <h1 class="text-2xl font-bold text-slate-900">Nueva Inspección de Herramienta</h1>
          </div>
        </div>
      </div>

      <!-- Formulario -->
      <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
        <form @submit.prevent="submitForm" class="space-y-6">
          <!-- Herramienta -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Herramienta *
            </label>
            <select
              v-model="form.herramienta_id"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              required
            >
              <option value="">Seleccionar herramienta...</option>
              <option
                v-for="herramienta in herramientasFiltradas"
                :key="herramienta.id"
                :value="herramienta.id"
              >
                {{ herramienta.nombre }} ({{ herramienta.numero_serie }})
              </option>
            </select>
            <div v-if="herramientaSeleccionada" class="mt-2 p-3 bg-gray-50 rounded-md">
              <p class="text-sm text-gray-600">
                <strong>Estado actual:</strong>
                <span :class="getCondicionClasses(herramientaSeleccionada.estado)" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ml-2">
                  {{ herramientaSeleccionada.estado }}
                </span>
              </p>
              <p v-if="herramientaSeleccionada.categoria" class="text-sm text-gray-600">
                <strong>Categoría:</strong> {{ herramientaSeleccionada.categoria }}
              </p>
            </div>
          </div>

          <!-- Condición General -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Condición General *
            </label>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
              <div
                v-for="(condicion, key) in condicionesHerramientas"
                :key="key"
                @click="updateCondicion(key)"
                :class="[
                  'p-4 border rounded-lg cursor-pointer transition-all',
                  form.condicion_general === key
                    ? `border-${condicion.color}-500 bg-${condicion.color}-50`
                    : 'border-gray-200 hover:border-gray-300'
                ]"
              >
                <div class="flex items-center justify-between mb-2">
                  <span class="font-medium text-gray-900">{{ condicion.label }}</span>
                  <span :class="`inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-${condicion.color}-100 text-${condicion.color}-700`">
                    {{ key === 'excelente' ? '0-20%' : key === 'buena' ? '20-40%' : key === 'regular' ? '40-60%' : key === 'mala' ? '60-80%' : '80-100%' }}
                  </span>
                </div>
                <p class="text-sm text-gray-600">{{ condicion.descripcion }}</p>
              </div>
            </div>
          </div>

          <!-- Porcentaje de Desgaste -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Porcentaje de Desgaste * ({{ form.porcentaje_desgaste }}%)
            </label>
            <div class="space-y-3">
              <input
                type="range"
                min="0"
                max="100"
                :value="form.porcentaje_desgaste"
                @input="updatePorcentajeDesgaste($event.target.value)"
                class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer"
              />
              <div class="flex justify-between text-xs text-gray-500">
                <span>0%</span>
                <span>25%</span>
                <span>50%</span>
                <span>75%</span>
                <span>100%</span>
              </div>
            </div>
          </div>

          <!-- Requiere Mantenimiento -->
          <div>
            <label class="flex items-center">
              <input
                v-model="form.requiere_mantenimiento"
                @change="toggleMantenimiento"
                type="checkbox"
                class="form-checkbox h-4 w-4 text-blue-600 rounded"
              />
              <span class="ml-2 text-sm text-gray-700">Requiere mantenimiento</span>
            </label>
          </div>

          <!-- Prioridad de Mantenimiento -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Prioridad de Mantenimiento
            </label>
            <div class="flex gap-3">
              <label
                v-for="(prioridad, key) in prioridadesMantenimiento"
                :key="key"
                class="inline-flex items-center"
              >
                <input
                  v-model="form.prioridad_mantenimiento"
                  type="radio"
                  :value="key"
                  class="form-radio h-4 w-4 text-blue-600"
                />
                <span :class="getPrioridadClasses(key)" class="ml-2 inline-flex items-center px-2 py-1 rounded-full text-xs font-medium">
                  {{ prioridad.label }}
                </span>
              </label>
            </div>
          </div>

          <!-- Costo Estimado -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Costo Estimado de Mantenimiento
            </label>
            <input
              v-model="form.costo_estimado"
              type="number"
              step="0.01"
              min="0"
              placeholder="0.00"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
          </div>

          <!-- Próxima Inspección -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Fecha de Próxima Inspección
            </label>
            <input
              v-model="form.proxima_inspeccion"
              type="date"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
          </div>

          <!-- Observaciones -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Observaciones
            </label>
            <textarea
              v-model="form.observaciones"
              rows="4"
              placeholder="Describe el estado de la herramienta, daños observados, recomendaciones de mantenimiento..."
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            ></textarea>
          </div>

          <!-- Foto del Estado -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Foto del Estado Actual
            </label>
            <div class="flex items-center gap-4">
              <input
                id="foto_estado"
                type="file"
                @change="handleFileChange"
                accept="image/*"
                class="hidden"
              />
              <label
                for="foto_estado"
                class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 cursor-pointer transition-colors"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span class="text-sm">{{ formatFileName(form.foto_estado) }}</span>
              </label>
              <button
                v-if="form.foto_estado"
                type="button"
                @click="removeFile"
                class="inline-flex items-center gap-2 px-3 py-2 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition-colors"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                <span class="text-sm">Remover</span>
              </button>
            </div>
          </div>

          <!-- Botones de Acción -->
          <div class="flex justify-end gap-3 pt-6 border-t">
            <button
              type="button"
              @click="cancel"
              class="px-6 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors"
            >
              Cancelar
            </button>
            <button
              type="submit"
              :disabled="!isFormValid"
              class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors"
            >
              Crear Inspección
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<style scoped>
.estados-create {
  min-height: 100vh;
  background-color: #f9fafb;
}

/* Estilos para el slider */
input[type="range"]::-webkit-slider-thumb {
  appearance: none;
  height: 20px;
  width: 20px;
  border-radius: 50%;
  background: #3b82f6;
  cursor: pointer;
}

input[type="range"]::-moz-range-thumb {
  height: 20px;
  width: 20px;
  border-radius: 50%;
  background: #3b82f6;
  cursor: pointer;
  border: none;
}
</style>
