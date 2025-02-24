<template>
    <div class="cotizaciones-show">
      <!-- Título de la página -->
      <h1 class="text-2xl font-semibold mb-6">Detalles de la Cotización</h1>

      <!-- Verificar si cotizacion no es null -->
      <div v-if="cotizacion" class="bg-white rounded-lg shadow-md p-6">
        <div class="mb-4">
          <h2 class="text-lg font-medium text-gray-700">Cliente</h2>
          <p>{{ cotizacion.cliente.nombre_razon_social }}</p>
        </div>
        <div class="mb-4">
          <h2 class="text-lg font-medium text-gray-700">Productos</h2>
          <ul>
            <li v-for="producto in cotizacion.productos" :key="producto.id" class="mb-2">
              <strong>{{ producto.nombre }}</strong> - ${{ producto.pivot.precio }} (Cantidad: {{ producto.pivot.cantidad }})
            </li>
          </ul>
        </div>
        <div class="mb-4">
          <h2 class="text-lg font-medium text-gray-700">Total</h2>
          <p>${{ cotizacion.total }}</p>
        </div>
      </div>
      <div v-else>
        <p>Cargando detalles de la cotización...</p>
      </div>

      <!-- Botones de acción -->
      <div v-if="cotizacion" class="mt-6 flex space-x-4">
        <Link :href="route('cotizaciones.edit', cotizacion.id)" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
          Editar
        </Link>
        <button @click="eliminarCotizacion(cotizacion.id)" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
          Eliminar
        </button>
        <button @click="enviarAPedido" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
          Enviar a Pedido
        </button>
      </div>

      <!-- Spinner de carga -->
      <div v-if="loading" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500"></div>
      </div>
    </div>
  </template>

  <script setup>
  import { Head, Link, router } from '@inertiajs/vue3';
  import { ref, defineEmits } from 'vue';
  import { Notyf } from 'notyf';
  import 'notyf/notyf.min.css';
  import Dashboard from '@/Pages/Dashboard.vue';

  // Define el layout del dashboard
  defineOptions({ layout: Dashboard });

  // Propiedades
  const props = defineProps({ cotizacion: Object });
  const loading = ref(false);
  const emit = defineEmits(['convertir-a-pedido']);

  // Configuración de Notyf para notificaciones
  const notyf = new Notyf({ duration: 3000, position: { x: 'right', y: 'top' } });

  // Función para eliminar una cotización
  const eliminarCotizacion = async (id) => {
    loading.value = true;
    if (confirm('¿Estás seguro de que deseas eliminar esta cotización?')) {
      try {
        await router.delete(`/cotizaciones/${id}`, {
          onSuccess: () => {
            notyf.success('Cotización eliminada exitosamente.');
            router.visit(route('cotizaciones.index'));
          },
          onError: () => notyf.error('Error al eliminar la cotización.')
        });
      } catch (error) {
        notyf.error('Ocurrió un error inesperado.');
      } finally {
        loading.value = false;
      }
    }
  };

  // Función para enviar a pedido
  const enviarAPedido = () => {
    emit('convertir-a-pedido', props.cotizacion);
  };
  </script>

  <style scoped>
  /* Aquí van tus estilos personalizados */
  </style>
