<template>
  <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
      <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex justify-between items-center">
        <h3 class="text-xl font-bold text-gray-900">Vista Previa de la Cotización</h3>
        <button @click="$emit('cerrar')" class="text-gray-400 hover:text-gray-600">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>
      <div class="p-6">
        <div class="text-center mb-8">
          <h1 class="text-3xl font-bold text-gray-900 mb-2">COTIZACIÓN</h1>
          <p class="text-gray-600">{{ new Date().toLocaleDateString('es-MX', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
          }) }}</p>
        </div>
        <div v-if="clienteSeleccionado" class="mb-8 p-6 bg-gray-50 rounded-lg">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Cliente</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <p><strong>Nombre:</strong> {{ clienteSeleccionado.nombre_razon_social }}</p>
              <p v-if="clienteSeleccionado.email"><strong>Email:</strong> {{ clienteSeleccionado.email }}</p>
              <p v-if="clienteSeleccionado.telefono"><strong>Teléfono:</strong> {{ clienteSeleccionado.telefono }}</p>
            </div>
            <div>
              <p v-if="clienteSeleccionado.calle"><strong>Dirección:</strong> {{ clienteSeleccionado.calle }}</p>
              <p v-if="clienteSeleccionado.rfc"><strong>RFC:</strong> {{ clienteSeleccionado.rfc }}</p>
            </div>
          </div>
        </div>
        <div class="mb-8">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Productos y Servicios</h2>
          <div class="overflow-x-auto">
            <table class="w-full border-collapse border border-gray-300">
              <thead>
                <tr class="bg-gray-100">
                  <th class="border border-gray-300 px-4 py-2 text-left">Descripción</th>
                  <th class="border border-gray-300 px-4 py-2 text-center">Cantidad</th>
                  <th class="border border-gray-300 px-4 py-2 text-right">Precio Unit.</th>
                  <th class="border border-gray-300 px-4 py-2 text-right">Descuento</th>
                  <th class="border border-gray-300 px-4 py-2 text-right">Subtotal</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="entry in selectedProducts" :key="`${entry.tipo}-${entry.id}`">
                  <td class="border border-gray-300 px-4 py-2">
                    {{ obtenerProducto(entry.id, entry.tipo)?.nombre || obtenerProducto(entry.id, entry.tipo)?.descripcion }}
                  </td>
                  <td class="border border-gray-300 px-4 py-2 text-center">
                    {{ quantities[`${entry.tipo}-${entry.id}`] || 0 }}
                  </td>
                  <td class="border border-gray-300 px-4 py-2 text-right">
                    ${{ (prices[`${entry.tipo}-${entry.id}`] || 0).toLocaleString('es-MX', { minimumFractionDigits: 2 }) }}
                  </td>
                  <td class="border border-gray-300 px-4 py-2 text-right">
                    {{ discounts[`${entry.tipo}-${entry.id}`] || 0 }}%
                  </td>
                  <td class="border border-gray-300 px-4 py-2 text-right">
                    ${{ calcularSubtotalProducto(entry).toLocaleString('es-MX', { minimumFractionDigits: 2 }) }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        <div class="border-t border-gray-300 pt-4">
          <div class="flex justify-end">
            <div class="w-64">
              <div class="flex justify-between py-2">
                <span>Subtotal:</span>
                <span>${{ totales.subtotal.toLocaleString('es-MX', { minimumFractionDigits: 2 }) }}</span>
              </div>
              <div v-if="totales.descuentoGeneral > 0" class="flex justify-between py-2 text-orange-600">
                <span>Descuento General:</span>
                <span>-${{ totales.descuentoGeneral.toLocaleString('es-MX', { minimumFractionDigits: 2 }) }}</span>
              </div>
              <div v-if="totales.descuentoItems > 0" class="flex justify-between py-2 text-orange-600">
                <span>Descuentos por item:</span>
                <span>-${{ totales.descuentoItems.toLocaleString('es-MX', { minimumFractionDigits: 2 }) }}</span>
              </div>
              <div class="flex justify-between py-2">
                <span>IVA (16%):</span>
                <span>${{ totales.iva.toLocaleString('es-MX', { minimumFractionDigits: 2 }) }}</span>
              </div>
              <div class="flex justify-between py-2 border-t border-gray-300 font-bold text-lg">
                <span>Total:</span>
                <span>${{ totales.total.toLocaleString('es-MX', { minimumFractionDigits: 2 }) }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="sticky bottom-0 bg-white border-t border-gray-200 px-6 py-4 flex justify-end gap-3">
        <button @click="$emit('imprimir')" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
          <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
          </svg>
          Imprimir
        </button>
        <button @click="$emit('cerrar')" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
          Cerrar
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  clienteSeleccionado: Object,
  selectedProducts: Array,
  quantities: Object,
  prices: Object,
  discounts: Object,
  totales: Object,
  productos: Array,
  servicios: Array
});

defineEmits(['cerrar', 'imprimir']);

const obtenerProducto = (id, tipo) => {
  const coleccion = tipo === 'producto' ? props.productos : props.servicios;
  return coleccion.find(item => item.id === id);
};

const calcularSubtotalProducto = (entry) => {
  const key = `${entry.tipo}-${entry.id}`;
  const cantidad = Number.parseFloat(props.quantities[key]) || 0;
  const precio = Number.parseFloat(props.prices[key]) || 0;
  const descuento = Number.parseFloat(props.discounts[key]) || 0;
  const subtotal = cantidad * precio;
  return subtotal - (subtotal * (descuento / 100));
};
</script>
