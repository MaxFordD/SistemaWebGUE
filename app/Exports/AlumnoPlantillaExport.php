<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Comment;

class AlumnoPlantillaExport implements FromArray, WithHeadings, WithTitle, ShouldAutoSize, WithStyles, WithEvents
{
    public function array(): array
    {
        return [];
    }

    public function headings(): array
    {
        return ['grado', 'seccion', 'apellidos', 'nombres', 'dni', 'fecha_nacimiento', 'sexo'];
    }

    public function title(): string
    {
        return 'Plantilla';
    }

    public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet): array
    {
        return [
            1 => [
                'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF7A1A0C']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $notas = [
                    'A1' => "Grado: Primero, Segundo, Tercero, Cuarto, Quinto o Sexto",
                    'B1' => "Sección: letra de la sección, ej: A, B, C",
                    'C1' => "Apellidos completos del alumno",
                    'D1' => "Nombres completos del alumno",
                    'E1' => "DNI: 8 dígitos numéricos",
                    'F1' => "Fecha de nacimiento en formato DD/MM/AAAA, ej: 15/03/2012",
                    'G1' => "Sexo: M o F",
                ];

                foreach ($notas as $celda => $texto) {
                    $comment = $sheet->getComment($celda);
                    $comment->setWidth('220pt');
                    $comment->setHeight('60pt');
                    $comment->getText()->createTextRun($texto);
                }
            },
        ];
    }
}
