<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$path = 'F:/PRACTICAS II/usuarios_estudiantes_completo.csv';
$handle = fopen($path, 'r');

$bom = fread($handle, 3);
if ($bom !== "\xEF\xBB\xBF") rewind($handle);

$rawHeader = fgetcsv($handle, 0, ';');
$header = array_map(fn($h) => mb_strtolower(trim($h)), $rawHeader);
echo "Columnas: " . implode(', ', $header) . "\n";

$aliases = [
    'apellido'=>'apellidos','apellidos'=>'apellidos',
    'nombre'=>'nombres','nombres'=>'nombres',
    'dni'=>'dni','sexo'=>'sexo',
    'fecha_nacimiento'=>'fecha_nacimiento',
    'grado'=>'grado','seccion'=>'seccion',
];
$normalizedHeader = array_map(fn($col) => $aliases[$col] ?? $col, $header);

$rows = [];
while (($row = fgetcsv($handle, 0, ';')) !== false) {
    if (count($row) !== count($normalizedHeader)) continue;
    $rows[] = array_combine($normalizedHeader, $row);
}
fclose($handle);

echo "Total filas: " . count($rows) . "\n";
echo "Primera: apellidos={$rows[0]['apellidos']} | dni={$rows[0]['dni']} | sexo={$rows[0]['sexo']}\n";

// Test fechas con dia sin cero
$fechas = ['28/03/2020', '6/07/2020', '1/11/2019'];
foreach ($fechas as $f) {
    $ok = null;
    foreach (['d/m/Y','j/m/Y','j/n/Y','d/n/Y'] as $fmt) {
        try {
            $ok = Carbon\Carbon::createFromFormat($fmt, $f)->format('Y-m-d');
            break;
        } catch (Exception $ex) {}
    }
    echo "  '$f' => " . ($ok ?? 'ERROR') . "\n";
}

// Secciones en BD
$secciones = collect(DB::select('CALL sp_Seccion_ListarActivas(?)', [2026]));
echo "Secciones 2026: " . $secciones->count() . "\n";

// Verificar match
$gradoMap = [
    'primero'=>'1ro','segundo'=>'2do','tercero'=>'3ro',
    'cuarto'=>'4to','quinto'=>'5to','sexto'=>'6to',
];
$gDb = $gradoMap[mb_strtolower('PRIMERO')] ?? null;
$sec = $secciones->first(fn($s) =>
    mb_strtolower($s->grado) === mb_strtolower($gDb) &&
    mb_strtoupper($s->seccion) === 'A'
);
echo "Match PRIMERO A => " . ($sec ? "seccion_id={$sec->seccion_id}" : 'NO ENCONTRADO') . "\n";

// Contar cuantos grados del CSV pueden matchear
$required = ['apellidos','nombres','dni','fecha_nacimiento','sexo'];
$present  = array_keys($rows[0]);
$missing  = array_diff($required, $present);
echo "Columnas faltantes: " . (empty($missing) ? 'ninguna' : implode(', ', $missing)) . "\n";
