<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Spatie\Permission\Models\Role;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('filament/resources.users.sections.form'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label(__('filament/resources.users.fields.name'))
                            ->required(),
                        TextInput::make('email')
                            ->label(__('filament/resources.users.fields.email'))
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true),
                        DateTimePicker::make('email_verified_at')
                            ->label(__('filament/resources.users.fields.email_verified_at')),
                        TextInput::make('password')
                            ->label(__('filament/resources.users.fields.password'))
                            ->password()
                            ->revealable()
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->required(fn (string $operation): bool => $operation === 'create'),
                        Select::make('roles')
                            ->label(__('filament/resources.users.fields.roles'))
                            ->relationship('roles', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->required()
                            ->default(function (): array {
                                $role = Role::findOrCreate(
                                    'customer',
                                    config('auth.defaults.guard', 'web'),
                                );

                                return [$role->getKey()];
                            })
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
