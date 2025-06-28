<template>
    <div :class="{'mb-4': type !== 'checkbox', 'flex items-center': type === 'checkbox'}">
        <label v-if="label && type !== 'checkbox'" :for="id" class="block text-sm font-medium text-gray-700 mb-1">
            {{ label }}
            <span v-if="required" class="text-red-500">*</span>
        </label>

        <select
            v-if="type === 'select'"
            :id="id"
            :value="modelValue"
            @change="$emit('update:modelValue', $event.target.value)"
            class="mt-1 block w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition duration-200 ease-in-out"
            :class="[error ? 'border-red-300' : 'border-gray-300']"
            :required="required"
            :disabled="disabled"
        >
            <option v-for="option in options" :key="option.value" :value="option.value" :disabled="option.disabled">
                {{ option.text }}
            </option>
        </select>

        <textarea
            v-else-if="type === 'textarea'"
            :id="id"
            :value="modelValue"
            @input="$emit('update:modelValue', $event.target.value)"
            :placeholder="placeholder"
            :rows="rows"
            class="mt-1 block w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition duration-200 ease-in-out"
            :class="[error ? 'border-red-300' : 'border-gray-300']"
            :required="required"
            :disabled="disabled"
        ></textarea>

        <input
            v-else
            :id="id"
            :type="type"
            :value="modelValue"
            @input="$emit('update:modelValue', $event.target.value)"
            :placeholder="placeholder"
            class="mt-1 block w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition duration-200 ease-in-out"
            :class="[error ? 'border-red-300' : 'border-gray-300']"
            :required="required"
            :disabled="disabled"
            :min="min"
            :max="max"
            :step="step"
            :list="datalist ? `${id}-datalist` : null"
        />

        <div v-if="datalist && datalist.length > 0">
            <datalist :id="`${id}-datalist`">
                <option v-for="item in datalist" :key="item" :value="item"></option>
            </datalist>
        </div>

        <p v-if="error" class="mt-1 text-sm text-red-600">{{ error }}</p>
    </div>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue';

// Define las props que este componente puede recibir.
defineProps({
    // 'modelValue' es la prop utilizada con v-model. Puede ser String, Number o Boolean.
    modelValue: [String, Number, Boolean],
    // 'label' es la etiqueta de texto que se muestra junto al campo.
    label: String,
    // 'id' es un identificador único para el campo. Es requerido.
    id: {
        type: String,
        required: true,
    },
    // 'type' define el tipo de campo de formulario (ej. 'text', 'select', 'textarea').
    type: {
        type: String,
        default: 'text', // Valor por defecto si no se especifica.
    },
    // 'options' es un array de objetos para campos de tipo 'select'.
    options: {
        type: Array,
        default: () => [], // Función para devolver un array vacío por defecto.
    },
    // 'error' es una cadena de texto para mostrar un mensaje de error.
    error: String,
    // 'placeholder' es el texto que se muestra dentro del campo cuando está vacío.
    placeholder: String,
    // 'required' indica si el campo es obligatorio.
    required: {
        type: Boolean,
        default: false,
    },
    // 'disabled' indica si el campo está deshabilitado.
    disabled: {
        type: Boolean,
        default: false,
    },
    // 'rows' define el número de filas visibles para un <textarea>.
    // ¡CORRECCIÓN IMPORTANTE AQUÍ!: Aunque aquí se define como Number (que es correcto),
    // la advertencia se produce cuando se pasa un STRING desde el componente padre.
    // Asegúrate de usar ':rows="3"' en lugar de 'rows="3"' en el componente donde lo uses.
    rows: {
        type: Number,
        default: 3, // Valor por defecto si no se especifica.
    },
    // 'min' y 'max' para campos numéricos, de fecha, etc.
    min: [String, Number],
    max: [String, Number],
    // 'step' para campos numéricos.
    step: [String, Number],
    // 'datalist' es un array de opciones para un <datalist> (sugerencias para inputs de texto).
    datalist: {
        type: Array,
        default: () => [],
    }
});

// Define los eventos que este componente puede emitir.
// En este caso, solo 'update:modelValue' para el v-model bidireccional.
defineEmits(['update:modelValue']);
</script>
