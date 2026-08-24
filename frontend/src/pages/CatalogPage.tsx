import { useState } from "react";
import { Plus } from "lucide-react";

import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { DeleteProductDialog } from "@/components/products/DeleteProductDialog";
import { ProductDialog } from "@/components/products/ProductDialog";
import { ProductTable } from "@/components/products/ProductTable";
import { mockProducts } from "@/lib/mock-data";
import type { Product, ProductInput } from "@/types/product";

export function CatalogPage() {
  const [products, setProducts] = useState<Product[]>(mockProducts);
  const [isDialogOpen, setIsDialogOpen] = useState(false);
  const [editingProduct, setEditingProduct] = useState<Product | null>(null);
  const [productPendingDelete, setProductPendingDelete] = useState<Product | null>(
    null,
  );

  function openCreateDialog() {
    setEditingProduct(null);
    setIsDialogOpen(true);
  }

  function openEditDialog(product: Product) {
    setEditingProduct(product);
    setIsDialogOpen(true);
  }

  function handleSubmit(values: ProductInput) {
    if (editingProduct) {
      setProducts((current) =>
        current.map((product) =>
          product.id === editingProduct.id
            ? { ...product, ...values }
            : product,
        ),
      );
    } else {
      const nextId = Math.max(0, ...products.map((product) => product.id)) + 1;

      setProducts((current) => [{ id: nextId, ...values }, ...current]);
    }

    setIsDialogOpen(false);
  }

  function confirmDelete(product: Product) {
    setProducts((current) => current.filter((item) => item.id !== product.id));
    setProductPendingDelete(null);
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
            {products.length === 0 ? (
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
                onDelete={setProductPendingDelete}
              />
            )}
          </CardContent>
        </Card>
      </div>

      <ProductDialog
        open={isDialogOpen}
        onOpenChange={setIsDialogOpen}
        product={editingProduct}
        onSubmit={handleSubmit}
      />

      <DeleteProductDialog
        product={productPendingDelete}
        onOpenChange={(open) => {
          if (!open) setProductPendingDelete(null);
        }}
        onConfirm={confirmDelete}
      />
    </main>
  );
}
