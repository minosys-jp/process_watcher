<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
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
        $YMD = str_replace("logs_", "", $fname);
        $YMD = str_replace(".zip", "", $YMD);
        $year = substr($YMD, 0, 4);
        $month = substr($YMD, 4, 2);
        $day = substr($YMD, 6);
        return Carbon::parse("$year-$month-$day");
    }

    private function recoverGraphLog($stream, $ymd, $ofile) {
        $count = 0;
        $first = TRUE;
        echo "ReciverGraphLog:$ymd\n";
        while (($fields = fgetcsv($stream)) !== FALSE) {
            if ($count++ % 5000 == 0) {
		if (!$first) {
		    fwrite($ofile, ";\n");
		    $first = TRUE;
		}
            }
            if ($first) {
echo $count, "\n";
                fwrite($ofile, "INSERT INTO graph_module_log (graph_id, module_log_id) VALUES ");
                $first = FALSE;
            } else {
                fwrite($ofile, ",");
            }
            fprintf($ofile, "(%d, %d)", $fields[0], $fields[1]);
        }
        if (!$first) {
            fwrite($ofile, ";\n");
        }
    }

    private function recoverModuleLog($stream, $ymd, $ofile) {
        $count = 0;
        $first = TRUE;
        echo "Recover Module Log";
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
        if ($zip->open($this->backup . $fname) !== FALSE) {
            $ymd = $this->getYMDFromFname($fname);
            $ymdname = $ymd->format('Ymd');
            $ofile = fopen($this->backup . "$ymdname.sql", "w");
            if ($ofile !== FALSE) {
                echo "Recover $fname\n";
	        // graph_log table
	        $stream = $zip->getStreamIndex(0, \ZipArchive::FL_UNCHANGED);
	        if ($stream !== FALSE) {
	            $this->recoverGraphLog($stream, $ymd, $ofile);
	            fclose($stream);
	        }
	        // module_logs table
	        $stream = $zip->getStreamIndex(1, \ZipArchive::FL_UNCHANGED);
	        if ($stream !== FALSE) {
	            $this->recoverModuleLog($stream, $ymd, $ofile);
	            fclose($stream);
	        }
                fclose($ofile);
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
            $fname = "logs_$date.zip";
            $this->recover($fname);
        } else {
            foreach (glob($this->backup . "logs_*.zip") as $fname) {
                $fname2 = substr($fname, strlen($this->backup));
                $this->recover($fname2);
            }
        }
        return Command::SUCCESS;
    }
}
