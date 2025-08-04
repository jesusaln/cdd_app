<template>
  <Head title="Crear Pedidos" />
  <div class="pedidos-create min-h-screen bg-gradient-to-br from-slate-50 to-blue-50 p-6">
    <div class="max-w-6xl mx-auto">
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Crear Nuevo Pedido</h1>
        <p class="text-gray-600">Crea un nuevo pedido para tus clientes</p>
      </div>

      <form @submit.prevent="crearPedido" class="space-y-8">
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
          <div class="relative p-6">
            <BuscarCliente
              ref="buscarClienteRef"
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
              ref="buscarProductoRef"
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
              :mostrarCalculadoraMargen="mostrarCalculadoraMargen"
              @eliminar-producto="eliminarProducto"
              @update-quantity="updateQuantity"
              @update-discount="updateDiscount"
              @calcular-total="calcularTotal"
              @mostrar-margen="mostrarMargenProducto"
            />
          </div>
        </div>

        <!-- Total -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
          <div class="bg-gradient-to-r from-purple-500 to-purple-600 px-6 py-4">
            <h2 class="text-lg font-semibold text-white flex items-center">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
              </svg>
              Resumen del Pedido
            </h2>
          </div>
          <div class="p-6">
            <div class="text-right">
              <div class="text-sm text-gray-600">Total:</div>
              <div class="text-2xl font-bold text-purple-600">
                ${{ form.total }}
              </div>
            </div>
          </div>
        </div>

        <!-- Botones -->
        <div class="flex justify-end space-x-4">
          <Link :href="route('pedidos.index')" class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 hover:border-gray-400 transition-all duration-200">
            Cancelar
          </Link>
          <button
            type="submit"
            class="inline-flex items-center justify-center px-6 py-3 border border-transparent rounded-lg text-sm font-medium text-white bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 shadow-lg hover:shadow-xl"
            :disabled="!form.cliente_id || selectedProducts.length === 0"
          >
            Guardar Pedido
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted, onBeforeUnmount } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import axios from 'axios';
import { Notyf } from 'notyf';
import AppLayout from '@/Layouts/AppLayout.vue';
import BuscarCliente from '@/Components/BuscarCliente.vue';
import BuscarProducto from '@/Components/BuscarProducto.vue';
import ProductosSeleccionados from '@/Components/ProductosSeleccionados.vue';

// Configura Notyf
const notyf = new Notyf({
  duration: 5000,
  position: { x: 'right', y: 'bottom' },
  types: [
    { type: 'success', background: '#10B981', icon: { className: 'notyf__icon--success', tagName: 'i', text: '✓' } },
    { type: 'error', background: '#EF4444', icon: { className: 'notyf__icon--error', tagName: 'i', text: '✗' } },
    { type: 'info', background: '#3B82F6', icon: { className: 'notyf__icon--info', tagName: 'i', text: 'ℹ' } },
  ],
});

// Función para mostrar notificaciones
const showNotification = (message, type = 'success') => {
  console.log('showNotification:', { message, type }); // Depuración
  notyf.open({ type, message, duration: 5000, ripple: true, dismissible: true });
};

defineOptions({ layout: AppLayout });

const props = defineProps({
  clientes: Array,
  productos: { type: Array, default: () => [] },
  servicios: { type: Array, default: () => [] },
});

const form = useForm({
  cliente_id: '',
  total: 0,
  productos: [],
});

const buscarClienteRef = ref(null);
const buscarProductoRef = ref(null);
const selectedProducts = ref([]);
const quantities = ref({});
const prices = ref({});
const discounts = ref({});
const clienteSeleccionado = ref(null);
const mostrarCalculadoraMargen = ref(false);

const getProductById = (entry) => {
  if (!entry || !entry.id || !entry.tipo) {
    console.error('Entrada inválida para getProductById:', entry);
    return null;
  }
  return entry.tipo === 'producto'
    ? props.productos.find((p) => p.id === entry.id) || null
    : props.servicios.find((s) => s.id === entry.id) || null;
};

const calcularTotal = () => {
  console.log('Calculando total'); // Depuración
  let total = 0;
  let descuentoItems = 0;
  for (const entry of selectedProducts.value) {
    const key = `${entry.tipo}-${entry.id}`;
    const cantidad = Number.parseFloat(quantities.value[key]) || 0;
    const precio = Number.parseFloat(prices.value[key]) || 0;
    const descuento = Number.parseFloat(discounts.value[key]) || 0;
    const subtotalItem = cantidad * precio;
    descuentoItems += subtotalItem * (descuento / 100);
    total += subtotalItem;
  }
  form.total = (total - descuentoItems).toFixed(2);
};

const onClienteSeleccionado = (cliente) => {
  console.log('onClienteSeleccionado:', cliente); // Depuración
  try {
    if (!cliente) {
      clienteSeleccionado.value = null;
      form.cliente_id = '';
      form.clearErrors('cliente_id');
      showNotification('Selección de cliente limpiada', 'info');
      return;
    }
    if (clienteSeleccionado.value && clienteSeleccionado.value.id === cliente.id) {
      console.log('Cliente ya seleccionado, ignorando'); // Depuración
      return;
    }
    clienteSeleccionado.value = cliente;
    form.cliente_id = cliente.id;
    form.clearErrors('cliente_id');
    showNotification(`Cliente seleccionado: ${cliente.nombre_razon_social}`);
  } catch (error) {
    console.error('Error en onClienteSeleccionado:', error);
    showNotification('Error al seleccionar cliente', 'error');
  }
};

const crearNuevoCliente = async (nombreBuscado) => {
  console.log('crearNuevoCliente:', nombreBuscado); // Depuración
  try {
    const response = await axios.post(route('clientes.store'), { nombre_razon_social: nombreBuscado });
    const nuevoCliente = response.data;
    props.clientes.push(nuevoCliente);
    onClienteSeleccionado(nuevoCliente);
    showNotification(`Cliente creado: ${nuevoCliente.nombre_razon_social}`);
  } catch (error) {
    console.error('Error al crear cliente:', error);
    showNotification('Error al crear cliente', 'error');
  }
};

const limpiarCliente = () => {
  console.log('limpiarCliente'); // Depuración
  clienteSeleccionado.value = null;
  form.cliente_id = '';
  form.clearErrors('cliente_id');
  showNotification('Cliente deseleccionado', 'info');
};

const agregarProducto = (item) => {
  console.log('agregarProducto:', item); // Depuración
  const itemEntry = { id: item.id, tipo: item.tipo };
  const exists = selectedProducts.value.some(
    (entry) => entry.id === item.id && entry.tipo === item.tipo
  );
  if (!exists) {
    selectedProducts.value.push(itemEntry);
    quantities.value[`${item.tipo}-${item.id}`] = 1;
    prices.value[`${item.tipo}-${item.id}`] = item.tipo === 'producto' ? (item.precio_venta || 0) : (item.precio || 0);
    discounts.value[`${item.tipo}-${item.id}`] = 0;
    calcularTotal();
    showNotification(`Producto añadido: ${item.nombre || item.descripcion}`);
  }
};

const eliminarProducto = (entry) => {
  console.log('eliminarProducto:', entry); // Depuración
  const item = getProductById(entry);
  selectedProducts.value = selectedProducts.value.filter(
    (item) => !(item.id === entry.id && item.tipo === entry.tipo)
  );
  delete quantities.value[`${entry.tipo}-${entry.id}`];
  delete prices.value[`${entry.tipo}-${entry.id}`];
  delete discounts.value[`${entry.tipo}-${entry.id}`];
  calcularTotal();
  showNotification(`Producto eliminado: ${item?.nombre || item?.descripcion || 'Item'}`, 'info');
};

const updateQuantity = (key, quantity) => {
  console.log('updateQuantity:', { key, quantity }); // Depuración
  quantities.value[key] = quantity;
  calcularTotal();
};

const updateDiscount = (key, discount) => {
  console.log('updateDiscount:', { key, discount }); // Depuración
  discounts.value[key] = discount;
  calcularTotal();
};

const mostrarMargenProducto = () => {
  console.log('mostrarMargenProducto:', mostrarCalculadoraMargen.value); // Depuración
  mostrarCalculadoraMargen.value = !mostrarCalculadoraMargen.value;
};

watch(
  [() => form.cliente_id, selectedProducts, quantities, prices, discounts, clienteSeleccionado],
  () => {
    console.log('Guardando en localStorage'); // Depuración
    const dataToSave = {
      cliente_id: form.cliente_id,
      cliente: clienteSeleccionado.value,
      selectedProducts: selectedProducts.value,
      quantities: quantities.value,
      prices: prices.value,
      discounts: discounts.value,
    };
    localStorage.setItem('pedidoEnProgreso', JSON.stringify(dataToSave));
  },
  { deep: true }
);

onMounted(() => {
  console.log('onMounted - Clientes:', props.clientes); // Depuración
  console.log('onMounted - Productos:', props.productos); // Depuración
  console.log('onMounted - Servicios:', props.servicios); // Depuración
  const savedData = localStorage.getItem('pedidoEnProgreso');
  if (savedData) {
    const parsedData = JSON.parse(savedData);
    form.cliente_id = parsedData.cliente_id;
    clienteSeleccionado.value = parsedData.cliente || null;
    selectedProducts.value = Array.isArray(parsedData.selectedProducts)
      ? parsedData.selectedProducts.filter(
          (entry) => entry && typeof entry === 'object' && 'id' in entry && 'tipo' in entry
        )
      : [];
    quantities.value = parsedData.quantities || {};
    prices.value = parsedData.prices || {};
    discounts.value = parsedData.discounts || {};
    calcularTotal();
  }
  window.addEventListener('beforeunload', handleBeforeUnload);
});

onBeforeUnmount(() => {
  window.removeEventListener('beforeunload', handleBeforeUnload);
});

const handleBeforeUnload = (event) => {
  if (form.cliente_id || selectedProducts.value.length > 0) {
    event.preventDefault();
    event.returnValue = '';
  }
};

const crearPedido = () => {
  console.log('crearPedido'); // Depuración
  form.productos = selectedProducts.value.map((entry) => ({
    id: entry.id,
    tipo: entry.tipo,
    cantidad: quantities.value[`${entry.tipo}-${entry.id}`] || 1,
    precio: prices.value[`${entry.tipo}-${entry.id}`] || 0,
    descuento: discounts.value[`${entry.tipo}-${entry.id}`] || 0,
  }));

  form.post(route('pedidos.store'), {
    onSuccess: () => {
      console.log('Pedido creado con éxito'); // Depuración
      localStorage.removeItem('pedidoEnProgreso');
      selectedProducts.value = [];
      quantities.value = {};
      prices.value = {};
      discounts.value = {};
      clienteSeleccionado.value = null;
      form.reset();
      showNotification('Pedido creado con éxito');
    },
    onError: (error) => {
      console.error('Error al crear el pedido:', error);
      showNotification('Error al crear el pedido', 'error');
    },
  });
};

const formatearLocalStorage = () => {
  console.log('formatearLocalStorage'); // Depuración
  localStorage.removeItem('pedidoEnProgreso');
  form.cliente_id = '';
  clienteSeleccionado.value = null;
  selectedProducts.value = [];
  quantities.value = {};
  prices.value = {};
  discounts.value = {};
  buscarClienteRef.value.limpiarBusqueda();
  calcularTotal();
  showNotification('Datos locales limpiados', 'info');
};
</script>

<style scoped>
.form-group {
  margin-bottom: 1rem;
}
</style>
