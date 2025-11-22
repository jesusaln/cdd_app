<!-- /resources/js/Pages/Compras/Edit.vue -->
<script setup>
import { computed, ref } from 'vue'
import { Head, router, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

defineOptions({ layout: AppLayout })

const props = defineProps({
  compra: { type: Object, required: true },
  proveedores: { type: Array, default: () => [] },
  productos: { type: Array, default: () => [] },
  almacenes: { type: Array, default: () => [] }
})

const goBack = () => router.visit(route('compras.index'))

const productosConSerie = computed(() =>
  (props.compra.productos || []).filter((p) => p.requiere_serie)
)

const seriesForm = useForm({
  productos: productosConSerie.value.map((p) => ({
    id: p.id,
    nombre: p.nombre,
    cantidad: p.cantidad,
    seriales: Array.from({ length: p.cantidad }, (_, i) => p.seriales?.[i] || '')
  }))
})

const serieError = ref('')

const updateSerie = (productoIndex, serieIndex, value) => {
  serieError.value = ''
  seriesForm.productos[productoIndex].seriales[serieIndex] = value
}

const submitSeries = () => {
  serieError.value = ''

  for (const producto of seriesForm.productos) {
    const trimmed = (producto.seriales || []).map((s) => (s || '').trim())
    const unique = new Set(trimmed)

    if (trimmed.length !== producto.cantidad) {
      serieError.value = `Debes capturar exactamente ${producto.cantidad} series para ${producto.nombre}.`
      return
    }
    if (unique.size !== trimmed.length) {
      serieError.value = `Las series de ${producto.nombre} deben ser únicas.`
      return
    }
  }

  seriesForm.post(route('compras.actualizar-series', props.compra.id), {
    preserveScroll: true,
    onError: (errors) => {
      serieError.value = errors.seriales || 'Revisa los datos enviados.'
    }
  })
}
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

      <!-- Series -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-lg font-semibold text-gray-800">Series de los productos</h2>
          <span class="text-xs text-gray-500">Edita o captura las series faltantes.</span>
        </div>

        <div v-if="productosConSerie.length">
          <div
            v-for="(producto, index) in seriesForm.productos"
            :key="producto.id"
            class="border border-gray-200 rounded-lg p-4 mb-4 bg-gray-50"
          >
            <div class="flex items-center justify-between mb-2">
              <div>
                <p class="text-sm font-semibold text-gray-900">{{ producto.nombre }}</p>
                <p class="text-xs text-gray-500">Cantidad: {{ producto.cantidad }}</p>
              </div>
              <span class="text-xs px-2 py-1 bg-blue-100 text-blue-700 rounded-full">Requiere serie</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
              <div
                v-for="(serie, sIndex) in producto.seriales"
                :key="sIndex"
                class="flex items-center space-x-2"
              >
                <span class="text-xs text-gray-500 w-6 text-right">{{ sIndex + 1 }}.</span>
                <input
                  :value="serie"
                  @input="updateSerie(index, sIndex, $event.target.value)"
                  type="text"
                  :placeholder="`Serie ${sIndex + 1}`"
                  class="flex-1 px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  required
                />
              </div>
            </div>
          </div>

          <div v-if="serieError" class="mb-3 text-sm text-red-600">
            {{ serieError }}
          </div>

          <div class="flex justify-end">
            <button
              @click="submitSeries"
              class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
            >
              Guardar series
            </button>
          </div>
        </div>
        <div v-else class="text-sm text-gray-500">
          Esta compra no tiene productos que requieran series.
        </div>
      </div>
    </div>
  </div>
</template>
