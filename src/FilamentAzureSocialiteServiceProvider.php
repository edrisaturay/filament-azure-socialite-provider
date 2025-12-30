<?php

declare(strict_types=1);

namespace EdrisaTuray\FilamentAzureSocialite;

use EdrisaTuray\FilamentAzureSocialite\Console\DoctorCommand;
use EdrisaTuray\FilamentAzureSocialite\Console\InstallCommand;
use EdrisaTuray\FilamentAzureSocialite\Http\Controllers\AzureCallbackController;
use EdrisaTuray\FilamentAzureSocialite\Http\Controllers\AzureRedirectController;
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

                // Get Azure config to check for custom redirect URI
                $azureConfig = config('services.azure', []);
                
                // Determine callback path - use custom path from redirect URI if set
                if (! empty($azureConfig['redirect'])) {
                    // Extract path from full redirect URI
                    $redirectUri = $azureConfig['redirect'];
                    $parsedUrl = parse_url($redirectUri);
                    $callbackPath = $parsedUrl['path'] ?? 'auth/azure/callback';
                    
                    // Remove leading slash if present
                    $callbackPath = ltrim($callbackPath, '/');
                } else {
                    // Default path within panel
                    $callbackPath = 'auth/azure/callback';
                }

                Route::middleware($panel->getMiddleware())
                    ->name("filament.{$panelId}.")
                    ->group(function () use ($panelId, $panelPath, $callbackPath) {
                        // Redirect route stays within panel path
                        Route::get("{$panelPath}/auth/azure/redirect", [AzureRedirectController::class, 'redirect'])
                            ->name('auth.azure.redirect');

                        // Callback route uses custom path if configured
                        Route::get($callbackPath, [AzureCallbackController::class, 'callback'])
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

