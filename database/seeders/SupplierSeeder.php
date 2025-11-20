<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Supplier;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        Supplier::create([
            'bpid' => 'BP01',
            'name' => 'Supplier Plat Besi',
        ]);

        Supplier::create([
            'bpid' => 'BP02',
            'name' => 'Supplier Baja Ringan',
        ]);

        Supplier::create([
            'bpid' => 'BP03',
            'name' => 'Supplier Aluminium',
        ]);

        Supplier::create([
            'bpid' => 'KYBI1',
            'name' => 'KAYABA INDONESIA',
        ]);

        // Tambahan supplier agar sinkron dengan user seeder
        Supplier::create([
            'bpid' => 'SLIDACJ01',
            'name' => 'CV.SLI',
        ]);

        Supplier::create([
            'bpid' => 'SLIDBMK01',
            'name' => 'CV. slida',

        ]);

        Supplier::create([
            'bpid' => 'SLIDAAA01',
            'name' => 'PT. slide',
        ]);
    }
}
