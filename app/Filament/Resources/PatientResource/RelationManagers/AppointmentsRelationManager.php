<?php

namespace App\Filament\Resources\PatientResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AppointmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'appointments';

    protected static ?string $label = 'Consultas';

    protected static ?string $title = 'Consultas';

    protected static ?string $modelLabel = 'Consultas';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('healer_id')
                    ->relationship('healer', 'user.name')
                    ->required()
                    ->label('Magnetizador'),
                Forms\Components\DatePicker::make('scheduled_date')
                    ->required()
                    ->label('Data Agendada'),
                Forms\Components\TimePicker::make('scheduled_time')
                    ->required()
                    ->label('Hora Agendada'),
                Forms\Components\Select::make('status')
                    ->options([
                        'scheduled' => 'Agendado',
                        'confirmed' => 'Confirmado',
                        'completed' => 'Concluído',
                        'cancelled' => 'Cancelado',
                        'no_show' => 'Falta',
                    ])
                    ->required()
                    ->label('Status'),
                Forms\Components\Textarea::make('notes')
                    ->label('Observações')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('scheduled_date')
            ->columns([
                Tables\Columns\TextColumn::make('healer.user.name')
                    ->label('Magnetizador')
                    ->sortable(),
                Tables\Columns\TextColumn::make('scheduled_date')
                    ->label('Data')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('scheduled_time')
                    ->label('Hora')
                    ->time('H:i'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'scheduled' => 'info',
                        'confirmed' => 'warning',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        'no_show' => 'gray',
                    }),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}