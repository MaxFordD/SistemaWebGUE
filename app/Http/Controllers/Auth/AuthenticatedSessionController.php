<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    // Mostrar el formulario de inicio de sesión
    public function create()
    {
        return view('auth.login'); // Retorna la vista del formulario de login
    }

    // Procesar el inicio de sesión
    public function store(Request $request)
    {
        // Cambia 'email' por 'nombre_usuario'
        $request->validate([
            'nombre_usuario' => ['required', 'string'], // Cambiado de 'email' a 'nombre_usuario'
            'password' => ['required', 'string'],
        ]);

        // Cambiar 'nombre_usuario' en la autenticación
        if (Auth::attempt(['nombre_usuario' => $request->nombre_usuario, 'password' => $request->password], $request->remember)) {
            $request->session()->regenerate();

            // Verificar si tiene el rol de admin o director
            if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('director')) {
                return redirect()->route('admin.dashboard');
            } else {
                return redirect()->route('home');
            }
        }

        // Error si las credenciales no coinciden
        return back()->withErrors([
            'nombre_usuario' => 'Las credenciales no coinciden con nuestros registros.',
        ]);
    }

    // Cerrar sesión
    public function destroy(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
