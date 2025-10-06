<template>
    <AppLayout title="Detalles de Cuenta por Pagar">
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Detalles de Cuenta por Pagar
                </h2>
                <div class="flex space-x-2">
                    <Link
                        :href="route('cuentas-por-pagar.edit', cuenta.id)"
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                    >
                        Editar
                    </Link>
                    <Link
                        :href="route('cuentas-por-pagar.index')"
                        class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded"
                    >
                        Volver
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <!-- Información General -->
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Información General</h3>
                            <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-6">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">ID de Cuenta</dt>
                                    <dd class="mt-1 text-sm text-gray-900">#{{ cuenta.id }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Estado</dt>
                                    <dd class="mt-1">
                                        <span :class="{
                                            'bg-red-100 text-red-800': cuenta.estado === 'vencido',
                                            'bg-yellow-100 text-yellow-800': cuenta.estado === 'parcial',
                                            'bg-green-100 text-green-800': cuenta.estado === 'pagado',
                                            'bg-gray-100 text-gray-800': cuenta.estado === 'pendiente'
                                        }" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                                            {{ cuenta.estado }}
                                        </span>
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Fecha de Creación</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ new Date(cuenta.created_at).toLocaleDateString() }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Fecha de Vencimiento</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        {{ cuenta.fecha_vencimiento ? new Date(cuenta.fecha_vencimiento).toLocaleDateString() : 'No especificada' }}
                                    </dd>
                                </div>
                            </dl>
                        </div>

                        <!-- Información de la Compra -->
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Información de la Compra</h3>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-6">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Número de Compra</dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            <Link :href="route('compras.show', cuenta.compra.id)" class="text-blue-600 hover:text-blue-800">
                                                {{ cuenta.compra.numero_compra }}
                                            </Link>
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Proveedor</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ cuenta.compra.proveedor.nombre_razon_social }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Total de la Compra</dt>
                                        <dd class="mt-1 text-sm text-gray-900">${{ cuenta.compra.total.toFixed(2) }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Estado de la Compra</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ cuenta.compra.estado }}</dd>
                                    </div>
                                </dl>
                            </div>
                        </div>

                        <!-- Estado de Pagos -->
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Estado de Pagos</h3>
                            <div class="bg-blue-50 p-4 rounded-lg">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div class="text-center">
                                        <div class="text-2xl font-bold text-blue-600">${{ cuenta.monto_total.toFixed(2) }}</div>
                                        <div class="text-sm text-blue-600">Monto Total</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-2xl font-bold text-green-600">${{ cuenta.monto_pagado.toFixed(2) }}</div>
                                        <div class="text-sm text-green-600">Monto Pagado</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-2xl font-bold text-red-600">${{ cuenta.monto_pendiente.toFixed(2) }}</div>
                                        <div class="text-sm text-red-600">Monto Pendiente</div>
                                    </div>
                                </div>

                                <!-- Barra de progreso -->
                                <div class="mt-4">
                                    <div class="flex justify-between text-sm text-gray-600 mb-1">
                                        <span>Progreso de Pago</span>
                                        <span>{{ Math.round((cuenta.monto_pagado / cuenta.monto_total) * 100) }}%</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div
                                            class="bg-blue-600 h-2 rounded-full"
                                            :style="{ width: Math.round((cuenta.monto_pagado / cuenta.monto_total) * 100) + '%' }"
                                        ></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Notas -->
                        <div v-if="cuenta.notas" class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Notas</h3>
                            <div class="bg-yellow-50 p-4 rounded-lg">
                                <p class="text-sm text-gray-700 whitespace-pre-line">{{ cuenta.notas }}</p>
                            </div>
                        </div>

                        <!-- Historial de Pagos (simulado) -->
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Historial de Pagos</h3>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <p class="text-sm text-gray-600">
                                    Los pagos se registran en las notas de la cuenta. Para ver el historial completo,
                                    edita la cuenta y registra nuevos pagos.
                                </p>
                                <div class="mt-4">
                                    <Link
                                        :href="route('cuentas-por-pagar.edit', cuenta.id)"
                                        class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded"
                                    >
                                        Registrar Nuevo Pago
                                    </Link>
                                </div>
                            </div>
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
    cuenta: Object,
});
</script>
