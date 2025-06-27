<template>
    <li>
        <Link
            :href="href"
            class="group flex items-center px-4 py-3 text-gray-300 transition-all duration-200 ease-in-out hover:bg-gray-700 hover:text-white focus:bg-gray-700 focus:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-gray-800"
            :class="{
                'bg-gray-700 text-white border-r-2 border-blue-500': isActive,
                'hover:pl-6': !isActive
            }"
            :aria-current="isActive ? 'page' : undefined"
        >
            <FontAwesomeIcon
                :icon="iconObject"
                class="mr-3 h-5 w-5 flex-shrink-0 transition-transform duration-200 group-hover:scale-110"
                :class="{ 'text-blue-400': isActive }"
            />
            <span class="font-medium truncate">
                <slot />
            </span>
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
        type: [String, Array, Object],
        default: 'circle',
    },
    exact: {
        type: Boolean,
        default: false,
    },
});

const page = usePage();

// Lógica mejorada para determinar si el enlace está activo
const isActive = computed(() => {
    const currentUrl = page.url;

    if (props.exact) {
        return currentUrl === props.href;
    }

    // Para rutas raíz, usar coincidencia exacta
    if (props.href === '/') {
        return currentUrl === '/';
    }

    return currentUrl.startsWith(props.href);
});

// Manejo inteligente de iconos de FontAwesome
const iconObject = computed(() => {
    // Si ya es un objeto o array, devolverlo tal como está
    if (typeof props.icon === 'object') {
        return props.icon;
    }

    // Si es un string, procesarlo inteligentemente
    if (typeof props.icon === 'string') {
        // Si ya tiene el prefijo fa-, usarlo directamente
        if (props.icon.startsWith('fa-')) {
            return props.icon;
        }

        // Si es solo el nombre del icono, agregar el prefijo solid
        return ['fas', props.icon];
    }

    // Fallback
    return ['fas', 'circle'];
});
</script>

<style scoped>
/* Animación suave para el indicador de estado activo */
.border-r-2 {
    transition: border-color 0.2s ease-in-out;
}

/* Efecto de hover mejorado */
.group:hover .fa-icon {
    transform: scale(1.1);
}

/* Mejora la accesibilidad con focus visible */
.group:focus-visible {
    outline: 2px solid #3b82f6;
    outline-offset: 2px;
}
</style>
