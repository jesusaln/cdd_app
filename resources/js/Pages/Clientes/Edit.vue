<template>
    <Head title="Editar Cliente" />
    <div class="max-w-4xl mx-auto p-6">
        <!-- Header con navegación -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Editar Cliente</h1>
                <p class="text-gray-600 mt-1">Actualiza la información del cliente</p>
            </div>
            <button
                @click="$inertia.visit(route('clientes.index'))"
                class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors"
            >
                Volver a Lista
            </button>
        </div>

        <!-- Mensajes de estado -->
        <div v-if="successMessage" class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg">
            {{ successMessage }}
        </div>

        <!-- Formulario de edición -->
        <form @submit.prevent="submit" class="bg-white shadow-lg rounded-lg p-6">
            <!-- Errores generales -->
            <div v-if="Object.keys(form.errors).length > 0" class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                <h3 class="text-red-800 font-medium mb-2">Por favor corrige los siguientes errores:</h3>
                <ul class="text-red-700 text-sm space-y-1">
                    <li v-for="(error, field) in form.errors" :key="field">{{ error }}</li>
                </ul>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Información Básica -->
                <div class="lg:col-span-2">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4 border-b pb-2">Información Básica</h2>
                </div>

                <!-- Nombre/Razón Social -->
                <div class="lg:col-span-2">
                    <label for="nombre_razon_social" class="block text-sm font-medium text-gray-700 mb-2">
                        Nombre/Razón Social *
                    </label>
                    <input
                        v-model="form.nombre_razon_social"
                        type="text"
                        id="nombre_razon_social"
                        :class="inputClass('nombre_razon_social')"
                        @blur="convertirAMayusculas('nombre_razon_social')"
                        @input="clearFieldError('nombre_razon_social')"
                        maxlength="100"
                        required
                        placeholder="Ingresa el nombre o razón social"
                    />
                    <div v-if="form.errors.nombre_razon_social" class="mt-1 text-red-500 text-sm">
                        {{ form.errors.nombre_razon_social }}
                    </div>
                </div>

                <!-- Tipo de Persona -->
                <div>
                    <label for="tipo_persona" class="block text-sm font-medium text-gray-700 mb-2">
                        Tipo de Persona *
                    </label>
                    <select
                        v-model="form.tipo_persona"
                        id="tipo_persona"
                        :class="inputClass('tipo_persona')"
                        @change="handleTipoPersonaChange"
                        required
                    >
                        <option value="" disabled>Selecciona el tipo de persona</option>
                        <option value="fisica">Persona Física</option>
                        <option value="moral">Persona Moral</option>
                    </select>
                    <div v-if="form.errors.tipo_persona" class="mt-1 text-red-500 text-sm">
                        {{ form.errors.tipo_persona }}
                    </div>
                </div>

                <!-- RFC -->
                <div>
                    <label for="rfc" class="block text-sm font-medium text-gray-700 mb-2">
                        RFC *
                        <span class="text-xs text-gray-500">
                            ({{ form.tipo_persona === 'fisica' ? '13 caracteres' : '12 caracteres' }})
                        </span>
                    </label>
                    <input
                        v-model="form.rfc"
                        type="text"
                        id="rfc"
                        :maxlength="form.tipo_persona === 'fisica' ? 13 : 12"
                        :class="inputClass('rfc')"
                        @input="handleRFCInput"
                        @blur="validarRFC"
                        :placeholder="form.tipo_persona === 'fisica' ? 'AAAA######AAA' : 'AAA######AAA'"
                        required
                    />
                    <div v-if="form.errors.rfc" class="mt-1 text-red-500 text-sm">
                        {{ form.errors.rfc }}
                    </div>
                </div>

                <!-- Información Fiscal -->
                <div class="lg:col-span-2 mt-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4 border-b pb-2">Información Fiscal</h2>
                </div>

                <!-- Régimen Fiscal -->
                <div>
                    <label for="regimen_fiscal" class="block text-sm font-medium text-gray-700 mb-2">
                        Régimen Fiscal *
                    </label>
                    <select
                        v-model="form.regimen_fiscal"
                        id="regimen_fiscal"
                        :class="inputClass('regimen_fiscal')"
                        @change="clearFieldError('regimen_fiscal')"
                        required
                    >
                        <option value="" disabled>Selecciona un régimen fiscal</option>
                        <option v-for="regimen in regimenesFiscales" :key="regimen.clave" :value="regimen.descripcion">
                            {{ regimen.clave }} - {{ regimen.descripcion }}
                        </option>
                    </select>
                    <div v-if="form.errors.regimen_fiscal" class="mt-1 text-red-500 text-sm">
                        {{ form.errors.regimen_fiscal }}
                    </div>
                </div>

                <!-- Uso CFDI -->
                <div>
                    <label for="uso_cfdi" class="block text-sm font-medium text-gray-700 mb-2">
                        Uso CFDI *
                    </label>
                    <select
                        v-model="form.uso_cfdi"
                        id="uso_cfdi"
                        :class="inputClass('uso_cfdi')"
                        @change="clearFieldError('uso_cfdi')"
                        required
                    >
                        <option value="" disabled>Selecciona un uso CFDI</option>
                        <option v-for="uso in usosCFDI" :key="uso.clave" :value="uso.descripcion">
                            {{ uso.clave }} - {{ uso.descripcion }}
                        </option>
                    </select>
                    <div v-if="form.errors.uso_cfdi" class="mt-1 text-red-500 text-sm">
                        {{ form.errors.uso_cfdi }}
                    </div>
                </div>

                <!-- Información de Contacto -->
                <div class="lg:col-span-2 mt-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4 border-b pb-2">Información de Contacto</h2>
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        Email *
                    </label>
                    <input
                        v-model="form.email"
                        type="email"
                        id="email"
                        :class="inputClass('email')"
                        @input="clearFieldError('email')"
                        @blur="validarEmail"
                        placeholder="ejemplo@correo.com"
                        required
                    />
                    <div v-if="form.errors.email" class="mt-1 text-red-500 text-sm">
                        {{ form.errors.email }}
                    </div>
                </div>

                <!-- Teléfono -->
                <div>
                    <label for="telefono" class="block text-sm font-medium text-gray-700 mb-2">
                        Teléfono *
                    </label>
                    <input
                        v-model="form.telefono"
                        type="tel"
                        id="telefono"
                        maxlength="10"
                        :class="inputClass('telefono')"
                        @input="handleTelefonoInput"
                        @blur="validarTelefono"
                        placeholder="1234567890"
                        required
                    />
                    <div v-if="form.errors.telefono" class="mt-1 text-red-500 text-sm">
                        {{ form.errors.telefono }}
                    </div>
                </div>

                <!-- Dirección -->
                <div class="lg:col-span-2 mt-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4 border-b pb-2">Dirección</h2>
                </div>

                <!-- Código Postal -->
                <div>
                    <label for="codigo_postal" class="block text-sm font-medium text-gray-700 mb-2">
                        Código Postal *
                    </label>
                    <input
                        v-model="form.codigo_postal"
                        type="text"
                        id="codigo_postal"
                        maxlength="5"
                        :class="inputClass('codigo_postal')"
                        @input="handleCodigoPostalInput"
                        placeholder="12345"
                        required
                    />
                    <div v-if="form.errors.codigo_postal" class="mt-1 text-red-500 text-sm">
                        {{ form.errors.codigo_postal }}
                    </div>
                </div>

                <!-- Calle -->
                <div>
                    <label for="calle" class="block text-sm font-medium text-gray-700 mb-2">
                        Calle *
                    </label>
                    <input
                        v-model="form.calle"
                        type="text"
                        id="calle"
                        maxlength="100"
                        :class="inputClass('calle')"
                        @blur="convertirAMayusculas('calle')"
                        @input="clearFieldError('calle')"
                        placeholder="Nombre de la calle"
                        required
                    />
                    <div v-if="form.errors.calle" class="mt-1 text-red-500 text-sm">
                        {{ form.errors.calle }}
                    </div>
                </div>

                <!-- Número Exterior -->
                <div>
                    <label for="numero_exterior" class="block text-sm font-medium text-gray-700 mb-2">
                        Número Exterior *
                    </label>
                    <input
                        v-model="form.numero_exterior"
                        type="text"
                        id="numero_exterior"
                        maxlength="10"
                        :class="inputClass('numero_exterior')"
                        @input="clearFieldError('numero_exterior')"
                        placeholder="123"
                        required
                    />
                    <div v-if="form.errors.numero_exterior" class="mt-1 text-red-500 text-sm">
                        {{ form.errors.numero_exterior }}
                    </div>
                </div>

                <!-- Número Interior -->
                <div>
                    <label for="numero_interior" class="block text-sm font-medium text-gray-700 mb-2">
                        Número Interior
                    </label>
                    <input
                        v-model="form.numero_interior"
                        type="text"
                        id="numero_interior"
                        maxlength="10"
                        :class="inputClass('numero_interior')"
                        placeholder="A, B, 1, 2..."
                    />
                </div>

                <!-- Colonia -->
                <div>
                    <label for="colonia" class="block text-sm font-medium text-gray-700 mb-2">
                        Colonia *
                    </label>
                    <input
                        v-model="form.colonia"
                        type="text"
                        id="colonia"
                        :class="inputClass('colonia')"
                        @blur="convertirAMayusculas('colonia')"
                        @input="clearFieldError('colonia')"
                        placeholder="Nombre de la colonia"
                        required
                    />
                    <div v-if="form.errors.colonia" class="mt-1 text-red-500 text-sm">
                        {{ form.errors.colonia }}
                    </div>
                </div>

                <!-- Municipio -->
                <div>
                    <label for="municipio" class="block text-sm font-medium text-gray-700 mb-2">
                        Municipio *
                    </label>
                    <input
                        v-model="form.municipio"
                        type="text"
                        id="municipio"
                        :class="inputClass('municipio')"
                        @blur="convertirAMayusculas('municipio')"
                        @input="clearFieldError('municipio')"
                        placeholder="Nombre del municipio"
                        required
                    />
                    <div v-if="form.errors.municipio" class="mt-1 text-red-500 text-sm">
                        {{ form.errors.municipio }}
                    </div>
                </div>

                <!-- Estado -->
                <div>
                    <label for="estado" class="block text-sm font-medium text-gray-700 mb-2">
                        Estado *
                    </label>
                    <input
                        v-model="form.estado"
                        type="text"
                        id="estado"
                        :class="inputClass('estado')"
                        @blur="convertirAMayusculas('estado')"
                        @input="clearFieldError('estado')"
                        placeholder="Nombre del estado"
                        required
                    />
                    <div v-if="form.errors.estado" class="mt-1 text-red-500 text-sm">
                        {{ form.errors.estado }}
                    </div>
                </div>

                <!-- País -->
                <div>
                    <label for="pais" class="block text-sm font-medium text-gray-700 mb-2">
                        País *
                    </label>
                    <input
                        v-model="form.pais"
                        type="text"
                        id="pais"
                        :class="inputClass('pais')"
                        @blur="convertirAMayusculas('pais')"
                        @input="clearFieldError('pais')"
                        placeholder="Nombre del país"
                        required
                    />
                    <div v-if="form.errors.pais" class="mt-1 text-red-500 text-sm">
                        {{ form.errors.pais }}
                    </div>
                </div>
            </div>

            <!-- Botones de acción -->
            <div class="mt-8 flex flex-col sm:flex-row gap-3 justify-end">
                <button
                    type="button"
                    @click="resetForm"
                    class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-lg transition-colors"
                    :disabled="form.processing"
                >
                    Restablecer
                </button>
                <button
                    type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center"
                    :disabled="form.processing || !isFormValid"
                >
                    <svg v-if="form.processing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    {{ form.processing ? 'Actualizando...' : 'Actualizar Cliente' }}
                </button>
            </div>
        </form>
    </div>
</template>

<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed, onMounted, watch } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';

defineOptions({ layout: AppLayout });

// Props
const props = defineProps({
   cliente: Object,
});

// Estados reactivos
const successMessage = ref('');

// Listas de regímenes fiscales y usos CFDI
const regimenesFiscales = [
   { clave: '601', descripcion: 'General de Ley Personas Morales' },
   { clave: '603', descripcion: 'Personas Morales con Fines no Lucrativos' },
   // ... (el resto de los regímenes fiscales)
];

const usosCFDI = [
   { clave: 'G01', descripcion: 'Adquisición de mercancías' },
   { clave: 'G02', descripcion: 'Devoluciones, descuentos o bonificaciones' },
   // ... (el resto de los usos CFDI)
];

// Inicialización del formulario
const form = useForm({
   nombre_razon_social: props.cliente.nombre_razon_social || '',
   tipo_persona: props.cliente.tipo_persona || '',
   rfc: props.cliente.rfc || '',
   regimen_fiscal: props.cliente.regimen_fiscal || '',
   uso_cfdi: props.cliente.uso_cfdi || '',
   email: props.cliente.email || '',
   telefono: props.cliente.telefono || '',
   calle: props.cliente.calle || '',
   numero_exterior: props.cliente.numero_exterior || '',
   numero_interior: props.cliente.numero_interior || '',
   colonia: props.cliente.colonia || '',
   codigo_postal: props.cliente.codigo_postal || '',
   municipio: props.cliente.municipio || '',
   estado: props.cliente.estado || '',
   pais: props.cliente.pais || 'MÉXICO',
});

// Computed para validación del formulario
const isFormValid = computed(() => {
   const requiredFields = [
       'nombre_razon_social', 'tipo_persona', 'rfc', 'regimen_fiscal',
       'uso_cfdi', 'email', 'telefono', 'calle', 'numero_exterior',
       'colonia', 'codigo_postal', 'municipio', 'estado', 'pais'
   ];

   return requiredFields.every(field => form[field]?.toString().trim()) &&
          Object.keys(form.errors).length === 0;
});

// Función para clases CSS dinámicas
const inputClass = (field) => {
   const baseClass = "mt-1 block w-full rounded-lg border shadow-sm transition-colors focus:ring-2 focus:ring-blue-500 focus:border-blue-500";
   const errorClass = "border-red-300 text-red-900 placeholder-red-300 focus:ring-red-500 focus:border-red-500";
   const normalClass = "border-gray-300 focus:border-blue-500";

   return `${baseClass} ${form.errors[field] ? errorClass : normalClass}`;
};

// Funciones de validación
const validarRFC = () => {
   if (!form.rfc || !form.tipo_persona) return;

   const rfcRegexFisica = /^[A-ZÑ&]{4}\d{6}[A-Z\d]{3}$/;
   const rfcRegexMoral = /^[A-ZÑ&]{3}\d{6}[A-Z\d]{3}$/;

   form.rfc = form.rfc.toUpperCase().replace(/[^A-ZÑ&0-9]/g, '');

   if (form.tipo_persona === 'fisica') {
       if (form.rfc.length !== 13 || !rfcRegexFisica.test(form.rfc)) {
           form.setError('rfc', 'El RFC debe tener 13 caracteres válidos para persona física.');
           return false;
       }
   } else if (form.tipo_persona === 'moral') {
       if (form.rfc.length !== 12 || !rfcRegexMoral.test(form.rfc)) {
           form.setError('rfc', 'El RFC debe tener 12 caracteres válidos para persona moral.');
           return false;
       }
   }

   form.clearErrors('rfc');
   return true;
};

const validarEmail = () => {
   if (!form.email) return;

   const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
   if (!emailRegex.test(form.email)) {
       form.setError('email', 'Por favor ingresa un email válido.');
       return false;
   }

   form.clearErrors('email');
   return true;
};

const validarTelefono = () => {
   if (!form.telefono) return;

   const telefonoRegex = /^\d{10}$/;
   if (!telefonoRegex.test(form.telefono)) {
       form.setError('telefono', 'El teléfono debe tener exactamente 10 dígitos.');
       return false;
   }

   form.clearErrors('telefono');
   return true;
};

// Handlers de eventos
const handleTipoPersonaChange = () => {
   form.rfc = '';
   form.clearErrors(['rfc', 'tipo_persona']);
};

const handleRFCInput = (event) => {
   const value = event.target.value.toUpperCase().replace(/[^A-ZÑ&0-9]/g, '');
   const maxLength = form.tipo_persona === 'fisica' ? 13 : 12;
   form.rfc = value.substring(0, maxLength);
   form.clearErrors('rfc');
};

const handleTelefonoInput = (event) => {
   const value = event.target.value.replace(/\D/g, '');
   form.telefono = value.substring(0, 10);
   form.clearErrors('telefono');
};

const handleCodigoPostalInput = (event) => {
   const value = event.target.value.replace(/\D/g, '');
   form.codigo_postal = value.substring(0, 5);
   form.clearErrors('codigo_postal');
};

// Funciones utilitarias
const convertirAMayusculas = (campo) => {
   if (form[campo]) {
       form[campo] = form[campo].toString().toUpperCase();
   }
};

const clearFieldError = (field) => {
   if (form.errors[field]) {
       form.clearErrors(field);
   }
};

const resetForm = () => {
   form.reset();
   Object.keys(props.cliente).forEach(key => {
       if (form.hasOwnProperty(key)) {
           form[key] = props.cliente[key] || '';
       }
   });
   successMessage.value = '';
};

// Envío del formulario
const submit = () => {
   const validaciones = [validarRFC(), validarEmail(), validarTelefono()];

   if (validaciones.some(v => v === false)) {
       return;
   }

   const camposTexto = ['nombre_razon_social', 'calle', 'colonia', 'numero_exterior', 'numero_interior', 'municipio', 'estado', 'pais'];
   camposTexto.forEach(campo => {
       if (form[campo]) {
           form[campo] = form[campo].toString().trim();
       }
   });

   form.put(route('clientes.update', props.cliente.id), {
       preserveScroll: true,
       preserveState: true,
       onSuccess: () => {
           successMessage.value = 'Cliente actualizado correctamente.';
           window.scrollTo({ top: 0, behavior: 'smooth' });
       },
       onError: (errors) => {
           console.error('Error al actualizar cliente:', errors);
           const firstErrorField = Object.keys(errors)[0];
           const element = document.getElementById(firstErrorField);
           if (element) {
               element.scrollIntoView({ behavior: 'smooth', block: 'center' });
               element.focus();
           }
       },
   });
};

// Watcher para limpiar mensaje de éxito después de un tiempo
watch(successMessage, (newValue) => {
   if (newValue) {
       setTimeout(() => {
           successMessage.value = '';
       }, 5000);
   }
});
</script>

<style scoped>
/* Estilos personalizados si necesitas */
.fade-enter-active, .fade-leave-active {
    transition: opacity 0.3s;
}
.fade-enter-from, .fade-leave-to {
    opacity: 0;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.animate-spin {
    animation: spin 1s linear infinite;
}
</style>
