<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\Tenant;
use App\Models\Domain;
use App\Models\Hostname;
use App\Models\ProgramModule;
use App\Models\ModuleLog;
use App\Models\Graph;
use App\Libs\Common;

class HostnameController extends Controller
{
    public $lib;

    public function __construct(Common $lib) {
        $this->lib = $lib;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $did)
    {
        //
        $domain = Domain::find($did);
        $selectors = [];
        $startDate = Carbon::parse('2023-06-01');
        $endDate = Carbon::now();
        $endDate->day = 0;
        while ($startDate->lt($endDate)) {
            $selectors[$startDate->format('Y-m-01')] = $startDate->format('Y年m月');
            $startDate->addMonth();
        }
        $hostnames = Hostname::where('domain_id', $did)->paginate(50);
        foreach ($hostnames as $h) {
            $hostnameAlarm = Hostname::select('hostnames.id', DB::RAW('max(program_modules.alarm) AS alarm'))
                ->join('program_modules', 'program_modules.hostname_id', 'hostnames.id')
                ->where('hostnames.id', $h->id)
                ->groupBy('hostnames.id')
                ->first();
            $h->status = $hostnameAlarm ? $hostnameAlarm->alarm : \App\Models\ModuleLog::FLG_GRAY;
        }
        $breads = [
            'ホーム' => route('home'),
            'ドメイン一覧' => route('domain.index'),
            'ホスト一覧' => route('hostname.index', $did),
        ];
        return view('hostnames.index')->with(compact('did', 'hostnames', 'selectors', 'breads'));
    }

    /**
     * ホストの詳細表示
     */
    public function show(Request $request, $hid) {
	$search = $request->search;
        $hostname = Hostname::find($hid);
        if (!$hostname) {
            abort(404);
        }
        $modules = ProgramModule::select('*')
		 ->where('hostname_id', $hid)
                 ->where('flg_exe', 1);
        if ($search) {
            $modules = $modules->where('name', 'like', "%$search%");
        }
        $modules = $modules->paginate(50);
        $breads = [
            'ホーム' => route('home'),
            'ドメイン一覧' => route('domain.index'),
	    'ホスト一覧' => route('hostname.index', $hostname->domain_id),
	    'モジュール一覧' => route('hostname.show', $hid),
        ];
        return view('hostnames.show')->with(compact('hostname', 'modules', 'search', 'breads'));
    }

    /**
     * ホストの状態変更
     */
    public function change(Request $req, $hid) {
        $host = Hostname::find($hid);
        if (!$host) {
            abort(404);
        }

	DB::beginTransaction();
	try {
Log::debug("Host id:" . $host->id);
            $modules = $host->program_modules()->where('program_modules.alarm', '!=', ModuleLog::FLG_WHITE)->select('program_modules.*')->get();
	    Log::debug("Change Status modules:" . $modules->count());
	    $count = 0;
            foreach ($modules as $pm) {
if ($count++ < 10) {
Log::debug($pm->id . "=>" . $pm->alarm);
}
                $this->lib->change_status($req, $pm);
            }
            session()->flash('flashSuccess', 'ホストの状態を更新しました');
	    DB::commit();
	} catch (\Exception $e) {
            session()->flash('flashFailure', 'DB エラーが発生しました');
            // session()->flash('flashFailure', $e->getMessage());
	    DB::rollback();
        }
        return redirect()->route('hostname.show', $hid);
    }

    /**
     * ホスト名の編集
     */
    public function edit($hid) {
        $hostname = Hostname::find($hid);
        if (!$hostname) {
            abort(404);
        }
        $breads = [
            'ホーム' => route('home'),
            'ドメイン一覧' => route('domain.index'),
	    'ホスト一覧' => route('hostname.index', $hostname->domain_id),
	    'ホスト編集' => route('hostname.edit', $hid),
        ];
        return view('hostnames.edit')->with(compact('hostname', 'breads'));
    }

    /**
     * ホスト名の編集
     */
    public function update(Request $request, $hid) {
        $hostname = Hostname::find($hid);
        if (!$hostname) {
            abort(404);
        }
        $hostname->fill($request->only(['name']));
        $hostname->save();
        session()->flash('flashSuccess', 'ホスト名を更新しました');
        return redirect()->route('hostname.edit', $hid);
    }

    /**
     * 対象Domainの変更検知 CSV を出力する
     */
    public function csv(Request $request, $did) {
        $startDate = Carbon::parse($request->ym);
        $endDate = $startDate->copy()->addMonth();
        $endDate->day = 0;
        $sql = Hostname::join('program_modules', 'program_modules.hostname_id', 'hostnames.id')
            ->join('finger_prints', 'finger_prints.program_module_id', 'program_modules.id')
            ->select('hostnames.name as hostname', 'program_modules.name', 'finger_prints.created_at')
            ->where('hostnames.domain_id', $did)
            ->whereBetween('finger_prints.created_at', [$startDate, $endDate])
            ->orderBy('hostname')
            ->orderBy('program_modules.name')
            ->orderBy('finger_prints.created_at');

        $callback = function() use ($sql) {
            $stream = fopen('php://output', 'w');
            stream_filter_prepend($stream, 'convert.iconv.utf-8/cp932//TRANSLIT');
            fputcsv($stream, ['ホスト名', 'プログラム名', '変更検出日時' ]);
            foreach ($sql->cursor() as $item) {
                fputcsv($stream, [$item->hostname, $item->name, $item->created_at]);
            }
            fclose($stream);
        };
        $filename = sprintf('signal-%02d-%s.csv', $did, $startDate->format('Ym'));
        $header = [ 'Content-Type' => 'application/octet-stream' ];
        return response()->streamDownload($callback, $filename, $header);
    }
}
