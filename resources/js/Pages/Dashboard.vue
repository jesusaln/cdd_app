<template>
    <div class="flex h-screen bg-gray-100">
        <!-- Sidebar -->
        <aside class="w-64 bg-gray-800 text-white">
            <div class="p-4">
                <h2 class="text-lg font-semibold">Dashboard</h2>
            </div>
            <nav>
                <ul>
                    <li class="hover:bg-gray-700">
                        <Link href="/clientes" class="block p-4" :class="{ 'bg-gray-700': $page.url.startsWith('/clientes') }">
                            Clientes
                        </Link>
                    </li>
                    <li class="hover:bg-gray-700">
                        <Link href="/productos" class="block p-4" :class="{ 'bg-gray-700': $page.url.startsWith('/productos') }">
                            Productos
                        </Link>
                    </li>
                    <li class="hover:bg-gray-700">
                        <Link href="/cotizacion" class="block p-4" :class="{ 'bg-gray-700': $page.url.startsWith('/cotizacion') }">
                            Cotización
                        </Link>
                    </li>
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
import { Link } from '@inertiajs/vue3';
</script>
