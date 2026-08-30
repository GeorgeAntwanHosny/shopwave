import { z } from "zod";

export const becomeVendorSchema = z.object({
  shop_name: z.string().min(2, "Shop name must be at least 2 characters").max(255),
});

export type BecomeVendorFormValues = z.infer<typeof becomeVendorSchema>;