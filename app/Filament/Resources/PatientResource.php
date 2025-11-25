<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PatientResource\Pages;
use App\Filament\Resources\PatientResource\RelationManagers;
use App\Models\Patient;
use App\Models\Healer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class PatientResource extends Resource
{
    protected static ?string $model = Patient::class;

    protected static ?string $label = 'Pacientes';

    protected static ?string $modelLabel = 'Pacientes';

    protected static ?string $navigationIcon = 'heroicon-o-identification';

    protected static ?string $navigationGroup = 'Gestão de Pessoas';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->label('Nome Completo'),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->maxLength(255)
                    ->label('E-mail'),
                Forms\Components\DatePicker::make('birth_date')
                    ->label('Data de Nascimento'),
                Forms\Components\TextInput::make('phone')
                    ->label('Telefone')
                    ->maxLength(20),
                Forms\Components\TextInput::make('emergency_contact')
                    ->label('Contato de Emergência')
                    ->maxLength(255),
                Forms\Components\Textarea::make('health_notes')
                    ->label('Observações de Saúde')
                    ->columnSpanFull(),
                Forms\Components\Select::make('preferred_healer_id')
                    ->relationship(
                        name: 'preferredHealer',
                        modifyQueryUsing: fn ($query) => $query->with('user')
                    )
                    ->getOptionLabelFromRecordUsing(fn (Healer $record) => $record->user->name)
                    ->label('Magnetizador Preferido')
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('access_code')
                    ->label('Código de Acesso')
                    ->maxLength(10),
                Forms\Components\Hidden::make('manager_id')
                    ->default(Auth::id()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Telefone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('emergency_contact')
                    ->label('Contato Emergência')
                    ->searchable(),
                Tables\Columns\TextColumn::make('preferredHealer.user.name')
                    ->label('Magnetizador Preferido')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('access_code')
                    ->label('Código Acesso')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
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
            RelationManagers\AppointmentsRelationManager::class,
            RelationManagers\AttendancesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPatients::route('/'),
            'create' => Pages\CreatePatient::route('/create'),
            'edit' => Pages\EditPatient::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        // Gestores veem todos os pacientes, magnetizadores veem apenas seus pacientes preferidos
        if (Auth::user()->isManager()) {
            return parent::getEloquentQuery();
        }

        return parent::getEloquentQuery()->where('preferred_healer_id', Auth::user()->healerInfo->id);
    }
}