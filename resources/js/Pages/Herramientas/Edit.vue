<template>
    <Head title="Editar Herramienta" />
    <div>
        <h1 class="text-2xl font-semibold mb-4">Editar Herramienta</h1>
        <form @submit.prevent="submit" enctype="multipart/form-data">
            <div class="space-y-4">
                <!-- Nombre -->
                <div>
                    <label for="nombre" class="block text-sm font-medium text-gray-700">Nombre</label>
                    <input
                        v-model="form.nombre"
                        type="text"
                        id="nombre"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
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
                        required
                    />
                    <p v-if="form.errors.numero_serie" class="text-red-500 text-sm">{{ form.errors.numero_serie }}</p>
                </div>

                <!-- Foto Actual -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Foto Actual</label>
                    <div v-if="herramienta.foto_url && !form.remove_foto" class="mt-2 flex items-center space-x-4">
                        <img :src="herramienta.foto_url" alt="Foto de la herramienta" class="w-40 h-40 object-cover rounded-md border" />
                        <button
                            type="button"
                            @click="removePhoto"
                            class="text-red-500 hover:text-red-700 text-sm"
                        >
                            Eliminar Foto
                        </button>
                    </div>
                    <p v-else class="text-gray-500 text-sm">No hay foto disponible.</p>
                </div>

                <!-- Nueva Foto -->
                <div>
                    <label for="foto" class="block text-sm font-medium text-gray-700">Nueva Foto (opcional)</label>
                    <input
                        type="file"
                        id="foto"
                        @change="handleFileChange"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                        accept="image/jpeg,image/png,image/jpg"
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
                        <option value="">Sin técnico asignado</option>
                        <option v-for="tecnico in tecnicos" :key="tecnico.id" :value="tecnico.id">
                            {{ tecnico.nombre }} {{ tecnico.apellido }}
                        </option>
                    </select>
                    <p v-if="form.errors.tecnico_id" class="text-red-500 text-sm">{{ form.errors.tecnico_id }}</p>
                </div>
            </div>

            <div class="mt-6 space-x-2">
                <button
                    type="submit"
                    class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600"
                    :disabled="form.processing"
                >
                    Actualizar Herramienta
                </button>
                <Link
                    :href="route('herramientas.index')"
                    class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600"
                >
                    Cancelar
                </Link>
            </div>
        </form>
    </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import Dashboard from '@/Pages/Dashboard.vue';

defineOptions({ layout: Dashboard });

const props = defineProps({
    herramienta: {
        type: Object,
        required: true
    },
    tecnicos: {
        type: Array,
        required: true
    },
});

const form = useForm({
    nombre: props.herramienta?.nombre || '',
    numero_serie: props.herramienta?.numero_serie || '',
    tecnico_id: props.herramienta?.tecnico_id || '',
    foto: null,
    remove_foto: false,
});

const herramienta = ref(props.herramienta);

const submit = () => {
    form.put(route('herramientas.update', props.herramienta.id), {
        onSuccess: () => {
            if (!form.hasErrors) {
                form.reset('foto', 'remove_foto');
            }
        }
    });
};

const handleFileChange = (event) => {
    form.foto = event.target.files[0];
};

const removePhoto = () => {
    form.remove_foto = true;
};

watch(() => form.remove_foto, (newValue) => {
    if (newValue) {
        form.foto = null;
    }
});

</script>
