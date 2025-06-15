<template>
  <Head title="Crear cotizaciones" />
  <div class="cotizaciones-create min-h-screen bg-gradient-to-br from-slate-50 to-blue-50 p-6">
    <div class="max-w-6xl mx-auto">
      <!-- Header -->
      <div class="mb-8">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Nueva Cotización</h1>
            <p class="text-gray-600">Crea una nueva cotización para tus clientes</p>
          </div>
          <Link
            :href="route('cotizaciones.index')"
            class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 hover:border-gray-400 transition-all duration-200 shadow-sm"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Volver
          </Link>
        </div>
      </div>

      <form @submit.prevent="crearCotizacion" class="space-y-8">
        <!-- Información del Cliente -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
          <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-4">
            <h2 class="text-lg font-semibold text-white flex items-center">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
              </svg>
              Información del Cliente
            </h2>
          </div>
          <div class="p-6">
            <!-- Componente de búsqueda -->
            <BuscarCliente
              :clientes="clientes"
              :form="form"
              @cliente-seleccionado="onClienteSeleccionado"
            />

            <!-- Información del cliente seleccionado - Visible y destacada -->
            <div v-if="clienteSeleccionado" class="mt-6 p-6 bg-gradient-to-r from-blue-50 to-indigo-50 border-2 border-blue-200 rounded-lg shadow-sm">
              <div class="flex items-start justify-between mb-4">
                <h3 class="text-lg font-semibold text-blue-900 flex items-center">
                  <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                  Cliente Seleccionado
                </h3>
                <button
                  type="button"
                  @click="limpiarCliente"
                  class="text-red-500 hover:text-red-700 hover:bg-red-100 p-1 rounded-full transition-colors duration-200"
                  title="Cambiar cliente"
                >
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                  </svg>
                </button>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="space-y-2">
                  <div class="flex items-center text-sm font-medium text-blue-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    Nombre
                  </div>
                  <div class="text-lg font-semibold text-gray-900">{{ clienteSeleccionado.nombre }}</div>
                </div>

                <div class="space-y-2" v-if="clienteSeleccionado.email">
                  <div class="flex items-center text-sm font-medium text-blue-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    Email
                  </div>
                  <div class="text-gray-900">{{ clienteSeleccionado.email }}</div>
                </div>

                <div class="space-y-2" v-if="clienteSeleccionado.telefono">
                  <div class="flex items-center text-sm font-medium text-blue-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                    </svg>
                    Teléfono
                  </div>
                  <div class="text-gray-900">{{ clienteSeleccionado.telefono }}</div>
                </div>

                <div class="space-y-2" v-if="clienteSeleccionado.empresa">
                  <div class="flex items-center text-sm font-medium text-blue-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    Empresa
                  </div>
                  <div class="text-gray-900">{{ clienteSeleccionado.empresa }}</div>
                </div>

                <div class="space-y-2" v-if="clienteSeleccionado.direccion">
                  <div class="flex items-center text-sm font-medium text-blue-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Dirección
                  </div>
                  <div class="text-gray-900">{{ clienteSeleccionado.direccion }}</div>
                </div>

                <div class="space-y-2" v-if="clienteSeleccionado.rfc">
                  <div class="flex items-center text-sm font-medium text-blue-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    RFC
                  </div>
                  <div class="text-gray-900 font-mono">{{ clienteSeleccionado.rfc }}</div>
                </div>
              </div>
            </div>

            <!-- Mensaje cuando no hay cliente seleccionado -->
            <div v-else class="mt-6 p-6 border-2 border-dashed border-gray-300 rounded-lg text-center">
              <svg class="w-12 h-12 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
              </svg>
              <p class="text-gray-500 text-lg font-medium">Selecciona un cliente</p>
              <p class="text-gray-400 text-sm mt-1">Busca y selecciona un cliente para continuar con la cotización</p>
            </div>
          </div>
        </div>

        <!-- Productos y Servicios -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
          <div class="bg-gradient-to-r from-green-500 to-green-600 px-6 py-4">
            <h2 class="text-lg font-semibold text-white flex items-center">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
              </svg>
              Productos y Servicios
            </h2>
          </div>
          <div class="p-6">
            <BuscarProducto
              :productos="productos"
              :servicios="servicios"
              @agregar-producto="agregarProducto"
            />
            <ProductosSeleccionados
              :selectedProducts="selectedProducts"
              :productos="productos"
              :servicios="servicios"
              @eliminar-producto="eliminarProducto"
              @calcular-total="calcularTotal"
            />
          </div>
        </div>

        <!-- Resumen Total -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
          <div class="bg-gradient-to-r from-purple-500 to-purple-600 px-6 py-4">
            <h2 class="text-lg font-semibold text-white flex items-center">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
              </svg>
              Resumen de la Cotización
            </h2>
          </div>
          <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
              <div class="text-center p-6 bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl border border-blue-200">
                <div class="text-3xl font-bold text-blue-600 mb-2">{{ selectedProducts.length }}</div>
                <div class="text-sm text-blue-600 font-medium">Items Seleccionados</div>
              </div>
              <div class="text-center p-6 bg-gradient-to-br from-green-50 to-green-100 rounded-xl border border-green-200">
                <div class="text-3xl font-bold text-green-600 mb-2">
                  {{ Object.values(quantities).reduce((sum, qty) => sum + (qty || 0), 0) }}
                </div>
                <div class="text-sm text-green-600 font-medium">Cantidad Total</div>
              </div>
              <div class="text-center p-6 bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl border border-purple-200">
                <div class="text-3xl font-bold text-purple-600 mb-2">
                  ${{ parseFloat(form.total || 0).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}
                </div>
                <div class="text-sm text-purple-600 font-medium">Total a Pagar</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Botones de Acción -->
        <div class="flex flex-col sm:flex-row gap-4 justify-end">
          <Link
            :href="route('cotizaciones.index')"
            class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            Cancelar
          </Link>
          <button
            type="submit"
            :disabled="!form.cliente_id || selectedProducts.length === 0 || form.processing"
            class="inline-flex items-center justify-center px-6 py-3 border border-transparent rounded-lg text-sm font-medium text-white bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 shadow-lg hover:shadow-xl"
          >
            <svg v-if="form.processing" class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <svg v-else class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            {{ form.processing ? 'Guardando...' : 'Crear Cotización' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import BuscarCliente from '@/Components/BuscarCliente.vue';
import BuscarProducto from '@/Components/BuscarProducto.vue';
import ProductosSeleccionados from '@/Components/ProductosSeleccionados.vue';

// Define el layout del dashboard
defineOptions({ layout: AppLayout });

const props = defineProps({
  clientes: Array,
  productos: {
    type: Array,
    default: () => [],
  },
  servicios: {
    type: Array,
    default: () => [],
  },
});

const form = useForm({
  cliente_id: '',
  total: 0,
  productos: [],
});

const selectedProducts = ref([]);
const quantities = ref({});
const prices = ref({});
const clienteSeleccionado = ref(null);

const onClienteSeleccionado = (cliente) => {
  clienteSeleccionado.value = cliente;
  form.cliente_id = cliente.id;
};

const limpiarCliente = () => {
  clienteSeleccionado.value = null;
  form.cliente_id = '';
};

const agregarProducto = (item) => {
  const itemEntry = { id: item.id, tipo: item.tipo };
  const exists = selectedProducts.value.some(
    (entry) => entry.id === item.id && entry.tipo === item.tipo
  );
  if (!exists) {
    selectedProducts.value.push(itemEntry);
    quantities.value[`${item.tipo}-${item.id}`] = 1;
    prices.value[`${item.tipo}-${item.id}`] = item.tipo === 'producto' ? (item.precio_venta || 0) : (item.precio || 0);
  }
};

const eliminarProducto = (entry) => {
  selectedProducts.value = selectedProducts.value.filter(
    (item) => !(item.id === entry.id && item.tipo === entry.tipo)
  );
  delete quantities.value[`${entry.tipo}-${entry.id}`];
  delete prices.value[`${entry.tipo}-${entry.id}`];
  calcularTotal();
};

const calcularTotal = (newQuantities, newPrices) => {
  if (newQuantities) quantities.value = newQuantities;
  if (newPrices) prices.value = newPrices;

  let total = 0;
  for (const entry of selectedProducts.value) {
    const key = `${entry.tipo}-${entry.id}`;
    const cantidad = Number.parseFloat(quantities.value[key]) || 0;
    const precio = Number.parseFloat(prices.value[key]) || 0;
    total += cantidad * precio;
  }
  form.total = total.toFixed(2);
};

const crearCotizacion = () => {
  form.productos = selectedProducts.value.map((entry) => ({
    id: entry.id,
    tipo: entry.tipo,
    cantidad: quantities.value[`${entry.tipo}-${entry.id}`] || 1,
    precio: prices.value[`${entry.tipo}-${entry.id}`] || 0,
  }));

  form.post(route('cotizaciones.store'), {
    onSuccess: () => {
      selectedProducts.value = [];
      quantities.value = {};
      prices.value = {};
      clienteSeleccionado.value = null;
    },
    onError: (error) => {
      console.error('Error al crear la cotización:', error);
    },
  });
};

onMounted(() => {
  console.log('Clientes:', props.clientes);
  console.log('Productos:', props.productos);
  console.log('Servicios:', props.servicios);
});
</script>

<style scoped>
/* Animaciones para la entrada de elementos */
@keyframes slideInFromTop {
  from {
    opacity: 0;
    transform: translateY(-20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes slideInFromLeft {
  from {
    opacity: 0;
    transform: translateX(-20px);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

/* Aplicar animaciones */
.cotizaciones-create {
  animation: fadeIn 0.5s ease-out;
}

/* Estilos para el cliente seleccionado */
.cliente-seleccionado-enter-active {
  animation: slideInFromTop 0.4s ease-out;
}

.cliente-seleccionado-leave-active {
  animation: slideInFromTop 0.4s ease-out reverse;
}

/* Estilos para hover en tarjetas */
.hover-card {
  transition: all 0.3s ease;
}

.hover-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}

/* Estilos para los iconos */
.icon-bounce {
  transition: transform 0.3s ease;
}

.icon-bounce:hover {
  transform: scale(1.1);
}

/* Estilos para los botones mejorados */
.btn-enhanced {
  position: relative;
  overflow: hidden;
}

.btn-enhanced::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
  transition: left 0.5s;
}

.btn-enhanced:hover::before {
  left: 100%;
}

/* Estilos para las estadísticas mejoradas */
.stat-card {
  transition: all 0.3s ease;
}

.stat-card:hover {
  transform: translateY(-4px) scale(1.02);
  box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
}

/* Estilos responsivos mejorados */
@media (max-width: 768px) {
  .cliente-info-grid {
    grid-template-columns: 1fr;
    gap: 1rem;
  }

  .stats-grid {
    grid-template-columns: 1fr;
    gap: 1rem;
  }
}

/* Estilos para focus mejorados */
button:focus,
a:focus {
  outline: 2px solid #3B82F6;
  outline-offset: 2px;
}

/* Estilos para el estado de carga */
.loading-spinner {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

/* Estilos para elementos deshabilitados */
.disabled {
  opacity: 0.6;
  cursor: not-allowed;
  pointer-events: none;
}

/* Estilos para tooltips mejorados */
.tooltip-enhanced {
  position: relative;
}

.tooltip-enhanced::after {
  content: attr(title);
  position: absolute;
  bottom: 100%;
  left: 50%;
  transform: translateX(-50%);
  background: rgba(0, 0, 0, 0.9);
  color: white;
  padding: 0.5rem 0.75rem;
  border-radius: 0.375rem;
  font-size: 0.75rem;
  white-space: nowrap;
  opacity: 0;
  visibility: hidden;
  transition: all 0.3s ease;
  z-index: 10;
}

.tooltip-enhanced:hover::after {
  opacity: 1;
  visibility: visible;
  transform: translateX(-50%) translateY(-8px);
}
</style>
