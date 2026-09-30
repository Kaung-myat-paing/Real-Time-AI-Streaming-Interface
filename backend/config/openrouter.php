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
    | Default model: OpenRouter free tier (no credits consumed, rate-limited)
    | To use a paid model (better quality, higher limits), set OPENROUTER_MODEL
    | in .env to any model from https://openrouter.ai/models
    | Requires an OpenRouter API key: https://openrouter.ai/settings/keys
    |
    */

    'model' => env('OPENROUTER_MODEL', 'nvidia/nemotron-3-ultra-550b-a55b:free'),

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
