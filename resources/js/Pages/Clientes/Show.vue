<template>
  <Head :title="`Cliente: ${cliente.nombre_razon_social}`" />
  <div class="max-w-4xl mx-auto p-4">
    <div class="bg-white shadow-sm rounded-lg p-6">
      <!-- Header -->
      <div class="flex items-start justify-between mb-6">
        <div>
          <h1 class="text-2xl font-semibold text-gray-800">Detalles del Cliente</h1>
          <p class="text-sm text-gray-600 mt-1">{{ cliente.nombre_razon_social }}</p>
          <div class="mt-2">
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800" v-if="cliente.activo">
              Activo
            </span>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800" v-else>
              Inactivo
            </span>
          </div>
        </div>
        <div class="flex space-x-3">
          <Link
            :href="route('clientes.edit', cliente.id)"
            class="inline-flex items-center px-4 py-2 text-sm font-medium text-blue-700 bg-blue-50 border border-blue-200 rounded-md hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
            </svg>
            Editar
          </Link>
          <Link
            :href="route('clientes.index')"
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
            <label class="block text-sm font-medium text-gray-700 mb-1">Nombre/Razón Social</label>
            <p class="text-gray-900">{{ cliente.nombre_razon_social }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <p class="text-gray-900">{{ cliente.email }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
            <p class="text-gray-900" v-if="cliente.telefono">{{ cliente.telefono }}</p>
            <p class="text-gray-500 italic" v-else>Sin teléfono</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Persona</label>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                  :class="cliente.tipo_persona === 'fisica' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800'">
              {{ cliente.tipo_persona_nombre }}
            </span>
          </div>
          <div v-if="cliente.notas">
            <label class="block text-sm font-medium text-gray-700 mb-1">Notas</label>
            <p class="text-gray-900 whitespace-pre-wrap">{{ cliente.notas }}</p>
          </div>
        </div>
      </section>

      <!-- Información Fiscal -->
      <section class="border-b border-gray-200 pb-6 mb-6">
        <h2 class="text-lg font-medium text-gray-900 mb-4">Información Fiscal</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">RFC</label>
            <p class="text-gray-900 font-mono">{{ cliente.rfc }}</p>
          </div>
          <div v-if="cliente.curp">
            <label class="block text-sm font-medium text-gray-700 mb-1">CURP</label>
            <p class="text-gray-900 font-mono">{{ cliente.curp }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Régimen Fiscal</label>
            <p class="text-gray-900">{{ cliente.regimen_fiscal }} - {{ cliente.regimen_fiscal_nombre }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Uso CFDI</label>
            <p class="text-gray-900">{{ cliente.uso_cfdi }} - {{ cliente.uso_cfdi_nombre }}</p>
          </div>
          <div v-if="cliente.cfdi_default_use">
            <label class="block text-sm font-medium text-gray-700 mb-1">Uso CFDI Predeterminado</label>
            <p class="text-gray-900">{{ cliente.cfdi_default_use }}</p>
          </div>
          <div v-if="cliente.payment_form_default">
            <label class="block text-sm font-medium text-gray-700 mb-1">Forma de Pago Predeterminada</label>
            <p class="text-gray-900">{{ cliente.payment_form_default }}</p>
          </div>
          <div v-if="cliente.facturapi_customer_id">
            <label class="block text-sm font-medium text-gray-700 mb-1">ID en Facturapi</label>
            <p class="text-gray-900 font-mono">{{ cliente.facturapi_customer_id }}</p>
          </div>
        </div>
      </section>

      <!-- Dirección -->
      <section class="border-b border-gray-200 pb-6 mb-6">
        <h2 class="text-lg font-medium text-gray-900 mb-4">Dirección</h2>
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Dirección Completa</label>
            <p class="text-gray-900">{{ cliente.direccion_completa }}</p>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Calle</label>
              <p class="text-gray-900">{{ cliente.calle }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Números</label>
              <p class="text-gray-900">
                Ext: {{ cliente.numero_exterior }}
                <span v-if="cliente.numero_interior"> | Int: {{ cliente.numero_interior }}</span>
              </p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Colonia, Municipio</label>
              <p class="text-gray-900">{{ cliente.colonia }}, {{ cliente.municipio }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Código Postal</label>
              <p class="text-gray-900">{{ cliente.codigo_postal }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
              <p class="text-gray-900">{{ cliente.estado }} - {{ cliente.estado_nombre }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">País</label>
              <p class="text-gray-900">{{ cliente.pais }}</p>
            </div>
          </div>
        </div>
      </section>

      <!-- Estadísticas Relacionadas -->
      <section>
        <h2 class="text-lg font-medium text-gray-900 mb-4">Relacionado</h2>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
          <div class="bg-gray-50 p-4 rounded-lg">
            <h3 class="text-sm font-medium text-gray-500">Cotizaciones</h3>
            <p class="text-2xl font-bold text-gray-900">{{ cliente.cotizaciones_count || 0 }}</p>
          </div>
          <div class="bg-blue-50 p-4 rounded-lg">
            <h3 class="text-sm font-medium text-blue-700">Ventas</h3>
            <p class="text-2xl font-bold text-blue-900">{{ cliente.ventas_count || 0 }}</p>
          </div>
          <div class="bg-green-50 p-4 rounded-lg">
            <h3 class="text-sm font-medium text-green-700">Pedidos</h3>
            <p class="text-2xl font-bold text-green-900">{{ cliente.pedidos_count || 0 }}</p>
          </div>
          <div class="bg-purple-50 p-4 rounded-lg">
            <h3 class="text-sm font-medium text-purple-700">Facturas</h3>
            <p class="text-2xl font-bold text-purple-900">{{ cliente.facturas_count || 0 }}</p>
          </div>
        </div>
      </section>

      <!-- Debug (desarrollo) -->
      <div v-if="isDevelopment" class="mt-6 p-4 bg-gray-50 rounded-md text-xs">
        <h3 class="font-semibold mb-2">Debug: Cliente ID {{ cliente.id }}</h3>
        <pre class="text-xs overflow-auto">{{ JSON.stringify(cliente, null, 2) }}</pre>
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
  cliente: {
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
const cliente = computed(() => ({
  ...props.cliente,
  cotizaciones_count: props.cliente.cotizaciones_count || 0,
  ventas_count: props.cliente.ventas_count || 0,
  pedidos_count: props.cliente.pedidos_count || 0,
  facturas_count: props.cliente.facturas_count || 0,
  direccion_completa: props.cliente.direccion_completa || `${props.cliente.calle} ${props.cliente.numero_exterior}${props.cliente.numero_interior ? ' Int. ' + props.cliente.numero_interior : ''}, ${props.cliente.colonia}, ${props.cliente.codigo_postal} ${props.cliente.municipio}, ${props.cliente.estado} ${props.cliente.pais}`
}))
</script>

<style scoped>
/* Estilos opcionales para mejorar layout */
section + section { margin-top: 2rem; }
</style>
