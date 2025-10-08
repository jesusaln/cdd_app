<template>
    <li>
        <Link
            :href="href"
            class="group relative flex items-center py-3 text-gray-300 transition-all duration-200 ease-in-out hover:bg-gray-700 hover:text-white focus:bg-gray-700 focus:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-gray-800"
            :class="{
                // Clases para el estado activo y colapsado
                'bg-gray-700 text-white border-r-2 border-blue-500': isActive,
                'px-4': !collapsed, // Padding normal cuando está expandido
                'px-4 justify-center': collapsed, // Centrar y mismo padding horizontal cuando está colapsado
                'hover:pl-6': !isActive && !collapsed, // Efecto hover solo cuando no está activo y expandido
            }"
            :aria-current="isActive ? 'page' : undefined"
            @mouseenter="showTooltip($event)"
            @mouseleave="hideTooltip"
        >
            <FontAwesomeIcon
                :icon="iconObject"
                class="flex-shrink-0 transition-transform duration-200"
                :class="{
                    'mr-3 h-5 w-5 group-hover:scale-110': !collapsed, // Espacio y tamaño normal para el icono cuando está expandido
                    'h-6 w-6': collapsed, // Ícono ligeramente más grande cuando está colapsado para mejor visibilidad
                    'text-blue-400': isActive,
                }"
            />
            <span
                v-if="!collapsed"
                class="font-medium truncate transition-opacity duration-300 ease-in-out"
            >
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
    // Nueva prop para saber si el sidebar está colapsado
    collapsed: {
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
    // Si la URL actual es solo '/', solo el NavLink con href='/' será activo.
    if (props.href === '/') {
        return currentUrl === '/';
    }

    // Para otras rutas, verificar si la URL actual comienza con el href del enlace.
    // Pero evitar activar múltiples elementos para rutas similares
    if (props.href === '/') {
        return currentUrl === '/';
    }

    // Verificar coincidencia exacta o que la URL comience con el href seguido de '/' o fin de string
    return currentUrl === props.href ||
           currentUrl.startsWith(props.href + '/') ||
           (currentUrl + '/').startsWith(props.href + '/');
});

// Manejo inteligente de iconos de FontAwesome
const iconObject = computed(() => {
    // Si ya es un objeto o array, devolverlo tal como está
    if (typeof props.icon === 'object') {
        return props.icon;
    }

    // Si es un string, procesarlo inteligentemente
    if (typeof props.icon === 'string') {
        // Si ya tiene el prefijo 'fa-', usarlo directamente
        if (props.icon.startsWith('fa-')) {
            // Eliminar 'fa-' si está presente y el NavLink lo gestionará con el prefijo 'fas'
            // Esto es para que FontAwesomeIcon pueda manejar ['fas', 'icon-name']
            const iconName = props.icon.replace('fa-', '');
            return ['fas', iconName];
        }

        // Si es solo el nombre del icono, agregar el prefijo 'fas' (solid) por defecto
        return ['fas', props.icon];
    }

    // Fallback: icono de círculo sólido por defecto si el formato es desconocido
    return ['fas', 'circle'];
});

// Emitir eventos para el tooltip
const emit = defineEmits(['show-tooltip', 'hide-tooltip']);

const showTooltip = (event) => {
    if (props.collapsed) {
        // Obtenemos el texto del slot, que es el contenido del NavLink
        const slotContent = event.target.querySelector('span') ? event.target.querySelector('span').textContent : '';
        emit('show-tooltip', event, slotContent.trim());
    }
};

const hideTooltip = () => {
    if (props.collapsed) {
        emit('hide-tooltip');
    }
};
</script>

<style scoped>
/* Animación suave para el indicador de estado activo */
.border-r-2 {
    transition: border-color 0.2s ease-in-out;
}

/* Ajuste para el efecto de hover "hover:pl-6" */
/* Cuando el sidebar está expandido y el enlace no está activo, queremos el efecto de desplazamiento. */
/* Esto se maneja con la clase 'hover:pl-6' en Tailwind. */
/* Si necesitas más control o un efecto diferente al hover por defecto, puedes añadirlo aquí. */

/* Mejora la accesibilidad con focus visible */
.group:focus-visible {
    outline: 2px solid #3b82f6; /* color-blue-500 */
    outline-offset: 2px;
}
</style>
