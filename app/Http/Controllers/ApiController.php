<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Tenant;
use App\Models\Domain;
use App\Models\Hostname;
use App\Models\ProgramModule;
use App\Models\FingerPrint;
use App\Models\Graph;
use App\Models\Configure;
use GuzzleHttp\Client;
use Carbon\Carbon;

class ApiController extends Controller
{
    private $discord_queue = [];

    //
    public function post(Request $request) {
    Log::debug("enter post");
        // パラメータ取り出し
        $tenant_code = $request->tenant;
        $domain_code = $request->domain;
        $host_code = $request->hostname;
        $fingers = $request->fingers;
        $graphs = $request->graphs;

        // テナント、ドメインの登録を確認
        $tenant = Tenant::where('code', $tenant_code)->first();
        if ($tenant === null) {
            abort(404);
        }
        try {
            DB::beginTransaction();
            $domain = Domain::select('domains.*')->where('domains.code', $domain_code)
                ->join('tenants', 'tenants.id', 'tenant_id')
                ->where('tenants.code', $tenant_code)
                ->first();
            if ($domain === null) {
                abort(404);
            }
    
            // ホスト名を確認/未登録なら登録する
            $hostname = Hostname::select('hostnames.*')
                ->where('hostnames.code', $host_code)
                ->join('domains', 'hostnames.domain_id', 'domains.id')
                ->join('tenants', 'tenants.id', 'domains.tenant_id')
                ->where('domains.code', $domain_code)
                ->where('tenants.code', $tenant_code)
                ->first();
            if (!$hostname) {
                $hostname = new Hostname;
                $hostname->domain_id = $domain->id;
                $hostname->code = $host_code;
                $hostname->name = $host_code;
                $hostname->save();
                $this->queueDiscord('New Host: ' . $hostname->name, $domain->id, $tenant->id);
            }
    
            // フィンガープリントの更新
            if ($fingers && is_array($fingers)) {
                $bNewProc = false;
                $newProcs = [];
                $countProcs = 0;
                foreach ($fingers as $finger) {
                    if (!array_key_exists('name', $finger) || !array_key_exists('finger', $finger)) {
                        continue;
                    }

                    $proc = ProgramModule::where('name', $finger['name'])
                        ->where('hostname_id', $hostname->id)
                        ->first();
                    if ($proc) {
                        // 既存プログラムの更新
                        $proc->version += 1;
                        $bNewProc = false;
                    } else {
                        // 新規プログラムの登録
                        $proc = new ProgramModule;
                        $proc->name = $finger['name'];
                        $proc->hostname_id = $hostname->id;
                        $proc->version = 1;
                        $countProcs += 1;
                        if ($countProcs < 5) {
                            $newProcs[] = basename($proc->name);
                        }
                    }
                    $proc->save();
                    $fprints = new FingerPrint;
                    $fprints->program_module_id = $proc->id;
                    $fprints->version = $proc->version;
                    $fprints->finger_print = $finger['finger'];
                    $fprints->save();
                }
                if (!$bNewProc) {
                    $this->queueDiscord('Updated Finger-print Host: ' . $hostname->name, $domain->id, $tenant->id);
                }
                if (count($newProcs) > 0) {
                    $procs = implode(' ', $newProcs);
                    if ($countProcs >= 5) {
                        $procs += " etc.";
                    }
                    $this->queueDiscord('New program: ' . $procs . " on " . $hostname->name, $domain->id, $tenant->id);
                }
            }

            // 実行ファイルグラフの更新
            $cache = [];
            if ($graphs && is_array($graphs)) {
                foreach ($graphs as $graph) {
                    $exe = $graph['exe'];
                    $dlls = $graph['dlls'];
                    $module_exe = $this->loadModule($cache, $exe, $hostname->id);
                    foreach ($dlls as $dll) {
                        $module_dll = $this->loadModule($cache, $dll, $hostname->id);
                        $graph = new Graph;
                        $graph->parent_id = $module_exe->id;
                        $graph->parent_version = $module_exe->version;
                        $graph->child_id = $module_dll->id;
                        $graph->child_version = $module_dll->version;
                        $graph->save();
                    }
                }
                $this->queueDiscord('Updated graphs Host: ' . $hostname->name, $domain->id, $tenant->id);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([ false, $e->getMessage() ]);
        }

        try {
            $this->postDiscord();
        } catch (\Exception $e) {
            return response()->json([ false, $e->getMessage() ]);
        }

        return response()->json([ true ]);
    }

    private function queueDiscord($message, int $did, int $tid) {
        $key = $tid . ":" . $did;
        if (!isset($this->discord_queue[$key])) {
            $this->discord_queue[$key] = [];
        }
        $this->discord_queue[$key] = [ 'time' => Carbon::now(), 'message' => $message, 'domain_id' => $did, 'tenant_id' => $tid ];
    }

    private function postDiscord() {
        $client = new Client;
        foreach ($this->discord_queue as $dtkey => $messages) {
            $ids = explode(":", $dtkey);
            // domain id 毎に discord 送信
            $config1 = Configure::where('tenant_id', $ids[0])
                ->where('domain_id', $ids[1]);
            $config2 = Configure::where('tenant_id', $ids[0])
                ->where('domain_id', $ids[1]);
            $this->postDiscordInt($client, $config1, $config2, $messages);

            $config1 = Configure::where('tenant_id', $ids[0])
                ->where('domain_id', $ids[1]);
            $config2 = Configure::where('tenant_id', $ids[0])
                ->where('domain_id', $ids[1]);
            // ワイルドカードユーザへの送信
            $this->postDiscordInt($client, $config1, $config2, $messages);
        }
    }

    private function postDiscordInt($client, $config1, $config2, $messages) {
        $urls = $config1->where('ckey', 'discord_url')->pluck('cvalue');
        $users = $config2->where('ckey', 'discord_user')->pluck('cvalue');
        $user = '';
        if ($users->count() > 0) {
            $user = implode(' ', $users->toArray()) . ' ';
        }
        $message = $user;
        foreach ($messages as $m) {
            $message .= $user . $m['time']->format(' [Y-m-d H:i:s] ') . $m['message'] . ";";
        }

        foreach ($urls as $url) {
            $option = [
                'verify' => false,
                'headers' => [
                    'Accept' => 'application/json',
                ],
                'json' => [ 'content' => $message ],
            ];
            $client->request('POST', $url, $option);
        }
    }

    private function loadModule(&$cache, $modname, int $hid) {
        if (isset($cache[$hid][$modname])) {
            return $cache[$hid][$modname];
        }
        if (!array_key_exists($hid, $cache)) {
            $cache[$hid] = [];
        }
        $pmod = ProgramModule::where('name', $modname)
            ->first();
        $cache[$hid][$modname] = $pmod;
        return $pmod;
    }
}
