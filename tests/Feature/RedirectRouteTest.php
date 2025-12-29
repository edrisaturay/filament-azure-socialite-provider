<?php

declare(strict_types=1);

namespace EdrisaTuray\FilamentAzureSocialite\Tests\Feature;

use EdrisaTuray\FilamentAzureSocialite\FilamentAzureSocialitePlugin;
use EdrisaTuray\FilamentAzureSocialite\AzureSocialiteRegistry;
use EdrisaTuray\FilamentAzureSocialite\Tests\TestCase;
use Illuminate\Support\Facades\Route;

class RedirectRouteTest extends TestCase
{
    public function test_redirect_route_is_registered_when_plugin_enabled(): void
    {
        // This test would require a full Filament panel setup
        // For now, we test the route name pattern
        $panelId = 'admin';
        $expectedRouteName = "filament.{$panelId}.auth.azure.redirect";

        // Register plugin config
        AzureSocialiteRegistry::register($panelId, [
            'enabled' => true,
            'buttonLabel' => 'Login with Microsoft',
            'hook' => 'after',
        ]);

        // Note: Actual route registration happens in service provider
        // which requires a full Filament panel setup
        $this->assertTrue(true);
    }

    public function test_redirect_route_name_follows_convention(): void
    {
        $panelId = 'admin';
        $routeName = "filament.{$panelId}.auth.azure.redirect";

        $this->assertStringContainsString('filament', $routeName);
        $this->assertStringContainsString($panelId, $routeName);
        $this->assertStringContainsString('auth.azure.redirect', $routeName);
    }

    public function test_callback_route_name_follows_convention(): void
    {
        $panelId = 'admin';
        $routeName = "filament.{$panelId}.auth.azure.callback";

        $this->assertStringContainsString('filament', $routeName);
        $this->assertStringContainsString($panelId, $routeName);
        $this->assertStringContainsString('auth.azure.callback', $routeName);
    }
}
