<template>
  <Head title="Panel" />
  <div class="container mx-auto px-6 py-10">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
      <!-- Clientes -->
      <PanLink href="/clientes" class="group bg-white p-6 rounded-2xl shadow-lg border border-gray-200 transition-all transform hover:scale-105 hover:shadow-xl text-center flex flex-col items-center justify-center h-full">
        <div class="flex flex-col items-center justify-center space-y-2">
          <FontAwesomeIcon :icon="['fas', 'users']" class="h-10 w-10 text-blue-600 group-hover:text-blue-700 transition-colors" />
          <h2 class="text-xl font-bold text-gray-900">Clientes {{ clientesCount }}</h2>
          <p class="text-sm text-gray-600">Nuevos este mes: {{ clientesNuevosCount }}</p>
        </div>
      </PanLink>

      <!-- Productos -->
      <PanLink href="/productos" class="group bg-white p-6 rounded-2xl shadow-lg border border-gray-200 transition-all transform hover:scale-105 hover:shadow-xl text-center flex flex-col items-center justify-center h-full">
        <div class="flex flex-col items-center justify-center space-y-2">
          <FontAwesomeIcon :icon="['fas', 'box-open']" class="h-10 w-10 text-green-600 group-hover:text-green-700 transition-colors" />
          <h2 class="text-xl font-bold text-gray-900">Productos {{ productosCount }}</h2>
          <p class="text-sm text-gray-600">Bajo stock: {{ productosBajoStockCount }}</p>
        </div>
      </PanLink>

      <!-- Proveedores -->
      <PanLink href="/proveedores" class="group bg-white p-6 rounded-2xl shadow-lg border border-gray-200 transition-all transform hover:scale-105 hover:shadow-xl text-center flex flex-col items-center justify-center h-full">
        <div class="flex flex-col items-center justify-center space-y-2">
          <FontAwesomeIcon :icon="['fas', 'truck']" class="h-10 w-10 text-purple-600 group-hover:text-purple-700 transition-colors" />
          <h2 class="text-xl font-bold text-gray-900">Proveedores {{ proveedoresCount }}</h2>
          <p class="text-sm text-gray-600">Con pedidos pendientes: {{ proveedoresPedidosPendientesCount }}</p>
        </div>
      </PanLink>

      <!-- Citas -->
      <PanLink href="/citas" class="group bg-white p-6 rounded-2xl shadow-lg border border-gray-200 transition-all transform hover:scale-105 hover:shadow-xl text-center flex flex-col items-center justify-center h-full">
        <div class="flex flex-col items-center justify-center space-y-2">
          <FontAwesomeIcon :icon="['fas', 'calendar-alt']" class="h-10 w-10 text-orange-600 group-hover:text-orange-700 transition-colors" />
          <h2 class="text-xl font-bold text-gray-900">Citas {{ citasCount }}</h2>
          <p class="text-sm text-gray-600">Para hoy: {{ citasHoyCount }}</p>
        </div>
      </PanLink>
    </div>

    <!-- Alerta de Stock Bajo -->
    <div v-if="productosBajoStockNombres.length > 0" class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-8">
      <div class="bg-white p-6 rounded-2xl shadow-lg border-l-8 border-red-500 flex flex-col justify-between items-start text-left md:col-span-1">
        <div class="w-full">
          <div class="flex items-center mb-4">
            <FontAwesomeIcon :icon="['fas', 'exclamation-triangle']" class="h-8 w-8 text-red-600 mr-3" />
            <h3 class="text-2xl font-extrabold text-gray-900">Alerta de Stock Bajo</h3>
          </div>
          <p class="text-base text-gray-700 mb-4">
            Actualmente tienes <strong>{{ productosBajoStockNombres.length }} producto(s) con stock críticamente bajo.</strong>
            Considera reponerlos pronto para evitar interrupciones.
          </p>
          <h4 class="text-lg font-bold text-gray-800 mb-2">Productos afectados:</h4>
          <ul class="text-gray-700 space-y-1 list-none">
            <li v-for="productoNombre in productosBajoStockNombres" :key="productoNombre" class="text-base">
              {{ productoNombre }}
            </li>
          </ul>
        </div>
        <PanLink href="/productos?stock=low" class="mt-6 px-6 py-2 bg-red-500 text-white font-semibold rounded-lg shadow hover:bg-red-600 transition-colors duration-300 transform hover:scale-105 list-none">
          Gestionar Inventario
          <FontAwesomeIcon :icon="['fas', 'arrow-right']" class="ml-2" />
        </PanLink>
      </div>
    </div>

    <!-- Tarjeta: Citas del día de hoy -->
    <div v-if="citasHoyDetalles.length > 0" class="mt-8 bg-white p-6 rounded-2xl shadow-lg border-l-8 border-blue-500">
  <div class="flex items-center mb-4">
    <FontAwesomeIcon :icon="['fas', 'calendar-alt']" class="h-8 w-8 text-blue-600 mr-3" />
    <h3 class="text-2xl font-extrabold text-gray-900">Citas del día de hoy</h3>
  </div>
  <p class="text-base text-gray-700 mb-4">
    Tienes <strong>{{ citasHoyDetalles.length }} cita(s)</strong> programadas para hoy.
  </p>
  <ul class="space-y-2">
    <li v-for="cita in citasHoyDetalles" :key="cita.id" class="flex items-center justify-between text-gray-800 bg-gray-50 p-3 rounded-md shadow-sm">
      <div class="flex flex-col text-left"> <div class="font-semibold text-lg text-gray-900">Solicitud de Trabajo: {{ cita.titulo }}</div>
        <div class="text-sm text-gray-700">Cliente: {{ cita.cliente }}</div>
        <div class="text-sm text-gray-700">Técnico: {{ cita.tecnico }}</div>
      </div>
      <div class="text-base font-medium text-blue-600">Hora {{ cita.hora }}</div>
    </li>
  </ul>
  <PanLink href="/citas" class="mt-6 inline-block px-5 py-2 bg-blue-500 text-white font-semibold rounded-lg shadow hover:bg-blue-600 transition-colors">
    Ver todas las citas
    <FontAwesomeIcon :icon="['fas', 'arrow-right']" class="ml-2" />
  </PanLink>
</div>

    <!-- Actividad Reciente -->
    <div class="mt-8 bg-white p-6 rounded-2xl shadow-lg border border-gray-200">
      <h3 class="text-xl font-bold text-gray-900 mb-4">Actividad Reciente</h3>
      <ul class="space-y-2">
        <li v-for="(activity, index) in recentActivity" :key="index" class="flex items-center text-gray-700">
          <FontAwesomeIcon :icon="activity.icon" class="text-gray-500 mr-2" />
          <span v-html="activity.text"></span> <span class="text-sm text-gray-500">({{ activity.timestamp }})</span>
        </li>
      </ul>
    </div>
  </div>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import PanLink from '@/Components/PanLink.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { library } from '@fortawesome/fontawesome-svg-core';
import {
  faUsers,
  faBoxOpen,
  faTruck,
  faCalendarAlt,
  faExclamationTriangle,
  faUserPlus,
  faBox,
  faCalendarCheck,
  faArrowRight
} from '@fortawesome/free-solid-svg-icons';

library.add(
  faUsers,
  faBoxOpen,
  faTruck,
  faCalendarAlt,
  faExclamationTriangle,
  faUserPlus,
  faBox,
  faCalendarCheck,
  faArrowRight
);

defineOptions({ layout: AppLayout });

const props = defineProps({
  clientesCount: { type: Number, default: 0 },
  clientesNuevosCount: { type: Number, default: 0 },
  productosCount: { type: Number, default: 0 },
  productosBajoStockCount: { type: Number, default: 0 },
  productosBajoStockNombres: {
    type: Array,
    default: () => []
  },
  proveedoresCount: { type: Number, default: 0 },
  proveedoresPedidosPendientesCount: { type: Number, default: 0 },
  citasCount: { type: Number, default: 0 },
  citasHoyCount: { type: Number, default: 0 },
  citasHoyDetalles: {
    type: Array,
    default: () => []
  },
  recentActivity: {
    type: Array,
    default: () => []
  }
});
</script>
