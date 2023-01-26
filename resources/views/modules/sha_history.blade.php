@extends('adminlte::page')

@section('title', '改変履歴')

@section('content_header')
    <h1 class="m-0 text-dark">改変履歴</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="post">
                        @csrf
                        @method('put')
                        <div class="form-group">
                            <label for="status">プロセス状態の更新</label>
                            <select id="status" name="status">
                                <option value="{{ \App\Models\ModuleLog::FLG_WHITE" selected>WHITE</option>
                                <option value="{{ \App\Models\ModuleLog::FLG_BLACK1" selected>BLACK1 (停止なし)</option>
                                <option value="{{ \App\Models\ModuleLog::FLG_BLACK2" selected>BLACK2 (停止あり)</option>
                            </select>
                        </div>
                        <button class="btn btn-primary">設定</button>
                    </form>
                    <table class="table">
                        <tr>
                            <th>番号</th>
                            <th>状態</th>
                            <th>ハッシュ値</th>
                            <th>更新日時</th>
                        </tr>
                        @foreach ($shas as $sha)
                        <tr>
                            <td>{{ $sha->id }}</td>
                            <td class="@if ($status >= \App\Models\ModuleLog::FLG_BLACK1) red @endif ">{{ \App\Models\ModuleLog::FLG_NAMES[$sha->status] }}
                            <td>{{ $sha->finger_print }}</td>
                            <td>{{ $sha->created_at }}</td>
                        </tr>
                        @endforeach
                    </table>
                </div>
                <div class="card-footer">
                    {{ $shas->links() }}
                </div>
            </div>
        </div>
    </div>
@stop
@push('css')
.red {
    font-weight: bold;
    color: red;
}
@endpush
