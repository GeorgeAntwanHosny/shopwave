"use client";

import { use, useState } from "react";
import Link from "next/link";
import { ArrowLeft } from "lucide-react";
import { Skeleton } from "@/components/ui/skeleton";
import { Badge } from "@/components/ui/badge";
import { useAdminVendor } from "@/features/admin/hooks/useAdminVendors";
import { useAdminProducts } from "@/features/admin/hooks/useAdminProducts";
import { useAdminOrders } from "@/features/admin/hooks/useAdminOrders";

export default function AdminVendorDetailPage({ params }: { params: Promise<{ id: string }> }) {
  const { id } = use(params);
  const [tab, setTab] = useState<"products" | "orders">("products");
  const { data: vendor, isLoading, isError } = useAdminVendor(id);
  const { data: productsData, isLoading: productsLoading } = useAdminProducts({ vendor_id: id });
  const { data: ordersData, isLoading: ordersLoading } = useAdminOrders({ vendor_id: id });

  if (isLoading) {
    return (
      <div className="mx-auto max-w-4xl space-y-4 p-4 sm:p-6 lg:p-8">
        <Skeleton className="h-8 w-48" />
        <Skeleton className="h-32 w-full" />
      </div>
    );
  }

  if (isError || !vendor) {
    return (
      <div className="mx-auto max-w-4xl p-4 text-center sm:p-6 lg:p-8">
        <p className="text-destructive">Could not load this vendor.</p>
      </div>
    );
  }

  return (
    <div className="mx-auto max-w-4xl space-y-6 p-4 sm:p-6 lg:p-8">
      <Link href="/admin/vendors" className="inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground">
        <ArrowLeft className="h-4 w-4" />
        Back to vendors
      </Link>

      <div className="rounded-lg border border-border bg-card p-6">
        <div className="flex flex-wrap items-start justify-between gap-3">
          <div>
            <h1 className="text-2xl font-semibold text-foreground">{vendor.shop_name}</h1>
            <p className="text-sm text-muted-foreground">{vendor.user.name} · {vendor.user.email}</p>
          </div>
          <div className="flex gap-2">
            <Badge variant={vendor.stripe_onboarding_complete ? "default" : "secondary"}>
              {vendor.stripe_onboarding_complete ? "Onboarded" : "Onboarding"}
            </Badge>
            <Badge variant={vendor.is_suspended ? "destructive" : "default"}>{vendor.is_suspended ? "Suspended" : "Active"}</Badge>
          </div>
        </div>
        <div className="mt-4 grid grid-cols-2 gap-4 text-sm sm:grid-cols-3">
          <div><p className="text-muted-foreground">Products</p><p className="font-medium text-foreground">{vendor.products_count}</p></div>
          <div><p className="text-muted-foreground">Orders</p><p className="font-medium text-foreground">{vendor.orders_count}</p></div>
          <div><p className="text-muted-foreground">Rating</p><p className="font-medium text-foreground">{vendor.average_rating} ({vendor.rating_count})</p></div>
        </div>
        {vendor.is_suspended && vendor.suspension_reason && (
          <p className="mt-3 text-sm text-destructive">Suspension reason: {vendor.suspension_reason}</p>
        )}
      </div>

      <div className="flex gap-1 border-b border-border">
        <button onClick={() => setTab("products")} className={`px-3 py-2 text-sm font-medium ${tab === "products" ? "border-b-2 border-primary text-foreground" : "text-muted-foreground"}`}>
          Products ({vendor.products_count})
        </button>
        <button onClick={() => setTab("orders")} className={`px-3 py-2 text-sm font-medium ${tab === "orders" ? "border-b-2 border-primary text-foreground" : "text-muted-foreground"}`}>
          Orders ({vendor.orders_count})
        </button>
      </div>

      {tab === "products" ? (
        productsLoading ? (
          <div className="space-y-3">{Array.from({ length: 2 }).map((_, i) => <Skeleton key={i} className="h-16 w-full" />)}</div>
        ) : (
          <div className="space-y-3">
            {productsData?.products.map((product) => (
              <Link key={product.id} href={`/admin/products/${product.id}`} className="block rounded-lg border border-border bg-card p-4 hover:shadow-sm">
                <div className="flex items-center justify-between">
                  <p className="font-medium text-card-foreground">{product.name}</p>
                  <div className="flex gap-2">
                    <Badge variant={product.is_active ? "default" : "secondary"}>{product.is_active ? "Active" : "Inactive"}</Badge>
                    {product.is_flagged && <Badge variant="destructive">Flagged</Badge>}
                  </div>
                </div>
                <p className="text-sm text-muted-foreground">${product.price} · {product.stock_quantity} in stock</p>
              </Link>
            ))}
            {productsData?.products.length === 0 && <p className="text-sm text-muted-foreground">No products yet.</p>}
          </div>
        )
      ) : ordersLoading ? (
        <div className="space-y-3">{Array.from({ length: 2 }).map((_, i) => <Skeleton key={i} className="h-16 w-full" />)}</div>
      ) : (
        <div className="space-y-3">
          {ordersData?.orders.map((order) => (
            <Link key={order.id} href={`/admin/orders/${order.id}`} className="block rounded-lg border border-border bg-card p-4 hover:shadow-sm">
              <div className="flex items-center justify-between">
                <p className="font-medium text-card-foreground">Order #{order.id} — {order.user.name}</p>
                <Badge variant={order.status === "paid" ? "default" : "secondary"}>{order.status}</Badge>
              </div>
              <p className="text-sm text-muted-foreground">${order.total} · {new Date(order.created_at).toLocaleDateString()}</p>
            </Link>
          ))}
          {ordersData?.orders.length === 0 && <p className="text-sm text-muted-foreground">No orders yet.</p>}
        </div>
      )}
    </div>
  );
}