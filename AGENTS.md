# Working in this repository

Read this file before changing the Laravel backend or React frontend.

## Commands

- Backend setup: `cd backend && composer install && cp .env.example .env && php artisan key:generate`
- Backend tests: `cd backend && composer test`
- Frontend install: `cd frontend && npm install`
- Frontend checks: `cd frontend && npm run lint && npm run build`
- Local development: `cd backend && composer run dev`; run `cd frontend && npm run dev` separately when using the split app layout.
- Backend configuration is environment-driven; use `backend/.env.example` as the documented template and never commit `backend/.env`.

## Conventions and pitfalls

- Keep the streaming contract in `backend/app/Services/AiStreamServiceContract.php`; bind implementations in `backend/app/Providers/OpenRouterServiceProvider.php` and resolve the contract from the container in request code so feature-test fakes are honored.
- The public stream endpoint is `POST /api/ai/stream`; preserve prompt validation, the SSE event names (`content_block_delta`, `error`, `done`), and the terminal `data: [DONE]` marker.
- The frontend parser in `frontend/src/App.tsx` must buffer incomplete network chunks and reset pending SSE event state between events and requests. Changes to the stream protocol require coordinated backend tests and frontend handling.
- Keep OpenRouter model, token, API URL, and CORS origins configurable through `backend/.env.example`; do not put credentials or production origins in source.
- When changing the API response or validation behavior, update `backend/tests/Feature/AiStreamTest.php` and the frontend API error handling in `frontend/src/lib/api.ts`.
- Recent Copilot review feedback identified three concrete streaming regressions to check for: scroll alignment, stale pending-event state, and an `Accept` header that permits JSON validation errors.

## Adding a streaming feature

Trace the complete registration chain: route in `backend/routes/api.php`; controller in `backend/app/Http/Controllers/`; contract and implementation in `backend/app/Services/`; provider binding in `backend/app/Providers/` and `backend/bootstrap/providers.php`; configuration in `backend/config/` and `backend/.env.example`; feature coverage in `backend/tests/Feature/`; then update `frontend/src/lib/api.ts` and `frontend/src/App.tsx` for client behavior.

## Maintenance matrix

| Change | Also update |
|---|---|
| `backend/routes/api.php` | `backend/app/Http/Controllers/`, `backend/tests/Feature/AiStreamTest.php`, frontend API client if the endpoint contract changes |
| `backend/app/Http/Controllers/AiStreamController.php` | `backend/tests/Feature/AiStreamTest.php`, `frontend/src/lib/api.ts` and `frontend/src/App.tsx` for SSE or validation changes |
| `backend/app/Services/` or `backend/app/Providers/` | `backend/config/openrouter.php`, `backend/.env.example`, `backend/tests/Feature/AiStreamTest.php` |
| `backend/config/openrouter.php` or `backend/config/cors.php` | `backend/.env.example`, deployment environment configuration, relevant backend tests |
| `backend/database/migrations/` | database state and migration tests or feature coverage |
| `frontend/src/lib/api.ts` or `frontend/src/App.tsx` | frontend lint/build and backend SSE contract tests when protocol behavior changes |

## Done means

- `cd backend && composer test` exits successfully.
- `cd frontend && npm run lint && npm run build` exits successfully.
- Any behavior change has a test that fails against the old behavior.
- SSE contract changes are updated on both backend and frontend sides.

## Never merges without a human

A person has to have **read this diff** before it lands. Telling an agent "merge it when you're done" is approving a goal, not this change — so it does not count for anything on this list. Everywhere else it counts fine, which is the point of having a list.

- Anything under `backend/database/migrations/` that changes persisted schema or data.
- Any change to the shape of `POST /api/ai/stream` validation or SSE events.
- Any change to `backend/config/cors.php`, `backend/config/openrouter.php`, or tracked environment configuration.
- Any change to `.github/workflows/` or release/publish automation.

## Documentation

Repository-level onboarding is in `README.md`; backend and frontend retain their framework-generated READMEs. Update `CHANGELOG.md` for notable user-visible or contributor-facing changes.
