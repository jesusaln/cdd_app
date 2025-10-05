<template>
    <Head title="Centro de Reportes" />

    <div class="max-w-7xl mx-auto">
        <div class="bg-white shadow-sm rounded-lg">
            <!-- Header -->
            <div class="border-b border-gray-200 px-6 py-4">
                <h1 class="text-2xl font-semibold text-gray-900">Centro de Reportes</h1>
                <p class="text-sm text-gray-600 mt-1">Accede a todos los reportes del sistema organizados por categorías</p>

            </div>

            <!-- Contenido -->
            <div class="p-6">
                <!-- Todos los reportes en una cuadrícula -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    <div
                        v-for="card in reportCards"
                        :key="card.titulo"
                        class="bg-white border border-gray-200 rounded-lg p-6 hover:shadow-lg transition-shadow flex flex-col"
                    >
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex items-start space-x-4">
                                <div :class="['w-12 h-12 rounded-lg flex items-center justify-center', getCategoriaColor(card.categoria)]">
                                    <component :is="getCategoriaIcon(card.categoria)" class="w-6 h-6 text-white" />
                                </div>
                                <div>
                                    <div class="flex items-center space-x-2">
                                        <h3 class="text-lg font-semibold text-gray-900">{{ card.titulo }}</h3>
                                        <span
                                            v-if="card.isNew"
                                            class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-700"
                                        >
                                            Nuevo
                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-600">{{ card.descripcion }}</p>
                                </div>
                            </div>
                        </div>

                        <ul v-if="card.highlights?.length" class="mt-4 space-y-2 flex-1">
                            <li
                                v-for="highlight in card.highlights"
                                :key="highlight"
                                class="flex items-start text-sm text-gray-600"
                            >
                                <span
                                    :class="['mt-0.5 mr-2 inline-flex items-center justify-center w-5 h-5 rounded-full text-xs font-semibold', getCategoriaBadgeColor(card.categoria)]"
                                >
                                    ✓
                                </span>
                                <span class="leading-snug">{{ highlight }}</span>
                            </li>
                        </ul>

                        <Link
                            :href="card.href"
                            :class="['mt-6 inline-flex items-center px-4 py-2 text-white text-sm font-medium rounded-md transition-colors w-full justify-center', getCategoriaButtonColor(card.categoria)]"
                        >
                            Ver Reporte
                            <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineOptions({ layout: AppLayout });

const reportCards = [
    {
        titulo: 'Ventas y Productos',
        descripcion: 'Analiza el desempeño comercial y los artículos con mayor impacto en ventas.',
        href: '/reportes?tab=ventas',
        categoria: 'ventas',
        highlights: [
            'Ventas generales y utilidades',
            'Ventas pendientes de pago',
            'Ranking de productos más vendidos',
        ],
    },
    {
        titulo: 'Corte de Pagos',
        descripcion: 'Control diario y semanal de ingresos, egresos y conciliaciones.',
        href: '/reportes?tab=corte',
        categoria: 'pagos',
        highlights: [
            'Resumen de caja por periodo',
            'Conciliación de ingresos y egresos',
            'Exportación rápida de cortes',
        ],
    },
    {
        titulo: 'Gestión de Cobranzas',
        descripcion: 'Monitorea el estado de las cuentas por cobrar y sus responsables.',
        href: '/reportes?tab=cobranzas',
        categoria: 'pagos',
        highlights: [
            'Pagos pendientes y realizados',
            'Alertas de clientes morosos',
            'Seguimiento por responsable',
        ],
    },
    {
        titulo: 'Clientes y Fidelización',
        descripcion: 'Conoce a tus clientes activos y detecta oportunidades de seguimiento.',
        href: '/reportes?tab=clientes',
        categoria: 'clientes',
        highlights: [
            'Clientes activos recientes',
            'Clientes con pagos pendientes',
            'Segmentación por categoría',
        ],
    },
    {
        titulo: 'Inventario en Tiempo Real',
        descripcion: 'Consulta existencias disponibles y recibe alertas de stock.',
        href: '/reportes?tab=inventario',
        categoria: 'inventario',
        highlights: [
            'Stock disponible por categoría',
            'Productos bajos o sin stock',
            'Valor total del inventario',
        ],
    },
    {
        titulo: 'Movimientos de Inventario',
        descripcion: 'Audita entradas y salidas filtradas por periodo o almacén.',
        href: '/reportes?tab=movimientos',
        categoria: 'inventario',
        highlights: [
            'Movimientos recientes',
            'Filtros por almacén y usuario',
            'Historial exportable',
        ],
    },
    {
        titulo: 'Reportes Avanzados de Inventario',
        descripcion: 'Explora reportes dedicados de stock, costos y movimientos detallados.',
        href: '/reportes/inventario/dashboard',
        categoria: 'inventario',
        highlights: [
            'Stock por almacén',
            'Productos con bajo stock',
            'Costos de inventario y valorización',
        ],
        isNew: true,
    },
    {
        titulo: 'Servicios y Citas',
        descripcion: 'Supervisa la demanda de servicios y el cumplimiento de agendas.',
        href: '/reportes?tab=servicios',
        categoria: 'servicios',
        highlights: [
            'Servicios más vendidos',
            'Agenda de citas programadas',
            'Historial de mantenimientos',
        ],
    },
    {
        titulo: 'Rentas Activas',
        descripcion: 'Controla equipos rentados, renovaciones pendientes y vencimientos.',
        href: '/reportes?tab=rentas',
        categoria: 'rentas',
        highlights: [
            'Rentas vigentes',
            'Fechas de devolución',
            'Alertas de vencimiento',
        ],
    },
    {
        titulo: 'Compras y Costos',
        descripcion: 'Evalúa el gasto por periodo y la eficiencia de las compras.',
        href: '/reportes?tab=compras',
        categoria: 'finanzas',
        highlights: [
            'Historial de compras',
            'Comparativo por proveedor',
            'Tendencias de costos',
        ],
    },
    {
        titulo: 'Proveedores Estratégicos',
        descripcion: 'Analiza la dependencia y cumplimiento de los proveedores clave.',
        href: '/reportes?tab=proveedores',
        categoria: 'finanzas',
        highlights: [
            'Volumen por proveedor',
            'Pedidos pendientes',
            'Condiciones comerciales',
        ],
    },
    {
        titulo: 'Ganancias y KPIs',
        descripcion: 'Mide rentabilidad, márgenes y crecimiento por periodo.',
        href: '/reportes?tab=ganancias',
        categoria: 'finanzas',
        highlights: [
            'Margen bruto y neto',
            'Utilidad por periodo',
            'Tendencias de crecimiento',
        ],
    },
    {
        titulo: 'Talento y Técnicos',
        descripcion: 'Monitorea el desempeño del personal y su aportación al negocio.',
        href: '/reportes?tab=personal',
        categoria: 'personal',
        highlights: [
            'Rendimiento por técnico',
            'Horas trabajadas',
            'Comisiones generadas',
        ],
    },
    {
        titulo: 'Reporte de Técnicos',
        descripcion: 'Acceso directo al informe detallado por técnico y periodo.',
        href: '/reportes/tecnicos',
        categoria: 'personal',
        highlights: [
            'KPIs individuales',
            'Servicios atendidos',
            'Alertas de desempeño',
        ],
        isNew: true,
    },
    {
        titulo: 'Auditoría y Bitácora',
        descripcion: 'Revisa la actividad del sistema y los cambios críticos registrados.',
        href: '/reportes?tab=auditoria',
        categoria: 'auditoria',
        highlights: [
            'Acciones por usuario',
            'Eventos de seguridad',
            'Exportación de bitácora',
        ],
    },
];

// Función para obtener el color de la categoría
const getCategoriaColor = (categoria) => {
    const colores = {
        ventas: 'bg-blue-600',
        pagos: 'bg-green-600',
        clientes: 'bg-purple-600',
        inventario: 'bg-orange-600',
        servicios: 'bg-indigo-600',
        rentas: 'bg-teal-600',
        finanzas: 'bg-yellow-600',
        personal: 'bg-red-600',
        auditoria: 'bg-gray-600',
    };
    return colores[categoria] || 'bg-gray-600';
};

const getCategoriaButtonColor = (categoria) => {
    const colores = {
        ventas: 'bg-blue-600 hover:bg-blue-700',
        pagos: 'bg-green-600 hover:bg-green-700',
        clientes: 'bg-purple-600 hover:bg-purple-700',
        inventario: 'bg-orange-600 hover:bg-orange-700',
        servicios: 'bg-indigo-600 hover:bg-indigo-700',
        rentas: 'bg-teal-600 hover:bg-teal-700',
        finanzas: 'bg-yellow-600 hover:bg-yellow-700',
        personal: 'bg-red-600 hover:bg-red-700',
        auditoria: 'bg-gray-600 hover:bg-gray-700',
    };
    return colores[categoria] || 'bg-gray-600 hover:bg-gray-700';
};

const getCategoriaBadgeColor = (categoria) => {
    const colores = {
        ventas: 'bg-blue-100 text-blue-700',
        pagos: 'bg-green-100 text-green-700',
        clientes: 'bg-purple-100 text-purple-700',
        inventario: 'bg-orange-100 text-orange-700',
        servicios: 'bg-indigo-100 text-indigo-700',
        rentas: 'bg-teal-100 text-teal-700',
        finanzas: 'bg-yellow-100 text-yellow-700',
        personal: 'bg-red-100 text-red-700',
        auditoria: 'bg-gray-200 text-gray-700',
    };
    return colores[categoria] || 'bg-gray-200 text-gray-700';
};

// Función para obtener el ícono de la categoría
const getCategoriaIcon = (categoria) => {
    const iconos = {
        ventas: ShoppingCartIcon,
        pagos: CashIcon,
        clientes: UsersIcon,
        inventario: ArchiveIcon,
        servicios: WrenchIcon,
        rentas: HandIcon,
        finanzas: ChartBarIcon,
        personal: UserGroupIcon,
        auditoria: DocumentIcon,
    };
    return iconos[categoria] || DocumentIcon;
};

// Componentes de íconos (usando Heroicons)
const ShoppingCartIcon = {
    template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.1 5H19M7 13l-1.1 5M7 13v8a2 2 0 002 2h10a2 2 0 002-2v-3"/></svg>`
};

const CashIcon = {
    template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7l4 0m0 4l4 0"/></svg>`
};

const UsersIcon = {
    template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/></svg>`
};

const ArchiveIcon = {
    template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>`
};

const WrenchIcon = {
    template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>`
};

const HandIcon = {
    template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11.5V14m0-2.5v-6a1.5 1.5 0 113 0m-3 6v2.5m0-2.5c1.5 0 3-1.5 3-3.5s-1.5-3.5-3-3.5-3 1.5-3 3.5 1.5 3.5 3 3.5z"/></svg>`
};

const ChartBarIcon = {
    template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>`
};

const UserGroupIcon = {
    template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>`
};

const DocumentIcon = {
    template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>`
};
</script>

<style scoped>
/* Estilos adicionales si son necesarios */
</style>
