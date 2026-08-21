# Product Management App — Project Overview

## Project Purpose

This project is a small full-stack Product Management application built as preparation for a senior PHP/full-stack coding challenge.

The main goal is to demonstrate:

- Clean PHP 8.5 code
- Lightweight Domain-Driven Design
- Clear separation of concerns
- REST API design
- MySQL persistence with PDO
- React frontend architecture
- Unit and integration testing
- Practical use of selected Symfony components
- Docker-based local development

The implementation should remain intentionally small and understandable.

Avoid unnecessary abstraction or framework-level complexity.

---

## Core Domain

The current domain is **Product Management**.

A Product contains:

- ID
- Name
- Price
- Currency
- Creation timestamp

Money should be represented using integer minor units.

Example:

```text
9990 = €99.90
```

Avoid floating-point values for money.

---

## Domain Rules

Current rules:

- Product name must not be empty.
- Product price cannot be negative.
- Currency should use a valid 3-letter code.
- Business rules belong in the Domain layer.
- HTTP, PDO, MySQL, React, and JSON concerns must not leak into the Domain layer.

Future business rules may include:

- Product creation limits per user
- User roles and permissions
- Product status
- Pricing rules
- Product ownership

These should be implemented using domain behavior or domain policies when appropriate.

---

## Architecture

The backend follows lightweight Domain-Driven Design.

```text
React
  |
  | HTTP / JSON
  v
Symfony HTTP / Routing
  |
  v
Controller
  |
  v
Application Use Case
  |
  v
Domain
  |
  v
Repository Interface
  ^
  |
Infrastructure Repository
  |
  v
PDO
  |
  v
MySQL
```

Main rule:

> Application coordinates use cases.
> Domain contains business rules.
> Infrastructure handles technical implementation details.

---

## Backend Structure

```text
src/
├── Product/
│   ├── Domain/
│   │   ├── Entity/
│   │   │   └── Product.php
│   │   │
│   │   ├── ValueObject/
│   │   │   └── Money.php
│   │   │
│   │   └── Repository/
│   │       └── ProductRepositoryInterface.php
│   │
│   ├── Application/
│   │   └── UseCase/
│   │       ├── CreateProduct.php
│   │       ├── GetProduct.php
│   │       ├── ListProducts.php
│   │       ├── UpdateProduct.php
│   │       └── DeleteProduct.php
│   │
│   └── Infrastructure/
│       └── Persistence/
│           └── PdoProductRepository.php
│
├── Http/
│   └── Controller/
│       └── ProductController.php
│
└── Shared/
    └── Infrastructure/
        └── Database.php

config/
└── routes.php

public/
└── index.php

tests/
├── Unit/
└── Integration/
```

---

## Domain Layer

The Domain layer contains the core business model.

Examples:

```text
Product
Money
ProductRepositoryInterface
```

The Domain must not depend on:

```text
Symfony
PDO
MySQL
React
Docker
HTTP
JSON
```

### Product

`Product` is an Entity because it has identity.

Example responsibilities:

- Store product identity
- Store product name
- Store product price
- Rename product
- Change product price
- Protect product invariants

Example conceptual API:

```php
$product->rename($name);

$product->changePrice($money);
```

Business rules should preferably be protected by the domain object itself.

---

## Value Objects

`Money` is a Value Object.

Example:

```text
Money
├── amount: 9990
└── currency: EUR
```

Rules:

- Amount cannot be negative.
- Currency uses a 3-letter code.
- Money should be immutable.

Example:

```php
new Money(
    amount: 9990,
    currency: 'EUR',
);
```

---

## Application Layer

Application classes represent use cases.

Examples:

```text
CreateProduct
GetProduct
ListProducts
UpdateProduct
DeleteProduct
```

Application classes coordinate work.

They should not contain:

- SQL
- HTTP logic
- JSON serialization
- PDO setup

Example responsibility of `CreateProduct`:

```text
1. Receive application input
2. Create Money
3. Create Product
4. Ask repository to persist Product
5. Return Product
```

Application classes should depend on abstractions where appropriate.

Example:

```php
final readonly class CreateProduct
{
    public function __construct(
        private ProductRepositoryInterface $repository,
    ) {
    }
}
```

---

## Dependency Injection

Use constructor injection for services and collaborators.

Example:

```text
CreateProduct
    |
    v
ProductRepositoryInterface
```

Do not use Singleton for database access.

Do not use traits merely to demonstrate language features.

Create domain objects directly when they are the result of the use case.

Example:

```php
$money = new Money(...);

$product = new Product(...);
```

Inject infrastructure or collaborating services.

Example:

```php
public function __construct(
    private ProductRepositoryInterface $repository,
) {
}
```

If domain object creation becomes complex, a `ProductFactory` may be introduced.

Do not introduce factories prematurely.

---

## Repository Pattern

The Domain/Application side depends on:

```text
ProductRepositoryInterface
```

Infrastructure provides:

```text
PdoProductRepository
```

Dependency direction:

```text
Application
    |
    v
ProductRepositoryInterface
    ^
    |
PdoProductRepository
```

The application must not directly instantiate a PDO repository.

---

## Database

Database:

```text
MySQL 8.4
```

Persistence:

```text
PDO
pdo_mysql
```

Current schema:

```sql
CREATE TABLE products (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    price_cents INT UNSIGNED NOT NULL,
    currency CHAR(3) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);
```

Repository SQL should always use prepared statements for dynamic values.

---

## HTTP Layer

The project uses selected Symfony components rather than the full Symfony framework.

Packages:

```text
symfony/http-foundation
symfony/routing
```

Use Symfony for generic HTTP and routing infrastructure.

Keep business logic independent from Symfony.

### Request

Use:

```php
Symfony\Component\HttpFoundation\Request
```

Example:

```php
$request = Request::createFromGlobals();

$data = $request->toArray();
```

### Response

Use:

```php
Symfony\Component\HttpFoundation\JsonResponse
```

Example:

```php
return new JsonResponse(
    $data,
    200,
);
```

### Routing

Use:

```php
Symfony\Component\Routing\Route
Symfony\Component\Routing\RouteCollection
Symfony\Component\Routing\Matcher\UrlMatcher
```

Routes should be defined separately in:

```text
config/routes.php
```

---

## API Endpoints

Current REST API:

```text
GET    /api/products
GET    /api/products/{id}
POST   /api/products
PUT    /api/products/{id}
DELETE /api/products/{id}
```

### Create Product

Request:

```json
{
  "name": "Keyboard",
  "price": 9990,
  "currency": "EUR"
}
```

Response:

```json
{
  "id": 1,
  "name": "Keyboard",
  "price": 9990,
  "currency": "EUR"
}
```

Expected status:

```text
201 Created
```

---

## Request Flow

Example:

```text
POST /api/products
        |
        v
Symfony Router
        |
        v
ProductController::create()
        |
        v
CreateProduct::execute()
        |
        +--> new Money(...)
        |
        +--> new Product(...)
        |
        v
ProductRepositoryInterface
        |
        v
PdoProductRepository
        |
        v
MySQL
        |
        v
JsonResponse 201
```

---

## Controller Rules

Controllers should remain thin.

Controller responsibilities:

- Read HTTP input
- Call application use case
- Convert result into HTTP response

Controllers should not:

- Write SQL
- Contain business rules
- Construct database connections
- Directly manage transactions unless acting purely as infrastructure orchestration

---

## Frontend Stack

Frontend:

```text
React
Vite
TypeScript
Tailwind CSS v4
shadcn/ui
```

Frontend is located in:

```text
frontend/
```

Expected structure:

```text
frontend/src/
├── api/
│   └── products.ts
│
├── components/
│   ├── products/
│   │   ├── ProductForm.tsx
│   │   └── ProductList.tsx
│   │
│   └── ui/
│       └── shadcn components
│
├── types/
│   └── product.ts
│
├── App.tsx
├── main.tsx
└── index.css
```

---

## Frontend Architecture

React components should focus on UI and user interaction.

HTTP calls should live outside UI components when practical.

Example:

```text
ProductForm
    |
    v
api/products.ts
    |
    | POST /api/products
    v
PHP Backend
```

Do not introduce Redux or another global state library unless requirements justify it.

Prefer:

```text
useState
useEffect
small reusable hooks
simple component composition
```

for the current scope.

---

## Product Type

Frontend product type should roughly match the API:

```ts
export type Product = {
  id: number;
  name: string;
  price: number;
  currency: string;
};
```

---

## Frontend API Layer

Preferred calls:

```ts
fetch("/api/products");
```

Do not hardcode:

```text
http://localhost:8080
```

inside React API functions unless necessary.

Nginx is responsible for routing `/api/*` requests to the PHP backend.

---

## UI

Use:

```text
Tailwind CSS v4
shadcn/ui
```

Current UI goal:

- Clean
- Minimal
- Responsive
- Developer-friendly
- Fast to implement during coding challenges

Preferred shadcn components:

```text
Button
Input
Label
Card
Table
Dialog
```

Avoid installing large numbers of components that are not required.

---

## Docker Development Environment

Services:

```text
nginx
php
mysql
frontend
```

Architecture:

```text
Browser
   |
   | localhost:8080
   v
Nginx
   |
   +---- /api/* ----> PHP-FPM
   |                     |
   |                     v
   |                   MySQL
   |
   +---- /* ---------> React / Vite
```

PHP container:

```text
PHP 8.5 FPM
Composer
PDO
pdo_mysql
unzip
```

Database service hostname from PHP:

```text
mysql
```

Do not use:

```text
localhost
```

for container-to-container MySQL communication.

---

## Composer

Composer handles:

- PHP dependency management
- PSR-4 autoloading
- PHPUnit

Namespace:

```text
App\ -> src/
```

Example:

```text
src/Product/Domain/Entity/Product.php

App\Product\Domain\Entity\Product
```

---

## Testing Strategy

Testing is important.

### Unit Tests

Test Domain and Application logic without MySQL.

Examples:

```text
MoneyTest
ProductTest
CreateProductTest
```

Use an in-memory or fake repository for Application tests.

Example:

```text
CreateProduct
      |
      v
InMemoryProductRepository
```

### Integration Tests

Test infrastructure with a real database.

Examples:

```text
PdoProductRepositoryTest
```

These verify:

- SQL
- hydration
- insert/update/delete behavior

### Functional Tests

If time allows, test API behavior:

```text
POST /api/products
GET /api/products
```

---

## Development Principles

Prefer:

- Simple code
- Explicit dependencies
- Constructor injection
- Small focused classes
- Domain rules inside Domain
- PSR-4
- Strict types
- Typed properties
- Readonly where appropriate
- Prepared SQL statements
- Small use cases
- Clear naming
- Composition over inheritance

Avoid:

- Singleton
- Service Locator
- Global mutable state
- Traits without a real need
- Large base classes
- Framework recreation
- Premature factories
- Premature abstractions
- Pattern usage only to demonstrate knowledge
- Business rules in controllers
- SQL in application services

---

## DDD Guidelines

Use lightweight DDD.

Useful concepts:

```text
Entity
Value Object
Repository
Application Use Case
Domain Policy
Domain Factory when justified
Bounded Context
Ubiquitous Language
```

Do not introduce by default:

```text
CQRS
Event Sourcing
Command Bus
Domain Events
Specification Pattern
Complex Aggregate architecture
```

unless the requirements genuinely benefit from them.

---

## Senior Engineering Principle

Every abstraction should answer:

```text
Why does this class exist?
What responsibility does it have?
What should NOT be inside it?
```

If a class cannot answer those questions clearly, reconsider the abstraction.

---

## Coding Challenge Context

This project is also used as practice for a coding test with roughly:

```text
10:00–12:00  Whiteboard / design
13:00–16:00  Coding
16:00–17:00  Code discussion
```

AI and Internet access are allowed.

Therefore priorities are:

1. Correctness
2. Clear architecture
3. Business modeling
4. Working implementation
5. Tests
6. Ability to explain decisions
7. Avoid overengineering

Do not spend excessive time recreating infrastructure that can be solved with small, trusted libraries.

---

## Current Status

Backend:

- Docker environment ready
- Composer configured
- MySQL connection working
- Product Domain started
- Money Value Object implemented/planned
- Product repository abstraction implemented/planned
- PDO repository implemented/planned
- Application use cases implemented/planned
- Symfony HttpFoundation selected
- Symfony Routing selected
- Product API under construction

Frontend:

- Vite + React initialized
- Tailwind CSS v4 installed
- shadcn/ui installed
- Product UI still to be implemented
- API integration still to be implemented

---

## Immediate Frontend Goal

Build a small Product Management interface containing:

```text
ProductPage
├── page title
├── Add Product button
├── Product table/list
├── empty state
├── loading state
├── error state
└── create/edit Product dialog
```

Required operations:

```text
List products
Create product
Edit product
Delete product
```

Frontend should consume:

```text
GET    /api/products
POST   /api/products
PUT    /api/products/{id}
DELETE /api/products/{id}
```

Keep the UI implementation small, maintainable, and appropriate for a timed coding challenge.
