<template>
    <Head title="Crear Cita" />
    <div>
        <h1 class="text-2xl font-semibold mb-6">Crear Cita</h1>

        <form @submit.prevent="submit">
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Cliente</label>
                <select v-model="form.cliente_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                    <option v-for="cliente in clientes" :key="cliente.id" :value="cliente.id">{{ cliente.nombre_razon_social }}</option>
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Tipo de Servicio</label>
                <select v-model="form.tipo_servicio" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                    <option value="instalacion">Instalación</option>
                    <option value="diagnostico">Diagnóstico</option>
                    <option value="reparacion">Reparación</option>
                    <option value="garantia">Garantía</option>
                    <option value="otro_servicio">Otro Servicio</option>
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Fecha y Hora</label>
                <input v-model="form.fecha_hora" type="datetime-local" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Descripción</label>
                <textarea v-model="form.descripcion" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"></textarea>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Tipo de Equipo</label>
                <select v-model="form.tipo_equipo" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                    <option value="minisplit">Minisplit</option>
                    <option value="boiler">Boiler</option>
                    <option value="refrigerador">Refrigerador</option>
                    <option value="lavadora">Lavadora</option>
                    <option value="secadora">Secadora</option>
                    <option value="estufa">Estufa</option>
                    <option value="campana">Campana</option>
                    <option value="horno_de_microondas">Horno de Microondas</option>
                    <option value="licuadora">Licuadora</option>
                    <option value="otro_equipo">Otro Equipo</option>
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Marca del Equipo</label>
                <input v-model="form.marca_equipo" type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Modelo del Equipo</label>
                <input v-model="form.modelo_equipo" type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Problema Reportado</label>
                <textarea v-model="form.problema_reportado" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"></textarea>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Estado</label>
                <select v-model="form.estado" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                    <option value="pendiente">Pendiente</option>
                    <option value="en_proceso">En Proceso</option>
                    <option value="completado">Completado</option>
                    <option value="cancelado">Cancelado</option>
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Técnico</label>
                <select v-model="form.tecnico_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                    <option v-for="tecnico in tecnicos" :key="tecnico.id" :value="tecnico.id">{{ tecnico.nombre }}</option>
                </select>
            </div>
            <div class="flex items-center justify-between">
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition duration-300">
                    Crear Cita
                </button>
            </div>
        </form>
    </div>
</template>

<script setup>
import { Head, router } from '@inertiajs/vue3';
import { reactive, onMounted } from 'vue';
import { Notyf } from 'notyf';
import 'notyf/notyf.min.css';
import Dashboard from '@/Pages/Dashboard.vue';

defineOptions({ layout: Dashboard });

defineProps({ tecnicos: Array, clientes: Array });

const notyf = new Notyf({
    duration: 3000,
    position: { x: 'right', y: 'top' },
    types: [
        { type: 'success', background: '#4caf50', icon: { className: 'fas fa-check-circle', tagName: 'i', color: '#fff' } },
        { type: 'error', background: '#f44336', icon: { className: 'fas fa-times-circle', tagName: 'i', color: '#fff' } },
    ],
});

const form = reactive({
    tecnico_id: '',
    cliente_id: '',
    tipo_servicio: 'instalacion', // Valor predeterminado del tipo de servicio
    fecha_hora: '',
    descripcion: '',
    tipo_equipo: 'minisplit', // Valor predeterminado del tipo de equipo
    marca_equipo: '',
    modelo_equipo: '',
    problema_reportado: '',
    estado: 'pendiente', // Valor predeterminado del estado
});

onMounted(() => {
    const now = new Date();
    const offset = now.getTimezoneOffset();
    now.setMinutes(now.getMinutes() + offset);
    form.fecha_hora = now.toISOString().slice(0, 16);
});

const submit = async () => {
    try {
        await router.post(route('citas.store'), form, {
            onSuccess: () => {
                notyf.success('La cita ha sido creada exitosamente.');
                form.tecnico_id = '';
                form.cliente_id = '';
                form.tipo_servicio = 'instalacion';
                form.fecha_hora = '';
                form.descripcion = '';
                form.tipo_equipo = 'minisplit';
                form.marca_equipo = '';
                form.modelo_equipo = '';
                form.problema_reportado = '';
                form.estado = 'pendiente';
            },
            onError: (error) => {
                console.error('Error al crear la cita:', error);
                notyf.error('Hubo un error al crear la cita.');
            },
        });
    } catch (error) {
        console.error('Error inesperado:', error);
        notyf.error('Ocurrió un error inesperado.');
    }
};
</script>
