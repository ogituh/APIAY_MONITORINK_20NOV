<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller

{
    public function index(Request $request)
    {
        $user = Auth::user();
        $currentMonth = Carbon::now()->month;  // 11 untuk November
        $currentYear = Carbon::now()->year;    // 2025

        // Query mirip dari OrdersImportController, tapi simplified buat API
        $ordersQuery = Order::query()
            ->join('parts', function ($join) {
                $join->on('orders.supplier', '=', 'parts.bpid')
                    ->on('orders.part_no', '=', 'parts.part_no');  // Adjust join kalau perlu
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
            ->where('orders.supplier', $user->bpid)  // Filter by user BPID
            ->whereNotNull('orders.stock')
            ->where('orders.stock', '!=', '')
            ->whereMonth('orders.plan_delv_date', $currentMonth)
            ->whereYear('orders.plan_delv_date', $currentYear)
            ->groupBy(
                'orders.plan_delv_date',
                'orders.supplier',
                'orders.part_no',
                'parts.name',
                'orders.stock',
                'orders.qty_po',
                'orders.previous_qty_po',
                'orders.qty_po_change',
                'orders.created_at',
                'orders.updated_at'
            )
            ->orderBy('orders.part_no', 'desc');

        // Paginate (optional, bisa tambah ?page=1 di URL)
        $orders = $ordersQuery->paginate(20);

        return response()->json([
            'message' => 'Orders berhasil di-fetch.',
            'data' => $orders->items(),  // Array items aja, atau full paginate
            'pagination' => [
                'current_page' => $orders->currentPage(),
                'total' => $orders->total(),
                'per_page' => $orders->perPage(),
                'last_page' => $orders->lastPage(),
            ]
        ], 200);
    }


    public function store(Request $request)
    {
        // 🔥 VALIDASI: Sesuaikan key body Postman lo
        $request->validate([
            'plan_delv_date' => 'required|date',  // NOT NULL di schema
            'bpid' => 'required|string',          // Buat supplier
            'item' => 'required|string',          // Buat part_no
            'qty' => 'required|integer|min:1',    // Buat qty_po
        ]);

        $user = Auth::user();
        if ($user->bpid !== $request->bpid) {
            return response()->json([
                'message' => 'Unauthorized: Anda tidak memiliki izin membuat order untuk BPID ini.'
            ], 403);
        }

        // Format date (kalau perlu, Carbon udah handle date string)
        $planDelvDate = $request->plan_delv_date;  // Langsung pake, asal YYYY-MM-DD

        // 🔥 CREATE: Mapping ke schema table – isi semua required/nullable
        $order = Order::create([
            'plan_delv_date' => $planDelvDate,             // Dari body, NOT NULL
            'supplier' => $request->bpid,                  // Mapping bpid → supplier (NOT NULL)
            'part_no' => $request->item,                   // Mapping item → part_no (NOT NULL)
            'qty_po' => $request->qty,                     // Mapping qty → qty_po (int, NOT NULL? Asumsi ya)
            'stock' => null,                               // Nullable
            'upload_source' => 'supplier',                 // Default buat API store
            'downloaded_by_supplier' => 0,                 // Default 0
            'previous_qty_po' => 0,                        // Default awal (decimal NULL → 0)
            'qty_po_change' => $request->qty,              // Change awal = qty (decimal NULL)
            'standard' => null,                            // Nullable
            'previous_stock' => null,                      // Nullable
            'stock_change' => null,                        // Nullable
            // created_at & updated_at auto
        ]);

        return response()->json([
            'message' => 'Order berhasil ditambahkan.',
            'data' => $order
        ], 201);
    }
}
