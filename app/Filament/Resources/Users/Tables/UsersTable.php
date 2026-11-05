<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

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
                TernaryFilter::make('email_verified')
                    ->label(__('filament/resources.filters.email_verified'))
                    ->queries(
                        true: fn (Builder $query): Builder => $query->whereNotNull('email_verified_at'),
                        false: fn (Builder $query): Builder => $query->whereNull('email_verified_at'),
                    ),
                Filter::make('created_at')
                    ->label(__('filament/resources.users.fields.created_at'))
                    ->form([
                        DatePicker::make('from')
                            ->label(__('filament/resources.filters.from')),
                        DatePicker::make('until')
                            ->label(__('filament/resources.filters.until')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'] ?? null,
                                fn (Builder $builder, mixed $date): Builder => $builder->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['until'] ?? null,
                                fn (Builder $builder, mixed $date): Builder => $builder->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ]);
    }
}
