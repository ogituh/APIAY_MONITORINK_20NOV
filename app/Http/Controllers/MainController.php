<?php

namespace App\Http\Controllers;

use App\Exports\MonitoringExport;
use App\Models\Order;
use App\Models\Supplier;
use App\Models\OrderHistory;
use App\Models\OtpVerify;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class MainController extends Controller
{
    public function otp()
    {
        return view('formotp');
    }

    public function resendOtp(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan atau belum login.'
            ], 401);
        }

        $hp = $user->phone;
        if (!$hp) {
            return response()->json([
                'success' => false,
                'message' => 'Nomor HP belum terdaftar.'
            ], 400);
        }

        $otp = rand(100000, 999999);

        OtpVerify::create([
            'bpid' => $user->bpid,
            'otp' => $otp,
            'hp' => $hp,
            'expired_date' => now()->addMinutes(5)
        ]);

        session(['otp' => $otp, 'otp_hp' => $hp]);

        return response()->json([
            'success' => true,
            'message' => 'Kode OTP baru telah dikirim ulang.',
            'otp' => $otp
        ]);
    }

    // =================================================================
    // MONITORING – INI YANG DIPERBAIKI TOTAL
    // =================================================================
    public function monitoring(Request $request)
    {
        $start = $request->get('start_date');
        $end   = $request->get('end_date');

        $startDate = $start ?? '';
        $endDate   = $end ?? '';

        $isFiltered = $start && $end;

        // 1. Ambil SEMUA supplier (pastikan semua tampil)
        $suppliers = Supplier::all(); // <- sebelumnya pake with(), tapi kita proses manual

        // 2. Proses setiap supplier (bahkan yang kosong pun tetap masuk)
        $suppliersWithSummary = $suppliers->map(function ($supplier) use ($isFiltered, $start, $end) {
            $bpid = $supplier->bpid;

            // Query orders dari supplier (data yang diisi supplier)
            $query = Order::where('supplier', $bpid);

            if ($isFiltered) {
                $query->whereBetween('plan_delv_date', [$start, $end]);
            } else {
                $query->whereYear('plan_delv_date', now()->year)
                    ->whereMonth('plan_delv_date', now()->month);
            }

            $ordersFromSupplier = $query->get();

            // Jika supplier belum isi → coba ambil dari order_admins (data master)
            if ($ordersFromSupplier->isEmpty()) {
                $adminQuery = DB::table('orders_admins')->where('supplier', $bpid);

                if ($isFiltered) {
                    $adminQuery->whereBetween('plan_delv_date', [$start, $end]);
                } else {
                    $adminQuery->whereYear('plan_delv_date', now()->year)
                        ->whereMonth('plan_delv_date', now()->month);
                }

                $ordersFromAdmin = $adminQuery->get();

                $ordersFromSupplier = $ordersFromAdmin->map(function ($item) {
                    return (object) [
                        'plan_delv_date' => $item->plan_delv_date,
                        'part_no'        => $item->part_no,
                        'qty_po'         => $item->qty_po,
                        'stock'          => $item->stock ?? 0,
                        'standard'       => is_null($item->stock) || $item->stock == ''
                            ? null
                            : ($item->stock >= $item->qty_po ? 'OK' : 'NOK'),
                        'created_at'     => $item->created_at ?? now(),
                        'updated_at'     => $item->updated_at ?? now(),
                    ];
                });
            }

            // Ambil last update dari OrderHistory (supplier)
            // 1. Prioritas utama: supplier pernah isi sendiri → pakai waktu dia
            $lastSupplierUpdate = OrderHistory::where('supplier', $bpid)->max('created_at');

            if ($lastSupplierUpdate) {
                $lastUpdate = $lastSupplierUpdate;
            } else {
                // 2. Kalau supplier belum pernah isi → cek apakah DIA TERDAMPAK oleh upload master terakhir
                $lastMasterUploadTime = DB::table('upload_order_histories')
                    ->where('type', 'master')
                    ->max('uploaded_at');

                if (!$lastMasterUploadTime) {
                    $lastUpdate = null;
                } else {
                    // Cek apakah ADA data di orders_admins untuk supplier ini
                    // yang created_at atau updated_at >= waktu upload master terakhir
                    $affected = DB::table('orders_admins')
                        ->where('supplier', $bpid)
                        ->where(function ($q) use ($lastMasterUploadTime) {
                            $q->where('created_at', '>=', $lastMasterUploadTime)
                                ->orWhere('updated_at', '>=', $lastMasterUploadTime);
                        })
                        ->exists();

                    $lastUpdate = $affected ? $lastMasterUploadTime : null;
                }
            }

            // Kalau supplier belum pernah update sama sekali → tetap null (akan ditampilkan "No Data")

            $totalItems = $ordersFromSupplier->count();

            // Hitung OK dan NOK hanya jika supplier SUDAH PERNAH ISI STOK (ada di tabel orders atau order_histories)
            $supplierEverFilledStock = OrderHistory::where('supplier', $bpid)->exists()
                || Order::where('supplier', $bpid)->whereNotNull('stock')->where('stock', '!=', '')->exists();

            if ($supplierEverFilledStock) {
                // Baru boleh hitung OK/NOK berdasarkan standard dari data supplier
                $okCount  = $ordersFromSupplier->where('standard', 'OK')->count();
                $nokCount = $ordersFromSupplier->where('standard', 'NOK')->count();
            } else {
                // Belum pernah isi stok sama sekali → paksa OK = 0, NOK = 0
                // Biar tidak kelihatan "merah" padahal belum waktunya dinilai
                $okCount  = 0;
                $nokCount = 0;
            }

            // Jika tidak ada order sama sekali (bahkan dari admin), tetap tampilkan supplier
            return [
                'supplier' => $supplier,
                'summary'  => [
                    'total_items' => $totalItems,
                    'ok_count'    => $okCount,
                    'nok_count'   => $nokCount,
                    'last_update' => $lastUpdate, // bisa null → akan tampil "No Data"
                    'all_orders'  => $ordersFromSupplier->isEmpty() ? collect() : $ordersFromSupplier,
                ],
            ];
        });

        // Sort berdasarkan last_update (yang null = paling bawah)
        $suppliersWithSummary = $suppliersWithSummary->sortByDesc(function ($item) {
            return $item['summary']['last_update'] ?? '1900-01-01 00:00:00';
        })->values();

        return view('index', [
            'startDate'            => $startDate,
            'endDate'              => $endDate,
            'suppliersWithSummary' => $suppliersWithSummary,
            'user'                 => Auth::user(),
        ]);
    }

    // =================================================================
    // EXPORT EXCEL – IKUT FILTER
    // =================================================================
    public function exportMonitoring(Request $request)
    {
        $startDate = $request->get('start_date');
        $endDate   = $request->get('end_date');

        // Kirim start & end ke Export (bisa null)
        return Excel::download(
            new MonitoringExport($startDate, $endDate),
            'Monitoring_' . now()->format('Ymd_His') . '.xlsx'
        );
    }

    public function report(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::today()->subDays(7)->toDateString());
        $endDate   = $request->input('end_date', Carbon::today()->toDateString());

        $suppliers = Supplier::with([
            'orders' => fn($q) => $q->whereBetween('plan_delv_date', [$startDate, $endDate]),
            'stocks' => fn($q) => $q->whereBetween('insert_date', [$startDate, $endDate]),
        ])->paginate(10);

        return view('report', compact('suppliers', 'startDate', 'endDate'));
    }
}
