<template>
    <Head title="Crear clientes" />
    <div>
        <h1 class="text-2xl font-semibold mb-4">Crear Cliente</h1>
        <!-- Formulario de creación de clientes -->
        <form @submit.prevent="submit" class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div v-if="form.errors.email" class="text-red-500 col-span-2">{{ form.errors.email }}</div>
            <div class="space-y-4 col-span-2">
                <!-- Nombre/Razón Social -->
                <FormField
                    v-model="form.nombre_razon_social"
                    label="Nombre/Razón Social"
                    type="text"
                    id="nombre_razon_social"
                    :error="form.errors.nombre_razon_social"
                    @blur="convertirAMayusculas('nombre_razon_social')"
                />
            </div>

            <!-- Tipo de Persona -->
            <FormField
                v-model="form.tipo_persona"
                label="Tipo de Persona"
                type="select"
                id="tipo_persona"
                :options="[{value: '', text: 'Selecciona el tipo de persona', disabled: true}, {value: 'fisica', text: 'Persona Física'}, {value: 'moral', text: 'Persona Moral'}]"
                :error="form.errors.tipo_persona"
                @change="validarRFC"
                required
            />

            <!-- RFC -->
            <FormField
                v-model="form.rfc"
                label="RFC"
                type="text"
                id="rfc"
                :maxlength="form.tipo_persona === 'fisica' ? 13 : 12"
                :error="form.errors.rfc"
                @input="validarRFC"
                @blur="convertirAMayusculas('rfc')"
                required
            />

            <!-- Régimen Fiscal -->
            <FormField
                v-model="form.regimen_fiscal"
                label="Régimen Fiscal"
                type="select"
                id="regimen_fiscal"
                :options="[{value: '', text: 'Selecciona un régimen fiscal', disabled: true}, ...regimenesFiscales.map(regimen => ({value: regimen, text: regimen}))]"
                :error="form.errors.regimen_fiscal"
                required
            />

            <!-- Uso CFDI -->
            <FormField
                v-model="form.uso_cfdi"
                label="Uso CFDI"
                type="select"
                id="uso_cfdi"
                :options="[{value: '', text: 'Selecciona un uso CFDI', disabled: true}, ...usosCFDI.map(uso => ({value: uso, text: uso}))]"
                :error="form.errors.uso_cfdi"
                required
            />

            <!-- Email -->
            <FormField
                v-model="form.email"
                label="Email"
                type="email"
                id="email"
                :error="form.errors.email"
                required
            />

            <!-- Teléfono -->
            <FormField
                v-model="form.telefono"
                label="Teléfono"
                type="text"
                id="telefono"
                maxlength="10"
                :error="form.errors.telefono"
                @input="validarTelefono"
                @blur="convertirAMayusculas('telefono')"
                required
            />

            <!-- Calle -->
            <FormField
                v-model="form.calle"
                label="Calle"
                type="text"
                id="calle"
                maxlength="40"
                :error="form.errors.calle"
                @blur="convertirAMayusculas('calle')"
                required
            />

            <!-- Número Exterior -->
            <FormField
                v-model="form.numero_exterior"
                label="Número Exterior"
                type="text"
                id="numero_exterior"
                :error="form.errors.numero_exterior"
                @blur="convertirAMayusculas('numero_exterior')"
                required
            />

            <!-- Número Interior -->
            <FormField
                v-model="form.numero_interior"
                label="Número Interior"
                type="text"
                id="numero_interior"
                :error="form.errors.numero_interior"
                @blur="convertirAMayusculas('numero_interior')"
            />

            <!-- Colonia -->
            <FormField
                v-model="form.colonia"
                label="Colonia"
                type="text"
                id="colonia"
                :error="form.errors.colonia"
                @blur="convertirAMayusculas('colonia')"
                required
            />

            <!-- Código Postal -->
            <FormField
                v-model="form.codigo_postal"
                label="Código Postal"
                type="text"
                id="codigo_postal"
                :error="form.errors.codigo_postal"
                @blur="convertirAMayusculas('codigo_postal')"
                required
            />

            <!-- Municipio -->
            <FormField
                v-model="form.municipio"
                label="Municipio"
                type="text"
                id="municipio"
                readonly
                @blur="convertirAMayusculas('municipio')"
            />

            <!-- Estado -->
            <FormField
                v-model="form.estado"
                label="Estado"
                type="text"
                id="estado"
                readonly
                @blur="convertirAMayusculas('estado')"
            />

            <!-- País -->
            <FormField
                v-model="form.pais"
                label="País"
                type="text"
                id="pais"
                readonly
                @blur="convertirAMayusculas('pais')"
            />

            <div class="mt-6 col-span-2">
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">
                    Guardar Cliente
                </button>
            </div>
        </form>
    </div>
</template>

<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import FormField from '@/Components/FormField.vue';

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

// Formulario para crear un cliente
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
    form.post(route('clientes.store'), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => form.reset(),
        onError: (errors) => console.error('Error al crear:', errors),
    });
};

// Función para convertir a mayúsculas
const convertirAMayusculas = (campo) => {
    if (form[campo] && campo !== 'email') {
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
    if (!telefonoRegex.test(form.telefono)) {
        form.setError('telefono', 'El teléfono debe tener 10 dígitos.');
    } else {
        form.clearErrors('telefono');
    }
};
</script>
