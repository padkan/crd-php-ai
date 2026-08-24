import { useId, useState, type FormEvent } from "react";

import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import type { ProductInput } from "@/types/product";

const DEFAULT_CURRENCY = "USD";

type ProductFormProps = {
  initialValues?: ProductInput;
  submitLabel?: string;
  onCancel: () => void;
  onSubmit: (values: ProductInput) => void;
};

export function ProductForm({
  initialValues,
  submitLabel = "Save product",
  onCancel,
  onSubmit,
}: ProductFormProps) {
  const nameId = useId();
  const descriptionId = useId();
  const priceId = useId();

  const [name, setName] = useState(initialValues?.name ?? "");
  const [description, setDescription] = useState(
    initialValues?.description ?? "",
  );
  const [price, setPrice] = useState(
    initialValues ? (initialValues.price / 100).toFixed(2) : "",
  );
  const [error, setError] = useState<string | null>(null);

  function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();

    if (!name.trim()) {
      setError("Name is required.");
      return;
    }

    const priceInCents = Math.round(Number(price) * 100);

    if (!Number.isFinite(priceInCents) || priceInCents < 0) {
      setError("Price must be a valid, non-negative amount.");
      return;
    }

    setError(null);

    onSubmit({
      name: name.trim(),
      description: description.trim(),
      price: priceInCents,
      currency: initialValues?.currency ?? DEFAULT_CURRENCY,
    });
  }

  return (
    <form onSubmit={handleSubmit} className="flex flex-col gap-4">
      <div className="flex flex-col gap-1.5">
        <Label htmlFor={nameId}>Name</Label>
        <Input
          id={nameId}
          value={name}
          onChange={(event) => setName(event.target.value)}
          placeholder="e.g. Workspace Pro"
        />
      </div>

      <div className="flex flex-col gap-1.5">
        <Label htmlFor={descriptionId}>
          Description <span className="text-muted-foreground">(optional)</span>
        </Label>
        <Textarea
          id={descriptionId}
          value={description}
          onChange={(event) => setDescription(event.target.value)}
          placeholder="A short description of this product"
        />
      </div>

      <div className="flex flex-col gap-1.5">
        <Label htmlFor={priceId}>Price</Label>
        <div className="relative">
          <span className="pointer-events-none absolute top-1/2 left-2.5 -translate-y-1/2 text-sm text-muted-foreground">
            $
          </span>
          <Input
            id={priceId}
            type="number"
            min="0"
            step="0.01"
            inputMode="decimal"
            value={price}
            onChange={(event) => setPrice(event.target.value)}
            placeholder="0.00"
            className="pl-6"
          />
        </div>
      </div>

      {error && (
        <p role="alert" className="text-sm text-destructive">
          {error}
        </p>
      )}

      <div className="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
        <Button type="button" variant="outline" onClick={onCancel}>
          Cancel
        </Button>
        <Button type="submit">{submitLabel}</Button>
      </div>
    </form>
  );
}
