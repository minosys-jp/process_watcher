<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Configure;
use App\Models\Tenant;
use App\Models\Domain;
use App\Models\Hostname;
use App\Models\ProgramModule;
use App\Models\Graph;
use App\Models\FingerPrint;
use App\Models\ModuleLog;
use App\Mail\DiffNotifyMail;
use GuzzleHttp\Client;
use Carbon\Carbon;

class NotifyDiscord extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:NotifyDiscord';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notify to dicord server';

    /**
     * notify to discord server
     */
    private function notify2Discord($next_update) {
        $mlogs = ModuleLog::where('flg_discord', 0)
               ->where('status', '>=', ModuleLog::FLG_BLACK1)
               ->where('created_at', '<=', $next_update);
        $mlogs = $mlogs->get();
        $discords = [];
        $hash = [];
Log::debug("updated at " . $next_update);
Log::debug("notify2Discord: got mlogs:" . $mlogs->count() . "\n");
        foreach ($mlogs as $mlog) {
            if ($mlog->finger_print_id) {
                // finger print
                $h = $mlog->finger_print->program_module->hostname;
            } else {
                // graph
                $g = $mlog->graphs()->first();
                if ($g) {
                    $h = $g->parent->hostname;
                }
            }
            $d = $h->domain;
            $t = $d->tenant;
            if (in_array($t, $hash)) {
                if (!in_array($d, $hash[$t->id])) {
                    $hash[$t->id][$d->id] = [];
                }
            } else {
                $hash[$t->id] = [];
                $hash[$t->id][$d->id] = [];
            }
            $hash[$t->id][$d->id][] = $h->name;
        }
        foreach ($hash as $t => $hval) {
            foreach ($hval as $d => $hnames) {
                $url = Configure::select('cvalue')
                        ->where('tenant_id', $t)->where('domain_id', $d)
                        ->where('ckey', 'discord_url')->first();
                if (!$url) {
                    $url = Configure::select('cvalue')
                        ->where('tenant_id', $t)
                        ->where('ckey', 'discord_url')->first();
                }
                if (!$url) {
                    continue;
                }
                $url = $url->cvalue;
                $users = Configure::select('cvalue')
                        ->where('tenant_id', $t)->where('domain_id', $d)
                        ->where('ckey', 'discord_user')
                        ->pluck('cvalue')->toArray();
                if (count($users) === 0) {
                    $users = Configure::select('cvalue')
                        ->where('tenant_id', $t)
                        ->where('ckey', 'discord_user')
                        ->pluck('cvalue')->toArray();
                }
                if (count($users) === 0) {
                    continue;
                }
                $users = implode(' ', $users);
                $tname = Tenant::find($t)->name;
                $dname = Domain::find($d)->name;
                $hostlist = implode(',', $hnames);
                $discords[$url] = "$users $hostlist@[$tname:$dname]";
            }
        }
        $client = new Client;
        $headers = [
            'Content-Type: application/json',
            'Accept: application/json',
        ];
Log::debug("send discord:" . count($discords));
        foreach ($discords as $url => $content) {
            $options = [
                'content' => $content,
            ];
            $client->request('post', $url, [
                'json' => $options,
                'headers' => $headers,
            ]);
            usleep(10000);
        }
    }

    /**
     * notify to Email server
     */
    public function notify2Email($next_update) {
        $mlogs = ModuleLog::where('flg_discord', 0)
               ->where('status', '>=', ModuleLog::FLG_BLACK1)
               ->where('created_at', '<=', $next_update)
               ->get();
        $hash = [];
Log::debug("notify2DEmail: got mlogs\n");
        foreach ($mlogs as $mlog) {
            if ($mlog->finger_print_id) {
                // finger print
                $pm = $mlog->finger_print->program_module;
                $h = $pm->hostname;
            } else {
                // graph
                $g = $mlog->graphs()->first();
                if (!$g) {
                    continue;
                }
                $pm = $g->parent;
                $h = $g->parent->hostname;
            }
            $d = $h->domain;
            $t = $d->tenant;
            if (!in_array($t->id, $hash)) {
                $hash[$t->id] = [];
            }
            if (!in_array($d->id, $hash[$t->id])) {
                $hash[$t->id][$d->id] = [];
            }
            if (!array_key_exists($h->name, $hash[$t->id][$d->id])) {
                $hash[$t->id][$d->id] = [ $h->name => [] ];
            }
            $hash[$t->id][$d->id][$h->name][] = $pm->name;
        }

        foreach ($hash as $tid => $domains) {
            foreach ($domains as $did => $hosts) {
                $mails = [];
                $to = Configure::where('ckey', 'email_to')
                    ->where('tenant_id', $tid)
                    ->where('domain_id', $did)
                    ->pluck('cvalue')->toArray();
                if (count($to) === 0) {
                    $to = Configure::where('ckey', 'email_to')
                        ->where('tenant_id', $tid)
                        ->pluck('cvalue')->toArray();
                }
                if (count($to) === 0) {
                    continue;
                }
                $dname = Domain::find($did)->name;
                \Mail::to($to)->send(new DiffNotifyMail($dname, $hosts));
                usleep(10000);
            }
        }
Log::debug("sent emails");
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $last_updated_config = Configure::select('id', 'cvalue')->where('ckey', 'next_update')->first();
        $config_id = null;
        $last_updated = null;
        if (!$last_updated_config) {
            $last_updated = '2022-01-01 00:00:00';
        } else {
            $last_updated = $last_updated_config->cvalue;
            $config_id = $last_updated_config->id;
        }
Log::debug("last_updated:" . $last_updated);
        $last_updated = Carbon::parse($last_updated);
        $next_update = Carbon::now();

Log::debug("start NotifyDiscord command");
        try {
            DB::beginTransaction();
            $this->notify2Discord($next_update);
            $this->notify2Email($next_update);
            if ($last_updated_config) {
                $last_updated_config->cvalue = $next_update;
                $last_updated_config->save();
            } else {
                $config = new Configure;
                $config->ckey = 'next_update';
                $config->cvalue = $next_update;
                $config->save();
            }
            ModuleLog::where('flg_discord', 0)
                ->where('created_at', '<=', $next_update)
                ->update(['flg_discord' => 1]);
            DB::commit();
        } catch (\Exception $e) {
            Log::error($e);
            DB::rollback();
        }
        return Command::SUCCESS;
    }
}
