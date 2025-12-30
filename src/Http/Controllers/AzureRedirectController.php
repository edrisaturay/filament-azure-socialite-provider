<?php

declare(strict_types=1);

namespace EdrisaTuray\FilamentAzureSocialite\Http\Controllers;

use EdrisaTuray\FilamentAzureSocialite\AzureSocialiteRegistry;
use Filament\Facades\Filament;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Laravel\Socialite\Facades\Socialite;
use SocialiteProviders\Manager\Config;

class AzureRedirectController extends Controller
{
    public function redirect(Request $request): RedirectResponse
    {
        $panel = Filament::getCurrentPanel();

        if (! $panel) {
            abort(404);
        }

        $panelId = $panel->getId();

        if (! AzureSocialiteRegistry::isEnabled($panelId)) {
            abort(404);
        }

        // Get Azure config from services config
        $azureConfig = config('services.azure', []);

        $clientId = $azureConfig['client_id'] ?? '';
        $clientSecret = $azureConfig['client_secret'] ?? '';

        if (empty($clientId) || empty($clientSecret)) {
            abort(500, 'Azure Socialite is not properly configured. Please check your services.azure configuration.');
        }

        // Use configured redirect URI if set, otherwise compute dynamically
        if (! empty($azureConfig['redirect'])) {
            $fullRedirectUri = rtrim($azureConfig['redirect'], '/');
        } else {
            // Compute redirect URI dynamically per panel
            $redirectUri = route("filament.{$panelId}.auth.azure.callback", [], false);
            $fullRedirectUri = url($redirectUri);
        }

        // Build Socialite config
        $socialiteConfig = new Config(
            $clientId,
            $clientSecret,
            $fullRedirectUri,
            [
                'tenant' => $azureConfig['tenant'] ?? 'common',
            ]
        );

        // Execute beforeRedirect callback if set
        $beforeRedirect = AzureSocialiteRegistry::getBeforeRedirectCallback($panelId);
        if ($beforeRedirect) {
            $beforeRedirect($request, $socialiteConfig);
        }

        return Socialite::driver('azure')
            ->setConfig($socialiteConfig)
            ->redirect();
    }
}

