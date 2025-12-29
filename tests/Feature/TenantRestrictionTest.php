<?php

declare(strict_types=1);

namespace EdrisaTuray\FilamentAzureSocialite\Tests\Feature;

use EdrisaTuray\FilamentAzureSocialite\AzureSocialiteRegistry;
use EdrisaTuray\FilamentAzureSocialite\Tests\TestCase;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Mockery;

class TenantRestrictionTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_tenant_validation_from_raw_data(): void
    {
        $panelId = 'admin';

        AzureSocialiteRegistry::register($panelId, [
            'enabled' => true,
            'allowedTenants' => ['tenant-123', 'tenant-456'],
        ]);

        $controller = new \EdrisaTuray\FilamentAzureSocialite\Http\Controllers\AzureCallbackController();
        $reflection = new \ReflectionClass($controller);
        $method = $reflection->getMethod('validateTenant');
        $method->setAccessible(true);

        $azureUser = Mockery::mock(SocialiteUser::class);
        $azureUser->shouldReceive('getRaw')->andReturn(['tid' => 'tenant-123']);

        $result = $method->invoke($controller, $panelId, $azureUser);

        $this->assertTrue($result);
    }

    public function test_tenant_validation_from_jwt_token(): void
    {
        $panelId = 'admin';

        AzureSocialiteRegistry::register($panelId, [
            'enabled' => true,
            'allowedTenants' => ['tenant-123'],
        ]);

        // Create a mock JWT token
        $header = base64_encode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $payload = base64_encode(json_encode(['tid' => 'tenant-123', 'sub' => 'user-123']));
        $token = "{$header}.{$payload}.signature";

        $controller = new \EdrisaTuray\FilamentAzureSocialite\Http\Controllers\AzureCallbackController();
        $reflection = new \ReflectionClass($controller);
        $method = $reflection->getMethod('validateTenant');
        $method->setAccessible(true);

        $azureUser = Mockery::mock(SocialiteUser::class);
        $azureUser->shouldReceive('getRaw')->andReturn([]);
        $azureUser->token = $token;

        $result = $method->invoke($controller, $panelId, $azureUser);

        $this->assertTrue($result);
    }

    public function test_tenant_validation_rejects_invalid_token(): void
    {
        $panelId = 'admin';

        AzureSocialiteRegistry::register($panelId, [
            'enabled' => true,
            'allowedTenants' => ['tenant-123'],
        ]);

        $controller = new \EdrisaTuray\FilamentAzureSocialite\Http\Controllers\AzureCallbackController();
        $reflection = new \ReflectionClass($controller);
        $method = $reflection->getMethod('validateTenant');
        $method->setAccessible(true);

        $azureUser = Mockery::mock(SocialiteUser::class);
        $azureUser->shouldReceive('getRaw')->andReturn([]);
        $azureUser->token = 'invalid-token';

        $result = $method->invoke($controller, $panelId, $azureUser);

        $this->assertFalse($result);
    }

    public function test_tenant_validation_handles_missing_token(): void
    {
        $panelId = 'admin';

        AzureSocialiteRegistry::register($panelId, [
            'enabled' => true,
            'allowedTenants' => ['tenant-123'],
        ]);

        $controller = new \EdrisaTuray\FilamentAzureSocialite\Http\Controllers\AzureCallbackController();
        $reflection = new \ReflectionClass($controller);
        $method = $reflection->getMethod('validateTenant');
        $method->setAccessible(true);

        $azureUser = Mockery::mock(SocialiteUser::class);
        $azureUser->shouldReceive('getRaw')->andReturn([]);
        $azureUser->token = null;

        $result = $method->invoke($controller, $panelId, $azureUser);

        $this->assertFalse($result);
    }
}

