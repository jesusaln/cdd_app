<template>
    <Head title="Mostrar clientes" />
    <div>
        <h1 class="text-2xl font-semibold mb-4">Detalles del Cliente</h1>

        <!-- Modal para mostrar detalles del cliente -->
        <ClienteModal
            :cliente="cliente"
            :isOpen="isModalOpen"
            @close="closeModal"
        />

        <!-- Botón para abrir el modal -->
        <button @click="openModal" class="bg-green-500 text-white px-4 py-2 rounded-md hover:bg-green-600">
            Ver Detalles
        </button>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import ClienteModal from '@/Components/ClienteModal.vue'; // Importar el componente del modal

// Recibe el cliente como prop
const props = defineProps({
    cliente: Object, // Recibe el cliente
});

// Estado para controlar la visibilidad del modal
const isModalOpen = ref(false);

// Funciones para abrir y cerrar el modal
const openModal = () => {
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
};
</script>

<style scoped>
/* Estilos adicionales si son necesarios */
</style>
