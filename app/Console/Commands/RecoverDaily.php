<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RecoverDaily extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:RecoverDaily {date? : Date to recover}';
    private $backup = "storage/app/backup/";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recover Daily files';

    private function getYMDFromFname($fname) {
        $YMD = str_replace(str_replace($fname, "logs_", ""), ".zip", "");
        $year = substr($YMD, 0, 4);
        $month = substr($YMD, 4, 2);
        $day = substr($YMD, 6);
        return new Carbon($year, $month, $day);
    }

    private function recoverGraphLog($stream, $ymd) {
        while (($fields = fgetcsv($stream)) !== FALSE) {
            DB::table('graph_module_log')
                ->insert([
                    'graph_id' => $fields[0],
                    'module_log_id' => $fields[1],
                ]);
        }
    }

    private function recoverModuleLog($stream) {
        while (($fields = fgetcsv($stream)) !== FALSE) {
            DB::table('module_logs')
                ->insert([
                    'id' => $fields[0],
                    'status' => $fields[1],
                    'finger_print_id' => ($fields[2] === '') ?? NULL,
                    'flg_discord' => $fields[3],
                    'created_at' => $fields[4],
                    'updated_at' => $fields[5],
                ]);
        }
    }

    private function recover($fname) {
        $zip = new \ZipArchive;
        if ($zip->open($fname) !== FALSE) {
            try {
                echo "Recover $name\n";
                DB::beginTransaction();
	        // graph_log table
	        $stream = $zip->getStreamIndex(0, \ZipArchive::FL_UNCHANGED);
	        $ymd = $this->getYMDFromFname($fname);
	        if ($stream !== FALSE) {
	            $this->recoverGraphLog($stream, $ymd);
	            fclose($stream);
	        }
	        // module_logs table
	        $stream = $zip->getStreamIndex(1, \ZipArchive::FL_UNCHANGED);
	        if ($stream !== FALSE) {
	            $this->redcoverModuleLog($stream);
	            fclose($stream);
	        }
                DB::commit();
            } catch (\Excepetion $e) {
                DB::rollback();
                Log::error($e);
            }
            $zip->close();
        } else {
           Log::error("Failed to open $fname");
        }
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $date = $this->argument("date");
        if ($date) {
            $fname = $this->backup . "logs_$date.zip";
            $this->recover($fname);
        } else {
            foreach (glob($this->backup . "logs_*.zip") as $fname) {
                $this->recover($fname);
            }
        }
        return Command::SUCCESS;
    }
}
