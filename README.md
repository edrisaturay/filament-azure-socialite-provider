# Filament Azure Socialite

[![Latest Version](https://img.shields.io/packagist/v/edrisaturay/filament-azure-socialite.svg?style=flat-square)](https://packagist.org/packages/edrisaturay/filament-azure-socialite)
[![Total Downloads](https://img.shields.io/packagist/dt/edrisaturay/filament-azure-socialite.svg?style=flat-square)](https://packagist.org/packages/edrisaturay/filament-azure-socialite)

A production-ready Filament plugin that adds "Login with Microsoft" (Azure AD) authentication to your Filament panels using Laravel Socialite and SocialiteProviders.

## Features

- ✅ **Multi-Panel Support** - Works with multiple Filament panels
- ✅ **Auto-Registration** - Automatically registers the Socialite provider
- ✅ **Auto Redirect URI** - Computes redirect URIs dynamically (no manual configuration needed)
- ✅ **Security Features** - Email domain and tenant ID restrictions
- ✅ **Flexible Configuration** - Fluent API for easy setup
- ✅ **Enterprise Ready** - Production-quality error handling and logging
- ✅ **Filament v3 & v4** - Compatible with both versions
- ✅ **Laravel 10-12** - Supports all recent Laravel versions

## Requirements

- PHP >= 8.1
- Laravel >= 10.0
- Filament >= 3.0

## Installation

Install the package via Composer:

```bash
composer require edrisaturay/filament-azure-socialite
```

## Configuration

### 1. Azure App Registration

First, you need to register your application in Azure AD:

1. Go to [Azure Portal](https://portal.azure.com)
2. Navigate to **Azure Active Directory** > **App registrations**
3. Click **New registration**
4. Enter a name for your app
5. Select supported account types:
   - **Single tenant**: Your organization only
   - **Multi-tenant**: Any organization
   - **Multi-tenant + personal**: Any organization + personal accounts
6. Set **Redirect URI**:
   - Platform: **Web**
   - URI: `{your-app-url}/{panel-path}/auth/azure/callback`
   - Example: `https://example.com/admin/auth/azure/callback`
7. Click **Register**
8. Copy the **Application (client) ID** to your `.env` file
9. Go to **Certificates & secrets** > **New client secret**
10. Copy the secret value to your `.env` file
11. Note your **Directory (tenant) ID** for the `.env` file

### 2. Environment Variables

Add these to your `.env` file:

```env
AZURE_CLIENT_ID=your-client-id
AZURE_CLIENT_SECRET=your-client-secret
AZURE_TENANT_ID=common
```

**Note**: The `AZURE_REDIRECT_URI` is optional - it will be auto-computed per panel if not set.

### 3. Services Configuration

Add this to your `config/services.php`:

```php
'azure' => [
    'client_id' => env('AZURE_CLIENT_ID'),
    'client_secret' => env('AZURE_CLIENT_SECRET'),
    'redirect' => env('AZURE_REDIRECT_URI'), // Optional
    'tenant' => env('AZURE_TENANT_ID', 'common'),
    'proxy' => env('PROXY'), // Optional
],
```

### 4. Multi-Tenant Configuration

For `AZURE_TENANT_ID`, use:

- **Single tenant**: Your tenant ID (e.g., `12345678-1234-1234-1234-123456789012`)
- **Multi-tenant (organizations only)**: `organizations`
- **Multi-tenant + personal**: `common` (default)
- **Personal accounts only**: `consumers`

### 5. Register the Plugin

#### Filament v4

In your panel provider (e.g., `app/Providers/Filament/AdminPanelProvider.php`):

```php
use EdrisaTuray\FilamentAzureSocialite\AzureSocialitePlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        ->plugins([
            AzureSocialitePlugin::make()
                ->enabled()
                ->buttonLabel('Login with Microsoft')
                ->hook('after') // or 'before'
                ->allowRegistration(true)
                ->allowedDomains(['company.com']) // Optional
                ->allowedTenants(['tenant-id']) // Optional
        ]);
}
```

#### Filament v3

In your panel provider:

```php
use EdrisaTuray\FilamentAzureSocialite\AzureSocialitePlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        ->plugins([
            AzureSocialitePlugin::make()
                ->enabled()
                ->buttonLabel('Login with Microsoft')
                ->hook('after')
                ->allowRegistration(true)
        ]);
}
```

## Configuration Options

### Basic Configuration

```php
AzureSocialitePlugin::make()
    ->enabled(true)                    // Enable/disable the plugin
    ->buttonLabel('Login with Microsoft') // Button text
    ->hook('after')                    // 'before' or 'after' the login form
    ->allowRegistration(true)          // Allow creating new users
```

### Security Configuration

```php
AzureSocialitePlugin::make()
    ->allowedDomains(['company.com', 'partner.com']) // Restrict to specific email domains
    ->allowedTenants(['tenant-id-1', 'tenant-id-2']) // Restrict to specific Azure tenants
```

### Custom User Resolution

```php
AzureSocialitePlugin::make()
    ->resolveUserUsing(function ($azureUser) {
        // Custom logic to find or create user
        return User::firstOrCreate(
            ['email' => $azureUser->getEmail()],
            [
                'name' => $azureUser->getName(),
                'role' => 'user',
            ]
        );
    })
```

### Callbacks

```php
AzureSocialitePlugin::make()
    ->beforeRedirect(function ($request, $config) {
        // Called before redirecting to Azure
        // You can modify $config here
    })
    ->afterUserResolved(function ($user, $azureUser) {
        // Called after user is resolved/created
        // Useful for syncing additional data
    })
```

## Artisan Commands

### Installation Guide

Get step-by-step installation instructions:

```bash
php artisan filament-azure-socialite:install
```

This command will:
- Show required environment variables
- Display computed redirect URLs for each panel
- Provide Azure App Registration steps

### Configuration Doctor

Validate your configuration:

```bash
php artisan filament-azure-socialite:doctor
```

This command checks:
- Services configuration
- Route registration per panel
- Socialite listener registration
- Environment configuration (APP_URL, HTTPS, proxy)

## Multi-Panel Setup

The plugin supports multiple Filament panels. Each panel can have its own configuration:

```php
// Admin Panel
AdminPanelProvider::class => [
    AzureSocialitePlugin::make()
        ->enabled()
        ->allowedDomains(['admin.company.com'])
],

// User Panel
UserPanelProvider::class => [
    AzureSocialitePlugin::make()
        ->enabled()
        ->allowedDomains(['company.com'])
        ->allowRegistration(true)
],
```

## User Model

The plugin works with any user model. By default, it uses `App\Models\User`.

### Storing Azure ID

If you want to store the Azure user ID, add an `azure_id` column to your users table:

```php
Schema::table('users', function (Blueprint $table) {
    $table->string('azure_id')->nullable()->unique();
});
```

The plugin will automatically store the Azure ID if the column exists.

## Error Handling

The plugin provides user-friendly error messages via Filament notifications:

- **Invalid email domain**: "Your email domain is not authorized"
- **Invalid tenant**: "Your organization is not authorized"
- **Registration disabled**: "User registration is not allowed"
- **General errors**: "An error occurred during authentication"

Errors are logged in local/dev environments for debugging.

## Troubleshooting

### Button Not Showing

1. Ensure the plugin is enabled: `->enabled()`
2. Check that routes are registered: `php artisan route:list | grep azure`
3. Run the doctor command: `php artisan filament-azure-socialite:doctor`

### Redirect URI Mismatch

The redirect URI in Azure must match exactly. The plugin auto-computes the URI, but you can verify it:

```bash
php artisan filament-azure-socialite:install
```

Make sure the redirect URI in Azure matches the computed one.

### HTTPS Required

Azure requires HTTPS for production redirect URIs. If testing locally, you can use `http://localhost`, but production must use HTTPS.

### Proxy Configuration

If you're behind a proxy, set the `PROXY` environment variable:

```env
PROXY=http://proxy.example.com:8080
```

## Testing

The package includes tests using Pest/PHPUnit and Orchestra Testbench. Run tests with:

```bash
composer test
```

## Security Considerations

1. **Always use HTTPS in production** - Azure requires it
2. **Restrict email domains** - Use `allowedDomains()` for enterprise apps
3. **Restrict tenants** - Use `allowedTenants()` for multi-tenant apps
4. **Disable registration** - Set `allowRegistration(false)` if you only want existing users
5. **Validate redirects** - The plugin only redirects within the panel scope

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.

## Support

For issues and questions, please open an issue on [GitHub](https://github.com/edrisaturay/filament-azure-socialite).

# filament-azure-socialite-provider
