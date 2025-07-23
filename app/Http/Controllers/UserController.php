<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;

class UserController extends Controller
{
    public function store(Request $request)
    {
        // 1) Validación
        $validated = $request->validate([
            'name'                  => 'required|string|max:255',
            'email'                 => 'required|string|email|max:255|unique:users,email',
            'rol'                   => 'required|string|in:promotor,supervisor,administrador,ejecutivo',
            'telefono'              => 'nullable|string|max:20',
            'password'              => ['required','confirmed', Rules\Password::defaults()],
        ]);

        // 2) Creación de usuario
        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'rol'      => $validated['rol'],
            'telefono' => $validated['telefono'] ?? null,
            'password' => Hash::make($validated['password']),
        ]);

        // 3) Disparar evento Registered (opcional)
        event(new Registered($user));

        // 4) Redirigir con flash
        return redirect()
               ->route('register.form')
               ->with('success', 'Empleado registrado correctamente.');
    }
}
