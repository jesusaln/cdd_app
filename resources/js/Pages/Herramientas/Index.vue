<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'

defineOptions({ layout: AppLayout })

const props = defineProps({
  herramientas: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
})

const search = ref(props.filters?.search || '')
const items = computed(() => props.herramientas?.data || [])

const doSearch = () => {
  router.get(route('herramientas.index'), { search: search.value }, { preserveState: true, preserveScroll: true })
}

const pageLinks = computed(() => props.herramientas?.links || [])
</script>

<template>
  <Head title="Herramientas" />

  <div class="flex items-center justify-between mb-4">
    <h1 class="text-2xl font-semibold text-slate-900">Catálogo de Herramientas</h1>
    <Link class="px-3 py-2 bg-blue-600 text-white rounded hover:bg-blue-700" :href="route('herramientas.create')">
      Nueva Herramienta
    </Link>
  </div>

  <div class="mb-4">
    <input
      v-model="search"
      @keyup.enter="doSearch"
      type="search"
      placeholder="Buscar por nombre o número de serie"
      class="w-full md:w-96 border rounded px-3 py-2"
    />
  </div>

  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    <div v-for="h in items" :key="h.id" class="border rounded-lg p-4 bg-white shadow-sm">
      <div class="flex items-center gap-3 mb-3">
        <img v-if="h.foto" :src="`/storage/${h.foto}`" alt="Foto" class="w-16 h-16 object-cover rounded" />
        <div>
          <h3 class="font-semibold">{{ h.nombre }}</h3>
          <p class="text-sm text-gray-600">Serie: {{ h.numero_serie || 'N/A' }}</p>
        </div>
      </div>
      <div class="flex items-center justify-between text-sm">
        <span class="px-2 py-0.5 rounded bg-slate-100">{{ h.estado }}</span>
        <div class="flex gap-2">
          <Link :href="route('herramientas.edit', h.id)" class="text-blue-600 hover:underline">Editar</Link>
          <Link as="button" method="delete" :href="route('herramientas.destroy', h.id)" class="text-red-600 hover:underline" preserve-scroll>
            Eliminar
          </Link>
        </div>
      </div>
    </div>
  </div>

  <div class="flex gap-2 mt-6">
    <Link v-for="l in pageLinks" :key="l.url + l.label" :href="l.url || '#'" preserve-scroll :class="[
      'px-3 py-1 border rounded',
      l.active ? 'bg-blue-600 text-white border-blue-600' : 'bg-white'
    ]" v-html="l.label" />
  </div>
</template>

