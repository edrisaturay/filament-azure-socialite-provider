<?php

declare(strict_types=1);

namespace EdrisaTuray\FilamentAzureSocialite\Tests\Feature;

use EdrisaTuray\FilamentAzureSocialite\AzureSocialiteRegistry;
use EdrisaTuray\FilamentAzureSocialite\Tests\TestCase;

class RegistryTest extends TestCase
{
    protected function tearDown(): void
    {
        // Clear registry between tests
        $reflection = new \ReflectionClass(AzureSocialiteRegistry::class);
        $property = $reflection->getProperty('configs');
        $property->setAccessible(true);
        $property->setValue(null, []);

        parent::tearDown();
    }

    public function test_registry_can_store_and_retrieve_config(): void
    {
        $panelId = 'admin';
        $config = [
            'enabled' => true,
            'buttonLabel' => 'Custom Label',
            'hook' => 'before',
        ];

        AzureSocialiteRegistry::register($panelId, $config);

        $retrieved = AzureSocialiteRegistry::getConfig($panelId);

        $this->assertEquals($config, $retrieved);
    }

    public function test_registry_returns_null_for_non_existent_panel(): void
    {
        $config = AzureSocialiteRegistry::getConfig('non-existent');

        $this->assertNull($config);
    }

    public function test_registry_is_enabled_check(): void
    {
        $panelId = 'admin';

        AzureSocialiteRegistry::register($panelId, ['enabled' => true]);
        $this->assertTrue(AzureSocialiteRegistry::isEnabled($panelId));

        AzureSocialiteRegistry::register($panelId, ['enabled' => false]);
        $this->assertFalse(AzureSocialiteRegistry::isEnabled($panelId));
    }

    public function test_registry_get_button_label(): void
    {
        $panelId = 'admin';

        AzureSocialiteRegistry::register($panelId, ['buttonLabel' => 'Custom Label']);
        $this->assertEquals('Custom Label', AzureSocialiteRegistry::getButtonLabel($panelId));

        // Test default
        $this->assertEquals('Login with Microsoft', AzureSocialiteRegistry::getButtonLabel('non-existent'));
    }

    public function test_registry_get_hook(): void
    {
        $panelId = 'admin';

        AzureSocialiteRegistry::register($panelId, ['hook' => 'before']);
        $this->assertEquals('before', AzureSocialiteRegistry::getHook($panelId));

        // Test default
        $this->assertEquals('after', AzureSocialiteRegistry::getHook('non-existent'));
    }

    public function test_registry_get_allow_registration(): void
    {
        $panelId = 'admin';

        AzureSocialiteRegistry::register($panelId, ['allowRegistration' => false]);
        $this->assertFalse(AzureSocialiteRegistry::getAllowRegistration($panelId));

        // Test default
        $this->assertTrue(AzureSocialiteRegistry::getAllowRegistration('non-existent'));
    }

    public function test_registry_get_allowed_domains(): void
    {
        $panelId = 'admin';
        $domains = ['company.com', 'partner.com'];

        AzureSocialiteRegistry::register($panelId, ['allowedDomains' => $domains]);
        $this->assertEquals($domains, AzureSocialiteRegistry::getAllowedDomains($panelId));

        // Test null
        $this->assertNull(AzureSocialiteRegistry::getAllowedDomains('non-existent'));
    }

    public function test_registry_get_allowed_tenants(): void
    {
        $panelId = 'admin';
        $tenants = ['tenant-123', 'tenant-456'];

        AzureSocialiteRegistry::register($panelId, ['allowedTenants' => $tenants]);
        $this->assertEquals($tenants, AzureSocialiteRegistry::getAllowedTenants($panelId));

        // Test null
        $this->assertNull(AzureSocialiteRegistry::getAllowedTenants('non-existent'));
    }

    public function test_registry_get_callbacks(): void
    {
        $panelId = 'admin';
        $callback = function () {};

        AzureSocialiteRegistry::register($panelId, ['resolveUserUsing' => $callback]);
        $this->assertSame($callback, AzureSocialiteRegistry::getResolveUserCallback($panelId));

        // Test null
        $this->assertNull(AzureSocialiteRegistry::getResolveUserCallback('non-existent'));
    }
}

