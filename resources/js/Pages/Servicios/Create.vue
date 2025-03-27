<template>
    <Head title="Crear Servicio" />
    <div>
        <h1 class="text-2xl font-semibold mb-4">Crear Servicio</h1>
        <form @submit.prevent="submit" class="grid grid-cols-2 gap-4">
            <div class="space-y-4">
                <!-- Nombre -->
                <div>
                    <label for="nombre" class="block text-sm font-medium text-gray-700">Nombre</label>
                    <input v-model="form.nombre" type="text" id="nombre" placeholder="Nombre" class="input" required />
                    <div v-if="form.errors.nombre" class="text-red-500">{{ form.errors.nombre }}</div>
                </div>

                <!-- Descripción -->
                <div>
                    <label for="descripcion" class="block text-sm font-medium text-gray-700">Descripción</label>
                    <textarea v-model="form.descripcion" id="descripcion" placeholder="Descripción" class="input"></textarea>
                </div>

                <!-- Código -->
                <div>
                    <label for="codigo" class="block text-sm font-medium text-gray-700">Código</label>
                    <input v-model="form.codigo" type="text" id="codigo" placeholder="Código" class="input" required />
                    <div v-if="form.errors.codigo" class="text-red-500">{{ form.errors.codigo }}</div>
                </div>

                <!-- Categoría -->
                <div>
                    <label for="categoria_id" class="block text-sm font-medium text-gray-700">Categoría</label>
                    <select v-model="form.categoria_id" id="categoria_id" class="input" required>
                        <option value="">Selecciona una categoría</option>
                        <option v-for="categoria in categorias" :key="categoria.id" :value="categoria.id">
                            {{ categoria.nombre }}
                        </option>
                    </select>
                    <div v-if="form.errors.categoria_id" class="text-red-500">{{ form.errors.categoria_id }}</div>
                </div>

                <!-- Precio -->
                <div>
                    <label for="precio" class="block text-sm font-medium text-gray-700">Precio</label>
                    <input v-model="form.precio" type="number" step="0.01" id="precio" placeholder="Precio" class="input" required />
                    <div v-if="form.errors.precio" class="text-red-500">{{ form.errors.precio }}</div>
                </div>

                <!-- Duración -->
                <div>
                    <label for="duracion" class="block text-sm font-medium text-gray-700">Duración (minutos)</label>
                    <input v-model="form.duracion" type="number" id="duracion" placeholder="Duración" class="input" required />
                    <div v-if="form.errors.duracion" class="text-red-500">{{ form.errors.duracion }}</div>
                </div>
            </div>

            <div class="space-y-4">
                <!-- Estado -->
                <div>
                    <label for="estado" class="block text-sm font-medium text-gray-700">Estado</label>
                    <select v-model="form.estado" id="estado" class="input" required>
                        <option value="activo">Activo</option>
                        <option value="inactivo">Inactivo</option>
                    </select>
                    <div v-if="form.errors.estado" class="text-red-500">{{ form.errors.estado }}</div>
                </div>
            </div>

            <!-- Botón de Envío -->
            <div class="col-span-2 mt-6">
                <button type="submit" class="btn">Guardar Servicio</button>
            </div>
        </form>
    </div>
</template>

<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

// Define el layout del dashboard
defineOptions({ layout: AppLayout });

// Recibir las relaciones desde el backend
const props = defineProps({
    categorias: Array,
});

// Formulario para crear un servicio
const form = useForm({
    nombre: '',
    descripcion: '',
    codigo: '',
    categoria_id: '',
    precio: '',
    duracion: '',
    estado: 'activo',
});

// Enviar formulario
const submit = () => {
    form.post(route('servicios.store'), {
        onSuccess: () => form.reset(),
    });
};
</script>

<style>
.input {
    width: 100%;
    padding: 8px;
    border: 1px solid #ccc;
    border-radius: 4px;
}
.btn {
    background-color: blue;
    color: white;
    padding: 10px;
    border-radius: 4px;
    cursor: pointer;
}
.grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
}
</style>
