<template>
    <aside
        :class="{'w-60': !isSidebarCollapsed, 'w-10': isSidebarCollapsed}"
        class="bg-gray-800 text-white absolute left-0 top-0 bottom-0 z-10 transition-all duration-300 ease-in-out overflow-y-auto"
    >
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
                <NavLink href="/ventas" icon="dollar-sign">Ventas</NavLink>
                <NavLink href="/reportes" icon="chart-bar">Reportes</NavLink>
                <NavLink href="/compras" icon="cart-shopping">Compras</NavLink>
                <NavLink href="/herramientas" icon="circle">Herramientas</NavLink>
                <NavLink href="/tecnicos" icon="users">Técnicos</NavLink>
                <NavLink href="/usuarios" icon="user">Usuarios</NavLink>
                <NavLink href="/carros" icon="car-alt">Carros</NavLink>
                <NavLink href="/mantenimientos" icon="tools">Mantenimientos</NavLink>
            </ul>
        </nav>
    </aside>
</template>

<script setup>
import NavLink from '@/Components/NavLink.vue';
import { ref } from 'vue';

const isSidebarCollapsed = ref(false);

const toggleSidebar = () => {
    isSidebarCollapsed.value = !isSidebarCollapsed.value;
};
</script>
