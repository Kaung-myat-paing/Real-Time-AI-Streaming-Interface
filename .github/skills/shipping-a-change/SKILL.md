---
name: shipping-a-change
description: What to update when you change streaming behavior in this repository, and what done requires. Use before opening a pull request.
---

# Shipping a change

## Add or change a streaming feature

1. `backend/routes/api.php` — add or update the HTTP route.
2. `backend/app/Http/Controllers/` — validate input and emit the documented SSE response.
3. `backend/app/Services/AiStreamServiceContract.php` and `backend/app/Services/` — define and implement the service contract.
4. `backend/app/Providers/OpenRouterServiceProvider.php` and `backend/bootstrap/providers.php` — register the binding.
5. `backend/config/openrouter.php` and `backend/.env.example` — declare configurable provider settings.
6. `backend/tests/Feature/AiStreamTest.php` — cover the endpoint and service behavior.
7. `frontend/src/lib/api.ts` and `frontend/src/App.tsx` — consume the stream and render its states.
8. `cd backend && composer test && cd ../frontend && npm run lint && npm run build` — verify.

## When you change this, also change that

| Change | Also update |
|---|---|
| `backend/routes/api.php` | Controller, feature tests, and frontend client when the contract changes |
| Controller or service SSE events | Feature tests, `frontend/src/lib/api.ts`, and `frontend/src/App.tsx` |
| OpenRouter or CORS config | `.env.example`, deployment environment, and relevant tests |
| Frontend stream parsing | Backend contract tests and frontend lint/build |
| Database migrations | Migration state and feature coverage |

## Done

- `cd backend && composer test` exits successfully.
- `cd frontend && npm run lint && npm run build` exits successfully.
- Any behavior change has a test that fails against the old behavior.
- SSE contract changes are updated on both backend and frontend sides.
