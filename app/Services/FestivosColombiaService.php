<?php

namespace App\Services;

use Carbon\Carbon;

class FestivosColombiaService
{
    /**
     * Obtiene todos los días festivos de Colombia para un año determinado.
     *
     * @param int|null $year Año para el que se quieren obtener los festivos. Si es null, usa el año actual.
     * @return array Array con las fechas de los días festivos en formato Y-m-d
     */
    public static function obtenerFestivos(?int $year = null): array
    {
        $year = $year ?? date('Y');
        
        // Festivos fijos
        $festivos = [
            "$year-01-01", // Año Nuevo
            "$year-05-01", // Día del Trabajo
            "$year-07-20", // Día de la Independencia
            "$year-08-07", // Batalla de Boyacá
            "$year-12-08", // Día de la Inmaculada Concepción
            "$year-12-25", // Navidad
        ];
        
        // Festivos que caen en lunes (emilianados)
        // Estos son festivos que se trasladan al lunes siguiente
        $reyes = self::siguiente_lunes(Carbon::parse("$year-01-06")); // Día de Reyes (6 de enero)
        $sanJose = self::siguiente_lunes(Carbon::parse("$year-03-19")); // Día de San José (19 de marzo)
        $sanPedro = self::siguiente_lunes(Carbon::parse("$year-06-29")); // San Pedro y San Pablo (29 de junio)
        $asuncion = self::siguiente_lunes(Carbon::parse("$year-08-15")); // Asunción (15 de agosto)
        $raza = self::siguiente_lunes(Carbon::parse("$year-10-12")); // Día de la Raza (12 de octubre)
        $todosSantos = self::siguiente_lunes(Carbon::parse("$year-11-01")); // Todos los Santos (1 de noviembre)
        $independencia = self::siguiente_lunes(Carbon::parse("$year-11-11")); // Independencia de Cartagena (11 de noviembre)
        
        // Agregar festivos emilianados
        $festivos[] = $reyes->format('Y-m-d');
        $festivos[] = $sanJose->format('Y-m-d');
        $festivos[] = $sanPedro->format('Y-m-d');
        $festivos[] = $asuncion->format('Y-m-d');
        $festivos[] = $raza->format('Y-m-d');
        $festivos[] = $todosSantos->format('Y-m-d');
        $festivos[] = $independencia->format('Y-m-d');
        
        // Festivos que dependen de la Pascua
        $pascua = self::calcular_pascua($year);
        $jueves_santo = (clone $pascua)->subDays(3);
        $viernes_santo = (clone $pascua)->subDays(2);
        $ascension = self::siguiente_lunes(Carbon::parse((clone $pascua)->addDays(39)->format('Y-m-d')));
        $corpus = self::siguiente_lunes(Carbon::parse((clone $pascua)->addDays(60)->format('Y-m-d')));
        $sagrado_corazon = self::siguiente_lunes(Carbon::parse((clone $pascua)->addDays(68)->format('Y-m-d')));
        
        // Agregar festivos dependientes de la Pascua
        $festivos[] = $jueves_santo->format('Y-m-d');
        $festivos[] = $viernes_santo->format('Y-m-d');
        $festivos[] = $ascension->format('Y-m-d');
        $festivos[] = $corpus->format('Y-m-d');
        $festivos[] = $sagrado_corazon->format('Y-m-d');
        
        // Ordenar festivos por fecha
        sort($festivos);
        
        return $festivos;
    }
    
    /**
     * Determina si una fecha dada corresponde a un festivo en Colombia.
     *
     * @param string|Carbon $fecha Fecha a verificar en formato Y-m-d o instancia de Carbon
     * @return bool true si es festivo, false si no
     */
    public static function esFestivo($fecha): bool
    {
        if ($fecha instanceof Carbon) {
            $fecha = $fecha->format('Y-m-d');
        }
        
        $year = substr($fecha, 0, 4);
        $festivos = self::obtenerFestivos($year);
        
        return in_array($fecha, $festivos);
    }
    
    /**
     * Determina si una fecha cae en fin de semana (sábado o domingo).
     *
     * @param string|Carbon $fecha Fecha a verificar
     * @return bool true si es fin de semana, false si no
     */
    public static function esFinDeSemana($fecha): bool
    {
        if (!($fecha instanceof Carbon)) {
            $fecha = Carbon::parse($fecha);
        }
        
        return $fecha->isWeekend();
    }
    
    /**
     * Calcula el siguiente lunes para una fecha dada.
     * Si la fecha ya es lunes, devuelve la misma fecha.
     *
     * @param Carbon $fecha Fecha a partir de la cual calcular
     * @return Carbon Fecha del lunes
     */
    private static function siguiente_lunes(Carbon $fecha): Carbon
    {
        if ($fecha->dayOfWeek == Carbon::MONDAY) {
            return $fecha;
        }
        
        return $fecha->next(Carbon::MONDAY);
    }
    
    /**
     * Calcula la fecha de Pascua para un año dado.
     * Implementación del algoritmo de Butcher.
     *
     * @param int $year Año para el que calcular la Pascua
     * @return Carbon Fecha de la Pascua
     */
    private static function calcular_pascua(int $year): Carbon
    {
        $a = $year % 19;
        $b = floor($year / 100);
        $c = $year % 100;
        $d = floor($b / 4);
        $e = $b % 4;
        $f = floor(($b + 8) / 25);
        $g = floor(($b - $f + 1) / 3);
        $h = (19 * $a + $b - $d - $g + 15) % 30;
        $i = floor($c / 4);
        $k = $c % 4;
        $l = (32 + 2 * $e + 2 * $i - $h - $k) % 7;
        $m = floor(($a + 11 * $h + 22 * $l) / 451);
        $month = floor(($h + $l - 7 * $m + 114) / 31);
        $day = (($h + $l - 7 * $m + 114) % 31) + 1;
        
        return Carbon::create($year, $month, $day);
    }
}
