<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AlumnoController extends Controller
{
    public function index(Request $request)
    {
        $seccionId = $request->get('seccion_id');
        $año       = $request->get('año', date('Y'));

        try {
            $secciones = collect(DB::select('CALL sp_Seccion_ListarActivas(?)', [(int) $año]));
            $alumnos   = collect();
            $seccion   = null;

            if ($seccionId) {
                $alumnos = collect(DB::select('CALL sp_Alumno_ListarPorSeccion(?)', [(int) $seccionId]));
                $seccionData = DB::select('CALL sp_Seccion_ObtenerPorId(?)', [(int) $seccionId]);
                $seccion = $seccionData[0] ?? null;
            }

            return view('admin.alumnos.index', compact('secciones', 'alumnos', 'seccion', 'seccionId', 'año'));
        } catch (\Exception $e) {
            Log::error('Error al listar alumnos: ' . $e->getMessage());
            return redirect()->route('admin.dashboard')->with('error', 'Error al cargar los alumnos.');
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'seccion_id'       => 'required|integer',
            'nombres'          => 'required|string|max:100',
            'apellidos'        => 'required|string|max:100',
            'dni'              => 'required|string|size:8|regex:/^[0-9]+$/',
            'fecha_nacimiento' => 'nullable|date|before:today',
            'sexo'             => 'required|in:M,F',
        ], [
            'seccion_id.required'  => 'Seleccione una sección.',
            'nombres.required'     => 'Los nombres son obligatorios.',
            'apellidos.required'   => 'Los apellidos son obligatorios.',
            'dni.required'         => 'El DNI es obligatorio.',
            'dni.size'             => 'El DNI debe tener exactamente 8 dígitos.',
            'dni.regex'            => 'El DNI solo debe contener números.',
            'sexo.required'        => 'El sexo es obligatorio.',
            'fecha_nacimiento.before' => 'La fecha de nacimiento no puede ser futura.',
        ]);

        try {
            DB::select('CALL sp_Alumno_Insertar(?, ?, ?, ?, ?, ?)', [
                (int) $request->seccion_id,
                $request->nombres,
                $request->apellidos,
                $request->dni,
                $request->fecha_nacimiento ?: null,
                $request->sexo,
            ]);

            return redirect()->route('admin.alumnos.index', [
                'seccion_id' => $request->seccion_id,
                'año'        => $request->año ?? date('Y'),
            ])->with('success', 'Alumno registrado correctamente.');

        } catch (\Exception $e) {
            // DNI duplicado
            if (str_contains($e->getMessage(), 'Duplicate entry')) {
                return back()->withInput()->with('error', 'El DNI ingresado ya está registrado en el sistema.');
            }
            Log::error('Error al crear alumno: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Error al registrar el alumno.');
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'seccion_id'       => 'required|integer',
            'nombres'          => 'required|string|max:100',
            'apellidos'        => 'required|string|max:100',
            'dni'              => 'required|string|size:8|regex:/^[0-9]+$/',
            'fecha_nacimiento' => 'nullable|date|before:today',
            'sexo'             => 'required|in:M,F',
            'estado'           => 'required|in:0,1',
        ], [
            'dni.size'  => 'El DNI debe tener exactamente 8 dígitos.',
            'dni.regex' => 'El DNI solo debe contener números.',
        ]);

        try {
            DB::statement('CALL sp_Alumno_Actualizar(?, ?, ?, ?, ?, ?, ?, ?)', [
                (int) $id,
                (int) $request->seccion_id,
                $request->nombres,
                $request->apellidos,
                $request->dni,
                $request->fecha_nacimiento ?: null,
                $request->sexo,
                (int) $request->estado,
            ]);

            return redirect()->route('admin.alumnos.index', [
                'seccion_id' => $request->seccion_id,
                'año'        => $request->año ?? date('Y'),
            ])->with('success', 'Alumno actualizado correctamente.');

        } catch (\Exception $e) {
            if (str_contains($e->getMessage(), 'Duplicate entry')) {
                return back()->withInput()->with('error', 'El DNI ingresado ya está registrado en otro alumno.');
            }
            Log::error('Error al actualizar alumno: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Error al actualizar el alumno.');
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            DB::statement('CALL sp_Alumno_Eliminar(?)', [(int) $id]);
            return redirect()->route('admin.alumnos.index', [
                'seccion_id' => $request->seccion_id,
                'año'        => $request->año ?? date('Y'),
            ])->with('success', 'Alumno desactivado correctamente.');
        } catch (\Exception $e) {
            Log::error('Error al eliminar alumno: ' . $e->getMessage());
            return redirect()->route('admin.alumnos.index')->with('error', 'Error al desactivar el alumno.');
        }
    }
}
