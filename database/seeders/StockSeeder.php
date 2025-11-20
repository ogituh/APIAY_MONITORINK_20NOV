<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Stock;
use Carbon\Carbon;

class StockSeeder extends Seeder
{
    public function run(): void
    {
        Stock::create([
            'bpid'        => 'SLIDAAA01 ',
            'part_no'     => 'INGOT AC2B-F',
            'quantity'    => 5000,
            'insert_date' => Carbon::now()->toDateString(),
            'insert_by'   => 'system',
        ]);

        Stock::create([
            'bpid'        => 'SLIDBMK01',
            'part_no'     => 'INGOT AC2B-F',
            'quantity'    => 500,
            'insert_date' => Carbon::now()->toDateString(),
            'insert_by'   => 'system',
        ]);

        Stock::create([
            'bpid'        => 'SLIDAAA01',
            'part_no'     => 'INGOT AC4D',
            'quantity'    => 1000,
            'insert_date' => Carbon::now()->addDays(1)->toDateString(),
            'insert_by'   => 'system',
        ]);

        Stock::create([
            'bpid'        => 'SLIDACJ01 ',
            'part_no'     => '12001-03081-GJ9',
            'quantity'    => 32322,
            'insert_date' => Carbon::now()->toDateString(),
            'insert_by'   => 'system',
        ]);
    }
}
