<?php

declare(strict_types=1);

namespace EdrisaTuray\FilamentAzureSocialite\Http\Controllers;

use EdrisaTuray\FilamentAzureSocialite\AzureSocialiteRegistry;
use Filament\Facades\Filament;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Two\InvalidStateException;
use Laravel\Socialite\Facades\Socialite;
use SocialiteProviders\Manager\Config;

class AzureCallbackController extends Controller
{
    public function callback(Request $request): RedirectResponse
    {
        $panel = Filament::getCurrentPanel();

        if (! $panel) {
            abort(404);
        }

        $panelId = $panel->getId();

        if (! AzureSocialiteRegistry::isEnabled($panelId)) {
            abort(404);
        }

        try {
            // Compute redirect URI (same as in redirect controller)
            $redirectUri = route("filament.{$panelId}.auth.azure.callback", [], false);
            $fullRedirectUri = url($redirectUri);

            $azureConfig = config('services.azure', []);

            $socialiteConfig = new Config(
                $azureConfig['client_id'] ?? '',
                $azureConfig['client_secret'] ?? '',
                $fullRedirectUri,
                [
                    'tenant' => $azureConfig['tenant'] ?? 'common',
                ]
            );

            $azureUser = Socialite::driver('azure')
                ->setConfig($socialiteConfig)
                ->user();

            // Check if email is present
            if (! $azureUser->getEmail()) {
                \Filament\Notifications\Notification::make()
                    ->title('Authentication Failed')
                    ->body('Your Microsoft account does not have an email address. Please contact support.')
                    ->danger()
                    ->send();

                return redirect()->route("filament.{$panelId}.auth.login");
            }

            // Validate email domain
            if (! $this->validateEmailDomain($panelId, $azureUser)) {
                \Filament\Notifications\Notification::make()
                    ->title('Authentication Failed')
                    ->body('Your email domain is not authorized to access this application.')
                    ->danger()
                    ->send();

                return redirect()->route("filament.{$panelId}.auth.login");
            }

            // Validate tenant
            if (! $this->validateTenant($panelId, $azureUser)) {
                \Filament\Notifications\Notification::make()
                    ->title('Authentication Failed')
                    ->body('Your organization is not authorized to access this application.')
                    ->danger()
                    ->send();

                return redirect()->route("filament.{$panelId}.auth.login");
            }

            // Resolve or create user
            $user = $this->resolveUser($panelId, $azureUser);

            if (! $user) {
                \Filament\Notifications\Notification::make()
                    ->title('Authentication Failed')
                    ->body('User registration is not allowed.')
                    ->danger()
                    ->send();

                return redirect()->route("filament.{$panelId}.auth.login");
            }

            // Execute afterUserResolved callback if set
            $afterUserResolved = AzureSocialiteRegistry::getAfterUserResolvedCallback($panelId);
            if ($afterUserResolved) {
                $afterUserResolved($user, $azureUser);
            }

            // Login user with panel guard
            $guard = $panel->getAuthGuard();
            Auth::guard($guard)->login($user, true);

            // Safe redirect
            $intended = $request->session()->pull('url.intended', $panel->getUrl());

            // Ensure redirect is within panel scope
            if (! str_starts_with($intended, $panel->getUrl())) {
                $intended = $panel->getUrl();
            }

            return redirect()->to($intended);
        } catch (InvalidStateException $e) {
            // User canceled or state mismatch
            \Filament\Notifications\Notification::make()
                ->title('Authentication Canceled')
                ->body('The authentication process was canceled. Please try again.')
                ->warning()
                ->send();

            return redirect()->route("filament.{$panelId}.auth.login");
        } catch (\Laravel\Socialite\Two\ProviderException $e) {
            // Provider-specific errors
            if (app()->environment(['local', 'dev', 'testing'])) {
                Log::error('Azure Socialite provider error', [
                    'error' => $e->getMessage(),
                ]);
            }

            \Filament\Notifications\Notification::make()
                ->title('Authentication Failed')
                ->body('An error occurred during authentication. Please try again.')
                ->danger()
                ->send();

            return redirect()->route("filament.{$panelId}.auth.login");
        } catch (\Exception $e) {
            // Log error in local/dev
            if (app()->environment(['local', 'dev', 'testing'])) {
                Log::error('Azure Socialite callback error', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }

            \Filament\Notifications\Notification::make()
                ->title('Authentication Failed')
                ->body('An error occurred during authentication. Please try again.')
                ->danger()
                ->send();

            return redirect()->route("filament.{$panelId}.auth.login");
        }
    }

    protected function validateEmailDomain(string $panelId, SocialiteUser $azureUser): bool
    {
        $allowedDomains = AzureSocialiteRegistry::getAllowedDomains($panelId);

        if (! $allowedDomains) {
            return true; // No restriction
        }

        $email = $azureUser->getEmail();
        if (! $email) {
            return false;
        }

        $domain = substr(strrchr($email, '@'), 1);

        return in_array($domain, $allowedDomains, true);
    }

    protected function validateTenant(string $panelId, SocialiteUser $azureUser): bool
    {
        $allowedTenants = AzureSocialiteRegistry::getAllowedTenants($panelId);

        if (! $allowedTenants) {
            return true; // No restriction
        }

        $tenantId = null;

        // Try to get tenant ID from user's raw data first (Azure provider may set this)
        if (method_exists($azureUser, 'getRaw') && is_array($azureUser->getRaw())) {
            $raw = $azureUser->getRaw();
            $tenantId = $raw['tid'] ?? $raw['tenantId'] ?? null;
        }

        // If not found, try to extract from token (JWT)
        if (! $tenantId) {
            $token = $azureUser->token ?? null;
            if ($token) {
                try {
                    $parts = explode('.', $token);
                    if (count($parts) === 3) {
                        $payload = json_decode(base64_decode($parts[1]), true);
                        $tenantId = $payload['tid'] ?? null;
                    }
                } catch (\Exception $e) {
                    // Ignore token parsing errors
                }
            }
        }

        if (! $tenantId) {
            return false;
        }

        return in_array($tenantId, $allowedTenants, true);
    }

    protected function resolveUser(string $panelId, SocialiteUser $azureUser): ?Authenticatable
    {
        $customResolver = AzureSocialiteRegistry::getResolveUserCallback($panelId);

        if ($customResolver) {
            $user = $customResolver($azureUser);
            if ($user instanceof Authenticatable) {
                return $user;
            }
        }

        // Default resolver
        return $this->defaultResolveUser($panelId, $azureUser);
    }

    protected function defaultResolveUser(string $panelId, SocialiteUser $azureUser): ?Authenticatable
    {
        $email = $azureUser->getEmail();

        if (! $email) {
            return null;
        }

        $userClass = config('auth.providers.users.model', \App\Models\User::class);

        $user = $userClass::where('email', $email)->first();

        if ($user) {
            // Update user data if needed
            $updateData = [];
            if ($azureUser->getName() && $user->name !== $azureUser->getName()) {
                $updateData['name'] = $azureUser->getName();
            }

            // Store Azure ID if column exists
            if (method_exists($user, 'getFillable') && in_array('azure_id', $user->getFillable())) {
                $updateData['azure_id'] = $azureUser->getId();
            }

            if (! empty($updateData)) {
                $user->update($updateData);
            }

            return $user;
        }

        // User not found - check if registration is allowed
        if (! AzureSocialiteRegistry::getAllowRegistration($panelId)) {
            return null;
        }

        // Create new user
        $userData = [
            'email' => $email,
            'name' => $azureUser->getName() ?? 'User',
            'password' => Hash::make(Str::random(24)),
        ];

        // Add Azure ID if column exists
        $user = new $userClass();
        if (method_exists($user, 'getFillable')) {
            $fillable = $user->getFillable();
            if (in_array('azure_id', $fillable)) {
                $userData['azure_id'] = $azureUser->getId();
            }
        }

        try {
            return $userClass::create($userData);
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle unique constraint violations (e.g., email already exists)
            if (str_contains($e->getMessage(), 'Duplicate entry')) {
                // Try to fetch the user again (race condition)
                return $userClass::where('email', $email)->first();
            }
            throw $e;
        }
    }
}

