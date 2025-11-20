<?php

namespace App\Http\Controllers;

use App\Imports\MasterImport;
use App\Template\TemplateOrderDownload;
use App\Imports\OrdersImport;
use App\Models\UploadOrderHistory;
use App\Models\Order;
use App\Models\OrderHistory;
use App\Models\Part;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class OrdersImportController extends Controller
{
    /**
     * Tampilkan halaman utama import orders/master data
     */
    public function orderView()
    {
        $user = Auth::user();
        $isSuperAdmin = (int) $user->status === 1;

        // Current month/year untuk filter bulan berjalan
        $currentMonth = Carbon::now()->month;
        $currentYear  = Carbon::now()->year;

        // 1. Data Orders (hanya supplier login + bulan berjalan)
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
            ->where('orders.supplier', $user->bpid)
            ->whereNotNull('orders.stock')
            ->where('orders.stock', '!=', '')
            ->whereMonth('orders.plan_delv_date', $currentMonth)
            ->whereYear('orders.plan_delv_date', $currentYear)
            ->groupBy(
                'orders.part_no',
                'orders.supplier',
                'orders.plan_delv_date',
                'parts.name',
                'orders.stock',
                'orders.qty_po',
                'orders.previous_qty_po',
                'orders.qty_po_change',
                'orders.created_at',
                'orders.updated_at'
            )
            ->orderBy('orders.part_no', 'desc');

        $orders = $ordersQuery->paginate(20, ['*'], 'orders_page');

        // 2. History perubahan (bulan berjalan)
        $orderHistories = OrderHistory::where('supplier', $user->bpid)
            ->whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->orderBy('created_at', 'desc')
            ->paginate(15, ['*'], 'history_page');

        // 3. Histori upload pribadi
        $uploadHistories = UploadOrderHistory::where('upload_by', $user->bpid)
            ->where('type', 'order')
            ->orderBy('uploaded_at', 'desc')
            ->paginate(10, ['*'], 'upload_history_page');

        // 4. Super Admin only
        $allUserHistories = collect();
        $masterHistories   = collect();
        if ($isSuperAdmin) {
            $allUserHistories = UploadOrderHistory::where('type', 'order')
                ->orderBy('uploaded_at', 'desc')
                ->paginate(20, ['*'], 'all_history_page');

            $masterHistories = UploadOrderHistory::where('type', 'master')
                ->orderBy('uploaded_at', 'desc')
                ->paginate(20, ['*'], 'master_history_page');
        }

        // ===================================================================
        // LOGIKA KHUSUS UNTUK SUPPLIER (yang paling penting!)
        // ===================================================================
        $lastUploadDate      = null;
        $lastUploadByLabel   = '-';
        $totalOrders = 0;
        $hasBeenDownloaded   = false;
        $hasNewAdminData     = false;


        if (!$isSuperAdmin) {
            // 1. Upload terakhir oleh supplier sendiri
            $lastSupplierUpload = UploadOrderHistory::where('upload_by', $user->bpid)
                ->where('type', 'order')
                ->latest('uploaded_at')
                ->first();

            // 2. Upload master terakhir oleh admin
            $lastAdminUpload = UploadOrderHistory::where('type', 'master')
                ->latest('uploaded_at')
                ->first();

            // 3. Waktu terakhir supplier download data admin (dari kolom updated_at saat mark downloaded)
            $lastDownloadTime = Order::where('supplier', $user->bpid)
                ->where('upload_source', 'admin')
                ->where('downloaded_by_supplier', true)
                ->max('updated_at');

            // 4. Cek apakah ada DATA BARU dari admin untuk supplier ini
            $hasNewAdminData = false;

            if ($lastAdminUpload) {
                if ($lastDownloadTime) {
                    // Ada download sebelumnya → cek apakah ada baris yang di-update setelah download itu
                    $hasNewAdminData = Order::where('supplier', $user->bpid)
                        ->where('upload_source', 'admin')
                        ->where('updated_at', '>', $lastDownloadTime)
                        ->exists();
                } else {
                    // Belum pernah download sama sekali → pasti ada data baru kalau ada data admin
                    $hasNewAdminData = Order::where('supplier', $user->bpid)
                        ->where('upload_source', 'admin')
                        ->exists();
                }
            }

            // 5. Tentukan upload terakhir yang relevan & labelnya
            if ($hasNewAdminData && $lastAdminUpload) {
                $latestUpload    = $lastAdminUpload;
                $lastUploadByLabel = 'Admin';
            } elseif ($lastSupplierUpload) {
                $latestUpload    = $lastSupplierUpload;
                $lastUploadByLabel = $user->bpid;
            } else {
                $latestUpload = $lastAdminUpload ?? $lastSupplierUpload;
                $lastUploadByLabel = $latestUpload?->type === 'master' ? 'Admin' : $user->bpid;
            }

            $lastUploadDate = $latestUpload?->uploaded_at
                ? Carbon::parse($latestUpload->uploaded_at)
                : null;

            // 6. Status download
            $hasBeenDownloaded = !$hasNewAdminData && $lastDownloadTime !== null;

            // 7. Total data dari admin (bulan berjalan)
            // $totalOrders = Order::where('supplier', $user->bpid)
            //     ->where('upload_source', 'admin')
            //     ->whereMonth('plan_delv_date', $currentMonth)
            //     ->whereYear('plan_delv_date', $currentYear)
            //     ->whereNotNull('stock')
            //     ->count();
            $bpid = Auth::user()->bpid;
            $totalOrders = Part::where('bpid', $bpid)->count();
        }

        return view('import-orders', [
            'orders'              => $orders,
            'orderHistories'      => $orderHistories,
            'uploadHistories'     => $uploadHistories,
            'allUserHistories'    => $allUserHistories,
            'masterHistories'     => $masterHistories,
            'user'                => $user,
            'lastUploadDate'      => $lastUploadDate,
            'lastUploadByLabel'   => $lastUploadByLabel,
            'totalOrders'         => $totalOrders,
            'hasBeenDownloaded'   => $hasBeenDownloaded,
            'hasNewAdminData'     => $hasNewAdminData,
        ]);
    }


    /**
     * Download template order Excel
     */
    public function orderTemplate()
    {
        $user = Auth::user();
        $bpid = $user->bpid ?? 'ADMIN';
        $isSuperAdmin = (int) $user->status === 1;

        // 🔥 Tandai semua data dari Admin sebagai "sudah didownload" oleh supplier
        if (!$isSuperAdmin) {
            Order::where('supplier', $bpid)
                ->where('upload_source', 'admin')
                ->update([
                    'downloaded_by_supplier' => true,
                    'updated_at' => now() // Update timestamp untuk tracking
                ]);

            Log::info("Supplier {$bpid} downloaded template, marked admin data as downloaded");
        }

        $prefix = $isSuperAdmin ? 'Master_' : 'Order_';
        $fileName = $prefix . $bpid . '_' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(new \App\Template\TemplateOrderDownload($isSuperAdmin), $fileName);
    }

    /**
     * Import file Excel orders/master
     */
    public function import(Request $request)
    {
        Log::info('=== START IMPORT PROCESS ===');

        $validator = Validator::make($request->all(), [
            'file' => [
                'required',
                'file',
                'max:15360', // naikin sedikit biar aman (15MB)
                function ($attribute, $value, $fail) {
                    $extension = strtolower($value->getClientOriginalExtension());
                    if (!in_array($extension, ['xlsx', 'xls', 'csv'])) {
                        $fail('File harus berformat Excel (.xlsx, .xls) atau CSV (.csv)');
                    }
                },
            ],
        ]);

        if ($validator->fails()) {
            Log::error('VALIDATION FAILED:', $validator->errors()->toArray());
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $user = Auth::user();
        $file = $request->file('file');
        $fileName = $file->getClientOriginalName();
        $isSuperAdmin = (int) $user->status === 1;
        $type = $isSuperAdmin ? 'master' : 'order';

        // Pilih import class sesuai tipe user
        $importClass = $isSuperAdmin ? MasterImport::class : OrdersImport::class;

        DB::beginTransaction();
        try {
            Log::info("Starting import using: $importClass | File: $fileName");

            // INI YANG PENTING: langsung import file-nya (OrdersImport sudah support CSV otomatis)
            $ext = strtolower($file->getClientOriginalExtension());

            $reader = match ($ext) {
                'csv'  => \Maatwebsite\Excel\Excel::CSV,
                'xls'  => \Maatwebsite\Excel\Excel::XLS,
                'xlsx' => \Maatwebsite\Excel\Excel::XLSX,
                default => \Maatwebsite\Excel\Excel::CSV
            };

            Excel::import(new $importClass, $file, null, $reader);


            // Simpan histori upload
            UploadOrderHistory::create([
                'upload_by'   => $user->bpid ?? $user->username,
                'file_name'   => $fileName,
                'uploaded_at' => now(),
                'type'        => $type,
            ]);

            DB::commit();
            Log::info('IMPORT SUCCESS', ['file' => $fileName, 'type' => $type]);

            $successMessage = $isSuperAdmin
                ? 'Master data berhasil diupload!'
                : 'Data stok berhasil diupload!';

            return redirect()->back()->with('success', $successMessage);
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            DB::rollBack();
            $failures = $e->failures();
            $errorMessages = collect($failures)->pluck('errors')->flatten()->unique();

            return redirect()->back()->withErrors([
                'error' => 'Ada kesalahan pada data: ' . $errorMessages->implode(', ')
            ])->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('IMPORT FAILED:', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return redirect()->back()->withErrors([
                'error' => 'Gagal mengimpor file: ' . $e->getMessage()
            ])->withInput();
        }
    }
}
