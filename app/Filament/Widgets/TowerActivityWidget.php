<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Actividad;
use App\Models\Torre;
use Illuminate\Support\Facades\DB;

class TowerActivityWidget extends ChartWidget
{
    protected static ?string $heading = 'Distribución de Actividades por Torre';
    protected static ?int $sort = 2;
    
    // Definimos un tamaño más pequeño para este widget
    protected int | string | array $columnSpan = ['lg' => 1];

    protected function getData(): array
    {
        $data = Actividad::select('torre_id', DB::raw('count(*) as total'))
            ->groupBy('torre_id')
            ->get()
            ->mapWithKeys(function ($item) {
                $torre = Torre::find($item->torre_id);
                $nombre = $torre ? $torre->nombre : 'Sin torre';
                return [$nombre => $item->total];
            })
            ->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Torres',
                    'data' => array_values($data),
                    'backgroundColor' => [
                        'rgba(34, 197, 94, 0.8)',  // Verde primario
                        'rgba(16, 185, 129, 0.8)', // Esmeralda 600
                        'rgba(20, 83, 45, 0.8)',   // Verde 900
                        'rgba(6, 95, 70, 0.8)',    // Esmeralda 800
                        'rgba(167, 243, 208, 0.8)', // Verde 200
                    ],
                    'borderColor' => '#ffffff',
                    'borderWidth' => 2
                ],
            ],
            'labels' => array_keys($data),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
