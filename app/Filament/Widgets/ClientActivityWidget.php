<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Actividad;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ClientActivityWidget extends ChartWidget
{
    protected static ?string $heading = 'Actividades por Cliente';
    protected static ?int $sort = 3;
    
    // Definimos un tamaño adecuado para este widget
    protected int | string | array $columnSpan = ['lg' => 1];

    protected function getData(): array
    {
        // Obtenemos los 5 clientes con más actividades
        $topClients = Actividad::select('cliente', DB::raw('count(*) as total'))
            ->whereNotNull('cliente')
            ->groupBy('cliente')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->mapWithKeys(function ($item) {
                // Limitamos el nombre del cliente a 15 caracteres para mejor visualización
                $clientName = Str::limit($item->cliente, 15);
                return [$clientName => $item->total];
            })
            ->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Clientes',
                    'data' => array_values($topClients),
                    'backgroundColor' => [
                        'rgba(34, 197, 94, 0.8)',  // Verde primario
                        'rgba(5, 150, 105, 0.8)',  // Esmeralda 700 
                        'rgba(16, 185, 129, 0.8)', // Esmeralda 600
                        'rgba(4, 120, 87, 0.8)',   // Esmeralda 800
                        'rgba(110, 231, 183, 0.8)', // Esmeralda 300
                    ],
                    'borderColor' => '#ffffff',
                    'borderWidth' => 1
                ],
            ],
            'labels' => array_keys($topClients),
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
