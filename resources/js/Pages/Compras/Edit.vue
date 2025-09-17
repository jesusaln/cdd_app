<template>
  <Head title="Editar Compra" />
  <div class="compras-edit min-h-screen bg-gradient-to-br from-slate-50 to-blue-50 p-6">
    <div class="max-w-6xl mx-auto">
      <!-- Header -->
      <Header
        title="Editar Compra"
        description="Modifica los detalles de la compra"
        :can-preview="proveedorSeleccionado && selectedProducts.length > 0"
        :back-url="route('compras.index')"
        :show-shortcuts="mostrarAtajos"
        @preview="handlePreview"
        @close-shortcuts="closeShortcuts"
      />

      <form @submit.prevent="actualizarCompra" class="space-y-8">
        <!-- Proveedor -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
          <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-4">
            <h2 class="text-lg font-semibold text-white flex items-center">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
              </svg>
              Información del Proveedor
            </h2>
          </div>
          <div class="p-6">
            <BuscarProveedor
              ref="buscarProveedorRef"
              :proveedores="proveedoresActivos"
              :proveedor-seleccionado="proveedorSeleccionado"
              @proveedor-seleccionado="onProveedorSeleccionado"
              @crear-nuevo-proveedor="crearNuevoProveedor"
            />
          </div>
        </div>

        <!-- Productos -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
          <div class="bg-gradient-to-r from-green-500 to-green-600 px-6 py-4">
            <h2 class="text-lg font-semibold text-white flex items-center">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
              </svg>
              Productos
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
              @eliminar-producto="eliminarProducto"
              @update-quantity="updateQuantity"
              @update-discount="updateDiscount"
            />
          </div>
        </div>

        <!-- Notas -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
          <div class="bg-gradient-to-r from-purple-500 to-purple-600 px-6 py-4">
            <h2 class="text-lg font-semibold text-white flex items-center">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
              </svg>
              Notas Adicionales
            </h2>
          </div>
          <div class="p-6">
            <textarea
              v-model="form.notas"
              class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-vertical"
              rows="4"
              placeholder="Agrega notas adicionales, términos y condiciones, o información relevante para la compra..."
            ></textarea>
          </div>
        </div>

        <!-- Totales -->
        <Totales
          :show-margin-calculator="false"
          :margin-data="{ costoTotal: 0, precioVenta: 0, ganancia: 0, margenPorcentaje: 0 }"
          :totals="totales"
          :item-count="selectedProducts.length"
          :total-quantity="Object.values(quantities).reduce((sum, qty) => sum + (qty || 0), 0)"
          @update:descuento-general="val => form.descuento_general = val"
        />

        <!-- Botones -->
        <BotonesAccion
          :back-url="route('compras.index')"
          :is-processing="form.processing"
          :can-submit="form.proveedor_id && selectedProducts.length > 0"
          :button-text="form.processing ? 'Guardando...' : 'Actualizar Compra'"
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
    </div>

    <!-- Modal Vista Previa -->
    <VistaPreviaModal
      :show="mostrarVistaPrevia"
      type="compra"
      :proveedor="proveedorSeleccionado"
      :items="selectedProducts"
      :totals="totales"
      :notas="form.notas"
      @close="mostrarVistaPrevia = false"
      @print="() => window.print()"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import axios from 'axios';
import { Notyf } from 'notyf';
import AppLayout from '@/Layouts/AppLayout.vue';
import Header from '@/Components/CreateComponents/Header.vue';
import BuscarProveedor from '@/Components/CreateComponents/BuscarProveedor.vue';
import BuscarProducto from '@/Components/CreateComponents/BuscarProducto.vue';
import ProductosSeleccionados from '@/Components/CreateComponents/ProductosSeleccionados.vue';
import Totales from '@/Components/CreateComponents/Totales.vue';
import BotonesAccion from '@/Components/CreateComponents/BotonesAccion.vue';
import VistaPreviaModal from '@/Components/Modals/VistaPreviaModal.vue';

const notyf = new Notyf({
  duration: 5000,
  position: { x: 'right', y: 'top' },
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
  compra: Object,
  proveedores: Array,
  productos: Array,
  servicios: { type: Array, default: () => [] },
});

// Filtrar solo proveedores activos
const proveedoresActivos = computed(() => {
  return props.proveedores.filter(proveedor => {
    // Verificar diferentes formas de representar el estado activo
    const estado = proveedor.estado || proveedor.status || proveedor.activo;

    // Manejar diferentes tipos de valores para estado activo
    if (typeof estado === 'string') {
      return estado.toLowerCase() === 'activo' || estado.toLowerCase() === 'active';
    } else if (typeof estado === 'boolean') {
      return estado === true;
    } else if (typeof estado === 'number') {
      return estado === 1;
    }

    // Si no hay campo de estado, asumir que está activo (por compatibilidad)
    return true;
  });
});

// Formulario
const form = useForm({
  proveedor_id: props.compra?.proveedor?.id || '',
  subtotal: props.compra?.subtotal || 0,
  iva: props.compra?.iva || 0,
  total: props.compra?.total || 0,
  productos: [],
  notas: props.compra?.notas || '',
});

// Estado
const selectedProducts = ref([]);
const quantities = ref({});
const prices = ref({});
const discounts = ref({});
const proveedorSeleccionado = ref(props.compra?.proveedor || null);
const mostrarVistaPrevia = ref(false);
const mostrarAtajos = ref(true);

// Validaciones
const isValidNumber = (value, min = 0) => {
  const num = parseFloat(value);
  return !isNaN(num) && num >= min;
};

const validateQuantity = (quantity) => {
  return isValidNumber(quantity, 0.01);
};

const validatePrice = (price) => {
  return isValidNumber(price, 0);
};

const validateDiscount = (discount) => {
  const num = parseFloat(discount);
  return !isNaN(num) && num >= 0 && num <= 100;
};

// Cargar datos
onMounted(() => {
  if (props.compra?.items) {
    props.compra.items.forEach(item => {
      const key = `${item.comprable_type === 'App\\\\Models\\\\Producto' ? 'producto' : 'servicio'}-${item.comprable_id}`;
      selectedProducts.value.push({ id: item.comprable_id, tipo: item.comprable_type === 'App\\\\Models\\\\Producto' ? 'producto' : 'servicio' });
      quantities.value[key] = item.cantidad || 1;
      prices.value[key] = item.precio || 0;
      discounts.value[key] = item.descuento || 0;
    });
    calcularTotal();
  }
});

// Funciones
const handlePreview = () => {
  if (proveedorSeleccionado.value && selectedProducts.value.length > 0) {
    mostrarVistaPrevia.value = true;
  } else {
    showNotification('Selecciona un proveedor y al menos un producto', 'error');
  }
};

const closeShortcuts = () => {
  mostrarAtajos.value = false;
};

const onProveedorSeleccionado = (proveedor) => {
  if (!proveedor) {
    proveedorSeleccionado.value = null;
    form.proveedor_id = '';
    showNotification('Proveedor eliminado', 'info');
    return;
  }
  proveedorSeleccionado.value = proveedor;
  form.proveedor_id = proveedor.id;
  showNotification(`Proveedor: ${proveedor.nombre_razon_social || proveedor.nombre || 'Sin nombre'}`);
};

const crearNuevoProveedor = async (nombreBuscado) => {
  if (!nombreBuscado?.trim()) {
    showNotification('El nombre del proveedor es requerido', 'error');
    return;
  }

  try {
    const response = await axios.post(route('proveedores.store'), {
      nombre_razon_social: nombreBuscado.trim()
    });

    if (response.data) {
      const nuevoProveedor = response.data;
      props.proveedores.push(nuevoProveedor);
      onProveedorSeleccionado(nuevoProveedor);
      showNotification(`Proveedor creado: ${nuevoProveedor.nombre_razon_social || nuevoProveedor.nombre || 'Sin nombre'}`);
    }
  } catch (error) {
    console.error('Error al crear proveedor:', error);

    if (error.response?.status === 422) {
      const errors = error.response.data?.errors;
      if (errors) {
        const errorMessages = Object.values(errors).flat().join(', ');
        showNotification(`Errores de validación: ${errorMessages}`, 'error');
      } else {
        showNotification('Datos de proveedor inválidos', 'error');
      }
    } else if (error.response?.status === 409) {
      showNotification('Ya existe un proveedor con ese nombre', 'error');
    } else if (error.response?.status >= 500) {
      showNotification('Error del servidor. Intenta nuevamente', 'error');
    } else {
      showNotification('No se pudo crear el proveedor', 'error');
    }
  }
};

const agregarProducto = (item) => {
  if (!item?.id || !item?.tipo) {
    showNotification('Producto inválido', 'error');
    return;
  }

  const itemEntry = { id: item.id, tipo: item.tipo };

  // Comparar correctamente item.tipo con entry.tipo
  const exists = selectedProducts.value.some(
    (entry) => entry.id === item.id && entry.tipo === item.tipo
  );

  if (!exists) {
    selectedProducts.value.push(itemEntry);
    const key = `${item.tipo}-${item.id}`;
    quantities.value[key] = 1;

    // Determinar precio según tipo
    let defaultPrice = 0;
    if (item.tipo === 'producto') {
      defaultPrice = item.precio_compra || item.precio || 0;
    } else if (item.tipo === 'servicio') {
      defaultPrice = item.precio || item.precio_compra || 0;
    }

    prices.value[key] = defaultPrice;
    discounts.value[key] = 0;
    calcularTotal();

    const itemName = item.nombre || item.descripcion || item.titulo || `${item.tipo} ${item.id}`;
    showNotification(`Añadido: ${itemName}`);
  } else {
    const itemName = item.nombre || item.descripcion || item.titulo || `${item.tipo} ${item.id}`;
    showNotification(`${itemName} ya está agregado`, 'info');
  }
};

const eliminarProducto = (entry) => {
  if (!entry?.id || !entry?.tipo) {
    showNotification('Error al eliminar producto', 'error');
    return;
  }

  const key = `${entry.tipo}-${entry.id}`;
  selectedProducts.value = selectedProducts.value.filter(
    (item) => !(item.id === entry.id && item.tipo === entry.tipo)
  );
  delete quantities.value[key];
  delete prices.value[key];
  delete discounts.value[key];
  calcularTotal();

  const itemName = entry.nombre || entry.descripcion || entry.titulo || `${entry.tipo} ${entry.id}`;
  showNotification(`Eliminado: ${itemName}`, 'info');
};

const updateQuantity = (key, quantity) => {
  if (!validateQuantity(quantity)) {
    showNotification('La cantidad debe ser mayor a 0', 'error');
    return;
  }
  quantities.value[key] = parseFloat(quantity);
  calcularTotal();
};

const updateDiscount = (key, discount) => {
  if (!validateDiscount(discount)) {
    showNotification('El descuento debe estar entre 0% y 100%', 'error');
    return;
  }
  discounts.value[key] = parseFloat(discount);
  calcularTotal();
};

// Cálculos
const totales = computed(() => {
  let subtotal = 0;
  let descuentoItems = 0;

  selectedProducts.value.forEach(entry => {
    const key = `${entry.tipo}-${entry.id}`;
    const cantidad = parseFloat(quantities.value[key]) || 0;
    const precio = parseFloat(prices.value[key]) || 0;
    const descuento = parseFloat(discounts.value[key]) || 0;

    const subtotalItem = cantidad * precio;
    const descuentoItem = subtotalItem * (descuento / 100);

    subtotal += subtotalItem;
    descuentoItems += descuentoItem;
  });

  const subtotalConDescuentos = subtotal - descuentoItems;
  const iva = subtotalConDescuentos * 0.16;
  const total = subtotalConDescuentos + iva;

  return {
    subtotal: Number(subtotal.toFixed(2)),
    descuentoItems: Number(descuentoItems.toFixed(2)),
    subtotalConDescuentos: Number(subtotalConDescuentos.toFixed(2)),
    iva: Number(iva.toFixed(2)),
    total: Number(total.toFixed(2)),
  };
});

const calcularTotal = () => {
  const totals = totales.value;
  form.subtotal = totals.subtotal;
  form.iva = totals.iva;
  form.total = totals.total;
};

// Guardar cambios
const actualizarCompra = () => {
  // Validaciones
  if (!form.proveedor_id) {
    showNotification('Selecciona un proveedor', 'error');
    return;
  }

  if (selectedProducts.value.length === 0) {
    showNotification('Agrega al menos un producto o servicio', 'error');
    return;
  }

  // Validar todos los productos seleccionados
  for (const entry of selectedProducts.value) {
    const key = `${entry.tipo}-${entry.id}`;
    const quantity = quantities.value[key];
    const price = prices.value[key];
    const discount = discounts.value[key] || 0;

    if (!validateQuantity(quantity)) {
      showNotification(`Cantidad inválida para ${entry.tipo} ${entry.id}`, 'error');
      return;
    }

    if (!validatePrice(price)) {
      showNotification(`Precio inválido para ${entry.tipo} ${entry.id}`, 'error');
      return;
    }

    if (!validateDiscount(discount)) {
      showNotification(`Descuento inválido para ${entry.tipo} ${entry.id}`, 'error');
      return;
    }
  }

  // Preparar datos para envío
  form.productos = selectedProducts.value.map(entry => {
    const key = `${entry.tipo}-${entry.id}`;
    return {
      id: entry.id,
      tipo: entry.tipo,
      cantidad: parseFloat(quantities.value[key]) || 1,
      precio: parseFloat(prices.value[key]) || 0,
      descuento: parseFloat(discounts.value[key]) || 0,
    };
  });

  calcularTotal();

  form.put(route('compras.update', props.compra?.id), {
    onSuccess: () => {
      showNotification('Compra actualizada con éxito');
    },
    onError: (errors) => {
      console.error('Errores de validación:', errors);

      // Mostrar errores específicos
      if (typeof errors === 'object' && errors !== null) {
        const errorMessages = Object.values(errors).flat().join(', ');
        showNotification(`Errores: ${errorMessages}`, 'error');
      } else {
        showNotification('Hubo errores al actualizar la compra', 'error');
      }
    },
  });
};
</script>
