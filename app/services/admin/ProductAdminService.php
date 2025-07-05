<?php


namespace App\services\admin;

use Illuminate\Support\Facades\DB;
use App\Models\Product;

class ProductAdminService
{
    protected int $lowStockThreshold = 10;

    public function productsCount(): int {
        return Product::count();
    }
    public function lowStockProductsCount(): int {
        return Product::where('stock', '<', $this->lowStockThreshold)->count();
    }
    public function lowStockProduct(): array {
        return Product::where('stock', '<', $this->lowStockThreshold)
            ->select('id', 'name', 'description', 'stock', 'category', 'imageurl', 'updated_at')
            ->get()
            ->toArray();
    }

    public function topTenSoldProducts(): array
    {
        // TODO: Due to time constraints, using raw SQL (CTE) directly in the controller to fetch dashboard stats.
        // For long-term maintainability, consider moving this to a dedicated database view, service class, or repository.
        return DB::select("
            WITH product_sales AS (
                    SELECT
                        oi.productid,
                        SUM(oi.quantity) AS total_quantity_sold
                    FROM orderitems oi
                             JOIN orders o ON oi.orderid = o.id
                             JOIN users u ON o.userid = u.id
                    WHERE u.role = 'Customer'
                    GROUP BY oi.productid
                ),
                     ranked_products AS (
                    SELECT
                             ps.productid,
                             p.name AS product_name,
                             ps.total_quantity_sold,
                             DENSE_RANK() OVER (ORDER BY ps.total_quantity_sold DESC) AS sales_rank
                         FROM product_sales ps
                                  JOIN products p ON p.id = ps.productid
                     )
                SELECT
                    productid,
                    product_name,
                    total_quantity_sold,
                    sales_rank
                FROM ranked_products
                WHERE sales_rank <= 10;
        ");
    }

}