<?php

namespace App\Livewire;

use Livewire\Component;
use App\Events\HeartRateUpdated; // Add this line
use Illuminate\Support\Facades\Auth; // Add this line to get the authenticated user ID

class HeartRateDisplay extends Component
{
    public $latestBpm = 'N/A'; // Public property to display the BPM

    // Listen for the HeartRateUpdated event
    public function getListeners()
    {
        // Listen to a private channel specific to the authenticated user
        return [
            "echo-private:heart-rate." . Auth::id() . ",.heart-rate-updated" => 'updateHeartRate',
        ];
    }

    public function updateHeartRate(array $event)
    {
        $this->latestBpm = $event['heartRateData']['bpm'];
    }

    public function render()
    {
        return view('livewire.heart-rate-display');
    }
}
