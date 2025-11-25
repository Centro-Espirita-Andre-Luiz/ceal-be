<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HealerResource\Pages;
use App\Filament\Resources\HealerResource\RelationManagers;
use App\Models\Healer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class HealerResource extends Resource
{
    protected static ?string $model = Healer::class;

    protected static ?string $label = 'Magnetizador';

    protected static ?string $modelLabel = 'Magnetizador';

    protected static ?string $pluralLabel = 'Magnetizador';

    protected static ?string $pluralModelLabel = 'Magnetizador';

    protected static ?string $navigationIcon = 'heroicon-o-user';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name') // Isso deve funcionar pois user existe
                    ->required()
                    ->label('Usuário'),
                Forms\Components\TextInput::make('specialty')
                    ->label('Especialidade')
                    ->maxLength(255),
                Forms\Components\TextInput::make('license_number')
                    ->label('Número de Licença')
                    ->maxLength(255),
                Forms\Components\Toggle::make('active')
                    ->label('Ativo')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('specialty')
                    ->label('Especialidade')
                    ->searchable(),
                Tables\Columns\TextColumn::make('license_number')
                    ->label('Licença')
                    ->searchable(),
                Tables\Columns\IconColumn::make('active')
                    ->label('Ativo')
                    ->boolean(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHealers::route('/'),
            'create' => Pages\CreateHealer::route('/create'),
            'edit' => Pages\EditHealer::route('/{record}/edit'),
        ];
    }
}
