<template>
    <Head title="Editar proveedor" />
    <div>
        <h1 class="text-2xl font-semibold mb-4">Editar Proveedor</h1>
        <!-- Formulario de edición de proveedores -->
        <form @submit.prevent="submit">
            <div v-if="form.errors.email" class="text-red-500">{{ form.errors.email }}</div>
            <div class="space-y-4">
                <!-- Nombre/Razón Social -->
                <div>
                    <label for="nombre" class="block text-sm font-medium text-gray-700">Nombre/Razón Social</label>
                    <input
                        v-model="form.nombre"
                        type="text"
                        id="nombre"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                        @blur="convertirAMayusculas('nombre')"
                    />
                    <p v-if="form.errors.nombre" class="text-red-500 text-sm">{{ form.errors.nombre }}</p>
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
                        required
                    />
                    <p v-if="form.errors.rfc" class="text-red-500 text-sm">{{ form.errors.rfc }}</p>
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

                <!-- Dirección -->
                <div>
                    <label for="direccion" class="block text-sm font-medium text-gray-700">Dirección</label>
                    <input
                        v-model="form.direccion"
                        type="text"
                        id="direccion"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                        required
                    />
                    <p v-if="form.errors.direccion" class="text-red-500 text-sm">{{ form.errors.direccion }}</p>
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
            </div>
            <div class="mt-6">
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">
                    Actualizar Proveedor
                </button>
            </div>
        </form>
    </div>
</template>

<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';


// Define el layout del dashboard
defineOptions({ layout: AppLayout });

// Recibe el proveedor como prop
const props = defineProps({
    proveedor: Object,
});

// Inicializa el formulario con los datos del proveedor
const form = useForm({
    nombre: props.proveedor.nombre,
    rfc: props.proveedor.rfc,
    email: props.proveedor.email,
    telefono: props.proveedor.telefono,
    direccion: props.proveedor.direccion,
    codigo_postal: props.proveedor.codigo_postal,
    municipio: props.proveedor.municipio,
    estado: props.proveedor.estado,
    pais: props.proveedor.pais,
});

// Función para enviar el formulario
const submit = () => {
    form.put(route('proveedores.update', props.proveedor.id), {
        onSuccess: () => {
            alert('Proveedor actualizado correctamente.');
        },
        onError: (errors) => {
            console.error('Error al actualizar:', errors);
        },
    });
};

// Método para convertir a mayúsculas
const convertirAMayusculas = (campo) => {
    if (form[campo]) {
        form[campo] = form[campo].toUpperCase();
    }
};

// Validación del RFC
const validarRFC = () => {
    const rfcRegex = /^[A-ZÑ&]{3,4}\d{6}[A-Z0-9]{3}$/; // Expresión regular para RFC
    const minLength = 12; // Mínimo de 12 caracteres
    const maxLength = 13; // Máximo de 13 caracteres

    // Convertir el RFC a mayúsculas para validar
    const rfcValue = form.rfc.toUpperCase();

    // Verificar longitud mínima
    if (rfcValue.length < minLength) {
        form.setError('rfc', 'El RFC debe tener al menos 12 caracteres.');
        return;
    }

    // Verificar longitud máxima
    if (rfcValue.length > maxLength) {
        form.setError('rfc', 'El RFC no puede tener más de 13 caracteres.');
        return;
    }

    // Verificar formato con expresión regular
    if (!rfcRegex.test(rfcValue)) {
        form.setError('rfc', 'El RFC no es válido.');
        return;
    }

    // Si pasa todas las validaciones, limpiar el error
    form.clearErrors('rfc');
};

// Validación del teléfono
const validarTelefono = () => {
    const telefonoRegex = /^\d{10}$/;
    if (!telefonoRegex.test(form.telefono)) {
        form.setError('telefono', 'El teléfono debe tener 10 dígitos.');
    }
};
</script>
