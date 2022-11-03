<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Configure;
use App\Models\Tenant;
use App\Models\Domain;
use App\Models\Hostname;
use App\Models\DiscordNotify;
use App\Models\ProgramModule;
use App\Models\Graph;
use App\Models\FingerPrint;
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
    private function notify2Discrd($last_updated, $next_update) {
        $tenants = Tenant::pluck('name', 'id');
        foreach ($tenants as $tid => $tname) {
            $domains = Domain::where('tenant_id', $tid)->pluck('name', 'id');
            foreach ($domains as $did => $dname) {
                $urls = Configure::select('cvalue')
                        ->where('tenant_id', $tid)->where('domain_id', $did)
                        ->where('ckey', 'discord_url')->get();
                if ($urls === null) {
                    // search urls without domain id
                    $urls = Configure::select('cvalue')
                        ->where('tenant_id', $tid)
                        ->where('ckey', 'discord_url')->get();
                    if ($urls === null) {
                        continue;
                    }
                }

                $dns = DiscordNotify::select('hostnames.name', DB::raw('min(discord_notifies.id)'))
                    ->join('hostnames', 'hostname.id', 'discord_notifies.hostname_id')
                    ->where('discord_notifies.tenant_id', $tid)
                    ->where('discord_notifies.domain_id', $did)
                    ->whereBetween('discord_notifies.created_at', $last_updated, $next_update)
                    ->groupBy('discord_notifies.hostname_id')
                    ->get();
                $hostnames = [];
                foreach ($dns as $dn) {
                    $hostnames[] = $dn['name'];
                }
                $hostlist = implode(',', $hostnames);
                $users = Configure::select('cvalue')
                        ->where('tenant_id', $tid)->where('domain_id', $did)
                        ->where('ckey', 'discord_user')
                        ->pluck('cvalue')->toArray();
                if (count($users) === 0) {
                    $users = Configure::select('cvalue')
                        ->where('tenant_id', $tid)
                        ->where('ckey', 'discord_user')
                        ->pluck('cvalue')->toArray();
                }
                $users = implode(' ', $users);
                $client = new Client;
                $headers = [
                    'Content-Type: application/json',
                    'Accept: application/json',
                ];
                $options = [
                    'content' => "$users $hostlist@[$tname:$dname]",
                ];
                foreach ($urls as $url) {
                    $client->request('post', $url['cvalue'], [
                        'json' => $options,
                        'headers' => $headers,
                    ]);
                }
            } 
        }
    }

    /**
     * notify to Email server
     */
    public function notify2Email($last_updated, $next_update) {
        $tenants = Tenant::pluck('name', 'id');
        foreach ($tenants as $tid => $tname) {
            $mails = [];
            $to = Configure::where('ckey', 'email_to')->first();
            if ($to) {
                $to = $to->cvalue;
            }
            $ccs_d = Configure::where('tenant_id', $tid)
                ->whereNull('domain_id')
                ->where('ckey', 'email_cc')
                ->pluck('cvalue');
            $domains = Domain::where('tenant_id', $tid)->pluck('name', 'id');
            foreach ($domains as $did => $dname) {
                $ccs = Configure::where('tenant_id', $tid)
                    ->where('domain_id', $did)
                    ->where('ckey', 'email_cc')
                    ->pluck('cvalue');
                $dns = DiscordNotify::where('tenant_id', $tid)
                    ->where('domain_id', $did)
                    ->whereBetween('created_at', $last_updated, $next_update)
                    ->get();
                $maili = [];
                foreach ($dns as $dn) {
                    if ($dn->finger_id) {
                        $hostname = Hostname::find($dn->hostname_id);
                        $finger = FingerPrint::find($dn->finger_id);
                        $pm = ProgramModule::find($finger->program_module_id);
                        $maili[$hostname->name]['fingers'][$pm->name] = [$dh->type_id, $pm->flg_white, $finger->finger_print];
                    }
                    if ($dn->graph_id) {
                        $graph = Graph::find($dn->graph_id);
                        $parent = ProgramModule::find($graph->parent_id);
                        $child = ProgramModule::find($graph->child_id);
                        $graphs[$parent->name][] = $child->name;
                        $flg_graphs[$parent->name] = $dn->type_id;
                        $flg_bw[$pm->name] = $pm->flg_white;
                        $maili[$hostname->name]['graphs'][$parent->name]['child'][] = $child->name;
                        $maili[$hostname->name]['graphs'][$parent->name]['child'][] = $child->name;
                        $maili[$hostname->name]['graphs'][$parent->name]['type_id'] = $dn->type_id;
                        $maili[$hostname->name]['graphs'][$parent->name]['flg_white'] = $pm->flg_white;
                    }
                }
                $mails[$dname] = $maili;
                $ccs2to = implode(',', $ccs->toArray());
                if ($ccs->length() > 0) {
                    Mail::to($ccs2to)->send(new DiffNotifyMail([$dname => $maili]));
                }
            }

            if ($to) {
                if ($ccs_d) {
                    Mail::to($to)->cc($ccs_d)->send(new DiffNotifyMail($mails));
                } else {
                    Mail::to($to)->send(new DiffNotifyMail($mails));
                }
            }
        }
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $last_updated_config = Configure::select('cvalue')->where('ckey', 'next_update')->first();
        if (!$last_updated_config) {
            $last_updated_config = '2022-01-01 00:00:00';
        }
        $last_updated = new Carbon($last_updated_config);
        $next_update = new Carbon;

        try {
            DB::beginTransaction();
            $this->notify2Discord($last_updated, $next_update);
            $this->notify2Email($last_updated, $next_update);

            // update last invocation datetime
            if (!$last_update_config) {
                $last_update_config = new Configure;
                $last_update_config->ckey = 'next_update';
            }
            $last_update_config->cvalue = $next_update->format('Y-m-d H:i:s');
            $last_update_config->save();
            DB::commit();
        } catch (\Exception $e) {
            Log::error($e);
            DB::rollback();
        }
        return Command::SUCCESS;
    }
}
