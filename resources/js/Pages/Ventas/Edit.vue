<template>
    <Head title="Editar Venta" />
    <div class="ventas-edit">
      <h1 class="text-2xl font-semibold mb-6">Editar Venta</h1>
      <form @submit.prevent="actualizarVenta" class="space-y-6">
        <!-- Búsqueda de cliente -->
        <div class="form-group">
          <label for="buscarCliente" class="block text-sm font-medium text-gray-700">Buscar Cliente</label>
          <input
            v-model="buscarCliente"
            type="text"
            placeholder="Buscar cliente..."
            @focus="mostrarClientes = true"
            @blur="ocultarClientesDespuesDeTiempo"
            class="mt-1 block w-full px-4 py-2 border rounded-md focus:ring-blue-500 focus:border-blue-500"
          />
          <ul v-if="mostrarClientes && clientesFiltrados.length > 0" class="absolute z-10 bg-white border rounded-md shadow-md w-full max-h-48 overflow-y-auto">
            <li
              v-for="cliente in clientesFiltrados"
              :key="cliente.id"
              @click="seleccionarCliente(cliente)"
              class="px-4 py-2 cursor-pointer hover:bg-gray-100"
            >
              {{ cliente.nombre_razon_social }}
            </li>
          </ul>
          <div v-if="clientesFiltrados.length === 0 && buscarCliente" class="text-red-500 text-sm mt-2">
            No se encontraron clientes.
          </div>
        </div>

        <!-- Búsqueda de productos -->
        <div class="form-group">
          <label for="buscarProducto" class="block text-sm font-medium text-gray-700">Buscar Producto</label>
          <input
            v-model="buscarProducto"
            type="text"
            placeholder="Buscar producto..."
            @focus="mostrarProductos = true"
            @blur="ocultarProductosDespuesDeTiempo"
            class="mt-1 block w-full px-4 py-2 border rounded-md focus:ring-blue-500 focus:border-blue-500"
          />
          <ul v-if="mostrarProductos && productosFiltrados.length > 0" class="absolute z-10 bg-white border rounded-md shadow-md w-full max-h-48 overflow-y-auto">
            <li
              v-for="producto in productosFiltrados"
              :key="producto.id"
              @click="agregarProducto(producto)"
              class="px-4 py-2 cursor-pointer hover:bg-gray-100"
            >
              {{ producto.nombre }}
            </li>
          </ul>
          <div v-if="productosFiltrados.length === 0 && buscarProducto" class="text-red-500 text-sm mt-2">
            No se encontraron productos.
          </div>
        </div>

        <!-- Lista de productos seleccionados -->
        <div v-if="selectedProducts.length > 0" class="mt-4">
          <h3 class="text-lg font-medium mb-4">Productos Seleccionados</h3>
          <div v-for="(productoId, index) in selectedProducts" :key="index" class="flex items-center justify-between bg-white border rounded-md shadow-sm p-4 mb-4">
            <div class="flex items-center space-x-4 w-full">
              <span class="font-medium text-gray-800 w-1/3">{{ getProductById(productoId)?.nombre }}</span>
              <div class="flex flex-col w-1/4">
                <label for="cantidad" class="text-sm font-medium text-gray-700">Cantidad</label>
                <input
                  v-model.number="quantities[productoId]"
                  type="number"
                  class="px-4 py-2 border rounded-md mt-1 w-full"
                  min="1"
                  @input="calcularTotal"
                />
              </div>
              <div class="flex flex-col w-1/4">
                <label for="precio_venta" class="text-sm font-medium text-gray-700">Precio de Venta</label>
                <input
                  v-model.number="prices[productoId]"
                  type="number"
                  class="px-4 py-2 border rounded-md mt-1 w-full"
                  min="0"
                  @input="calcularTotal"
                />
              </div>
              <div class="flex flex-col w-1/4">
                <label for="subtotal" class="text-sm font-medium text-gray-700">Subtotal</label>
                <input
                  :value="(quantities[productoId] * prices[productoId]).toFixed(2)"
                  type="text"
                  class="px-4 py-2 border rounded-md mt-1 w-full bg-gray-100 cursor-not-allowed"
                  readonly
                />
              </div>
            </div>
            <button
              type="button"
              @click="eliminarProducto(productoId)"
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
            v-model="form.total"
            type="text"
            readonly
            class="mt-1 block w-full px-4 py-2 border rounded-md bg-gray-100 cursor-not-allowed"
          />
        </div>

        <!-- Botones -->
        <div class="flex justify-end space-x-4">
          <Link :href="route('ventas.index')" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">
            Cancelar
          </Link>
          <button
            type="submit"
            class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 disabled:bg-gray-400"
            :disabled="!form.cliente_id || selectedProducts.length === 0"
          >
            Guardar Cambios
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
    venta: Object,
    clientes: Array,
    productos: Array,
  });

  const form = useForm({
    cliente_id: props.venta.cliente_id,
    total: props.venta.total,
    productos: props.venta.productos,
  });

  const buscarCliente = ref(props.venta.cliente ? props.venta.cliente.nombre_razon_social : '');
  const buscarProducto = ref('');
  const mostrarClientes = ref(false);
  const mostrarProductos = ref(false);
  const selectedProducts = ref(props.venta.productos.map(p => p.id));
  const quantities = ref(props.venta.productos.reduce((acc, p) => {
    acc[p.id] = p.pivot.cantidad; // Usar pivot para obtener la cantidad
    return acc;
  }, {}));
  const prices = ref(props.venta.productos.reduce((acc, p) => {
    acc[p.id] = p.pivot.precio; // Usar pivot para obtener el precio
    return acc;
  }, {}));
  const clienteSeleccionado = ref(props.venta.cliente ? props.venta.cliente.nombre_razon_social : null);

  const clientesFiltrados = computed(() => {
    if (!buscarCliente.value) return [];
    return props.clientes.filter((cliente) =>
      cliente.nombre_razon_social.toLowerCase().includes(buscarCliente.value.toLowerCase())
    );
  });

  const productosFiltrados = computed(() => {
    if (!buscarProducto.value) return [];
    return props.productos.filter((producto) =>
      producto.nombre.toLowerCase().includes(buscarProducto.value.toLowerCase())
    );
  });

  const getProductById = (id) => {
    if (!props.productos) {
      console.error('La lista de productos no está disponible.');
      return {};
    }
    const producto = props.productos.find((producto) => producto.id === id);
    if (producto) {
      return producto;
    }
    console.error(`Producto con id ${id} no encontrado.`);
    return {};
  };

  const calcularTotal = () => {
    let total = 0;
    for (const id of selectedProducts.value) {
      const cantidad = Number.parseFloat(quantities.value[id]) || 0;
      const precio = Number.parseFloat(prices.value[id]) || 0;
      total += cantidad * precio;
    }
    form.total = total.toFixed(2);
  };

  watch(quantities, calcularTotal, { deep: true });

  const seleccionarCliente = (cliente) => {
    form.cliente_id = cliente.id;
    buscarCliente.value = cliente.nombre_razon_social;
    clienteSeleccionado.value = cliente.nombre_razon_social;
    mostrarClientes.value = false;
  };

  const agregarProducto = (producto) => {
    if (!selectedProducts.value.includes(producto.id)) {
      selectedProducts.value.push(producto.id);
      quantities.value[producto.id] = 1;
      prices.value[producto.id] = producto.precio_venta || 0;
    }
    buscarProducto.value = '';
    mostrarProductos.value = false;
    calcularTotal();
  };

  const eliminarProducto = (productoId) => {
    selectedProducts.value = selectedProducts.value.filter(id => id !== productoId);
    delete quantities.value[productoId];
    delete prices.value[productoId];
    calcularTotal();
  };

  const ocultarClientesDespuesDeTiempo = (event) => {
    setTimeout(() => {
      if (!event.relatedTarget || !event.relatedTarget.classList.contains('cliente-item')) {
        mostrarClientes.value = false;
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

  onMounted(() => {
    const savedData = localStorage.getItem('ventaEnProgreso');
    if (savedData) {
      const parsedData = JSON.parse(savedData);
      form.cliente_id = parsedData.cliente_id;
      clienteSeleccionado.value = parsedData.cliente_nombre;
      buscarCliente.value = parsedData.cliente_nombre;
      selectedProducts.value = parsedData.selectedProducts;
      quantities.value = parsedData.quantities;
      prices.value = parsedData.prices;
      calcularTotal();
    }

    window.addEventListener('beforeunload', handleBeforeUnload);
  });

  onBeforeUnmount(() => {
    window.removeEventListener('beforeunload', handleBeforeUnload);
  });

  const handleBeforeUnload = (event) => {
    if (form.dirty) {
      const confirmationMessage = 'Tienes cambios no guardados. ¿Estás seguro de que quieres salir?';
      event.returnValue = confirmationMessage;
      return confirmationMessage;
    }
  };

  const actualizarVenta = () => {
    form.productos = selectedProducts.value.map((productoId) => ({
      id: productoId,
      cantidad: quantities.value[productoId] || 1,
      precio: prices.value[productoId] || 0,
    }));

    form.put(route('ventas.update', props.venta.id), {
      onSuccess: () => {
        localStorage.removeItem('ventaEnProgreso');
        selectedProducts.value = [];
        quantities.value = {};
        prices.value = {};
      },
      onError: (error) => {
        console.error('Error al actualizar la venta:', error);
      },
    });
  };
  </script>

  <style scoped>
  /* Aquí van tus estilos personalizados */
  </style>
