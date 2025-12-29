<?php

declare(strict_types=1);

namespace EdrisaTuray\FilamentAzureSocialite\Tests\Feature;

use EdrisaTuray\FilamentAzureSocialite\FilamentFilamentAzureSocialitePlugin;
use EdrisaTuray\FilamentAzureSocialite\AzureSocialiteRegistry;
use EdrisaTuray\FilamentAzureSocialite\Tests\TestCase;
use Filament\Facades\Filament;
use Filament\Panel;
use Illuminate\Support\Facades\View;

class RenderHookTest extends TestCase
{
    public function test_registry_stores_config_for_panel(): void
    {
        $panelId = 'admin';

        AzureSocialiteRegistry::register($panelId, [
            'enabled' => true,
            'buttonLabel' => 'Login with Microsoft',
            'hook' => 'after',
        ]);

        $this->assertTrue(AzureSocialiteRegistry::isEnabled($panelId));
        $this->assertEquals('Login with Microsoft', AzureSocialiteRegistry::getButtonLabel($panelId));
        $this->assertEquals('after', AzureSocialiteRegistry::getHook($panelId));
    }

    public function test_registry_returns_false_when_panel_not_enabled(): void
    {
        $panelId = 'admin';

        $this->assertFalse(AzureSocialiteRegistry::isEnabled($panelId));
    }

    public function test_registry_returns_default_values_when_config_not_set(): void
    {
        $panelId = 'admin';

        // Should return defaults when config not set
        $this->assertFalse(AzureSocialiteRegistry::isEnabled($panelId));
        $this->assertEquals('Login with Microsoft', AzureSocialiteRegistry::getButtonLabel($panelId));
        $this->assertEquals('after', AzureSocialiteRegistry::getHook($panelId));
    }

    public function test_plugin_registers_config_in_registry(): void
    {
        $panel = $this->createMock(Panel::class);
        $panel->method('getId')->willReturn('admin');

        $plugin = FilamentAzureSocialitePlugin::make()
            ->enabled(true)
            ->buttonLabel('Custom Label')
            ->hook('before');

        $plugin->register($panel);

        $this->assertTrue(AzureSocialiteRegistry::isEnabled('admin'));
        $this->assertEquals('Custom Label', AzureSocialiteRegistry::getButtonLabel('admin'));
        $this->assertEquals('before', AzureSocialiteRegistry::getHook('admin'));
    }

    public function test_plugin_can_be_disabled(): void
    {
        $panel = $this->createMock(Panel::class);
        $panel->method('getId')->willReturn('admin');

        $plugin = FilamentAzureSocialitePlugin::make()
            ->enabled(false);

        $plugin->register($panel);

        $this->assertFalse(AzureSocialiteRegistry::isEnabled('admin'));
    }
}
