<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $isSuperAdmin = (int) $user->status === 1;

        // Data untuk KPI Cards
        $kpiData = $this->getKPIData($user, $isSuperAdmin);

        // Data untuk Charts
        $chartData = $this->getChartData($user, $isSuperAdmin);

        // Data untuk Recent Activities
        $recentActivities = $this->getRecentActivities($user, $isSuperAdmin);

        return view('dashboard', compact(
            'user',
            'kpiData',
            'chartData',
            'recentActivities',
            'isSuperAdmin'
        ));
    }

    private function getKPIData($user, $isSuperAdmin)
    {
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        if ($isSuperAdmin) {
            // KPI untuk Super Admin
            return [
                'total_suppliers' => DB::table('users')->where('status', 0)->count(),
                'total_parts' => DB::table('parts')->count(),
                'total_orders' => DB::table('orders')
                    ->whereMonth('plan_delv_date', $currentMonth)
                    ->whereYear('plan_delv_date', $currentYear)
                    ->count(),
                'total_stock_value' => DB::table('orders')
                    ->whereMonth('plan_delv_date', $currentMonth)
                    ->whereYear('plan_delv_date', $currentYear)
                    ->sum('stock'),
                'on_time_delivery' => $this->calculateOnTimeDelivery(),
                'stock_accuracy' => $this->calculateStockAccuracy()
            ];
        } else {
            // KPI untuk Supplier
            return [
                'total_parts' => DB::table('orders')
                    ->where('supplier', $user->bpid)
                    ->whereMonth('plan_delv_date', $currentMonth)
                    ->whereYear('plan_delv_date', $currentYear)
                    ->distinct('part_no')
                    ->count('part_no'),
                'total_orders' => DB::table('orders')
                    ->where('supplier', $user->bpid)
                    ->whereMonth('plan_delv_date', $currentMonth)
                    ->whereYear('plan_delv_date', $currentYear)
                    ->count(),
                'total_stock' => DB::table('orders')
                    ->where('supplier', $user->bpid)
                    ->whereMonth('plan_delv_date', $currentMonth)
                    ->whereYear('plan_delv_date', $currentYear)
                    ->sum('stock'),
                'total_qty_po' => DB::table('orders')
                    ->where('supplier', $user->bpid)
                    ->whereMonth('plan_delv_date', $currentMonth)
                    ->whereYear('plan_delv_date', $currentYear)
                    ->sum('qty_po'),
                'standard_ok_rate' => $this->calculateStandardOKRate($user->bpid),
                'stock_adequacy' => $this->calculateStockAdequacy($user->bpid)
            ];
        }
    }

    private function getChartData($user, $isSuperAdmin)
    {
        $currentYear = Carbon::now()->year;

        if ($isSuperAdmin) {
            // Chart data untuk Super Admin
            $monthlyOrders = DB::table('orders')
                ->select(
                    DB::raw('MONTH(plan_delv_date) as month'),
                    DB::raw('COUNT(*) as total_orders'),
                    DB::raw('SUM(stock) as total_stock'),
                    DB::raw('SUM(qty_po) as total_qty_po')
                )
                ->whereYear('plan_delv_date', $currentYear)
                ->groupBy(DB::raw('MONTH(plan_delv_date)'))
                ->orderBy('month')
                ->get();

            $supplierPerformance = DB::table('orders')
                ->join('users', 'orders.supplier', '=', 'users.bpid')
                ->select(
                    'users.username as supplier_name',
                    DB::raw('COUNT(*) as total_orders'),
                    DB::raw('AVG(CASE WHEN orders.standard = "OK" THEN 1 ELSE 0 END) * 100 as ok_rate')
                )
                ->whereYear('orders.plan_delv_date', $currentYear)
                ->where('users.status', 0)
                ->groupBy('users.username', 'orders.supplier')
                ->orderByDesc('ok_rate')
                ->limit(10)
                ->get();

            $stockByCategory = DB::table('orders')
                ->select(
                    DB::raw('CASE
                        WHEN stock = 0 THEN "Zero Stock"
                        WHEN stock < 100 THEN "Low Stock (<100)"
                        WHEN stock BETWEEN 100 AND 500 THEN "Medium Stock (100-500)"
                        ELSE "High Stock (>500)"
                    END as stock_category'),
                    DB::raw('COUNT(*) as count')
                )
                ->whereYear('plan_delv_date', $currentYear)
                ->groupBy('stock_category')
                ->get();
        } else {
            // Chart data untuk Supplier
            $monthlyOrders = DB::table('orders')
                ->select(
                    DB::raw('MONTH(plan_delv_date) as month'),
                    DB::raw('COUNT(*) as total_orders'),
                    DB::raw('SUM(stock) as total_stock'),
                    DB::raw('SUM(qty_po) as total_qty_po')
                )
                ->where('supplier', $user->bpid)
                ->whereYear('plan_delv_date', $currentYear)
                ->groupBy(DB::raw('MONTH(plan_delv_date)'))
                ->orderBy('month')
                ->get();

            $partPerformance = DB::table('orders')
                ->select(
                    'part_no',
                    DB::raw('SUM(stock) as total_stock'),
                    DB::raw('SUM(qty_po) as total_qty_po'),
                    DB::raw('AVG(CASE WHEN standard = "OK" THEN 1 ELSE 0 END) * 100 as ok_rate')
                )
                ->where('supplier', $user->bpid)
                ->whereYear('plan_delv_date', $currentYear)
                ->groupBy('part_no')
                ->orderByDesc('total_qty_po')
                ->limit(10)
                ->get();

            $stockDistribution = DB::table('orders')
                ->select(
                    DB::raw('CASE
                        WHEN stock = 0 THEN "Zero Stock"
                        WHEN stock < qty_po * 0.1 THEN "Critical (<10% PO)"
                        WHEN stock < qty_po * 0.5 THEN "Low (10-50% PO)"
                        ELSE "Adequate (>50% PO)"
                    END as stock_status'),
                    DB::raw('COUNT(*) as count')
                )
                ->where('supplier', $user->bpid)
                ->whereYear('plan_delv_date', $currentYear)
                ->groupBy('stock_status')
                ->get();
        }

        return [
            'monthly_orders' => $monthlyOrders,
            'supplier_performance' => $isSuperAdmin ? $supplierPerformance : $partPerformance,
            'stock_distribution' => $isSuperAdmin ? $stockByCategory : $stockDistribution,
            'is_super_admin' => $isSuperAdmin
        ];
    }

    private function getRecentActivities($user, $isSuperAdmin)
    {
        if ($isSuperAdmin) {
            return DB::table('upload_order_histories')
                ->select('file_name', 'uploaded_at', 'upload_by', 'type')
                ->orderByDesc('uploaded_at')
                ->limit(10)
                ->get();
        } else {
            return DB::table('upload_order_histories')
                ->where('upload_by', $user->bpid)
                ->select('file_name', 'uploaded_at', 'type')
                ->orderByDesc('uploaded_at')
                ->limit(10)
                ->get();
        }
    }

    private function calculateOnTimeDelivery()
    {
        // Logika untuk menghitung on-time delivery rate
        $totalOrders = DB::table('orders')->count();
        $onTimeOrders = DB::table('orders')
            ->where('standard', 'OK')
            ->count();

        return $totalOrders > 0 ? round(($onTimeOrders / $totalOrders) * 100, 2) : 0;
    }

    private function calculateStockAccuracy()
    {
        // Logika untuk menghitung stock accuracy
        $totalItems = DB::table('orders')->count();
        $accurateItems = DB::table('orders')
            ->where('stock', '>=', DB::raw('qty_po * 0.8')) // Stock minimal 80% dari PO
            ->count();

        return $totalItems > 0 ? round(($accurateItems / $totalItems) * 100, 2) : 0;
    }

    private function calculateStandardOKRate($bpid)
    {
        $totalOrders = DB::table('orders')
            ->where('supplier', $bpid)
            ->count();

        $okOrders = DB::table('orders')
            ->where('supplier', $bpid)
            ->where('standard', 'OK')
            ->count();

        return $totalOrders > 0 ? round(($okOrders / $totalOrders) * 100, 2) : 0;
    }

    private function calculateStockAdequacy($bpid)
    {
        $adequateOrders = DB::table('orders')
            ->where('supplier', $bpid)
            ->where('stock', '>=', DB::raw('qty_po * 0.5')) // Stock minimal 50% dari PO
            ->count();

        $totalOrders = DB::table('orders')
            ->where('supplier', $bpid)
            ->count();

        return $totalOrders > 0 ? round(($adequateOrders / $totalOrders) * 100, 2) : 0;
    }
}
