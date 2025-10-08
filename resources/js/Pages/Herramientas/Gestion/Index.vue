<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { computed } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'

defineOptions({ layout: AppLayout })

const props = defineProps({
  tecnicos: { type: [Array, Object], required: true },
})

const rows = computed(() => Array.isArray(props.tecnicos) ? props.tecnicos : props.tecnicos)
</script>

<template>
  <Head title="Gestión de Herramientas" />

  <div class="flex items-center justify-between mb-4">
    <h1 class="text-2xl font-semibold text-slate-900">Gestión de Herramientas</h1>
    <Link class="px-3 py-2 bg-blue-600 text-white rounded hover:bg-blue-700" :href="route('herramientas.gestion.create')">Crear Asignación</Link>
  </div>

  <div class="bg-white shadow rounded overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
      <thead class="bg-gray-50">
        <tr>
          <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Técnico</th>
          <th class="px-4 py-2"></th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-200">
        <tr v-for="t in rows" :key="t.id">
          <td class="px-4 py-2">
            <div class="font-medium">{{ t.nombre }}</div>
            <div class="text-gray-500 text-sm">{{ t.telefono || '' }}</div>
          </td>
          <td class="px-4 py-2 text-right">
            <Link class="text-blue-600 hover:underline" :href="route('herramientas.gestion.edit', t.id)">Gestionar</Link>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>
