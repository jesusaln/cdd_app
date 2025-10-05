<!-- /resources/js/Pages/Traspasos/Index.vue -->
<script setup>
import { ref } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

defineOptions({ layout: AppLayout })

// Estado
const loading = ref(false)
</script>

<template>
  <Head title="Traspasos de Inventario" />

  <div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-6 py-8">
      <!-- Header -->
      <div class="mb-8">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Traspasos de Inventario</h1>
            <p class="text-gray-600 mt-1">Transfiere productos entre almacenes</p>
          </div>
          <Link
            :href="route('traspasos.create')"
            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Nuevo Traspaso
          </Link>
        </div>
      </div>

      <!-- Contenido -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
        <div class="text-center py-12">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
          </svg>
          <h3 class="mt-2 text-sm font-medium text-gray-900">Traspasos de Inventario</h3>
          <p class="mt-1 text-sm text-gray-500">
            Aquí podrás gestionar los traspasos de productos entre diferentes almacenes.
          </p>
          <div class="mt-6">
            <Link
              :href="route('traspasos.create')"
              class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
            >
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
              </svg>
              Crear Primer Traspaso
            </Link>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
