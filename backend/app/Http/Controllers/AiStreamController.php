<?php

namespace App\Http\Controllers;

use App\Services\AiStreamServiceContract;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AiStreamController extends Controller
{
    public function __invoke(Request $request): StreamedResponse|JsonResponse
    {
        $apiKey = config('openrouter.api_key');

        if (empty($apiKey)) {
            return response()->json([
                'error' => 'AI service not configured. Set OPENROUTER_API_KEY in your .env',
            ], 500);
        }

        $validated = $request->validate([
            'prompt' => 'required|string|max:4000',
        ]);

        $prompt = preg_replace('/\s+/', ' ', trim($validated['prompt']));

        $aiStream = app(AiStreamServiceContract::class);

        return response()->stream(function () use ($prompt, $aiStream) {
            if (function_exists('apache_setenv')) {
                @apache_setenv('no-gzip', '1');
            }

            @ini_set('zlib.output_compression', '0');
            @ini_set('output_buffering', 'off');
            @ini_set('implicit_flush', '1');
            ob_implicit_flush(true);

            $aiStream->stream(
                prompt: $prompt,
                onEvent: function (string $event, mixed $data): void {
                    echo "event: {$event}\n";
                    echo 'data: ' . json_encode($data) . "\n\n";
                    @ob_flush();
                    @flush();
                },
                onError: function (\Throwable $e) use ($prompt): void {
                    Log::warning('OpenRouter API streaming failed', [
                        'prompt_length' => strlen($prompt),
                        'error' => $e->getMessage(),
                    ]);

                    echo "event: error\n";
                    echo 'data: ' . json_encode([
                        'message' => 'An error occurred while generating the response.',
                    ]) . "\n\n";
                    @ob_flush();
                    @flush();
                },
            );

            echo "data: [DONE]\n\n";
            @ob_flush();
            @flush();
        }, 200, [
            'Content-Type'      => 'text/event-stream; charset=UTF-8',
            'Cache-Control'     => 'no-cache, must-revalidate',
            'Connection'        => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }
}
