<?php

namespace App\Exports;

use App\Models\Cotizacion;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CotizacionExport implements FromArray, ShouldAutoSize, WithStyles, WithTitle
{
    public function __construct(private Cotizacion $cotizacion)
    {
    }

    public function title(): string
    {
        return 'Proforma';
    }

    public function array(): array
    {
        $cliente = $this->cotizacion->cliente;

        return [
            ['BARFLOWEC - PROFORMA DE EVENTO'],
            [],
            ['Código', $this->cotizacion->quote_number],
            ['Cliente', $cliente?->name ?? 'Sin cliente'],
            ['Empresa', $cliente?->company ?? '-'],
            ['Email', $cliente?->email ?? '-'],
            ['Teléfono', $cliente?->phone ?? '-'],
            ['Dirección', $cliente?->address ?? '-'],
            [],
            ['Datos del evento'],
            ['Tipo de evento', $this->cotizacion->event_type ?? '-'],
            ['Fecha', $this->cotizacion->event_date ?? '-'],
            ['Invitados', $this->cotizacion->guests],
            ['Estado', $this->cotizacion->status],
            [],
            ['Detalle comercial'],
            ['Concepto', 'Valor'],
            ['Subtotal', (float) $this->cotizacion->subtotal],
            ['IVA 12%', (float) $this->cotizacion->tax],
            ['Total', (float) $this->cotizacion->total],
            [],
            ['Notas'],
            [$this->cotizacion->notes ?? 'Sin notas'],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->mergeCells('A1:B1');

        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A10')->getFont()->setBold(true);
        $sheet->getStyle('A16')->getFont()->setBold(true);
        $sheet->getStyle('A17:B17')->getFont()->setBold(true);
        $sheet->getStyle('A20:B20')->getFont()->setBold(true);

        $sheet->getStyle('A1:B1')->getFill()->setFillType('solid')->getStartColor()->setARGB('FF7C3AED');
        $sheet->getStyle('A1:B1')->getFont()->getColor()->setARGB('FFFFFFFF');

        $sheet->getStyle('A17:B20')->getBorders()->getAllBorders()->setBorderStyle('thin');
        $sheet->getStyle('B18:B20')->getNumberFormat()->setFormatCode('$#,##0.00');

        return [];
    }
}
