<template>
    <Head title="Editar Venta" />
    <div class="ventas-edit max-w-4xl mx-auto p-6 bg-gray-50 rounded-lg shadow-md">
      <h1 class="text-2xl font-semibold mb-6 text-center">Editar Venta #{{ props.venta.id }}</h1>
      <form @submit.prevent="actualizarVenta" class="space-y-6">
        <!-- Búsqueda de cliente -->
        <div class="form-group relative">
          <label for="buscarCliente" class="block text-sm font-medium text-gray-700">Cliente</label>
          <input
            v-model="buscarCliente"
            type="text"
            placeholder="Buscar cliente..."
            @input="debouncedBuscarCliente"
            @keydown.enter.prevent="seleccionarPrimerCliente"
            @focus="mostrarClientes = true"
            @blur="ocultarClientesDespuesDeTiempo"
            class="mt-1 block w-full px-4 py-2 border rounded-md focus:ring-blue-500 focus:border-blue-500"
          />
          <ul v-if="mostrarClientes && clientesFiltrados.length > 0" class="absolute z-10 bg-white border rounded-md shadow-md w-full max-h-48 overflow-y-auto mt-1">
            <li
              v-for="cliente in clientesFiltrados"
              :key="cliente.id"
              @click="seleccionarCliente(cliente)"
              @keydown.enter="seleccionarCliente(cliente)"
              tabindex="0"
              class="px-4 py-2 cursor-pointer hover:bg-gray-100"
            >
              {{ cliente.nombre_razon_social }}
            </li>
          </ul>
          <p v-if="!form.cliente_id && form.errors.cliente_id" class="text-red-500 text-sm mt-1">{{ form.errors.cliente_id }}</p>
        </div>

        <!-- Búsqueda de productos -->
        <div class="form-group relative">
          <label for="buscarProducto" class="block text-sm font-medium text-gray-700">Agregar Producto</label>
          <input
            v-model="buscarProducto"
            type="text"
            placeholder="Buscar producto..."
            @input="debouncedBuscarProducto"
            @keydown.enter.prevent="agregarPrimerProducto"
            @focus="mostrarProductos = true"
            @blur="ocultarProductosDespuesDeTiempo"
            class="mt-1 block w-full px-4 py-2 border rounded-md focus:ring-blue-500 focus:border-blue-500"
          />
          <ul v-if="mostrarProductos && productosFiltrados.length > 0" class="absolute z-10 bg-white border rounded-md shadow-md w-full max-h-48 overflow-y-auto mt-1">
            <li
              v-for="producto in productosFiltrados"
              :key="producto.id"
              @click="agregarProducto(producto)"
              @keydown.enter="agregarProducto(producto)"
              tabindex="0"
              class="px-4 py-2 cursor-pointer hover:bg-gray-100"
            >
              {{ producto.nombre }} (Disponible: {{ adjustedStock[producto.id] ?? producto.stock }})
            </li>
          </ul>
        </div>

        <!-- Lista de productos seleccionados -->
        <div v-if="selectedProducts.length > 0" class="mt-4">
          <h3 class="text-lg font-medium mb-4 text-center">Productos Seleccionados</h3>
          <table class="w-full border-collapse bg-white shadow-md rounded-md">
            <thead>
              <tr class="bg-gray-100">
                <th class="px-4 py-2 text-center font-medium text-gray-700">Producto</th>
                <th class="px-4 py-2 text-center font-medium text-gray-700">Cantidad</th>
                <th class="px-4 py-2 text-center font-medium text-gray-700">Precio Unitario</th>
                <th class="px-4 py-2 text-center font-medium text-gray-700">Subtotal</th>
                <th class="px-4 py-2 text-center font-medium text-gray-700">Acción</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(productoId, index) in selectedProducts" :key="index" class="border-t">
                <td class="px-4 py-2 text-center">{{ getProductById(productoId)?.nombre }}</td>
                <td class="px-4 py-2 text-center">
                  <input
                    v-model.number="quantities[productoId]"
                    type="number"
                    class="w-16 px-2 py-1 border rounded-md text-center"
                    min="1"
                    :max="getProductById(productoId)?.stock + (originalQuantities[productoId] || 0)"
                    @input="adjustStock(productoId)"
                  />
                </td>
                <td class="px-4 py-2 text-center">
                  <input
                    v-model.number="prices[productoId]"
                    type="number"
                    class="w-20 px-2 py-1 border rounded-md text-center"
                    min="0"
                    step="0.01"
                    @input="calcularTotal"
                  />
                </td>
                <td class="px-4 py-2 text-center">{{ (quantities[productoId] * prices[productoId]).toFixed(2) }}</td>
                <td class="px-4 py-2 text-center">
                  <button type="button" @click="eliminarProducto(productoId)" class="text-red-500 hover:text-red-700">✕</button>
                </td>
              </tr>
            </tbody>
          </table>
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
          <Link :href="route('ventas.index')" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">Cancelar</Link>
          <button
            type="submit"
            class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600"
            :disabled="!form.cliente_id || selectedProducts.length === 0 || form.processing"
          >
            <span v-if="form.processing">Guardando...</span>
            <span v-else>Guardar Cambios</span>
          </button>
        </div>
      </form>
    </div>
  </template>

  <script setup>
  import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue';
  import { Head, useForm, Link } from '@inertiajs/vue3';
  import AppLayout from '@/Layouts/AppLayout.vue';
  import debounce from 'lodash/debounce';

  defineOptions({ layout: AppLayout });

  const props = defineProps({
    venta: Object,
    clientes: Array,
    productos: Array,
  });

  const form = useForm({
    cliente_id: props.venta.cliente_id || '',
    total: props.venta.total || 0,
    productos: props.venta.productos || [],
  });

  const buscarCliente = ref(props.venta.cliente?.nombre_razon_social || '');
  const buscarProducto = ref('');
  const mostrarClientes = ref(false);
  const mostrarProductos = ref(false);
  const selectedProducts = ref(props.venta.productos.map(p => p.id) || []);
  const quantities = ref(
    props.venta.productos.reduce((acc, p) => {
      acc[p.id] = p.pivot.cantidad;
      return acc;
    }, {})
  );
  const prices = ref(
    props.venta.productos.reduce((acc, p) => {
      acc[p.id] = p.pivot.precio;
      return acc;
    }, {})
  );
  const originalQuantities = ref(
    props.venta.productos.reduce((acc, p) => {
      acc[p.id] = p.pivot.cantidad;
      return acc;
    }, {})
  );
  const adjustedStock = ref({}); // Stock ajustado en el frontend

  // Debounce para búsquedas
  const debouncedBuscarCliente = debounce(() => {}, 300);
  const debouncedBuscarProducto = debounce(() => {}, 300);

  const clientesFiltrados = computed(() =>
    buscarCliente.value
      ? props.clientes.filter(cliente =>
          cliente.nombre_razon_social.toLowerCase().includes(buscarCliente.value.toLowerCase())
        )
      : []
  );

  const productosFiltrados = computed(() =>
    buscarProducto.value
      ? props.productos.filter(
          producto =>
            producto.nombre.toLowerCase().includes(buscarProducto.value.toLowerCase()) &&
            !selectedProducts.value.includes(producto.id) &&
            (adjustedStock.value[producto.id] ?? producto.stock) > 0
        )
      : []
  );

  const getProductById = (id) => props.productos.find(producto => producto.id === id) || { nombre: 'Producto no encontrado', stock: 0 };

  const calcularTotal = () => {
    form.total = selectedProducts.value
      .reduce((sum, id) => sum + ((quantities.value[id] || 0) * (prices.value[id] || 0)), 0)
      .toFixed(2);
  };

  const adjustStock = (productoId) => {
    const producto = getProductById(productoId);
    const originalQty = originalQuantities.value[productoId] || 0;
    const newQty = quantities.value[productoId] || 0;
    const stockBase = producto.stock + originalQty; // Stock disponible antes de cambios
    adjustedStock.value[productoId] = stockBase - newQty; // Ajustar stock en frontend
    calcularTotal();
  };

  const seleccionarCliente = (cliente) => {
    form.cliente_id = cliente.id;
    buscarCliente.value = cliente.nombre_razon_social;
    mostrarClientes.value = false;
  };

  const agregarProducto = (producto) => {
    if (!selectedProducts.value.includes(producto.id) && (adjustedStock.value[producto.id] ?? producto.stock) > 0) {
      selectedProducts.value.push(producto.id);
      quantities.value[producto.id] = 1;
      prices.value[producto.id] = producto.precio_venta || 0;
      originalQuantities.value[producto.id] = 0; // Nuevo producto, no tiene cantidad original
      adjustStock(producto.id);
    } else if ((adjustedStock.value[producto.id] ?? producto.stock) <= 0) {
      alert(`El producto ${producto.nombre} no tiene stock disponible.`);
    }
    buscarProducto.value = '';
    mostrarProductos.value = false;
  };

  const eliminarProducto = (productoId) => {
    selectedProducts.value = selectedProducts.value.filter(id => id !== productoId);
    delete quantities.value[productoId];
    delete prices.value[productoId];
    delete adjustedStock.value[productoId]; // Restaurar stock al eliminar
    delete originalQuantities.value[productoId];
    calcularTotal();
  };

  const seleccionarPrimerCliente = () => {
    if (clientesFiltrados.value.length > 0) seleccionarCliente(clientesFiltrados.value[0]);
  };

  const agregarPrimerProducto = () => {
    if (productosFiltrados.value.length > 0) agregarProducto(productosFiltrados.value[0]);
  };

  const actualizarVenta = () => {
    form.productos = selectedProducts.value.map(id => ({
      id,
      cantidad: quantities.value[id] || 1,
      precio: prices.value[id] || 0,
      original_cantidad: originalQuantities.value[id] || 0, // Enviar cantidad original para comparación
    }));

    // Validar stock antes de enviar
    for (const producto of form.productos) {
      const stockDisponible = getProductById(producto.id).stock + (producto.original_cantidad || 0);
      if (producto.cantidad > stockDisponible) {
        alert(`La cantidad de ${getProductById(producto.id).nombre} excede el stock disponible (${stockDisponible}).`);
        return;
      }
    }

    form.put(route('ventas.update', props.venta.id), {
      onSuccess: () => {
        localStorage.removeItem('ventaEnProgreso');
        form.reset('total');
        adjustedStock.value = {}; // Limpiar stock ajustado tras guardar
      },
      onError: (error) => {
        console.error('Error al actualizar la venta:', error);
      },
    });
  };

  const ocultarClientesDespuesDeTiempo = () => {
    setTimeout(() => {
      mostrarClientes.value = false;
    }, 200);
  };
  const ocultarProductosDespuesDeTiempo = () => setTimeout(() => {
    mostrarProductos.value = false;
  }, 200);

  onMounted(() => {
    // Inicializar stock ajustado con las cantidades originales
    for (const productoId of selectedProducts.value) {
      adjustStock(productoId);
    }
    calcularTotal();
    const savedData = localStorage.getItem('ventaEnProgreso');
    if (savedData) {
      const parsedData = JSON.parse(savedData);
      form.cliente_id = parsedData.cliente_id;
      buscarCliente.value = parsedData.cliente_nombre;
      selectedProducts.value = parsedData.selectedProducts;
      quantities.value = parsedData.quantities;
      prices.value = parsedData.prices;
      for (const productoId of selectedProducts.value) {
        adjustStock(productoId);
      }
      calcularTotal();
    }
    window.addEventListener('beforeunload', handleBeforeUnload);
  });

  onBeforeUnmount(() => {
    window.removeEventListener('beforeunload', handleBeforeUnload);
  });

  const handleBeforeUnload = (event) => {
    if (form.dirty) {
      localStorage.setItem('ventaEnProgreso', JSON.stringify({
        cliente_id: form.cliente_id,
        cliente_nombre: buscarCliente.value,
        selectedProducts: selectedProducts.value,
        quantities: quantities.value,
        prices: prices.value,
      }));
      event.preventDefault();
      event.returnValue = 'Tienes cambios no guardados. ¿Seguro que quieres salir?';
    }
  };
  </script>

  <style scoped>
  table {
    width: 100%;
    border-collapse: collapse;
  }

  th, td {
    padding: 8px 16px;
    text-align: center;
    vertical-align: middle;
  }

  th {
    background-color: #f3f4f6;
    font-weight: 600;
  }

  tr {
    border-bottom: 1px solid #e5e7eb;
  }

  input[type="number"] {
    -moz-appearance: textfield;
  }

  input[type="number"]::-webkit-outer-spin-button,
  input[type="number"]::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
  }

  .form-group {
    margin-bottom: 1.5rem;
  }

  button:disabled {
    background-color: #d1d5db;
    cursor: not-allowed;
  }
  </style>
