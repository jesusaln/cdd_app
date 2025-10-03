<template>
  <Suspense>
    <template #default>
      <AsyncReportesIndexContent v-bind="propsPayload" />
    </template>
    <template #fallback>
      <div class="flex items-center justify-center py-24">
        <svg class="animate-spin h-6 w-6 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
        </svg>
        <span class="ml-3 text-sm text-gray-500">Cargando módulo de reportes…</span>
      </div>
    </template>
  </Suspense>
</template>

<script setup>
import { defineAsyncComponent, computed } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'

defineOptions({ layout: AppLayout })

const incomingProps = defineProps({
  reportesVentas: { type: Array, default: () => [] },
  corteVentas: { type: Number, default: 0 },
  utilidadVentas: { type: Number, default: 0 },
  reportesCompras: { type: Array, default: () => [] },
  totalCompras: { type: Number, default: 0 },
  inventario: { type: Array, default: () => [] },
  movimientosInventario: { type: Array, default: () => [] },
})

const AsyncReportesIndexContent = defineAsyncComponent({
  loader: () => import('./IndexContent.vue'),
  suspensible: true,
})

const propsPayload = computed(() => ({ ...incomingProps }))
</script>
