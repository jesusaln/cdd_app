<template>
    <div v-if="visible" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg shadow-md max-w-sm w-full">
            <h3 class="text-lg font-semibold mb-4">{{ title }}</h3>
            <p class="mb-4 text-gray-700">{{ message }}</p>
            <div class="flex justify-end space-x-4">
                <button @click="cancel" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400 transition duration-300">
                    Cancelar
                </button>
                <button @click="confirm" class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600 transition duration-300">
                    Confirmar
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';

// Estados reactivos
const visible = ref(false);
const title = ref(''); // Título del cuadro de confirmación
const message = ref(''); // Mensaje del cuadro de confirmación
const resolvePromise = ref(null);

// Método para mostrar el cuadro de confirmación
const show = (newTitle, newMessage) => {
    title.value = newTitle; // Asigna el título
    message.value = newMessage; // Asigna el mensaje
    visible.value = true; // Muestra el cuadro de diálogo
    return new Promise((resolve) => {
        resolvePromise.value = resolve;
    });
};

// Método para confirmar la acción
const confirm = () => {
    visible.value = false; // Oculta el cuadro de diálogo
    resolvePromise.value(true); // Resuelve la promesa con `true`
};

// Método para cancelar la acción
const cancel = () => {
    visible.value = false; // Oculta el cuadro de diálogo
    resolvePromise.value(false); // Resuelve la promesa con `false`
};

defineExpose({ show });
</script>

<style scoped>
/* Estilos adicionales si son necesarios */
</style>
