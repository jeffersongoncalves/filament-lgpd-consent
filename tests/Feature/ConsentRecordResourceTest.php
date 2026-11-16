<?php

use JeffersonGoncalves\Filament\LgpdConsent\LgpdConsentPlugin;
use JeffersonGoncalves\Filament\LgpdConsent\Resources\ConsentRecords\ConsentRecordResource;
use JeffersonGoncalves\Filament\LgpdConsent\Resources\ConsentRecords\Pages\ListConsentRecords;
use JeffersonGoncalves\Filament\LgpdConsent\Resources\ConsentRecords\Pages\ViewConsentRecord;
use JeffersonGoncalves\Filament\LgpdConsent\Support\Labels;
use JeffersonGoncalves\Filament\LgpdConsent\Tests\Fixtures\TestUser;
use JeffersonGoncalves\LgpdConsent\Facades\LgpdConsent;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = TestUser::create(['name' => 'Maria', 'email' => 'maria@example.com']);

    $this->actingAs($this->user);
});

it('lists users and visitors consents', function () {
    $granted = LgpdConsent::grant($this->user, 'marketing', source: 'signup');
    $visitor = LgpdConsent::revoke('3f2b9c1e-visitor', 'cookies');

    Livewire::test(ListConsentRecords::class)
        ->assertSuccessful()
        ->assertCanSeeTableRecords([$granted, $visitor])
        ->assertSee('TestUser #'.$this->user->getKey())
        ->assertSee('Visitor 3f2b9c1e...')
        ->assertSee('Granted')
        ->assertSee('Revoked');
});

it('filters by purpose, decision and visitors', function () {
    $marketing = LgpdConsent::grant($this->user, 'marketing');
    $cookies = LgpdConsent::revoke('visitor-1', 'cookies');

    Livewire::test(ListConsentRecords::class)
        ->filterTable('purpose', 'marketing')
        ->assertCanSeeTableRecords([$marketing])
        ->assertCanNotSeeTableRecords([$cookies]);

    Livewire::test(ListConsentRecords::class)
        ->filterTable('action', 'revoked')
        ->assertCanSeeTableRecords([$cookies])
        ->assertCanNotSeeTableRecords([$marketing]);

    Livewire::test(ListConsentRecords::class)
        ->filterTable('visitors', true)
        ->assertCanSeeTableRecords([$cookies])
        ->assertCanNotSeeTableRecords([$marketing]);
});

it('shows the proof of consent', function () {
    $record = LgpdConsent::grant($this->user, 'marketing', version: '2026-01', source: 'signup', metadata: ['campaign' => 'black-friday']);

    Livewire::test(ViewConsentRecord::class, ['record' => $record->getKey()])
        ->assertSuccessful()
        ->assertSee('marketing')
        ->assertSee('2026-01')
        ->assertSee('signup')
        ->assertSee('black-friday');
});

it('flags grants given for older terms', function () {
    config()->set('lgpd-consent.purposes', ['marketing' => ['version' => '2026-01']]);
    $old = LgpdConsent::grant($this->user, 'marketing');

    config()->set('lgpd-consent.purposes', ['marketing' => ['version' => '2026-11']]);
    $current = LgpdConsent::grant($this->user, 'marketing');

    expect(Labels::isOutdated($old))->toBeTrue()
        ->and(Labels::isOutdated($current))->toBeFalse()
        ->and(Labels::isOutdated(LgpdConsent::revoke($this->user, 'marketing')))->toBeFalse();
});

it('is read-only and applies the fluent plugin options', function () {
    LgpdConsentPlugin::get()->navigationGroup('LGPD')->slug('consent-ledger');

    expect(ConsentRecordResource::canCreate())->toBeFalse()
        ->and(ConsentRecordResource::getPages())->toHaveKeys(['index', 'view'])->not->toHaveKey('edit')
        ->and(ConsentRecordResource::getNavigationGroup())->toBe('LGPD')
        ->and(ConsentRecordResource::getSlug())->toBe('consent-ledger');
});
