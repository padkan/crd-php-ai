# Current Feature: Catalog Phase 2

## Status

In Progress

## Goals

- Generic HTTP helper (`src/api/http.ts`) with `ApiError` and a `handleResponse<T>` that handles JSON, non-OK statuses, and 204 No Content
- Product API service (`src/api/products.ts`): `getProducts`, `getProduct`, `createProduct`, `updateProduct`, `deleteProduct` against the real `/api/products` endpoints
- Wire `CatalogPage` to the API layer instead of mock data, with separate `loading` and `submitting` states
- Surface backend error messages in the UI (list load errors and form submit errors)
- Keep API calls isolated in `src/api` — no `fetch()` inside UI components
- Product/ProductInput types centralized in `src/types/product.ts`, no `any`

## Notes

Phase 2 of 6: replace the mock-data-backed catalog UI from phase 1 with the real PHP backend. Reuse the existing `/catalog` page, Tailwind/shadcn UI, and React Router setup as-is — this phase only adds the API integration layer underneath.

Price stays in integer minor units end-to-end (convert only for display, matching `formatMoney`). Don't delete `frontend/src/lib/mock-data.ts` since a later phase may still reference it. Don't introduce React Query/Redux/Zustand/Axios.

Backend routes already exist per CLAUDE.md: `GET/POST /api/products`, `GET /api/products/{id}`; `PUT`/`DELETE` wiring in `config/routes.php` and `public/index.php` may still need to be added if not already done — verify before assuming they work.

References:

- @context/features/catalog-phase-2-spec.md

## History

<!-- Keep this updated. Earliest to latest -->

- Project setup and boilerplate cleanup
- Catalog Phase 1: bootstrapped TypeScript tooling, initialized shadcn/ui (Base UI + Nova preset), added the /catalog route, and built the product list with create/edit/delete dialogs (dark mode by default)
