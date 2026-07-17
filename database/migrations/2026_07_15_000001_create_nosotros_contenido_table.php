<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('NosotrosContenido', function (Blueprint $table) {
            $table->id();
            $table->string('clave')->unique();
            $table->text('valor')->nullable();
            $table->timestamps();
        });

        $pilares = [
            ['icon' => 'heart-fill',     'titulo' => 'Valores',              'desc' => 'Respeto, responsabilidad, honestidad y solidaridad son la base de nuestra formación integral.', 'color' => 'danger'],
            ['icon' => 'book-half',      'titulo' => 'Excelencia Académica', 'desc' => 'Comprometidos con los más altos estándares educativos y la mejora continua.',                   'color' => 'primary'],
            ['icon' => 'shield-check',   'titulo' => 'Tradición',            'desc' => 'Más de 190 años de historia formando generaciones de líderes trujillanos.',                     'color' => 'success'],
            ['icon' => 'lightbulb-fill', 'titulo' => 'Innovación',           'desc' => 'Incorporamos tecnología y metodologías modernas para una educación del siglo XXI.',            'color' => 'warning'],
            ['icon' => 'people-fill',    'titulo' => 'Inclusión',            'desc' => 'Valoramos la diversidad y garantizamos oportunidades educativas para todos.',                   'color' => 'info'],
            ['icon' => 'star-fill',      'titulo' => 'Liderazgo',            'desc' => 'Formamos líderes con pensamiento crítico y compromiso social.',                                 'color' => 'dark'],
        ];

        $normas = [
            'Respetar a todos los miembros de la comunidad educativa',
            'Cumplir con puntualidad y responsabilidad nuestros deberes',
            'Cuidar las instalaciones y materiales educativos',
            'Mantener un ambiente de convivencia armónica y pacífica',
            'Practicar la honestidad en todas nuestras acciones',
            'Valorar y respetar la diversidad cultural',
            'Resolver conflictos mediante el diálogo y la mediación',
            'Contribuir al cuidado del medio ambiente',
        ];

        $ahora = now();
        $campos = [
            'historia_titulo' => 'Nuestra Historia',
            'historia_p1'     => 'La Institución Educativa Emblemática José Faustino Sánchez Carrión es una de las instituciones educativas más prestigiosas de Trujillo, con una larga trayectoria formando generaciones de estudiantes comprometidos con la excelencia académica y los valores ciudadanos.',
            'historia_p2'     => 'A lo largo de nuestra historia, hemos mantenido un firme compromiso con la calidad educativa, adaptándonos a los cambios y necesidades de cada generación sin perder nuestra esencia institucional.',
            'historia_imagen' => 'images/gue.jpg',
            'mision'          => 'Formar estudiantes con excelencia académica, valores sólidos y compromiso ciudadano, capaces de enfrentar los retos del mundo actual con pensamiento crítico y responsabilidad social.',
            'vision'          => 'Ser reconocidos como una institución educativa líder en la región, referente de calidad académica, formación en valores e innovación pedagógica.',
            'pilares'         => json_encode($pilares, JSON_UNESCAPED_UNICODE),
            'normas'          => json_encode($normas, JSON_UNESCAPED_UNICODE),
        ];

        foreach ($campos as $clave => $valor) {
            DB::table('NosotrosContenido')->insert([
                'clave' => $clave,
                'valor' => $valor,
                'created_at' => $ahora,
                'updated_at' => $ahora,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('NosotrosContenido');
    }
};
