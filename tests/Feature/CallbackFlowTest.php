<?php

declare(strict_types=1);

namespace EdrisaTuray\FilamentAzureSocialite\Tests\Feature;

use EdrisaTuray\FilamentAzureSocialite\AzureSocialiteRegistry;
use EdrisaTuray\FilamentAzureSocialite\Tests\TestCase;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Mockery;

class CallbackFlowTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_domain_validation_allows_authorized_domains(): void
    {
        $panelId = 'admin';

        AzureSocialiteRegistry::register($panelId, [
            'enabled' => true,
            'allowedDomains' => ['company.com', 'partner.com'],
        ]);

        $azureUser = Mockery::mock(SocialiteUser::class);
        $azureUser->shouldReceive('getEmail')->andReturn('user@company.com');

        $controller = new \EdrisaTuray\FilamentAzureSocialite\Http\Controllers\AzureCallbackController();
        $reflection = new \ReflectionClass($controller);
        $method = $reflection->getMethod('validateEmailDomain');
        $method->setAccessible(true);

        $result = $method->invoke($controller, $panelId, $azureUser);

        $this->assertTrue($result);
    }

    public function test_domain_validation_rejects_unauthorized_domains(): void
    {
        $panelId = 'admin';

        AzureSocialiteRegistry::register($panelId, [
            'enabled' => true,
            'allowedDomains' => ['company.com'],
        ]);

        $azureUser = Mockery::mock(SocialiteUser::class);
        $azureUser->shouldReceive('getEmail')->andReturn('user@unauthorized.com');

        $controller = new \EdrisaTuray\FilamentAzureSocialite\Http\Controllers\AzureCallbackController();
        $reflection = new \ReflectionClass($controller);
        $method = $reflection->getMethod('validateEmailDomain');
        $method->setAccessible(true);

        $result = $method->invoke($controller, $panelId, $azureUser);

        $this->assertFalse($result);
    }

    public function test_domain_validation_allows_all_when_no_restriction(): void
    {
        $panelId = 'admin';

        AzureSocialiteRegistry::register($panelId, [
            'enabled' => true,
            'allowedDomains' => null,
        ]);

        $azureUser = Mockery::mock(SocialiteUser::class);
        $azureUser->shouldReceive('getEmail')->andReturn('user@anydomain.com');

        $controller = new \EdrisaTuray\FilamentAzureSocialite\Http\Controllers\AzureCallbackController();
        $reflection = new \ReflectionClass($controller);
        $method = $reflection->getMethod('validateEmailDomain');
        $method->setAccessible(true);

        $result = $method->invoke($controller, $panelId, $azureUser);

        $this->assertTrue($result);
    }

    public function test_tenant_validation_allows_authorized_tenants(): void
    {
        $panelId = 'admin';

        AzureSocialiteRegistry::register($panelId, [
            'enabled' => true,
            'allowedTenants' => ['tenant-123', 'tenant-456'],
        ]);

        $azureUser = Mockery::mock(SocialiteUser::class);
        $azureUser->shouldReceive('getRaw')->andReturn(['tid' => 'tenant-123']);

        $controller = new \EdrisaTuray\FilamentAzureSocialite\Http\Controllers\AzureCallbackController();
        $reflection = new \ReflectionClass($controller);
        $method = $reflection->getMethod('validateTenant');
        $method->setAccessible(true);

        $result = $method->invoke($controller, $panelId, $azureUser);

        $this->assertTrue($result);
    }

    public function test_tenant_validation_rejects_unauthorized_tenants(): void
    {
        $panelId = 'admin';

        AzureSocialiteRegistry::register($panelId, [
            'enabled' => true,
            'allowedTenants' => ['tenant-123'],
        ]);

        $azureUser = Mockery::mock(SocialiteUser::class);
        $azureUser->shouldReceive('getRaw')->andReturn(['tid' => 'tenant-999']);

        $controller = new \EdrisaTuray\FilamentAzureSocialite\Http\Controllers\AzureCallbackController();
        $reflection = new \ReflectionClass($controller);
        $method = $reflection->getMethod('validateTenant');
        $method->setAccessible(true);

        $result = $method->invoke($controller, $panelId, $azureUser);

        $this->assertFalse($result);
    }
}
