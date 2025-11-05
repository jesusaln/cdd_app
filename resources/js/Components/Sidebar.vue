<template>
  <aside
    :class="{
      'w-64': !props.isSidebarCollapsed,
      'w-20': props.isSidebarCollapsed
    }"
    class="bg-gradient-to-b from-gray-800 to-gray-900 text-white fixed left-0 top-0 bottom-0 z-20 transition-all duration-300 ease-in-out overflow-y-auto shadow-2xl border-r border-gray-700 flex flex-col"
    role="navigation"
    aria-label="Barra lateral"
  >
    <!-- Header -->
    <div class="flex items-center justify-between p-4 border-b border-gray-700 bg-gray-800/50 backdrop-blur-sm flex-shrink-0">
      <Link
        href="/panel"
        class="flex items-center group overflow-hidden"
        :class="{'justify-center w-full': props.isSidebarCollapsed}"
        :title="props.isSidebarCollapsed ? 'Ir al Panel' : null"
      >
        <img
          src="/images/logo.png"
          alt="Logo"
          class="h-10 w-auto transition-transform duration-200 group-hover:scale-105"
          :class="{'mx-auto': props.isSidebarCollapsed}"
        />
        <span
          v-if="!props.isSidebarCollapsed"
          class="ml-3 text-xl font-semibold whitespace-nowrap overflow-hidden"
        >
          <!-- Climas del Desierto -->
        </span>
      </Link>

      <button
        v-if="!isMobile"
        @click="toggleSidebar"
        class="p-2 rounded-lg hover:bg-gray-700 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 ml-auto"
        :title="props.isSidebarCollapsed ? 'Expandir sidebar' : 'Contraer sidebar'"
        :aria-label="props.isSidebarCollapsed ? 'Expandir sidebar' : 'Contraer sidebar'"
      >
        <FontAwesomeIcon
          :icon="props.isSidebarCollapsed ? 'fa-solid fa-chevron-right' : 'fa-solid fa-chevron-left'"
          class="text-gray-300 hover:text-white transition-colors duration-200"
        />
      </button>
    </div>

    <!-- Navegación -->
    <nav class="flex-1 overflow-y-auto pt-4">
      <div class="px-2 pb-4">
        <!-- Dashboard visible para todos los roles -->
        <div class="mb-4">
          <h3
            v-show="!props.isSidebarCollapsed"
            class="px-3 mb-2 text-xs font-semibold text-gray-400 uppercase tracking-wider flex items-center gap-2"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
            Dashboard
          </h3>
          <div v-show="props.isSidebarCollapsed" class="px-3 mb-2 flex justify-center">
            <div class="w-8 h-8 flex items-center justify-center text-gray-400 hover:text-white hover:bg-gray-700/50 rounded-md transition-colors duration-200" title="Dashboard">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
              </svg>
            </div>
          </div>
          <ul class="space-y-1">
            <NavLink href="/panel" icon="tachometer-alt" :collapsed="props.isSidebarCollapsed" :title="props.isSidebarCollapsed ? 'Panel' : null">
              Panel
            </NavLink>
          </ul>
        </div>

        <!-- Ventas -->
        <div class="mb-4">
          <div
            @click="toggleAccordion('ventas')"
            class="flex items-center justify-between px-3 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider cursor-pointer hover:text-white hover:bg-gray-700/50 rounded-md transition-colors duration-200"
          >
            <div class="flex items-center gap-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <span>Ventas</span>
            </div>
            <svg
              :class="accordionStates.ventas ? 'rotate-90' : ''"
              class="w-3 h-3 transition-transform duration-200"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </div>
          <div v-show="accordionStates.ventas" class="mt-2 space-y-1">
            <NavLink href="/clientes" icon="users" :collapsed="props.isSidebarCollapsed" :title="props.isSidebarCollapsed ? 'Clientes' : null">
              Clientes
            </NavLink>
            <NavLink href="/citas" icon="calendar-alt" :collapsed="props.isSidebarCollapsed" :title="props.isSidebarCollapsed ? 'Citas Agendadas' : null">
              Citas Agendadas
            </NavLink>
            <NavLink href="/cotizaciones" icon="file-alt" :collapsed="props.isSidebarCollapsed" :title="props.isSidebarCollapsed ? 'Cotizaciones' : null">
              Cotizaciones
            </NavLink>
            <NavLink href="/pedidos" icon="truck" :collapsed="props.isSidebarCollapsed" :title="props.isSidebarCollapsed ? 'Pedidos' : null">
              Pedidos
            </NavLink>
            <NavLink href="/ventas" icon="dollar-sign" :collapsed="props.isSidebarCollapsed" :title="props.isSidebarCollapsed ? 'Ventas Realizadas' : null">
              Ventas Realizadas
            </NavLink>
          </div>
        </div>

        <!-- Cobranza (solo admin) -->
        <div v-if="isAdmin" class="mb-4">
          <div
            @click="toggleAccordion('cobranza')"
            class="flex items-center justify-between px-3 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider cursor-pointer hover:text-white hover:bg-gray-700/50 rounded-md transition-colors duration-200"
          >
            <div class="flex items-center gap-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <span>Cobranza</span>
            </div>
            <svg
              :class="accordionStates.cobranza ? 'rotate-90' : ''"
              class="w-3 h-3 transition-transform duration-200"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </div>
          <div v-show="accordionStates.cobranza" class="mt-2 space-y-1">
            <NavLink href="/cuentas-por-cobrar" icon="file-invoice-dollar" :collapsed="props.isSidebarCollapsed" :title="props.isSidebarCollapsed ? 'Cuentas por Cobrar' : null">
              Cuentas por Cobrar
            </NavLink>
            <NavLink href="/entregas-dinero" icon="dollar-sign" :collapsed="props.isSidebarCollapsed" :title="props.isSidebarCollapsed ? 'Entregas de Dinero' : null">
              Entregas de Dinero
            </NavLink>
          </div>
        </div>

        <!-- Préstamos (solo para admin y user) -->
        <div v-if="!isVentasRole" class="mb-4">
          <div
            @click="toggleAccordion('prestamos')"
            class="flex items-center justify-between px-3 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider cursor-pointer hover:text-white hover:bg-gray-700/50 rounded-md transition-colors duration-200"
          >
            <div class="flex items-center gap-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <span>Préstamos</span>
            </div>
            <svg
              :class="accordionStates.prestamos ? 'rotate-90' : ''"
              class="w-3 h-3 transition-transform duration-200"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </div>
          <div v-show="accordionStates.prestamos" class="mt-2 space-y-1">
            <NavLink href="/prestamos" icon="money-bill-wave" :collapsed="props.isSidebarCollapsed" :title="props.isSidebarCollapsed ? 'Lista de Préstamos' : null">
              Lista de Préstamos
            </NavLink>
            <NavLink href="/prestamos/create" icon="plus" :collapsed="props.isSidebarCollapsed" :title="props.isSidebarCollapsed ? 'Crear Préstamo' : null">
              Crear Préstamo
            </NavLink>
            <NavLink href="/pagos" :collapsed="props.isSidebarCollapsed" :title="props.isSidebarCollapsed ? 'Pagos de Préstamos' : null">
              💳 Pagos de Préstamos
            </NavLink>
          </div>
        </div>

        <!-- Compras (solo para admin y user) -->
        <div v-if="!isVentasRole" class="mb-4">
          <div
            @click="toggleAccordion('compras')"
            class="flex items-center justify-between px-3 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider cursor-pointer hover:text-white hover:bg-gray-700/50 rounded-md transition-colors duration-200"
          >
            <div class="flex items-center gap-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
              </svg>
              <span>Compras</span>
            </div>
            <svg
              :class="accordionStates.compras ? 'rotate-90' : ''"
              class="w-3 h-3 transition-transform duration-200"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </div>
          <div v-show="accordionStates.compras" class="mt-2 space-y-1">
            <NavLink href="/proveedores" icon="truck" :collapsed="props.isSidebarCollapsed" :title="props.isSidebarCollapsed ? 'Proveedores' : null">
              Proveedores
            </NavLink>
            <NavLink href="/ordenescompra" icon="file-invoice-dollar" :collapsed="props.isSidebarCollapsed" :title="props.isSidebarCollapsed ? 'Órdenes de Compra' : null">
              Órdenes de Compra
            </NavLink>
            <NavLink href="/compras" icon="cart-shopping" :collapsed="props.isSidebarCollapsed" :title="props.isSidebarCollapsed ? 'Compras a Proveedores' : null">
              Compras a Proveedores
            </NavLink>
            <NavLink href="/cuentas-por-pagar" icon="file-invoice-dollar" :collapsed="props.isSidebarCollapsed" :title="props.isSidebarCollapsed ? 'Cuentas por Pagar' : null">
              Cuentas por Pagar
            </NavLink>
          </div>
        </div>

        <!-- Rentas PDV (solo para admin y user) -->
        <div v-if="!isVentasRole" class="mb-4">
          <div
            @click="toggleAccordion('rentas')"
            class="flex items-center justify-between px-3 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider cursor-pointer hover:text-white hover:bg-gray-700/50 rounded-md transition-colors duration-200"
          >
            <div class="flex items-center gap-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
              <span>Rentas PDV</span>
            </div>
            <svg
              :class="accordionStates.rentas ? 'rotate-90' : ''"
              class="w-3 h-3 transition-transform duration-200"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </div>
          <div v-show="accordionStates.rentas" class="mt-2 space-y-1">
            <NavLink href="/rentas" icon="file-contract" :collapsed="props.isSidebarCollapsed" :title="props.isSidebarCollapsed ? 'Rentas' : null">
              Rentas
            </NavLink>
            <NavLink href="/equipos" icon="laptop" :collapsed="props.isSidebarCollapsed" :title="props.isSidebarCollapsed ? 'Equipos' : null">
              Equipos
            </NavLink>
            <NavLink href="/cobranza" icon="dollar-sign" :collapsed="props.isSidebarCollapsed" :title="props.isSidebarCollapsed ? 'Cobranza' : null">
              Cobranza
            </NavLink>
          </div>
        </div>

        <!-- Inventario (solo para admin y user) -->
        <div v-if="!isVentasRole" class="mb-4">
          <div
            @click="toggleAccordion('inventario')"
            class="flex items-center justify-between px-3 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider cursor-pointer hover:text-white hover:bg-gray-700/50 rounded-md transition-colors duration-200"
          >
            <div class="flex items-center gap-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
              </svg>
              <span>Inventario</span>
            </div>
            <svg
              :class="accordionStates.inventario ? 'rotate-90' : ''"
              class="w-3 h-3 transition-transform duration-200"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </div>
          <div v-show="accordionStates.inventario" class="mt-2 space-y-1">
            <NavLink href="/productos" icon="box" :collapsed="props.isSidebarCollapsed" :title="props.isSidebarCollapsed ? 'Productos' : null">
              Productos
            </NavLink>
            <NavLink href="/almacenes" icon="warehouse" :collapsed="props.isSidebarCollapsed" :title="props.isSidebarCollapsed ? 'Almacenes' : null">
              Almacenes
            </NavLink>
            <NavLink href="/traspasos" icon="exchange-alt" :collapsed="props.isSidebarCollapsed" :title="props.isSidebarCollapsed ? 'Traspasos' : null">
              Traspasos
            </NavLink>
            <NavLink href="/movimientos-inventario" icon="history" :collapsed="props.isSidebarCollapsed" :title="props.isSidebarCollapsed ? 'Movimientos' : null">
              Movimientos
            </NavLink>
            <NavLink href="/ajustes-inventario" icon="cogs" :collapsed="props.isSidebarCollapsed" :title="props.isSidebarCollapsed ? 'Ajustes' : null">
              Ajustes
            </NavLink>
            <NavLink href="/movimientos-manuales" icon="exchange-alt" :collapsed="props.isSidebarCollapsed" :title="props.isSidebarCollapsed ? 'Mov. Manuales' : null">
              Mov. Manuales
            </NavLink>
          </div>
        </div>

        <!-- Catálogos (solo para admin y user) -->
        <div v-if="!isVentasRole" class="mb-4">
          <div
            @click="toggleAccordion('catalogos')"
            class="flex items-center justify-between px-3 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider cursor-pointer hover:text-white hover:bg-gray-700/50 rounded-md transition-colors duration-200"
          >
            <div class="flex items-center gap-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
              </svg>
              <span>Catálogos</span>
            </div>
            <svg
              :class="accordionStates.catalogos ? 'rotate-90' : ''"
              class="w-3 h-3 transition-transform duration-200"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </div>
          <div v-show="accordionStates.catalogos" class="mt-2 space-y-1">
            <NavLink href="/categorias" icon="tags" :collapsed="props.isSidebarCollapsed" :title="props.isSidebarCollapsed ? 'Categorías' : null">
              Categorías
            </NavLink>
            <NavLink href="/marcas" icon="trademark" :collapsed="props.isSidebarCollapsed" :title="props.isSidebarCollapsed ? 'Marcas de Productos' : null">
              Marcas de Productos
            </NavLink>
            <NavLink href="/servicios" icon="wrench" :collapsed="props.isSidebarCollapsed" :title="props.isSidebarCollapsed ? 'Servicios' : null">
              Servicios
            </NavLink>
          </div>
        </div>

        <!-- Reportes (solo para admin y user) -->
        <div v-if="!isVentasRole" class="mb-4">
          <div
            @click="toggleAccordion('reportes')"
            class="flex items-center justify-between px-3 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider cursor-pointer hover:text-white hover:bg-gray-700/50 rounded-md transition-colors duration-200"
          >
            <div class="flex items-center gap-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
              </svg>
              <span>Reportes</span>
            </div>
            <svg
              :class="accordionStates.reportes ? 'rotate-90' : ''"
              class="w-3 h-3 transition-transform duration-200"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </div>
          <div v-show="accordionStates.reportes" class="mt-2 space-y-1">
            <NavLink href="/reportes" icon="chart-bar" :collapsed="props.isSidebarCollapsed" :title="props.isSidebarCollapsed ? 'Centro de Reportes' : null">
              Centro de Reportes
            </NavLink>
          </div>
        </div>

                <!-- Gestión de Herramientas (solo para admin y user) -->
        <div v-if="!isVentasRole" class="mb-4">
          <div
            @click="toggleAccordion('gestion_herramientas')"
            class="flex items-center justify-between px-3 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider cursor-pointer hover:text-white hover:bg-gray-700/50 rounded-md transition-colors duration-200"
          >
            <div class="flex items-center gap-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M5 7l2 12h10l2-12M10 11h4m-6 0h.01M10 15h4m-6 0h.01" />
              </svg>
              <span>Gestión de Herramientas</span>
            </div>
            <svg
              :class="accordionStates.gestion_herramientas ? 'rotate-90' : ''"
              class="w-3 h-3 transition-transform duration-200"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </div>
          <div v-show="accordionStates.gestion_herramientas" class="mt-2 space-y-1">
            <NavLink href="/herramientas" icon="toolbox" :collapsed="props.isSidebarCollapsed" :title="props.isSidebarCollapsed ? 'Catálogo de Herramientas' : null">
              Catálogo de Herramientas
            </NavLink>
            <NavLink href="/tecnicos" icon="user-cog" :collapsed="props.isSidebarCollapsed" :title="props.isSidebarCollapsed ? 'Catálogo de Técnicos' : null">
              Catálogo de Técnicos
            </NavLink>
            <NavLink href="/herramientas/gestion" icon="wrench" :collapsed="props.isSidebarCollapsed" :title="props.isSidebarCollapsed ? 'Gestión de Herramientas' : null">
              Gestión de Herramientas
            </NavLink>
          </div>
        </div>

        <!-- Taller (solo para admin y user) -->
        <div v-if="!isVentasRole" class="mb-4">
          <div
            @click="toggleAccordion('taller')"
            class="flex items-center justify-between px-3 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider cursor-pointer hover:text-white hover:bg-gray-700/50 rounded-md transition-colors duration-200"
          >
            <div class="flex items-center gap-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a2 2 0 012-2h4a2 2 0 012 2v4m-6 9l6-6m0 0v6m0-6h-6M3 12h18M3 12l2-2m0 0l2 2M3 10l2 2m0 0l2-2M21 12l-2 2m0 0l-2-2m2 2l2-2" />
              </svg>
              <span>Taller</span>
            </div>
            <svg
              :class="accordionStates.taller ? 'rotate-90' : ''"
              class="w-3 h-3 transition-transform duration-200"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </div>
          <div v-show="accordionStates.taller" class="mt-2 space-y-1">
            <NavLink href="/carros" icon="car" :collapsed="props.isSidebarCollapsed" :title="props.isSidebarCollapsed ? 'Vehículos' : null">
              Vehículos
            </NavLink>
            <NavLink href="/mantenimientos" icon="wrench" :collapsed="props.isSidebarCollapsed" :title="props.isSidebarCollapsed ? 'Mantenimientos' : null">
              Mantenimientos
            </NavLink>
          </div>
        </div><!-- Administración (solo para admin y user) -->
        <div v-if="!isVentasRole" class="mb-4">
          <div
            @click="toggleAccordion('administracion')"
            class="flex items-center justify-between px-3 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider cursor-pointer hover:text-white hover:bg-gray-700/50 rounded-md transition-colors duration-200"
          >
            <div class="flex items-center gap-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
              <span>Administración</span>
            </div>
            <svg
              :class="accordionStates.administracion ? 'rotate-90' : ''"
              class="w-3 h-3 transition-transform duration-200"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </div>
          <div v-show="accordionStates.administracion" class="mt-2 space-y-1">
            <NavLink :href="routeOr('/backup')" icon="database" :collapsed="props.isSidebarCollapsed" :title="props.isSidebarCollapsed ? 'Copia de Seguridad' : null">
              Copia de Seguridad
            </NavLink>
            <NavLink href="/empresa/configuracion" icon="cog" :collapsed="props.isSidebarCollapsed" :title="props.isSidebarCollapsed ? 'Configuración de Empresa' : null">
              Configuración de Empresa
            </NavLink>
          </div>
        </div>

        <!-- Usuario (solo para admin y user) -->
        <div v-if="!isVentasRole" class="mb-4">
          <div
            @click="toggleAccordion('usuario')"
            class="flex items-center justify-between px-3 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider cursor-pointer hover:text-white hover:bg-gray-700/50 rounded-md transition-colors duration-200"
          >
            <div class="flex items-center gap-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
              <span>Usuario</span>
            </div>
            <svg
              :class="accordionStates.usuario ? 'rotate-90' : ''"
              class="w-3 h-3 transition-transform duration-200"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </div>
          <div v-show="accordionStates.usuario" class="mt-2 space-y-1">
            <NavLink href="/usuarios" icon="user" :collapsed="props.isSidebarCollapsed" :title="props.isSidebarCollapsed ? 'Usuarios' : null">
              Usuarios
            </NavLink>
            <NavLink href="/bitacora" icon="clipboard-list" :collapsed="props.isSidebarCollapsed" :title="props.isSidebarCollapsed ? 'Bitácora' : null">
              Bitácora
            </NavLink>
            <NavLink href="/mis-vacaciones" :collapsed="props.isSidebarCollapsed" :title="props.isSidebarCollapsed ? 'Mis Vacaciones' : null">
              Mis Vacaciones
            </NavLink>
            <NavLink href="/vacaciones" :collapsed="props.isSidebarCollapsed" :title="props.isSidebarCollapsed ? 'Gestión de Vacaciones' : null">
              Gestión de Vacaciones
            </NavLink>
            <NavLink href="/vacaciones/create" :collapsed="props.isSidebarCollapsed" :title="props.isSidebarCollapsed ? 'Nueva Solicitud' : null">
              Nueva Solicitud
            </NavLink>
            <NavLink href="/registro-vacaciones" :collapsed="props.isSidebarCollapsed" :title="props.isSidebarCollapsed ? 'Registro de Vacaciones' : null">
              Registro de Vacaciones
            </NavLink>
          </div>
        </div>

      </div>
    </nav>

    <!-- Usuario -->
    <div
      class="border-t border-gray-700 p-4 bg-gray-800/50 backdrop-blur-sm flex-shrink-0"
      :class="{'flex justify-center': props.isSidebarCollapsed}"
    >
      <div class="flex items-center" :class="{'w-full justify-center': props.isSidebarCollapsed, 'space-x-3': !props.isSidebarCollapsed}">
        <img
          :src="props.usuario.profile_photo_url"
          :alt="props.usuario.name"
          class="w-10 h-10 rounded-full border-2 border-gray-600 object-cover flex-shrink-0"
        />
        <div v-show="!props.isSidebarCollapsed" class="flex-1 min-w-0">
          <p class="text-sm font-medium text-gray-100 truncate">
            {{ props.usuario.name }}
          </p>
          <p class="text-xs text-gray-400 truncate">
            {{ props.usuario.email }}
          </p>
        </div>
      </div>
    </div>
  </aside>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import NavLink from '@/Components/NavLink.vue';

// Props
const props = defineProps({
  usuario: {
    type: Object,
    required: true
  },
  isSidebarCollapsed: {
    type: Boolean,
    default: false
  },
  isMobile: {
    type: Boolean,
    default: false
  }
});

// Computed para determinar si el usuario tiene rol de ventas (y no es admin)
const isVentasRole = computed(() => {
  if (!props.usuario.roles) return false;
  const hasAdmin = props.usuario.roles.some(role => role.name === 'admin');
  const hasVentas = props.usuario.roles.some(role => role.name === 'ventas');
  return hasVentas && !hasAdmin; // Solo ventas si tiene ventas pero no admin
});

// Computed para saber si es admin
const isAdmin = computed(() => {
  if (!props.usuario) return false;
  if (props.usuario.is_admin) return true;
  if (props.usuario.roles) {
    return props.usuario.roles.some(role => role.name === 'admin');
  }
  return false;
});

// Emits
const emit = defineEmits(['toggleSidebar']);

// Estado del acordeÃƒÆ’Ã‚Â³n
const accordionStates = ref({
  ventas: false,
  prestamos: false,
  compras: false,
  catalogos: false,
  inventario: false,
  administracion: false,
  usuario: false,
  clientes_citas: false,
  reportes: false,
  rentas: false,
  taller: false,
  gestion_herramientas: false,
  cobranza: false
});

// FunciÃƒÆ’Ã‚Â³n para alternar acordeÃƒÆ’Ã‚Â³n
const toggleAccordion = (section) => {
  // Si el sidebar estÃƒÆ’Ã‚Â¡ colapsado, expandir la secciÃƒÆ’Ã‚Â³n
  if (props.isSidebarCollapsed) {
    Object.keys(accordionStates.value).forEach(key => {
      accordionStates.value[key] = key === section;
    });
  } else {
    // Si no estÃƒÆ’Ã‚Â¡ colapsado, alternar normalmente
    accordionStates.value[section] = !accordionStates.value[section];
  }
};

// FunciÃƒÆ’Ã‚Â³n para determinar la secciÃƒÆ’Ã‚Â³n actual basada en la URL
const getCurrentSection = () => {
  const path = window.location.pathname;

  // Verificar inventario primero (mÃƒÆ’Ã‚Â¡s especÃƒÆ’Ã‚Â­fico)
  if (path.includes('/productos') || path.includes('/traspasos') || path.includes('/movimientos-inventario') || path.includes('/ajustes-inventario') || path.includes('/movimientos-manuales')) {
    return 'inventario';
  } else if (path.includes('/prestamos')) {
    return 'prestamos';
  } else if (path.includes('/clientes') || path.includes('/citas')) {
    return 'ventas'; // Clientes y citas ahora están en Ventas
  } else if (path.includes('/cotizaciones') || path.includes('/pedidos') || path.includes('/ventas')) {
    return 'ventas';
  } else if (path.includes('/cuentas-por-cobrar') || path.includes('/entregas-dinero')) {
    return 'cobranza';
  } else if (path.includes('/compras') || path.includes('/ordenescompra') || path.includes('/proveedores') || path.includes('/cuentas-por-pagar')) {
    return 'compras';
  } else if (path.includes('/servicios') || path.includes('/categorias') || path.includes('/marcas') || path.includes('/almacenes')) {
    return 'catalogos';
  } else if (path.includes('/backup') || path.includes('/empresa/configuracion')) {
    return 'administracion';
  } else if (path.includes('/usuarios') || path.includes('/bitacora') || path.includes('/vacaciones') || path.includes('/mis-vacaciones')) {
    return 'usuario';
  } else if (path.includes('/reportes')) {
    return 'reportes';
  } else if (path.includes('/rentas') || path.includes('/equipos') || path.includes('/cobranza')) {
    return 'rentas';
  } else if (path.includes('/carros') || path.includes('/mantenimientos')) {
    return 'taller';
  } else if (path.includes('/tecnicos')) {
    return 'gestion_herramientas';
  } else if (path.includes('/herramientas/gestion') || path.includes('/herramientas-dashboard') || path.includes('/herramientas-mantenimiento') || path.includes('/herramientas-alertas') || path.includes('/herramientas-reportes')) {
    return 'gestion_herramientas';
  } else if (path === '/herramientas') {
    return 'gestion_herramientas';
  }

  return null;
};

// Auto-expandir la secciÃƒÆ’Ã‚Â³n actual cuando se carga la pÃƒÆ’Ã‚Â¡gina
const autoExpandCurrentSection = () => {
  const currentSection = getCurrentSection();
  if (currentSection) {
    accordionStates.value[currentSection] = true;
  }
};

// Helper para tolerar ausencia de Ziggy route()
const routeOr = (fallback) => {
  try {
    // si Ziggy estÃƒÆ’Ã‚Â¡ disponible
    if (typeof route === 'function') return route('backup.index');
    return fallback;
  } catch {
    return fallback;
  }
};

const toggleSidebar = () => {
  emit('toggleSidebar');
};

// Lifecycle hooks
onMounted(() => {
  // Auto-expandir la secciÃƒÆ’Ã‚Â³n actual cuando se carga la pÃƒÆ’Ã‚Â¡gina
  autoExpandCurrentSection();
});

// Exponer si necesitas manipular desde fuera
defineExpose({
  toggleSidebar,
  toggleAccordion,
  autoExpandCurrentSection,
  isSidebarCollapsed: props.isSidebarCollapsed,
  accordionStates
});
</script>

<style scoped>
/* Scrollbar personalizado */
aside::-webkit-scrollbar { width: 4px; }
aside::-webkit-scrollbar-track { background: rgba(55, 65, 81, 0.5); }
aside::-webkit-scrollbar-thumb { background: rgba(156, 163, 175, 0.5); border-radius: 2px; }
aside::-webkit-scrollbar-thumb:hover { background: rgba(156, 163, 175, 0.7); }

/* Suaves */
.transition-opacity { transition: opacity 0.3s ease-in-out; }

/* Animaciones del acordeÃƒÆ’Ã‚Â³n */
.accordion-section {
  transition: all 0.3s ease-in-out;
}

.accordion-section:hover {
  background-color: rgba(55, 65, 81, 0.3);
}

.accordion-chevron {
  transition: transform 0.2s ease-in-out;
}

.accordion-chevron.rotated {
  transform: rotate(90deg);
}

/* Responsive mÃƒÆ’Ã‚Â³vil */
@media (max-width: 768px) {
  aside {
    position: fixed;
    z-index: 50;
    width: 4rem; /* w-16 */
    transform: translateX(-100%); /* oculto por defecto */
  }
  aside.w-64 {
    transform: translateX(0%); /* visible cuando expandes */
  }
}
</style>



