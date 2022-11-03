<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Hostname;

class HostnamesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        Hostname::create([
            'id' => 1,
            'domain_id' => 1,
            'code' => 'example-host',
            'name' => 'サンプルホスト',
        ]);
    }
}
