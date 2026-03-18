<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class OpenAiChatClient
{
    /**
     * @param  bool|string  $verify  true/false ou caminho para um CA bundle (.pem)
     */
    public function __construct(
        private readonly string $apiKey,
        private readonly string $model,
        private readonly float $temperature = 0.2,
        private readonly int $timeoutSeconds = 30,
        private readonly string $baseUrl = 'https://api.openai.com',
        private readonly bool|string $verify = true,
    ) {
    }

    /**
     * @param  array<int, array{role: string, content: string}>  $messages
     * @return array<string, mixed>
     */
    public function chatJson(array $messages): array
    {
        $endpoint = rtrim($this->baseUrl, '/').'/v1/chat/completions';

        $response = Http::withToken($this->apiKey)
            ->acceptJson()
            ->asJson()
            ->timeout($this->timeoutSeconds)
            ->withOptions(['verify' => $this->verify])
            ->post($endpoint, [
                'model' => $this->model,
                'messages' => $messages,
                'temperature' => $this->temperature,
                'response_format' => ['type' => 'json_object'],
            ]);

        if (! $response->successful()) {
            $status = $response->status();

            $message = (string) data_get($response->json(), 'error.message', '');
            if ($message === '') {
                $message = (string) $response->body();
            }

            $message = trim(preg_replace('/\\s+/', ' ', $message) ?? $message);
            $message = Str::limit($message, 900);

            throw new \RuntimeException("OpenAI request failed ({$status}): {$message}");
        }

        $content = (string) data_get($response->json(), 'choices.0.message.content', '');
        $content = trim($content);

        $decoded = json_decode($content, true);
        if (! is_array($decoded)) {
            throw new \RuntimeException('OpenAI returned non-JSON content.');
        }

        return $decoded;
    }
}
