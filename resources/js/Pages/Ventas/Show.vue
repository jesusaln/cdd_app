<template>
  <Head title="Detalles de Venta" />
  <div class="min-h-screen bg-gray-50 py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-5xl mx-auto">
      <!-- Header -->
      <div class="mb-6">
        <Link :href="route('ventas.index')" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 mb-4">
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
          Volver a Ventas
        </Link>
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Detalles de Venta</h1>
            <p class="text-sm text-gray-500 mt-1">#{{ venta.id }}</p>
          </div>
          <div class="flex items-center space-x-2">
            <span :class="getEstadoClass(venta.estado)" class="px-3 py-1 rounded-full text-sm font-medium">
              {{ getEstadoLabel(venta.estado) }}
            </span>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Columna Principal -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Información de Venta -->
          <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
              <h2 class="text-lg font-semibold text-gray-900">N° Venta/Factura: {{ venta.numero_venta }}</h2>
            </div>
            <div class="px-6 py-4">
              <dl class="grid grid-cols-2 gap-4">
                <div>
                  <dt class="text-sm font-medium text-gray-500">Cliente</dt>
                  <dd class="mt-1 text-sm text-gray-900 font-medium">{{ venta.cliente.nombre_razon_social }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">Email</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ venta.cliente.email || 'N/A' }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">Fecha</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ formatearFecha(venta.fecha) }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">Estado de Pago</dt>
                  <dd class="mt-1">
                    <span :class="venta.pagado ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" 
                          class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                      {{ venta.pagado ? 'Pagado' : 'Pendiente' }}
                    </span>
                  </dd>
                </div>
              </dl>
            </div>
          </div>

          <!-- Productos y Servicios -->
          <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
              <h2 class="text-lg font-semibold text-gray-900">Productos y Servicios</h2>
              <p class="text-sm text-gray-500 mt-1">{{ venta.productos.length }} items</p>
            </div>
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Cantidad</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Precio</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Descuento</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Subtotal</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <template v-for="producto in venta.productos" :key="producto.id">
                    <tr class="hover:bg-gray-50">
                      <td class="px-6 py-4">
                        <div class="text-sm font-medium text-gray-900">{{ producto.nombre }}</div>
                        <div v-if="producto.requiere_serie && producto.series.length > 0" class="mt-1">
                          <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
                            </svg>
                            {{ producto.series.length }} serie(s)
                          </span>
                        </div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <span :class="producto.tipo === 'producto' ? 'bg-purple-100 text-purple-800' : 'bg-green-100 text-green-800'" 
                              class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize">
                          {{ producto.tipo }}
                        </span>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900">{{ producto.pivot.cantidad }}</td>
                      <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900">{{ formatCurrency(producto.pivot.precio) }}</td>
                      <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900">{{ producto.pivot.descuento }}%</td>
                      <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-gray-900">
                        {{ formatCurrency(producto.pivot.cantidad * producto.pivot.precio * (1 - producto.pivot.descuento / 100)) }}
                      </td>
                    </tr>
                    <!-- Series Row -->
                    <tr v-if="producto.requiere_serie && producto.series.length > 0" class="bg-blue-50">
                      <td colspan="6" class="px-6 py-3">
                        <div class="text-xs font-medium text-blue-900 mb-2">Series vendidas:</div>
                        <div class="flex flex-wrap gap-2">
                          <div v-for="(serie, idx) in producto.series" :key="idx" 
                               class="inline-flex items-center bg-white border border-blue-200 rounded-md px-3 py-1.5">
                            <svg class="w-4 h-4 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
                            </svg>
                            <span class="text-sm font-mono font-medium text-gray-900">{{ serie.numero_serie }}</span>
                            <span class="ml-2 text-xs text-gray-500">• {{ serie.almacen }}</span>
                          </div>
                        </div>
                      </td>
                    </tr>
                  </template>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Columna Lateral -->
        <div class="space-y-6">
          <!-- Resumen de Totales -->
          <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
              <h2 class="text-lg font-semibold text-gray-900">Resumen</h2>
            </div>
            <div class="px-6 py-4 space-y-3">
              <div class="flex justify-between text-sm">
                <span class="text-gray-600">Subtotal</span>
                <span class="font-medium text-gray-900">{{ formatCurrency(venta.subtotal) }}</span>
              </div>
              <div v-if="venta.descuento_general > 0" class="flex justify-between text-sm">
                <span class="text-gray-600">Descuento General</span>
                <span class="font-medium text-red-600">-{{ formatCurrency(venta.descuento_general) }}</span>
              </div>
              <div class="flex justify-between text-sm">
                <span class="text-gray-600">IVA</span>
                <span class="font-medium text-gray-900">{{ formatCurrency(venta.iva) }}</span>
              </div>
              <div class="pt-3 border-t border-gray-200">
                <div class="flex justify-between">
                  <span class="text-base font-semibold text-gray-900">Total</span>
                  <span class="text-xl font-bold text-gray-900">{{ formatCurrency(venta.total) }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Información de Pago -->
          <div v-if="venta.pagado" class="bg-green-50 rounded-lg border border-green-200 p-4">
            <div class="flex items-center mb-3">
              <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <h3 class="text-sm font-semibold text-green-900">Pago Completado</h3>
            </div>
            <dl class="space-y-2">
              <div>
                <dt class="text-xs text-green-700">Método de Pago</dt>
                <dd class="text-sm font-medium text-green-900 capitalize">{{ venta.metodo_pago || 'N/A' }}</dd>
              </div>
              <div v-if="venta.fecha_pago">
                <dt class="text-xs text-green-700">Fecha de Pago</dt>
                <dd class="text-sm font-medium text-green-900">{{ formatearFecha(venta.fecha_pago) }}</dd>
              </div>
              <div v-if="venta.notas_pago">
                <dt class="text-xs text-green-700">Notas</dt>
                <dd class="text-sm text-green-800 mt-1">{{ venta.notas_pago }}</dd>
              </div>
            </dl>
          </div>

          <!-- Auditoría -->
          <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
              <h2 class="text-lg font-semibold text-gray-900">Auditoría</h2>
            </div>
            <div class="px-6 py-4 space-y-3 text-sm">
              <div>
                <dt class="text-xs font-medium text-gray-500">Creado</dt>
                <dd class="mt-1 text-gray-900">{{ formatearFechaHora(venta.created_at) }}</dd>
              </div>
              <div>
                <dt class="text-xs font-medium text-gray-500">Última Actualización</dt>
                <dd class="mt-1 text-gray-900">{{ formatearFechaHora(venta.updated_at) }}</dd>
              </div>
            </div>
          </div>

          <!-- Acciones -->
          <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 space-y-2">
            <Link :href="route('ventas.pdf', venta.id)" target="_blank" 
                  class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
              </svg>
              Descargar PDF
            </Link>
            <Link :href="route('ventas.ticket', venta.id)" target="_blank" 
                  class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
              </svg>
              Imprimir Ticket
            </Link>
            <Link v-if="canEdit" :href="route('ventas.edit', venta.id)" 
                  class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
              </svg>
              Editar Venta
            </Link>
            <button v-if="!venta.pagado" @click="showPagoModal = true" 
                    class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              Marcar como Pagado
            </button>
            <button v-if="canDelete" @click="eliminarVenta(venta.id)" 
                    class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
              Eliminar Venta
            </button>
          </div>
        </div>
      </div>

      <!-- Modal para Marcar como Pagado -->
      <div v-if="showPagoModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" @click.self="showPagoModal = false">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
          <div class="flex items-center justify-between p-6 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Marcar Venta como Pagada</h3>
            <button @click="showPagoModal = false" class="text-gray-400 hover:text-gray-600">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
          <div class="p-6 space-y-4">
            <div class="bg-gray-50 p-4 rounded-lg">
              <div class="flex justify-between items-center">
                <span class="text-sm font-medium text-gray-700">Monto Total:</span>
                <span class="text-lg font-bold text-gray-900">{{ formatCurrency(venta.total) }}</span>
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Método de Pago *</label>
              <select v-model="metodoPago" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Seleccionar método</option>
                <option value="efectivo">Efectivo</option>
                <option value="transferencia">Transferencia</option>
                <option value="cheque">Cheque</option>
                <option value="tarjeta">Tarjeta</option>
                <option value="otros">Otros</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Notas (opcional)</label>
              <textarea v-model="notasPago" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Agregar notas..."></textarea>
            </div>
          </div>
          <div class="flex justify-end gap-3 px-6 py-4 border-t border-gray-200 bg-gray-50">
            <button @click="showPagoModal = false" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">Cancelar</button>
            <button @click="confirmarPago" :disabled="!metodoPago" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed">
              Confirmar Pago
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Notyf } from 'notyf';
import 'notyf/notyf.min.css';

const props = defineProps({ 
  venta: Object,
  canEdit: Boolean,
  canDelete: Boolean
});

const notyf = new Notyf({ duration: 3000, position: { x: 'right', y: 'top' } });
const showPagoModal = ref(false);
const metodoPago = ref('');
const notasPago = ref('');

const formatCurrency = (value) => {
  return new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(value);
};

const formatearFecha = (fecha) => {
  if (!fecha) return 'N/A';
  return new Date(fecha).toLocaleDateString('es-MX', { year: 'numeric', month: 'long', day: 'numeric' });
};

const formatearFechaHora = (fecha) => {
  if (!fecha) return 'N/A';
  return new Date(fecha).toLocaleString('es-MX', { 
    year: 'numeric', month: 'long', day: 'numeric', 
    hour: '2-digit', minute: '2-digit' 
  });
};

const getEstadoClass = (estado) => {
  const classes = {
    'borrador': 'bg-gray-100 text-gray-800',
    'pendiente': 'bg-yellow-100 text-yellow-800',
    'aprobada': 'bg-green-100 text-green-800',
    'cancelada': 'bg-red-100 text-red-800'
  };
  return classes[estado] || 'bg-gray-100 text-gray-800';
};

const getEstadoLabel = (estado) => {
  const labels = {
    'borrador': 'Borrador',
    'pendiente': 'Pendiente',
    'aprobada': 'Aprobada',
    'cancelada': 'Cancelada'
  };
  return labels[estado] || estado;
};

const confirmarPago = () => {
  router.post(route('ventas.marcar-pagado', props.venta.id), {
    metodo_pago: metodoPago.value,
    notas_pago: notasPago.value
  }, {
    onSuccess: () => {
      notyf.success('Venta marcada como pagada');
      showPagoModal.value = false;
    },
    onError: () => notyf.error('Error al marcar como pagada')
  });
};

const eliminarVenta = (id) => {
  if (confirm('¿Estás seguro de que deseas eliminar esta venta?')) {
    router.delete(route('ventas.destroy', id), {
      onSuccess: () => {
        notyf.success('Venta eliminada exitosamente');
        router.visit(route('ventas.index'));
      },
      onError: () => notyf.error('Error al eliminar la venta')
    });
  }
};
</script>
