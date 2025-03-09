<template>
    <Head title="Crear Pedido" />
    <div class="pedidos-create">
      <h1 class="text-2xl font-semibold mb-6">Crear Nuevo Pedido</h1>
      <form @submit.prevent="crearPedido" class="space-y-6">
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
            <li v-for="cliente in clientesFiltrados" :key="cliente.id" @click="seleccionarCliente(cliente)" class="px-4 py-2 cursor-pointer hover:bg-gray-100">
              {{ cliente.nombre_razon_social }}
            </li>
          </ul>
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
            <li v-for="producto in productosFiltrados" :key="producto.id" @click="agregarProducto(producto)" class="px-4 py-2 cursor-pointer hover:bg-gray-100">
              {{ producto.nombre }} (Disponible: {{ producto.stock }})
            </li>
          </ul>
        </div>

        <!-- Lista de productos seleccionados -->
        <div v-if="selectedProducts.length > 0" class="mt-4">
          <h3 class="text-lg font-medium mb-4">Productos Seleccionados</h3>
          <div v-for="(productoId, index) in selectedProducts" :key="index" class="flex items-center justify-between bg-white border rounded-md shadow-sm p-4 mb-4">
            <span class="font-medium text-gray-800">{{ getProductById(productoId)?.nombre }}</span>
            <input v-model.number="quantities[productoId]" type="number" class="px-4 py-2 border rounded-md mt-1 w-16" min="1" @input="calcularTotal" />
            <input v-model.number="prices[productoId]" type="number" class="px-4 py-2 border rounded-md mt-1 w-20" min="0" @input="calcularTotal" />
            <input :value="(quantities[productoId] * prices[productoId]).toFixed(2)" type="text" class="px-4 py-2 border rounded-md mt-1 w-24 bg-gray-100 cursor-not-allowed" readonly />
            <button type="button" @click="eliminarProducto(productoId)" class="text-red-500 hover:text-red-700">✕</button>
          </div>
        </div>

        <!-- Total -->
        <div class="form-group">
          <label for="total" class="block text-sm font-medium text-gray-700">Total</label>
          <input v-model="form.total" type="text" readonly class="mt-1 block w-full px-4 py-2 border rounded-md bg-gray-100 cursor-not-allowed" />
        </div>

        <!-- Botones -->
        <div class="flex justify-end space-x-4">
          <Link :href="route('pedidos.index')" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">Cancelar</Link>
          <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600" :disabled="!form.cliente_id || selectedProducts.length === 0">Guardar Pedido</button>
        </div>
      </form>
    </div>
  </template>

  <script setup>
  import { ref, computed, watch, onMounted } from 'vue';
  import { Head, useForm, Link } from '@inertiajs/vue3';
  import AppLayout from '@/Layouts/AppLayout.vue';


// Define el layout del dashboard
defineOptions({ layout: AppLayout });

  const props = defineProps({ clientes: Array, productos: Array });
  const form = useForm({ cliente_id: '', total: 0, productos: [] });
  const buscarCliente = ref('');
  const buscarProducto = ref('');
  const mostrarClientes = ref(false);
  const mostrarProductos = ref(false);
  const selectedProducts = ref([]);
  const quantities = ref({});
  const prices = ref({});

  const clientesFiltrados = computed(() => buscarCliente.value ? props.clientes.filter(cliente => cliente.nombre_razon_social.toLowerCase().includes(buscarCliente.value.toLowerCase())) : []);
  const productosFiltrados = computed(() => buscarProducto.value ? props.productos.filter(producto => producto.nombre.toLowerCase().includes(buscarProducto.value.toLowerCase())) : []);

  const getProductById = (id) => props.productos.find(producto => producto.id === id);
  const calcularTotal = () => {
    form.total = selectedProducts.value.reduce((sum, id) => sum + (quantities.value[id] * prices.value[id] || 0), 0).toFixed(2);
  };

  const seleccionarCliente = (cliente) => { form.cliente_id = cliente.id; buscarCliente.value = cliente.nombre_razon_social; mostrarClientes.value = false; };
  const agregarProducto = (producto) => { if (!selectedProducts.value.includes(producto.id)) { selectedProducts.value.push(producto.id); quantities.value[producto.id] = 1; prices.value[producto.id] = producto.precio_venta || 0; } buscarProducto.value = ''; mostrarProductos.value = false; calcularTotal(); };
  const eliminarProducto = (productoId) => { selectedProducts.value = selectedProducts.value.filter(id => id !== productoId); delete quantities.value[productoId]; delete prices.value[productoId]; calcularTotal(); };
  const crearPedido = () => { form.productos = selectedProducts.value.map(id => ({ id, cantidad: quantities.value[id] || 1, precio: prices.value[id] || 0 })); form.post(route('pedidos.store')); };
  </script>
