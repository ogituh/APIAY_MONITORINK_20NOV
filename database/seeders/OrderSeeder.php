<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;
use Carbon\Carbon;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        Order::create([
            'plan_delv_date' => Carbon::now()->toDateString(),
            'supplier'       => 'BP01', // kode supplier
            'part_no'        => 'PRT-001',
            'qty_po'         => 1200,
        ]);

        Order::create([
            'plan_delv_date' => Carbon::now()->addDays(1)->toDateString(),
            'supplier'       => 'BP02',
            'part_no'        => 'PRT-002',
            'qty_po'         => 250,
        ]);

        Order::create([
            'plan_delv_date' => Carbon::now()->subDays(1)->toDateString(),
            'supplier'       => 'BP03',
            'part_no'        => 'PRT-003',
            'qty_po'         => 3000,
        ]);

        Order::create([
            'plan_delv_date' => Carbon::now()->addDays(2)->toDateString(),
            'supplier'       => 'BP04',
            'part_no'        => 'PRT-004',
            'qty_po'         => 1800,
        ]);

        Order::create([
            'plan_delv_date' => Carbon::now()->addDays(3)->toDateString(),
            'supplier'       => 'BP05',
            'part_no'        => 'PRT-005',
            'qty_po'         => 950,
        ]);
    }
}
