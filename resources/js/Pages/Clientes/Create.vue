<template>
  <Head title="Crear Cliente" />
  <div class="max-w-4xl mx-auto p-4">
    <div class="bg-white shadow-sm rounded-lg p-6">
      <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold text-gray-800">Crear Nuevo Cliente</h1>
        <div class="text-sm text-gray-500">
          Campos obligatorios marcados con <span class="text-red-500">*</span>
        </div>
      </div>

      <!-- Resumen de errores -->
      <div v-if="hasGlobalErrors" class="mb-6 p-4 bg-red-50 border border-red-200 rounded-md">
        <div class="flex items-start">
          <svg class="h-5 w-5 text-red-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
          </svg>
          <div class="ml-3">
            <h3 class="text-sm font-medium text-red-800">Errores en el formulario</h3>
            <div class="mt-2 text-sm text-red-700">
              <ul class="list-disc list-inside space-y-1">
                <li v-for="(error, key) in form.errors" :key="key">
                  {{ getFieldLabel(key) }}: {{ Array.isArray(error) ? error[0] : error }}
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>

      <!-- Mensaje de éxito -->
      <transition
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="transform scale-95 opacity-0"
        enter-to-class="transform scale-100 opacity-100"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="transform scale-100 opacity-100"
        leave-to-class="transform scale-95 opacity-0"
      >
        <div
          v-if="showSuccessMessage"
          class="mb-6 p-4 bg-green-50 border border-green-200 rounded-md"
          aria-live="polite"
        >
          <div class="flex items-start">
            <svg class="h-5 w-5 text-green-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            <p class="ml-3 text-sm font-medium text-green-800">Cliente creado exitosamente</p>
          </div>
        </div>
      </transition>

      <form @submit.prevent="submit" class="space-y-8" novalidate>
        <!-- Información General -->
        <div class="border-b border-gray-200 pb-6">
          <h2 class="text-lg font-medium text-gray-900 mb-4">Información General</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2">
              <FormField
                label="Nombre/Razón Social"
                required
                :error="form.errors.nombre_razon_social"
                :touched="touchedFields.nombre_razon_social"
              >
                <input
                  type="text"
                  id="nombre_razon_social"
                  v-model.trim="form.nombre_razon_social"
                  @blur="handleBlur('nombre_razon_social')"
                  @input="clearFieldError('nombre_razon_social')"
                  :class="getInputClasses('nombre_razon_social')"
                  placeholder="Ingrese el nombre o razón social"
                  autocomplete="organization"
                  required
                />
              </FormField>
            </div>

            <FormField
              label="Email"
              required
              :error="form.errors.email"
              :touched="touchedFields.email"
            >
              <input
                type="email"
                id="email"
                v-model.trim="form.email"
                @blur="handleBlur('email')"
                @input="clearFieldError('email')"
                placeholder="correo@ejemplo.com"
                autocomplete="email"
                :class="getInputClasses('email')"
                required
              />
            </FormField>

            <FormField
              label="Teléfono"
              :error="form.errors.telefono"
              :touched="touchedFields.telefono"
            >
              <input
                type="tel"
                id="telefono"
                v-model.trim="form.telefono"
                @blur="handleBlur('telefono')"
                @input="clearFieldError('telefono')"
                placeholder="Número telefónico (opcional)"
                autocomplete="tel"
                :class="getInputClasses('telefono')"
              />
            </FormField>
          </div>
        </div>

        <!-- Información Fiscal -->
        <div class="border-b border-gray-200 pb-6">
          <h2 class="text-lg font-medium text-gray-900 mb-4">Información Fiscal</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <FormField
              label="Tipo de Persona"
              required
              :error="form.errors.tipo_persona"
              :touched="touchedFields.tipo_persona"
            >
              <select
                id="tipo_persona"
                v-model="form.tipo_persona"
                @change="onTipoPersonaChange"
                @blur="handleBlur('tipo_persona')"
                :class="getSelectClasses('tipo_persona')"
                required
              >
                <option value="">Selecciona una opción</option>
                <option value="fisica">Persona Física</option>
                <option value="moral">Persona Moral</option>
              </select>
            </FormField>

            <FormField
              label="RFC"
              required
              :error="form.errors.rfc"
              :touched="touchedFields.rfc"
              :helper-text="!form.tipo_persona ? 'Primero selecciona el tipo de persona' : `Formato: ${rfcPlaceholder}`"
            >
              <input
                type="text"
                id="rfc"
                :maxlength="rfcMaxLength"
                :placeholder="rfcPlaceholder"
                :value="form.rfc"
                @input="onRfcInput"
                @blur="handleBlur('rfc')"
                :disabled="!form.tipo_persona"
                :class="getInputClasses('rfc', !form.tipo_persona)"
                required
              />
            </FormField>

            <FormField
              label="Régimen Fiscal"
              required
              :error="form.errors.regimen_fiscal"
              :touched="touchedFields.regimen_fiscal"
              :helper-text="!form.tipo_persona ? 'Primero selecciona el tipo de persona' : undefined"
            >
              <select
                id="regimen_fiscal"
                v-model="form.regimen_fiscal"
                @blur="handleBlur('regimen_fiscal')"
                @change="clearFieldError('regimen_fiscal')"
                :disabled="!form.tipo_persona"
                :class="getSelectClasses('regimen_fiscal', !form.tipo_persona)"
                required
              >
                <option value="">Selecciona una opción</option>
                <option
                  v-for="regimen in regimenesFiltrados"
                  :key="regimen.value"
                  :value="regimen.value"
                >
                  {{ regimen.text }}
                </option>
              </select>
            </FormField>

            <FormField
              label="Uso CFDI"
              required
              :error="form.errors.uso_cfdi"
              :touched="touchedFields.uso_cfdi"
            >
              <select
                id="uso_cfdi"
                v-model="form.uso_cfdi"
                @blur="handleBlur('uso_cfdi')"
                @change="clearFieldError('uso_cfdi')"
                :class="getSelectClasses('uso_cfdi')"
                required
              >
                <option value="">Selecciona una opción</option>
                <option
                  v-for="uso in usosCFDI"
                  :key="uso.value"
                  :value="uso.value"
                >
                  {{ uso.text }}
                </option>
              </select>
            </FormField>
          </div>
        </div>

        <!-- Dirección -->
        <div>
          <h2 class="text-lg font-medium text-gray-900 mb-4">Dirección</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="md:col-span-2">
              <FormField
                label="Calle"
                required
                :error="form.errors.calle"
                :touched="touchedFields.calle"
              >
                <input
                  type="text"
                  id="calle"
                  v-model.trim="form.calle"
                  @blur="handleBlur('calle')"
                  @input="clearFieldError('calle')"
                  :class="getInputClasses('calle')"
                  placeholder="Nombre de la calle"
                  required
                />
              </FormField>
            </div>

            <FormField
              label="Número Exterior"
              required
              :error="form.errors.numero_exterior"
              :touched="touchedFields.numero_exterior"
            >
              <input
                type="text"
                id="numero_exterior"
                v-model.trim="form.numero_exterior"
                @blur="handleBlur('numero_exterior')"
                @input="clearFieldError('numero_exterior')"
                :class="getInputClasses('numero_exterior')"
                placeholder="Núm. ext."
                required
              />
            </FormField>

            <FormField
              label="Número Interior"
              :error="form.errors.numero_interior"
              :touched="touchedFields.numero_interior"
            >
              <input
                type="text"
                id="numero_interior"
                v-model.trim="form.numero_interior"
                @blur="handleBlur('numero_interior')"
                @input="clearFieldError('numero_interior')"
                :class="getInputClasses('numero_interior')"
                placeholder="Núm. int. (opcional)"
              />
            </FormField>

            <FormField
              label="Colonia"
              required
              :error="form.errors.colonia"
              :touched="touchedFields.colonia"
            >
              <input
                type="text"
                id="colonia"
                v-model.trim="form.colonia"
                @blur="handleBlur('colonia')"
                @input="clearFieldError('colonia')"
                :class="getInputClasses('colonia')"
                placeholder="Nombre de la colonia"
                required
              />
            </FormField>

            <FormField
              label="Código Postal"
              required
              :error="form.errors.codigo_postal"
              :touched="touchedFields.codigo_postal"
            >
              <input
                type="text"
                id="codigo_postal"
                maxlength="5"
                pattern="[0-9]{5}"
                :value="form.codigo_postal"
                @input="onCpInput"
                @blur="handleBlur('codigo_postal')"
                placeholder="12345"
                :class="getInputClasses('codigo_postal')"
                required
              />
            </FormField>

            <FormField
              label="Municipio"
              required
              :error="form.errors.municipio"
              :touched="touchedFields.municipio"
            >
              <input
                type="text"
                id="municipio"
                v-model.trim="form.municipio"
                @blur="handleBlur('municipio')"
                @input="clearFieldError('municipio')"
                :class="getInputClasses('municipio')"
                placeholder="Nombre del municipio"
                required
              />
            </FormField>

            <FormField
              label="Estado"
              required
              :error="form.errors.estado"
              :touched="touchedFields.estado"
            >
              <select
                id="estado"
                v-model="form.estado"
                @blur="handleBlur('estado')"
                @change="clearFieldError('estado')"
                :class="getSelectClasses('estado')"
                required
              >
                <option value="">Selecciona una opción</option>
                <option
                  v-for="estado in estados"
                  :key="estado.value"
                  :value="estado.value"
                >
                  {{ estado.text }}
                </option>
              </select>
            </FormField>

            <FormField
              label="País"
              required
              :error="form.errors.pais"
              :touched="touchedFields.pais"
            >
              <input
                type="text"
                id="pais"
                v-model="form.pais"
                readonly
                class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 shadow-sm sm:text-sm cursor-not-allowed"
                tabindex="-1"
              />
            </FormField>
          </div>
        </div>

        <!-- Progress indicator -->
        <div class="bg-gray-50 p-4 rounded-md">
          <div class="flex items-center justify-between text-sm text-gray-600 mb-2">
            <span>Progreso del formulario</span>
            <span>{{ Math.round(formCompletionPercentage) }}%</span>
          </div>
          <div class="w-full bg-gray-200 rounded-full h-2">
            <div
              class="bg-blue-600 h-2 rounded-full transition-all duration-300 ease-out"
              :style="{ width: formCompletionPercentage + '%' }"
            ></div>
          </div>
        </div>

        <!-- Información de debug (solo en desarrollo) -->
        <div v-if="isDevelopment" class="bg-gray-50 p-4 rounded-md text-xs border-l-4 border-yellow-400">
          <h3 class="font-semibold mb-2 text-yellow-800">🐛 Debug Info:</h3>
          <div class="space-y-1 text-gray-700">
            <p><strong>Formulario válido:</strong> {{ isFormValid }}</p>
            <p><strong>Campos requeridos llenos:</strong> {{ requiredFieldsFilled }}</p>
            <p><strong>Sin errores:</strong> {{ !hasGlobalErrors }}</p>
            <p><strong>Procesando:</strong> {{ form.processing }}</p>
            <details class="mt-2">
              <summary class="cursor-pointer font-semibold">Valores de campos requeridos</summary>
              <ul class="ml-4 list-disc mt-2 space-y-1">
                <li v-for="field in requiredFields" :key="field" :class="{ 'text-red-600': !form[field] }">
                  <strong>{{ field }}:</strong> "{{ form[field] }}" ({{ typeof form[field] }})
                </li>
              </ul>
            </details>
          </div>
        </div>

        <!-- Botones de acción -->
        <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200">
          <button
            type="button"
            @click="resetForm"
            :disabled="form.processing"
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
          >
            <svg class="w-4 h-4 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            Limpiar
          </button>
          <button
            type="submit"
            :disabled="form.processing || !isFormValid"
            class="px-6 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 transform hover:scale-105 active:scale-95"
          >
            <span v-if="form.processing" class="flex items-center">
              <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Guardando...
            </span>
            <span v-else class="flex items-center">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
              </svg>
              Crear Cliente
            </span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import { computed, reactive, ref, watch } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import FormField from '@/Components/FormField.vue'

defineOptions({ layout: AppLayout })

const props = defineProps({
  catalogs: { type: Object, required: true },
  cliente: { type: Object, required: true }
})

// Estado reactivo
const showSuccessMessage = ref(false)
const isDevelopment = ref(import.meta.env?.DEV || false)
const touchedFields = reactive({})

// Labels para los campos (para mensajes de error más amigables)
const fieldLabels = {
  nombre_razon_social: 'Nombre/Razón Social',
  email: 'Email',
  telefono: 'Teléfono',
  tipo_persona: 'Tipo de Persona',
  rfc: 'RFC',
  regimen_fiscal: 'Régimen Fiscal',
  uso_cfdi: 'Uso CFDI',
  calle: 'Calle',
  numero_exterior: 'Número Exterior',
  numero_interior: 'Número Interior',
  colonia: 'Colonia',
  codigo_postal: 'Código Postal',
  municipio: 'Municipio',
  estado: 'Estado',
  pais: 'País'
}

// Inicializa form con valores por defecto seguros
const initFormData = () => ({
  nombre_razon_social: props.cliente?.nombre_razon_social || '',
  email: props.cliente?.email || '',
  telefono: props.cliente?.telefono || '',
  tipo_persona: props.cliente?.tipo_persona || '',
  rfc: props.cliente?.rfc || '',
  regimen_fiscal: props.cliente?.regimen_fiscal || '',
  uso_cfdi: props.cliente?.uso_cfdi || '',
  calle: props.cliente?.calle || '',
  numero_exterior: props.cliente?.numero_exterior || '',
  numero_interior: props.cliente?.numero_interior || '',
  colonia: props.cliente?.colonia || '',
  codigo_postal: props.cliente?.codigo_postal || '',
  municipio: props.cliente?.municipio || '',
  estado: props.cliente?.estado || '',
  pais: props.cliente?.pais || 'MX',
})

const form = useForm(initFormData())

// Computed properties
const hasGlobalErrors = computed(() => Object.keys(form.errors).length > 0)

const requiredFields = [
  'nombre_razon_social', 'email', 'tipo_persona', 'rfc',
  'regimen_fiscal', 'uso_cfdi', 'calle', 'numero_exterior',
  'colonia', 'codigo_postal', 'municipio', 'estado', 'pais'
]

const requiredFieldsFilled = computed(() => {
  return requiredFields.every(field => {
    const value = form[field]
    return value !== null &&
           value !== undefined &&
           String(value).trim() !== '' &&
           String(value).trim() !== 'null' &&
           String(value).trim() !== 'undefined'
  })
})

const isFormValid = computed(() => {
  return requiredFieldsFilled.value && !hasGlobalErrors.value && !form.processing
})

const formCompletionPercentage = computed(() => {
  const filledFields = requiredFields.filter(field => {
    const value = form[field]
    return value && String(value).trim() !== ''
  }).length

  return (filledFields / requiredFields.length) * 100
})

// Opciones para selects
const estados = computed(() => {
  return (props.catalogs?.estados || []).map(e => ({
    value: e.clave,
    text: `${e.clave} — ${e.nombre}`
  }))
})

const regimenesFiltrados = computed(() => {
  if (!form.tipo_persona) return []

  return (props.catalogs?.regimenesFiscales || [])
    .filter(r => {
      return form.tipo_persona === 'moral' ? r.persona_moral : r.persona_fisica
    })
    .map(r => ({
      value: r.clave,
      text: `${r.clave} — ${r.descripcion}`
    }))
})

const usosCFDI = computed(() => {
  return (props.catalogs?.usosCFDI || []).map(u => ({
    value: u.clave,
    text: `${u.clave} — ${u.descripcion}`
  }))
})

// Configuración dinámica del RFC
const rfcMaxLength = computed(() => form.tipo_persona === 'fisica' ? 13 : 12)
const rfcPlaceholder = computed(() =>
  form.tipo_persona === 'fisica' ? 'ABCD123456EFG' : 'ABC123456EF'
)

// Watchers
watch(() => form.tipo_persona, (newVal, oldVal) => {
  if (newVal !== oldVal) {
    form.rfc = ''
    form.regimen_fiscal = ''
    form.clearErrors(['rfc', 'regimen_fiscal'])
  }
})

// Funciones utilitarias
const getFieldLabel = (fieldName) => {
  return fieldLabels[fieldName] || fieldName
}

const getInputClasses = (fieldName, disabled = false) => {
  const baseClasses = 'mt-1 block w-full rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm transition-colors'
  const errorClasses = form.errors[fieldName] ? 'border-red-300 bg-red-50 focus:border-red-500 focus:ring-red-500' : 'border-gray-300'
  const disabledClasses = disabled ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : ''

  return `${baseClasses} ${errorClasses} ${disabledClasses}`
}

const getSelectClasses = (fieldName, disabled = false) => {
  return getInputClasses(fieldName, disabled)
}

const handleBlur = (fieldName) => {
  touchedFields[fieldName] = true

  // Convertir a mayúsculas si es un campo de texto que lo requiere
  const upperCaseFields = ['nombre_razon_social', 'calle', 'numero_exterior', 'numero_interior', 'colonia', 'municipio']
  if (upperCaseFields.includes(fieldName) && form[fieldName]) {
    form[fieldName] = String(form[fieldName]).toUpperCase().trim()
  }
}

const clearFieldError = (fieldName) => {
  if (form.errors[fieldName]) {
    form.clearErrors(fieldName)
  }
}

// Funciones de manejo de input
const onTipoPersonaChange = () => {
  form.rfc = ''
  form.regimen_fiscal = ''
  form.clearErrors(['rfc', 'regimen_fiscal'])
  touchedFields.tipo_persona = true
}

const onRfcInput = (event) => {
  const value = event.target ? event.target.value : event
  const cleaned = String(value).toUpperCase().replace(/[^A-ZÑ&0-9]/g, '')
  const maxLen = rfcMaxLength.value
  form.rfc = cleaned.slice(0, maxLen)

  clearFieldError('rfc')
}

const onCpInput = (event) => {
  const value = event.target ? event.target.value : event
  const digits = String(value).replace(/\D/g, '')
  form.codigo_postal = digits.slice(0, 5)

  clearFieldError('codigo_postal')
}

// Funciones principales
const resetForm = () => {
  const newData = initFormData()
  Object.keys(newData).forEach(key => {
    form[key] = newData[key]
  })
  form.clearErrors()
  showSuccessMessage.value = false

  // Limpiar campos tocados
  Object.keys(touchedFields).forEach(key => {
    touchedFields[key] = false
  })
}

const submit = () => {
  // Marcar todos los campos como tocados para mostrar errores
  requiredFields.forEach(field => {
    touchedFields[field] = true
  })

  // Limpiar mensajes previos
  showSuccessMessage.value = false

  // Validación antes de enviar
  if (!isFormValid.value) {
    console.warn('Formulario no válido, evitando envío')
    return
  }

  // Limpiar y normalizar datos antes del envío
  const dataToSend = { ...form.data() }

  // Asegurar que todos los campos requeridos tengan valores válidos
  const missingFields = requiredFields.filter(field => {
    const value = dataToSend[field]
    return !value || String(value).trim() === ''
  })

  if (missingFields.length > 0) {
    console.error('Campos requeridos faltantes:', missingFields)
    return
  }

  form.post(route('clientes.store'), {
    preserveScroll: true,
    onSuccess: () => {
      showSuccessMessage.value = true
      setTimeout(() => {
        showSuccessMessage.value = false
        resetForm()
      }, 3000)
    },
    onError: (errors) => {
      console.error('Errores de validación:', errors)

      // Marcar campos con errores como tocados
      Object.keys(errors).forEach(field => {
        touchedFields[field] = true
      })
    }
  })
}
</script>
