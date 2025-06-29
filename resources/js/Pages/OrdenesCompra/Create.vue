<template>
  <Head title="Crear Órdenes de Compra" />
  <div class="ordenes-compra-create">
    <h1 class="text-2xl font-semibold mb-6">Crear Nueva Orden de Compra</h1>
    <form @submit.prevent="crearOrdenCompra" class="space-y-6">
      <!-- Búsqueda de proveedor -->
      <div class="form-group">
        <label for="buscarProveedor" class="block text-sm font-medium text-gray-700">Buscar Proveedor</label>
        <input
          id="buscarProveedor"
          v-model="buscarProveedor"
          type="text"
          placeholder="Buscar proveedor..."
          @focus="mostrarProveedores = true"
          @blur="ocultarProveedoresDespuesDeTiempo"
          autocomplete="off"
          class="mt-1 block w-full px-4 py-2 border rounded-md focus:ring-blue-500 focus:border-blue-500"
        />
        <ul v-if="mostrarProveedores && proveedoresFiltrados.length > 0" class="absolute z-10 bg-white border rounded-md shadow-md w-full max-h-48 overflow-y-auto">
          <li
            v-for="proveedor in proveedoresFiltrados"
            :key="proveedor.id"
            @click="seleccionarProveedor(proveedor)"
            class="px-4 py-2 cursor-pointer hover:bg-gray-100 proveedor-item"
          >
            {{ proveedor.nombre_razon_social }}
          </li>
        </ul>
        <div v-if="proveedoresFiltrados.length === 0 && buscarProveedor" class="text-red-500 text-sm mt-2">
          No se encontraron proveedores.
        </div>
      </div>

      <!-- Búsqueda de productos y servicios -->
      <div class="form-group">
        <label for="buscarProducto" class="block text-sm font-medium text-gray-700">Buscar Producto/Servicio</label>
        <input
          id="buscarProducto"
          v-model="buscarProducto"
          type="text"
          placeholder="Buscar producto/servicio..."
          @focus="mostrarProductos = true"
          @blur="ocultarProductosDespuesDeTiempo"
          autocomplete="off"
          class="mt-1 block w-full px-4 py-2 border rounded-md focus:ring-blue-500 focus:border-blue-500"
        />
        <ul v-if="mostrarProductos && productosFiltrados.length > 0" class="absolute z-10 bg-white border rounded-md shadow-md w-full max-h-48 overflow-y-auto">
          <li
            v-for="item in productosFiltrados"
            :key="item.id"
            @click="agregarProducto(item)"
            class="px-4 py-2 cursor-pointer hover:bg-gray-100 producto-item"
          >
            {{ item.nombre }} ({{ item.tipo }}) (Disponible: {{ item.stock || 'N/A' }})
          </li>
        </ul>
        <div v-if="productosFiltrados.length === 0 && buscarProducto" class="text-red-500 text-sm mt-2">
          No se encontraron productos/servicios.
        </div>
      </div>

      <!-- Lista de productos y servicios seleccionados -->
      <div v-if="selectedItems.length > 0" class="mt-4">
        <h3 class="text-lg font-medium mb-4">Productos/Servicios Seleccionados</h3>
        <div v-for="(entry, index) in selectedItems" :key="index" class="flex items-center justify-between bg-white border rounded-md shadow-sm p-4 mb-4">
          <div class="flex items-center space-x-4 w-full">
            <span class="font-medium text-gray-800 w-1/3">{{ getItemById(entry)?.nombre || 'Item no encontrado' }}</span>
            <div class="flex flex-col w-1/4">
              <label :for="`cantidad-${entry.tipo}-${entry.id}`" class="text-sm font-medium text-gray-700">Cantidad</label>
              <input
                :id="`cantidad-${entry.tipo}-${entry.id}`"
                v-model.number="quantities[`${entry.tipo}-${entry.id}`]"
                type="number"
                class="px-4 py-2 border rounded-md mt-1 w-full"
                min="1"
                @input="calcularTotal"
              />
            </div>
            <div class="flex flex-col w-1/4">
              <label :for="`precio-${entry.tipo}-${entry.id}`" class="text-sm font-medium text-gray-700">Precio de Compra</label>
              <input
                :id="`precio-${entry.tipo}-${entry.id}`"
                v-model.number="prices[`${entry.tipo}-${entry.id}`]"
                type="number"
                class="px-4 py-2 border rounded-md mt-1 w-full"
                min="0"
                @input="calcularTotal"
              />
            </div>
            <div class="flex flex-col w-1/4">
              <label :for="`subtotal-${entry.tipo}-${entry.id}`" class="text-sm font-medium text-gray-700">Subtotal</label>
              <input
                :id="`subtotal-${entry.tipo}-${entry.id}`"
                :value="(quantities[`${entry.tipo}-${entry.id}`] * prices[`${entry.tipo}-${entry.id}`]).toFixed(2)"
                type="text"
                class="px-4 py-2 border rounded-md mt-1 w-full bg-gray-100 cursor-not-allowed"
                readonly
              />
            </div>
          </div>
          <button
            type="button"
            @click="eliminarItem(entry)"
            class="text-red-500 hover:text-red-700 ml-4"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>

      <!-- Total -->
      <div class="form-group">
        <label for="total" class="block text-sm font-medium text-gray-700">Total</label>
        <input
          id="total"
          v-model="form.total"
          type="text"
          readonly
          class="mt-1 block w-full px-4 py-2 border rounded-md bg-gray-100 cursor-not-allowed"
        />
      </div>

      <!-- Botones -->
      <div class="flex justify-end space-x-4">
        <Link :href="route('ordenescompra.index')" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">
          Cancelar
        </Link>
        <button
          type="submit"
          class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 disabled:bg-gray-400"
          :disabled="!form.proveedor_id || selectedItems.length === 0"
        >
          Guardar Orden de Compra
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

// Define el layout del dashboard
defineOptions({ layout: AppLayout });

const props = defineProps({
  proveedores: Array,
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
  proveedor_id: '', // Cambiado de cliente_id a proveedor_id
  total: 0,
  items: [], // Cambiado de 'productos' a 'items' para incluir ambos tipos
});

const buscarProveedor = ref(''); // Cambiado de buscarCliente
const buscarProducto = ref('');
const mostrarProveedores = ref(false); // Cambiado de mostrarClientes
const mostrarProductos = ref(false);
const selectedItems = ref([]); // Cambiado de selectedProducts
const quantities = ref({});
const prices = ref({});
const proveedorSeleccionado = ref(null); // Cambiado de clienteSeleccionado

const proveedoresFiltrados = computed(() => { // Cambiado de clientesFiltrados
  if (!buscarProveedor.value) return [];
  return props.proveedores.filter((proveedor) =>
    proveedor.nombre_razon_social.toLowerCase().includes(buscarProveedor.value.toLowerCase())
  );
});

const productosFiltrados = computed(() => {
  if (!buscarProducto.value) return [];
  const productosYServicios = [
    ...(props.productos || []).map(producto => ({
      ...producto,
      tipo: 'producto',
    })),
    ...(props.servicios || []).map(servicio => ({
      ...servicio,
      tipo: 'servicio',
    })),
  ];

  return productosYServicios.filter(item =>
    item.nombre.toLowerCase().includes(buscarProducto.value.toLowerCase()) ||
    (item.codigo?.toLowerCase().includes(buscarProducto.value.toLowerCase())) ||
    (item.numero_de_serie?.toLowerCase().includes(buscarProducto.value.toLowerCase())) ||
    (item.codigo_barras?.toLowerCase().includes(buscarProducto.value.toLowerCase()))
  );
});

const getItemById = (entry) => { // Cambiado de getProductById
  if (!entry || !entry.id || !entry.tipo) {
    console.error('Entrada inválida para getItemById:', entry);
    return null;
  }
  if (entry.tipo === 'producto') {
    const producto = props.productos.find((p) => p.id === entry.id);
    return producto || null;
  }
  if (entry.tipo === 'servicio') {
    const servicio = props.servicios.find((s) => s.id === entry.id);
    return servicio || null;
  }
  console.error(`No se encontró item con ID: ${entry.id} y tipo: ${entry.tipo}`);
  return null;
};

const calcularTotal = () => {
  let total = 0;
  for (const entry of selectedItems.value) { // Cambiado de selectedProducts
    const key = `${entry.tipo}-${entry.id}`;
    const cantidad = Number.parseFloat(quantities.value[key]) || 0;
    const precio = Number.parseFloat(prices.value[key]) || 0;
    total += cantidad * precio;
  }
  form.total = total.toFixed(2);
};

watch(quantities, calcularTotal, { deep: true });
watch(prices, calcularTotal, { deep: true });

const seleccionarProveedor = (proveedor) => { // Cambiado de seleccionarCliente
  form.proveedor_id = proveedor.id; // Cambiado de cliente_id
  buscarProveedor.value = proveedor.nombre_razon_social; // Cambiado de buscarCliente
  proveedorSeleccionado.value = proveedor.nombre_razon_social; // Cambiado de clienteSeleccionado
  mostrarProveedores.value = false; // Cambiado de mostrarClientes
};

const agregarProducto = (item) => {
  console.log('Item seleccionado:', item);
  const itemEntry = { id: item.id, tipo: item.tipo };
  const exists = selectedItems.value.some( // Cambiado de selectedProducts
    (entry) => entry.id === item.id && entry.tipo === item.tipo
  );
  if (!exists) {
    selectedItems.value.push(itemEntry); // Cambiado de selectedProducts
    quantities.value[`${item.tipo}-${item.id}`] = 1;
    // Para órdenes de compra, se usa el precio_compra del producto o el precio del servicio.
    prices.value[`${item.tipo}-${item.id}`] = item.tipo === 'producto' ? (item.precio_compra || 0) : (item.precio || 0);
    console.log('Precio asignado:', prices.value[`${item.tipo}-${item.id}`]);
  }
  buscarProducto.value = '';
  mostrarProductos.value = false;
  calcularTotal();
};

const eliminarItem = (entry) => { // Cambiado de eliminarProducto
  selectedItems.value = selectedItems.value.filter( // Cambiado de selectedProducts
    (item) => !(item.id === entry.id && item.tipo === entry.tipo)
  );
  delete quantities.value[`${entry.tipo}-${entry.id}`];
  delete prices.value[`${entry.tipo}-${entry.id}`];
  calcularTotal();
};

const ocultarProveedoresDespuesDeTiempo = (event) => { // Cambiado de ocultarClientesDespuesDeTiempo
  setTimeout(() => {
    if (!event.relatedTarget || !event.relatedTarget.classList.contains('proveedor-item')) { // Cambiado de cliente-item
      mostrarProveedores.value = false; // Cambiado de mostrarClientes
    }
  }, 300);
};

const ocultarProductosDespuesDeTiempo = (event) => {
  setTimeout(() => {
    if (!event.relatedTarget || !event.relatedTarget.classList.contains('producto-item')) {
      mostrarProductos.value = false;
    }
  }, 300);
};

watch(
  [() => form.proveedor_id, selectedItems, quantities, prices, proveedorSeleccionado], // Cambiado de cliente_id y clienteSeleccionado, selectedProducts
  () => {
    const dataToSave = {
      proveedor_id: form.proveedor_id, // Cambiado de cliente_id
      proveedor_nombre: proveedorSeleccionado.value, // Cambiado de cliente_nombre
      selectedItems: selectedItems.value, // Cambiado de selectedProducts
      quantities: quantities.value,
      prices: prices.value,
    };
    localStorage.setItem('ordenCompraEnProgreso', JSON.stringify(dataToSave)); // Cambiado de pedidoEnProgreso
  },
  { deep: true }
);

onMounted(() => {
  console.log('Proveedores:', props.proveedores); // Cambiado de Clientes
  console.log('Productos:', props.productos);
  console.log('Servicios:', props.servicios);
  const savedData = localStorage.getItem('ordenCompraEnProgreso'); // Cambiado de pedidoEnProgreso
  if (savedData) {
    const parsedData = JSON.parse(savedData);
    console.log('Datos cargados desde localStorage:', parsedData);
    form.proveedor_id = parsedData.proveedor_id; // Cambiado
    proveedorSeleccionado.value = parsedData.proveedor_nombre; // Cambiado
    buscarProveedor.value = parsedData.proveedor_nombre; // Cambiado
    selectedItems.value = Array.isArray(parsedData.selectedItems) // Cambiado
      ? parsedData.selectedItems.filter(
          (entry) => entry && typeof entry === 'object' && 'id' in entry && 'tipo' in entry
        )
      : [];
    quantities.value = parsedData.quantities || {};
    prices.value = parsedData.prices || {};
    calcularTotal();
  }
  window.addEventListener('beforeunload', handleBeforeUnload);
});

onBeforeUnmount(() => {
  window.removeEventListener('beforeunload', handleBeforeUnload);
});

const handleBeforeUnload = (event) => {
  if (form.proveedor_id || selectedItems.value.length > 0) { // Cambiado de cliente_id y selectedProducts
    event.preventDefault();
    event.returnValue = '';
  }
};

const crearOrdenCompra = () => { // Cambiado de crearPedido
  form.items = selectedItems.value.map((entry) => ({ // Cambiado de form.productos y selectedProducts
    id: entry.id,
    tipo: entry.tipo,
    cantidad: quantities.value[`${entry.tipo}-${entry.id}`] || 1,
    precio: prices.value[`${entry.tipo}-${entry.id}`] || 0,
  }));

  form.post(route('ordenescompra.store'), { // Cambiado a 'ordenescompra.store'
    onSuccess: () => {
      localStorage.removeItem('ordenCompraEnProgreso'); // Cambiado
      selectedItems.value = []; // Cambiado
      quantities.value = {};
      prices.value = {};
      router.visit(route('ordenescompra.index')); // REDIRECCIÓN AÑADIDA
    },
    onError: (error) => {
      console.error('Error al crear la orden de compra:', error); // Mensaje actualizado
    },
  });
};

const formatearLocalStorage = () => {
  localStorage.removeItem('ordenCompraEnProgreso'); // Cambiado
  form.proveedor_id = ''; // Cambiado
  proveedorSeleccionado.value = null; // Cambiado
  buscarProveedor.value = ''; // Cambiado
  selectedItems.value = []; // Cambiado
  quantities.value = {};
  prices.value = {};
  calcularTotal();
};
</script>

<style scoped>
.form-group {
  margin-bottom: 1rem;
}
</style>
