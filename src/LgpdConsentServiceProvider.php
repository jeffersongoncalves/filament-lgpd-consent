<?php

namespace JeffersonGoncalves\Filament\LgpdConsent;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LgpdConsentServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('filament-lgpd-consent')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigrations();
    }
}
