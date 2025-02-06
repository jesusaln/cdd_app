<template>
    <li>
        <Link
            :href="href"
            class="flex items-center p-4 hover:bg-gray-700"
            :class="{ 'bg-gray-700': isActive }"
        >
            <font-awesome-icon :icon="icon" class="mr-2" />
            <slot />
        </Link>
    </li>
</template>

<script setup>
import { computed } from "vue";
import { Link, usePage } from "@inertiajs/vue3";

const props = defineProps({
    href: String,
    icon: String,
});

const page = usePage();

// Determina si el enlace es activo
const isActive = computed(() => page.url.startsWith(props.href));
</script>
