import Link from "next/link";
import { AlertTriangle } from "lucide-react";

interface LowStockProduct {
  id: number;
  name: string;
  slug: string;
  stock_quantity: number;
}

export function LowStockList({ products }: { products: LowStockProduct[] }) {
  if (products.length === 0) {
    return <p className="text-sm text-muted-foreground">No low-stock products right now.</p>;
  }

  return (
    <ul className="space-y-2">
      {products.map((product) => (
        <li key={product.id}>
          <Link
            href={`/vendor/products/${product.id}/edit`}
            className="flex items-center justify-between rounded-md border border-border bg-card p-3 text-sm hover:shadow-sm"
          >
            <span className="flex items-center gap-2 text-card-foreground">
              <AlertTriangle className="h-4 w-4 text-destructive" />
              {product.name}
            </span>
            <span className="text-destructive">{product.stock_quantity} left</span>
          </Link>
        </li>
      ))}
    </ul>
  );
}