<?php

declare(strict_types=1);

namespace EdrisaTuray\FilamentAzureSocialite\Tests\Feature;

use EdrisaTuray\FilamentAzureSocialite\AzureSocialitePlugin;
use EdrisaTuray\FilamentAzureSocialite\Tests\TestCase;
use Filament\Panel;

class PluginConfigurationTest extends TestCase
{
    public function test_plugin_has_correct_id(): void
    {
        $plugin = AzureSocialitePlugin::make();

        $this->assertEquals('filament-azure-socialite', $plugin->getId());
    }

    public function test_plugin_fluent_api_enabled(): void
    {
        $plugin = AzureSocialitePlugin::make()
            ->enabled(true);

        $this->assertInstanceOf(AzureSocialitePlugin::class, $plugin);
    }

    public function test_plugin_fluent_api_button_label(): void
    {
        $plugin = AzureSocialitePlugin::make()
            ->buttonLabel('Custom Label');

        $this->assertInstanceOf(AzureSocialitePlugin::class, $plugin);
    }

    public function test_plugin_fluent_api_hook(): void
    {
        $plugin = AzureSocialitePlugin::make()
            ->hook('before');

        $this->assertInstanceOf(AzureSocialitePlugin::class, $plugin);
    }

    public function test_plugin_fluent_api_allow_registration(): void
    {
        $plugin = AzureSocialitePlugin::make()
            ->allowRegistration(true);

        $this->assertInstanceOf(AzureSocialitePlugin::class, $plugin);
    }

    public function test_plugin_fluent_api_allowed_domains(): void
    {
        $plugin = AzureSocialitePlugin::make()
            ->allowedDomains(['company.com']);

        $this->assertInstanceOf(AzureSocialitePlugin::class, $plugin);
    }

    public function test_plugin_fluent_api_allowed_domains_string(): void
    {
        $plugin = AzureSocialitePlugin::make()
            ->allowedDomains('company.com');

        $this->assertInstanceOf(AzureSocialitePlugin::class, $plugin);
    }

    public function test_plugin_fluent_api_allowed_tenants(): void
    {
        $plugin = AzureSocialitePlugin::make()
            ->allowedTenants(['tenant-123']);

        $this->assertInstanceOf(AzureSocialitePlugin::class, $plugin);
    }

    public function test_plugin_fluent_api_resolve_user_using(): void
    {
        $plugin = AzureSocialitePlugin::make()
            ->resolveUserUsing(function ($user) {
                return $user;
            });

        $this->assertInstanceOf(AzureSocialitePlugin::class, $plugin);
    }

    public function test_plugin_throws_exception_for_invalid_hook(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        AzureSocialitePlugin::make()
            ->hook('invalid');
    }

    public function test_plugin_chaining(): void
    {
        $plugin = AzureSocialitePlugin::make()
            ->enabled(true)
            ->buttonLabel('Login')
            ->hook('after')
            ->allowRegistration(true)
            ->allowedDomains(['company.com']);

        $this->assertInstanceOf(AzureSocialitePlugin::class, $plugin);
    }
}

