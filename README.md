# speedyspec-api-seo

A core SEO API for SpeedySpec WordPress: one model of a page's search-facing output (title, meta description, robots
directives, canonical URL) that plugins build on.

This is a monorepo. Every contract lives in one package, and each implementation layer is its own package, so an
implementation can be replaced one contract at a time or as a whole layer.

## Packages

| Package | Layer | Purpose |
|---|---|---|
| `packages/wp-seo-domain` | Domain | Contracts, value objects, `HeadDocument`, per-contract service container. No dependencies. |
| `packages/wp-seo-app` | Application | Framework-free resolvers, head contributors, builder and renderer. |
| `packages/wp-seo-infra-wp` | Infrastructure | WordPress adapters and resolvers that keep WordPress's existing filters firing. |
| `packages/wp-seo-infra-memory` | Infrastructure | In-memory adapters for tests and non-WordPress hosts. |
| `packages/wp-seo-legacy` | Legacy | The existing WordPress functions (`wp_robots()`, `rel_canonical()`, ...) as wrappers over the contracts. |
| `packages/wp-seo-contract-tests` | Testing | Shared Pest contract tests that any replacement implementation runs. |

Packages find their siblings through Composer path repositories (`../*`).

## Replacing an implementation

Each implementation package ships a service provider that binds one factory per contract on `SeoServiceContainer`.

- **One contract:** call `add()` (replace) or `extend()` (decorate) for that contract before it is first resolved.
- **A whole layer:** install another package that provides `speedyspec/wp-seo-app-implementation` or
  `speedyspec/wp-seo-infra-implementation` and registers its own bindings.

## Running the tests

Each package is tested on its own:

```bash
cd packages/wp-seo-domain && composer install && vendor/bin/pest
```

`wp-seo-contract-tests` has no suite of its own; the other five packages do.

## Status

Phase 1 (contracts and the head core). Structured data, social cards, robots.txt, sitemaps, hreflang, redirects and
the admin screen are later phases.
