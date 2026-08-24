import { createBrowserRouter, Navigate } from "react-router-dom";

import { CatalogPage } from "@/pages/CatalogPage";
import { NotFoundPage } from "@/pages/NotFoundPage";

export const router = createBrowserRouter([
  {
    path: "/",
    element: <Navigate to="/catalog" replace />,
  },
  {
    path: "/catalog",
    element: <CatalogPage />,
  },
  {
    path: "*",
    element: <NotFoundPage />,
  },
]);
