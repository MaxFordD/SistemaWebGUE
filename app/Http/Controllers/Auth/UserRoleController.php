<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserRoleController extends Controller
{
    public function index(Request $request)
    {
        $usuarios = collect(DB::select('CALL sp_Usuario_Listar()'));
        $roles    = collect(DB::select('CALL sp_Rol_Listar()'));

        $usuarioId    = $request->get('usuario_id');
        $rolesUsuario = collect();

        if ($usuarioId) {
            $rolesUsuario = collect(DB::select('CALL sp_UsuarioRol_ListarPorUsuario(?)', [(int)$usuarioId]));
        }

        return view('admin.roles.users', compact('usuarios', 'roles', 'rolesUsuario', 'usuarioId'));
    }

    public function create(Request $request)
    {
        $usuarios = collect(DB::select('CALL sp_Usuario_Listar()'));
        $roles    = collect(DB::select('CALL sp_Rol_Listar()'));

        $usuarioId    = $request->get('usuario_id');
        $rolesUsuario = collect();

        if ($usuarioId) {
            $rolesUsuario = collect(DB::select('CALL sp_UsuarioRol_ListarPorUsuario(?)', [(int)$usuarioId]));
        }

        return view('admin.roles.assign', compact('usuarios', 'roles', 'usuarioId', 'rolesUsuario'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'usuario_id' => 'required|integer',
            'rol_id'     => 'required|integer',
        ]);

        // Inicializar variables de salida
        DB::statement('SET @resultado = 0, @mensaje = ""');

        // Llamar al procedimiento
        DB::statement('CALL sp_UsuarioRol_Asignar(?, ?, @resultado, @mensaje)', [
            (int)$data['usuario_id'],
            (int)$data['rol_id']
        ]);

        // Obtener resultados
        $out = DB::select('SELECT @resultado as resultado, @mensaje as mensaje');
        $ok  = (int)($out[0]->resultado ?? 0) === 1;

        return redirect()
            ->route('admin.roles.assign', ['usuario_id' => (int)$data['usuario_id']])
            ->with($ok ? 'success' : 'error', $out[0]->mensaje ?? 'Operación finalizada');
    }
}
