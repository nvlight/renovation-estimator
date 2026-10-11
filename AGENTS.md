# Repository Guidelines

## Project Structure & Module Organization

The Laravel 12 API lives in `app/`; controllers are split between `Http/Controllers/Api/V1` and `V2`, with validation in `Http/Requests`, JSON resources in `Http/Resources`, and authorization in `Policies`. Routes are defined in `routes/`. Database migrations, factories, seeders, and material fixture images are under `database/`. Pest feature tests live in `tests/Feature`.

The Vue 3/Quasar SPA is isolated in `quasar_frontend/`. Put screens in `src/pages`, reusable UI and calculators in `src/components`, Pinia stores in `src/stores`, and frontend tests in `test/vitest/__tests__`. Architectural and domain notes are maintained in `docs/`.

## Build, Test, and Development Commands

- `composer install` installs backend dependencies; use `./vendor/bin/sail` for PHP commands when PHP is unavailable locally.
- `./vendor/bin/sail up -d` starts Laravel, PostgreSQL, Redis, and the Quasar dev server.
- `./vendor/bin/sail artisan migrate --seed` applies schema changes and loads the complete demo dataset.
- `./vendor/bin/sail artisan test` runs the Pest suite.
- `./vendor/bin/pint` formats PHP.
- `docker compose exec quasar-node npm run test:unit:ci` runs Vitest once.
- `docker compose exec quasar-node npm run lint` checks JS/Vue; `npm run format` applies Prettier.
- `docker compose exec quasar-node npm run build` creates the production SPA bundle.

## Coding Style & Naming Conventions

Use four spaces for PHP and `docker-compose.yml`; use two for JS, Vue, other YAML, and JSON. Follow PSR-4 namespaces and Laravel conventions: singular PascalCase models, `*Controller`, `*Request`, and timestamped snake_case migrations. Vue components and pages use PascalCase filenames; Pinia stores use camelCase. Prettier enforces single quotes, no semicolons, and a 100-character line width.

## Testing Guidelines

Backend tests use Pest with Laravel's `RefreshDatabase`; name files `*Test.php` and group API coverage by version. Frontend tests use Vitest and Vue Test Utils with `*.test.js`. Add focused tests for changed behavior and run the relevant suite before submitting. Coverage is available with `npm run coverage` inside `quasar-node`; no numeric threshold is enforced.

## Commit & Pull Request Guidelines

Recent commits favor short imperative summaries, sometimes prefixed by scope or type, such as `docs: add detailed project documentation` or `fix bug with empty projects list`. Keep each commit focused. Pull requests should explain the user-visible change, identify affected API versions or screens, list verification commands, link related issues, and include screenshots for UI changes.

## Configuration & Data Safety

Copy `.env.example` to `.env`; never commit secrets. Keep `quasar_frontend/src/config.js` aligned with `APP_PORT`. Run `artisan storage:link` for material images. Note that `db:seed` intentionally truncates and rebuilds business data.
