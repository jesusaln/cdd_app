<template>
    <div>
        <h1>Página de Clientes</h1>
        <p>Aquí se muestran los productos.</p>
    </div>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import Dashboard from '@/Pages/Dashboard.vue'; // Importa el layout del dashboard

defineOptions({ layout: Dashboard }); // Usa el layout del dashboard
</script>
