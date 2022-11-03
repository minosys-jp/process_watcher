<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Graph;

class GraphsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        Graph::create([
            'parent_id' => 1,
            'parent_version' => 1,
            'child_id' => 3,
            'child_version' => 1,
        ]);
        Graph::create([
            'parent_id' => 2,
            'parent_version' => 2,
            'child_id' => 3,
            'child_version' => 1,
        ]);
    }
}
