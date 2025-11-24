<?php

namespace App\Livewire;

use Livewire\Component;
use App\Events\HeartRateUpdated; // Add this line
use Illuminate\Support\Facades\Auth; // Add this line to get the authenticated user ID

class HeartRateDisplay extends Component
{
    public $latestBpm = 'N/A';
    public $isVisible = false; // Add this property

    // Listen for the HeartRateUpdated event or a device connected event
    public function getListeners()
    {
        return [
            "echo-private:heart-rate." . Auth::id() . ",.heart-rate-updated" => 'updateHeartRate',
            'device-connected' => 'showHeartRateDisplay', // Listen for this event
        ];
    }

    public function updateHeartRate(array $event)
    {
        $this->latestBpm = $event['heartRateData']['bpm'];
        $this->isVisible = true; // Ensure visibility when heart rate data comes in
    }

    public function showHeartRateDisplay()
    {
        $this->isVisible = true;
    }


    public function render()
    {
        return view('livewire.heart-rate-display');
    }
}
