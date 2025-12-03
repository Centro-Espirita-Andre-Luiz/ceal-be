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
                        modifyQueryUsing: fn($query) => $query->with('user')
                    )
                    ->getOptionLabelFromRecordUsing(fn(Healer $record) => $record->user->name)
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
                Tables\Actions\Action::make('exportXLSX')
                    ->label('Exportar Excel')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->tooltip('Exportar dados completos em formato Excel (XLSX)')
                    ->action(function (Patient $record) {
                        // Usar o serviço de exportação
                        $exportService = new \App\Services\PatientExportService();
                        $export = $exportService->exportToXlsx($record);

                        // Retornar o download
                        return response()->download(
                            $export['path'],
                            $export['filename'],
                            [
                                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            ]
                        )->deleteFileAfterSend(true);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('exportSelected')
                        ->label('Exportar Selecionados')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->action(function ($records) {
                            return self::exportarPacientesSelecionados($records);
                        }),
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

    public static function exportarPacienteIndividual(Patient $patient)
    {
        $filename = 'paciente_' . $patient->id . '_' . now()->format('Ymd_His') . '.csv';
        $filepath = storage_path('app/exports/' . $filename);

        // Garantir diretório
        if (!file_exists(storage_path('app/exports'))) {
            mkdir(storage_path('app/exports'), 0755, true);
        }

        $file = fopen($filepath, 'w');

        // Cabeçalhos
        fputcsv($file, ['Campo', 'Valor']);

        // Função auxiliar para formatar datas
        $formatarData = function ($date, $format = 'd/m/Y') {
            if (!$date) return '';
            try {
                if ($date instanceof \Carbon\Carbon) {
                    return $date->format($format);
                }
                return \Carbon\Carbon::parse($date)->format($format);
            } catch (\Exception $e) {
                return is_string($date) ? $date : '';
            }
        };

        // Dados do paciente
        $dados = [
            ['ID', $patient->id],
            ['Nome', $patient->name],
            ['Email', $patient->email ?? ''],
            ['Telefone', $patient->phone ?? ''],
            ['Data Nascimento', $formatarData($patient->birth_date)],
            ['Contato Emergência', $patient->emergency_contact ?? ''],
            ['Observações Saúde', $patient->health_notes ?? ''],
            ['Código Acesso', $patient->access_code ?? ''],
            ['Magnetizador Preferido', optional($patient->preferredHealer)->user->name ?? 'N/A'],
            ['Gerente Responsável', optional($patient->manager)->name ?? 'N/A'],
            ['Data Cadastro', $patient->created_at->format('d/m/Y H:i')],
        ];

        foreach ($dados as $linha) {
            fputcsv($file, $linha);
        }

        fclose($file);

        return response()->download($filepath, $filename)->deleteFileAfterSend(true);
    }

    public static function exportarPacientesSelecionados($records)
    {
        $filename = 'pacientes_selecionados_' . now()->format('Ymd_His') . '.csv';
        $filepath = storage_path('app/exports/' . $filename);

        if (!file_exists(storage_path('app/exports'))) {
            mkdir(storage_path('app/exports'), 0755, true);
        }

        $file = fopen($filepath, 'w');

        // Cabeçalhos
        fputcsv($file, [
            'ID',
            'Nome',
            'Email',
            'Telefone',
            'Data Nascimento',
            'Contato Emergência',
            'Magnetizador Preferido',
            'Data Cadastro'
        ]);

        foreach ($records as $patient) {
            fputcsv($file, [
                $patient->id,
                $patient->name,
                $patient->email ?? '',
                $patient->phone ?? '',
                $patient->birth_date ? optional($patient->birth_date)->format('d/m/Y') : '', // Usar optional()
                $patient->emergency_contact ?? '',
                $patient->preferredHealer ? optional($patient->preferredHealer)->user->name : 'N/A',
                $patient->created_at->format('d/m/Y H:i'),
            ]);
        }

        fclose($file);

        return response()->download($filepath, $filename)->deleteFileAfterSend(true);
    }
}
