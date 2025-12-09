<?php

namespace App\Jobs;

use App\Events\ReadingTaskGenerated;
use App\Events\ReadingTaskFailed;
use App\Services\OllamaService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateReadingTask implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300; // Set timeout to 5 minutes

    protected $age;
    protected $topic;
    protected $userId;
    protected $customPrompt; // Added

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($age, $topic, $userId, $customPrompt = null) // Modified
    {
        $this->age = $age;
        $this->topic = $topic;
        $this->userId = $userId;
        $this->customPrompt = $customPrompt; // Added
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(OllamaService $ollamaService)
    {
        try {
            $taskData = $ollamaService->generateTask($this->age, $this->topic, $this->customPrompt); // Modified

            if ($taskData) {
                // Fire an event to notify the user that the task is ready
                ReadingTaskGenerated::dispatch($taskData, $this->userId);
            } else {
                Log::error('Failed to generate task for user ' . $this->userId . ' - OllamaService returned no data.');
                ReadingTaskFailed::dispatch($this->userId, 'OllamaService returned no data.');
            }
        } catch (\Exception $e) {
            Log::error('Error in GenerateReadingTask job for user ' . $this->userId . ': ' . $e->getMessage());
            ReadingTaskFailed::dispatch($this->userId, $e->getMessage());
        }
    }
}
