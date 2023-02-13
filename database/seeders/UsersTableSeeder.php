<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        User::create([
            'email' => 'minoru@minosys.com',
            'name' => 'Minoru Matsumoto',
            'password' => Hash::make('matsumoto21'),
            'flg_admin' => 1,
            'tenant_id' => null,
        ]);

	User::create([
            'email' => 'operators@skyster.net',
            'name' => 'Skyster Operator',
            'password' => Hash::make('opr-skyster-net'),
            'flg_admin' => 1,
            'tenant_id' => null,
	]);
    }
}
