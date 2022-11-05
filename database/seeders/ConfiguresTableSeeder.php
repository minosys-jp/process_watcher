<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Configure;

class ConfiguresTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        Configure::create([
            'tenant_id' => 1, 
            'ckey' => 'discord_url',
            'cvalue' => 'https://discord.com/api/webhooks/1020819899171491930/yowRUhlxkFwdbgemnSTQRx7Hf_-z3zHebhNUw8ITdyYix5XqW40MOHleXZKgc8f2xeZL',
        ]);
        Configure::create([
            'tenant_id' => 1,
            'ckey' => 'discord_user',
            'cvalue' => '<@!938604248067887156>',
        ]);
        Configure::create([
            'tenant_id' => 1,
            'ckey' => 'email_to',
            'cvalue' => 'minosys3@gmail.com',
        ]);
    }
}
