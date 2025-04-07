<template>
    <Head title="Editar Cita" />
    <div>
        <h1 class="text-2xl font-semibold mb-6">Editar Cita</h1>

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
                    <option v-for="tecnico in tecnicos" :key="tecnico.id" :value="tecnico.id">
  {{ tecnico.nombre }} {{ tecnico.apellido }}
</option>
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Evidencias (Opcional)</label>
                <textarea v-model="form.evidencias" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"></textarea>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Foto del Equipo (Opcional)</label>
                <img v-if="form.foto_equipo_url" :src="form.foto_equipo_url" alt="Foto del Equipo" class="mb-2">
                <input type="file" @change="handleFotoEquipoChange" accept="image/*" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Foto de la Hoja de Servicio (Opcional)</label>
                <img v-if="form.foto_hoja_servicio_url" :src="form.foto_hoja_servicio_url" alt="Foto de la Hoja de Servicio" class="mb-2">
                <input type="file" @change="handleFotoHojaServicioChange" accept="image/*" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Foto de Identificación del Cliente (Opcional)</label>
                <img v-if="form.foto_identificacion_url" :src="form.foto_identificacion_url" alt="Foto de Identificación del Cliente" class="mb-2">
                <input type="file" @change="handleFotoIdentificacionChange" accept="image/*" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            <div class="flex items-center justify-between">
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition duration-300">
                    Actualizar Cita
                </button>
            </div>
        </form>
    </div>
</template>

<script setup>
import { Head, router } from '@inertiajs/vue3';
import { reactive } from 'vue';
import { Notyf } from 'notyf';
import 'notyf/notyf.min.css';
import AppLayout from '@/Layouts/AppLayout.vue';

defineOptions({ layout: AppLayout });

const props = defineProps({
    cita: Object,
    tecnicos: Array,
    clientes: Array
});

const notyf = new Notyf({
    duration: 3000,
    position: { x: 'right', y: 'top' },
    types: [
        { type: 'success', background: '#4caf50', icon: { className: 'fas fa-check-circle', tagName: 'i', color: '#fff' } },
        { type: 'error', background: '#f44336', icon: { className: 'fas fa-times-circle', tagName: 'i', color: '#fff' } },
    ],
});

const form = reactive({
    tecnico_id: props.cita.tecnico_id,
    cliente_id: props.cita.cliente_id,
    tipo_servicio: props.cita.tipo_servicio,
    fecha_hora: props.cita.fecha_hora,
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

// Manejar cambios en los archivos
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
    const formData = new FormData();

    // Agregar los campos de texto
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

    // Agregar las imágenes solo si se subieron nuevas
    if (form.foto_equipo) formData.append('foto_equipo', form.foto_equipo);
    if (form.foto_hoja_servicio) formData.append('foto_hoja_servicio', form.foto_hoja_servicio);
    if (form.foto_identificacion) formData.append('foto_identificacion', form.foto_identificacion);

    // Indicar que es una actualización parcial
    formData.append('_method', 'PUT');

    try {
        await router.post(route('citas.update', props.cita.id), formData, {
            forceFormData: true, // Forzar el uso de FormData en Inertia
            onSuccess: () => {
                notyf.success('La cita ha sido actualizada exitosamente.');
            },
            onError: (errors) => {
                console.error('Error al actualizar la cita:', errors);
                notyf.error('Hubo un error al actualizar la cita.');
            },
        });
    } catch (error) {
        console.error('Error inesperado:', error);
        notyf.error('Ocurrió un error inesperado.');
    }
};
</script>
