<?php

namespace App\Http\Controllers;

use Symfony\Component\HttpFoundation\StreamedResponse;

class AiStreamController extends Controller
{
    public function __invoke(): StreamedResponse
    {
        $sentence = 'The future of web development is not just about building static interfaces; its about creating intelligent experiences. By integrating Large Language Models (LLMs) with modern frameworks like Laravel and React, we can transform how users interact with data. Streaming responses via Server-Sent Events (SSE) ensures a smooth, real-time experience that eliminates perceived latency. Lets build the future together.';
        $words = explode(' ', $sentence);

        return response()->stream(function () use ($words) {
            if (function_exists('apache_setenv')) {
                @apache_setenv('no-gzip', '1');
            }

            @ini_set('zlib.output_compression', '0');
            @ini_set('output_buffering', 'off');
            @ini_set('implicit_flush', '1');
            ob_implicit_flush(true);

            foreach ($words as $index => $word) {
                echo ($index === 0 ? '' : ' ') . $word;
                @ob_flush();
                @flush();
                usleep(200_000); // 200ms per word
            }
        }, 200, [
            'Content-Type'                 => 'text/plain; charset=UTF-8',
            'Cache-Control'                => 'no-cache, must-revalidate',
            'X-Accel-Buffering'            => 'no',
            'Access-Control-Allow-Origin'  => '*',
            'Access-Control-Allow-Methods' => 'GET, OPTIONS',
        ]);
    }
}

