<template>
    <div class="flex flex-col h-screen bg-gray-100">
        <!-- Barra de navegación -->
        <nav class="bg-gray-900 shadow-md p-3 flex justify-between items-center">
            <div class="flex items-center space-x-4">
                <!-- Botón del logo -->
                <Link href="/panel" class="flex items-center">
                    <img src="/images/logo.png" alt="Logo" class="h-12 w-auto mr-3 cursor-pointer" />
                </Link>


                <!-- Botón para el Sidebar (Hamburguesa) -->
                <button
                    @click="alternarSidebar"
                    class="p-2 text-gray-300 hover:bg-gray-700 rounded-full transition duration-200 focus:outline-none"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>
<!-- Nombre del usuario -->
                <span v-if="usuario" class="text-gray-200 text-lg ">
                    Bienvenido: {{ usuario.nombre }} ( {{ usuario.rol }}. )
                </span>




            <!-- Menú de usuario y notificaciones -->
            <div class="flex items-center space-x-6">
                <!-- Componente de notificaciones -->
                <div class="relative">
                    <button
                        @click="alternarNotificaciones"
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
                        <!-- Insignia de notificaciones no leídas -->
                        <span
                            v-if="conteoNoLeidas > 0"
                            class="absolute top-0 right-0 bg-red-500 text-white text-xs rounded-full px-1 transform translate-x-1/2 -translate-y-1/2"
                        >
                            {{ conteoNoLeidas }}
                        </span>
                    </button>

                    <!-- Menú desplegable de notificaciones -->
                    <div
                        v-if="mostrarNotificaciones"
                        class="absolute right-0 mt-2 w-64 bg-white border border-gray-200 rounded-lg shadow-lg z-50"
                    >
                        <ul class="desplegable-notificaciones">
                            <li
                                v-for="notificacion in notificaciones"
                                :key="notificacion.id"
                                class="p-3 border-b border-gray-200 hover:bg-gray-100"
                            >
                                <div class="text-sm">{{ notificacion.data.nombre_cliente }} Cliente nuevo creado.</div>
                                <button
                                    @click="marcarComoLeida(notificacion.id)"
                                    class="text-xs text-blue-500 hover:text-blue-700"
                                >
                                    Marcar como leída
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Foto de perfil y menú desplegable -->
                <div class="relative">
                    <button
                        @click="alternarDesplegable"
                        class="flex items-center focus:outline-none"
                    >
                        <img
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
                        v-if="estaDesplegableAbierto"
                        class="absolute right-0 mt-2 w-48 bg-gray-700 rounded-lg shadow-lg z-50 transition-all duration-200 ease-in-out"
                    >
                        <a :href="route('profile.show')" class="block px-4 py-2 text-gray-300 hover:bg-gray-600">Perfil</a>
                        <a href="#" class="block px-4 py-2 text-gray-300 hover:bg-gray-600">Configuración</a>
                        <form @submit.prevent="cerrarSesion" class="block w-full">
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
            <!-- Barra lateral -->
            <Sidebar :estaSidebarColapsada="estaSidebarColapsada" @alternarSidebar="alternarSidebar" />

            <!-- Área de contenido -->
            <main
                :class="{'ml-64': !estaSidebarColapsada, 'ml-20': estaSidebarColapsada}"
                class="flex-1 p-6 overflow-y-auto bg-gray-50"
            >
                <slot /> <!-- Contenido de la página -->
            </main>
        </div>
    </div>
</template>

<script setup>
import Sidebar from '@/Components/Sidebar.vue';
import NavLink from '@/Components/NavLink.vue';
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

const estaDesplegableAbierto = ref(false);
const estaSidebarColapsada = ref(false);

const alternarDesplegable = () => {
    estaDesplegableAbierto.value = !estaDesplegableAbierto.value;
};

const alternarSidebar = () => {
    estaSidebarColapsada.value = !estaSidebarColapsada.value;
};

const cerrarSesion = () => {
    router.post(route('logout'));
};

const notificaciones = ref([]);
const mostrarNotificaciones = ref(false);
const conteoNoLeidas = ref(0);

const obtenerNotificaciones = async () => {
    const respuesta = await axios.get('/notifications');
    notificaciones.value = respuesta.data;
    conteoNoLeidas.value = notificaciones.value.filter(n => !n.read_at).length;
};

const alternarNotificaciones = () => {
    mostrarNotificaciones.value = !mostrarNotificaciones.value;
    if (mostrarNotificaciones.value) {
        obtenerNotificaciones();
    }
};

const marcarComoLeida = async (id) => {
    await axios.post('/notifications/mark-as-read', { ids: [id] });
    obtenerNotificaciones();
};

onMounted(obtenerNotificaciones);
</script>

<style scoped>
.desplegable-notificaciones {
    max-height: 300px;
    overflow-y: auto;
}

.desplegable-notificaciones ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.desplegable-notificaciones li {
    padding: 10px;
    border-bottom: 1px solid #e5e7eb;
}

.desplegable-notificaciones li:last-child {
    border-bottom: none;
}
</style>
