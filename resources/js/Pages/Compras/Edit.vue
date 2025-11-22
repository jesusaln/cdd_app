<!-- /resources/js/Pages/Compras/Edit.vue -->
<script setup>
import { Head, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

defineOptions({ layout: AppLayout })

const props = defineProps({
  compra: { type: Object, required: true },
  proveedores: { type: Array, default: () => [] },
  productos: { type: Array, default: () => [] },
  almacenes: { type: Array, default: () => [] }
})

const goBack = () => router.visit(route('compras.index'))
</script>

<template>
  <Head title="Editar Compra" />
  <div class="min-h-screen bg-gray-50 py-8 px-6">
    <div class="max-w-6xl mx-auto space-y-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Editar Compra #{{ compra.id }}</h1>
          <p class="text-sm text-gray-600">Proveedor: {{ compra.proveedor?.nombre || 'N/A' }} | Almacén: {{ compra.almacen?.nombre || 'N/A' }}</p>
        </div>
        <button @click="goBack" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
          Volver
        </button>
      </div>

      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Productos de la compra</h2>
        <div v-if="compra.productos?.length" class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Producto</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600">Cantidad</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600">Precio</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600">Descuento</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600">Subtotal</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="p in compra.productos" :key="p.id" class="hover:bg-gray-50">
                <td class="px-4 py-3">
                  <div class="text-sm font-medium text-gray-900">{{ p.nombre }}</div>
                  <div class="text-xs text-gray-500">{{ p.descripcion }}</div>
                </td>
                <td class="px-4 py-3 text-right text-sm text-gray-900">{{ p.cantidad }}</td>
                <td class="px-4 py-3 text-right text-sm text-gray-900">${{ parseFloat(p.precio).toFixed(2) }}</td>
                <td class="px-4 py-3 text-right text-sm text-gray-900">
                  <span v-if="p.descuento">{{ p.descuento }}%</span>
                  <span v-else>-</span>
                </td>
                <td class="px-4 py-3 text-right text-sm font-semibold text-gray-900">${{ parseFloat(p.subtotal).toFixed(2) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
        <div v-else class="text-sm text-gray-500">No hay productos asociados a esta compra.</div>
      </div>
    </div>
  </div>
</template>
