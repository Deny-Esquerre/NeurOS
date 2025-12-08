<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class OllamaService
{
    protected string $baseUrl;
    protected string $model = 'gemma:2b'; // Default Ollama model

    public function __construct()
    {
        $this->baseUrl = env('OLLAMA_BASE_URL', 'http://localhost:11434');

        if (empty($this->baseUrl)) {
            throw new \Exception('Ollama Base URL not configured.');
        }
    }

    /**
     * Make a request to the Ollama API.
     *
     * @param array $messages
     * @param string|null $model
     * @param float $temperature
     * @return array|null
     */
    protected function makeRequest(array $messages, ?string $model = null, float $temperature = 0.7): ?array
    {
        $model = $model ?? $this->model; // Use provided model or default

        $response = Http::timeout(300)->post("{$this->baseUrl}/api/chat", [ // Ollama chat endpoint
            'model' => $model,
            'messages' => $messages,
            'options' => [
                'temperature' => $temperature,
            ],
            'stream' => false,
        ]);

        if ($response->successful()) {
            return $response->json();
        }

        \Log::error('Ollama API Error: ' . $response->body());
        return null;
    }

    /**
     * Generate 3 reading comprehension topics based on age.
     *
     * @param int $age
     * @return array|null
     */
    public function generateTopics(int $age, ?string $customPrompt = null): ?array
    {
        $defaultPrompt = "Sugiere 3 temas diversos y atractivos de comprensión lectora adecuados para un niño de {$age} años. Proporciona solo los temas, uno por línea, sin numeración ni ningún otro texto. Asegúrate de que sean distintos y apropiados para la edad, y que la respuesta esté íntegramente en español.";

        $prompt = $customPrompt ?? $defaultPrompt;

        $messages = [
            ['role' => 'system', 'content' => 'You are a helpful assistant that suggests reading comprehension topics.'],
            ['role' => 'user', 'content' => $prompt],
        ];

        $result = $this->makeRequest($messages, null, 0.8); // Use default model

        if ($result && isset($result['message']['content'])) { // Ollama response structure
            $topicsString = trim($result['message']['content']);
            return array_filter(array_map('trim', explode("\n", $topicsString)));
        }

        return null;
    }

    /**
     * Generate a reading text and 3-5 comprehension questions based on age and topic.
     *
     * @param int $age
     * @param string $topic
     * @return array|null ['text' => '...', 'questions' => ['...', '...', ...]]
     */
            public function generateTask(int $age, string $topic, ?string $customPrompt = null): ?array
            {
                $defaultPrompt = "Genera un texto de comprensión lectora y 3-5 preguntas para un niño de {$age} años sobre el tema: '{$topic}'.\n"
                               . "Para cada pregunta, proporciona 4 alternativas y la respuesta correcta. La respuesta debe estar íntegramente en español.\n\n"
                               . "Formatea la salida estrictamente como un objeto JSON con las siguientes claves:\n"
                               . "{\n"
                               . "  \"text\": \"[El texto de lectura aquí]\",\n"
                               . "  \"questions\": [\n"
                               . "    {\n"
                               . "      \"question\": \"[Pregunta 1]\",\n"
                               . "      \"alternatives\": [\"[Alternativa A]\", \"[Alternativa B]\", \"[Alternativa C]\", \"[Alternativa D]\"],\n"
                               . "      \"correct_answer\": \"[Alternativa Correcta]\"\n"
                               . "    },\n"
                               . "    // ... más preguntas\n"
                               . "  ]\n"
                               . "}\n\n"
                               . "Asegúrate de que el texto sea atractivo y las preguntas se relacionen directamente con el texto y sean apropiadas para la edad.";
        
                $prompt = $customPrompt ?? $defaultPrompt;    
            $messages = [
                ['role' => 'system', 'content' => 'Eres un asistente útil que genera tareas de comprensión lectora en español.'],
                ['role' => 'user', 'content' => $prompt],
            ];
    
            $result = $this->makeRequest($messages, null, 0.7); // Use default model
    
                        if ($result && isset($result['message']['content'])) {
                            $rawContent = trim($result['message']['content']);
            
                            // Extract JSON from markdown code block
                            if (preg_match('/```json\s*(.*?)\s*```/s', $rawContent, $matches)) {
                                $jsonContent = trim($matches[1]);
                            } else {
                                // If no markdown block, assume the whole content is JSON
                                $jsonContent = $rawContent;
                            }
            
                            $parsedData = json_decode($jsonContent, true);
            
                            if (json_last_error() === JSON_ERROR_NONE && isset($parsedData['text']) && isset($parsedData['questions'])) {
                                // Ensure questions have the expected structure
                                foreach ($parsedData['questions'] as &$q) {
                                    if (!isset($q['question']) || !isset($q['alternatives']) || !isset($q['correct_answer'])) {
                                        // Log or handle malformed question
                                        \Log::warning('Malformed question generated by Ollama: ' . json_encode($q));
                                        return null;
                                    }
                                    // Ensure alternatives are an array of strings
                                    if (!is_array($q['alternatives'])) {
                                        $q['alternatives'] = [];
                                    }
                                }
                                return $parsedData;
                            } else {
                                \Log::error('Ollama API: Failed to parse JSON response or missing keys. Response: ' . $jsonContent);
                            }
                        }    
            return null;
        }}
