<template>
    <AppLayout title="Detalle del Movimiento de Inventario">
        <template #header>
            <div class="flex items-center space-x-4">
                <Link
                    :href="route('reportes.movimientos-inventario')"
                    class="text-indigo-600 hover:text-indigo-900"
                >
                    ← Volver al Reporte
                </Link>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Detalle del Movimiento
                </h2>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6">
                        <!-- Información General -->
                        <div class="mb-8">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Información General</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">ID del Movimiento</label>
                                    <div class="mt-1 text-sm text-gray-900">{{ movimiento.id }}</div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Fecha de Creación</label>
                                    <div class="mt-1 text-sm text-gray-900">{{ movimiento.created_at }}</div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Última Actualización</label>
                                    <div class="mt-1 text-sm text-gray-900">{{ movimiento.updated_at }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Información del Producto -->
                        <div class="mb-8" v-if="movimiento.producto">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Producto</h3>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Nombre</label>
                                        <div class="mt-1 text-sm text-gray-900">{{ movimiento.producto.nombre }}</div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Código</label>
                                        <div class="mt-1 text-sm text-gray-900">{{ movimiento.producto.codigo }}</div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Categoría</label>
                                        <div class="mt-1 text-sm text-gray-900">{{ movimiento.producto.categoria || 'Sin categoría' }}</div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Marca</label>
                                        <div class="mt-1 text-sm text-gray-900">{{ movimiento.producto.marca || 'Sin marca' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Información del Usuario -->
                        <div class="mb-8" v-if="movimiento.user">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Usuario Responsable</h3>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Nombre</label>
                                        <div class="mt-1 text-sm text-gray-900">{{ movimiento.user.name }}</div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Email</label>
                                        <div class="mt-1 text-sm text-gray-900">{{ movimiento.user.email }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Detalles del Movimiento -->
                        <div class="mb-8">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Detalles del Movimiento</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Tipo</label>
                                    <div class="mt-1">
                                        <span
                                            :class="movimiento.tipo === 'entrada' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                            class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                        >
                                            {{ movimiento.tipo === 'entrada' ? 'Entrada' : 'Salida' }}
                                        </span>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Cantidad</label>
                                    <div class="mt-1 text-sm text-gray-900">
                                        <span
                                            :class="movimiento.tipo === 'entrada' ? 'text-green-600' : 'text-red-600'"
                                            class="font-medium"
                                        >
                                            {{ movimiento.tipo === 'entrada' ? '+' : '-' }}{{ formatNumber(movimiento.cantidad) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700">Motivo</label>
                                    <div class="mt-1 text-sm text-gray-900">{{ movimiento.motivo }}</div>
                                </div>
                                <div class="md:col-span-2" v-if="movimiento.referencia">
                                    <label class="block text-sm font-medium text-gray-700">Referencia</label>
                                    <div class="mt-1 text-sm text-gray-900">{{ movimiento.referencia }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Metadatos -->
                        <div class="mb-8" v-if="movimiento.metadatos && Object.keys(movimiento.metadatos).length > 0">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Metadatos Adicionales</h3>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <pre class="text-xs text-gray-800 overflow-x-auto">{{ JSON.stringify(movimiento.metadatos, null, 2) }}</pre>
                            </div>
                        </div>

                        <!-- Acciones -->
                        <div class="flex justify-end space-x-3">
                            <Link
                                :href="route('reportes.movimientos-inventario')"
                                class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-md text-sm font-medium"
                            >
                                Volver al Reporte
                            </Link>
                            <Link
                                :href="route('reportes.movimientos-inventario-export', { ...$page.props.filters, producto_id: movimiento.producto?.id })"
                                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium"
                            >
                                Exportar Movimientos del Producto
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    movimiento: Object,
});

// Formatear números
const formatNumber = (number) => {
    return new Intl.NumberFormat('es-MX').format(number);
};
</script>

<style scoped>
pre {
    white-space: pre-wrap;
    word-wrap: break-word;
}
</style>
