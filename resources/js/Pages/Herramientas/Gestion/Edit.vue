<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

defineOptions({ layout: AppLayout })

const props = defineProps({
  tecnico: { type: Object, required: true },
  asignadas: { type: Array, default: () => [] },
  disponibles: { type: Array, default: () => [] },
})

const form = useForm({ asignadas: props.asignadas.map(h => h.id) })

const toggle = (id) => {
  const i = form.asignadas.indexOf(id)
  if (i === -1) form.asignadas.push(id)
  else form.asignadas.splice(i, 1)
}

const isChecked = (id) => form.asignadas.includes(id)

const submit = () => form.put(route('herramientas.gestion.update', props.tecnico.id))
</script>

<template>
  <Head :title="`Gestión de Herramientas - ${props.tecnico.nombre}`" />

  <div class="flex items-center justify-between mb-4">
    <h1 class="text-2xl font-semibold text-slate-900">Gestión de Herramientas - {{ props.tecnico.nombre }}</h1>
    <Link class="text-blue-600 hover:underline" :href="route('herramientas.gestion.index')">Volver</Link>
  </div>

  <form @submit.prevent="submit" class="grid lg:grid-cols-2 gap-6">
    <div class="bg-white shadow rounded p-4">
      <div class="font-medium mb-2">Asignadas</div>
      <div class="space-y-2 max-h-[28rem] overflow-auto">
        <label v-for="h in props.asignadas" :key="`a-${h.id}`" class="flex items-center gap-2 border rounded p-2">
          <input type="checkbox" :value="h.id" :checked="isChecked(h.id)" @change="toggle(h.id)" />
          <span>{{ h.nombre }} <span class="text-gray-500">(Serie: {{ h.numero_serie || 'N/A' }})</span></span>
        </label>
      </div>
    </div>

    <div class="bg-white shadow rounded p-4">
      <div class="font-medium mb-2">Disponibles</div>
      <div class="space-y-2 max-h-[28rem] overflow-auto">
        <label v-for="h in props.disponibles" :key="`d-${h.id}`" class="flex items-center gap-2 border rounded p-2">
          <input type="checkbox" :value="h.id" :checked="isChecked(h.id)" @change="toggle(h.id)" />
          <span>{{ h.nombre }} <span class="text-gray-500">(Serie: {{ h.numero_serie || 'N/A' }})</span></span>
        </label>
      </div>
    </div>

    <div class="lg:col-span-2">
      <button :disabled="form.processing" type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50">Guardar cambios</button>
    </div>
  </form>
</template>

