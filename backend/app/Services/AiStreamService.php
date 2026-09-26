<?php

namespace App\Services;

use Closure;
use OpenAI\Client;

class AiStreamService implements AiStreamServiceContract
{
    public function __construct(
        private readonly Client $client,
    ) {}

    /**
     * Stream a response from OpenRouter for the given prompt.
     *
     * Calls $onEvent for each SSE event and $onError if the API call fails.
     * Returns the generator so the caller controls iteration.
     */
    public function stream(string $prompt, Closure $onEvent, Closure $onError): void
    {
        try {
            $stream = $this->client->chat()->createStreamed([
                'model' => config('openrouter.model', 'anthropic/claude-3.5-haiku'),
                'max_tokens' => config('openrouter.max_tokens', 1024),
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
            ]);

            foreach ($stream as $response) {
                $delta = $response->choices[0]->delta;

                if ($delta->content !== null && $delta->content !== '') {
                    $onEvent('content_block_delta', ['text' => $delta->content]);
                }
            }

            $onEvent('done', (object) []);
        } catch (\Throwable $e) {
            \Log::error('OpenRouter streaming error', [
                'message' => $e->getMessage(),
                'class' => get_class($e),
            ]);
            $onError($e);
        }
    }
}
