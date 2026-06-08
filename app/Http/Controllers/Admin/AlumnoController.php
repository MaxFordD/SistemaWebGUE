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

    public function borrar(Request $request, $id)
    {
        try {
            DB::statement('CALL sp_Alumno_BorrarFisico(?)', [(int) $id]);
            return redirect()->route('admin.alumnos.index', [
                'seccion_id' => $request->seccion_id,
                'año'        => $request->año ?? date('Y'),
            ])->with('success', 'Alumno eliminado permanentemente.');
        } catch (\Exception $e) {
            Log::error('Error al borrar alumno: ' . $e->getMessage());
            return redirect()->route('admin.alumnos.index', [
                'seccion_id' => $request->seccion_id,
                'año'        => $request->año ?? date('Y'),
            ])->with('error', 'Error al eliminar el alumno.');
        }
    }

    public function importarPreview(Request $request)
    {
        $request->validate([
            'archivo' => 'required|file|mimes:csv,txt|max:5120',
            'año'     => 'required|integer|min:2020|max:2099',
        ]);

        $handle = fopen($request->file('archivo')->getPathname(), 'r');
        // Quitar BOM que agrega Excel al guardar CSV
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") rewind($handle);

        $rawHeader = fgetcsv($handle, 0, ';');
        if (!$rawHeader) {
            return response()->json(['ok' => false, 'error' => 'El archivo está vacío o no es válido.'], 422);
        }

        $header = array_map(fn($h) => mb_strtolower(trim($h)), $rawHeader);
        $aliases = [
            'apellido' => 'apellidos', 'apellidos' => 'apellidos',
            'nombre'   => 'nombres',   'nombres'   => 'nombres',
            'dni'      => 'dni',       'sexo'      => 'sexo',
            'fecha_nacimiento' => 'fecha_nacimiento',
            'grado'    => 'grado',     'seccion'   => 'seccion', 'sección' => 'seccion',
        ];
        $normalizedHeader = array_map(fn($col) => $aliases[$col] ?? $col, $header);

        $known = ['grado', 'seccion', 'apellidos', 'nombres', 'dni', 'fecha_nacimiento', 'sexo'];
        $rows  = [];
        while (($row = fgetcsv($handle, 0, ';')) !== false) {
            if (count($row) !== count($normalizedHeader)) continue;
            $mapped = array_combine($normalizedHeader, $row);
            $rows[] = array_intersect_key($mapped, array_flip($known));
        }
        fclose($handle);

        if (empty($rows)) {
            return response()->json(['ok' => false, 'error' => 'No se encontraron filas de datos.'], 422);
        }

        $presentFields = array_keys($rows[0]);
        if (!in_array('grado', $presentFields) || !in_array('seccion', $presentFields)) {
            return response()->json(['ok' => false, 'error' => 'El archivo debe tener las columnas "grado" y "seccion".'], 422);
        }

        $required = ['apellidos', 'nombres', 'dni', 'fecha_nacimiento', 'sexo'];
        $missing  = array_values(array_diff($required, $presentFields));

        return response()->json([
            'ok'      => true,
            'rows'    => $rows,
            'missing' => $missing,
            'total'   => count($rows),
        ]);
    }

    public function importarConfirmar(Request $request)
    {
        $request->validate([
            'año'  => 'required|integer|min:2020|max:2099',
            'rows' => 'required|string',
        ]);

        $año  = (int) $request->año;
        $rows = json_decode($request->rows, true);

        if (!is_array($rows) || empty($rows)) {
            return response()->json(['ok' => false, 'error' => 'No hay datos para importar.'], 422);
        }

        $secciones = collect(DB::select('CALL sp_Seccion_ListarActivas(?)', [$año]));

        $gradoMap = [
            'primero' => '1ro', 'segundo' => '2do', 'tercero' => '3ro',
            'cuarto'  => '4to', 'quinto'  => '5to', 'sexto'   => '6to',
            '1ro' => '1ro', '2do' => '2do', '3ro' => '3ro',
            '4to' => '4to', '5to' => '5to', '6to' => '6to',
        ];

        $inserted = 0;
        $skipped  = 0;
        $errors   = [];

        foreach ($rows as $i => $row) {
            $gradoKey = mb_strtolower(trim($row['grado'] ?? ''));
            $gradoDb  = $gradoMap[$gradoKey] ?? null;
            $seccionLetra = mb_strtoupper(trim($row['seccion'] ?? ''));

            if (!$gradoDb) {
                $errors[] = "Fila " . ($i + 2) . ": grado '{$row['grado']}' no reconocido.";
                continue;
            }

            $seccion = $secciones->first(fn($s) =>
                mb_strtolower($s->grado) === mb_strtolower($gradoDb) &&
                mb_strtoupper($s->seccion) === $seccionLetra
            );

            if (!$seccion) {
                $errors[] = "Fila " . ($i + 2) . ": no existe sección {$row['grado']} {$seccionLetra} en {$año}.";
                continue;
            }

            $fechaNac = null;
            if (!empty($row['fecha_nacimiento'])) {
                $rawFecha = trim($row['fecha_nacimiento']);
                // Probar DD/MM/YYYY y también D/MM/YYYY (día sin cero)
                foreach (['d/m/Y', 'j/m/Y', 'j/n/Y', 'd/n/Y'] as $fmt) {
                    try {
                        $fechaNac = \Carbon\Carbon::createFromFormat($fmt, $rawFecha)->format('Y-m-d');
                        break;
                    } catch (\Exception $e) {}
                }
            }

            $sexo      = mb_strtoupper(trim($row['sexo'] ?? ''));
            $nombres   = mb_strtoupper(trim($row['nombres'] ?? ''));
            $apellidos = mb_strtoupper(trim($row['apellidos'] ?? ''));
            $dni       = trim($row['dni'] ?? '');

            if (!in_array($sexo, ['M', 'F'])) {
                $errors[] = "Fila " . ($i + 2) . " ({$apellidos}): sexo '{$sexo}' inválido (debe ser M o F).";
                continue;
            }

            try {
                DB::select('CALL sp_Alumno_Insertar(?, ?, ?, ?, ?, ?)', [
                    $seccion->seccion_id, $nombres, $apellidos, $dni, $fechaNac, $sexo,
                ]);
                $inserted++;
            } catch (\Exception $e) {
                if (str_contains($e->getMessage(), 'Duplicate entry')) {
                    $skipped++;
                } else {
                    $errors[] = "Fila " . ($i + 2) . " ({$apellidos}): " . $e->getMessage();
                }
            }
        }

        return response()->json([
            'ok'       => true,
            'inserted' => $inserted,
            'skipped'  => $skipped,
            'errors'   => $errors,
        ]);
    }

    public function borrarMasivo(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array|min:1',
            'ids.*' => 'required|integer',
        ]);

        $params = [
            'seccion_id' => $request->seccion_id,
            'año'        => $request->año ?? date('Y'),
        ];

        try {
            DB::transaction(function () use ($request) {
                foreach ($request->ids as $id) {
                    DB::statement('CALL sp_Alumno_BorrarFisico(?)', [(int) $id]);
                }
            });
            $total = count($request->ids);
            return redirect()->route('admin.alumnos.index', $params)
                ->with('success', "{$total} alumno(s) eliminado(s) permanentemente.");
        } catch (\Exception $e) {
            Log::error('Error al borrar alumnos en masa: ' . $e->getMessage());
            return redirect()->route('admin.alumnos.index', $params)
                ->with('error', 'Error al eliminar los alumnos seleccionados.');
        }
    }
}
