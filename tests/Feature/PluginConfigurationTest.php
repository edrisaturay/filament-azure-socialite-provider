<?php

declare(strict_types=1);

namespace EdrisaTuray\FilamentAzureSocialite\Tests\Feature;

use EdrisaTuray\FilamentAzureSocialite\FilamentFilamentAzureSocialitePlugin;
use EdrisaTuray\FilamentAzureSocialite\Tests\TestCase;
use Filament\Panel;

class PluginConfigurationTest extends TestCase
{
    public function test_plugin_has_correct_id(): void
    {
        $plugin = FilamentAzureSocialitePlugin::make();

        $this->assertEquals('filament-azure-socialite', $plugin->getId());
    }

    public function test_plugin_fluent_api_enabled(): void
    {
        $plugin = FilamentAzureSocialitePlugin::make()
            ->enabled(true);

        $this->assertInstanceOf(FilamentAzureSocialitePlugin::class, $plugin);
    }

    public function test_plugin_fluent_api_button_label(): void
    {
        $plugin = FilamentAzureSocialitePlugin::make()
            ->buttonLabel('Custom Label');

        $this->assertInstanceOf(FilamentAzureSocialitePlugin::class, $plugin);
    }

    public function test_plugin_fluent_api_hook(): void
    {
        $plugin = FilamentAzureSocialitePlugin::make()
            ->hook('before');

        $this->assertInstanceOf(FilamentAzureSocialitePlugin::class, $plugin);
    }

    public function test_plugin_fluent_api_allow_registration(): void
    {
        $plugin = FilamentAzureSocialitePlugin::make()
            ->allowRegistration(true);

        $this->assertInstanceOf(FilamentAzureSocialitePlugin::class, $plugin);
    }

    public function test_plugin_fluent_api_allowed_domains(): void
    {
        $plugin = FilamentAzureSocialitePlugin::make()
            ->allowedDomains(['company.com']);

        $this->assertInstanceOf(FilamentAzureSocialitePlugin::class, $plugin);
    }

    public function test_plugin_fluent_api_allowed_domains_string(): void
    {
        $plugin = FilamentAzureSocialitePlugin::make()
            ->allowedDomains('company.com');

        $this->assertInstanceOf(FilamentAzureSocialitePlugin::class, $plugin);
    }

    public function test_plugin_fluent_api_allowed_tenants(): void
    {
        $plugin = FilamentAzureSocialitePlugin::make()
            ->allowedTenants(['tenant-123']);

        $this->assertInstanceOf(FilamentAzureSocialitePlugin::class, $plugin);
    }

    public function test_plugin_fluent_api_resolve_user_using(): void
    {
        $plugin = FilamentAzureSocialitePlugin::make()
            ->resolveUserUsing(function ($user) {
                return $user;
            });

        $this->assertInstanceOf(FilamentAzureSocialitePlugin::class, $plugin);
    }

    public function test_plugin_throws_exception_for_invalid_hook(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        FilamentAzureSocialitePlugin::make()
            ->hook('invalid');
    }

    public function test_plugin_chaining(): void
    {
        $plugin = FilamentAzureSocialitePlugin::make()
            ->enabled(true)
            ->buttonLabel('Login')
            ->hook('after')
            ->allowRegistration(true)
            ->allowedDomains(['company.com']);

        $this->assertInstanceOf(FilamentAzureSocialitePlugin::class, $plugin);
    }
}

