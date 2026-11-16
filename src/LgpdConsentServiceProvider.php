<?php

namespace JeffersonGoncalves\Filament\LgpdConsent;

use JeffersonGoncalves\FilamentPluginCore\BasePackageServiceProvider;
use Spatie\LaravelPackageTools\Package;

class LgpdConsentServiceProvider extends BasePackageServiceProvider
{
    public static string $name = 'filament-lgpd-consent';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(static::$name)
            ->hasTranslations();
    }
}
