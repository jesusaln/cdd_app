<template>
    <Head title="Crear clientes" />
    <div>
        <h1 class="text-2xl font-semibold mb-4">Crear Cliente</h1>
        <form @submit.prevent="submit">
            <div class="space-y-4">
                <!-- Selector de Persona -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="tipo_persona" class="block text-sm font-medium text-gray-700">Tipo de Persona</label>
                    <select
                        v-model="form.tipo_persona"
                        id="tipo_persona"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                        required
                    >
                        <option value="" disabled>Selecciona un tipo</option>
                        <option value="F">Persona Física</option>
                        <option value="M">Persona Moral</option>
                    </select>
                </div>



                <!-- RFC con validación dinámica -->
                <div>
                    <label for="rfc" class="block text-sm font-medium text-gray-700">RFC</label>
                    <input
                        v-model="form.rfc"
                        type="text"
                        id="rfc"
                        :maxlength="form.tipo_persona === 'F' ? 13 : 12"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                        @blur="validarRFC"
                        required
                    />
                    <p v-if="form.errors.rfc" class="text-red-500 text-sm">{{ form.errors.rfc }}</p>
                </div>
                </div>

                 <!-- Nombre/Razón Social -->
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
                </div>

               <!-- Campos organizados en grid -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
            @blur="convertirAMinusculas('email')"
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
</div>

                <!-- Dirección -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
                    </div>
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
                    <div>
                        <label for="numero_interior" class="block text-sm font-medium text-gray-700">Número Interior</label>
                        <input
                            v-model="form.numero_interior"
                            type="text"
                            id="numero_interior"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                        />
                    </div>
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
import { Head, useForm } from '@inertiajs/vue3';
import { watch } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';

defineOptions({ layout: AppLayout });

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

const form = useForm({
    tipo_persona: '',
    nombre_razon_social: '',
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

// Watcher para limpiar RFC al cambiar tipo de persona
watch(() => form.tipo_persona, () => {
    form.rfc = '';
    form.clearErrors('rfc');
});

const submit = () => {
    form.post(route('clientes.store'), {
        onSuccess: () => form.reset(),
    });
};

const convertirAMayusculas = (campo) => {
    if (form[campo]) { // Verificar que el campo exista
        form[campo] = form[campo].toUpperCase(); // Método correcto
    }
};

const convertirAMinusculas = (campo) => {
    if (form[campo]) { // Verificar que el campo exista
        form[campo] = form[campo].toLowerCase(); // Método correcto
    }
};

const validarRFC = () => {
    if (!form.tipo_persona) {
        form.setError('rfc', 'Selecciona primero el tipo de persona');
        return;
    }

    // Convertir a mayúsculas y actualizar el formulario
    const rfc = form.rfc.toUpperCase();
    form.rfc = rfc; // ⬅️ Actualiza el valor en el formulario

    const requiredLength = form.tipo_persona === 'F' ? 13 : 12;
    const regex = form.tipo_persona === 'F'
        ? /^[A-ZÑ&]{4}\d{6}[A-Z0-9]{3}$/
        : /^[A-ZÑ&]{3}\d{6}[A-Z0-9]{3}$/;

    if (rfc.length !== requiredLength) {
        form.setError('rfc', `El RFC debe tener ${requiredLength} caracteres para ${form.tipo_persona === 'F' ? 'Persona Física' : 'Persona Moral'}`);
        return;
    }

    if (!regex.test(rfc)) {
        form.setError('rfc', 'Formato de RFC inválido');
        return;
    }

    form.clearErrors('rfc');
};

const validarTelefono = () => {
    const telefonoRegex = /^\d{10}$/;
    if (!telefonoRegex.test(form.telefono)) {
        form.setError('telefono', 'El teléfono debe tener 10 dígitos');
    } else {
        form.clearErrors('telefono');
    }
};
</script>
