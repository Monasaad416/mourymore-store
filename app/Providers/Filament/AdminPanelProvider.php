<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Dashboard;
use App\Filament\Pages\ManageSiteSettings;
use App\Models\SiteSetting;
use App\Support\Theme;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use LaraZeus\SpatieTranslatable\SpatieTranslatablePlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandName(fn (): string => SiteSetting::current()->displayName())
            ->brandLogo(fn (): string => SiteSetting::current()->logoUrl())
            ->darkModeBrandLogo(fn (): string => SiteSetting::current()->logoUrl())
            ->brandLogoHeight('4.75rem')
            ->favicon(fn (): string => SiteSetting::current()->faviconUrl())
            ->colors([
                // Soft gold primary; red kept for delete/destructive only.
                'primary' => Theme::filamentPalette('#c9a227'),
                'gray' => Color::Stone,
                'warning' => Theme::filamentPalette(Theme::star()),
                'danger' => Theme::filamentPalette('#dc2626'),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->resourceCreatePageRedirect('index')
            ->pages([
                Dashboard::class,
                ManageSiteSettings::class,
            ])
            ->widgets([
                AccountWidget::class,
            ])
            ->plugin(SpatieTranslatablePlugin::make()
                ->persist()->defaultLocales(['ar', 'en']))
            ->plugin(FilamentShieldPlugin::make())
            ->navigationGroups([
                __('filament/resources.navigation.catalog'),
                __('filament/resources.navigation.sales'),
                __('filament/resources.navigation.store'),
                __('filament/resources.navigation.content'),
                __('filament/resources.navigation.customers'),
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);

    }
}
