<template>
  <Head :title="'Editar Cliente: ' + cliente.nombre_razon_social" />
  <div class="max-w-6xl mx-auto p-6">
    <div class="bg-white shadow-lg rounded-lg">
      <!-- Header -->
      <div class="bg-gradient-to-r from-blue-600 to-blue-700 text-white p-6 rounded-t-lg">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-2xl font-bold">
              Editar Cliente
            </h1>
            <p class="text-blue-100 mt-1">
              Actualiza la información del cliente
            </p>
          </div>
          <div class="text-sm text-blue-100">
            Campos obligatorios marcados con <span class="text-red-300 font-bold">*</span>
          </div>
        </div>
      </div>

      <div class="p-6">
        <!-- Progress Bar -->
        <div class="mb-8">
          <div class="flex items-center justify-between text-sm text-gray-600 mb-2">
            <span>Progreso del formulario</span>
            <span>{{ Math.round(formProgress) }}% completado</span>
          </div>
          <div class="w-full bg-gray-200 rounded-full h-2">
            <div
              class="bg-gradient-to-r from-blue-500 to-blue-600 h-2 rounded-full transition-all duration-300"
              :style="{ width: formProgress + '%' }"
            ></div>
          </div>
        </div>

        <!-- Alertas globales de error -->
        <Transition
          enter-active-class="transition duration-300 ease-out"
          enter-from-class="transform scale-95 opacity-0"
          enter-to-class="transform scale-100 opacity-100"
          leave-active-class="transition duration-200 ease-in"
          leave-from-class="transform scale-100 opacity-100"
          leave-to-class="transform scale-95 opacity-0"
        >
          <div v-if="hasGlobalErrors" class="mb-6 p-4 bg-red-50 border-l-4 border-red-400 rounded-md">
            <div class="flex">
              <div class="flex-shrink-0">
                <i class="fas fa-exclamation-triangle text-red-400"></i>
              </div>
              <div class="ml-3">
                <h3 class="text-sm font-medium text-red-800">
                  Se encontraron {{ Object.keys(form.errors).length }} error(es) en el formulario
                </h3>
                <div class="mt-2 text-sm text-red-700">
                  <ul class="list-disc list-inside space-y-1">
                    <!-- FIX: Display the first error message from the array -->
                    <li v-for="(error, key) in form.errors" :key="key">{{ error[0] }}</li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </Transition>

        <!-- Alerta de éxito -->
        <Transition
          enter-active-class="transition duration-300 ease-out"
          enter-from-class="transform translate-y-2 opacity-0"
          enter-to-class="transform translate-y-0 opacity-100"
          leave-active-class="transition duration-200 ease-in"
          leave-from-class="transform translate-y-0 opacity-100"
          leave-to-class="transform translate-y-2 opacity-0"
        >
          <div v-if="showSuccessMessage" class="mb-6 p-4 bg-green-50 border-l-4 border-green-400 rounded-md">
            <div class="flex">
              <div class="flex-shrink-0">
                <i class="fas fa-check-circle text-green-400"></i>
              </div>
              <div class="ml-3">
                <p class="text-sm font-medium text-green-800">
                  ¡Excelente! Cliente actualizado exitosamente
                </p>
              </div>
            </div>
          </div>
        </Transition>

        <!-- Formulario principal -->
        <form @submit.prevent="submit" class="space-y-8">
          <!-- Sección: Información General -->
          <div class="bg-gray-50 p-6 rounded-lg border">
            <div class="flex items-center mb-6">
              <i class="fas fa-user text-blue-600 mr-3 text-xl"></i>
              <h2 class="text-xl font-semibold text-gray-900">Información General</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="md:col-span-2">
                <FormField
                  v-model="form.nombre_razon_social"
                  label="Nombre/Razón Social"
                  type="text"
                  id="nombre_razon_social"
                  :error="form.errors.nombre_razon_social?.[0]"
                  placeholder="Ingresa el nombre completo o razón social"
                  @blur="convertirAMayusculas('nombre_razon_social')"
                  :maxlength="255"
                  required
                  autocomplete="organization"
                />
              </div>

              <FormField
                v-model="form.email"
                label="Correo Electrónico"
                type="email"
                id="email"
                :error="form.errors.email?.[0]"
                placeholder="correo@ejemplo.com"
                @blur="validarEmail"
                required
                autocomplete="email"
              />

              <FormField
                v-model="form.telefono"
                label="Teléfono"
                type="tel"
                id="telefono"
                :maxlength="10"
                :error="form.errors.telefono?.[0]"
                placeholder="6621234567"
                @input="formatearTelefono"
                @blur="validarTelefono"
                pattern="[0-9]{10}"
                required
                autocomplete="tel"
              />
            </div>
          </div>

          <!-- Sección: Información Fiscal -->
          <div class="bg-gray-50 p-6 rounded-lg border">
            <div class="flex items-center mb-6">
              <i class="fas fa-file-invoice text-green-600 mr-3 text-xl"></i>
              <h2 class="text-xl font-semibold text-gray-900">Información Fiscal</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <FormField
                v-model="form.tipo_persona"
                label="Tipo de Persona"
                type="select"
                id="tipo_persona"
                :options="tipoPersonaOptions"
                :error="form.errors.tipo_persona?.[0]"
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
                  :error="form.errors.rfc?.[0]"
                  :placeholder="form.tipo_persona === 'fisica' ? 'ABCD123456EFG' : 'ABC123456EFG'"
                  @input="handleRfcInput"
                  @blur="validarRFC"
                  required
                  autocomplete="off"
                />
                <Transition
                  enter-active-class="transition duration-200 ease-out"
                  enter-from-class="transform scale-95 opacity-0"
                  enter-to-class="transform scale-100 opacity-100"
                >
                  <div v-if="rfcValidationStatus" class="mt-2 flex items-center">
                    <i v-if="rfcValidationStatus === 'valid'" class="fas fa-check-circle text-green-500"></i>
                    <i v-else class="fas fa-exclamation-circle text-red-500"></i>
                    <span :class="rfcValidationStatus === 'valid' ? 'text-green-600' : 'text-red-600'" class="text-sm ml-2">
                      {{ rfcValidationMessage }}
                    </span>
                  </div>
                </Transition>
              </div>

              <FormField
                v-model="form.regimen_fiscal"
                label="Régimen Fiscal"
                type="select"
                id="regimen_fiscal"
                :options="regimenesFiscalesOptions"
                :error="form.errors.regimen_fiscal?.[0]"
                required
              />

              <FormField
                v-model="form.uso_cfdi"
                label="Uso CFDI"
                type="select"
                id="uso_cfdi"
                :options="usosCFDIOptions"
                :error="form.errors.uso_cfdi?.[0]"
                required
              />
            </div>
          </div>

          <!-- Sección: Dirección -->
          <div class="bg-gray-50 p-6 rounded-lg border">
            <div class="flex items-center mb-6">
              <i class="fas fa-map-marker-alt text-purple-600 mr-3 text-xl"></i>
              <h2 class="text-xl font-semibold text-gray-900">Dirección</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              <div class="md:col-span-2">
                <FormField
                  v-model="form.calle"
                  label="Calle"
                  type="text"
                  id="calle"
                  :maxlength="100"
                  :error="form.errors.calle?.[0]"
                  placeholder="Nombre de la calle"
                  @blur="convertirAMayusculas('calle')"
                  required
                  autocomplete="street-address"
                />
              </div>

              <FormField
                v-model="form.numero_exterior"
                label="Número Exterior"
                type="text"
                id="numero_exterior"
                :error="form.errors.numero_exterior?.[0]"
                placeholder="123"
                @blur="convertirAMayusculas('numero_exterior')"
                required
              />

              <FormField
                v-model="form.numero_interior"
                label="Número Interior"
                type="text"
                id="numero_interior"
                :error="form.errors.numero_interior?.[0]"
                placeholder="A, B, 1, etc. (opcional)"
                @blur="convertirAMayusculas('numero_interior')"
              />

              <FormField
                v-model="form.colonia"
                label="Colonia"
                type="text"
                id="colonia"
                :error="form.errors.colonia?.[0]"
                placeholder="Nombre de la colonia"
                @blur="convertirAMayusculas('colonia')"
                required
              />

              <FormField
                v-model="form.codigo_postal"
                label="Código Postal"
                type="text"
                id="codigo_postal"
                :error="form.errors.codigo_postal?.[0]"
                placeholder="12345"
                maxlength="5"
                @input="validarCodigoPostal"
                @blur="buscarInfoPorCP"
                required
                autocomplete="postal-code"
              />

              <FormField
                v-model="form.municipio"
                label="Municipio"
                type="text"
                id="municipio"
                :error="form.errors.municipio?.[0]"
                placeholder="Nombre del municipio"
                @blur="convertirAMayusculas('municipio')"
                required
              />

              <FormField
                v-model="form.estado"
                label="Estado"
                type="text"
                id="estado"
                :error="form.errors.estado?.[0]"
                placeholder="Nombre del estado"
                @blur="convertirAMayusculas('estado')"
                required
              />

              <FormField
                v-model="form.pais"
                label="País"
                type="text"
                id="pais"
                :error="form.errors.pais?.[0]"
                placeholder="MÉXICO"
                @blur="convertirAMayusculas('pais')"
                required
                autocomplete="country"
              />
            </div>
          </div>

          <!-- Sección: Información Adicional -->
          <div class="bg-gray-50 p-6 rounded-lg border">
            <div class="flex items-center mb-6">
              <i class="fas fa-info-circle text-orange-600 mr-3 text-xl"></i>
              <h2 class="text-xl font-semibold text-gray-900">Información Adicional</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <FormField
                v-model="form.notas"
                label="Notas"
                type="textarea"
                id="notas"
                :error="form.errors.notas?.[0]"
                placeholder="Observaciones adicionales del cliente (opcional)"
                :rows="Number(4)"
                maxlength="500"
              />

              <div class="space-y-4">
                <div class="flex items-center">
                  <input
                    id="activo"
                    v-model="form.activo"
                    type="checkbox"
                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                  >
                  <label for="activo" class="ml-2 block text-sm text-gray-900">
                    Cliente activo
                  </label>
                </div>

                <div class="flex items-center">
                  <input
                    id="acepta_marketing"
                    v-model="form.acepta_marketing"
                    type="checkbox"
                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                  >
                  <label for="acepta_marketing" class="ml-2 block text-sm text-gray-900">
                    Acepta recibir comunicaciones de marketing
                  </label>
                </div>
              </div>
            </div>
          </div>

          <!-- Botones de acción -->
          <div class="flex justify-between items-center pt-6 border-t-2 border-gray-200">
            <button
              type="button"
              @click="resetForm"
              :disabled="form.processing"
              class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
            >
              <i class="fas fa-undo w-4 h-4 mr-2"></i>
              Revertir Cambios
            </button>

            <div class="flex items-center space-x-4">
              <div v-if="!isFormValid" class="text-sm text-red-600 flex items-center">
                <i class="fas fa-exclamation-triangle w-4 h-4 mr-1"></i>
                Completa todos los campos obligatorios
              </div>

              <button
                type="submit"
                :disabled="form.processing || !isFormValid || !form.isDirty"
                class="inline-flex items-center px-6 py-3 text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-blue-700 border border-transparent rounded-md hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 transform hover:scale-105"
              >
                <span v-if="form.processing" class="flex items-center">
                  <i class="fas fa-spinner fa-spin w-4 h-4 mr-2"></i>
                  Actualizando...
                </span>
                <span v-else class="flex items-center">
                  <i class="fas fa-check w-4 h-4 mr-2"></i>
                  Actualizar Cliente
                </span>
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import FormField from '@/Components/FormField.vue';

// Define el layout por defecto para esta página
defineOptions({ layout: AppLayout });

// Definición de props
const props = defineProps({
  cliente: {
    type: Object,
    required: true, // En Edit, el cliente siempre debe existir
  },
  regimenes_fiscales: {
    type: Array,
    default: () => []
  },
  usos_cfdi: {
    type: Array,
    default: () => []
  }
});

/*
regimenes_fisicas: {
    type: Array,
    default: () => ['605', '606', '607', '608', '610', '611']
},
*/

// Estados reactivos
const showSuccessMessage = ref(false);
const rfcValidationStatus = ref(null);
const rfcValidationMessage = ref('');
const isLoadingCP = ref(false);
const cpInfo = ref(null);

// Opciones para los campos select
const tipoPersonaOptions = [
  { value: '', text: 'Selecciona el tipo de persona', disabled: true },
  { value: 'fisica', text: 'Persona Física' },
  { value: 'moral', text: 'Persona Moral' }
];

const regimenesFiscalesOptions = computed(() => {
  const defaultOptions = [
    { value: '', text: 'Selecciona un régimen fiscal', disabled: true },
    { value: '601', text: '601 - General de Ley Personas Morales' },
    { value: '605', text: '605 - Sueldos y Salarios e Ingresos Asimilados a Salarios' },
    { value: '612', text: '612 - Personas Físicas con Actividades Empresariales y Profesionales' },
    { value: '626', text: '626 - Régimen Simplificado de Confianza' },
    // Agrega más opciones por defecto si es necesario
  ];

  return props.regimenes_fiscales.length > 0
    ? [{ value: '', text: 'Selecciona un régimen fiscal', disabled: true }, ...props.regimenes_fiscales]
    : defaultOptions;
});

const usosCFDIOptions = computed(() => {
  const defaultOptions = [
    { value: '', text: 'Selecciona un uso CFDI', disabled: true },
    { value: 'G01', text: 'G01 - Adquisición de mercancías' },
    { value: 'G03', text: 'G03 - Gastos en general' },
    { value: 'S01', text: 'S01 - Sin efectos fiscales' },
    { value: 'CP01', text: 'CP01 - Pagos' },
    // Agrega más opciones por defecto si es necesario
  ];

  return props.usos_cfdi.length > 0
    ? [{ value: '', text: 'Selecciona un uso CFDI', disabled: true }, ...props.usos_cfdi]
    : defaultOptions;
});

// Formulario de Inertia
const form = useForm({
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
  pais: props.cliente.pais,
  notas: props.cliente.notas || '',
  activo: props.cliente.activo !== undefined ? props.cliente.activo : true,
  acepta_marketing: props.cliente.acepta_marketing || false
});

// Propiedades computadas
const hasGlobalErrors = computed(() => {
  return Object.keys(form.errors).length > 0;
});

const formProgress = computed(() => {
  const requiredFields = [
    'nombre_razon_social', 'email', 'telefono', 'tipo_persona', 'rfc',
    'regimen_fiscal', 'uso_cfdi', 'calle', 'numero_exterior', 'colonia',
    'codigo_postal', 'municipio', 'estado', 'pais'
  ];
  const filledFields = requiredFields.filter(field => {
    const value = form[field];
    return value !== null && value !== undefined && value.toString().trim() !== '';
  });
  return (filledFields.length / requiredFields.length) * 100;
});

const isFormValid = computed(() => {
  const requiredFields = [
    'nombre_razon_social', 'email', 'telefono', 'tipo_persona', 'rfc',
    'regimen_fiscal', 'uso_cfdi', 'calle', 'numero_exterior', 'colonia',
    'codigo_postal', 'municipio', 'estado', 'pais'
  ];
  const allRequiredFieldsFilled = requiredFields.every(field =>
    form[field] !== null && form[field] !== undefined && form[field].toString().trim() !== ''
  );
  return allRequiredFieldsFilled && Object.keys(form.errors).length === 0;
});

// Funciones de validación y manejo
const handleTipoPersonaChange = () => {
  form.rfc = '';
  form.clearErrors('rfc');
  rfcValidationStatus.value = null;
  rfcValidationMessage.value = '';
};

const handleRfcInput = (event) => {
  const value = event.target.value.toUpperCase().replace(/[^A-ZÑ&0-9]/g, '');
  form.rfc = value;
  const expectedLength = form.tipo_persona === 'fisica' ? 13 : 12;
  if (value.length === expectedLength) {
    validarRFC();
  } else {
    rfcValidationStatus.value = null;
    rfcValidationMessage.value = '';
    form.clearErrors('rfc');
  }
};

const validarRFC = () => {
  if (!form.rfc || form.rfc.trim() === '') {
    rfcValidationStatus.value = null;
    rfcValidationMessage.value = '';
    return false;
  }
  const rfcRegexFisica = /^[A-ZÑ&]{4}\d{6}[A-Z0-9]{3}$/;
  const rfcRegexMoral = /^[A-ZÑ&]{3}\d{6}[A-Z0-9]{3}$/;
  let isValid = false;
  let errorMessage = '';

  if (form.tipo_persona === 'fisica') {
    if (form.rfc.length === 13 && rfcRegexFisica.test(form.rfc)) {
      isValid = true;
    } else {
      errorMessage = 'RFC inválido para persona física (13 caracteres).';
    }
  } else {
    if (form.rfc.length === 12 && rfcRegexMoral.test(form.rfc)) {
      isValid = true;
    } else {
      errorMessage = 'RFC inválido para persona moral (12 caracteres).';
    }
  }

  if (!isValid) {
    form.setError('rfc', errorMessage);
    rfcValidationStatus.value = 'invalid';
    rfcValidationMessage.value = errorMessage;
    return false;
  } else {
    form.clearErrors('rfc');
    rfcValidationStatus.value = 'valid';
    rfcValidationMessage.value = 'RFC válido';
    return true;
  }
};

const formatearTelefono = (event) => {
  const value = event.target.value.replace(/\D/g, '');
  form.telefono = value;
  if (value.length === 10) {
    validarTelefono();
  }
};

const validarTelefono = () => {
  const telefonoLimpio = form.telefono.replace(/\D/g, '');
  form.telefono = telefonoLimpio;
  if (telefonoLimpio.length !== 10) {
    form.setError('telefono', 'El teléfono debe tener exactamente 10 dígitos.');
    return false;
  }
  form.clearErrors('telefono');
  return true;
};

const validarEmail = () => {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!emailRegex.test(form.email)) {
    form.setError('email', 'Ingresa un email válido.');
    return false;
  }
  form.clearErrors('email');
  return true;
};

const convertirAMayusculas = (campo) => {
  if (form[campo] && campo !== 'email') {
    form[campo] = form[campo].toString().toUpperCase().trim();
  }
};

const validarCodigoPostal = (event) => {
  const value = event.target.value.replace(/\D/g, '');
  form.codigo_postal = value;
  if (value.length === 5) {
    buscarInfoPorCP();
  }
};

const buscarInfoPorCP = async () => {
  if (form.codigo_postal.length !== 5) return;
  isLoadingCP.value = true;
  try {
    // Simulación de API. Reemplazar con una llamada real si es necesario.
    const cpData = {
      '83000': { municipio: 'HERMOSILLO', estado: 'SONORA' },
      '06600': { municipio: 'CUAUHTÉMOC', estado: 'CIUDAD DE MÉXICO' },
      '64000': { municipio: 'MONTERREY', estado: 'NUEVO LEÓN' },
      '44100': { municipio: 'GUADALAJARA', estado: 'JALISCO' }
    };
    const info = cpData[form.codigo_postal];
    if (info) {
      form.municipio = info.municipio;
      form.estado = info.estado;
      cpInfo.value = info;
    }
  } catch (error) {
    console.error('Error al buscar información del código postal:', error);
  } finally {
    isLoadingCP.value = false;
  }
};

const resetForm = () => {
  // `form.reset()` de Inertia revierte a los valores iniciales pasados a `useForm`
  form.reset();
  rfcValidationStatus.value = null;
  rfcValidationMessage.value = '';
  showSuccessMessage.value = false;
  cpInfo.value = null;
  form.clearErrors();
};

const submit = () => {
  showSuccessMessage.value = false;
  form.clearErrors();

  // Realizar validaciones del lado del cliente antes de enviar
  const isRfcValid = validarRFC();
  const isTelefonoValid = validarTelefono();
  const isEmailValid = validarEmail();

  if (!isRfcValid || !isTelefonoValid || !isEmailValid || !isFormValid.value) {
    console.warn('Falló la validación del lado del cliente. No se envió el formulario.');
    // Opcional: mostrar un mensaje de error general
    if (!form.hasErrors) {
        form.setError('general', 'Por favor, corrige los errores antes de continuar.');
    }
    return;
  }

  const submitConfig = {
    preserveScroll: true,
    onSuccess: () => {
      showSuccessMessage.value = true;
      // `form.defaults()` actualiza los valores base del formulario con los nuevos datos
      // `form.reset()` los revierte a esos nuevos valores base.
      form.defaults(form.data());
      form.reset();

      setTimeout(() => {
        showSuccessMessage.value = false;
      }, 3000);
    },
    onError: (serverErrors) => {
      console.error('Error en la petición:', serverErrors);
    }
  };

  // Para la página de edición, siempre usamos PUT/PATCH
  form.put(route('clientes.update', props.cliente.id), submitConfig);
};

// Watchers para mejorar UX
watch(() => form.tipo_persona, (newValue) => {
  if (form.rfc) {
    handleTipoPersonaChange();
  }
});
</script>
