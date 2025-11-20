<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class SupplierApiController extends Controller
{
    /**
     * Pull data orders berdasarkan bpid supplier (authenticated user).
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOrders(Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->bpid) {
            return Response::json([
                'success' => false,
                'message' => 'Unauthorized: User tidak valid atau tidak punya BPID.',
                'data' => null
            ], 401);
        }

        $bpid = $user->bpid;
        $perPage = $request->get('limit', 20); // Default 20 items per page
        $page = $request->get('page', 1);

        try {
            // Query mirip di OrdersImportController (tapi simplified untuk API)
            $ordersQuery = DB::table('orders')
                ->join('parts', function ($join) {
                    $join->on('orders.part_no', '=', 'parts.part_no')
                        ->whereColumn('orders.supplier', 'parts.bpid');
                })
                ->select([
                    'orders.plan_delv_date',
                    'orders.supplier',
                    'orders.part_no',
                    'orders.stock',
                    'orders.qty_po',
                    'orders.previous_qty_po',
                    'orders.qty_po_change',
                    'orders.created_at',
                    'orders.updated_at',
                    'parts.name as part_name',
                    DB::raw('SUM(orders.qty_po) as total_qty'),
                    DB::raw('ANY_VALUE(orders.standard) as standard')
                ])
                ->where('orders.supplier', $bpid)
                ->whereNotNull('orders.stock')
                ->where('orders.stock', '!=', '')
                ->groupBy('orders.part_no', 'orders.supplier', 'orders.plan_delv_date', 'parts.name', 'orders.stock', 'orders.qty_po', 'orders.previous_qty_po', 'orders.qty_po_change', 'orders.created_at', 'orders.updated_at')
                ->orderBy('orders.part_no', 'desc');

            $orders = $ordersQuery->paginate($perPage, ['*'], 'page', $page);

            return Response::json([

                'success' => true,
                'message' => 'Data orders berhasil diambil.',
                'data' => [
                    'orders' => $orders->items(), // Array data
                    'pagination' => [
                        'current_page' => $orders->currentPage(),
                        'total_pages' => $orders->lastPage(),
                        'total_items' => $orders->total(),
                        'per_page' => $orders->perPage(),
                        'next_page_url' => $orders->nextPageUrl(),
                        'prev_page_url' => $orders->previousPageUrl()
                    ]
                ]
            ], 200);
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    /**
     * Contoh endpoint lain: Get history perubahan orders (opsional).
     */
    public function getOrderHistory(Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->bpid) {
            return Response::json([
                'success' => false,
                'message' => 'Unauthorized.',
                'data' => null
            ], 401);
        }

        $bpid = $user->bpid;
        $perPage = $request->get('limit', 15);

        $histories = \App\Models\OrderHistory::where('supplier', $bpid)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return Response::json([
            'success' => true,
            'message' => 'History berhasil diambil.',
            'data' => [
                'histories' => $histories->items(),
                'pagination' => [
                    'current_page' => $histories->currentPage(),
                    'total_pages' => $histories->lastPage(),
                    'total_items' => $histories->total()
                ]
            ]
        ], 200);
    }
}
