<template>
    <div>
        <h1 class="text-2xl font-semibold mb-4">Crear Cliente</h1>
        <!-- Formulario de creación de clientes -->
        <form @submit.prevent="submit">
            <div v-if="errorMessage" class="text-red-500 mb-4">{{ errorMessage }}</div>
            <div class="space-y-4">
                <!-- Nombre/Razón Social -->
                <div>
                    <label for="nombre_razon_social" class="block text-sm font-medium text-gray-700">Nombre/Razón Social</label>
                    <input
                        v-model="form.nombre_razon_social"
                        type="text"
                        id="nombre_razon_social"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                        @blur="convertirAMayusculas('nombre_razon_social')"
                    />
                </div>

                <!-- RFC -->
                <div>
                    <label for="rfc" class="block text-sm font-medium text-gray-700">RFC</label>
                    <input
                        v-model="form.rfc"
                        type="text"
                        id="rfc"
                        maxlength="13"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                        @blur="validarRFC"
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
                    >
                        <option value="" disabled>Selecciona un régimen fiscal</option>
                        <option v-for="regimen in regimenesFiscales" :key="regimen" :value="regimen">{{ regimen }}</option>
                    </select>
                </div>

                <!-- Uso CFDI -->
                <div>
                    <label for="uso_cfdi" class="block text-sm font-medium text-gray-700">Uso CFDI</label>
                    <select
                        v-model="form.uso_cfdi"
                        id="uso_cfdi"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                    >
                        <option value="" disabled>Selecciona un uso CFDI</option>
                        <option v-for="uso in usosCFDI" :key="uso" :value="uso">{{ uso }}</option>
                    </select>
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input
                        v-model="form.email"
                        type="email"
                        id="email"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                    />
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
                    />
                </div>

                <!-- Número Exterior -->
                <div>
                    <label for="numero_exterior" class="block text-sm font-medium text-gray-700">Número Exterior</label>
                    <input
                        v-model="form.numero_exterior"
                        type="text"
                        id="numero_exterior"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
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
                    />
                </div>

                <!-- Código Postal -->
                <div>
                    <label for="codigo_postal" class="block text-sm font-medium text-gray-700">Código Postal</label>
                    <input
                        v-model="form.codigo_postal"
                        type="text"
                        id="codigo_postal"
                        maxlength="5"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
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

                <div class="mt-6">
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">
                        Guardar Cliente
                    </button>
                </div>
            </div>
        </form>
    </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import Dashboard from '@/Pages/Dashboard.vue';

defineOptions({ layout: Dashboard });

const fields = {
    nombre_razon_social: 'Nombre/Razón Social',
    rfc: 'RFC',
    regimen_fiscal: 'Régimen Fiscal',
    uso_cfdi: 'Uso CFDI',
    email: 'Email',
    telefono: 'Teléfono',
    calle: 'Calle',
    numero_exterior: 'Número Exterior',
    numero_interior: 'Número Interior',
    colonia: 'Colonia',
    codigo_postal: 'Código Postal',
    municipio: 'Municipio',
    estado: 'Estado',
    pais: 'País'
};

const inputTypes = { email: 'email', telefono: 'text', rfc: 'text' };
const maxLengths = { rfc: 13, telefono: 10, codigo_postal: 5 };
const readonlyFields = ['municipio', 'estado', 'pais'];

const regimenesFiscales = ['Persona Física con Actividad Empresarial', 'Régimen General de Ley Personas Morales', 'Régimen Simplificado de Confianza'];
const usosCFDI = ['Gastos en general', 'Adquisición de mercancías', 'Honorarios médicos, dentales y gastos hospitalarios'];

const form = useForm({
    nombre_razon_social: '', rfc: '', regimen_fiscal: '', uso_cfdi: '', email: '', telefono: '',
    calle: '', numero_exterior: '', numero_interior: '', colonia: '', codigo_postal: '',
    municipio: 'Hermosillo', estado: 'Sonora', pais: 'México'
});

const errorMessage = ref('');

const submit = async () => {
    if (await checkEmailUniqueness(form.email)) {
        errorMessage.value = 'El correo electrónico ya está registrado.';
        return;
    }
    if (!form.regimen_fiscal || !form.uso_cfdi) {
        errorMessage.value = 'Todos los campos obligatorios deben estar completos.';
        return;
    }
    form.post(route('clientes.store'), { onSuccess: () => { form.reset(); errorMessage.value = ''; }});
};

const onBlur = (field) => {
    if (['nombre_razon_social', 'calle', 'colonia'].includes(field)) {
        form[field] = form[field].toUpperCase();
    }
    if (field === 'rfc') validarRFC();
    if (field === 'telefono') validarTelefono();
};

const validarRFC = () => {
    const rfcRegex = /^[A-ZÑ&]{3,4}\d{6}[A-Z0-9]{3}$/;
    form.setError('rfc', rfcRegex.test(form.rfc.toUpperCase()) ? '' : 'RFC inválido. Debe tener 13 caracteres.');
};

const validarTelefono = () => {
    const telefonoRegex = /^\d{10}$/;
    form.setError('telefono', telefonoRegex.test(form.telefono) ? '' : 'El teléfono debe tener 10 dígitos.');
};

const checkEmailUniqueness = async (email) => {
    try {
        const response = await fetch(`/api/check-email?email=${encodeURIComponent(email)}`);
        const data = await response.json();
        return data.exists;
    } catch (error) {
        console.error('Error al verificar el correo:', error);
        return false;
    }
};
</script>
