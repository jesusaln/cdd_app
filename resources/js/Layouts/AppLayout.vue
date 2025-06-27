<template>
    <div class="flex flex-col h-screen bg-gradient-to-br from-gray-50 to-gray-100">
        <!-- Navbar mejorado -->
        <nav class="bg-gradient-to-r from-gray-900 to-gray-800 shadow-lg border-b border-gray-700">
            <div class="px-4 py-3">
                <div class="flex justify-between items-center">
                    <!-- Sección izquierda: Logo y hamburguesa -->
                    <div class="flex items-center space-x-3">
                        <Link href="/panel" class="flex items-center group">
                            <img
                                src="/images/logo.png"
                                alt="Logo"
                                class="h-10 w-auto transition-transform duration-200 group-hover:scale-105"
                            />
                        </Link>

                        <button
                            @click="toggleSidebar"
                            class="p-2 text-gray-300 hover:text-white hover:bg-gray-700 rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            :title="isSidebarCollapsed ? 'Expandir menú' : 'Contraer menú'"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                            </svg>
                        </button>
                    </div>

                    <!-- Saludo personalizado mejorado -->
                    <div v-if="usuario" class="hidden md:flex items-center">
                        <span class="text-gray-200 text-sm font-medium">
                            {{ getGreeting() }},
                            <span class="text-blue-300 font-semibold">{{ usuario.name }}</span>
                        </span>
                    </div>

                    <!-- Sección derecha: Notificaciones y perfil -->
                    <div class="flex items-center space-x-3">
                        <!-- Notificaciones mejoradas -->
                        <div class="relative" ref="notificationsContainer">
                            <button
                                @click="toggleNotifications"
                                class="relative p-2 text-gray-300 hover:text-white hover:bg-gray-700 rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500"
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

                                <!-- Badge animado -->
                                <span
                                    v-if="unreadCount > 0"
                                    class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full px-1.5 py-0.5 min-w-[20px] text-center animate-pulse"
                                >
                                    {{ unreadCount > 99 ? '99+' : unreadCount }}
                                </span>
                            </button>

                            <!-- Dropdown mejorado de notificaciones -->
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
                                    class="absolute right-0 mt-2 w-80 bg-white border border-gray-200 rounded-xl shadow-xl z-50 overflow-hidden"
                                >
                                    <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                                        <div class="flex justify-between items-center">
                                            <h3 class="text-sm font-semibold text-gray-900">Notificaciones</h3>
                                            <button
                                                v-if="unreadCount > 0"
                                                @click="markAllAsRead"
                                                class="text-xs text-blue-600 hover:text-blue-800 font-medium"
                                            >
                                                Marcar todas como leídas
                                            </button>
                                        </div>
                                    </div>

                                    <div class="max-h-80 overflow-y-auto">
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

                        <!-- Menú de perfil mejorado -->
                        <div class="relative" ref="profileContainer">
                            <button
                                @click="toggleDropdown"
                                class="flex items-center space-x-2 p-1 rounded-lg hover:bg-gray-700 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            >
                                <img
                                    :src="usuario.profile_photo_url"
                                    alt="Foto de perfil"
                                    class="h-8 w-8 rounded-full border-2 border-gray-600 object-cover"
                                />
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-4 w-4 text-gray-300 transition-transform duration-200"
                                    :class="{ 'rotate-180': isDropdownOpen }"
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

                            <!-- Dropdown mejorado del perfil -->
                            <Transition
                                enter-active-class="transition ease-out duration-200"
                                enter-from-class="transform opacity-0 scale-95"
                                enter-to-class="transform opacity-100 scale-100"
                                leave-active-class="transition ease-in duration-150"
                                leave-from-class="transform opacity-100 scale-100"
                                leave-to-class="transform opacity-0 scale-95"
                            >
                                <div
                                    v-if="isDropdownOpen"
                                    class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-xl z-50 overflow-hidden border border-gray-200"
                                >
                                    <!-- Header del dropdown -->
                                    <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
                                        <p class="text-sm font-medium text-gray-900">{{ usuario.name }}</p>
                                        <p class="text-xs text-gray-500">{{ usuario.email }}</p>
                                    </div>

                                    <!-- Opciones del menú -->
                                    <div class="py-1">
                                        <Link
                                            :href="route('profile.show')"
                                            class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-150"
                                        >
                                            <svg class="mr-3 h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                            Mi Perfil
                                        </Link>
                                        <Link
                                            :href="route('empresas.index')"
                                            class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-150"
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

        <!-- Contenido principal con transiciones -->
        <div class="flex flex-1 relative overflow-hidden">
            <!-- Sidebar -->
            <Sidebar :isSidebarCollapsed="isSidebarCollapsed" @toggleSidebar="toggleSidebar" />

            <!-- Área de contenido mejorada -->
            <main
                :class="{'ml-64': !isSidebarCollapsed, 'ml-20': isSidebarCollapsed}"
                class="flex-1 overflow-y-auto bg-gray-50 transition-all duration-300 ease-in-out"
            >
                <div class="p-6">
                    <slot /> <!-- Contenido de la página -->
                </div>
            </main>
        </div>

        <!-- Loading indicator -->
        <div v-if="isLoading" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 flex items-center space-x-3">
                <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
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

// Configuración de iconos
library.add(
    faCalendar, faWrench, faTools, faCarAlt, faChartBar, faCartShopping,
    faCircle, faHome, faUsers, faBox, faTags, faTrademark, faTruck,
    faWarehouse, faFileAlt, faTruckLoading, faDollarSign, faUser
);

// Estados reactivos
const { props } = usePage();
const usuario = ref(props.auth.user);
const isDropdownOpen = ref(false);
const isSidebarCollapsed = ref(localStorage.getItem('sidebarCollapsed') === 'true');
const showNotifications = ref(false);
const notifications = ref([]);
const unreadCount = ref(0);
const isLoading = ref(false);

// Referencias del DOM
const notificationsContainer = ref(null);
const profileContainer = ref(null);

// Timers para cerrar dropdowns
let notificationTimer;
let profileTimer;

// Computed properties
const getGreeting = () => {
    const hour = new Date().getHours();
    if (hour < 12) return 'Buenos días';
    if (hour < 18) return 'Buenas tardes';
    return 'Buenas noches';
};

// Métodos principales
const toggleDropdown = () => {
    isDropdownOpen.value = !isDropdownOpen.value;
    if (showNotifications.value) showNotifications.value = false;
};

const toggleSidebar = () => {
    isSidebarCollapsed.value = !isSidebarCollapsed.value;
    localStorage.setItem('sidebarCollapsed', isSidebarCollapsed.value);
};

const toggleNotifications = () => {
    showNotifications.value = !showNotifications.value;
    if (isDropdownOpen.value) isDropdownOpen.value = false;
    if (showNotifications.value) {
        fetchNotifications();
    }
};

const logout = async () => {
    isLoading.value = true;
    try {
        await router.post(route('logout'));
    } catch (error) {
        console.error('Error al cerrar sesión:', error);
    } finally {
        isLoading.value = false;
    }
};

// Manejo de notificaciones
const fetchNotifications = async () => {
    try {
        const response = await axios.get('/notifications');
        notifications.value = response.data;
        unreadCount.value = notifications.value.filter(n => !n.read_at).length;
    } catch (error) {
        console.error('Error al obtener notificaciones:', error);
    }
};

const markAsRead = async (id) => {
    try {
        await axios.post('/notifications/mark-as-read', { ids: [id] });
        await fetchNotifications();
    } catch (error) {
        console.error('Error al marcar como leído:', error);
    }
};

const markAllAsRead = async () => {
    try {
        const unreadIds = notifications.value
            .filter(n => !n.read_at)
            .map(n => n.id);

        if (unreadIds.length > 0) {
            await axios.post('/notifications/mark-as-read', { ids: unreadIds });
            await fetchNotifications();
        }
    } catch (error) {
        console.error('Error al marcar todas como leídas:', error);
    }
};

const handleNotificationClick = (notification) => {
    if (!notification.read_at) {
        markAsRead(notification.id);
    }
    // Aquí puedes agregar lógica para navegar a la página relacionada
    showNotifications.value = false;
};

const formatNotificationTime = (timestamp) => {
    const date = new Date(timestamp);
    const now = new Date();
    const diffInHours = Math.floor((now - date) / (1000 * 60 * 60));

    if (diffInHours < 1) return 'Hace unos minutos';
    if (diffInHours < 24) return `Hace ${diffInHours} hora${diffInHours > 1 ? 's' : ''}`;

    const diffInDays = Math.floor(diffInHours / 24);
    if (diffInDays < 7) return `Hace ${diffInDays} día${diffInDays > 1 ? 's' : ''}`;

    return date.toLocaleDateString();
};

// Event handlers
const handleClickOutside = (event) => {
    if (notificationsContainer.value && !notificationsContainer.value.contains(event.target)) {
        showNotifications.value = false;
    }
    if (profileContainer.value && !profileContainer.value.contains(event.target)) {
        isDropdownOpen.value = false;
    }
};

// Lifecycle hooks
onMounted(() => {
    fetchNotifications();
    document.addEventListener('click', handleClickOutside);

    // Actualizar notificaciones cada 30 segundos
    const interval = setInterval(fetchNotifications, 30000);

    onBeforeUnmount(() => {
        clearInterval(interval);
    });
});

onBeforeUnmount(() => {
    document.removeEventListener('click', handleClickOutside);
    clearTimeout(notificationTimer);
    clearTimeout(profileTimer);
});
</script>

<style scoped>
/* Animaciones personalizadas */
@keyframes slideIn {
    from {
        transform: translateX(-100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

/* Scrollbar personalizado */
.max-h-80::-webkit-scrollbar {
    width: 6px;
}

.max-h-80::-webkit-scrollbar-track {
    background: #f1f5f9;
}

.max-h-80::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 3px;
}

.max-h-80::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

/* Mejoras de accesibilidad */
@media (prefers-reduced-motion: reduce) {
    * {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
    }
}

/* Responsive design */
@media (max-width: 768px) {
    .w-80 {
        width: calc(100vw - 2rem);
        max-width: 320px;
    }
}
</style>
