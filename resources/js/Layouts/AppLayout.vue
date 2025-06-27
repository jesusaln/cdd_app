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
                        <div class="relative" ref="notificationsContainer">
                            <button
                                @click="toggleNotifications"
                                class="relative p-2 text-gray-300 hover:text-white hover:bg-gray-700 rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                :aria-expanded="showNotifications.toString()"
                                aria-controls="notifications-dropdown"
                                title="Notificaciones"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-5 w-5"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
                                    ></path>
                                </svg>

                                <span
                                    v-if="unreadCount > 0"
                                    class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full px-1.5 py-0.5 min-w-[20px] text-center animate-pulse"
                                    aria-live="polite"
                                    aria-atomic="true"
                                >
                                    {{ unreadCount > 99 ? '99+' : unreadCount }}
                                </span>
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
                                    v-if="showNotifications"
                                    id="notifications-dropdown"
                                    role="menu"
                                    aria-orientation="vertical"
                                    aria-labelledby="notifications-button"
                                    class="absolute right-0 mt-2 w-80 bg-white border border-gray-200 rounded-xl shadow-xl z-50 overflow-hidden"
                                >
                                    <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                                        <div class="flex justify-between items-center">
                                            <h3 class="text-sm font-semibold text-gray-900">Notificaciones</h3>
                                            <button
                                                v-if="unreadCount > 0"
                                                @click="markAllAsRead"
                                                class="text-xs text-blue-600 hover:text-blue-800 font-medium"
                                                role="menuitem"
                                            >
                                                Marcar todas como leídas
                                            </button>
                                        </div>
                                    </div>

                                    <div class="max-h-80 overflow-y-auto custom-scrollbar">
                                        <div v-if="notifications.length === 0" class="p-6 text-center text-gray-500">
                                            <svg class="mx-auto h-12 w-12 text-gray-400 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                            </svg>
                                            No hay notificaciones
                                        </div>

                                        <div
                                            v-for="notification in notifications"
                                            :key="notification.id"
                                            class="p-4 border-b border-gray-100 hover:bg-gray-50 transition-colors duration-150 cursor-pointer"
                                            :class="{ 'bg-blue-50': !notification.read_at }"
                                            @click="handleNotificationClick(notification)"
                                            role="menuitem"
                                            tabindex="0"
                                        >
                                            <div class="flex items-start space-x-3">
                                                <div class="flex-shrink-0">
                                                    <div class="w-2 h-2 bg-blue-500 rounded-full" v-if="!notification.read_at"></div>
                                                    <div class="w-2 h-2" v-else></div>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-sm text-gray-900 font-medium">
                                                        {{ notification.data.client_name }}
                                                    </p>
                                                    <p class="text-sm text-gray-600">
                                                        Cliente nuevo creado
                                                    </p>
                                                    <p class="text-xs text-gray-400 mt-1">
                                                        {{ formatNotificationTime(notification.created_at) }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </Transition>
                        </div>

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
const showNotifications = ref(false);
const notifications = ref([]);
const isLoading = ref(false);

// --- DOM References ---
const notificationsContainer = ref(null);
const profileContainer = ref(null);

// --- Computed Properties ---
const unreadCount = computed(() => notifications.value.filter(n => !n.read_at).length);

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
 * Toggles the notifications dropdown visibility. Closes profile dropdown if open.
 * Fetches notifications if opening.
 */
const toggleNotifications = () => {
    showNotifications.value = !showNotifications.value;
    if (isProfileDropdownOpen.value) {
        isProfileDropdownOpen.value = false;
    }
    if (showNotifications.value) {
        fetchNotifications();
    }
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

// --- Notification Management ---

/**
 * Fetches notifications from the backend.
 */
const fetchNotifications = async () => {
    try {
        const response = await axios.get('/notifications');
        notifications.value = response.data;
    } catch (error) {
        console.error('Error al obtener notificaciones:', error);
        // TODO: Implement a user-friendly error notification
    }
};

/**
 * Marks a specific notification as read by its ID.
 * Updates the local notification array for immediate UI reflection.
 * @param {string} id - The ID of the notification to mark as read.
 */
const markAsRead = async (id) => {
    try {
        await axios.post('/notifications/mark-as-read', { ids: [id] });
        const index = notifications.value.findIndex(n => n.id === id);
        if (index !== -1) {
            notifications.value[index].read_at = new Date().toISOString();
        }
    } catch (error) {
        console.error('Error al marcar notificación como leída:', error);
        // TODO: Implement a user-friendly error notification
    }
};

/**
 * Marks all unread notifications as read.
 * Updates the local notification array for immediate UI reflection.
 */
const markAllAsRead = async () => {
    const unreadIds = notifications.value
        .filter(n => !n.read_at)
        .map(n => n.id);

    if (unreadIds.length > 0) {
        try {
            await axios.post('/notifications/mark-as-read', { ids: unreadIds });
            notifications.value.forEach(n => {
                if (!n.read_at) n.read_at = new Date().toISOString();
            });
        } catch (error) {
            console.error('Error al marcar todas las notificaciones como leídas:', error);
            // TODO: Implement a user-friendly error notification
        }
    }
};

/**
 * Handles click on a notification item. Marks it as read and closes the dropdown.
 * Additional navigation logic can be added based on notification data.
 * @param {object} notification - The notification object that was clicked.
 */
const handleNotificationClick = (notification) => {
    if (!notification.read_at) {
        markAsRead(notification.id);
    }
    // Example: Navigate to a specific route based on notification data
    // if (notification.data.url) {
    //     router.visit(notification.data.url);
    // }
    showNotifications.value = false;
};

/**
 * Formats a timestamp into a human-readable relative time string.
 * @param {string} timestamp - The ISO timestamp string.
 * @returns {string} Formatted time string (e.g., "Hace 5 minutos", "Hace 2 días").
 */
const formatNotificationTime = (timestamp) => {
    const date = new Date(timestamp);
    const now = new Date();
    const diffInSeconds = Math.floor((now - date) / 1000);

    if (diffInSeconds < 60) return 'Hace unos segundos';

    const diffInMinutes = Math.floor(diffInSeconds / 60);
    if (diffInMinutes < 60) return `Hace ${diffInMinutes} minuto${diffInMinutes > 1 ? 's' : ''}`;

    const diffInHours = Math.floor(diffInMinutes / 60);
    if (diffInHours < 24) return `Hace ${diffInHours} hora${diffInHours > 1 ? 's' : ''}`;

    const diffInDays = Math.floor(diffInHours / 24);
    if (diffInDays < 30) return `Hace ${diffInDays} día${diffInDays > 1 ? 's' : ''}`;

    // Fallback for older notifications: "DD Mon. YYYY"
    return date.toLocaleDateString('es-ES', { day: '2-digit', month: 'short', year: 'numeric' });
};

// --- Event Handlers ---

/**
 * Closes dropdowns when a click occurs outside their respective containers.
 * @param {Event} event - The click event object.
 */
const handleClickOutside = (event) => {
    if (showNotifications.value && notificationsContainer.value && !notificationsContainer.value.contains(event.target)) {
        showNotifications.value = false;
    }
    if (isProfileDropdownOpen.value && profileContainer.value && !profileContainer.value.contains(event.target)) {
        isProfileDropdownOpen.value = false;
    }
};

// --- Lifecycle Hooks ---
let notificationFetchInterval;

onMounted(() => {
    // Initial fetch of notifications
    fetchNotifications();
    // Attach global click listener for closing dropdowns
    document.addEventListener('click', handleClickOutside);
    // Set up periodic notification fetching
    notificationFetchInterval = setInterval(fetchNotifications, 60000); // Fetch every minute
});

onBeforeUnmount(() => {
    // Clean up event listener and interval before component unmounts
    document.removeEventListener('click', handleClickOutside);
    clearInterval(notificationFetchInterval);
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

