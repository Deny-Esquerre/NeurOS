<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class HeartRateMonitor extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationLabel = 'Pulseras Activas';

    protected static ?string $title = 'Escanear Dispositivos';

    protected static string $view = 'filament.pages.heart-rate-monitor';
}
