<?php

declare(strict_types=1);

namespace EdrisaTuray\FilamentAzureSocialite;

use Closure;
use Filament\Contracts\Plugin as PluginContract;
use Filament\Panel;

class FilamentAzureSocialitePlugin implements PluginContract
{
    protected bool $enabled = true;

    protected string $buttonLabel = 'Login with Microsoft';

    protected string $hook = 'after';

    protected bool $allowRegistration = true;

    protected ?array $allowedDomains = null;

    protected ?array $allowedTenants = null;

    protected ?Closure $resolveUserUsing = null;

    protected ?Closure $beforeRedirect = null;

    protected ?Closure $afterUserResolved = null;

    public static function make(): static
    {
        return app(static::class);
    }

    public function getId(): string
    {
        return 'filament-azure-socialite';
    }

    public function register(Panel $panel): void
    {
        $panelId = $panel->getId();

        AzureSocialiteRegistry::register($panelId, [
            'enabled' => $this->enabled,
            'buttonLabel' => $this->buttonLabel,
            'hook' => $this->hook,
            'allowRegistration' => $this->allowRegistration,
            'allowedDomains' => $this->allowedDomains,
            'allowedTenants' => $this->allowedTenants,
            'resolveUserUsing' => $this->resolveUserUsing,
            'beforeRedirect' => $this->beforeRedirect,
            'afterUserResolved' => $this->afterUserResolved,
        ]);
    }

    public function boot(Panel $panel): void
    {
        // Nothing to boot
    }

    public function enabled(bool $enabled = true): static
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function buttonLabel(string $label): static
    {
        $this->buttonLabel = $label;

        return $this;
    }

    public function hook(string $hook): static
    {
        if (! in_array($hook, ['before', 'after'], true)) {
            throw new \InvalidArgumentException("Hook must be 'before' or 'after'");
        }

        $this->hook = $hook;

        return $this;
    }

    public function allowRegistration(bool $allow = true): static
    {
        $this->allowRegistration = $allow;

        return $this;
    }

    public function allowedDomains(array|string|null $domains): static
    {
        if (is_string($domains)) {
            $domains = [$domains];
        }

        $this->allowedDomains = $domains;

        return $this;
    }

    public function allowedTenants(array|string|null $tenants): static
    {
        if (is_string($tenants)) {
            $tenants = [$tenants];
        }

        $this->allowedTenants = $tenants;

        return $this;
    }

    public function resolveUserUsing(Closure $callback): static
    {
        $this->resolveUserUsing = $callback;

        return $this;
    }

    public function beforeRedirect(?Closure $callback): static
    {
        $this->beforeRedirect = $callback;

        return $this;
    }

    public function afterUserResolved(?Closure $callback): static
    {
        $this->afterUserResolved = $callback;

        return $this;
    }
}

