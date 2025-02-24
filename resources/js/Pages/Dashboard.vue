<template>
    <div class="flex flex-col h-screen bg-gray-100">
        <!-- Navbar -->
        <nav class="bg-gray-900 shadow-md p-3 flex justify-between items-center">
            <div class="flex items-center">
                <!-- Botón del logo -->
                <Link href="/panel">
                    <img src="/images/logo.png" alt="Logo" class="h-12 w-auto mr-3 cursor-pointer" />
                </Link>

                <!-- Botón para el Sidebar (Hamburguesa) -->
                <button @click="toggleSidebar" class="lg:hidden p-2 text-gray-300 hover:bg-gray-700 rounded-full transition duration-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
            </div>

            <!-- Menú de usuario y notificaciones -->
            <div class="flex items-center space-x-6">
                <!-- Ícono de notificaciones con badge -->
                <button class="relative p-2 hover:bg-gray-700 rounded-full transition duration-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    <span class="absolute top-0 right-0 bg-red-500 text-white text-xs rounded-full px-1">3</span>
                </button>

                <!-- Foto de perfil y menú desplegable -->
                <div class="relative">
                    <button @click="toggleDropdown" class="flex items-center focus:outline-none">
                        <img src="/path/to/profile.jpg" alt="Foto de perfil" class="h-8 w-8 rounded-full" />
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <!-- Menú desplegable -->
                    <div v-if="isDropdownOpen" class="absolute right-0 mt-2 w-48 bg-gray-700 rounded-lg shadow-lg z-50 transition-all duration-200 ease-in-out">
                        <a href="#" class="block px-4 py-2 text-gray-300 hover:bg-gray-600">Perfil</a>
                        <a href="#" class="block px-4 py-2 text-gray-300 hover:bg-gray-600">Configuración</a>
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
            <aside :class="{'w-64': !isSidebarCollapsed, 'w-20': isSidebarCollapsed}" class="bg-gray-800 text-white absolute left-0 top-0 bottom-0 z-10 transition-all duration-300 ease-in-out">
                <nav>
                    <ul>
                        <NavLink href="/panel" icon="home">Panel</NavLink>
                        <NavLink href="/clientes" icon="users">Clientes</NavLink>
                        <NavLink href="/productos" icon="box">Productos</NavLink>
                        <NavLink href="/categorias" icon="tags">Categorías</NavLink>
                        <NavLink href="/marcas" icon="trademark">Marcas</NavLink>
                        <NavLink href="/proveedores" icon="truck">Proveedores</NavLink>
                        <NavLink href="/almacenes" icon="warehouse">Almacenes</NavLink>
                        <NavLink href="/cotizaciones" icon="file-alt">Cotizaciones</NavLink>
                        <NavLink href="/pedidos" icon="truck-loading">Pedidos</NavLink>
                    </ul>
                </nav>
            </aside>

            <!-- Área de contenido -->
            <main :class="{'ml-64': !isSidebarCollapsed, 'ml-20': isSidebarCollapsed}" class="flex-1 p-6 overflow-y-auto bg-gray-50">
                <slot /> <!-- Contenido de la página -->
            </main>
        </div>
    </div>
</template>

<script setup>
import NavLink from '@/Components/NavLink.vue'; // Asegúrate de que la ruta sea correcta
import { Link } from '@inertiajs/vue3'; // Importa el componente Link de Inertia
import { library } from '@fortawesome/fontawesome-svg-core';
import { fas } from '@fortawesome/free-solid-svg-icons'; // Agregar todos los iconos de la familia 'solid'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { ref } from 'vue';
import { router } from '@inertiajs/vue3'; // Importa router de Inertia

// Registrar los iconos en la librería
library.add(fas);

// Lógica para el menú desplegable
const isDropdownOpen = ref(false);
const isSidebarCollapsed = ref(false); // Estado para controlar si el sidebar está colapsado

const toggleDropdown = () => {
    isDropdownOpen.value = !isDropdownOpen.value;
};

// Función para colapsar o expandir el sidebar
const toggleSidebar = () => {
    isSidebarCollapsed.value = !isSidebarCollapsed.value;
};

// Función para cerrar sesión
const logout = () => {
    router.post(route('logout')); // Usa la ruta de cierre de sesión de Laravel
};
</script>
