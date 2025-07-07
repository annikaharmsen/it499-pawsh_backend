<?php


namespace App\services\admin;
use App\Models\Payment;

class PaymentAdminService
{

    public function monthlyRevenue(): float
    {
        return Payment::whereBetween('paymentdate', [
            now()->startOfMonth(),
            now()
        ])->where('status', 'paid')->sum('amount');
    }
    public function previousMonthlyRevenue(): float
    {
        return Payment::whereBetween('paymentdate', [
            now()->copy()->subMonthNoOverflow()->startOfMonth(),
            now()->copy()->subMonthNoOverflow()->endOfMonth()
        ])->where('status', 'paid')->sum('amount');
    }

}