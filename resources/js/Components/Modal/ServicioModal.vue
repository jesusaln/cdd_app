<template>
    <div v-if="isOpen" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg w-11/12 md:w-1/2 lg:w-2/3 p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold">Detalles del Servicio</h2>
                <button @click="closeModal" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <!-- Usamos una grid para mostrar los detalles en dos columnas -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Nombre</label>
                    <p class="mt-1 block w-full rounded-md bg-gray-100 p-2">{{ servicio.nombre }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Descripción</label>
                    <p class="mt-1 block w-full rounded-md bg-gray-100 p-2">{{ servicio.descripcion }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Código</label>
                    <p class="mt-1 block w-full rounded-md bg-gray-100 p-2">{{ servicio.codigo }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Categoría</label>
                    <p class="mt-1 block w-full rounded-md bg-gray-100 p-2">{{ servicio.categoria_id || 'No disponible' }}</p>

                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Precio</label>
                    <p class="mt-1 block w-full rounded-md bg-gray-100 p-2">{{ servicio.precio }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Duración (minutos)</label>
                    <p class="mt-1 block w-full rounded-md bg-gray-100 p-2">{{ servicio.duracion }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Estado</label>
                    <p class="mt-1 block w-full rounded-md bg-gray-100 p-2">{{ servicio.estado }}</p>
                </div>
            </div>
            <div class="mt-6 flex justify-end">
                <button @click="closeModal" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { defineProps, defineEmits, onMounted } from 'vue';

const props = defineProps({
    servicio: Object,
    isOpen: Boolean
});

const emit = defineEmits(['close']);

const closeModal = () => {
    emit('close');
};

// Depuración: Verifica la estructura del objeto servicio
onMounted(() => {
    console.log("Servicio recibido:", props.servicio);
});
</script>

<style scoped>
.fixed {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
}

.bg-opacity-50 {
    background-color: rgba(0, 0, 0, 0.5);
}

.z-50 {
    z-index: 50;
}

.rounded-lg {
    border-radius: 0.5rem;
}

.shadow-lg {
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

.grid-cols-1 {
    grid-template-columns: repeat(1, minmax(0, 1fr));
}

.sm\:grid-cols-2 {
    grid-template-columns: repeat(2, minmax(0, 1fr));
}

.gap-4 {
    gap: 1rem;
}

.text-gray-500 {
    color: #6b7280;
}

.hover\:text-gray-700:hover {
    color: #374151;
}

.bg-gray-100 {
    background-color: #f3f4f6;
}

.rounded-md {
    border-radius: 0.375rem;
}

.p-2 {
    padding: 0.5rem;
}

.w-full {
    width: 100%;
}

.h-auto {
    height: auto;
}

.mt-1 {
    margin-top: 0.25rem;
}

.mb-4 {
    margin-bottom: 1rem;
}

.font-semibold {
    font-weight: 600;
}
</style>
