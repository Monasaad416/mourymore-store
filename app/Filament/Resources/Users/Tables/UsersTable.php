<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('name')
                    ->label(__('filament/resources.users.fields.name'))
                    ->searchable(),
                TextColumn::make('email')
                    ->label(__('filament/resources.users.fields.email'))
                    ->searchable(),
                TextColumn::make('roles.name')
                    ->label(__('filament/resources.users.fields.roles'))
                    ->badge()
                    ->placeholder(__('filament/resources.users.fields.no_role')),
                TextColumn::make('email_verified_at')
                    ->label(__('filament/resources.users.fields.email_verified_at'))
                    ->dateTime()
                    ->placeholder(__('filament/resources.users.fields.unverified')),
                TextColumn::make('created_at')
                    ->label(__('filament/resources.users.fields.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('roles')
                    ->label(__('filament/resources.users.fields.roles'))
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ]);
    }
}
