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
            'code' => 'RAPID-AI',
            'name' => 'RAPID-AI',
        ]);
    }
}
