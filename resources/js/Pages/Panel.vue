<template>
    <div>
        <h1 class="text-2xl font-bold mb-4">Bienvenido al Panel</h1>
        <div class="grid grid-cols-3 gap-4">
            <div class="bg-white p-4 rounded-lg shadow">
                <h2 class="text-lg font-semibold">Clientes</h2>
                <p>Administrar clientes registrados.</p>
                <NavLink href="/clientes" class="text-blue-500">Ver más</NavLink>
            </div>
            <div class="bg-white p-4 rounded-lg shadow">
                <h2 class="text-lg font-semibold">Productos</h2>
                <p>Gestionar inventario y productos.</p>
                <NavLink href="/productos" class="text-blue-500">Ver más</NavLink>
            </div>
            <div class="bg-white p-4 rounded-lg shadow">
                <h2 class="text-lg font-semibold">Proveedores</h2>
                <p>Lista de proveedores registrados.</p>
                <NavLink href="/proveedores" class="text-blue-500">Ver más</NavLink>
            </div>
        </div>
    </div>
</template>

<script setup>
import Dashboard from '@/Pages/Dashboard.vue'; // Importa el layout del dashboard
import NavLink from '@/Components/NavLink.vue';

defineOptions({ layout: Dashboard }); // Usa el layout del dashboard


</script>
