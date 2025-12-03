<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\Healer;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Hash;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Só faz hash da senha se foi preenchida
        if (isset($data['password']) && $data['password']) {
            $data['password'] = Hash::make($data['password']);
        } else {
            // Remove a senha do array para não sobrescrever com valor vazio
            unset($data['password']);
        }

        return $data;
    }

    protected function afterSave(): void
    {
        if ($this->record->type === 'healer') {
            $healerData = $this->data['healer'] ?? [];

            if ($this->record->healer) {
                $this->record->healer->update($healerData);
            } else {
                Healer::create(array_merge($healerData, ['user_id' => $this->record->id]));
            }
        } elseif ($this->record->healer) {
            $this->record->healer->delete();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
