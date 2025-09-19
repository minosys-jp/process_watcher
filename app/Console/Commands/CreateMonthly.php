<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\ModuleLog;

class CreateMonthly extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:CreateMonthly';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command to create a monthly backup';

    private function graphBackup($zip, $cutDatetime, $logid) {
        $offset = 0;
        $iname = 'graph_module_log_' . $cutDatetime->format('Ymd') . '.csv';
        $fname = storage_path('app/backup/' . $iname);
        $fp = fopen($fname, 'w');
        $count = 0;
        do {
            $logs = DB::table('graph_module_log')
                ->select('graph_id', 'module_log_id')
                ->where('module_log_id', '<=', $logid)
                ->offset($offset)
                ->limit(100000)
                ->get();
            foreach ($logs as $log) {
                $arr = [$log->graph_id, $log->module_log_id];
                fputcsv($fp, $arr);
            }
            $count = $logs->count();
echo "graph_module_logs:" . $count;
            $offset += $count;
        } while($count > 0);

        DB::table('graph_module_log')
            ->where('module_log_id', '<=', $logid)
            ->delete();
        fclose($fp);
        $zip->addFile($fname, $iname);
    }

    private function logBackup($zip, $cutDatetime, $logid) {
        $iname = 'module_logs_' . $cutDatetime->format('Ymd') . '.csv';
        $fname = storage_path('app/backup/' . $iname);
        $fp = fopen($fname, 'w');
        $offset = 0;
        $count = 0;
        do {
            $logs = DB::table('module_logs')
                ->select('id', 'status', 'finger_print_id', 'flg_discord', 'created_at', 'updated_at')
                ->where('id', '<=', $logid)
                ->offset($offset)
                ->limit(100000)
                ->get();
            foreach ($logs as $log) {
                $arr = [$log->id, $log->status, $log->finger_print_id ?? '', $log->flg_discord, $log->created_at, $log->updated_at];
                fputcsv($fp, $arr);
            }
            $count = $logs->count();
            $offset += $count;
echo "module_logs:" . $count;
        } while ($count > 0);
        DB::table('module_logs')
            ->where('id', '<=', $logid)
            ->delete();
        fclose($fp);
        $zip->addFile($fname, $iname);
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $cutDatetime = Carbon::now()->subMonth(1);
        $logid = ModuleLog::where('created_at', '<', $cutDatetime)
            ->max('id');
        $logid = $logid ?? 0;
Log::debug("logid <=" . $logid);
        $zfname = storage_path('app/backup/logs_' . $cutDatetime->format('Ymd') . '.zip');
        $zip = new \ZipArchive();
        $zip->open($zfname, \ZipArchive::CREATE);

        try {
            DB::beginTransaction();
            $this->graphBackup($zip, $cutDatetime, $logid);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            Log::error($e);
        }

        try {
            DB::beginTransaction();
            $this->logBackup($zip, $cutDatetime, $logid);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            Log::error($e);
        }
        $zip->close();
        unlink(storage_path('app/backup/graph_module_log_' . $cutDatetime->format('Ymd') . '.csv'));
        unlink(storage_path('app/backup/module_logs_' . $cutDatetime->format('Ymd') . '.csv'));
        return Command::SUCCESS;
    }
}
