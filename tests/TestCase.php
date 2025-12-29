<?php

declare(strict_types=1);

namespace EdrisaTuray\FilamentAzureSocialite\Tests;

use EdrisaTuray\FilamentAzureSocialite\FilamentAzureSocialiteServiceProvider;
use Filament\FilamentServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Set up basic configuration
        $this->app['config']->set('database.default', 'testing');
        $this->app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        // Set up auth config
        $this->app['config']->set('auth.providers.users.model', \App\Models\User::class);

        // Set up services config
        $this->app['config']->set('services.azure', [
            'client_id' => 'test-client-id',
            'client_secret' => 'test-client-secret',
            'tenant' => 'common',
        ]);

        // Create users table
        Schema::create('users', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('azure_id')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    protected function getPackageProviders($app): array
    {
        return [
            FilamentServiceProvider::class,
            FilamentAzureSocialiteServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        // Set up app URL
        $app['config']->set('app.url', 'http://localhost');

        // Set up session
        $app['config']->set('session.driver', 'array');
    }
}

