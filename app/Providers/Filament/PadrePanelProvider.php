<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\CustomLogin;
use App\Filament\Padre\Pages\Escritorio; // Importar la página de Escritorio personalizada
use App\Filament\Padre\Pages\Tareas; // Importar la página de Tareas
use App\Http\Middleware\RedirectIfCannotAccessPanel;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationItem; // Importar NavigationItem
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class PadrePanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('padre')
            ->path('padre')
            ->login(CustomLogin::class)
            ->colors([
                'primary' => Color::Sky,
            ])
            ->discoverResources(in: app_path('Filament/Padre/Resources'), for: 'App\\Filament\\Padre\\Resources')
            ->discoverPages(in: app_path('Filament/Padre/Pages'), for: 'App\\Filament\\Padre\\Pages')
            ->pages([
                Escritorio::class, // Usar la página de Escritorio personalizada
            ])
            ->navigationItems([
                NavigationItem::make('Comprensión Lectora')
                    ->url(fn (): string => Tareas::getUrl(['category' => 'compresion_lectora']))
                    ->icon('heroicon-o-book-open'),
                NavigationItem::make('Matemáticas')
                    ->url(fn (): string => Tareas::getUrl(['category' => 'matematicas']))
                    ->icon('heroicon-o-calculator'),
                NavigationItem::make('Juegos de Recreación')
                    ->url(fn (): string => Tareas::getUrl(['category' => 'recreacion']))
                    ->icon('heroicon-o-puzzle-piece'),
            ])
            ->discoverWidgets(in: app_path('Filament/Padre/Widgets'), for: 'App\\Filament\\Padre\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
            ])
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
                Authenticate::class,
            ]);
    }
}
