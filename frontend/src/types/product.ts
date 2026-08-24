export type ProductStatus = "active" | "draft";

export type Product = {
  id: number;
  name: string;
  description: string;
  price: number;
  currency: string;
  status: ProductStatus;
};
