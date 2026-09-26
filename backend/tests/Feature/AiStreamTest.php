<?php

namespace Tests\Feature;

use App\Services\AiStreamService;
use App\Services\AiStreamServiceContract;
use Closure;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class AiStreamTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('openrouter.api_key', 'sk-or-v1-test-key');
        Config::set('openrouter.model', 'anthropic/claude-3.5-haiku');
        Config::set('openrouter.max_tokens', 1024);
    }

    public function test_post_without_prompt_returns_422(): void
    {
        $response = $this->postJson('/api/ai/stream', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('prompt');
    }

    public function test_post_with_empty_prompt_returns_422(): void
    {
        $response = $this->postJson('/api/ai/stream', [
            'prompt' => '',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('prompt');
    }

    public function test_post_with_whitespace_only_prompt_returns_422(): void
    {
        $response = $this->postJson('/api/ai/stream', [
            'prompt' => '   ',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('prompt');
    }

    public function test_post_with_valid_prompt_returns_sse_stream(): void
    {
        $this->fakeStreamService(function (string $prompt, Closure $onEvent, Closure $onError): void {
            $onEvent('content_block_start', ['type' => 'text']);
            $onEvent('content_block_delta', ['text' => 'Hello']);
            $onEvent('content_block_delta', ['text' => ' world']);
            $onEvent('content_block_stop', []);
            $onEvent('message_start', []);
            $onEvent('message_delta', ['stop_reason' => 'end_turn']);
            $onEvent('message_stop', []);
            $onEvent('done', (object) []);
        });

        $response = $this->postJson('/api/ai/stream', [
            'prompt' => 'Tell me something',
        ]);

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/event-stream; charset=UTF-8');
        $response->assertHeader('X-Accel-Buffering', 'no');

        $cacheControl = $response->headers->get('Cache-Control');
        $this->assertStringContainsString('no-cache', $cacheControl);
        $this->assertStringContainsString('must-revalidate', $cacheControl);

        $content = $response->streamedContent();
        $this->assertStringContainsString('event: content_block_delta', $content);
        $this->assertStringContainsString('"text":"Hello"', $content);
        $this->assertStringContainsString('data: [DONE]', $content);
    }

    public function test_post_with_prompt_exceeding_max_length_returns_422(): void
    {
        $response = $this->postJson('/api/ai/stream', [
            'prompt' => str_repeat('a', 4001),
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('prompt');
    }

    public function test_api_error_returns_error_sse_event(): void
    {
        $this->fakeStreamService(function (string $prompt, Closure $onEvent, Closure $onError): void {
            $onError(new \RuntimeException('API connection failed'));
        });

        $response = $this->postJson('/api/ai/stream', [
            'prompt' => 'Hello',
        ]);

        $response->assertStatus(200);
        $content = $response->streamedContent();
        $this->assertStringContainsString('event: error', $content);
        $this->assertStringContainsString('An error occurred while generating the response.', $content);
        $this->assertStringContainsString('data: [DONE]', $content);
    }

    public function test_get_request_returns_usage_message(): void
    {
        $response = $this->get('/api/ai/stream');

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'message' => 'Send a POST request with a JSON body containing a "prompt" field.',
        ]);
    }

    private function fakeStreamService(Closure $handler): void
    {
        $fake = new class ($handler) implements \App\Services\AiStreamServiceContract {
            public function __construct(
                private readonly Closure $handler,
            ) {}

            public function stream(string $prompt, Closure $onEvent, Closure $onError): void
            {
                ($this->handler)($prompt, $onEvent, $onError);
            }
        };

        $this->app->instance(AiStreamServiceContract::class, $fake);
    }
}
