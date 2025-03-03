<template>
    <Head title="Crear Usuario" />
    <div class="usuarios-create">
      <h1 class="text-2xl font-semibold mb-6">Crear Usuario</h1>
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
  <label class="block text-gray-700 text-sm font-bold mb-2">Rol:</label>
  <select v-model="form.role" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
    <option value="user">Usuario</option>
    <option value="admin">Administrador</option>
  </select>
  <InputError class="mt-2" :message="form.errors.role" />
</div>

        <div class="mb-4">
          <label class="block text-gray-700 text-sm font-bold mb-2">Contraseña:</label>
          <input v-model="form.password" type="password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="Contraseña">
          <InputError class="mt-2" :message="form.errors.password" />
        </div>
        <div class="mb-4">
          <label class="block text-gray-700 text-sm font-bold mb-2">Confirmar Contraseña:</label>
          <input v-model="form.password_confirmation" type="password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="Confirmar Contraseña">
          <InputError class="mt-2" :message="form.errors.password_confirmation" />
        </div>
        <div class="flex items-center justify-between">
          <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Crear</button>
        </div>
      </form>
    </div>
  </template>

  <script setup>
  import { Head, router, useForm } from '@inertiajs/vue3';
  import InputError from '@/Components/InputError.vue';
  import { Notyf } from 'notyf';
  import 'notyf/notyf.min.css';
  import Dashboard from '@/Pages/Dashboard.vue';

  defineOptions({ layout: Dashboard });

  const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: 'user', // Valor por defecto
  });

  const notyf = new Notyf({ duration: 3000, position: { x: 'right', y: 'top' } });

  const submit = () => {
    form.post(route('usuarios.store'), {
      onSuccess: () => {
        notyf.success('Usuario creado exitosamente.');
        form.reset();
      },
      onError: (errors) => {
        notyf.error('Error al crear el usuario.');
      }
    });
  };
  </script>
