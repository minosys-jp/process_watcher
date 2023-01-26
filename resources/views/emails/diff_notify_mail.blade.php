以下のBLACKプログラムが検出されました。({{ new \Carbon\Carbon->format('Y-m-d H:i:s') }})

-*-*- {{ $dname }} ドメイン -*-*-
@foreach ($programs as $hname => $hosts)
--- {{ $hname }} ホスト ---
{{ implode("\n", $hosts) }}

-----------------------------
@endforeach
-*-*-*-*-*-*-*-*-*-*-*-*-*-*-
@endforeach
---
プロセス監視システム
Powered by Skyster Inc. (c) All rights reserved.
