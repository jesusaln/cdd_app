<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

defineOptions({ layout: AppLayout })

const props = defineProps({
  herramienta: { type: Object, required: true }
})
</script>

<template>
  <Head :title="`Herramienta - ${props.herramienta.nombre}`" />

  <div class="flex items-center justify-between mb-4">
    <h1 class="text-2xl font-semibold text-slate-900">{{ props.herramienta.nombre }}</h1>
    <div class="flex gap-3">
      <Link class="text-blue-600 hover:underline" :href="route('herramientas.edit', props.herramienta.id)">Editar</Link>
      <Link class="text-slate-600 hover:underline" :href="route('herramientas.index')">Volver</Link>
    </div>
  </div>

  <div class="bg-white shadow rounded p-6 grid md:grid-cols-2 gap-6">
    <div>
      <div class="text-sm text-gray-500">Número de serie</div>
      <div class="font-medium">{{ props.herramienta.numero_serie || 'N/A' }}</div>

      <div class="mt-4 text-sm text-gray-500">Estado</div>
      <div class="font-medium capitalize">{{ props.herramienta.estado }}</div>

      <div class="mt-4 text-sm text-gray-500">Descripción</div>
      <div class="font-medium whitespace-pre-wrap">{{ props.herramienta.descripcion || '—' }}</div>
    </div>
    <div>
      <div class="text-sm text-gray-500 mb-2">Foto de condición</div>
      <img v-if="props.herramienta.foto" :src="`/storage/${props.herramienta.foto}`" alt="Foto" class="w-full max-w-sm h-auto object-cover rounded" />
      <div v-else class="text-gray-500">Sin imagen</div>
    </div>
  </div>
</template>

