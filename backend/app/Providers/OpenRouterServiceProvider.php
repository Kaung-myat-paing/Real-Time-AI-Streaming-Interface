<?php

namespace App\Providers;

use App\Services\AiStreamService;
use App\Services\AiStreamServiceContract;
use Illuminate\Support\ServiceProvider;
use OpenAI;
use OpenAI\Client;

class OpenRouterServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Client::class, function () {
            $apiKey = config('openrouter.api_key');

            if (empty($apiKey)) {
                throw new \RuntimeException(
                    'OpenRouter API key is not configured. Set OPENROUTER_API_KEY in your .env file.'
                );
            }

            return OpenAI::factory()
                ->withApiKey($apiKey)
                ->withBaseUri(config('openrouter.base_url'))
                ->make();
        });

        $this->app->singleton(AiStreamServiceContract::class, function () {
            return app(AiStreamService::class);
        });
    }

    public function boot(): void
    {
        //
    }
}
