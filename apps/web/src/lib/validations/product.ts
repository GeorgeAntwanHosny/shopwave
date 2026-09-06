import { z } from "zod";

export const productSchema = z.object({
  name: z.string().min(2, "Name must be at least 2 characters").max(255),
  description: z.string().max(5000).optional().or(z.literal("")),
  price: z.coerce.number().positive("Price must be greater than 0"),
  stock_quantity: z.coerce.number().int().min(0, "Stock can't be negative"),
  category_id: z.string().optional(),
  is_active: z.boolean().default(true),
});

export type ProductFormValues = z.infer<typeof productSchema>;