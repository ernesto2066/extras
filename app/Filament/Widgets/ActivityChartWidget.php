<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Actividad;
use App\Models\TipoCaso;
use Illuminate\Support\Facades\DB;

class ActivityChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Actividades por Tipo de Caso';
    protected static ?int $sort = 1;
    
    // Establecer el tamaño del widget (opcional)
    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $data = Actividad::select('tipo_caso_id', DB::raw('count(*) as total'))
            ->groupBy('tipo_caso_id')
            ->get()
            ->mapWithKeys(function ($item) {
                $tipoCaso = TipoCaso::find($item->tipo_caso_id);
                $nombre = $tipoCaso ? $tipoCaso->nombre : 'Sin tipo';
                return [$nombre => $item->total];
            })
            ->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Actividades',
                    'data' => array_values($data),
                    'backgroundColor' => [
                        'rgba(34, 197, 94, 0.8)', // Verde primario
                        'rgba(22, 163, 74, 0.8)', // Verde oscuro
                        'rgba(134, 239, 172, 0.8)', // Verde claro
                        'rgba(187, 247, 208, 0.8)', // Verde más claro
                        'rgba(240, 253, 244, 0.8)', // Verde muy claro
                    ],
                    'borderColor' => '#22c55e',
                    'borderWidth' => 1
                ],
            ],
            'labels' => array_keys($data),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
