<template>
    <Head title="Editar Servicio" />
    <div class="max-w-4xl mx-auto p-6">
        <!-- Encabezado -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Editar Servicio</h1>
            <p class="text-gray-600">Modifica los datos del servicio seleccionado</p>
        </div>

        <!-- Formulario de edición -->
        <form @submit.prevent="submit" class="space-y-6">
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Columna Izquierda -->
                    <div class="space-y-6">
                        <!-- Nombre -->
                        <div>
                            <label for="nombre" class="block text-sm font-semibold text-gray-700 mb-2">
                                Nombre del Servicio *
                            </label>
                            <input
                                v-model="form.nombre"
                                type="text"
                                id="nombre"
                                placeholder="Ingresa el nombre del servicio"
                                class="input"
                                :class="{ 'border-red-500': form.errors.nombre }"
                                required
                            />
                            <div v-if="form.errors.nombre" class="error-message">
                                {{ form.errors.nombre }}
                            </div>
                        </div>

                        <!-- Código -->
                        <div>
                            <label for="codigo" class="block text-sm font-semibold text-gray-700 mb-2">
                                Código *
                            </label>
                            <input
                                v-model="form.codigo"
                                type="text"
                                id="codigo"
                                placeholder="Código único del servicio"
                                class="input"
                                :class="{ 'border-red-500': form.errors.codigo }"
                                required
                            />
                            <div v-if="form.errors.codigo" class="error-message">
                                {{ form.errors.codigo }}
                            </div>
                        </div>

                        <!-- Categoría -->
                        <div>
                            <label for="categoria_id" class="block text-sm font-semibold text-gray-700 mb-2">
                                Categoría *
                            </label>
                            <select
                                v-model="form.categoria_id"
                                id="categoria_id"
                                class="input"
                                :class="{ 'border-red-500': form.errors.categoria_id }"
                                required
                            >
                                <option value="">Selecciona una categoría</option>
                                <option
                                    v-for="categoria in categorias"
                                    :key="categoria.id"
                                    :value="categoria.id"
                                >
                                    {{ categoria.nombre }}
                                </option>
                            </select>
                            <div v-if="form.errors.categoria_id" class="error-message">
                                {{ form.errors.categoria_id }}
                            </div>
                        </div>

                        <!-- Precio -->
                        <div>
                            <label for="precio" class="block text-sm font-semibold text-gray-700 mb-2">
                                Precio *
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">$</span>
                                <input
                                    v-model="form.precio"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    id="precio"
                                    placeholder="0.00"
                                    class="input pl-8"
                                    :class="{ 'border-red-500': form.errors.precio }"
                                    required
                                />
                            </div>
                            <div v-if="form.errors.precio" class="error-message">
                                {{ form.errors.precio }}
                            </div>
                        </div>
                    </div>

                    <!-- Columna Derecha -->
                    <div class="space-y-6">
                        <!-- Descripción -->
                        <div>
                            <label for="descripcion" class="block text-sm font-semibold text-gray-700 mb-2">
                                Descripción
                            </label>
                            <textarea
                                v-model="form.descripcion"
                                id="descripcion"
                                placeholder="Describe el servicio..."
                                rows="4"
                                class="input resize-vertical"
                                :class="{ 'border-red-500': form.errors.descripcion }"
                            ></textarea>
                            <div v-if="form.errors.descripcion" class="error-message">
                                {{ form.errors.descripcion }}
                            </div>
                        </div>

                        <!-- Duración -->
                        <div>
                            <label for="duracion" class="block text-sm font-semibold text-gray-700 mb-2">
                                Duración (minutos) *
                            </label>
                            <input
                                v-model="form.duracion"
                                type="number"
                                min="1"
                                id="duracion"
                                placeholder="60"
                                class="input"
                                :class="{ 'border-red-500': form.errors.duracion }"
                                required
                            />
                            <div v-if="form.errors.duracion" class="error-message">
                                {{ form.errors.duracion }}
                            </div>
                        </div>

                        <!-- Estado -->
                        <div>
                            <label for="estado" class="block text-sm font-semibold text-gray-700 mb-2">
                                Estado *
                            </label>
                            <select
                                v-model="form.estado"
                                id="estado"
                                class="input"
                                :class="{ 'border-red-500': form.errors.estado }"
                                required
                            >
                                <option value="activo">🟢 Activo</option>
                                <option value="inactivo">🔴 Inactivo</option>
                            </select>
                            <div v-if="form.errors.estado" class="error-message">
                                {{ form.errors.estado }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botones de Acción -->
            <div class="flex justify-between items-center pt-6 border-t border-gray-200">
                <Link
                    :href="route('servicios.index')"
                    class="btn-secondary"
                >
                    ← Cancelar
                </Link>

                <div class="flex space-x-3">
                    <button
                        type="button"
                        @click="resetForm"
                        class="btn-outline"
                        :disabled="form.processing"
                    >
                        Restablecer
                    </button>

                    <button
                        type="submit"
                        class="btn-primary"
                        :disabled="form.processing"
                    >
                        <span v-if="form.processing" class="flex items-center">
                            <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Actualizando...
                        </span>
                        <span v-else>
                            💾 Actualizar Servicio
                        </span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</template>

<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';

// Define el layout del dashboard
defineOptions({ layout: AppLayout });

// Props del componente
const props = defineProps({
    servicio: {
        type: Object,
        required: true
    },
    categorias: {
        type: Array,
        default: () => []
    }
});

// Datos originales para resetear el formulario
const originalData = {
    id: props.servicio?.id || '',
    nombre: props.servicio?.nombre || '',
    descripcion: props.servicio?.descripcion || '',
    codigo: props.servicio?.codigo || '',
    categoria_id: props.servicio?.categoria_id || '',
    precio: props.servicio?.precio || '',
    duracion: props.servicio?.duracion || '',
    estado: props.servicio?.estado || 'activo',
};

// Formulario reactivo
const form = useForm(originalData);

// Función para restablecer el formulario
const resetForm = () => {
    Object.keys(originalData).forEach(key => {
        form[key] = originalData[key];
    });
    form.clearErrors();
};

// Función para validar antes del envío
const validateForm = () => {
    const errors = {};

    if (!form.nombre.trim()) {
        errors.nombre = 'El nombre es requerido';
    }

    if (!form.codigo.trim()) {
        errors.codigo = 'El código es requerido';
    }

    if (!form.categoria_id) {
        errors.categoria_id = 'La categoría es requerida';
    }

    if (!form.precio || form.precio <= 0) {
        errors.precio = 'El precio debe ser mayor a 0';
    }

    if (!form.duracion || form.duracion <= 0) {
        errors.duracion = 'La duración debe ser mayor a 0';
    }

    return Object.keys(errors).length === 0;
};

// Función para enviar el formulario
const submit = () => {
    if (!validateForm()) {
        return;
    }

    form.put(route('servicios.update', props.servicio.id), {
        onSuccess: () => {
            // Mostrar notificación de éxito (puedes usar tu sistema de notificaciones)
            console.log('Servicio actualizado correctamente');
        },
        onError: (errors) => {
            console.error('Error al actualizar el servicio:', errors);
        },
        onFinish: () => {
            console.log('Actualización de servicio completada');
        },
    });
};

// Watcher para formatear el precio
watch(() => form.precio, (newValue) => {
    if (newValue && !isNaN(newValue)) {
        // Formatear a 2 decimales si es necesario
        const formatted = parseFloat(newValue).toFixed(2);
        if (formatted !== newValue.toString()) {
            form.precio = formatted;
        }
    }
});
</script>

<style scoped>
/* Estilos base para inputs */
.input {
    @apply w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm;
    @apply focus:ring-2 focus:ring-blue-500 focus:border-blue-500;
    @apply transition-colors duration-200;
    @apply text-gray-900 placeholder-gray-500;
}

.input:focus {
    outline: none;
}

/* Textarea específico */
.resize-vertical {
    resize: vertical;
    min-height: 100px;
}

/* Botones */
.btn-primary {
    @apply bg-blue-600 hover:bg-blue-700 text-white font-semibold;
    @apply px-6 py-3 rounded-lg shadow-sm;
    @apply transition-colors duration-200;
    @apply disabled:opacity-50 disabled:cursor-not-allowed;
}

.btn-secondary {
    @apply bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold;
    @apply px-6 py-3 rounded-lg shadow-sm;
    @apply transition-colors duration-200;
    @apply no-underline;
}

.btn-outline {
    @apply border border-gray-300 hover:bg-gray-50 text-gray-700 font-semibold;
    @apply px-6 py-3 rounded-lg shadow-sm;
    @apply transition-colors duration-200;
    @apply disabled:opacity-50 disabled:cursor-not-allowed;
}

/* Mensajes de error */
.error-message {
    @apply text-red-600 text-sm mt-1 font-medium;
}

/* Estados de input con error */
.input-error {
    @apply border-red-500 focus:ring-red-500 focus:border-red-500;
}

/* Responsive */
@media (max-width: 768px) {
    .grid {
        grid-template-columns: 1fr;
    }
}
</style>
