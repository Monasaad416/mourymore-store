<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('filament/resources.users.sections.form'))
                    ->columns(2)
                    ->schema([
                        TextEntry::make('name')
                            ->label(__('filament/resources.users.fields.name')),
                        TextEntry::make('email')
                            ->label(__('filament/resources.users.fields.email')),
                        TextEntry::make('roles.name')
                            ->label(__('filament/resources.users.fields.roles'))
                            ->badge()
                            ->placeholder(__('filament/resources.users.fields.no_role')),
                        TextEntry::make('email_verified_at')
                            ->label(__('filament/resources.users.fields.email_verified_at'))
                            ->dateTime()
                            ->placeholder(__('filament/resources.users.fields.unverified')),
                        TextEntry::make('created_at')
                            ->label(__('filament/resources.users.fields.created_at'))
                            ->dateTime(),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
