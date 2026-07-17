<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class AsistenciaAlumnoExport implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles, WithEvents
{
    protected Collection $historial;
    protected array $meta;

    public function __construct(Collection $historial, array $meta)
    {
        $this->historial = $historial;
        $this->meta      = $meta;
    }

    public function collection(): Collection
    {
        return $this->historial->map(fn($r, $i) => [
            'N°'            => $i + 1,
            'Fecha'         => \Carbon\Carbon::parse($r->fecha)->format('d/m/Y'),
            'Día'           => \Carbon\Carbon::parse($r->fecha)->locale('es')->isoFormat('dddd'),
            'Estado'        => match($r->estado_asistencia) {
                'Asistio'  => 'Asistió',
                'Falta'    => 'Falta',
                'Tardanza' => 'Tardanza',
                default    => $r->estado_asistencia,
            },
            'Observación'   => $r->observacion ?? '',
            'Registrado por'=> $r->registrado_por ?? '',
        ]);
    }

    public function headings(): array
    {
        return ['N°', 'Fecha', 'Día', 'Estado', 'Observación', 'Registrado por'];
    }

    public function title(): string
    {
        return 'Historial';
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                  'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '7A1A0C']]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Insertar 4 filas de encabezado al inicio
                $sheet->insertNewRowBefore(1, 4);

                $sheet->setCellValue('A1', 'I.E. José Faustino Sánchez Carrión');
                $sheet->setCellValue('A2', 'Reporte de Asistencia Individual');
                $sheet->setCellValue('A3', 'Alumno: ' . ($this->meta['alumno'] ?? ''));
                $sheet->setCellValue('B3', 'DNI: ' . ($this->meta['dni'] ?? ''));
                $sheet->setCellValue('A4', 'Grado: ' . ($this->meta['grado'] ?? ''));
                $sheet->setCellValue('B4', 'Período: ' . ($this->meta['mes_nombre'] ?? '') . ' ' . ($this->meta['año'] ?? ''));

                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);
                $sheet->getStyle('A2')->getFont()->setSize(11);
                $sheet->getStyle('A3:B4')->getFont()->setBold(true);

                // Estilo de la fila de encabezados (ahora en fila 5)
                $sheet->getStyle('A5:F5')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '7A1A0C']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
            },
        ];
    }
}
