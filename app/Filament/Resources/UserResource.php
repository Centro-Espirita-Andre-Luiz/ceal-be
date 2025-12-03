<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use App\Models\Healer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Configurações';
    protected static ?string $label = 'Usuários';
    protected static ?string $modelLabel = 'Usuário';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informações Básicas')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nome Completo')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->label('E-mail')
                            ->email()
                            ->required()
                            ->unique('users', 'email', ignoreRecord: true)
                            ->validationMessages([
                                'unique' => 'Este e-mail já está cadastrado no sistema.',
                            ])
                            ->maxLength(255),
                        Forms\Components\TextInput::make('phone')
                            ->label('Telefone')
                            ->tel()
                            ->maxLength(20),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Tipo de Acesso')
                    ->schema([
                        Forms\Components\Select::make('type')
                            ->label('Função no Sistema')
                            ->options([
                                'manager' => 'Gerente',
                                'healer' => 'Magnetizador',
                                'attendant' => 'Atendente',
                            ])
                            ->required()
                            ->live()
                            ->helperText('Gerente: Acesso total | Magnetizador: Faz atendimentos | Atendente: Cadastra pacientes'),
                    ]),

                Forms\Components\Section::make('Dados do Magnetizador')
                    ->schema([
                        Forms\Components\TextInput::make('healer.specialty')
                            ->label('Especialidade')
                            ->maxLength(255)
                            ->placeholder('Ex: Passes Magnéticos, Magnetização...'),
                        Forms\Components\TextInput::make('healer.license_number')
                            ->label('Número de Registro')
                            ->maxLength(255),
                    ])
                    ->columns(2)
                    ->visible(fn(Forms\Get $get) => $get('type') === 'healer'),

                Forms\Components\Section::make('Senha de Acesso')
                    ->schema([
                        Forms\Components\TextInput::make('password')
                            ->label(fn(string $context): string => $context === 'create' ? 'Senha*' : 'Nova Senha')
                            ->password()
                            ->required(fn(string $context): bool => $context === 'create')
                            ->dehydrated(fn($state) => filled($state))
                            ->maxLength(255)
                            ->minLength(6)
                            ->default('')
                            ->helperText(
                                fn(string $context): string =>
                                $context === 'create'
                                    ? 'Digite uma senha com mínimo 6 caracteres.'
                                    : 'Deixe em branco para manter a senha atual.'
                            )
                            ->extraInputAttributes(['type' => 'password']),
                    ]),
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
                Tables\Columns\TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Função')
                    ->formatStateUsing(fn($state) => match ($state) {
                        'manager' => 'Gerente',
                        'healer' => 'Magnetizador',
                        'attendant' => 'Atendente',
                        default => $state
                    })
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'manager' => 'danger',
                        'healer' => 'success',
                        'attendant' => 'blue',
                        default => 'gray'
                    }),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Telefone')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Filtrar por Função')
                    ->options([
                        'manager' => 'Gerentes',
                        'healer' => 'Magnetizadores',
                        'attendant' => 'Atendentes',
                    ]),
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

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
