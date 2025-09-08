<template>
  <div :class="{'mb-4': type !== 'checkbox', 'flex items-center': type === 'checkbox'}">
    <label
      v-if="label && type !== 'checkbox'"
      :for="id"
      class="block text-sm font-medium text-gray-700 mb-1"
    >
      {{ label }}
      <span v-if="required" class="text-red-500">*</span>
    </label>

    <!-- SELECT -->
    <select
      v-if="type === 'select'"
      :id="id"
      :value="props.modelValue"
      @change="onChange"
      class="mt-1 block w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition duration-200 ease-in-out"
      :class="[hasError ? 'border-red-300' : 'border-gray-300']"
      :required="required"
      :disabled="disabled"
      :aria-invalid="hasError ? 'true' : 'false'"
      :aria-describedby="hasError ? `${id}-error` : undefined"
    >
      <option
        v-for="option in options"
        :key="option.value"
        :value="option.value"
        :disabled="option.disabled"
      >
        {{ option.text }}
      </option>
    </select>

    <!-- TEXTAREA -->
    <textarea
      v-else-if="type === 'textarea'"
      :id="id"
      :value="props.modelValue"
      @input="onInput"
      :placeholder="placeholder"
      :rows="rows"
      class="mt-1 block w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition duration-200 ease-in-out"
      :class="[hasError ? 'border-red-300' : 'border-gray-300']"
      :required="required"
      :disabled="disabled"
      :aria-invalid="hasError ? 'true' : 'false'"
      :aria-describedby="hasError ? `${id}-error` : undefined"
    ></textarea>

    <!-- INPUT -->
    <input
      v-else
      :id="id"
      :type="type"
      :value="props.modelValue"
      @input="onInput"
      :placeholder="placeholder"
      class="mt-1 block w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition duration-200 ease-in-out"
      :class="[hasError ? 'border-red-300' : 'border-gray-300']"
      :required="required"
      :disabled="disabled"
      :min="min"
      :max="max"
      :step="step"
      :list="datalist ? `${id}-datalist` : null"
      :aria-invalid="hasError ? 'true' : 'false'"
      :aria-describedby="hasError ? `${id}-error` : undefined"
    />

    <!-- DATALIST -->
    <div v-if="datalist && datalist.length > 0">
      <datalist :id="`${id}-datalist`">
        <option v-for="item in datalist" :key="item" :value="item"></option>
      </datalist>
    </div>

    <!-- ERROR -->
    <p v-if="hasError" :id="`${id}-error`" class="mt-1 text-sm text-red-600">
      {{ displayError }}
    </p>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  modelValue: [String, Number, Boolean],
  label: String,
  id: { type: String, required: true },
  type: { type: String, default: 'text' },
  options: { type: Array, default: () => [] },
  error: { type: [String, Array], default: '' },
  placeholder: String,
  required: { type: Boolean, default: false },
  disabled: { type: Boolean, default: false },
  rows: { type: Number, default: 3 },
  min: [String, Number],
  max: [String, Number],
  step: [String, Number],
  datalist: { type: Array, default: () => [] },
});

const emit = defineEmits(['update:modelValue', 'input', 'change', 'clear-error']);

const hasError = computed(() => (Array.isArray(props.error) ? props.error.length > 0 : !!props.error));
const displayError = computed(() => (Array.isArray(props.error) ? (props.error[0] ?? '') : (props.error || '')));

function onInput(e) {
  const v = e.target.value;
  emit('update:modelValue', v); // v-model
  emit('input', v);             // para listeners del padre
  emit('clear-error', props.id); // pedir limpiar error de este campo
}

function onChange(e) {
  const v = e.target.value;
  emit('update:modelValue', v);
  emit('change', v);
  emit('clear-error', props.id);
}
</script>
