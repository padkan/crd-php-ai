# Coding Standards — Product Management App

## Scope

These standards apply to the full project:

```text
project/
├── src/
├── config/
├── public/
├── tests/
├── frontend/
├── context/
├── docker/
├── composer.json
└── docker-compose.yml
```

Project architecture is defined in:

```text
context/project-overview.md
```

Frontend-specific guidance is defined in:

```text
context/frontend-guidelines.md
```

Claude Code should read both files before making architectural changes.

These coding standards apply to both backend and frontend unless a section explicitly states otherwise.

---

# General Principles

Prefer:

- clear code
- explicit dependencies
- small focused classes/functions
- domain-oriented naming
- composition over inheritance
- framework-independent business logic
- simple solutions over speculative abstractions
- testable architecture
- predictable error handling

Avoid:

- unnecessary patterns
- generic abstractions
- hidden dependencies
- global mutable state
- service locators
- singleton-based application design
- business rules in controllers
- SQL inside application use cases
- premature optimization
- abstractions introduced only to demonstrate knowledge

Every abstraction should answer:

```text
Why does this exist?
What responsibility does it have?
What should NOT be inside it?
```

---

# Backend — PHP

## PHP Version

Use:

```text
PHP 8.5+
```

Every PHP source file should use:

```php
<?php

declare(strict_types=1);
```

Use modern PHP features where they improve clarity.

Preferred:

- constructor property promotion
- readonly classes/properties where appropriate
- enums where they model a real finite domain concept
- named arguments when they improve readability
- union/null types only when semantically meaningful

Do not use new language features merely to show knowledge.

---

# PHP Typing

Use strict types.

Do not leave public APIs untyped.

Prefer explicit types for:

```text
constructor arguments
method arguments
method return types
repository interfaces
application service boundaries
domain methods
DTOs
value objects
```

Good:

```php
public function findById(int $id): ?Product
```

Avoid:

```php
public function findById($id)
```

Use `mixed` only at genuine unsafe boundaries.

Validate and convert external data before sending it deeper into the application.

---

# PHP Naming

Use:

```text
Classes           → PascalCase
Interfaces        → PascalCase + Interface when useful
Methods           → camelCase
Variables         → camelCase
Constants         → SCREAMING_SNAKE_CASE
Namespaces        → PascalCase segments
Database columns  → snake_case
API JSON fields   → camelCase
```

Examples:

```text
CreateProduct
ProductRepositoryInterface
PdoProductRepository
changePrice
createdBy
price_cents
createdAt
```

Use domain language.

Good:

```text
Product
Money
CreateProduct
changePrice
ProductCreationPolicy
```

Avoid meaningless names:

```text
DataHandler
Manager
Helper
Stuff
Object2
processData
doThing
```

---

# Namespaces and PSR-4

Composer maps:

```text
App\ → src/
Tests\ → tests/
```

Example:

```text
src/Product/Domain/Entity/Product.php
```

must use:

```php
namespace App\Product\Domain\Entity;
```

Use Composer autoloading.

Do not manually `require` application classes.

Only bootstrap Composer once:

```php
require dirname(__DIR__) . '/vendor/autoload.php';
```

---

# Backend Architecture

Use lightweight Domain-Driven Design.

Main flow:

```text
HTTP
 ↓
Controller
 ↓
Application Use Case
 ↓
Domain
 ↓
Repository Interface
 ↑
Infrastructure Repository
 ↓
PDO
 ↓
MySQL
```

Dependency direction must remain intentional.

The Domain must not depend on:

```text
Symfony
PDO
MySQL
HTTP
JSON
React
Docker
```

Infrastructure may depend on Domain abstractions.

---

# Domain Layer

The Domain layer contains:

```text
Entities
Value Objects
Domain Rules
Domain Policies
Repository Interfaces when domain-owned
Factories when genuinely needed
```

Example structure:

```text
src/Product/Domain/
├── Entity/
├── ValueObject/
├── Repository/
├── Policy/
└── Factory/
```

Domain objects should protect valid business state.

Example:

```php
$product->rename($name);
$product->changePrice($money);
```

Prefer behavior over exposing writable public state.

Avoid anemic entities where all rules live outside the domain.

---

# Entities

Entities have identity.

Example:

```text
Product
User
Order
```

A Product may contain:

```text
id
name
price
createdBy
```

Identity determines entity continuity.

Do not treat an Entity as a generic DTO.

Business behavior belongs on the Entity when the rule naturally belongs to its state.

Example:

```php
$product->changePrice($newPrice);
```

instead of:

```php
$product->price = $newPrice;
```

---

# Value Objects

Use Value Objects for business concepts defined by their value.

Examples:

```text
Money
ProductId
UserId
Email
Currency
```

Value Objects should generally be immutable.

Example:

```php
final readonly class Money
{
    public function __construct(
        private int $amount,
        private string $currency,
    ) {
    }
}
```

Avoid primitive obsession when a primitive carries important business meaning.

---

# Money

Do not use PHP `float` for persisted monetary values.

Current project representation:

```text
integer minor units + currency
```

Example:

```text
9990 EUR = €99.90
```

Preferred Domain representation:

```php
new Money(
    amount: 9990,
    currency: 'EUR',
);
```

Database representation:

```text
price_cents BIGINT UNSIGNED
currency CHAR(3)
```

If the project evolves into a broader international financial system, reconsider whether generic minor units or exact `DECIMAL` values are more appropriate.

Do not silently convert persisted exact monetary values to float.

---

# Domain Rules

Business rules belong in the Domain when possible.

Bad:

```php
if ($user->productCount() >= 10) {
    // controller/application rule
}
```

Preferred when the rule belongs to User:

```php
$user->registerProductCreation();
```

or:

```php
$user->canCreateProduct();
```

Use a Domain Policy when a rule spans multiple concepts or becomes too complex for one Entity.

Example:

```text
ProductCreationPolicy
```

Do not extract every simple condition into a Policy.

---

# Application Layer

Application classes represent use cases.

Examples:

```text
CreateProduct
GetProduct
ListProducts
UpdateProduct
DeleteProduct
```

Application responsibilities:

```text
coordinate use case
load domain objects
call domain behavior
coordinate repositories
coordinate transaction boundaries when necessary
return result
```

Application classes should not:

```text
write SQL
parse HTTP
send JSON
know Symfony Request
know PDO
contain presentation logic
```

Example:

```php
final readonly class CreateProduct
{
    public function __construct(
        private ProductRepositoryInterface $repository,
    ) {
    }

    public function execute(
        string $name,
        int $price,
        string $currency,
    ): Product {
        $product = new Product(
            id: null,
            name: $name,
            price: new Money($price, $currency),
        );

        return $this->repository->save($product);
    }
}
```

---

# Dependency Injection

Use constructor injection.

Good:

```php
public function __construct(
    private ProductRepositoryInterface $repository,
) {
}
```

Avoid:

```php
$this->repository = new PdoProductRepository(...);
```

inside application/domain classes.

Inject collaborators/services.

Create domain objects directly when creation is part of the use case.

Example:

```php
$money = new Money(...);
$product = new Product(...);
```

Do not inject Entity instances as long-lived services.

---

# Factory Usage

Do not create factories prematurely.

Direct construction is preferred while creation remains simple.

Good:

```php
$product = new Product(
    id: null,
    name: $name,
    price: new Money($price, $currency),
);
```

Introduce a Factory when product construction has meaningful complexity.

Example reasons:

```text
generated domain IDs
multiple required value objects
default status rules
clock dependency
complex invariants
different creation variants
```

Factory responsibility:

```text
how to construct a valid new Product
```

Application responsibility:

```text
what should happen in the CreateProduct use case
```

---

# Repository Interfaces

Application/Domain code depends on repository abstractions.

Example:

```php
interface ProductRepositoryInterface
{
    public function save(Product $product): Product;

    public function findById(int $id): ?Product;

    /** @return list<Product> */
    public function findAll(): array;

    public function delete(Product $product): void;
}
```

Repository interfaces should express domain/application needs.

Avoid generic repository methods such as:

```text
findEverything
executeQuery
raw
getData
```

---

# Infrastructure Repositories

Infrastructure owns database-specific persistence.

Example:

```text
PdoProductRepository
```

Infrastructure may know:

```text
PDO
SQL
MySQL
table names
column names
hydration logic
```

Application and Domain must not know these details.

Always use prepared statements for dynamic values.

Good:

```php
$statement = $this->pdo->prepare(
    'SELECT id, name
     FROM products
     WHERE id = :id'
);

$statement->execute([
    'id' => $id,
]);
```

Never interpolate untrusted values directly into SQL.

---

# Database Connection

Use one PDO connection created during application bootstrap.

Inject PDO into repositories.

Do not:

```text
create PDO inside every repository method
use Singleton for database access
use global connection state
```

Preferred composition:

```text
Database config
 ↓
PDO
 ↓
PdoProductRepository
 ↓
Application Use Cases
```

PDO configuration should enable exceptions.

Example:

```php
PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
```

---

# Database IDs

For the current project use:

```sql
BIGINT UNSIGNED AUTO_INCREMENT
```

Example:

```sql
id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
```

This is preferred for the coding challenge because it is:

```text
simple
efficient
easy to inspect
easy to join
easy to explain
```

Consider UUID/ULID only when system requirements justify distributed ID generation or externally generated identities.

Do not introduce UUID merely because it appears more advanced.

---

# Database Naming

Database columns use snake_case.

Examples:

```text
id
price_cents
created_at
user_id
product_id
```

PHP/API names use camelCase when appropriate.

Examples:

```text
createdAt
userId
productId
```

Keep persistence naming separate from API/domain naming when needed.

---

# Database Schema

Current Product table:

```sql
CREATE TABLE products (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    price_cents BIGINT UNSIGNED NOT NULL,
    currency CHAR(3) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);
```

Use:

- primary keys
- foreign keys when relationships require them
- unique constraints where uniqueness is a business/data invariant
- indexes based on real query patterns

Do not add indexes randomly.

---

# Migrations

Database schema changes should become reproducible.

For the current small project, a simple migration/schema mechanism is acceptable.

Preferred workflow:

```text
change schema
 ↓
review SQL
 ↓
run locally
 ↓
run repository/integration tests
 ↓
commit migration
```

Do not rely on manually editing production databases.

Do not reset persistent production volumes to apply schema changes.

---

# Transactions

Use transactions for multi-step writes that must be atomic.

Example:

```text
create Product
+
increment User product count
```

If both operations are part of one business transaction:

```text
begin
 ↓
save Product
 ↓
save User
 ↓
commit
```

On failure:

```text
rollback
```

Application layer may coordinate transaction boundaries through an abstraction.

Do not scatter `beginTransaction()` calls throughout Domain code.

---

# Symfony Components

This project uses selected Symfony Components, not the full Symfony framework.

Approved infrastructure components:

```text
symfony/http-foundation
symfony/routing
```

Potentially:

```text
symfony/console
```

when CLI commands are genuinely needed.

Symfony components are infrastructure helpers.

They must not leak into Domain or core Application logic.

---

# HTTP Request

Use:

```php
Symfony\Component\HttpFoundation\Request
```

Create it at the HTTP boundary:

```php
$request = Request::createFromGlobals();
```

HTTP parsing belongs in the HTTP layer.

Do not pass Symfony Request objects into Domain classes.

Prefer extracting primitive/DTO input before calling Application.

---

# HTTP Response

Use:

```php
Symfony\Component\HttpFoundation\JsonResponse
```

Controllers return HTTP responses.

Example:

```php
return new JsonResponse(
    $payload,
    201,
);
```

Do not manually mix:

```text
header()
http_response_code()
echo json_encode()
```

when HttpFoundation already solves the concern.

---

# Routing

Use Symfony Routing.

Keep route definitions outside controllers.

Preferred file:

```text
config/routes.php
```

Use RESTful resource-oriented paths.

Good:

```text
GET    /api/products
GET    /api/products/{id}
POST   /api/products
PUT    /api/products/{id}
DELETE /api/products/{id}
```

Avoid RPC-like endpoint names for CRUD:

```text
/api/getProducts
/api/createProduct
/api/deleteProduct
```

Use route requirements where useful.

Example:

```php
requirements: [
    'id' => '\d+',
]
```

---

# Controllers

Controllers must remain thin.

Controller responsibilities:

```text
read HTTP input
validate/normalize boundary data
call Application Use Case
map result to HTTP response
```

Controllers must not:

```text
contain SQL
create PDO
implement core business rules
contain long workflows
directly mutate database
```

Preferred flow:

```text
Request
 ↓
Controller
 ↓
Use Case
 ↓
Response
```

---

# API Design

Use REST for the current project.

Current endpoints:

```text
GET    /api/products
GET    /api/products/{id}
POST   /api/products
PUT    /api/products/{id}
DELETE /api/products/{id}
```

Use appropriate HTTP status codes.

Examples:

```text
200 OK
201 Created
204 No Content
400 Bad Request
404 Not Found
409 Conflict
422 Unprocessable Content
500 Internal Server Error
```

Do not expose internal exceptions directly to clients.

---

# API Contracts

Do not expose raw database rows as an intentional API design.

Map Domain/Application results to explicit API response shapes.

Current Product API:

```json
{
  "id": 1,
  "name": "Keyboard",
  "price": 9990,
  "currency": "EUR"
}
```

API JSON fields use camelCase.

Frontend must not depend on MySQL column names such as:

```text
price_cents
created_at
```

---

# Error Handling

Never silently swallow errors.

At system boundaries:

- validate external input
- throw meaningful Domain/Application errors
- map expected errors to appropriate HTTP responses
- log internal details server-side when logging exists
- return safe messages externally

Never expose:

```text
database credentials
SQL statements with sensitive data
stack traces
internal filesystem paths
authorization tokens
Docker-internal hostnames
```

Avoid:

```php
catch (Throwable $e) {
    return new JsonResponse([
        'error' => $e->getMessage(),
    ]);
}
```

for unexpected production failures.

Use a generic external message for unexpected exceptions.

---

# Validation

Validate external input.

External input includes:

```text
HTTP request body
query parameters
route parameters
headers
environment variables
file metadata
authentication information
```

Frontend validation is UX only.

Backend validation remains authoritative.

Domain invariants should still protect invalid state even if controller validation is bypassed.

---

# Frontend — TypeScript

Use TypeScript strict mode.

Do not use `any`.

Use `unknown` at unsafe boundaries and narrow it.

Prefer inferred local types where obvious.

Use explicit types for:

```text
component props
API contracts
shared Product types
public utility APIs
route params where needed
```

Avoid broad assertions.

Do not use:

```ts
value as unknown as SomeType;
```

to hide type errors.

---

# Frontend Naming

Use:

```text
Components       → PascalCase
Types            → PascalCase
Functions        → camelCase
Variables        → camelCase
Constants        → SCREAMING_SNAKE_CASE when truly constant
Files            → kebab-case for utilities/modules where practical
API JSON fields  → camelCase
```

Examples:

```text
ProductsPage
ProductForm
ProductTable
ProductInput
createProduct
selectedProduct
formatMoney
```

Use domain language.

Avoid:

```text
data2
temp
obj
thing
handler2
doStuff
```

---

# Frontend Imports

Prefer configured aliases.

Example:

```ts
import { Button } from "@/components/ui/button";
import type { Product } from "@/types/product";
```

Use `import type` for type-only imports.

Avoid deep relative imports such as:

```text
../../../../components/...
```

when an alias exists.

Avoid circular dependencies.

---

# React

Use functional components only.

Keep components focused.

Prefer composition over mega-components.

Avoid unnecessary `useEffect`.

Use `useEffect` for actual side effects such as initial API loading.

Do not use it to derive state that can be calculated during render.

Avoid unnecessary root-level context providers.

Do not memoize everything by default.

Measure before adding performance-specific memoization.

Use semantic HTML.

---

# React Router

React Router is part of the intended frontend stack.

Use it for:

```text
page navigation
route selection
route parameters
not-found routes
future detail/edit pages
```

Do not use React Router as:

```text
form state
modal state
API state
product list state
loading state
```

Preferred routing structure:

```text
frontend/src/router/
└── index.tsx
```

Pages:

```text
frontend/src/pages/
├── ProductsPage.tsx
└── NotFoundPage.tsx
```

Keep routing simple.

---

# Frontend Data Access

Frontend accesses backend data only through HTTP abstractions.

Preferred:

```text
Component/Page
 ↓
api/products.ts
 ↓
HTTP
 ↓
PHP Backend
```

Do not call backend endpoints from many random components.

Keep Product API functions in:

```text
frontend/src/api/products.ts
```

Use relative paths:

```ts
fetch("/api/products");
```

Do not hardcode local hostnames unless environment requirements justify it.

---

# Frontend State

For the current project, prefer local React state.

Use:

```text
useState
useEffect
```

where appropriate.

Do not add by default:

```text
Redux
MobX
Zustand
React Query
```

unless the application complexity justifies them.

Keep page-level CRUD state in `ProductsPage` or a small dedicated hook if extraction becomes useful.

---

# Tailwind CSS v4

CRITICAL: this project uses Tailwind CSS v4.

Do not create:

```text
tailwind.config.js
tailwind.config.ts
```

unless an explicit compatibility requirement exists.

Use Tailwind v4 CSS-based configuration.

Example:

```css
@import "tailwindcss";
```

Use Tailwind as the default styling mechanism.

Avoid CSS-in-JS.

Avoid large custom CSS files for ordinary component styling.

Prefer semantic design tokens where available.

---

# shadcn/ui

Generated shadcn primitives belong in:

```text
frontend/src/components/ui/
```

Business/Product components belong elsewhere.

Example:

```text
frontend/src/components/products/
```

Do not put `ProductForm` into `components/ui`.

Prefer composing shadcn primitives rather than heavily modifying generated files.

If modifying a generated primitive:

- keep the change minimal
- understand why it is necessary
- avoid introducing business logic there

Use accessible labels for interactive controls.

---

# Frontend File Organization

Preferred:

```text
frontend/src/
├── api/
├── components/
│   ├── products/
│   └── ui/
├── pages/
├── router/
├── types/
├── App.tsx
├── main.tsx
└── index.css
```

Responsibilities:

```text
api
→ backend communication

components/ui
→ generic shadcn components

components/products
→ Product-specific UI

pages
→ page-level coordination

router
→ navigation

types
→ shared frontend types
```

Avoid broad dumping-ground files such as:

```text
helpers.ts
everything.ts
common.ts
```

Prefer domain-specific names.

---

# Product Frontend Types

Use explicit frontend types.

Example:

```ts
export type Product = {
  id: number;
  name: string;
  price: number;
  currency: string;
};

export type ProductInput = {
  name: string;
  price: number;
  currency: string;
};
```

Do not expose backend PHP Entity classes or database row shapes to React.

Frontend contracts are API contracts.

---

# Frontend Validation

Frontend validation improves UX.

Example:

```text
name required
price >= 0
currency required
```

Do not rely on frontend validation for security or Domain correctness.

Backend remains the source of truth.

---

# Styling

Use:

```text
Tailwind CSS
shadcn/ui
```

Prefer:

- responsive layouts
- mobile-first design
- semantic HTML
- keyboard-accessible interactions
- visible focus styles
- consistent spacing
- existing design tokens

Avoid:

- unnecessary inline styles
- arbitrary one-off colors when semantic tokens exist
- clickable `div` elements when `button` is appropriate

---

# Testing — Backend

Use PHPUnit.

Testing levels:

```text
Unit
Integration
Functional/API
```

Unit tests target:

```text
Money
Product
Application Use Cases
Domain Policies
```

Application tests may use fake or mocked repositories.

Example:

```text
CreateProduct
 ↓
InMemoryProductRepository
```

Repository behavior should be integration tested against real MySQL when correctness depends on SQL/schema behavior.

Examples:

```text
PdoProductRepositoryTest
```

Test:

- inserts
- updates
- hydration
- deletion
- missing rows
- transaction rollback when relevant

---

# Testing — Frontend

Use Vitest where frontend tests are added.

Testing Library may be added for component behavior if needed.

Prefer testing behavior rather than implementation details.

Useful targets:

```text
formatMoney
ProductForm validation
ProductsPage loading state
ProductsPage error state
CRUD user behavior
```

Frontend testing is secondary to core Domain/Application correctness during a time-limited challenge unless requirements prioritize frontend tests.

---

# Test Naming

Use descriptive behavior-oriented test names.

Good:

```text
rejects a negative money amount
creates a product with a valid price
returns null when product does not exist
prevents a user from creating more than ten products
removes a deleted product from the table
```

Avoid:

```text
works
test1
testProduct
handlesData
```

---

# Code Quality

Do not leave:

```text
unused imports
unused variables
commented-out production code
debug logging
temporary test code
dead feature flags
broad error-swallowing catch blocks
```

Avoid giant files when clear domain separation exists.

Do not extract tiny functions/classes when they create more indirection than clarity.

---

# Functions and Methods

Keep functions focused and named by responsibility.

Good:

```text
createProduct
changePrice
findById
formatMoney
loadProducts
```

Avoid:

```text
process
handleStuff
doWork
manage
executeData
```

Do not enforce arbitrary line limits.

Extract when the code becomes difficult to understand or a separate concept emerges.

---

# Comments

Comments should explain:

```text
why
business constraint
security reason
non-obvious tradeoff
technical limitation
```

Do not narrate obvious syntax.

Bad:

```php
// Create new product
$product = new Product(...);
```

Better:

```php
// Product prices are stored as integer minor units to avoid floating-point precision issues.
```

---

# Dependencies

Prefer existing stack capabilities.

Before adding a dependency ask:

```text
Is this already solved by PHP?
Is this already solved by Symfony Components?
Is this already solved by React?
Is this already solved by React Router?
Is this already solved by Tailwind/shadcn?
Is this necessary for the current requirement?
What maintenance/runtime cost does it introduce?
```

Do not add a package only to save a few lines of simple code.

Approved current dependencies include:

```text
PHP
Composer
PDO
Symfony HttpFoundation
Symfony Routing
PHPUnit

React
React DOM
React Router
Vite
TypeScript
Tailwind CSS v4
shadcn/ui
```

---

# Security Standards

Never:

- expose database credentials to frontend code
- commit real secrets
- log authentication tokens
- trust frontend validation
- interpolate untrusted data into SQL
- expose stack traces publicly
- return raw SQL errors to users
- store passwords in plaintext
- use UI hiding as authorization
- trust user IDs from the browser without backend authorization checks

Use:

```text
prepared statements
backend authorization
environment variables
safe HTTP error mapping
```

---

# Authorization

Authorization belongs on the backend.

Frontend hiding a button is only UX.

If product ownership is introduced, backend must verify:

```text
authenticated user
product ownership
role/permission
allowed action
```

Do not trust:

```text
userId from request body
```

as proof of identity.

Identity should come from authenticated context.

---

# Performance

Frontend:

- avoid duplicate API requests
- avoid unnecessary global state
- keep component boundaries reasonable
- measure before memoizing
- avoid oversized dependencies

Backend:

- use proper indexes
- use one reusable PDO connection per request/bootstrap
- avoid N+1 query patterns
- select only required columns when practical
- use transactions where correctness requires them
- measure before introducing caching

---

# Docker

Docker is a development/runtime tool, not a Domain concern.

Current services:

```text
nginx
php
mysql
frontend
```

Container communication uses Docker service names.

Examples:

```text
PHP → mysql:3306
Nginx → php:9000
Nginx → frontend:5173
```

Do not use `localhost` for container-to-container service communication.

Do not store application business logic in Docker scripts.

---

# Formatting

Use the existing project formatting/linting configuration.

Do not introduce multiple competing formatters.

Frontend should follow the configured TypeScript/ESLint style.

PHP should use consistent PSR-style formatting.

Keep formatting changes separate from unrelated architectural changes where practical.

---

# Documentation

Update documentation when changes affect:

```text
architecture
API contracts
folder structure
development commands
database schema
routing
dependencies
Docker setup
testing strategy
```

Do not update documentation for trivial internal refactors unless the existing documentation becomes inaccurate.

Important project documentation:

```text
context/project-overview.md
context/frontend-guidelines.md
context/coding-standards.md
CLAUDE.md
```

---

# Claude Code Guidance

Before making changes Claude Code should:

1. Read `context/project-overview.md`.
2. Read `context/frontend-guidelines.md` for frontend tasks.
3. Read `context/coding-standards.md`.
4. Inspect existing implementation before creating new abstractions.
5. Preserve the lightweight DDD dependency direction.
6. Keep Domain independent from Symfony/PDO/React.
7. Reuse existing Symfony Components rather than rebuilding HTTP infrastructure.
8. Reuse existing shadcn primitives rather than rebuilding generic controls.
9. Do not add dependencies without a concrete reason.
10. Prefer working, explicit code over speculative architecture.
11. Keep code understandable enough to explain during a senior code review.
12. Do not silently redesign existing architecture.

---

# Coding Challenge Priorities

This project is intended as preparation for a senior full-stack coding challenge.

Priority order:

```text
1. Correct behavior
2. Clear Domain model
3. Clean architecture
4. Working end-to-end flow
5. Tests
6. Error handling
7. Code readability
8. Visual polish
9. Optional abstractions
```

Do not sacrifice a working solution to implement unnecessary patterns.

When time is limited:

```text
working simple design
>
unfinished complex architecture
```

---

# Definition of Done

A change is complete when:

```text
requested behavior works
code follows architecture boundaries
types are correct
no unused code remains
relevant tests pass
frontend builds when frontend changed
backend tests pass when backend changed
API contract remains consistent
database schema changes are reproducible
business rules remain on backend/domain
errors are handled safely
no unnecessary dependency was added
documentation is updated when architecture/contracts changed
```

For user-facing CRUD changes, verify at minimum:

```text
GET works
POST works
PUT works
DELETE works
loading state works
error state works
empty state works
invalid backend input is rejected
```

---

# Final Engineering Rule

Prefer the simplest design that protects the business rules and keeps dependencies explicit.

Patterns are tools, not goals.

Use:

```text
DDD
Dependency Injection
Repository
Factory
Policy
Value Object
Transaction
```

only when each solves a concrete problem.

The goal is not to demonstrate the largest number of patterns.

The goal is to produce code that is:

```text
correct
clear
testable
maintainable
explainable
```
