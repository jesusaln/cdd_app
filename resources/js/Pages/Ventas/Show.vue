<template>
    <Head title="Mostrar Venta" />
    <div class="ventas-show">
      <!-- Título de la página -->
      <h1 class="text-2xl font-semibold mb-6">Detalles de la Venta</h1>

      <!-- Verificar si venta no es null -->
      <div v-if="venta" class="bg-white rounded-lg shadow-md p-6">
        <div class="mb-4">
          <h2 class="text-lg font-medium text-gray-700">Cliente</h2>
          <p>{{ venta.cliente.nombre_razon_social }}</p>
        </div>
        <div class="mb-4">
          <h2 class="text-lg font-medium text-gray-700">Productos</h2>
          <ul>
            <li v-for="producto in venta.productos" :key="producto.id" class="mb-2">
              <strong>{{ producto.nombre }}</strong> - ${{ producto.pivot.precio }} (Cantidad: {{ producto.pivot.cantidad }})
            </li>
          </ul>
        </div>
        <div class="mb-4">
          <h2 class="text-lg font-medium text-gray-700">Total</h2>
          <p>${{ venta.total }}</p>
        </div>
        <div class="mb-4">
          <h2 class="text-lg font-medium text-gray-700">Estado</h2>
          <p>{{ venta.estado }}</p>
        </div>

        <!-- Información de Pago -->
        <div class="mb-4">
          <h2 class="text-lg font-medium text-gray-700">Estado de Pago</h2>
          <div class="flex items-center space-x-4">
            <span :class="{
              'bg-green-100 text-green-800': venta.pagado,
              'bg-red-100 text-red-800': !venta.pagado
            }" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium">
              {{ venta.pagado ? 'Pagado' : 'Pendiente' }}
            </span>
            <span v-if="venta.pagado" class="text-sm text-gray-600">
              Pagado el {{ formatearFecha(venta.fecha_pago) }}
            </span>
          </div>
        </div>

        <!-- Información detallada si está pagada -->
        <div v-if="venta.pagado" class="mb-4">
          <h3 class="text-md font-medium text-gray-700 mb-2">Detalles del Pago</h3>
          <div class="bg-green-50 p-4 rounded-lg">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-4">
              <div>
                <dt class="text-sm font-medium text-gray-500">Método de Pago</dt>
                <dd class="mt-1">
                  <span :class="getMetodoPagoClass(venta.metodo_pago)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                    {{ getMetodoPagoLabel(venta.metodo_pago) }}
                  </span>
                </dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">Pagado Por</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ venta.pagado_por_usuario?.name || 'Usuario no encontrado' }}</dd>
              </div>
            </div>
            <div v-if="venta.notas_pago" class="mt-4">
              <dt class="text-sm font-medium text-gray-500">Notas de Pago</dt>
              <dd class="mt-1 text-sm text-gray-700 bg-white p-3 rounded border">{{ venta.notas_pago }}</dd>
            </div>
          </div>
        </div>
      </div>
      <div v-else>
        <p>Cargando detalles de la venta...</p>
      </div>

      <!-- Botones de acción -->
      <div v-if="venta" class="mt-6 flex flex-wrap gap-2">
        <Link :href="route('ventas.edit', venta.id)" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
          Editar
        </Link>

        <!-- Botones de pago -->
        <div v-if="!venta.pagado" class="flex space-x-2">
          <button @click="showPagoParcialModal = true" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
            Registrar Pago Parcial
          </button>
          <button @click="showPagoModal = true" class="bg-purple-500 text-white px-4 py-2 rounded hover:bg-purple-600">
            Marcar como Pagado
          </button>
        </div>

        <button @click="eliminarVenta(venta.id)" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
          Eliminar
        </button>
      </div>

      <!-- Spinner de carga -->
      <div v-if="loading" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500"></div>
      </div>

      <!-- Modal para Pago Parcial -->
      <div v-if="showPagoParcialModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" @click.self="showPagoParcialModal = false">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md max-h-[90vh] overflow-y-auto">
          <div class="flex items-center justify-between p-6 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Registrar Pago Parcial</h3>
            <button @click="showPagoParcialModal = false" class="text-gray-400 hover:text-gray-600 transition-colors">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <div class="p-6">
            <div class="space-y-4">
              <div class="bg-gray-50 p-4 rounded-lg">
                <div class="flex justify-between items-center">
                  <span class="text-sm font-medium text-gray-700">Venta:</span>
                  <span class="text-sm text-gray-900">{{ venta.numero_venta }}</span>
                </div>
                <div class="flex justify-between items-center mt-2">
                  <span class="text-sm font-medium text-gray-700">Cliente:</span>
                  <span class="text-sm text-gray-900">{{ venta.cliente.nombre_razon_social }}</span>
                </div>
                <div class="flex justify-between items-center mt-2">
                  <span class="text-sm font-medium text-gray-700">Monto Total:</span>
                  <span class="text-lg font-bold text-gray-900">{{ formatCurrency(venta.total) }}</span>
                </div>
                <div class="flex justify-between items-center mt-2">
                  <span class="text-sm font-medium text-gray-700">Pendiente:</span>
                  <span class="text-lg font-bold text-red-600">{{ formatCurrency(venta.total - (venta.monto_pagado || 0)) }}</span>
                </div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Monto del Pago *</label>
                <input
                  v-model="montoPagoParcial"
                  type="number"
                  step="0.01"
                  min="0.01"
                  :max="venta.total - (venta.monto_pagado || 0)"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  placeholder="0.00"
                />
                <p class="text-xs text-gray-500 mt-1">
                  Máximo: {{ formatCurrency(venta.total - (venta.monto_pagado || 0)) }}
                </p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Notas del Pago (opcional)</label>
                <textarea
                  v-model="notasPagoParcial"
                  rows="3"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  placeholder="Agregar notas sobre este pago parcial..."
                ></textarea>
              </div>
            </div>
          </div>

          <div class="flex justify-end gap-3 px-6 py-4 border-t border-gray-200 bg-gray-50">
            <button @click="showPagoParcialModal = false" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors">
              Cancelar
            </button>
            <button
              @click="confirmarPagoParcial"
              :disabled="!montoPagoParcial || isNaN(parseFloat(montoPagoParcial)) || parseFloat(montoPagoParcial) <= 0"
              class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
            >
              Registrar Pago
            </button>
          </div>
        </div>
      </div>

      <!-- Modal para Marcar como Pagado -->
      <div v-if="showPagoModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" @click.self="showPagoModal = false">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md max-h-[90vh] overflow-y-auto">
          <div class="flex items-center justify-between p-6 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Marcar Venta como Pagada</h3>
            <button @click="showPagoModal = false" class="text-gray-400 hover:text-gray-600 transition-colors">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <div class="p-6">
            <div class="space-y-4">
              <div class="bg-gray-50 p-4 rounded-lg">
                <div class="flex justify-between items-center">
                  <span class="text-sm font-medium text-gray-700">Venta:</span>
                  <span class="text-sm text-gray-900">{{ venta.numero_venta }}</span>
                </div>
                <div class="flex justify-between items-center mt-2">
                  <span class="text-sm font-medium text-gray-700">Cliente:</span>
                  <span class="text-sm text-gray-900">{{ venta.cliente.nombre_razon_social }}</span>
                </div>
                <div class="flex justify-between items-center mt-2">
                  <span class="text-sm font-medium text-gray-700">Monto Total:</span>
                  <span class="text-lg font-bold text-gray-900">{{ formatCurrency(venta.total) }}</span>
                </div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Método de Pago *</label>
                <select
                  v-model="metodoPago"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  required
                >
                  <option value="">Seleccionar método de pago</option>
                  <option value="efectivo">Efectivo</option>
                  <option value="transferencia">Transferencia</option>
                  <option value="cheque">Cheque</option>
                  <option value="tarjeta">Tarjeta</option>
                  <option value="otros">Otros</option>
                </select>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Notas de Pago (opcional)</label>
                <textarea
                  v-model="notasPago"
                  rows="3"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  placeholder="Agregar notas sobre el pago..."
                ></textarea>
              </div>
            </div>
          </div>

          <div class="flex justify-end gap-3 px-6 py-4 border-t border-gray-200 bg-gray-50">
            <button @click="showPagoModal = false" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors">
              Cancelar
            </button>
            <button
              @click="confirmarPago"
              :disabled="!metodoPago"
              class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
            >
              Marcar como Pagado
            </button>
          </div>
        </div>
      </div>
    </div>
  </template>

  <script setup>
  import { Head, Link, router } from '@inertiajs/vue3';
  import { ref, defineProps } from 'vue';
  import { Notyf } from 'notyf';
  import 'notyf/notyf.min.css';

  // Propiedades
  const props = defineProps({ venta: Object });
  const loading = ref(false);

  // Configuración de Notyf para notificaciones
  const notyf = new Notyf({ duration: 3000, position: { x: 'right', y: 'top' } });

  // Función para eliminar una venta
  const eliminarVenta = async (id) => {
    loading.value = true;
    if (confirm('¿Estás seguro de que deseas eliminar esta venta?')) {
      try {
        await router.delete(`/ventas/${id}`, {
          onSuccess: () => {
            notyf.success('Venta eliminada exitosamente.');
            router.visit(route('ventas.index'));  // Regresar a la lista de ventas
          },
          onError: () => notyf.error('Error al eliminar la venta.')
        });
      } catch (error) {
        notyf.error('Ocurrió un error inesperado.');
      } finally {
        loading.value = false;
      }
    }
  };
  </script>

  <style scoped>
  /* Aquí van tus estilos personalizados */
  </style>
