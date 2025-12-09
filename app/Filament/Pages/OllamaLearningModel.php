<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\OllamaRequestLog; // Import the model

class OllamaLearningModel extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static string $view = 'filament.pages.ollama-learning-model';

    protected static ?string $navigationLabel = 'Modelo Ollama';
    protected static ?string $title = 'Modelo Ollama';
    protected static ?string $slug = 'ollama-learning-model';

    protected static ?string $navigationGroup = 'Modelos de Aprendizajes';

    public $totalRequests;
    public $totalSuccessfulRequests; // Added
    public $totalFailedRequests; // Added
    public $successRate; // Added
    public $latestRequests;

    public function mount(): void
    {
        $this->totalRequests = OllamaRequestLog::count();
        $this->totalSuccessfulRequests = OllamaRequestLog::where('status', 'success')->count(); // Added
        $this->totalFailedRequests = OllamaRequestLog::where('status', 'failed')->count(); // Added

        $this->successRate = ($this->totalRequests > 0)
                            ? round(($this->totalSuccessfulRequests / $this->totalRequests) * 100, 2)
                            : 0; // Added

        $this->latestRequests = OllamaRequestLog::orderByDesc('created_at')
                                                ->take(3) // Removed where('status', 'success') to show all latest requests
                                                ->get();
    }
}
