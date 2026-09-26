<?php

return [

    /*
    |--------------------------------------------------------------------------
    | OpenRouter API Key
    |--------------------------------------------------------------------------
    |
    | Your OpenRouter API key. You can find this at https://openrouter.ai/keys
    | Never commit this value directly to version control.
    |
    */

    'api_key' => env('OPENROUTER_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Base URL
    |--------------------------------------------------------------------------
    |
    | The base URL for OpenRouter API requests.
    |
    */

    'base_url' => env('OPENROUTER_BASE_URL', 'https://openrouter.ai/api/v1'),

    /*
    |--------------------------------------------------------------------------
    | Default Model
    |--------------------------------------------------------------------------
    |
    | The default model to use for text generation via OpenRouter.
    | See https://openrouter.ai/models for available models.
    |
    */

    'model' => env('OPENROUTER_MODEL', 'openai/gpt-3.5-turbo'),

    /*
    |--------------------------------------------------------------------------
    | Max Tokens
    |--------------------------------------------------------------------------
    |
    | The maximum number of tokens to generate in each response.
    |
    */

    'max_tokens' => env('OPENROUTER_MAX_TOKENS', 1024),

];
