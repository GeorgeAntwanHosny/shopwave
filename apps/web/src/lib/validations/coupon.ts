import { z } from "zod";

export const couponSchema = z
  .object({
    code: z.string().min(3, "Code must be at least 3 characters").max(50),
    type: z.enum(["percentage", "fixed"]),
    value: z.coerce.number().positive("Value must be greater than 0"),
    min_order_amount: z.coerce.number().min(0).optional().or(z.literal("")),
    max_uses: z.coerce.number().int().positive().optional().or(z.literal("")),
    expires_at: z.string().optional().or(z.literal("")),
    is_active: z.boolean().default(true),
  })
  .refine((data) => data.type !== "percentage" || data.value <= 100, {
    message: "Percentage discounts cannot exceed 100",
    path: ["value"],
  });

export type CouponFormValues = z.infer<typeof couponSchema>;