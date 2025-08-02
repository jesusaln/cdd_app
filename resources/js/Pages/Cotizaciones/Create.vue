<template>
  <Head title="Crear cotizaciones" />
  <div class="cotizaciones-create min-h-screen bg-gradient-to-br from-slate-50 to-blue-50 p-6">
    <div class="max-w-6xl mx-auto">
      <Header
        :autoguardando="autoguardando"
        :ultimoAutoguardado="ultimoAutoguardado"
        @mostrar-vista-previa="toggleVistaPrevia"
        @mostrar-plantillas="togglePlantillas"
        :clienteSeleccionado="clienteSeleccionado"
        :selectedProducts="selectedProducts"
      />

      <AtajosTeclado v-if="mostrarAtajos" @cerrar="mostrarAtajos = false" />

      <form @submit.prevent="crearCotizacion" class="space-y-8">
        <BuscarCliente
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
          @mostrar-margen="toggleCalculadoraMargen"

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
            {{ autoguardando ? 'Guardando...' : 'Guardar Borrador' }}
          </button>
          <button
            type="submit"
            :disabled="!canSubmitForm"
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

      <!-- Modals -->
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
      <AyudaBoton @toggle-atajos="toggleAtajos" />
    </div>
  </div>
</template>

<script setup>
import { ref, watch, computed, onMounted, onUnmounted, nextTick, toRefs } from 'vue';
import { Head, useForm, Link, router } from '@inertiajs/vue3';
import { Notyf } from 'notyf';
import axios from 'axios';

// Components
import AppLayout from '@/Layouts/AppLayout.vue';
import Header from '@/Components/Cotizaciones/Header.vue';
import AtajosTeclado from '@/Components/Cotizaciones/AtajosTeclado.vue';
import ClienteInfo from '@/Components/Cotizaciones/ClienteInfo.vue';
import ProductosServicios from '@/Components/Cotizaciones/ProductosServicios.vue';
import CalculadoraMargenes from '@/Components/Cotizaciones/CalculadoraMargenes.vue';
import DescuentoGeneral from '@/Components/Cotizaciones/DescuentoGeneral.vue';
import ResumenTotal from '@/Components/Cotizaciones/ResumenTotal.vue';
import VistaPreviaModal from '@/Components/Cotizaciones/VistaPreviaModal.vue';
import PlantillasModal from '@/Components/Cotizaciones/PlantillasModal.vue';
import NotificacionAutoguardado from '@/Components/Cotizaciones/NotificacionAutoguardado.vue';
import AyudaBoton from '@/Components/Cotizaciones/AyudaBoton.vue';
import CrearClienteModal from '@/Components/Cotizaciones/CrearClienteModal.vue';
import BuscarCliente from '@/Components/Cotizaciones/BuscarCliente.vue';

// Inicializar Notyf
const notyf = new Notyf({
  duration: 5000,
  position: {
    x: 'right',
    y: 'top' // Cambia 'bottom' a 'top'
  },
  types: [
    {
      type: 'success',
      background: '#10B981',
      icon: {
        className: 'notyf__icon--success',
        tagName: 'i',
        text: '✓'
      }
    },
    {
      type: 'error',
      background: '#EF4444',
      icon: {
        className: 'notyf__icon--error',
        tagName: 'i',
        text: '✗'
      }
    },
    {
      type: 'info',
      background: '#3B82F6',
      icon: {
        className: 'notyf__icon--info',
        tagName: 'i',
        text: 'ℹ'
      }
    },
    {
      type: 'warning',
      background: '#F59E0B',
      icon: {
        className: 'notyf__icon--warning',
        tagName: 'i',
        text: '⚠'
      }
    }
  ]
});

// Constants
const IVA_RATE = 0.16;
const AUTOSAVE_INTERVAL = 30000; // 30 segundos
const NOTIFICATION_DURATION = 3000;

const verificandoPrecios = ref(false);
const guardandoBorrador = ref(false);
const creandoCliente = ref(false);

// Define layout
defineOptions({ layout: AppLayout });

// Props
const props = defineProps({
  clientes: {
    type: Array,
    required: true,
    validator: (value) => Array.isArray(value)
  },
  productos: {
    type: Array,
    default: () => [],
    validator: (value) => Array.isArray(value)
  },
  servicios: {
    type: Array,
    default: () => [],
    validator: (value) => Array.isArray(value)
  },
});

// Composables inline (funciones de utilidad)
const showNotification = (message, type = 'success') => {
  notyf.open({
    type,
    message,
    duration: 5000,
    ripple: true,
    dismissible: true
  });
};

const debounce = (func, wait) => {
  let timeout;
  return function executedFunction(...args) {
    const later = () => {
      clearTimeout(timeout);
      func(...args);
    };
    clearTimeout(timeout);
    timeout = setTimeout(later, wait);
  };
};

// Función para calcular totales
const calculateTotals = ({ selectedProducts, quantities, prices, discounts, descuentoGeneral }) => {
  let subtotal = 0;
  let descuentoItems = 0;

  for (const entry of selectedProducts) {
    const key = `${entry.tipo}-${entry.id}`;
    const cantidad = Number.parseFloat(quantities[key]) || 0;
    const precio = Number.parseFloat(prices[key]) || 0;
    const descuentoItem = Number.parseFloat(discounts[key]) || 0;

    const subtotalItem = cantidad * precio;
    const descuentoItemMonto = subtotalItem * (descuentoItem / 100);

    subtotal += subtotalItem;
    descuentoItems += descuentoItemMonto;
  }

  const subtotalConDescuentoItems = subtotal - descuentoItems;
  const descuentoGeneralMonto = subtotalConDescuentoItems * (descuentoGeneral / 100);
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
};

// Función para calcular márgenes
const calculateMargins = ({ selectedProducts, quantities, prices, productos, servicios }) => {
  let costoTotal = 0;
  let precioVenta = 0;

  for (const entry of selectedProducts) {
    const key = `${entry.tipo}-${entry.id}`;
    const cantidad = parseFloat(quantities[key]) || 0;
    const precio = parseFloat(prices[key]) || 0;

    const coleccion = entry.tipo === 'producto' ? productos : servicios;
    const producto = coleccion.find(item => item.id === entry.id);
    const costo = producto?.costo || precio * 0.7;

    costoTotal += cantidad * costo;
    precioVenta += cantidad * precio;
  }

  const ganancia = precioVenta - costoTotal;
  const margenPorcentaje = precioVenta > 0 ? (ganancia / precioVenta) * 100 : 0;

  return {
    costoTotal,
    precioVenta,
    ganancia,
    margenPorcentaje
  };
};

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

// Refs para componentes
const buscarClienteRef = ref(null);
const buscarProductoRef = ref(null);

// Estado de la UI
const uiState = ref({
  mostrarVistaPrevia: false,
  mostrarPlantillas: false,
  mostrarCalculadoraMargen: false,
  mostrarAtajos: true,
  mostrarNotificacionAutoguardado: false,
});

// Estado del autoguardado
const autoguardando = ref(false);
const ultimoAutoguardado = ref(null);

// Estado de productos y clientes
const selectedProducts = ref([]);
const quantities = ref({});
const prices = ref({});
const discounts = ref({});
const descuentoGeneral = ref(0);
const clienteSeleccionado = ref(null);

// Plantillas
const nuevaPlantilla = ref({ nombre: '', descripcion: '' });
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

// Computed properties
const totales = computed(() => {
  return calculateTotals({
    selectedProducts: selectedProducts.value,
    quantities: quantities.value,
    prices: prices.value,
    discounts: discounts.value,
    descuentoGeneral: descuentoGeneral.value
  });
});

const calculadoraMargen = computed(() => {
  return calculateMargins({
    selectedProducts: selectedProducts.value,
    quantities: quantities.value,
    prices: prices.value,
    productos: props.productos,
    servicios: props.servicios
  });
});

const canSubmitForm = computed(() => {
  return !form.processing &&
         clienteSeleccionado.value &&
         selectedProducts.value.length > 0 &&
         isValidQuantities.value &&
         isValidDiscounts.value;
});

const isValidQuantities = computed(() => {
  return selectedProducts.value.every(entry => {
    const key = `${entry.tipo}-${entry.id}`;
    const quantity = quantities.value[key];
    return quantity && quantity > 0;
  });
});

const isValidDiscounts = computed(() => {
  const itemDiscountsValid = selectedProducts.value.every(entry => {
    const key = `${entry.tipo}-${entry.id}`;
    const discount = discounts.value[key] || 0;
    return discount >= 0 && discount <= 100;
  });

  const generalDiscountValid = descuentoGeneral.value >= 0 && descuentoGeneral.value <= 100;

  return itemDiscountsValid && generalDiscountValid;
});

// Métodos de UI
const toggleVistaPrevia = () => {
  if (canShowPreview.value) {
    uiState.value.mostrarVistaPrevia = true;
  } else {
    showNotification('Necesitas seleccionar un cliente y al menos un producto', 'warning');
  }
};

const togglePlantillas = () => {
  uiState.value.mostrarPlantillas = true;
};

const toggleCalculadoraMargen = () => {
  uiState.value.mostrarCalculadoraMargen = !uiState.value.mostrarCalculadoraMargen;
};

const toggleAtajos = () => {
  uiState.value.mostrarAtajos = !uiState.value.mostrarAtajos;
};

const canShowPreview = computed(() => {
  return clienteSeleccionado.value && selectedProducts.value.length > 0;
});

// Desestructuring para acceso directo a estados UI
const {
  mostrarVistaPrevia,
  mostrarPlantillas,
  mostrarCalculadoraMargen,
  mostrarAtajos,
  mostrarNotificacionAutoguardado
} = toRefs(uiState.value);

// Funciones de utilidad
const obtenerProducto = (id, tipo) => {
  const coleccion = tipo === 'producto' ? props.productos : props.servicios;
  return coleccion.find(item => item.id === id);
};

const generateProductKey = (id, tipo) => `${tipo}-${id}`;

const calcularTotal = debounce(() => {
  const totalesCalculados = totales.value;
  form.subtotal = totalesCalculados.subtotal;
  form.descuento_general = totalesCalculados.descuentoGeneral;
  form.descuento_items = totalesCalculados.descuentoItems;
  form.iva = totalesCalculados.iva;
  form.total = totalesCalculados.total;
}, 100);

// Manejo de clientes
const onClienteSeleccionado = (cliente) => {
  if (!cliente) {
    limpiarCliente();
    return;
  }

  if (clienteSeleccionado.value?.id === cliente.id) {
    return;
  }

  clienteSeleccionado.value = cliente;
  form.cliente_id = cliente.id;
  form.clearErrors('cliente_id');
  showNotification(`Cliente seleccionado: ${cliente.nombre_razon_social}`, 'success');
};

const limpiarCliente = () => {
  clienteSeleccionado.value = null;
  form.cliente_id = '';
  form.clearErrors('cliente_id');
  showNotification('Selección de cliente limpiada', 'info');
};

const crearNuevoCliente = async (nombreBuscado) => {
  if (!nombreBuscado?.trim()) {
    notyf.open({ type: 'error', message: 'El nombre del cliente es requerido' });
    return;
  }

  creandoCliente.value = true; // ✅ AGREGAR
  try {
    const response = await axios.post(route('clientes.store'), {
      nombre_razon_social: nombreBuscado.trim()
    });
    // ... resto del código igual
  } catch (error) {
    // ... código de error igual
  } finally {
    creandoCliente.value = false; // ✅ AGREGAR
  }
};

// Validación de stock y precios en backend
const validateStockAndPrices = async () => {
  if (selectedProducts.value.length === 0) return true;

  try {
    const response = await axios.post(route('productos.validateStock'), {
      productos: selectedProducts.value.map(entry => {
        const key = `${entry.tipo}-${entry.id}`;
        return {
          id: entry.id,
          tipo: entry.tipo,
          cantidad: quantities.value[key] || 1
        };
      })
    });

    const validation = response.data;

    if (!validation.valid) {
      // Mostrar errores específicos
      validation.errors.forEach(error => {
        notyf.open({
          type: 'error',
          message: `❌ ${error.producto}: ${error.mensaje}`,
          duration: 7000
        });
      });
      return false;
    }

    // Actualizar precios si han cambiado
    if (validation.pricesUpdated && validation.pricesUpdated.length > 0) {
      validation.pricesUpdated.forEach(update => {
        const key = `${update.tipo}-${update.id}`;
        if (prices.value[key] !== update.nuevoPrecio) {
          prices.value[key] = update.nuevoPrecio;
          notyf.open({
            type: 'info',
            message: `💰 Precio actualizado: ${update.nombre}`,
            duration: 5000
          });
        }
      });
      nextTick(() => calcularTotal());
    }

    return true;
  } catch (error) {
    console.error('Error validando stock:', error);
    notyf.open({
      type: 'error',
      message: 'Error al validar disponibilidad de productos'
    });
    return false;
  }
};

// Manejo de productos
const agregarProducto = (item) => {
 if (!item?.id || !item?.tipo) {
   console.error('Intento de agregar producto inválido:', item);
   notyf.open({ type: 'error', message: 'Datos de producto inválidos' });
   return;
 }

 // Validar nombre y precio
 const nombreProducto = item.nombre || item.descripcion || 'Producto sin nombre';
 const precioProducto = item.tipo === 'producto' ? (item.precio_venta || item.precio || 0) : (item.precio || 0);

 if (precioProducto <= 0) {
   notyf.open({
     type: 'warning',
     message: `"${nombreProducto}" no tiene precio válido. Se asignará precio 0.`
   });
 }

 const itemEntry = { id: item.id, tipo: item.tipo };
 const exists = selectedProducts.value.some(
   entry => entry.id === item.id && entry.tipo === item.tipo
 );

 if (exists) {
   console.log('Producto ya existe en la cotización:', item);
   notyf.open({
     type: 'warning',
     message: `"${nombreProducto}" ya está en la cotización`
   });
   return;
 }

 selectedProducts.value.push(itemEntry);
 const key = generateProductKey(item.id, item.tipo);

 quantities.value[key] = 1;
 prices.value[key] = precioProducto;
 discounts.value[key] = 0;

 console.log('Producto agregado:', {
   id: item.id,
   tipo: item.tipo,
   nombre: nombreProducto,
   cantidad: quantities.value[key],
   precio: prices.value[key],
   descuento: discounts.value[key]
 });

 nextTick(() => calcularTotal());
 notyf.open({
   type: 'success',
   message: `✓ "${nombreProducto}" añadido a la cotización`
 });
};

const eliminarProducto = (entry) => {
 const item = obtenerProducto(entry.id, entry.tipo);
 const nombreProducto = item?.nombre || item?.descripcion || 'Producto';

 selectedProducts.value = selectedProducts.value.filter(
   item => !(item.id === entry.id && item.tipo === entry.tipo)
 );

 const key = generateProductKey(entry.id, entry.tipo);
 delete quantities.value[key];
 delete prices.value[key];
 delete discounts.value[key];

 nextTick(() => calcularTotal());
 notyf.open({
   type: 'info',
   message: `"${nombreProducto}" eliminado de la cotización`
 });
};

const updateQuantity = (key, quantity) => {
  const numericQuantity = Number(quantity);
  if (numericQuantity < 0) {
    showNotification('La cantidad no puede ser negativa', 'error');
    return;
  }

  quantities.value[key] = numericQuantity;
  nextTick(() => calcularTotal());
};

const updateDiscount = (key, discount) => {
  const numericDiscount = Number(discount);
  if (numericDiscount < 0 || numericDiscount > 100) {
    showNotification('El descuento debe estar entre 0% y 100%', 'error');
    return;
  }

  discounts.value[key] = numericDiscount;
  nextTick(() => calcularTotal());
};



const validateDraftData = (draftData) => {
  const errors = [];
  if (!draftData.cliente_id) {
    errors.push('El ID del cliente es requerido.');
  }
  if (!draftData.productos || draftData.productos.length === 0) {
    errors.push('Debes seleccionar al menos un producto.');
  } else {
    draftData.productos.forEach(producto => {
      if (!producto.id || !producto.tipo) {
        errors.push('Datos de producto inválidos.');
      }
      if (producto.cantidad <= 0) {
        errors.push('La cantidad debe ser mayor que cero.');
      }
      if (producto.precio <= 0) {
        errors.push('El precio debe ser mayor que cero.');
      }
      if (producto.descuento < 0 || producto.descuento > 100) {
        errors.push('El descuento debe estar entre 0% y 100%.');
      }
    });
  }
  return errors;
};

function guardarBorrador() {
    // Validar datos mínimos antes de guardar
    if (!clienteSeleccionado.value) {
        notyf.open({ type: 'warning', message: 'Selecciona un cliente antes de guardar' });
        return;
    }

    if (selectedProducts.value.length === 0) {
        notyf.open({ type: 'warning', message: 'Agrega al menos un producto antes de guardar' });
        return;
    }

    // Validar que las cantidades sean válidas
    const hasInvalidQuantities = selectedProducts.value.some(entry => {
        const key = `${entry.tipo}-${entry.id}`;
        const quantity = quantities.value[key];
        return !quantity || quantity <= 0;
    });

    if (hasInvalidQuantities) {
        notyf.open({ type: 'warning', message: 'Corrige las cantidades antes de guardar' });
        return;
    }

    const url = form.id
        ? route('cotizaciones.draft', { cotizacion: form.id })
        : route('cotizaciones.storeDraft');

    form.post(url, {
        onSuccess: () => {
            notyf.open({ type: 'success', message: '💾 Borrador guardado correctamente' });
            ultimoAutoguardado.value = new Date();
        },
        onError: (errors) => {
            notyf.open({ type: 'error', message: 'Error al guardar el borrador' });
            console.error('Errores al guardar borrador:', errors);
        },
    });

}






// Manejo de plantillas
const aplicarPlantilla = (plantilla) => {
  try {
    selectedProducts.value = plantilla.productos.map(p => ({ id: p.id, tipo: p.tipo }));
    quantities.value = {};
    prices.value = {};
    discounts.value = {};

    plantilla.productos.forEach(p => {
      const key = generateProductKey(p.id, p.tipo);
      quantities.value[key] = p.cantidad;
      prices.value[key] = p.precio;
      discounts.value[key] = p.descuento;
    });

    nextTick(() => calcularTotal());
    uiState.value.mostrarPlantillas = false;
    showNotification(`Plantilla aplicada: ${plantilla.nombre}`, 'success');
  } catch (error) {
    console.error('Error applying template:', error);
    showNotification('Error al aplicar la plantilla', 'error');
  }
};

const guardarPlantilla = async () => {
  if (!nuevaPlantilla.value.nombre?.trim()) {
    showNotification('El nombre de la plantilla es requerido', 'error');
    return;
  }

  if (selectedProducts.value.length === 0) {
    showNotification('No hay productos para guardar en la plantilla', 'warning');
    return;
  }

  try {
    const plantillaData = {
      nombre: nuevaPlantilla.value.nombre.trim(),
      descripcion: nuevaPlantilla.value.descripcion?.trim() || '',
      productos: selectedProducts.value.map(entry => {
        const key = generateProductKey(entry.id, entry.tipo);
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
    showNotification('Plantilla guardada con éxito', 'success');
  } catch (error) {
    console.error('Error saving template:', error);
    const errorMessage = error.response?.data?.message || 'No se pudo guardar la plantilla';
    showNotification(errorMessage, 'error');
  }
};

// Vista previa
const imprimirVistaPrevia = () => {
  try {
    window.print();
  } catch (error) {
    console.error('Error printing:', error);
    showNotification('Error al imprimir', 'error');
  }
};

// Acciones del formulario
const crearCotizacion = async () => {
  form.clearErrors();

  console.log('Iniciando proceso de guardado...');
  console.log('Cliente seleccionado:', clienteSeleccionado.value);
  console.log('Productos seleccionados:', selectedProducts.value);

  const validationErrors = validateForm();
  if (validationErrors.length > 0) {
    console.error('Errores de validación:', validationErrors);
    validationErrors.forEach(error => showNotification(error, 'error'));
    return;
  }

  // ✅ AGREGAR ESTA VALIDACIÓN:
  const stockValid = await validateStockAndPrices();
  if (!stockValid) {
    notyf.open({
      type: 'warning',
      message: 'Corrige los problemas de stock antes de continuar'
    });
    return;
  }

  try {
    // Preparar productos para envío
    form.productos = selectedProducts.value.map((entry) => {
      const key = generateProductKey(entry.id, entry.tipo);
      const cantidad = quantities.value[key] || 1;
      const precio = prices.value[key] || 0;
      const descuento = discounts.value[key] || 0;

      const productoData = {
        id: entry.id,
        tipo: entry.tipo,
        cantidad,
        precio,
        descuento,
        subtotal: cantidad * precio,
        descuento_monto: (cantidad * precio) * (descuento / 100)
      };

      console.log('Preparando producto para tabla pivote:', productoData);
      return productoData;
    });

    calcularTotal();

    console.log('Datos completos a enviar:', {
      cliente_id: form.cliente_id,
      productos: form.productos,
      totales: {
        subtotal: form.subtotal,
        descuento_general: form.descuento_general,
        descuento_items: form.descuento_items,
        iva: form.iva,
        total: form.total
      }
    });

    form.post(route('cotizaciones.store'), {
      onSuccess: (response) => {
        console.log('Respuesta del servidor:', response);
        // Opción 1: Solo si rediriges a la vista de cotización
        //console.log('Cotización creada exitosamente. Redirigiendo...');

        router.visit(route('cotizaciones.index'), {
          onSuccess: () => {
            showNotification('Cotización creada con éxito', 'success');
          },
          onError: (errors) => {
            console.error('Error al redirigir:', errors);
            showNotification('Error al redirigir a la lista de cotizaciones', 'error');
          }
        });
      },
      onError: (errors) => {
        console.error('Errores en el guardado:', errors);
        showNotification('Hubo errores de validación. Por favor, corrige los campos', 'error');
      }
    });
  } catch (error) {
    console.error('Error en el proceso de guardado:', error);
    showNotification('Error al crear la cotización', 'error');
  }
};

// Función de validación del formulario
const validateForm = () => {
  const errors = [];
  if (!form.cliente_id) {
    errors.push('Debes seleccionar un cliente');
    form.setError('cliente_id', 'Debes seleccionar un cliente.');
  }
  if (selectedProducts.value.length === 0) {
    errors.push('Debes seleccionar al menos un producto o servicio');
  }
  if (!isValidQuantities.value) {
    errors.push('Todas las cantidades deben ser mayores a 0');
  }
  if (!isValidDiscounts.value) {
    errors.push('Los descuentos deben estar entre 0% y 100%');
  }
  return errors;
};


// Atajos de teclado
const setupKeyboardShortcuts = () => {
  const shortcuts = {
    'ctrl+s': (e) => {
      e.preventDefault();
      guardarBorrador();
    },
    'ctrl+p': (e) => {
      e.preventDefault();
      if (canShowPreview.value) {
        toggleVistaPrevia();
      }
    },
    'ctrl+f': (e) => {
      e.preventDefault();
      buscarClienteRef.value?.focus?.();
    },
    'ctrl+b': (e) => {
      e.preventDefault();
      buscarProductoRef.value?.focus?.();
    },
  };

  const handleKeydown = (event) => {
    const key = `${event.ctrlKey ? 'ctrl+' : ''}${event.key.toLowerCase()}`;
    const handler = shortcuts[key];
    if (handler) {
      handler(event);
    }
  };

  window.addEventListener('keydown', handleKeydown);
  return () => window.removeEventListener('keydown', handleKeydown);
};

let autoSaveInterval = null;

const startAutoSave = () => {
  // Asegurarse de no iniciar múltiples intervalos
  if (autoSaveInterval) {
    clearInterval(autoSaveInterval);
  }

  autoSaveInterval = setInterval(() => {
    // Solo guardar si hay algo que guardar para evitar llamadas innecesarias
    if (clienteSeleccionado.value && selectedProducts.value.length > 0) {
      guardarBorrador();
    }
  }, AUTOSAVE_INTERVAL);
};

const stopAutoSave = () => {
  if (autoSaveInterval) {
    clearInterval(autoSaveInterval);
  }
};

// Lifecycle hooks
onMounted(() => {
  console.log('Componente montado con datos:', {
    clientes: props.clientes.length,
    productos: props.productos.length,
    servicios: props.servicios.length
  });

  // Verificar duplicados en clientes
  const duplicateClients = props.clientes.filter((cliente, index, self) =>
    index !== self.findIndex(c => c.id === cliente.id)
  );

  if (duplicateClients.length > 0) {
    console.warn('Clientes duplicados encontrados:', duplicateClients);
  }

  // Configurar autoguardado
  startAutoSave();

  // Configurar atajos de teclado
  const cleanupKeyboardShortcuts = setupKeyboardShortcuts();

  onUnmounted(() => {
    stopAutoSave();
    cleanupKeyboardShortcuts();
  });
});
</script>

<style scoped>
/* Estilos específicos del componente si son necesarios */
.cotizaciones-create {
  /* Animaciones suaves para transiciones */
  transition: all 0.3s ease;
}

/* Mejoras de accesibilidad */
@media (prefers-reduced-motion: reduce) {
  * {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
  }
}
</style>
