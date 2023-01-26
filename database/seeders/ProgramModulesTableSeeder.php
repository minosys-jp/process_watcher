<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ProgramModule;

class ProgramModulesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        ProgramModule::create([
            'id' => 1,
            'hostname_id' => 1,
            'name' => "C:\\Skyster\\ProcessWacher\\process_watcher.exe",
        ]);
        ProgramModule::create([
            'id' => 2,
            'hostname_id' => 1,
            'name' => "C:\\Skyster\\ProcessWacher\\sqlite3.dll",
        ]);
    }
}
