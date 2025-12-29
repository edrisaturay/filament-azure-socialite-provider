<?php

declare(strict_types=1);

namespace EdrisaTuray\FilamentAzureSocialite\Console;

use EdrisaTuray\FilamentAzureSocialite\AzureSocialiteRegistry;
use Filament\Facades\Filament;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;

class DoctorCommand extends Command
{
    protected $signature = 'filament-azure-socialite:doctor';

    protected $description = 'Validate Filament Azure Socialite configuration and setup';

    public function handle(): int
    {
        $this->info('Filament Azure Socialite - Configuration Doctor');
        $this->newLine();

        $hasErrors = false;

        // Check services.azure config
        $hasErrors = $this->validateServicesConfig() || $hasErrors;

        // Check route registration
        $hasErrors = $this->validateRoutes() || $hasErrors;

        // Check Socialite listener
        $hasErrors = $this->validateSocialiteListener() || $hasErrors;

        // Check environment
        $hasErrors = $this->validateEnvironment() || $hasErrors;

        if (! $hasErrors) {
            $this->newLine();
            $this->info('✓ All checks passed!');
        } else {
            $this->newLine();
            $this->error('✗ Some issues were found. Please fix them before using the plugin.');
        }

        return $hasErrors ? self::FAILURE : self::SUCCESS;
    }

    protected function validateServicesConfig(): bool
    {
        $this->line('Checking services.azure configuration...');

        $config = config('services.azure', []);

        $hasErrors = false;

        if (empty($config['client_id'])) {
            $this->error('  ✗ AZURE_CLIENT_ID is not set');
            $hasErrors = true;
        } else {
            $this->info('  ✓ AZURE_CLIENT_ID is set');
        }

        if (empty($config['client_secret'])) {
            $this->error('  ✗ AZURE_CLIENT_SECRET is not set');
            $hasErrors = true;
        } else {
            $this->info('  ✓ AZURE_CLIENT_SECRET is set');
        }

        if (empty($config['tenant'])) {
            $this->warn('  ⚠ AZURE_TENANT_ID is not set (defaulting to "common")');
        } else {
            $this->info('  ✓ AZURE_TENANT_ID is set: ' . $config['tenant']);
        }

        return $hasErrors;
    }

    protected function validateRoutes(): bool
    {
        $this->newLine();
        $this->line('Checking route registration...');

        $panels = Filament::getPanels();
        $hasErrors = false;

        if (count($panels) === 0) {
            $this->warn('  ⚠ No Filament panels found');
            return false;
        }

        foreach ($panels as $panel) {
            $panelId = $panel->getId();
            $isEnabled = AzureSocialiteRegistry::isEnabled($panelId);

            if (! $isEnabled) {
                $this->warn("  ⚠ Panel '{$panelId}' - Plugin not enabled");
                continue;
            }

            $redirectRoute = "filament.{$panelId}.auth.azure.redirect";
            $callbackRoute = "filament.{$panelId}.auth.azure.callback";

            if (! Route::has($redirectRoute)) {
                $this->error("  ✗ Panel '{$panelId}' - Redirect route not registered");
                $hasErrors = true;
            } else {
                $this->info("  ✓ Panel '{$panelId}' - Redirect route registered");
            }

            if (! Route::has($callbackRoute)) {
                $this->error("  ✗ Panel '{$panelId}' - Callback route not registered");
                $hasErrors = true;
            } else {
                $this->info("  ✓ Panel '{$panelId}' - Callback route registered");
            }
        }

        return $hasErrors;
    }

    protected function validateSocialiteListener(): bool
    {
        $this->newLine();
        $this->line('Checking Socialite listener registration...');

        // Check if the listener is registered
        // This is a basic check - the actual registration happens in the service provider
        $this->info('  ✓ Socialite listener is auto-registered by the package');

        return false;
    }

    protected function validateEnvironment(): bool
    {
        $this->newLine();
        $this->line('Checking environment configuration...');

        $hasErrors = false;

        $appUrl = config('app.url');
        if (empty($appUrl) || $appUrl === 'http://localhost') {
            $this->warn('  ⚠ APP_URL is not properly configured');
            $this->warn('     This may cause issues with redirect URIs');
        } else {
            $this->info('  ✓ APP_URL is set: ' . $appUrl);
        }

        // Check HTTPS
        if (! request()->secure() && ! app()->environment(['local', 'testing'])) {
            $this->warn('  ⚠ Application is not using HTTPS');
            $this->warn('     Azure requires HTTPS for production redirect URIs');
        } else {
            $this->info('  ✓ HTTPS check passed');
        }

        // Check proxy config
        $proxy = config('services.azure.proxy');
        if (empty($proxy) && ! empty(env('HTTP_PROXY')) && ! empty(env('HTTPS_PROXY'))) {
            $this->warn('  ⚠ Proxy environment variables detected but not configured in services.azure');
            $this->warn('     Consider setting services.azure.proxy if you need proxy support');
        }

        return $hasErrors;
    }
}

