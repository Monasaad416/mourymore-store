<?php

namespace App\Providers;

use App\Models\User;
use App\Services\Cart;
use App\Support\SiteContentFileLoader;
use Carbon\CarbonImmutable;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\ViewAction;
use Illuminate\Auth\Events\Login;
use Illuminate\Contracts\Translation\Loader as TranslationLoader;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(Cart::class);

        $this->app->extend('translation.loader', function (TranslationLoader $loader): SiteContentFileLoader {
            return new SiteContentFileLoader($loader);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureFilamentActions();

        Event::listen(Login::class, function (Login $event): void {
            if (! $event->user instanceof User) {
                return;
            }

            app(Cart::class)->claimFor($event->user);
        });
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }

    protected function configureFilamentActions(): void
    {
        ViewAction::configureUsing(fn (ViewAction $action): ViewAction => $action->color('gray'));
        EditAction::configureUsing(fn (EditAction $action): EditAction => $action->color('primary'));
        DeleteAction::configureUsing(fn (DeleteAction $action): DeleteAction => $action->color('danger'));
        DeleteBulkAction::configureUsing(fn (DeleteBulkAction $action): DeleteBulkAction => $action->color('danger'));
        ForceDeleteAction::configureUsing(fn (ForceDeleteAction $action): ForceDeleteAction => $action->color('danger'));
        ForceDeleteBulkAction::configureUsing(fn (ForceDeleteBulkAction $action): ForceDeleteBulkAction => $action->color('danger'));
    }
}
