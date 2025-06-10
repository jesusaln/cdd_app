<template>
    <div>
        <label :for="id" class="block text-sm font-medium text-gray-700">{{ label }}</label>
        <input
            v-if="type !== 'select'"
            :type="type"
            :id="id"
            :value="modelValue"
            @input="$emit('update:modelValue', $event.target.value)"
            :maxlength="maxlength"
            :readonly="readonly"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
        />
        <select
            v-else
            :id="id"
            :value="modelValue"
            @change="$emit('update:modelValue', $event.target.value)"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
        >
            <option v-for="option in options" :key="option.value" :value="option.value" :disabled="option.disabled">
                {{ option.text }}
            </option>
        </select>
        <p v-if="error" class="text-red-500 text-sm">{{ error }}</p>
    </div>
</template>

<script setup>
defineProps({
    modelValue: [String, Number],
    label: String,
    type: {
        type: String,
        default: 'text'
    },
    id: String,
    maxlength: Number,
    readonly: Boolean,
    error: String,
    options: {
        type: Array,
        default: () => []
    }
});

defineEmits(['update:modelValue']);
</script>
