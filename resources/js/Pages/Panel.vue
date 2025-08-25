<template>
  <Head title="Panel" />

  <div class="container mx-auto px-6 py-10">
    <!-- Tarjetas de Resumen -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
      <!-- Clientes -->
      <PanLink
        :href="clientesHref"
        class="group bg-white p-6 rounded-2xl shadow-lg border border-gray-200 transition-all transform hover:scale-105 hover:shadow-xl text-center flex flex-col items-center justify-center h-full"
        aria-label="Ir a clientes"
      >
        <div class="flex flex-col items-center justify-center space-y-2">
          <FontAwesomeIcon :icon="['fas', 'users']" class="h-10 w-10 text-blue-600 group-hover:text-blue-700 transition-colors" />
          <h2 class="text-xl font-bold text-gray-900">
            Clientes {{ n(clientesCount) }}
          </h2>
          <p class="text-sm text-gray-600">
            Nuevos este mes: {{ n(clientesNuevosCount) }}
          </p>
        </div>
      </PanLink>

      <!-- Productos -->
      <PanLink
        :href="productosHref"
        class="group bg-white p-6 rounded-2xl shadow-lg border border-gray-200 transition-all transform hover:scale-105 hover:shadow-xl text-center flex flex-col items-center justify-center h-full"
        aria-label="Ir a productos"
      >
        <div class="flex flex-col items-center justify-center space-y-2">
          <!-- Usa 'box' que sí está cargado globalmente en tu app.js -->
          <FontAwesomeIcon :icon="['fas', 'box']" class="h-10 w-10 text-green-600 group-hover:text-green-700 transition-colors" />
          <h2 class="text-xl font-bold text-gray-900">
            Productos {{ n(productosCount) }}
          </h2>
          <p class="text-sm text-gray-600">
            Bajo stock: {{ n(productosBajoStockCount) }}
          </p>
        </div>
      </PanLink>

      <!-- Proveedores -->
      <PanLink
        :href="proveedoresHref"
        class="group bg-white p-6 rounded-2xl shadow-lg border border-gray-200 transition-all transform hover:scale-105 hover:shadow-xl text-center flex flex-col items-center justify-center h-full"
        aria-label="Ir a proveedores"
      >
        <div class="flex flex-col items-center justify-center space-y-2">
          <FontAwesomeIcon :icon="['fas', 'truck']" class="h-10 w-10 text-purple-600 group-hover:text-purple-700 transition-colors" />
          <h2 class="text-xl font-bold text-gray-900">
            Proveedores {{ n(proveedoresCount) }}
          </h2>
          <p class="text-sm text-gray-600">
            Con pedidos pendientes: {{ n(proveedoresPedidosPendientesCount) }}
          </p>
        </div>
      </PanLink>

      <!-- Citas -->
      <PanLink
        :href="citasHref"
        class="group bg-white p-6 rounded-2xl shadow-lg border border-gray-200 transition-all transform hover:scale-105 hover:shadow-xl text-center flex flex-col items-center justify-center h-full"
        aria-label="Ir a citas"
      >
        <div class="flex flex-col items-center justify-center space-y-2">
          <FontAwesomeIcon :icon="['fas', 'calendar-alt']" class="h-10 w-10 text-orange-600 group-hover:text-orange-700 transition-colors" />
          <h2 class="text-xl font-bold text-gray-900">
            Citas {{ n(citasCount) }}
          </h2>
          <p class="text-sm text-gray-600">
            Para hoy: {{ n(citasHoyCount) }}
          </p>
        </div>
      </PanLink>
    </div>

    <!-- Sección de Alertas -->
    <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-8">
      <!-- Alerta de Stock Bajo -->
      <div
        v-if="productosBajoStockNombresSafe.length > 0"
        class="bg-white p-6 rounded-2xl shadow-lg border-l-8 border-red-500 flex flex-col justify-between items-start text-left"
      >
        <div class="w-full">
          <div class="flex items-center mb-4">
            <FontAwesomeIcon :icon="['fas', 'exclamation-triangle']" class="h-8 w-8 text-red-600 mr-3" />
            <h3 class="text-2xl font-extrabold text-gray-900">Alerta de Stock Bajo</h3>
          </div>
          <p class="text-base text-gray-700 mb-4">
            Actualmente tienes
            <strong>{{ n(productosBajoStockNombresSafe.length) }} producto(s) con stock críticamente bajo.</strong>
            Considera reponerlos pronto para evitar interrupciones.
          </p>
          <h4 class="text-lg font-bold text-gray-800 mb-2">Productos afectados:</h4>
          <ul class="text-gray-700 space-y-1 list-none">
            <li
              v-for="(productoNombre, i) in productosBajoStockNombresSafe"
              :key="`low-${i}-${productoNombre}`"
              class="text-base"
            >
              {{ productoNombre }}
            </li>
          </ul>
        </div>
        <PanLink
          :href="productosLowHref"
          class="mt-6 px-6 py-2 bg-red-500 text-white font-semibold rounded-lg shadow hover:bg-red-600 transition-colors duration-300 transform hover:scale-105 list-none"
          aria-label="Gestionar inventario bajo en stock"
        >
          Gestionar Inventario
          <FontAwesomeIcon :icon="['fas', 'arrow-right']" class="ml-2" />
        </PanLink>
      </div>

      <!-- Alerta de Órdenes de Compra Pendientes -->
      <div
        v-if="proveedoresPedidosPendientesCount > 0"
        class="bg-white p-6 rounded-2xl shadow-lg border-l-8 border-amber-500 flex flex-col justify-between items-start text-left"
      >
        <div class="w-full">
          <div class="flex items-center mb-4">
            <FontAwesomeIcon :icon="['fas', 'clipboard-list']" class="h-8 w-8 text-amber-600 mr-3" />
            <h3 class="text-2xl font-extrabold text-gray-900">Órdenes de Compra Pendientes</h3>
          </div>
          <p class="text-base text-gray-700 mb-4">
            Tienes <strong>{{ n(proveedoresPedidosPendientesCount) }} orden(es) de compra pendientes</strong> con proveedores.
          </p>

          <h4 class="text-lg font-bold text-gray-800 mb-2">Detalles de órdenes:</h4>
          <ul class="text-gray-700 space-y-2 list-none">
            <li
              v-for="orden in ordenesPendientesDetallesSafe"
              :key="`oc-${orden.id ?? orden.proveedor ?? Math.random()}`"
              class="text-base bg-gray-50 p-3 rounded-md"
            >
              <div class="font-medium">{{ orden.proveedor ?? 'Proveedor N/D' }}</div>
              <div class="text-sm text-gray-600">Total: ${{ money(orden.total) }}</div>
              <div class="text-sm text-gray-600">Fecha esperada: {{ orden.fecha_recepcion ?? 'N/D' }}</div>
            </li>
          </ul>
        </div>
        <PanLink
          :href="ordenesPendientesHref"
          class="mt-6 px-6 py-2 bg-amber-500 text-white font-semibold rounded-lg shadow hover:bg-amber-600 transition-colors duration-300 transform hover:scale-105 list-none"
          aria-label="Ver órdenes de compra pendientes"
        >
          Ver Órdenes Pendientes
          <FontAwesomeIcon :icon="['fas', 'arrow-right']" class="ml-2" />
        </PanLink>
      </div>
    </div>

    <!-- Citas del día de hoy -->
    <div
      v-if="citasHoyDetallesSafe.length > 0"
      class="mt-8 bg-white p-6 rounded-2xl shadow-lg border-l-8 border-blue-500"
    >
      <div class="flex items-center mb-4">
        <FontAwesomeIcon :icon="['fas', 'calendar-alt']" class="h-8 w-8 text-blue-600 mr-3" />
        <h3 class="text-2xl font-extrabold text-gray-900">Citas del día de hoy</h3>
      </div>
      <p class="text-base text-gray-700 mb-4">
        Tienes <strong>{{ n(citasHoyDetallesSafe.length) }} cita(s)</strong> programadas para hoy.
      </p>
      <ul class="space-y-2">
        <li
          v-for="cita in citasHoyDetallesSafe"
          :key="`cita-${cita.id ?? cita.titulo ?? Math.random()}`"
          class="flex items-center justify-between text-gray-800 bg-gray-50 p-3 rounded-md shadow-sm"
        >
          <div class="flex flex-col text-left">
            <div class="font-semibold text-lg text-gray-900">
              Solicitud de Trabajo: {{ cita.titulo ?? 'Sin título' }}
            </div>
            <div class="text-sm text-gray-700">Cliente: {{ cita.cliente ?? 'N/D' }}</div>
            <div class="text-sm text-gray-700">Técnico: {{ cita.tecnico ?? 'N/D' }}</div>
          </div>
          <div class="text-base font-medium text-blue-600">
            Hora {{ cita.hora ?? '—' }}
          </div>
        </li>
      </ul>
      <PanLink
        :href="citasHref"
        class="mt-6 inline-block px-5 py-2 bg-blue-500 text-white font-semibold rounded-lg shadow hover:bg-blue-600 transition-colors"
        aria-label="Ver todas las citas"
      >
        Ver todas las citas
        <FontAwesomeIcon :icon="['fas', 'arrow-right']" class="ml-2" />
      </PanLink>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { Head } from '@inertiajs/vue3'
import PanLink from '@/Components/PanLink.vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'

defineOptions({ layout: AppLayout })

// ✅ Props con defaults seguros
const props = defineProps({
  clientesCount: { type: Number, default: 0 },
  clientesNuevosCount: { type: Number, default: 0 },

  productosCount: { type: Number, default: 0 },
  productosBajoStockCount: { type: Number, default: 0 },
  productosBajoStockNombres: { type: Array, default: () => [] },

  proveedoresCount: { type: Number, default: 0 },
  proveedoresPedidosPendientesCount: { type: Number, default: 0 },
  ordenesPendientesDetalles: { type: Array, default: () => [] },

  citasCount: { type: Number, default: 0 },
  citasHoyCount: { type: Number, default: 0 },
  citasHoyDetalles: { type: Array, default: () => [] }
})

// ===== Utilidades de formato (evita repetir lógica)
const n = (val) => Number(val || 0).toLocaleString('es-MX')
const money = (val) => {
  const num = Number(val)
  return Number.isFinite(num)
    ? num.toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
    : '0.00'
}

// ===== Fallbacks defensivos
const productosBajoStockNombresSafe = computed(() =>
  Array.isArray(props.productosBajoStockNombres) ? props.productosBajoStockNombres : []
)
const ordenesPendientesDetallesSafe = computed(() =>
  Array.isArray(props.ordenesPendientesDetalles) ? props.ordenesPendientesDetalles : []
)
const citasHoyDetallesSafe = computed(() =>
  Array.isArray(props.citasHoyDetalles) ? props.citasHoyDetalles : []
)

// ===== Rutas (usa lo que tengas; si tienes Ziggy, podrías usar route('nombre'))
const clientesHref = '/clientes'
const productosHref = '/productos'
const productosLowHref = '/productos?stock=low'
const proveedoresHref = '/proveedores'
const ordenesPendientesHref = '/ordenes-compra?estado=pendiente'
const citasHref = '/citas'
</script>
