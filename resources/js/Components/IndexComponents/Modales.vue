<template>
  <Transition name="modal">
    <div
      v-if="show"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
    >
      <div
        :class="{
          'max-w-md': mode === 'confirm',
          'max-w-4xl': mode === 'details'
        }"
        class="bg-white rounded-lg shadow-xl w-full max-h-[90vh] overflow-y-auto p-6"
      >
        <!-- Modo: Confirmación de eliminación -->
        <div v-if="mode === 'confirm'" class="text-center">
          <div
            class="w-12 h-12 mx-auto bg-red-100 rounded-full flex items-center justify-center mb-4"
          >
            <svg
              class="w-6 h-6 text-red-600"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"
              />
            </svg>
          </div>
          <h3 class="text-lg font-medium mb-2">
            ¿Eliminar {{ config.titulo.toLowerCase() }}?
          </h3>
          <p class="text-gray-600 mb-6">
            Esta acción no se puede deshacer.
          </p>
          <div class="flex gap-3">
            <button
              @click="onCancel"
              class="flex-1 px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300 transition-colors"
            >
              Cancelar
            </button>
            <button
              @click="onConfirm"
              class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors"
            >
              Eliminar
            </button>
          </div>
        </div>

        <!-- Modo: Detalles -->
        <div v-else-if="mode === 'details'" class="space-y-4">
          <h3 class="text-lg font-medium mb-4">
            Detalles de {{ config.titulo }}
            <span v-if="selected?.id" class="text-sm text-gray-500"
              >#{{ selected.id }}</span
            >
          </h3>

          <div v-if="selected" class="space-y-4">
            <!-- Información general -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <p class="text-sm text-gray-600">
                  <strong>Cliente:</strong>
                  {{ selected.cliente?.nombre || 'Sin cliente' }}
                </p>
                <p v-if="selected.cliente?.email" class="text-sm text-gray-600">
                  <strong>Email:</strong> {{ selected.cliente.email }}
                </p>
                <p class="text-sm text-gray-600">
                  <strong>Fecha:</strong>
                  {{ formatearFecha(selected.created_at || selected.fecha) }}
                </p>
                <p class="text-sm text-gray-600">
                  <strong>Estado:</strong>
                  <span
                    :class="obtenerClasesEstado(selected.estado)"
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                  >
                    <span
                      class="w-1.5 h-1.5 rounded-full mr-1.5"
                      :class="obtenerColorPuntoEstado(selected.estado)"
                    ></span>
                    {{ obtenerLabelEstado(selected.estado) }}
                  </span>
                </p>
              </div>
              <div>
                <p v-if="config.mostrarCampoExtra" class="text-sm text-gray-600">
                  <strong>{{ config.campoExtra.label }}:</strong>
                  {{ selected[config.campoExtra.key] || 'N/A' }}
                </p>
                <p class="text-sm text-gray-600">
                  <strong>Total:</strong> ${{ formatearMoneda(selected.total) }}
                </p>
                <p class="text-sm text-gray-600">
                  <strong>Productos:</strong>
                  {{ selected.productos?.length || 0 }} items
                </p>
              </div>
            </div>

            <!-- Tabla de productos -->
            <div v-if="selected.productos?.length" class="mt-4">
              <h4 class="text-sm font-medium text-gray-900 mb-2">Productos</h4>
              <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                  <thead class="bg-gray-50">
                    <tr>
                      <th
                        class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider"
                      >
                        Nombre
                      </th>
                      <th
                        class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider"
                      >
                        Tipo
                      </th>
                      <th
                        class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider"
                      >
                        Cantidad
                      </th>
                      <th
                        class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider"
                      >
                        Precio
                      </th>
                      <th
                        class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider"
                      >
                        Descuento
                      </th>
                      <th
                        class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider"
                      >
                        Subtotal
                      </th>
                    </tr>
                  </thead>
                  <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="producto in selected.productos" :key="producto.id">
                      <td class="px-4 py-2 text-sm text-gray-900">
                        {{ producto.nombre || 'Sin nombre' }}
                      </td>
                      <td class="px-4 py-2 text-sm text-gray-600">
                        {{ producto.tipo || 'N/A' }}
                      </td>
                      <td class="px-4 py-2 text-sm text-gray-600">
                        {{ producto.cantidad || 0 }}
                      </td>
                      <td class="px-4 py-2 text-sm text-gray-600">
                        ${{ formatearMoneda(producto.precio) }}
                      </td>
                      <td class="px-4 py-2 text-sm text-gray-600">
                        ${{ formatearMoneda(producto.descuento || 0) }}
                      </td>
                      <td class="px-4 py-2 text-sm text-gray-600">
                        ${{ formatearMoneda((producto.cantidad * producto.precio) - (producto.descuento || 0)) }}
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
            <p v-else class="text-sm text-gray-600">
              No hay productos asociados.
            </p>
          </div>
          <div v-else class="text-sm text-gray-600">
            No hay datos disponibles.
          </div>

          <!-- Botones de acción -->
          <div class="flex justify-end gap-3 mt-6">
            <button
              v-if="config.acciones.enviarPedido"
              @click="confirmarEnvioPedido"
              class="px-4 py-2 text-white rounded-lg transition-colors"
              :class="{
                'bg-green-600 hover:bg-green-700': !yaEnviado,
                'bg-blue-600 hover:bg-blue-700': yaEnviado
              }"
            >
              {{ yaEnviado ? 'Reenviar a Pedido' : 'Enviar a Pedido' }}
            </button>
            <button
              v-if="config.acciones.imprimir"
              @click="onImprimir"
              class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors"
            >
              Imprimir
            </button>
            <button
              v-if="config.acciones.editar"
              @click="onEditar"
              class="px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition-colors"
            >
              Editar
            </button>
            <button
              @click="onClose"
              class="px-4 py-2 bg-gray-300 rounded-lg hover:bg-gray-400 transition-colors"
            >
              Cerrar
            </button>
          </div>
        </div>
      </div>
    </div>
  </Transition>

  <!-- Modal de confirmación para reenvío -->
  <Transition name="modal">
    <div
      v-if="showConfirmReenvio"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
    >
      <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-6">
        <div class="text-center">
          <div class="w-12 h-12 mx-auto bg-blue-100 rounded-full flex items-center justify-center mb-4">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
            </svg>
          </div>
          <h3 class="text-lg font-medium mb-2">¿Reenviar cotización a pedido?</h3>
          <p class="text-gray-600 mb-6">
            Esta cotización ya fue enviada anteriormente ({{ formatearFecha(selected?.updated_at) }}).
            ¿Deseas crear un nuevo pedido?
          </p>
          <div class="flex gap-3">
            <button
              @click="showConfirmReenvio = false"
              class="flex-1 px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300 transition-colors"
            >
              Cancelar
            </button>
            <button
              @click="reenviarAPedido"
              class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
            >
              Reenviar
            </button>
          </div>
        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { computed, ref } from 'vue';

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  mode: {
    type: String,
    default: 'details',
    validator: (value) => ['confirm', 'details'].includes(value)
  },
  selected: {
    type: Object,
    default: null
  },
  tipo: {
    type: String,
    required: true,
    validator: (value) => ['cotizaciones', 'pedidos', 'ventas'].includes(value)
  }
});

const emit = defineEmits([
  'close',
  'confirm-delete',
  'imprimir',
  'editar',
  'enviar-pedido'
]);

const showConfirmReenvio = ref(false);

// Configuración dinámica según el tipo de documento
const config = computed(() => {
  const configs = {
    cotizaciones: {
      titulo: 'Cotización',
      mostrarCampoExtra: false,
      campoExtra: null,
      acciones: {
        editar: true,
        imprimir: true,
        enviarPedido: true
      },
      estados: {
        'borrador': { label: 'Borrador', classes: 'bg-gray-100 text-gray-800', color: 'bg-gray-400' },
        'pendiente': { label: 'Pendiente', classes: 'bg-yellow-100 text-yellow-800', color: 'bg-yellow-400' },
        'enviado_pedido': { label: 'Enviado a Pedido', classes: 'bg-blue-100 text-blue-800', color: 'bg-blue-400' },
        'enviado_venta': { label: 'Enviado a Venta', classes: 'bg-indigo-100 text-indigo-800', color: 'bg-indigo-400' },
        'aprobado': { label: 'Aprobada', classes: 'bg-green-100 text-green-800', color: 'bg-green-400' },
        'rechazado': { label: 'Rechazada', classes: 'bg-red-100 text-red-800', color: 'bg-red-400' },
        'cancelado': { label: 'Cancelada', classes: 'bg-gray-100 text-gray-800', color: 'bg-gray-400' }
      }
    },
    pedidos: {
      titulo: 'Pedido',
      mostrarCampoExtra: true,
      campoExtra: { key: 'numero_pedido', label: 'N° Pedido' },
      acciones: {
        editar: true,
        imprimir: true,
        enviarPedido: false
      },
      estados: {
        'borrador': { label: 'Borrador', classes: 'bg-gray-100 text-gray-800', color: 'bg-gray-400' },
        'pendiente': { label: 'Pendiente', classes: 'bg-yellow-100 text-yellow-800', color: 'bg-yellow-400' },
        'confirmado': { label: 'Confirmado', classes: 'bg-blue-100 text-blue-800', color: 'bg-blue-400' },
        'en_preparacion': { label: 'En Preparación', classes: 'bg-orange-100 text-orange-800', color: 'bg-orange-400' },
        'listo_entrega': { label: 'Listo para Entrega', classes: 'bg-purple-100 text-purple-800', color: 'bg-purple-400' },
        'entregado': { label: 'Entregado', classes: 'bg-green-100 text-green-800', color: 'bg-green-400' },
        'cancelado': { label: 'Cancelado', classes: 'bg-red-100 text-red-800', color: 'bg-red-400' }
      }
    },
    ventas: {
      titulo: 'Venta',
      mostrarCampoExtra: true,
      campoExtra: { key: 'numero_factura', label: 'N° Factura' },
      acciones: {
        editar: false,
        imprimir: true,
        enviarPedido: false
      },
      estados: {
        'borrador': { label: 'Borrador', classes: 'bg-gray-100 text-gray-800', color: 'bg-gray-400' },
        'facturado': { label: 'Facturado', classes: 'bg-blue-100 text-blue-800', color: 'bg-blue-400' },
        'pagado': { label: 'Pagado', classes: 'bg-green-100 text-green-800', color: 'bg-green-400' },
        'vencido': { label: 'Vencido', classes: 'bg-red-100 text-red-800', color: 'bg-red-400' },
        'anulado': { label: 'Anulado', classes: 'bg-gray-100 text-gray-800', color: 'bg-gray-400' }
      }
    }
  };

  return configs[props.tipo] || configs.cotizaciones;
});

// Computed para saber si ya fue enviada
const yaEnviado = computed(() => {
  return props.selected?.estado === 'enviado_pedido';
});

// Métodos de formateo
const formatearFecha = (date) => {
  if (!date) return 'Fecha no disponible';
  try {
    return new Date(date).toLocaleDateString('es-MX', {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    });
  } catch (error) {
    console.error('Error formatting date:', date, error);
    return 'Fecha inválida';
  }
};

const formatearMoneda = (num) => {
  const value = parseFloat(num) || 0;
  return new Intl.NumberFormat('es-MX', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(value);
};

// Estados dinámicos
const obtenerClasesEstado = (estado) => {
  const estadoConfig = config.value.estados[estado];
  return estadoConfig ? estadoConfig.classes : 'bg-gray-100 text-gray-800';
};

const obtenerColorPuntoEstado = (estado) => {
  const estadoConfig = config.value.estados[estado];
  return estadoConfig ? estadoConfig.color : 'bg-gray-400';
};

const obtenerLabelEstado = (estado) => {
  const estadoConfig = config.value.estados[estado];
  return estadoConfig ? estadoConfig.label : 'Pendiente';
};

// Método para confirmar el envío
const confirmarEnvioPedido = () => {
  if (yaEnviado.value) {
    showConfirmReenvio.value = true;
  } else {
    onEnviarPedido();
  }
};

const onEnviarPedido = async () => {
  try {
    const response = await emit('enviar-pedido', props.selected);

    if (response?.success) {
      alert(`Pedido #${response.numero_pedido} creado exitosamente`);
      emit('close');
      emit('refresh'); // Si necesitas actualizar la lista
    }
  } catch (error) {
    console.error('Error al enviar:', error.response?.data);

    if (error.response?.data?.requiere_confirmacion) {
      showConfirmReenvio.value = true;
    } else {
      alert(error.response?.data?.error || 'Error al procesar la solicitud');
    }
  }
};

const reenviarAPedido = async () => {
  showConfirmReenvio.value = false;
  try {
    const response = await emit('enviar-pedido', {
      ...props.selected,
      forzarReenvio: true
    });

    if (response?.success) {
      alert(`Pedido #${response.numero_pedido} actualizado exitosamente`);
      emit('close');
      emit('refresh');
    }
  } catch (error) {
    console.error('Error al reenviar:', error.response?.data);
    alert(error.response?.data?.error || 'Error al actualizar el pedido');
  }
};



// Emits
const onCancel = () => emit('close');
const onConfirm = () => emit('confirm-delete');
const onClose = () => emit('close');
const onImprimir = () => emit('imprimir', props.selected);
const onEditar = () => emit('editar', props.selected?.id);
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s ease, transform 0.3s ease;
}
.modal-enter-from,
.modal-leave-to {
  opacity: 0;
  transform: scale(0.95);
}
.modal-enter-to,
.modal-leave-from {
  opacity: 1;
  transform: scale(1);
}
</style>
