<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AttendanceResource\Pages;
use App\Filament\Resources\AttendanceResource\RelationManagers;
use App\Models\Attendance;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AttendanceResource extends Resource
{
    protected static ?string $model = Attendance::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $label = 'Atendimentos';

    protected static ?string $modelLabel = 'Atendimentos';

    protected static ?string $navigationGroup = 'Atendimentos';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Seção de Pré-Atendimento
                Forms\Components\Section::make('Pré-Atendimento')
                    ->description('Avaliação inicial do paciente')
                    ->schema([
                        Forms\Components\Select::make('symptoms_status')
                            ->label('Evolução dos Sintomas')
                            ->options([
                                'worsened' => 'Pioraram',
                                'same' => 'Iguais',
                                'improved' => 'Melhoraram',
                            ])
                            ->required()
                            ->default('same'),
                        Forms\Components\Textarea::make('pre_notes')
                            ->label('Observações do Paciente')
                            ->placeholder('Sintomas relatados pelo paciente...')
                            ->columnSpanFull()
                            ->rows(3),
                    ])
                    ->columns(2)
                    ->collapsible(),
                // Seção Principal (que você já tem)
                Forms\Components\Section::make('Informações do Atendimento')
                    ->schema([
                        Forms\Components\Select::make('patient_id')
                            ->relationship('patient', 'name')
                            ->required()
                            ->label('Paciente')
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('healer_id')
                            ->relationship(
                                name: 'healer',
                                modifyQueryUsing: fn($query) => $query->with('user')
                            )
                            ->getOptionLabelFromRecordUsing(fn($record) => $record->user->name ?? 'N/A')
                            ->required()
                            ->label('Magnetizador')
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('appointment_id')
                            ->relationship('appointment', 'id')
                            ->label('Agendamento (Opcional)')
                            ->searchable()
                            ->preload(),
                        Forms\Components\DateTimePicker::make('checkin_time')
                            ->label('Horário de Check-in')
                            ->native(false)
                            ->required()
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
                            ->default(function () {
                                return Attendance::whereDate('checkin_time', today())->max('queue_number') + 1 ?? 1;
                            }),
                        Forms\Components\Textarea::make('notes')
                            ->label('Observações')
                            ->columnSpanFull(),
                    ]),

                // Seção de Pós-Atendimento
                Forms\Components\Section::make('Pós-Atendimento')
                    ->description('Avaliação após o procedimento')
                    ->schema([
                        Forms\Components\TextInput::make('wellness_score')
                            ->label('Nível de Bem-estar (0-10)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(10)
                            ->placeholder('0 a 10')
                            ->helperText('Nota dada pelo paciente após a sessão'),
                        Forms\Components\Textarea::make('procedure_report')
                            ->label('Relato do Procedimento')
                            ->placeholder('Descrição do procedimento realizado...')
                            ->columnSpanFull()
                            ->rows(3),
                        Forms\Components\Textarea::make('magnetizer_notes')
                            ->label('Anotações do Magnetizador')
                            ->placeholder('Observações e impressões do magnetizador...')
                            ->columnSpanFull()
                            ->rows(3),
                        Forms\Components\Textarea::make('notes')
                            ->label('Observações Gerais')
                            ->columnSpanFull()
                            ->rows(2),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('queue_number')
                    ->label('Senha')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('patient.name')
                    ->label('Paciente')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('healer.user.name')
                    ->label('Magnetizador')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('checkin_time')
                    ->label('Check-in')
                    ->dateTime('d/m/Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'waiting' => 'gray',
                        'in_progress' => 'warning',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                    }),
                Tables\Columns\TextColumn::make('symptoms_status')
                    ->label('Sintomas')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->formatStateUsing(fn($state) => match ($state) {
                        'worsened' => 'Pioraram',
                        'same' => 'Iguais',
                        'improved' => 'Melhoraram',
                        default => 'N/A'
                    })
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->color(fn($state) => match ($state) {
                        'worsened' => 'danger',
                        'same' => 'warning',
                        'improved' => 'success',
                        default => 'gray'
                    }),
                Tables\Columns\TextColumn::make('wellness_score')
                    ->label('Bem-estar')
                    ->formatStateUsing(fn($state) => $state ? "{$state}/10" : 'N/A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('today')
                    ->label('Atendimentos de Hoje')
                    ->query(fn(Builder $query): Builder => $query->whereDate('checkin_time', today())),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'waiting' => 'Aguardando',
                        'in_progress' => 'Em Atendimento',
                        'completed' => 'Concluído',
                        'cancelled' => 'Cancelado',
                    ])
                    ->label('Status'),
            ])
            ->actions([
                Tables\Actions\Action::make('start')
                    ->label('Iniciar')
                    ->icon('heroicon-o-play')
                    ->action(function (Attendance $record) {
                        $record->update([
                            'status' => 'in_progress',
                            'start_time' => now()
                        ]);
                    })
                    ->visible(fn(Attendance $record) => $record->status === 'waiting'),
                Tables\Actions\Action::make('complete')
                    ->label('Concluir')
                    ->icon('heroicon-o-check')
                    ->action(function (Attendance $record) {
                        $record->update([
                            'status' => 'completed',
                            'end_time' => now()
                        ]);
                    })
                    ->visible(fn(Attendance $record) => $record->status === 'in_progress'),
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('checkin_time', 'desc');
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
            'index' => Pages\ListAttendances::route('/'),
            'create' => Pages\CreateAttendance::route('/create'),
            'edit' => Pages\EditAttendance::route('/{record}/edit'),
            // 'view' => Pages\ViewAttendance::route('/{record}'),
        ];
    }
}
