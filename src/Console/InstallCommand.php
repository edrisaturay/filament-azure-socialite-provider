<?php

declare(strict_types=1);

namespace EdrisaTuray\FilamentAzureSocialite\Console;

use Filament\Facades\Filament;
use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature = 'filament-azure-socialite:install';

    protected $description = 'Display installation instructions for Filament Azure Socialite';

    public function handle(): int
    {
        $this->info('Filament Azure Socialite - Installation Guide');
        $this->newLine();

        // Environment variables
        $this->section('Required Environment Variables');
        $this->line('Add these to your .env file:');
        $this->newLine();
        $this->line('AZURE_CLIENT_ID=your-client-id');
        $this->line('AZURE_CLIENT_SECRET=your-client-secret');
        $this->line('AZURE_TENANT_ID=common  # or your tenant ID');
        $this->line('AZURE_REDIRECT_URI=  # Optional - auto-computed if not set');
        $this->newLine();

        // Services config
        $this->section('Services Configuration');
        $this->line('Add this to your config/services.php:');
        $this->newLine();
        $this->line("'azure' => [");
        $this->line("    'client_id' => env('AZURE_CLIENT_ID'),");
        $this->line("    'client_secret' => env('AZURE_CLIENT_SECRET'),");
        $this->line("    'redirect' => env('AZURE_REDIRECT_URI'),");
        $this->line("    'tenant' => env('AZURE_TENANT_ID', 'common'),");
        $this->line("    'proxy' => env('PROXY'),  // optional");
        $this->line('],');
        $this->newLine();

        // Computed redirect URLs
        $this->section('Computed Redirect URLs (per panel)');
        $panels = Filament::getPanels();
        if (count($panels) === 0) {
            $this->warn('No Filament panels found. Register the plugin in your panel provider first.');
        } else {
            foreach ($panels as $panel) {
                $panelId = $panel->getId();
                $redirectUrl = route("filament.{$panelId}.auth.azure.callback", [], true);
                $this->line("Panel: {$panelId}");
                $this->line("  Redirect URI: {$redirectUrl}");
                $this->newLine();
            }
        }

        // Azure App Registration steps
        $this->section('Azure App Registration Steps');
        $this->line('1. Go to https://portal.azure.com');
        $this->line('2. Navigate to Azure Active Directory > App registrations');
        $this->line('3. Click "New registration"');
        $this->line('4. Enter a name for your app');
        $this->line('5. Select supported account types:');
        $this->line('   - Single tenant: Your organization only');
        $this->line('   - Multi-tenant: Any organization');
        $this->line('   - Multi-tenant + personal: Any organization + personal accounts');
        $this->line('6. Set Redirect URI:');
        $this->line('   - Platform: Web');
        if (count($panels) > 0) {
            foreach ($panels as $panel) {
                $panelId = $panel->getId();
                $redirectUrl = route("filament.{$panelId}.auth.azure.callback", [], true);
                $this->line("   - URI: {$redirectUrl}");
            }
        } else {
            $this->line('   - URI: {your-panel-path}/auth/azure/callback');
        }
        $this->line('7. Click "Register"');
        $this->line('8. Copy the Application (client) ID to AZURE_CLIENT_ID');
        $this->line('9. Go to "Certificates & secrets" > "New client secret"');
        $this->line('10. Copy the secret value to AZURE_CLIENT_SECRET');
        $this->line('11. Note your Directory (tenant) ID for AZURE_TENANT_ID');
        $this->newLine();

        // Multi-tenant notes
        $this->section('Multi-Tenant Configuration');
        $this->line('For AZURE_TENANT_ID, use:');
        $this->line('  - Single tenant: Your tenant ID');
        $this->line('  - Multi-tenant (organizations only): "organizations"');
        $this->line('  - Multi-tenant + personal: "common"');
        $this->line('  - Personal accounts only: "consumers"');
        $this->newLine();

        $this->info('Installation guide complete!');
        $this->line('Run "php artisan filament-azure-socialite:doctor" to validate your setup.');

        return self::SUCCESS;
    }

    protected function section(string $title): void
    {
        $this->newLine();
        $this->line("<fg=cyan>=== {$title} ===</>");
        $this->newLine();
    }
}

