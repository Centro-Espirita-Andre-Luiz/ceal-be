<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static ?string $navigationLabel = 'Início';

    protected static ?string $title = 'CEAL - Sistema de Gestão';

    protected static ?int $navigationSort = -2;

    // Usar a funcionalidade padrão do Filament
    // Não sobrescrever $view
}
