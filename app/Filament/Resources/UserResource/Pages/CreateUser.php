<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\User;
use App\Models\Healer;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // A senha já é obrigatória no formulário, então sempre existirá
        $data['password'] = Hash::make($data['password']);
        return $data;
    }

    protected function afterCreate(): void
    {
        if ($this->record->type === 'healer') {
            Healer::create([
                'user_id' => $this->record->id,
                'specialty' => $this->data['healer']['specialty'] ?? null,
                'license_number' => $this->data['healer']['license_number'] ?? null,
                'active' => true,
            ]);
        }
    }

    // Opcional: Adicionar validação extra
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
