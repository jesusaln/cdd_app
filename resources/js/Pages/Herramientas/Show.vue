<template>
  <Head :title="`Herramienta: ${herramienta.nombre}`" />
  <div class="max-w-4xl mx-auto p-4">
    <div class="bg-white shadow-sm rounded-lg p-6">
      <!-- Header -->
      <div class="flex items-start justify-between mb-6">
        <div>
          <h1 class="text-2xl font-semibold text-gray-800">Detalles de la Herramienta</h1>
          <p class="text-sm text-gray-600 mt-1">{{ herramienta.nombre }}</p>
          <div class="mt-2">
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800" v-if="herramienta.tecnico">
              Asignada
            </span>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800" v-else>
              Sin asignar
            </span>
          </div>
        </div>
        <div class="flex space-x-3">
          <Link
            :href="route('herramientas.edit', herramienta.id)"
            class="inline-flex items-center px-4 py-2 text-sm font-medium text-blue-700 bg-blue-50 border border-blue-200 rounded-md hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
            </svg>
            Editar
          </Link>
          <Link
            :href="route('herramientas.index')"
            class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Regresar
          </Link>
        </div>
      </div>

      <!-- Mensaje de éxito/error -->
      <div v-if="flash.success" class="mb-6 p-4 bg-green-50 border border-green-200 rounded-md">
        <p class="text-sm text-green-800">{{ flash.success }}</p>
      </div>
      <div v-if="flash.error" class="mb-6 p-4 bg-red-50 border border-red-200 rounded-md">
        <p class="text-sm text-red-800">{{ flash.error }}</p>
      </div>

      <!-- Información General -->
      <section class="border-b border-gray-200 pb-6 mb-6">
        <h2 class="text-lg font-medium text-gray-900 mb-4">Información General</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
            <p class="text-gray-900">{{ herramienta.nombre }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Número de Serie</label>
            <p class="text-gray-900 font-mono">{{ herramienta.numero_serie }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Técnico Asignado</label>
            <p class="text-gray-900" v-if="herramienta.tecnico">{{ herramienta.tecnico.nombre }} {{ herramienta.tecnico.apellido }}</p>
            <p class="text-gray-500 italic" v-else>Sin asignar</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de Creación</label>
            <p class="text-gray-900">{{ formatearFecha(herramienta.created_at) }}</p>
          </div>
          <div v-if="herramienta.descripcion">
            <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
            <p class="text-gray-900 whitespace-pre-wrap">{{ herramienta.descripcion }}</p>
          </div>
        </div>
      </section>

      <!-- Foto -->
      <section class="border-b border-gray-200 pb-6 mb-6" v-if="herramienta.foto">
        <h2 class="text-lg font-medium text-gray-900 mb-4">Foto</h2>
        <div class="flex justify-center">
          <img :src="herramienta.foto" :alt="`Foto de ${herramienta.nombre}`" class="max-w-md h-auto rounded-lg shadow-sm border border-gray-200" />
        </div>
      </section>

      <!-- Estadísticas Relacionadas -->
      <section>
        <h2 class="text-lg font-medium text-gray-900 mb-4">Relacionado</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div class="bg-gray-50 p-4 rounded-lg">
            <h3 class="text-sm font-medium text-gray-500">Mantenimientos</h3>
            <p class="text-2xl font-bold text-gray-900">{{ herramienta.mantenimientos_count || 0 }}</p>
          </div>
          <div class="bg-blue-50 p-4 rounded-lg">
            <h3 class="text-sm font-medium text-blue-700">Citas</h3>
            <p class="text-2xl font-bold text-blue-900">{{ herramienta.citas_count || 0 }}</p>
          </div>
          <div class="bg-green-50 p-4 rounded-lg">
            <h3 class="text-sm font-medium text-green-700">Bitácora</h3>
            <p class="text-2xl font-bold text-green-900">{{ herramienta.bitacora_count || 0 }}</p>
          </div>
        </div>
      </section>

      <!-- Debug (desarrollo) -->
      <div v-if="isDevelopment" class="mt-6 p-4 bg-gray-50 rounded-md text-xs">
        <h3 class="font-semibold mb-2">Debug: Herramienta ID {{ herramienta.id }}</h3>
        <pre class="text-xs overflow-auto">{{ JSON.stringify(herramienta, null, 2) }}</pre>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { computed } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'

defineOptions({ layout: AppLayout })

const props = defineProps({
  herramienta: {
    type: Object,
    required: true
  },
  flash: {
    type: Object,
    default: () => ({})
  }
})

const isDevelopment = import.meta.env?.DEV || false

// Computed para counts si no vienen del backend
const herramienta = computed(() => ({
  ...props.herramienta,
  mantenimientos_count: props.herramienta.mantenimientos_count || 0,
  citas_count: props.herramienta.citas_count || 0,
  bitacora_count: props.herramienta.bitacora_count || 0
}))

const formatearFecha = (date) => {
  if (!date) return 'Fecha no disponible'
  try {
    const d = new Date(date)
    return d.toLocaleDateString('es-MX', {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    })
  } catch {
    return 'Fecha inválida'
  }
}
</script>

<style scoped>
/* Estilos opcionales para mejorar layout */
section + section { margin-top: 2rem; }
</style>
