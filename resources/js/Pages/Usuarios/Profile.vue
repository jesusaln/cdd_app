<template>
    <Head title="Perfil del Usuario" />
    <div class="perfil">
      <h1 class="text-2xl font-semibold mb-6">Perfil del Usuario</h1>

      <div class="mb-4">
        <label class="block text-gray-700 text-sm font-bold mb-2">Nombre:</label>
        <input
          v-model="usuario.name"
          type="text"
          class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
          placeholder="Nombre"
          disabled
        />
      </div>

      <div class="mb-4">
        <label class="block text-gray-700 text-sm font-bold mb-2">Email:</label>
        <input
          v-model="usuario.email"
          type="email"
          class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
          placeholder="Email"
          disabled
        />
      </div>

      <div class="mb-4">
        <label class="block text-gray-700 text-sm font-bold mb-2">Fecha de creación:</label>
        <input
          v-model="usuario.created_at"
          type="text"
          class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
          placeholder="Fecha de creación"
          disabled
        />
      </div>

      <div class="flex items-center justify-between mt-6">
        <button @click="editarPerfil" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
          Editar Perfil
        </button>
      </div>
    </div>
  </template>

  <script setup>
  import { Head } from '@inertiajs/vue3';
  import { defineProps } from 'vue';

  // Recibimos el usuario desde el controlador
  const props = defineProps({
    usuario: Object,
  });

  // Función para redirigir a la página de edición
  const editarPerfil = () => {
    // Redirigir a la vista de edición (aquí puedes cambiar la ruta si es necesario)
    window.location.href = route('usuarios.edit', props.usuario.id);
  };
  </script>

  <style scoped>
  .perfil {
    max-width: 600px;
    margin: 0 auto;
  }
  </style>
