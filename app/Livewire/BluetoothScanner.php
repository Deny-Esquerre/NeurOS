<?php

namespace App\Livewire;

use Livewire\Component;

class BluetoothScanner extends Component
{
    public $devices = [];
    public $connectedDevice = null;
    public $scanActive = false; // To indicate if scanning is in progress

    public function render()
    {
        return view('livewire.bluetooth-scanner');
    }

    public function startScan()
    {
        $this->scanActive = true;
        // This method will primarily trigger the JS event to start scanning
        $this->dispatch('start-bluetooth-scan');
    }

    public function stopScan()
    {
        $this->scanActive = false;
        $this->dispatch('stop-bluetooth-scan');
    }

    // This method will be called from JavaScript with the found device
    public function addDevice($device)
    {
        // Check if the device is already in the list
        $existingDevice = collect($this->devices)->firstWhere('id', $device['id']);

        if (!$existingDevice) {
            $this->devices[] = $device;
        }
        
        // $this->scanActive = false; // Removed: Scan active state is managed by JavaScript's success/failure
    }

    // This method will be called from JavaScript when a device is connected
    public function deviceConnected($device)
    {
        $this->connectedDevice = $device;
        $this->dispatch('device-connected');
        // Optionally, save connected device to database or session here
    }

    // This method will be called from JavaScript when a device is disconnected
    public function deviceDisconnected($device)
    {
        if ($this->connectedDevice && $this->connectedDevice['id'] === $device['id']) {
            $this->connectedDevice = null;
            $this->dispatch('device-disconnected'); // Notify JS to handle actual disconnection
        }
    }

    public function disconnect()
    {
        if ($this->connectedDevice) {
            $this->dispatch('disconnect-bluetooth-device', deviceId: $this->connectedDevice['id']);
            $this->connectedDevice = null;
        }
    }
}