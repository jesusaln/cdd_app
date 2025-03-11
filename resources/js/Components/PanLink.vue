<template>
    <li>
        <Link
            :href="href"
            class="flex items-center p-4 hover:bg-white"
            :class="{ 'bg-white': isActive }"
        >

            <slot />
        </Link>
    </li>
</template>

<script setup>
import { computed } from "vue";
import { Link, usePage } from "@inertiajs/vue3";
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';

const props = defineProps({
    href: {
        type: String,
        required: true,
    },
    icon: {
        type: String,
        default: 'circle', // Ícono predeterminado si no se pasa uno
    },
});

const page = usePage();

// Determina si el enlace es activo
const isActive = computed(() => page.url.startsWith(props.href));

// Formatear el ícono para que sea compatible con FontAwesomeIcon
const formattedIcon = computed(() => {
    return `fa-solid fa-${props.icon}`;
});
</script>
