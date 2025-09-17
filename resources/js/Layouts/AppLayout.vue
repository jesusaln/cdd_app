<template>
    <div class="flex flex-col h-screen bg-gradient-to-br from-gray-50 to-gray-100">
        <nav class="bg-gradient-to-r from-gray-900 to-gray-800 shadow-lg border-b border-gray-700">
            <div class="px-4 py-3">
                <div class="flex justify-between items-center">
                    <div>
                        </div>

                    <div v-if="usuario" class="hidden md:flex items-center">
                        <span class="text-gray-200 text-sm font-medium">
                            {{ getGreeting() }},
                            <span class="text-blue-300 font-semibold">{{ usuario.name }}</span>
                        </span>
                    </div>

                    <div class="flex items-center space-x-3">
                        <!-- Componente de Notificaciones Mejorado -->
                        <NotificationBell
                            :auto-refresh="true"
                            :refresh-interval="30000"
                            @notification-clicked="handleNotificationClick"
                        />

                        <div class="relative" ref="profileContainer">
                            <button
                                @click="toggleProfileDropdown"
                                class="flex items-center space-x-2 p-1 rounded-lg hover:bg-gray-700 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                :aria-expanded="isProfileDropdownOpen.toString()"
                                aria-controls="profile-dropdown"
                            >
                                <img
                                    :src="usuario.profile_photo_url"
                                    alt="Foto de perfil"
                                    class="h-8 w-8 rounded-full border-2 border-gray-600 object-cover"
                                />
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-4 w-4 text-gray-300 transition-transform duration-200"
                                    :class="{ 'rotate-180': isProfileDropdownOpen }"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M19 9l-7 7-7-7"
                                    ></path>
                                </svg>
                            </button>

                            <Transition
                                enter-active-class="transition ease-out duration-200"
                                enter-from-class="transform opacity-0 scale-95"
                                enter-to-class="transform opacity-100 scale-100"
                                leave-active-class="transition ease-in duration-150"
                                leave-from-class="transform opacity-100 scale-100"
                                leave-to-class="transform opacity-0 scale-95"
                            >
                                <div
                                    v-if="isProfileDropdownOpen"
                                    id="profile-dropdown"
                                    role="menu"
                                    aria-orientation="vertical"
                                    aria-labelledby="profile-button"
                                    class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-xl z-50 overflow-hidden border border-gray-200"
                                >
                                    <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
                                        <p class="text-sm font-medium text-gray-900">{{ usuario.name }}</p>
                                        <p class="text-xs text-gray-500">{{ usuario.email }}</p>
                                    </div>

                                    <div class="py-1">
                                        <Link
                                            :href="route('profile.show')"
                                            class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-150"
                                            role="menuitem"
                                        >
                                            <svg class="mr-3 h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                            Mi Perfil
                                        </Link>
                                        <Link
                                            :href="route('empresas.index')"
                                            class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-150"
                                            role="menuitem"
                                        >
                                            <svg class="mr-3 h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                            Configuración
                                        </Link>
                                    </div>

                                    <div class="border-t border-gray-200">
                                        <button
                                            @click="logout"
                                            class="flex items-center w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors duration-150"
                                            role="menuitem"
                                        >
                                            <svg class="mr-3 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                            </svg>
                                            Cerrar Sesión
                                        </button>
                                    </div>
                                </div>
                            </Transition>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <div class="flex flex-1 relative overflow-hidden">
            <Sidebar :isSidebarCollapsed="isSidebarCollapsed" @toggleSidebar="toggleSidebar" />

            <main
                :class="{'ml-64': !isSidebarCollapsed, 'ml-20': isSidebarCollapsed}"
                class="flex-1 overflow-y-auto bg-gray-50 transition-all duration-300 ease-in-out"
            >
                <div class="p-6">
                    <slot />
                </div>
            </main>
        </div>

        <div v-if="isLoading" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" aria-modal="true" role="dialog">
            <div class="bg-white rounded-lg p-6 flex items-center space-x-3 shadow-lg">
                <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600" role="status" aria-label="Cargando"></div>
                <span class="text-gray-700">Cargando...</span>
            </div>
        </div>
    </div>
</template>

<script setup>
import Sidebar from '@/Components/Sidebar.vue';
import NotificationBell from '@/Components/Notifications/NotificationBell.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { library } from '@fortawesome/fontawesome-svg-core';
import {
    faCalendar, faWrench, faTools, faCarAlt, faChartBar, faCartShopping,
    faCircle, faHome, faUsers, faBox, faTags, faTrademark, faTruck,
    faWarehouse, faFileAlt, faTruckLoading, faDollarSign, faUser,
} from '@fortawesome/free-solid-svg-icons';
import { ref, onMounted, onBeforeUnmount, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';

// --- Font Awesome Icon Configuration ---
library.add(
    faCalendar, faWrench, faTools, faCarAlt, faChartBar, faCartShopping,
    faCircle, faHome, faUsers, faBox, faTags, faTrademark, faTruck,
    faWarehouse, faFileAlt, faTruckLoading, faDollarSign, faUser
);

// --- Reactive States ---
const { props } = usePage();
const usuario = ref(props.auth.user);
const isProfileDropdownOpen = ref(false);
const isSidebarCollapsed = ref(localStorage.getItem('sidebarCollapsed') === 'true');
const isLoading = ref(false);

// --- DOM References ---
const profileContainer = ref(null);

/**
 * Returns a greeting based on the current hour.
 * @returns {string} The appropriate greeting (Buenos días, Buenas tardes, Buenas noches).
 */
const getGreeting = () => {
    const hour = new Date().getHours();
    if (hour < 12) return 'Buenos días';
    if (hour < 18) return 'Buenas tardes';
    return 'Buenas noches';
};

// --- Methods ---

/**
 * Toggles the profile dropdown visibility. Closes notifications if open.
 */
const toggleProfileDropdown = () => {
    isProfileDropdownOpen.value = !isProfileDropdownOpen.value;
    if (showNotifications.value) {
        showNotifications.value = false;
    }
};

/**
 * Toggles the sidebar collapse state and saves it to local storage.
 */
const toggleSidebar = () => {
    isSidebarCollapsed.value = !isSidebarCollapsed.value;
    localStorage.setItem('sidebarCollapsed', isSidebarCollapsed.value);
};


/**
 * Handles user logout, showing a loading indicator.
 */
const logout = async () => {
    isLoading.value = true;
    try {
        await router.post(route('logout'));
    } catch (error) {
        console.error('Error al cerrar sesión:', error);
        // TODO: Implement a user-friendly error notification (e.g., toast)
    } finally {
        isLoading.value = false;
    }
};

/**
 * Handles notification click events from the NotificationBell component.
 * @param {object} notification - The notification object that was clicked.
 */
const handleNotificationClick = (notification) => {
    // Aquí puedes agregar lógica adicional cuando se hace click en una notificación
    console.log('Notificación clickeada:', notification);

    // Si la notificación tiene una URL de acción, navegar a ella
    if (notification.action_url) {
        router.visit(notification.action_url);
    }
};

// --- Event Handlers ---

/**
 * Closes profile dropdown when a click occurs outside its container.
 * @param {Event} event - The click event object.
 */
const handleClickOutside = (event) => {
    if (isProfileDropdownOpen.value && profileContainer.value && !profileContainer.value.contains(event.target)) {
        isProfileDropdownOpen.value = false;
    }
};

// --- Lifecycle Hooks ---

onMounted(() => {
    // Attach global click listener for closing dropdowns
    document.addEventListener('click', handleClickOutside);
});

onBeforeUnmount(() => {
    // Clean up event listener before component unmounts
    document.removeEventListener('click', handleClickOutside);
});
</script>

<style scoped>
/* Custom Scrollbar */
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: #f1f5f9; /* Tailwind gray-100 */
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #cbd5e1; /* Tailwind gray-300 */
    border-radius: 3px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #94a3b8; /* Tailwind gray-400 */
}

/* Accessibility: Reduce motion for users who prefer it */
@media (prefers-reduced-motion: reduce) {
    * {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
    }
}

/* Responsive adjustments for dropdown width */
@media (max-width: 768px) {
    .w-80 {
        width: calc(100vw - 2rem); /* Full width minus padding on small screens */
        max-width: 320px; /* Max width to keep it from being too wide on slightly larger mobiles */
    }
}
</style>

