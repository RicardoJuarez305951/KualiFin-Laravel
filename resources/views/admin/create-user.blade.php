<x-layouts.authenticated>
    @php
        $roles = [
            'promotor' => ['label' => 'Promotor', 'description' => 'Encargado de captación de clientes', 'icon' => '🎯'],
            'supervisor' => ['label' => 'Supervisor', 'description' => 'Supervisión de operaciones', 'icon' => '👁️'],
            'administrador' => ['label' => 'Administrador', 'description' => 'Gestión completa del sistema', 'icon' => '⚙️'],
            'ejecutivo' => ['label' => 'Ejecutivo', 'description' => 'Manejo de cartera y clientes', 'icon' => '💼'],
        ];
    @endphp

    <div class="min-h-screen bg-gray-50 p-6">
        <div class="max-w-4xl mx-auto space-y-8">
            <!-- Header -->
            <div class="text-center space-y-2">
                <h1 class="text-4xl font-bold text-gray-900">
                    Nuevo Empleado
                </h1>
                <p class="text-lg text-gray-600">
                    Registra un nuevo miembro del equipo
                </p>
            </div>

            <!-- Success Message -->
            @if(session('success'))
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center text-white">
                            ✓
                        </div>
                        <div>
                            <p class="font-medium text-green-800">¡Éxito!</p>
                            <p class="text-sm text-green-700">{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Form Card -->
            <div class="bg-white rounded-lg shadow-sm border">
                <div class="p-6 border-b">
                    <h2 class="text-xl font-semibold text-gray-900 flex items-center gap-2">
                        <span class="text-2xl">👤</span>
                        Información del Empleado
                    </h2>
                </div>
                
                <form method="POST" action="{{ route('admin.empleados.store') }}" class="p-6 space-y-6">
                    @csrf

                    <!-- Personal Information Section -->
                    <div class="space-y-6">
                        <div class="border-l-4 border-blue-500 pl-4">
                            <h3 class="text-lg font-medium text-gray-900 flex items-center gap-2">
                                <span class="text-xl">📋</span>
                                Datos Personales
                            </h3>
                            <p class="text-sm text-gray-600 mt-1">Información básica del empleado</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Name -->
                            <div class="space-y-2">
                                <label for="name" class="block text-sm font-medium text-gray-700">
                                    Nombre Completo <span class="text-red-500">*</span>
                                </label>
                                <input 
                                    type="text" 
                                    id="name" 
                                    name="name" 
                                    value="{{ old('name') }}"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors @error('name') border-red-500 @enderror"
                                    placeholder="Ej: Juan Carlos Pérez"
                                    required
                                >
                                @error('name')
                                    <p class="text-sm text-red-600 flex items-center gap-1">
                                        <span class="text-red-500">⚠️</span>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <!-- Phone -->
                            <div class="space-y-2">
                                <label for="telefono" class="block text-sm font-medium text-gray-700">
                                    Teléfono
                                </label>
                                <input 
                                    type="tel" 
                                    id="telefono" 
                                    name="telefono" 
                                    value="{{ old('telefono') }}"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors @error('telefono') border-red-500 @enderror"
                                    placeholder="Ej: +52 55 1234 5678"
                                >
                                @error('telefono')
                                    <p class="text-sm text-red-600 flex items-center gap-1">
                                        <span class="text-red-500">⚠️</span>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="space-y-2">
                            <label for="email" class="block text-sm font-medium text-gray-700">
                                Correo Electrónico <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="email" 
                                id="email" 
                                name="email" 
                                value="{{ old('email') }}"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors @error('email') border-red-500 @enderror"
                                placeholder="Ej: juan.perez@empresa.com"
                                required
                            >
                            @error('email')
                                <p class="text-sm text-red-600 flex items-center gap-1">
                                    <span class="text-red-500">⚠️</span>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>

                    <!-- Role Selection Section -->
                    <div class="space-y-6">
                        <div class="border-l-4 border-green-500 pl-4">
                            <h3 class="text-lg font-medium text-gray-900 flex items-center gap-2">
                                <span class="text-xl">🏢</span>
                                Rol en la Empresa
                            </h3>
                            <p class="text-sm text-gray-600 mt-1">Selecciona el rol que desempeñará</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($roles as $roleKey => $roleData)
                                <label class="relative cursor-pointer">
                                    <input 
                                        type="radio" 
                                        name="rol" 
                                        value="{{ $roleKey }}" 
                                        class="sr-only peer"
                                        {{ old('rol') === $roleKey ? 'checked' : '' }}
                                        required
                                    >
                                    <div class="p-4 border-2 border-gray-200 rounded-lg peer-checked:border-blue-500 peer-checked:bg-blue-50 hover:border-gray-300 transition-all">
                                        <div class="flex items-start gap-3">
                                            <span class="text-2xl">{{ $roleData['icon'] }}</span>
                                            <div class="flex-1">
                                                <h4 class="font-medium text-gray-900">{{ $roleData['label'] }}</h4>
                                                <p class="text-sm text-gray-600 mt-1">{{ $roleData['description'] }}</p>
                                            </div>
                                            <div class="w-4 h-4 border-2 border-gray-300 rounded-full peer-checked:border-blue-500 peer-checked:bg-blue-500 flex items-center justify-center">
                                                <div class="w-2 h-2 bg-white rounded-full opacity-0 peer-checked:opacity-100"></div>
                                            </div>
                                        </div>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                        @error('rol')
                            <p class="text-sm text-red-600 flex items-center gap-1">
                                <span class="text-red-500">⚠️</span>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Security Section -->
                    <div class="space-y-6">
                        <div class="border-l-4 border-yellow-500 pl-4">
                            <h3 class="text-lg font-medium text-gray-900 flex items-center gap-2">
                                <span class="text-xl">🔐</span>
                                Credenciales de Acceso
                            </h3>
                            <p class="text-sm text-gray-600 mt-1">Contraseña para acceder al sistema</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Password -->
                            <div class="space-y-2">
                                <label for="password" class="block text-sm font-medium text-gray-700">
                                    Contraseña <span class="text-red-500">*</span>
                                </label>
                                <input 
                                    type="password" 
                                    id="password" 
                                    name="password"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors @error('password') border-red-500 @enderror"
                                    placeholder="Mínimo 8 caracteres"
                                    required
                                >
                                @error('password')
                                    <p class="text-sm text-red-600 flex items-center gap-1">
                                        <span class="text-red-500">⚠️</span>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <!-- Confirm Password -->
                            <div class="space-y-2">
                                <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
                                    Confirmar Contraseña <span class="text-red-500">*</span>
                                </label>
                                <input 
                                    type="password" 
                                    id="password_confirmation" 
                                    name="password_confirmation"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                    placeholder="Repite la contraseña"
                                    required
                                >
                            </div>
                        </div>

                        <!-- Password Requirements -->
                        <div class="bg-gray-50 rounded-lg p-4 border">
                            <h4 class="text-sm font-medium text-gray-700 mb-2 flex items-center gap-2">
                                <span>ℹ️</span>
                                Requisitos de la contraseña:
                            </h4>
                            <ul class="text-sm text-gray-600 space-y-1">
                                <li class="flex items-center gap-2">
                                    <span class="w-1.5 h-1.5 bg-gray-400 rounded-full"></span>
                                    Mínimo 8 caracteres
                                </li>
                                <li class="flex items-center gap-2">
                                    <span class="w-1.5 h-1.5 bg-gray-400 rounded-full"></span>
                                    Se recomienda incluir mayúsculas, minúsculas y números
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-col sm:flex-row gap-4 pt-6 border-t">
                        <button 
                            type="submit"
                            class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-6 rounded-lg transition-colors duration-200 flex items-center justify-center gap-2"
                        >
                            <span class="text-lg">✅</span>
                            Administrar Empleado
                        </button>
                        
                        <a 
                            href="{{ route('admin.index') }}" 
                            class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-3 px-6 rounded-lg transition-colors duration-200 flex items-center justify-center gap-2"
                        >
                            <span class="text-lg">↩️</span>
                            Volver al Panel
                        </a>
                    </div>
                </form>
            </div>

            <!-- Quick Stats -->
            <div class="bg-white rounded-lg shadow-sm border">
                <div class="p-6 border-b">
                    <h2 class="text-xl font-semibold text-gray-900 flex items-center gap-2">
                        <span class="text-2xl">📊</span>
                        Estadísticas del Equipo
                    </h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div class="space-y-3">
                            <div class="flex items-center gap-2">
                                <span class="text-xl">🎯</span>
                                <span class="font-medium text-sm">Promotores</span>
                            </div>
                            <div>
                                <div class="text-2xl font-bold">4</div>
                                <div class="text-sm text-gray-600">Activos</div>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <div class="flex items-center gap-2">
                                <span class="text-xl">👁️</span>
                                <span class="font-medium text-sm">Supervisores</span>
                            </div>
                            <div>
                                <div class="text-2xl font-bold">2</div>
                                <div class="text-sm text-gray-600">Activos</div>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <div class="flex items-center gap-2">
                                <span class="text-xl">💼</span>
                                <span class="font-medium text-sm">Ejecutivos</span>
                            </div>
                            <div>
                                <div class="text-2xl font-bold">3</div>
                                <div class="text-sm text-gray-600">Activos</div>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <div class="flex items-center gap-2">
                                <span class="text-xl">⚙️</span>
                                <span class="font-medium text-sm">Administradores</span>
                            </div>
                            <div>
                                <div class="text-2xl font-bold">1</div>
                                <div class="text-sm text-gray-600">Activo</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.authenticated>