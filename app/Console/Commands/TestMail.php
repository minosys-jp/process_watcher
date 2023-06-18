<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Models\Configure;
use App\Models\Tenant;
use App\Mail\DiffNotifyMail;

class TestMail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:TestMail {tenant}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $hosts = [];
        $hosts['sampleHost'] = [];
        $hosts['sampleHost'][] = 'sample_module';
        $to = Configure::where('configures.ckey', 'email_to')
            ->join('tenants', 'tenants.id', 'configures.tenant_id')
            ->pluck('configures.cvalue')->toArray();
        if (count($to) === 0) {
            $to = Configure::where('ckey', 'email_to')
                 ->pluck('cvalue')->toArray();
        }
        if (count($to) === 0) {
            return Command::SUCCESS;
        }
        $dname = "skyster.net";
        \Mail::to($to)->send(new DiffNotifyMail($dname, $hosts));
        echo "mailed to $to\n";
        return Command::SUCCESS;
    }
}
