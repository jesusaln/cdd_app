<template>
    <AppLayout title="Cuentas por Cobrar">
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Cuentas por Cobrar
                </h2>
                <Link
                    :href="route('cuentas-por-cobrar.create')"
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                >
                    Nueva Cuenta por Cobrar
                </Link>
            </div>
        </template>

        <!-- EstadÃƒÂ­sticas -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-red-500 rounded-full flex items-center justify-center">
                                <span class="text-white text-sm font-bold">!</span>
                            </div>
                        </div>
                        <div class="ml-4">
                            <dt class="text-sm font-medium text-gray-500 truncate">
                                Total Vencido
                            </dt>
                            <dd class="text-lg font-semibold text-gray-900">
                                {{ formatCurrency(stats.total_vencido) }}
                            </dd>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-yellow-500 rounded-full flex items-center justify-center">
                                <span class="text-white text-sm font-bold">P</span>
                            </div>
                        </div>
                        <div class="ml-4">
                            <dt class="text-sm font-medium text-gray-500 truncate">
                                Total Pendiente
                            </dt>
                            <dd class="text-lg font-semibold text-gray-900">
                                {{ formatCurrency(stats.total_pendiente) }}
                            </dd>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                                <span class="text-white text-sm font-bold">{{ stats.cuentas_pendientes }}</span>
                            </div>
                        </div>
                        <div class="ml-4">
                            <dt class="text-sm font-medium text-gray-500 truncate">
                                Cuentas Pendientes
                            </dt>
                            <dd class="text-lg font-semibold text-gray-900">
                                {{ stats.cuentas_pendientes }}
                            </dd>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-red-500 rounded-full flex items-center justify-center">
                                <span class="text-white text-sm font-bold">{{ stats.cuentas_vencidas }}</span>
                            </div>
                        </div>
                        <div class="ml-4">
                            <dt class="text-sm font-medium text-gray-500 truncate">
                                Cuentas Vencidas
                            </dt>
                            <dd class="text-lg font-semibold text-gray-900">
                                {{ stats.cuentas_vencidas }}
                            </dd>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="p-6">
                <form @submit.prevent="applyFilters" class="flex flex-wrap gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Estado</label>
                        <select v-model="filters.estado" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            <option value="">Todos</option>
                            <option value="pendiente">Pendiente</option>
                            <option value="parcial">Parcial</option>
                            <option value="pagado">Pagado</option>
                            <option value="vencido">Vencido</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Cliente</label>
                        <select v-model="filters.cliente_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            <option value="">Todos</option>
                            <option v-for="cliente in clientes" :key="cliente.id" :value="cliente.id">
                                {{ cliente.nombre_razon_social }}
                            </option>
                        </select>
                    </div>

                    <div class="flex items-end">
                        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Filtrar
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabla de cuentas -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Venta
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Cliente
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Monto Total
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Pendiente
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Vencimiento
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Estado
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="cuenta in cuentas.data" :key="cuenta.id">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ cuenta.venta.numero_venta }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ cuenta.venta.cliente.nombre_razon_social }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ formatCurrency(cuenta.monto_total) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ formatCurrency(cuenta.monto_pendiente) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ cuenta.fecha_vencimiento ? new Date(cuenta.fecha_vencimiento).toLocaleDateString() : 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span :class="{
                                        'bg-red-100 text-red-800': cuenta.estado === 'vencido',
                                        'bg-yellow-100 text-yellow-800': cuenta.estado === 'parcial',
                                        'bg-green-100 text-green-800': cuenta.estado === 'pagado',
                                        'bg-gray-100 text-gray-800': cuenta.estado === 'pendiente'
                                    }" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                                        {{ cuenta.estado }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <Link :href="route('cuentas-por-cobrar.show', cuenta.id)" class="text-indigo-600 hover:text-indigo-900 mr-3">
                                        Ver
                                    </Link>
                                    <Link :href="route('cuentas-por-cobrar.edit', cuenta.id)" class="text-indigo-600 hover:text-indigo-900">
                                        Editar
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- PaginaciÃƒÂ³n -->
                <div class="mt-4">
                    <div class="flex justify-between items-center">
                        <div class="text-sm text-gray-700">
                            Mostrando {{ cuentas.from }} a {{ cuentas.to }} de {{ cuentas.total }} resultados
                        </div>
                        <div class="flex space-x-1">
                            <Link
                                v-for="link in cuentas.links"
                                :key="link.label"
                                :href="link.url || '#'"
                                v-html="link.label"
                                :class="[
                                    'px-3 py-2 text-sm border rounded',
                                    link.active ? 'bg-blue-500 text-white border-blue-500' : (link.url ? 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50' : 'bg-gray-100 text-gray-400 border-gray-200 cursor-not-allowed')
                                ]"
                                :preserve-scroll="link.url ? true : false"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    cuentas: Object,
    stats: Object,
    filters: Object,
});

const clientes = ref([]);

const filters = ref({
    estado: props.filters.estado || '',
    cliente_id: props.filters.cliente_id || '',
});

const currencyFormatter = new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' });

const toNumber = (value) => {
    if (value === null || value === undefined) {
        return 0;
    }

    const number = Number(value);
    return Number.isFinite(number) ? number : 0;
};

const formatCurrency = (value) => currencyFormatter.format(toNumber(value));

const applyFilters = () => {
    // Implementar filtrado
    window.location.href = route('cuentas-por-cobrar.index', filters.value);
};

// onMounted(() => {
//     // Cargar clientes para el filtro - temporalmente deshabilitado
//     // fetch(route('clientes.index', { per_page: 100 }))
//     //     .then(response => response.json())
//     //     .then(data => {
//     //         clientes.value = data.data || [];
//     //     })
//     //     .catch(error => {
//     //         console.error('Error loading clientes:', error);
//     //     });
// });
</script>

