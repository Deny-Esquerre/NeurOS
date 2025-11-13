<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\CustomLogin;
use App\Http\Middleware\RedirectIfCannotAccessPanel;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class PsicologoPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('psicologo')
            ->path('psicologo')
            ->login(CustomLogin::class)
            ->authGuard('web')
            ->homeUrl('/psicologo')
            ->colors([
                'primary' => Color::Cyan, // Celeste color
            ])
            ->discoverResources(
                in: app_path('Filament/Psicologo/Resources'),
                for: 'App\\Filament\\Psicologo\\Resources'
            )
            ->discoverPages(
                in: app_path('Filament/Psicologo/Pages'),
                for: 'App\\Filament\\Psicologo\\Pages'
            )
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(
                in: app_path('Filament/Psicologo/Widgets'),
                for: 'App\\Filament\\Psicologo\\Widgets'
            )
            ->widgets([])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                RedirectIfCannotAccessPanel::class, // Add this line
            ])
            ->authMiddleware([
                \App\Http\Middleware\PsicologoAuthMiddleware::class,
            ]);
    }
}
