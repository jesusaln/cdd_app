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

      <!-- Mensaje de autocompletado -->
      <div
        v-if="showAutoCompleteMessage"
        class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-md"
        aria-live="polite"
      >
        <div class="flex">
          <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
            </svg>
          </div>
          <div class="ml-3">
            <p class="text-sm font-medium text-blue-800">Dirección autocompletada</p>
            <p class="text-sm text-blue-700">Los campos de estado y municipio se han completado automáticamente.</p>
          </div>
        </div>
      </div>

      <form @submit.prevent="submit" class="space-y-8" autocomplete="off">
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
                 autocomplete="new-password"
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
                <span v-if="form.requiere_factura" class="text-gray-400">(requerido para facturación)</span>
              </label>
              <input
               type="email"
               id="email"
               v-model="form.email"
               @blur="normalizeEmail"
               autocomplete="off"
               readonly
               onfocus="this.removeAttribute('readonly');"
               placeholder="correo@ejemplo.com"

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
               autocomplete="new-password"
               v-model="form.telefono"
               @input="validateTelefono"
               maxlength="10"
               :placeholder="form.requiere_factura ? '10 dígitos (requerido)' : '10 dígitos (opcional)'"
               :pattern="form.requiere_factura ? '[0-9]{10}' : undefined"

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

          <!-- Checkbox para mostrar dirección -->
          <div class="mb-6">
            <div class="flex items-center">
              <input
                type="checkbox"
                id="mostrar_direccion"
                v-model="form.mostrar_direccion"
                :class="[
                  'h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded'
                ]"
              />
              <label for="mostrar_direccion" class="ml-2 block text-sm font-medium text-gray-700">
                Agregar información de dirección
              </label>
            </div>
            <div class="mt-1 text-sm text-gray-500">
              Marque esta opción si desea agregar la dirección del cliente (calle, colonia, código postal, etc.)
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
                RFC <span v-if="form.requiere_factura" class="text-red-500">*</span>
                <span v-if="!form.requiere_factura" class="text-gray-400">(opcional)</span>
              </label>
              <input
                type="text"
                id="rfc"
                :maxlength="rfcMaxLength"
                :placeholder="!form.requiere_factura ? 'Solo requerido si necesita facturación' : rfcPlaceholder"
                :value="form.rfc"
                @input="onRfcInput"
                :disabled="!form.tipo_persona || !form.requiere_factura"
                autocomplete="new-password"
                :class="[
                  'mt-1 block w-full rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm',
                  form.errors.rfc ? 'border-red-300 bg-red-50' : 'border-gray-300',
                  (!form.tipo_persona || !form.requiere_factura) ? 'bg-gray-100 text-gray-400' : ''
                ]"
                :required="form.requiere_factura && form.tipo_persona"
              />
              <div v-if="form.errors.rfc" class="mt-2 text-sm text-red-600">
                {{ form.errors.rfc }}
              </div>
              <div v-if="!form.requiere_factura" class="mt-1 text-xs text-gray-500">
                Régimen fiscal solo requerido cuando el cliente necesita facturación
              </div>
              <div v-else-if="!form.tipo_persona" class="mt-1 text-xs text-gray-500">
                Primero selecciona el tipo de persona
              </div>
            </div>

            <!-- NUEVO: CURP -->
            <div class="mb-4">
              <label for="curp" class="block text-sm font-medium text-gray-700">
                CURP
                <span v-if="form.tipo_persona === 'fisica' && form.requiere_factura" class="text-gray-400">(opcional)</span>
                <span v-if="!form.requiere_factura" class="text-gray-400">(opcional)</span>
              </label>
              <input
                type="text"
                id="curp"
                :maxlength="18"
                :placeholder="!form.requiere_factura ? 'Solo requerido si necesita facturación' : (form.tipo_persona === 'fisica' ? 'ABCD880321HXXLRN09' : 'No aplica para Persona Moral')"
                :value="form.curp"
                @input="onCurpInput"
                :disabled="form.tipo_persona === 'moral' || !form.tipo_persona || !form.requiere_factura"
                autocomplete="new-password"
                :class="[
                  'mt-1 block w-full rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm',
                  form.errors.curp ? 'border-red-300 bg-red-50' : 'border-gray-300',
                  (form.tipo_persona === 'moral' || !form.tipo_persona || !form.requiere_factura) ? 'bg-gray-100 text-gray-400' : ''
                ]"
              />
              <div v-if="form.errors.curp" class="mt-2 text-sm text-red-600">
                {{ form.errors.curp }}
              </div>
              <div v-if="form.requiere_factura" class="mt-1 text-xs text-gray-500">
                {{ curpHelperText }}
              </div>
            </div>
            <!-- /NUEVO: CURP -->

            <div class="mb-4">
              <label for="regimen_fiscal" class="block text-sm font-medium text-gray-700">
                Régimen Fiscal <span v-if="form.requiere_factura" class="text-red-500">*</span>
                <span v-if="!form.requiere_factura" class="text-gray-400">(opcional)</span>
              </label>
              <select
                id="regimen_fiscal"
                v-model="form.regimen_fiscal"
                :disabled="!form.tipo_persona || !form.requiere_factura"
                :class="[
                  'mt-1 block w-full rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm',
                  form.errors.regimen_fiscal ? 'border-red-300 bg-red-50' : 'border-gray-300',
                  (!form.tipo_persona || !form.requiere_factura) ? 'bg-gray-100 text-gray-400' : ''
                ]"
                :required="form.requiere_factura && form.tipo_persona"
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
              <div v-if="!form.requiere_factura" class="mt-1 text-xs text-gray-500">
                RFC solo requerido cuando el cliente necesita facturación
              </div>
              <div v-else-if="!form.tipo_persona" class="mt-1 text-xs text-gray-500">
                Primero selecciona el tipo de persona
              </div>
            </div>

            <div class="mb-4">
              <label for="uso_cfdi" class="block text-sm font-medium text-gray-700">
                Uso CFDI <span v-if="form.requiere_factura" class="text-red-500">*</span>
                <span v-if="!form.requiere_factura" class="text-gray-400">(opcional)</span>
              </label>
              <select
                id="uso_cfdi"
                v-model="form.uso_cfdi"
                :class="[
                  'mt-1 block w-full rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm',
                  form.errors.uso_cfdi ? 'border-red-300 bg-red-50' : 'border-gray-300',
                  !form.requiere_factura ? 'bg-gray-100 text-gray-400' : ''
                ]"
                :required="form.requiere_factura"
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
        <div v-if="form.mostrar_direccion">
          <h2 class="text-lg font-medium text-gray-900 mb-4">Dirección</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="md:col-span-2">
              <div class="mb-4">
                <label for="calle" class="block text-sm font-medium text-gray-700">
                  Calle
                  <span class="text-gray-400">(opcional)</span>
                </label>
                <input
                  type="text"
                  id="calle"
                  v-model="form.calle"
                  @blur="toUpper('calle')"
                  autocomplete="new-password"
                  :class="[
                    'mt-1 block w-full rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm',
                    form.errors.calle ? 'border-red-300 bg-red-50' : 'border-gray-300'
                  ]"
                />
                <div v-if="form.errors.calle" class="mt-2 text-sm text-red-600">
                  {{ form.errors.calle }}
                </div>
              </div>
            </div>

            <div class="mb-4">
              <label for="numero_exterior" class="block text-sm font-medium text-gray-700">
                Número Exterior
                <span class="text-gray-400">(opcional)</span>
              </label>
              <input
                type="text"
                id="numero_exterior"
                v-model="form.numero_exterior"
                @blur="toUpper('numero_exterior')"
                autocomplete="new-password"
                :class="[
                  'mt-1 block w-full rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm',
                  form.errors.numero_exterior ? 'border-red-300 bg-red-50' : 'border-gray-300'
                ]"
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
                autocomplete="new-password"
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
              <label for="codigo_postal" class="block text-sm font-medium text-gray-700">
                Código Postal
                <span class="text-gray-400">(opcional)</span>
              </label>
              <input
                type="text"
                id="codigo_postal"
                maxlength="5"
                pattern="[0-9]{5}"
                :value="form.codigo_postal"
                @input="onCpInput"
                :disabled="!form.mostrar_direccion"
                placeholder="12345"
                autocomplete="new-password"
                :class="[
                  'mt-1 block w-full rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm',
                  form.errors.codigo_postal ? 'border-red-300 bg-red-50' : 'border-gray-300',
                  !form.mostrar_direccion ? 'bg-gray-100 text-gray-400' : ''
                ]"
              />
              <div v-if="isLoadingCp" class="mt-1 text-xs text-blue-600 flex items-center">
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Buscando código postal...
              </div>
              <div v-if="form.errors.codigo_postal" class="mt-2 text-sm text-red-600">
                {{ form.errors.codigo_postal }}
              </div>
              <div class="mt-1 text-xs text-gray-500">
                Al ingresar un código postal válido, se autocompletarán automáticamente el estado y municipio.
              </div>
            </div>

            <div class="mb-4">
              <label for="colonia" class="block text-sm font-medium text-gray-700">
                Colonia
                <span class="text-gray-400">(opcional)</span>
              </label>
              <select
                id="colonia"
                v-model="form.colonia"
                @change="form.clearErrors('colonia')"
                :disabled="availableColonias.length === 0 || !form.mostrar_direccion"
                :class="[
                  'mt-1 block w-full rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm',
                  form.errors.colonia ? 'border-red-300 bg-red-50' : 'border-gray-300',
                  (availableColonias.length === 0 || !form.mostrar_direccion) ? 'bg-gray-100 text-gray-400' : ''
                ]"
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
              <label for="municipio" class="block text-sm font-medium text-gray-700">
                Municipio
                <span class="text-gray-400">(opcional)</span>
              </label>
              <input
                type="text"
                id="municipio"
                v-model="form.municipio"
                :disabled="!form.mostrar_direccion"
                autocomplete="new-password"
                :class="[
                  'mt-1 block w-full rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm',
                  form.errors.municipio ? 'border-red-300 bg-red-50' : 'border-gray-300',
                  !form.mostrar_direccion ? 'bg-gray-100 text-gray-400' : ''
                ]"
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
                <select
                  id="pais"
                  v-model="form.pais"
                  class="mt-1 block w-full rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm border-gray-300"
                >
                  <option value="MX">México</option>
                  <option value="USA">Estados Unidos</option>
                  <option value="CAN">Canadá</option>
                  <option value="">Otro</option>
                </select>
                <div v-if="form.errors.pais" class="mt-2 text-sm text-red-600">
                  {{ form.errors.pais }}
                </div>
                <div class="mt-1 text-xs text-gray-500">
                  México por defecto.
                </div>
              </div>
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
const showAutoCompleteMessage = ref(false)
const isLoadingCp = ref(false) // Nuevo estado de carga

// Mapeo de nombres de estados a códigos SAT
const estadoMapping = {
  'Aguascalientes': 'AGU',
  'Baja California': 'BCN',
  'Baja California Sur': 'BCS',
  'Campeche': 'CAM',
  'Chiapas': 'CHP',
  'Chihuahua': 'CHH',
  'Ciudad de México': 'CMX',
  'Coahuila': 'COA',
  'Coahuila de Zaragoza': 'COA',
  'Colima': 'COL',
  'Durango': 'DUR',
  'Estado de México': 'MEX',
  'México': 'MEX',
  'Guanajuato': 'GUA',
  'Guerrero': 'GRO',
  'Hidalgo': 'HID',
  'Jalisco': 'JAL',
  'Michoacán': 'MIC',
  'Michoacán de Ocampo': 'MIC',
  'Morelos': 'MOR',
  'Nayarit': 'NAY',
  'Nuevo León': 'NLE',
  'Oaxaca': 'OAX',
  'Puebla': 'PUE',
  'Querétaro': 'QUE',
  'Querétaro de Arteaga': 'QUE',
  'Quintana Roo': 'ROO',
  'San Luis Potosí': 'SLP',
  'Sinaloa': 'SIN',
  'Sonora': 'SON',
  'Tabasco': 'TAB',
  'Tamaulipas': 'TAM',
  'Tlaxcala': 'TLA',
  'Veracruz': 'VER',
  'Veracruz de Ignacio de la Llave': 'VER',
  'Yucatán': 'YUC',
  'Zacatecas': 'ZAC'
}

// Estados se obtienen de la API, no necesitamos mapeo local

// Inicializa form con valores por defecto seguros
const initFormData = () => ({
  nombre_razon_social: props.cliente?.nombre_razon_social || '',
  email: props.cliente?.email || '',
  telefono: props.cliente?.telefono || '',
  requiere_factura: props.cliente?.requiere_factura || false,
  mostrar_direccion: props.cliente?.calle || props.cliente?.numero_exterior || false, // Si tiene datos de dirección, mostrarla
  tipo_persona: '',
  rfc: props.cliente?.rfc || '',
  curp: props.cliente?.curp || '',                 // <<< NUEVO
  regimen_fiscal: props.cliente?.regimen_fiscal || '',
  uso_cfdi: props.cliente?.uso_cfdi || 'G03', // G03 - Gastos en general por defecto
  calle: props.cliente?.calle || '',
  numero_exterior: props.cliente?.numero_exterior || '',
  numero_interior: props.cliente?.numero_interior || '',
  colonia: props.cliente?.colonia || '',
  codigo_postal: props.cliente?.codigo_postal || '',
  municipio: props.cliente?.municipio || '',
  estado: props.cliente?.estado || '', // Sin valor por defecto
  pais: props.cliente?.pais || '',       // Sin valor por defecto
})

const form = useForm(initFormData())

const hasGlobalErrors = computed(() => Object.keys(form.errors).length > 0)

const requiredFields = computed(() => {
  const baseFields = [
    'nombre_razon_social', 'email'
  ]

  // Agregar teléfono si requiere factura
  if (form.requiere_factura) {
    baseFields.push('telefono')
  }

  // Los campos de dirección ahora son opcionales incluso cuando se muestra la dirección
  // No agregamos campos de dirección a requiredFields

  // Si requiere factura, agregar campos fiscales
  if (form.requiere_factura) {
    baseFields.push('tipo_persona', 'rfc', 'regimen_fiscal', 'uso_cfdi')
  }

  return baseFields
})

const requiredFieldsFilled = computed(() => {
  return requiredFields.value.every(field => {
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

  // Estado: mantener como está (solo informativo, sin conversión a claves SAT)
  // País: tu backend pide "MX" (no "MEX"); sólo fuerza MX si NO es extranjero
  if (!isExtranjero.value) {
    out.pais = 'MX'
  }
  return out
}

// Opciones para selects
const estados = computed(() => {
  // Si props.catalogs.estados ya viene formateado correctamente, úsalo
  if (Array.isArray(props.catalogs?.estados) && props.catalogs.estados[0]?.value) {
    return props.catalogs.estados
  }

  // Si props.catalogs.estados viene como [{ clave: 'SON', nombre: 'Sonora' }, ...] ÚSALO DIRECTO:
  if (Array.isArray(props.catalogs?.estados) && props.catalogs.estados[0]?.clave) {
    return props.catalogs.estados.map(e => ({
      value: e.clave,               // <-- CLAVE SAT (3)
      text: e.nombre                // <-- Solo nombre (ej. "Sonora")
    }))
  }

  // Si sólo tienes nombres, deriva la clave con estadoMapping:
  return Object.entries(estadoMapping).map(([nombre, clave]) => ({
    value: clave,                   // <-- CLAVE SAT (3)
    text: nombre                    // <-- Solo nombre (ej. "Sonora")
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
    value: u.value || u.clave,
    text: u.text || `${u.clave} — ${u.descripcion}`
  }))
})

// Configuración dinámica RFC/CURP
const rfcMaxLength = computed(() => form.tipo_persona === 'fisica' ? 13 : 12)
const rfcPlaceholder = computed(() => form.tipo_persona === 'fisica' ? 'ABCD123456EFG' : 'ABC123456EF')
const curpHelperText = computed(() =>
  !form.requiere_factura
    ? 'CURP solo requerida cuando el cliente necesita facturación.'
    : form.tipo_persona === 'moral'
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

// Limpiar campos de dirección si se desmarca el checkbox
watch(() => form.mostrar_direccion, (nuevoValor) => {
  if (!nuevoValor) {
    // Limpiar campos de dirección
    form.calle = ''
    form.numero_exterior = ''
    form.numero_interior = ''
    form.colonia = ''
    form.codigo_postal = ''
    form.municipio = ''
    form.estado = ''
    form.pais = ''

    // Limpiar errores de dirección
    form.clearErrors(['calle', 'numero_exterior', 'colonia', 'codigo_postal', 'municipio'])
  }
})

// Handlers
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
    isLoadingCp.value = true
    try {
      const response = await axios.get(`/api/cp/${form.codigo_postal}`)
      const data = response.data

      // AUTOCOMPLETAR ESTADO Y MUNICIPIO
      // Estado: convertir nombre a código SAT (Case Insensitive)
      if (data.estado) {
        const estadoNombre = data.estado.trim().toUpperCase()
        const estadoEntry = Object.entries(estadoMapping).find(([nombre, clave]) => 
          nombre.toUpperCase() === estadoNombre
        )
        const estadoCodigo = estadoEntry ? estadoEntry[1] : (estadoMapping[data.estado] || data.estado)
        form.estado = estadoCodigo
        console.log('Estado autocompletado:', data.estado, '->', estadoCodigo)
      }

      // Municipio: autocompletar siempre
      if (data.municipio) {
        form.municipio = data.municipio
        console.log('Municipio autocompletado:', data.municipio)
      }

      // Solo autocompletar país si no está establecido (para permitir clientes extranjeros)
      if (!form.pais || form.pais.trim() === '') {
        form.pais = data.pais
      }

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

      // Mostrar mensaje de éxito de autocompletado
      if (data.estado || data.municipio) {
        showAutoCompleteMessage.value = true
        console.log('✅ Dirección autocompletada desde código postal')

        // Ocultar mensaje después de 3 segundos
        setTimeout(() => {
          showAutoCompleteMessage.value = false
        }, 3000)
      }
    } catch (error) {
      console.warn('Error al consultar código postal:', error)
      // Mostrar error al usuario
      if (error.response?.status === 404) {
        console.warn('Código postal no encontrado en la base de datos')
      } else if (error.response?.status === 500) {
        console.error('Error interno del servidor al consultar código postal')
      } else {
        console.error('Error de red al consultar código postal:', error.message)
      }
      // Limpiar colonias disponibles en caso de error
      availableColonias.value = []
      form.colonia = ''
    } finally {
      isLoadingCp.value = false
    }
  } else {
    // Si el CP no tiene 5 dígitos, limpiar colonias
    availableColonias.value = []
    form.colonia = ''
    isLoadingCp.value = false
  }
}

const toUpper = (campo) => {
  if (form[campo] && typeof form[campo] === 'string') {
    form[campo] = form[campo].toUpperCase().trim()
    if (form[campo] && form.errors[campo]) form.clearErrors(campo)
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

// Acciones
const resetForm = () => {
  const newData = initFormData()
  Object.keys(newData).forEach(key => { form[key] = newData[key] })
  form.clearErrors()
  showSuccessMessage.value = false
  showAutoCompleteMessage.value = false
  availableColonias.value = []
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

  // Si no muestra dirección, eliminar campos de dirección del envío
  if (!dataToSend.mostrar_direccion) {
    delete dataToSend.calle
    delete dataToSend.numero_exterior
    delete dataToSend.numero_interior
    delete dataToSend.colonia
    delete dataToSend.codigo_postal
    delete dataToSend.municipio
    delete dataToSend.estado
    delete dataToSend.pais
  } else {
    // Si muestra dirección, eliminar campos de dirección vacíos ya que ahora son opcionales
    if (!dataToSend.calle || dataToSend.calle.trim() === '') delete dataToSend.calle
    if (!dataToSend.numero_exterior || dataToSend.numero_exterior.trim() === '') delete dataToSend.numero_exterior
    if (!dataToSend.numero_interior || dataToSend.numero_interior.trim() === '') delete dataToSend.numero_interior
    if (!dataToSend.colonia || dataToSend.colonia.trim() === '') delete dataToSend.colonia
    if (!dataToSend.codigo_postal || dataToSend.codigo_postal.trim() === '') delete dataToSend.codigo_postal
    if (!dataToSend.municipio || dataToSend.municipio.trim() === '') delete dataToSend.municipio
    if (!dataToSend.estado || dataToSend.estado.trim() === '') delete dataToSend.estado
    if (!dataToSend.pais || dataToSend.pais.trim() === '') delete dataToSend.pais
  }

  console.log('Datos a enviar:', dataToSend)

  // Normalizar datos para el backend
  dataToSend = normalizeForBackend(dataToSend)

  form.post(route('clientes.store'), {
    data: dataToSend,
    preserveScroll: true,
    onSuccess: () => {
      showSuccessMessage.value = true
      showAutoCompleteMessage.value = false // Ocultar mensaje de autocompletado
      setTimeout(() => {
        showSuccessMessage.value = false
        resetForm()
      }, 3000)
    },
    onError: (errors) => {
      console.error('Errores de validación:', errors)
      // Scroll to top to show error messages
      window.scrollTo({ top: 0, behavior: 'smooth' })
    }
  })
}
</script>
