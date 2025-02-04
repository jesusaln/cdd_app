<template>
    <div>
        <!-- Título de la página -->
        <h1 class="text-2xl font-semibold mb-12">Registro de Clientes</h1>

        <!-- Botón para crear un nuevo cliente -->
        <div class="mb-6">
            <Link :href="route('clientes.create')" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">
                Crear Cliente
            </Link>
        </div>

        <!-- Tabla de clientes -->
        <div class="overflow-x-auto bg-white rounded-lg shadow">
            <table class="min-w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Nombre/Razón Social
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            RFC
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Régimen Fiscal
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Uso CFDI
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Email
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Teléfono
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Dirección
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <tr v-for="cliente in clientes" :key="cliente.id" class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            {{ cliente.nombre_razon_social }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            {{ cliente.rfc }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            {{ cliente.regimen_fiscal }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            {{ cliente.uso_cfdi }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            {{ cliente.email }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            {{ cliente.telefono }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            {{ cliente.calle }} {{ cliente.numero_exterior }} {{ cliente.numero_interior }},
                            {{ cliente.colonia }}, {{ cliente.codigo_postal }},
                            {{ cliente.municipio }}, {{ cliente.estado }}, {{ cliente.pais }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import Dashboard from '@/Pages/Dashboard.vue'; // Importa el layout del dashboard

// Define el layout del dashboard
defineOptions({ layout: Dashboard });

// Recibe los clientes como prop
defineProps({
    clientes: Array,
});
</script>
