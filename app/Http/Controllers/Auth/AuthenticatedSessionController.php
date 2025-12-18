<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    public function create()
    {
        return view('auth.login');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre_usuario' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt(['nombre_usuario' => $request->nombre_usuario, 'password' => $request->password], $request->remember)) {
            $request->session()->regenerate();

            if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('director')) {
                return redirect()->route('admin.dashboard');
            } else {
                return redirect()->route('home');
            }
        }

        return back()->withErrors([
            'nombre_usuario' => 'Las credenciales no coinciden con nuestros registros.',
        ]);
    }

    public function destroy(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
