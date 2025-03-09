<template>
    <Head title="Crear Herramienta" />
    <div>
      <h1 class="text-2xl font-semibold mb-4">Crear Herramienta</h1>
      <!-- Formulario de creación de herramientas -->
      <form @submit.prevent="submit">
        <div v-if="form.errors.nombre" class="text-red-500">{{ form.errors.nombre }}</div>
        <div class="space-y-4">
          <!-- Nombre -->
          <div>
            <label for="nombre" class="block text-sm font-medium text-gray-700">Nombre</label>
            <input
              v-model="form.nombre"
              type="text"
              id="nombre"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
              @blur="convertirAMayusculas('nombre')"
              required
            />
            <p v-if="form.errors.nombre" class="text-red-500 text-sm">{{ form.errors.nombre }}</p>
          </div>

          <!-- Número de Serie -->
          <div>
            <label for="numero_serie" class="block text-sm font-medium text-gray-700">Número de Serie</label>
            <input
              v-model="form.numero_serie"
              type="text"
              id="numero_serie"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
              @blur="convertirAMayusculas('numero_serie')"
              required
            />
            <p v-if="form.errors.numero_serie" class="text-red-500 text-sm">{{ form.errors.numero_serie }}</p>
          </div>

          <!-- Foto -->
          <div>
            <label for="foto" class="block text-sm font-medium text-gray-700">Foto</label>
            <input
              type="file"
              @change="handleFileChange"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
            />
            <p v-if="form.errors.foto" class="text-red-500 text-sm">{{ form.errors.foto }}</p>
          </div>

          <!-- Técnico Asignado -->
          <div>
            <label for="tecnico_id" class="block text-sm font-medium text-gray-700">Técnico Asignado</label>
            <select
              v-model="form.tecnico_id"
              id="tecnico_id"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
            >
              <option value="" disabled>Selecciona un técnico</option>
              <option v-for="tecnico in tecnicos" :key="tecnico.id" :value="tecnico.id">
                {{ tecnico.nombre }} {{ tecnico.apellido }}
              </option>
            </select>
            <p v-if="form.errors.tecnico_id" class="text-red-500 text-sm">{{ form.errors.tecnico_id }}</p>
          </div>
        </div>
        <div class="mt-6">
          <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">
            Guardar Herramienta
          </button>
        </div>
      </form>
    </div>
  </template>

  <script setup>
  import { ref } from 'vue';
  import { Head, useForm } from '@inertiajs/vue3';
  import AppLayout from '@/Layouts/AppLayout.vue';


// Define el layout del dashboard
defineOptions({ layout: AppLayout });

  // Props para recibir la lista de técnicos
  const props = defineProps({
    tecnicos: Array,
  });

  // Formulario para crear una herramienta
  const form = useForm({
    nombre: '',
    numero_serie: '',
    foto: null,
    tecnico_id: '',
  });

  // Función para enviar el formulario
  const submit = () => {
    form.post(route('herramientas.store'), {
      onSuccess: () => {
        form.reset(); // Limpia el formulario después de guardar
      },
    });
  };

  // Método para convertir a mayúsculas
  const convertirAMayusculas = (campo) => {
    if (form[campo]) {
      form[campo] = form[campo].toUpperCase();
    }
  };

  // Manejar el cambio de archivo de foto
  const handleFileChange = (event) => {
    form.foto = event.target.files[0];
  };
  </script>

  <style scoped>
  /* Estilos adicionales si son necesarios */
  </style>
