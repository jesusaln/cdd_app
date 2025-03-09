<template>
    <Head title="Mantenimientos" />
    <div>
        <h1 class="text-2xl font-semibold mb-6">Mantenimientos</h1>

        <div class="mb-4">
            <Link :href="route('mantenimientos.create')" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition duration-300">
                Crear Mantenimiento
            </Link>
        </div>

        <div v-if="mantenimientos.length > 0" class="overflow-x-auto bg-white rounded-lg shadow-md">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Carro</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Próximo Mantenimiento</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Notas</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <tr v-for="mantenimiento in mantenimientos" :key="mantenimiento.id" class="hover:bg-gray-100">
                        <td class="px-4 py-3 text-sm text-gray-700">{{ mantenimiento.carro.marca }} {{ mantenimiento.carro.modelo }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ mantenimiento.tipo }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ mantenimiento.fecha }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ mantenimiento.proximo_mantenimiento }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ mantenimiento.notas }}</td>
                        <td class="px-4 py-3 flex space-x-2">
                            <Link :href="route('mantenimientos.edit', mantenimiento.id)" class="bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600 transition duration-300">
                                Editar
                            </Link>
                            <button @click="abrirModal(mantenimiento.id)" class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 transition duration-300">
                                Eliminar
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-else class="text-center text-gray-500 mt-4">
            No hay mantenimientos registrados.
        </div>

        <div v-if="mostrarModal" class="fixed inset-0 bg-gray-800 bg-opacity-50 flex justify-center items-center">
            <div class="bg-white p-6 rounded-lg shadow-lg w-96">
                <h3 class="text-xl font-semibold mb-4">¿Estás seguro de eliminar este mantenimiento?</h3>
                <p class="mb-4">Esta acción no se puede deshacer.</p>
                <div class="flex justify-end space-x-4">
                    <button @click="cerrarModal" class="bg-gray-300 text-black px-4 py-2 rounded hover:bg-gray-400">
                        Cancelar
                    </button>
                    <button @click="eliminarMantenimiento" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
                        Eliminar
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Notyf } from 'notyf';
import 'notyf/notyf.min.css';
import AppLayout from '@/Layouts/AppLayout.vue';


// Define el layout del dashboard
defineOptions({ layout: AppLayout });

defineProps({ mantenimientos: Array });

const notyf = new Notyf({
    duration: 3000,
    position: { x: 'right', y: 'top' },
    types: [
        { type: 'success', background: '#4caf50', icon: { className: 'fas fa-check-circle', tagName: 'i', color: '#fff' } },
        { type: 'error', background: '#f44336', icon: { className: 'fas fa-times-circle', tagName: 'i', color: '#fff' } },
    ],
});

const mostrarModal = ref(false);
const idMantenimientoAEliminar = ref(null);

const abrirModal = (id) => {
    idMantenimientoAEliminar.value = id;
    mostrarModal.value = true;
};

const cerrarModal = () => {
    mostrarModal.value = false;
    idMantenimientoAEliminar.value = null;
};

const eliminarMantenimiento = async () => {
    try {
        await router.delete(route('mantenimientos.destroy', idMantenimientoAEliminar.value), {
            onSuccess: () => {
                notyf.success('El mantenimiento ha sido eliminado exitosamente.');
                cerrarModal();
            },
            onError: (error) => {
                console.error('Error al eliminar el mantenimiento:', error);
                notyf.error('Hubo un error al eliminar el mantenimiento.');
                cerrarModal();
            },
        });
    } catch (error) {
        console.error('Error inesperado:', error);
        notyf.error('Ocurrió un error inesperado.');
        cerrarModal();
    }
};
</script>
