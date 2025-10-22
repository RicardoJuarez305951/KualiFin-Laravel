<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? config('app.name', 'Kualifin') }}</title>
    @vite(['resources/css/app.css', 'resources/css/authenticated.css', 'resources/js/app.js'])
    
</head>
<body 
    x-data="authenticated.init()"
    x-init="initSidebar()"
    class="min-h-screen bg-gray-100"
    :class="{'overflow-hidden': sidebarOpen}"
>
    <!-- Header -->
    <nav class="fixed top-0 z-50 w-full bg-white border-b border-gray-200">
        <div class="px-3 py-3 lg:px-5 lg:pl-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center justify-start">
                    <!-- Botón menú móvil -->
                    <button 
                        @click="toggleSidebar()"
                        class="p-2 text-gray-600 rounded-lg hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200 md:hidden"
                    >
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                    <!-- Botón colapsar sidebar -->
                    <button 
                        @click="toggleCollapse()"
                        class="hidden md:block p-2 text-gray-600 rounded-lg hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                    <img src="/images/Logo.png" alt="Logo" class="h-8 ml-3"/>
                </div>
                <div class="flex items-center">
                    <div class="hidden md:flex md:items-center md:ml-6">
                        <div class="text-right">
                            <p class="text-sm font-medium text-gray-900">{{ Auth::user()->name }}</p>
                            <p class="text-xs text-gray-500">Bienvenido</p>
                        </div>
                        <div class="ml-3 w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center text-white font-medium">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Sidebar -->
    <aside
        class="sidebar-transition fixed top-0 left-0 z-40 h-screen pt-20 bg-white border-r border-gray-200 md:translate-x-0"
        :class="{
            'translate-x-0 sidebar-expanded': sidebarOpen && !sidebarCollapsed,
            '-translate-x-full': !sidebarOpen && !sidebarCollapsed,
            'sidebar-expanded': !sidebarCollapsed && !sidebarOpen,
            'sidebar-collapsed': sidebarCollapsed,
        }"
    >
        <div class="h-full px-3 pb-4 overflow-y-auto bg-white">
            <ul class="space-y-2 font-medium">
                <x-layouts.navlink route="dashboard" icon="📊" text="Dashboard" />
                <x-layouts.navlink route="consulta.historica" icon="📜" text="Consulta Historica" />
                <x-layouts.navlink route="mobile.index" icon="📱" text="Vista Movil" />
                <x-layouts.navlink route="admin.index" icon="🏢" text="Panel Administrativo" />
                <x-layouts.navlink route="panelRevision" icon="📝" text="Panel de Revision" />
                <x-layouts.navlink route="preAprobacion" icon="✅" text="Pre Aprobacion" />
                <x-layouts.navlink route="reportes" icon="📄" text="Reportes" />

                {{-- Administrativo - accesos de escritorio --}}
                <x-layouts.navlink route="administrativo.clientes.index" icon="👥" text="Clientes" />
                <x-layouts.navlink route="administrativo.documentos.index" icon="📁" text="Documentos" />
                <x-layouts.navlink route="administrativo.parametros" icon="⚙️" text="Parametros" />
                <x-layouts.navlink route="administrativo.asignaciones" icon="🧩" text="Asignaciones" />
                <x-layouts.navlink route="administrativo.cartera_global" icon="🌐" text="Cartera Global" />
                <x-layouts.navlink route="administrativo.ventas_desembolsos" icon="💸" text="Ventas y Desembolsos" />
                <x-layouts.navlink route="administrativo.inversiones" icon="📈" text="Inversiones" />
                <x-layouts.navlink route="administrativo.auditoria_seguridad" icon="🔐" text="Auditoria y Seguridad" />
            </ul>
            
            <div class="pt-4 mt-4 space-y-2 border-t border-gray-200">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button
                        type="submit"
                        class="w-full flex items-center p-2 text-red-600 hover:bg-red-50 rounded-lg group"
                    >
                        <span class="text-xl">🚪</span>
                        <span class="ml-3" x-show="!sidebarCollapsed">Cerrar sesión</span>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Main content -->
    <div class="content-transition" 
        :class="{
            'md:ml-64': !sidebarCollapsed,
            'md:ml-20': sidebarCollapsed,
            'p-4 pt-20': true
        }"
    >
        @if (isset($header))
            <div class="mb-4 p-4 bg-white rounded-lg shadow-sm">
                {{ $header }}
            </div>
        @endif

        {{ $slot }}
    </div>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @stack('scripts')
</body>
</html>

