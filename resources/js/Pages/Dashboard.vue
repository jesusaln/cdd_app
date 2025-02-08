<template>
    <div class="flex h-screen bg-gray-100">
        <!-- Sidebar -->
        <aside class="w-64 bg-gray-800 text-white">
            <div class="p-4">
                <h2 class="text-lg font-semibold">Dashboard</h2>
            </div>
            <nav>
                <ul>
                    <NavLink href="/panel" icon="home">Panel</NavLink>
<NavLink href="/clientes" icon="users">Clientes</NavLink>
<NavLink href="/productos" icon="box">Productos</NavLink>
<NavLink href="/categorias" icon="tags">Categorías</NavLink>
<NavLink href="/marcas" icon="trademark">Marcas</NavLink>
<NavLink href="/proveedores" icon="truck">Proveedores</NavLink>
<NavLink href="/almacenes" icon="truck">Almacenes</NavLink>

                </ul>
            </nav>
        </aside>

        <!-- Contenido principal -->
        <main class="flex-1 p-4 overflow-y-auto">
            <slot />
        </main>
    </div>
</template>

<script setup>
import NavLink from '@/Components/NavLink.vue'; // Asegúrate de que la ruta sea correcta
import { library } from '@fortawesome/fontawesome-svg-core';
import { fas } from '@fortawesome/free-solid-svg-icons'; // Agregar todos los iconos de la familia 'solid'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';

// Registrar los iconos en la librería
library.add(fas); // Agregar todos los iconos de FontAwesome (puedes agregar solo los específicos si prefieres)

</script>
