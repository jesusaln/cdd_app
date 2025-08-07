<!-- /resources/js/Pages/Ventas/Edit.vue -->
<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import axios from 'axios';
import { Notyf } from 'notyf';
import AppLayout from '@/Layouts/AppLayout.vue';

// Rutas corregidas según tu estructura
import Header from '@/Components/CreateComponents/Header.vue';
import BuscarCliente from '@/Components/CreateComponents/BuscarCliente.vue';
import BuscarProducto from '@/Components/CreateComponents/BuscarProducto.vue';
import ProductosSeleccionados from '@/Components/CreateComponents/ProductosSeleccionados.vue';
import Totales from '@/Components/CreateComponents/Totales.vue';
import BotonesAccion from '@/Components/CreateComponents/BotonesAccion.vue';
import VistaPreviaModal from '@/Components/Modals/VistaPreviaModal.vue';

defineOptions({ layout: AppLayout });

const props = defineProps({
  venta: {
    type: Object,
    required: true
  },
  clientes: {
    type: Array,
    required: true
  },
  productos: {
    type: Array,
    default: () => []
  },
  servicios: {
    type: Array,
    default: () => []
  }
});

// En el script del componente padre:


const notyf = new Notyf({
  duration: 4000,
  position: { x: 'right', y: 'top' },
  types: [
    { type: 'success', background: '#10b981', icon: false },
    { type: 'error', background: '#ef4444', icon: false }
  ]
});

// --- Formulario ---
const form = useForm({
  cliente_id: props.venta.cliente.id,
  numero_venta: props.venta.numero_venta,
  fecha: props.venta.fecha,
  estado: props.venta.estado,
  descuento_general: props.venta.descuento_general || 0,
  productos: [],
  notas: props.venta.notas || ''
});

// --- Estado reactivo ---
const clientesList = ref([...props.clientes]);
const selectedProducts = ref([]);
const quantities = ref({});
const prices = ref({});
const discounts = ref({});
const clienteSeleccionado = ref(props.venta.cliente);
const mostrarVistaPrevia = ref(false);
const mostrarAtajos = ref(true);

// --- Cargar datos de la venta ---
onMounted(() => {
  const savedData = localStorage.getItem(`venta_edit_${props.venta.id}`);
  if (savedData) {
    const data = JSON.parse(savedData);
    console.log('Datos recuperados de localStorage:', data); // Log de depuración
    Object.assign(clienteSeleccionado.value, data.cliente || {});
    form.cliente_id = data.cliente_id;
    selectedProducts.value = data.selectedProducts || [];
    Object.assign(quantities.value, data.quantities || {});
    Object.assign(prices.value, data.precios || {});
    Object.assign(discounts.value, data.descuentos || {});
    form.descuento_general = data.descuento_general || form.descuento_general;
    form.notas = data.notas || form.notas;
    notyf.success('Datos recuperados de sesión anterior');
  } else {
    console.log('Cargando datos desde props:', props.venta.productos); // Log de depuración
    props.venta.productos.forEach(item => {
      const key = `${item.tipo}-${item.id}`;
      selectedProducts.value.push({ id: item.id, tipo: item.tipo });
      quantities.value[key] = item.cantidad;
      prices.value[key] = item.precio;
      discounts.value[key] = item.descuento || 0;
    });
  }
});


// --- Guardar en localStorage ---
const saveToLocalStorage = () => {
  const state = {
    cliente_id: form.cliente_id,
    cliente: clienteSeleccionado.value,
    selectedProducts: selectedProducts.value,
    cantidades: quantities.value,
    precios: prices.value,
    descuentos: discounts.value,
    descuento_general: form.descuento_general,
    notas: form.notas
  };
  localStorage.setItem(`venta_edit_${props.venta.id}`, JSON.stringify(state));
};

// --- Eventos de componentes ---
const onClienteSeleccionado = (cliente) => {
  if (!cliente) {
    clienteSeleccionado.value = null;
    form.cliente_id = '';
    notyf.info('Cliente eliminado');
    return;
  }
  clienteSeleccionado.value = cliente;
  form.cliente_id = cliente.id;
  notyf.success(`Cliente: ${cliente.nombre_razon_social || cliente.nombre}`);
  saveToLocalStorage();
};

const onProductosSeleccionados = (productos) => {
  selectedProducts.value = productos;
  saveToLocalStorage();
};

const eliminarProducto = (entry) => {
  if (!entry?.id || !entry?.tipo) {
    notyf.error('Producto inválido');
    return;
  }
  const key = `${entry.tipo}-${entry.id}`;
  selectedProducts.value = selectedProducts.value.filter(
    p => !(p.id === entry.id && p.tipo === entry.tipo)
  );
  delete quantities.value[key];
  delete prices.value[key];
  delete discounts.value[key];
  notyf.info(`Producto eliminado`);
  saveToLocalStorage();
};

// --- Cálculos ---
const totales = computed(() => {
  let subtotal = 0;
  let descuentoItems = 0;

  selectedProducts.value.forEach(item => {
    const key = `${item.tipo}-${item.id}`;
    const cantidad = parseFloat(quantities.value[key]) || 0;
    const precio = parseFloat(prices.value[key]) || 0;
    const descuento = parseFloat(discounts.value[key]) || 0;

    const subtotalItem = cantidad * precio;
    const descuentoItem = subtotalItem * (descuento / 100);

    subtotal += subtotalItem;
    descuentoItems += descuentoItem;
  });

  const subtotalConDescuentos = subtotal - descuentoItems;
  const descuentoGeneral = subtotalConDescuentos * (parseFloat(form.descuento_general) / 100);
  const subtotalFinal = subtotalConDescuentos - descuentoGeneral;
  const iva = subtotalFinal * 0.16;
  const total = subtotalFinal + iva;

  return {
  subtotal: parseFloat(subtotal.toFixed(2)),
  descuento_items: parseFloat(descuentoItems.toFixed(2)),
  descuento_general: parseFloat(descuentoGeneral.toFixed(2)),
  // 👇 CAMBIA ESTA LÍNEA 👇
  subtotalConDescuentos: parseFloat(subtotalFinal.toFixed(2)), // <- Correcto
  iva: parseFloat(iva.toFixed(2)),
  total: parseFloat(total.toFixed(2))
};
});

// Actualizar totales
const calcularTotal = () => {
  const totals = totales.value;
  form.subtotal = totals.subtotal;
  form.iva = totals.iva;
  form.total = totals.total;
};

// --- Guardar cambios ---
const actualizarVenta = () => {
  if (!form.cliente_id) {
    notyf.error('Selecciona un cliente');
    return;
  }

  if (selectedProducts.value.length === 0) {
    notyf.error('Agrega al menos un producto o servicio');
    return;
  }

  // Validar precios, cantidades, descuentos
  for (const entry of selectedProducts.value) {
    const key = `${entry.tipo}-${entry.id}`;
    const cantidad = parseFloat(quantities.value[key]);
    const precio = parseFloat(prices.value[key]);
    const descuento = parseFloat(discounts.value[key]) || 0;

    if (isNaN(cantidad) || cantidad <= 0) {
      notyf.error(`Cantidad inválida para el producto ${entry.id}`);
      return;
    }
    if (isNaN(precio) || precio < 0) {
      notyf.error(`Precio inválido para el producto ${entry.id}`);
      return;
    }
    if (isNaN(descuento) || descuento < 0 || descuento > 100) {
      notyf.error(`Descuento inválido para el producto ${entry.id}`);
      return;
    }
  }

  // Asignar productos al formulario
  form.productos = selectedProducts.value.map(entry => {
    const key = `${entry.tipo}-${entry.id}`;
    return {
      id: entry.id,
      tipo: entry.tipo,
      cantidad: parseFloat(quantities.value[key]) || 1,
      precio: parseFloat(prices.value[key]) || 0,
      descuento: parseFloat(discounts.value[key]) || 0
    };
  });

  calcularTotal();

  form.put(route('ventas.update', props.venta.id), {
    onSuccess: () => {
      localStorage.removeItem(`venta_edit_${props.venta.id}`);
      notyf.success('Venta actualizada correctamente');
    },
    onError: (errors) => {
      console.error('Errores de validación:', errors);
      const firstError = Object.values(errors)[0];
      notyf.error(Array.isArray(firstError) ? firstError[0] : firstError);
    }
  });
};

// --- Vista previa e impresión ---
const verVistaPrevia = () => {
  if (!clienteSeleccionado.value || selectedProducts.value.length === 0) {
    notyf.error('Completa cliente y productos');
    return;
  }
  mostrarVistaPrevia.value = true;
};

const imprimirVenta = async () => {
  const ventaParaPDF = {
    ...props.venta,
    cliente: clienteSeleccionado.value,
    productos: selectedProducts.value.map(entry => {
      const key = `${entry.tipo}-${entry.id}`;
      return {
        ...entry,
        cantidad: quantities.value[key],
        precio: prices.value[key],
        descuento: discounts.value[key] || 0
      };
    }),
    subtotal: totales.value.subtotal,
    descuento_general: form.descuento_general,
    iva: totales.value.iva,
    total: totales.value.total,
    fecha: form.fecha,
    numero_venta: form.numero_venta,
    notas: form.notas
  };

  try {
    notyf.success('Generando PDF...');
    await generarPDF('Venta', ventaParaPDF);
    notyf.success('PDF generado');
  } catch (error) {
    notyf.error('Error al generar PDF');
    console.error(error);
  }
};

// --- Atajos ---
const closeShortcuts = () => {
  mostrarAtajos.value = false;
};
</script>

<template>
  <Head title="Editar Venta" />

  <div class="ventas-edit min-h-screen bg-gradient-to-br from-slate-50 to-blue-50 p-6">
    <div class="max-w-6xl mx-auto">

      <!-- Header -->
      <Header
        title="Editar Venta"
        description="Modifica los detalles de la venta"
        :can-preview="clienteSeleccionado && selectedProducts.length > 0"
        :back-url="route('ventas.index')"
        @preview="verVistaPrevia"
        :show-shortcuts="mostrarAtajos"
        @close-shortcuts="closeShortcuts"
      />

      <form @submit.prevent="actualizarVenta" class="space-y-8">

        <!-- Cliente -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
          <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-4">
            <h2 class="text-lg font-semibold text-white flex items-center">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
              </svg>
              Cliente
            </h2>
          </div>
          <div class="p-6">
            <BuscarCliente
              :clientes="clientesList"
              :cliente-seleccionado="clienteSeleccionado"
              @cliente-seleccionado="onClienteSeleccionado"
            />
          </div>
        </div>

        <!-- Productos y Servicios -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
          <div class="bg-gradient-to-r from-green-500 to-green-600 px-6 py-4">
            <h2 class="text-lg font-semibold text-white flex items-center">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
              </svg>
              Productos y Servicios
            </h2>
          </div>
          <ProductosSeleccionados
            :productos="productos"
            :servicios="servicios"
            :selected-products="selectedProducts"
            @update:productos="onProductosSeleccionados"
            @eliminar-producto="eliminarProducto"
            v-model:quantities="quantities"
            v-model:prices="prices"
            v-model:discounts="discounts"
          />
        </div>

        <!-- Notas -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
          <div class="px-6 py-4 bg-gray-50 border-b">
            <h2 class="text-lg font-semibold text-gray-800">Notas (Opcional)</h2>
          </div>
          <div class="p-6">
            <textarea
              v-model="form.notas"
              rows="3"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
              placeholder="Notas adicionales para la venta..."
            ></textarea>
          </div>
        </div>

        <!-- Totales -->
       <Totales
          :show-margin-calculator="false"
          :margin-data="{ costoTotal: 0, precioVenta: 0, ganancia: 0, margenPorcentaje: 0 }"
          :totals="totales"
          :item-count="selectedProducts.length"
          :total-quantity="Object.values(quantities).reduce((sum, qty) => sum + (parseFloat(qty) || 0), 0)"
        />

        <!-- Botones -->
        <BotonesAccion
          :back-url="route('ventas.index')"
          :is-processing="form.processing"
          :can-submit="form.cliente_id && selectedProducts.length > 0"
          :button-text="form.processing ? 'Guardando...' : 'Actualizar Venta'"

        />

      </form>

      <!-- Atajos de teclado -->
      <button
        @click="mostrarAtajos = !mostrarAtajos"
        class="fixed bottom-4 left-4 bg-gray-600 text-white p-3 rounded-full shadow-lg hover:bg-gray-700 transition-colors duration-200"
        title="Mostrar atajos de teclado"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
      </button>

      <!-- Modal Vista Previa -->
      <VistaPreviaModal
        :show="mostrarVistaPrevia"
        type="venta"
        :cliente="clienteSeleccionado"
        :items="selectedProducts"
        :quantities="quantities"
        :prices="prices"
        :discounts="discounts"
        :totals="totales"
        :notas="form.notas"
        @close="mostrarVistaPrevia = false"
        @print="imprimirVenta"
      />

    </div>
  </div>
</template>

<style scoped>
.ventas-edit {
  min-height: 100vh;
  background: linear-gradient(to bottom right, #f8fafc, #e0f2fe);
}
</style>
