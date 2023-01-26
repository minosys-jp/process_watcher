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
            'program_module_id' => 1,
            'status' => ModuleLog::FLG_WHITE,
            'finger_print_id' => 1,
            'graph_id' => null,
        ]);
        ModuleLog::create([
            'program_module_id' => 2,
            'status' => ModuleLog::FLG_WHITE,
            'finger_print_id' => 2,
            'graph_id' => null,
        ]);
        ModuleLog::create([
            'program_module_id' => 2,
            'status' => ModuleLog::FLG_WHITE,
            'finger_print_id' => null,
            'graph_id' => 1,
        ]);
        ModuleLog::create([
            'program_module_id' => 1,
            'status' => ModuleLog::FLG_BLACK1,
            'finger_print_id' => 3,
            'graph_id' => null,
        ]);
    }
}
