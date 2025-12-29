<?php

declare(strict_types=1);

namespace EdrisaTuray\FilamentAzureSocialite;

use Closure;

class AzureSocialiteRegistry
{
    protected static array $configs = [];

    public static function register(string $panelId, array $config): void
    {
        static::$configs[$panelId] = $config;
    }

    public static function getConfig(string $panelId): ?array
    {
        return static::$configs[$panelId] ?? null;
    }

    public static function isEnabled(string $panelId): bool
    {
        $config = static::getConfig($panelId);

        return $config['enabled'] ?? false;
    }

    public static function getButtonLabel(string $panelId): string
    {
        $config = static::getConfig($panelId);

        return $config['buttonLabel'] ?? 'Login with Microsoft';
    }

    public static function getHook(string $panelId): string
    {
        $config = static::getConfig($panelId);

        return $config['hook'] ?? 'after';
    }

    public static function getAllowRegistration(string $panelId): bool
    {
        $config = static::getConfig($panelId);

        return $config['allowRegistration'] ?? true;
    }

    public static function getAllowedDomains(string $panelId): ?array
    {
        $config = static::getConfig($panelId);

        return $config['allowedDomains'] ?? null;
    }

    public static function getAllowedTenants(string $panelId): ?array
    {
        $config = static::getConfig($panelId);

        return $config['allowedTenants'] ?? null;
    }

    public static function getResolveUserCallback(string $panelId): ?Closure
    {
        $config = static::getConfig($panelId);

        return $config['resolveUserUsing'] ?? null;
    }

    public static function getBeforeRedirectCallback(string $panelId): ?Closure
    {
        $config = static::getConfig($panelId);

        return $config['beforeRedirect'] ?? null;
    }

    public static function getAfterUserResolvedCallback(string $panelId): ?Closure
    {
        $config = static::getConfig($panelId);

        return $config['afterUserResolved'] ?? null;
    }
}

