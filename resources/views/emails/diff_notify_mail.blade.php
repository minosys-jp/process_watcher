以下のプログラム変更が検出されました。({{ new \Carbon\Carbon->format('Y-m-d H:i:s') }})

@foreach ($domains as $dname => $domain)
-*-*- {{ $dname }} ドメイン -*-*-
@foreach ($domain as $hname => $host)
--- {{ $hname }} ホスト ---
o Black プロセスの起動
<?php
$blacks = [];
foreach ($host['fingers'] as $pname => $arr) {
    if ($arr[1] != \App\Models\ProgramModule::FLG_WHITE) {
        $blacks[] = $pname;
    }
}
?>
{{ implode("\n", $blacks) }}

o 内容の変更
@foreach ($host['fingers'] as $pname => $arr)
[{{ $arr[0] == \App\Models\DiscordNotify::TYPE_NEW ? 'NEW' : 'UPDATE' }}]{{ $pname }} => {{ $arr[2] }}
@endforeach

o 読み込み DLL の変更
@foreach ($host['graphs'] as $pname => $hs)
[{{ $hs['type_id'] == \App\Models\DiscordNotify::TYPE_NEW ? 'NEW' : 'UPDATE' }}: {{ $pname }}]
{{ impode("\n", $hs['child']) }}

@endforeach
-----------------------------
@endforeach
-*-*-*-*-*-*-*-*-*-*-*-*-*-*-
@endforeach
---
プロセス監視システム
Powered by Skyster Inc. (c) 2022 All rights reserved.
