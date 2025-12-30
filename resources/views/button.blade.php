@php
    $panelId = $panelId ?? null;
    
    if (! $panelId) {
        try {
            $panel = \Filament\Facades\Filament::getCurrentPanel();
            $panelId = $panel?->getId();
        } catch (\Exception $e) {
            // Silently fail if panel cannot be determined
            return;
        }
    }
    
    if (! $panelId) {
        return;
    }
    
    $isEnabled = \EdrisaTuray\FilamentAzureSocialite\AzureSocialiteRegistry::isEnabled($panelId);
    
    if (! $isEnabled) {
        return;
    }
    
    $buttonLabel = \EdrisaTuray\FilamentAzureSocialite\AzureSocialiteRegistry::getButtonLabel($panelId);
    $routeName = "filament.{$panelId}.auth.azure.redirect";
    
    // Check if route exists
    if (! \Illuminate\Support\Facades\Route::has($routeName)) {
        return;
    }
@endphp

<div class="mt-4">
    <a
        href="{{ route($routeName) }}"
        class="fi-btn fi-btn-color-primary fi-btn-size-lg w-full justify-center"
    >
        {{ $buttonLabel }}
    </a>
</div>

