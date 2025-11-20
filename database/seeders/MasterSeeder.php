<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\MasterImport;

class MasterSeeder extends Seeder
{
    public function run(): void
    {
        $path = public_path('docs/api_supp_data.xlsx');

        if (!file_exists($path)) {
            $this->command->error("❌ File tidak ditemukan di: {$path}");
            return;
        }

        try {
            Excel::import(new MasterImport, $path);
            $this->command->info("✅ Data berhasil diimport dari {$path}");
        } catch (\Exception $e) {
            $this->command->error("⚠️ Gagal import: " . $e->getMessage());
        }
    }
}
