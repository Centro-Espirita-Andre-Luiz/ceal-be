<?php

namespace App\Filament\Widgets;

use App\Models\Patient;
use App\Models\Appointment;
use App\Models\Attendance;
use App\Models\Absence;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SystemStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total de Pacientes', Patient::count())
                ->description('Cadastrados no sistema')
                ->descriptionIcon('heroicon-o-user-group')
                ->color('primary')
                ->url(\App\Filament\Resources\PatientResource::getUrl()),

            Stat::make('Atendimentos Hoje', Attendance::whereDate('checkin_time', today())->count())
                ->description('Realizados hoje')
                ->descriptionIcon('heroicon-o-calendar')
                ->color('success')
                ->url(\App\Filament\Resources\AttendanceResource::getUrl()),

            Stat::make('Consultas Agendadas', Appointment::where('status', 'scheduled')->count())
                ->description('Próximas consultas')
                ->descriptionIcon('heroicon-o-clock')
                ->color('warning')
                ->url(\App\Filament\Resources\AppointmentResource::getUrl()),

            Stat::make('Faltas do Mês', Absence::whereMonth('absence_date', now()->month)->count())
                ->description('Registradas este mês')
                ->descriptionIcon('heroicon-o-x-circle')
                ->color('danger')
                ->url(\App\Filament\Resources\AbsenceResource::getUrl()),
        ];
    }
}
