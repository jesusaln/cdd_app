<template>
    <Head title="Crear Venta" />
    <div class="ventas-create">
      <h1 class="text-2xl font-semibold mb-6">Crear Nueva Venta</h1>
      <form @submit.prevent="crearVenta" class="space-y-6">
        <!-- Búsqueda de cliente -->
        <div class="form-group">
          <label for="buscarCliente" class="block text-sm font-medium text-gray-700">Buscar Cliente</label>
          <input
            id="buscarCliente"
            v-model="buscarCliente"
            type="text"
            placeholder="Buscar cliente..."
            @focus="mostrarClientes = true"
            @blur="ocultarClientesDespuesDeTiempo"
            autocomplete="off"
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
              class="px-4 py-2 cursor-pointer hover:bg-gray-100"
            >
              {{ item.nombre }} ({{ item.tipo }}) (Disponible: {{ item.stock || 'N/A' }})
            </li>
          </ul>
          <div v-if="productosFiltrados.length === 0 && buscarProducto" class="text-red-500 text-sm mt-2">
            No se encontraron productos/servicios.
          </div>
        </div>

        <!-- Lista de productos y servicios seleccionados -->
        <div v-if="selectedProducts.length > 0" class="mt-4">
          <h3 class="text-lg font-medium mb-4">Productos/Servicios Seleccionados</h3>
          <div v-for="(entry, index) in selectedProducts" :key="index" class="flex items-center justify-between bg-white border rounded-md shadow-sm p-4 mb-4">
            <div class="flex items-center space-x-4 w-full">
              <span class="font-medium text-gray-800 w-1/3">{{ getProductById(entry)?.nombre || 'Item no encontrado' }}</span>
              <div class="flex flex-col w-1/4">
                <label :for="`cantidad-${entry.tipo}-${entry.id}`" class="text-sm font-medium text-gray-700">Cantidad</label>
                <input
                  :id="`cantidad-${entry.tipo}-${entry.id}`"
                  v-model.number="quantities[`${entry.tipo}-${entry.id}`]"
                  type="number"
                  class="px-4 py-2 border rounded-md mt-1 w-full"
                  min="1"
                  :max="entry.tipo === 'producto' ? getProductById(entry)?.stock : undefined"
                  @input="calcularTotal"
                />
              </div>
              <div class="flex flex-col w-1/4">
                <label :for="`precio-${entry.tipo}-${entry.id}`" class="text-sm font-medium text-gray-700">Precio de Venta</label>
                <input
                  :id="`precio-${entry.tipo}-${entry.id}`"
                  v-model.number="prices[`${entry.tipo}-${entry.id}`]"
                  type="number"
                  class="px-4 py-2 border rounded-md mt-1 w-full"
                  min="0"
                  step="0.01"
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
              @click="eliminarProducto(entry)"
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
          <Link :href="route('ventas.index')" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">
            Cancelar
          </Link>
          <button
            type="submit"
            class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 disabled:bg-gray-400"
            :disabled="!form.cliente_id || selectedProducts.length === 0 || form.processing"
          >
            <span v-if="form.processing">Guardando...</span>
            <span v-else>Guardar Venta</span>
          </button>
        </div>
      </form>
    </div>
  </template>

  <script setup>
  import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue';
  import { Head, useForm, Link } from '@inertiajs/vue3';
  import AppLayout from '@/Layouts/AppLayout.vue';

  defineOptions({ layout: AppLayout });

  const props = defineProps({
    clientes: { type: Array, default: () => [] },
    productos: { type: Array, default: () => [] },
    servicios: { type: Array, default: () => [] },
  });

  const form = useForm({
    cliente_id: '',
    total: 0,
    productos: [],
  });

  const buscarCliente = ref('');
  const buscarProducto = ref('');
  const mostrarClientes = ref(false);
  const mostrarProductos = ref(false);
  const selectedProducts = ref([]);
  const quantities = ref({});
  const prices = ref({});
  const clienteSeleccionado = ref(null);

  const clientesFiltrados = computed(() => {
    if (!buscarCliente.value) return [];
    return props.clientes.filter((cliente) =>
      cliente.nombre_razon_social.toLowerCase().includes(buscarCliente.value.toLowerCase())
    );
  });

  const productosFiltrados = computed(() => {
    if (!buscarProducto.value) return [];
    const productosYServicios = [
      ...(props.productos || []).map(producto => ({ ...producto, tipo: 'producto' })),
      ...(props.servicios || []).map(servicio => ({ ...servicio, tipo: 'servicio' })),
    ];

    return productosYServicios.filter(item =>
      item.nombre.toLowerCase().includes(buscarProducto.value.toLowerCase()) &&
      !selectedProducts.value.some(selected => selected.id === item.id && selected.tipo === item.tipo) &&
      (item.tipo === 'servicio' || item.stock > 0)
    );
  });

  const getProductById = (entry) => {
    if (!entry || !entry.id || !entry.tipo) {
      console.error('Entrada inválida para getProductById:', entry);
      return null;
    }
    if (entry.tipo === 'producto') {
      return props.productos.find((p) => p.id === entry.id) || null;
    }
    if (entry.tipo === 'servicio') {
      return props.servicios.find((s) => s.id === entry.id) || null;
    }
    console.error(`No se encontró item con ID: ${entry.id} y tipo: ${entry.tipo}`);
    return null;
  };

  const calcularTotal = () => {
    let total = 0;
    for (const entry of selectedProducts.value) {
      const key = `${entry.tipo}-${entry.id}`;
      const cantidad = Number.parseFloat(quantities.value[key]) || 0;
      const precio = Number.parseFloat(prices.value[key]) || 0;
      total += cantidad * precio;
    }
    form.total = total.toFixed(2);
  };

  watch(quantities, calcularTotal, { deep: true });
  watch(prices, calcularTotal, { deep: true });

  const seleccionarCliente = (cliente) => {
    form.cliente_id = cliente.id;
    buscarCliente.value = cliente.nombre_razon_social;
    clienteSeleccionado.value = cliente.nombre_razon_social;
    mostrarClientes.value = false;
    console.log('Cliente seleccionado - form.cliente_id:', form.cliente_id);
  };

  const agregarProducto = (item) => {
    const itemEntry = { id: item.id, tipo: item.tipo };
    if (item.tipo === 'producto' && item.stock <= 0) {
      alert(`El producto ${item.nombre} no tiene stock disponible.`);
      return;
    }
    const exists = selectedProducts.value.some(
      (entry) => entry.id === item.id && entry.tipo === item.tipo
    );
    if (!exists) {
      selectedProducts.value.push(itemEntry);
      quantities.value[`${item.tipo}-${item.id}`] = 1;
      prices.value[`${item.tipo}-${item.id}`] = item.tipo === 'producto' ? (item.precio_venta || 0) : (item.precio || 0);
      console.log('Producto agregado - selectedProducts:', selectedProducts.value);
    }
    buscarProducto.value = '';
    mostrarProductos.value = false;
    calcularTotal();
  };

  const eliminarProducto = (entry) => {
    selectedProducts.value = selectedProducts.value.filter(
      (item) => !(item.id === entry.id && item.tipo === entry.tipo)
    );
    delete quantities.value[`${entry.tipo}-${entry.id}`];
    delete prices.value[`${entry.tipo}-${entry.id}`];
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

  watch(
    [() => form.cliente_id, selectedProducts, quantities, prices, clienteSeleccionado],
    () => {
      const dataToSave = {
        cliente_id: form.cliente_id,
        cliente_nombre: clienteSeleccionado.value,
        selectedProducts: selectedProducts.value,
        quantities: quantities.value,
        prices: prices.value,
      };
      localStorage.setItem('ventaEnProgreso', JSON.stringify(dataToSave));
    },
    { deep: true }
  );

  onMounted(() => {
    console.log('Clientes:', props.clientes);
    console.log('Productos:', props.productos);
    console.log('Servicios:', props.servicios);
    const savedData = localStorage.getItem('ventaEnProgreso');
    if (savedData) {
      const parsedData = JSON.parse(savedData);
      console.log('Datos cargados desde localStorage:', parsedData);
      form.cliente_id = parsedData.cliente_id || '';
      clienteSeleccionado.value = parsedData.cliente_nombre || null;
      buscarCliente.value = parsedData.cliente_nombre || '';
      selectedProducts.value = Array.isArray(parsedData.selectedProducts)
        ? parsedData.selectedProducts.filter(
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
    if (form.cliente_id || selectedProducts.value.length > 0) {
      event.preventDefault();
      event.returnValue = '';
    }
  };

  const crearVenta = () => {
    console.log('Iniciando crearVenta - form.processing:', form.processing);
    console.log('Datos antes de enviar:', {
      cliente_id: form.cliente_id,
      productos: selectedProducts.value,
      quantities: quantities.value,
      prices: prices.value,
    });

    if (selectedProducts.value.length === 0) {
      alert('Debes agregar al menos un producto o servicio.');
      return;
    }

    try {
      form.productos = selectedProducts.value.map((entry) => {
        const key = `${entry.tipo}-${entry.id}`;
        const cantidad = quantities.value[key] || 1;
        const precio = prices.value[key] || 0;
        const stockDisponible = getProductById(entry)?.stock;

        if (entry.tipo === 'producto' && cantidad > stockDisponible) {
          throw new Error(`La cantidad de ${getProductById(entry).nombre} excede el stock disponible (${stockDisponible}).`);
        }

        return {
          id: entry.id,
          tipo: entry.tipo, // Agregar tipo para que el backend lo use
          cantidad,
          precio,
        };
      });

      form.post(route('ventas.store'), {
        preserveScroll: true,
        onSuccess: () => {
          console.log('Venta creada exitosamente - form.processing:', form.processing);
          localStorage.removeItem('ventaEnProgreso');
          selectedProducts.value = [];
          quantities.value = {};
          prices.value = {};
          form.reset();
        },
        onError: (errors) => {
          console.error('Error al crear la venta:', errors);
        },
        onFinish: () => {
          console.log('Solicitud finalizada - form.processing:', form.processing);
        },
      });
    } catch (error) {
      console.error('Error en crearVenta:', error.message);
      alert(error.message);
    }
  };
  </script>

  <style scoped>
  .form-group {
    margin-bottom: 1rem;
  }
  </style>
