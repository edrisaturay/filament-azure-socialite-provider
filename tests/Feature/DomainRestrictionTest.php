<?php

declare(strict_types=1);

namespace EdrisaTuray\FilamentAzureSocialite\Tests\Feature;

use EdrisaTuray\FilamentAzureSocialite\AzureSocialiteRegistry;
use EdrisaTuray\FilamentAzureSocialite\Tests\TestCase;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Mockery;

class DomainRestrictionTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_email_domain_extraction(): void
    {
        $email = 'user@company.com';
        $domain = substr(strrchr($email, '@'), 1);

        $this->assertEquals('company.com', $domain);
    }

    public function test_domain_validation_with_multiple_allowed_domains(): void
    {
        $panelId = 'admin';

        AzureSocialiteRegistry::register($panelId, [
            'enabled' => true,
            'allowedDomains' => ['company.com', 'partner.com', 'subsidiary.com'],
        ]);

        $controller = new \EdrisaTuray\FilamentAzureSocialite\Http\Controllers\AzureCallbackController();
        $reflection = new \ReflectionClass($controller);
        $method = $reflection->getMethod('validateEmailDomain');
        $method->setAccessible(true);

        // Test each allowed domain
        foreach (['company.com', 'partner.com', 'subsidiary.com'] as $domain) {
            $azureUser = Mockery::mock(SocialiteUser::class);
            $azureUser->shouldReceive('getEmail')->andReturn("user@{$domain}");

            $result = $method->invoke($controller, $panelId, $azureUser);
            $this->assertTrue($result, "Domain {$domain} should be allowed");
        }
    }

    public function test_domain_validation_rejects_case_sensitive_domains(): void
    {
        $panelId = 'admin';

        AzureSocialiteRegistry::register($panelId, [
            'enabled' => true,
            'allowedDomains' => ['Company.com'], // Note the capital C
        ]);

        $controller = new \EdrisaTuray\FilamentAzureSocialite\Http\Controllers\AzureCallbackController();
        $reflection = new \ReflectionClass($controller);
        $method = $reflection->getMethod('validateEmailDomain');
        $method->setAccessible(true);

        $azureUser = Mockery::mock(SocialiteUser::class);
        $azureUser->shouldReceive('getEmail')->andReturn('user@company.com'); // lowercase

        $result = $method->invoke($controller, $panelId, $azureUser);

        // Should fail because of case sensitivity
        $this->assertFalse($result);
    }

    public function test_domain_validation_handles_missing_email(): void
    {
        $panelId = 'admin';

        AzureSocialiteRegistry::register($panelId, [
            'enabled' => true,
            'allowedDomains' => ['company.com'],
        ]);

        $controller = new \EdrisaTuray\FilamentAzureSocialite\Http\Controllers\AzureCallbackController();
        $reflection = new \ReflectionClass($controller);
        $method = $reflection->getMethod('validateEmailDomain');
        $method->setAccessible(true);

        $azureUser = Mockery::mock(SocialiteUser::class);
        $azureUser->shouldReceive('getEmail')->andReturn(null);

        $result = $method->invoke($controller, $panelId, $azureUser);

        $this->assertFalse($result);
    }
}

