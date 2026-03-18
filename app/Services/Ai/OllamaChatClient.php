<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class OllamaChatClient
{
    public function __construct(
        private readonly string $baseUrl,
        private readonly string $model,
        private readonly float $temperature = 0.2,
        private readonly int $timeoutSeconds = 60,
        private readonly float $connectTimeoutSeconds = 1.2,
        /** @var array<string, mixed> */
        private readonly array $options = [],
        private readonly ?string $keepAlive = null,
    ) {
    }

    /**
     * @param  array<int, array{role: string, content: string}>  $messages
     * @return array<string, mixed>
     */
    public function chatJson(array $messages): array
    {
        $endpoint = rtrim($this->baseUrl, '/').'/api/chat';

        $options = $this->options;
        // Explicit params win over whatever came from config.
        $options['temperature'] = $this->temperature;

        $body = [
            'model' => $this->model,
            'messages' => $messages,
            'stream' => false,
            // Ask Ollama to enforce JSON output. Some models still might fail, so we keep a fallback parser.
            'format' => 'json',
            'options' => $options,
        ];
        if ($this->keepAlive !== null && trim($this->keepAlive) !== '') {
            // Keeps the model loaded between requests. Greatly reduces "first token" latency when the model goes cold.
            $body['keep_alive'] = $this->keepAlive;
        }

        $response = Http::acceptJson()
            ->asJson()
            ->timeout($this->timeoutSeconds)
            ->connectTimeout($this->connectTimeoutSeconds)
            ->post($endpoint, $body);

        if (! $response->successful()) {
            $status = $response->status();

            $message = (string) data_get($response->json(), 'error', '');
            if ($message === '') {
                $message = (string) $response->body();
            }

            $message = trim(preg_replace('/\s+/', ' ', $message) ?? $message);
            $message = Str::limit($message, 900);

            throw new \RuntimeException("Ollama request failed ({$status}): {$message}");
        }

        $content = (string) data_get($response->json(), 'message.content', '');
        $content = trim($content);

        $decoded = $this->decodeJson($content);
        if (! is_array($decoded)) {
            throw new \RuntimeException('Ollama returned non-JSON content.');
        }

        return $decoded;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function decodeJson(string $content): ?array
    {
        if ($content === '') {
            return null;
        }

        $decoded = json_decode($content, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        // Common fallback: model wraps JSON in code fences or adds extra text.
        $trim = preg_replace('/^```(?:json)?\s*/i', '', $content) ?? $content;
        $trim = preg_replace('/\s*```$/', '', $trim) ?? $trim;
        $trim = trim($trim);

        $first = strpos($trim, '{');
        $last = strrpos($trim, '}');
        if ($first === false || $last === false || $last <= $first) {
            return null;
        }

        $maybe = substr($trim, $first, $last - $first + 1);
        $decoded = json_decode($maybe, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        return null;
    }
}
