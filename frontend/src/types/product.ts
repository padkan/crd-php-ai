export type Product = {
  id: number;
  name: string;
  description: string;
  price: number;
  currency: string;
};

export type ProductInput = {
  name: string;
  description: string;
  price: number;
  currency: string;
};
