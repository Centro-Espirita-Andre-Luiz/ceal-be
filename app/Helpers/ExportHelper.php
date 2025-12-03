<?php

namespace App\Helpers;

use Carbon\Carbon;

class ExportHelper
{
    public static function formatDate($date, $format = 'd/m/Y')
    {
        if (empty($date)) {
            return 'N/A';
        }

        try {
            if ($date instanceof Carbon) {
                return $date->format($format);
            }

            return Carbon::parse($date)->format($format);
        } catch (\Exception $e) {
            return 'Data inválida';
        }
    }

    public static function formatDateTime($datetime, $format = 'd/m/Y H:i')
    {
        return self::formatDate($datetime, $format);
    }

    public static function formatTime($time, $format = 'H:i')
    {
        if (empty($time)) {
            return 'N/A';
        }

        try {
            if ($time instanceof Carbon) {
                return $time->format($format);
            }

            // Se for string de hora (09:00:00)
            if (is_string($time) && str_contains($time, ':')) {
                return substr($time, 0, 5); // Pega apenas HH:mm
            }

            return Carbon::parse($time)->format($format);
        } catch (\Exception $e) {
            return 'Hora inválida';
        }
    }
}
