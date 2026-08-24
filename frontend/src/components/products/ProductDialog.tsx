import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
} from "@/components/ui/dialog";
import { ProductForm } from "@/components/products/ProductForm";
import type { Product, ProductInput } from "@/types/product";

type ProductDialogProps = {
  open: boolean;
  onOpenChange: (open: boolean) => void;
  product: Product | null;
  onSubmit: (values: ProductInput) => void;
};

export function ProductDialog({
  open,
  onOpenChange,
  product,
  onSubmit,
}: ProductDialogProps) {
  const isEditing = product !== null;

  return (
    <Dialog open={open} onOpenChange={onOpenChange}>
      <DialogContent>
        <DialogHeader>
          <DialogTitle>{isEditing ? "Edit product" : "Create product"}</DialogTitle>
          <DialogDescription>
            {isEditing
              ? "Update the details for this product."
              : "Add a new product to your catalog."}
          </DialogDescription>
        </DialogHeader>

        <ProductForm
          initialValues={product ?? undefined}
          submitLabel="Save product"
          onCancel={() => onOpenChange(false)}
          onSubmit={onSubmit}
        />
      </DialogContent>
    </Dialog>
  );
}
