# Frontend Guidelines

## Purpose

This file defines how the frontend should be implemented for the Product Management application.

The frontend should stay:

- Simple
- Fast to understand
- Easy to change
- Easy to explain in a coding challenge
- Consistent with the existing PHP API
- Free from unnecessary state-management or architectural complexity

Do not redesign the backend from the frontend.

The frontend should consume the existing API contract and remain focused on UI concerns.

---

## Tech Stack

Use:

```text
React
Vite
TypeScript
Tailwind CSS v4
shadcn/ui
```

Avoid adding extra libraries unless there is a clear need.

Do not add by default:

```text
Redux
Zustand
MobX
React Query
Axios
Formik
React Hook Form
Zod
Next.js
```

If a requirement genuinely needs one of them, explain why before adding it.

For the current scope, prefer:

```text
useState
useEffect
fetch
small reusable components
plain TypeScript types
```

---

## Frontend Location

The frontend lives in:

```text
frontend/
```

Preferred structure:

```text
frontend/src/
├── api/
│   └── products.ts
│
├── components/
│   ├── products/
│   │   ├── ProductForm.tsx
│   │   ├── ProductList.tsx
│   │   ├── ProductTable.tsx
│   │   └── ProductDialog.tsx
│   │
│   └── ui/
│       └── shadcn generated components
│
├── pages/
│   └── ProductsPage.tsx
│
├── types/
│   └── product.ts
│
├── App.tsx
├── main.tsx
└── index.css
```

Keep generic UI components separate from product-specific components.

---

## Responsibility Boundaries

### `components/ui`

Use this only for generic shadcn components.

Examples:

```text
Button
Input
Label
Dialog
Table
Card
AlertDialog
```

Do not add application-specific business behavior inside these files.

### `components/products`

Use this for product-specific UI.

Examples:

```text
ProductForm
ProductTable
ProductDialog
```

These components can know about Product-related UI requirements.

### `api`

All backend HTTP calls should live here.

Do not scatter `fetch()` calls across many components.

### `types`

Keep shared frontend types here.

---

## Product Type

Create:

```text
frontend/src/types/product.ts
```

Use:

```ts
export type Product = {
  id: number;
  name: string;
  price: number;
  currency: string;
};
```

For create/update form data:

```ts
export type ProductInput = {
  name: string;
  price: number;
  currency: string;
};
```

Do not duplicate this shape in multiple components.

---

## API Contract

The backend exposes:

```text
GET    /api/products
GET    /api/products/{id}
POST   /api/products
PUT    /api/products/{id}
DELETE /api/products/{id}
```

The frontend should call the API using relative URLs.

Use:

```ts
fetch("/api/products");
```

Do not hardcode:

```text
http://localhost:8080/api/products
```

Nginx is responsible for routing `/api/*` requests to the PHP backend.

---

## API Layer

Create:

```text
frontend/src/api/products.ts
```

Recommended interface:

```ts
import type { Product, ProductInput } from "@/types/product";

const endpoint = "/api/products";

async function handleResponse<T>(response: Response): Promise<T> {
  if (!response.ok) {
    let message = "Something went wrong";

    try {
      const body = await response.json();

      if (body?.error) {
        message = body.error;
      }
    } catch {
      // Keep default message.
    }

    throw new Error(message);
  }

  if (response.status === 204) {
    return undefined as T;
  }

  return response.json();
}

export async function getProducts(): Promise<Product[]> {
  const response = await fetch(endpoint);

  return handleResponse<Product[]>(response);
}

export async function getProduct(id: number): Promise<Product> {
  const response = await fetch(`${endpoint}/${id}`);

  return handleResponse<Product>(response);
}

export async function createProduct(input: ProductInput): Promise<Product> {
  const response = await fetch(endpoint, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify(input),
  });

  return handleResponse<Product>(response);
}

export async function updateProduct(
  id: number,
  input: ProductInput,
): Promise<Product> {
  const response = await fetch(`${endpoint}/${id}`, {
    method: "PUT",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify(input),
  });

  return handleResponse<Product>(response);
}

export async function deleteProduct(id: number): Promise<void> {
  const response = await fetch(`${endpoint}/${id}`, {
    method: "DELETE",
  });

  await handleResponse<void>(response);
}
```

Do not use Axios unless a real requirement appears.

---

## Main Page

Create:

```text
frontend/src/pages/ProductsPage.tsx
```

The page should own the main page-level state.

Recommended responsibilities:

```text
load products
track loading
track error
open create dialog
open edit dialog
delete product
refresh/update local product list
```

Avoid placing every behavior inside `App.tsx`.

`App.tsx` should stay small.

Example:

```tsx
import { ProductsPage } from "@/pages/ProductsPage";

export default function App() {
  return <ProductsPage />;
}
```

---

## Products Page UI

The main page should contain:

```text
ProductsPage
├── header
│   ├── title
│   └── Add Product button
│
├── loading state
├── error state
├── empty state
│
└── product table
    ├── name
    ├── price
    ├── currency
    └── actions
        ├── Edit
        └── Delete
```

Keep the layout minimal and professional.

---

## Suggested Layout

Use a centered application container.

Example structure:

```tsx
<main className="min-h-screen bg-muted/30">
  <div className="mx-auto max-w-6xl p-6">...</div>
</main>
```

Preferred visual direction:

- Neutral background
- Clear spacing
- Simple typography
- No excessive decoration
- Responsive by default
- shadcn defaults where possible

Do not spend excessive coding-challenge time on visual polish.

---

## Product Table

Prefer shadcn `Table`.

Columns:

```text
Name
Price
Currency
Actions
```

Example display:

```text
Keyboard | €99.90 | EUR | Edit Delete
```

The backend sends:

```text
price = 9990
```

which means:

```text
99.90
```

Format it only for display.

Example helper:

```ts
export function formatMoney(amount: number, currency: string): string {
  return new Intl.NumberFormat("en", {
    style: "currency",
    currency,
  }).format(amount / 100);
}
```

Do not convert the stored API value into a float for persistence.

The API still receives integer minor units.

---

## Product Form

Use one reusable form for create and edit.

Suggested props:

```ts
type ProductFormProps = {
  initialValues?: ProductInput;
  submitLabel?: string;
  onSubmit: (values: ProductInput) => Promise<void>;
};
```

Fields:

```text
name
price
currency
```

For the user-facing price field, it is acceptable to show decimal input such as:

```text
99.90
```

Before sending to the backend, convert it to cents.

Example:

```ts
const priceInCents = Math.round(Number(price) * 100);
```

Be aware that this simple conversion is acceptable for this small coding-challenge frontend.

The backend/domain remains responsible for protecting money rules.

---

## Validation

Keep frontend validation simple.

Validate basic UX concerns:

```text
name required
price required
price >= 0
currency required
```

Do not treat frontend validation as the source of truth.

The backend Domain remains authoritative.

If the backend returns:

```json
{
  "error": "Product name cannot be empty."
}
```

show that error to the user.

---

## Create Product Flow

Expected flow:

```text
User clicks Add Product
        |
        v
Dialog opens
        |
        v
User fills form
        |
        v
ProductForm
        |
        v
createProduct()
        |
        | POST /api/products
        v
PHP Backend
        |
        v
Product returned
        |
        v
Update local product list
        |
        v
Close dialog
```

Do not reload the whole page.

---

## Edit Product Flow

Expected flow:

```text
User clicks Edit
      |
      v
Open ProductDialog
      |
      v
Prefill current product values
      |
      v
Submit
      |
      v
updateProduct(id, input)
      |
      v
Replace updated product in local state
```

Use the same form component for create and edit where practical.

---

## Delete Product Flow

Use a confirmation dialog before deletion.

Recommended shadcn component:

```text
AlertDialog
```

Flow:

```text
Delete click
   |
   v
Confirmation
   |
   v
deleteProduct(id)
   |
   v
Remove product from local state
```

Do not delete immediately without confirmation.

---

## Loading State

When the initial product list is loading, show a simple state.

Example:

```tsx
<p className="text-sm text-muted-foreground">Loading products...</p>
```

A skeleton is optional.

Do not spend excessive time building custom loaders.

---

## Empty State

If the API returns:

```json
[]
```

show a useful empty state.

Example content:

```text
No products yet.

Create your first product.
```

Include an Add Product action if appropriate.

---

## Error State

If loading products fails, show the error clearly.

Example:

```tsx
<div role="alert">{error}</div>
```

Do not silently swallow API errors.

---

## Local State Strategy

For this project, keep state local.

Recommended page state:

```ts
const [products, setProducts] = useState<Product[]>([]);
const [loading, setLoading] = useState(true);
const [error, setError] = useState<string | null>(null);
```

Additional UI state may include:

```ts
selectedProduct;
isDialogOpen;
isSubmitting;
```

Do not introduce global state for the current scope.

---

## Initial Data Fetch

Use `useEffect` for the initial list.

Example:

```ts
useEffect(() => {
  void loadProducts();
}, []);
```

Recommended function:

```ts
async function loadProducts() {
  try {
    setLoading(true);
    setError(null);

    const data = await getProducts();

    setProducts(data);
  } catch (error) {
    setError(
      error instanceof Error ? error.message : "Could not load products",
    );
  } finally {
    setLoading(false);
  }
}
```

---

## State Updates After CRUD

After create:

```ts
setProducts((current) => [createdProduct, ...current]);
```

After update:

```ts
setProducts((current) =>
  current.map((product) =>
    product.id === updatedProduct.id ? updatedProduct : product,
  ),
);
```

After delete:

```ts
setProducts((current) => current.filter((product) => product.id !== id));
```

Prefer these local updates over refetching everything unless there is a reason to refetch.

---

## shadcn Components

Use only components needed for the page.

Recommended:

```text
Button
Input
Label
Table
Dialog
AlertDialog
Card
```

Optional:

```text
Badge
Skeleton
```

Do not install a large component set unnecessarily.

---

## Styling Rules

Prefer Tailwind utility classes and shadcn defaults.

Do not add a custom CSS framework.

Avoid large custom CSS files.

Use spacing consistently.

Examples:

```text
gap-4
space-y-4
p-6
max-w-6xl
text-sm
text-muted-foreground
```

Prefer semantic component composition over large groups of arbitrary styling.

---

## Accessibility

Keep basic accessibility in place.

Use:

- Proper labels for inputs
- Buttons for actions
- Dialog titles
- `role="alert"` for errors where appropriate
- Keyboard-friendly shadcn components

Do not replace buttons with clickable `div` elements.

---

## Currency Input

For the current scope, currency may be a simple input or small select.

Suggested values:

```text
EUR
USD
GBP
```

Keep the API contract as a 3-letter currency code.

Do not implement exchange-rate conversion.

---

## Backend Error Handling

Backend errors should be displayed, not transformed into unrelated frontend messages.

Example backend response:

```json
{
  "error": "Product not found."
}
```

Preferred frontend behavior:

```text
Product not found.
```

The frontend may provide a fallback only if the API response has no useful error.

---

## Do Not Duplicate Domain Rules

Frontend can improve UX, but it must not become the only place business rules live.

Example:

```text
Frontend:
price >= 0 for immediate feedback

Backend Domain:
price >= 0 as authoritative rule
```

If frontend validation is bypassed, the backend must still reject invalid state.

---

## React Component Guidelines

Prefer small components with a clear responsibility.

Good:

```text
ProductsPage
ProductTable
ProductForm
ProductDialog
```

Avoid:

```text
ProductsPage with 500+ lines
```

But also avoid splitting every few lines into a separate component.

Use extraction when it improves readability or reuse.

---

## TypeScript Guidelines

Do not use `any` unless absolutely necessary.

Prefer:

```ts
Product;
ProductInput;
string | null;
```

Use inferred types where obvious.

Avoid unnecessary generic abstractions.

---

## Naming

Use business language.

Good:

```text
Product
ProductForm
createProduct
updateProduct
selectedProduct
```

Avoid generic names like:

```text
Data
ItemThing
HandlerManager
HelperUtils
```

Names should reflect the Product domain.

---

## Code Quality

Prefer:

- Explicit functions
- Clear naming
- Early returns
- Small components
- Small API functions
- Immutable state updates
- Type safety

Avoid:

- Deep nesting
- Large anonymous callbacks
- Duplicated request logic
- Business rules scattered through components
- Premature hooks abstraction

---

## Testing

Frontend testing is secondary to backend/domain testing for the coding challenge, but basic tests may be added if time permits.

If testing is added, prefer Vitest.

Useful targets:

```text
formatMoney()
ProductForm validation
ProductsPage behavior
```

Do not spend large amounts of challenge time creating a broad frontend test suite unless required.

---

## Backend Is Source of Truth

Do not modify backend architecture from frontend tasks unless explicitly required.

Do not move business rules from PHP into React.

Do not change API field names without updating the agreed API contract.

Current Product API shape:

```json
{
  "id": 1,
  "name": "Keyboard",
  "price": 9990,
  "currency": "EUR"
}
```

Keep frontend aligned with this structure.

---

## Immediate Implementation Order

Implement frontend in this order:

```text
1. Product Type
2. API layer
3. ProductsPage
4. Initial product list
5. ProductTable
6. Empty/loading/error states
7. Create Product dialog
8. Edit Product
9. Delete confirmation
10. Small cleanup/refactor
```

Do not start with complex UI abstractions.

Get the basic end-to-end flow working first.

---

## Definition of Done

The frontend is complete for the current scope when:

- Products load from the PHP API
- Products display in a table
- Empty state works
- Loading state works
- API errors are visible
- User can create a product
- User can edit a product
- User can delete a product with confirmation
- UI is responsive
- Tailwind and shadcn are used consistently
- No unnecessary state-management library is introduced
- No backend business logic is duplicated unnecessarily in React

---

## Claude Code Guidance

When working on the frontend:

1. Read `context/project-overview.md` first.
2. Read this file before making frontend changes.
3. Inspect the existing frontend before adding files.
4. Preserve the current backend API contract.
5. Prefer the simplest implementation that satisfies the requirement.
6. Do not introduce dependencies without a clear reason.
7. Do not redesign the DDD backend from frontend tasks.
8. Keep all product-related HTTP calls in the API layer.
9. Use shadcn components instead of rebuilding generic UI controls.
10. Prioritize a working CRUD flow over visual polish.
11. Keep code understandable enough to explain during a senior coding interview.
12. If an architectural choice is unclear, favor explicit and simple code over abstraction.

---

## Current Frontend Goal

Build this experience:

```text
Products
------------------------------------------------
                                [ Add Product ]

Name              Price           Actions
------------------------------------------------
Keyboard          €99.90          Edit   Delete
Monitor           €299.00         Edit   Delete
------------------------------------------------
```

Create and edit should happen in a dialog.

Delete should require confirmation.

The final frontend flow should remain easy to explain:

```text
React UI
   |
   v
products.ts
   |
   v
PHP REST API
   |
   v
Application Use Case
   |
   v
Domain
   |
   v
MySQL
```

The frontend is an adapter to the application, not the owner of the business rules.
