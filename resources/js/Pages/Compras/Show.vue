<template>
    <Head title="Ver Compra" />
    <AppLayout>
        <div class="compras-show min-h-screen bg-gradient-to-br from-slate-50 to-blue-50 p-6">
            <div class="max-w-4xl mx-auto">
                <!-- Encabezado -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden mb-6">
                    <div class="px-6 py-4 bg-gradient-to-r from-blue-500 to-blue-600 text-white">
                        <h1 class="text-xl font-bold">Compra #{{ compra.id }}</h1>
                    </div>
                    <div class="p-6">
                        <p><strong>Proveedor:</strong> {{ compra.proveedor.nombre_razon_social }}</p>
                        <p>
                            <strong>Estado:</strong>
                            <span class="ml-2 px-3 py-1 rounded-full text-sm font-medium"
                                  :class="{
                                      'bg-green-100 text-green-800': compra.estado === 'recibida',
                                      'bg-blue-100 text-blue-800': compra.estado === 'aprobada',
                                      'bg-yellow-100 text-yellow-800': compra.estado === 'pendiente',
                                      'bg-red-100 text-red-800': compra.estado === 'rechazada' || compra.estado === 'cancelada',
                                      'bg-gray-100 text-gray-800': compra.estado === 'borrador'
                                  }">
                                {{ compra.estado }}
                            </span>
                        </p>
                        <p v-if="compra.notas" class="mt-2">
                            <strong>Notas:</strong> {{ compra.notas }}
                        </p>
                    </div>
                </div>

                <!-- Tabla de ítems -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden mb-6">
                    <div class="p-6">
                        <h2 class="text-lg font-semibold mb-4">Productos y Servicios</h2>
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-500">Nombre</th>
                                    <th class="px-4 py-2 text-right text-sm font-medium text-gray-500">Cantidad</th>
                                    <th class="px-4 py-2 text-right text-sm font-medium text-gray-500">Precio</th>
                                    <th class="px-4 py-2 text-right text-sm font-medium text-gray-500">Descuento</th>
                                    <th class="px-4 py-2 text-right text-sm font-medium text-gray-500">Subtotal</th>
                                    <th class="px-4 py-2 text-center text-sm font-medium text-gray-500">Stock Antes</th>
                                    <th class="px-4 py-2 text-center text-sm font-medium text-gray-500">Stock Después</th>
                                    <th class="px-4 py-2 text-center text-sm font-medium text-gray-500">Diferencia</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <tr v-for="item in items" :key="item.id">
                                    <td class="px-4 py-3 text-sm">
                                        <div>
                                            <div class="font-medium">{{ item.nombre }}</div>
                                            <div v-if="item.descripcion" class="text-xs text-gray-500 mt-1">{{ item.descripcion }}</div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-right">{{ item.cantidad }}</td>
                                    <td class="px-4 py-3 text-sm text-right">${{ item.precio.toFixed(2) }}</td>
                                    <td class="px-4 py-3 text-sm text-right">{{ item.descuento }}%</td>
                                    <td class="px-4 py-3 text-sm text-right">
                                        ${{ ((item.cantidad * item.precio) * (1 - item.descuento / 100)).toFixed(2) }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-center">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ item.stock_antes }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-center">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            {{ item.stock_despues }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-center">
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                            :class="{
                                                'bg-green-100 text-green-800': item.diferencia_stock > 0,
                                                'bg-red-100 text-red-800': item.diferencia_stock < 0,
                                                'bg-gray-100 text-gray-800': item.diferencia_stock === 0
                                            }"
                                        >
                                            <svg v-if="item.diferencia_stock > 0" class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M5.293 7.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 5.414V17a1 1 0 11-2 0V5.414L6.707 7.707a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                            </svg>
                                            <svg v-else-if="item.diferencia_stock < 0" class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M14.707 12.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L9 14.586V3a1 1 0 012 0v11.586l2.293-2.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                            </svg>
                                            {{ item.diferencia_stock > 0 ? '+' : '' }}{{ item.diferencia_stock }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Totales -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden mb-6">
                    <div class="p-6">
                        <div class="space-y-2 text-right">
                            <p><strong>Subtotal:</strong> ${{ subtotal.toFixed(2) }}</p>
                            <p v-if="descuentoGeneral > 0"><strong>Descuento General:</strong> ${{ descuentoGeneral.toFixed(2) }}</p>
                            <p><strong>IVA:</strong> ${{ iva.toFixed(2) }}</p>
                            <p class="text-xl"><strong>Total:</strong> ${{ total.toFixed(2) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Acciones -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="p-6 flex flex-wrap gap-3">
                        <Link
                            v-if="canEdit"
                            :href="route('compras.edit', compra.id)"
                            class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors"
                        >
                            Editar
                        </Link>

                        <Link
                            v-if="canDelete"
                            :href="route('compras.destroy', compra.id)"
                            method="delete"
                            as="button"
                            class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors"
                        >
                            Eliminar
                        </Link>

                        <button
                            @click="mostrarVistaPrevia = true"
                            class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors"
                        >
                            Vista Previa
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de Vista Previa -->
        <VistaPreviaModal
            :show="mostrarVistaPrevia"
            type="compra"
            :proveedor="compra.proveedor"
            :items="items"
            :totals="{
                subtotal,
                descuentoGeneral,
                iva,
                total
            }"
            :descuento-general="descuentoGeneral"
            :notas="compra.notas"
            @close="mostrarVistaPrevia = false"
            @print="() => window.print()"
        />
    </AppLayout>
</template>

<script setup>
import { computed, ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import VistaPreviaModal from '@/Components/Modals/VistaPreviaModal.vue';

// Props
const props = defineProps({
    compra: Object,
    canReceive: {
        type: Boolean,
        default: true
    },
    canEdit: {
        type: Boolean,
        default: true
    },
    canDelete: {
        type: Boolean,
        default: true
    }
});

// Totales
const subtotal = computed(() => parseFloat(props.compra.subtotal) || 0);
const descuentoGeneral = computed(() => parseFloat(props.compra.descuento_general) || 0);
const iva = computed(() => parseFloat(props.compra.iva) || 0);
const total = computed(() => parseFloat(props.compra.total) || 0);

// Items
const items = computed(() => {
    if (!props.compra.productos || !Array.isArray(props.compra.productos)) {
        return [];
    }
    return props.compra.productos.map(item => ({
        id: item.id,
        nombre: item.nombre || 'Sin nombre',
        descripcion: item.descripcion || '',
        cantidad: parseInt(item.cantidad) || 0,
        precio: parseFloat(item.precio) || 0,
        descuento: parseFloat(item.descuento) || 0,
        stock_antes: parseInt(item.stock_antes) || 0,
        stock_despues: parseInt(item.stock_despues) || 0,
        diferencia_stock: parseInt(item.diferencia_stock) || 0
    }));
});

// Estado
const mostrarVistaPrevia = ref(false);
</script>

  <style scoped>
  /* Aquí van tus estilos personalizados */
  </style>
