<template>
  <Head :title="`Registro de Vacaciones - ${empleado.name}`" />
  <div class="min-h-screen bg-gray-50">
    <div class="max-w-5xl mx-auto px-6 py-8">
      <div class="flex items-center justify-between mb-6">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Registro de Vacaciones</h1>
          <p class="text-gray-600">Empleado: <span class="font-medium">{{ empleado.name }}</span></p>
        </div>
        <Link :href="route('vacaciones.index')" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">Volver</Link>
      </div>

      <!-- Resumen -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white border border-gray-200 rounded-lg p-4">
          <p class="text-xs text-gray-600">Año</p>
          <p class="text-xl font-semibold text-gray-900">{{ anio }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-lg p-4">
          <p class="text-xs text-gray-600">Días correspondientes</p>
          <p class="text-xl font-semibold text-gray-900">{{ registro?.dias_correspondientes ?? '-' }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-lg p-4">
          <p class="text-xs text-gray-600">Disponibles</p>
          <p class="text-xl font-semibold text-gray-900">{{ registro?.dias_disponibles ?? '-' }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-lg p-4">
          <p class="text-xs text-gray-600">Utilizados</p>
          <p class="text-xl font-semibold text-gray-900">{{ registro?.dias_utilizados ?? '-' }}</p>
        </div>
      </div>

      <!-- Ajustes -->
      <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
        <div class="p-4 border-b border-gray-200 flex items-center justify-between">
          <h2 class="text-lg font-semibold text-gray-900">Ajustes ({{ anio }} y {{ anio - 1 }})</h2>
        </div>
        <div v-if="ajustes && ajustes.length" class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Fecha</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Año</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Días (+/-)</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Motivo</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Aplicado por</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="a in ajustes" :key="a.id">
                <td class="px-4 py-3 text-sm text-gray-900">{{ formatDate(a.created_at) }}</td>
                <td class="px-4 py-3 text-sm text-gray-700">{{ a.anio }}</td>
                <td class="px-4 py-3 text-sm" :class="a.dias >= 0 ? 'text-green-700' : 'text-red-700'">{{ a.dias }}</td>
                <td class="px-4 py-3 text-sm text-gray-700">{{ a.motivo || '-' }}</td>
                <td class="px-4 py-3 text-sm text-gray-900">{{ a.creador?.name || 'Sistema' }}</td>
              </tr>
            </tbody>
          </table>
        </div>
        <p v-else class="p-4 text-sm text-gray-600">No hay ajustes registrados.</p>
      </div>
    </div>
  </div>
  
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3'

const props = defineProps({
  empleado: Object,
  anio: Number,
  registro: Object,
  ajustes: Array,
})

const formatDate = (date) => {
  try {
    return new Date(date).toLocaleString('es-MX', {
      day: '2-digit', month: '2-digit', year: 'numeric',
      hour: '2-digit', minute: '2-digit'
    })
  } catch { return date }
}
</script>

<style scoped>
</style>

