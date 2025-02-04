<template>
    <div>
        <h1 class="text-2xl font-semibold mb-4">Crear Cliente</h1>

        <!-- Formulario de creación de clientes -->
        <form @submit.prevent="submit">
            <div v-if="form.errors.email" class="text-red-500">{{ form.errors.email }}</div>
            <div class="space-y-4">
                <div>
                    <label for="nombre_razon_social" class="block text-sm font-medium text-gray-700">Nombre/Razón Social</label>
                    <input v-model="form.nombre_razon_social" type="text" id="nombre_razon_social" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" />
                </div>
                <div>
                    <label for="rfc" class="block text-sm font-medium text-gray-700">RFC</label>
                    <input v-model="form.rfc" type="text" id="rfc" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" />
                </div>
                <div>
                    <label for="regimen_fiscal" class="block text-sm font-medium text-gray-700">Régimen Fiscal</label>
                    <input v-model="form.regimen_fiscal" type="text" id="regimen_fiscal" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" />
                </div>
                <div>
                    <label for="uso_cfdi" class="block text-sm font-medium text-gray-700">Uso CFDI</label>
                    <input v-model="form.uso_cfdi" type="text" id="uso_cfdi" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" />
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input v-model="form.email" type="email" id="email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" />
                </div>
                <div>
                    <label for="telefono" class="block text-sm font-medium text-gray-700">Teléfono</label>
                    <input v-model="form.telefono" type="text" id="telefono" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" />
                </div>
                <div>
                    <label for="calle" class="block text-sm font-medium text-gray-700">Calle</label>
                    <input v-model="form.calle" type="text" id="calle" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" />
                </div>
                <div>
                    <label for="numero_exterior" class="block text-sm font-medium text-gray-700">Número Exterior</label>
                    <input v-model="form.numero_exterior" type="text" id="numero_exterior" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" />
                </div>
                <div>
                    <label for="numero_interior" class="block text-sm font-medium text-gray-700">Número Interior</label>
                    <input v-model="form.numero_interior" type="text" id="numero_interior" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" />
                </div>
                <div>
                    <label for="colonia" class="block text-sm font-medium text-gray-700">Colonia</label>
                    <input v-model="form.colonia" type="text" id="colonia" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" />
                </div>
                <div>
                    <label for="codigo_postal" class="block text-sm font-medium text-gray-700">Código Postal</label>
                    <input v-model="form.codigo_postal" type="text" id="codigo_postal" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" />
                </div>
                <div>
                    <label for="municipio" class="block text-sm font-medium text-gray-700">Municipio</label>
                    <input v-model="form.municipio" type="text" id="municipio" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" />
                </div>
                <div>
                    <label for="estado" class="block text-sm font-medium text-gray-700">Estado</label>
                    <input v-model="form.estado" type="text" id="estado" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" />
                </div>
                <div>
                    <label for="pais" class="block text-sm font-medium text-gray-700">País</label>
                    <input v-model="form.pais" type="text" id="pais" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" />
                </div>
            </div>

            <div class="mt-6">
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">
                    Guardar Cliente
                </button>
            </div>
        </form>
    </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import Dashboard from '@/Pages/Dashboard.vue'; // Importa el layout del dashboard

// Define el layout del dashboard
defineOptions({ layout: Dashboard });

// Formulario para crear un cliente
const form = useForm({
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
    codigo_postal: '',
    municipio: '',
    estado: '',
    pais: '',
});

// Función para enviar el formulario
const submit = () => {
    form.post(route('clientes.store'), {
        onSuccess: () => {
            form.reset(); // Limpia el formulario después de guardar
        },
    });
};
</script>
