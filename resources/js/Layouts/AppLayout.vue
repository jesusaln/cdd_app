<template>
    <div class="flex flex-col h-screen bg-gray-100">
        <!-- Navbar -->
        <nav class="bg-gray-900 shadow-md p-3 flex justify-between items-center">
            <div class="flex items-center">
                <!-- Botón del logo -->
                <Link href="/panel" class="flex items-center">
                    <img src="/images/logo.png" alt="Logo" class="h-12 w-auto mr-3 cursor-pointer" />
                </Link>

                <!-- Botón para el Sidebar (Hamburguesa) -->
                <button
                    @click="toggleSidebar"
                    class="p-2 text-gray-300 hover:bg-gray-700 rounded-full transition duration-200 focus:outline-none"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>

            <!-- Nombre y rol del usuario -->
            <span v-if="usuario" class="text-gray-200 text-lg">
                Bienvenido: {{ usuario.name }}
            </span>

            <!-- Menú de usuario y notificaciones -->
            <div class="flex items-center space-x-6">
                <!-- Componente de notificaciones -->
                <div class="relative">
                    <button
                        @click="toggleNotifications"
                        class="relative p-2 hover:bg-gray-700 rounded-full transition duration-200 focus:outline-none"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-6 w-6 text-gray-300"
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
                        <!-- Badge de notificaciones no leídas -->
                        <span
                            v-if="unreadCount > 0"
                            class="absolute top-0 right-0 bg-red-500 text-white text-xs rounded-full px-1 transform translate-x-1/2 -translate-y-1/2"
                        >
                            {{ unreadCount }}
                        </span>
                    </button>

                    <!-- Dropdown de notificaciones -->
                    <div
                        v-if="showNotifications"
                        class="absolute right-0 mt-2 w-64 bg-white border border-gray-200 rounded-lg shadow-lg z-50"
                    >
                        <ul class="notifications-dropdown">
                            <li
                                v-for="notification in notifications"
                                :key="notification.id"
                                class="p-3 border-b border-gray-200 hover:bg-gray-100"
                            >
                                <div class="text-sm">{{ notification.data.client_name }} Cliente nuevo creado.</div>
                                <button
                                    @click="markAsRead(notification.id)"
                                    class="text-xs text-blue-500 hover:text-blue-700"
                                >
                                    Marcar como leído
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Foto de perfil y menú desplegable -->
                <div class="relative">
                    <button
                        @click="toggleDropdown"
                        class="flex items-center focus:outline-none"
                    >
                        <img
                            :src="usuario.profile_photo_url"
                            alt="Foto de perfil"
                            class="h-8 w-8 rounded-full"
                        />
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-4 w-4 ml-1 text-gray-300"
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

                    <!-- Menú desplegable -->
                    <div
                        v-if="isDropdownOpen"
                        class="absolute right-0 mt-2 w-48 bg-gray-700 rounded-lg shadow-lg z-50 transition-all duration-200 ease-in-out"
                    >
                        <Link :href="route('profile.show')" class="block px-4 py-2 text-gray-300 hover:bg-gray-600">Perfil</Link>
                        <Link :href="route('profile.config')" class="block px-4 py-2 text-gray-300 hover:bg-gray-600">Configuración</Link>
                        <form @submit.prevent="logout" class="block w-full">
                            <button type="submit" class="w-full text-left px-4 py-2 text-gray-300 hover:bg-gray-600">
                                Cerrar sesión
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Contenido principal -->
        <div class="flex flex-1 relative">
            <!-- Sidebar -->
            <Sidebar :isSidebarCollapsed="isSidebarCollapsed" @toggleSidebar="toggleSidebar" />

            <!-- Área de contenido -->
            <main
                :class="{'ml-64': !isSidebarCollapsed, 'ml-20': isSidebarCollapsed}"
                class="flex-1 p-6 overflow-y-auto bg-gray-50"
            >
                <slot /> <!-- Contenido de la página -->
            </main>
        </div>
    </div>
</template>

<script setup>
import Sidebar from '@/Components/Sidebar.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { library } from '@fortawesome/fontawesome-svg-core';
import {
    faCalendar,
    faTools,
    faCarAlt,
    faChartBar,
    faCartShopping,
    faCircle,
    faHome,
    faUsers,
    faBox,
    faTags,
    faTrademark,
    faTruck,
    faWarehouse,
    faFileAlt,
    faTruckLoading,
    faDollarSign,
    faUser,
} from '@fortawesome/free-solid-svg-icons';
import { ref, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';

library.add(faCalendar, faTools, faCarAlt, faChartBar, faCartShopping, faCircle, faHome, faUsers, faBox, faTags, faTrademark, faTruck, faWarehouse, faFileAlt, faTruckLoading, faDollarSign, faUser);

// Acceder a los datos del usuario desde las propiedades de la página de Inertia
const { props } = usePage();
const usuario = ref(props.auth.user);

const isDropdownOpen = ref(false);
const isSidebarCollapsed = ref(false);

const toggleDropdown = () => {
    isDropdownOpen.value = !isDropdownOpen.value;
};

const toggleSidebar = () => {
    isSidebarCollapsed.value = !isSidebarCollapsed.value;
};

const logout = () => {
    router.post(route('logout'));
};

const notifications = ref([]);
const showNotifications = ref(false);
const unreadCount = ref(0);

const fetchNotifications = async () => {
    try {
        const response = await axios.get('/notifications');
        notifications.value = response.data;
        unreadCount.value = notifications.value.filter(n => !n.read_at).length;
    } catch (error) {
        console.error('Error al obtener las notificaciones:', error);
    }
};

const toggleNotifications = () => {
    showNotifications.value = !showNotifications.value;
    if (showNotifications.value) {
        fetchNotifications();
    }
};

const markAsRead = async (id) => {
    console.log('Marcando como leído:', id); // Depuración
    try {
        await axios.post('/notifications/mark-as-read', { ids: [id] });
        fetchNotifications();
    } catch (error) {
        console.error('Error al marcar como leído:', error);
    }
};

onMounted(fetchNotifications);
</script>

<style scoped>
.notifications-dropdown {
    max-height: 300px;
    overflow-y: auto;
}

.notifications-dropdown ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.notifications-dropdown li {
    padding: 10px;
    border-bottom: 1px solid #e5e7eb;
}

.notifications-dropdown li:last-child {
    border-bottom: none;
}
</style>
