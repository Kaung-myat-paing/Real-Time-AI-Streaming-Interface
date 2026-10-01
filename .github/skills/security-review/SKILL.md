---
name: security-review
description: Security rules for the Laravel SSE boundary, OpenRouter configuration, CORS, and frontend stream handling. Use when touching these surfaces.
---

# Security review

## Trust boundaries in this repo

- `backend/routes/api.php` and `backend/app/Http/Controllers/AiStreamController.php` accept browser input at `POST /api/ai/stream`; prompt validation and normalization happen before the service call.
- `backend/config/cors.php` controls which browser origins can call the API through `CORS_ORIGINS`; review origin changes as deployment configuration.
- `backend/config/openrouter.php` reads `OPENROUTER_API_KEY`, `OPENROUTER_BASE_URL`, `OPENROUTER_MODEL`, and `OPENROUTER_MAX_TOKENS`; the key must remain server-side.
- `backend/app/Services/AiStreamService.php` sends the validated prompt to OpenRouter and logs failures; logs must not include the API key or full user prompt.
- `frontend/src/lib/api.ts` and `frontend/src/App.tsx` parse untrusted streamed event data and display it as text, not HTML.

## Never

- Never commit `backend/.env` or replace environment-driven OpenRouter and CORS settings with credentials or production values in source.
- Never widen `CORS_ORIGINS` or change prompt/SSE validation without an explicit test and human review.
- Never render streamed response content through `dangerouslySetInnerHTML` or equivalent HTML sinks.

## Before merging a change to the streaming boundary

- Run `cd backend && composer test`.
- Confirm `AiStreamController` still validates `prompt` at most 4000 characters and emits the documented SSE events.
- Confirm `frontend/src/App.tsx` retains chunk buffering and treats streamed values as text.
- Inspect changes to `backend/.env.example`, `backend/config/cors.php`, and `backend/config/openrouter.php` for secret or origin exposure.
