<template>
    <Head title="Panel" />
    <div class="container mx-auto px-6 py-10">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
            <!-- Tarjeta de Clientes -->
            <PanLink href="/clientes" class="group block bg-white p-6 rounded-2xl shadow-lg border border-gray-200 transition-all transform hover:scale-105 hover:shadow-xl text-center flex flex-col items-center justify-center h-full">
                <div class="flex flex-col items-center justify-center space-y-2">
                    <FontAwesomeIcon :icon="['fas', 'users']" class="h-10 w-10 text-blue-600 group-hover:text-blue-700 transition-colors" />
                    <h2 class="text-xl text-gray-900">Clientes</h2>
                    <h2 class="text-xl text-gray-900">{{ clientesCount }}</h2>
                </div>
            </PanLink>

            <!-- Tarjeta de Productos -->
            <PanLink href="/productos" class="group block bg-white p-6 rounded-2xl shadow-lg border border-gray-200 transition-all transform hover:scale-105 hover:shadow-xl text-center flex flex-col items-center justify-center h-full">
                <div class="flex flex-col items-center justify-center space-y-2">
                    <FontAwesomeIcon :icon="['fas', 'box-open']" class="h-10 w-10 text-green-600 group-hover:text-green-700 transition-colors" />
                    <h2 class="text-xl font-bold text-gray-900">Productos</h2>
                </div>
            </PanLink>

            <!-- Tarjeta de Proveedores -->
            <PanLink href="/proveedores" class="group block bg-white p-6 rounded-2xl shadow-lg border border-gray-200 transition-all transform hover:scale-105 hover:shadow-xl text-center flex flex-col items-center justify-center h-full">
                <div class="flex flex-col items-center justify-center space-y-2">
                    <FontAwesomeIcon :icon="['fas', 'truck']" class="h-10 w-10 text-purple-600 group-hover:text-purple-700 transition-colors" />
                    <h2 class="text-xl font-bold text-gray-900">Proveedores</h2>
                </div>
            </PanLink>

            <!-- Tarjeta de Citas -->
            <PanLink href="/citas" class="group block bg-white p-6 rounded-2xl shadow-lg border border-gray-200 transition-all transform hover:scale-105 hover:shadow-xl text-center flex flex-col items-center justify-center h-full">
                <div class="flex flex-col items-center justify-center space-y-2">
                    <FontAwesomeIcon :icon="['fas', 'calendar-alt']" class="h-10 w-10 text-orange-600 group-hover:text-orange-700 transition-colors" />
                    <h2 class="text-xl font-bold text-gray-900">Citas</h2>
                </div>
            </PanLink>
        </div>
    </div>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import PanLink from '@/Components/PanLink.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { library } from '@fortawesome/fontawesome-svg-core';
import { faUsers, faBoxOpen, faTruck, faCalendarAlt } from '@fortawesome/free-solid-svg-icons';

library.add(faUsers, faBoxOpen, faTruck, faCalendarAlt);

defineOptions({ layout: AppLayout });

defineProps({
    clientesCount: Number,
    citas: {
        type: Array,
        default: () => []
    }
});
</script>
