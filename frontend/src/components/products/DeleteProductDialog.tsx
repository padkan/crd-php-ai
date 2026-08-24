import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from "@/components/ui/alert-dialog";
import type { Product } from "@/types/product";

type DeleteProductDialogProps = {
  product: Product | null;
  onOpenChange: (open: boolean) => void;
  onConfirm: (product: Product) => void;
};

export function DeleteProductDialog({
  product,
  onOpenChange,
  onConfirm,
}: DeleteProductDialogProps) {
  return (
    <AlertDialog open={product !== null} onOpenChange={onOpenChange}>
      <AlertDialogContent>
        <AlertDialogHeader>
          <AlertDialogTitle>Delete product</AlertDialogTitle>
          <AlertDialogDescription>
            Are you sure you want to delete{" "}
            {product && <strong>{product.name}</strong>}? This action cannot
            be undone.
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel>Cancel</AlertDialogCancel>
          <AlertDialogAction
            variant="destructive"
            onClick={() => product && onConfirm(product)}
          >
            Delete
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>
  );
}
