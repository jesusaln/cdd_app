<template>
  <Head title="Garantías - Buscar Serie" />
  <div class="max-w-3xl mx-auto p-6">
    <h1 class="text-2xl font-semibold mb-4">Buscar Garantía por Serie</h1>

    <form method="get" :action="route('garantias.buscar')" class="flex gap-2 mb-6">
      <input
        type="text"
        name="serie"
        v-model="serieInput"
        class="flex-1 border rounded-lg px-4 py-2"
        placeholder="Ingresa número de serie"
        required
      />
      <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg">Buscar</button>
    </form>

    <div v-if="resultado" class="bg-white border rounded-lg p-4 shadow-sm">
      <h2 class="text-lg font-medium mb-3">Resultado</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <div class="text-sm text-gray-600">Número de Serie</div>
          <div class="font-semibold">{{ resultado.numero_serie }}</div>
        </div>
        <div>
          <div class="text-sm text-gray-600">Estado de Serie</div>
          <div class="font-semibold uppercase">{{ resultado.estado_serie || 'N/D' }}</div>
        </div>
        <div>
          <div class="text-sm text-gray-600">Producto</div>
          <div class="font-semibold">{{ resultado.producto_nombre || ('#' + resultado.producto_id) }}</div>
        </div>
        <div>
          <div class="text-sm text-gray-600">Venta</div>
          <div class="font-semibold">{{ resultado.numero_venta ? ('#' + resultado.numero_venta) : 'No asociado' }}</div>
        </div>
        <div class="md:col-span-2" v-if="resultado.cliente_id">
          <div class="text-sm text-gray-600">Cliente</div>
          <div class="font-semibold">{{ resultado.cliente_nombre }} <span class="text-gray-500">{{ resultado.cliente_email }} · {{ resultado.cliente_telefono }}</span></div>
        </div>
      </div>

      <div class="mt-4 flex gap-2">
        <Link
          v-if="resultado.cliente_id"
          :href="citaUrl"
          class="px-4 py-2 bg-green-600 text-white rounded-lg"
        >Crear Cita de Revisión</Link>
        <div v-else class="text-sm text-amber-600">Esta serie no está asociada a una venta/cliente.</div>
      </div>
    </div>

    <div v-else-if="serie && !resultado" class="p-4 bg-amber-50 border border-amber-200 rounded-lg text-amber-800">
      No se encontró información para la serie "{{ serie }}".
    </div>
  </div>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'

defineOptions({
  layout: AppLayout,
})

const props = defineProps({
  serie: { type: String, default: '' },
  resultado: { type: Object, default: null },
})

const serieInput = ref(props.serie || '')

const citaUrl = computed(() => {
  if (!props.resultado?.cliente_id) return '#'
  const params = new URLSearchParams({
    cliente_id: String(props.resultado.cliente_id),
    numero_serie: props.resultado.numero_serie || '',
    descripcion: `Garantía por serie ${props.resultado.numero_serie} - Venta ${props.resultado.numero_venta || ''}`,
  })
  return `${route('citas.create')}?${params.toString()}`
})
</script>

