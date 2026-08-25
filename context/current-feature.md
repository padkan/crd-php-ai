# Current Feature

<!-- Feature Name -->

## Status

<!-- Not Started|In Progress|Completed -->

Not Started

## Goals

<!-- Goals & requirements -->

## Notes

<!-- Any extra notes -->

## History

<!-- Keep this updated. Earliest to latest. Organized as separate tracks; append new entries to the relevant track's timeline. -->

**General**

- Project setup and boilerplate cleanup

**Catalog UI rollout** — multi-phase initiative, see `context/features/catalog-phase-*-spec.md`

- Phase 1 — 2026-08-24: bootstrapped TypeScript tooling, initialized shadcn/ui (Base UI + Nova preset), added the /catalog route, and built the product list with create/edit/delete dialogs (dark mode by default)
- Phase 2 — 2026-08-24: replaced mock data with the real API (api/http.ts, api/products.ts), wired PUT/DELETE end-to-end on the backend (previously unreachable), and added description support across the full stack with a domain-level length guard
- Phase 3 — pending (spec exists at `context/features/catalog-phase-3-spec.md`, not yet started)
