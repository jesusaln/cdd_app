<template>
    <Head title="Lista de Herramientas" />
    <div>
      <h1 class="text-2xl font-semibold mb-4">Lista de Herramientas</h1>

      <!-- Botón para crear una nueva herramienta -->
      <button @click="createHerramienta" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 mb-4">
        Crear Herramienta
      </button>

      <!-- Filtro por Técnico -->
      <div class="mb-4">
        <label for="tecnicoFilter" class="block text-sm font-medium text-gray-700">Filtrar por Técnico:</label>
        <select v-model="selectedTecnico" id="tecnicoFilter" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
          <option value="">Todos los técnicos</option>
          <option v-for="tecnico in tecnicos" :key="tecnico.id" :value="tecnico.id">
            {{ tecnico.nombre }} {{ tecnico.apellido }}
          </option>
        </select>
      </div>

      <!-- Tabla de herramientas -->
      <table class="min-w-full bg-white shadow-md rounded-lg overflow-hidden">
        <thead class="bg-gray-50">
          <tr>
            <th class="py-2 px-4 border-b">Nombre</th>
            <th class="py-2 px-4 border-b">Número de Serie</th>
            <th class="py-2 px-4 border-b">Foto</th>
            <th class="py-2 px-4 border-b">Técnico Asignado</th>
            <th class="py-2 px-4 border-b">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="herramienta in filteredHerramientas" :key="herramienta.id" class="hover:bg-gray-100">
            <td class="py-2 px-4 border-b">{{ herramienta.nombre }}</td>
            <td class="py-2 px-4 border-b">{{ herramienta.numero_serie }}</td>
            <td class="py-2 px-4 border-b">
              <img v-if="herramienta.foto" :src="herramienta.foto" alt="Foto de la herramienta" class="w-16 h-16 object-cover">
            </td>
            <td class="py-2 px-4 border-b">{{ herramienta.tecnico ? `${herramienta.tecnico.nombre} ${herramienta.tecnico.apellido}` : 'Sin asignar' }}</td>
            <td class="px-4 py-3 flex space-x-2">
              <!-- Botón Ver -->
              <button @click="openModal(herramienta)" class="bg-green-500 text-white px-4 py-2 rounded-md hover:bg-green-600">
                Ver
              </button>
              <!-- Botón Editar -->
              <Link :href="route('herramientas.edit', herramienta.id)" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">
                Editar
              </Link>
              <!-- Botón Eliminar -->
              <button @click="confirmarEliminacion(herramienta.id)" class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600">
                Eliminar
              </button>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Modal Component -->
      <ModalHerramientas v-if="showModal" :herramienta="selectedHerramienta" @close="closeModal" />
    </div>
  </template>

  <script setup>
  import { ref, computed } from 'vue';
  import { Head, Link, router } from '@inertiajs/vue3';
  import AppLayout from '@/Layouts/AppLayout.vue';
  import ModalHerramientas from '@/Components/Modal/ModalHerramientas.vue'; // Importa el componente del modal

  // Define el layout del dashboard
  defineOptions({ layout: AppLayout });

  // Props para recibir la lista de herramientas y técnicos
  const props = defineProps({
    herramientas: Array, // Recibe la lista de herramientas
    tecnicos: Array, // Recibe la lista de técnicos
  });

  // Estado para el técnico seleccionado
  const selectedTecnico = ref('');

  // Herramientas filtradas
  const filteredHerramientas = computed(() => {
    if (!selectedTecnico.value) {
      return props.herramientas;
    }
    return props.herramientas.filter(herramienta => herramienta.tecnico && herramienta.tecnico.id === selectedTecnico.value);
  });

  // Estado para controlar la visibilidad del modal
  const showModal = ref(false);
  const selectedHerramienta = ref(null);

  // Métodos para manejar las acciones
  const openModal = (herramienta) => {
    selectedHerramienta.value = herramienta;
    showModal.value = true;
  };

  const closeModal = () => {
    showModal.value = false;
    selectedHerramienta.value = null;
  };

  // Método para confirmar la eliminación
  const confirmarEliminacion = (id) => {
    if (confirm('¿Estás seguro de que deseas eliminar esta herramienta?')) {
      // Envía una solicitud DELETE al servidor
      router.delete(route('herramientas.destroy', id), {
        onSuccess: () => {
          // Recarga la página o actualiza la lista de herramientas
          router.reload();
        },
        onError: (error) => {
          console.error('Error al eliminar la herramienta:', error);
        },
      });
    }
  };

  // Método para redirigir a la página de creación de herramientas
  const createHerramienta = () => {
    router.visit('/herramientas/create');
  };
  </script>

  <style scoped>
  /* Estilos adicionales si son necesarios */
  </style>
