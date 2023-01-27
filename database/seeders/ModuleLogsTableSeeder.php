<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ModuleLog;

class ModuleLogsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        ModuleLog::create([
            'id' => 1,
            'status' => ModuleLog::FLG_WHITE,
            'finger_print_id' => 1,
        ]);
        ModuleLog::create([
            'id' => 2,
            'status' => ModuleLog::FLG_WHITE,
            'finger_print_id' => 2,
        ]);
        ModuleLog::create([
            'id' => 3,
            'status' => ModuleLog::FLG_WHITE,
            'finger_print_id' => null,
        ]);
        ModuleLog::create([
            'id' => 4,
            'status' => ModuleLog::FLG_BLACK1,
            'finger_print_id' => 3,
        ]);
        $mlog = ModuleLog::find(3);
        $mlog->graphs()->sync([1]);
    }
}
