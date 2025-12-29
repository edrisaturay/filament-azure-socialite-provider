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

<div class="fi-azure-socialite-button mt-4">
    <a
        href="{{ route($routeName) }}"
        class="fi-btn fi-btn-color-gray fi-btn-size-lg fi-btn-outline flex w-full items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 font-semibold text-gray-700 shadow-sm transition duration-75 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
    >
        <svg class="h-5 w-5" viewBox="0 0 23 23" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M0 0h11.377v11.372H0z" fill="#f25022"/>
            <path d="M12.623 0H24v11.372H12.623z" fill="#00a4ef"/>
            <path d="M0 12.628h11.377V24H0z" fill="#7fba00"/>
            <path d="M12.623 12.628H24V24H12.623z" fill="#ffb900"/>
        </svg>
        <span>{{ $buttonLabel }}</span>
    </a>
</div>

