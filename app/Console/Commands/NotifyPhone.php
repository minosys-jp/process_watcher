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
use GuzzleHttp\Client;
use Carbon\Carbon;

class NotifyPhone extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:NotifyPhone';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notify by smart-phone';

   /**
     * Notifier for the smart-hone
     * @var string
     */
    private $notifier = '/usr/local/bin/send_alert.sh';

    /**
      * Notification json file
      * @var string
      */
    private $tmpJson = '/tmp/send_alert.json';

    /**
      * To address
      */
    private $to = 'operations@skyster.net';

    /**
      * Drive function for the smart phone
      * @param $dname Domain name
      * @param $hosts Host/Program name array
      */
    private function send($dname, $hosts) {
      $subject = "Process_watcher alert mail";
      $json = new Array();
      $json["subject"] = $subject;
      $json["recipients"] = $this->to;
      $body = new Array();
      $body[] = "detected program change events\\n";
      $body[] = "Domain: " . $dname . "\\n";
      foreach ($hosts as $hname => $pm) {
        $body[] = "Host: " . $hname + "\\n";
        $body[] = implode("\\n", $pm);
        $body[] = "\\n\\n";
      }
      $body[] = "Powered by Skyster Inc.\\n";
      $json["body"] = implode("\\n", $body);
      $f = fopen($this->tmpJson, "w");
      fwrite($f, json_encode($json));
      fclose($f);
      exec($this->notifier . " " . $this->tmpJson);
Log::debug("invoke " . $this->notifier . " " . $this->tmpJson);
    }

    /**
     * notify to Smart-Phone
     */
    public function notify2phone($next_update) {
        $mlogs = ModuleLog::where('flg_discord', 0)
               ->where('status', '>=', ModuleLog::FLG_BLACK1)
               ->where('created_at', '<=', $next_update)
               ->get();
        $hash = [];
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
                $dname = Domain::find($did)->name;
                $this->send($dname, $hosts);
                usleep(10000);
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
        $last_updated_config = Configure::select('id', 'cvalue')->where('ckey', 'next_update')->first();
        $config_id = null;
        $last_updated = null;
        if (!$last_updated_config) {
            $last_updated = '2022-01-01 00:00:00';
        } else {
            $last_updated = $last_updated_config->cvalue;
            $config_id = $last_updated_config->id;
        }
        $last_updated = Carbon::parse($last_updated);
        $next_update = Carbon::now();

        try {
            DB::beginTransaction();
            $this->notify2phone($next_update);
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
