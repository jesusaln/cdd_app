<!-- resources/js/Pages/Pedidos/Edit.vue -->
<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { Notyf } from 'notyf';
import AppLayout from '@/Layouts/AppLayout.vue';
import Header from '@/Components/CreateComponents/Header.vue';
import BuscarCliente from '@/Components/CreateComponents/BuscarCliente.vue';
import BuscarProducto from '@/Components/CreateComponents/BuscarProducto.vue';
import ProductosSeleccionados from '@/Components/CreateComponents/ProductosSeleccionados.vue';
import Totales from '@/Components/CreateComponents/Totales.vue';
import BotonesAccion from '@/Components/CreateComponents/BotonesAccion.vue';
import VistaPreviaModal from '@/Components/Modals/VistaPreviaModal.vue';

const notyf = new Notyf({
  duration: 5000,
  position: { x: 'right', y: 'bottom' },
  types: [
    { type: 'success', background: '#10B981', icon: { className: 'notyf__icon--success', tagName: 'i', text: '✓' } },
    { type: 'error', background: '#EF4444', icon: { className: 'notyf__icon--error', tagName: 'i', text: '✗' } },
    { type: 'info', background: '#3B82F6', icon: { className: 'notyf__icon--info', tagName: 'i', text: 'ℹ' } },
  ],
});

const showNotification = (message, type = 'success') => {
  notyf.open({ type, message });
};

defineOptions({ layout: AppLayout });

const props = defineProps({
  pedido: Object,
  clientes: Array,
  productos: Array,
  servicios: Array,
});

// --- FORMULARIO ---
const form = useForm({
  cliente_id: props.pedido.cliente.id,
  subtotal: props.pedido.subtotal,
  descuento_general: props.pedido.descuento_general || 0,
  descuento_items: 0, // calculado dinámicamente
  iva: props.pedido.iva,
  total: props.pedido.total,
  productos: [],
  notas: props.pedido.notas || '',
});

// --- ESTADO ---
const selectedProducts = ref([]);
const quantities = ref({});
const prices = ref({});
const discounts = ref({});
const clienteSeleccionado = ref(props.pedido.cliente);
const mostrarVistaPrevia = ref(false);
const mostrarAtajos = ref(true);

// --- CARGAR DATOS DEL PEDIDO ---
onMounted(() => {
  // Recorre todos los ítems (productos y servicios combinados)
  props.pedido.productos.forEach(item => {
    const key = `${item.tipo}-${item.id}`;

    // Añade el ítem (producto o servicio)
    selectedProducts.value.push({
      id: item.id,
      tipo: item.tipo,
      nombre: item.nombre, // opcional: para mostrar
    });

    // Restaura cantidad, precio y descuento desde el pivot
    quantities.value[key] = item.pivot.cantidad;
    prices.value[key] = item.pivot.precio;
    discounts.value[key] = item.pivot.descuento || 0;
  });

  // Recalcula totales
  calcularTotal();
});

// --- FUNCIONES ---

const handlePreview = () => {
  if (clienteSeleccionado.value && selectedProducts.value.length > 0) {
    mostrarVistaPrevia.value = true;
  } else {
    showNotification('Selecciona un cliente y al menos un producto', 'error');
  }
};

const closeShortcuts = () => {
  mostrarAtajos.value = false;
};

const onClienteSeleccionado = (cliente) => {
  if (!cliente) {
    clienteSeleccionado.value = null;
    form.cliente_id = '';
    showNotification('Cliente eliminado', 'info');
    return;
  }
  clienteSeleccionado.value = cliente;
  form.cliente_id = cliente.id;
  showNotification(`Cliente: ${cliente.nombre_razon_social}`);
};

const crearNuevoCliente = async (nombreBuscado) => {
  try {
    const response = await axios.post(route('clientes.store'), { nombre_razon_social: nombreBuscado });
    const nuevoCliente = response.data;
    props.clientes.push(nuevoCliente);
    onClienteSeleccionado(nuevoCliente);
    showNotification(`Cliente creado: ${nuevoCliente.nombre_razon_social}`);
  } catch (error) {
    console.error('Error al crear cliente:', error);
    showNotification('No se pudo crear el cliente', 'error');
  }
};

const agregarProducto = (item) => {
  const itemEntry = { id: item.id, tipo: item.tipo };
  const exists = selectedProducts.value.some(
    (entry) => entry.id === item.id && entry.tipo === item.tipo
  );
  if (!exists) {
    selectedProducts.value.push(itemEntry);
    const key = `${item.tipo}-${item.id}`;
    quantities.value[key] = 1;
    prices.value[key] = item.tipo === 'producto' ? (item.precio_venta || 0) : (item.precio || 0);
    discounts.value[key] = 0;
    calcularTotal();
    showNotification(`Añadido: ${item.nombre || item.descripcion}`);
  }
};

const eliminarProducto = (entry) => {
  const key = `${entry.tipo}-${entry.id}`;
  selectedProducts.value = selectedProducts.value.filter(
    (item) => !(item.id === entry.id && item.tipo === entry.tipo)
  );
  delete quantities.value[key];
  delete prices.value[key];
  delete discounts.value[key];
  calcularTotal();
  showNotification(`Eliminado: ${entry.nombre || entry.descripcion || 'Item'}`, 'info');
};

const updateQuantity = (key, quantity) => {
  quantities.value[key] = quantity;
  calcularTotal();
};

const updateDiscount = (key, discount) => {
  discounts.value[key] = discount;
  calcularTotal();
};

// --- CÁLCULOS ---
const totales = computed(() => {
  let subtotal = 0;
  let descuentoItems = 0;

  selectedProducts.value.forEach(entry => {
    const key = `${entry.tipo}-${entry.id}`;
    const cantidad = parseFloat(quantities.value[key]) || 0;
    const precio = parseFloat(prices.value[key]) || 0;
    const descuento = parseFloat(discounts.value[key]) || 0;
    const subtotalItem = cantidad * precio;
    descuentoItems += subtotalItem * (descuento / 100);
    subtotal += subtotalItem;
  });

  const subtotalConDescuentoItems = subtotal - descuentoItems;
  const descuentoGeneralMonto = subtotalConDescuentoItems * (form.descuento_general / 100);
  const subtotalConDescuentos = subtotalConDescuentoItems - descuentoGeneralMonto;
  const iva = subtotalConDescuentos * 0.16;
  const total = subtotalConDescuentos + iva;

  return {
    subtotal,
    descuentoItems,
    descuentoGeneral: descuentoGeneralMonto,
    subtotalConDescuentos,
    iva,
    total,
  };
});

const calcularTotal = () => {
  const totals = totales.value;
  form.subtotal = totals.subtotal;
  form.descuento_general = totals.descuentoGeneral;
  form.descuento_items = totals.descuentoItems;
  form.iva = totals.iva;
  form.total = totals.total;
};

// --- GUARDAR CAMBIOS ---
const actualizarPedido = () => {
  if (!form.cliente_id) {
    showNotification('Selecciona un cliente', 'error');
    return;
  }
  if (selectedProducts.value.length === 0) {
    showNotification('Agrega al menos un producto o servicio', 'error');
    return;
  }

  for (const entry of selectedProducts.value) {
    const key = `${entry.tipo}-${entry.id}`;
    const discount = discounts.value[key] || 0;
    if (discount < 0 || discount > 100) {
      showNotification('Los descuentos deben estar entre 0% y 100%.', 'error');
      return;
    }
  }

  if (form.descuento_general < 0 || form.descuento_general > 100) {
    showNotification('El descuento general debe estar entre 0% y 100%.', 'error');
    return;
  }

  form.productos = selectedProducts.value.map(entry => {
    const key = `${entry.tipo}-${entry.id}`;
    return {
      id: entry.id,
      tipo: entry.tipo,
      cantidad: quantities.value[key] || 1,
      precio: prices.value[key] || 0,
      descuento: discounts.value[key] || 0,
    };
  });

  calcularTotal();

  form.put(route('pedidos.update', props.pedido.id), {
    onSuccess: () => {
      showNotification('Pedido actualizado con éxito');
    },
    onError: (errors) => {
      console.error('Errores:', errors);
      showNotification('Hubo errores', 'error');
    },
  });
};
</script>

<template>
  <Head title="Editar Pedido" />
  <div class="pedidos-edit min-h-screen bg-gradient-to-br from-slate-50 to-blue-50 p-6">
    <div class="max-w-6xl mx-auto">
      <Header
        title="Editar Pedido"
        description="Modifica los detalles del pedido"
        :can-preview="clienteSeleccionado && selectedProducts.length > 0"
        :back-url="route('pedidos.index')"
        :show-shortcuts="mostrarAtajos"
        @preview="handlePreview"
        @close-shortcuts="closeShortcuts"
      />

      <form @submit.prevent="actualizarPedido" class="space-y-8">
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
              :clientes="clientes"
              :cliente-seleccionado="clienteSeleccionado"
              @cliente-seleccionado="onClienteSeleccionado"
              @crear-nuevo-cliente="crearNuevoCliente"
            />
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
              :quantities="quantities"
              :prices="prices"
              :discounts="discounts"
              @eliminar-producto="eliminarProducto"
              @update-quantity="updateQuantity"
              @update-discount="updateDiscount"
            />
          </div>
        </div>

        <!-- Totales -->
        <Totales
          :show-margin-calculator="false"
          :margin-data="{ costoTotal: 0, precioVenta: 0, ganancia: 0, margenPorcentaje: 0 }"
          :descuento-general="form.descuento_general"
          :totals="totales"
          :item-count="selectedProducts.length"
          :total-quantity="Object.values(quantities).reduce((sum, qty) => sum + (qty || 0), 0)"
          @update:descuento-general="val => form.descuento_general = val"
        />

        <!-- Botones -->
        <BotonesAccion
          :back-url="route('pedidos.index')"
          :is-processing="form.processing"
          :can-submit="form.cliente_id && selectedProducts.length > 0"
          :button-text="form.processing ? 'Guardando...' : 'Actualizar Pedido'"
        />
      </form>

      <!-- Botón ayuda -->
      <button
        @click="mostrarAtajos = !mostrarAtajos"
        class="fixed bottom-4 left-4 bg-gray-600 text-white p-3 rounded-full shadow-lg hover:bg-gray-700 transition-colors duration-200"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
      </button>
    </div>

    <!-- Modal Vista Previa -->
    <VistaPreviaModal
      :show="mostrarVistaPrevia"
      type="pedido"
      :cliente="clienteSeleccionado"
      :items="selectedProducts"
      :totals="totales"
      :descuento-general="form.descuento_general"
      :notas="form.notas"
      @close="mostrarVistaPrevia = false"
      @print="() => window.print()"
    />
  </div>
</template>
