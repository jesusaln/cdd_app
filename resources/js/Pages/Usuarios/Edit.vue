<template>
    <Head title="Editar Usuario" />
    <div class="usuarios-edit">
      <h1 class="text-2xl font-semibold mb-6">Editar Usuario</h1>
      <form @submit.prevent="submit">
        <div class="mb-4">
          <label class="block text-gray-700 text-sm font-bold mb-2">Nombre:</label>
          <input v-model="form.name" type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="Nombre">
          <InputError class="mt-2" :message="form.errors.name" />
        </div>
        <div class="mb-4">
          <label class="block text-gray-700 text-sm font-bold mb-2">Email:</label>
          <input v-model="form.email" type="email" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="Email">
          <InputError class="mt-2" :message="form.errors.email" />
        </div>
        <div class="mb-4">
          <label class="block text-gray-700 text-sm font-bold mb-2">Contraseña:</label>
          <input v-model="form.password" type="password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="Contraseña (dejar en blanco para no cambiar)">
          <InputError class="mt-2" :message="form.errors.password" />
        </div>
        <div class="mb-4">
          <label class="block text-gray-700 text-sm font-bold mb-2">Confirmar Contraseña:</label>
          <input v-model="form.password_confirmation" type="password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="Confirmar Contraseña">
          <InputError class="mt-2" :message="form.errors.password_confirmation" />
        </div>
        <div class="mb-4">
          <label class="block text-gray-700 text-sm font-bold mb-2">Rol:</label>
          <select v-model="form.role" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            <option value="user">Usuario</option>
            <option value="admin">Administrador</option>
          </select>
          <InputError class="mt-2" :message="form.errors.role" />
        </div>
        <div class="flex items-center justify-between">
          <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Guardar</button>
        </div>
      </form>
    </div>
  </template>

  <script setup>
  import { Head, useForm } from '@inertiajs/vue3';
  import InputError from '@/Components/InputError.vue';
  import { Notyf } from 'notyf';
  import 'notyf/notyf.min.css';
 // import Dashboard from '@/Pages/Dashboard.vue';
  import { defineProps } from 'vue';

  import AppLayout from '@/Layouts/AppLayout.vue';


// Define el layout del dashboard
defineOptions({ layout: AppLayout });

  const props = defineProps({
    usuario: Object,
  });

  const form = useForm({
    name: props.usuario.name,
    email: props.usuario.email,
    password: '', // Contraseña inicialmente vacía
    password_confirmation: '', // Confirmación de contraseña inicialmente vacía
    role: (props.usuario.roles || []).length ? props.usuario.roles[0].name : 'user', // Asignar el primer rol o 'user' por defecto
});

  const notyf = new Notyf({ duration: 3000, position: { x: 'right', y: 'top' } });

  const submit = () => {
    // Verificar si las contraseñas coinciden
    if (form.password && form.password !== form.password_confirmation) {
      notyf.error('Las contraseñas no coinciden.');
      return;
    }

    // Si la contraseña está vacía, establecemos el campo password a null
    if (!form.password) {
      form.password = null;
    }

    // Enviar la solicitud PUT para actualizar el usuario
    form.put(route('usuarios.update', props.usuario.id), {
      onSuccess: () => {
        notyf.success('Usuario actualizado exitosamente.');
      },
      onError: (errors) => {
        if (errors.email?.includes('El correo electrónico ya ha sido tomado.')) {
          notyf.error('El correo electrónico ya ha sido tomado.');
        } else {
          notyf.error('Error al actualizar el usuario.');
        }
      }
    });
  };
  </script>
