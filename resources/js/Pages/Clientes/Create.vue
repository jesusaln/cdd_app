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

      <div v-if="hasGlobalErrors" class="mb-6 p-4 bg-red-50 border border-red-200 rounded-md">
        <div class="flex">
          <div class="ml-3">
            <h3 class="text-sm font-medium text-red-800">Error en el formulario</h3>
            <div class="mt-2 text-sm text-red-700">
              <ul class="list-disc list-inside space-y-1">
                <li v-for="(error, key) in form.errors" :key="key">{{ Array.isArray(error) ? error[0] : error }}</li>
              </ul>
            </div>
          </div>
        </div>
      </div>

      <div v-if="showSuccessMessage" class="mb-6 p-4 bg-green-50 border border-green-200 rounded-md">
        <p class="text-sm font-medium text-green-800">Cliente creado exitosamente</p>
      </div>

      <form @submit.prevent="submit" class="space-y-8">
        <!-- Información General -->
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
                @blur="toUpper('nombre_razon_social')"
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
              required
            />

            <FormField
              v-model="form.telefono"
              label="Teléfono"
              type="tel"
              id="telefono"
              :error="form.errors.telefono"
              placeholder="Opcional"
            />
          </div>
        </div>

        <!-- Información Fiscal -->
        <div class="border-b border-gray-200 pb-6">
          <h2 class="text-lg font-medium text-gray-900 mb-4">Información Fiscal</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <FormField
              v-model="form.tipo_persona"
              label="Tipo de Persona"
              type="select"
              id="tipo_persona"
              :options="selectOptions(props.catalogs.tiposPersona)"
              :error="form.errors.tipo_persona"
              @change="handleTipoPersonaChange"
              required
            />

            <FormField
              v-model="form.rfc"
              label="RFC"
              type="text"
              id="rfc"
              :maxlength="form.tipo_persona === 'fisica' ? 13 : 12"
              :error="form.errors.rfc"
              :placeholder="form.tipo_persona === 'fisica' ? 'ABCD123456EFG' : 'ABC123456EFG'"
              @input="onRfcInput"
              required
            />

            <FormField
              v-model="form.regimen_fiscal"
              label="Régimen Fiscal"
              type="select"
              id="regimen_fiscal"
              :options="selectOptions(regimenesFiltrados, true)"
              :error="form.errors.regimen_fiscal"
              required
            />

            <FormField
              v-model="form.uso_cfdi"
              label="Uso CFDI"
              type="select"
              id="uso_cfdi"
              :options="selectOptions(props.catalogs.usosCFDI)"
              :error="form.errors.uso_cfdi"
              required
            />
          </div>
        </div>

        <!-- Dirección -->
        <div>
          <h2 class="text-lg font-medium text-gray-900 mb-4">Dirección</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="md:col-span-2">
              <FormField
                v-model="form.calle"
                label="Calle"
                type="text"
                id="calle"
                :error="form.errors.calle"
                @blur="toUpper('calle')"
                required
              />
            </div>

            <FormField
              v-model="form.numero_exterior"
              label="Número Exterior"
              type="text"
              id="numero_exterior"
              :error="form.errors.numero_exterior"
              @blur="toUpper('numero_exterior')"
              required
            />

            <FormField
              v-model="form.numero_interior"
              label="Número Interior"
              type="text"
              id="numero_interior"
              :error="form.errors.numero_interior"
              @blur="toUpper('numero_interior')"
            />

            <FormField
              v-model="form.colonia"
              label="Colonia"
              type="text"
              id="colonia"
              :error="form.errors.colonia"
              @blur="toUpper('colonia')"
              required
            />

            <FormField
              v-model="form.codigo_postal"
              label="Código Postal"
              type="text"
              id="codigo_postal"
              :error="form.errors.codigo_postal"
              maxlength="5"
              required
            />

            <FormField
              v-model="form.municipio"
              label="Municipio"
              type="text"
              id="municipio"
              :error="form.errors.municipio"
              @blur="toUpper('municipio')"
              required
            />

            <FormField
              v-model="form.estado"
              label="Estado"
              type="select"
              id="estado"
              :options="selectOptions(props.catalogs.estados)"
              :error="form.errors.estado"
              required
            />

            <FormField
              v-model="form.pais"
              label="País"
              type="text"
              id="pais"
              :error="form.errors.pais"
              @blur="toUpper('pais')"
              required
            />
          </div>
        </div>

        <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200">
          <button
            type="button"
            @click="resetForm"
            :disabled="form.processing"
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none"
          >
            Limpiar
          </button>
          <button
            type="submit"
            :disabled="form.processing || !isFormValid"
            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 focus:outline-none"
          >
            <span v-if="form.processing">Guardando...</span>
            <span v-else>Crear Cliente</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { Head, useForm, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import FormField from '@/Components/FormField.vue';

defineOptions({ layout: AppLayout });

const props = defineProps({
  catalogs: { type: Object, required: true },
  cliente:  { type: Object, required: true }
});

const showSuccessMessage = ref(false);

const form = useForm({ ...props.cliente });

const hasGlobalErrors = computed(() => Object.keys(form.errors).length > 0);

const isFormValid = computed(() => {
  const requiredFields = [
    'nombre_razon_social','email','tipo_persona','rfc','regimen_fiscal','uso_cfdi',
    'calle','numero_exterior','colonia','codigo_postal','municipio','estado','pais'
  ];
  return requiredFields.every(f => form[f] !== null && form[f] !== undefined && String(form[f]).trim() !== '')
    && Object.keys(form.errors).length === 0;
});

// Helpers para selects (convierte {value,label} a {value,text} si tu FormField lo requiere)
const selectOptions = (arr, includeEmpty = false) => {
  const options = (arr || []).map(o => ({ value: o.value, text: o.label, disabled: o.disabled || false }));
  if (includeEmpty && options.length && options[0].value !== '') {
    options.unshift({ value: '', text: 'Selecciona una opción', disabled: true });
  }
  return options;
};

// Filtrar regímenes por tipo_persona usando endpoint o local
const regimenesFiltrados = computed(() => {
  // props.catalogs.regimenesFiscales ya viene completo; en el back se valida por tipo.
  return props.catalogs.regimenesFiscales;
});

const handleTipoPersonaChange = () => {
  form.rfc = '';
  form.regimen_fiscal = '';
};

const onRfcInput = (e) => {
  const value = e.target.value.toUpperCase().replace(/[^A-ZÑ&0-9]/g, '');
  form.rfc = value;
};

const toUpper = (campo) => {
  if (form[campo] && campo !== 'email') form[campo] = String(form[campo]).toUpperCase().trim();
};

const resetForm = () => {
  Object.keys(form.data()).forEach(k => form[k] = props.cliente[k] ?? '');
  form.clearErrors();
  showSuccessMessage.value = false;
};

const submit = () => {
  showSuccessMessage.value = false;
  form.post(route('clientes.store'), {
    preserveScroll: true,
    onSuccess: () => {
      showSuccessMessage.value = true;
      setTimeout(() => {
        showSuccessMessage.value = false;
        resetForm();
      }, 2000);
    }
  });
};
</script>
