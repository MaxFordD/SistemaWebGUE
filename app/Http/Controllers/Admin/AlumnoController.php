<?php

namespace App\Http\Controllers\Admin;

use App\Exports\AlumnoExport;
use App\Exports\AlumnoPlantillaExport;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

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

    public function exportar(Request $request)
    {
        $seccionId = $request->get('seccion_id');
        $año       = $request->get('año', date('Y'));

        if (!$seccionId) {
            return redirect()->route('admin.alumnos.index')->with('error', 'Selecciona una sección para exportar.');
        }

        $alumnos = collect(DB::select('CALL sp_Alumno_ListarPorSeccion(?)', [(int) $seccionId]));
        $seccionData = DB::select('CALL sp_Seccion_ObtenerPorId(?)', [(int) $seccionId]);
        $seccion = $seccionData[0] ?? null;

        $meta = [
            'grado'   => $seccion->grado   ?? '',
            'seccion' => $seccion->seccion  ?? '',
            'nivel'   => $seccion->nivel    ?? '',
            'año'     => $año,
        ];

        $nombre = 'alumnos_' . ($seccion->grado ?? '') . '_' . ($seccion->seccion ?? '') . '_' . $año . '.xlsx';

        return Excel::download(new AlumnoExport($alumnos, $meta), $nombre);
    }

    public function plantilla()
    {
        return Excel::download(new AlumnoPlantillaExport(), 'plantilla_importar_alumnos.xlsx');
    }

    public function store(Request $request)
    {
        $this->normalizarNombrePropio($request, ['nombres', 'apellidos']);

        $request->validate([
            'seccion_id'       => 'required|integer',
            'nombres'          => ['required', 'string', 'max:100', 'regex:/^[\p{L}\s\.\'\-]+$/u'],
            'apellidos'        => ['required', 'string', 'max:100', 'regex:/^[\p{L}\s\.\'\-]+$/u'],
            'dni'              => 'required|string|size:8|regex:/^[0-9]+$/',
            'fecha_nacimiento' => 'nullable|date|before:today',
            'sexo'             => 'required|in:M,F',
        ], [
            'seccion_id.required'  => 'Seleccione una sección.',
            'nombres.required'     => 'Los nombres son obligatorios.',
            'nombres.regex'        => 'Los nombres solo deben contener letras.',
            'apellidos.required'   => 'Los apellidos son obligatorios.',
            'apellidos.regex'      => 'Los apellidos solo deben contener letras.',
            'dni.required'         => 'El DNI es obligatorio.',
            'dni.size'             => 'El DNI debe tener exactamente 8 dígitos.',
            'dni.regex'            => 'El DNI solo debe contener números.',
            'sexo.required'        => 'El sexo es obligatorio.',
            'fecha_nacimiento.before' => 'La fecha de nacimiento no puede ser futura.',
        ]);

        try {
            DB::select('CALL sp_Alumno_Insertar(?, ?, ?, ?, ?, ?, ?)', [
                (int) $request->seccion_id,
                $request->nombres,
                $request->apellidos,
                $request->dni,
                $request->fecha_nacimiento ?: null,
                $request->sexo,
                bin2hex(random_bytes(16)),
            ]);

            $this->registrarBitacora('alumnos', "Registró al alumno {$request->apellidos}, {$request->nombres} (DNI {$request->dni})");

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
        $this->normalizarNombrePropio($request, ['nombres', 'apellidos']);

        $request->validate([
            'seccion_id'       => 'required|integer',
            'nombres'          => ['required', 'string', 'max:100', 'regex:/^[\p{L}\s\.\'\-]+$/u'],
            'apellidos'        => ['required', 'string', 'max:100', 'regex:/^[\p{L}\s\.\'\-]+$/u'],
            'dni'              => 'required|string|size:8|regex:/^[0-9]+$/',
            'fecha_nacimiento' => 'nullable|date|before:today',
            'sexo'             => 'required|in:M,F',
            'estado'           => 'required|in:0,1',
        ], [
            'dni.size'      => 'El DNI debe tener exactamente 8 dígitos.',
            'dni.regex'     => 'El DNI solo debe contener números.',
            'nombres.regex' => 'Los nombres solo deben contener letras.',
            'apellidos.regex' => 'Los apellidos solo deben contener letras.',
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

            $this->registrarBitacora('alumnos', "Actualizó al alumno {$request->apellidos}, {$request->nombres} (DNI {$request->dni})");

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
            $alumnoData = DB::select('CALL sp_Alumno_ObtenerPorId(?)', [(int) $id]);
            $nombre     = isset($alumnoData[0]) ? "{$alumnoData[0]->apellidos}, {$alumnoData[0]->nombres}" : "alumno #{$id}";

            DB::statement('CALL sp_Alumno_Eliminar(?)', [(int) $id]);
            $this->registrarBitacora('alumnos', "Desactivó al alumno {$nombre}");
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
            $alumnoData = DB::select('CALL sp_Alumno_ObtenerPorId(?)', [(int) $id]);
            $nombre     = isset($alumnoData[0]) ? "{$alumnoData[0]->apellidos}, {$alumnoData[0]->nombres}" : "alumno #{$id}";

            DB::statement('CALL sp_Alumno_BorrarFisico(?)', [(int) $id]);
            $this->registrarBitacora('alumnos', "Eliminó permanentemente al alumno {$nombre}");
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
            'archivo' => 'required|file|max:5120',
            'año'     => 'required|integer|min:2020|max:2099',
        ]);

        $archivo   = $request->file('archivo');
        $extension = strtolower($archivo->getClientOriginalExtension());
        $esExcel   = in_array($extension, ['xlsx', 'xls']);

        [$rawHeader, $dataRows] = $esExcel
            ? ($this->leerFilasExcel($archivo->getPathname()) ?? [null, []])
            : $this->leerFilasCsv($archivo->getPathname());

        if (!$rawHeader) {
            return response()->json(['ok' => false, 'error' => 'El archivo está vacío o no es válido.'], 422);
        }

        $header = array_map(fn($h) => mb_strtolower(trim((string) $h)), $rawHeader);
        $aliases = [
            'apellido' => 'apellidos', 'apellidos' => 'apellidos',
            'nombre'   => 'nombres',   'nombres'   => 'nombres',
            'dni'      => 'dni',       'sexo'      => 'sexo',
            'fecha_nacimiento' => 'fecha_nacimiento', 'fecha de nacimiento' => 'fecha_nacimiento',
            'grado'    => 'grado',     'seccion'   => 'seccion', 'sección' => 'seccion',
        ];
        $normalizedHeader = array_map(fn($col) => $aliases[$col] ?? $col, $header);
        $fechaIdx = array_search('fecha_nacimiento', $normalizedHeader, true);

        $known = ['grado', 'seccion', 'apellidos', 'nombres', 'dni', 'fecha_nacimiento', 'sexo'];
        $rows  = [];
        foreach ($dataRows as $row) {
            if (count($row) !== count($normalizedHeader)) continue;
            if (count(array_filter($row, fn($v) => trim((string) ($v ?? '')) !== '')) === 0) continue;

            if ($esExcel && $fechaIdx !== false && is_numeric($row[$fechaIdx] ?? null)) {
                try {
                    $row[$fechaIdx] = ExcelDate::excelToDateTimeObject($row[$fechaIdx])->format('d/m/Y');
                } catch (\Exception $e) {}
            }

            $mapped = array_combine($normalizedHeader, $row);
            $rows[] = array_intersect_key($mapped, array_flip($known));
        }

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

    private function leerFilasCsv(string $path): array
    {
        $handle = fopen($path, 'r');
        // Quitar BOM que agrega Excel al guardar CSV
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") rewind($handle);

        // Auto-detectar separador: tab o punto y coma
        $firstLine = fgets($handle);
        rewind($handle);
        if ($bom === "\xEF\xBB\xBF") fread($handle, 3);
        $sep = str_contains($firstLine, "\t") ? "\t" : ';';

        $rawHeader = fgetcsv($handle, 0, $sep);
        if (!$rawHeader) {
            fclose($handle);
            return [null, []];
        }

        $rows = [];
        while (($row = fgetcsv($handle, 0, $sep)) !== false) {
            $rows[] = $row;
        }
        fclose($handle);

        return [$rawHeader, $rows];
    }

    private function leerFilasExcel(string $path): ?array
    {
        try {
            $spreadsheet = IOFactory::load($path);
        } catch (\Exception $e) {
            return null;
        }

        $data = $spreadsheet->getActiveSheet()->toArray(null, true, false, false);
        $data = array_values(array_filter($data, fn($row) =>
            count(array_filter($row, fn($v) => trim((string) ($v ?? '')) !== '')) > 0
        ));

        if (empty($data)) {
            return null;
        }

        $rawHeader = array_map(fn($v) => (string) $v, array_shift($data));

        return [$rawHeader, $data];
    }

    /**
     * Convierte cualquier forma de escribir el grado (palabra completa,
     * abreviatura, con/sin tilde, con "Primaria/Secundaria" al lado) al
     * número de grado (1-6), para poder comparar el Excel importado
     * contra el valor libre que el admin haya escrito en la tabla Grado.
     */
    private function normalizarGrado(?string $texto): ?int
    {
        $t = mb_strtolower(trim((string) $texto));
        $t = strtr($t, ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u']);

        $palabras = [
            'primero' => 1, '1ro' => 1, '1' => 1,
            'segundo' => 2, '2do' => 2, '2' => 2,
            'tercero' => 3, '3ro' => 3, '3' => 3,
            'cuarto'  => 4, '4to' => 4, '4' => 4,
            'quinto'  => 5, '5to' => 5, '5' => 5,
            'sexto'   => 6, '6to' => 6, '6' => 6,
        ];

        if (isset($palabras[$t])) {
            return $palabras[$t];
        }

        // Casos como "1° primaria", "1ro de primaria", "grado 1", etc.
        if (preg_match('/(\d)/', $t, $m)) {
            $n = (int) $m[1];
            if ($n >= 1 && $n <= 6) {
                return $n;
            }
        }

        return null;
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

        $inserted = 0;
        $skipped  = 0;
        $errors   = [];

        foreach ($rows as $i => $row) {
            $gradoNum     = $this->normalizarGrado($row['grado'] ?? '');
            $seccionLetra = mb_strtoupper(trim($row['seccion'] ?? ''));

            if (!$gradoNum) {
                $errors[] = "Fila " . ($i + 2) . ": grado '{$row['grado']}' no reconocido.";
                continue;
            }

            $seccion = $secciones->first(fn($s) =>
                $this->normalizarGrado($s->grado) === $gradoNum &&
                mb_strtoupper(trim($s->seccion)) === $seccionLetra
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
            $nombres   = Str::title(trim($row['nombres'] ?? ''));
            $apellidos = Str::title(trim($row['apellidos'] ?? ''));
            $dni       = trim($row['dni'] ?? '');

            if (!in_array($sexo, ['M', 'F'])) {
                $errors[] = "Fila " . ($i + 2) . " ({$apellidos}): sexo '{$sexo}' inválido (debe ser M o F).";
                continue;
            }

            if (!preg_match('/^[0-9]{8}$/', $dni)) {
                $errors[] = "Fila " . ($i + 2) . " ({$apellidos}): DNI '{$dni}' inválido (debe tener exactamente 8 dígitos numéricos).";
                continue;
            }

            if ($nombres === '' || $apellidos === '' || !preg_match('/^[\p{L}\s\.\'\-]+$/u', $nombres) || !preg_match('/^[\p{L}\s\.\'\-]+$/u', $apellidos)) {
                $errors[] = "Fila " . ($i + 2) . " ({$apellidos}): nombres/apellidos inválidos (solo se permiten letras).";
                continue;
            }

            try {
                DB::select('CALL sp_Alumno_Insertar(?, ?, ?, ?, ?, ?, ?)', [
                    $seccion->seccion_id, $nombres, $apellidos, $dni, $fechaNac, $sexo,
                    bin2hex(random_bytes(16)),
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

        if ($inserted > 0) {
            $this->registrarBitacora('alumnos', "Importó {$inserted} alumno(s) desde Excel para el año {$año}");
        }

        return response()->json([
            'ok'       => true,
            'inserted' => $inserted,
            'skipped'  => $skipped,
            'errors'   => $errors,
        ]);
    }

    public function qr($id, Request $request)
    {
        $alumnoData = DB::select('CALL sp_Alumno_ObtenerPorId(?)', [(int) $id]);
        if (empty($alumnoData)) {
            abort(404);
        }
        $alumno = $alumnoData[0];

        $qrSvg = QrCode::size(260)->generate($alumno->codigo_qr);

        if ($request->ajax()) {
            return view('admin.alumnos.qr-carnet', compact('alumno', 'qrSvg'));
        }

        return view('admin.alumnos.qr', compact('alumno', 'qrSvg'));
    }

    public function qrTodos(Request $request)
    {
        $seccionId = $request->get('seccion_id');
        if (!$seccionId) {
            return redirect()->route('admin.alumnos.index')->with('error', 'Selecciona una sección para descargar los QR.');
        }

        $seccionData = DB::select('CALL sp_Seccion_ObtenerPorId(?)', [(int) $seccionId]);
        $seccion = $seccionData[0] ?? null;

        $alumnos = DB::table('Alumno')
            ->where('seccion_id', (int) $seccionId)
            ->where('estado', 1)
            ->orderBy('apellidos')
            ->orderBy('nombres')
            ->get();

        $tarjetas = $alumnos->map(function ($alumno) {
            return [
                'alumno' => $alumno,
                'qrSvg'  => QrCode::size(160)->generate($alumno->codigo_qr),
            ];
        });

        return view('admin.alumnos.qr-todos', compact('seccion', 'tarjetas'));
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
            $this->registrarBitacora('alumnos', "Eliminó permanentemente {$total} alumno(s) en lote (IDs: " . implode(',', $request->ids) . ')');
            return redirect()->route('admin.alumnos.index', $params)
                ->with('success', "{$total} alumno(s) eliminado(s) permanentemente.");
        } catch (\Exception $e) {
            Log::error('Error al borrar alumnos en masa: ' . $e->getMessage());
            return redirect()->route('admin.alumnos.index', $params)
                ->with('error', 'Error al eliminar los alumnos seleccionados.');
        }
    }
}
