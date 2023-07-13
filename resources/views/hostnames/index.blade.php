@extends('adminlte::page')

@section('title', 'ホスト名一覧')

@section('content_header')
    <h1 class="m-0 text-dark">ホスト名一覧</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <form method="post" action="{{ route('hostname.csv', $did) }}">
                        @csrf
                        <div>検出履歴の出力(CSV)</div>
                        <select name="ym">
                        @foreach ($selectors as $key => $value)
                            <option value="{{ $key }}">{{ $value }}</option>
                        @endforeach
                        </select>
                        <button>CSV出力</button>
                    </form>
                </div>
                <div class="card-body">
                    <table class="table">
                        <tr>
                            <th>番号</th>
                            <th>ホストコード</th>
                            <th>名称</th>
                            <th>状態</th>
                            <th>操作</th>
                        </tr>
                        @foreach ($hostnames as $hostname)
                        <tr>
                            <td>{{ $hostname->id }}</td>
                            <td>{{ $hostname->code }}</td>
                            <td>{{ $hostname->name }}</td>
                            <td class="@if ($hostname->status >= \App\Models\ModuleLog::FLG_BLACK1) red @endif ">{{ \App\Models\ModuleLog::FLG_NAMES[$hostname->status] }}</td>
                            <td>
                                <a href="{{ route('hostname.show', $hostname->id) }}" class="btn btn-success">モジュール一覧</a>
                                <a href="{{ route('hostname.edit', $hostname->id) }}" class="btn btn-primary">編集</a>
                        </tr>
                        @endforeach
                    </table>
                </div>
                <div class="card-footer">
                    {{ $hostnames->links() }}
                </div>
            </div>
        </div>
    </div>
@stop

@push ('css')
<style>
.red {
    font-weight: bold;
    color: red;
}
</style>
@endpush
