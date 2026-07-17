<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
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
            $path = $archivo->store($directorio, 'public');

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
            $fullPath = public_path('storage/' . $path);

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
        return file_exists(public_path('storage/' . $path));
    }

    /**
     * Redimensiona (si excede $maxDim) y recomprime en el sitio una imagen ya
     * guardada en storage/app/public. No hace nada si la extensión GD no está
     * disponible o el archivo no es jpg/png/webp: en ese caso el archivo queda
     * tal como se subió (comportamiento previo).
     *
     * @param string $rutaRelativa Ruta relativa devuelta por UploadedFile::store()
     */
    public function optimizarImagen(string $rutaRelativa, int $maxDim = 1600, int $calidadJpeg = 82): void
    {
        if (!extension_loaded('gd')) {
            return;
        }

        $ext = strtolower(pathinfo($rutaRelativa, PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
            return;
        }

        $rutaCompleta = Storage::disk('public')->path($rutaRelativa);

        try {
            $info = @getimagesize($rutaCompleta);
            if (!$info) {
                return;
            }

            $tipo = $info[2];
            $origen = match ($tipo) {
                IMAGETYPE_JPEG => @imagecreatefromjpeg($rutaCompleta),
                IMAGETYPE_PNG  => @imagecreatefrompng($rutaCompleta),
                IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($rutaCompleta) : null,
                default        => null,
            };
            if (!$origen) {
                return;
            }

            // Corrige la rotación de fotos de celular (el metadato EXIF se pierde al recomprimir)
            if ($tipo === IMAGETYPE_JPEG && function_exists('exif_read_data')) {
                $exif = @exif_read_data($rutaCompleta);
                $origen = match ($exif['Orientation'] ?? 1) {
                    3 => imagerotate($origen, 180, 0),
                    6 => imagerotate($origen, -90, 0),
                    8 => imagerotate($origen, 90, 0),
                    default => $origen,
                };
            }

            $ancho = imagesx($origen);
            $alto  = imagesy($origen);
            $ratio = min(1, $maxDim / max($ancho, $alto));
            $nuevoAncho = max(1, (int) round($ancho * $ratio));
            $nuevoAlto  = max(1, (int) round($alto * $ratio));

            $destino = imagecreatetruecolor($nuevoAncho, $nuevoAlto);
            if ($tipo === IMAGETYPE_PNG || $tipo === IMAGETYPE_WEBP) {
                imagealphablending($destino, false);
                imagesavealpha($destino, true);
            }
            imagecopyresampled($destino, $origen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);

            match ($tipo) {
                IMAGETYPE_JPEG => imagejpeg($destino, $rutaCompleta, $calidadJpeg),
                IMAGETYPE_PNG  => imagepng($destino, $rutaCompleta, 6),
                IMAGETYPE_WEBP => imagewebp($destino, $rutaCompleta, $calidadJpeg),
                default        => null,
            };

            imagedestroy($origen);
            imagedestroy($destino);
        } catch (\Throwable $e) {
            Log::warning('No se pudo optimizar la imagen "' . $rutaRelativa . '": ' . $e->getMessage());
        }
    }
}
