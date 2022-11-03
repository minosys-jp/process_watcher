@extends('adminlte::page')

@section('title', '更新履歴')

@section('content_header')
    <h1 class="m-0 text-dark">更新履歴</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <table class="table">
                        <tr>
                            <th>番号</th>
                            <th>B/Wフラグ</th>
                            <th>バージョン</th>
                            <th>ハッシュ値</th>
                            <th>更新日時</th>
                        </tr>
                        @foreach ($shas as $sha)
                        <tr>
                            <td>{{ $sha->id }}</td>
                            <td>{{ $sha->flg_white == \App\Models\ProgramModule::FLG_WHITE ? 'W' : 'B' }}</td>
                            <td>{{ $sha->version }}</td>
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
