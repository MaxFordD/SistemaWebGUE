<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

            /** @var \App\Models\Usuario $user */
            $user = Auth::user();

            $uid = $user->usuario_id ?? $user->id;
            $roles = collect(DB::select('CALL sp_UsuarioRol_ListarPorUsuario(?)', [(int)$uid]))
                ->pluck('nombre')
                ->map(fn($n) => mb_strtolower(trim($n)))
                ->toArray();
            $request->session()->put('user_roles', $roles);

            if ($user->hasRole('Administrador') || $user->hasRole('Director')) {
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
