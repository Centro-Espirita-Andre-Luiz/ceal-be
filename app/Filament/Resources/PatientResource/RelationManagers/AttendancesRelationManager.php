<?php

namespace App\Filament\Resources\PatientResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AttendancesRelationManager extends RelationManager
{
    protected static string $relationship = 'attendances';

    protected static ?string $label = 'Atendimentos';

    protected static ?string $title = 'Atendimentos';

    protected static ?string $modelLabel = 'Atendimentos';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('healer_id')
                    ->relationship(
                        name: 'healer',
                        modifyQueryUsing: fn ($query) => $query->with('user')
                    )
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->user->name ?? 'N/A')
                    ->required()
                    ->label('Magnetizador')
                    ->searchable()
                    ->preload(),
                Forms\Components\DateTimePicker::make('checkin_time')
                    ->required()
                    ->label('Horário de Check-in')
                    ->default(now()),
                Forms\Components\DateTimePicker::make('start_time')
                    ->label('Início do Atendimento'),
                Forms\Components\DateTimePicker::make('end_time')
                    ->label('Término do Atendimento'),
                Forms\Components\Select::make('status')
                    ->options([
                        'waiting' => 'Aguardando',
                        'in_progress' => 'Em Atendimento',
                        'completed' => 'Concluído',
                        'cancelled' => 'Cancelado',
                    ])
                    ->required()
                    ->label('Status')
                    ->default('waiting'),
                Forms\Components\TextInput::make('queue_number')
                    ->label('Número da Senha')
                    ->required()
                    ->numeric()
                    ->default(function ($livewire) {
                        $patientId = $livewire->ownerRecord->id;
                        return \App\Models\Attendance::where('patient_id', $patientId)
                            ->whereDate('checkin_time', today())
                            ->max('queue_number') + 1 ?? 1;
                    }),
                Forms\Components\Textarea::make('notes')
                    ->label('Observações')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('checkin_time')
            ->columns([
                Tables\Columns\TextColumn::make('queue_number')
                    ->label('Senha')
                    ->sortable(),
                Tables\Columns\TextColumn::make('healer.user.name')
                    ->label('Magnetizador')
                    ->sortable(),
                Tables\Columns\TextColumn::make('checkin_time')
                    ->label('Check-in')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'waiting' => 'gray',
                        'in_progress' => 'warning',
                        'completed' => 'success',
                        'cancelled' => 'danger',
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
