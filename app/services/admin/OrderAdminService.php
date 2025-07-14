<?php


namespace App\services\admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Models\Order;

class OrderAdminService
{

    public function ordersCount(): int
    {
        return Order::count();
    }
    public function processingOrdersCount(): int
    {
        return Order::where('status', 'processing')->count();
    }

    public function ordersCountByStatus(): array
    {
        return Order::select('status', DB::raw('COUNT(*) as order_count'))
            ->groupBy('status')
            ->orderBy('status')
            ->get()
            ->toArray();
    }


    public function todayOrders(): int
    {
        return Order::whereDate('orderdate', date('Y-m-d'))->count();
    }

    public function awaitingShipment(): array
    {
        return Order::where('status', 'processing')
            ->select('id', 'orderdate', 'status')
            ->get()
            ->toarray();
    }

}
