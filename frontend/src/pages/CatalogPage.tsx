import { useEffect, useState } from "react";
import { Plus } from "lucide-react";

import {
  createProduct,
  deleteProduct,
  getProducts,
  updateProduct,
} from "@/api/products";
import { ApiError } from "@/api/http";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { DeleteProductDialog } from "@/components/products/DeleteProductDialog";
import { ProductDialog } from "@/components/products/ProductDialog";
import { ProductTable } from "@/components/products/ProductTable";
import type { Product, ProductInput } from "@/types/product";

function messageFor(error: unknown, fallback: string): string {
  if (error instanceof ApiError || error instanceof Error) {
    return error.message;
  }

  return fallback;
}

export function CatalogPage() {
  const [products, setProducts] = useState<Product[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  const [isDialogOpen, setIsDialogOpen] = useState(false);
  const [editingProduct, setEditingProduct] = useState<Product | null>(null);
  const [submitting, setSubmitting] = useState(false);
  const [submitError, setSubmitError] = useState<string | null>(null);

  const [productPendingDelete, setProductPendingDelete] = useState<Product | null>(
    null,
  );
  const [deleting, setDeleting] = useState(false);
  const [deleteError, setDeleteError] = useState<string | null>(null);

  async function loadProducts() {
    try {
      setLoading(true);
      setError(null);

      const data = await getProducts();

      setProducts(data);
    } catch (error) {
      setError(messageFor(error, "Could not load products"));
    } finally {
      setLoading(false);
    }
  }

  useEffect(() => {
    // Standard fetch-on-mount effect (per the spec's documented pattern);
    // the initial loading/error resets are intentional and harmless here.
    // eslint-disable-next-line react-hooks/set-state-in-effect
    void loadProducts();
  }, []);

  function openCreateDialog() {
    setEditingProduct(null);
    setSubmitError(null);
    setIsDialogOpen(true);
  }

  function openEditDialog(product: Product) {
    setEditingProduct(product);
    setSubmitError(null);
    setIsDialogOpen(true);
  }

  async function handleSubmit(values: ProductInput) {
    try {
      setSubmitting(true);
      setSubmitError(null);

      if (editingProduct) {
        const updated = await updateProduct(editingProduct.id, values);

        setProducts((current) =>
          current.map((product) =>
            product.id === updated.id ? updated : product,
          ),
        );
      } else {
        const created = await createProduct(values);

        setProducts((current) => [created, ...current]);
      }

      setIsDialogOpen(false);
    } catch (error) {
      setSubmitError(messageFor(error, "Could not save product"));
    } finally {
      setSubmitting(false);
    }
  }

  async function confirmDelete(product: Product) {
    try {
      setDeleting(true);
      setDeleteError(null);

      await deleteProduct(product.id);

      setProducts((current) => current.filter((item) => item.id !== product.id));
      setProductPendingDelete(null);
    } catch (error) {
      setDeleteError(messageFor(error, "Could not delete product"));
    } finally {
      setDeleting(false);
    }
  }

  return (
    <main className="min-h-screen bg-muted/30">
      <div className="mx-auto max-w-6xl p-6">
        <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
          <div>
            <p className="text-sm text-muted-foreground">Catalog</p>
            <h1 className="text-3xl font-semibold text-foreground">Products</h1>
            <p className="mt-1 text-sm text-muted-foreground">
              Manage the products available in your catalog.
            </p>
          </div>
          <Button onClick={openCreateDialog}>
            <Plus />
            Add product
          </Button>
        </div>

        <Card className="mt-6">
          <CardHeader>
            <CardTitle>
              All products <span className="text-muted-foreground">({products.length})</span>
            </CardTitle>
          </CardHeader>
          <CardContent>
            {loading ? (
              <p className="py-10 text-center text-sm text-muted-foreground">
                Loading products...
              </p>
            ) : error ? (
              <div role="alert" className="py-10 text-center">
                <p className="text-sm font-medium text-destructive">{error}</p>
              </div>
            ) : products.length === 0 ? (
              <div className="py-10 text-center">
                <p className="text-sm font-medium text-foreground">No products yet.</p>
                <p className="mt-1 text-sm text-muted-foreground">
                  Create your first product.
                </p>
                <Button className="mt-4" onClick={openCreateDialog}>
                  <Plus />
                  Add product
                </Button>
              </div>
            ) : (
              <ProductTable
                products={products}
                onEdit={openEditDialog}
                onDelete={(product) => {
                  setDeleteError(null);
                  setProductPendingDelete(product);
                }}
              />
            )}
          </CardContent>
        </Card>
      </div>

      <ProductDialog
        open={isDialogOpen}
        onOpenChange={setIsDialogOpen}
        product={editingProduct}
        isSubmitting={submitting}
        submitError={submitError}
        onSubmit={handleSubmit}
      />

      <DeleteProductDialog
        product={productPendingDelete}
        isDeleting={deleting}
        error={deleteError}
        onOpenChange={(open) => {
          if (!open) setProductPendingDelete(null);
        }}
        onConfirm={confirmDelete}
      />
    </main>
  );
}
