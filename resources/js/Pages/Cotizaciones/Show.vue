<template>
  <Head title="Detalles de la Cotización" />
  <div class="cotizaciones-show min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header con navegación -->
      <div class="mb-8">
        <nav class="flex items-center space-x-2 text-sm text-gray-500 mb-4">
          <Link :href="route('cotizaciones.index')" class="hover:text-gray-700 transition-colors">
            Cotizaciones
          </Link>
          <span>/</span>
          <span class="text-gray-900 font-medium">Detalles</span>
        </nav>
        <div class="flex items-center justify-between">
          <h1 class="text-3xl font-bold text-gray-900">
            Cotización #{{ cotizacion?.id || '...' }}
          </h1>
          <div class="flex items-center space-x-2">
            <span
              :class="getStatusClass(cotizacion?.estado)"
              class="px-3 py-1 rounded-full text-sm font-medium"
            >
              {{ getStatusText(cotizacion?.estado) }}
            </span>
          </div>
        </div>
      </div>
      <!-- Contenido principal -->
      <div v-if="cotizacion" class="space-y-6">
        <!-- Información del cliente -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900 flex items-center">
              <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
              Información del Cliente
            </h2>
          </div>
          <div class="px-6 py-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <dt class="text-sm font-medium text-gray-500">Cliente</dt>
                <dd class="mt-1 text-lg font-semibold text-gray-900">
                  {{ cotizacion.cliente.nombre_razon_social }}
                </dd>
              </div>
              <div v-if="cotizacion.cliente.email">
                <dt class="text-sm font-medium text-gray-500">Email</dt>
                <dd class="mt-1 text-sm text-gray-900">
                  {{ cotizacion.cliente.email }}
                </dd>
              </div>
              <div v-if="cotizacion.fecha_creacion">
                <dt class="text-sm font-medium text-gray-500">Fecha de Creación</dt>
                <dd class="mt-1 text-sm text-gray-900">
                  {{ formatDate(cotizacion.fecha_creacion) }}
                </dd>
              </div>
              <div v-if="cotizacion.fecha_vencimiento">
                <dt class="text-sm font-medium text-gray-500">Fecha de Vencimiento</dt>
                <dd class="mt-1 text-sm text-gray-900">
                  {{ formatDate(cotizacion.fecha_vencimiento) }}
                </dd>
              </div>
            </div>
          </div>
        </div>
        <!-- Productos -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900 flex items-center">
              <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
              </svg>
              Productos ({{ cotizacion.productos?.length || 0 }})
            </h2>
          </div>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Producto
                  </th>
                  <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Cantidad
                  </th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Precio Unitario
                  </th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Subtotal
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr
                  v-for="producto in cotizacion.productos"
                  :key="producto.id"
                  class="hover:bg-gray-50 transition-colors"
                >
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">
                      {{ producto.nombre }}
                    </div>
                    <div v-if="producto.descripcion" class="text-sm text-gray-500">
                      {{ producto.descripcion }}
                    </div>
                  </td>
                  <td class="px-6 py-4 text-center whitespace-nowrap">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                      {{ producto.pivot.cantidad }}
                    </span>
                  </td>
                  <td class="px-6 py-4 text-right whitespace-nowrap text-sm text-gray-900">
                    ${{ formatCurrency(producto.pivot.precio) }}
                  </td>
                  <td class="px-6 py-4 text-right whitespace-nowrap text-sm font-medium text-gray-900">
                    ${{ formatCurrency(producto.pivot.precio * producto.pivot.cantidad) }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        <!-- Resumen financiero -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900 flex items-center">
              <svg class="w-5 h-5 mr-2 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
              </svg>
              Resumen Financiero
            </h2>
          </div>
          <div class="px-6 py-4">
            <div class="space-y-3">
              <div class="flex justify-between items-center">
                <span class="text-gray-600">Subtotal:</span>
                <span class="font-medium">${{ formatCurrency(subtotal) }}</span>
              </div>
              <div v-if="cotizacion.descuento" class="flex justify-between items-center">
                <span class="text-gray-600">Descuento:</span>
                <span class="font-medium text-red-600">-${{ formatCurrency(cotizacion.descuento) }}</span>
              </div>
              <div v-if="cotizacion.impuestos" class="flex justify-between items-center">
                <span class="text-gray-600">Impuestos:</span>
                <span class="font-medium">${{ formatCurrency(cotizacion.impuestos) }}</span>
              </div>
              <div class="border-t pt-3 flex justify-between items-center">
                <span class="text-lg font-semibold text-gray-900">Total:</span>
                <span class="text-2xl font-bold text-green-600">${{ formatCurrency(cotizacion.total) }}</span>
              </div>
            </div>
          </div>
        </div>
        <!-- Notas adicionales -->
        <div v-if="cotizacion.notas" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900 flex items-center">
              <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
              Notas Adicionales
            </h2>
          </div>
          <div class="px-6 py-4">
            <p class="text-gray-700 whitespace-pre-wrap">{{ cotizacion.notas }}</p>
          </div>
        </div>
      </div>
      <!-- Estado de carga -->
      <div v-else class="flex justify-center items-center py-12">
        <div class="text-center">
          <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500 mx-auto mb-4"></div>
          <p class="text-gray-500">Cargando detalles de la cotización...</p>
        </div>
      </div>
      <!-- Botones de acción -->
      <div v-if="cotizacion" class="mt-8 flex flex-wrap gap-3 justify-end">
        <Link
          :href="route('cotizaciones.edit', cotizacion.id)"
          class="inline-flex items-center px-4 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 focus:ring-4 focus:ring-blue-200 transition-all duration-200 shadow-sm"
        >
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
          </svg>
          Editar
        </Link>
        <button
          @click="mostrarModalEliminacion = true"
          :disabled="loading"
          class="inline-flex items-center px-4 py-2 bg-red-600 text-white font-medium rounded-lg hover:bg-red-700 focus:ring-4 focus:ring-red-200 transition-all duration-200 shadow-sm disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
          </svg>
          Eliminar
        </button>
        <button
          @click="enviarAPedido"
          :disabled="loading || cotizacion.estado === 'pedido'"
          class="inline-flex items-center px-4 py-2 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 focus:ring-4 focus:ring-green-200 transition-all duration-200 shadow-sm disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
          </svg>
          {{ cotizacion.estado === 'pedido' ? 'Ya convertido a Pedido' : 'Convertir a Pedido' }}
        </button>
        <button
          @click="enviarAVenta"
          :disabled="loading || cotizacion.estado === 'venta'"
          class="inline-flex items-center px-4 py-2 bg-purple-600 text-white font-medium rounded-lg hover:bg-purple-700 focus:ring-4 focus:ring-purple-200 transition-all duration-200 shadow-sm disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
          </svg>
          {{ cotizacion.estado === 'venta' ? 'Ya convertido a Venta' : 'Convertir a Venta' }}
        </button>
      </div>
    </div>
    <!-- Modal de confirmación de eliminación -->
    <div
      v-if="mostrarModalEliminacion"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
      @click.self="mostrarModalEliminacion = false"
    >
      <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6">
        <div class="flex items-center mb-4">
          <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
            <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
            </svg>
          </div>
        </div>
        <div class="text-center">
          <h3 class="text-lg font-medium text-gray-900 mb-2">
            Confirmar Eliminación
          </h3>
          <p class="text-sm text-gray-500 mb-6">
            ¿Estás seguro de que deseas eliminar esta cotización? Esta acción no se puede deshacer.
          </p>
          <div class="flex justify-center space-x-3">
            <button
              @click="mostrarModalEliminacion = false"
              class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors"
            >
              Cancelar
            </button>
            <button
              @click="confirmarEliminacion"
              :disabled="loading"
              class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors disabled:opacity-50"
            >
              <span v-if="loading" class="flex items-center">
                <div class="animate-spin rounded-full h-4 w-4 border-t-2 border-b-2 border-white mr-2"></div>
                Eliminando...
              </span>
              <span v-else>Eliminar</span>
            </button>
          </div>
        </div>
      </div>
    </div>
    <!-- Spinner de carga global -->
    <div v-if="loading && !mostrarModalEliminacion" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 shadow-xl">
        <div class="flex items-center">
          <div class="animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-blue-500 mr-3"></div>
          <span class="text-gray-700">Procesando...</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { Notyf } from 'notyf';
import 'notyf/notyf.min.css';
import AppLayout from '@/Layouts/AppLayout.vue';

// Define el layout del dashboard
defineOptions({ layout: AppLayout });

// Define los eventos que el componente puede emitir
const emit = defineEmits(['convertirAPedido']);

// Propiedades
const props = defineProps({
  cotizacion: {
    type: Object,
    default: () => ({
      puede_convertir: true, // Asegúrate de que esto esté definido correctamente
      estado: 'pendiente', // Asegúrate de que el estado esté definido correctamente
      // ... otras propiedades
    })
  }
});

// Variables reactivas
const loading = ref(false);
const mostrarModalEliminacion = ref(false);

// Configuración de Notyf para notificaciones
const notyf = new Notyf({
  duration: 4000,
  position: { x: 'right', y: 'top' },
  types: [
    {
      type: 'success',
      background: 'linear-gradient(135deg, #10B981, #059669)',
      icon: {
        className: 'notyf__icon--success',
        tagName: 'i'
      }
    },
    {
      type: 'error',
      background: 'linear-gradient(135deg, #EF4444, #DC2626)',
      icon: {
        className: 'notyf__icon--error',
        tagName: 'i'
      }
    }
  ]
});

// Computed properties
const subtotal = computed(() => {
  if (!props.cotizacion?.productos) return 0;
  return props.cotizacion.productos.reduce((total, producto) => {
    return total + (producto.pivot.precio * producto.pivot.cantidad);
  }, 0);
});

// Funciones de formato
const formatCurrency = (amount) => {
  return new Intl.NumberFormat('es-MX', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(amount || 0);
};

const formatDate = (date) => {
  if (!date) return 'N/A';
  return new Date(date).toLocaleDateString('es-MX', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
};

const getStatusClass = (estado) => {
  const classes = {
    'pendiente': 'bg-yellow-100 text-yellow-800',
    'pedido': 'bg-blue-100 text-blue-800',
    'venta': 'bg-purple-100 text-purple-800',
    'rechazado': 'bg-red-100 text-red-800',
    'vencido': 'bg-gray-100 text-gray-800'
  };
  return classes[estado] || 'bg-gray-100 text-gray-800';
};

const getStatusText = (estado) => {
  const texts = {
    'pendiente': 'Pendiente',
    'pedido': 'Convertido a Pedido',
    'venta': 'Convertido a Venta',
    'rechazado': 'Rechazado',
    'vencido': 'Vencido'
  };
  return texts[estado] || 'Sin estado';
};

// Funciones de acción
const confirmarEliminacion = async () => {
  loading.value = true;
  try {
    await router.delete(`/cotizaciones/${props.cotizacion.id}`, {
      onSuccess: () => {
        notyf.success('Cotización eliminada exitosamente.');
        router.visit(route('cotizaciones.index'));
      },
      onError: (errors) => {
        console.error('Error al eliminar:', errors);
        notyf.error('Error al eliminar la cotización. Por favor, inténtalo de nuevo.');
      }
    });
  } catch (error) {
    console.error('Error inesperado:', error);
    notyf.error('Ocurrió un error inesperado. Por favor, inténtalo de nuevo.');
  } finally {
    loading.value = false;
    mostrarModalEliminacion.value = false;
  }
};

const enviarAPedido = () => {
  if (props.cotizacion.estado === 'pedido') {
    notyf.error('Esta cotización ya ha sido convertida a pedido.');
    return;
  }
  loading.value = true;
  router.post(`/cotizaciones/${props.cotizacion.id}/convertir-a-pedido`,
    { confirmado: true },
    {
      preserveScroll: true,
      onSuccess: () => {
        notyf.success('Cotización convertida a pedido exitosamente.');
        emit('convertirAPedido');
      },
      onError: (errors) => {
        console.error('Error al convertir a pedido:', errors);
        notyf.error('Error al convertir la cotización a pedido.');
      },
      onFinish: () => {
        loading.value = false;
      }
    }
  );
};

const enviarAVenta = () => {
  if (props.cotizacion.estado === 'venta') {
    notyf.error('Esta cotización ya ha sido convertida a venta.');
    return;
  }
  loading.value = true;
  router.post(`/cotizaciones/${props.cotizacion.id}/convertir-a-venta`,
    { confirmado: true },
    {
      preserveScroll: true,
      onSuccess: () => {
        notyf.success('Cotización convertida a venta exitosamente.');
      },
      onError: (errors) => {
        console.error('Error al convertir a venta:', errors);
        notyf.error('Error al convertir la cotización a venta.');
      },
      onFinish: () => {
        loading.value = false;
      }
    }
  );
};
</script>

<style scoped>
/* Estilos personalizados para mejorar la apariencia */
.cotizaciones-show {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}
/* Animaciones suaves */
.transition-all {
  transition: all 0.2s ease-in-out;
}
/* Mejora en los focus states para accesibilidad */
button:focus, a:focus {
  outline: 2px solid transparent;
  outline-offset: 2px;
}
/* Hover effects personalizados */
.hover\:scale-105:hover {
  transform: scale(1.05);
}
/* Sombras suaves */
.shadow-sm {
  box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
}
.shadow-xl {
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}
/* Estilo para el botón de venta */
.bg-purple-600 {
  background-color: #7c3aed;
}
.hover\:bg-purple-700:hover {
  background-color: #6d28d9;
}
.focus\:ring-purple-200:focus {
  --tw-ring-color: rgba(216, 180, 254, 0.5);
}
/* Clases para el estado de venta */
.bg-purple-100 {
  background-color: #f3e8ff;
}
.text-purple-800 {
  color: #6b21a8;
}
</style>
