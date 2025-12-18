<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class ArchivoService
{
    /**
     * Guardar múltiples archivos y retornar rutas y nombres
     *
     * @param array $archivos Array de UploadedFile
     * @param string $directorio Directorio de destino dentro de storage/app/public
     * @return array [$rutasRelativas, $nombresOriginales]
     */
    public function guardarMultiples(array $archivos, string $directorio): array
    {
        $storedPaths = [];
        $originalNames = [];

        foreach ($archivos as $archivo) {
            if (!$archivo instanceof UploadedFile) {
                continue;
            }

            $nombreOriginal = $archivo->getClientOriginalName();
            $path = $archivo->storeAs($directorio, $nombreOriginal, 'public');

            $storedPaths[] = $path;
            $originalNames[] = $nombreOriginal;
        }

        return [$storedPaths, $originalNames];
    }

    /**
     * Concatenar rutas con el formato esperado: /storage/path1; /storage/path2
     *
     * @param array $rutas Rutas relativas (sin /storage/)
     * @return string|null
     */
    public function concatenarRutas(array $rutas): ?string
    {
        if (empty($rutas)) {
            return null;
        }

        return '/storage/' . implode('; /storage/', $rutas);
    }

    /**
     * Eliminar archivos físicos dado un string concatenado
     *
     * @param string|null $rutasConcatenadas String con rutas separadas por ';'
     * @return int Cantidad de archivos eliminados
     */
    public function eliminarArchivos(?string $rutasConcatenadas): int
    {
        if (empty($rutasConcatenadas)) {
            return 0;
        }

        $archivos = explode(';', $rutasConcatenadas);
        $eliminados = 0;

        foreach ($archivos as $archivo) {
            $path = trim(str_replace('/storage/', '', $archivo));
            $fullPath = storage_path('app/public/' . $path);

            if (file_exists($fullPath) && @unlink($fullPath)) {
                $eliminados++;
            }
        }

        return $eliminados;
    }

    /**
     * Extraer solo imágenes de un string concatenado
     *
     * @param string|null $rutasConcatenadas
     * @return array
     */
    public function extraerImagenes(?string $rutasConcatenadas): array
    {
        if (empty($rutasConcatenadas)) {
            return [];
        }

        $archivos = array_filter(array_map('trim', explode(';', $rutasConcatenadas)));

        return array_filter($archivos, function($archivo) {
            $ext = strtolower(pathinfo($archivo, PATHINFO_EXTENSION));
            return in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg']);
        });
    }

    /**
     * Verificar si un archivo existe en storage/app/public
     *
     * @param string $rutaRelativa Ruta relativa (puede incluir o no /storage/)
     * @return bool
     */
    public function existe(string $rutaRelativa): bool
    {
        $path = ltrim(str_replace('/storage/', '', $rutaRelativa), '/');
        return file_exists(storage_path('app/public/' . $path));
    }
}
