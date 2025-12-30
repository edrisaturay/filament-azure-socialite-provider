<?php

declare(strict_types=1);

namespace EdrisaTuray\FilamentAzureSocialite;

use EdrisaTuray\FilamentAzureSocialite\Console\DoctorCommand;
use EdrisaTuray\FilamentAzureSocialite\Console\InstallCommand;
use EdrisaTuray\FilamentAzureSocialite\Http\Controllers\AzureCallbackController;
use EdrisaTuray\FilamentAzureSocialite\Http\Controllers\AzureRedirectController;
use Filament\Events\ServingFilament;
use Filament\Facades\Filament;
use Filament\Support\Facades\FilamentView;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use SocialiteProviders\Azure\Provider;
use SocialiteProviders\Manager\SocialiteWasCalled;

class FilamentAzureSocialiteServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Register Socialite provider automatically
        $this->registerSocialiteProvider();

        // Register routes for each panel
        $this->registerRoutes();

        // Register render hooks when Filament is serving
        $this->registerRenderHooks();

        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
                DoctorCommand::class,
            ]);
        }

        // Publish config
        $this->publishes([
            __DIR__ . '/../config/filament-azure-socialite.php' => config_path('filament-azure-socialite.php'),
        ], 'filament-azure-socialite-config');
    }

    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/filament-azure-socialite.php',
            'filament-azure-socialite'
        );

        // Register views
        $this->loadViewsFrom(
            __DIR__ . '/../resources/views',
            'filament-azure-socialite'
        );
    }

    protected function registerSocialiteProvider(): void
    {
        // Auto-register the Azure Socialite provider
        Event::listen(function (SocialiteWasCalled $event) {
            $event->extendSocialite('azure', Provider::class);
        });
    }

    protected function registerRoutes(): void
    {
        // Wait until after panels are registered
        $this->app->booted(function () {
            foreach (Filament::getPanels() as $panel) {
                $panelId = $panel->getId();
                $panelPath = $panel->getPath();

                // Only register routes if plugin is enabled for this panel
                if (! AzureSocialiteRegistry::isEnabled($panelId)) {
                    continue;
                }

                Route::middleware($panel->getMiddleware())
                    ->prefix($panelPath)
                    ->name("filament.{$panelId}.")
                    ->group(function () use ($panelId) {
                        Route::get('auth/azure/redirect', [AzureRedirectController::class, 'redirect'])
                            ->name('auth.azure.redirect');

                        Route::get('auth/azure/callback', [AzureCallbackController::class, 'callback'])
                            ->name('auth.azure.callback');
                    });
            }
        });
    }

    protected function registerRenderHooks(): void
    {
        // Register hooks when panels are available
        $this->app->booted(function () {
            foreach (Filament::getPanels() as $panel) {
                $panelId = $panel->getId();

                if (! AzureSocialiteRegistry::isEnabled($panelId)) {
                    continue;
                }

                $hook = AzureSocialiteRegistry::getHook($panelId);
                $hookName = $hook === 'before' 
                    ? 'panels::auth.login.form.before'
                    : 'panels::auth.login.form.after';

                // Register the hook with null scope so it applies to all panels
                // The view will check if plugin is enabled for the current panel
                FilamentView::registerRenderHook(
                    $hookName,
                    fn () => view('filament-azure-socialite::button', [
                        'panelId' => $panelId,
                    ]),
                    null // null scope means it applies to all panels
                );
            }
        });
    }

}

