# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Purpose

A small full-stack Product Management app (PHP 8.5 + React), built as practice for a senior PHP/full-stack coding challenge. The goal is to demonstrate clean, lightweight DDD, REST API design, and a simple React frontend — intentionally small, not a framework showcase. See `context/project-overview.md` (backend/architecture/API contract) and `context/frontend-guidelines.md` (frontend conventions, component layout, state strategy) for the full design spec — read the relevant one before making non-trivial changes in that area.

## Commands

There is no local PHP or Composer binary on the host — everything PHP-related runs inside the `php` Docker container.

```bash
# Start the full stack (nginx :8080, php-fpm, mysql :3308, vite dev server :5173)
docker compose up -d

# Install PHP deps (needed once, or after composer.json changes)
docker compose exec php composer install

# Run backend tests (no phpunit.xml exists yet — pass the test path explicitly)
docker compose exec php ./vendor/bin/phpunit tests

# Run a single test file / method
docker compose exec php ./vendor/bin/phpunit tests/Unit/Product/Domain/MoneyTest.php
docker compose exec php ./vendor/bin/phpunit --filter testMethodName

# Frontend (from frontend/, or via the frontend container which already runs `npm run dev`)
cd frontend && npm install
npm run dev       # served through nginx at localhost:8080, direct at :5173
npm run lint
npm run build
```

The app is reached at `http://localhost:8080` — nginx routes `/api/*` to PHP-FPM and everything else to the Vite dev server (see `docker/nginx/default.conf`). MySQL is only reachable from the host on port `3308`; from inside the `php` container, the DB host is `mysql` (never `localhost`).

Backend tests directory (`tests/Unit/...`) currently has no test files — it's scaffolded but empty. There is no `phpunit.xml`.

## Architecture

Lightweight DDD with a strict one-way dependency flow:

```
Controller -> Application Use Case -> Domain <- Repository Interface <- Infrastructure Repository -> PDO
```

- **`src/Product/Domain/`** — `Product` (entity, has identity, protects its own invariants via `rename()`/`changePrice()`), `Money` (immutable value object: non-negative integer minor units + 3-letter currency), `ProductRepositoryInterface`. Must not depend on Symfony, PDO, HTTP, or JSON.
- **`src/Product/Application/UseCase/`** — one class per use case (`CreateProduct`, `GetProduct`, `ListProducts`, `UpdateProduct`; `DeleteProduct` is planned but not yet implemented). Each takes `ProductRepositoryInterface` via constructor injection, contains no SQL/HTTP/JSON.
- **`src/Product/Infrastructure/Persistence/PdoProductRepository.php`** — implements the repository interface with prepared statements only; hydrates rows into `Product`/`Money`.
- **`src/Http/Controller/ProductController.php`** — thin: reads the `Request`, calls a use case, maps the result to `JsonResponse`. No business logic.
- **`src/Shared/Infrastructure/Database.php`** — builds the PDO connection from `DB_*` env vars (set in `docker-compose.yml`).
- **`public/index.php`** — the only entrypoint. Manually wires all dependencies (no DI container), loads `config/routes.php`, matches the request with Symfony's `UrlMatcher`, dispatches via a `match()` on `_controller`, and maps exceptions to HTTP status codes: `ResourceNotFoundException` → 404, `InvalidArgumentException` → 422 (domain validation failures surface here), `RuntimeException` → 404 (e.g. "not found" from a use case), anything else → 500.
- **`config/routes.php`** — Symfony `RouteCollection`, one `Route` per endpoint, each naming a `_controller` key handled by the `match()` in `public/index.php`.

Money is always integer minor units end-to-end (e.g. `9990` = €99.90); the domain never uses floats for money. The API is currently `GET/POST /api/products`, `GET /api/products/{id}` (routes + wiring for `PUT`/`DELETE` are not yet in `config/routes.php` or `public/index.php`, though `UpdateProduct` and the repository's `delete()` already exist).

To add a new use case end-to-end: add the Application class → wire it into `public/index.php` (construct it, pass to `ProductController`) → add the route in `config/routes.php` → add the controller method and its `match()` branch.

## Frontend

`frontend/` is a fresh Vite+React scaffold (`App.jsx` is still the default Vite starter page) — the actual Product UI described in `context/frontend-guidelines.md` (TypeScript, Tailwind v4, shadcn/ui, `api/products.ts`, `ProductsPage`, etc.) has not been built yet. The project currently has plain JS/JSX and no TypeScript tooling configured. Read `context/frontend-guidelines.md` before starting frontend work — it specifies the target structure, the `Product`/`ProductInput` types, the API layer shape, state strategy (local `useState`, no Redux/React Query/Axios/Zod), and CRUD flow details, and should be treated as the spec to build toward rather than documentation of existing code.

## Key Conventions (from context docs)

- Constructor injection everywhere; no singletons, service locators, or DI container.
- Domain objects (`Product`, `Money`) are constructed directly (`new Product(...)`) rather than through factories — don't add a factory unless creation logic genuinely gets complex.
- All dynamic SQL uses prepared statements.
- Avoid premature abstraction (CQRS, event sourcing, command bus, etc. are explicitly out of scope) — keep additions as small and explicit as the existing code.
