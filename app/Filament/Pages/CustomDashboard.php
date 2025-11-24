<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use App\Filament\Widgets\TasksChart;
use App\Filament\Widgets\DashboardStatsOverview;
use App\Filament\Widgets\TaskCompletionsChart;
// Removed use Filament\Widgets\WidgetGroup; // Remove this line

// Removed App\Filament\Widgets\PsychologistStatsOverview;
// Removed Filament\Widgets; // if unused

class CustomDashboard extends BaseDashboard
{
    protected static ?string $title = 'Dashboard';

    public function getWidgets(): array
    {
        return [
            DashboardStatsOverview::class, // The 3 activity cards
            TasksChart::class, // First chart
            TaskCompletionsChart::class, // Second chart
        ];
    }

    public function getColumns(): int | string | array
    {
        return 2; // Define a 2-column grid for the dashboard
    }
}
