<template>
    <Head title="Reportes" />

</template>

<script setup>
import { ref, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import { format, isWithinInterval } from 'date-fns';
import { es } from 'date-fns/locale';
import AppLayout from '@/Layouts/AppLayout.vue';


// Define el layout del dashboard
defineOptions({ layout: AppLayout });

const props = defineProps({
    reportesVentas: { type: Array, default: () => [] },
    corteVentas: { type: Number, default: 0 },
    utilidadVentas: { type: Number, default: 0 },
    reportesCompras: { type: Array, default: () => [] },
    totalCompras: { type: Number, default: 0 },
    inventario: { type: Array, default: () => [] },
});

const activeTab = ref('ventas');
const fechaInicio = ref('');
const fechaFin = ref('');

const formatCurrency = (value) => `$${(Number.parseFloat(value) || 0).toFixed(2)}`;
const calculateProfit = (venta) => (Number.parseFloat(venta.total) || 0) - (Number.parseFloat(venta.costo_total) || 0);
const formatDate = (date) => {
    if (!date) return 'Fecha no disponible';
    try {
        return format(new Date(date), 'MMM d, yyyy h:mm a', { locale: es });
    } catch {
        return 'Fecha inválida';
    }
};

const filtrarPorFecha = (items) => {
    if (!fechaInicio.value || !fechaFin.value) return items;

    const start = new Date(fechaInicio.value);
    const end = new Date(fechaFin.value);

    return items.filter(item => {
        const fecha = new Date(item.created_at);
        return isWithinInterval(fecha, { start, end });
    });
};

const ventasFiltradas = computed(() => filtrarPorFecha(props.reportesVentas));
const comprasFiltradas = computed(() => filtrarPorFecha(props.reportesCompras));
const corteFiltrado = computed(() => ventasFiltradas.value.reduce((acc, v) => acc + Number.parseFloat(v.total), 0));
const utilidadFiltrada = computed(() => ventasFiltradas.value.reduce((acc, v) => acc + calculateProfit(v), 0));
const totalComprasFiltrado = computed(() => comprasFiltradas.value.reduce((acc, c) => acc + Number.parseFloat(c.total), 0));

const tabClass = (tab) => ({
    'bg-blue-500 text-white': activeTab.value === tab,
    'bg-gray-300': activeTab.value !== tab,
});
</script>

<style scoped>
.container {
    max-width: 1200px;
}
</style>
