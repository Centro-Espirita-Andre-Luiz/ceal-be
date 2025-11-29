<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AppointmentResource\Pages;
use App\Filament\Resources\AppointmentResource\RelationManagers;
use App\Models\Appointment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AppointmentResource extends Resource
{
    protected static ?string $model = Appointment::class;

    protected static ?string $label = 'Consultas';

    protected static ?string $modelLabel = 'Consultas';

    protected static ?string $navigationIcon = 'heroicon-o-document-check';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informações da Consulta')
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
                            ->getOptionLabelFromRecordUsing(fn($record) => $record->user->name)
                            ->required()
                            ->label('Magnetizador')
                            ->searchable()
                            ->preload(),
                        Forms\Components\DatePicker::make('scheduled_date')
                            ->label('Data Agendada')
                            ->required()
                            ->native(false)
                            ->minDate(now()),
                        Forms\Components\TimePicker::make('scheduled_time')
                            ->label('Horário Agendado')
                            ->required()
                            ->seconds(false),
                        Forms\Components\Select::make('status')
                            ->options([
                                'scheduled' => 'Agendada',
                                'confirmed' => 'Confirmada',
                                'completed' => 'Concluída',
                                'cancelled' => 'Cancelada',
                                'no_show' => 'Não Compareceu',
                            ])
                            ->required()
                            ->label('Status')
                            ->default('scheduled'),
                        Forms\Components\Textarea::make('notes')
                            ->label('Observações')
                            ->columnSpanFull()
                            ->rows(3),
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
                Tables\Columns\TextColumn::make('healer.user.name')
                    ->label('Magnetizador')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('scheduled_date')
                    ->label('Data')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('scheduled_time')
                    ->label('Horário')
                    ->time('H:i'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'scheduled' => 'gray',
                        'confirmed' => 'success',
                        'completed' => 'primary',
                        'cancelled' => 'danger',
                        'no_show' => 'warning',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'scheduled' => 'Agendada',
                        'confirmed' => 'Confirmada',
                        'completed' => 'Concluída',
                        'cancelled' => 'Cancelada',
                        'no_show' => 'Não Compareceu',
                    ])
                    ->label('Status'),
                Tables\Filters\Filter::make('scheduled_date')
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
                                fn(Builder $query, $date): Builder => $query->whereDate('scheduled_date', '>=', $date),
                            )
                            ->when(
                                $data['date_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('scheduled_date', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('confirm')
                    ->label('Confirmar')
                    ->icon('heroicon-o-check')
                    ->action(function (Appointment $record) {
                        $record->update(['status' => 'confirmed']);
                    })
                    ->visible(fn(Appointment $record) => $record->status === 'scheduled'),
                Tables\Actions\Action::make('complete')
                    ->label('Concluir')
                    ->icon('heroicon-o-document-check')
                    ->action(function (Appointment $record) {
                        $record->update(['status' => 'completed']);
                    })
                    ->visible(fn(Appointment $record) => in_array($record->status, ['scheduled', 'confirmed'])),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('scheduled_date', 'desc');
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
            'index' => Pages\ListAppointments::route('/'),
            'create' => Pages\CreateAppointment::route('/create'),
            'edit' => Pages\EditAppointment::route('/{record}/edit'),
        ];
    }
}
