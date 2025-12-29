<?php

declare(strict_types=1);

namespace EdrisaTuray\FilamentAzureSocialite;

use EdrisaTuray\FilamentAzureSocialite\Console\DoctorCommand;
use EdrisaTuray\FilamentAzureSocialite\Console\InstallCommand;
use EdrisaTuray\FilamentAzureSocialite\Http\Controllers\AzureCallbackController;
use EdrisaTuray\FilamentAzureSocialite\Http\Controllers\AzureRedirectController;
use Filament\Facades\Filament;
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

}

