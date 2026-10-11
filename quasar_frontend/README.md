# Renovation Estimator Frontend

Quasar 2 / Vue 3 SPA for the Renovation Estimator Laravel API. Repository-wide setup and architecture are documented in the root [README](../README.md) and [frontend guide](../docs/frontend.md).

## Development

Docker Compose starts this app automatically on `http://localhost:9008` and mounts this directory with Vite HMR. From the repository root:

```bash
./vendor/bin/sail up -d
```

The API origin is configured in `src/config.js` and must match the backend `APP_PORT`. To run the frontend without Compose, use Node 18+ and install the locked dependencies:

```bash
npm ci
npm run dev
```

## Checks and Build

Run these from this directory, or prefix them with `docker compose exec quasar-node` from the repository root:

```bash
npm run test:unit:ci   # Vitest, one pass
npm run lint           # ESLint for JS and Vue
npm run format         # apply Prettier
npm run coverage       # page coverage
npm run build          # production SPA bundle
```

Tests live in `test/vitest/__tests__`. Source code is organized into `src/pages`, `src/components`, `src/stores`, `src/router`, and `src/boot`.
