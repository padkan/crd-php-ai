import type { Product, ProductInput } from "@/types/product";

import { handleResponse } from "@/api/http";

const endpoint = "/api/products";

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
