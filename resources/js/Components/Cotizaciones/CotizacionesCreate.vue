<template>
  <Head title="Crear cotizaciones" />
  <div class="cotizaciones-create min-h-screen bg-gradient-to-br from-slate-50 to-blue-50 p-6">
    <div class="max-w-6xl mx-auto">
      <Header
        :autoguardando="autoguardando"
        :ultimoAutoguardado="ultimoAutoguardado"
        @mostrar-vista-previa="mostrarVistaPrevia = true"
        @mostrar-plantillas="mostrarPlantillas = true"
        :clienteSeleccionado="clienteSeleccionado"
        :selectedProducts="selectedProducts"
      />
      <AtajosTeclado v-if="mostrarAtajos" @cerrar="mostrarAtajos = false" />
      <form @submit.prevent="crearCotizacion" class="space-y-8">
        <ClienteInfo
          :clientes="clientes"
          :clienteSeleccionado="clienteSeleccionado"
          @cliente-seleccionado="onClienteSeleccionado"
          @crear-nuevo-cliente="crearNuevoCliente"
          @limpiar-cliente="limpiarCliente"
          ref="buscarClienteRef"
        />
        <ProductosServicios
          :productos="productos"
          :servicios="servicios"
          :selectedProducts="selectedProducts"
          :quantities="quantities"
          :prices="prices"
          :discounts="discounts"
          :mostrarCalculadoraMargen="mostrarCalculadoraMargen"
          @agregar-producto="agregarProducto"
          @eliminar-producto="eliminarProducto"
          @update-quantity="updateQuantity"
          @update-discount="updateDiscount"
          @calcular-total="calcularTotal"
          @mostrar-margen="mostrarMargenProducto"
          @verificar-precios="verificarPrecios"
          ref="buscarProductoRef"
        />
        <CalculadoraMargenes
          v-if="mostrarCalculadoraMargen"
          :calculadoraMargen="calculadoraMargen"
          @cerrar="mostrarCalculadoraMargen = false"
        />
        <DescuentoGeneral
          v-model:descuentoGeneral="descuentoGeneral"
          :totales="totales"
          @calcular-total="calcularTotal"
        />
        <ResumenTotal
          :selectedProducts="selectedProducts"
          :quantities="quantities"
          :totales="totales"
          :descuentoGeneral="descuentoGeneral"
        />
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
            type="button"
            @click="guardarBorrador"
            :disabled="!clienteSeleccionado || selectedProducts.length === 0"
            class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
            </svg>
            Guardar Borrador
          </button>
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
      <VistaPreviaModal
        v-if="mostrarVistaPrevia"
        :clienteSeleccionado="clienteSeleccionado"
        :selectedProducts="selectedProducts"
        :quantities="quantities"
        :prices="prices"
        :discounts="discounts"
        :totales="totales"
        :productos="productos"
        :servicios="servicios"
        @cerrar="mostrarVistaPrevia = false"
        @imprimir="imprimirVistaPrevia"
      />
      <PlantillasModal
        v-if="mostrarPlantillas"
        :plantillas="plantillas"
        :selectedProducts="selectedProducts"
        :nuevaPlantilla="nuevaPlantilla"
        @aplicar-plantilla="aplicarPlantilla"
        @guardar-plantilla="guardarPlantilla"
        @cerrar="mostrarPlantillas = false"
      />
      <NotificacionAutoguardado v-if="mostrarNotificacionAutoguardado" />
      <AyudaBoton @toggle-atajos="mostrarAtajos = !mostrarAtajos" />
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { Notyf } from 'notyf';
import axios from 'axios';
import AppLayout from '@/Layouts/AppLayout.vue';
import Header from './Header.vue';
import AtajosTeclado from './AtajosTeclado.vue';
import ClienteInfo from './ClienteInfo.vue';
import ProductosServicios from './ProductosServicios.vue';
import CalculadoraMargenes from './CalculadoraMargenes.vue';
import DescuentoGeneral from './DescuentoGeneral.vue';
import ResumenTotal from './ResumenTotal.vue';
import VistaPreviaModal from './VistaPreviaModal.vue';
import PlantillasModal from './PlantillasModal.vue';
import NotificacionAutoguardado from './NotificacionAutoguardado.vue';
import AyudaBoton from './AyudaBoton.vue';

// Initialize Notyf
const notyf = new Notyf({
  duration: 5000,
  position: { x: 'right', y: 'bottom' },
  types: [
    { type: 'success', background: '#10B981', icon: { className: 'notyf__icon--success', tagName: 'i', text: '✓' } },
    { type: 'error', background: '#EF4444', icon: { className: 'notyf__icon--error', tagName: 'i', text: '✗' } },
    { type: 'info', background: '#3B82F6', icon: { className: 'notyf__icon--info', tagName: 'i', text: 'ℹ' } }
  ]
});

// Define layout
defineOptions({ layout: AppLayout });

// Props
const props = defineProps({
  clientes: Array,
  productos: { type: Array, default: () => [] },
  servicios: { type: Array, default: () => [] },
});

// Form setup
const form = useForm({
  nombre_razon_social: '',
  email: '',
  cliente_id: '',
  subtotal: 0,
  descuento_general: 0,
  descuento_items: 0,
  iva: 0,
  total: 0,
  productos: [],
});

// State
const selectedProducts = ref([]);
const quantities = ref({});
const prices = ref({});
const discounts = ref({});
const descuentoGeneral = ref(0);
const clienteSeleccionado = ref(null);
const mostrarVistaPrevia = ref(false);
const mostrarPlantillas = ref(false);
const mostrarCalculadoraMargen = ref(false);
const mostrarAtajos = ref(true);
const mostrarNotificacionAutoguardado = ref(false);
const autoguardando = ref(false);
const ultimoAutoguardado = ref(null);
const intervalAutoguardado = ref(null);
const nuevaPlantilla = ref({ nombre: '', descripcion: '' });

// Templates
const plantillas = ref([
  {
    id: 1,
    nombre: 'Paquete Básico Web',
    descripcion: 'Diseño web básico con hosting',
    productos: [{ id: 1, tipo: 'producto', cantidad: 1, precio: 10000, descuento: 0 }],
    total: 15000,
    fechaModificacion: new Date().toISOString()
  },
  {
    id: 2,
    nombre: 'Consultoría TI Completa',
    descripcion: 'Auditoría y consultoría completa de sistemas',
    productos: [{ id: 1, tipo: 'servicio', cantidad: 1, precio: 40000, descuento: 0 }],
    total: 45000,
    fechaModificacion: new Date().toISOString()
  }
]);

// Constants
const IVA_RATE = 0.16;

// Computed properties
const totales = computed(() => {
  let subtotal = 0;
  let descuentoItems = 0;

  selectedProducts.value.forEach(entry => {
    const key = `${entry.tipo}-${entry.id}`;
    const cantidad = Number.parseFloat(quantities.value[key]) || 0;
    const precio = Number.parseFloat(prices.value[key]) || 0;
    const descuentoItem = Number.parseFloat(discounts.value[key]) || 0;
    const subtotalItem = cantidad * precio;
    const descuentoItemMonto = subtotalItem * (descuentoItem / 100);
    subtotal += subtotalItem;
    descuentoItems += descuentoItemMonto;
  });

  const subtotalConDescuentoItems = subtotal - descuentoItems;
  const descuentoGeneralMonto = subtotalConDescuentoItems * (descuentoGeneral.value / 100);
  const subtotalConDescuentos = subtotalConDescuentoItems - descuentoGeneralMonto;
  const iva = subtotalConDescuentos * IVA_RATE;
  const total = subtotalConDescuentos + iva;

  return {
    subtotal,
    descuentoItems,
    descuentoGeneral: descuentoGeneralMonto,
    subtotalConDescuentos,
    iva,
    total
  };
});

const calculadoraMargen = computed(() => {
  let costoTotal = 0;
  let precioVenta = 0;

  selectedProducts.value.forEach(entry => {
    const key = `${entry.tipo}-${entry.id}`;
    const cantidad = parseFloat(quantities.value[key]) || 0;
    const precio = parseFloat(prices.value[key]) || 0;
    const producto = obtenerProducto(entry.id, entry.tipo);
    const costo = producto?.costo || precio * 0.7;
    costoTotal += cantidad * costo;
    precioVenta += cantidad * precio;
  });

  const ganancia = precioVenta - costoTotal;
  const margenPorcentaje = precioVenta > 0 ? (ganancia / precioVenta) * 100 : 0;

  return {
    costoTotal,
    precioVenta,
    ganancia,
    margenPorcentaje
  };
});

// Helper functions
const obtenerProducto = (id, tipo) => {
  const coleccion = tipo === 'producto' ? props.productos : props.servicios;
  return coleccion.find(item => item.id === id);
};

const showNotification = (message, type = 'success') => {
  notyf.open({ type, message, duration: 5000, ripple: true, dismissible: true });
};

// Client handling
const onClienteSeleccionado = (cliente) => {
  if (!cliente) {
    limpiarCliente();
    return;
  }
  if (clienteSeleccionado.value?.id === cliente.id) return;

  clienteSeleccionado.value = cliente;
  form.cliente_id = cliente.id;
  form.clearErrors('cliente_id');
  showNotification(`Cliente seleccionado: ${cliente.nombre_razon_social}`);
};

const limpiarCliente = () => {
  clienteSeleccionado.value = null;
  form.cliente_id = '';
  form.clearErrors('cliente_id');
  showNotification('Selección de cliente limpiada', 'info');
};

const crearNuevoCliente = async (nombreBuscado) => {
  try {
    const response = await axios.post(route('clientes.store'), { nombre_razon_social: nombreBuscado });
    const nuevoCliente = response.data;
    if (!props.clientes.some(c => c.id === nuevoCliente.id)) {
      props.clientes.push(nuevoCliente);
    }
    onClienteSeleccionado(nuevoCliente);
    showNotification(`Cliente creado: ${nuevoCliente.nombre_razon_social}`);
  } catch (error) {
    console.error('Error creating new client:', error);
    showNotification('No se pudo crear el cliente. Inténtalo de nuevo.', 'error');
  }
};

// Product handling
const agregarProducto = (item) => {
  const itemEntry = { id: item.id, tipo: item.tipo };
  const exists = selectedProducts.value.some(entry => entry.id === item.id && entry.tipo === item.tipo);

  if (!exists) {
    selectedProducts.value.push(itemEntry);
    const key = `${item.tipo}-${item.id}`;
    quantities.value[key] = 1;
    prices.value[key] = item.tipo === 'producto' ? (item.precio_venta || 0) : (item.precio || 0);
    discounts.value[key] = 0;
    calcularTotal();
    showNotification(`Producto añadido: ${item.nombre || item.descripcion}`);
  }
};

const eliminarProducto = (entry) => {
  const item = obtenerProducto(entry.id, entry.tipo);
  selectedProducts.value = selectedProducts.value.filter(item => !(item.id === entry.id && item.tipo === entry.tipo));
  const key = `${entry.tipo}-${entry.id}`;
  delete quantities.value[key];
  delete prices.value[key];
  delete discounts.value[key];
  calcularTotal();
  showNotification(`Producto eliminado: ${item?.nombre || item?.descripcion}`, 'info');
};

const updateQuantity = (key, quantity) => {
  quantities.value[key] = quantity;
  calcularTotal();
};

const updateDiscount = (key, discount) => {
  discounts.value[key] = discount;
  calcularTotal();
};

// Price verification
const verificarPrecios = async () => {
  try {
    const response = await axios.post(route('productos.verificarPrecios'), {
      productos: selectedProducts.value.map(entry => ({ id: entry.id, tipo: entry.tipo }))
    });
    const updatedPrices = response.data;
    selectedProducts.value.forEach(entry => {
      const key = `${entry.tipo}-${entry.id}`;
      if (updatedPrices[key]) {
        prices.value[key] = updatedPrices[key].precio;
      }
    });
    calcularTotal();
    showNotification('Precios verificados y actualizados');
  } catch (error) {
    console.error('Error verifying prices:', error);
    showNotification('No se pudieron verificar los precios. Inténtalo de nuevo.', 'error');
  }
};

// Autosave
const guardarBorrador = async () => {
  if (!clienteSeleccionado.value || selectedProducts.value.length === 0) return;

  autoguardando.value = true;
  try {
    const draftData = {
      cliente_id: form.cliente_id,
      productos: selectedProducts.value.map(entry => {
        const key = `${entry.tipo}-${entry.id}`;
        return {
          id: entry.id,
          tipo: entry.tipo,
          cantidad: quantities.value[key] || 1,
          precio: prices.value[key] || 0,
          descuento: discounts.value[key] || 0
        };
      }),
      descuento_general: descuentoGeneral.value,
      totales: totales.value
    };

    await axios.post(route('cotizaciones.draft'), draftData);
    ultimoAutoguardado.value = new Date();
    mostrarNotificacionAutoguardado.value = true;
    setTimeout(() => mostrarNotificacionAutoguardado.value = false, 3000);
    showNotification('Borrador guardado automáticamente');
  } catch (error) {
    console.error('Error saving draft:', error);
    showNotification('No se pudo guardar el borrador. Inténtalo de nuevo.', 'error');
  } finally {
    autoguardando.value = false;
  }
};

// Template handling
const aplicarPlantilla = (plantilla) => {
  selectedProducts.value = plantilla.productos.map(p => ({ id: p.id, tipo: p.tipo }));
  quantities.value = {};
  prices.value = {};
  discounts.value = {};

  plantilla.productos.forEach(p => {
    const key = `${p.tipo}-${p.id}`;
    quantities.value[key] = p.cantidad;
    prices.value[key] = p.precio;
    discounts.value[key] = p.descuento;
  });

  calcularTotal();
  mostrarPlantillas.value = false;
  showNotification(`Plantilla aplicada: ${plantilla.nombre}`);
};

const guardarPlantilla = async () => {
  if (!nuevaPlantilla.value.nombre) return;

  try {
    const plantillaData = {
      nombre: nuevaPlantilla.value.nombre,
      descripcion: nuevaPlantilla.value.descripcion,
      productos: selectedProducts.value.map(entry => {
        const key = `${entry.tipo}-${entry.id}`;
        return {
          id: entry.id,
          tipo: entry.tipo,
          cantidad: quantities.value[key] || 1,
          precio: prices.value[key] || 0,
          descuento: discounts.value[key] || 0
        };
      }),
      total: totales.value.total,
      fechaModificacion: new Date().toISOString()
    };

    const response = await axios.post(route('plantillas.store'), plantillaData);
    plantillas.value.push(response.data);
    nuevaPlantilla.value = { nombre: '', descripcion: '' };
    showNotification('Plantilla guardada con éxito');
  } catch (error) {
    console.error('Error saving template:', error);
    showNotification('No se pudo guardar la plantilla. Inténtalo de nuevo.', 'error');
  }
};

// Margin calculator toggle
const mostrarMargenProducto = () => {
  mostrarCalculadoraMargen.value = !mostrarCalculadoraMargen.value;
};

// Preview handling
const imprimirVistaPrevia = () => {
  window.print();
};

// Calculate total
const calcularTotal = () => {
  form.subtotal = totales.value.subtotal;
  form.descuento_general = totales.value.descuentoGeneral;
  form.descuento_items = totales.value.descuentoItems;
  form.iva = totales.value.iva;
  form.total = totales.value.total;
};

// Create quotation
const crearCotizacion = () => {
  form.clearErrors();

  if (!form.cliente_id) {
    form.setError('cliente_id', 'Debes seleccionar un cliente.');
    showNotification('Por favor, selecciona un cliente.', 'error');
    return;
  }

  if (selectedProducts.value.length === 0) {
    showNotification('Debes seleccionar al menos un producto o servicio.', 'error');
    return;
  }

  for (const entry of selectedProducts.value) {
    const key = `${entry.tipo}-${entry.id}`;
    if (!quantities.value[key] || quantities.value[key] <= 0) {
      showNotification('Todas las cantidades deben ser mayores a 0.', 'error');
      return;
    }
    if (discounts.value[key] < 0 || discounts.value[key] > 100) {
      showNotification('Los descuentos deben estar entre 0% y 100%.', 'error');
      return;
    }
  }

  if (descuentoGeneral.value < 0 || descuentoGeneral.value > 100) {
    showNotification('El descuento general debe estar entre 0% y 100%.', 'error');
    return;
  }

  form.productos = selectedProducts.value.map(entry => {
    const key = `${entry.tipo}-${entry.id}`;
    const cantidad = quantities.value[key] || 1;
    const precio = prices.value[key] || 0;
    const descuento = discounts.value[key] || 0;
    return {
      id: entry.id,
      tipo: entry.tipo,
      cantidad,
      precio,
      descuento,
      subtotal: cantidad * precio,
      descuento_monto: (cantidad * precio) * (descuento / 100)
    };
  });

  calcularTotal();

  form.post(route('cotizaciones.store'), {
    onSuccess: () => {
      selectedProducts.value = [];
      quantities.value = {};
      prices.value = {};
      discounts.value = {};
      descuentoGeneral.value = 0;
      clienteSeleccionado.value = null;
      form.reset();
      showNotification('Cotización creada con éxito');
    },
    onError: (errors) => {
      console.error('Validation errors:', errors);
      showNotification('Hubo errores de validación. Por favor, corrige los campos.', 'error');
    }
  });
};

// Lifecycle hooks
onMounted(() => {
  intervalAutoguardado.value = setInterval(() => {
    if (clienteSeleccionado.value && selectedProducts.value.length > 0) {
      guardarBorrador();
    }
  }, 30000);

  const handleKeydown = (event) => {
    if (event.ctrlKey) {
      switch (event.key) {
        case 's':
          event.preventDefault();
          guardarBorrador();
          break;
        case 'p':
          event.preventDefault();
          if (clienteSeleccionado.value && selectedProducts.value.length > 0) {
            mostrarVistaPrevia.value = true;
          }
          break;
        case 'f':
          event.preventDefault();
          buscarClienteRef.value?.focus();
          break;
        case 'b':
          event.preventDefault();
          buscarProductoRef.value?.focus();
          break;
      }
    }
  };

  window.addEventListener('keydown', handleKeydown);

  onUnmounted(() => {
    clearInterval(intervalAutoguardado.value);
    window.removeEventListener('keydown', handleKeydown);
  });
});
</script>
