<x-guest-layout>
    <div class="min-h-screen bg-slate-50 flex items-center justify-center px-4">
        <div class="w-full max-w-md">
            <div class="bg-white rounded-2xl shadow-lg border border-slate-200 p-8">
                <!-- Header -->
                <div class="text-center mb-8">
                    <div class="inline-block mb-6">
                        <div class="bg-blue-600 rounded-xl p-4 shadow-md">
                            <img src="/images/Logo.png" alt="Logo"
                                class="h-12 w-auto object-contain filter brightness-0 invert" />
                        </div>
                    </div>
                    <h1 class="text-3xl font-bold text-blue-600 mb-2">KualiFin</h1>
                    <p class="text-slate-600 font-medium">Bienvenido al sistema de créditos</p>
                    @if (session('status'))
                        <div class="mt-4 p-3 bg-green-50 border border-green-200 rounded-xl text-green-700 text-sm font-medium">
                            {{ session('status') }}
                        </div>
                    @endif
                </div>

                <!-- Formulario -->
                <form method="POST" action="{{ route('login') }}" class="space-y-6">
                    @csrf
                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-semibold text-slate-700 mb-2 flex items-center gap-2">
                            <span>📧</span>
                            Correo electrónico
                        </label>
                        <input id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            autofocus
                            autocomplete="username"
                            placeholder="usuario@empresa.com"
                            class="w-full border rounded-xl px-4 py-3 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 {{ $errors->has('email') ? 'border-red-300 focus:ring-red-500 focus:border-red-500' : 'border-slate-300 hover:border-slate-400' }}">
                        @error('email')
                            <div class="mt-2 text-sm text-red-600 font-medium flex items-center gap-2">
                                <span>⚠️</span>{{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-semibold text-slate-700 mb-2 flex items-center gap-2">
                            <span>🔒</span>
                            Contraseña
                        </label>
                        <input id="password"
                            type="password"
                            name="password"
                            required
                            autocomplete="current-password"
                            placeholder="••••••••"
                            class="w-full border rounded-xl px-4 py-3 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 {{ $errors->has('password') ? 'border-red-300 focus:ring-red-500 focus:border-red-500' : 'border-slate-300 hover:border-slate-400' }}">
                        @error('password')
                            <div class="mt-2 text-sm text-red-600 font-medium flex items-center gap-2">
                                <span>⚠️</span>{{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- Remember me y forgot -->
                    <div class="flex items-center justify-between">
                        <label class="inline-flex items-center text-sm cursor-pointer">
                            <input type="checkbox"
                                name="remember"
                                class="h-4 w-4 text-blue-600 border-slate-300 rounded focus:ring-blue-500 focus:ring-2">
                            <span class="ml-2 text-slate-700 font-medium">Recuérdame</span>
                        </label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}"
                                class="text-sm text-blue-600 hover:text-blue-700 font-medium hover:underline transition-colors duration-200">
                                ¿Olvidaste tu contraseña?
                            </a>
                        @endif
                    </div>

                    <!-- Botón submit -->
                    <button type="submit"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-xl transition-colors duration-200 shadow-md flex items-center justify-center gap-3 hover:shadow-lg">
                        <span>🚀</span>
                        <span>Iniciar sesión</span>
                    </button>

                    <!-- Error general de auth -->
                    @if($errors->has('email') || $errors->has('password') || session('error'))
                        <div class="bg-red-50 border border-red-200 rounded-xl p-4">
                            <div class="text-center text-sm text-red-700 font-medium flex items-center justify-center gap-2">
                                <span>❌</span>
                                <span>Usuario o contraseña incorrectos</span>
                            </div>
                        </div>
                    @endif
                </form>

                <!-- Footer -->
                <div class="mt-8 text-center">
                    <div class="flex items-center justify-center gap-2 text-xs text-slate-500">
                        <span>🔐</span>
                        <span>Conexión segura y encriptada</span>
                        <span>🔐</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
