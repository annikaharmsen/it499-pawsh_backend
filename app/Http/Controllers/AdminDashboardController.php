<?php
namespace App\Http\Controllers;
use App\services\admin\PaymentAdminService;
use App\services\admin\ProductAdminService;
use App\services\admin\UserAdminService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use \App\services\admin\OrderAdminService;

class AdminDashboardController extends Controller
{
    protected OrderAdminService $orderAdminService;
    protected ProductAdminService $productAdminService;
    protected PaymentAdminService $paymentAdminService;
    protected UserAdminService $userAdminService;

    public function __construct(
        OrderAdminService $orderAdminService,
        ProductAdminService $productAdminService,
        PaymentAdminService $paymentAdminService,
        UserAdminService $userAdminService
    )
    {
        $this->orderAdminService = $orderAdminService;
        $this->productAdminService = $productAdminService;
        $this->paymentAdminService = $paymentAdminService;
        $this->userAdminService = $userAdminService;
    }

    public function overview()
    {
        $ordersService = $this->orderAdminService;
        $paymentService = $this->paymentAdminService;
        $userService = $this->userAdminService;
        $productService = $this->productAdminService;


        return ResponseService::sendResponse('Stats retrieved successfully.', [
            'total_orders' => $ordersService->ordersCount(),
            'today_orders' => $this->orderAdminService->todayOrders(),
            'pending_orders' => $ordersService->pendingOrdersCount(),
            'monthly_revenue' => $paymentService->monthlyRevenue(),
            'last_month_revenue' => $paymentService->previousMonthlyRevenue(),
            'total_users' => $userService->usersCount(),
            'new_users_this_month' => $userService->countNewUsersThisMonth(),
            'total_employees' => $userService->employeeCount(),
            'total_products' => $productService->productsCount(),
            'low_stock_products' => $productService->lowStockProductsCount(),
            ]);
    }

    public function ordersReport(): JsonResponse
    {
        return ResponseService::sendResponse('Order\'s Stats retrieved successfully.', [
            'orders_count_by_status' => $this->orderAdminService->ordersCountByStatus(),
            'awaiting_shipment' => $this->orderAdminService->awaitingShipment(),
        ]);
    }

    public function productsReport(Request $request): JsonResponse
    {
        return ResponseService::sendResponse('Stats retrieved successfully.', [
            'top_ten_sold_products' => $this->productAdminService->topTenSoldProducts(),
            'low_stock_products' => $this->productAdminService->lowStockProduct()
        ]);
    }

    public function usersReport(): JsonResponse
    {
        return ResponseService::sendResponse('Stats retrieved successfully.', [
            'top_ten_buyers' => $this->userAdminService->topTepBuyers(),
            'users_count_by_state' => $this->userAdminService->usersCountByState()
        ]);
    }

}
