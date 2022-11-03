<?php

namespace Database\Seeders;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
        $this->call(ConfiguresTableSeeder::class);
        $this->call(TenantsTableSeeder::class);
        $this->call(DomainsTableSeeder::class);
        $this->call(UsersTableSeeder::class);
        $this->call(HostnamesTableSeeder::class);
        $this->call(ProgramModulesTableSeeder::class);
        $this->call(FingerPrintsTableSeeder::class);
        $this->call(GraphsTableSeeder::class);
    }
}
