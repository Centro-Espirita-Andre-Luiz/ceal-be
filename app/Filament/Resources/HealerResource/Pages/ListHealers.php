<?php

namespace App\Filament\Resources\HealerResource\Pages;

use App\Filament\Resources\HealerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListHealers extends ListRecords
{
    protected static string $resource = HealerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
