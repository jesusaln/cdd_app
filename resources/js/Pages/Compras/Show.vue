<template>
    <Head title="Mostrar Compra" />
    <div class="compras-show">
      <!-- Título de la página -->
      <h1 class="text-2xl font-semibold mb-6">Detalles de la Compra</h1>

      <!-- Verificar si compra no es null -->
      <div v-if="compra" class="bg-white rounded-lg shadow-md p-6">
        <div class="mb-4">
          <h2 class="text-lg font-medium text-gray-700">Proveedor</h2>
          <p>{{ compra.proveedor.nombre_razon_social }}</p>
        </div>
        <div class="mb-4">
          <h2 class="text-lg font-medium text-gray-700">Productos</h2>
          <ul>
            <li v-for="producto in compra.productos" :key="producto.id" class="mb-2">
              <strong>{{ producto.nombre }}</strong> - ${{ producto.pivot.precio }} (Cantidad: {{ producto.pivot.cantidad }})
            </li>
          </ul>
        </div>
        <div class="mb-4">
          <h2 class="text-lg font-medium text-gray-700">Total</h2>
          <p>${{ compra.total }}</p>
        </div>
        <div class="mb-4">
          <h2 class="text-lg font-medium text-gray-700">Estado</h2>
          <p>{{ compra.estado }}</p>
        </div>
      </div>
      <div v-else>
        <p>Cargando detalles de la compra...</p>
      </div>

      <!-- Botones de acción -->
      <div v-if="compra" class="mt-6 flex space-x-4">
        <Link :href="route('compras.edit', compra.id)" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
          Editar
        </Link>
        <button @click="eliminarCompra(compra.id)" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
          Eliminar
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
  import { ref, defineProps } from 'vue';
  import { Notyf } from 'notyf';
  import 'notyf/notyf.min.css';

  // Propiedades
  const props = defineProps({ compra: Object });
  const loading = ref(false);

  // Configuración de Notyf para notificaciones
  const notyf = new Notyf({ duration: 3000, position: { x: 'right', y: 'top' } });

  // Función para eliminar una compra
  const eliminarCompra = async (id) => {
    loading.value = true;
    if (confirm('¿Estás seguro de que deseas eliminar esta compra?')) {
      try {
        await router.delete(`/compras/${id}`, {
          onSuccess: () => {
            notyf.success('Compra eliminada exitosamente.');
            router.visit(route('compras.index'));  // Regresar a la lista de compras
          },
          onError: () => notyf.error('Error al eliminar la compra.')
        });
      } catch (error) {
        notyf.error('Ocurrió un error inesperado.');
      } finally {
        loading.value = false;
      }
    }
  };
  </script>

  <style scoped>
  /* Aquí van tus estilos personalizados */
  </style>
