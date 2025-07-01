<template>
  <Head :title="cliente.id ? 'Editar Cliente' : 'Crear Cliente'" />
  <div class="max-w-4xl mx-auto p-4">
    <div class="bg-white shadow-sm rounded-lg p-6">
      <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold text-gray-800">
          {{ cliente.id ? 'Editar Cliente' : 'Crear Nuevo Cliente' }}
        </h1>
        <div class="text-sm text-gray-500">
          Campos obligatorios marcados con <span class="text-red-500">*</span>
        </div>
      </div>

      <!-- Alertas globales de error (mostradas por errores de validación de Inertia o custom) -->
      <div v-if="hasGlobalErrors" class="mb-6 p-4 bg-red-50 border border-red-200 rounded-md">
        <div class="flex">
          <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
            </svg>
          </div>
          <div class="ml-3">
            <h3 class="text-sm font-medium text-red-800">Error en el formulario</h3>
            <div class="mt-2 text-sm text-red-700">
              <ul class="list-disc list-inside space-y-1">
                <!-- Itera sobre los errores de Inertia para mostrarlos al usuario -->
                <li v-for="(error, key) in form.errors" :key="key">{{ error }}</li>
              </ul>
            </div>
          </div>
        </div>
      </div>

      <!-- Alerta de éxito -->
      <div v-if="showSuccessMessage" class="mb-6 p-4 bg-green-50 border border-green-200 rounded-md">
        <div class="flex">
          <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
          </div>
          <div class="ml-3">
            <p class="text-sm font-medium text-green-800">
              Cliente {{ cliente.id ? 'actualizado' : 'creado' }} exitosamente
            </p>
          </div>
        </div>
      </div>

      <!-- Formulario principal -->
      <form @submit.prevent="submit" class="space-y-8">




        <!-- Sección: Información General -->
        <div class="border-b border-gray-200 pb-6">
          <h2 class="text-lg font-medium text-gray-900 mb-4">Información General</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2">
              <FormField
                v-model="form.nombre_razon_social"
                label="Nombre/Razón Social"
                type="text"
                id="nombre_razon_social"
                :error="form.errors.nombre_razon_social"
                :placeholder="form.requiere_factura === 'no' ? 'PÚBLICO EN GENERAL' : 'Ingresa el nombre completo'"
                @blur="convertirAMayusculas('nombre_razon_social')"
                required
              />
            </div>

            <FormField
              v-model="form.email"
              label="Email"
              type="email"
              id="email"
              :error="form.errors.email"
              placeholder="correo@ejemplo.com"
              @blur="validarEmail"
              required
            />

            <FormField
              v-model="form.telefono"
              label="Teléfono"
              type="tel"
              id="telefono"
              :maxlength="10"
              :error="form.errors.telefono"
              placeholder="6621234567"
              @input="validarTelefono"
              pattern="[0-9]{10}"
              required
            />
          </div>
        </div>

        <!-- Sección: Información Fiscal (solo si requiere factura) -->
        <div v-if="form.requiere_factura === 'si'" class="border-b border-gray-200 pb-6">
          <h2 class="text-lg font-medium text-gray-900 mb-4">Información Fiscal</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <FormField
              v-model="form.tipo_persona"
              label="Tipo de Persona"
              type="select"
              id="tipo_persona"
              :options="tipoPersonaOptions"
              :error="form.errors.tipo_persona"
              @change="handleTipoPersonaChange"
              required
            />

            <div class="relative">
              <FormField
                v-model="form.rfc"
                label="RFC"
                type="text"
                id="rfc"
                :maxlength="form.tipo_persona === 'fisica' ? 13 : 12"
                :error="form.errors.rfc"
                :placeholder="form.tipo_persona === 'fisica' ? 'ABCD123456EFG' : 'ABC123456EFG'"
                @input="handleRfcInput"
                @blur="validarRFC"
                required
              />
              <div v-if="rfcValidationStatus" class="mt-1 flex items-center">
                <!-- Icono de validación de RFC -->
                <svg v-if="rfcValidationStatus === 'valid'" class="h-4 w-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <svg v-else class="h-4 w-4 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
                <span :class="rfcValidationStatus === 'valid' ? 'text-green-600' : 'text-red-600'" class="text-xs ml-1">
                  {{ rfcValidationMessage }}
                </span>
              </div>
            </div>

            <FormField
              v-model="form.regimen_fiscal"
              label="Régimen Fiscal"
              type="select"
              id="regimen_fiscal"
              :options="regimenesFiscalesOptions"
              :error="form.errors.regimen_fiscal"
              required
            />

            <FormField
              v-model="form.uso_cfdi"
              label="Uso CFDI"
              type="select"
              id="uso_cfdi"
              :options="usosCFDIOptions"
              :error="form.errors.uso_cfdi"
              required
            />
          </div>
        </div>

        <!-- Sección: Dirección -->
        <div>
          <h2 class="text-lg font-medium text-gray-900 mb-4">Dirección</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="md:col-span-2">
              <FormField
                v-model="form.calle"
                label="Calle"
                type="text"
                id="calle"
                :maxlength="100"
                :error="form.errors.calle"
                :placeholder="form.requiere_factura === 'no' ? 'CALLE GENERICA' : 'Nombre de la calle'"
                :readonly="form.requiere_factura === 'no'"
                :class="form.requiere_factura === 'no' ? 'bg-gray-50' : ''"
                @blur="convertirAMayusculas('calle')"
                required
              />
            </div>

            <FormField
              v-model="form.numero_exterior"
              label="Número Exterior"
              type="text"
              id="numero_exterior"
              :error="form.errors.numero_exterior"
              placeholder="123"
              @blur="convertirAMayusculas('numero_exterior')"
              required
            />

            <FormField
              v-model="form.numero_interior"
              label="Número Interior"
              type="text"
              id="numero_interior"
              :error="form.errors.numero_interior"
              placeholder="A, B, 1, etc. (opcional)"
              @blur="convertirAMayusculas('numero_interior')"
            />

            <FormField
              v-model="form.colonia"
              label="Colonia"
              type="text"
              id="colonia"
              :error="form.errors.colonia"
              :placeholder="form.requiere_factura === 'no' ? 'COLONIA GENERICA' : 'Nombre de la colonia'"
              :readonly="form.requiere_factura === 'no'"
              :class="form.requiere_factura === 'no' ? 'bg-gray-50' : ''"
              @blur="convertirAMayusculas('colonia')"
              required
            />

            <FormField
              v-model="form.codigo_postal"
              label="Código Postal"
              type="text"
              id="codigo_postal"
              :error="form.errors.codigo_postal"
              placeholder="12345"
              maxlength="5"
              required
            />

            <FormField
              v-model="form.municipio"
              label="Municipio"
              type="text"
              id="municipio"
              :error="form.errors.municipio"
              @blur="convertirAMayusculas('municipio')"
              required
            />

            <FormField
              v-model="form.estado"
              label="Estado"
              type="text"
              id="estado"
              :error="form.errors.estado"
              @blur="convertirAMayusculas('estado')"
              required
            />

            <FormField
              v-model="form.pais"
              label="País"
              type="text"
              id="pais"
              :error="form.errors.pais"
              @blur="convertirAMayusculas('pais')"
              required
            />
          </div>
        </div>

        <!-- Botones de acción -->
        <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200">
          <button
            type="button"
            @click="resetForm"
            :disabled="form.processing"
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Limpiar
          </button>
          <button
            type="submit"
            :disabled="form.processing || !isFormValid"
            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span v-if="form.processing" class="flex items-center">
              <svg class="w-4 h-4 mr-2 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Guardando...
            </span>
            <span v-else>{{ cliente.id ? 'Actualizar Cliente' : 'Crear Cliente' }}</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue'; // Asume que este componente existe
import FormField from '@/Components/FormField.vue'; // Asume que este componente existe y es la versión Vue


// Define las opciones para el componente layout
defineOptions({ layout: AppLayout });

// Define las propiedades que recibe el componente, incluyendo el objeto 'cliente'
const props = defineProps({
  cliente: {
    type: Object,
    // Define valores por defecto para un nuevo cliente si no se pasa 'cliente'
    default: () => ({
      id: null,
      requiere_factura: 'si',
      nombre_razon_social: '',
      tipo_persona: 'fisica',
      rfc: '',
      regimen_fiscal: '',
      uso_cfdi: '',
      email: '',
      telefono: '',
      calle: '',
      numero_exterior: '',
      numero_interior: '',
      colonia: '',
      codigo_postal: '',
      municipio: '',
      estado: '',
      pais: ''
    })
  }
});

// --- Estados reactivos ---
// Controla la visibilidad del mensaje de éxito
const showSuccessMessage = ref(false);
// Estado y mensaje de validación del RFC (para feedback visual instantáneo)
const rfcValidationStatus = ref(null); // 'valid', 'invalid', or null
const rfcValidationMessage = ref('');

// --- Opciones de selección para campos select/dropdowns ---
const tipoPersonaOptions = [
  { value: '', text: 'Selecciona el tipo de persona', disabled: true },
  { value: 'fisica', text: 'Persona Física' },
  { value: 'moral', text: 'Persona Moral' }
];

const regimenesFiscalesOptions = [
  { value: '', text: 'Selecciona un régimen fiscal', disabled: true },
  { value: '616', text: '616 - Sin obligaciones fiscales' },
  { value: '601', text: '601 - General de Ley Personas Morales' },
  { value: '605', text: '605 - Sueldos y Salarios e Ingresos Asimilados a Salarios' },
  { value: '606', text: '606 - Arrendamiento' },
  { value: '607', text: '607 - Régimen de Enajenación o Adquisición de Bienes' },
  { value: '608', text: '608 - Demás ingresos' },
  { value: '610', text: '610 - Residentes en el Extranjero sin Establecimiento Permanente en México' },
  { value: '611', text: '611 - Ingresos por Dividendos (socios y accionistas)' },
  { value: '612', text: '612 - Personas Físicas con Actividades Empresariales y Profesionales' },
  { value: '614', text: '614 - Ingresos por intereses' },
  { value: '615', text: '615 - Régimen de los ingresos por obtención de premios' },
  { value: '621', text: '621 - Incorporación Fiscal' },
  { value: '622', text: '622 - Actividades Agrícolas, Ganaderas, Silvícolas y Pesqueras' },
  { value: '623', text: '623 - Opcional para Grupos de Sociedades' },
  { value: '624', text: '624 - Coordinados' },
  { value: '625', text: '625 - Régimen de las Actividades Empresariales con ingresos a través de Plataformas Digitales' },
  { value: '626', text: '626 - Régimen Simplificado de Confianza' }
];

const usosCFDIOptions = [
  { value: '', text: 'Selecciona un uso CFDI', disabled: true },
  { value: 'G01', text: 'G01 - Adquisición de mercancías' },
  { value: 'G02', text: 'G02 - Devoluciones, descuentos o bonificaciones' },
  { value: 'G03', text: 'G03 - Gastos en general' },
  { value: 'I01', text: 'I01 - Construcciones' },
  { value: 'I02', text: 'I02 - Mobilario y equipo de oficina por inversiones' },
  { value: 'I03', text: 'I03 - Equipo de transporte' },
  { value: 'I04', text: 'I04 - Equipo de cómputo y accesorios' },
  { value: 'I05', text: 'I05 - Dados, troqueles, moldes, matrices y herramental' },
  { value: 'I06', text: 'I06 - Comunicaciones telefónicas' },
  { value: 'I07', text: 'I07 - Comunicaciones satelitales' },
  { value: 'I08', text: 'I08 - Otra maquinaria y equipo' },
  { value: 'D01', text: 'D01 - Honorarios médicos, dentales y gastos hospitalarios' },
  { value: 'D02', text: 'D02 - Gastos médicos por incapacidad o discapacidad' },
  { value: 'D03', text: 'D03 - Gastos funerales' },
  { value: 'D04', text: 'D04 - Donativos' },
  { value: 'D05', text: 'D05 - Intereses reales efectivamente pagados por créditos hipotecarios (casa habitación)' },
  { value: 'D06', text: 'D06 - Aportaciones voluntarias al SAR' },
  { value: 'D07', text: 'D07 - Primas por seguros de gastos médicos' },
  { value: 'D08', text: 'D08 - Gastos de transportación escolar obligatoria' },
  { value: 'D09', text: 'D09 - Depósitos en cuentas para el ahorro, primas que tengan como base planes de pensiones' },
  { value: 'D10', text: 'D10 - Pagos por servicios educativos (colegiaturas)' },
  { value: 'S01', text: 'S01 - Sin efectos fiscales' },
  { value: 'CP01', text: 'CP01 - Pagos' },
  { value: 'CN01', text: 'CN01 - Nómina' }
];

// --- Formulario de Inertia.js ---
// Inicializa el formulario con los datos del cliente que se está editando o valores por defecto para uno nuevo.
const form = useForm({
  requiere_factura: props.cliente.requiere_factura,
  nombre_razon_social: props.cliente.nombre_razon_social,
  tipo_persona: props.cliente.tipo_persona,
  rfc: props.cliente.rfc,
  regimen_fiscal: props.cliente.regimen_fiscal,
  uso_cfdi: props.cliente.uso_cfdi,
  email: props.cliente.email,
  telefono: props.cliente.telefono,
  calle: props.cliente.calle,
  numero_exterior: props.cliente.numero_exterior,
  numero_interior: props.cliente.numero_interior,
  colonia: props.cliente.colonia,
  codigo_postal: props.cliente.codigo_postal,
  municipio: props.cliente.municipio,
  estado: props.cliente.estado,
  pais: props.cliente.pais
});

// --- Propiedades computadas para la UI y validación general ---

// Verifica si existen errores globales en el formulario (de Inertia)
const hasGlobalErrors = computed(() => {
  return Object.keys(form.errors).length > 0;
});

// Determina si el formulario es válido para ser enviado
const isFormValid = computed(() => {
  // Campos obligatorios generales
  const requiredFields = ['nombre_razon_social', 'email', 'telefono', 'calle', 'numero_exterior', 'colonia', 'codigo_postal', 'municipio', 'estado', 'pais'];

  // Si requiere factura, añade los campos fiscales como obligatorios
  if (form.requiere_factura === 'si') {
    requiredFields.push('tipo_persona', 'rfc', 'regimen_fiscal', 'uso_cfdi');
  }

  // Comprueba que todos los campos obligatorios no estén vacíos
  const allRequiredFieldsFilled = requiredFields.every(field =>
    form[field] !== null && form[field] !== undefined && form[field].toString().trim() !== ''
  );

  // El formulario es válido si todos los campos obligatorios están llenos
  // Y si no hay errores de validación activos en el objeto 'form.errors' de Inertia
  return allRequiredFieldsFilled && Object.keys(form.errors).length === 0;
});

// --- Funciones de manejo de eventos y validación ---

/**
 * Maneja el cambio en la opción 'requiere_factura'.
 * Si es 'no', establece automáticamente los datos de "Público en General".
 * Si es 'si', limpia los campos relacionados con "Público en General" para que el usuario los introduzca.
 */


/**
 * Maneja el cambio en el 'tipo_persona' (Física o Moral).
 * Reinicia el campo RFC y su estado de validación, ya que el formato cambia.
 */
const handleTipoPersonaChange = () => {
  form.rfc = ''; // Limpia el RFC al cambiar el tipo de persona
  form.clearErrors('rfc');
  rfcValidationStatus.value = null;
  rfcValidationMessage.value = '';
};

/**
 * Procesa la entrada del RFC, lo convierte a mayúsculas y lo limpia de caracteres no válidos.
 * También dispara la validación si la longitud es la esperada.
 * @param {Event} event - El evento de entrada (input).
 */
const handleRfcInput = (event) => {
  // Convierte a mayúsculas y elimina caracteres no alfanuméricos (excepto & y Ñ)
  const value = event.target.value.toUpperCase().replace(/[^A-ZÑ&0-9]/g, '');
  form.rfc = value;

  // Determina la longitud esperada del RFC según el tipo de persona
  const expectedLength = form.tipo_persona === 'fisica' ? 13 : 12;

  // Dispara la validación si la longitud coincide con la esperada
  if (value.length === expectedLength) {
    validarRFC();
  } else {
    // Si la longitud no es la esperada, limpia el estado de validación del RFC
    rfcValidationStatus.value = null;
    rfcValidationMessage.value = '';
    form.clearErrors('rfc'); // Asegura que no haya un error persistente de RFC
  }
};

/**
 * Valida el formato del RFC según el tipo de persona (Física o Moral).
 * Actualiza el estado de validación y el mensaje.
 * @returns {boolean} True si el RFC es válido, false en caso contrario.
 */
const validarRFC = () => {
  // Si el RFC es nulo, vacío o el valor de "Público en General", es considerado válido aquí
  if (!form.rfc || form.rfc.trim() === '' || form.rfc === 'XAXX010101000') {
    form.clearErrors('rfc');
    rfcValidationStatus.value = null;
    rfcValidationMessage.value = '';
    return true;
  }

  // Expresiones regulares para RFC de persona física y moral
  const rfcRegexFisica = /^[A-ZÑ&]{4}\d{6}[A-Z0-9]{3}$/; // 4 letras + 6 dígitos + 3 caracteres (homoclave)
  const rfcRegexMoral = /^[A-ZÑ&]{3}\d{6}[A-Z0-9]{3}$/;  // 3 letras + 6 dígitos + 3 caracteres (homoclave)

  let isValid = false;
  let errorMessage = '';

  if (form.tipo_persona === 'fisica') {
    if (form.rfc.length === 13 && rfcRegexFisica.test(form.rfc)) {
      isValid = true;
    } else {
      errorMessage = 'El RFC debe tener 13 caracteres (4 letras, 6 dígitos, 3 alfanuméricos) y ser válido para una persona física.';
    }
  } else { // tipo_persona === 'moral'
    if (form.rfc.length === 12 && rfcRegexMoral.test(form.rfc)) {
      isValid = true;
    } else {
      errorMessage = 'El RFC debe tener 12 caracteres (3 letras, 6 dígitos, 3 alfanuméricos) y ser válido para una persona moral.';
    }
  }

  if (!isValid) {
    form.setError('rfc', errorMessage); // Establece el error en el objeto 'form' de Inertia
    rfcValidationStatus.value = 'invalid';
    rfcValidationMessage.value = errorMessage;
    return false;
  } else {
    form.clearErrors('rfc'); // Limpia cualquier error anterior para este campo
    rfcValidationStatus.value = 'valid';
    rfcValidationMessage.value = 'RFC válido';
    return true;
  }
};

/**
 * Valida que el campo de teléfono contenga exactamente 10 dígitos.
 * Limpia el valor para asegurar que solo haya números.
 * @returns {boolean} True si el teléfono es válido, false en caso contrario.
 */
const validarTelefono = () => {
  const telefonoLimpio = form.telefono.replace(/\D/g, ''); // Elimina caracteres no numéricos
  form.telefono = telefonoLimpio; // Actualiza el modelo con el valor limpio

  if (telefonoLimpio.length !== 10) {
    form.setError('telefono', 'El teléfono debe tener exactamente 10 dígitos.');
    return false;
  }

  form.clearErrors('telefono');
  return true;
};

/**
 * Valida el formato del correo electrónico.
 * @returns {boolean} True si el email es válido, false en caso contrario.
 */
const validarEmail = () => {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/; // Expresión regular básica para email

  if (!emailRegex.test(form.email)) {
    form.setError('email', 'Ingresa un email válido.');
    return false;
  }

  form.clearErrors('email');
  return true;
};

/**
 * Convierte el valor de un campo específico a mayúsculas y elimina espacios en blanco al inicio/final.
 * Se utiliza en el evento @blur de los campos de texto.
 * @param {string} campo - El nombre del campo a convertir.
 */
const convertirAMayusculas = (campo) => {
  if (form[campo] && campo !== 'email') { // El email no se convierte a mayúsculas
    form[campo] = form[campo].toString().toUpperCase().trim();
  }
};

/**
 * Reinicia el formulario a su estado inicial o por defecto.
 * Limpia todos los errores y mensajes de validación.
 */
const resetForm = () => {
  // Inertia's form.reset() restablece el formulario a su estado inicial.
  form.reset();
  // Se pueden restablecer explícitamente algunos valores por defecto si useForm() no los maneja así inicialmente
  form.requiere_factura = 'si';
  form.tipo_persona = 'fisica';
  // Asegura que los campos que fueron autocompletados o modificados se limpien correctamente
  form.rfc = '';
  form.regimen_fiscal = '';
  form.uso_cfdi = '';
  form.calle = '';
  form.colonia = '';
  form.numero_exterior = '';
  form.numero_interior = '';
  form.codigo_postal = '';
  form.municipio = '';
  form.estado = '';
  form.pais = '';

  // Reinicia los estados de validación específicos (RFC, éxito)
  rfcValidationStatus.value = null;
  rfcValidationMessage.value = '';
  showSuccessMessage.value = false;
  form.clearErrors(); // Limpia todos los errores del formulario
};

/**
 * Función principal para el envío del formulario.
 * Ejecuta validaciones del lado del cliente y luego envía los datos al servidor
 * usando las capacidades de Inertia.js (POST para crear, PUT para actualizar).
 */
const submit = () => {
  // 1. Limpiar mensajes de éxito y errores previos del formulario para una nueva validación
  showSuccessMessage.value = false;
  form.clearErrors();

  // 2. Ejecutar todas las validaciones del lado del cliente
  const isRfcValid = validarRFC();



  // 3. Si alguna validación del lado del cliente falla, detener el envío del formulario.
  // Las funciones de validación ya habrán establecido los errores en `form.errors`.
  if (!isRfcValid ) {
    console.warn('Falló la validación del lado del cliente. No se envió el formulario.');
    return;
  }

  // 4. Determinar si es una operación de actualización (PUT) o creación (POST)
  if (props.cliente.id) {
    // Si 'cliente.id' existe, estamos editando un cliente existente
    form.put(route('clientes.update', props.cliente.id), {
      preserveScroll: true, // Mantiene la posición del scroll después de la recarga/redirección
      onSuccess: () => {
        showSuccessMessage.value = true;
        // Ocultar el mensaje de éxito después de 3 segundos
        setTimeout(() => {
          showSuccessMessage.value = false;
        }, 3000);
        console.log('Cliente actualizado exitosamente.');
      },
      onError: (serverErrors) => {
        // Inertia.js propaga automáticamente los errores de validación de Laravel a `form.errors`.
        // Puedes loguear los errores para depuración.
        console.error('Error al actualizar cliente:', serverErrors);
      },
      onFinish: () => {
        // Lógica a ejecutar siempre al finalizar la petición (éxito o error)
        // Por ejemplo, para deshabilitar un spinner global si lo hubiera.
        console.log('Petición de actualización finalizada.');
      }
    });
  } else {
    // Si 'cliente.id' no existe, estamos creando un nuevo cliente
    form.post(route('clientes.store'), {
      preserveScroll: true, // Mantiene la posición del scroll
      onSuccess: () => {
        showSuccessMessage.value = true;
        // Ocultar el mensaje de éxito y resetear el formulario para una nueva entrada
        setTimeout(() => {
          showSuccessMessage.value = false;
          resetForm(); // Limpia el formulario para que el usuario pueda añadir otro cliente
        }, 3000);
        console.log('Cliente creado exitosamente.');
      },
      onError: (serverErrors) => {
        // Inertia.js propaga automáticamente los errores de validación de Laravel a `form.errors`.
        console.error('Error al crear cliente:', serverErrors);
      },
      onFinish: () => {
        // Lógica a ejecutar siempre al finalizar la petición
        console.log('Petición de creación finalizada.');
      }
    });
  }
};
</script>
