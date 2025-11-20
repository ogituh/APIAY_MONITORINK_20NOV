<?php

namespace App\Imports;

use App\Models\Stock;
use App\Models\Supplier;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Auth;

class StocksImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // Hapus kolom kosong biar gak ada index null
        $row = array_filter($row, fn($value) => !is_null($value) && $value !== '');

        if (!isset($row['part_no']) || !isset($row['quantity'])) {
            return null;
        }

        $bpid = isset($row['bpid']) ? strtoupper($row['bpid']) : 'UNKNOWN';

        return Stock::create([
            'part_no' => $row['part_no'],
            'quantity' => $row['quantity'],
            'insert_date' => now()->format('Y-m-d'),
            'insert_by' => $row['insert_by'] ?? (Auth::user()->username ?? 'system'),
            'bpid' => $bpid,
        ]);
    }
}
