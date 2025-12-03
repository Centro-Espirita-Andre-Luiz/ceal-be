<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AbsenceResource\Pages;
use App\Filament\Resources\AbsenceResource\RelationManagers;
use App\Models\Absence;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class AbsenceResource extends Resource
{
    protected static ?string $model = Absence::class;

    protected static ?string $label = 'Faltas';
    protected static ?string $modelLabel = 'Falta';
    protected static ?string $navigationIcon = 'heroicon-o-x-circle';
    protected static ?string $navigationGroup = 'Procedimentos';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Registro de Falta')
                    ->schema([
                        Forms\Components\Select::make('patient_id')
                            ->relationship('patient', 'name')
                            ->required()
                            ->label('Paciente')
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('appointment_id')
                            ->relationship('appointment', 'id')
                            ->getOptionLabelFromRecordUsing(
                                fn($record) =>
                                $record ?
                                    $record->patient->name . ' - ' .
                                    $record->scheduled_date->format('d/m/Y') . ' ' .
                                    $record->scheduled_time
                                    : 'N/A'
                            )
                            ->label('Agendamento Relacionado')
                            ->searchable()
                            ->preload()
                            ->helperText('Opcional - relacionar a um agendamento específico'),
                        Forms\Components\DatePicker::make('absence_date')
                            ->label('Data da Falta')
                            ->required()
                            ->native(false)
                            ->default(now()),
                        Forms\Components\Toggle::make('justified')
                            ->label('Falta Justificada?')
                            ->default(false)
                            ->live()
                            ->helperText('Marque se o paciente justificou a falta'),
                        Forms\Components\Textarea::make('justification')
                            ->label('Justificativa')
                            ->rows(3)
                            ->placeholder('Descrição da justificativa...')
                            ->visible(fn(Forms\Get $get) => $get('justified')),
                        Forms\Components\Select::make('approved_by')
                            ->relationship(
                                name: 'approver',
                                titleAttribute: 'name'
                            )
                            ->label('Aprovado por')
                            ->searchable()
                            ->preload()
                            ->default(Auth::id())
                            ->visible(fn(Forms\Get $get) => $get('justified')),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('patient.name')
                    ->label('Paciente')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('absence_date')
                    ->label('Data da Falta')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\IconColumn::make('justified')
                    ->label('Justificada')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('justification')
                    ->label('Justificativa')
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('approver.name')
                    ->label('Aprovado por')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Registrado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('absence_date')
                    ->form([
                        Forms\Components\DatePicker::make('date_from')
                            ->label('De'),
                        Forms\Components\DatePicker::make('date_until')
                            ->label('Até'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['date_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('absence_date', '>=', $date),
                            )
                            ->when(
                                $data['date_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('absence_date', '<=', $date),
                            );
                    }),
                Tables\Filters\SelectFilter::make('justified')
                    ->label('Status da Justificativa')
                    ->options([
                        true => 'Justificadas',
                        false => 'Não Justificadas',
                    ]),
                Tables\Filters\SelectFilter::make('patient_id')
                    ->relationship('patient', 'name')
                    ->label('Paciente')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\Action::make('justify')
                    ->label('Justificar')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->form([
                        Forms\Components\Textarea::make('justification')
                            ->label('Justificativa')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (Absence $record, array $data) {
                        $record->update([
                            'justified' => true,
                            'justification' => $data['justification'],
                            'approved_by' => Auth::id(),
                        ]);
                    })
                    ->visible(fn(Absence $record) => !$record->justified),
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('markJustified')
                        ->label('Marcar como Justificadas')
                        ->icon('heroicon-o-check')
                        ->action(function ($records) {
                            $records->each->update([
                                'justified' => true,
                                'approved_by' => Auth::id(),
                            ]);
                        }),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('absence_date', 'desc');
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
            'index' => Pages\ListAbsences::route('/'),
            'create' => Pages\CreateAbsence::route('/create'),
            'edit' => Pages\EditAbsence::route('/{record}/edit'),
            'view' => Pages\ViewAbsence::route('/{record}'),
        ];
    }
}
