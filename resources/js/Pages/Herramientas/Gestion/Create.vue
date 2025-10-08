<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

defineOptions({ layout: AppLayout })

const props = defineProps({
  tecnicos: { type: Array, default: () => [] },
  herramientas: { type: Array, default: () => [] },
})

const form = useForm({ tecnico_id: '', herramientas: [] })

const toggleHerramienta = (id) => {
  const i = form.herramientas.indexOf(id)
  if (i === -1) form.herramientas.push(id)
  else form.herramientas.splice(i, 1)
}

const submit = () => form.post(route('herramientas.gestion.asignar'))
</script>

<template>
  <Head title="Asignar Herramientas" />
  <div class="flex items-center justify-between mb-4">
    <h1 class="text-2xl font-semibold text-slate-900">Asignar Herramientas a Técnico</h1>
    <Link class="text-blue-600 hover:underline" :href="route('herramientas.gestion.index')">Volver</Link>
  </div>

  <form @submit.prevent="submit" class="bg-white shadow rounded p-6 space-y-6">
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Técnico</label>
      <select v-model="form.tecnico_id" class="w-full border rounded px-3 py-2" required>
        <option value="" disabled>Seleccione un técnico</option>
        <option v-for="t in props.tecnicos" :value="t.id" :key="t.id">{{ t.nombre }}</option>
      </select>
      <div v-if="form.errors.tecnico_id" class="text-sm text-red-600">{{ form.errors.tecnico_id }}</div>
    </div>

    <div>
      <div class="font-medium mb-2">Herramientas disponibles</div>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-2 max-h-96 overflow-auto">
        <label v-for="h in props.herramientas" :key="h.id" class="flex items-center gap-2 border rounded p-2">
          <input type="checkbox" :value="h.id" :checked="form.herramientas.includes(h.id)" @change="toggleHerramienta(h.id)" />
          <span>{{ h.nombre }} <span class="text-gray-500">(Serie: {{ h.numero_serie || 'N/A' }})</span></span>
        </label>
      </div>
      <div v-if="form.errors.herramientas" class="text-sm text-red-600">{{ form.errors.herramientas }}</div>
    </div>

    <div>
      <button :disabled="form.processing" type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50">Asignar</button>
    </div>
  </form>
</template>

