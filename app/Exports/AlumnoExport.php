<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Illuminate\Support\Collection;

class AlumnoExport implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles, WithEvents
{
    protected Collection $alumnos;
    protected array $meta;

    public function __construct(Collection $alumnos, array $meta)
    {
        $this->alumnos = $alumnos;
        $this->meta    = $meta;
    }

    /**
     * Neutraliza fórmulas de Excel/CSV: si el valor empieza con =, +, - o @,
     * Excel puede interpretarlo como fórmula al abrir el archivo exportado.
     */
    private function sinFormula($valor): string
    {
        $valor = (string) $valor;
        if ($valor !== '' && in_array($valor[0], ['=', '+', '-', '@'], true)) {
            return "'" . $valor;
        }
        return $valor;
    }

    public function collection(): Collection
    {
        return $this->alumnos->map(function ($a, $i) {
            return [
                'N°'               => $i + 1,
                'Apellidos'        => $this->sinFormula($a->apellidos),
                'Nombres'          => $this->sinFormula($a->nombres),
                'DNI'              => $a->dni,
                'Sexo'             => $a->sexo === 'M' ? 'Masculino' : 'Femenino',
                'F. Nacimiento'    => $a->fecha_nacimiento
                    ? \Carbon\Carbon::parse($a->fecha_nacimiento)->format('d/m/Y')
                    : '',
                'Estado'           => $a->estado ? 'Activo' : 'Inactivo',
            ];
        });
    }

    public function headings(): array
    {
        return ['N°', 'Apellidos', 'Nombres', 'DNI', 'Sexo', 'F. Nacimiento', 'Estado'];
    }

    public function title(): string
    {
        return 'Alumnos';
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
                $total = $this->alumnos->count();

                $sheet->insertNewRowBefore(1, 3);

                $sheet->mergeCells('A1:G1');
                $sheet->setCellValue('A1', 'I.E. José Faustino Sánchez Carrión — Lista de Alumnos');
                $sheet->getStyle('A1')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 13],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                $sheet->mergeCells('A2:G2');
                $sheet->setCellValue('A2',
                    ($this->meta['grado'] ?? '') . ' — Sección ' . ($this->meta['seccion'] ?? '') .
                    ' | ' . ($this->meta['nivel'] ?? '') .
                    ' | Año: ' . ($this->meta['año'] ?? date('Y'))
                );
                $sheet->getStyle('A2')->applyFromArray([
                    'font'      => ['size' => 10, 'italic' => true],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                $sheet->mergeCells('A3:G3');

                $dataRange = 'A4:G' . ($total + 3);
                $sheet->getStyle($dataRange)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color'       => ['argb' => 'FFD0D0D0'],
                        ],
                    ],
                ]);
            },
        ];
    }
}
