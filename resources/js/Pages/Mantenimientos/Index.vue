<template>
    <Head title="Mantenimientos" />
    <div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100">
        <div class="relative overflow-hidden bg-white/80 backdrop-blur-md border-b border-gray-200/50 shadow-lg">
            <div class="absolute inset-0 bg-gradient-to-r from-blue-600/10 to-indigo-600/10"></div>
            <div class="relative max-w-7xl mx-auto px-4 py-8">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="p-3 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl shadow-lg">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold bg-gradient-to-r from-gray-900 to-gray-600 bg-clip-text text-transparent">
                                {{ titulo }}
                            </h1>
                            <p class="text-gray-600 mt-1">Gestiona los mantenimientos de tu flota vehicular</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2 text-sm text-gray-500">
                        <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full font-medium">
                            {{ mantenimientos.length }} en total
                        </span>
                        <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full font-medium">
                            {{ mantenimientosFiltrados.length }} visibles
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 py-8">
            <div class="mb-8">
                <div class="flex flex-col lg:flex-row gap-4 items-start lg:items-center justify-between">
                    <div class="relative flex-1 max-w-md">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input
                            v-model="searchTerm"
                            type="text"
                            placeholder="Buscar por vehículo, tipo, fecha..."
                            class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white/80 backdrop-blur-sm shadow-sm transition-all duration-200 hover:shadow-md"
                        />
                        <div v-if="searchTerm" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                            <button @click="searchTerm = ''" class="text-gray-400 hover:text-gray-600 transition-colors">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center space-x-3">
                        <button @click="exportData" class="flex items-center space-x-2 px-4 py-2 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition-all duration-200 hover:scale-105">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span>Exportar</span>
                        </button>
                        <Link :href="route('mantenimientos.create')" class="flex items-center space-x-2 px-6 py-3 bg-gradient-to-r from-blue-500 to-indigo-600 text-white rounded-xl hover:from-blue-600 hover:to-indigo-700 transition-all duration-200 hover:scale-105 shadow-lg hover:shadow-xl">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            <span class="font-medium">Crear Mantenimiento</span>
                        </Link>
                    </div>
                </div>
            </div>

            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl overflow-hidden border border-gray-200/50">
                <div v-if="mantenimientosFiltrados.length > 0" class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gradient-to-r from-gray-50 to-blue-50">
                            <tr>
                                <th v-for="header in headers" :key="header" class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    {{ header }}
                                </th>
                                <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="(mantenimiento, index) in mantenimientosFiltrados" :key="mantenimiento.id"
                                class="hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 transition-all duration-200 group"
                                :style="{ animationDelay: `${index * 50}ms` }"
                                :class="'animate-fade-in-up'">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 bg-gradient-to-br from-gray-400 to-gray-600 rounded-full flex items-center justify-center text-white shadow-lg">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17l-5-5 5-5m6 10l5-5-5-5"></path></svg>
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ mantenimiento.carro.marca }} {{ mantenimiento.carro.modelo }}
                                            </div>
                                            <div class="text-xs text-gray-500">{{ mantenimiento.carro.placa }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ mantenimiento.tipo }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ formatDate(mantenimiento.fecha) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ formatDate(mantenimiento.proximo_mantenimiento) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span :class="getStatusClasses(mantenimiento.proximo_mantenimiento)">
                                        {{ getStatus(mantenimiento.proximo_mantenimiento) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="flex items-center justify-center space-x-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                        <Link :href="route('mantenimientos.edit', mantenimiento.id)" class="p-2 text-yellow-600 hover:text-yellow-800 hover:bg-yellow-50 rounded-lg transition-all duration-200 hover:scale-110" title="Editar">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </Link>
                                        <button @click="confirmarEliminacion(mantenimiento)" class="p-2 text-red-600 hover:text-red-800 hover:bg-red-50 rounded-lg transition-all duration-200 hover:scale-110" title="Eliminar">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-else class="text-center py-16">
                    <div class="w-24 h-24 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
                        <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No hay mantenimientos registrados</h3>
                    <p class="text-gray-500 mb-6">Comienza agregando tu primer mantenimiento al sistema</p>
                    <Link :href="route('mantenimientos.create')" class="inline-flex items-center space-x-2 px-6 py-3 bg-gradient-to-r from-blue-500 to-indigo-600 text-white rounded-xl hover:from-blue-600 hover:to-indigo-700 transition-all duration-200 hover:scale-105 shadow-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        <span>Crear Primer Mantenimiento</span>
                    </Link>
                </div>
            </div>
        </div>

        <div v-if="loading" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50">
            <div class="bg-white/90 backdrop-blur-md rounded-2xl p-8 shadow-2xl">
                <div class="flex items-center space-x-4">
                    <div class="w-8 h-8 border-4 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
                    <span class="text-lg font-medium text-gray-700">Procesando...</span>
                </div>
            </div>
        </div>

        <div v-if="isConfirmOpen" class="fixed inset-0 flex items-center justify-center bg-black/50 backdrop-blur-sm z-50">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden animate-scale-in">
                <div class="bg-gradient-to-r from-red-500 to-pink-600 px-6 py-4">
                    <h2 class="text-xl font-bold text-white flex items-center space-x-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.994-.833-2.764 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                        <span>Confirmar Eliminación</span>
                    </h2>
                </div>
                <div class="p-6">
                    <p class="text-gray-700 mb-4">
                        ¿Estás seguro de que deseas eliminar el mantenimiento para
                        <strong class="text-red-600">{{ mantenimientoAEliminar?.carro.marca }} {{ mantenimientoAEliminar?.carro.modelo }}</strong>
                        del día
                        <strong class="text-red-600">{{ formatDate(mantenimientoAEliminar?.fecha) }}</strong>?
                    </p>
                    <p class="text-sm text-gray-500 mb-6">Esta acción no se puede deshacer.</p>
                    <div class="flex justify-end space-x-3">
                        <button @click="isConfirmOpen = false" class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors duration-200">
                            Cancelar
                        </button>
                        <button @click="eliminarMantenimientoConfirmado" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors duration-200">
                            Eliminar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { Notyf } from 'notyf';
import 'notyf/notyf.min.css';
import AppLayout from '@/Layouts/AppLayout.vue';

// Define el layout del dashboard
defineOptions({ layout: AppLayout });

const props = defineProps({
    titulo: {
        type: String,
        default: 'Gestión de Mantenimientos'
    },
    mantenimientos: Array
});

// State
const headers = ['Vehículo', 'Tipo de Mantenimiento', 'Fecha', 'Próximo Mantenimiento', 'Estado'];
const loading = ref(false);
const searchTerm = ref('');
const isConfirmOpen = ref(false);
const mantenimientoAEliminar = ref(null);
const mantenimientos = ref(props.mantenimientos);

// Filtered data
const mantenimientosFiltrados = computed(() => {
    if (!searchTerm.value) return mantenimientos.value;

    const searchLower = searchTerm.value.toLowerCase();
    return mantenimientos.value.filter(m => {
        const vehiculo = `${m.carro.marca} ${m.carro.modelo} ${m.carro.placa}`.toLowerCase();
        return vehiculo.includes(searchLower) ||
               m.tipo.toLowerCase().includes(searchLower) ||
               m.fecha.includes(searchLower);
    });
});

// Notifications
const notyf = new Notyf({
    duration: 4000,
    position: { x: 'right', y: 'top' },
    types: [
        { type: 'success', background: 'linear-gradient(135deg, #10b981, #059669)', icon: false },
        { type: 'error', background: 'linear-gradient(135deg, #ef4444, #dc2626)', icon: false }
    ]
});

// Methods
const formatDate = (dateString) => {
    if (!dateString) return 'N/A';

    try {
        // Si la fecha viene en formato ISO (YYYY-MM-DD), procesarla directamente
        const dateParts = dateString.split('-');
        if (dateParts.length === 3) {
            const year = parseInt(dateParts[0]);
            const month = parseInt(dateParts[1]) - 1; // Los meses en JS van de 0-11
            const day = parseInt(dateParts[2]);

            const date = new Date(year, month, day);
            const options = {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                timeZone: 'America/Mexico_City' // O tu zona horaria local
            };
            return date.toLocaleDateString('es-MX', options);
        }

        // Fallback para otros formatos
        const date = new Date(dateString);
        if (isNaN(date.getTime())) {
            return 'Fecha inválida';
        }

        const options = {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            timeZone: 'America/Mexico_City'
        };
        return date.toLocaleDateString('es-MX', options);

    } catch (error) {
        console.error('Error al formatear fecha:', error, dateString);
        return 'Error en fecha';
    }
};

// También actualiza la función getStatus para usar la misma lógica:
const getStatus = (proximoMantenimiento) => {
    if (!proximoMantenimiento) return 'Completado';

    try {
        const hoy = new Date();
        hoy.setHours(0, 0, 0, 0);

        // Procesar la fecha de la misma manera
        const dateParts = proximoMantenimiento.split('-');
        if (dateParts.length === 3) {
            const year = parseInt(dateParts[0]);
            const month = parseInt(dateParts[1]) - 1;
            const day = parseInt(dateParts[2]);
            const proximo = new Date(year, month, day);

            if (proximo < hoy) return 'Vencido';
            return 'Próximo';
        }

        // Fallback
        const proximo = new Date(proximoMantenimiento);
        if (proximo < hoy) return 'Vencido';
        return 'Próximo';

    } catch (error) {
        console.error('Error al procesar estado:', error);
        return 'Estado desconocido';
    }
};

const getStatusClasses = (proximoMantenimiento) => {
    const status = getStatus(proximoMantenimiento);
    const baseClasses = 'px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full';
    if (status === 'Vencido') return `${baseClasses} bg-red-100 text-red-800`;
    if (status === 'Próximo') return `${baseClasses} bg-yellow-100 text-yellow-800`;
    return `${baseClasses} bg-green-100 text-green-800`; // Completado
};

const confirmarEliminacion = (mantenimiento) => {
    mantenimientoAEliminar.value = mantenimiento;
    isConfirmOpen.value = true;
};

const eliminarMantenimientoConfirmado = async () => {
    if (!mantenimientoAEliminar.value) return;

    loading.value = true;
    router.delete(route('mantenimientos.destroy', mantenimientoAEliminar.value.id), {
        onSuccess: () => {
            notyf.success('Mantenimiento eliminado exitosamente');
            mantenimientos.value = mantenimientos.value.filter(m => m.id !== mantenimientoAEliminar.value.id);
            isConfirmOpen.value = false;
            mantenimientoAEliminar.value = null;
        },
        onError: (errors) => {
            notyf.error('Error al eliminar el mantenimiento.');
            console.error('Error:', errors);
        },
        onFinish: () => {
            loading.value = false;
        }
    });
};

const exportData = () => {
    loading.value = true;
    const csvContent = [
        ['ID', 'Vehículo', 'Placa', 'Tipo', 'Fecha', 'Próximo Mantenimiento', 'Notas'],
        ...mantenimientos.value.map(m => [
            m.id,
            `${m.carro.marca} ${m.carro.modelo}`,
            m.carro.placa,
            m.tipo,
            m.fecha,
            m.proximo_mantenimiento || 'N/A',
            `"${(m.notas || '').replace(/"/g, '""')}"`
        ])
    ].map(row => row.join(',')).join('\n');

    const blob = new Blob([`\uFEFF${csvContent}`], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `mantenimientos-${new Date().toISOString().split('T')[0]}.csv`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);

    notyf.success('Datos exportados exitosamente');
    loading.value = false;
};
</script>

<style scoped>
.animate-fade-in-up {
    animation: fadeInUp 0.6s ease-out forwards;
}

.animate-scale-in {
    animation: scaleIn 0.3s ease-out forwards;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes scaleIn {
    from {
        opacity: 0;
        transform: scale(0.9);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

/* Scrollbar personalizada */
::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

::-webkit-scrollbar-track {
    background: #f1f5f9;
}

::-webkit-scrollbar-thumb {
    background: linear-gradient(135deg, #3b82f6, #6366f1);
    border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(135deg, #2563eb, #4f46e5);
}
</style>
