<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ArchivoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class HistoriaLegadoController extends Controller
{

    public function index()
    {
        $items = collect(DB::select('CALL sp_HistoriaLegado_Listar()'));
        return view('admin.historia-legado.index', compact('items'));
    }

    public function store(Request $request)
    {
        $tipo = $request->input('tipo');

        $rules = [
            'tipo'   => 'required|in:foto,texto,video',
            'titulo' => 'required|string|max:200',
        ];
        if ($tipo === 'foto')  $rules['foto']      = 'required|image|mimes:jpeg,jpg,png,webp|max:4096';
        if ($tipo === 'texto') $rules['contenido'] = 'required|string';
        if ($tipo === 'video') $rules['url_video'] = 'required|url|max:500';

        $request->validate($rules);

        $archivo  = null;
        $urlVideo = null;
        $contenido = $request->input('contenido');

        if ($tipo === 'foto' && $request->hasFile('foto')) {
            $archivo = $request->file('foto')->store('historia-legado', 'public');
            app(ArchivoService::class)->optimizarImagen($archivo);
        }
        if ($tipo === 'video') {
            $urlVideo = $this->convertirEmbedUrl($request->input('url_video'));
        }

        $orden = (DB::table('Historia_Legado')->max('orden') ?? 0) + 1;

        try {
            DB::statement('SET @resultado = 0, @mensaje = ""');
            DB::statement('CALL sp_HistoriaLegado_Insertar(?, ?, ?, ?, ?, ?, @resultado, @mensaje)', [
                $tipo, $request->titulo, $contenido, $archivo, $urlVideo, $orden,
            ]);
            $out = DB::select('SELECT @resultado as resultado, @mensaje as mensaje');
            $ok  = (int)($out[0]->resultado ?? 0) > 0;

            if (!$ok && $archivo) Storage::disk('public')->delete($archivo);
            if ($ok) $this->registrarBitacora("Agregó a Historia y Legado: \"{$request->titulo}\" ({$tipo})");

            return back()->with($ok ? 'success' : 'error', $out[0]->mensaje ?? 'Error');
        } catch (\Exception $e) {
            if ($archivo) Storage::disk('public')->delete($archivo);
            Log::error('HistoriaLegado store: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Error al guardar el elemento.');
        }
    }

    public function update(Request $request, $id)
    {
        $tipo = $request->input('tipo');

        $rules = [
            'tipo'   => 'required|in:foto,texto,video',
            'titulo' => 'required|string|max:200',
            'estado' => 'required|in:A,I',
        ];
        if ($tipo === 'foto')  $rules['foto']      = 'nullable|image|mimes:jpeg,jpg,png,webp|max:4096';
        if ($tipo === 'texto') $rules['contenido'] = 'required|string';
        if ($tipo === 'video') $rules['url_video'] = 'required|url|max:500';

        $request->validate($rules);

        $actual = DB::select('CALL sp_HistoriaLegado_ObtenerPorId(?)', [(int)$id]);
        if (empty($actual)) return back()->with('error', 'Elemento no encontrado.');
        $actual = $actual[0];

        $archivo   = $actual->archivo;
        $urlVideo  = $tipo === 'video' ? $this->convertirEmbedUrl($request->input('url_video')) : null;
        $contenido = $request->input('contenido');

        if ($tipo === 'foto' && $request->hasFile('foto')) {
            $nuevoArchivo = $request->file('foto')->store('historia-legado', 'public');
            app(ArchivoService::class)->optimizarImagen($nuevoArchivo);
            if ($archivo && Storage::disk('public')->exists($archivo)) {
                Storage::disk('public')->delete($archivo);
            }
            $archivo = $nuevoArchivo;
        }
        if ($tipo !== 'foto') $archivo = null;

        try {
            DB::statement('SET @resultado = 0, @mensaje = ""');
            DB::statement('CALL sp_HistoriaLegado_Actualizar(?, ?, ?, ?, ?, ?, ?, ?, @resultado, @mensaje)', [
                (int)$id, $tipo, $request->titulo, $contenido,
                $archivo, $urlVideo, (int)$actual->orden, $request->estado,
            ]);
            $out = DB::select('SELECT @resultado as resultado, @mensaje as mensaje');
            $ok  = (int)($out[0]->resultado ?? 0) === 1;
            if ($ok) $this->registrarBitacora("Actualizó en Historia y Legado: \"{$request->titulo}\"");
            return back()->with($ok ? 'success' : 'error', $out[0]->mensaje ?? 'Error');
        } catch (\Exception $e) {
            Log::error('HistoriaLegado update: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Error al actualizar.');
        }
    }

    public function destroy($id)
    {
        try {
            $actual = collect(DB::select('CALL sp_HistoriaLegado_ObtenerPorId(?)', [(int)$id]))->first();

            DB::statement('SET @resultado = 0, @mensaje = ""');
            DB::statement('CALL sp_HistoriaLegado_Eliminar(?, @resultado, @mensaje)', [(int)$id]);
            $out = DB::select('SELECT @resultado as resultado, @mensaje as mensaje');
            $ok  = (int)($out[0]->resultado ?? 0) === 1;
            if ($ok) {
                $titulo = $actual->titulo ?? "elemento #{$id}";
                $this->registrarBitacora("Eliminó de Historia y Legado: \"{$titulo}\"");
            }
            return back()->with($ok ? 'success' : 'error', $out[0]->mensaje ?? 'Error');
        } catch (\Exception $e) {
            Log::error('HistoriaLegado destroy: ' . $e->getMessage());
            return back()->with('error', 'Error al eliminar.');
        }
    }

    private function convertirEmbedUrl(string $url): string
    {
        // YouTube: youtu.be/ID o youtube.com/watch?v=ID → embed
        if (preg_match('/youtu\.be\/([a-zA-Z0-9_-]+)/', $url, $m) ||
            preg_match('/youtube\.com\/watch\?v=([a-zA-Z0-9_-]+)/', $url, $m)) {
            return 'https://www.youtube.com/embed/' . $m[1];
        }
        // Vimeo
        if (preg_match('/vimeo\.com\/(\d+)/', $url, $m)) {
            return 'https://player.vimeo.com/video/' . $m[1];
        }
        return $url; // ya es embed u otro proveedor
    }
}
