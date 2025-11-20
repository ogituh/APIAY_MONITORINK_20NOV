<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Part;

class PartSeeder extends Seeder
{
    public function run(): void
    {
        Part::create([
            'bpid'        => 'SLIDACD01 ',
            'name'        => 'INGOT AC2B-F',
            'part_no'     => 'INGOT AC2B-F',
            'description' => 'INGOT AC2B-F                ',
            'unit'        => 'lembar',
        ]);

        Part::create([
            'bpid'        => 'SLIDAAA01 ',
            'name'        => 'ALUMINIUM INGOT AC4D        ',
            'part_no'     => 'INGOT AC4D',
            'description' => 'ALUMINIUM INGOT AC4D        ',
            'unit'        => 'pcs',
        ]);

        Part::create([
            'bpid'        => 'SLIDAAA01 ',
            'name'        => 'INGOT AC2B-F',
            'part_no'     => 'INGOT AC2B-F              ',
            'description' => 'INGOT AC2B-F              ',
            'unit'        => 'pcs',
        ]);

        Part::create([
            'bpid'        => 'SLIDACJ01',
            'name'        => 'COLLAR',
            'part_no'     => '12001-03081-GJ9',
            'description' => 'COLLAR-12001-03081-GJ9',
            'unit'        => 'pcs',
        ]);

        Part::create([
            'bpid'        => 'SLIDBMK01',
            'name'        => 'RING',
            'part_no'     => '12001-03081-BMK',
            'description' => 'RING-12001-03081-BMK',
            'unit'        => 'pcs',
        ]);

    }
}
