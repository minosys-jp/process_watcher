<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Domain;

class DomainsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        Domain::create([
            'id' => 1,
            'tenant_id' => 1,
            'code' => 'KAMISHIRANE-HOSPITAL',
            'name' => '上白根病院',
        ]);
        Domain::create([
            'id' => 2,
            'tenant_id' => 1,
            'code' => 'TAKEYAMA-HOSPITAL',
            'name' => '竹山病院',
        ]);
        Domain::create([
            'id' => 3,
            'tenant_id' => 1,
            'code' => 'SAWAYAKA',
            'name' => 'リハビリセンターさわやか',
        ]);
    }
}
