<!-- Modales.vue -->
<template>
    <!-- Modal Confirmación -->
    <Transition name="modal">
        <div
            v-if="showConfirm"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
        >
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
                <div class="text-center">
                    <div
                        class="w-12 h-12 mx-auto bg-red-100 rounded-full flex items-center justify-center mb-4"
                    >
                        <svg
                            class="w-6 h-6 text-red-600"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"
                            />
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium mb-2">
                        ¿Eliminar cotización?
                    </h3>
                    <p class="text-gray-600 mb-6">
                        Esta acción no se puede deshacer.
                    </p>
                    <div class="flex gap-3">
                        <button
                            @click="onCancel"
                            class="flex-1 px-4 py-2 bg-gray-200 rounded-lg"
                        >
                            Cancelar
                        </button>
                        <button
                            @click="onConfirm"
                            class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg"
                        >
                            Eliminar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Transition>

    <!-- Modal Detalles (simplificado) -->
    <Transition name="modal">
        <div
            v-if="showDetails"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
        >
            <div
                class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto p-6"
            >
                <h3 class="text-lg font-medium mb-4">Detalles de Cotización</h3>
                <div v-if="selected">
                    <p>
                        <strong>Cliente:</strong>
                        {{ selected.cliente?.nombre_razon_social }}
                    </p>
                    <p>
                        <strong>Total:</strong> ${{
                            formatearMoneda(selected.total)
                        }}
                    </p>
                    <p>
                        <strong>Productos:</strong>
                        {{ selected.productos.length }} items
                    </p>
                </div>
                <div class="flex justify-end mt-6">
                    <button
                        @click="onCloseDetails"
                        class="px-4 py-2 bg-gray-300 rounded-lg"
                    >
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </Transition>
</template>

<script setup>
    import { defineProps, defineEmits } from "vue";

    const props = defineProps({
        showConfirm: Boolean,
        showDetails: Boolean,
        selected: Object,
    });

    const emit = defineEmits([
        "close-confirm",
        "confirm-delete",
        "close-details",
    ]);

    const onCancel = () => emit("close-confirm");
    const onConfirm = () => emit("confirm-delete");
    const onCloseDetails = () => emit("close-details");

    const formatearMoneda = (num) =>
        new Intl.NumberFormat("es-MX", { minimumFractionDigits: 2 }).format(
            num
        );
</script>

<style scoped>
    .modal-enter-active,
    .modal-leave-active {
        transition: opacity 0.3s ease;
    }
    .modal-enter-from,
    .modal-leave-to {
        opacity: 0;
    }
</style>
