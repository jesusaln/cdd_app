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
  herramientas: { type: Array, default: () => [] },
  tecnicos: { type: Array, default: () => [] },
  tipo: { type: String, default: 'entrega' },
  herramienta_id: { type: [Number, String], default: null }
})

// Estado del formulario
const form = ref({
  herramienta_id: props.herramienta_id ? Number(props.herramienta_id) : '',
  tecnico_id: '',
  tipo_asignacion: props.tipo,
  observaciones_entrega: '',
  observaciones_recepcion: '',
  estado_herramienta_entrega: '',
  estado_herramienta_recepcion: '',
  foto_estado_entrega: null,
  foto_estado_recepcion: null,
})

// Estados de herramientas
const estadosHerramientas = {
  'excelente': { label: 'Excelente', color: 'green' },
  'buena': { label: 'Buena', color: 'blue' },
  'regular': { label: 'Regular', color: 'yellow' },
  'mala': { label: 'Mala', color: 'orange' },
  'critica': { label: 'Crítica', color: 'red' }
}

// Computed
const herramientaSeleccionada = computed(() => {
  return props.herramientas.find(h => h.id === form.value.herramienta_id)
})

const tecnicoSeleccionado = computed(() => {
  return props.tecnicos.find(t => t.id === form.value.tecnico_id)
})

const herramientasFiltradas = computed(() => {
  if (form.value.tipo_asignacion === 'entrega') {
    return props.herramientas.filter(h => h.estado === 'disponible')
  } else {
    return props.herramientas.filter(h => h.estado === 'asignada')
  }
})

const isFormValid = computed(() => {
  return form.value.herramienta_id && form.value.tecnico_id
})

// Métodos
const handleFileChange = (event, field) => {
  const file = event.target.files[0]
  if (file) {
    form.value[field] = file
  }
}

const removeFile = (field) => {
  form.value[field] = null
  // Reset file input
  const input = document.getElementById(field)
  if (input) input.value = ''
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

  router.post(route('herramientas.asignaciones.store'), formData, {
    onSuccess: () => {
      notyf.success('Asignación creada correctamente')
    },
    onError: (errors) => {
      console.error('Errores:', errors)
      notyf.error('Error al crear la asignación')
    }
  })
}

const cancel = () => {
  router.visit(route('herramientas.index'))
}

// Helpers
const formatFileName = (file) => {
  return file ? file.name : 'Seleccionar archivo...'
}

const getEstadoClasses = (estado) => {
  const estadoInfo = estadosHerramientas[estado] || { color: 'gray' }
  return `bg-${estadoInfo.color}-100 text-${estadoInfo.color}-700`
}
</script>

<template>
  <Head :title="tipo === 'entrega' ? 'Nueva Entrega de Herramienta' : 'Nueva Recepción de Herramienta'" />

  <div class="asignaciones-create min-h-screen bg-gray-50">
    <div class="max-w-4xl mx-auto px-6 py-8">
      <!-- Header -->
      <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6 mb-6">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3">
            <Link
              :href="route('herramientas.index')"
              class="inline-flex items-center gap-2 px-4 py-2 text-gray-600 hover:text-gray-900 transition-colors"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
              </svg>
              <span>Volver</span>
            </Link>
            <div class="h-6 w-px bg-gray-300"></div>
            <h1 class="text-2xl font-bold text-slate-900">
              {{ tipo === 'entrega' ? 'Nueva Entrega de Herramienta' : 'Nueva Recepción de Herramienta' }}
            </h1>
          </div>
        </div>
      </div>

      <!-- Formulario -->
      <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
        <form @submit.prevent="submitForm" class="space-y-6">
          <!-- Tipo de Asignación -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Tipo de Asignación *
            </label>
            <div class="flex gap-4">
              <label class="inline-flex items-center">
                <input
                  v-model="form.tipo_asignacion"
                  type="radio"
                  value="entrega"
                  class="form-radio h-4 w-4 text-blue-600"
                  :disabled="tipo !== 'entrega'"
                />
                <span class="ml-2 text-sm text-gray-700">Entrega</span>
              </label>
              <label class="inline-flex items-center">
                <input
                  v-model="form.tipo_asignacion"
                  type="radio"
                  value="recepcion"
                  class="form-radio h-4 w-4 text-blue-600"
                  :disabled="tipo !== 'recepcion'"
                />
                <span class="ml-2 text-sm text-gray-700">Recepción</span>
              </label>
            </div>
          </div>

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
            <p v-if="herramientaSeleccionada" class="mt-1 text-sm text-gray-500">
              Estado actual: <span :class="getEstadoClasses(herramientaSeleccionada.estado)" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium">
                {{ herramientaSeleccionada.estado }}
              </span>
            </p>
          </div>

          <!-- Técnico -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              {{ tipo === 'entrega' ? 'Técnico Receptor' : 'Técnico Entregador' }} *
            </label>
            <select
              v-model="form.tecnico_id"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              required
            >
              <option value="">Seleccionar técnico...</option>
              <option
                v-for="tecnico in tecnicos"
                :key="tecnico.id"
                :value="tecnico.id"
              >
                {{ tecnico.nombre }} {{ tecnico.apellido }}
              </option>
            </select>
          </div>

          <!-- Observaciones -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Observaciones {{ tipo === 'entrega' ? 'de Entrega' : 'de Recepción' }}
            </label>
            <textarea
              v-model="form[`observaciones_${tipo}`]"
              rows="3"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              :placeholder="tipo === 'entrega' ? 'Observaciones al entregar la herramienta...' : 'Observaciones al recibir la herramienta...'"
            ></textarea>
          </div>

          <!-- Estado de la Herramienta -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Estado de la Herramienta {{ tipo === 'entrega' ? 'al Entregar' : 'al Recibir' }}
            </label>
            <select
              v-model="form[`estado_herramienta_${tipo}`]"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="">Seleccionar estado...</option>
              <option
                v-for="(estado, key) in estadosHerramientas"
                :key="key"
                :value="key"
              >
                {{ estado.label }}
              </option>
            </select>
          </div>

          <!-- Foto del Estado -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Foto del Estado {{ tipo === 'entrega' ? 'de Entrega' : 'de Recepción' }}
            </label>
            <div class="flex items-center gap-4">
              <input
                :id="`foto_estado_${tipo}`"
                type="file"
                @change="handleFileChange($event, `foto_estado_${tipo}`)"
                accept="image/*"
                class="hidden"
              />
              <label
                :for="`foto_estado_${tipo}`"
                class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 cursor-pointer transition-colors"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span class="text-sm">{{ formatFileName(form[`foto_estado_${tipo}`]) }}</span>
              </label>
              <button
                v-if="form[`foto_estado_${tipo}`]"
                type="button"
                @click="removeFile(`foto_estado_${tipo}`)"
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
              {{ tipo === 'entrega' ? 'Entregar Herramienta' : 'Recibir Herramienta' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<style scoped>
.asignaciones-create {
  min-height: 100vh;
  background-color: #f9fafb;
}
</style>
