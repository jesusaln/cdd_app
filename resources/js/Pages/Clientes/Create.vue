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
        <div class="flex">
          <div class="ml-3">
            <h3 class="text-sm font-medium text-red-800">Error en el formulario</h3>
            <div class="mt-2 text-sm text-red-700">
              <ul class="list-disc list-inside space-y-1">
                <li v-for="(error, key) in form.errors" :key="key">
                  {{ Array.isArray(error) ? error[0] : error }}
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>

      <!-- Mensaje de éxito -->
      <div
        v-if="showSuccessMessage"
        class="mb-6 p-4 bg-green-50 border border-green-200 rounded-md"
        aria-live="polite"
      >
        <p class="text-sm font-medium text-green-800">Cliente creado exitosamente</p>
      </div>

      <form @submit.prevent="submit" class="space-y-8">
        <!-- Información General -->
        <div class="border-b border-gray-200 pb-6">
          <h2 class="text-lg font-medium text-gray-900 mb-4">Información General</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2">
              <div class="mb-4">
                <label for="nombre_razon_social" class="block text-sm font-medium text-gray-700">
                  Nombre/Razón Social <span class="text-red-500">*</span>
                </label>
                <input
                  type="text"
                  id="nombre_razon_social"
                  v-model="form.nombre_razon_social"
                  @blur="toUpper('nombre_razon_social')"
                  :class="[
                    'mt-1 block w-full rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm',
                    form.errors.nombre_razon_social ? 'border-red-300 bg-red-50' : 'border-gray-300'
                  ]"
                  required
                />
                <div v-if="form.errors.nombre_razon_social" class="mt-2 text-sm text-red-600">
                  {{ form.errors.nombre_razon_social }}
                </div>
              </div>
            </div>

            <div class="mb-4">
              <label for="email" class="block text-sm font-medium text-gray-700">
                Email <span class="text-red-500">*</span>
              </label>
              <input
               type="email"
               id="email"
               v-model="form.email"
               placeholder="correo@ejemplo.com"
               :class="[
                  'mt-1 block w-full rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm',
                  form.errors.email ? 'border-red-300 bg-red-50' : 'border-gray-300'
                ]"
                required
              />
              <div v-if="form.errors.email" class="mt-2 text-sm text-red-600">
                {{ form.errors.email }}
              </div>
            </div>

            <div class="mb-4">
              <label for="telefono" class="block text-sm font-medium text-gray-700">
                Teléfono
              </label>
              <input
               type="tel"
               id="telefono"
               v-model="form.telefono"
               placeholder="Opcional"
               :class="[
                  'mt-1 block w-full rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm',
                  form.errors.telefono ? 'border-red-300 bg-red-50' : 'border-gray-300'
                ]"
              />
              <div v-if="form.errors.telefono" class="mt-2 text-sm text-red-600">
                {{ form.errors.telefono }}
              </div>
            </div>
          </div>
        </div>

        <!-- Información Fiscal -->
        <div class="border-b border-gray-200 pb-6">
          <h2 class="text-lg font-medium text-gray-900 mb-4">Información Fiscal</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="mb-4">
              <label for="tipo_persona" class="block text-sm font-medium text-gray-700">
                Tipo de Persona <span class="text-red-500">*</span>
              </label>
              <select
                id="tipo_persona"
                v-model="form.tipo_persona"
                @change="onTipoPersonaChange"
                :class="[
                  'mt-1 block w-full rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm',
                  form.errors.tipo_persona ? 'border-red-300 bg-red-50' : 'border-gray-300'
                ]"
                required
              >
                <option value="">Selecciona una opción</option>
                <option value="fisica">Persona Física</option>
                <option value="moral">Persona Moral</option>
              </select>
              <div v-if="form.errors.tipo_persona" class="mt-2 text-sm text-red-600">
                {{ form.errors.tipo_persona }}
              </div>
            </div>

            <div class="mb-4">
              <label for="rfc" class="block text-sm font-medium text-gray-700">
                RFC <span class="text-red-500">*</span>
              </label>
              <input
                type="text"
                id="rfc"
                :maxlength="rfcMaxLength"
                :placeholder="rfcPlaceholder"
                :value="form.rfc"
                @input="onRfcInput"
                :disabled="!form.tipo_persona"
                :class="[
                  'mt-1 block w-full rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm',
                  form.errors.rfc ? 'border-red-300 bg-red-50' : 'border-gray-300',
                  !form.tipo_persona ? 'bg-gray-100 text-gray-400' : ''
                ]"
                required
              />
              <div v-if="form.errors.rfc" class="mt-2 text-sm text-red-600">
                {{ form.errors.rfc }}
              </div>
              <div v-if="!form.tipo_persona" class="mt-1 text-xs text-gray-500">
                Primero selecciona el tipo de persona
              </div>
            </div>

            <!-- NUEVO: CURP -->
            <div class="mb-4">
              <label for="curp" class="block text-sm font-medium text-gray-700">
                CURP
                <span v-if="form.tipo_persona === 'fisica'" class="text-gray-400">(opcional)</span>
              </label>
              <input
                type="text"
                id="curp"
                :maxlength="18"
                :placeholder="form.tipo_persona === 'fisica' ? 'ABCD880321HXXLRN09' : 'No aplica para Persona Moral'"
                :value="form.curp"
                @input="onCurpInput"
                :disabled="form.tipo_persona === 'moral' || !form.tipo_persona"
                :class="[
                  'mt-1 block w-full rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm',
                  form.errors.curp ? 'border-red-300 bg-red-50' : 'border-gray-300',
                  (form.tipo_persona === 'moral' || !form.tipo_persona) ? 'bg-gray-100 text-gray-400' : ''
                ]"
              />
              <div v-if="form.errors.curp" class="mt-2 text-sm text-red-600">
                {{ form.errors.curp }}
              </div>
              <div class="mt-1 text-xs text-gray-500">
                {{ curpHelperText }}
              </div>
            </div>
            <!-- /NUEVO: CURP -->

            <div class="mb-4">
              <label for="regimen_fiscal" class="block text-sm font-medium text-gray-700">
                Régimen Fiscal <span class="text-red-500">*</span>
              </label>
              <select
                id="regimen_fiscal"
                v-model="form.regimen_fiscal"
                :disabled="!form.tipo_persona"
                :class="[
                  'mt-1 block w-full rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm',
                  form.errors.regimen_fiscal ? 'border-red-300 bg-red-50' : 'border-gray-300',
                  !form.tipo_persona ? 'bg-gray-100 text-gray-400' : ''
                ]"
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
              <div v-if="form.errors.regimen_fiscal" class="mt-2 text-sm text-red-600">
                {{ form.errors.regimen_fiscal }}
              </div>
              <div v-if="!form.tipo_persona" class="mt-1 text-xs text-gray-500">
                Primero selecciona el tipo de persona
              </div>
            </div>

            <div class="mb-4">
              <label for="uso_cfdi" class="block text-sm font-medium text-gray-700">
                Uso CFDI <span class="text-red-500">*</span>
              </label>
              <select
                id="uso_cfdi"
                v-model="form.uso_cfdi"
                :class="[
                  'mt-1 block w-full rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm',
                  form.errors.uso_cfdi ? 'border-red-300 bg-red-50' : 'border-gray-300'
                ]"
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
              <div v-if="form.errors.uso_cfdi" class="mt-2 text-sm text-red-600">
                {{ form.errors.uso_cfdi }}
              </div>
            </div>
          </div>
        </div>

        <!-- Dirección -->
        <div>
          <h2 class="text-lg font-medium text-gray-900 mb-4">Dirección</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="md:col-span-2">
              <div class="mb-4">
                <label for="calle" class="block text-sm font-medium text-gray-700">
                  Calle <span class="text-red-500">*</span>
                </label>
                <input
                  type="text"
                  id="calle"
                  v-model="form.calle"
                  @blur="toUpper('calle')"
                  :class="[
                    'mt-1 block w-full rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm',
                    form.errors.calle ? 'border-red-300 bg-red-50' : 'border-gray-300'
                  ]"
                  required
                />
                <div v-if="form.errors.calle" class="mt-2 text-sm text-red-600">
                  {{ form.errors.calle }}
                </div>
              </div>
            </div>

            <div class="mb-4">
              <label for="numero_exterior" class="block text-sm font-medium text-gray-700">
                Número Exterior <span class="text-red-500">*</span>
              </label>
              <input
                type="text"
                id="numero_exterior"
                v-model="form.numero_exterior"
                @blur="toUpper('numero_exterior')"
                :class="[
                  'mt-1 block w-full rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm',
                  form.errors.numero_exterior ? 'border-red-300 bg-red-50' : 'border-gray-300'
                ]"
                required
              />
              <div v-if="form.errors.numero_exterior" class="mt-2 text-sm text-red-600">
                {{ form.errors.numero_exterior }}
              </div>
            </div>

            <div class="mb-4">
              <label for="numero_interior" class="block text-sm font-medium text-gray-700">
                Número Interior
              </label>
              <input
                type="text"
                id="numero_interior"
                v-model="form.numero_interior"
                @blur="toUpper('numero_interior')"
                :class="[
                  'mt-1 block w-full rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm',
                  form.errors.numero_interior ? 'border-red-300 bg-red-50' : 'border-gray-300'
                ]"
              />
              <div v-if="form.errors.numero_interior" class="mt-2 text-sm text-red-600">
                {{ form.errors.numero_interior }}
              </div>
            </div>

            <div class="mb-4">
              <label for="colonia" class="block text-sm font-medium text-gray-700">
                Colonia <span class="text-red-500">*</span>
              </label>
              <select
                id="colonia"
                v-model="form.colonia"
                :disabled="availableColonias.length === 0"
                :class="[
                  'mt-1 block w-full rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm',
                  form.errors.colonia ? 'border-red-300 bg-red-50' : 'border-gray-300',
                  availableColonias.length === 0 ? 'bg-gray-100 text-gray-400' : ''
                ]"
                required
              >
                <option value="">
                  {{ availableColonias.length === 0 ? 'Ingresa un código postal primero' : 'Selecciona una colonia' }}
                </option>
                <option
                  v-for="colonia in availableColonias"
                  :key="colonia"
                  :value="colonia"
                >
                  {{ colonia }}
                </option>
              </select>
              <div v-if="form.errors.colonia" class="mt-2 text-sm text-red-600">
                {{ form.errors.colonia }}
              </div>
              <div v-if="availableColonias.length === 0" class="mt-1 text-xs text-gray-500">
                Primero ingresa un código postal válido para cargar las colonias disponibles
              </div>
            </div>

            <div class="mb-4">
              <label for="codigo_postal" class="block text-sm font-medium text-gray-700">
                Código Postal <span class="text-red-500">*</span>
              </label>
              <input
                type="text"
                id="codigo_postal"
                maxlength="5"
                pattern="[0-9]{5}"
                :value="form.codigo_postal"
                @input="onCpInput"
                placeholder="12345"
                :class="[
                  'mt-1 block w-full rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm',
                  form.errors.codigo_postal ? 'border-red-300 bg-red-50' : 'border-gray-300'
                ]"
                required
              />
              <div v-if="form.errors.codigo_postal" class="mt-2 text-sm text-red-600">
                {{ form.errors.codigo_postal }}
              </div>
            </div>

            <div class="mb-4">
              <label for="municipio" class="block text-sm font-medium text-gray-700">
                Municipio <span class="text-red-500">*</span>
              </label>
              <input
                type="text"
                id="municipio"
                v-model="form.municipio"
                :class="[
                  'mt-1 block w-full rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm',
                  form.errors.municipio ? 'border-red-300 bg-red-50' : 'border-gray-300'
                ]"
                required
              />
              <div v-if="form.errors.municipio" class="mt-2 text-sm text-red-600">
                {{ form.errors.municipio }}
              </div>
            </div>

            <div class="mb-4">
              <label for="estado" class="block text-sm font-medium text-gray-700">
                Estado <span class="text-red-500">*</span>
              </label>
              <select
                id="estado"
                v-model="form.estado"
                :class="[
                  'mt-1 block w-full rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm',
                  form.errors.estado ? 'border-red-300 bg-red-50' : 'border-gray-300'
                ]"
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
              <div v-if="form.errors.estado" class="mt-2 text-sm text-red-600">
                {{ form.errors.estado }}
              </div>
            </div>

            <div class="mb-4">
              <label for="pais" class="block text-sm font-medium text-gray-700">
                País <span class="text-red-500">*</span>
              </label>
              <input
                type="text"
                id="pais"
                v-model="form.pais"
                readonly
                class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 shadow-sm sm:text-sm"
              />
              <div v-if="form.errors.pais" class="mt-2 text-sm text-red-600">
                {{ form.errors.pais }}
              </div>
            </div>
          </div>
        </div>

        <!-- Información de debug (remover en producción) -->
        <div v-if="isDevelopment" class="bg-gray-50 p-4 rounded-md text-xs">
          <h3 class="font-semibold mb-2">Debug Info:</h3>
          <p>Formulario válido: {{ isFormValid }}</p>
          <p>Campos requeridos llenos: {{ requiredFieldsFilled }}</p>
          <p>Sin errores: {{ !hasGlobalErrors }}</p>
          <div class="mt-2">
            <strong>Valores actuales:</strong>
            <ul class="ml-4 list-disc">
              <li v-for="field in requiredFields" :key="field">
                {{ field }}: "{{ form[field] }}" ({{ typeof form[field] }})
              </li>
            </ul>
          </div>
        </div>

        <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200">
          <button
            type="button"
            @click="resetForm"
            :disabled="form.processing"
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Limpiar
          </button>
          <button
            type="submit"
            :disabled="form.processing || !isFormValid"
            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span v-if="form.processing" class="flex items-center">
              <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Guardando...
            </span>
            <span v-else>Crear Cliente</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
// import FormField from '@/Components/FormField.vue' // (No se usa en esta versión)

defineOptions({ layout: AppLayout })

const props = defineProps({
  catalogs: { type: Object, required: true },
  cliente: { type: Object, required: true }
})

const showSuccessMessage = ref(false)
const isDevelopment = ref(import.meta.env?.DEV || false)
const availableColonias = ref([])

// Mapeo de nombres de estados a claves SAT
const estadoMapping = {
  'Aguascalientes': 'AGU',
  'Baja California': 'BCN',
  'Baja California Sur': 'BCS',
  'Campeche': 'CAM',
  'Chihuahua': 'CHH',
  'Chiapas': 'CHP',
  'Ciudad de México': 'CMX',
  'Coahuila': 'COA',
  'Colima': 'COL',
  'Durango': 'DUR',
  'Guerrero': 'GRO',
  'Guanajuato': 'GUA',
  'Hidalgo': 'HID',
  'Jalisco': 'JAL',
  'Estado de México': 'MEX',
  'Michoacán': 'MIC',
  'Morelos': 'MOR',
  'Nayarit': 'NAY',
  'Nuevo León': 'NLE',
  'Oaxaca': 'OAX',
  'Puebla': 'PUE',
  'Querétaro': 'QUE',
  'Quintana Roo': 'ROO',
  'Sinaloa': 'SIN',
  'San Luis Potosí': 'SLP',
  'Sonora': 'SON',
  'Tabasco': 'TAB',
  'Tamaulipas': 'TAM',
  'Tlaxcala': 'TLA',
  'Veracruz': 'VER',
  'Yucatán': 'YUC',
  'Zacatecas': 'ZAC'
}

// Inicializa form con valores por defecto seguros
const initFormData = () => ({
  nombre_razon_social: props.cliente?.nombre_razon_social || '',
  email: props.cliente?.email || '',
  telefono: props.cliente?.telefono || '',
  tipo_persona: props.cliente?.tipo_persona || '',
  rfc: props.cliente?.rfc || '',
  curp: props.cliente?.curp || '',                 // <<< NUEVO
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

const hasGlobalErrors = computed(() => Object.keys(form.errors).length > 0)

const requiredFields = [
  'nombre_razon_social', 'email', 'tipo_persona', 'rfc',
  'regimen_fiscal', 'uso_cfdi', 'calle', 'numero_exterior',
  'colonia', 'codigo_postal', 'municipio', 'estado', 'pais'
]
// Nota: CURP NO es requerida (backend la valida como nullable)

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

// Opciones para selects
const estados = computed(() => {
  return (props.catalogs?.estados || []).map(e => ({
    value: e.nombre,
    text: e.nombre
  }))
})

const regimenesFiltrados = computed(() => {
  if (!form.tipo_persona) return []

  return (props.catalogs?.regimenesFiscales || [])
    .filter(r => (form.tipo_persona === 'moral' ? r.persona_moral : r.persona_fisica))
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

// Configuración dinámica RFC/CURP
const rfcMaxLength = computed(() => form.tipo_persona === 'fisica' ? 13 : 12)
const rfcPlaceholder = computed(() => form.tipo_persona === 'fisica' ? 'ABCD123456EFG' : 'ABC123456EF')
const curpHelperText = computed(() =>
  form.tipo_persona === 'moral'
    ? 'CURP no aplica para Personas Morales.'
    : 'Formato CURP: 18 caracteres (solo A–Z y 0–9).'
)

// Watchers
watch(() => form.tipo_persona, (newVal, oldVal) => {
  if (newVal !== oldVal) {
    form.rfc = ''
    form.regimen_fiscal = ''
    form.clearErrors(['rfc', 'regimen_fiscal'])

    // Si cambia a moral, limpiamos CURP y deshabilitamos
    if (newVal === 'moral') {
      form.curp = ''
      form.clearErrors('curp')
    }
  }
})

// Handlers
const onTipoPersonaChange = () => {
  form.rfc = ''
  form.regimen_fiscal = ''
  form.clearErrors(['rfc', 'regimen_fiscal'])
}

const onRfcInput = (event) => {
  const value = event.target ? event.target.value : event
  const cleaned = String(value).toUpperCase().replace(/[^A-ZÑ&0-9]/g, '')
  const maxLen = rfcMaxLength.value
  form.rfc = cleaned.slice(0, maxLen)
  if (form.rfc && form.errors.rfc) form.clearErrors('rfc')
}

// NUEVO: normalización CURP
const onCurpInput = (event) => {
  const value = event.target ? event.target.value : event
  form.curp = String(value).toUpperCase().replace(/[^A-Z0-9]/g, '').slice(0, 18)
  if (form.curp && form.errors.curp) form.clearErrors('curp')
}

const onCpInput = async (event) => {
  const value = event.target ? event.target.value : event
  const digits = String(value).replace(/\D/g, '')
  form.codigo_postal = digits.slice(0, 5)
  if (form.codigo_postal && form.errors.codigo_postal) form.clearErrors('codigo_postal')

  // Autocompletar cuando tenga 5 dígitos
  if (form.codigo_postal.length === 5) {
    try {
      const response = await fetch(`/api/cp/${form.codigo_postal}`)
      if (response.ok) {
        const data = await response.json()
        form.estado = data.estado
        form.municipio = data.municipio
        form.pais = data.pais

        // Poblar lista de colonias disponibles
        availableColonias.value = data.colonias || []

        // Si solo hay una colonia, la seleccionamos automáticamente
        if (data.colonias && data.colonias.length === 1) {
          form.colonia = data.colonias[0]
        } else if (data.colonias && data.colonias.length > 1) {
          // Si hay múltiples colonias, limpiar selección actual
          form.colonia = ''
        }

        // Limpiar errores de campos autocompletados
        form.clearErrors(['estado', 'municipio', 'pais'])
      } else {
        // Si hay error, limpiar colonias disponibles
        availableColonias.value = []
        form.colonia = ''
      }
    } catch (error) {
      console.warn('Error al consultar código postal:', error)
      // Limpiar colonias disponibles en caso de error
      availableColonias.value = []
      form.colonia = ''
    }
  } else {
    // Si el CP no tiene 5 dígitos, limpiar colonias
    availableColonias.value = []
    form.colonia = ''
  }
}

const toUpper = (campo) => {
  if (form[campo] && typeof form[campo] === 'string') {
    form[campo] = form[campo].toUpperCase().trim()
    if (form[campo] && form.errors[campo]) form.clearErrors(campo)
  }
}

// Acciones
const resetForm = () => {
  const newData = initFormData()
  Object.keys(newData).forEach(key => { form[key] = newData[key] })
  form.clearErrors()
  showSuccessMessage.value = false
  availableColonias.value = []
}

const submit = () => {
  showSuccessMessage.value = false
  if (!isFormValid.value) {
    console.warn('Formulario no válido, evitando envío')
    return
  }

  // (opcional) verificación local de requeridos
  const dataToSend = { ...form.data() }
  requiredFields.forEach(field => {
    if (!dataToSend[field] || String(dataToSend[field]).trim() === '') {
      console.error(`Campo requerido vacío: ${field}`)
      return
    }
  })

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
    }
  })
}
</script>
