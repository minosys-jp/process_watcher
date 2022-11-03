<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\FingerPrint;

class FingerPrintsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        FingerPrint::create([
            'id' => 1,
            'program_module_id' => 1,
            'version' => 1,
            'finger_print' => Hash::make("0123456789ABCDEF"),
        ]);
        FingerPrint::create([
            'id' => 2,
            'program_module_id' => 2,
            'version' => 2,
            'finger_print' => Hash::make("ABCDEFGHIJKLMNOP"),
        ]);
        FingerPrint::create([
            'id' => 3,
            'program_module_id' => 3,
            'version' => 1,
            'finger_print' => Hash::make('aoeiuroihgjs;odh90900OASKODFJ'),
        ]);
    }
}
