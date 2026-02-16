<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Lot;

class LotSeeder extends Seeder
{
    public function run(): void
    {
        Lot::create([
            'title'=>'BrrBrr Car CA200',
            'description'=>'Perfect Brrr Car CA200',
            'starting_price'=>1000.00,
            'current_price'=>1000.00,
            'ends_at'=>Carbon::now()->addDays(3),
        ]);
    }
}
