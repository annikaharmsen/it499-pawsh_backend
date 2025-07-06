<?php


namespace App\services\admin;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class UserAdminService
{

    public function usersCount(): int
    {
        return User::Count();
    }
    public function employeeCount() :int
    {
        return User::where('role', 'Employee')->count();
    }
    public function countNewUsersThisMonth(): int
    {
        return User::whereDate('created_at', '>=', now()->startOfMonth())->count();
    }

    public function usersCountByState(): array
    {
        return DB::select("select COUNT(*), state from addresses group by state;");
    }

    public function topTepBuyers(): array
    {
        // TODO: Due to time constraints, using raw SQL (CTE) directly in the controller to fetch dashboard stats.
        // For long-term maintainability, consider moving this to a dedicated database view, service class, or repository.
        return DB::select("
            WITH
                customer_orders AS (
                    SELECT
                        u.id AS userid,
                        CONCAT (u.firstname, ' ', u.lastname) AS customer_name,
                        o.id AS orderid,
                        p.amount,
                        oi.productid,
                        oi.quantity
                    FROM
                        users u
                        JOIN orders o ON o.userid = u.id
                        JOIN payments p ON p.orderid = o.id
                        JOIN orderitems oi ON oi.orderid = o.id
                    WHERE
                        u.role = 'Customer'
                ),
                order_summary AS (
                    SELECT
                        userid,
                        customer_name,
                        COUNT(DISTINCT orderid) AS num_orders,
                        SUM(amount) AS lifetime_spending
                    FROM customer_orders
                    GROUP BY
                        userid,
                        customer_name
                ),
                popular_items AS (
                    SELECT
                        userid,
                        productid,
                        SUM(quantity) AS total_quantity,
                        ROW_NUMBER() OVER (
                            PARTITION BY
                                userid
                            ORDER BY SUM(quantity) DESC
                        ) AS rn
                    FROM customer_orders
                    GROUP BY
                        userid,
                        productid
                ),
                top_items AS (
                    SELECT pi.userid, pr.name AS most_popular_item
                    FROM popular_items pi
                        JOIN products pr ON pr.id = pi.productid
                    WHERE
                        pi.rn = 1
                )

            SELECT os.customer_name, os.lifetime_spending, os.num_orders, ti.most_popular_item
            FROM
                order_summary os
                LEFT JOIN top_items ti ON os.userid = ti.userid
            ORDER BY os.lifetime_spending DESC
            LIMIT 10;
            ");
    }
}