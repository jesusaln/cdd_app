<template>
    <div>
      <h1 class="text-2xl font-semibold mb-4">Crear Proveedor</h1>
      <form @submit.prevent="submit" class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div v-if="Object.keys(form.errors).length" class="text-red-500 col-span-2">
          <p v-for="(error, field) in form.errors" :key="field">{{ field }}: {{ error }}</p>
        </div>

        <!-- Nombre/Razón Social -->
        <div class="space-y-4 col-span-2">
          <div>
            <label for="nombre_razon_social" class="block text-sm font-medium text-gray-700">Nombre/Razón Social</label>
            <input
              v-model="form.nombre_razon_social"
              type="text"
              id="nombre_razon_social"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
              @blur="convertirAMayusculas('nombre_razon_social')"
              required
            />
            <p v-if="form.errors.nombre_razon_social" class="text-red-500 text-sm">{{ form.errors.nombre_razon_social }}</p>
          </div>
        </div>

        <!-- Tipo de Persona -->
        <div>
          <label for="tipo_persona" class="block text-sm font-medium text-gray-700">Tipo de Persona</label>
          <select
            v-model="form.tipo_persona"
            id="tipo_persona"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
            @change="validarRFC"
            required
          >
            <option value="" disabled>Selecciona el tipo de persona</option>
            <option value="fisica">Persona Física</option>
            <option value="moral">Persona Moral</option>
          </select>
          <p v-if="form.errors.tipo_persona" class="text-red-500 text-sm">{{ form.errors.tipo_persona }}</p>
        </div>

        <!-- RFC -->
        <div>
          <label for="rfc" class="block text-sm font-medium text-gray-700">RFC</label>
          <input
            v-model="form.rfc"
            type="text"
            id="rfc"
            :maxlength="form.tipo_persona === 'fisica' ? 13 : 12"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
            @input="validarRFC"
            required
          />
          <p v-if="form.errors.rfc" class="text-red-500 text-sm">{{ form.errors.rfc }}</p>
        </div>

        <!-- Régimen Fiscal -->
        <div>
          <label for="regimen_fiscal" class="block text-sm font-medium text-gray-700">Régimen Fiscal</label>
          <select
            v-model="form.regimen_fiscal"
            id="regimen_fiscal"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
            required
          >
            <option value="" disabled>Selecciona un régimen fiscal</option>
            <option v-for="regimen in regimenesFiscales" :key="regimen" :value="regimen">{{ regimen }}</option>
          </select>
          <p v-if="form.errors.regimen_fiscal" class="text-red-500 text-sm">{{ form.errors.regimen_fiscal }}</p>
        </div>

        <!-- Uso CFDI -->
        <div>
          <label for="uso_cfdi" class="block text-sm font-medium text-gray-700">Uso CFDI</label>
          <select
            v-model="form.uso_cfdi"
            id="uso_cfdi"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
            required
          >
            <option value="" disabled>Selecciona un uso CFDI</option>
            <option v-for="uso in usosCFDI" :key="uso" :value="uso">{{ uso }}</option>
          </select>
          <p v-if="form.errors.uso_cfdi" class="text-red-500 text-sm">{{ form.errors.uso_cfdi }}</p>
        </div>

        <!-- Email -->
        <div>
          <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
          <input
            v-model="form.email"
            type="email"
            id="email"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
            required
          />
          <p v-if="form.errors.email" class="text-red-500 text-sm">{{ form.errors.email }}</p>
        </div>

        <!-- Teléfono -->
        <div>
          <label for="telefono" class="block text-sm font-medium text-gray-700">Teléfono</label>
          <input
            v-model="form.telefono"
            type="text"
            id="telefono"
            maxlength="10"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
            @input="validarTelefono"
            required
          />
          <p v-if="form.errors.telefono" class="text-red-500 text-sm">{{ form.errors.telefono }}</p>
        </div>

        <!-- Calle -->
        <div>
          <label for="calle" class="block text-sm font-medium text-gray-700">Calle</label>
          <input
            v-model="form.calle"
            type="text"
            id="calle"
            maxlength="40"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
            @blur="convertirAMayusculas('calle')"
            required
          />
          <p v-if="form.errors.calle" class="text-red-500 text-sm">{{ form.errors.calle }}</p>
        </div>

        <!-- Número Exterior -->
        <div>
          <label for="numero_exterior" class="block text-sm font-medium text-gray-700">Número Exterior</label>
          <input
            v-model="form.numero_exterior"
            type="text"
            id="numero_exterior"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
            required
          />
        </div>

        <!-- Número Interior -->
        <div>
          <label for="numero_interior" class="block text-sm font-medium text-gray-700">Número Interior</label>
          <input
            v-model="form.numero_interior"
            type="text"
            id="numero_interior"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
          />
        </div>

        <!-- Colonia -->
        <div>
          <label for="colonia" class="block text-sm font-medium text-gray-700">Colonia</label>
          <input
            v-model="form.colonia"
            type="text"
            id="colonia"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
            @blur="convertirAMayusculas('colonia')"
            required
          />
        </div>

        <!-- Código Postal -->
        <div>
          <label for="codigo_postal" class="block text-sm font-medium text-gray-700">Código Postal</label>
          <input
            v-model="form.codigo_postal"
            type="text"
            id="codigo_postal"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
            required
          />
        </div>

        <!-- Municipio -->
        <div>
          <label for="municipio" class="block text-sm font-medium text-gray-700">Municipio</label>
          <input
            v-model="form.municipio"
            type="text"
            id="municipio"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
            readonly
          />
        </div>

        <!-- Estado -->
        <div>
          <label for="estado" class="block text-sm font-medium text-gray-700">Estado</label>
          <input
            v-model="form.estado"
            type="text"
            id="estado"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
            readonly
          />
        </div>

        <!-- País -->
        <div>
          <label for="pais" class="block text-sm font-medium text-gray-700">País</label>
          <input
            v-model="form.pais"
            type="text"
            id="pais"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
            readonly
          />
        </div>

        <div class="mt-6 col-span-2">
          <button
            type="submit"
            class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600"
            :disabled="form.processing"
          >
            Guardar Proveedor
          </button>
        </div>
      </form>
    </div>
  </template>

  <script setup>
  import { useForm } from '@inertiajs/vue3';
  import AppLayout from '@/Layouts/AppLayout.vue';

defineOptions({ layout: AppLayout });

  // Listas predefinidas
  const regimenesFiscales = [
    'Persona Física con Actividad Empresarial',
    'Régimen General de Ley Personas Morales',
    'Régimen Simplificado de Confianza'
  ];
  const usosCFDI = [
    'Gastos en general',
    'Adquisición de mercancías',
    'Honorarios médicos, dentales y gastos hospitalarios'
  ];

  // Formulario para crear un proveedor
  const form = useForm({
    nombre_razon_social: '',
    tipo_persona: '',
    rfc: '',
    regimen_fiscal: '',
    uso_cfdi: '',
    email: '',
    telefono: '',
    calle: '',
    numero_exterior: '',
    numero_interior: '',
    colonia: '',
    codigo_postal: '83000',
    municipio: 'HERMOSILLO',
    estado: 'SONORA',
    pais: 'MEXICO'
  });

  // Función para enviar el formulario
  const submit = () => {
    form.post(route('proveedores.store'), {
      preserveScroll: true,
      preserveState: true,
      onSuccess: () => form.reset(),
      onError: (errors) => console.error('Error al crear:', errors),
    });
  };

  // Convertir a mayúsculas
  const convertirAMayusculas = (campo) => {
    if (form[campo]) {
      form[campo] = form[campo].toUpperCase();
    }
  };

  // Validación del RFC
  const validarRFC = () => {
    const rfcRegexFisica = /^[A-ZÑ&]{4}\d{6}[A-Z0-9]{3}$/;
    const rfcRegexMoral = /^[A-ZÑ&]{3}\d{6}[A-Z0-9]{3}$/;

    // Convertir el valor del RFC a mayúsculas
    form.rfc = form.rfc.toUpperCase();

    if (form.tipo_persona === 'fisica') {
      if (form.rfc.length !== 13 || !rfcRegexFisica.test(form.rfc)) {
        form.setError('rfc', 'El RFC debe tener 13 caracteres y ser válido para una persona física.');
        return;
      }
    } else if (form.tipo_persona === 'moral') {
      if (form.rfc.length !== 12 || !rfcRegexMoral.test(form.rfc)) {
        form.setError('rfc', 'El RFC debe tener 12 caracteres y ser válido para una persona moral.');
        return;
      }
    }

    form.clearErrors('rfc');
  };

  // Validación del teléfono
  const validarTelefono = () => {
    const telefonoRegex = /^\d{10}$/;
    if (form.telefono && !telefonoRegex.test(form.telefono)) {
      form.setError('telefono', 'El teléfono debe tener 10 dígitos.');
    } else {
      form.clearErrors('telefono');
    }
  };
  </script>
