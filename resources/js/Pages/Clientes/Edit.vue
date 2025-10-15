<template>
  <Head title="Editar Cliente" />
  <div class="max-w-4xl mx-auto p-4">
    <div class="bg-white shadow-sm rounded-lg p-6">
      <div class="flex items-center justify-between mb-6">
        <div>
          <h1 class="text-2xl font-semibold text-gray-800">Editar Cliente</h1>

        </div>
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
        <p class="text-sm font-medium text-green-800">Cliente actualizado exitosamente</p>
      </div>

      <form @submit.prevent="submit" class="space-y-8">
        <!-- Información General -->
        <div class="border-b border-gray-200 pb-6">
          <h2 class="text-lg font-medium text-gray-900 mb-4">Información General</h2>

          <!-- Checkbox para factura -->
          <div class="mb-6">
            <div class="flex items-center">
              <input
                type="checkbox"
                id="requiere_factura"
                v-model="form.requiere_factura"
                @change="onFacturaChange"
                :class="[
                  'h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded'
                ]"
              />
              <label for="requiere_factura" class="ml-2 block text-sm font-medium text-gray-700">
                ¿Requiere factura? <span class="text-red-500">*</span>
              </label>
            </div>
            <div class="mt-1 text-sm text-gray-500">
              Marque esta opción si el cliente necesita facturación electrónica
            </div>
          </div>
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
                  autocomplete="off"
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
                Email <span v-if="form.requiere_factura" class="text-red-500">*</span>
                <span v-if="form.requiere_factura" class="text-gray-400">(requerido para facturación)</span>
              </label>
              <input
               type="email"
               id="email"
               v-model="form.email"
               @blur="() => { normalizeEmail(); validateEmail(form.email); }"
               placeholder="correo@ejemplo.com"
               autocomplete="new-password"
               :class="[
                  'mt-1 block w-full rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm',
                  form.errors.email ? 'border-red-300 bg-red-50' : 'border-gray-300'
                ]"
                :required="form.requiere_factura"
              />
              <div v-if="form.errors.email" class="mt-2 text-sm text-red-600">
                {{ form.errors.email }}
              </div>
            </div>

            <div class="mb-4">
              <label for="telefono" class="block text-sm font-medium text-gray-700">
                Teléfono <span v-if="form.requiere_factura" class="text-red-500">*</span>
                <span v-if="form.requiere_factura" class="text-gray-400">(requerido para facturación)</span>
              </label>
              <input
                type="tel"
                id="telefono"
                v-model="form.telefono"
                @input="validateTelefono"
                maxlength="10"
                placeholder="10 dígitos (opcional)"
                pattern="[0-9]{10}"
                autocomplete="tel-national"
                :class="[
                   'mt-1 block w-full rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm',
                   form.errors.telefono ? 'border-red-300 bg-red-50' : 'border-gray-300'
                 ]"
                 :required="form.requiere_factura"
              />
              <div v-if="form.errors.telefono" class="mt-2 text-sm text-red-600">
                {{ form.errors.telefono }}
              </div>
            </div>
          </div>
        </div>

        <!-- Estado del Cliente -->
        <div class="border-b border-gray-200 pb-6">
          <h2 class="text-lg font-medium text-gray-900 mb-4">Estado del Cliente</h2>
          <div class="grid grid-cols-1 gap-6">
            <div class="mb-4">
              <label class="inline-flex items-center">
                <input
                  type="checkbox"
                  v-model="form.activo"
                  class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                />
                <span class="ml-2 text-sm font-medium text-gray-700">Cliente Activo</span>
              </label>
              <p class="mt-1 text-xs text-gray-500">
                Desmarca para inactivar el cliente. Los clientes inactivos no aparecerán en listas por defecto.
              </p>
            </div>
          </div>
        </div>

        <!-- Información Fiscal -->
        <div v-if="form.requiere_factura" class="border-b border-gray-200 pb-6">
          <h2 class="text-lg font-medium text-gray-900 mb-4">Información Fiscal</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="mb-4">
              <label for="tipo_persona" class="block text-sm font-medium text-gray-700">
                Tipo de Persona <span v-if="form.requiere_factura" class="text-red-500">*</span>
                <span v-if="!form.requiere_factura" class="text-gray-400">(opcional)</span>
              </label>
              <select
                id="tipo_persona"
                v-model="form.tipo_persona"
                @change="onTipoPersonaChange"
                :class="[
                  'mt-1 block w-full rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm',
                  form.errors.tipo_persona ? 'border-red-300 bg-red-50' : 'border-gray-300'
                ]"
                :required="form.requiere_factura"
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
                v-model="form.rfc"
                :maxlength="rfcMaxLength"
                :placeholder="rfcPlaceholder"
                @input="onRfcInput"
                @blur="() => { toUpper('rfc'); validateRfc(form.rfc, props.cliente.id) }"
                :disabled="!form.tipo_persona"
                autocomplete="off"
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

            <div class="mb-4">
              <label for="curp" class="block text-sm font-medium text-gray-700">
                CURP
              </label>
              <input
                type="text"
                id="curp"
                v-model="form.curp"
                @input="onCurpInput"
                @blur="toUpper('curp')"
                :disabled="form.tipo_persona === 'moral'"
                :maxlength="18"
                :placeholder="form.tipo_persona === 'fisica' ? 'ABCD123456HMEFGH99' : 'Opcional'"
                autocomplete="off"
                :class="[
                  'mt-1 block w-full rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm',
                  form.errors.curp ? 'border-red-300 bg-red-50' : 'border-gray-300',
                  form.tipo_persona === 'moral' ? 'bg-gray-100 text-gray-400' : ''
                ]"
              />
              <div v-if="form.errors.curp" class="mt-2 text-sm text-red-600">
                {{ form.errors.curp }}
              </div>
              <div v-if="form.tipo_persona === 'moral'" class="mt-1 text-xs text-gray-500">
                Opcional para personas morales
              </div>
            </div>

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
                  autocomplete="off"
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
                autocomplete="off"
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
                autocomplete="off"
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
                autocomplete="off"
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
                autocomplete="off"
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
                Estado
              </label>
              <select
                id="estado"
                v-model="form.estado"
                class="mt-1 block w-full rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm border-gray-300"
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
              <div class="mt-1 text-xs text-gray-500">
                Opcional para clientes extranjeros
              </div>
            </div>

            <div class="mb-4">
              <label for="pais" class="block text-sm font-medium text-gray-700">
                País
              </label>
              <input
                type="text"
                id="pais"
                v-model="form.pais"
                @blur="toUpper('pais')"
                placeholder="MX (usa MEX sólo si tu backend lo admite)"
                autocomplete="new-password"
                :class="[
                  'mt-1 block w-full rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm',
                  form.errors.pais ? 'border-red-300 bg-red-50' : 'border-gray-300'
                ]"
              />
              <div v-if="form.errors.pais" class="mt-2 text-sm text-red-600">
                {{ form.errors.pais }}
              </div>
              <div class="mt-1 text-xs text-gray-500">
                Código de país (2-3 letras). MX para México, USA para Estados Unidos, etc.
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
          <p>Datos modificados: {{ form.isDirty }}</p>
          <div class="mt-2">
            <strong>Valores actuales:</strong>
            <ul class="ml-4 list-disc">
              <li v-for="field in (Array.isArray(requiredFields) ? requiredFields : [])" :key="field">
                {{ field }}: "{{ form[field] }}" ({{ typeof form[field] }})
              </li>
            </ul>
          </div>
        </div>

        <div class="flex justify-between space-x-4 pt-6 border-t border-gray-200">
          <div>
            <Link
              :href="route('clientes.show', cliente.id)"
              class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
              </svg>
              Volver al Cliente
            </Link>
          </div>
          <div class="flex space-x-4">
            <button
              type="button"
              @click="resetForm"
              :disabled="form.processing"
              class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Restaurar
            </button>
            <button
              type="submit"
              :disabled="form.processing || !isFormValid || !form.isDirty"
              class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <span v-if="form.processing" class="flex items-center">
                <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Guardando...
              </span>
              <span v-else>Actualizar Cliente</span>
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import axios from 'axios'
import { computed, ref, watch, onMounted } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'

defineOptions({ layout: AppLayout })

const props = defineProps({
  catalogs: { type: Object, required: true },
  cliente: { type: Object, required: true }
})

const showSuccessMessage = ref(false)
const isDevelopment = ref(import.meta.env?.DEV || false)
const availableColonias = ref([])

// Mapeo de estados mexicanos con códigos SAT
const estadoMapping = {
  'Aguascalientes': 'AGU',
  'Baja California': 'BCN',
  'Baja California Sur': 'BCS',
  'Campeche': 'CAM',
  'Chiapas': 'CHP',
  'Chihuahua': 'CHH',
  'Coahuila': 'COA',
  'Colima': 'COL',
  'Ciudad de México': 'CDMX',
  'Durango': 'DUR',
  'Guanajuato': 'GUA',
  'Guerrero': 'GRO',
  'Hidalgo': 'HID',
  'Jalisco': 'JAL',
  'México': 'MEX',
  'Michoacán': 'MIC',
  'Morelos': 'MOR',
  'Nayarit': 'NAY',
  'Nuevo León': 'NLE',
  'Oaxaca': 'OAX',
  'Puebla': 'PUE',
  'Querétaro': 'QUE',
  'Quintana Roo': 'ROO',
  'San Luis Potosí': 'SLP',
  'Sinaloa': 'SIN',
  'Sonora': 'SON',
  'Tabasco': 'TAB',
  'Tamaulipas': 'TAM',
  'Tlaxcala': 'TLA',
  'Veracruz': 'VER',
  'Yucatán': 'YUC',
  'Zacatecas': 'ZAC'
}

// Inicializa form con los datos del cliente existente
const initFormData = () => ({
  nombre_razon_social: props.cliente?.nombre_razon_social || '',
  email: props.cliente?.email || '',
  telefono: props.cliente?.telefono || '',
  tipo_persona: props.cliente?.tipo_persona || '',
  rfc: props.cliente?.rfc || '',
  curp: props.cliente?.curp || '',
  regimen_fiscal: props.cliente?.regimen_fiscal || '',
  uso_cfdi: props.cliente?.uso_cfdi || 'G03', // G03 - Gastos en general por defecto
  calle: props.cliente?.calle || '',
  numero_exterior: props.cliente?.numero_exterior || '',
  numero_interior: props.cliente?.numero_interior || '',
  colonia: props.cliente?.colonia || '',
  codigo_postal: props.cliente?.codigo_postal || '',
  municipio: props.cliente?.municipio || '',
  estado: props.cliente?.estado || 'SON', // Sonora por defecto
  pais: props.cliente?.pais || 'MX',
  activo: props.cliente?.activo ?? true,
})

const form = useForm(initFormData())

// Cargar colonias disponibles cuando el componente se monte
onMounted(async () => {
  const codigoPostal = form.codigo_postal
  if (codigoPostal && codigoPostal.length === 5) {
    try {
      const response = await fetch(`/api/cp/${codigoPostal}`)
      if (response.ok) {
        const data = await response.json()
        availableColonias.value = data.colonias || []

        // Si la colonia del cliente existe en las colonias disponibles, mantenerla seleccionada
        if (form.colonia && data.colonias && data.colonias.includes(form.colonia)) {
          // La colonia ya está correctamente seleccionada
        } else if (data.colonias && data.colonias.length === 1) {
          // Si solo hay una colonia disponible, seleccionarla automáticamente
          form.colonia = data.colonias[0]
        } else if (form.colonia && data.colonias) {
          // Intentar encontrar una coincidencia aproximada (ignorando mayúsculas/minúsculas y espacios)
          const coloniaCliente = form.colonia.toLowerCase().trim()
          const coincidencia = data.colonias.find(colonia =>
            colonia.toLowerCase().trim() === coloniaCliente ||
            colonia.toLowerCase().trim().includes(coloniaCliente) ||
            coloniaCliente.includes(colonia.toLowerCase().trim())
          )

          if (coincidencia) {
            // Si encontramos una coincidencia aproximada, usar esa
            form.colonia = coincidencia
          } else {
            // Si no hay coincidencia, mantener la colonia actual pero agregarla a las opciones disponibles
            console.warn('Colonia del cliente no encontrada en el código postal, manteniendo valor actual:', form.colonia)
            // No limpiamos el campo, permitimos que el usuario mantenga la colonia actual
            // Pero necesitamos asegurarnos de que aparezca en el select
            availableColonias.value = [form.colonia, ...data.colonias]
          }
        }
      }
    } catch (error) {
      console.warn('Error al cargar colonias en edición:', error)
    }
  }
})

const hasGlobalErrors = computed(() => Object.keys(form.errors).length > 0)

const requiredFields = computed(() => {
  const baseFields = [
    'nombre_razon_social', 'calle', 'numero_exterior',
    'colonia', 'codigo_postal', 'municipio'
  ]

  // Si requiere factura, agregar campos fiscales
  if (form.requiere_factura) {
    return [
      ...baseFields,
      'tipo_persona', 'rfc', 'regimen_fiscal', 'uso_cfdi'
    ]
  }

  return baseFields
})

const requiredFieldsFilled = computed(() => {
  // Asegurar que requiredFields sea un array válido
  const fields = Array.isArray(requiredFields.value) ? requiredFields.value : []

  return fields.every(field => {
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

// Detectar si es cliente extranjero
const isExtranjero = computed(() => form.rfc?.toUpperCase() === 'XEXX010101000')

// Normalizar datos antes de enviar al backend
const normalizeForBackend = (payload) => {
  const out = { ...payload }

  // Estado: si por alguna razón viene nombre, pásalo a clave
  if (out.estado && out.estado.length !== 3) {
    const clave = estadoMapping[out.estado]
    if (clave) out.estado = clave
  }

  // País: tu backend pide "MX" (no "MEX"); sólo fuerza MX si NO es extranjero
  if (!isExtranjero.value) {
    out.pais = 'MX'
  }
  return out
}

// Opciones para selects con validaciones y fallbacks
const estados = computed(() => {
  // Si props.catalogs.estados viene como [{ clave: 'SON', nombre: 'Sonora' }, ...] ÚSALO DIRECTO:
  if (Array.isArray(props.catalogs?.estados) && props.catalogs.estados[0]?.clave) {
    return props.catalogs.estados.map(e => ({
      value: e.clave,               // <-- CLAVE SAT (3)
      text: `${e.clave} — ${e.nombre}`
    }))
  }

  // Si sólo tienes nombres, deriva la clave con estadoMapping:
  return Object.entries(estadoMapping).map(([nombre, clave]) => ({
    value: clave,                   // <-- CLAVE SAT (3)
    text: `${clave} — ${nombre}`
  }))
})

const regimenesFiltrados = computed(() => {
  if (!form.tipo_persona) return []

  const regimenesData = props.catalogs?.regimenesFiscales || props.catalogs?.regimenes || []
  console.log('Regímenes raw data:', regimenesData, 'Tipo persona:', form.tipo_persona)

  return regimenesData
    .filter(r => {
      if (!r.persona_moral && !r.persona_fisica) return true
      return form.tipo_persona === 'moral' ? r.persona_moral : r.persona_fisica
    })
    .map(r => {
      const clave = (r.clave || r.codigo || r.id || r.value || '').toString().trim()
      let descripcion = (r.descripcion || r.nombre || r.text || r.label || clave).toString().trim()

      // Evitar duplicar el formato
      if (!descripcion.startsWith(`${clave} —`)) {
        descripcion = `${clave} — ${descripcion}`
      }

      return {
        value: clave,
        text: descripcion
      }
    })
    // ✅ ELIMINAR DUPLICADOS POR VALUE (normalizado)
    .filter((item, index, self) =>
      index === self.findIndex(t => t.value.toString().trim().toUpperCase() === item.value.toString().trim().toUpperCase())
    )
})

const usosCFDI = computed(() => {
  const usosData = props.catalogs?.usosCFDI || props.catalogs?.usos_cfdi || props.catalogs?.usos || []
  console.log('Usos CFDI raw data:', usosData)

  return usosData.map(u => {
    const clave = u.clave || u.codigo || u.id || u.value
    const descripcion = u.descripcion || u.nombre || u.text || u.label || clave

    return {
      value: clave,
      text: `${clave} — ${descripcion}`
    }
  })
})

// Configuración dinámica del RFC
const rfcMaxLength = computed(() => form.tipo_persona === 'fisica' ? 13 : 12)
const rfcPlaceholder = computed(() =>
  form.tipo_persona === 'fisica' ? 'ABCD123456EFG' : 'ABC123456EF'
)

// Watchers
watch(() => form.tipo_persona, (newVal, oldVal) => {
  if (newVal !== oldVal && oldVal) { // Solo resetear si había un valor previo
    form.rfc = ''
    form.regimen_fiscal = ''
    form.clearErrors(['rfc', 'regimen_fiscal'])
  }
})

// Forzar país a "MX" cuando no es extranjero
watch(isExtranjero, (val) => {
  if (!val) form.pais = 'MX'
})

// Funciones de manejo de input
const onFacturaChange = () => {
  if (!form.requiere_factura) {
    // Si desmarca factura, limpiar campos fiscales
    form.tipo_persona = ''
    form.rfc = ''
    form.curp = ''
    form.regimen_fiscal = ''
    form.uso_cfdi = 'G03'
    form.clearErrors(['tipo_persona', 'rfc', 'curp', 'regimen_fiscal', 'uso_cfdi'])
  }
}

const onTipoPersonaChange = () => {
  // En edición, solo limpiar si realmente cambió el tipo
  const originalTipo = props.cliente?.tipo_persona
  if (form.tipo_persona !== originalTipo) {
    form.rfc = ''
    form.regimen_fiscal = ''
    form.clearErrors(['rfc', 'regimen_fiscal'])
  }
}

const onRfcInput = (event) => {
  const value = event.target ? event.target.value : event
  const cleaned = String(value).toUpperCase().replace(/[^A-ZÑ&0-9]/g, '')
  const maxLen = rfcMaxLength.value
  form.rfc = cleaned.slice(0, maxLen)

  if (form.rfc && form.errors.rfc) {
    form.clearErrors('rfc')
  }
}

const onCpInput = async (event) => {
  const value = event.target ? event.target.value : event
  const digits = String(value).replace(/\D/g, '')
  form.codigo_postal = digits.slice(0, 5)

  if (form.codigo_postal && form.errors.codigo_postal) {
    form.clearErrors('codigo_postal')
  }

  // Autocompletar cuando tenga 5 dígitos
  if (form.codigo_postal.length === 5) {
    try {
      const response = await fetch(`/api/cp/${form.codigo_postal}`)
      if (response.ok) {
        const data = await response.json()

        // Estado: usar clave SAT directamente si viene del API
        if (data.estado) {
          form.estado = data.estado // Ya viene como clave SAT (AGU, SON, etc.)
        }

        form.municipio = data.municipio
        // Solo autocompletar país si no está establecido (para permitir clientes extranjeros)
        if (!form.pais || form.pais.trim() === '') {
          form.pais = data.pais
        }

        // Poblar lista de colonias disponibles
        const coloniasDisponibles = data.colonias || []
        availableColonias.value = coloniasDisponibles

        // Si solo hay una colonia, la seleccionamos automáticamente
        if (coloniasDisponibles.length === 1) {
          form.colonia = coloniasDisponibles[0]
        } else if (coloniasDisponibles.length > 1) {
          // Si hay múltiples colonias, intentar mantener la colonia actual si existe en la lista
          if (form.colonia && coloniasDisponibles.includes(form.colonia)) {
            // La colonia actual está disponible, mantenerla seleccionada
          } else if (form.colonia) {
            // Intentar encontrar una coincidencia aproximada
            const coloniaCliente = form.colonia.toLowerCase().trim()
            const coincidencia = coloniasDisponibles.find(colonia =>
              colonia.toLowerCase().trim() === coloniaCliente ||
              colonia.toLowerCase().trim().includes(coloniaCliente) ||
              coloniaCliente.includes(colonia.toLowerCase().trim())
            )

            if (coincidencia) {
              form.colonia = coincidencia
            } else {
              // Si no hay coincidencia, limpiar selección actual
              form.colonia = ''
            }
          } else {
            // No había colonia seleccionada, limpiar el campo
            form.colonia = ''
          }
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

    if (form[campo] && form.errors[campo]) {
      form.clearErrors(campo)
    }
  }
}

const normalizeEmail = () => {
  if (form.email && typeof form.email === 'string') {
    form.email = form.email.toLowerCase().trim()
    if (form.email && form.errors.email) form.clearErrors('email')
  }
}

const validateTelefono = (event) => {
  const value = event.target ? event.target.value : event
  // Solo permitir números y limitar a 10 dígitos
  const cleaned = String(value).replace(/\D/g, '').slice(0, 10)
  form.telefono = cleaned
  if (form.telefono && form.errors.telefono) form.clearErrors('telefono')
}

// Funciones principales
const resetForm = async () => {
  const originalData = initFormData()
  Object.keys(originalData).forEach(key => {
    form[key] = originalData[key]
  })
  form.clearErrors()
  showSuccessMessage.value = false

  // Recargar colonias disponibles después de restaurar
  const codigoPostal = form.codigo_postal
  if (codigoPostal && codigoPostal.length === 5) {
    try {
      const response = await fetch(`/api/cp/${codigoPostal}`)
      if (response.ok) {
        const data = await response.json()
        const coloniasDisponibles = data.colonias || []

        // Aplicar la misma lógica mejorada para manejar colonias no encontradas
        if (form.colonia && !coloniasDisponibles.includes(form.colonia)) {
          const coloniaCliente = form.colonia.toLowerCase().trim()
          const coincidencia = coloniasDisponibles.find(colonia =>
            colonia.toLowerCase().trim() === coloniaCliente ||
            colonia.toLowerCase().trim().includes(coloniaCliente) ||
            coloniaCliente.includes(colonia.toLowerCase().trim())
          )

          if (coincidencia) {
            form.colonia = coincidencia
            availableColonias.value = coloniasDisponibles
          } else {
            // Mantener la colonia actual y agregarla a las opciones disponibles
            availableColonias.value = [form.colonia, ...coloniasDisponibles]
          }
        } else {
          availableColonias.value = coloniasDisponibles
        }
      } else {
        availableColonias.value = []
      }
    } catch (error) {
      console.warn('Error al recargar colonias en resetForm:', error)
      availableColonias.value = []
    }
  } else {
    availableColonias.value = []
  }
}

const onCurpInput = (event) => {
  const value = event.target.value.toUpperCase().replace(/[^A-Z0-9]/g, '')
  form.curp = value.slice(0, 18)
  if (form.curp && form.errors.curp) {
    form.clearErrors('curp')
  }
}

// AJAX Validation
const validateRfc = async (rfc, clienteId = null) => {
  if (!rfc.trim()) return

  try {
    const params = { rfc: rfc.trim() }
    if (clienteId) params.cliente_id = clienteId

    const response = await axios.get(route('clientes.validarRfc'), { params })
    if (response.data.success) {
      form.clearErrors('rfc')
      if (response.data.exists) {
        form.setError('rfc', response.data.message)
      }
    } else {
      form.setError('rfc', response.data.message)
    }
  } catch (error) {
    console.error('Error validating RFC:', error)
    form.setError('rfc', 'Error al validar RFC')
  }
}

const validateEmail = async (email) => {
  if (!email.trim()) return

  try {
    const response = await axios.get(route('clientes.validarEmail'), { params: { email: email.trim() } })
    if (response.data.success) {
      form.clearErrors('email')
    } else {
      form.setError('email', response.data.message)
    }
  } catch (error) {
    console.error('Error validating email:', error)
    form.setError('email', 'Error al validar email')
  }
}

const submit = () => {
  showSuccessMessage.value = false

  if (!isFormValid.value) {
    console.warn('Formulario no válido, evitando envío')
    return
  }

  // Preparar datos para enviar
  let dataToSend = { ...form.data() }

  // Si no requiere factura, eliminar campos fiscales del envío
  if (!dataToSend.requiere_factura) {
    delete dataToSend.tipo_persona
    delete dataToSend.rfc
    delete dataToSend.curp
    delete dataToSend.regimen_fiscal
    delete dataToSend.uso_cfdi
    // Hacer email y teléfono opcionales cuando no requiere factura
    if (!dataToSend.email || dataToSend.email.trim() === '') delete dataToSend.email
    if (!dataToSend.telefono || dataToSend.telefono.trim() === '') delete dataToSend.telefono
  } else {
    // Si requiere factura, limpiar campos fiscales vacíos
    if (!dataToSend.tipo_persona || dataToSend.tipo_persona.trim() === '') delete dataToSend.tipo_persona
    if (!dataToSend.rfc || dataToSend.rfc.trim() === '') delete dataToSend.rfc
    if (!dataToSend.curp || dataToSend.curp.trim() === '') delete dataToSend.curp
    if (!dataToSend.regimen_fiscal || dataToSend.regimen_fiscal.trim() === '') delete dataToSend.regimen_fiscal
    if (!dataToSend.uso_cfdi || dataToSend.uso_cfdi.trim() === '') delete dataToSend.uso_cfdi
  }

  // Limpiar campos básicos vacíos
  if (!dataToSend.email || dataToSend.email.trim() === '') delete dataToSend.email
  if (!dataToSend.telefono || dataToSend.telefono.trim() === '') delete dataToSend.telefono
  if (!dataToSend.numero_interior || dataToSend.numero_interior.trim() === '') delete dataToSend.numero_interior

  console.log('Datos a enviar:', dataToSend)

  // Normalizar email y datos para el backend
  normalizeEmail()
  dataToSend = normalizeForBackend(dataToSend)

  // Usar PUT/PATCH para actualización
  form.put(route('clientes.update', props.cliente.id), {
    data: dataToSend,
    preserveScroll: true,
    onSuccess: () => {
      showSuccessMessage.value = true
      setTimeout(() => {
        showSuccessMessage.value = false
      }, 3000)
    },
    onError: (errors) => {
      console.error('Errores de validación:', errors)
    }
  })
}
</script>
