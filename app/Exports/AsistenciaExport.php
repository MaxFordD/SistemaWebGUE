<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Illuminate\Support\Collection;

class AsistenciaExport implements
    FromCollection,
    WithHeadings,
    WithTitle,
    ShouldAutoSize,
    WithStyles,
    WithEvents
{
    protected Collection $resumen;
    protected array $meta;

    public function __construct(Collection $resumen, array $meta)
    {
        $this->resumen = $resumen;
        $this->meta    = $meta;
    }

    public function collection(): Collection
    {
        return $this->resumen->map(function ($r, $i) {
            $total = $r->total_asistio + $r->total_faltas + $r->total_tardanzas;
            $pct   = $total > 0 ? round($r->total_asistio / $total * 100, 1) : 0;
            return [
                'N°'           => $i + 1,
                'Apellidos'    => $r->apellidos,
                'Nombres'      => $r->nombres,
                'Asistió'      => $r->total_asistio,
                'Faltas'       => $r->total_faltas,
                'Tardanzas'    => $r->total_tardanzas,
                'Total días'   => $total,
                '% Asistencia' => $pct . '%',
            ];
        });
    }

    public function headings(): array
    {
        return ['N°', 'Apellidos', 'Nombres', 'Asistió', 'Faltas', 'Tardanzas', 'Total días', '% Asistencia'];
    }

    public function title(): string
    {
        return 'Asistencia';
    }

    public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet): array
    {
        return [
            1 => [
                'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF2E4057']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $totalRows = $this->resumen->count() + 1;

                // Fila de info arriba (insertar 3 filas al inicio)
                $sheet->insertNewRowBefore(1, 3);

                // Título principal
                $sheet->mergeCells('A1:H1');
                $sheet->setCellValue('A1', 'I.E. José Faustino Sánchez Carrión — Reporte de Asistencia');
                $sheet->getStyle('A1')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 13],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // Info de la sección y periodo
                $sheet->mergeCells('A2:H2');
                $sheet->setCellValue('A2',
                    ($this->meta['grado'] ?? '') . ' — Sección ' . ($this->meta['seccion'] ?? '') .
                    ' | ' . ($this->meta['nivel'] ?? '') .
                    ' | Mes: ' . ($this->meta['mes_nombre'] ?? '') . ' ' . ($this->meta['año'] ?? '')
                );
                $sheet->getStyle('A2')->applyFromArray([
                    'font'      => ['size' => 10, 'italic' => true],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // Espacio
                $sheet->mergeCells('A3:H3');

                // Bordes en la tabla de datos
                $dataRange = 'A4:H' . ($totalRows + 3);
                $sheet->getStyle($dataRange)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color'       => ['argb' => 'FFD0D0D0'],
                        ],
                    ],
                ]);

                // Alinear columnas numéricas al centro
                foreach (['D', 'E', 'F', 'G', 'H'] as $col) {
                    $sheet->getStyle("{$col}4:{$col}" . ($totalRows + 3))
                          ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }
            },
        ];
    }
}
