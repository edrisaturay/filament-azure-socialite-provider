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
        class="fi-color fi-color-primary fi-bg-color-400 hover:fi-bg-color-300 dark:fi-bg-color-600 dark:hover:fi-bg-color-500 fi-text-color-900 hover:fi-text-color-800 dark:fi-text-color-950 dark:hover:fi-text-color-950 fi-btn fi-size-md w-full flex items-center justify-center gap-x-1.5 rounded-lg font-semibold outline-none transition duration-75 focus-visible:outline-2 focus-visible:outline-offset-2 disabled:opacity-70"
    >
        {{ $buttonLabel }}
    </a>
</div>

