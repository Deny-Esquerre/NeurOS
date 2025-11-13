<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\HeartRateData;
use Illuminate\Support\Facades\Auth;
use App\Events\HeartRateUpdated; // Add this line

class HeartRateController extends Controller
{
    public function store(Request $request)
    {
        // Validate the incoming request data
        $validatedData = $request->validate([
            'bpm' => 'required|integer|min:1',
            'user_id' => 'required|exists:users,id', // Ensure user exists
        ]);

        // Create a new HeartRateData record
        $heartRate = HeartRateData::create([
            'user_id' => $validatedData['user_id'],
            'bpm' => $validatedData['bpm'],
            'recorded_at' => now(), // Record the current timestamp
        ]);

        // Dispatch the event
        HeartRateUpdated::dispatch($heartRate); // Add this line

        return response()->json([
            'message' => 'Heart rate data stored successfully',
            'data' => $heartRate
        ], 201);
    }
}
