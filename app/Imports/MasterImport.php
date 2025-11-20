<?php

namespace App\Imports;

use App\Models\Part;
use App\Models\Supplier;
use App\Models\User;
use App\Models\OrdersAdmin;        // ← Tambah ini
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class MasterImport implements ToCollection, WithHeadingRow, WithStartRow, SkipsEmptyRows, WithChunkReading
{
    private $chunkSize = 1000;
    private $summary = ['created' => 0, 'updated' => 0, 'skipped' => 0];

    public function headingRow(): int
    {
        return 1;
    }

    public function startRow(): int
    {
        return 2;
    }

    public function chunkSize(): int
    {
        return $this->chunkSize;
    }

    public function collection(Collection $rows)
    {
        Log::info('=== MASTER IMPORT STARTED ===', ['rows_count' => $rows->count()]);
        DB::beginTransaction();
        try {
            // Koleksi untuk bulk insert/update
            $uniqueSuppliers = [];
            $suppliersToCreate = collect();
            $uniqueUsers = [];
            $usersToCreate = collect();
            $uniqueParts = [];
            $partsToCreate = collect();
            $partsToUpdate = collect();
            $uniqueOrders = [];

            foreach ($rows as $row) {
                $rawRow = $row->toArray();

                // Normalisasi kolom
                $supplier   = $this->getValue($rawRow, ['supplier', 'bpid', 0]);
                $planDate   = $this->getValue($rawRow, ['plan_delv_date', 'plan delv date', 'plandate', 'deliverydate', 'delvdate', 1]);
                $partNo     = $this->getValue($rawRow, ['part_no', 'part no', 'partno', 2]);
                $partName   = $this->getValue($rawRow, ['part_name', 'part name', 'name', 3]);
                $qtyPo      = $this->getValue($rawRow, ['qty_po', 'qty po', 'qtypo', 'qty', 4]);
                $rawStock   = $this->getValue($rawRow, ['stock', 6]);

                // Validasi wajib
                if (empty($supplier) || empty($partNo)) {
                    $this->summary['skipped']++;
                    continue;
                }

                $bpid     = trim(strtoupper($supplier));
                $partNo   = trim($partNo);
                $partName = !empty($partName) ? trim($partName) : '-';
                $qtyPo    = is_numeric($qtyPo) ? (int) $qtyPo : 0;
                $stock    = is_numeric($rawStock) ? (int) $rawStock : 0;

                // ===================================================================
                // 1. SUPPLIER + USER
                // ===================================================================
                if (!isset($uniqueSuppliers[$bpid])) {
                    if (!Supplier::where('bpid', $bpid)->exists()) {
                        $suppliersToCreate->push([
                            'bpid'       => $bpid,
                            'name'       => $bpid,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                    }
                    if (!User::where('bpid', $bpid)->exists()) {
                        $usersToCreate->push([
                            'bpid'       => $bpid,
                            'username'   => $bpid,
                            'password'   => Hash::make('123'),
                            'phone'      => null,
                            'status'     => 0,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                    }
                    $uniqueSuppliers[$bpid] = true;
                }

                // ===================================================================
                // 2. PART
                // ===================================================================
                $partKey = $bpid . '|' . $partNo;
                if (!isset($uniqueParts[$partKey])) {
                    $existingPart = Part::where('bpid', $bpid)->where('part_no', $partNo)->first();
                    $partData = [
                        'bpid'       => $bpid,
                        'part_no'    => $partNo,
                        'name'       => $partName,
                        'updated_at' => now(),
                    ];

                    if (!$existingPart) {
                        $partData['created_at'] = now();
                        $partsToCreate->push($partData);
                    } else {
                        $partsToUpdate->push(array_merge($partData, ['id' => $existingPart->id]));
                    }
                    $uniqueParts[$partKey] = true;
                }

                // ===================================================================
                // 3. ORDER – KHUSUS ADMIN → PAKAI TABEL orders_admins
                // ===================================================================
                if ($qtyPo > 0 && !empty($planDate)) {
                    $parsedDate = $this->smartParseDate($planDate);
                    if ($parsedDate) {
                        $formattedDate = $parsedDate->format('Y-m-d');
                        $orderKey = $bpid . '|' . $partNo . '|' . $formattedDate;

                        if (!isset($uniqueOrders[$orderKey])) {
                            // GUNAKAN MODEL OrdersAdmin
                            $order = OrdersAdmin::where('supplier', $bpid)
                                ->where('part_no', $partNo)
                                ->where('plan_delv_date', $formattedDate)
                                ->first();

                            if (!$order) {
                                OrdersAdmin::create([
                                    'supplier'              => $bpid,
                                    'part_no'               => $partNo,
                                    'plan_delv_date'        => $formattedDate,
                                    'qty_po'                => $qtyPo,
                                    'stock'                 => $stock,
                                    'upload_source'         => 'admin',
                                    'downloaded_by_supplier' => false,
                                    'created_at'            => now(),
                                    'updated_at'            => now(),
                                ]);
                                $this->summary['created']++;
                                Log::info('ORDER CREATED (Master → orders_admins)', compact('bpid', 'partNo', 'formattedDate', 'qtyPo'));
                            } else {
                                $updateData = [
                                    'qty_po'         => $qtyPo,
                                    'upload_source'  => 'admin',
                                    'downloaded_by_supplier' => false,
                                    'updated_at'     => now(),
                                ];
                                if ($rawStock !== null && trim((string)$rawStock) !== '') {
                                    $updateData['stock'] = $stock;
                                }
                                $order->update($updateData);
                                $this->summary['updated']++;
                            }
                            $uniqueOrders[$orderKey] = true;
                        }
                    } else {
                        Log::warning('Gagal parse Plan Delv Date (Master Import)', ['raw' => $planDate, 'bpid' => $bpid]);
                        $this->summary['skipped']++;
                    }
                } else {
                    $this->summary['skipped']++;
                }
            }

            // Bulk insert
            if ($suppliersToCreate->isNotEmpty()) Supplier::insertOrIgnore($suppliersToCreate->toArray());
            if ($usersToCreate->isNotEmpty()) User::insertOrIgnore($usersToCreate->toArray());
            if ($partsToCreate->isNotEmpty()) Part::insertOrIgnore($partsToCreate->toArray());
            if ($partsToUpdate->isNotEmpty()) {
                foreach ($partsToUpdate as $part) {
                    Part::where('id', $part['id'])->update(['name' => $part['name'], 'updated_at' => now()]);
                }
            }

            DB::commit();
            Log::info('=== MASTER IMPORT SUCCESS ===', $this->summary);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('MASTER IMPORT FAILED', [
                'error'  => $e->getMessage(),
                'trace'  => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    // ===================================================================
    // HELPER: Ambil nilai dari array dengan banyak kemungkinan key/index
    // ===================================================================
    private function getValue($row, $keys)
    {
        if (is_array($keys)) {
            foreach ($keys as $key) {
                if (is_numeric($key) && isset($row[$key])) {
                    return $row[$key];
                }
                if (is_string($key) && isset($row[$key])) {
                    return $row[$key];
                }
                // Normalisasi key
                $normalized = strtolower(preg_replace('/[^a-z0-9]/', '', $key));
                foreach ($row as $k => $v) {
                    if (strtolower(preg_replace('/[^a-z0-9]/', '', $k)) === $normalized) {
                        return $v;
                    }
                }
            }
        }
        return null;
    }

    // ===================================================================
    // HELPER: Parsing tanggal SUPER AKURAT
    // ===================================================================
    private function smartParseDate($value): ?Carbon
    {
        if ($value === null || $value === '' || trim($value) === '') {
            return null;
        }
        $value = trim($value);

        // 1. YYYYMMDD
        if (preg_match('/^(\d{8})$/', $value, $m)) {
            $date = Carbon::createFromFormat('Ymd', $value);
            if ($date && $date->year >= 2000 && $date->year <= 2100) {
                return $date;
            }
        }

        // 2. YYYYMM → pakai tanggal hari ini di bulan tersebut
        if (preg_match('/^(\d{6})$/', $value, $m)) {
            $year  = substr($value, 0, 4);
            $month = substr($value, 4, 2);
            if ($year >= 2000 && $year <= 2100 && $month >= 1 && $month <= 12) {
                $today = Carbon::today();
                return Carbon::create(
                    $year,
                    $month,
                    $today->day,
                    $today->hour,
                    $today->minute,
                    $today->second
                );
            }
        }

        // 3. Excel serial date
        if (is_numeric($value) && $value > 40000 && $value < 90000) {
            try {
                return Carbon::instance(ExcelDate::excelToDateTimeObject($value));
            } catch (\Exception $e) {
                Log::warning('Excel date parse failed', ['value' => $value]);
            }
        }

        // 4. Format tanggal biasa
        $formats = ['Y-m-d', 'd/m/Y', 'd-m-Y', 'd.m.Y', 'j F Y', 'j M Y', 'd M Y'];
        foreach ($formats as $format) {
            $date = Carbon::createFromFormat($format, $value);
            if ($date && $date->year >= 2000 && $date->year <= 2100) {
                return $date;
            }
        }

        // 5. Carbon parse terakhir
        try {
            $date = Carbon::parse($value);
            if ($date && $date->year >= 2000 && $date->year <= 2100) {
                return $date;
            }
        } catch (\Exception $e) {
            // no-op
        }

        return null;
    }

    public function getSummary()
    {
        return $this->summary;
    }
}
