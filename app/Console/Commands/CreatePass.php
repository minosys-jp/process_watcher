<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreatePass extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:CreatePass {pass}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new password';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        print(Hash::make($this->argument('pass')));
	print("\n");
        return Command::SUCCESS;
    }
}
