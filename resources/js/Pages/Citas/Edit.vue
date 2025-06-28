<template>
    <Head title="Editar Cita" />
    <div>
        <h1 class="text-2xl font-semibold mb-6">Editar Cita</h1>

        <form @submit.prevent="submit">
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="cliente_id">Cliente</label>
                <select v-model="form.cliente_id" id="cliente_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" :class="{ 'border-red-500': errors.cliente_id }" required>
                    <option v-for="cliente in clientes" :key="cliente.id" :value="cliente.id">{{ cliente.nombre_razon_social }}</option>
                </select>
                <p v-if="errors.cliente_id" class="text-red-500 text-xs italic mt-1">{{ errors.cliente_id }}</p>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="tipo_servicio">Tipo de Servicio</label>
                <select v-model="form.tipo_servicio" id="tipo_servicio" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" :class="{ 'border-red-500': errors.tipo_servicio }" required>
                    <option value="instalacion">Instalación</option>
                    <option value="diagnostico">Diagnóstico</option>
                    <option value="reparacion">Reparación</option>
                    <option value="garantia">Garantía</option>
                    <option value="otro_servicio">Otro Servicio</option>
                </select>
                <p v-if="errors.tipo_servicio" class="text-red-500 text-xs italic mt-1">{{ errors.tipo_servicio }}</p>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="fecha_hora">Fecha y Hora</label>
                <input v-model="form.fecha_hora" type="datetime-local" id="fecha_hora" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" :class="{ 'border-red-500': errors.fecha_hora }" required>
                <p v-if="errors.fecha_hora" class="text-red-500 text-xs italic mt-1">{{ errors.fecha_hora }}</p>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="descripcion">Descripción</label>
                <textarea v-model="form.descripcion" id="descripcion" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" :class="{ 'border-red-500': errors.descripcion }"></textarea>
                <p v-if="errors.descripcion" class="text-red-500 text-xs italic mt-1">{{ errors.descripcion }}</p>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="tipo_equipo">Tipo de Equipo</label>
                <select v-model="form.tipo_equipo" id="tipo_equipo" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" :class="{ 'border-red-500': errors.tipo_equipo }" required>
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
                <p v-if="errors.tipo_equipo" class="text-red-500 text-xs italic mt-1">{{ errors.tipo_equipo }}</p>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="marca_equipo">Marca del Equipo</label>
                <input v-model="form.marca_equipo" type="text" id="marca_equipo" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" :class="{ 'border-red-500': errors.marca_equipo }" required>
                <p v-if="errors.marca_equipo" class="text-red-500 text-xs italic mt-1">{{ errors.marca_equipo }}</p>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="modelo_equipo">Modelo del Equipo</label>
                <input v-model="form.modelo_equipo" type="text" id="modelo_equipo" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" :class="{ 'border-red-500': errors.modelo_equipo }" required>
                <p v-if="errors.modelo_equipo" class="text-red-500 text-xs italic mt-1">{{ errors.modelo_equipo }}</p>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="problema_reportado">Problema Reportado</label>
                <textarea v-model="form.problema_reportado" id="problema_reportado" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" :class="{ 'border-red-500': errors.problema_reportado }"></textarea>
                <p v-if="errors.problema_reportado" class="text-red-500 text-xs italic mt-1">{{ errors.problema_reportado }}</p>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="estado">Estado</label>
                <select v-model="form.estado" id="estado" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" :class="{ 'border-red-500': errors.estado }" required>
                    <option value="pendiente">Pendiente</option>
                    <option value="en_proceso">En Proceso</option>
                    <option value="completado">Completado</option>
                    <option value="cancelado">Cancelado</option>
                </select>
                <p v-if="errors.estado" class="text-red-500 text-xs italic mt-1">{{ errors.estado }}</p>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="tecnico_id">Técnico</label>
                <select v-model="form.tecnico_id" id="tecnico_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" :class="{ 'border-red-500': errors.tecnico_id }" required>
                    <option v-for="tecnico in tecnicos" :key="tecnico.id" :value="tecnico.id">
                        {{ tecnico.nombre }} {{ tecnico.apellido }}
                    </option>
                </select>
                <p v-if="errors.tecnico_id" class="text-red-500 text-xs italic mt-1">{{ errors.tecnico_id }}</p>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="evidencias">Evidencias (Opcional)</label>
                <textarea v-model="form.evidencias" id="evidencias" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" :class="{ 'border-red-500': errors.evidencias }"></textarea>
                <p v-if="errors.evidencias" class="text-red-500 text-xs italic mt-1">{{ errors.evidencias }}</p>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="foto_equipo">Foto del Equipo (Opcional)</label>
                <img v-if="form.foto_equipo_url" :src="form.foto_equipo_url" alt="Foto del Equipo" class="mb-2 max-w-xs h-auto object-cover">
                <input type="file" @change="handleFotoEquipoChange" accept="image/*" id="foto_equipo" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" :class="{ 'border-red-500': errors.foto_equipo }">
                <p v-if="errors.foto_equipo" class="text-red-500 text-xs italic mt-1">{{ errors.foto_equipo }}</p>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="foto_hoja_servicio">Foto de la Hoja de Servicio (Opcional)</label>
                <img v-if="form.foto_hoja_servicio_url" :src="form.foto_hoja_servicio_url" alt="Foto de la Hoja de Servicio" class="mb-2 max-w-xs h-auto object-cover">
                <input type="file" @change="handleFotoHojaServicioChange" accept="image/*" id="foto_hoja_servicio" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" :class="{ 'border-red-500': errors.foto_hoja_servicio }">
                <p v-if="errors.foto_hoja_servicio" class="text-red-500 text-xs italic mt-1">{{ errors.foto_hoja_servicio }}</p>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="foto_identificacion">Foto de Identificación del Cliente (Opcional)</label>
                <img v-if="form.foto_identificacion_url" :src="form.foto_identificacion_url" alt="Foto de Identificación del Cliente" class="mb-2 max-w-xs h-auto object-cover">
                <input type="file" @change="handleFotoIdentificacionChange" accept="image/*" id="foto_identificacion" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" :class="{ 'border-red-500': errors.foto_identificacion }">
                <p v-if="errors.foto_identificacion" class="text-red-500 text-xs italic mt-1">{{ errors.foto_identificacion }}</p>
            </div>
            <div class="flex items-center justify-between">
                <button type="submit" :disabled="isLoading" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition duration-300">
                    {{ isLoading ? 'Actualizando...' : 'Actualizar Cita' }}
                </button>
            </div>
        </form>
    </div>
</template>

<script setup>
import { Head, router } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';
import { Notyf } from 'notyf';
import 'notyf/notyf.min.css';
import AppLayout from '@/Layouts/AppLayout.vue';

defineOptions({ layout: AppLayout });

const props = defineProps({
    cita: Object,
    tecnicos: Array,
    clientes: Array,
    errors: Object,
});

const notyf = new Notyf({
    duration: 3000,
    position: { x: 'right', y: 'top' },
    types: [
        { type: 'success', background: '#4caf50', icon: { className: 'fas fa-check-circle', tagName: 'i', color: '#fff' } },
        { type: 'error', background: '#f44336', icon: { className: 'fas fa-times-circle', tagName: 'i', color: '#fff' } },
    ],
});

const isLoading = ref(false);

// --- New Helper Function ---
const formatDateTimeLocal = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);

    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0'); // Month is 0-indexed
    const day = String(date.getDate()).padStart(2, '0');
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');

    return `${year}-${month}-${day}T${hours}:${minutes}`;
};
// --- End New Helper Function ---


const form = reactive({
    tecnico_id: props.cita.tecnico_id,
    cliente_id: props.cita.cliente_id,
    tipo_servicio: props.cita.tipo_servicio,
    // Apply the new helper function here
    fecha_hora: formatDateTimeLocal(props.cita.fecha_hora),
    descripcion: props.cita.descripcion,
    tipo_equipo: props.cita.tipo_equipo,
    marca_equipo: props.cita.marca_equipo,
    modelo_equipo: props.cita.modelo_equipo,
    problema_reportado: props.cita.problema_reportado,
    estado: props.cita.estado,
    evidencias: props.cita.evidencias,
    foto_equipo: null,
    foto_hoja_servicio: null,
    foto_identificacion: null,
    foto_equipo_url: props.cita.foto_equipo ? `/storage/${props.cita.foto_equipo}` : null,
    foto_hoja_servicio_url: props.cita.foto_hoja_servicio ? `/storage/${props.cita.foto_hoja_servicio}` : null,
    foto_identificacion_url: props.cita.foto_identificacion ? `/storage/${props.cita.foto_identificacion}` : null,
});

const handleFotoEquipoChange = (event) => {
    form.foto_equipo = event.target.files[0];
    form.foto_equipo_url = form.foto_equipo ? URL.createObjectURL(form.foto_equipo) : form.foto_equipo_url;
};

const handleFotoHojaServicioChange = (event) => {
    form.foto_hoja_servicio = event.target.files[0];
    form.foto_hoja_servicio_url = form.foto_hoja_servicio ? URL.createObjectURL(form.foto_hoja_servicio) : form.foto_hoja_servicio_url;
};

const handleFotoIdentificacionChange = (event) => {
    form.foto_identificacion = event.target.files[0];
    form.foto_identificacion_url = form.foto_identificacion ? URL.createObjectURL(form.foto_identificacion) : form.foto_identificacion_url;
};

const submit = async () => {
    isLoading.value = true;
    const formData = new FormData();

    formData.append('tecnico_id', form.tecnico_id);
    formData.append('cliente_id', form.cliente_id);
    formData.append('tipo_servicio', form.tipo_servicio);
    formData.append('fecha_hora', form.fecha_hora);
    formData.append('descripcion', form.descripcion || '');
    formData.append('tipo_equipo', form.tipo_equipo);
    formData.append('marca_equipo', form.marca_equipo);
    formData.append('modelo_equipo', form.modelo_equipo);
    formData.append('problema_reportado', form.problema_reportado || '');
    formData.append('estado', form.estado);
    formData.append('evidencias', form.evidencias || '');

    if (form.foto_equipo) formData.append('foto_equipo', form.foto_equipo);
    if (form.foto_hoja_servicio) formData.append('foto_hoja_servicio', form.foto_hoja_servicio);
    if (form.foto_identificacion) formData.append('foto_identificacion', form.foto_identificacion);

    formData.append('_method', 'PUT'); // Important for PUT requests with FormData in Laravel

    try {
        await router.post(route('citas.update', props.cita.id), formData, {
            forceFormData: true,
            onSuccess: () => {
                notyf.success('La cita ha sido actualizada exitosamente.');
            },
            onError: (errors) => {
                console.error('Error al actualizar la cita:', errors);
                notyf.error('Hubo un error al actualizar la cita. Por favor, revise los campos.');
            },
            onFinish: () => {
                isLoading.value = false;
            },
        });
    } catch (error) {
        console.error('Error inesperado:', error);
        notyf.error('Ocurrió un error inesperado al procesar la solicitud.');
        isLoading.value = false;
    }
};
</script>
