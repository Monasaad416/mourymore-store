<?php

namespace App\Filament\Resources\Articles\Schemas;

use App\Models\Article;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ArticleInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('filament/resources.articles.sections.overview'))
                    ->columns(3)
                    ->schema([
                        ImageEntry::make('image')
                            ->hiddenLabel()
                            ->disk('public')
                            ->visibility('public')
                            ->defaultImageUrl(fn (Article $record): ?string => $record->imageUrl())
                            ->imageHeight('12rem')
                            ->extraImgAttributes(['class' => 'rounded-2xl object-cover'])
                            ->columnSpan(1),
                        TextEntry::make('title')
                            ->label(__('filament/resources.articles.fields.title'))
                            ->weight('bold')
                            ->size('lg')
                            ->columnSpan(2),
                        TextEntry::make('slug')
                            ->label(__('filament/resources.articles.fields.slug'))
                            ->copyable(),
                        TextEntry::make('published_at')
                            ->label(__('filament/resources.articles.fields.published_at'))
                            ->dateTime()
                            ->placeholder('—'),
                        IconEntry::make('is_published')
                            ->label(__('filament/resources.articles.fields.is_published'))
                            ->boolean(),
                    ])
                    ->columnSpanFull(),
                Section::make(__('filament/resources.articles.sections.content'))
                    ->schema([
                        TextEntry::make('excerpt')
                            ->label(__('filament/resources.articles.fields.excerpt'))
                            ->prose()
                            ->columnSpanFull(),
                        TextEntry::make('body')
                            ->label(__('filament/resources.articles.fields.body'))
                            ->prose()
                            ->markdown()
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
                Section::make(__('filament/resources.articles.sections.seo'))
                    ->columns(1)
                    ->collapsed()
                    ->schema([
                        TextEntry::make('meta_title')
                            ->label(__('filament/resources.articles.fields.meta_title'))
                            ->placeholder('—'),
                        TextEntry::make('meta_description')
                            ->label(__('filament/resources.articles.fields.meta_description'))
                            ->placeholder('—'),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
