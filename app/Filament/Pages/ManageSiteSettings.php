<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Validation\Rules\File;

/**
 * @property-read Schema $form
 */
class ManageSiteSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::Cog6Tooth;

    protected static ?int $navigationSort = 0;

    protected static ?string $slug = 'site-settings';

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public static function getNavigationGroup(): ?string
    {
        return __('filament/resources.navigation.content');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/resources.site_settings.navigation_label');
    }

    public function getTitle(): string|Htmlable
    {
        return __('filament/resources.site_settings.title');
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user !== null && $user->hasAnyRole(['admin', 'super_admin']);
    }

    public function mount(): void
    {
        $settings = SiteSetting::current();

        $this->form->fill([
            'name' => $settings->getTranslations('name'),
            'tagline' => $settings->getTranslations('tagline'),
            'logo' => $settings->logo,
            'favicon' => $settings->favicon,
            'cover' => $settings->cover,
            'currency_code' => $settings->currency_code ?: 'EGP',
            'currency_symbol_en' => $settings->currency_symbol_en ?: 'EGP',
            'currency_symbol_ar' => $settings->currency_symbol_ar ?: 'ج.م',
            'tax_percent' => $settings->tax_percent ?? 0,
            'prices_include_tax' => $settings->prices_include_tax ?? true,
            'free_shipping_over' => $settings->free_shipping_over ?? config('store.free_shipping_over', 1500),
            'support_email' => $settings->support_email,
            'support_phone' => $settings->support_phone,
            'support_whatsapp' => $settings->support_whatsapp,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('filament/resources.site_settings.sections.branding'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('name.en')
                            ->label(__('filament/resources.site_settings.fields.name_en'))
                            ->required(),
                        TextInput::make('name.ar')
                            ->label(__('filament/resources.site_settings.fields.name_ar'))
                            ->required(),
                        TextInput::make('tagline.en')
                            ->label(__('filament/resources.site_settings.fields.tagline_en')),
                        TextInput::make('tagline.ar')
                            ->label(__('filament/resources.site_settings.fields.tagline_ar')),
                    ])
                    ->columnSpanFull(),
                Section::make(__('filament/resources.site_settings.sections.media'))
                    ->columns(3)
                    ->schema([
                        FileUpload::make('logo')
                            ->label(__('filament/resources.site_settings.fields.logo'))
                            ->disk('public')
                            ->directory('branding')
                            ->visibility('public')
                            ->image()
                            ->acceptedFileTypes([
                                'image/jpeg',
                                'image/png',
                                'image/webp',
                                'image/gif',
                                'image/svg+xml',
                                'application/octet-stream',
                            ])
                            ->rules([
                                File::default()->extensions(['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'])->max(2 * 1024),
                            ]),
                        FileUpload::make('favicon')
                            ->label(__('filament/resources.site_settings.fields.favicon'))
                            ->disk('public')
                            ->directory('branding')
                            ->visibility('public')
                            ->image()
                            ->acceptedFileTypes([
                                'image/png',
                                'image/x-icon',
                                'image/vnd.microsoft.icon',
                                'image/jpeg',
                                'image/webp',
                                'image/svg+xml',
                                'application/octet-stream',
                            ])
                            ->rules([
                                File::default()->extensions(['png', 'ico', 'jpg', 'jpeg', 'webp', 'svg'])->max(1024),
                            ]),
                        FileUpload::make('cover')
                            ->label(__('filament/resources.site_settings.fields.cover'))
                            ->disk('public')
                            ->directory('branding')
                            ->visibility('public')
                            ->image()
                            ->acceptedFileTypes([
                                'image/jpeg',
                                'image/png',
                                'image/webp',
                                'application/octet-stream',
                            ])
                            ->rules([
                                File::default()->extensions(['jpg', 'jpeg', 'png', 'webp'])->max(5 * 1024),
                            ]),
                    ])
                    ->columnSpanFull(),
                Section::make(__('filament/resources.site_settings.sections.commerce'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('currency_code')
                            ->label(__('filament/resources.site_settings.fields.currency_code'))
                            ->required()
                            ->maxLength(8)
                            ->dehydrateStateUsing(fn (?string $state): string => strtoupper(trim((string) $state))),
                        TextInput::make('tax_percent')
                            ->label(__('filament/resources.site_settings.fields.tax_percent'))
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%')
                            ->required(),
                        TextInput::make('currency_symbol_en')
                            ->label(__('filament/resources.site_settings.fields.currency_symbol_en'))
                            ->required(),
                        TextInput::make('currency_symbol_ar')
                            ->label(__('filament/resources.site_settings.fields.currency_symbol_ar'))
                            ->required(),
                        Toggle::make('prices_include_tax')
                            ->label(__('filament/resources.site_settings.fields.prices_include_tax'))
                            ->helperText(__('filament/resources.site_settings.fields.prices_include_tax_help'))
                            ->columnSpanFull(),
                        TextInput::make('free_shipping_over')
                            ->label(__('filament/resources.site_settings.fields.free_shipping_over'))
                            ->numeric()
                            ->minValue(0)
                            ->prefix(fn ($get): string => (string) ($get('currency_code') ?: 'EGP')),
                    ])
                    ->columnSpanFull(),
                Section::make(__('filament/resources.site_settings.sections.contact'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('support_email')
                            ->label(__('filament/resources.site_settings.fields.support_email'))
                            ->email(),
                        TextInput::make('support_phone')
                            ->label(__('filament/resources.site_settings.fields.support_phone'))
                            ->tel(),
                        TextInput::make('support_whatsapp')
                            ->label(__('filament/resources.site_settings.fields.support_whatsapp'))
                            ->tel()
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([
                    EmbeddedSchema::make('form'),
                ])
                    ->id('form')
                    ->livewireSubmitHandler('save')
                    ->footer([
                        Actions::make([
                            Action::make('save')
                                ->label(__('filament/resources.site_settings.actions.save'))
                                ->submit('save'),
                        ]),
                    ]),
            ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $settings = SiteSetting::current();

        if (! $settings->exists) {
            $settings = SiteSetting::query()->create(SiteSetting::defaultAttributes());
        }

        $settings->update([
            'name' => [
                'en' => $data['name']['en'] ?? '',
                'ar' => $data['name']['ar'] ?? '',
            ],
            'tagline' => [
                'en' => $data['tagline']['en'] ?? '',
                'ar' => $data['tagline']['ar'] ?? '',
            ],
            'logo' => filled($data['logo'] ?? null) ? $data['logo'] : $settings->logo,
            'favicon' => filled($data['favicon'] ?? null) ? $data['favicon'] : $settings->favicon,
            'cover' => filled($data['cover'] ?? null) ? $data['cover'] : $settings->cover,
            'currency_code' => strtoupper(trim((string) ($data['currency_code'] ?? 'EGP'))),
            'currency_symbol_en' => trim((string) ($data['currency_symbol_en'] ?? 'EGP')),
            'currency_symbol_ar' => trim((string) ($data['currency_symbol_ar'] ?? 'ج.م')),
            'tax_percent' => (float) ($data['tax_percent'] ?? 0),
            'prices_include_tax' => (bool) ($data['prices_include_tax'] ?? true),
            'free_shipping_over' => $data['free_shipping_over'] !== null && $data['free_shipping_over'] !== ''
                ? (float) $data['free_shipping_over']
                : null,
            'support_email' => filled($data['support_email'] ?? null) ? $data['support_email'] : null,
            'support_phone' => filled($data['support_phone'] ?? null) ? $data['support_phone'] : null,
            'support_whatsapp' => filled($data['support_whatsapp'] ?? null) ? $data['support_whatsapp'] : null,
        ]);

        Notification::make()
            ->title(__('filament/resources.site_settings.saved'))
            ->success()
            ->send();
    }
}
