<template>
    <aside class="w-64 bg-gray-800 text-white">
        <div class="p-4">
            <h2 class="text-lg font-semibold">Dashboard</h2>
        </div>
        <nav>
            <ul>
                <!-- Usamos el componente <Link> de Inertia -->
                <li class="hover:bg-gray-700">
                    <Link href="/clientes" class="block p-4" :class="{ 'bg-gray-700': $page.url === '/clientes' }">
                        Clientes
                    </Link>
                </li>
                <li class="hover:bg-gray-700">
                    <Link href="/productos" class="block p-4" :class="{ 'bg-gray-700': $page.url === '/productos' }">
                        Productos
                    </Link>
                </li>
                <li class="hover:bg-gray-700">
                    <Link href="/cotizacion" class="block p-4" :class="{ 'bg-gray-700': $page.url === '/cotizacion' }">
                        Cotización
                    </Link>
                </li>
            </ul>
        </nav>
    </aside>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<style scoped>
/* Estilos específicos para el sidebar */
</style>
