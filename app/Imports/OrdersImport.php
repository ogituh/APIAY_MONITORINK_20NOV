<?php

namespace App\Imports;

use App\Models\Order;
use App\Models\OrderHistory;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class OrdersImport implements ToCollection, WithHeadingRow, WithCalculatedFormulas
{
    public function headingRow(): int
    {
        return 1;
    }

    public function collection(Collection $rows)
    {
        Log::info('=== START SUPPLIER ORDER IMPORT (QTY_PO BISA DI-UPDATE) ===');

        $user    = Auth::user();
        $created = $updated = $skipped = 0;

        foreach ($rows as $index => $row) {
            $excelRow = $index + 2; // karena heading di baris 1

            // Skip baris kosong
            if ($row->filter()->isEmpty()) {
                $skipped++;
                continue;
            }

            $supplier     = trim($row['supplier'] ?? '');
            $planDelvDate = trim($row['plan delv date'] ?? $row['plan_delv_date'] ?? '');
            $partNo       = trim($row['part no'] ?? $row['part_no'] ?? '');
            $stock        = trim($row['stock'] ?? '');
            $standard     = trim($row['standart'] ?? $row['otomatis (ok/nok)'] ?? $row['standard'] ?? '');
            $qtyPoRaw     = trim($row['qty po'] ?? $row['qty_po'] ?? $row['qtypo'] ?? '');

            Log::info("Row {$excelRow}", compact('supplier', 'planDelvDate', 'partNo', 'stock', 'qtyPoRaw'));

            // Validasi wajib
            if (empty($supplier) || empty($partNo) || $stock === '' || $stock === null) {
                Log::warning("Row {$excelRow} skipped: missing required data");
                $skipped++;
                continue;
            }

            if (!preg_match('/^\d{8}$/', $planDelvDate)) {
                Log::warning("Row {$excelRow}: invalid date format", ['date' => $planDelvDate]);
                $skipped++;
                continue;
            }

            $date        = Carbon::createFromFormat('Ymd', $planDelvDate)->format('Y-m-d');
            $bpid        = strtoupper($supplier);
            $newStock    = (float) preg_replace('/[^0-9.-]/', '', $stock);
            if ($newStock < 0) $newStock = 0;

            $standardValue = strtoupper($standard) === 'OK' ? 'OK' : 'NOK';

            // Qty PO dari file Excel
            $qtyPo = $qtyPoRaw !== '' ? (int) preg_replace('/[^0-9]/', '', $qtyPoRaw) : 0;

            // Cari record existing berdasarkan 3 kunci unik
            $order = Order::where('supplier', $bpid)
                ->where('part_no', $partNo)
                ->where('plan_delv_date', $date)
                ->first();

            $isNewRecord = !$order;

            // Simpan nilai lama untuk history
            $previousStock  = $isNewRecord ? 0 : (float) ($order->stock ?? 0);
            $previousQtyPo  = $isNewRecord ? 0 : (float) ($order->qty_po ?? 0);

            // =================================================================
            // CREATE BARU
            // =================================================================
            if ($isNewRecord) {
                $partExists = \App\Models\Part::where('bpid', $bpid)
                    ->where('part_no', $partNo)
                    ->exists();

                if (!$partExists) {
                    Log::warning("Row {$excelRow}: Part tidak terdaftar → skip", [
                        'supplier' => $bpid,
                        'part_no'  => $partNo
                    ]);
                    $skipped++;
                    continue;
                }

                $order = new Order();
                $order->supplier               = $bpid;
                $order->part_no                = $partNo;
                $order->plan_delv_date         = $date;
                $order->qty_po                 = $qtyPo;           // baru → langsung pakai dari Excel
                $order->upload_source          = 'supplier';
                $order->downloaded_by_supplier = false;

                Log::info("Row {$excelRow}: CREATED NEW → qty_po = {$qtyPo}");
                $created++;
            }
            // =================================================================
            // UPDATE EXISTING → SEKARANG QTY_PO BISA DIUBAH JUGA!
            // =================================================================
            else {
                // Update qty_po jika ada nilai di Excel (bahkan kalau 0, tetap override)
                if ($qtyPoRaw !== '') {
                    $order->qty_po = $qtyPo;
                    Log::info("Row {$excelRow}: UPDATED EXISTING → qty_po diubah {$previousQtyPo} → {$qtyPo}");
                } else {
                    Log::info("Row {$excelRow}: UPDATED EXISTING → qty_po tetap = {$order->qty_po}");
                }
                $updated++;
            }

            // Update stock & standard (selalu)
            $order->stock          = $newStock;
            $order->previous_stock = $previousStock;
            $order->stock_change   = $newStock - $previousStock;
            $order->standard       = $standardValue;
            $order->upload_source  = $order->upload_source ?? 'supplier';
            $_keepForTracking      = $order->downloaded_by_supplier; // jangan ilangin nilai lama
            $order->downloaded_by_supplier = false;
            $order->updated_at     = now();
            $order->save();

            // Restore flag download kalau memang sudah pernah didownload sebelumnya
            if ($keepForTracking ?? false) {
                $order->downloaded_by_supplier = true;
                $order->save();
            }

            // =================================================================
            // HISTORY (termasuk perubahan qty_po)
            // =================================================================
            OrderHistory::create([
                'supplier'         => $bpid,
                'part_no'          => $partNo,
                'plan_delv_date'   => $date,
                'previous_qty_po'  => $previousQtyPo,
                'new_qty_po'       => $order->qty_po ?? 0,
                'qty_po_change'    => ($order->qty_po ?? 0) - $previousQtyPo,
                'previous_stock'   => $previousStock,
                'new_stock'        => $newStock,
                'stock_change'     => $newStock - $previousStock,
                'standard'         => $standardValue,
                'updated_by'       => $user->bpid,
                'file_name'        => request()->file('file')->getClientOriginalName(),
                'uploaded_at'      => now(),
                'note'             => $isNewRecord
                    ? "Created by supplier (qty_po: {$qtyPo})"
                    : "Updated by supplier (qty_po: {$previousQtyPo} → {$order->qty_po})",
            ]);
        }

        Log::info('IMPORT SELESAI', compact('created', 'updated', 'skipped'));
        session()->flash('success', "Sukses! {$created} data baru, {$updated} diupdate (qty_po ikut berubah), {$skipped} diskip.");
    }
}
