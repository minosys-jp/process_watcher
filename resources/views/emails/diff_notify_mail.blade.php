以下のプログラム状態の変更を検出しました。({{ \Carbon\Carbon::now()->format('Y-m-d H:i:s') }})

-*-*- {{ $dname }} ドメイン -*-*-
@foreach ($programs as $hname => $hosts)
--- {{ $hname }} ホスト ---
{{ implode("\n", $hosts) }}

-----------------------------
@endforeach
-*-*-*-*-*-*-*-*-*-*-*-*-*-*-
---
プロセス監視システム
Powered by Skyster Inc. (c) All rights reserved.
