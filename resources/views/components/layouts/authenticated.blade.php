<!DOCTYPE html>
<html lang="es" x-data="{ sidebarOpen: false }" :class="sidebarOpen ? 'md:pl-64' : 'md:pl-20'">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? config('app.name', 'Laravel') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="//unpkg.com/alpinejs"></script>
</head>
<body class="min-h-screen bg-slate-50 font-sans text-slate-800 flex flex-col">
    <!-- Header -->
    <header class="fixed top-0 left-0 right-0 z-50 flex items-center h-16 px-6 bg-white shadow-sm border-b border-slate-200">
        <div class="flex items-center w-full">
            <button
                @click="sidebarOpen = !sidebarOpen"
                :aria-label="sidebarOpen ? 'Cerrar menú' : 'Abrir menú'"
                class="mr-4 p-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition-colors duration-200"
                type="button"
            >
                <template x-if="sidebarOpen">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </template>
                <template x-if="!sidebarOpen">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </template>
            </button>
            <img src="/images/Logo.png" alt="Logo" class="h-10 w-auto object-contain" />
            <div class="ml-auto flex items-center gap-4">
                <div class="hidden md:block text-right">
                    <p class="text-sm font-semibold text-slate-700">{{ Auth::user()->name }}</p>
                    <p class="text-xs text-slate-500">Bienvenido de vuelta</p>
                </div>
                <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center text-white font-bold">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </div>
            </div>
        </div>
    </header>

    @if (isset($header))
        <div class="fixed top-16 left-0 right-0 z-40 bg-white border-b border-slate-200 px-6 py-4 shadow-sm">
            {{ $header }}
        </div>
    @endif

    <!-- Mobile Backdrop -->
    <div x-show="sidebarOpen" class="fixed inset-0 z-30 bg-black/50 md:hidden" @click="sidebarOpen = false"></div>

    <div class="flex flex-1 pt-16" :class="{'pt-24': Boolean(@js(isset($header)))}">
        <!-- Sidebar -->
        <aside
            class="fixed top-16 bottom-0 left-0 z-40 bg-white shadow-lg border-r border-slate-200 flex flex-col justify-between transition-all duration-200 ease-out"
            :class="sidebarOpen ? 'w-64 translate-x-0' : 'w-20 -translate-x-full md:translate-x-0'"
            :style="'top: ' + (Boolean(@js(isset($header))) ? '6rem' : '4rem')"
        >
            <nav class="flex flex-col gap-1 px-3 mt-6">
                <x-layouts.navlink route="dashboard" icon="🏠" text="Dashboard" />
                <x-layouts.navlink route="solicitud" icon="📋" text="Nueva Solicitud" />
                <x-layouts.navlink route="nuevoCliente" icon="👥" text="Nuevo Cliente" />
                <x-layouts.navlink route="panelRevision" icon="🔍" text="Panel de Revisión" />
                <x-layouts.navlink route="reportes" icon="📊" text="Reportes" />
                <x-layouts.navlink route="recreditoClientes" icon="🔄" text="Recrédito Clientes" />
                <x-layouts.navlink route="AdminDashboard" icon="🧑‍💼" text="Panel Administrativo" />
            </nav>
            <div x-show="sidebarOpen" class="px-3 pb-6 border-t border-slate-200 mt-4">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button
                        type="submit"
                        class="w-full flex items-center gap-2 px-3 py-2 text-sm font-medium text-red-600 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors duration-150"
                    >
                        <span class="text-base">🚪</span>
                        <span>Cerrar sesión</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main content -->
        <main
            class="flex-1 min-h-screen transition-all duration-200 ease-out pl-0 md:pl-20 pr-4"
            :class="sidebarOpen ? 'md:pl-64' : 'md:pl-20', Boolean(@js(isset($header))) ? 'pt-16' : 'pt-0'"
            tabindex="-1"
        >
            {{ $slot }}
        </main>
    </div>
</body>
</html>
